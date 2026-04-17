<?php

use App\Http\Controllers\Hr\AnnouncementPolicyController;
use App\Http\Controllers\Hr\DashboardController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeeListController;
use App\Http\Controllers\Hr\HolidayController;
use App\Http\Controllers\Hr\PayrollCutOffController;
use App\Http\Controllers\Hr\PositionController;
use App\Http\Controllers\Hr\SalaryEmployeeController;
use App\Http\Controllers\Hr\SalaryPayrollController;
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

    Route::get('/holiday', [HolidayController::class, 'index'])->name('holiday.index');
    Route::post('/holiday/store', [HolidayController::class, 'store'])->name('holiday.store');
    Route::post('/holiday/update/{id}', [HolidayController::class, 'update'])->name('holiday.update');

    Route::get('/payroll-cut-off', [PayrollCutOffController::class, 'index'])->name('payrollcutoff.index');
    Route::post('/payroll-cut-off/store', [PayrollCutOffController::class, 'store'])->name('payrollcutoff.store');
    Route::post('/payroll-cut-off/update/{id}', [PayrollCutOffController::class, 'update'])->name('payrollcutoff.update');
    Route::get('/payroll-cut-off/{id}/attendance', [PayrollCutOffController::class, 'attendancePage'])->name('payrollcutoff.attendance');
    Route::post('/payroll-cut-off/{id}/approve', [PayrollCutOffController::class, 'approve'])->name('payrollcutoff.approve');
    Route::get('/payroll-cutoff/{id}/export', [PayrollCutOffController::class, 'exportAttendance'])->name('attendance.export');

    Route::get('/salary-employee', [SalaryEmployeeController::class, 'index'])->name('payrollcutoff.index');
    Route::post('/salary-employee/store', [SalaryEmployeeController::class, 'store'])->name('payrollcutoff.store');
    Route::post('/salary-employee/update/{id}', [SalaryEmployeeController::class, 'update'])->name('payrollcutoff.update');

    Route::get('/salary-payroll', [SalaryPayrollController::class, 'index'])->name('salarypayroll.index');
    Route::get('/salary-payroll/{id}/list', [SalaryPayrollController::class, 'list'])->name('salarypayrolllist.list');
    Route::post('/salary-payroll/{id}/update', [SalaryPayrollController::class, 'update'])->name('salarypayrolllist.update');
    Route::post('/salary-payroll/{id}/export', [SalaryPayrollController::class, 'export'])->name('salarypayrollexport.export');

});
