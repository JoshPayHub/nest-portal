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
    public function index()
    {
        $rawNotifications = BusinessNotification::with(['statuses.user.userType', 'statuses.status'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $notifications = $rawNotifications->map(function ($item) {
            $headEntry = $item->statuses->first(fn($s) => $s->user?->userType?->name === 'Head');
            $hrEntry = $item->statuses->first(fn($s) => $s->user?->userType?->name === 'HR');

            return [
                'id' => $item->id,
                'date_filed' => $item->created_at->format('M d, Y'),
                'purposes' => $item->purposes,
                'exact_date' => Carbon::parse($item->exact_date)->format('M d, Y'),
                'leader_status' => $headEntry ? $headEntry->status->name : 'Pending',
                'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
            ];
        });

        return Inertia::render('management/Employee/BusinessNotificationList', [
            'notifications' => $notifications
        ]);
    }

    public function create()
    {
        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/BusinessNotification', [
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

        return redirect()->route('employee.businessnotification.index')->with('message', 'Notification submitted!');
    }

    public function edit($id)
    {
        $notification = BusinessNotification::with('statuses.status')->findOrFail($id);

        if ($notification->user_id !== Auth::id()) abort(403);

        $isApproved = $notification->statuses->contains(fn($s) => strtolower($s->status?->name) === 'approved');
        if ($isApproved) return redirect()->back()->with('error', 'Approved notifications cannot be edited.');

        $user = Auth::user()->load(['department', 'position']);
        return Inertia::render('management/Employee/BusinessNotification', [
            'report' => $notification,
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
        $notification = BusinessNotification::findOrFail($id);
        if ($notification->user_id !== Auth::id()) abort(403);

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
            // Reset statuses to Pending (Assumed ID 4 based on your Manpower controller)
            DB::table('business_notification_statuses')
                ->where('business_notification_id', $notification->id)
                ->update(['status_id' => 4, 'updated_at' => now()]);
        });

        return redirect()->route('employee.businessnotification.index')->with('message', 'Notification updated.');
    }
}
