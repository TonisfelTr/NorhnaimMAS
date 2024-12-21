<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleHashtag;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleHashtagFactory extends Factory
{
    protected $model = ArticleHashtag::class;

    public function definition(): array
    {
        return [
            'hashtag' => fake()->word(),
            'article_id' => Article::inRandomOrder()->first()->id
        ];
    }
}
