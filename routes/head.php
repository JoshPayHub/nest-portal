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

});
