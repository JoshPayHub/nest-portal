<?php

namespace Database\Seeders;

use App\Models\SssContribution;
use Illuminate\Database\Seeder;

class SSSContributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sss = [
            // Halimbawa ng low range
            ['min_salary' => 19750, 'max_salary' => 20249.99, 'employee_share' => 900.00, 'wisp_employee' => 11.25],
            // Range para sa 30k-35k Gross
            ['min_salary' => 30250, 'max_salary' => 30749.99, 'employee_share' => 900.00, 'wisp_employee' => 483.75],
            // Range para sa 40k Gross (Nasa sample image mo: Total 1,750)
            ['min_salary' => 39750, 'max_salary' => 40249.99, 'employee_share' => 900.00, 'wisp_employee' => 850.00],
        ];

        foreach ($sss as $row) {
           SssContribution::create($row);
        }
    }
}
