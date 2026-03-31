<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\BusinessNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ✅ Employees (same department)
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        // ✅ Query (same as manpower)
        $query = BusinessNotification::whereHas('user', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })
        ->with([
            'user',
            'approvalStatuses.user',
            'approvalStatuses.status'
        ]);

        // 🔍 Search
        if ($request->filled('search')) {
            $query->where('purposes', 'like', "%{$request->search}%");
        }

        // 👤 Employee filter
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        $notifications = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {

                $leaderEntry = $item->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 3);

                $hrEntry = $item->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $item->id,

                    // 👤 employee
                    'employee_name' => $item->user->first_name . ' ' . $item->user->last_name,

                    'date_filed' => $item->created_at->format('M d, Y'),
                    'exact_date' => Carbon::parse($item->exact_date)->format('M d, Y'),

                    'purposes' => $item->purposes,
                    'reason' => $item->reason,
                    'location' => $item->location,

                    'business_time' => Carbon::parse($item->business_time)->format('h:i A'),
                    'returned_time' => Carbon::parse($item->returned_time)->format('h:i A'),

                    // ✅ unified status
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name' => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/Head/BusinessNotificationList', [
            'items' => $notifications,
            'employeeOptions' => $employees,
            'filters' => $request->only(['search', 'employee_id']),
        ]);
    }

    // ✅ APPROVAL SAME AS MANPOWER
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $notification = BusinessNotification::findOrFail($id);

        DB::table('business_notification_statuses')->updateOrInsert(
            [
                'business_notification_id' => $notification->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('message', 'Business notification updated.');
    }
}
