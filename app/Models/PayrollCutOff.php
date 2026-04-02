<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}

