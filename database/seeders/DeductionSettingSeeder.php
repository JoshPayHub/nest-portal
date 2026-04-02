<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeductionSetting;

class DeductionSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Cutoff 1: SSS at Tax
            ['name' => 'SSS', 'type' => 'table_based', 'cutoff_assignment' => 1],
            ['name' => 'Withholding Tax', 'type' => 'train_law', 'cutoff_assignment' => 1],

            // Cutoff 2: PhilHealth at Pag-IBIG
            ['name' => 'PhilHealth', 'type' => 'percentage', 'amount_or_rate' => 0.05, 'cutoff_assignment' => 2],
            ['name' => 'Pag-IBIG', 'type' => 'fixed', 'amount_or_rate' => 200.00, 'cutoff_assignment' => 2],
        ];

        foreach ($settings as $setting) {
            DeductionSetting::create($setting);
        }
    }
}
