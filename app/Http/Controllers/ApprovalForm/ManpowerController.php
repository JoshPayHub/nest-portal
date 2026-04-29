<?php

namespace App\Http\Controllers\ApprovalForm;

use App\Http\Controllers\Controller;
use App\Models\Manpower;
use App\Models\Notification;
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

        $departments = Department::where('status_id', 1)->orderBy('name', 'asc')->get();
        $positions = Position::where('status_id', 1)->orderBy('name', 'asc')->get();

        $employeesQuery = User::query()->select('id', 'first_name', 'last_name', 'username', 'department_id');

        if (!$isHR) {
            $employeesQuery->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }

        $employees = $employeesQuery->orderBy('first_name', 'asc')->get();

        $query = Manpower::with([
            'user.department',
            'approvalStatuses.user.userType',
            'approvalStatuses.status'
        ]);

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
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
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
        $request->validate(['status_id' => 'required|in:7,8']);

        $user = $request->user();
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

        $userTypeName = ($user->user_type_id == 1) ? 'HR' : 'Department Head';
        $statusName = ($request->status_id == 7) ? 'Approved' : 'Rejected';

        $title = "{$userTypeName} {$statusName} your Manpower Request";
        $message = "Your manpower request has been " . strtolower($statusName) . " by " . $user->first_name . ".";

        $this->notifyUsers($manpower, $title, $message);

        return redirect()->back()->with('message', 'Manpower request processed.');
    }

    private function notifyUsers($manpower, $title, $message)
    {
        $employee = User::find($manpower->user_id);

        if ($employee) {
            $userTypePrefix = ($employee->user_type_id == 3) ? 'head' : 'employee';

            Notification::create([
                'user_id'      => $employee->id,
                'user_type_id' => null,
                'title'        => $title,
                'message'      => $message,
                'route'        => "/{$userTypePrefix}/manpowers",
                'data'         => json_encode(['manpower_id' => $manpower->id]),
            ]);
        }
    }
}
