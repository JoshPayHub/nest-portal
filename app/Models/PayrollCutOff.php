<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough; // Add this

class PayrollCutOff extends Model
{
    protected $fillable = [
        'name',
        'from_cutoff_date',
        'to_cutoff_date',
        'status_id',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function attendanceEmployees(): HasMany
    {
        return $this->hasMany(AttendanceEmployee::class, 'payroll_cut_off_id');
    }

    // Add this to link to the salary_payroll table
    public function salaryPayrolls(): HasManyThrough
    {
        return $this->hasManyThrough(
            SalaryPayroll::class,
            AttendanceEmployee::class,
            'payroll_cut_off_id',
            'attendance_employee_id',
            'id',
            'id'
        );
    }
}
