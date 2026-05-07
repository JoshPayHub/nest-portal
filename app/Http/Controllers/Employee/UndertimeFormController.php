<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Undertime;
use App\Models\User;
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
                    'from_time' => Carbon::parse($item->from_time)->format('h:i A'),
                    'to_time' => Carbon::parse($item->to_time)->format('h:i A'),
                    'total_time' => $displayTime,
                    'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                    'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
                ];
            });

        return Inertia::render('management/Employee/UndertimeFormList', [
            'undertimes' => $undertimes,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function create()
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/UndertimeForm', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'isEditing' => false,
            'auth_user_type_id' => auth()->user()->user_type_id,
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

        $undertime = Undertime::create([
            'user_id'        => Auth::id(),
            'undertime_date' => $validated['undertime_date'],
            'from_time'      => $validated['from_time'],
            'to_time'        => $validated['to_time'],
            'total_time'     => $validated['total_time'],
            'reason'         => $validated['reason'],
            'department_id'  => Auth::user()->department_id,
            'position_id'    => Auth::user()->position_id,
        ]);

        $this->notifyUsers(
                $request,
                $undertime,
                "New Undertime request",
                "A new Undertime request has been submitted by " . $request->user()->first_name
            );

        return redirect()->back()->with('message', 'Undertime submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user()->load(['department', 'position']);
        $undertime = Undertime::with(['approvalStatuses.status'])->findOrFail($id);
        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.undertimeforms.index',
            3 => 'head.undertimeforms.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($undertime->user_id !== $user->id) {
            return redirect()->route($routeName)->with('error', 'Unauthorized access.');
        }

        $hasRejected = $undertime->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $undertime->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route($routeName)->with('error', 'This request is approved and cannot be modified.');
        }

        // Fixed: Use undertime_date instead of date_required
        $undertime->undertime_date = Carbon::parse($undertime->undertime_date)->format('Y-m-d');
        $undertime->from_time = Carbon::parse($undertime->from_time)->format('H:i');
        $undertime->to_time = Carbon::parse($undertime->to_time)->format('H:i');

        return Inertia::render('management/Employee/UndertimeForm', [
            'report' => $undertime,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A'
            ],
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $undertime = Undertime::findOrFail($id);

        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.undertimeforms.index',
            3 => 'head.undertimeforms.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($undertime->user_id !== Auth::id()) {
            return redirect()->route($routeName)->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'undertime_date' => 'required|date',
            'from_time'      => 'required',
            'to_time'        => 'required',
            'total_time'     => 'required|integer|min:1',
            'reason'         => 'required|string|min:10',
        ]);

        DB::transaction(function () use ($request, $undertime, $validated) {
            $undertime->update($validated);
            DB::table('undertime_statuses')
                ->where('undertime_id', $undertime->id)
                ->update(['status_id' => 4, 'updated_at' => now()]);

             $this->notifyUsers(
                $request,
                $undertime,
                "Undertime Updated",
                $request->user()->first_name . " has updated their Undertime and is awaiting re-approval."
            );
        });

        return redirect()->route($routeName)->with('message', 'Undertime updated.');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $types = [
            3 => '/head/undertime-form',
            1 => '/hr/undertime-form'
        ];

        foreach ($types as $typeId => $route) {
            $notification = Notification::where('user_id', $employeeId)
                ->where('user_type_id', $typeId)
                ->where('data', 'LIKE', '%undertime_id%')
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
                    'data'         => json_encode(['undertime_id' => $report->id]),
                ]);
            }
        }
    }
}
