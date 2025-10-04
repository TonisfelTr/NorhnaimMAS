<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\ClinicFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFeedbackFactory extends Factory
{
    protected $model = ClinicFeedback::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'mark' => random_int(1, 5),
            'clinic_id' => Clinic::inRandomOrder()->first()->id
        ];
    }
}
