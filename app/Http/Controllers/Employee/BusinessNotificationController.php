<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessNotification;
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
            'notifications' => $notifications,
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

        BusinessNotification::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'position_id' => Auth::user()->position_id,
        ]));

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

        DB::transaction(function () use ($notification, $validated) {
            $notification->update($validated);
            // Reset statuses to Pending (Assumed ID 4)
            DB::table('business_notification_statuses')
                ->where('business_notification_id', $notification->id)
                ->update(['status_id' => 4, 'updated_at' => now()]);
        });

        return redirect()->route($routeName)->with('message', 'Notification updated.');
    }
}
