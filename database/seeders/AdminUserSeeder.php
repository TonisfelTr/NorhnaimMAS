<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('email', 'admin@admin.ru')->delete();

        User::create([
            'login' => 'Admin',
            'password' => Hash::make('password'),
            'email' => 'admin@admin.ru',
            'email_verified_at' => Carbon::now(),
            'group_id' => 2
                     ]);
    }
}
