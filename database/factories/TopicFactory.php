<?php

namespace Database\Factories;

use App\Models\Topic;
use App\Models\TopicsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TopicFactory extends Factory
{
    protected $model = Topic::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'name' => fake()->name(),
            'description' => $text = fake()->realText(),
            'photo' => null,
            'topics_category_id' => TopicsCategory::inRandomOrder()->value('id'),
            'content' => $text
        ];
    }
}
