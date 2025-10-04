<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;

class CreateNullClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clinic::whereId(0)->delete();

        $clinic = new Clinic();
        $clinic->id = 0;
        $clinic->name = 'Частная практика';
        $clinic->address = 'Нет адреса';
        $clinic->description = 'Нет описания';
        $clinic->phone = 'Нет телефона';
        $clinic->save();
    }
}
