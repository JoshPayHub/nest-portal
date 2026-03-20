<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {

    $statusIds = 1;
    $names = [
        'IT Department',
        'Admin Department',
        'HR Department',
        'Finance Department',
        'Accounting Department',
        'Marketing Department',
        'Sales Department',
        'Operations Department',
        'Customer Service Department',
        'Procurement Department',
        'Research and Development Department',
        'Legal Department'
    ];

    foreach ($names as $name) {
        DB::table('departments')->insert([
            'name' => $name,
            'status_id' => $statusIds,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    }
}
