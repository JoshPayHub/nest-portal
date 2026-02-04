<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = [
        'name',
        'status_id',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}

