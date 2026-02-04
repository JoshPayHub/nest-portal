<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}

