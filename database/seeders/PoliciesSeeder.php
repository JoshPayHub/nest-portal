<?php

namespace Database\Seeders;

use App\Models\Policies;
use App\Models\Department;
use App\Models\Status;
use Illuminate\Database\Seeder;

class PoliciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch IDs
        $dept = Department::first();
        $status = Status::first();

        if ($dept && $status) {
            Policies::create([
                'department_id' => $dept->id,
                'status_id'     => $status->id,
                'title'         => 'Data Privacy & Security Policy',
                'description'   => 'Guidelines on how to handle sensitive client information and password management.',
            ]);

            Policies::create([
                'department_id' => $dept->id,
                'status_id'     => $status->id,
                'title'         => 'Remote Work Protocol',
                'description'   => 'Standard operating procedures for employees working from home or outside the office.',
            ]);

            Policies::create([
                'department_id' => $dept->id,
                'status_id'     => $status->id,
                'title'         => 'Employee Code of Conduct',
                'description'   => 'Expectations for professional behavior, dress code, and workplace ethics.',
            ]);
        }
    }
}
