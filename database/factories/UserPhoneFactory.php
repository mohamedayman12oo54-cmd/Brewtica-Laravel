<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPhone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPhone>
 */
class UserPhoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => $this->faker->unique()->numerify('01#########'),
            'is_primary' => false,
        ];
    }
}
