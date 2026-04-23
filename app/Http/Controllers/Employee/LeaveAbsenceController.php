<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveAbsence;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveAbsenceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $absences = LeaveAbsence::where('user_id', $user->id)
        ->with([
            'approvalStatuses.user',
            'approvalStatuses.status'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->through(function ($absence) { // Changed $req to $absence for clarity
            // Match user_type_id logic
            $leaderEntry = $absence->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
            $hrEntry     = $absence->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

            return [
                'id' => $absence->id,
                'date_filed' => $absence->created_at->format('M d, Y'),
                'type_absence' => $absence->type_absence,
                'date_absence' => Carbon::parse($absence->date_absence)->format('M d, Y'),
                'reason' => $absence->reason,
                'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
            ];
        });

        // Ensure this path matches your folder structure exactly
        return Inertia::render('management/Employee/LeaveAbsenceList', [
            'absences' => $absences
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/LeaveAbsence', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'isEditing' => false,
            'todayDate' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'type_absence' => 'required|string',
            'date_absence' => 'required|date',
            'reason'       => 'required|string|min:10',
        ], [
            'type_absence.required' => 'Please select the type of absence.',
            'date_absence.required' => 'Absence date is required.',
            'reason.required'       => 'Please provide a reason.',
            'reason.min'            => 'The reason must be at least 10 characters long.',
        ]);

        LeaveAbsence::create(array_merge($validated, [
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
        ]));

        return redirect()->back()->with('message', 'Absence request submitted successfully');
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user()->load(['department', 'position']);
        $absence = LeaveAbsence::with(['approvalStatuses.status'])->find($id);

        if (!$absence) {
            return redirect()->route('employee.leaveabsence.index')->with('error', 'Record not found.');
        }

        if ($absence->user_id !== $user->id) {
            return redirect()->route('employee.leaveabsence.index')->with('error', 'Unauthorized access.');
        }

        // Logic check: Approved check (Mirroring your Leave logic)
        $hasRejected = $absence->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $absence->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.leaveabsence.index')->with('error', 'This request is approved and cannot be modified.');
        }

        return Inertia::render('management/Employee/LeaveAbsence', [
            'report' => $absence,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'todayDate' => now()->toDateString(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $absence = LeaveAbsence::with('approvalStatuses.status')->find($id);

        if (!$absence || $absence->user_id !== Auth::id()) {
            return redirect()->route('employee.leaveabsence.index')->with('error', 'Unable to update.');
        }

        // Lock check
        $hasRejected = $absence->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $absence->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.leaveabsence.index')->with('error', 'Cannot update approved request.');
        }

        $validated = $request->validate([
            'type_absence' => 'required|string',
            'date_absence' => 'required|date',
            'reason'       => 'required|string|min:10',
        ]);

        DB::transaction(function () use ($absence, $validated) {
            $absence->update($validated);

            // Reset statuses to Pending (4) - Table name check: leave_absent_statuses
            DB::table('leave_absent_statuses')
                ->where('leave_absent_id', $absence->id)
                ->update([
                    'status_id' => 4,
                    'updated_at' => now()
                ]);
        });

        return redirect()->route('employee.leaveabsence.index')->with('message', 'Absence request updated.');
    }
}
