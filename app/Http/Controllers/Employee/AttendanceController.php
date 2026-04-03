<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // ✅ Validation
        $request->validate([
            'payroll_cut_off_id' => 'required|exists:payroll_cut_offs,id',
            'attendances' => 'required|array',
            'attendances.*.date' => 'required|date',
            'attendances.*.time_in' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'attendances.*.time_out' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
        ]);

        // ✅ Fetch attendance with ONLY the current employee's approvals
        $attendanceEmployee = AttendanceEmployee::with([
            'approvalStatuses' => function($q) use ($user) {
                $q->where('employee_id', $user->id);
            }
        ])
        ->where('user_id', $user->id)
        ->where('payroll_cut_off_id', $request->payroll_cut_off_id)
        ->first();

        // ✅ Check if the employee's own attendance is approved
        if ($attendanceEmployee) {
            $approvedStatus = $attendanceEmployee->approvalStatuses
                ->firstWhere('status_id', 7); // 7 = Approved

            if ($approvedStatus) {
                return back()->withErrors([
                    'error' => 'Attendance cannot be edited because it is already approved by you.'
                ]);
            }
        }

        // ✅ Transaction to create/update attendance
        DB::transaction(function () use ($request, $user, &$attendanceEmployee) {

            if (!$attendanceEmployee) {
                // Create attendance employee record
                $attendanceEmployee = AttendanceEmployee::create([
                    'user_id' => $user->id,
                    'department_id' => $user->department_id,
                    'position_id' => $user->position_id,
                    'payroll_cut_off_id' => $request->payroll_cut_off_id,
                ]);
            } else {
                // Delete old attendance lists for update
                $attendanceEmployee->attendanceLists()->delete();
            }

            // Insert new attendance lists
            foreach ($request->attendances as $att) {
                AttendanceList::create([
                    'attendance_employee_id' => $attendanceEmployee->id,
                    'attendance_date' => $att['date'],
                    'time_in' => $att['time_in'] ?? null,
                    'time_out' => $att['time_out'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Attendance submitted successfully!');
    }
}
