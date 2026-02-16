<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeOffLabel extends Model
{
    // Explicitly defining table as per your migration
    protected $table = 'change_off_label';

    // Disable timestamps since they are not in your migration
    public $timestamps = false;

    protected $fillable = [
        'change_off_id',
        'off_id',         // Maps to the category (Time or Day)
        'original_date',
        'new_date',
        'original_day_id', // Maps to the specific day (e.g., Monday)
        'new_day_id',      // Maps to the specific day (e.g., Wednesday)
        'original_time',
        'new_time',
    ];

    /**
     * The parent ChangeOff request.
     */
    public function changeOff(): BelongsTo
    {
        return $this->belongsTo(ChangeOff::class, 'change_off_id');
    }

    /**
     * Relationship for the category (e.g., ID 1 for Time, ID 2 for Day).
     */
    public function offType(): BelongsTo
    {
        return $this->belongsTo(Off::class, 'off_id');
    }

    /**
     * Relationship for the original day name (if category is Day).
     */
    public function originalDay(): BelongsTo
    {
        return $this->belongsTo(Off::class, 'original_day_id');
    }

    /**
     * Relationship for the new day name (if category is Day).
     */
    public function newDay(): BelongsTo
    {
        return $this->belongsTo(Off::class, 'new_day_id');
    }
}
