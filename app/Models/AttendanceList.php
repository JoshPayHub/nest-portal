<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceList extends Model
{
    protected $table = 'attendance_list';

    protected $fillable = [
        'attendance_employee_id',
        'attendance_date',
        'time_in',
        'time_out',
    ];

    /**
     * Relationships
     */

    public function attendanceEmployee()
    {
        return $this->belongsTo(AttendanceEmployee::class);
    }
}
