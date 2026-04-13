<?php

namespace App\Http\Controllers\Hr;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\ChangeOff;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Overtime;
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
        $cutoffs = PayrollCutOff::join('statuses', 'payroll_cut_offs.status_id', '=', 'statuses.id')
            ->select('payroll_cut_offs.*', 'statuses.name as status_name')
            ->withCount('attendanceEmployees as attendances_count')
            ->when($request->search, function ($query, $search) {
                $query->where('payroll_cut_offs.name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/HR/PayrollCutOff', [
            'cutoffs' => $cutoffs,
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|in:first_cutoff,second_cutoff',
            'from_cutoff_date' => 'required|date',
            'to_cutoff_date' => 'required|date|after_or_equal:from_cutoff_date',
            'status_id' => 'required|exists:statuses,id'
        ]);

        $this->checkForOverlap($validated['from_cutoff_date'], $validated['to_cutoff_date']);

        PayrollCutOff::create($validated);
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $cutoff = PayrollCutOff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|in:first_cutoff,second_cutoff',
            'from_cutoff_date' => 'required|date',
            'to_cutoff_date' => 'required|date|after_or_equal:from_cutoff_date',
            'status_id' => 'required|exists:statuses,id'
        ]);

        $this->checkForOverlap($validated['from_cutoff_date'], $validated['to_cutoff_date'], $id);

        $cutoff->update($validated);
        return redirect()->back();
    }

    private function checkForOverlap($from, $to, $excludeId = null)
    {
        $overlap = PayrollCutOff::where(function ($query) use ($from, $to) {
            $query->whereBetween('from_cutoff_date', [$from, $to])
                  ->orWhereBetween('to_cutoff_date', [$from, $to])
                  ->orWhere(function ($q) use ($from, $to) {
                      $q->where('from_cutoff_date', '<=', $from)
                        ->where('to_cutoff_date', '>=', $to);
                  });
        })
        ->when($excludeId, function ($query) use ($excludeId) {
            $query->where('id', '!=', $excludeId);
        })
        ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'from_cutoff_date' => 'The selected date range overlaps with an existing cutoff period.'
            ]);
        }
    }

public function attendancePage(Request $request, $id)
{
    $cutoff = PayrollCutOff::findOrFail($id);
    $employees = User::with(['department', 'position', 'status'])->get();

    $reports = AttendanceEmployee::where('payroll_cut_off_id', $id)
        ->join('users', 'attendance_employees.user_id', '=', 'users.id')
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
        ->when($request->department, fn($q, $deptId) => $q->where('attendance_employees.department_id', $deptId))
        ->when($request->status, fn($q, $statusId) => $q->where('attendance_employees.hr_status_id', $statusId))
        ->orderBy('attendance_employees.created_at', 'desc')
        ->paginate(10)
        ->withQueryString()
        ->through(function ($item) use ($cutoff) {

            $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
            $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

            // ---------------- Attendance ----------------
            $attendances = $item->attendances->filter(fn($log) =>
                $log->attendance_date >= $cutoff->from_cutoff_date &&
                $log->attendance_date <= $cutoff->to_cutoff_date
            )->values();

            // ---------------- Holidays ----------------
            $holidayMap = Holiday::where('status_id', 1)->get()
                ->mapWithKeys(fn($h) => [
                    Carbon::parse($h->date)->toDateString() => strtolower($h->type)
                ])->toArray();

            // ---------------- Counters ----------------
            $regularHolidayCount = 0;
            $specialHolidayCount = 0;
            $rdSpecialHolidayCount = 0;
            $rdRegularHolidayCount = 0;

            // ---------------- Late ----------------
            $totalLateMinutes = 0;

            foreach ($attendances as $log) {
                if (!$log->time_in) continue;

                $attendanceDate = $log->attendance_date;
                $timeIn = Carbon::parse($attendanceDate . ' ' . $log->time_in);

                $morningStart = Carbon::parse($attendanceDate . ' 08:00:00');
                $morningGrace = Carbon::parse($attendanceDate . ' 08:05:59');
                $morningEnd   = Carbon::parse($attendanceDate . ' 12:00:00');

                $afternoonStart = Carbon::parse($attendanceDate . ' 13:00:00');
                $afternoonGrace = Carbon::parse($attendanceDate . ' 13:05:59');
                $afternoonEnd   = Carbon::parse($attendanceDate . ' 17:00:00');

                if ($timeIn->greaterThan($morningGrace) && $timeIn->lessThanOrEqualTo($morningEnd)) {
                    $totalLateMinutes += $morningStart->diffInMinutes($timeIn);
                } elseif ($timeIn->greaterThan($afternoonGrace) && $timeIn->lessThanOrEqualTo($afternoonEnd)) {
                    $totalLateMinutes += $afternoonStart->diffInMinutes($timeIn);
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

                    $leave->with_pay
                        ? $paidLeaveDays += $count
                        : $unpaidLeaveApproved += $count;
                }
            }

            // ---------------- Rest Day (APPROVED ONLY) ----------------
            $weekDayMap = ['monday'=>3,'tuesday'=>4,'wednesday'=>5,'thursday'=>6,'friday'=>7,'saturday'=>8,'sunday'=>9];

            $approvedChanges = ChangeOff::with(['label', 'approvalStatuses.user'])
                ->where('user_id', $item->user_id)
                ->whereHas('approvalStatuses', fn($q) =>
                    $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 1))
                )
                ->whereHas('approvalStatuses', fn($q) =>
                    $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 3))
                )
                ->get()
                ->map(function($co) {
                    $hrStatus = $co->approvalStatuses
                        ->first(fn($s) => $s->status_id == 7 && ($s->user->user_type_id ?? null) == 1);

                    $co->hr_ref_date = $hrStatus ? Carbon::parse($hrStatus->created_at) : null;
                    return $co;
                })
                ->filter(fn($co) => $co->hr_ref_date !== null)
                ->sortByDesc('hr_ref_date');

            $restDayId = $approvedChanges->first()?->label?->new_day_id;

            // ---------------- Payroll Loop ----------------
            $dayInMinutes = 8 * 60;
            $grandTotalMinutes = 0;
            $absentDays = 0;
            $presentDaysCount = 0;

            $startDate = Carbon::parse($cutoff->from_cutoff_date);
            $endDate = Carbon::parse($cutoff->to_cutoff_date);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

                $dateStr = $date->toDateString();
                $dayId = $weekDayMap[strtolower($date->format('l'))] ?? null;

                $log = $attendances->first(fn($a) =>
                    Carbon::parse($a->attendance_date)->toDateString() === $dateStr
                );

                $isPresent = ($log && !is_null($log->time_in));
                $isHoliday = isset($holidayMap[$dateStr]);
                $holidayType = $holidayMap[$dateStr] ?? null;

                $isRestDay = ($restDayId == $dayId);
                $isLeave = $leaves->first(fn($l) => $dateStr >= $l->start_date && $dateStr <= $l->end_date);

                if ($isHoliday) {
                    if ($holidayType === 'regular') {
                        if ($isPresent) {
                            $isRestDay ? $rdRegularHolidayCount++ : $regularHolidayCount++;
                        } elseif (!$isRestDay) {
                            $regularHolidayCount++;
                        }
                    } else {
                        if ($isPresent) {
                            $isRestDay ? $rdSpecialHolidayCount++ : $specialHolidayCount++;
                        }
                    }
                }

                if ($isPresent) {
                    $presentDaysCount++;
                    $grandTotalMinutes += $dayInMinutes;
                } else {
                    if (!$isHoliday && !$isLeave && !$isRestDay) {
                        $absentDays++;
                    }
                }
            }

            // ---------------- OVERTIME BREAKDOWN ----------------
            $regularOT = 0;
            $rdOT = 0;
            $regularHolidayOT = 0;
            $specialHolidayOT = 0;
            $rdRegularHolidayOT = 0;
            $rdSpecialHolidayOT = 0;

            $overtimeLists = OvertimeList::with('overtime.approvalStatuses.user')
                ->whereHas('overtime', fn($q) => $q->where('user_id', $item->user_id))
                ->whereBetween('overtime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
                ->get();

            foreach ($overtimeLists as $ot) {

                $l = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $h = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);

                if (!($l?->status_id == 7 && $h?->status_id == 7)) continue;

                $dateStr = Carbon::parse($ot->overtime_date)->toDateString();
                $dayId = $weekDayMap[strtolower(Carbon::parse($dateStr)->format('l'))] ?? null;

                $isRestDay = ($restDayId == $dayId);
                $isHoliday = isset($holidayMap[$dateStr]);
                $holidayType = $holidayMap[$dateStr] ?? null;

                $minutes = $ot->additional_hours_worked * 60;

                if (!$isHoliday && !$isRestDay) {
                    $regularOT += $minutes;
                } elseif (!$isHoliday && $isRestDay) {
                    $rdOT += $minutes;
                } elseif ($isHoliday && !$isRestDay) {
                    if ($holidayType === 'regular') {
                        $regularHolidayOT += $minutes;
                    } else {
                        $specialHolidayOT += $minutes;
                    }
                } elseif ($isHoliday && $isRestDay) {
                    if ($holidayType === 'regular') {
                        $rdRegularHolidayOT += $minutes;
                    } else {
                        $rdSpecialHolidayOT += $minutes;
                    }
                }
            }

            // ---------------- UNDERTIME ----------------
            $totalUndertimeMinutes = 0;

            $undertimes = Undertime::with('approvalStatuses.user')
                ->where('user_id', $item->user_id)
                ->whereBetween('undertime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
                ->get();

            foreach ($undertimes as $ut) {
                $l = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $h = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);

                if ($l?->status_id == 7 && $h?->status_id == 7) {
                    $totalUndertimeMinutes += (int)$ut->total_time;
                }
            }

            $totalOvertimeMinutes =
                $regularOT + $rdOT + $regularHolidayOT +
                $specialHolidayOT + $rdRegularHolidayOT + $rdSpecialHolidayOT;

            $grandTotalMinutes += $totalOvertimeMinutes;
            $grandTotalMinutes -= $totalUndertimeMinutes;
            $grandTotalMinutes -= ($unpaidLeaveApproved * $dayInMinutes);

            if ($grandTotalMinutes < 0) $grandTotalMinutes = 0;

            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'employee_name' => $item->employee_name,
                'department_name' => $item->department?->name ?? 'N/A',
                'hr_status_id' => $item->hr_status_id,
                'rest_name' => $restDayId ?? 'N/A',

                'late_minutes' => $totalLateMinutes,
                'paid_leaves' => $paidLeaveDays,
                'unpaid_leaves' => ($absentDays + $unpaidLeaveApproved),
                'days_count' => $presentDaysCount,

                'regular_holiday_count' => $regularHolidayCount,
                'special_holiday_count' => $specialHolidayCount,
                'rd_special_holiday_count' => $rdSpecialHolidayCount,
                'rd_regular_holiday_count' => $rdRegularHolidayCount,

                // ✅ ALL OT TYPES
                'regular_overtime_hours' => ['h'=>floor($regularOT/60),'m'=>$regularOT%60],
                'rd_overtime_hours' => ['h'=>floor($rdOT/60),'m'=>$rdOT%60],
                'regular_holiday_overtime_hours' => ['h'=>floor($regularHolidayOT/60),'m'=>$regularHolidayOT%60],
                'special_overtime_hours' => ['h'=>floor($specialHolidayOT/60),'m'=>$specialHolidayOT%60],
                'rd_regular_overtime_hours' => ['h'=>floor($rdRegularHolidayOT/60),'m'=>$rdRegularHolidayOT%60],
                'rd_special_overtime_hours' => ['h'=>floor($rdSpecialHolidayOT/60),'m'=>$rdSpecialHolidayOT%60],

                'undertime_hours' => [
                    'h' => floor($totalUndertimeMinutes / 60),
                    'm' => $totalUndertimeMinutes % 60
                ],

                'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                'hr_status_name' => $hrEntry?->status?->name ?? 'Pending',

                'total_summary' => [
                    'days' => floor($grandTotalMinutes / $dayInMinutes),
                    'hours' => floor(($grandTotalMinutes % $dayInMinutes) / 60),
                    'minutes' => $grandTotalMinutes % 60
                ],
            ];
        });

    return Inertia::render('management/HR/PayrollCutOffList', [
        'cutoff' => $cutoff,
        'employees' => $employees,
        'departments' => Department::all(),
        'reports' => $reports,
        'filters' => $request->only(['search', 'department', 'status', 'employee_id'])
    ]);
}
    public function exportAttendance(Request $request, $id)
{
    $cutoff = PayrollCutOff::findOrFail($id);

    $reports = AttendanceEmployee::where('attendance_employees.payroll_cut_off_id', $id)
        ->join('users', 'attendance_employees.user_id', '=', 'users.id')
        ->with([
            'approvalStatuses.user', 'approvalStatuses.status', 'attendances',
            'leaderStatus.status', 'hrStatus.status', 'user.department', 'department'
        ])
        ->select('attendance_employees.*', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as employee_name"))
        ->when($request->search, function ($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")->orWhere('users.last_name', 'like', "%{$search}%");
            });
        })
        ->when($request->employee_id, fn($q, $empId) => $q->where('attendance_employees.user_id', $empId))
        ->when($request->department, fn($q, $deptId) => $q->where('attendance_employees.department_id', $deptId))
        ->when($request->status, fn($q, $statusId) => $q->where('attendance_employees.hr_status_id', $statusId))
        ->get()
        ->map(function ($item) use ($cutoff) {

            $attendances = $item->attendances->filter(fn($log) =>
                $log->attendance_date >= $cutoff->from_cutoff_date &&
                $log->attendance_date <= $cutoff->to_cutoff_date
            )->values();

            $holidays = Holiday::where('status_id', 1)->get();
            $holidayMap = $holidays->mapWithKeys(fn($h) => [
                Carbon::parse($h->date)->toDateString() => strtolower($h->type)
            ])->toArray();

            // --- ADDED REST DAY LOGIC TO MATCH ATTENDANCEPAGE ---
            $weekDayMap = ['monday'=>3, 'tuesday'=>4, 'wednesday'=>5, 'thursday'=>6, 'friday'=>7, 'saturday'=>8, 'sunday'=>9];
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
            // ---------------------------------------------------

            $totalLateMinutes = 0;
            foreach ($attendances as $log) {
                if (!$log->time_in) continue;
                $attendanceDate = $log->attendance_date;
                $timeIn = Carbon::parse($attendanceDate . ' ' . $log->time_in);
                $morningGrace = Carbon::parse($attendanceDate . ' 08:05:59');
                $morningStart = Carbon::parse($attendanceDate . ' 08:00:00');
                $morningEnd = Carbon::parse($attendanceDate . ' 12:00:00');
                $afternoonGrace = Carbon::parse($attendanceDate . ' 13:05:59');
                $afternoonStart = Carbon::parse($attendanceDate . ' 13:00:00');
                $afternoonEnd = Carbon::parse($attendanceDate . ' 17:00:00');

                if ($timeIn->greaterThan($morningGrace) && $timeIn->lessThanOrEqualTo($morningEnd)) {
                    $totalLateMinutes += $morningStart->diffInMinutes($timeIn);
                } elseif ($timeIn->greaterThan($afternoonGrace) && $timeIn->lessThanOrEqualTo($afternoonEnd)) {
                    $totalLateMinutes += $afternoonStart->diffInMinutes($timeIn);
                }
            }

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

            $dayInMinutes = 8 * 60;
            $grandTotalMinutes = 0;
            $absentDays = 0;
            $startDate = Carbon::parse($cutoff->from_cutoff_date);
            $endDate = Carbon::parse($cutoff->to_cutoff_date);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $dayId = $weekDayMap[strtolower($date->format('l'))] ?? null; // Added dayId

                $log = $attendances->first(fn($a) => Carbon::parse($a->attendance_date)->toDateString() === $dateStr);
                $isPresent = ($log && !is_null($log->time_in));
                $isHoliday = isset($holidayMap[$dateStr]);
                $holidayType = $isHoliday ? $holidayMap[$dateStr] : null;
                $isLeave = $leaves->first(fn($l) => $dateStr >= $l->start_date && $dateStr <= $l->end_date); // Added isLeave check

                // --- ADDED REST DAY CHECK ---
                $activeSchedule = $approvedChanges->first(fn($co) => $date->greaterThanOrEqualTo($co->hr_ref_date->startOfDay())) ?: $approvedChanges->last();
                $isRestDay = ($activeSchedule && $activeSchedule->label && $activeSchedule->label->new_day_id == $dayId);
                // ----------------------------

                if ($isPresent) {
                    if ($isHoliday) {
                        $grandTotalMinutes += ($holidayType === 'regular') ? ($dayInMinutes * 2) : ($dayInMinutes * 1.3);
                    } else {
                        $grandTotalMinutes += $dayInMinutes;
                    }
                } else {
                    if ($isHoliday) {
                        if ($holidayType === 'regular') { $grandTotalMinutes += $dayInMinutes; }
                    } elseif ($isLeave) {
                        // Handled via $paidLeaveDays logic
                    } elseif ($isRestDay) {
                        // No pay, no absence (MATCHES YOUR TABLE)
                    } else {
                        $absentDays++;
                    }
                }
            }

            $grandTotalMinutes += ($paidLeaveDays * $dayInMinutes);

            $totalOvertimeMinutes = 0;
            $overtimeLists = OvertimeList::with('overtime.approvalStatuses.user')
                ->whereHas('overtime', fn($q) => $q->where('user_id', $item->user_id))
                ->whereBetween('overtime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

            foreach ($overtimeLists as $ot) {
                $l = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $h = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                if ($l?->status_id == 7 && $h?->status_id == 7) {
                    $totalOvertimeMinutes += $ot->additional_hours_worked * 60;
                }
            }
            $grandTotalMinutes += $totalOvertimeMinutes;

            $totalUndertimeMinutes = 0;
            $undertimes = Undertime::with('approvalStatuses.user')
                ->where('user_id', $item->user_id)
                ->whereBetween('undertime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

            foreach ($undertimes as $ut) {
                $l = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                $h = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                if ($l?->status_id == 7 && $h?->status_id == 7) {
                    $totalUndertimeMinutes += (int)$ut->total_time;
                }
            }
            $grandTotalMinutes -= $totalUndertimeMinutes;
            $grandTotalMinutes -= ($unpaidLeaveApproved * $dayInMinutes);

            return [
                'employee_name' => $item->employee_name,
                'department_name' => $item->department?->name ?? 'N/A',
                'late_minutes' => $totalLateMinutes,
                'paid_leaves' => $paidLeaveDays,
                'unpaid_leaves' => ($absentDays + $unpaidLeaveApproved),
                'holiday_count' => count(array_filter($holidayMap, fn($date) => $date >= $cutoff->from_cutoff_date && $date <= $cutoff->to_cutoff_date, ARRAY_FILTER_USE_KEY)),
                'overtime_hours' => ['h' => floor($totalOvertimeMinutes / 60), 'm' => $totalOvertimeMinutes % 60],
                'undertime_hours' => ['h' => floor($totalUndertimeMinutes / 60), 'm' => $totalUndertimeMinutes % 60],
                'total_summary' => [
                    'days' => floor($grandTotalMinutes / (8 * 60)),
                    'hours' => floor(($grandTotalMinutes % (8 * 60)) / 60),
                    'minutes' => $grandTotalMinutes % 60
                ],
            ];
        });

        $from = Carbon::parse($cutoff->from_cutoff_date)->format('F j, Y');
        $to = Carbon::parse($cutoff->to_cutoff_date)->format('F j, Y');
        $dateRangeString = $from . ' to ' . $to;

    return Excel::download(
        new AttendanceExport($reports, $dateRangeString),
        'Attendance_Report_' . $cutoff->from_cutoff_date . '.xlsx'
    );
}


    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $report = AttendanceEmployee::findOrFail($id);

        // Update or Create the approval status for this Head/HR
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
