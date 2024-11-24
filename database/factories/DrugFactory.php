<?php

namespace Database\Factories;

use App\Enums\MedicineTypesEnum;
use App\Models\Drug;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugFactory extends Factory
{
    protected $model = Drug::class;

    private function receptors(): array {
        $receptors = [
            "D-1", "D-2", "D-3", "D-4", "D-5",
            "5HT-1A", "5HT-1B", "5HT-1C", "5HT-1D", "5HT-1E", "5HT-1F", "5HT-2A", "5HT-2B", "5HT-2C",
            "5HT-3", "5HT-4", "5HT-5A", "5HT-5B", "5HT-6", "5HT-7",
            "a-1A", "a-1B", "a-1D", "a-2A", "a-2B", "a-2D", "b-1", "b-2", "b-3",
            "H-1", "H-2", "H-3", "H-4",
            "ACh-N", "ACh-M1", "ACh-M2", "ACh-M3", "ACh-M4", "ACh-M5",
        ];
        $result = [];

        $count = random_int(0, 37);
        for($i = 0; $i <= $count; $i++) {
            $random = random_int(0, 37);
            $result[] = $receptors[$random];
        }

        return $result;
    }

    private function group(): MedicineTypesEnum{
        return MedicineTypesEnum::cases()[random_int(0, 9)];
    }

    private function generics(): string {
        $max = random_int(1, 4);
        $result = [];
        for($i = 1; $i <= $max; $i++) {
            $result[] = ucfirst(fake()->word());
        }

        return json_encode(implode(', ', $result));
    }

    public function definition(): array {
        return [
            'name' => fake()->word(),
            'group' => $this->group(),
            'latin_name' => fake()->word(),
            'ht_output_to' => $min = fake()->randomNumber('2'),
            'ht_output_from' => fake()->numberBetween('1', $min),
            'forms' => json_encode([
                "tablets" => [
                    fake()->numberBetween(3, $min = fake()->numberBetween(4)),
                    $min
                ],
            ]),
            'receptors' => $this->receptors(),
            'generics' => $this->generics()
        ];
    }
}
