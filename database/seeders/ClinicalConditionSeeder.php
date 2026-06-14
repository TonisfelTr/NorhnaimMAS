<?php

namespace Database\Seeders;

use App\Models\ClinicalCondition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicalConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Аритмия', 'slug' => 'arrhythmia', 'group' => 'cardiovascular'],
            ['name' => 'Удлинение интервала QT', 'slug' => 'qt_prolongation', 'group' => 'cardiovascular'],
            ['name' => 'Артериальная гипертензия', 'slug' => 'hypertension', 'group' => 'cardiovascular'],
            ['name' => 'Тахикардия', 'slug' => 'tachycardia', 'group' => 'cardiovascular'],
            ['name' => 'Брадикардия', 'slug' => 'bradycardia', 'group' => 'cardiovascular'],
            ['name' => 'Сердечная недостаточность', 'slug' => 'heart_failure', 'group' => 'cardiovascular'],
            ['name' => 'Эпилепсия', 'slug' => 'epilepsy', 'group' => 'neurology'],
            ['name' => 'Печеночная недостаточность', 'slug' => 'hepatic_failure', 'group' => 'gastroenterology'],
            ['name' => 'Почечная недостаточность', 'slug' => 'renal_failure', 'group' => 'nephrology'],
            ['name' => 'Сахарный диабет', 'slug' => 'diabetes', 'group' => 'endocrinology'],
            ['name' => 'Беременность', 'slug' => 'pregnancy', 'group' => 'special'],
            ['name' => 'Лактация', 'slug' => 'lactation', 'group' => 'special'],
        ];

        foreach ($items as $item) {
            ClinicalCondition::updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
