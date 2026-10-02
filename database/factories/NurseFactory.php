<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseFactory extends Factory
{
    protected $model = Nurse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'patronym' => fake()->optional()->firstName(),

            'user_id' => User::query()
                ->inRandomOrder()
                ->value('id'),
            'clinic_id' => Clinic::inRandomOrder()->value('id'),

            'profession' => fake()->randomElement([
                'Медицинская сестра',
                'Старшая медицинская сестра',
                'Процедурная медицинская сестра',
            ]),
        ];
    }
}
