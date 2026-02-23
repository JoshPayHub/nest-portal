<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAbsenceStatus extends Model
{
    protected $table = 'leave_absent_statuses';

    protected $fillable = [
        'leave_absent_id',
        'user_id',
        'status_id',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(LeaveAbsence::class, 'leave_absent_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class, 'status_id'); }
}
