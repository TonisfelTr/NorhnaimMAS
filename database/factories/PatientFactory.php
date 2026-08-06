<?php

namespace Database\Factories;

use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Patient::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'patronym' => $this->faker->firstName(),
            'birth_at' => $this->faker
                ->dateTimeBetween('-85 years', '-18 years')
                ->format('Y-m-d'),

            'address_registration' => $this->faker->address(),
            'address_residence' => $this->faker->address(),
            'job_organization' => $this->faker->company(),

            // Используем только реально существующие внешние ключи.
            'doctor_id' => fn () => Doctor::query()
                ->inRandomOrder()
                ->value('id'),
            'diagnose_id' => fn () => Diagnose::query()
                ->inRandomOrder()
                ->value('id'),

            'socially_dangerous' => $this->faker->boolean(5),
            'disability' => $this->faker->boolean(10),
            'married' => $this->faker->boolean(),
            'profession' => $this->faker->jobTitle(),

            'serial' => $this->faker->numerify('####'),
            'number' => $this->faker->numerify('######'),
            'issued_by' => $this->faker->company(),
            'issued_at' => $this->faker
                ->dateTimeBetween('-30 years', '-1 year')
                ->format('Y-m-d'),
            'department_code' => $this->faker->numerify('###-###'),
            'birth_place' => $this->faker->city(),
            'snils' => $this->faker->numerify('###-###-### ##'),
            'oms' => $this->faker->numerify('################'),
        ];
    }
}
