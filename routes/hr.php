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

    Route::get('/announcement-and-policy', [AnnouncementPolicyController::class, 'index'])->name('announcementpolicy.index');
    Route::post('/announcement-and-policy/store', [AnnouncementPolicyController::class, 'store'])->name('announcementpolicy.store');
    Route::put('/announcement-and-policy/update/{id}', [AnnouncementPolicyController::class, 'update'])->name('announcementpolicy.update'); // change POST → PUT

    Route::get('/add-employees', [EmployeeController::class, 'index'])->name('addemployees.index');
    Route::post('/add-employees/store', [EmployeeController::class, 'store'])->name('addemployees.store');


    Route::get('/department', [DepartmentController::class, 'index'])->name('department.index');
    Route::post('/department/store', [DepartmentController::class, 'store'])->name('department.store');
    Route::post('/department/update/{id}', [DepartmentController::class, 'update'])->name('department.update');

    Route::get('/position', [PositionController::class, 'index'])->name('position.index');
    Route::post('/position/store', [PositionController::class, 'store'])->name('position.store');
    Route::post('/position/update/{id}', [PositionController::class, 'update'])->name('position.update');

    Route::get('/list-employee', [EmployeeListController::class, 'index'])->name('employee.index');
    Route::post('/list-employee/store', [EmployeeListController::class, 'store'])->name('employee.store');
    Route::post('/list-employee/update/{id}', [EmployeeListController::class, 'update'])->name('employee.update');
});
