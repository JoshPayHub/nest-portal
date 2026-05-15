<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeListController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::with([
                'department',
                'position',
                'status'
            ])
            ->where('user_type_id', '!=', 4)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_email', 'like', "%{$search}%");
                });
            })
            ->when($request->department, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
            ->when($request->status, function ($query, $statusId) {
                $query->where('status_id', $statusId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('management/HR/Employees', [
            'employees' => $employees,
            'departments' => Department::all(),

            // Only show allowed employee statuses
            'statuses' => Status::whereIn('id', [
                1, // active
                2, // inactive
                3, // suspended
                4, // pending
                9, // terminated
                10 // resigned
            ])->get(),

            'filters' => $request->only([
                'search',
                'department',
                'status'
            ]),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'status_id' => 'required|in:1,2,3,9,10',
        ]);

        if ($user->status_id == 4) {
            return back()->with('error', 'Pending employee status cannot be updated.');
        }

        $user->update([
            'status_id' => $validated['status_id'],
        ]);

        return back()->with('success', 'Employee status updated successfully.');
    }
}
