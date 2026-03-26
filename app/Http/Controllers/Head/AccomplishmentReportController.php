<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\AccomplishReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AccomplishmentReportController extends Controller
{
   public function index(Request $request)
{
    $user = $request->user();

    // Fetch employees in the same department for the filter dropdown
    $employees = User::where('department_id', $user->department_id)
        ->select('id', 'first_name', 'last_name', 'username')
        ->orderBy('first_name', 'asc')
        ->get();

    $reportsQuery = AccomplishReport::where('department_id', $user->department_id)
        ->with([
            'user',
            'activities.status',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

    // Apply Employee Filter if selected
    if ($request->has('employee_id') && $request->employee_id != '') {
        $reportsQuery->where('user_id', $request->employee_id);
    }

    $reports = $reportsQuery->orderBy('created_at', 'desc')
        ->paginate(10)
        ->withQueryString() // Keeps filters active during pagination
        ->through(function ($report) {
            $leaderEntry = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
            $hrEntry     = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

            return [
                'id' => $report->id,
                'user_id' => $report->user_id, // Added for easier filtering logic
                'employee_name' => $report->user->first_name . ' ' . $report->user->last_name,
                'report_date' => $report->created_at->format('M d, Y'),
                'period_from' => $report->from_date ? Carbon::parse($report->from_date)->format('M d, Y') : '',
                'period_to'   => $report->to_date ? Carbon::parse($report->to_date)->format('M d, Y') : '',
                'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                'activities_count' => $report->activities->count(),
                'activities' => $report->activities->map(function($act) {
                    return [
                        'date' => $act->activity_date ? Carbon::parse($act->activity_date)->format('M d, Y') : '',
                        'activity' => $act->activity,
                        'status_name' => $act->status?->name ?? 'N/A'
                    ];
                }),
            ];
        });

    return Inertia::render('management/Head/AccomplishmentListReport', [
        'reports' => $reports,
        'employeeOptions' => $employees,
        'filters' => $request->only(['employee_id'])
    ]);
}

    // Add this new method to handle the approval post
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $report = AccomplishReport::findOrFail($id);

        // Update or Create the approval status for this Head
        DB::table('accomplish_report_statuses')->updateOrInsert(
            [
                'accomplish_report_id' => $report->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('message', 'Action processed successfully.');
    }
}
