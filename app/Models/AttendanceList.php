<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function report(): BelongsTo
    {
        return $this->belongsTo(AttendanceEmployee::class, 'attendance_employee_id');
    }

}
