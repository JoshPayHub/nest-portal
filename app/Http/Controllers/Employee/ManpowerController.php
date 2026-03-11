<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Manpower;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManpowerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $manpowers = Manpower::where('user_id', $user->id)
            ->with([
                'approvalStatuses.user',
                'approvalStatuses.status'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($item) {
                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $item->id,
                    'date_filed' => $item->created_at->format('M d, Y'),
                    'position_type' => $item->position_type,
                    'report_to' => $item->report_to,
                    'job_description' => $item->job_description,
                    'justification' => $item->justification,

                    'status_type' => $item->status_type,
                    'payment_type' => $item->payment_type,

                    'date_required' => Carbon::parse($item->date_required)->format('M d, Y'),
                    'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                    'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
                ];
            });

        return Inertia::render('management/Employee/ManpowerList', [
            'manpowers' => $manpowers
        ]);
    }

    public function create()
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/Manpower', [
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'isEditing' => false
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_to' => 'required|string',
            'date_required' => 'required|date|after:today',
            'position_type' => 'required|string',
            'replacement_for' => 'nullable|string',
            'job_description' => 'required|string|min:20',
            'justification' => 'required|string|min:20',
            'status_type' => 'required|string',
            'payment_type' => 'required|string',
        ]);

        Manpower::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'position_id' => Auth::user()->position_id,
        ]));

        return redirect()->back()->with('message', 'Manpower request submitted successfully!');
    }

    public function edit($id)
    {
        $user = Auth::user()->load(['department', 'position']);
        $manpower = Manpower::with(['approvalStatuses.status'])->findOrFail($id);

        if ($manpower->user_id !== $user->id) {
            return redirect()->route('employee.manpower.index')->with('error', 'Unauthorized access.');
        }

        // Logic to check if locked
        $hasRejected = $manpower->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $manpower->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.manpower.index')->with('error', 'This request is approved and cannot be modified.');
        }

        // Format date for the HTML input
        $manpower->date_required = Carbon::parse($manpower->date_required)->format('Y-m-d');

        return Inertia::render('management/Employee/Manpower', [
            'report' => $manpower,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $manpower = Manpower::with('approvalStatuses.status')->findOrFail($id);

        if ($manpower->user_id !== Auth::id()) {
            return redirect()->route('employee.manpower.index')->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'report_to' => 'required|string',
            'date_required' => 'required|date',
            'position_type' => 'required|string',
            'replacement_for' => 'nullable|string',
            'job_description' => 'required|string|min:20',
            'justification' => 'required|string|min:20',
            'status_type' => 'required|string',
            'payment_type' => 'required|string',
        ]);

        DB::transaction(function () use ($manpower, $validated) {
            $manpower->update($validated);

            // Reset statuses to Pending (Assumed ID 4)
            DB::table('manpower_statuses')->where('manpower_id', $manpower->id)->update([
                'status_id' => 4,
                'updated_at' => now()
            ]);
        });

        return redirect()->route('employee.manpower.index')->with('message', 'Manpower request updated.');
    }
}
