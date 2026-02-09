<?php

use App\Http\Controllers\Employee\AccomplishmentReportController;
use App\Http\Controllers\Employee\DashboardController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user_type:Employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/accomplishment-report', [AccomplishmentReportController::class, 'index'])->name('accomplishmentreport.index');
    Route::get('/accomplishment-report/create', [AccomplishmentReportController::class, 'createIndex'])->name('accomplishmentreport.createIndex');
    Route::post('/accomplishment-report/store', [AccomplishmentReportController::class, 'store'])->name('accomplishmentreport.store');
});
