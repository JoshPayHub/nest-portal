<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function approvalStatuses()
    {
    return $this->hasMany(LeaveAbsenceStatus::class, 'leave_absent_id');
    }

    // Logic to get Leader status (Assumes User Type 2 is Leader/Head)
    public function leaderStatus()
    {
        return $this->approvalStatuses()
            ->whereHas('user', fn($q) => $q->where('user_type_id', 3))
            ->with('status')
            ->latest();
    }

    // Logic to get HR status (Assumes User Type 3 is HR)
    public function hrStatus()
    {
        return $this->approvalStatuses()
            ->whereHas('user', fn($q) => $q->where('user_type_id', 1))
            ->with('status')
            ->latest();
    }
}
