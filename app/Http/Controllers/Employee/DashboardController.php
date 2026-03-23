<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementPolicy;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\AccomplishReport;
use App\Models\BusinessNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $deptId = $user->department_id;

        // Fetch announcements relevant to the user's department
        $announcements = AnnouncementPolicy::with('status')
            ->whereHas('status', fn($q) => $q->where('name', 'Active'))
            ->where(function ($q) use ($deptId) {
                $q->whereDoesntHave('filters')
                  ->orWhereHas('filters', fn($f) => $f->whereNull('department_id')->orWhere('department_id', $deptId));
            })
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'types' => $item->types,
                'date' => $item->created_at->format('M d, Y'),
            ]);

        // Summary Statistics
        $stats = [
            'pending_leaves' => Leave::where('user_id', $user->id)
                ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 1))
                ->count(),
            'total_ot_hours' => Overtime::where('user_id', $user->id)
                ->with('activities')
                ->get()
                ->flatMap->activities->sum('additional_hours_worked'),
            'recent_reports' => AccomplishReport::where('user_id', $user->id)->count(),
        ];

        return Inertia::render('management/Employee/Index', [
            'announcements' => $announcements,
            'stats' => $stats,
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'position' => $user->position?->name ?? 'Staff',
                'department' => $user->department?->name ?? 'General',
            ],
        ]);
    }
}


//  return Inertia::render('management/Employee/Index');
