<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user_type:Employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('management/Dashboard');
    })->name('dashboard');
});
