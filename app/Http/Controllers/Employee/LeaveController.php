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
    public function index(Request $request)
    {
    $user = $request->user();

    $leaves = Leave::where('user_id', $user->id)
        ->with([
            'approvalStatuses.user',
            'approvalStatuses.status'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->through(function ($leave) { // Changed $req to $leave for clarity
            // Match user_type_id logic
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
                'pay_type'   => ($leave->type_leave === 'Leave with Pay') ? 'With Pay' : 'Without Pay',
                'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
            ];
        });

        return Inertia::render('management/Employee/LeaveList', [
            'leaves' => $leaves
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/Leave', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'isEditing' => false,
            'todayDate' => now()->toDateString(),
            'availableLeave' => 7,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'type_leave' => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|min:10',
        ], [
            'type_leave.required' => 'Please select the type of leave.',
            'start_date.required' => 'Start date is required.',
            'end_date.required'   => 'End date is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'reason.required'     => 'Please provide a reason for your leave.',
            'reason.min'          => 'The reason must be at least 10 characters long.',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        if ($request->type_leave === 'Leave with Pay' && $totalDays > 7) {
            return back()->withErrors(['end_date' => 'Requested days exceed available balance (7 days max).']);
        }

        Leave::create(array_merge($validated, [
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
            'total_days' => $totalDays,
            'with_pay' => $request->type_leave === 'Leave with Pay'
        ]));

        return redirect()->back()->with('message', 'Leave request submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user()->load(['department', 'position']);

        // 1. Fetch record or handle missing
        $leave = Leave::with(['approvalStatuses.status'])->find($id);

        if (!$leave) {
            return redirect()->route('employee.leave.index')->with('error', 'Leave record not found.');
        }

        // 2. Ownership Check
        if ($leave->user_id !== $user->id) {
            return redirect()->route('employee.leave.index')->with('error', 'Unauthorized access.');
        }

        // 3. Status Check Logic (Mirroring ChangeOff)
        $hasRejected = $leave->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $leave->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.leave.index')->with('error', 'This request is approved and cannot be modified.');
        }

        return Inertia::render('management/Employee/Leave', [
            'report' => $leave,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'availableLeave' => 7,
            'todayDate' => now()->toDateString(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $leave = Leave::with('approvalStatuses.status')->find($id);

        if (!$leave || $leave->user_id !== Auth::id()) {
            return redirect()->route('employee.leave.index')->with('error', 'Unable to update request.');
        }

        // Lock check
        $hasRejected = $leave->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $leave->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.leave.index')->with('error', 'Cannot update an approved request.');
        }

        $validated = $request->validate([
            'type_leave' => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|min:10',
        ], [
            'type_leave.required' => 'Please select the type of leave.',
            'start_date.required' => 'Start date is required.',
            'end_date.required'   => 'End date is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'reason.required'     => 'Please provide a reason for your leave.',
            'reason.min'          => 'The reason must be at least 10 characters long.',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        DB::transaction(function () use ($leave, $validated, $totalDays) {
            $leave->update(array_merge($validated, [
                'total_days' => $totalDays,
                'with_pay' => $validated['type_leave'] === 'Leave with Pay'
            ]));

            // Reset statuses to Pending (4)
            DB::table('leave_statuses')
                ->where('leave_id', $leave->id)
                ->update([
                    'status_id' => 4,
                    'updated_at' => now()
                ]);
        });

        return redirect()->route('employee.leave.index')->with('message', 'Leave request updated and resubmitted.');
    }
}
