<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Store or update employee attendance.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Validation
        $validated = $request->validate([
            'payroll_cut_off_id' => 'required|exists:payroll_cut_offs,id',
            'attendances' => 'required|array',
            'attendances.*.date' => 'required|date',
            'attendances.*.time_in' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'attendances.*.time_out' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
        ]);

        // 2. Fetch existing record with approval statuses
        $attendanceEmployee = AttendanceEmployee::with(['approvalStatuses.user'])
            ->where('user_id', $user->id)
            ->where('payroll_cut_off_id', $validated['payroll_cut_off_id'])
            ->first();

        if ($attendanceEmployee) {
            $approvals = $attendanceEmployee->approvalStatuses;

            // Logic: 7 = Approved, 8 = Rejected (Matching Accomplishment Report)
            $isLeaderApproved = $approvals->contains(fn($a) => $a->user?->user_type_id == 3 && $a->status_id == 7);
            $isHRApproved     = $approvals->contains(fn($a) => $a->user?->user_type_id == 1 && $a->status_id == 7);
            $hasAnyRejection  = $approvals->contains(fn($a) => $a->status_id == 8);

            if (($isLeaderApproved || $isHRApproved) && !$hasAnyRejection) {
                return redirect()->back()->withErrors([
                    'error' => 'Attendance is locked. It cannot be edited once approval has started unless it is rejected.'
                ]);
            }
        }

        // 3. Database Transaction
        DB::transaction(function () use ($user, $validated, &$attendanceEmployee) {

            if (!$attendanceEmployee) {
                $attendanceEmployee = AttendanceEmployee::create([
                    'user_id' => $user->id,
                    'department_id' => $user->department_id,
                    'position_id' => $user->position_id,
                    'payroll_cut_off_id' => $validated['payroll_cut_off_id'],
                ]);
            } else {
                // Delete old lists
                $attendanceEmployee->attendanceLists()->delete();

                /**
                 * UPDATED TABLE NAME:
                 * Based on your SQL logs, the table is 'attendance_statuses'
                 */
                DB::table('attendance_statuses')
                    ->where('attendance_employee_id', $attendanceEmployee->id)
                    ->update([
                        'status_id' => 4, // Reset to Pending
                        'updated_at' => now()
                    ]);
            }

            // 4. Insert Attendance List Rows
            foreach ($validated['attendances'] as $att) {
                AttendanceList::create([
                    'attendance_employee_id' => $attendanceEmployee->id,
                    'attendance_date' => $att['date'],
                    'time_in' => $att['time_in'] ?? null,
                    'time_out' => $att['time_out'] ?? null,
                ]);
            }
        });

        return redirect()->route('employee.payrollcutoff.index')
            ->with('message', 'Attendance submitted and approval reset to pending.');
    }
}
