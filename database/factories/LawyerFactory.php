<?php

namespace Database\Factories;

use App\Models\Lawyer;
use Illuminate\Database\Eloquent\Factories\Factory;

class LawyerFactory extends Factory
{
    protected $model = Lawyer::class;

    public function definition()
    {
        // Русские имена и фамилии
        $firstNames = ['Иван', 'Пётр', 'Александр', 'Дмитрий', 'Василий'];
        $lastNames = ['Иванов', 'Петров', 'Сидоров', 'Кузнецов', 'Смирнов'];

        // Навыки юристов
        $skillsPool = [
            'Составление договоров',
            'Юридическое консультирование',
            'Представительство в суде',
            'Регистрация компаний',
            'Составление исковых заявлений',
        ];

        // Генерация навыков для конкретного юриста
        $skills = $this->faker->randomElements($skillsPool, rand(2, 4));

        return [
            'name' => $this->faker->randomElement($firstNames),
            'surname' => $this->faker->randomElement($lastNames),
            'profession' => 'Юрист',
            'skills' => json_encode($skills),
            'base_price' => $this->faker->numberBetween(5000, 20000), // Базовая стоимость услуг
            'experience' => $this->faker->numberBetween(1, 30), // Опыт работы в годах
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
