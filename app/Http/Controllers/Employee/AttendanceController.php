<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceList;
use App\Models\Notification;
use App\Models\User;
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

        // 2. Fetch existing record
        $attendanceEmployee = AttendanceEmployee::with(['approvalStatuses.user'])
            ->where('user_id', $user->id)
            ->where('payroll_cut_off_id', $validated['payroll_cut_off_id'])
            ->first();

        // --- LOGIC TO DETECT STORE VS UPDATE ---
        if (!$attendanceEmployee) {
            $notifTitle = "New Attendance request";
            $notifMessage = "A new Attendance request has been submitted by " . $user->first_name;
        } else {
            $notifTitle = "Attendance Updated";
            $notifMessage = $user->first_name . " has updated their Attendance and is awaiting re-approval.";

            // Check if locked
            $approvals = $attendanceEmployee->approvalStatuses;
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
        DB::transaction(function () use ($request, $user, $validated, &$attendanceEmployee, $notifTitle, $notifMessage) {

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

            // Trigger notification with the dynamic title and message
            $this->notifyUsers($request, $attendanceEmployee, $notifTitle, $notifMessage);
        });

        $userTypeId = $user->user_type_id;
        $routeMap = [
            2 => 'employee.payrollcutoffs.index',
            3 => 'head.payrollcutoffs.index',
        ];

        return redirect()->route($routeMap[$userTypeId])
            ->with('message', 'Attendance submitted and approval reset to pending.');
    }

    private function notifyUsers(Request $request, $report, $title, $message)
    {
        $employeeId = $report->user_id;
        $cutOffId = $report->payroll_cut_off_id;

        $types = [
            3 => "/head/payroll-cut-off/{$cutOffId}/attendance",
            1 => "/hr/payroll-cut-off/{$cutOffId}/attendance"
        ];

        foreach ($types as $typeId => $route) {
            $notification = Notification::where('user_id', $employeeId)
                ->where('user_type_id', $typeId)
                ->where('data', 'LIKE', '%attendance_id%')
                ->where('data', 'LIKE', '%' . $report->id . '%')
                ->first();

            if ($notification) {
                $notification->update([
                    'title'   => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'updated_at' => now(),
                ]);
            } else {
                Notification::create([
                    'user_id'      => $employeeId,
                    'user_type_id' => $typeId,
                    'title'        => $title,
                    'message'      => $message,
                    'route'        => $route,
                    'data'         => json_encode(['attendance_id' => $report->id]),
                ]);
            }
        }
    }
}
