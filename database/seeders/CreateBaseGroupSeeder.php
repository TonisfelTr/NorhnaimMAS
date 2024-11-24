<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CreateBaseGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultGroup = new \App\Models\Group();
        $defaultGroup->name = 'Пользователи';
        $defaultGroup->slug = 'users';
        $defaultGroup->save();

        $adminGroup = new \App\Models\Group();
        $adminGroup->name = 'Администратор';
        $adminGroup->slug = 'admin';
        $adminGroup->adminpanel_see = true;
        $adminGroup->user_edit = true;
        $adminGroup->user_banning = true;
        $adminGroup->user_change_role = true;
        $adminGroup->user_remove = true;
        $adminGroup->group_add = true;
        $adminGroup->group_remove = true;
        $adminGroup->group_edit = true;
        $adminGroup->group_change_perms = true;
        $adminGroup->group_deactivate = true;
        $adminGroup->settings_main = true;
        $adminGroup->settings_mail = true;
        $adminGroup->settings_seo = true;
        $adminGroup->settings_blog = true;
        $adminGroup->blog_new_post = true;
        $adminGroup->blog_edit_post = true;
        $adminGroup->blog_remove_post = true;
        $adminGroup->blog_new_category = true;
        $adminGroup->blog_edit_category = true;
        $adminGroup->blog_remove_category = true;
        $adminGroup->save();
    }
}
