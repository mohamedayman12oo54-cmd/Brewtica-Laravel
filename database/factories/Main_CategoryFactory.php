<?php

namespace Database\Factories;

use App\Models\Main_Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Main_Category>
 */
class Main_CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
        ];
    }
}
