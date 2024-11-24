<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keys = [
            'mail-host',
            'mail-port',
            'mail-username',
            'mail-password',
            'mail-encryption',
            'mail-from-address',
            'title',
            'description',
            'og_title',
            'og_description',
            'og_type',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_card',
            'twitter_image',
        ];

        foreach ($keys as $key) {
            $setting = new Setting();
            $setting->key = $key;
            $setting->save();
        }
    }
}
