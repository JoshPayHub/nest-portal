<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
       DB::table('user_types')->insert([
            ['id' => 1, 'name' => 'HR'],
            ['id' => 2, 'name' => 'Employee'],
            ['id' => 3, 'name' => 'Head'],
            // ['id' => 4, 'name' => 'Admin'],
            // ['id' => 5, 'name' => 'Supervisor'],
        ]);
    }
}
