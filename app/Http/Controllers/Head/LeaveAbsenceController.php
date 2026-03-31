<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\LeaveAbsence;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveAbsenceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ✅ Employees (same department)
        $employees = User::where('department_id', $user->department_id)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        // ✅ Query (same logic as manpower)
        $query = LeaveAbsence::whereHas('user', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })
        ->with([
            'user',
            'approvalStatuses.user',
            'approvalStatuses.status'
        ]);

        // 🔍 Search
        if ($request->filled('search')) {
            $query->where('type_absence', 'like', "%{$request->search}%");
        }

        // 👤 Employee filter
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        // ✅ Transform (same format as manpower/leave)
        $absences = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($absence) {

                $leaderEntry = $absence->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 3);

                $hrEntry = $absence->approvalStatuses
                    ->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id' => $absence->id,

                    // 👤 employee name (NEW)
                    'employee_name' => $absence->user->first_name . ' ' . $absence->user->last_name,

                    'date_filed' => $absence->created_at->format('M d, Y'),
                    'date_absence' => Carbon::parse($absence->date_absence)->format('M d, Y'),

                    'type_absence' => $absence->type_absence,
                    'reason' => $absence->reason,

                    // ✅ unified naming (IMPORTANT)
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name' => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/Head/LeaveAbsenceList', [
            'items' => $absences, // ✅ SAME AS MANPOWER
            'employeeOptions' => $employees,
            'filters' => $request->only(['search', 'employee_id']),
        ]);
    }

    // ✅ SAME APPROVAL FUNCTION AS MANPOWER
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7 = Approved, 8 = Rejected
        ]);

        $absence = LeaveAbsence::findOrFail($id);

        DB::table('leave_absent_statuses')->updateOrInsert(
            [
                'leave_absent_id' => $absence->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('message', 'Leave absence updated.');
    }
}
