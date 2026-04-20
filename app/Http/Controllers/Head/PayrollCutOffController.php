<?php

namespace App\Http\Controllers\Head;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\ChangeOff;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\OvertimeList;
use App\Models\PayrollCutOff;
use App\Models\Status;
use App\Models\Undertime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use Maatwebsite\Excel\Facades\Excel;

class PayrollCutOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cutoffs = PayrollCutOff::join('statuses', 'payroll_cut_offs.status_id', '=', 'statuses.id')
            ->select('payroll_cut_offs.*', 'statuses.name as status_name')
            ->where('payroll_cut_offs.status_id', 1)
            ->withCount(['attendanceEmployees as attendances_count' => function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('payroll_cut_offs.name', 'like', "%{$search}%");
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

        // Filter employees in the head's department
        $employees = User::where('department_id', $user->department_id)->get();

        $reports = AttendanceEmployee::where('payroll_cut_off_id', $id)
            ->join('users', 'attendance_employees.user_id', '=', 'users.id')
            ->where('attendance_employees.department_id', $user->department_id)
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
                $query->where(function($q) use ($search) {
                    $q->where('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%");
                });
            })
            ->when($request->employee_id, fn($q, $empId) => $q->where('attendance_employees.user_id', $empId))
            ->when($request->status, fn($q, $statusId) => $q->where('attendance_employees.head_status_id', $statusId))
            ->orderBy('attendance_employees.created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) use ($cutoff) {

                // Your specific logic for finding Approval Entries
                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'employee_name' => $item->employee_name,
                    'report_date' => $item->created_at->format('Y-m-d H:i:s'),
                    'head_status_id' => $item->head_status_id,
                    'name' => $cutoff->name,
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                    'user' => $item->user,
                    'attendances' => $item->attendances->filter(fn($log) =>
                        $log->attendance_date >= $cutoff->from_cutoff_date &&
                        $log->attendance_date <= $cutoff->to_cutoff_date
                    )->values()
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
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $report = AttendanceEmployee::findOrFail($id);

        // Update or Create the approval status for this Head/Head
        DB::table('attendance_statuses')->updateOrInsert(
            [
                'attendance_employee_id' => $report->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back();
    }
}
