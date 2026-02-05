<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Status;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed lookup tables first (The Dependencies)
        $this->call([
            UserTypeSeeder::class,
            StatusSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            AnnouncementSeeder::class,
            PoliciesSeeder::class,
        ]);

        // 2. Get default IDs to prevent foreign key errors
        $defaultDept = Department::first()->id ?? 1;
        $defaultPos = Position::first()->id ?? 1;
        $defaultStatus = Status::first()->id ?? 1;

        // 3. Create Specific Test Users
        User::factory()->create([
            'user_type_id' => 1,
            'name' => 'HR Admin',
            'email' => 'hr@gmail.com',
            'phone' => '09123123121',
            'department_id' => $defaultDept,
            'position_id' => $defaultPos,
            'status_id' => $defaultStatus,
        ]);

        User::factory()->create([
            'user_type_id' => 2,
            'name' => 'Standard Employee',
            'email' => 'employee@gmail.com',
            'phone' => '09123123122',
            'department_id' => $defaultDept,
            'position_id' => $defaultPos,
            'status_id' => $defaultStatus,
        ]);

        User::factory()->create([
            'user_type_id' => 3,
            'name' => 'Department Head',
            'email' => 'head@gmail.com',
            'phone' => '09123123123',
            'department_id' => $defaultDept,
            'position_id' => $defaultPos,
            'status_id' => $defaultStatus,
        ]);

        User::factory()->create([
            'user_type_id' => 4,
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'phone' => '09123123124',
            'department_id' => $defaultDept,
            'position_id' => $defaultPos,
            'status_id' => $defaultStatus,
        ]);

        // 4. Create Random Bulk Users
        // These will use the random logic defined in your UserFactory
        User::factory(30)->create();

        // Specific batches
        User::factory(30)->create([
            'user_type_id' => 4,
            'department_id' => $defaultDept,
            'position_id' => $defaultPos,
            'status_id' => $defaultStatus,
        ]);

        User::factory(30)->create([
            'user_type_id' => 2,
            'department_id' => $defaultDept,
            'position_id' => $defaultPos,
            'status_id' => $defaultStatus,
        ]);
    }
}
