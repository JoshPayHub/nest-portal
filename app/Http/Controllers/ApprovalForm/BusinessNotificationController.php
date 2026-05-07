<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\BusinessNotification;
use App\Models\Notification;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isHR = $user->user_type_id == 1;

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

        // 3. Build Business Notification Query
        $query = BusinessNotification::with([
            'user.department',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

        // Access Logic: Head sees only their department. HR sees all (unless filtered).
        if (!$isHR) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } else {
            // Apply Department Filter for HR
            if ($request->filled('department_id')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        // Apply Search Filter (by name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Apply Employee Filter
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        $notifications = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                // Find specific approval entries (Leader = ID 3, HR = ID 1)
                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'                 => $item->id,
                    'employee_name'      => $item->user->first_name . ' ' . $item->user->last_name,
                    'department_name'    => $item->user->department->name ?? 'N/A',
                    'date_filed'         => $item->created_at->format('M d, Y'),
                    'exact_date'         => Carbon::parse($item->exact_date)->format('M d, Y'),
                    'purposes'           => $item->purposes,
                    'reason'             => $item->reason,
                    'location'           => $item->location,
                    'business_time'      => Carbon::parse($item->business_time)->format('h:i A'),
                    'returned_time'      => Carbon::parse($item->returned_time)->format('h:i A'),
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/ApprovalForm/BusinessNotificationList', [
            'items'           => $notifications,
            'departments'     => $departments,
            'positions'       => $positions,
            'employeeOptions' => $employees,
            'filters'         => $request->only(['search', 'employee_id', 'department_id']),
            'auth_user_type'  => $user->user_type_id
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['status_id' => 'required|in:7,8']);

        $user = $request->user();
        $Businessnotification = BusinessNotification::findOrFail($id);

        DB::table('business_notification_statuses')->updateOrInsert(
            [
                'business_notification_id' => $Businessnotification->id,
                'user_id'                  => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $userTypeName = ($user->user_type_id == 1) ? 'HR' : 'Department Head';
        $statusName = ($request->status_id == 7) ? 'Approved' : 'Rejected';

        $title = "{$userTypeName} {$statusName} your Business Notification Request";
        $message = "Your business notification request has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($Businessnotification, $title, $message);

        return redirect()->back()->with('message', 'Business notification request processed.');
    }

    private function notifyUsers($Businessnotification, $title, $message)
    {
        $employee = User::find($Businessnotification->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';
            $notification = Notification::where('user_id', $employee->id)
                ->whereNull('user_type_id')
                ->where('data', 'LIKE', '%business_notification_id%')
                ->where('data', 'LIKE', '%' . $Businessnotification->id . '%')
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
                    'route'        => "/{$userTypePrefix}/business-notifications",
                    'data'         => json_encode(['business_notification_id' => $Businessnotification->id]),
                ]);
            }
        }
    }
}
