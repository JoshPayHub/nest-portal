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
        'msc',        // Added
        'ee_share',
        'er_share',   // Added
        'wisp_ee',    // Matches migration name wisp_ee
        'wisp_er',
        'ec_er'       // Added
    ];
}
