<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Notification;
use App\Models\User;
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

        // ✅ Only count leaves where BOTH HR and HEAD are approved (status_id = 7)
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
                    'pay_type'   => $leave->with_pay ? 'Leave with Pay' : 'Leave without Pay',
                    'leader_status' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status'     => $hrEntry?->status?->name ?? 'Pending',
                    'can_edit'      => !$leaderEntry && !$hrEntry,
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
                return back()->withErrors(['end_date' => 'Insufficient leave balance. Available: ' . $balance['balance']]);
            }
        }

        $leave = Leave::create([
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

        $this->notifyUsers(
            $request,
            $leave,
            "New Leave Request",
            $user->first_name . " has submitted a new Leave request."
        );

        $routeMap = [
            2 => 'employee.leaves.create',
            3 => 'head.leaves.create',
        ];

        return redirect()->route($routeMap[$user->user_type_id])->with('message', 'Leave request submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user()->load(['department', 'position']);
        // Fix: Use the relationship instead of DB::table to avoid "table not found" error
        $leave = Leave::with(['approvalStatuses'])->where('user_id', $user->id)->findOrFail($id);

        if ($leave->approvalStatuses->count() > 0) {
            return redirect()->back()->with('error', 'Cannot edit a leave request that has already been processed.');
        }

        $balance = $this->getLeaveBalance($user);

        return Inertia::render('management/Employee/Leave', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'report' => $leave, // Changed from 'leave' to 'report' to match your Manpower/Frontend logic
            'isEditing' => true,
            'todayDate' => now()->toDateString(),
            'availableLeave' => $balance['balance'],
            'leaveTotal' => $balance['total'],
            'leaveUsed' => $balance['used'],
            'auth_user_type_id' => $user->user_type_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $leave = Leave::where('user_id', $user->id)->findOrFail($id);

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
            $balance = $this->getLeaveBalance($user);
            if ($totalDays > $balance['balance']) {
                return back()->withErrors(['end_date' => 'Insufficient leave balance.']);
            }
        }

        DB::transaction(function () use ($request, $leave, $validated, $totalDays, $user) {
            $leave->update([
                'type_leave' => $validated['type_leave'],
                'start_date' => $validated['start_date'],
                'end_date'   => $validated['end_date'],
                'reason'     => $validated['reason'],
                'total_days' => $totalDays,
                'with_pay'   => $request->type_leave === 'Leave with Pay'
            ]);

            $this->notifyUsers(
                $request,
                $leave,
                "Leave Updated",
                $user->first_name . " has updated their Leave request and is awaiting re-approval."
            );
        });

        $routeMap = [
            2 => 'employee.leaves.index',
            3 => 'head.leaves.index',
        ];

        return redirect()->route($routeMap[$user->user_type_id])->with('message', 'Leave request updated successfully!');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $types = [
            3 => '/head/leave',
            1 => '/hr/leave'
        ];

        foreach ($types as $typeId => $route) {
            $notification = Notification::where('user_id', $employeeId)
                ->where('user_type_id', $typeId)
                ->where('data', 'LIKE', '%leave_id%')
                ->where('data', 'LIKE', '%' . $report->id . '%')
                ->first();

            if ($notification) {
                $notification->update([
                    'title'   => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'read_at'    => null,
                    'updated_at' => now(),
                ]);
            } else {
                Notification::create([
                    'user_id'      => $employeeId,
                    'user_type_id' => $typeId,
                    'title'        => $title,
                    'message'      => $message,
                    'route'        => $route,
                    'data'         => json_encode(['leave_id' => $report->id]),
                ]);
            }
        }
    }
}
