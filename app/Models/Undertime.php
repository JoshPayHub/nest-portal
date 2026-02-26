<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Undertime extends Model
{
    protected $fillable = [
        'user_id', 'department_id', 'position_id',
        'undertime_date', 'from_time', 'to_time',
        'total_time', 'reason'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function statuses(): HasMany { return $this->hasMany(UndertimeStatus::class); }
}
