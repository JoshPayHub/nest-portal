<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ChangeOff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ChangeOffController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    $requests = ChangeOff::where('user_id', $user->id)
        ->with([
            'label.off',
            'label.originalDay',
            'label.newDay',
            'approvalStatuses.user',
            'approvalStatuses.status'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->through(function ($req) {
            // We use the collection from 'approvalStatuses' loaded above
            // This is faster than calling $req->leaderStatus() which runs a new query
            $leaderEntry = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
            $hrEntry     = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

            return [
                'id'            => $req->id,
                'date_filed'    => $req->created_at->format('M d, Y'),
                'request_type'  => $req->label->off->name ?? 'N/A',
                'original_date' => $req->label ? Carbon::parse($req->label->original_date)->format('M d, Y') : 'N/A',
                'original_day'  => $req->label->originalDay->name ?? 'N/A',
                'original_time' => $req->label->original_time ?? 'N/A',
                'new_date'      => $req->label ? Carbon::parse($req->label->new_date)->format('M d, Y') : 'N/A',
                'new_day'       => $req->label->newDay->name ?? 'N/A',
                'new_time'      => $req->label->new_time ?? 'N/A',
                'leader_status' => $leaderEntry->status->name ?? 'Pending',
                'hr_status'     => $hrEntry->status->name ?? 'Pending',
            ];
        });

    return Inertia::render('management/Employee/ChangeOffList', [
        'requests' => $requests,
    ]);
}

    public function create(Request $request)
    {
        $user = $request->user()->load(['department', 'position']);
        $days = DB::table('offs')->get();

        return Inertia::render('management/Employee/ChangeOff', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'days' => $days,
            'todayDate' => now()->format('Y-m-d'),
            'isEditing' => false
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|exists:offs,id',
            'original_date' => 'required|date',
            'original_off_id' => 'required_if:request_type,2|nullable|exists:offs,id',
            'original_time' => 'required_if:request_type,1|nullable',
            'new_date' => 'required|date|after_or_equal:today',
            'new_off_id' => 'required_if:request_type,2|nullable|exists:offs,id',
            'new_time' => 'required_if:request_type,1|nullable',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $user = $request->user();

            $changeOff = ChangeOff::create([
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'position_id' => $user->position_id,
            ]);

            $changeOff->label()->create([
                'off_id'          => $validated['request_type'],
                'original_date'   => $validated['original_date'],
                'new_date'        => $validated['new_date'],
                'original_day_id' => $validated['original_off_id'],
                'new_day_id'      => $validated['new_off_id'],
                'original_time'   => $validated['request_type'] == 2 ? null : $validated['original_time'],
                'new_time'        => $validated['request_type'] == 2 ? null : $validated['new_time'],
            ]);
        });

        return redirect()->back()->with('message', 'Change Off submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $user = $request->user()->load(['department', 'position']);
        $days = DB::table('offs')->get();

        // Updated relation name to 'approvalStatuses'
        $report = ChangeOff::with(['label', 'approvalStatuses.status'])->find($id);

        if (!$report) {
            return redirect()->route('employee.changeoff.index')->with('error', 'Request record not found.');
        }

        if ($report->user_id !== $user->id) {
            return redirect()->route('employee.changeoff.index')->with('error', 'Unauthorized access.');
        }

        // Updated check to use 'approvalStatuses'
        $hasRejected = $report->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $report->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.changeoff.index')->with('error', 'This request is approved and cannot be modified.');
        }

        return Inertia::render('management/Employee/ChangeOff', [
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'days' => $days,
            'report' => $report,
            'todayDate' => now()->format('Y-m-d'),
            'isEditing' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        // Updated relation name to 'approvalStatuses'
        $changeOff = ChangeOff::with('approvalStatuses.status')->find($id);

        if (!$changeOff || $changeOff->user_id !== $request->user()->id) {
            return redirect()->route('employee.changeoff.index')->with('error', 'Unable to update request.');
        }

        // Updated check to use 'approvalStatuses'
        $hasRejected = $changeOff->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $changeOff->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.changeoff.index')->with('error', 'Cannot update an approved request.');
        }

        $validated = $request->validate([
            'request_type' => 'required|exists:offs,id',
            'original_date' => 'required|date',
            'original_off_id' => 'required_if:request_type,2|nullable|exists:offs,id',
            'original_time' => 'required_if:request_type,1|nullable',
            'new_date' => 'required|date',
            'new_off_id' => 'required_if:request_type,2|nullable|exists:offs,id',
            'new_time' => 'required_if:request_type,1|nullable',
        ]);

        DB::transaction(function () use ($changeOff, $validated) {
            $changeOff->label()->update([
                'off_id'          => $validated['request_type'],
                'original_date'   => $validated['original_date'],
                'new_date'        => $validated['new_date'],
                'original_day_id' => $validated['original_off_id'],
                'new_day_id'      => $validated['new_off_id'],
                'original_time'   => $validated['request_type'] == 2 ? null : $validated['original_time'],
                'new_time'        => $validated['request_type'] == 2 ? null : $validated['new_time'],
            ]);

            DB::table('change_off_statuses')
                ->where('change_off_id', $changeOff->id)
                ->update([
                    'status_id' => 4, // Pending
                    'updated_at' => now()
                ]);
        });

        return redirect()->route('employee.changeoff.index')->with('message', 'Request updated and resubmitted.');
    }
}
