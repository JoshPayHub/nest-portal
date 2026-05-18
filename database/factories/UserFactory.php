<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $manilaNow = Carbon::now('Asia/Manila');

        return [
            // Foreign Keys (Defaults)
            'user_type_id' => 2,
            // 'department_id' => 1,
            // 'position_id' => 1,
            'status_id' => 1,

            // Basic Info
            'employee_id' => fake()->unique()->numerify('EMP-####'),
            'username' => fake()->unique()->userName(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other', 'Prefer not to say']),
            'date_birth' => fake()->date(),
            'civil_status' => fake()->randomElement(['Married', 'Single', 'Other']),
            'nationality' => 'Filipino',

            // Employment
            'employment_status' => 'Regular',
            'employment_type' => 'Full-Time',
            'date_hired' => fake()->date(),
            'regularization_date' => fake()->date(),
            'work_location' => 'Main Office',
            'payroll_group' => 'Monthly',

            // Contact
            'company_email' => fake()->unique()->safeEmail(),
            'company_email_verified_at' => $manilaNow,
            'personal_email' => fake()->unique()->safeEmail(),
            'mobile_number' => fake()->unique()->numerify('09#########'),
            'mobile_verified_at' => $manilaNow,
            'present_address' => fake()->address(),
            'permanent_address' => fake()->address(),
            'password' => static::$password ??= Hash::make('password'),
            'leave_pay' => 0,

            // Gov
            'sss_number' => fake()->numerify('##-#######-#'),
            'philhealth_number' => fake()->numerify('############'),
            'pagibig_number' => fake()->numerify('############'),
            'tin_number' => fake()->numerify('###-###-###-###'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_email_verified_at' => null,
            'personal_email_verified_at' => null,
            'mobile_verified_at' => null,
        ]);
    }
}
