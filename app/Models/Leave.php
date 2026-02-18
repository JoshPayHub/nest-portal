<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Leave extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'type_leave',
        'with_pay',
        'without_pay',
        'start_date',
        'end_date',
        'total_days',
        'reason',
    ];

    /* ==========================
     | Relationships
     |==========================*/

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }

    /**
     * Get the latest status/approval for this request.
     */
    public function status(): HasOne
    {
        return $this->hasOne(LeaveStatus::class, 'leave_id')->latestOfMany();
    }

    /**
     * Get the history of all statuses/approvals (HR and Head).
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(LeaveStatus::class, 'leave_id');
    }
}
