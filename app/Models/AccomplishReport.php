<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccomplishReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'from_date',
        'to_date',
        'leader_status_id',
        'hr_status_id',
    ];

    /* ==========================
     | Relationships
     |==========================*/

    // employee who submitted the report
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // department snapshot
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // position snapshot
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    // leader approval status
    public function leaderStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'leader_status_id');
    }

    // HR approval status
    public function hrStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'hr_status_id');
    }

    // activities under this report
    public function activities(): HasMany
    {
        return $this->hasMany(AccomplishActivity::class);
    }
}
