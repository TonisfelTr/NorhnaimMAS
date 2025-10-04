<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'name' => fake()->title(),
            'content' => fake()->realText(),
            'trial_begin_date' => fake()->dateTime(),
            'user_id' => 1,
            'authors' => json_encode([fake()->firstName(), fake()->firstName(), fake()->firstName()])
        ];
    }
}
