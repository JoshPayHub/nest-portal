<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeOffLabel extends Model
{
    protected $table = 'change_off_label';
    public $timestamps = false;

    protected $fillable = [
        'change_off_id',
        'off_id',
        'original_day_id',
        'new_day_id',
    ];

    public function changeOff(): BelongsTo
    {
        return $this->belongsTo(ChangeOff::class, 'change_off_id');
    }

    public function off(): BelongsTo
    {
        return $this->belongsTo(Off::class, 'off_id');
    }

    public function originalDay(): BelongsTo
    {
        return $this->belongsTo(Off::class, 'original_day_id');
    }

    public function newDay(): BelongsTo
    {
        return $this->belongsTo(Off::class, 'new_day_id');
    }
}
