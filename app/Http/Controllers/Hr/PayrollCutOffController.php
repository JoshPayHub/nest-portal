<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\PayrollCutOff;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

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

    // Get all employees for the dropdown filter
    $employees = User::with(['department', 'position', 'status'])->get();

    $reports = AttendanceEmployee::where('payroll_cut_off_id', $id)
        ->join('users', 'attendance_employees.user_id', '=', 'users.id')
        ->with(['attendances', 'leaderStatus.status', 'hrStatus.status', 'user.department', 'department'])
        ->select(
            'attendance_employees.*',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as employee_name")
        )
        // Filter by Search Term (Name)
        ->when($request->search, function ($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%");
            });
        })
        // Filter by Specific Employee ID from dropdown
        ->when($request->employee_id, function ($query, $empId) {
            $query->where('attendance_employees.user_id', $empId);
        })
        // Filter by Department
        ->when($request->department, function ($query, $deptId) {
            $query->where('attendance_employees.department_id', $deptId);
        })
        // Filter by HR Status
        ->when($request->status, function ($query, $statusId) {
            $query->where('attendance_employees.hr_status_id', $statusId);
        })
        ->orderBy('attendance_employees.created_at', 'desc')
        ->paginate(10)
        ->withQueryString() // Crucial: Keeps your filters when clicking next page
        ->through(function ($item) use ($cutoff) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'employee_name' => $item->employee_name,
                'days_count' => $item->attendances->count(),
                'name' => $cutoff->name,
                'from_cutoff_date' => $cutoff->from_cutoff_date,
                'to_cutoff_date' => $cutoff->to_cutoff_date,
                'report_date' => $item->created_at ? $item->created_at->format('Y-m-d') : null,
                'leader_status_name' => $item->leaderStatus?->status?->name ?? 'Pending',
                'hr_status_name' => $item->hrStatus?->status?->name ?? 'Pending',
                'hr_status_id' => $item->hrStatus?->status_id ?? 1,
                'user' => [
                    'first_name' => $item->user->first_name ?? '',
                    'last_name' => $item->user->last_name ?? '',
                    'department' => [
                        'id' => $item->department_id,
                        'name' => $item->department->name ?? 'N/A'
                    ],
                ],
                'attendances' => $item->attendances->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'attendance_date' => $log->attendance_date,
                        'time_in' => $log->time_in,
                        'time_out' => $log->time_out,
                    ];
                }),
            ];
        });

    return Inertia::render('management/HR/PayrollCutOffList', [
        'cutoff' => $cutoff,
        'employees' => $employees,
        'departments' => \App\Models\Department::all(),
        'reports' => $reports,
        'filters' => $request->only(['search', 'department', 'status', 'employee_id'])
    ]);
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
