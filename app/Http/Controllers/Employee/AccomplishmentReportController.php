<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AccomplishReport;
use App\Models\AccomplishActivity;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AccomplishmentReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $allStatuses = Status::all();

        $reports = AccomplishReport::where('user_id', $user->id)
            ->with([
                'activities.status',
                'approvalStatuses.user.userType',
                'approvalStatuses.status'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) {

                $leaderEntry = $report->approvalStatuses->first(function($log) {
                    return $log->user->user_type_id == 3;
                });

                $hrEntry = $report->approvalStatuses->first(function($log) {
                    return $log->user->user_type_id == 1;
                });

                return [
                    'id' => $report->id,
                    'report_date' => $report->created_at?->format('M d, Y') ?? '',
                    'period_from' => $report->from_date ? Carbon::parse($report->from_date)->format('M d, Y') : '',
                    'period_to'   => $report->to_date ? Carbon::parse($report->to_date)->format('M d, Y') : '',

                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'leader_approver_id' => $leaderEntry?->user_id,

                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                    'hr_approver_id'     => $hrEntry?->user_id,

                    'activities_count' => $report->activities->count(),
                    'activities' => $report->activities->map(function($act) {
                        return [
                            'date' => Carbon::parse($act->activity_date)->format('M d, Y'),
                            'activity' => $act->activity,
                            'status_name' => $act->status?->name ?? 'N/A'
                        ];
                    }),
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
            'activities.*.status_id' => ['required', 'exists:statuses,id'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $report = AccomplishReport::create([
                'user_id' => $request->user()->id,
                'department_id' => $request->user()->department_id,
                'position_id' => $request->user()->position_id,
                'from_date' => $validated['period_from'],
                'to_date' => $validated['period_to'],
            ]);

            foreach ($validated['activities'] as $activity) {
                $report->activities()->create([
                    'activity_date' => $activity['date'],
                    'activity' => $activity['activity'],
                    'status_id' => $activity['status_id'],
                ]);
            }
        });

        return redirect()->back()->with('message', 'Accomplishment report submitted successfully!');
    }

    public function edit(Request $request, $id)
    {
        $report = AccomplishReport::with(['activities', 'approvalStatuses'])->findOrFail($id);
        $user = $request->user();

        if ($report->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        // Logic: Check if it's already fully approved (status 7)
        // If HR or Head rejected, approvalStatuses will contain a status 8 (Rejected)
        $isApprovedByAll = $report->approvalStatuses->every(function ($item) {
            return $item->status_id == 7;
        }) && $report->approvalStatuses->count() >= 2; // Assuming Head + HR = 2 approvals

        if ($isApprovedByAll) {
            return redirect()->back()->with('error', 'Fully approved reports cannot be edited.');
        }

        $user->load(['department', 'position']);
        $statuses = DB::table('statuses')->whereIn('name', ['pending', 'completed'])->get();

        return Inertia::render('management/Employee/AccomplishmentReport', [
            'report' => $report,
            'authUser' => [
                'name' => $user->name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'statuses' => $statuses,
            'isEditing' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        $report = AccomplishReport::with('approvalStatuses')->findOrFail($id);

        $validated = $request->validate([
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'activities' => ['required', 'array', 'min:1'],
            'activities.*.date' => ['required', 'date'],
            'activities.*.activity' => ['required', 'string'],
            'activities.*.status_id' => ['required', 'exists:statuses,id'],
        ]);

        DB::transaction(function () use ($report, $validated) {
            // Update basic report info
            $report->update([
                'from_date' => $validated['period_from'],
                'to_date' => $validated['period_to'],
            ]);


            DB::table('accomplish_report_statuses')
                ->where('accomplish_report_id', $report->id)
                ->update([
                    'status_id' => 4,
                    'updated_at' => now()
                ]);

            // 2. Sync Activities (Delete old, Insert new)
            $report->activities()->delete();

            foreach ($validated['activities'] as $activity) {
                $report->activities()->create([
                    'activity_date' => $activity['date'],
                    'activity' => $activity['activity'],
                    'status_id' => $activity['status_id'],
                ]);
            }
        });

        return redirect()->route('employee.accomplishmentreport.index')
            ->with('message', 'Report updated successfully and reset to pending for all approvers.');
    }
}
