<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeaveAbsence extends Model
{
    protected $table = 'leave_absents';

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'date_absence',
        'type_absence',
        'reason',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }

    public function status(): HasOne
    {
        return $this->hasOne(LeaveAbsenceStatus::class, 'leave_absent_id')->latestOfMany();
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(LeaveAbsenceStatus::class, 'leave_absent_id');
    }
}
