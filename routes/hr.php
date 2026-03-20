<?php

use App\Http\Controllers\Hr\AnnouncementPolicyController;
use App\Http\Controllers\Hr\DashboardController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeeListController;
use App\Http\Controllers\Hr\PositionController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

// hr.php
Route::middleware(['auth', 'user_type:HR'])->prefix('hr')->name('hr.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(AnnouncementPolicyController::class)->group(function () {
        Route::get('/announcements-policies', 'index')->name('announcementpolicy.index');
        Route::post('/announcements-policies', 'store')->name('announcementpolicy.store');
        Route::post('/announcements-policies/{announcementPolicy}', 'update')->name('announcementpolicy.update');
        Route::delete('/announcements-policies/{announcementPolicy}', 'destroy')->name('announcementpolicy.destroy');
    });

    Route::get('/add-employees', [EmployeeController::class, 'index'])->name('addemployees.index');
    Route::post('/add-employees/store', [EmployeeController::class, 'store'])->name('addemployees.store');
    Route::get('/employees/edit/{user}', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/update/{user}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::get('/list-employee', [EmployeeListController::class, 'index'])->name('employee.index');

    Route::get('/department', [DepartmentController::class, 'index'])->name('department.index');
    Route::post('/department/store', [DepartmentController::class, 'store'])->name('department.store');
    Route::post('/department/update/{id}', [DepartmentController::class, 'update'])->name('department.update');

    Route::get('/position', [PositionController::class, 'index'])->name('position.index');
    Route::post('/position/store', [PositionController::class, 'store'])->name('position.store');
    Route::post('/position/update/{id}', [PositionController::class, 'update'])->name('position.update');
});
