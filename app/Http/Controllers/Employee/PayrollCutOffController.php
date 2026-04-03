<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\PayrollCutOff;
use App\Models\AttendanceEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class PayrollCutOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cutoffs = PayrollCutOff::with(['attendanceEmployees' => function ($q) use ($user) {
                $q->where('user_id', $user->id) // only their own attendance
                  ->with(['approvalStatuses' => fn($q2) => $q2->where('employee_id', $user->id)
                      ->with('status', 'approver')]); // show who approved
            }])
            ->latest()
            ->paginate(10)
            ->through(fn($cutoff) => $this->formatCutoffForUser($cutoff, $user));

        return Inertia::render('management/Employee/PayrollCutOff', [
            'cutoffs' => $cutoffs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function attendancePage(Request $request, $id)
    {
        $user = $request->user();

        $cutoff = PayrollCutOff::findOrFail($id);

        // Fetch attendance only for logged-in user
        $attendance = AttendanceEmployee::with([
                'attendanceLists',
                'approvalStatuses' => fn($q) => $q->where('employee_id', $user->id)
                    ->with('status', 'approver')
            ])
            ->where('user_id', $user->id)
            ->where('payroll_cut_off_id', $id)
            ->first();

        if (!$attendance) {
            abort(403, "You don't have access to this attendance.");
        }

        $attendanceData = [
            'attendance_employee_id' => $attendance->id,
            'dates' => $attendance->attendanceLists->map(fn($att) => [
                'date' => $att->attendance_date,
                'time_in' => $att->time_in,
                'time_out' => $att->time_out,
            ])->toArray(),
            'approvals' => $attendance->approvalStatuses,
        ];

        return Inertia::render('management/Employee/AttendanceForm', [
            'authUser' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
            ],
            'cutoff' => $cutoff,
            'attendanceData' => $attendanceData,
        ]);
    }

    private function formatCutoffForUser($cutoff, $user)
    {
        $attendance = $cutoff->attendanceEmployees->first();

        $leader = $attendance?->approvalStatuses
            ->first(fn($a) => $a->approver?->user_type_id == 3);

        $hr = $attendance?->approvalStatuses
            ->first(fn($a) => $a->approver?->user_type_id == 1);

        return [
            'id' => $cutoff->id,
            'name' => $cutoff->name,
            'from_cutoff_date' => $cutoff->from_cutoff_date,
            'to_cutoff_date' => $cutoff->to_cutoff_date,
            'leader_status_name' => $leader?->status?->name ?? 'Pending',
            'hr_status_name' => $hr?->status?->name ?? 'Pending',
        ];
    }
}
