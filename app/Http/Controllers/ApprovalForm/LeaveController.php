<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    /**
     * 🔥 CHECK LEAVE BALANCE BEFORE APPROVAL
     */
    private function getLeaveBalance($user)
    {
        $currentYear = now()->year;
        $totalLeaveCredits = $user->leave_credits ?? 7;

        $leaves = Leave::where('user_id', $user->id)
            ->where('with_pay', true)
            ->whereYear('start_date', $currentYear)
            ->with(['approvalStatuses.user'])
            ->get();

        $used = $leaves->filter(function ($leave) {

            $hasHR = $leave->approvalStatuses->contains(fn ($s) =>
                $s->status_id == 7 && $s->user?->user_type_id == 1
            );

            $hasHead = $leave->approvalStatuses->contains(fn ($s) =>
                $s->status_id == 7 && $s->user?->user_type_id == 3
            );

            return $hasHR && $hasHead;

        })->sum('total_days');

        return [
            'total' => $totalLeaveCredits,
            'used' => $used,
            'balance' => max($totalLeaveCredits - $used, 0),
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isHR = $user->user_type_id == 1;
        $allStatuses = Status::all();

        $departments = Department::where('status_id', 1)->orderBy('name', 'asc')->get();
        $positions = Position::where('status_id', 1)->orderBy('name', 'asc')->get();

        $employeesQuery = User::query()->select('id', 'first_name', 'last_name', 'username', 'department_id');

        if (!$isHR) {
            $employeesQuery->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->orderBy('first_name', 'asc')->get();

        $leavesQuery = Leave::with([
            'user.department',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

        if (!$isHR) {
            $leavesQuery->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } else {
            if ($request->filled('department_id')) {
                $leavesQuery->whereHas('user', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $leavesQuery->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $leavesQuery->where('user_id', $request->employee_id);
        }

        $leaves = $leavesQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($leave) {

                $leaderEntry = $leave->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $leave->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'              => $leave->id,
                    'reference_no'    => $leave->reference_no,
                    'employee_name'   => $leave->user->first_name . ' ' . $leave->user->last_name,
                    'department_name' => $leave->user->department->name ?? 'N/A',
                    'date_filed'      => $leave->created_at->format('M d, Y'),
                    'type_leave'      => $leave->type_leave,
                    'pay_type'        => $leave->with_pay ? 'With Pay' : 'Without Pay',
                    'start_date'      => Carbon::parse($leave->start_date)->format('M d, Y'),
                    'end_date'        => Carbon::parse($leave->end_date)->format('M d, Y'),
                    'total_days'      => $leave->total_days,
                    'reason'          => $leave->reason,
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                    'status'             => $leaderEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/ApprovalForm/LeaveList', [
            'items'           => $leaves,
            'departments'     => $departments,
            'positions'       => $positions,
            'employeeOptions' => $employees,
            'statuses'        => $allStatuses,
            'filters'         => $request->only(['employee_id', 'department_id', 'search']),
            'auth_user_type'  => $user->user_type_id
        ]);
    }

    /**
     * ✅ APPROVAL WITH LEAVE BALANCE VALIDATION
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $leave = Leave::with('user')->findOrFail($id);

        // 🔥 ONLY CHECK IF APPROVING (NOT REJECTING)
        if ((int)$request->status_id === 7) {

            $balance = $this->getLeaveBalance($leave->user);

            if ($balance['balance'] <= 0) {
                return back()->withErrors([
                    'status' => 'Cannot approve. Employee has no remaining leave balance.'
                ]);
            }

            // Optional strict validation per leave
            if ($leave->total_days > $balance['balance']) {
                return back()->withErrors([
                    'status' => 'Leave exceeds remaining balance (' . $balance['balance'] . ').'
                ]);
            }
        }

        DB::table('leave_statuses')->updateOrInsert(
            [
                'leave_id' => $leave->id,
                'user_id'  => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('message', 'Leave action processed successfully.');
    }
}
