<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestInterpretation;
use App\Models\TestItem;
use App\Models\TestItemOption;
use App\Models\TestKey;
use App\Models\TestSection;
use Illuminate\Database\Seeder;

class TestFullSeeder extends Seeder
{
    public function run(): void
    {
        TestInterpretation::truncate();
        TestKey::truncate();
        TestItemOption::truncate();
        TestItem::truncate();
        TestSection::truncate();
        Test::truncate();

        // Шкала депрессии Зунга (SDS)
        $test = Test::create([
            'code' => 'SDS',
            'name' => 'Шкала депрессии Зунга (SDS)',
            'description' => 'Шкала самодиагностики уровня депрессии',
            'status' => 'часто',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя подавленным и грустным",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Утром я чувствую себя хуже всего",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне хочется плакать или я плачу",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "У меня плохо спится по ночам",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я ем так же, как обычно",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне приятно общаться с противоположным полом",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я замечаю, что теряю вес",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Меня беспокоят запоры",
            'order' => 8
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Моё сердце бьётся быстрее, чем обычно",
            'order' => 9
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я устаю без видимой причины",
            'order' => 10
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я мыслю так же ясно, как всегда",
            'order' => 11
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне легко выполнять то, что я делаю",
            'order' => 12
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую беспокойство и не могу усидеть на месте",
            'order' => 13
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "У меня есть надежда на будущее",
            'order' => 14
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я более раздражителен, чем обычно",
            'order' => 15
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне легко принимать решения",
            'order' => 16
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую, что полезен и нужен",
            'order' => 17
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я живу достаточно полной жизнью",
            'order' => 18
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я считаю себя интересным человеком",
            'order' => 19
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я наслаждаюсь тем, что раньше доставляло удовольствие",
            'order' => 20
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда или редко',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда или постоянно',
            'value' => 4,
            'order' => 3
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем вопросам',
            'item_ids' => json_encode($itemIds)
        ]);

        TestInterpretation::insert([
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 20,
                'max_score' => 44,
                'text' => 'Норма',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 45,
                'max_score' => 59,
                'text' => 'Лёгкая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 60,
                'max_score' => 69,
                'text' => 'Умеренная депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 70,
                'max_score' => 80,
                'text' => 'Тяжёлая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);



        // Шкала депрессии Бека (BDI)
        $test = Test::create([
            'code' => 'BDI',
            'name' => 'Шкала депрессии Бека (BDI)',
            'description' => 'Классическая шкала для оценки выраженности депрессии',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я не чувствую себя расстроенным",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя унылым большую часть времени",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя несчастным всё время",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я такой несчастный, что не могу этого вынести",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я не чувствую себя обделённым",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую, что меня недооценивают",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя неудачником",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую, что всё в жизни бесполезно",
            'order' => 8
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я не испытываю чувства вины",
            'order' => 9
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я часто чувствую вину",
            'order' => 10
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя плохим человеком",
            'order' => 11
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я ненавижу себя",
            'order' => 12
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я не думаю, что выгляжу хуже других",
            'order' => 13
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я обеспокоен своей внешностью",
            'order' => 14
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я считаю, что выгляжу непривлекательно",
            'order' => 15
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я думаю, что отвратителен",
            'order' => 16
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я могу работать так же хорошо, как и раньше",
            'order' => 17
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне трудно заставить себя делать что-либо",
            'order' => 18
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я должен прилагать большие усилия, чтобы начать что-либо",
            'order' => 19
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я вообще ничего не делаю",
            'order' => 20
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем нет',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Немного',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно',
            'value' => 3,
            'order' => 3
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем вопросам',
            'item_ids' => json_encode($itemIds)
        ]);

        TestInterpretation::insert([
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 0,
                'max_score' => 9,
                'text' => 'Норма',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 10,
                'max_score' => 18,
                'text' => 'Лёгкая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 19,
                'max_score' => 29,
                'text' => 'Умеренная депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 30,
                'max_score' => 63,
                'text' => 'Тяжёлая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // HADS (тревожность)
        $test = Test::create([
            'code' => 'HADS-A',
            'name' => 'HADS (тревожность)',
            'description' => 'Подшкала тревожности госпитальной шкалы HADS',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую напряжение, мне не по себе",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "У меня бывают вспышки паники",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую, как у меня дрожат руки",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне кажется, что я нахожусь на грани срыва",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую беспокойство",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне трудно уснуть из-за беспокойства",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "У меня появляется ощущение страха без причины",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем вопросам',
            'item_ids' => json_encode($itemIds)
        ]);

        TestInterpretation::insert([
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 0,
                'max_score' => 7,
                'text' => 'Норма',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 8,
                'max_score' => 10,
                'text' => 'Пограничное состояние',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 11,
                'max_score' => 21,
                'text' => 'Выраженная тревога',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);



        // GAD-7
        $test = Test::create([
            'code' => 'GAD7',
            'name' => 'GAD-7',
            'description' => 'Шкала генерализованного тревожного расстройства',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувствовали ли вы себя нервным, тревожным или напряжённым?",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Не могли перестать волноваться или контролировать беспокойство?",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Слишком сильно волновались по различным поводам?",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "С трудом могли расслабиться?",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувствовали себя настолько беспокойно, что вам было трудно усидеть на месте?",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Легко раздражались или становились раздражённым?",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувствовали страх, будто что-то ужасное может произойти?",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем вопросам',
            'item_ids' => json_encode($itemIds)
        ]);

        TestInterpretation::insert([
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 0,
                'max_score' => 4,
                'text' => 'Минимальная тревожность',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 5,
                'max_score' => 9,
                'text' => 'Лёгкая тревожность',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 10,
                'max_score' => 14,
                'text' => 'Умеренная тревожность',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 15,
                'max_score' => 21,
                'text' => 'Тяжёлая тревожность',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // PHQ-9
        $test = Test::create([
            'code' => 'PHQ9',
            'name' => 'PHQ-9',
            'description' => 'Шкала самодиагностики депрессии PHQ-9',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Малый интерес или удовольствие от дел",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувство подавленности, безысходности",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Проблемы со сном: трудно заснуть, спать слишком много или прерывается сон",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Усталость или недостаток энергии",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Потеря аппетита или переедание",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувство никчемности или вины",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Затруднения с концентрацией внимания",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Замедленность движений или, наоборот, беспокойство",
            'order' => 8
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мысли о причинении себе вреда или смерти",
            'order' => 9
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Беспокоило несколько дней',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Более половины дней',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти каждый день',
            'value' => 3,
            'order' => 3
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем вопросам',
            'item_ids' => json_encode($itemIds)
        ]);

        TestInterpretation::insert([
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 0,
                'max_score' => 4,
                'text' => 'Минимальная депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 5,
                'max_score' => 9,
                'text' => 'Лёгкая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 10,
                'max_score' => 14,
                'text' => 'Умеренная депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 15,
                'max_score' => 19,
                'text' => 'Умеренно тяжёлая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 20,
                'max_score' => 27,
                'text' => 'Тяжёлая депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);



        // HADS (депрессия)
        $test = Test::create([
            'code' => 'HADS-D',
            'name' => 'HADS (депрессия)',
            'description' => 'Подшкала депрессии госпитальной шкалы HADS',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я в хорошем настроении",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я смеюсь с удовольствием",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую радость",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя бодро",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую удовольствие от обычных занятий",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую интерес к происходящему",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую, что моя жизнь наполнена смыслом",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всё время',
            'value' => 3,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 1,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не чувствую этого',
            'value' => 0,
            'order' => 3
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем вопросам',
            'item_ids' => json_encode($itemIds)
        ]);

        TestInterpretation::insert([
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 0,
                'max_score' => 7,
                'text' => 'Норма',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 8,
                'max_score' => 10,
                'text' => 'Пограничное состояние',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'test_id' => $test->id,
                'key_id' => $key->id,
                'min_score' => 11,
                'max_score' => 21,
                'text' => 'Выраженная депрессия',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // EPQ (Айзенк)
        $test = Test::create([
            'code' => 'EPQ',
            'name' => 'Опросник Айзенка (EPQ)',
            'description' => 'Шкала для оценки экстраверсии, нейротизма и психотизма',
            'status' => 'официально',
            'type' => 'опросник'
        ]);
        $itemIds = [];

        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Любите ли вы бывать в шумных компаниях?",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Да',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Нет',
            'value' => 0,
            'order' => 1
        ]);

        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Часто ли вы смеётесь над неприличными шутками?",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Да',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Нет',
            'value' => 0,
            'order' => 1
        ]);

        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Считаете ли вы себя беззаботным человеком?",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Да',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Нет',
            'value' => 0,
            'order' => 1
        ]);

        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Нравится ли вам быть в центре внимания?",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Да',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Нет',
            'value' => 0,
            'order' => 1
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала EPQ',
            'description' => 'Подсчёт баллов экстраверсии/нейротизма',
            'item_ids' => json_encode($itemIds)
        ]);



        // PANSS
        $test = Test::create([
            'code' => 'PANSS',
            'name' => 'PANSS (позитивные/негативные симптомы)',
            'description' => 'Оценка симптоматики при шизофрении',
            'status' => 'официально',
            'type' => 'клинический'
        ]);

        $section = TestSection::create([
            'test_id' => $test->id,
            'title' => 'Позитивная симптоматика'
        ]);
        $itemIds = [];

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Бредовые идеи",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Концептуальные расстройства мышления",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Галлюцинаторное поведение",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Шкала - ' . $section->title,
            'description' => 'Сумма по разделу ' . $section->title,
            'item_ids' => json_encode($itemIds)
        ]);

        $section = TestSection::create([
            'test_id' => $test->id,
            'title' => 'Негативная симптоматика'
        ]);
        $itemIds = [];

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Эмоциональная отгороженность",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Пассивность в социальной жизни",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Плохой контакт с окружающими",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Шкала - ' . $section->title,
            'description' => 'Сумма по разделу ' . $section->title,
            'item_ids' => json_encode($itemIds)
        ]);

        $section = TestSection::create([
            'test_id' => $test->id,
            'title' => 'Общая психопатология'
        ]);
        $itemIds = [];

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Тревожность",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Чувство вины",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $item = TestItem::create([
            'test_id' => $test->id,
            'section_id' => $section->id,
            'text' => "Напряжённость",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        for ($v = 1; $v <= 7; $v++) {
            TestItemOption::create([
                'item_id' => $item->id,
                'label' => strval($v),
                'value' => $v,
                'order' => $v
            ]);
        }

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Шкала - ' . $section->title,
            'description' => 'Сумма по разделу ' . $section->title,
            'item_ids' => json_encode($itemIds)
        ]);

        // Шкала депрессии Гамильтона (HAMD)
        $test = Test::create([
            'code' => 'HAMD',
            'name' => 'Шкала депрессии Гамильтона (HAMD)',
            'description' => 'Клиническое интервью для оценки выраженности депрессии',
            'status' => 'официально',
            'type' => 'интервью'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Депрессия (грусть, печаль, безысходность)",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувство вины",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Суицидальные мысли",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Бессонница (в начале ночи)",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Бессонница (в середине ночи)",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Бессонница (под утро)",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Работоспособность и интерес к деятельности",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем пунктам',
            'item_ids' => json_encode($itemIds)
        ]);



        // Шкала депрессии Монтгомери-Асберг (MADRS)
        $test = Test::create([
            'code' => 'MADRS',
            'name' => 'Шкала депрессии Монтгомери-Асберг (MADRS)',
            'description' => 'Интервью для оценки тяжести депрессии',
            'status' => 'официально',
            'type' => 'интервью'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Явное подавленное настроение",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Пониженный интерес",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Снижение сна",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Снижение аппетита",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Затруднённое мышление",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Внутреннее напряжение",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Пессимистические мысли",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 6
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем пунктам',
            'item_ids' => json_encode($itemIds)
        ]);

        // Y-BOCS
        $test = Test::create([
            'code' => 'YBOCS',
            'name' => 'Y-BOCS',
            'description' => 'Шкала оценки обсессивно-компульсивных симптомов',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Сколько времени в день вы заняты навязчивыми мыслями?",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько они мешают вашей повседневной деятельности?",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько они вызывают у вас дискомфорт?",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько вы стараетесь им сопротивляться?",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько вы контролируете навязчивости?",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Сколько времени уходит на компульсивные действия?",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько они мешают вашей повседневной деятельности?",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько они вызывают у вас дискомфорт?",
            'order' => 8
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько вы стараетесь им сопротивляться?",
            'order' => 9
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Насколько вы контролируете компульсии?",
            'order' => 10
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '0',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 4
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по 10 пунктам',
            'item_ids' => json_encode($itemIds)
        ]);



        // SCL-90-R
        $test = Test::create([
            'code' => 'SCL90',
            'name' => 'SCL-90-R',
            'description' => 'Симптоматический чек-лист 90',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Усталость или слабость",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Нарушения сна",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Слишком быстрая раздражительность",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Невозможность сосредоточиться",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Напряжённость или беспокойство",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мысли о смерти или самоубийстве",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Ощущение, что окружающие наблюдают за вами",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Проблемы с желудком",
            'order' => 8
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Чувство вины",
            'order' => 9
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Навязчивые мысли",
            'order' => 10
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Совсем не беспокоило',
            'value' => 0,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Слабо беспокоило',
            'value' => 1,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Умеренно беспокоило',
            'value' => 2,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Сильно беспокоило',
            'value' => 3,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Очень сильно беспокоило',
            'value' => 4,
            'order' => 4
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по всем пунктам',
            'item_ids' => json_encode($itemIds)
        ]);
// BPRS
        $test = Test::create([
            'code' => 'BPRS',
            'name' => 'BPRS',
            'description' => 'Краткая психиатрическая рейтинговая шкала',
            'status' => 'официально',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Тревожность",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Эмоциональная отрешенность",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Концептуальные расстройства мышления",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Враждебность",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Подозрительность",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Необычные восприятия",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Психомоторное возбуждение",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '1',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '2',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '3',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '4',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '5',
            'value' => 5,
            'order' => 4
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '6',
            'value' => 6,
            'order' => 5
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => '7',
            'value' => 7,
            'order' => 6
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Сумма баллов по 7-балльной шкале',
            'item_ids' => json_encode($itemIds)
        ]);



        // CAPE-42
        $test = Test::create([
            'code' => 'CAPE42',
            'name' => 'CAPE-42',
            'description' => 'Шкала оценки психотических переживаний',
            'status' => 'часто',
            'type' => 'опросник'
        ]);

        $itemIds = [];
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Иногда мне кажется, что люди читают мои мысли",
            'order' => 1
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я слышу голос, когда никто не говорит",
            'order' => 2
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне кажется, что за мной следят",
            'order' => 3
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Мне тяжело испытывать радость",
            'order' => 4
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую себя не таким, как другие люди",
            'order' => 5
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я вижу вещи, которых не видят другие",
            'order' => 6
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);
        $item = TestItem::create([
            'test_id' => $test->id,
            'text' => "Я чувствую, что мои мысли не мои",
            'order' => 7
        ]);
        $itemIds[] = $item->id;
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Никогда',
            'value' => 1,
            'order' => 0
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Редко',
            'value' => 2,
            'order' => 1
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Иногда',
            'value' => 3,
            'order' => 2
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Часто',
            'value' => 4,
            'order' => 3
        ]);
        TestItemOption::create([
            'item_id' => $item->id,
            'label' => 'Почти всегда',
            'value' => 5,
            'order' => 4
        ]);

        $key = TestKey::create([
            'test_id' => $test->id,
            'title' => 'Общая шкала',
            'description' => 'Частота психотических симптомов',
            'item_ids' => json_encode($itemIds)
        ]);

    }
}
