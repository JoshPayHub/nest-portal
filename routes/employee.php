<?php

use App\Http\Controllers\Employee\AccomplishmentReportController;
use App\Http\Controllers\Employee\AnnouncementPolicyController;
use App\Http\Controllers\Employee\BusinessNotificationController;
use App\Http\Controllers\Employee\ChangeOffController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\LeaveAbsenceController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\ManpowerController;
use App\Http\Controllers\Employee\NotificationController;
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
    Route::get('/notification', [NotificationController::class, 'index'])->name('notification.index');

     // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::put('/profile/update', 'update')->name('profile.update');
        Route::put('/profile/change-password', 'changePassword')->name('profile.change-password');
    });

    // Announcements
    Route::get('/announcements-policies', [AnnouncementPolicyController::class, 'index'])->name('announcementpolicy.index');

    // Accomplishment Report Form
    Route::controller(AccomplishmentReportController::class)->group(function () {
        Route::get('/accomplishment-reports', 'index')->name('accomplishmentreports.index');
        Route::get('/accomplishment-reports/create', 'createIndex')->name('accomplishmentreports.createIndex');
        Route::post('/accomplishment-reports/store', 'store')->name('accomplishmentreports.store');
        Route::get('/accomplishment-reports/edit/{id}', 'edit')->name('accomplishmentreports.edit');
        Route::put('/accomplishment-reports/update/{id}', 'update')->name('accomplishmentreports.update');

    });

    // Change Off Form
    Route::controller(ChangeOffController::class)->group(function () {
        Route::get('/change-offs', 'index')->name('changeoffs.index');
        Route::get('/change-offs/create', 'create')->name('changeoffs.create');
        Route::post('/change-offs/store', 'store')->name('changeoffs.store');
        Route::get('/change-offs/edit/{id}', 'edit')->name('changeoffs.edit');
        Route::put('/change-offs/update/{id}', 'update')->name('changeoffs.update');
    });

    // Leave Form
    Route::controller(LeaveController::class)->group(function () {
        Route::get('/leaves', 'index')->name('leaves.index');
        Route::get('/leaves/create', 'create')->name('leaves.create');
        Route::post('/leaves/store', 'store')->name('leaves.store');
        Route::get('/leaves/edit/{id}', 'edit')->name('leaves.edit');
        Route::put('/leaves/update/{id}', 'update')->name('leaves.update');
    });

    // Leave of Absence Form
    Route::controller(LeaveAbsenceController::class)->group(function () {
        Route::get('/leave-of-absences', 'index')->name('leaveabsences.index');
        Route::get('/leave-of-absences/create', 'create')->name('leaveabsences.create');
        Route::post('/leave-of-absences/store', 'store')->name('leaveabsences.store');
        Route::get('/leave-of-absences/edit/{id}', 'edit')->name('leaveabsences.edit');
        Route::put('/leave-of-absences/update/{id}', 'update')->name('leaveabsences.update');
    });

    // Manpower Form
    Route::controller(ManpowerController::class)->group(function () {
        Route::get('/manpowers', 'index')->name('manpowers.index');
        Route::get('/manpowers/create', 'create')->name('manpowers.create');
        Route::post('/manpowers/store', 'store')->name('manpowers.store');
        Route::get('/manpowers/edit/{id}', 'edit')->name('manpowers.edit');
        Route::put('/manpowers/update/{id}', 'update')->name('manpowers.update');
    });

    // Business Notification Form
    Route::controller(BusinessNotificationController::class)->group(function () {
        Route::get('/business-notifications', 'index')->name('businessnotifications.index');
        Route::get('/business-notifications/create', 'create')->name('businessnotifications.create');
        Route::post('/business-notifications/store', 'store')->name('businessnotifications.store');
        Route::get('/business-notifications/edit/{id}', 'edit')->name('businessnotifications.edit');
        Route::put('/business-notifications/update/{id}', 'update')->name('businessnotifications.update');
    });

    // Overtime Request Form
    Route::controller(OvertimeRequestController::class)->group(function () {
        Route::get('/overtime-requests', 'index')->name('overtimerequests.index');
        Route::get('/overtime-requests/create', 'create')->name('overtimerequests.create');
        Route::post('/overtime-requests/store', 'store')->name('overtimerequests.store');
        Route::get('/overtime-requests/edit/{id}', 'edit')->name('overtimerequests.edit');
        Route::put('/overtime-requests/update/{id}', 'update')->name('overtimerequests.update');
    });

    // Undertime Form
    Route::controller(UndertimeFormController::class)->group(function () {
        Route::get('/undertime-forms', 'index')->name('undertimeforms.index');
        Route::get('/undertime-forms/create', 'create')->name('undertimeforms.create');
        Route::post('/undertime-forms/store', 'store')->name('undertimeforms.store');
        Route::get('/undertime-forms/edit/{id}', 'edit')->name('undertimeforms.edit');
        Route::put('/undertime-forms/update/{id}', 'update')->name('undertimeforms.update');
    });

    // CutOff
    Route::controller(PayrollCutOffController::class)->group(function () {
        Route::get('/payroll-cut-offs', 'index')->name('payrollcutoffs.index');
        Route::get('/payroll-cut-offs/{id}/attendance', 'attendancePage')->name('payrollcutoffs.attendance');
    });

    // Attendance
    Route::post('/attendances/store', [AttendanceController::class, 'store'])->name('attendances.store');

    // Salary
    Route::get('/salary-payrolls', [SalaryPayrollController::class, 'index'])->name('salarypayrolls.index');
});
