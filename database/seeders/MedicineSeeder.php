<?php

namespace Database\Seeders;

use App\Enums\MedicineTypesEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $antipsyhotics = [
            [
                "name" => "Хлорпромазин",
                "group" => MedicineTypesEnum::Antipsyhotics,
                "generics" => [
                    "Аминазин",
                    "Торазин",
                ],
                "half_output_time" => 23,
                "forms" => [
                    "tablets" => [
                        10,
                    ],
                    "dragees" => [
                        25, 50, 100
                    ],
                    "ampules" => [
                        2.5 => [
                            1, 2
                        ]
                    ]
                ],
                "contraindications" => [
                    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14
                ]
            ]
        ];
    }
}
