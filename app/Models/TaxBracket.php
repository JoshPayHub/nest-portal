<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_salary',  // Updated to match migration
        'max_salary',  // Updated to match migration
        'base_tax',
        'excess_rate',
        'over_amount'  // Updated to match migration
    ];
}
