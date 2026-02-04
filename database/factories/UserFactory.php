<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $manilaNow = Carbon::now('Asia/Manila');

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => $manilaNow,
            'phone_verified_at' => $manilaNow,
            'password' => static::$password ??= Hash::make('password'),
            'user_type_id' => 2,
            'department_id' => 1,
            'position_id' => 1,
            'status_id' => 1,
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other', 'Prefer not to say']),
            'address' => fake()->address(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
