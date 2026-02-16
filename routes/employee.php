<?php

use App\Http\Controllers\Employee\AccomplishmentReportController;
use App\Http\Controllers\Employee\ChangeOffController;
use App\Http\Controllers\Employee\DashboardController;
use Inertia\Inertia;
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

    // Accomplishment Report Form
    Route::get('/change-off', [ChangeOffController::class, 'index'])->name('changeoff.index');
    Route::get('/change-off/create', [ChangeOffController::class, 'create'])->name('changeoff.create');
    Route::post('/change-off/store', [ChangeOffController::class, 'store'])->name('changeoff.store');
    Route::get('/change-off/edit/{id}', [ChangeOffController::class, 'edit'])->name('changeoff.edit');
    Route::put('/change-off/update/{id}', [ChangeOffController::class, 'update'])->name('changeoff.update');

});
