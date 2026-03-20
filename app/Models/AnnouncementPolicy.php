<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementPolicy extends Model
{
    // Explicitly define the table name since it's plural but unconventional
    protected $table = 'announcements_policies';

    protected $fillable = [
        'status_id',
        'title',
        'description',
        'types',
    ];

    /**
     * Get the status associated with the announcement/policy.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the filters (departments/positions) for this record.
     */
    public function filters(): HasMany
    {
        return $this->hasMany(AnnouncementPolicyFilter::class, 'announcement_policy_id');
    }
}
