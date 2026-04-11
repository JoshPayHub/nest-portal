<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeductionSetting;

class DeductionSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // PhilHealth (5% split 50/50)
            ['key' => 'philhealth_rate', 'value' => 0.05, 'description' => 'PhilHealth total premium rate'],
            ['key' => 'philhealth_min_salary', 'value' => 10000, 'description' => 'Floor salary for contribution'],
            ['key' => 'philhealth_max_salary', 'value' => 100000, 'description' => 'Ceiling salary for contribution'],

            // Pag-IBIG (HDMF)
            ['key' => 'pagibig_rate_low', 'value' => 0.01, 'description' => 'EE rate if salary <= 1500'],
            ['key' => 'pagibig_rate_high', 'value' => 0.02, 'description' => 'EE rate if salary > 1500'],
            ['key' => 'pagibig_er_rate', 'value' => 0.02, 'description' => 'Employer fixed rate'],
            ['key' => 'pagibig_salary_cap', 'value' => 10000, 'description' => 'Max Monthly Fund Salary (MFS)'],
            ['key' => 'pagibig_max_contribution', 'value' => 200, 'description' => 'Max contribution amount per side'],

            // General Payroll
            ['key' => 'days_per_month', 'value' => 26, 'description' => 'Standard working days for monthly rate calculation'],
        ];

        foreach ($settings as $setting) {
            DeductionSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
