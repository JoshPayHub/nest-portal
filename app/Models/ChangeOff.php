<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChangeOff extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }

    /**
     * Get the detailed label information for this change off.
     */
    public function label()
    {
        return $this->hasOne(ChangeOffLabel::class, 'change_off_id');
    }

    /**
     * Get the history of statuses/approvals for this request.
     */
   public function approvalStatuses(): HasMany
    {
        return $this->hasMany(ChangeOffStatus::class);
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
