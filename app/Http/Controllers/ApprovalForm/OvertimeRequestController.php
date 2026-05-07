<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Overtime;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isHR = $user->user_type_id == 1;
        $allStatuses = Status::all();

        // 1. Fetch Active Departments and Positions for filters
        $departments = Department::where('status_id', 1)->orderBy('name', 'asc')->get();
        $positions = Position::where('status_id', 1)->orderBy('name', 'asc')->get();

        // 2. Fetch Employees for Filter: HR sees all, Head sees department only
        $employeesQuery = User::query()->select('id', 'first_name', 'last_name', 'username', 'department_id');

        if (!$isHR) {
            $employeesQuery->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->orderBy('first_name', 'asc')->get();


        // 3. Build Overtime Query
        $reportsQuery = Overtime::with([
            'user.department',
            'activities',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

        // Access Logic: Head sees department only. HR sees all (unless filtered).
        if (!$isHR) {
            $reportsQuery->where('department_id', $user->department_id);
        } else {
            if ($request->filled('department_id')) {
                $reportsQuery->where('department_id', $request->department_id);
            }
        }

         // Search Filter
         if ($request->filled('search')) {
            $search = $request->search;
            $reportsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Apply Employee Filter
        if ($request->filled('employee_id')) {
            $reportsQuery->where('user_id', $request->employee_id);
        }

        // 4. Paginate and Map
        $reports = $reportsQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($report) {
                // Identify roles: Leader = 3, HR = 1
                $leaderEntry = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                $firstActivity = $report->activities->first();

                return [
                    'id'                 => $report->id,
                    'user_id'            => $report->user_id,
                    'employee_name'      => $report->user->first_name . ' ' . $report->user->last_name,
                    'department_name'    => $report->user->department->name ?? 'N/A',
                    'date_filed'         => $report->created_at?->format('M d, Y') ?? '',
                    'cut_off_date'       => $report->cut_off_date
                                            ? Carbon::parse($report->cut_off_date)->format('M d, Y')
                                            : '',
                    'overtime_date'      => $firstActivity
                                            ? Carbon::parse($firstActivity->overtime_date)->format('M d, Y')
                                            : 'N/A',
                    'start_time'         => $firstActivity ? Carbon::parse($firstActivity->time_start)->format('h:i A') : '',
                    'end_time'           => $firstActivity ? Carbon::parse($firstActivity->time_end)->format('h:i A') : '',
                    'total_hours'        => $report->activities->sum('additional_hours_worked'),
                    'reason'             => $firstActivity?->description ?? 'No reason provided',
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                    'activities_count'   => $report->activities->count(),
                    'activities'         => $report->activities->map(function ($item) {
                        return [
                            'date'        => Carbon::parse($item->overtime_date)->format('M d, Y'),
                            'description' => $item->description,
                            'time_start'  => Carbon::parse($item->time_start)->format('h:i A'),
                            'time_end'    => Carbon::parse($item->time_end)->format('h:i A'),
                            'hours'       => $item->additional_hours_worked,
                        ];
                    }),
                ];
            });

        return Inertia::render('management/ApprovalForm/OvertimeList', [
            'reports'         => $reports,
            'departments'     => $departments,
            'positions'       => $positions,
            'statuses'        => $allStatuses,
            'employeeOptions' => $employees,
            'filters'         => $request->only(['employee_id', 'department_id']),
            'auth_user_type'  => $user->user_type_id
        ]);
    }

    public function approve(Request $request, $id)
    {
         $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $user = $request->user();

        $overtime = Overtime::findOrFail($id);

        DB::table('overtime_statuses')->updateOrInsert(
            [
                'overtime_id' => $overtime->id,
                'user_id'     => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $userTypeName = ($user->user_type_id == 1) ? 'HR' : 'Department Head';
        $statusName = ($request->status_id == 7) ? 'Approved' : 'Rejected';

        $title = "{$userTypeName} {$statusName} your Overtime Request";
        $message = "Your overtime request has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($overtime, $title, $message);

        return redirect()->back()->with('message', 'Action processed successfully.');
    }

    private function notifyUsers($overtime, $title, $message)
    {
        $employee = User::find($overtime->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';
            $notification = Notification::where('user_id', $employee->id)
                ->whereNull('user_type_id')
                ->where('data', 'LIKE', '%overtime_id%')
                ->where('data', 'LIKE', '%' . $overtime->id . '%')
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
                    'route'        => "/{$userTypePrefix}/overtime-requests",
                    'data'         => json_encode(['overtime_id' => $overtime->id]),
                ]);
            }
        }
    }
}
