<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'amount_or_rate',
        'cutoff_assignment',
        'is_active'
    ];
}
