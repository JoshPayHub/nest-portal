<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'cut_off_date',
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

    // activities under this report
    public function activities(): HasMany
    {
        return $this->hasMany(OvertimeList::class);
    }

    // All approval logs
    public function approvalStatuses(): HasMany
    {
        return $this->hasMany(OvertimeStatus::class);
    }

    // Logic to get Leader status (Assumes User Type 2 is Leader/Head)
    public function leaderStatus()
    {
        return $this->approvalStatuses()
            ->whereHas('user', fn($q) => $q->where('user_type_id', 3))
            ->with('status')
            ->latest();
    }

    // Logic to get HR status (Assumes User Type 3 is HR)
    public function hrStatus()
    {
        return $this->approvalStatuses()
            ->whereHas('user', fn($q) => $q->where('user_type_id', 1))
            ->with('status')
            ->latest();
    }
}
