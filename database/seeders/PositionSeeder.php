<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {

    $statusIds = 1;
    $names = ['Head', 'Staff'];

    foreach ($names as $name) {
        DB::table('positions')->insert([
            'name' => $name,
            'status_id' => $statusIds,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    }
}
