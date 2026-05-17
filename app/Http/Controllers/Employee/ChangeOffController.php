<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ChangeOff;
use App\Models\Notification;
use App\Models\User;
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
                // Restore your status tracking logic
                $leaderEntry = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $req->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'            => $req->id,
                    'date_filed'    => $req->created_at->format('M d, Y'),
                    'request_type'  => $req->label->off->name ?? 'N/A',
                    'original_day'  => $req->label->originalDay->name ?? 'N/A',
                    'new_day'       => $req->label->newDay->name ?? 'N/A',
                    'leader_status' => $leaderEntry->status->name ?? 'Pending',
                    'hr_status'     => $hrEntry->status->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/Employee/ChangeOffList', [
            'requests' => $requests,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user()->load(['department', 'position']);
        $days = DB::table('offs')
        ->whereNotIn('name', ['time', 'day'])
        ->orderBy('id')
        ->get();

        return Inertia::render('management/Employee/ChangeOff', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'days' => $days,
            'todayDate' => now()->format('Y-m-d'),
            'isEditing' => false,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function store(Request $request)
    {
        // Validation now only requires the Off IDs (Days)
        $validated = $request->validate([
            'request_type' => 'required|exists:offs,id',
            'original_off_id' => 'required|exists:offs,id',
            'new_off_id' => 'required|exists:offs,id',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $user = $request->user();

            $changeOff = ChangeOff::create([
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'position_id' => $user->position_id,
            ]);

            // Removed original_date, new_date, original_time, new_time
            $changeOff->label()->create([
                'off_id'          => $validated['request_type'],
                'original_day_id' => $validated['original_off_id'],
                'new_day_id'      => $validated['new_off_id'],
            ]);
            $this->notifyUsers(
                $request,
                $changeOff,
                "New Change Off request",
                "A new Change Off request has been submitted by " . $request->user()->first_name
            );
        });

        return redirect()->back()->with('message', 'Change Off submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $user = $request->user()->load(['department', 'position']);
        $days = DB::table('offs')
        ->whereNotIn('name', ['time', 'day'])
        ->orderBy('id')
        ->get();

        $report = ChangeOff::with(['label', 'approvalStatuses.status'])->find($id);
        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.changeoffs.index',
            3 => 'head.changeoffs.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if (!$report) {
            return redirect()->route($routeName)->with('error', 'Request record not found.');
        }

        if ($report->user_id !== $user->id) {
            return redirect()->route($routeName)->with('error', 'Unauthorized access.');
        }

        // RESTORED: Your check logic
        $hasRejected = $report->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $report->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route($routeName)->with('error', 'This request is approved and cannot be modified.');
        }

        return Inertia::render('management/Employee/ChangeOff', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'days' => $days,
            'report' => $report,
            'todayDate' => now()->format('Y-m-d'),
            'isEditing' => true,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $changeOff = ChangeOff::with('approvalStatuses.status')->find($id);

        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.changeoffs.index',
            3 => 'head.changeoffs.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if (!$changeOff || $changeOff->user_id !== $request->user()->id) {
            return redirect()->route($routeName)->with('error', 'Unable to update request.');
        }

        // RESTORED: Your check logic
        $hasRejected = $changeOff->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $changeOff->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route($routeName)->with('error', 'Cannot update an approved request.');
        }

        $validated = $request->validate([
            'request_type' => 'required|exists:offs,id',
            'original_off_id' => 'required|exists:offs,id',
            'new_off_id' => 'required|exists:offs,id',
        ]);

         DB::transaction(function () use ($request, $changeOff, $validated) {
            // Removed time and date from update
            $changeOff->label()->update([
                'off_id'          => $validated['request_type'],
                'original_day_id' => $validated['original_off_id'],
                'new_day_id'      => $validated['new_off_id'],
            ]);

            // Resetting statuses back to pending
            DB::table('change_off_statuses')
                ->where('change_off_id', $changeOff->id)
                ->update([
                    'status_id' => 4, // Pending
                    'updated_at' => now()
                ]);

            $this->notifyUsers(
                $request,
                $changeOff,
                "Change Off Updated",
                $request->user()->first_name . " has updated their Change Off and is awaiting re-approval."
            );
        });

        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.changeoffs.index',
            3 => 'head.changeoffs.index',
        ];

        $routeName = $routeMap[$userTypeId];

        return redirect()->route($routeName)->with('message', 'Request updated and resubmitted.');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $types = [
            3 => '/head/change-off',
            1 => '/hr/change-off'
        ];

        foreach ($types as $typeId => $route) {
            $notification = Notification::where('user_id', $employeeId)
                ->where('user_type_id', $typeId)
                ->where('data', 'LIKE', '%change_off_id%')
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
                    'data'         => json_encode(['change_off_id' => $report->id]),
                ]);
            }
        }
    }

}
