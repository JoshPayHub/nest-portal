<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SssContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_salary',
        'max_salary',
        'msc',
        'ee_share',
        'er_share',
        'wisp_ee',
        'wisp_er',
        'ec_er'
    ];
}
