<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('user_types')->insert([
            ['id' => 1, 'name' => 'HR'],
            ['id' => 2, 'name' => 'Employee'],
            ['id' => 3, 'name' => 'Head'],
            ['id' => 4, 'name' => 'Super_Admin'],
            ['id' => 5, 'name' => 'Client'],
        ]);
    }
}
