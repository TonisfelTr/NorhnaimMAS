<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'description' => fake()->text(),
        ];
    }
}
