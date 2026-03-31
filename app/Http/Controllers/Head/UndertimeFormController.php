<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Undertime;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class UndertimeFormController extends Controller
{
    // Show undertime requests for the Department Head's staff
    public function index(Request $request)
    {
        $user = $request->user();
        $allStatuses = Status::all();

        // Fetch employees in the same department for the filter dropdown
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name', 'asc')
            ->get();

        $reportsQuery = Undertime::whereHas('user', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->with([
                'user',
                'approvalStatuses.user.userType',
                'approvalStatuses.status'
            ]);

        // Apply Employee Filter if selected
        if ($request->filled('employee_id')) {
            $reportsQuery->where('user_id', $request->employee_id);
        }

        $undertimes = $reportsQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                // IDs 3 (Leader/Head) and 1 (HR)
                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                $totalMins = (int)$item->total_time;
                $h = floor($totalMins / 60);
                $m = $totalMins % 60;
                $displayTime = ($h > 0 ? "{$h}h " : "") . ($m > 0 || $h == 0 ? "{$m}m" : "");

                return [
                    'id' => $item->id,
                    'employee_name' => $item->user->first_name . ' ' . $item->user->last_name,
                    'date_filed' => $item->created_at->format('M d, Y'),
                    'reason' => $item->reason,
                    'undertime_date' => Carbon::parse($item->undertime_date)->format('M d, Y'),
                    'from_date' => Carbon::parse($item->from_date)->format('h:i A'),
                    'to_date' => Carbon::parse($item->to_date)->format('h:i A'),
                    'total_time' => $displayTime,
                    'leader_status' => $leaderEntry ? $leaderEntry->status->name : 'Pending',
                    'leader_status_id' => $leaderEntry?->status_id,
                    'hr_status'     => $hrEntry ? $hrEntry->status->name : 'Pending',
                ];
            });

        return Inertia::render('management/Head/UndertimeFormList', [
            'undertimes' => $undertimes,
            'statuses'   => $allStatuses,
            'employeeOptions' => $employees,
            'filters' => $request->only(['employee_id']),
        ]);
    }

    // Process Approval/Rejection (IDs 7 and 8)
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $undertime = Undertime::findOrFail($id);

        DB::table('undertime_statuses')->updateOrInsert(
            [
                'undertime_id' => $undertime->id,
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
