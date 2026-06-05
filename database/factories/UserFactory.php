<?php

namespace Database\Factories;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        return [
            'f_name'        => fake()->firstName(),
            'l_name'        => fake()->lastName(),
            'email'         => fake()->unique()->safeEmail(),
            'password'      => Hash::make('password'),
            'role'          => UserRole::CUSTOMER,
            'gender'        => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
        ];
    }

    // ======= States =======

    public function admin(): static
    {
        return $this->state(['role' => UserRole::ADMIN]);
    }

    public function staff(): static
    {
        return $this->state(['role' => UserRole::STAFF]);
    }

    public function delivery(): static
    {
        return $this->state(['role' => UserRole::DELIVERY]);
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
