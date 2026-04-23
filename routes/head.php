<?php

use App\Http\Controllers\ApprovalForm\AccomplishmentReportController;
use App\Http\Controllers\Head\AnnouncementPolicyController;
use App\Http\Controllers\ApprovalForm\BusinessNotificationController;
use App\Http\Controllers\ApprovalForm\ChangeOffController;
use App\Http\Controllers\Head\DashboardController;
use App\Http\Controllers\ApprovalForm\LeaveAbsenceController;
use App\Http\Controllers\ApprovalForm\LeaveController;
use App\Http\Controllers\ApprovalForm\ManpowerController;
use App\Http\Controllers\ApprovalForm\OvertimeRequestController;
use App\Http\Controllers\Head\PayrollCutOffController;
use App\Http\Controllers\Head\ProfileController;
use App\Http\Controllers\ApprovalForm\UndertimeFormController;

// Head Form as Employee
use App\Http\Controllers\Employee\AccomplishmentReportController as EmployeeAccomplishmentReportController;
use App\Http\Controllers\Employee\AnnouncementPolicyController as EmployeeAnnouncementPolicyController;
use App\Http\Controllers\Employee\BusinessNotificationController as EmployeeBusinessNotificationController;
use App\Http\Controllers\Employee\ChangeOffController as EmployeeChangeOffController;
use App\Http\Controllers\Employee\LeaveAbsenceController as EmployeeLeaveAbsenceController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\ManpowerController as EmployeeManpowerController;
use App\Http\Controllers\Employee\OvertimeRequestController as EmployeeOvertimeRequestController;
use App\Http\Controllers\Employee\PayrollCutOffController as EmployeePayrollCutOffController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use App\Http\Controllers\Employee\SalaryPayrollController as EmployeeSalaryPayrollController;
use App\Http\Controllers\Employee\UndertimeFormController as EmployeeUndertimeFormController;

use Illuminate\Support\Facades\Route;

// head.php
Route::middleware(['auth', 'user_type:Head'])->prefix('head')->name('head.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Payroll Form
    Route::controller(PayrollCutOffController::class)->group(function () {
        Route::get('/payroll-cut-off', 'index')->name('payrollcutoff.index');
        Route::post('/payroll-cut-off/store', 'store')->name('payrollcutoff.store');
        Route::post('/payroll-cut-off/update/{id}', 'update')->name('payrollcutoff.update');
        Route::get('/payroll-cut-off/{id}/attendance', 'attendancePage')->name('payrollcutoff.attendance');
        Route::post('/payroll-cut-off/{id}/approve', 'approve')->name('payrollcutoff.approve');
        Route::get('/payroll-cutoff/{id}/export', 'exportAttendance')->name('attendance.export');
    });

    // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::put('/profile/update', 'update')->name('profile.update');
    });

    Route::get('/announcements-policies', [AnnouncementPolicyController::class, 'index'])->name('announcementpolicy.index');


    // <--------------------------- HEAD form as EMployee --------------------------->
    // Accomplishment Report Form
    Route::controller(EmployeeAccomplishmentReportController::class)->group(function () {
        Route::get('/accomplishment-reports', 'index')->name('accomplishmentreports.index');
        Route::get('/accomplishment-reports/create', 'createIndex')->name('accomplishmentreports.createIndex');
        Route::post('/accomplishment-reports/store', 'store')->name('accomplishmentreports.store');
        Route::get('/accomplishment-reports/edit/{id}', 'edit')->name('accomplishmentreports.edit');
        Route::put('/accomplishment-reports/update/{id}', 'update')->name('accomplishmentreports.update');

    });

    // Change Off Form
    Route::controller(EmployeeChangeOffController::class)->group(function () {
        Route::get('/change-offs', 'index')->name('changeoffs.index');
        Route::get('/change-offs/create', 'create')->name('changeoffs.create');
        Route::post('/change-offs/store', 'store')->name('changeoffs.store');
        Route::get('/change-offs/edit/{id}', 'edit')->name('changeoffs.edit');
        Route::put('/change-offs/update/{id}', 'update')->name('changeoffs.update');
    });

    // Leave Form
    Route::controller(EmployeeLeaveController::class)->group(function () {
        Route::get('/leaves', 'index')->name('leaves.index');
        Route::get('/leaves/create', 'create')->name('leaves.create');
        Route::post('/leaves/store', 'store')->name('leaves.store');
        Route::get('/leaves/edit/{id}', 'edit')->name('leaves.edit');
        Route::put('/leave/update/{id}', 'update')->name('leaves.update');
    });

    // Leave of Absence Form
    Route::controller(EmployeeLeaveAbsenceController::class)->group(function () {
        Route::get('/leave-of-absences', 'index')->name('leaveabsences.index');
        Route::get('/leave-of-absences/create', 'create')->name('leaveabsences.create');
        Route::post('/leave-of-absences/store', 'store')->name('leaveabsences.store');
        Route::get('/leave-of-absences/edit/{id}', 'edit')->name('leaveabsences.edit');
        Route::put('/leave-of-absences/update/{id}', 'update')->name('leaveabsences.update');
    });

    // Manpower Form
    Route::controller(EmployeeManpowerController::class)->group(function () {
        Route::get('/manpowers', 'index')->name('manpowers.index');
        Route::get('/manpowers/create', 'create')->name('manpowers.create');
        Route::post('/manpowers/store', 'store')->name('manpowers.store');
        Route::get('/manpowers/edit/{id}', 'edit')->name('manpowers.edit');
        Route::put('/manpowers/update/{id}', 'update')->name('manpowers.update');
    });

    // Business Notification Form
    Route::controller(EmployeeBusinessNotificationController::class)->group(function () {
        Route::get('/business-notifications', 'index')->name('businessnotifications.index');
        Route::get('/business-notifications/create', 'create')->name('businessnotifications.create');
        Route::post('/business-notifications/store', 'store')->name('businessnotifications.store');
        Route::get('/business-notifications/edit/{id}', 'edit')->name('businessnotifications.edit');
        Route::put('/business-notifications/update/{id}', 'update')->name('businessnotifications.update');
    });

    // Overtime Request Form
    Route::controller(EmployeeOvertimeRequestController::class)->group(function () {
        Route::get('/overtime-requests', 'index')->name('overtimerequests.index');
        Route::get('/overtime-requests/create', 'create')->name('overtimerequests.create');
        Route::post('/overtime-requests/store', 'store')->name('overtimerequests.store');
        Route::get('/overtime-requests/edit/{id}', 'edit')->name('overtimerequests.edit');
        Route::put('/overtime-requests/update/{id}', 'update')->name('overtimerequests.update');
    });

    // Undertime Form
    Route::controller(EmployeeUndertimeFormController::class)->group(function () {
        Route::get('/undertime-forms', 'index')->name('undertimeforms.index');
        Route::get('/undertime-forms/create', 'create')->name('undertimeforms.create');
        Route::post('/undertime-forms/store', 'store')->name('undertimeforms.store');
        Route::get('/undertime-forms/edit/{id}', 'edit')->name('undertimeforms.edit');
        Route::put('/undertime-forms/update/{id}', 'update')->name('undertimeforms.update');
    });

    // Profile
    Route::controller(EmployeeProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::put('/profile/update', 'update')->name('profile.update');
    });

    // Announcements
    Route::get('/announcements-policies', [EmployeeAnnouncementPolicyController::class, 'index'])->name('announcementpolicy.index');

    // CutOff
    Route::controller(EmployeePayrollCutOffController::class)->group(function () {
        Route::get('/payroll-cut-offs', 'index')->name('payrollcutoffs.index');
        Route::get('/payroll-cut-offs/{id}/attendance', 'attendancePage')->name('payrollcutoffs.attendance');
    });

    // Attendance
    Route::post('/attendances/store', [EmployeeAttendanceController::class, 'store'])->name('attendances.store');

    // Salary
    Route::get('/salary-payrolls', [EmployeeSalaryPayrollController::class, 'index'])->name('salarypayrolls.index');
});
