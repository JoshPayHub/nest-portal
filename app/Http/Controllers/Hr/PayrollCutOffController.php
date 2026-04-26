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

                // ---------------- Leaves (FIXED LOGIC) ----------------
                $leaves = Leave::with('approvalStatuses.user')
                    ->where('user_id', $item->user_id)
                    ->where(function ($q) use ($cutoff) {
                        $q->whereBetween('start_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
                        ->orWhereBetween('end_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
                        // Also cover leaves that start before and end after the cutoff
                        ->orWhere(function($sub) use ($cutoff) {
                            $sub->where('start_date', '<=', $cutoff->from_cutoff_date)
                                ->where('end_date', '>=', $cutoff->to_cutoff_date);
                        });
                    })->get();

                $paidLeaveDays = 0;
                $unpaidLeaveApproved = 0;
                $approvedLeaveDates = [];

                foreach ($leaves as $leave) {
                    $leader = $leave->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                    $hr = $leave->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);

                    // ONLY count if BOTH approved
                    if ($leader?->status_id == 7 && $hr?->status_id == 7) {
                        $start = max(Carbon::parse($leave->start_date), Carbon::parse($cutoff->from_cutoff_date));
                        $end = min(Carbon::parse($leave->end_date), Carbon::parse($cutoff->to_cutoff_date));

                        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                            $dateStr = $d->toDateString();
                            $approvedLeaveDates[] = $dateStr;

                            if ($leave->with_pay) {
                                $paidLeaveDays++;
                            } else {
                                $unpaidLeaveApproved++;
                            }
                        }
                    }
                }

            // ---------------- Rest Day (TIMELINE BASED) ----------------
            $weekDayMap = ['monday'=>3,'tuesday'=>4,'wednesday'=>5,'thursday'=>6,'friday'=>7,'saturday'=>8,'sunday'=>9];

            // 1. Get all approved changes sorted by the date HR approved them
            $restDayTimeline = ChangeOff::with(['label', 'approvalStatuses.user'])
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

                    // This is the date the new rest day becomes "active"
                    $co->active_from = $hrStatus ? Carbon::parse($hrStatus->created_at)->toDateString() : null;
                    return $co;
                })
                ->filter(fn($co) => $co->active_from !== null)
                ->sortBy('active_from');

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

                // 2. FIND THE REST DAY ACTIVE FOR THIS SPECIFIC DATE
                // We look for the latest approval that is LESS THAN OR EQUAL to the current loop date
                $activeChange = $restDayTimeline->filter(fn($co) => $co->active_from <= $dateStr)->last();

                // If no approved change exists yet for this date, you might want a default (e.g., Sunday = 9)
                $currentRestDayId = $activeChange ? $activeChange->label->new_day_id : 9;

                $log = $attendances->first(fn($a) =>
                    Carbon::parse($a->attendance_date)->toDateString() === $dateStr
                );

                $isPresent = ($log && !is_null($log->time_in));
                $isHoliday = isset($holidayMap[$dateStr]);
                $holidayType = $holidayMap[$dateStr] ?? null;

                // Use the dynamic rest day ID for this specific date
                $isRestDay = ($currentRestDayId == $dayId);
                $isApprovedLeave = in_array($dateStr, $approvedLeaveDates);

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
                        // If no attendance, not a holiday, not a rest day, AND NOT an approved leave, it's an ABSENCE
                        if (!$isHoliday && !$isRestDay && !$isApprovedLeave) {
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

                    // --- NEW DYNAMIC REST DAY LOGIC ---
                    // Look for the rest day that was active ON the date of this overtime
                    $activeChange = $restDayTimeline->filter(fn($co) => $co->active_from <= $dateStr)->last();
                    $currentRestDayId = $activeChange ? $activeChange->label->new_day_id : 9; // 9 is Sunday default

                    $isRestDay = ($currentRestDayId == $dayId);
                    // ----------------------------------

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
                // Add paid leave minutes to the total
                $grandTotalMinutes += ($paidLeaveDays * $dayInMinutes);

                if ($grandTotalMinutes < 0) $grandTotalMinutes = 0;

                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'employee_name' => $item->employee_name,
                    'department_name' => $item->department?->name ?? 'N/A',
                    'hr_status_id' => $item->hr_status_id,
                    'report_date' => $item->created_at->format('Y-m-d H:i:s'),
                    'rest_name' => $restDayId ?? 'N/A',

                    'late_minutes' => $totalLateMinutes,
                    'paid_leaves' => $paidLeaveDays,
                    'unpaid_leaves' => ($absentDays + $unpaidLeaveApproved),
                    'days_count' => $presentDaysCount,

                    'regular_holiday_count' => $regularHolidayCount,
                    'special_holiday_count' => $specialHolidayCount,
                    'rd_special_holiday_count' => $rdSpecialHolidayCount,
                    'rd_regular_holiday_count' => $rdRegularHolidayCount,

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

                    'attendances' => $attendances->map(fn ($log) => [
                        'attendance_date' => $log->attendance_date,
                        'time_in' => $log->time_in,
                        'time_out' => $log->time_out,
                    ]),

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
                // --- Rest Day Timeline Logic (UPDATED) ---
                $weekDayMap = ['monday'=>3, 'tuesday'=>4, 'wednesday'=>5, 'thursday'=>6, 'friday'=>7, 'saturday'=>8, 'sunday'=>9];

                $restDayTimeline = ChangeOff::with(['label', 'approvalStatuses.user'])
                    ->where('user_id', $item->user_id)
                    ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 1)))
                    ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 3)))
                    ->get()
                    ->map(function($co) {
                        $hrStatus = $co->approvalStatuses->first(fn($s) => $s->status_id == 7 && ($s->user->user_type_id ?? null) == 1);
                        $co->active_from = $hrStatus ? Carbon::parse($hrStatus->created_at)->toDateString() : null;
                        return $co;
                    })
                    ->filter(fn($co) => $co->active_from !== null)
                    ->sortBy('active_from');

                // --- Attendance & Holiday Map ---
                $attendances = $item->attendances->filter(fn($log) =>
                    $log->attendance_date >= $cutoff->from_cutoff_date && $log->attendance_date <= $cutoff->to_cutoff_date
                )->values();

                $holidayMap = Holiday::where('status_id', 1)->get()
                    ->mapWithKeys(fn($h) => [Carbon::parse($h->date)->toDateString() => strtolower($h->type)])
                    ->toArray();

                // --- Counters ---
                $totalLateMinutes = 0;
                $regularHolidayCount = 0;
                $specialHolidayCount = 0;
                $rdSpecialHolidayCount = 0;
                $rdRegularHolidayCount = 0;
                $presentDaysCount = 0;
                $absentDays = 0;
                $dayInMinutes = 8 * 60;
                $grandTotalMinutes = 0;

                // --- Late Calculation ---
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

                    if ($timeIn->gt($morningGrace) && $timeIn->lte($morningEnd)) {
                        $totalLateMinutes += $morningStart->diffInMinutes($timeIn);
                    } elseif ($timeIn->gt($afternoonGrace) && $timeIn->lte($afternoonEnd)) {
                        $totalLateMinutes += $afternoonStart->diffInMinutes($timeIn);
                    }
                }

                // --- Leaves ---
                $leaves = Leave::with('approvalStatuses.user')
                    ->where('user_id', $item->user_id)
                    ->where(function ($q) use ($cutoff) {
                        $q->whereBetween('start_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
                        ->orWhereBetween('end_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date]);
                    })->get();

                $paidLeaveDays = 0;
                $unpaidLeaveApproved = 0;
                $approvedLeaveDates = [];
                foreach ($leaves as $leave) {
                    $lStatus = $leave->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                    $hStatus = $leave->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                    if ($lStatus?->status_id == 7 && $hStatus?->status_id == 7) {
                        $start = max(Carbon::parse($leave->start_date), Carbon::parse($cutoff->from_cutoff_date));
                        $end = min(Carbon::parse($leave->end_date), Carbon::parse($cutoff->to_cutoff_date));
                        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                            $approvedLeaveDates[] = $d->toDateString();
                            $leave->with_pay ? $paidLeaveDays++ : $unpaidLeaveApproved++;
                        }
                    }
                }

                // --- Main Day Loop (UPDATED WITH DYNAMIC REST DAY) ---
                $startDate = Carbon::parse($cutoff->from_cutoff_date);
                $endDate = Carbon::parse($cutoff->to_cutoff_date);

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dateStr = $date->toDateString();
                    $dayId = $weekDayMap[strtolower($date->format('l'))] ?? null;

                    // Get rest day for this specific date
                    $activeChange = $restDayTimeline->filter(fn($co) => $co->active_from <= $dateStr)->last();
                    $currentRestDayId = $activeChange ? $activeChange->label->new_day_id : 9;

                    $log = $attendances->first(fn($a) => Carbon::parse($a->attendance_date)->toDateString() === $dateStr);
                    $isPresent = ($log && !is_null($log->time_in));
                    $isHoliday = isset($holidayMap[$dateStr]);
                    $holidayType = $holidayMap[$dateStr] ?? null;
                    $isRestDay = ($currentRestDayId == $dayId);
                    $isApprovedLeave = in_array($dateStr, $approvedLeaveDates);

                    if ($isHoliday) {
                        if ($holidayType === 'regular') {
                            if ($isPresent) { $isRestDay ? $rdRegularHolidayCount++ : $regularHolidayCount++; }
                            elseif (!$isRestDay) { $regularHolidayCount++; }
                        } else {
                            if ($isPresent) { $isRestDay ? $rdSpecialHolidayCount++ : $specialHolidayCount++; }
                        }
                    }

                    if ($isPresent) {
                        $presentDaysCount++;
                        $grandTotalMinutes += $dayInMinutes;
                    } elseif (!$isHoliday && !$isRestDay && !$isApprovedLeave) {
                        $absentDays++;
                    }
                }

                // --- Detailed Overtime (UPDATED WITH DYNAMIC REST DAY) ---
                $regOT = 0; $rdOT = 0; $regHolOT = 0; $specHolOT = 0; $rdRegOT = 0; $rdSpecOT = 0;

                $overtimeLists = OvertimeList::with('overtime.approvalStatuses.user')
                    ->whereHas('overtime', fn($q) => $q->where('user_id', $item->user_id))
                    ->whereBetween('overtime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

                foreach ($overtimeLists as $ot) {
                    $lO = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                    $hO = $ot->overtime->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);

                    if ($lO?->status_id == 7 && $hO?->status_id == 7) {
                        $otDateStr = Carbon::parse($ot->overtime_date)->toDateString();
                        $otDayId = $weekDayMap[strtolower(Carbon::parse($otDateStr)->format('l'))] ?? null;

                        // Get rest day for the OT date
                        $activeOTChange = $restDayTimeline->filter(fn($co) => $co->active_from <= $otDateStr)->last();
                        $currentOTRestDayId = $activeOTChange ? $activeOTChange->label->new_day_id : 9;

                        $isH = isset($holidayMap[$otDateStr]);
                        $hT = $holidayMap[$otDateStr] ?? null;
                        $isR = ($currentOTRestDayId == $otDayId);
                        $mins = $ot->additional_hours_worked * 60;

                        if (!$isH && !$isR) $regOT += $mins;
                        elseif (!$isH && $isR) $rdOT += $mins;
                        elseif ($isH && !$isR) ($hT === 'regular' ? $regHolOT += $mins : $specHolOT += $mins);
                        elseif ($isH && $isR) ($hT === 'regular' ? $rdRegOT += $mins : $rdSpecOT += $mins);
                    }
                }

                // --- Undertime ---
                $totalUndertimeMinutes = 0;
                $undertimes = Undertime::with('approvalStatuses.user')
                    ->where('user_id', $item->user_id)
                    ->whereBetween('undertime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

                foreach ($undertimes as $ut) {
                    $lU = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 3);
                    $hU = $ut->approvalStatuses->first(fn($a) => $a->user?->user_type_id == 1);
                    if ($lU?->status_id == 7 && $hU?->status_id == 7) $totalUndertimeMinutes += (int)$ut->total_time;
                }

                $totalOT = $regOT + $rdOT + $regHolOT + $specHolOT + $rdRegOT + $rdSpecOT;
                $grandTotalMinutes += $totalOT;
                $grandTotalMinutes -= $totalUndertimeMinutes;
                $grandTotalMinutes += ($paidLeaveDays * $dayInMinutes);

                return [
                    'employee_name' => $item->employee_name,
                    'department_name' => $item->department?->name ?? 'N/A',
                    'late_minutes' => $totalLateMinutes,
                    'undertime_minutes' => $totalUndertimeMinutes,
                    'paid_leaves' => $paidLeaveDays,
                    'unpaid_leaves' => ($absentDays + $unpaidLeaveApproved),
                    'reg_hol' => $regularHolidayCount,
                    'spec_hol' => $specialHolidayCount,
                    'rd_reg_hol' => $rdRegularHolidayCount,
                    'rd_spec_hol' => $rdSpecialHolidayCount,
                    'ot_reg' => $regOT,
                    'ot_rd' => $rdOT,
                    'ot_hol_reg' => $regHolOT,
                    'ot_spec' => $specHolOT,
                    'ot_rd_reg' => $rdRegOT,
                    'ot_rd_spec' => $rdSpecOT,
                    'total_summary' => [
                        'days' => floor($grandTotalMinutes / $dayInMinutes),
                        'hours' => floor(($grandTotalMinutes % $dayInMinutes) / 60),
                        'minutes' => $grandTotalMinutes % 60
                    ],
                ];
            });

        $dateRangeString = Carbon::parse($cutoff->from_cutoff_date)->format('F j, Y') . ' to ' . Carbon::parse($cutoff->to_cutoff_date)->format('F j, Y');

        return Excel::download(
            new AttendanceExport($reports, $dateRangeString),
            'Attendance_Report_' . $cutoff->from_cutoff_date . '.xlsx'
        );
    }


  public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8',
        ]);

        $report = AttendanceEmployee::with(['user.salaryEmployee', 'attendances', 'department'])->findOrFail($id);

        DB::transaction(function () use ($request, $report) {
            // 1. Update or Create the approval status
            DB::table('attendance_statuses')->updateOrInsert(
                ['attendance_employee_id' => $report->id, 'user_id' => $request->user()->id],
                ['status_id' => $request->status_id, 'updated_at' => now(), 'created_at' => now()]
            );

            // 2. Handle Payroll Insertion vs. Removal
            if ($request->status_id == 7) {
                $approvals = DB::table('attendance_statuses')
                    ->join('users', 'attendance_statuses.user_id', '=', 'users.id')
                    ->where('attendance_employee_id', $report->id)
                    ->where('attendance_statuses.status_id', 7)
                    ->pluck('users.user_type_id')
                    ->toArray();

                // Only insert if BOTH HR (1) and Head (3) have approved
                if (in_array(1, $approvals) && in_array(3, $approvals)) {
                    $this->calculateAndInsertPayroll($report);
                }
            } else {
                // Remove payroll record if rejected
                DB::table('salary_payroll')->where('attendance_employee_id', $report->id)->delete();
            }
        });

        return redirect()->back();
    }

    private function calculateAndInsertPayroll($report)
    {
        $user = $report->user;
        $salaryRecord = $user->salaryEmployee;
        if (!$salaryRecord) return;

        $cutoff = PayrollCutOff::findOrFail($report->payroll_cut_off_id);
        $metrics = $this->getPayrollMetrics($report, $cutoff);

        // --- 1. Monthly Constants (EXACT FRONTEND CLONE) ---
        $rawBasic = (float)$salaryRecord->salary_amount;
        $rawDeMinimis = (float)($salaryRecord->de_minimis ?? 0);

        $monthlyBasic = $salaryRecord->type === "daily" ? $rawBasic * 26 : $rawBasic;
        $monthlyDeMinimis = $salaryRecord->type === "daily" ? $rawDeMinimis * 26 : $rawDeMinimis;
        $monthlyGross = $monthlyBasic + $monthlyDeMinimis;

        // --- 2. Semi-Monthly Earnings (Rates include De Minimis for OT/Holidays) ---
        if ($salaryRecord->type === 'daily') {
            // Daily: 500 (Basic) + 200 (De Minimis) = 700 Daily Rate
            $dailyRate = $rawBasic + $rawDeMinimis;

            $workDaysCount = $metrics['present_days'] + $metrics['paid_leave_days'] + $metrics['reg_h_count'];

            // Split components for reporting, but based on days worked
            $semiMonthlyBasic = $workDaysCount * $rawBasic;
            $semiMonthlyDeMinimis = $workDaysCount * $rawDeMinimis;
        } else {
            // Monthly: (30,000 Basic + 5,000 De Minimis) / 26 = Daily Rate
            $dailyRate = ($monthlyBasic + $monthlyDeMinimis) / 26;

            $semiMonthlyBasic = $monthlyBasic / 2;
            $semiMonthlyDeMinimis = $monthlyDeMinimis / 2;

            $absenceWithPayAmount = 0;
        }

        // Hourly rate now reflects the combined Basic + Allowance
        $hourlyRate = $dailyRate / 8;

        $earnings = [
            // Total base pay for the cutoff
            'regular_pay'           => $semiMonthlyBasic + $semiMonthlyDeMinimis,
            'absence_with_pay'      => $absenceWithPayAmount,

            // OT and Holiday computations now use the combined $hourlyRate / $dailyRate
            'regular_ot'            => ($metrics['regular_ot_min'] / 60) * $hourlyRate * 1.25,
            'rdot'                  => ($metrics['rd_ot_min'] / 60) * $hourlyRate * 1.30,
            'regular_holiday_ot'    => ($metrics['reg_h_ot_min'] / 60) * $hourlyRate * 2.60,
            'special_holiday_ot'    => ($metrics['spe_h_ot_min'] / 60) * $hourlyRate * 1.69,
            'rd_regular_holiday_ot' => ($metrics['rd_reg_h_ot_min'] / 60) * $hourlyRate * 3.38,
            'rd_special_holiday_ot' => ($metrics['rd_spe_h_ot_min'] / 60) * $hourlyRate * 1.95,

            'regular_holiday'       => $metrics['reg_h_count'] * $dailyRate,
            'special_holiday'       => $metrics['spe_h_count'] * $dailyRate * 0.30,
            'rd_regular_holiday'    => $metrics['rd_reg_h_count'] > 0
                                        ? ($metrics['rd_reg_h_count'] * $dailyRate * 0.60) + ($metrics['rd_reg_h_count'] * $dailyRate)
                                        : 0,
            'rd_special_holiday'    => $metrics['rd_spe_h_count'] * $dailyRate * 0.50,
        ];

        $grossEarnings = array_sum($earnings);

        // --- 3. Monthly Statutory (EXACT FRONTEND CLONE) ---

        // SSS (Based on Gross: Basic + De Minimis)
        $sssRow = DB::table('sss_contributions')
            ->where('min_salary', '<=', $monthlyGross)
            ->where('max_salary', '>=', $monthlyGross)
            ->first();
        $sssEE = $sssRow ? ((float)$sssRow->ee_share + (float)$sssRow->wisp_ee) : 0;

        // PhilHealth (Based on Gross: Basic + De Minimis)
        $phRate = (float)DB::table('deduction_settings')->where('key', 'philhealth_rate')->value('value') ?: 0.05;
        $phMin = (float)DB::table('deduction_settings')->where('key', 'philhealth_min_salary')->value('value') ?: 10000;
        $phMax = (float)DB::table('deduction_settings')->where('key', 'philhealth_max_salary')->value('value') ?: 100000;
        $phBase = min(max($monthlyGross, $phMin), $phMax);
        $philhealthEE = ($phBase * $phRate) / 2;

        // Pag-IBIG (Based on Basic Pay)
        $piLow = (float)DB::table('deduction_settings')->where('key', 'pagibig_rate_low')->value('value') ?: 0.01;
        $piHigh = (float)DB::table('deduction_settings')->where('key', 'pagibig_rate_high')->value('value') ?: 0.02;
        $piCap = (float)DB::table('deduction_settings')->where('key', 'pagibig_max_contribution')->value('value') ?: 200;
        $piSalaryCap = (float)DB::table('deduction_settings')->where('key', 'pagibig_salary_cap')->value('value') ?: 5000;

        $piBase = min($monthlyBasic, $piSalaryCap);
        $pagibigEE = $piBase <= 1500 ? ($piBase * $piLow) : ($piBase * $piHigh);
        $pagibigEE = min($pagibigEE, $piCap);

        // --- 4. Monthly Tax (EXACT FRONTEND CLONE) ---
        // const statutory = sssEE + philhealthEE + pagibigEE;
        $statutory = $sssEE + $philhealthEE + $pagibigEE;
        // const taxableAmount = Math.max(0, basicPay - statutory);
        $taxableAmount = max(0, $monthlyBasic - $statutory);

        $taxRow = DB::table('tax_brackets')
            ->where('min_salary', '<=', $taxableAmount)
            ->orderBy('min_salary', 'desc')
            ->first();

        $tax = 0;
        if ($taxRow && $monthlyBasic > 0) {
            $tax = (float)$taxRow->base_tax +
                ($taxableAmount - (float)$taxRow->over_amount) * (float)$taxRow->excess_rate;
        }

        // --- 5. Final Mapping ---
        $isFirstCutoff = preg_match('/(1st|first)/i', $cutoff->name);

        $deductions = [
            // 'undertime' => ($metrics['undertime_min'] / 60) * $hourlyRate,
            'undertime' => 0,
            'absence_without_pay' => $metrics['absent_days'] * $dailyRate,
            'sss' => $isFirstCutoff ? $sssEE : 0,
            'tax' => $isFirstCutoff ? $tax : 0,
            'philhealth' => !$isFirstCutoff ? $philhealthEE : 0,
            'pag_ibig' => !$isFirstCutoff ? $pagibigEE : 0,
        ];

        $totalDeduction = array_sum($deductions);

        DB::table('salary_payroll')->updateOrInsert(
            ['attendance_employee_id' => $report->id],
            array_merge($earnings, $deductions, [
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'position_id' => $user->position_id,
                'status_id' => 4,
                'total_earning' => $grossEarnings,
                'total_deduction' => $totalDeduction,
                'total_home_pay' => $grossEarnings - $totalDeduction,
                'updated_at' => now(),
                'created_at' => now(),
            ])
        );
    }

    private function getPayrollMetrics($report, $cutoff)
    {
        $weekDayMap = ['monday'=>3,'tuesday'=>4,'wednesday'=>5,'thursday'=>6,'friday'=>7,'saturday'=>8,'sunday'=>9];

        $holidayMap = Holiday::where('status_id', 1)->get()
            ->mapWithKeys(fn($h) => [Carbon::parse($h->date)->toDateString() => strtolower($h->type)])->toArray();

        // --- NEW TIMELINE LOGIC ---
        $restDayTimeline = ChangeOff::with(['label', 'approvalStatuses.user'])
            ->where('user_id', $report->user_id)
            ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 1)))
            ->whereHas('approvalStatuses', fn($q) => $q->where('status_id', 7)->whereHas('user', fn($u) => $u->where('user_type_id', 3)))
            ->get()
            ->map(function($co) {
                $hrStatus = $co->approvalStatuses->first(fn($s) => $s->status_id == 7 && ($s->user->user_type_id ?? null) == 1);
                $co->active_from = $hrStatus ? Carbon::parse($hrStatus->created_at)->toDateString() : null;
                return $co;
            })
            ->filter(fn($co) => $co->active_from !== null)
            ->sortBy('active_from');
        // --------------------------

        // Approved Leaves
        $approvedLeaveDates = [];
        $paidLeaveDays = 0;
        $leaves = Leave::with('approvalStatuses.user')->where('user_id', $report->user_id)->get();
        foreach ($leaves as $leave) {
            $leader = $leave->approvalStatuses->first(fn($a) => ($a->user->user_type_id ?? null) == 3);
            $hr = $leave->approvalStatuses->first(fn($a) => ($a->user->user_type_id ?? null) == 1);

            if ($leader?->status_id == 7 && $hr?->status_id == 7) {
                $start = max(Carbon::parse($leave->start_date), Carbon::parse($cutoff->from_cutoff_date));
                $end = min(Carbon::parse($leave->end_date), Carbon::parse($cutoff->to_cutoff_date));
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $approvedLeaveDates[] = $d->toDateString();
                    if ($leave->with_pay) $paidLeaveDays++;
                }
            }
        }

        $metrics = [
            'present_days' => 0, 'paid_leave_days' => $paidLeaveDays, 'absent_days' => 0, 'undertime_min' => 0,
            'reg_h_count' => 0, 'spe_h_count' => 0, 'rd_reg_h_count' => 0, 'rd_spe_h_count' => 0,
            'regular_ot_min' => 0, 'rd_ot_min' => 0, 'reg_h_ot_min' => 0, 'spe_h_ot_min' => 0,
            'rd_reg_h_ot_min' => 0, 'rd_spe_h_ot_min' => 0
        ];

        $startDate = Carbon::parse($cutoff->from_cutoff_date);
        $endDate = Carbon::parse($cutoff->to_cutoff_date);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();
            $dayId = $weekDayMap[strtolower($date->format('l'))] ?? null;

            // Find rest day for this specific date
            $activeChange = $restDayTimeline->filter(fn($co) => $co->active_from <= $dateStr)->last();
            $currentRestDayId = $activeChange ? $activeChange->label->new_day_id : 9;

            $isRestDay = ($currentRestDayId == $dayId);
            $holidayType = $holidayMap[$dateStr] ?? null;
            $log = $report->attendances->first(fn($a) => $a->attendance_date === $dateStr);
            $isPresent = ($log && $log->time_in);
            $isApprovedLeave = in_array($dateStr, $approvedLeaveDates);

            if ($isPresent) {
                $metrics['present_days']++;
            }

            if ($holidayType === 'regular') {
                if ($isPresent) {
                    $isRestDay ? $metrics['rd_reg_h_count']++ : $metrics['reg_h_count']++;
                } elseif (!$isRestDay) {
                    $metrics['reg_h_count']++;
                }
            } elseif ($holidayType === 'special' && $isPresent) {
                $isRestDay ? $metrics['rd_spe_h_count']++ : $metrics['spe_h_count']++;
            }

            if (!$isPresent && !$isRestDay && !$holidayType && !$isApprovedLeave) {
                $metrics['absent_days']++;
            }
        }

        // Overtime
        $ots = OvertimeList::with('overtime.approvalStatuses.user')
            ->whereHas('overtime', fn($q) => $q->where('user_id', $report->user_id))
            ->whereBetween('overtime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])->get();

        foreach ($ots as $ot) {
            $l = $ot->overtime->approvalStatuses->first(fn($a) => $a->status_id == 7 && ($a->user->user_type_id ?? null) == 3);
            $h = $ot->overtime->approvalStatuses->first(fn($a) => $a->status_id == 7 && ($a->user->user_type_id ?? null) == 1);

            if ($l && $h) {
                $otDateStr = Carbon::parse($ot->overtime_date)->toDateString();
                $otDayId = $weekDayMap[strtolower(Carbon::parse($otDateStr)->format('l'))] ?? null;

                // Find rest day for the OT date
                $activeOTChange = $restDayTimeline->filter(fn($co) => $co->active_from <= $otDateStr)->last();
                $currentOTRestDayId = $activeOTChange ? $activeOTChange->label->new_day_id : 9;

                $isRD = ($currentOTRestDayId == $otDayId);
                $hType = $holidayMap[$otDateStr] ?? null;
                $m = $ot->additional_hours_worked * 60;

                if (!$hType && !$isRD) $metrics['regular_ot_min'] += $m;
                elseif (!$hType && $isRD) $metrics['rd_ot_min'] += $m;
                elseif ($hType === 'regular') $isRD ? $metrics['rd_reg_h_ot_min'] += $m : $metrics['reg_h_ot_min'] += $m;
                elseif ($hType === 'special') $isRD ? $metrics['rd_spe_h_ot_min'] += $m : $metrics['spe_h_ot_min'] += $m;
            }
        }

        // Undertime (Unchanged logic, but ensured relationships are loaded)
        $metrics['undertime_min'] = Undertime::with('approvalStatuses.user')
            ->where('user_id', $report->user_id)
            ->whereBetween('undertime_date', [$cutoff->from_cutoff_date, $cutoff->to_cutoff_date])
            ->get()->filter(function($u) {
                $approvals = $u->approvalStatuses->where('status_id', 7);
                $hasHR = $approvals->contains(fn($a) => ($a->user->user_type_id ?? null) == 1);
                $hasHead = $approvals->contains(fn($a) => ($a->user->user_type_id ?? null) == 3);
                return $hasHR && $hasHead;
            })->sum('total_time');

        return $metrics;
    }
}
