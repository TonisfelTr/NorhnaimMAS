<?php

namespace Database\Seeders;

use App\Models\ArticleHashtag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleHashtagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArticleHashtag::factory()
            ->count(40)
            ->create();
    }
}
