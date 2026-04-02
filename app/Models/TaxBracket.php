<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_income',
        'max_income',
        'base_tax',
        'excess_rate',
        'subtract_from_excess'
    ];
}
