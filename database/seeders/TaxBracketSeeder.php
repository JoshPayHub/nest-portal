<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxBracket;

class TaxBracketSeeder extends Seeder
{
    public function run(): void
    {
        $brackets = [
            ['min_salary' => 0,      'max_salary' => 20833,  'base_tax' => 0,        'excess_rate' => 0.00, 'over_amount' => 0],
            ['min_salary' => 20834,  'max_salary' => 33333,  'base_tax' => 0,        'excess_rate' => 0.15, 'over_amount' => 20833],
            ['min_salary' => 33334,  'max_salary' => 66667,  'base_tax' => 1875,     'excess_rate' => 0.20, 'over_amount' => 33333],
            ['min_salary' => 66668,  'max_salary' => 166667, 'base_tax' => 8541.67,  'excess_rate' => 0.25, 'over_amount' => 66667],
            ['min_salary' => 166668, 'max_salary' => 666667, 'base_tax' => 33541.67, 'excess_rate' => 0.30, 'over_amount' => 166667],
            ['min_salary' => 666668, 'max_salary' => 9999999,'base_tax' => 183541.67,'excess_rate' => 0.35, 'over_amount' => 666667],
        ];

        foreach ($brackets as $bracket) {
            TaxBracket::updateOrCreate(['min_salary' => $bracket['min_salary']], $bracket);
        }
    }
}
