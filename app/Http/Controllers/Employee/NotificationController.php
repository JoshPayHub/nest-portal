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

        $notifications = $query->with('employee')->orderBy('updated_at', 'desc')->paginate(10);

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
                    ?? $data['overtime_id']
                    ?? $data['undertime_id']
                    ?? $data['attendance_id']
                    ?? $data['cut_off_id']
                    ?? $data['payroll_id']
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
    if ($user->user_type_id == 1) {
        $query->where('user_type_id', 1);

    } elseif ($user->user_type_id == 3) {
        $query->where(function ($q) use ($user) {

            $q->where(function ($sub) use ($user) {
                $sub->where('user_type_id', 3)
                    ->whereHas('employee', function ($emp) use ($user) {
                        $emp->where('department_id', $user->department_id);
                    });
            })

            ->orWhere(function ($sub) use ($user) {
                $sub->where('user_id', $user->id)
                    ->whereNull('user_type_id');
            });

        });

    } else {
        $query->where('user_id', $user->id)
              ->whereNull('user_type_id');
    }
}
}
