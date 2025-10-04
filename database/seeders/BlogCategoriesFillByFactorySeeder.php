<?php

namespace Database\Seeders;

use App\Models\TopicsCategory;
use Illuminate\Database\Seeder;

class BlogCategoriesFillByFactorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TopicsCategory::factory()
            ->count(8)
            ->create();
    }
}
