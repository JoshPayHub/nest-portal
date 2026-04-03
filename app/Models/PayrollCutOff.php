<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // ✅ ADD THIS (VERY IMPORTANT)
    public function attendanceEmployees(): HasMany
    {
        return $this->hasMany(AttendanceEmployee::class, 'payroll_cut_off_id');
    }
}
