<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementPolicyFilter extends Model
{
    protected $fillable = [
        'announcement_policy_id',
        'department_id',
        'position_id',
    ];

    /**
     * Get the parent announcement or policy.
     */
    public function announcementPolicy(): BelongsTo
    {
        return $this->belongsTo(AnnouncementPolicy::class, 'announcement_policy_id');
    }

    /**
     * Get the department associated with this filter.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position associated with this filter.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
