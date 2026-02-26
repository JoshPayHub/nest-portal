<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManpowerStatus extends Model
{
    protected $fillable = [
        'manpower_id',
        'user_id',
        'status_id',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(LeaveAbsence::class, 'manpower_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class, 'status_id'); }
}
