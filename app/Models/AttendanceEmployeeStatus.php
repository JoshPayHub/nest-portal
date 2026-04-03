<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEmployeeStatus extends Model
{
    protected $table = 'attendance_statuses';
    protected $fillable = [
        'attendance_employee_id',
        'employee_id',   // who submitted
        'approval_id',   // who approved
        'status_id',
    ];

    // The attendance record this status belongs to
    public function attendanceEmployee(): BelongsTo
    {
        return $this->belongsTo(AttendanceEmployee::class);
    }

    // The employee who submitted the attendance
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // The user who approved this status (HR / Leader)
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_id');
    }

    // The actual status (Approved, Pending, etc.)
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
