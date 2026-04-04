<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceEmployee extends Model
{
    protected $table = 'attendance_employees';

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'payroll_cut_off_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // department snapshot
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // position snapshot
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Relationship used by AttendanceController for processing updates.
     */
    public function attendanceLists(): HasMany
    {
        return $this->hasMany(AttendanceList::class, 'attendance_employee_id');
    }

    /**
     * ALIAS: renamed back to attendances() to fix the error in PayrollCutOffController.
     * This allows both 'attendanceLists' and 'attendances' to work.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(AttendanceList::class, 'attendance_employee_id');
    }

    // All approval logs
    public function approvalStatuses(): HasMany
    {
        return $this->hasMany(AttendanceEmployeeStatus::class, 'attendance_employee_id');
    }

    // Logic to get Leader status (User Type 3 is Leader)
    public function leaderStatus(): HasOne
    {
        return $this->hasOne(AttendanceEmployeeStatus::class, 'attendance_employee_id')
            ->whereHas('user', fn($q) => $q->where('user_type_id', 3))
            ->with('status')
            ->latestOfMany();
    }

    // Logic to get HR status (User Type 1 is HR)
    public function hrStatus(): HasOne
    {
        return $this->hasOne(AttendanceEmployeeStatus::class, 'attendance_employee_id')
            ->whereHas('user', fn($q) => $q->where('user_type_id', 1))
            ->with('status')
            ->latestOfMany();
    }
}
