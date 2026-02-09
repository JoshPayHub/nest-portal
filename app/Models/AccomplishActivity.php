<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccomplishActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'accomplish_report_id',
        'activity_date',
        'activity',
        'remarks',
        'status_id',
    ];

    /* ==========================
     | Relationships
     |==========================*/

    // parent report
    public function report(): BelongsTo
    {
        return $this->belongsTo(AccomplishReport::class, 'accomplish_report_id');
    }

    // employee activity status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
