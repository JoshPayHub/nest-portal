<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\PayrollCutOff;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollCutOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cutoffs = PayrollCutOff::with(['attendanceEmployees' => function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->with([
                      'attendances',
                      'approvalStatuses.status',
                      'approvalStatuses.user'
                  ]);
            }])
            ->latest()
            ->paginate(10)
            ->through(fn($cutoff) => $this->formatCutoffForUser($cutoff, $user));

        return Inertia::render('management/Employee/PayrollCutOff', [
            'cutoffs' => $cutoffs,
            'filters' => $request->only(['search']),
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function attendancePage(Request $request, $id)
    {
        $user = $request->user();
        $cutoff = PayrollCutOff::findOrFail($id);

        $attendance = AttendanceEmployee::with([
                'attendances',
                'approvalStatuses.status',
                'approvalStatuses.user'
            ])
            ->where('user_id', $user->id)
            ->where('payroll_cut_off_id', $id)
            ->first();

        $userTypeId = $request->user()->user_type_id;

        $routeMap = [
            2 => 'employee.payrollcutoffs.index',
            3 => 'head.payrollcutoffs.index',
        ];

        $routeName = $routeMap[$userTypeId];

        if ($attendance) {
            $approvals = $attendance->approvalStatuses;

            $hasApproval = $approvals->contains('status_id', 7);
            $hasRejection = $approvals->contains(fn($a) => in_array($a->status_id, [8, 9]));

            if ($hasApproval && !$hasRejection) {
                return redirect()->route($routeName)
                    ->with('error', 'Attendance is currently under process or approved. Access denied.');
            }
        }

        // Generate dates for the form
        $dates = [];
        if ($attendance && $attendance->attendances->isNotEmpty()) {
            $dates = $attendance->attendances->map(fn($att) => [
                'date' => $att->attendance_date,
                'time_in' => $att->time_in,
                'time_out' => $att->time_out,
            ])->toArray();
        } else {
            $start = Carbon::parse($cutoff->from_cutoff_date);
            $end = Carbon::parse($cutoff->to_cutoff_date);
            while ($start <= $end) {
                $dates[] = [
                    'date' => $start->toDateString(),
                    'time_in' => null,
                    'time_out' => null,
                ];
                $start->addDay();
            }
        }

        return Inertia::render('management/Employee/AttendanceForm', [
            'authUser' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'department' => $user->department?->name ?? 'N/A',
                'position' => $user->position?->name ?? 'N/A',
                'department_position' => $user->department?->name . ' / ' . $user->position?->name ,
            ],
            'cutoff' => [
                'id' => $cutoff->id,
                'name' => $cutoff->name === 'first_cutoff' ? 'First Cut Off' : 'Second Cut Off',
                'from_cutoff_date' => $cutoff->from_cutoff_date,
                'to_cutoff_date' => $cutoff->to_cutoff_date,
            ],
            'attendanceData' => [
                'attendance_employee_id' => $attendance?->id ?? null,
                'dates' => $dates,
                'approvals' => $attendance?->approvalStatuses ?? [],
            ],
            'isEditing' => (bool)$attendance,
            'isLocked' => false,
            'auth_user_type_id' => auth()->user()->user_type_id,
        ]);
    }

    public function store(Request $request, $id)
    {
        $user = $request->user();
        $request->validate([
            'dates' => 'required|array',
            'dates.*.date' => 'required|date',
            'dates.*.time_in' => 'nullable',
            'dates.*.time_out' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $attendance = AttendanceEmployee::updateOrCreate(
                ['user_id' => $user->id, 'payroll_cut_off_id' => $id],
                [
                    'department_id' => $user->department_id,
                    'position_id' => $user->position_id
                ]
            );

            // Clean up and re-insert
            $attendance->attendances()->delete();
            foreach ($request->dates as $dateData) {
                $attendance->attendances()->create([
                    'attendance_date' => $dateData['date'],
                    'time_in' => $dateData['time_in'],
                    'time_out' => $dateData['time_out'],
                ]);
            }

            // Reset approvals to Pending (4)
            $attendance->approvalStatuses()->update(['status_id' => 4]);

            $userTypeId = $request->user()->user_type_id;

            $routeMap = [
                2 => 'employee.payrollcutoffs.index',
                3 => 'head.payrollcutoffs.index',
            ];

            $routeName = $routeMap[$userTypeId];

            DB::commit();
            return redirect()->route($routeName)->with('success', 'Attendance updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function formatCutoffForUser($cutoff, $user)
    {
        $attendance = $cutoff->attendanceEmployees->first();

        if (!$attendance) {
            return [
                'id' => $cutoff->id,
                'name' => $cutoff->name,
                'from_cutoff_date' => $cutoff->from_cutoff_date,
                'to_cutoff_date' => $cutoff->to_cutoff_date,
                'leader_status_name' => 'No Record',
                'hr_status_name' => 'No Record',
                'attendance_list' => [],
                'has_record' => false,
            ];
        }

        $leader = $attendance->approvalStatuses->first(fn($a) => optional($a->user)->user_type_id == 3);
        $hr = $attendance->approvalStatuses->first(fn($a) => optional($a->user)->user_type_id == 1);

        return [
            'id' => $cutoff->id,
            'name' => $cutoff->name,
            'from_cutoff_date' => $cutoff->from_cutoff_date,
            'to_cutoff_date' => $cutoff->to_cutoff_date,
            'leader_status_name' => $leader?->status?->name ?? 'Pending',
            'hr_status_name' => $hr?->status?->name ?? 'Pending',
            'attendance_list' => $attendance->attendances,
            'has_record' => true,
        ];
    }
}
