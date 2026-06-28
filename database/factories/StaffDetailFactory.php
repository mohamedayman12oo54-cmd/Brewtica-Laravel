<?php

namespace Database\Factories;

use App\Models\StaffDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffDetail>
 */
class StaffDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_title'  => fake()->jobTitle(),
            'salary'     => fake()->randomFloat(2, 3000, 8000),
            'hire_date'  => fake()->dateTimeBetween('-3 years', 'now'),
            'shift'      => fake()->randomElement(['morning', 'evening', 'night']),
            'department' => fake()->randomElement(['Operations', 'Kitchen', 'Delivery', 'Management']),
        ];
    }
}
