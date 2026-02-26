<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UndertimeStatus extends Model
{
    protected $fillable = ['undertime_id', 'user_id', 'status_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class, 'status_id'); }
}
