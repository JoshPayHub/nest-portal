<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->call([
        StatusSeeder::class,
        UserTypeSeeder::class,
        DepartmentSeeder::class,
        PositionSeeder::class,
        OffSeeder::class,
        DeductionSettingSeeder::class,
        TaxBracketSeeder::class,
        SSSContributionSeeder::class,
        ]);

        // 2. Setup Default Values
        $defaultDept = 1;
        $defaultPos = 1;
        $defaultStatus = 1;

        // 3. Define the specific users you need
        $testUsers = [
            [
                'user_type_id' => 1,
                'first_name' => 'Cherry-lynn',
                'last_name' => 'Allapitan',
                'email' => 'cherry.allapitan@happiestnestmcs.com',
                'username' => 'cherry.allapitan',
                'mobile' => '09173696462'
            ],
            // [
            //     'user_type_id' => 2,
            //     'first_name' => 'Standard',
            //     'last_name' => 'Employee',
            //     'email' => 'employee@gmail.com',
            //     'username' => 'standard_user',
            //     'mobile' => '09123123122'
            // ],
            // [
            //     'user_type_id' => 3,
            //     'first_name' => 'Department',
            //     'last_name' => 'Head',
            //     'email' => 'head@gmail.com',
            //     'username' => 'depthead',
            //     'mobile' => '09123123123'
            // ],
            // [
            //     'user_type_id' => 4,
            //     'first_name' => 'Super',
            //     'last_name' => 'Admin',
            //     'email' => 'superadmin@gmail.com',
            //     'username' => 'superadmin',
            //     'mobile' => '09123123124'
            // ],
            // [
            //     'user_type_id' => 5,
            //     'first_name' => 'Supervisor',
            //     'last_name' => 'User',
            //     'email' => 'supervisor@gmail.com',
            //     'username' => 'supervisor',
            //     'mobile' => '09123123321'
            // ],
        ];

        // 4. Create Specific Test Users
        foreach ($testUsers as $user) {
            User::factory()->create([
                'user_type_id' => $user['user_type_id'],
                'first_name'   => $user['first_name'],
                'last_name'    => $user['last_name'],
                'username'     => $user['username'],
                'company_email' => $user['email'],
                'mobile_number' => $user['mobile'],
                'department_id' => $defaultDept,
                'position_id'   => $defaultPos,
                'status_id'     => $defaultStatus,
            ]);
        }
    }
}
