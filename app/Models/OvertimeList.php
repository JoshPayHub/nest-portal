<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeList extends Model
{
    use HasFactory;

    protected $table = 'overtime_list';

    protected $fillable = [
        'overtime_id',
        'overtime_date',
        'description',
        'time_start',
        'time_end',
        'additional_hours_worked', // this is the total of the time form start to end time
    ];

    /* ==========================
     | Relationships
     |==========================*/

    // overtime
    public function overtime(): BelongsTo
    {
        return $this->belongsTo(Overtime::class, 'overtime_id');
    }

    // employee activity status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
