<?php

use App\Http\Controllers\Employee\AccomplishmentReportController;
use App\Http\Controllers\Employee\AnnouncementPolicyController;
use App\Http\Controllers\Employee\BusinessNotificationController;
use App\Http\Controllers\Employee\ChangeOffController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\LeaveAbsenceController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\ManpowerController;
use App\Http\Controllers\Employee\OvertimeRequestController;
use App\Http\Controllers\Employee\PayrollCutOffController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\ProfileController;
use App\Http\Controllers\Employee\SalaryPayrollController;
use App\Http\Controllers\Employee\UndertimeFormController;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user_type:Employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Accomplishment Report Form
    Route::controller(AccomplishmentReportController::class)->group(function () {
        Route::get('/accomplishment-report', 'index')->name('accomplishmentreport.index');
        Route::get('/accomplishment-report/create', 'createIndex')->name('accomplishmentreport.createIndex');
        Route::post('/accomplishment-report/store', 'store')->name('accomplishmentreport.store');
        Route::get('/accomplishment-report/edit/{id}', 'edit')->name('accomplishmentreport.edit');
        Route::put('/accomplishment-report/update/{id}', 'update')->name('accomplishmentreport.update');

    });

    // Change Off Form
    Route::controller(ChangeOffController::class)->group(function () {
        Route::get('/change-off', 'index')->name('changeoff.index');
        Route::get('/change-off/create', 'create')->name('changeoff.create');
        Route::post('/change-off/store', 'store')->name('changeoff.store');
        Route::get('/change-off/edit/{id}', 'edit')->name('changeoff.edit');
        Route::put('/change-off/update/{id}', 'update')->name('changeoff.update');
    });

    // Leave Form
    Route::controller(LeaveController::class)->group(function () {
        Route::get('/leave', 'index')->name('leave.index');
        Route::get('/leave/create', 'create')->name('leave.create');
        Route::post('/leave/store', 'store')->name('leave.store');
        Route::get('/leave/edit/{id}', 'edit')->name('leave.edit');
        Route::put('/leave/update/{id}', 'update')->name('leave.update');
    });

    // Leave of Absence Form
    Route::controller(LeaveAbsenceController::class)->group(function () {
        Route::get('/leave-of-absence', 'index')->name('leaveabsence.index');
        Route::get('/leave-of-absence/create', 'create')->name('leaveabsence.create');
        Route::post('/leave-of-absence/store', 'store')->name('leaveabsence.store');
        Route::get('/leave-of-absence/edit/{id}', 'edit')->name('leaveabsence.edit');
        Route::put('/leave-of-absence/update/{id}', 'update')->name('leaveabsence.update');
    });

    // Manpower Form
    Route::controller(ManpowerController::class)->group(function () {
        Route::get('/manpower', 'index')->name('manpower.index');
        Route::get('/manpower/create', 'create')->name('manpower.create');
        Route::post('/manpower/store', 'store')->name('manpower.store');
        Route::get('/manpower/edit/{id}', 'edit')->name('manpower.edit');
        Route::put('/manpower/update/{id}', 'update')->name('manpower.update');
    });

    // Business Notification Form
    Route::controller(BusinessNotificationController::class)->group(function () {
        Route::get('/business-notification', 'index')->name('businessnotification.index');
        Route::get('/business-notification/create', 'create')->name('businessnotification.create');
        Route::post('/business-notification/store', 'store')->name('businessnotification.store');
        Route::get('/business-notification/edit/{id}', 'edit')->name('businessnotification.edit');
        Route::put('/business-notification/update/{id}', 'update')->name('businessnotification.update');
    });

    // Overtime Request Form
    Route::controller(OvertimeRequestController::class)->group(function () {
        Route::get('/overtime-request', 'index')->name('overtimerequest.index');
        Route::get('/overtime-request/create', 'create')->name('overtimerequest.create');
        Route::post('/overtime-request/store', 'store')->name('overtimerequest.store');
        Route::get('/overtime-request/edit/{id}', 'edit')->name('overtimerequest.edit');
        Route::put('/overtime-request/update/{id}', 'update')->name('overtimerequest.update');
    });

    // Undertime Form
    Route::controller(UndertimeFormController::class)->group(function () {
        Route::get('/undertime-form', 'index')->name('undertimeform.index');
        Route::get('/undertime-form/create', 'create')->name('undertimeform.create');
        Route::post('/undertime-form/store', 'store')->name('undertimeform.store');
        Route::get('/undertime-form/edit/{id}', 'edit')->name('undertimeform.edit');
        Route::put('/undertime-form/update/{id}', 'update')->name('undertimeform.update');
    });

    // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::put('/profile/update', 'update')->name('profile.update');
    });

    // Announcements
    Route::get('/announcements-policies', [AnnouncementPolicyController::class, 'index'])->name('announcementpolicy.index');

    // CutOff
    Route::controller(PayrollCutOffController::class)->group(function () {
        Route::get('/payroll-cut-off', 'index')->name('payrollcutoff.index');
        Route::get('/payroll-cut-off/{id}/attendance', 'attendancePage')->name('payrollcutoff.attendance');
    });

    // Attendance
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    // Salary
    Route::get('/salary-payroll', [SalaryPayrollController::class, 'index'])->name('salarypayroll.index');
});
