<?php

namespace App\Http\Controllers\Head;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\ChangeOff;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\OvertimeList;
use App\Models\PayrollCutOff;
use App\Models\Status;
use App\Models\Undertime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use Maatwebsite\Excel\Facades\Excel;

class PayrollCutOffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cutoffs = PayrollCutOff::join('statuses', 'payroll_cut_offs.status_id', '=', 'statuses.id')
            ->select('payroll_cut_offs.*', 'statuses.name as status_name')
            ->where('payroll_cut_offs.status_id', 1)
            ->withCount(['attendanceEmployees as attendances_count' => function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('payroll_cut_offs.name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/Head/PayrollCutOff', [
            'cutoffs' => $cutoffs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function attendancePage(Request $request, $id)
{
    $user = $request->user();
    $cutoff = PayrollCutOff::findOrFail($id);

    // Filter employees to only show those in the head's department
    $employees = User::with(['department', 'position', 'status'])
        ->where('department_id', $user->department_id)
        ->get();

    $reports = AttendanceEmployee::where('payroll_cut_off_id', $id)
        ->join('users', 'attendance_employees.user_id', '=', 'users.id')
        // Force filter by the authenticated head's department_id
        ->where('attendance_employees.department_id', $user->department_id)
        ->with([
            'approvalStatuses.user',
            'approvalStatuses.status',
            'attendances',
            'leaderStatus.status',
            'hrStatus.status',
            'user.department',
            'department'
        ])
        ->select(
            'attendance_employees.*',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as employee_name")
        )
        ->when($request->search, function ($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                ->orWhere('users.last_name', 'like', "%{$search}%");
            });
        })
        ->when($request->employee_id, fn($q, $empId) => $q->where('attendance_employees.user_id', $empId))
        // Department filter is removed as it is now hard-coded to the head's department
        ->when($request->status, fn($q, $statusId) => $q->where('attendance_employees.hr_status_id', $statusId))
        ->orderBy('attendance_employees.created_at', 'desc')
        ->paginate(10)
        ->withQueryString()
        ->through(function ($item) use ($cutoff) {

            $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
            $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

            // ---------------- Attendance Data ----------------
            $attendances = $item->attendances->filter(fn($log) =>
                $log->attendance_date >= $cutoff->from_cutoff_date &&
                $log->attendance_date <= $cutoff->to_cutoff_date
            )->values();

            // ---------------- Holiday Setup ----------------
            $holidays = Holiday::where('status_id', 1)->get();
            $holidayMap = $holidays->mapWithKeys(fn($h) => [
                Carbon::parse($h->date)->toDateString() => strtolower($h->type)
            ])->toArray();

            // ---------------- Late calculation (Split Windows) ----------------
            $totalLateMinutes = 0;

            foreach ($attendances as $log) {
                if (!$log->time_in) continue;

                $attendanceDate = $log->attendance_date;
                $timeIn = Carbon::parse($attendanceDate . ' ' . $log->time_in);

                $morningStart   = Carbon::parse($attendanceDate . ' 08:00:00');
                $morningGrace   = Carbon::parse($attendanceDate . ' 08:05:59');
                $morningEnd     = Carbon::parse($attendanceDate . ' 12:00:00');

                $afternoonStart = Carbon::parse($attendanceDate . ' 13:00:00');
                $afternoonGrace = Carbon::parse($attendanceDate . ' 13:05:59');
                $afternoonEnd   = Carbon::parse($attendanceDate . ' 17:00:00');

                if ($timeIn->greaterThan($morningGrace) && $timeIn->lessThanOrEqualTo($morningEnd)) {
                    $late = $morningStart->diffInMinutes($timeIn);
                    $totalLateMinutes += $late;
                }
                elseif ($timeIn->greaterThan($afternoonGrace) && $timeIn->lessThanOrEqualTo($afternoonEnd)) {
                    $late = $afternoonStart->diffInMinutes($timeIn);
                    $totalLateMinutes += $late;
                }
            }

            // ---------------- Leaves ----------------
            $leaves = Leave::with('approvalStatuses.user')
                ->where('user_id', $item->user_id)
                ->where(function ($q) use ($cutoff) {
                    $q->whereBetween('start_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
                    ->orWhereBetween('end_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date]);
                })->get();

            $paidLeaveDays = 0;
            $unpaidLeaveApproved = 0;

            foreach ($leaves as $leave) {
                $leader = $leave->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $hr = $leave->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                if ($leader?->status_id == 7 && $hr?->status_id == 7) {
                    $start = max(Carbon::parse($leave->start_date), Carbon::parse($cutoff->from_cutoff_date));
                    $end = min(Carbon::parse($leave->end_date), Carbon::parse($cutoff->to_cutoff_date));
                    $count = $start->diffInDays($end) + 1;
                    $leave->with_pay ? $paidLeaveDays += $count : $unpaidLeaveApproved += $count;
                }
            }

            // ---------------- Rest Day Logic ----------------
            $weekDayMap = ['monday'=>3, 'tuesday'=>4, 'wednesday'=>5, 'thursday'=>6, 'friday'=>7, 'saturday'=>8, 'sunday'=>9];
            $idToNameMap = array_flip($weekDayMap);
            $restDayName = 'N/A';

            $approvedChanges = ChangeOff::with(['label', 'approvalStatuses.user'])
                ->where('user_id', $item->user_id)
                ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 1)))
                ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 3)))
                ->get()
                ->map(function($co) {
                    $hrStatus = $co->approvalStatuses->first(fn($s) => $s->status_id == 7 && ($s->user->user_type_id ?? null) == 1);
                    $co->hr_ref_date = $hrStatus ? Carbon::parse($hrStatus->created_at) : null;
                    return $co;
                })->filter(fn($co) => $co->hr_ref_date !== null)->sortByDesc('hr_ref_date');

            if ($approvedChanges->isNotEmpty() && $approvedChanges->first()->label) {
                $restDayName = ucfirst($idToNameMap[$approvedChanges->first()->label->new_day_id] ?? 'N/A');
            }

            // ---------------- CALCULATE MINUTES ----------------
            $dayInMinutes = 8 * 60;
            $grandTotalMinutes = 0;
            $absentDays = 0;
            $presentDaysCount = 0;

            $startDate = Carbon::parse($cutoff->from_cutoff_date);
            $endDate = Carbon::parse($cutoff->to_cutoff_date);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $dayId = $weekDayMap[strtolower($date->format('l'))] ?? null;

                $log = $attendances->first(fn($a) => Carbon::parse($a->attendance_date)->toDateString() === $dateStr);
                $isPresent = ($log && !is_null($log->time_in));
                $isHoliday = isset($holidayMap[$dateStr]);
                $holidayType = $isHoliday ? $holidayMap[$dateStr] : null;
                $isLeave = $leaves->first(fn($l) => $dateStr >= $l->start_date && $dateStr <= $l->end_date);

                $activeSchedule = $approvedChanges->first(fn($co) => $date->greaterThanOrEqualTo($co->hr_ref_date->startOfDay())) ?: $approvedChanges->last();
                $isRestDay = ($activeSchedule && $activeSchedule->label && $activeSchedule->label->new_day_id == $dayId);

                if ($isPresent) {
                    $presentDaysCount++;
                    if ($isHoliday) {
                        if ($holidayType === 'regular') {
                            $grandTotalMinutes += ($dayInMinutes * 2);
                        } else {
                            $grandTotalMinutes += ($dayInMinutes * 1.3);
                        }
                    } else {
                        $grandTotalMinutes += $dayInMinutes;
                    }
                } else {
                    if ($isHoliday) {
                        if ($holidayType === 'regular') {
                            $grandTotalMinutes += $dayInMinutes;
                        }
                    } elseif ($isLeave) {
                        // Handled by paid leaves
                    } elseif ($isRestDay) {
                        // Rest day
                    } else {
                        $absentDays++;
                    }
                }
            }

            $grandTotalMinutes += ($paidLeaveDays * $dayInMinutes);

            // ---------------- Overtime ----------------
            $totalOvertimeMinutes = 0;
            $overtimeLists = OvertimeList::with('overtime.approvalStatuses.user')
                ->whereHas('overtime', fn($q) => $q->where('user_id', $item->user_id))
                ->whereBetween('overtime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

            foreach ($overtimeLists as $ot) {
                $leader = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $hr = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                if ($leader?->status_id == 7 && $hr?->status_id == 7) {
                    $totalOvertimeMinutes += $ot->additional_hours_worked * 60;
                }
            }
            $grandTotalMinutes += $totalOvertimeMinutes;

            // ---------------- Undertime ----------------
            $totalUndertimeMinutes = 0;
            $undertimes = Undertime::with('approvalStatuses.user')
                ->where('user_id', $item->user_id)
                ->whereBetween('undertime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

            foreach ($undertimes as $ut) {
                $leader = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $hr = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                if ($leader?->status_id == 7 && $hr?->status_id == 7) {
                    $totalUndertimeMinutes += (int)$ut->total_time;
                }
            }
            $grandTotalMinutes -= $totalUndertimeMinutes;
            $grandTotalMinutes -= ($unpaidLeaveApproved * $dayInMinutes);

            if ($grandTotalMinutes < 0) $grandTotalMinutes = 0;

            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'employee_name' => $item->employee_name,
                'rest_name' => $restDayName,
                'user' => ['department' => ['id' => $item->department?->id, 'name' => $item->department?->name ?? 'N/A']],
                'late_minutes' => $totalLateMinutes,
                'paid_leaves' => $paidLeaveDays,
                'unpaid_leaves' => ($absentDays + $unpaidLeaveApproved),
                'days_count' => $presentDaysCount,
                'holiday_count' => count(array_filter($holidayMap, fn($date) => $date >= $cutoff->from_cutoff_date && $date <= $cutoff->to_cutoff_date, ARRAY_FILTER_USE_KEY)),
                'overtime_hours' => ['h' => floor($totalOvertimeMinutes / 60), 'm' => $totalOvertimeMinutes % 60],
                'undertime_hours' => ['h' => floor($totalUndertimeMinutes / 60), 'm' => $totalUndertimeMinutes % 60],
                'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                'total_summary' => [
                    'days' => floor($grandTotalMinutes / $dayInMinutes),
                    'hours' => floor(($grandTotalMinutes % $dayInMinutes) / 60),
                    'minutes' => $grandTotalMinutes % 60
                ],
                'attendances' => $attendances->map(fn ($log) => [
                    'attendance_date' => $log->attendance_date,
                    'time_in' => $log->time_in,
                    'time_out' => $log->time_out,
                ]),
            ];
        });

    return Inertia::render('management/Head/PayrollCutOffList', [
        'cutoff' => $cutoff,
        'employees' => $employees,
        // Department data removed as filtering by other departments is restricted
        'reports' => $reports,
        'filters' => $request->only(['search', 'status', 'employee_id'])
    ]);
}

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $report = AttendanceEmployee::findOrFail($id);

        // Update or Create the approval status for this Head/Head
        DB::table('attendance_statuses')->updateOrInsert(
            [
                'attendance_employee_id' => $report->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status_id' => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back();
    }
}
