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
    public function index()
    {
        $raw = Undertime::with(['statuses.user.userType', 'statuses.status'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $undertimes = $raw->map(function ($item) {
            $head = $item->statuses->first(fn($s) => $s->user?->userType?->name === 'Head');
            $hr = $item->statuses->first(fn($s) => $s->user?->userType?->name === 'HR');

            $totalMins = (int)$item->total_time;
            $h = floor($totalMins / 60);
            $m = $totalMins % 60;
            $displayTime = ($h > 0 ? "{$h}h " : "") . ($m > 0 || $h == 0 ? "{$m}m" : "");

            return [
                'id' => $item->id,
                'date_filed' => $item->created_at->format('M d, Y'),
                'undertime_date' => Carbon::parse($item->undertime_date)->format('M d, Y'),
                'total_time' => $displayTime,
                'leader_status' => $head ? $head->status->name : 'Pending',
                'hr_status'     => $hr ? $hr->status->name : 'Pending',
            ];
        });

        return Inertia::render('management/Employee/UndertimeFormList', ['undertimes' => $undertimes]);
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

        return redirect()->route('employee.undertimeform.index')->with('message', 'Undertime filed.');
    }

    public function edit($id)
    {
        $undertime = Undertime::with('statuses.status')->findOrFail($id);
        if ($undertime->user_id !== Auth::id()) abort(403);

        $isApproved = $undertime->statuses->contains(fn($s) => strtolower($s->status?->name) === 'approved');
        if ($isApproved) return redirect()->back()->with('error', 'Approved requests are locked.');

        $user = Auth::user()->load(['department', 'position']);
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
        if ($undertime->user_id !== Auth::id()) abort(403);

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
