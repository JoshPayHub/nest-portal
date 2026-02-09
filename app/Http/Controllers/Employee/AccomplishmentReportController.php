<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AccomplishReport;
use App\Models\AccomplishActivity;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccomplishmentReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all statuses once so we can look up names by ID
        $allStatuses = Status::all();

        $reports = AccomplishReport::where('user_id', $user->id)
            ->withCount('activities')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) use ($allStatuses) {
                return [
                    'id' => $report->id,
                    'report_date' => $report->created_at?->format('M d, Y') ?? '',
                    'period_from' => $report->from_date ? \Carbon\Carbon::parse($report->from_date)->format('M d, Y') : '',
                    'period_to'   => $report->to_date ? \Carbon\Carbon::parse($report->to_date)->format('M d, Y') : '',
                    'activities_count' => $report->activities_count,

                    // Manually find the status name based on the ID stored in the report
                    'leader_status_name' => $allStatuses->firstWhere('id', $report->leader_status_id)?->name ?? 'Pending',
                    'hr_status_name'     => $allStatuses->firstWhere('id', $report->hr_status_id)?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/Employee/AccomplishmentListReport', [
            'reports' => $reports,
            'statuses' => $allStatuses,
        ]);
    }

    public function createIndex(Request $request)
    {
        $user = $request->user()->load(['department', 'position']);
        $statuses = DB::table('statuses')
            ->whereIn('name', ['pending', 'completed'])
            ->get();

        return Inertia::render('management/Employee/AccomplishmentReport', [
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'statuses' => $statuses
        ]);
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'activities' => ['required', 'array', 'min:1'],
            'activities.*.date' => ['required', 'date'],
            'activities.*.activity' => ['required', 'string'],
            // 'remarks' removed
            'activities.*.status_id' => ['required', 'exists:statuses,id'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $report = AccomplishReport::create([
                'user_id' => $request->user()->id,
                'department_id' => $request->user()->department_id,
                'position_id' => $request->user()->position_id,
                'from_date' => $validated['period_from'],
                'to_date' => $validated['period_to'],
                'leader_status_id' => 4,
                'hr_status_id' => 4,
            ]);

            foreach ($validated['activities'] as $activity) {
                AccomplishActivity::create([
                    'accomplish_report_id' => $report->id,
                    'activity_date' => $activity['date'],
                    'activity' => $activity['activity'],
                    'status_id' => $activity['status_id'],
                ]);
            }
        });

        return redirect()->back()->with('message', 'Accomplishment report submitted successfully!');
    }
}
