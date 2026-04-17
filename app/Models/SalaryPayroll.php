<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayroll extends Model
{
    // Pointing to the specific table name from your migration
    protected $table = 'salary_payroll';

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'attendance_employee_id',
        'status_id',
        'regular_pay',
        'absence_with_pay',
        'regular_ot',
        'rdot',
        'regular_holiday_ot',
        'special_holiday_ot',
        'rd_regular_holiday_ot',
        'rd_special_holiday_ot',
        'night_differential',
        'regular_holiday',
        'special_holiday',
        'rd_regular_holiday',
        'rd_special_holiday',
        'adjustment',
        'allowance',
        'sss',
        'pag_ibig',
        'philhealth',
        'tax',
        'salary_loan',
        'cash_advance',
        'undertime',
        'absence_without_pay',
        'flu_vaccine',
        'food',
        'total_earning',
        'total_deduction',
        'total_home_pay',
    ];

    /**
     * Relationship to the Employee (User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to the Attendance Record
     */
    public function attendanceEmployee(): BelongsTo
    {
        return $this->belongsTo(AttendanceEmployee::class);
    }

    /**
     * Relationship to the Status (Pending/Approved)
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relationship to Department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship to Position
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
