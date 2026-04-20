<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\Manpower;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManpowerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isHR = $user->user_type_id == 1;

        // 1. Fetch Active Departments and Positions for filters
        $departments = Department::where('status_id', 1)->orderBy('name', 'asc')->get();
        $positions = Position::where('status_id', 1)->orderBy('name', 'asc')->get();

        // 2. Fetch Employees for Filter
        $employeesQuery = User::query()->select('id', 'first_name', 'last_name', 'username', 'department_id');

        if (!$isHR) {
            // Heads only see employees in their department
            $employeesQuery->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            // HR filtering employees by department
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->orderBy('first_name', 'asc')->get();

        // 3. Build Manpower Query
        $query = Manpower::with([
            'user.department', // Load department relationship
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

        // Access Logic: Head sees department only, HR sees all (unless filtered)
        if (!$isHR) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } else {
            if ($request->filled('department_id')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('position_type', 'like', "%{$search}%");
        }

        // Employee Filter
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        $manpowers = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {

                $leaderEntry = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 3);
                $hrEntry     = $item->approvalStatuses->first(fn ($log) => $log->user?->user_type_id == 1);

                return [
                    'id'              => $item->id,
                    'employee_name'   => $item->user->first_name . ' ' . $item->user->last_name,
                    'department_name' => $item->user->department->name ?? 'N/A', // Added department_name
                    'date_filed'      => $item->created_at->format('M d, Y'),
                    'date_required'   => Carbon::parse($item->date_required)->format('M d, Y'),
                    'position_type'   => $item->position_type,
                    'report_to'       => $item->report_to,
                    'job_description' => $item->job_description,
                    'justification'   => $item->justification,
                    'status_type'     => $item->status_type,
                    'payment_type'    => $item->payment_type,
                    'leader_status_name' => $leaderEntry?->status?->name ?? 'Pending',
                    'hr_status_name'     => $hrEntry?->status?->name ?? 'Pending',
                ];
            });

        return Inertia::render('management/ApprovalForm/ManpowerList', [
            'items'           => $manpowers,
            'departments'     => $departments,
            'positions'       => $positions,
            'employeeOptions' => $employees,
            'filters'         => $request->only(['search', 'employee_id', 'department_id']),
            'auth_user_type'  => $user->user_type_id
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|in:7,8', // 7=Approved, 8=Rejected
        ]);

        $manpower = Manpower::findOrFail($id);

        DB::table('manpower_statuses')->updateOrInsert(
            [
                'manpower_id' => $manpower->id,
                'user_id'     => $request->user()->id,
            ],
            [
                'status_id'  => $request->status_id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('message', 'Manpower request updated.');
    }
}
