<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        DB::table('offs')->insert([
            ['id' => 1, 'name' => 'time'],
            ['id' => 2, 'name' => 'day'],
            ['id' => 3, 'name' => 'monday'],
            ['id' => 4, 'name' => 'tuesday'],
            ['id' => 5, 'name' => 'wednesday'],
            ['id' => 6, 'name' => 'thursday'],
            ['id' => 7, 'name' => 'friday'],
            ['id' => 8, 'name' => 'saturday'],
            ['id' => 9, 'name' => 'sunday'],
        ]);
    }
}
