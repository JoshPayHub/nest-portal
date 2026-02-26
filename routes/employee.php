<?php

use App\Http\Controllers\Employee\AccomplishmentReportController;
use App\Http\Controllers\Employee\BusinessNotificationController;
use App\Http\Controllers\Employee\ChangeOffController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\LeaveAbsenceController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\ManpowerController;
use App\Http\Controllers\Employee\OvertimeRequestController;
use App\Http\Controllers\Employee\UndertimeFormController;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user_type:Employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Accomplishment Report Form
    Route::get('/accomplishment-report', [AccomplishmentReportController::class, 'index'])->name('accomplishmentreport.index');
    Route::get('/accomplishment-report/create', [AccomplishmentReportController::class, 'createIndex'])->name('accomplishmentreport.createIndex');
    Route::post('/accomplishment-report/store', [AccomplishmentReportController::class, 'store'])->name('accomplishmentreport.store');
    Route::get('/accomplishment-report/edit/{id}', [AccomplishmentReportController::class, 'edit'])->name('accomplishmentreport.edit');
    Route::put('/accomplishment-report/update/{id}', [AccomplishmentReportController::class, 'update'])->name('accomplishmentreport.update');

    // Change Off Form
    Route::get('/change-off', [ChangeOffController::class, 'index'])->name('changeoff.index');
    Route::get('/change-off/create', [ChangeOffController::class, 'create'])->name('changeoff.create');
    Route::post('/change-off/store', [ChangeOffController::class, 'store'])->name('changeoff.store');
    Route::get('/change-off/edit/{id}', [ChangeOffController::class, 'edit'])->name('changeoff.edit');
    Route::put('/change-off/update/{id}', [ChangeOffController::class, 'update'])->name('changeoff.update');

    // Leave Form
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::get('/leave/create', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/leave/store', [LeaveController::class, 'store'])->name('leave.store');
    Route::get('/leave/edit/{id}', [LeaveController::class, 'edit'])->name('leave.edit');
    Route::put('/leave/update/{id}', [LeaveController::class, 'update'])->name('leave.update');

    // Leave of Absence Form
    Route::get('/leave-of-absence', [LeaveAbsenceController::class, 'index'])->name('leaveabsence.index');
    Route::get('/leave-of-absence/create', [LeaveAbsenceController::class, 'create'])->name('leaveabsence.create');
    Route::post('/leave-of-absence/store', [LeaveAbsenceController::class, 'store'])->name('leaveabsence.store');
    Route::get('/leave-of-absence/edit/{id}', [LeaveAbsenceController::class, 'edit'])->name('leaveabsence.edit');
    Route::put('/leave-of-absence/update/{id}', [LeaveAbsenceController::class, 'update'])->name('leaveabsence.update');

    // Manpower Form
    Route::get('/manpower', [ManpowerController::class, 'index'])->name('manpower.index');
    Route::get('/manpower/create', [ManpowerController::class, 'create'])->name('manpower.create');
    Route::post('/manpower/store', [ManpowerController::class, 'store'])->name('manpower.store');
    Route::get('/manpower/edit/{id}', [ManpowerController::class, 'edit'])->name('manpower.edit');
    Route::put('/manpower/update/{id}', [ManpowerController::class, 'update'])->name('manpower.update');

    // Business Notification Form
    Route::get('/business-notification', [BusinessNotificationController::class, 'index'])->name('businessnotification.index');
    Route::get('/business-notification/create', [BusinessNotificationController::class, 'create'])->name('businessnotification.create');
    Route::post('/business-notification/store', [BusinessNotificationController::class, 'store'])->name('businessnotification.store');
    Route::get('/business-notification/edit/{id}', [BusinessNotificationController::class, 'edit'])->name('businessnotification.edit');
    Route::put('/business-notification/update/{id}', [BusinessNotificationController::class, 'update'])->name('businessnotification.update');

    // Overtime Request Form
    Route::get('/overtime-request', [OvertimeRequestController::class, 'index'])->name('overtimerequest.index');
    Route::get('/overtime-request/create', [OvertimeRequestController::class, 'create'])->name('overtimerequest.create');
    Route::post('/overtime-request/store', [OvertimeRequestController::class, 'store'])->name('overtimerequest.store');
    Route::get('/overtime-request/edit/{id}', [OvertimeRequestController::class, 'edit'])->name('overtimerequest.edit');
    Route::put('/overtime-request/update/{id}', [OvertimeRequestController::class, 'update'])->name('overtimerequest.update');

    // Undertime Form
    Route::get('/undertime-form', [UndertimeFormController::class, 'index'])->name('undertimeform.index');
    Route::get('/undertime-form/create', [UndertimeFormController::class, 'create'])->name('undertimeform.create');
    Route::post('/undertime-form/store', [UndertimeFormController::class, 'store'])->name('undertimeform.store');
    Route::get('/undertime-form/edit/{id}', [UndertimeFormController::class, 'edit'])->name('undertimeform.edit');
    Route::put('/undertime-form/update/{id}', [UndertimeFormController::class, 'update'])->name('undertimeform.update');
});
