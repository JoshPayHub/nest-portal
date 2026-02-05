<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Policies extends Model
{
    protected $fillable = [
        'department_id',
        'status_id',
        'title',
        'description',
        'created_at',
        'updated_at',
    ];

     public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}

