<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    // Show overtime requests
    public function index(Request $request)
    {
        $user = $request->user();
        $allStatuses = Status::all();

        // Fetch employees in the same department for the filter dropdown
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name', 'asc')
            ->get();

        $reportsQuery = Overtime::where('department_id', $user->department_id)
            ->with([
                'user',
                'activities',
                'approvalStatuses.user.userType',
                'approvalStatuses.status'
            ]);

        // Apply Employee Filter if selected
        if ($request->filled('employee_id')) {
            $reportsQuery->where('user_id', $request->employee_id);
        }

        $reports = $reportsQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($report) {
                // Adjust these IDs (3 and 1) to match your actual user_type_id roles
                $leaderEntry = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $report->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                $firstActivity = $report->activities->first();

                return [
                    'id' => $report->id,
                    'user_id' => $report->user_id,
                    'employee_name' => $report->user->first_name . ' ' . $report->user->last_name,
                    'created_at' => $report->created_at?->format('M d, Y') ?? '',
                    'cut_off_date' => $report->cut_off_date
                        ? Carbon::parse($report->cut_off_date)->format('M d, Y')
                        : '',

                    'overtime_date' => $firstActivity
                        ? Carbon::parse($firstActivity->overtime_date)->format('M d, Y')
                        : 'N/A',

                    'start_time'  => $firstActivity ? Carbon::parse($firstActivity->time_start)->format('h:i A') : '',
                    'end_time'    => $firstActivity ? Carbon::parse($firstActivity->time_end)->format('h:i A') : '',
                    'total_hours' => $report->activities->sum('additional_hours_worked'),
                    'reason'      => $firstActivity?->description ?? 'No reason provided',

                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'leader_approver_id' => $leaderEntry?->user_id,

                    'hr_status_name' => $hrEntry?->status?->name ?? 'Pending',
                    'hr_approver_id' => $hrEntry?->user_id,

                    'activities_count' => $report->activities->count(),
                    'activities' => $report->activities->map(function ($item) {
                        return [
                            'date'        => Carbon::parse($item->overtime_date)->format('M d, Y'),
                            'description' => $item->description,
                            'time_start'  => Carbon::parse($item->time_start)->format('h:i A'),
                            'time_end'    => Carbon::parse($item->time_end)->format('h:i A'),
                            'hours'       => $item->additional_hours_worked,
                        ];
                    }),
                ];
            });

        return Inertia::render('management/Head/OvertimeList', [
            'reports' => $reports, // Changed from 'overtimes' to 'reports'
            'statuses'  => $allStatuses,
            'employeeOptions' => $employees,
            'filters' => $request->only(['employee_id']),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $overtime = Overtime::findOrFail($id);

        DB::table('overtime_statuses')->updateOrInsert(
            [
                'overtime_id' => $overtime->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('message', 'Action processed successfully.');
    }
}
