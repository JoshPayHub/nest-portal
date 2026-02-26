<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Manpower extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'report_to',
        'date_required',
        'position_type',
        'replacement_for',
        'job_description',
        'justification',
        'status_type',
        'payment_type',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }

    public function status(): HasOne
    {
        return $this->hasOne(ManpowerStatus::class, 'manpower_id')->latestOfMany();
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(ManpowerStatus::class, 'manpower_id');
    }
}
