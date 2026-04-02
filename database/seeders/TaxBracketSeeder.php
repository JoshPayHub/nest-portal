<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxBracket;

class TaxBracketSeeder extends Seeder
{
    public function run(): void
    {
        $brackets = [
            [
                'min_income' => 0,
                'max_income' => 20833,
                'base_tax' => 0,
                'excess_rate' => 0,
                'subtract_from_excess' => 0
            ],
            [
                'min_income' => 20833.01,
                'max_income' => 33332,
                'base_tax' => 0,
                'excess_rate' => 0.15,
                'subtract_from_excess' => 20833
            ],
            [
                'min_income' => 33333,
                'max_income' => 66666,
                'base_tax' => 1875,
                'excess_rate' => 0.20,
                'subtract_from_excess' => 33333
            ],
            [
                'min_income' => 66667,
                'max_income' => 166666,
                'base_tax' => 8541.80,
                'excess_rate' => 0.25,
                'subtract_from_excess' => 66667
            ],
            [
                'min_income' => 166667,
                'max_income' => 666666,
                'base_tax' => 33541.80,
                'excess_rate' => 0.30,
                'subtract_from_excess' => 166667
            ],
            [
                'min_income' => 666667,
                'max_income' => 1000000,
                'base_tax' => 183541.80,
                'excess_rate' => 0.35,
                'subtract_from_excess' => 666667
            ],
        ];

        foreach ($brackets as $bracket) {
            TaxBracket::create($bracket);
        }
    }
}
