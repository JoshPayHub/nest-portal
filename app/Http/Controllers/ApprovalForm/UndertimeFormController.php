<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Undertime;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class UndertimeFormController extends Controller
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

        // 3. Build Undertime Query
        $reportsQuery = Undertime::with([
            'user.department',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

        // Logic: Head sees only their department. HR sees all (unless filtered).
        if (!$isHR) {
            $reportsQuery->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } else {
            // Apply Department Filter for HR
            if ($request->filled('department_id')) {
                $reportsQuery->whereHas('user', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
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
        $undertimes = $reportsQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                // IDs 3 (Leader/Head) and 1 (HR)
                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                // Time calculation logic
                $totalMins = (int)$item->total_time;
                $h = floor($totalMins / 60);
                $m = $totalMins % 60;
                $displayTime = ($h > 0 ? "{$h}h " : "") . ($m > 0 || $h == 0 ? "{$m}m" : "");

                return [
                    'id'                => $item->id,
                    'employee_name'     => $item->user->first_name . ' ' . $item->user->last_name,
                    'department_name'   => $item->user->department->name ?? 'N/A',
                    'profile_photo'     => $item->user->profile_photo,
                    'date_filed'        => $item->created_at->format('M d, Y'),
                    'reason'            => $item->reason,
                    'undertime_date'    => Carbon::parse($item->undertime_date)->format('M d, Y'),
                    'from_time'         => Carbon::parse($item->from_time)->format('h:i A'),
                    'to_time'           => Carbon::parse($item->to_time)->format('h:i A'),
                    'total_time'        => $displayTime,
                    'leader_status'     => $leaderEntry?->status?->name ?? 'Pending',
                    'leader_status_id'  => $leaderEntry?->status_id,
                    'hr_status'         => $hrEntry?->status?->name ?? 'Pending',
                    'hr_status_id'      => $hrEntry?->status_id,
                ];
            });

        return Inertia::render('management/ApprovalForm/UndertimeFormList', [
            'undertimes'      => $undertimes,
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

        $undertime = Undertime::findOrFail($id);

        DB::table('undertime_statuses')->updateOrInsert(
            [
                'undertime_id' => $undertime->id,
                'user_id'      => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $userTypeName = ($user->user_type_id == 1) ? 'HR' : 'Department Head';
        $statusName = ($request->status_id == 7) ? 'Approved' : 'Rejected';

        $title = "{$userTypeName} {$statusName} your Undertime Request";
        $message = "Your undertime request has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($undertime, $title, $message);

         return redirect()->back()->with('message', 'Undertime request processed.');
    }

    private function notifyUsers($undertime, $title, $message)
    {
        $employee = User::find($undertime->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';
            $notification = Notification::where('user_id', $employee->id)
                ->whereNull('user_type_id')
                ->where('data', 'LIKE', '%undertime_id%')
                ->where('data', 'LIKE', '%' . $undertime->id . '%')
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
                    'route'        => "/{$userTypePrefix}/undertime-forms",
                    'data'         => json_encode(['undertime_id' => $undertime->id]),
                ]);
            }
        }
    }
}
