<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceEmployee extends Model
{
    protected $table = 'attendance_employees';

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'payroll_cut_off_id',
    ];

    /**
     * Relationships
     */

    // Employee (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Position
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // Payroll Cut Off
    public function payrollCutOff()
    {
        return $this->belongsTo(PayrollCutOff::class);
    }

    // Attendance Lists (child records)
    public function attendanceLists()
    {
        return $this->hasMany(AttendanceList::class);
    }

    public function approvalStatuses(): HasMany
    {
        return $this->hasMany(AttendanceEmployeeStatus::class);
    }

    // Logic to get Leader status (Assumes User Type 2 is Leader/Head)
    public function leaderStatus()
    {
        return $this->approvalStatuses()
            ->whereHas('user', fn($q) => $q->where('user_type_id', 3))
            ->with('status')
            ->latest();
    }

    public function hrStatus()
    {
        return $this->approvalStatuses()
            ->whereHas('user', fn($q) => $q->where('user_type_id', 1))
            ->with('status')
            ->latest();
    }
}
