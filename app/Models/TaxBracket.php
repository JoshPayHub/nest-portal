<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_salary',
        'max_salary',
        'base_tax',
        'excess_rate',
        'over_amount'
    ];
}
