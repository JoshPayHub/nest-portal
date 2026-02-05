<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Status;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing IDs so the foreign keys work
        $dept = Department::first();
        $status = Status::first();

        // Safety check: only seed if we have a department and status
        if ($dept && $status) {
            Announcement::create([
                'department_id' => $dept->id,
                'status_id'     => $status->id,
                'title'         => 'Welcome to the Team!',
                'description'   => 'We are excited to announce our new office expansion project.',
            ]);

            Announcement::create([
                'department_id' => $dept->id,
                'status_id'     => $status->id,
                'title'         => 'Quarterly Meeting',
                'description'   => 'The general assembly will be held this Friday at 3:00 PM.',
            ]);
        }
    }
}
