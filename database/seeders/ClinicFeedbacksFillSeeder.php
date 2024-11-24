<?php

namespace Database\Seeders;

use App\Models\ClinicFeedback;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicFeedbacksFillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClinicFeedback::factory()
            ->count(15)
            ->create();
    }
}
