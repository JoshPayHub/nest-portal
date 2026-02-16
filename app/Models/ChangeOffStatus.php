<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeOffStatus extends Model
{
    protected $fillable = [
        'change_off_id',
        'user_id',
        'status_id',
    ];

    public function changeOff(): BelongsTo { return $this->belongsTo(ChangeOff::class); }

    /**
     * The user who performed this status change (e.g., the HR or Manager).
     */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /**
     * The actual status (Approved, Pending, etc.).
     */
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
