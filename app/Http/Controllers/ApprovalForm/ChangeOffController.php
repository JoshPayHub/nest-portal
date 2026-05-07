<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\ChangeOff;
use App\Models\Notification;
use App\Models\User;
use App\Models\Department;
use App\Models\Position; // Added Position model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ChangeOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isHR = $user->user_type_id == 1;

        // 1. Fetch Active Departments and Positions (status_id 1 = active)
        $departments = Department::where('status_id', 1)->orderBy('name', 'asc')->get();
        $positions = Position::where('status_id', 1)->orderBy('name', 'asc')->get();

        // 2. Fetch employees for filter: HR sees all, Head sees department only
        $employeesQuery = User::query();
        if (!$isHR) {
            $employeesQuery->where('department_id', $user->department_id);
        }

        $employees = $employeesQuery->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name', 'asc')
            ->get();

        // 3. Build Change Off Query
        $requestsQuery = ChangeOff::join('users', 'change_offs.user_id', '=', 'users.id')
            ->with([
                'user.department', // Load department relationship
                'label.off',
                'label.originalDay',
                'label.newDay',
                'approvalStatuses.user.userType',
                'approvalStatuses.status'
            ])
            ->select('change_offs.*');

        // Logic: Head sees only their department. HR sees all (unless filtered).
        if (!$isHR) {
            $requestsQuery->where('users.department_id', $user->department_id);
        } else {
            // Apply Department Filter for HR
            if ($request->filled('department_id')) {
                $requestsQuery->where('users.department_id', $request->department_id);
            }
        }

        // Apply Employee Filter
        if ($request->filled('employee_id')) {
            $requestsQuery->where('change_offs.user_id', $request->employee_id);
        }

        $requests = $requestsQuery->orderBy('change_offs.created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($req) {
                // Find specific approval entries based on user type roles
                $leaderEntry = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'              => $req->id,
                    'employee_name'   => $req->user->first_name . ' ' . $req->user->last_name,
                    'department_name' => $req->user->department->name ?? 'N/A', // Added department_name
                    'date_filed'      => $req->created_at->format('M d, Y'),
                    'request_type'    => $req->label->off->name ?? 'N/A',
                    'original_date'   => $req->label ? Carbon::parse($req->label->original_date)->format('M d, Y') : 'N/A',
                    'original_day'    => $req->label->originalDay->name ?? 'N/A',
                    'original_time'   => $req->label->original_time ?? 'N/A',
                    'new_date'        => $req->label ? Carbon::parse($req->label->new_date)->format('M d, Y') : 'N/A',
                    'new_day'         => $req->label->newDay->name ?? 'N/A',
                    'new_time'        => $req->label->new_time ?? 'N/A',
                    'leader_status'   => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status'       => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/ApprovalForm/ChangeOffList', [
            'requests'        => $requests,
            'departments'     => $departments,
            'positions'       => $positions, // Added positions to props
            'employeeOptions' => $employees,
            'filters'         => $request->only(['employee_id', 'department_id']),
            'auth_user_type'  => $user->user_type_id
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['status_id' => 'required|in:7,8']);

        $user = $request->user();
        $changeOff = ChangeOff::findOrFail($id);

         DB::table('change_off_statuses')->updateOrInsert(
            ['change_off_id' => $changeOff->id, 'user_id' => $request->user()->id],
            ['status_id' => $request->status_id, 'updated_at' => now(), 'created_at' => now()]
        );

        $userTypeName = ($user->user_type_id == 1) ? 'HR' : 'Department Head';
        $statusName = ($request->status_id == 7) ? 'Approved' : 'Rejected';

        $title = "{$userTypeName} {$statusName} your Change Off Request";
        $message = "Your change off request has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($changeOff, $title, $message);

        return redirect()->back()->with('message', 'Change Off request processed.');
    }

    private function notifyUsers($changeOff, $title, $message)
    {
        $employee = User::find($changeOff->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';
            $notification = Notification::where('user_id', $employee->id)
                ->whereNull('user_type_id')
                ->where('data', 'LIKE', '%change_off_id%')
                ->where('data', 'LIKE', '%' . $changeOff->id . '%')
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
                    'route'        => "/{$userTypePrefix}/change-offs",
                    'data'         => json_encode(['change_off_id' => $changeOff->id]),
                ]);
            }
        }
    }
}
