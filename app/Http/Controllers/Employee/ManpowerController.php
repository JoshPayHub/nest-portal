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
    public function index()
    {
        $rawManpowers = Manpower::with(['statuses.user.userType', 'statuses.status'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $manpowers = $rawManpowers->map(function ($item) {
            $headEntry = $item->statuses->first(fn($s) => $s->user?->userType?->name === 'Head');
            $hrEntry = $item->statuses->first(fn($s) => $s->user?->userType?->name === 'HR');

            return [
                'id' => $item->id,
                'date_filed' => $item->created_at->format('M d, Y'),
                'position_type' => $item->position_type,
                'date_required' => Carbon::parse($item->date_required)->format('M d, Y'),
                'leader_status' => $headEntry ? $headEntry->status->name : 'Pending',
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

        return redirect()->route('employee.manpower.index')->with('message', 'Manpower request submitted!');
    }

    public function edit($id)
    {
        $manpower = Manpower::with('statuses.status')->findOrFail($id);

        // Authorization & Lock Check
        if ($manpower->user_id !== Auth::id()) abort(403);

        $isApproved = $manpower->statuses->contains(fn($s) => strtolower($s->status?->name) === 'approved');
        if ($isApproved) return redirect()->back()->with('error', 'Approved requests cannot be edited.');

        $user = Auth::user()->load(['department', 'position']);
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
        $manpower = Manpower::findOrFail($id);
        if ($manpower->user_id !== Auth::id()) abort(403);

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
