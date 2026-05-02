<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusinessNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = BusinessNotification::where('user_id', $user->id)
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
                    'purposes' => $item->purposes,
                    'reason' => $item->reason,
                    'location' => $item->location,
                    'business_time' => Carbon::parse($item->business_time)->format('h:i A'),
                    'returned_time' => Carbon::parse($item->returned_time)->format('h:i A'),
                    'exact_date' => Carbon::parse($item->exact_date)->format('M d, Y'),
                    'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                    'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
                ];
            }); // Fixed: Closed the through function correctly

        return Inertia::render('management/Employee/BusinessNotificationList', [
            'businessNotifications' => $notifications,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function create()
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/BusinessNotification', [
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
            'purposes' => 'required|string',
            'reason' => 'required|string|min:10',
            'location' => 'required|string',
            'exact_date' => 'required|date|after_or_equal:today',
            'business_time' => 'required',
            'returned_time' => 'required',
        ]);

        $manpower = BusinessNotification::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'position_id' => Auth::user()->position_id,
        ]));

        $this->notifyUsers(
            $request,
            $manpower,
            "New Business Notification request",
            "A new Business Notification request has been submitted by " . $request->user()->first_name
        );

        return redirect()->back()->with('message', 'Business notification submitted successfully');
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user()->load(['department', 'position']);
        $notification = BusinessNotification::with(['approvalStatuses.status'])->findOrFail($id);
        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.businessnotifications.index',
            3 => 'head.businessnotifications.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($notification->user_id !== $user->id) {
            return redirect()->route($routeName)->with('error', 'Unauthorized access.');
        }

        // Logic to check if locked
        $hasRejected = $notification->approvalStatuses->contains(fn($s) => $s->status_id === 5 || strtolower($s->status?->name ?? '') === 'rejected');
        $hasApproved = $notification->approvalStatuses->contains(fn($s) => $s->status_id === 2 || strtolower($s->status?->name ?? '') === 'approved');

        if ($hasApproved && !$hasRejected) {
            return redirect()->route($routeName)->with('error', 'This request is approved and cannot be modified.');
        }

        return Inertia::render('management/Employee/BusinessNotification', [
            'report' => $notification,
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
        $notification = BusinessNotification::with('approvalStatuses.status')->findOrFail($id);
        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.businessnotifications.index',
            3 => 'head.businessnotifications.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($notification->user_id !== Auth::id()) {
            return redirect()->route($routeName)->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'purposes' => 'required|string',
            'reason' => 'required|string|min:10',
            'location' => 'required|string',
            'exact_date' => 'required|date',
            'business_time' => 'required',
            'returned_time' => 'required',
        ]);

        DB::transaction(function () use ($request, $notification, $validated) {
            $notification->update($validated);
            // Reset statuses to Pending (Assumed ID 4)
            DB::table('business_notification_statuses')
                ->where('business_notification_id', $notification->id)
                ->update(['status_id' => 4, 'updated_at' => now()]);

            $this->notifyUsers(
                $request,
                $notification,
                "Business Notification Updated",
                $request->user()->first_name . " has updated their Business Notification and is awaiting re-approval."
            );
        });

        return redirect()->route($routeName)->with('message', 'Notification updated.');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $types = [
            3 => '/head/business-notification',
            1 => '/hr/business-notification'
        ];

        foreach ($types as $typeId => $route) {
            $notification = Notification::where('user_id', $employeeId)
                ->where('user_type_id', $typeId)
                ->where('data', 'LIKE', '%business_notification_id%')
                ->where('data', 'LIKE', '%' . $report->id . '%')
                ->first();

            if ($notification) {
                $notification->update([
                    'title'   => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'updated_at' => now(),
                ]);
            } else {
                Notification::create([
                    'user_id'      => $employeeId,
                    'user_type_id' => $typeId,
                    'title'        => $title,
                    'message'      => $message,
                    'route'        => $route,
                    'data'         => json_encode(['business_notification_id' => $report->id]),
                ]);
            }
        }
    }
}
