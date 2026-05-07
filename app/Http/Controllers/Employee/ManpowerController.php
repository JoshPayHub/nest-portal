<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Manpower;
use App\Models\Notification;
use App\Models\User;
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
            'manpowers' => $manpowers,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function create()
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/Manpower', [
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
            'report_to' => 'required|string',
            'date_required' => 'required|date|after:today',
            'position_type' => 'required|string',
            'replacement_for' => 'nullable|string',
            'job_description' => 'required|string|min:20',
            'justification' => 'required|string|min:20',
            'status_type' => 'required|string',
            'payment_type' => 'required|string',
        ]);

        // FIX: You must assign the result to $manpower so notifyUsers can use it
        $manpower = Manpower::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'position_id' => Auth::user()->position_id,
        ]));

        $this->notifyUsers(
            $request,
            $manpower,
            "New Manpower request",
            "A new Manpower request has been submitted by " . $request->user()->first_name
        );

        return redirect()->back()->with('message', 'Manpower request submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user()->load(['department', 'position']);
        $manpower = Manpower::with(['approvalStatuses.status'])->findOrFail($id);
        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.manpowers.index',
            3 => 'head.manpowers.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($manpower->user_id !== $user->id) {
            return redirect()->route($routeName)->with('error', 'Unauthorized access.');
        }

        // Logic to check if locked
        $hasRejected = $manpower->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name) === 'rejected');
        $hasApproved = $manpower->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name) === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route($routeName)->with('error', 'This request is approved and cannot be modified.');
        }

        // Format date for the HTML input
        $manpower->date_required = Carbon::parse($manpower->date_required)->format('Y-m-d');

        return Inertia::render('management/Employee/Manpower', [
            'report' => $manpower,
            'isEditing' => true,
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $manpower = Manpower::with('approvalStatuses.status')->findOrFail($id);
        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.manpowers.index',
            3 => 'head.manpowers.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($manpower->user_id !== Auth::id()) {
            return redirect()->route($routeName)->with('error', 'Unauthorized.');
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

        DB::transaction(function () use ($request, $manpower, $validated) {
            $manpower->update($validated);

            // Reset statuses to Pending (Assumed ID 4)
            DB::table('manpower_statuses')->where('manpower_id', $manpower->id)->update([
                'status_id' => 4,
                'updated_at' => now()
            ]);
        $this->notifyUsers(
                $request,
                $manpower,
                "Manpower Updated",
                $request->user()->first_name . " has updated their Manpower and is awaiting re-approval."
            );
        });

        return redirect()->route($routeName)->with('message', 'Manpower request updated.');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $types = [
            3 => '/head/manpower',
            1 => '/hr/manpower'
        ];

        foreach ($types as $typeId => $route) {
            $notification = Notification::where('user_id', $employeeId)
                ->where('user_type_id', $typeId)
                ->where('data', 'LIKE', '%manpower_id%')
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
                    'data'         => json_encode(['manpower_id' => $report->id]),
                ]);
            }
        }
    }
}
