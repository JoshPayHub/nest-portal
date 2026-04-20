<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $allStatuses = Status::all();

        // 1. Fetch employees in the same department
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name', 'username')
            ->orderBy('first_name', 'asc')
            ->get();

        // 2. Query leaves with relationships
        $leavesQuery = Leave::whereHas('user', function ($query) use ($user) {
            $query->where('department_id', $user->department_id);
        })->with([
            'user',
            'approvalStatuses.user',
            'approvalStatuses.status'
        ]);

        // 3. Apply Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $leavesQuery->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $leavesQuery->where('user_id', $request->employee_id);
        }

        // 4. Paginate and Map to ensure all info is passed
        $leaves = $leavesQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($leave) {
                // Identify the specific approval entries
                $leaderEntry = $leave->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $leave->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'                => $leave->id,
                    'reference_no'      => $leave->reference_no,
                    'employee_name'     => $leave->user->first_name . ' ' . $leave->user->last_name,
                    'date_filed'        => $leave->created_at->format('M d, Y'),
                    'type_leave'        => $leave->type_leave,
                    'pay_type'          => ($leave->pay_type === 'With Pay') ? 'With Pay' : 'Without Pay',
                    'start_date'        => Carbon::parse($leave->start_date)->format('M d, Y'),
                    'end_date'          => Carbon::parse($leave->end_date)->format('M d, Y'),
                    'total_days'        => $leave->total_days,
                    'reason'            => $leave->reason,
                    // Status tracking
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                    'status'             => $leaderEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/ApprovalForm/LeaveList', [
            'items'           => $leaves,
            'employeeOptions' => $employees,
            'statuses'        => $allStatuses,
            'filters'         => $request->only(['employee_id', 'search'])
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $leave = Leave::findOrFail($id);

        // Using updateOrInsert to handle the approval log
        DB::table('leave_statuses')->updateOrInsert(
            [
                'leave_id' => $leave->id,
                'user_id'  => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('message', 'Leave action processed successfully.');
    }
}
