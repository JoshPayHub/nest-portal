<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Notification::query();

        // Apply shared filtering logic
        $this->applyUserFilters($query, $user);

        $notifications = $query->with('employee')->latest()->paginate(10);

        return Inertia::render('management/Employee/Notification', [
            'notifications' => $notifications,
            'auth_user_type_id' => $user->user_type_id,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        // 1. Decode data safely
        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);

        $targetId = $data['id']
                    ?? $data['report_id']
                    ?? $data['request_id']
                    ?? $data['change_off_id']
                    ?? $data['manpower_id']
                    ?? $data['business_notification_id']
                    ?? $data['leave_id']
                    ?? '';

        $url = $notification->link ?? $notification->route;

        if ($targetId && !str_contains($url, '?')) {
            $url .= "?open=" . $targetId;
        } elseif ($targetId) {
            $url .= "&open=" . $targetId;
        }

        return redirect()->to($url);
    }

    public function markAllRead(Request $request)
    {
        $user = auth()->user();
        $query = Notification::whereNull('read_at')->where('is_read', false);

        // Apply the same security filters as the index
        $this->applyUserFilters($query, $user);

        // Update all matching unread notifications
        $query->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Centralized logic to filter notifications based on user role and department
     */
    private function applyUserFilters($query, $user)
    {
        if (in_array($user->user_type_id, [1, 3])) {
            // HR (1) and Head (3) see notifications sent to their user_type
            $query->where('user_type_id', $user->user_type_id);

            if ($user->user_type_id == 3) {
                // Heads only see their specific department
                $query->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            }
        } else {
            // Regular employees see their personal notifications
            $query->where('user_id', $user->id)
                  ->whereNull('user_type_id');
        }
    }
}
