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
            'birth_at' => $this->faker->date('Y-m-d', '2000-01-01'),
            'address_registration' => $this->faker->address(),
            'address_residence' => $this->faker->address(),
            'address_job' => $this->faker->address(),
            'diagnose_id' => $this->faker->numberBetween(1, 50),
            'socially_dangerous' => $this->faker->boolean(),
            'disability' => $this->faker->boolean(),
            'married' => $this->faker->boolean(),
            'profession' => $this->faker->jobTitle(),
            'serial' => $this->faker->numerify('####'),
            'number' => $this->faker->numerify('######'),
            'issued_by' => $this->faker->sentence(3),
            'issued_at' => $this->faker->dateTimeBetween('-30 years', '-1 year')->format('Y-m-d'),
            'department_code' => $this->faker->numerify('###-###'),
            'birth_place' => $this->faker->city(),
            'snils' => $this->faker->numerify('###-###-### ##'),
            'oms' => $this->faker->numerify('###############'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
