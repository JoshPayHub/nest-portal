<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\AccomplishReport;
use App\Models\Notification;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AccomplishmentReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isHR = $user->user_type_id == 1;

        $departments = Department::where('status_id', 1)->orderBy('name', 'asc')->get();
        $positions = Position::where('status_id', 1)->orderBy('name', 'asc')->get();

        $employeesQuery = User::query()->select('id', 'first_name', 'last_name', 'username', 'department_id');

        if (!$isHR) {
            $employeesQuery->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->orderBy('first_name', 'asc')->get();

        $reportsQuery = AccomplishReport::with([
            'user.department',
            'activities.status',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

        if (!$isHR) {
            $reportsQuery->where('department_id', $user->department_id);
        } else {
            if ($request->filled('department_id')) {
                $reportsQuery->where('department_id', $request->department_id);
            }
        }

        if ($request->filled('employee_id')) {
            $reportsQuery->where('user_id', $request->employee_id);
        }

        $reports = $reportsQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($report) {
                $leaderEntry = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $report->id,
                    'user_id' => $report->user_id,
                    'employee_name' => $report->user->first_name . ' ' . $report->user->last_name,
                    'profile_photo' => $report->user->profile_photo,
                    'department_name' => $report->user->department->name ?? 'N/A',
                    'report_date' => $report->created_at->format('M d, Y'),
                    'period_from' => $report->from_date ? Carbon::parse($report->from_date)->format('M d, Y') : '',
                    'period_to'   => $report->to_date ? Carbon::parse($report->to_date)->format('M d, Y') : '',
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                    'activities_count' => $report->activities->count(),
                    'activities' => $report->activities->map(function($act) {
                        return [
                            'date' => $act->activity_date ? Carbon::parse($act->activity_date)->format('M d, Y') : '',
                            'activity' => $act->activity,
                            'status_name' => $act->status?->name ?? 'N/A'
                        ];
                    }),
                ];
            });

        return Inertia::render('management/ApprovalForm/AccomplishmentListReport', [
            'reports' => $reports,
            'employeeOptions' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'filters' => $request->only(['employee_id', 'department_id']),
            'auth_user_type' => $user->user_type_id
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $user = $request->user();
        $report = AccomplishReport::findOrFail($id);

        DB::table('accomplish_report_statuses')->updateOrInsert(
            [
                'accomplish_report_id' => $report->id,
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

        $title = "{$userTypeName} {$statusName} your Accomplishment Report";
        $message = "Your accomplishment report has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($report, $title, $message);

        return redirect()->back()->with('message', 'Action processed successfully.');
    }

    private function notifyUsers($report, $title, $message)
    {
        $employee = User::find($report->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';
            $notification = Notification::where('user_id', $employee->id)
                ->whereNull('user_type_id')
                ->where('data', 'LIKE', '%report_id%')
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
                    'route'        => "/{$userTypePrefix}/accomplishment-reports",
                    'data'         => json_encode(['report_id' => $report->id]),
                ]);
            }
        }
    }
}
