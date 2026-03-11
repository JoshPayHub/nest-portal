<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Undertime;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UndertimeFormController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $undertimes = Undertime::where('user_id', $user->id)
            ->with([
                'approvalStatuses.user',
                'approvalStatuses.status'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($item) {
                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                $totalMins = (int)$item->total_time;
                $h = floor($totalMins / 60);
                $m = $totalMins % 60;
                $displayTime = ($h > 0 ? "{$h}h " : "") . ($m > 0 || $h == 0 ? "{$m}m" : "");

                return [
                    'id' => $item->id,
                    'date_filed' => $item->created_at->format('M d, Y'),
                    'reason' => $item->reason,
                    'undertime_date' => Carbon::parse($item->undertime_date)->format('M d, Y'),
                    'from_date' => Carbon::parse($item->from_date)->format('h:i A'),
                    'to_date' => Carbon::parse($item->to_date)->format('h:i A'),
                    'total_time' => $displayTime,
                    'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                    'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
                ];
            });

        return Inertia::render('management/Employee/UndertimeFormList', [
            'undertimes' => $undertimes
        ]);
    }

    public function create()
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/UndertimeForm', [
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
            'undertime_date' => 'required|date',
            'from_time'      => 'required',
            'to_time'        => 'required',
            'total_time'     => 'required|integer|min:1',
            'reason'         => 'required|string|min:10',
        ], [
            'total_time.min' => 'End time must be after start time.',
        ]);

        Undertime::create([
            'user_id'        => Auth::id(),
            'undertime_date' => $validated['undertime_date'],
            'from_time'      => $validated['from_time'],
            'to_time'        => $validated['to_time'],
            'total_time'     => $validated['total_time'],
            'reason'         => $validated['reason'],
            'department_id'  => Auth::user()->department_id,
            'position_id'    => Auth::user()->position_id,
        ]);

        return redirect()->back()->with('message', 'Undertime submitted successfully!');
    }

    public function edit($id)
    {
        $user = Auth::user()->load(['department', 'position']);
        $undertime = Undertime::with(['approvalStatuses.status'])->findOrFail($id);

        if ($undertime->user_id !== $user->id) {
            return redirect()->route('employee.undertimeform.index')->with('error', 'Unauthorized access.');
        }

        $hasRejected = $undertime->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $undertime->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route('employee.undertimeform.index')->with('error', 'This request is approved and cannot be modified.');
        }

        // Fixed: Use undertime_date instead of date_required
        $undertime->undertime_date = Carbon::parse($undertime->undertime_date)->format('Y-m-d');

        return Inertia::render('management/Employee/UndertimeForm', [
            'report' => $undertime,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A'
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $undertime = Undertime::findOrFail($id);

        if ($undertime->user_id !== Auth::id()) {
            return redirect()->route('employee.undertimeform.index')->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'undertime_date' => 'required|date',
            'from_time'      => 'required',
            'to_time'        => 'required',
            'total_time'     => 'required|integer|min:1',
            'reason'         => 'required|string|min:10',
        ]);

        DB::transaction(function () use ($undertime, $validated) {
            $undertime->update($validated);
            // Assuming status 4 is "Pending"
            DB::table('undertime_statuses')
                ->where('undertime_id', $undertime->id)
                ->update(['status_id' => 4, 'updated_at' => now()]);
        });

        return redirect()->route('employee.undertimeform.index')->with('message', 'Undertime updated.');
    }
}
