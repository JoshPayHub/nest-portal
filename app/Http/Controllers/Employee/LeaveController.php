<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    private function getLeaveBalance($user)
    {
        $currentYear = now()->year;
        $totalLeaveCredits = $user->leave_pay;

        // ✅ FIXED: Only count leaves where BOTH HR and HEAD are approved (status_id = 7)
        $leaves = Leave::where('user_id', $user->id)
            ->where('with_pay', true)
            ->whereYear('start_date', $currentYear)
            ->with(['approvalStatuses.user'])
            ->get();

        $used = $leaves->filter(function ($leave) {

            $hasHRApproved = $leave->approvalStatuses->contains(function ($s) {
                return $s->status_id == 7 && $s->user?->user_type_id == 1; // HR
            });

            $hasHeadApproved = $leave->approvalStatuses->contains(function ($s) {
                return $s->status_id == 7 && $s->user?->user_type_id == 3; // HEAD
            });

            return $hasHRApproved && $hasHeadApproved;

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

        $leaves = Leave::where('user_id', $user->id)
            ->with(['approvalStatuses.user', 'approvalStatuses.status'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($leave) {

                $leaderEntry = $leave->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $leave->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $leave->id,
                    'date_filed' => $leave->created_at->format('M d, Y'),
                    'type_leave' => $leave->type_leave,
                    'start_date' => Carbon::parse($leave->start_date)->format('M d, Y'),
                    'end_date'   => Carbon::parse($leave->end_date)->format('M d, Y'),
                    'total_days' => $leave->total_days,
                    'reason' => $leave->reason,
                    'pay_type'   => $leave->with_pay ? 'With Pay' : 'Without Pay',
                    'leader_status' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status'     => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        $balance = $this->getLeaveBalance($user);

        return Inertia::render('management/Employee/LeaveList', [
            'leaves' => $leaves,
            'auth_user_type_id' => $user->user_type_id,
            'availableLeave' => $balance['balance'],
            'leaveTotal' => $balance['total'],
            'leaveUsed' => $balance['used'],
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->load(['department', 'position']);
        $balance = $this->getLeaveBalance($user);

        return Inertia::render('management/Employee/Leave', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'isEditing' => false,
            'todayDate' => now()->toDateString(),
            'availableLeave' => $balance['balance'],
            'leaveTotal' => $balance['total'],
            'leaveUsed' => $balance['used'],
            'auth_user_type_id' => $user->user_type_id,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $balance = $this->getLeaveBalance($user);

        $validated = $request->validate([
            'type_leave' => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|min:10',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        if ($request->type_leave === 'Leave with Pay') {
            if ($totalDays > $balance['balance']) {
                return back()->withErrors([
                    'end_date' => 'Insufficient leave balance. Available: ' . $balance['balance']
                ]);
            }
        }

        Leave::create([
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
            'type_leave' => $validated['type_leave'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'total_days' => $totalDays,
            'with_pay' => $request->type_leave === 'Leave with Pay'
        ]);

        return redirect()->back()->with('message', 'Leave request submitted successfully!');
    }
}
