<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            ['id' => 1, 'name' => 'active'],
            ['id' => 2, 'name' => 'inactive'],
            ['id' => 3, 'name' => 'suspended'],
            ['id' => 4, 'name' => 'pending'],
            ['id' => 5, 'name' => 'completed'],
            ['id' => 6, 'name' => 'submitted'],
            ['id' => 7, 'name' => 'approved'],
            ['id' => 8, 'name' => 'rejected'],
        ]);
    }
}
