<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Status;
use App\Models\UserType;
use App\Models\Leave;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            'regularization_date' => 'required|date',
            'immediate_supervisor' => 'required|string|max:255',
            'work_location' => 'required|string|max:255',
            'payroll_group' => 'required|string|max:255',
            'leave_pay' => 'required|numeric|min:0',
        ]);

        $defaultPassword = 'password';

        $user = User::create([
            'employee_id' => $validated['employee_id'],
            'username' => $validated['username'],
            'company_email' => $validated['company_email'],
            'password' => Hash::make($defaultPassword),
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

        // Send Email Notification
        try {
            Mail::html("
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>

                    <h2 style='text-align: center;'>
                        Welcome to HAPPIEST-NEST
                    </h2>

                    <p>Hello <strong>{$validated['username']}</strong>,</p>

                    <p>
                        Your employee account has been successfully created.
                        You can now access the system by logging in using your account credentials below:
                    </p>

                    <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 5px 0;'>
                            <strong>Username:</strong> {$validated['username']}
                        </p>

                        <p style='margin: 5px 0;'>
                            <strong>Password:</strong> {$defaultPassword}
                        </p>
                    </div>

                    <p>
                        Please log in and complete your information to access the full system.
                    </p>

                    <p>
                        For security purposes, we strongly recommend changing your password immediately to a strong password after logging in.
                    </p>

                    <br>

                    <p>Best Regards,</p>
                    <strong>HAPPIEST-NEST HR Team</strong>
                </div>
            ", function ($message) use ($validated) {
                $message->to($validated['company_email'])
                    ->subject('Your Employee Account Has Been Created');
            });
        } catch (\Exception $e) {
            \Log::error('Employee email sending failed: ' . $e->getMessage());
        }

        return redirect()->back()->with(
            'success',
            'Employee onboarded successfully and email notification sent.'
        );
    }

    public function edit(User $user)
    {
        // Format dates for input
        $user->date_hired = $user->date_hired
            ? Carbon::parse($user->date_hired)->format('Y-m-d')
            : null;

        $user->regularization_date = $user->regularization_date
            ? Carbon::parse($user->regularization_date)->format('Y-m-d')
            : null;

        $currentYear = now()->year;

        $leaveUsed = Leave::where('user_id', $user->id)
            ->where('with_pay', true)
            ->whereYear('start_date', $currentYear)
            ->whereHas('approvalStatuses', function ($q) {
                $q->where('status_id', 7);
            }, '=', 2)
            ->sum('total_days');

        $user->leave_pay_used = $leaveUsed;

        return Inertia::render('management/HR/AddEmployee', [
            'employee' => $user,
            'isEditing' => true,
            'departments' => Department::where('status_id', 1)->get(),
            'positions' => Position::where('status_id', 1)->get(),
            'userTypes' => UserType::all(),
            'pendingStatus' => Status::where('name', 'Pending')->first(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:users,employee_id,' . $user->id,
            'username' => 'required|string|unique:users,username,' . $user->id,
            'company_email' => 'required|email|unique:users,company_email,' . $user->id,
            'user_type_id' => 'required|exists:user_types,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'status_id' => 'required|exists:statuses,id',
            'employment_status' => 'required|in:Regular,Probationary,Contractual,Casual',
            'employment_type' => 'required|in:Full-Time,Part-Time',
            'date_hired' => 'required|date',
            'regularization_date' => 'required|date',
            'immediate_supervisor' => 'required|string|max:255',
            'work_location' => 'required|string|max:255',
            'payroll_group' => 'required|string|max:255',
            'leave_pay' => 'required|numeric|min:0',
        ]);

        $user->update($validated);

        return redirect()
            ->route('hr.employee.index')
            ->with('success', 'Employee updated successfully.');
    }
}
