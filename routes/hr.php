<?php

use App\Http\Controllers\ApprovalForm\AccomplishmentReportController;
use App\Http\Controllers\ApprovalForm\BusinessNotificationController;
use App\Http\Controllers\ApprovalForm\ChangeOffController;
use App\Http\Controllers\ApprovalForm\LeaveAbsenceController;
use App\Http\Controllers\ApprovalForm\LeaveController;
use App\Http\Controllers\ApprovalForm\ManpowerController;
use App\Http\Controllers\ApprovalForm\OvertimeRequestController;
use App\Http\Controllers\ApprovalForm\UndertimeFormController;
use App\Http\Controllers\Employee\NotificationController;
use App\Http\Controllers\Hr\AnnouncementPolicyController;
use App\Http\Controllers\Hr\DashboardController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeeListController;
use App\Http\Controllers\Hr\HolidayController;
use App\Http\Controllers\Hr\PayrollCutOffController;
use App\Http\Controllers\Hr\PositionController;
use App\Http\Controllers\Hr\ProfileController;
use App\Http\Controllers\Hr\SalaryDeductionsController;
use App\Http\Controllers\Hr\SalaryEmployeeController;
use App\Http\Controllers\Hr\SalaryPayrollController;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user.status', 'user_type:HR'])->prefix('hr')->name('hr.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notification', [NotificationController::class, 'index'])->name('notification.index');

    // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::put('/profile/update', 'update')->name('profile.update');
        Route::put('/profile/change-password', 'changePassword')->name('profile.change-password');
    });

    Route::controller(AnnouncementPolicyController::class)->group(function () {
        Route::get('/announcements-policies', 'index')->name('announcementpolicy.index');
        Route::post('/announcements-policies', 'store')->name('announcementpolicy.store');
        Route::post('/announcements-policies/{announcementPolicy}', 'update')->name('announcementpolicy.update');
        Route::delete('/announcements-policies/{announcementPolicy}', 'destroy')->name('announcementpolicy.destroy');
    });

    Route::controller(EmployeeController::class)->group(function () {
        Route::get('/add-employees', 'index')->name('addemployees.index');
        Route::post('/add-employees/store', 'store')->name('addemployees.store');
        Route::get('/employees/edit/{user}', 'edit')->name('employees.edit');
        Route::put('/employees/update/{user}', 'update')->name('employees.update');
    });

    Route::get('/list-employee', [EmployeeListController::class, 'index'])->name('employee.index');
    Route::put('/list-employee/update/{user}', [EmployeeListController::class, 'update'])->name('employee.update');

    Route::controller(DepartmentController::class)->group(function () {
        Route::get('/department', 'index')->name('department.index');
        Route::post('/department/store', 'store')->name('department.store');
        Route::post('/department/update/{id}', 'update')->name('department.update');
    });

    Route::controller(PositionController::class)->group(function () {
        Route::get('/position', 'index')->name('position.index');
        Route::post('/position/store', 'store')->name('position.store');
        Route::post('/position/update/{id}', 'update')->name('position.update');
    });

    Route::controller(HolidayController::class)->group(function () {
        Route::get('/holiday', 'index')->name('holiday.index');
        Route::post('/holiday/store', 'store')->name('holiday.store');
        Route::post('/holiday/update/{id}', 'update')->name('holiday.update');
    });

    Route::controller(PayrollCutOffController::class)->group(function () {
        Route::get('/payroll-cut-off', 'index')->name('payrollcutoff.index');
        Route::post('/payroll-cut-off/store', 'store')->name('payrollcutoff.store');
        Route::post('/payroll-cut-off/update/{id}', 'update')->name('payrollcutoff.update');
        Route::get('/payroll-cut-off/{id}/attendance', 'attendancePage')->name('payrollcutoff.attendance');
        Route::post('/payroll-cut-off/{id}/approve', 'approve')->name('payrollcutoff.approve');
        Route::get('/payroll-cutoff/{id}/export', 'exportAttendance')->name('attendance.export');
    });

    Route::controller(SalaryEmployeeController::class)->group(function () {
        Route::get('/salary-employee', 'index')->name('payrollcutoff.index');
        Route::post('/salary-employee/store', 'store')->name('payrollcutoff.store');
        Route::post('/salary-employee/update/{id}', 'update')->name('payrollcutoff.update');
    });

    Route::controller(SalaryDeductionsController::class)->group(function () {
        Route::get('/salary-deductions', 'index')->name('salarydeductions.index');
        Route::put('/salary-deductions/settings/{id}', 'updateSetting');
        Route::put('/salary-deductions/sss/{id}', 'updateSSS');
        Route::put('/salary-deductions/tax/{id}', 'updateTax');
    });

    Route::controller(SalaryPayrollController::class)->group(function () {
        Route::get('/salary-payroll', 'index')->name('salarypayroll.index');
        Route::get('/salary-payroll/{id}/list', 'list')->name('salarypayrolllist.list');
        Route::post('/salary-payroll/{id}/update', 'update')->name('salarypayrolllist.update');
        Route::get('/salary-payroll/{id}/export', 'export')->name('salarypayrollexport.export');
    });

    // Form Approval
    // Accomplishment Report Form
    Route::controller(AccomplishmentReportController::class)->group(function () {
        Route::get('/accomplishment-report', 'index')->name('accomplishmentreport.index');
        Route::post('/accomplishment-report/{id}/approve', 'approve')->name('accomplishmentreport.approve');
    });

    // Change Off Form
    Route::controller(ChangeOffController::class)->group(function () {
        Route::get('/change-off', 'index')->name('changeoff.index');
        Route::post('/change-off/{id}/approve', 'approve')->name('changeoff.approve');
    });

    // Leave Form
    Route::controller(LeaveController::class)->group(function () {
        Route::get('/leave', 'index')->name('leave.index');
        Route::post('/leave/{id}/approve', 'approve')->name('leave.approve');
    });

    // Leave of Absence Form
    Route::controller(LeaveAbsenceController::class)->group(function () {
        Route::get('/leave-of-absence', 'index')->name('leaveabsence.index');
        Route::post('/leave-of-absence/{id}/approve', 'approve')->name('leaveabsence.approve');
    });

    // Manpower Form
    Route::controller(ManpowerController::class)->group(function () {
        Route::get('/manpower', 'index')->name('manpower.index');
        Route::post('/manpower/{id}/approve', 'approve')->name('manpower.approve');
    });

    // Business Notification Form
    Route::controller(BusinessNotificationController::class)->group(function () {
        Route::get('/business-notification', 'index')->name('businessnotification.index');
        Route::post('/business-notification/{id}/approve', 'approve')->name('businessnotification.approve');
    });

    // Overtime Request Form
    Route::controller(OvertimeRequestController::class)->group(function () {
        Route::get('/overtime-request', 'index')->name('overtimerequest.index');
        Route::post('/overtime-request/{id}/approve', 'approve')->name('overtimerequest.approve');
    });

    // Undertime Form
    Route::controller(UndertimeFormController::class)->group(function () {
        Route::get('/undertime-form', 'index')->name('undertimeform.index');
        Route::post('/undertime-form/{id}/approve', 'approve')->name('undertimeform.approve');
    });
});
