<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessNotificationStatus extends Model
{
   protected $fillable = [
        'business_notification_id',
        'user_id',
        'status_id',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(BusinessNotification::class, 'business_notification_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class, 'status_id'); }
}
