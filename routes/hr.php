<?php

use App\Http\Controllers\ApprovalForm\AccomplishmentReportController;
use App\Http\Controllers\ApprovalForm\BusinessNotificationController;
use App\Http\Controllers\ApprovalForm\ChangeOffController;
use App\Http\Controllers\ApprovalForm\LeaveAbsenceController;
use App\Http\Controllers\ApprovalForm\LeaveController;
use App\Http\Controllers\ApprovalForm\ManpowerController;
use App\Http\Controllers\ApprovalForm\OvertimeRequestController;
use App\Http\Controllers\ApprovalForm\UndertimeFormController;
use App\Http\Controllers\Hr\AnnouncementPolicyController;
use App\Http\Controllers\Hr\DashboardController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeeListController;
use App\Http\Controllers\Hr\HolidayController;
use App\Http\Controllers\Hr\PayrollCutOffController;
use App\Http\Controllers\Hr\PositionController;
use App\Http\Controllers\Hr\SalaryDeductionsController;
use App\Http\Controllers\Hr\SalaryEmployeeController;
use App\Http\Controllers\Hr\SalaryPayrollController;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user_type:HR'])->prefix('hr')->name('hr.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(AnnouncementPolicyController::class)->group(function () {
        Route::get('/announcements-policies', 'index')->name('announcementpolicy.index');
        Route::post('/announcements-policies', 'store')->name('announcementpolicy.store');
        Route::post('/announcements-policies/{announcementPolicy}', 'update')->name('announcementpolicy.update');
        Route::delete('/announcements-policies/{announcementPolicy}', 'destroy')->name('announcementpolicy.destroy');
    });

    Route::get('/add-employees', [EmployeeController::class, 'index'])->name('addemployees.index');
    Route::post('/add-employees/store', [EmployeeController::class, 'store'])->name('addemployees.store');
    Route::get('/employees/edit/{user}', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/update/{user}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::get('/list-employee', [EmployeeListController::class, 'index'])->name('employee.index');

    Route::get('/department', [DepartmentController::class, 'index'])->name('department.index');
    Route::post('/department/store', [DepartmentController::class, 'store'])->name('department.store');
    Route::post('/department/update/{id}', [DepartmentController::class, 'update'])->name('department.update');

    Route::get('/position', [PositionController::class, 'index'])->name('position.index');
    Route::post('/position/store', [PositionController::class, 'store'])->name('position.store');
    Route::post('/position/update/{id}', [PositionController::class, 'update'])->name('position.update');

    Route::get('/holiday', [HolidayController::class, 'index'])->name('holiday.index');
    Route::post('/holiday/store', [HolidayController::class, 'store'])->name('holiday.store');
    Route::post('/holiday/update/{id}', [HolidayController::class, 'update'])->name('holiday.update');

    Route::get('/payroll-cut-off', [PayrollCutOffController::class, 'index'])->name('payrollcutoff.index');
    Route::post('/payroll-cut-off/store', [PayrollCutOffController::class, 'store'])->name('payrollcutoff.store');
    Route::post('/payroll-cut-off/update/{id}', [PayrollCutOffController::class, 'update'])->name('payrollcutoff.update');
    Route::get('/payroll-cut-off/{id}/attendance', [PayrollCutOffController::class, 'attendancePage'])->name('payrollcutoff.attendance');
    Route::post('/payroll-cut-off/{id}/approve', [PayrollCutOffController::class, 'approve'])->name('payrollcutoff.approve');
    Route::get('/payroll-cutoff/{id}/export', [PayrollCutOffController::class, 'exportAttendance'])->name('attendance.export');

    Route::get('/salary-employee', [SalaryEmployeeController::class, 'index'])->name('payrollcutoff.index');
    Route::post('/salary-employee/store', [SalaryEmployeeController::class, 'store'])->name('payrollcutoff.store');
    Route::post('/salary-employee/update/{id}', [SalaryEmployeeController::class, 'update'])->name('payrollcutoff.update');

    Route::get('/salary-deductions', [SalaryDeductionsController::class, 'index'])->name('salarydeductions.index');
    Route::put('/salary-deductions/settings/{id}', [SalaryDeductionsController::class, 'updateSetting']);
    Route::put('/salary-deductions/sss/{id}', [SalaryDeductionsController::class, 'updateSSS']);
    Route::put('/salary-deductions/tax/{id}', [SalaryDeductionsController::class, 'updateTax']);

    Route::get('/salary-payroll', [SalaryPayrollController::class, 'index'])->name('salarypayroll.index');
    Route::get('/salary-payroll/{id}/list', [SalaryPayrollController::class, 'list'])->name('salarypayrolllist.list');
    Route::post('/salary-payroll/{id}/update', [SalaryPayrollController::class, 'update'])->name('salarypayrolllist.update');
    Route::get('/salary-payroll/{id}/export', [SalaryPayrollController::class, 'export'])->name('salarypayrollexport.export');

    // Form Approval
    // Accomplishment Report Form
    Route::get('/accomplishment-report', [AccomplishmentReportController::class, 'index'])->name('accomplishmentreport.index');
    Route::post('/accomplishment-report/{id}/approve', [AccomplishmentReportController::class, 'approve'])->name('accomplishmentreport.approve');

    // Change Off Form
    Route::get('/change-off', [ChangeOffController::class, 'index'])->name('changeoff.index');
    Route::post('/change-off/{id}/approve', [ChangeOffController::class, 'approve'])->name('changeoff.approve');

    // Leave Form
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave/{id}/approve', [LeaveController::class, 'approve'])->name('leave.approve');

    // Leave of Absence Form
    Route::get('/leave-of-absence', [LeaveAbsenceController::class, 'index'])->name('leaveabsence.index');
    Route::post('/leave-of-absence/{id}/approve', [LeaveAbsenceController::class, 'approve'])->name('leaveabsence.approve');

    // Manpower Form
    Route::get('/manpower', [ManpowerController::class, 'index'])->name('manpower.index');
    Route::post('/manpower/{id}/approve', [ManpowerController::class, 'approve'])->name('manpower.approve');

    // Business Notification Form
    Route::get('/business-notification', [BusinessNotificationController::class, 'index'])->name('businessnotification.index');
    Route::post('/business-notification/{id}/approve', [BusinessNotificationController::class, 'approve'])->name('businessnotification.approve');

    // Overtime Request Form
    Route::get('/overtime-request', [OvertimeRequestController::class, 'index'])->name('overtimerequest.index');
    Route::post('/overtime-request/{id}/approve', [OvertimeRequestController::class, 'approve'])->name('overtimerequest.approve');

    // Undertime Form
    Route::get('/undertime-form', [UndertimeFormController::class, 'index'])->name('undertimeform.index');
    Route::post('/undertime-form/{id}/approve', [UndertimeFormController::class, 'approve'])->name('undertimeform.approve');
});
