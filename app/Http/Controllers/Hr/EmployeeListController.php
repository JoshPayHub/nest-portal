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
        $employees = User::with(['department', 'position', 'status'])
            // Exclude Admin (assuming ID 4 based on your update)
            ->where('user_type_id', '!=', 4)
            // 1. Apply Filters BEFORE Paginate
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->department, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
            ->when($request->status, function ($query, $statusId) {
                $query->where('status_id', $statusId);
            })
            // 2. Order and Paginate
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Keeps filters in the pagination links

        return Inertia::render('management/HR/Employees', [
            'employees' => $employees,
            'departments' => Department::all(),
            'statuses' => Status::whereIn('id', [1, 2])->get(),
            'filters' => $request->only(['search', 'department', 'status'])
        ]);
    }
}
