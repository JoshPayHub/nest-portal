<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        // Load leaves with relationships, specifically checking the user types in statuses
        $rawLeaves = Leave::with(['statuses.user.userType', 'statuses.status'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        $leaves = $rawLeaves->map(function ($leave) {
            // Find Head entry by checking the UserType name of the person who created the status
            $headEntry = $leave->statuses->first(fn($s) => $s->user?->userType?->name === 'Head');
            // Find HR entry
            $hrEntry = $leave->statuses->first(fn($s) => $s->user?->userType?->name === 'HR');

            return [
                'id' => $leave->id,
                'date_filed' => $leave->created_at->format('M d, Y'),
                'type_leave' => $leave->type_leave,
                'start_date' => Carbon::parse($leave->start_date)->format('M d, Y'),
                'end_date'   => Carbon::parse($leave->end_date)->format('M d, Y'),
                'total_days' => $leave->total_days,
                'pay_type'   => $leave->with_pay ? 'With Pay' : 'Without Pay',
                'leader_status' => $headEntry ? $headEntry->status->name : 'Pending',
                'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
            ];
        });

        return Inertia::render('management/Employee/LeaveList', [
            'leaves' => $leaves
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user()->load(['department', 'position']);

        return Inertia::render('management/Employee/Leave', [
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'isEditing' => false,
            'todayDate' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_leave' => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string',
            'with_pay'   => 'required|boolean',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        Leave::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'total_days' => $totalDays,
        ]));

        return redirect()->route('employee.leave.index');
    }

    public function edit(Request $request, Leave $leave)
    {
        // Use the same user loading logic as create for the sidebar/form info
        $user = $request->user()->load(['department', 'position']);

        // Load statuses to check if it's locked (Approved) or editable (Rejected)
        $leave->load('statuses.status');

        return Inertia::render('management/Employee/Leave', [
            'report' => $leave,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
        ]);
    }

    public function update(Request $request, Leave $leave)
    {
        // Security check: Don't allow updates if already approved and not rejected
        $hasApproved = $leave->statuses()->where('status_id', 2)->exists();
        $hasRejected = $leave->statuses()->where('status_id', 5)->exists();

        if ($hasApproved && !$hasRejected) {
            return back()->withErrors(['message' => 'Approved requests cannot be modified.']);
        }

        $validated = $request->validate([
            'type_leave' => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string',
            'with_pay'   => 'required|boolean',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        $leave->update(array_merge($validated, [
            'total_days' => $totalDays,
        ]));

        return redirect()->route('employee.leave.index');
    }
}
