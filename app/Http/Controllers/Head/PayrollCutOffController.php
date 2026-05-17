<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Notification;
use App\Models\PayrollCutOff;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollCutOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cutoffs = PayrollCutOff::join('statuses', 'payroll_cut_offs.status_id', '=', 'statuses.id')
            ->select('payroll_cut_offs.*', 'statuses.name as status_name')
            ->where('payroll_cut_offs.status_id', 1)

            // ✅ FIXED COUNT (STRICT DEPARTMENT FILTER)
            ->withCount([
                'attendanceEmployees as attendances_count' => function ($query) use ($user) {
                    $query->whereExists(function ($q) use ($user) {
                        $q->select(DB::raw(1))
                          ->from('users')
                          ->whereColumn('users.id', 'attendance_employees.user_id')
                          ->where('users.department_id', $user->department_id);
                    });
                }
            ])

           ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_cut_offs.name', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(payroll_cut_offs.from_cutoff_date, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(payroll_cut_offs.to_cutoff_date, '%b %d, %Y') LIKE ?", ["%{$search}%"]);
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/Head/PayrollCutOff', [
            'cutoffs' => $cutoffs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function attendancePage(Request $request, $id)
    {
        $user = $request->user();

        $cutoff = PayrollCutOff::findOrFail($id);

        $employees = User::where('department_id', $user->department_id)->get();

        $reports = AttendanceEmployee::query()
            ->where('attendance_employees.payroll_cut_off_id', $id)

            // ✅ STRICT FILTER
            ->where('attendance_employees.department_id', $user->department_id)

            ->join('users', 'attendance_employees.user_id', '=', 'users.id')
            ->where('users.department_id', $user->department_id)

            ->with([
                'approvalStatuses.user',
                'approvalStatuses.status',
                'attendances',
                'user.department'
            ])
            ->select(
                'attendance_employees.*',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as employee_name")
            )

            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.first_name', 'like', "%{$search}%")
                      ->orWhere('users.last_name', 'like', "%{$search}%");
                });
            })

            ->when($request->employee_id, function ($q, $empId) use ($user) {
                $q->where('attendance_employees.user_id', $empId)
                  ->where('attendance_employees.department_id', $user->department_id);
            })

            ->when($request->status, fn($q, $statusId) =>
                $q->where('attendance_employees.head_status_id', $statusId)
            )

            ->orderBy('attendance_employees.created_at', 'desc')
            ->paginate(10)
            ->withQueryString()

            ->through(function ($item) use ($cutoff) {

                $leaderEntry = $item->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 3);

                $hrEntry = $item->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'employee_name' => $item->employee_name,
                    'profile_photo' => $item->user?->profile_photo,
                    'report_date' => $item->created_at->format('Y-m-d H:i:s'),
                    'head_status_id' => $item->head_status_id,
                    'name' => $cutoff->name,
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name' => $hrEntry?->status?->name ?? 'Pending',
                    'user' => $item->user,
                    'attendances' => $item->attendances
                        ->filter(fn ($log) =>
                            $log->attendance_date >= $cutoff->from_cutoff_date &&
                            $log->attendance_date <= $cutoff->to_cutoff_date
                        )
                        ->values()
                ];
            });

        return Inertia::render('management/Head/PayrollCutOffList', [
            'cutoff' => $cutoff,
            'reports' => $reports,
            'employees' => $employees,
            'filters' => $request->only(['search', 'status', 'employee_id']),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $user = $request->user();

        $report = AttendanceEmployee::where('id', $id)
            ->where('department_id', $user->department_id)
            ->first();

        if (!$report) {
            throw ValidationException::withMessages([
                'error' => 'Unauthorized action. This record does not belong to your department.'
            ]);
        }

        DB::table('attendance_statuses')->updateOrInsert(
            [
                'attendance_employee_id' => $report->id,
                'user_id' => $user->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $userTypeName = ($user->user_type_id == 1) ? 'HR' : 'Department Head';
        $statusName = ($request->status_id == 7) ? 'Approved' : 'Rejected';

        $title = "{$userTypeName} {$statusName} your Cut Off";
        $message = "Your cut off has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($report, $title, $message);

         return redirect()->back()->with('message', 'Cut Off request processed.');
    }

    private function notifyUsers($report, $title, $message)
    {
        $employee = User::find($report->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';
            $notification = Notification::where('user_id', $employee->id)
                ->whereNull('user_type_id')
                ->where('data', 'LIKE', '%cut_off_id%')
                ->where('data', 'LIKE', '%' . $report->id . '%')
                ->first();

            if ($notification) {
                $notification->update([
                    'title'      => $title,
                    'message'    => $message,
                    'is_read'    => 0,
                    'read_at'    => null,
                    'updated_at' => now(),
                ]);
            } else {
                Notification::create([
                    'user_id'      => $employee->id,
                    'user_type_id' => null,
                    'title'        => $title,
                    'message'      => $message,
                    'route'        => "/{$userTypePrefix}/payroll-cut-offs",
                    'data'         => json_encode(['cut_off_id' => $report->id]),
                ]);
            }
        }
    }

}
