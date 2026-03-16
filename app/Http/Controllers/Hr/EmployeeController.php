<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Status;
use App\Models\UserType;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index()
    {
        return Inertia::render('management/HR/AddEmployee', [
            'departments' => Department::where('status_id', 1)->get(),
            'positions' => Position::where('status_id', 1)->get(),
            'userTypes' => UserType::all(),
            'pendingStatus' => Status::where('name', 'Pending')->first(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:users,employee_id',
            'username' => 'required|string|unique:users,username',
            'company_email' => 'required|email|unique:users,company_email',
            'user_type_id' => 'required|exists:user_types,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'status_id' => 'required|exists:statuses,id',
            'employment_status' => 'required|in:Regular,Probationary,Contractual,Casual',
            'employment_type' => 'required|in:Full-Time,Part-Time',
            'date_hired' => 'required|date',
            'regularization_date' => 'nullable|date',
            'immediate_supervisor' => 'nullable|string|max:255',
            'work_location' => 'nullable|string|max:255',
            'payroll_group' => 'nullable|string|max:255',
            'leave_pay' => 'nullable|numeric|min:0',
        ]);

        User::create([
            'employee_id' => $validated['employee_id'],
            'username' => $validated['username'],
            'company_email' => $validated['company_email'],
            'password' => Hash::make('password'),
            'user_type_id' => $validated['user_type_id'],
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'status_id' => $validated['status_id'],
            'employment_status' => $validated['employment_status'],
            'employment_type' => $validated['employment_type'],
            'date_hired' => $validated['date_hired'],
            'regularization_date' => $validated['regularization_date'],
            'immediate_supervisor' => $validated['immediate_supervisor'],
            'work_location' => $validated['work_location'],
            'payroll_group' => $validated['payroll_group'],
            'leave_pay' => $validated['leave_pay'] ?? 0,
            'company_email_verified_at' => Carbon::now('Asia/Manila'),
        ]);

        return redirect()->back()->with('success', 'Employee onboarded successfully.');
    }
}
