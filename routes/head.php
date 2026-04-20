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

    // Payroll Form
    Route::get('/payroll-cut-off', [PayrollCutOffController::class, 'index'])->name('payrollcutoff.index');
    Route::post('/payroll-cut-off/store', [PayrollCutOffController::class, 'store'])->name('payrollcutoff.store');
    Route::post('/payroll-cut-off/update/{id}', [PayrollCutOffController::class, 'update'])->name('payrollcutoff.update');
    Route::get('/payroll-cut-off/{id}/attendance', [PayrollCutOffController::class, 'attendancePage'])->name('payrollcutoff.attendance');
    Route::post('/payroll-cut-off/{id}/approve', [PayrollCutOffController::class, 'approve'])->name('payrollcutoff.approve');
    Route::get('/payroll-cutoff/{id}/export', [PayrollCutOffController::class, 'exportAttendance'])->name('attendance.export');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');


    Route::get('/announcements-policies', [AnnouncementPolicyController::class, 'index'])->name('announcementpolicy.index');

});
