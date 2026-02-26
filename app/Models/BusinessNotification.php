<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BusinessNotification extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'purposes',
        'reason',
        'location',
        'exact_date',
        'business_time',
        'returned_time',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }

    public function status(): HasOne
    {
        return $this->hasOne(BusinessNotificationStatus::class, 'business_notification_id')->latestOfMany();
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(BusinessNotificationStatus::class, 'business_notification_id');
    }
}
