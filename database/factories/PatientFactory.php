<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Patient::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'patronym' => $this->faker->firstName(),
            'birth_at' => $this->faker->date('Y-m-d', '2000-01-01'), // Рандомная дата рождения до 2000 года
            'address_registration' => $this->faker->address(),
            'address_residence' => $this->faker->address(),
            'address_job' => $this->faker->address(),
            'diagnose_id' => $this->faker->numberBetween(1, 50), // Примерный диапазон ID диагнозов
            'socially_dangerous' => $this->faker->boolean(),
            'disability' => $this->faker->boolean(),
            'married' => $this->faker->boolean(),
            'profession' => $this->faker->jobTitle(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
