<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->boolean('adminpanel_see')
                ->default(0);
            $table->boolean('user_edit')
                ->default(0);
            $table->boolean('user_banning')
                  ->default(0);
            $table->boolean('user_change_role')
                  ->default(0);
            $table->boolean('user_remove')
                  ->default(0);
            $table->boolean('group_add')
                  ->default(0);
            $table->boolean('group_remove')
                  ->default(0);
            $table->boolean('group_edit')
                  ->default(0);
            $table->boolean('group_change_perms')
                  ->default(0);
            $table->boolean('group_deactivate')
                  ->default(0);
            $table->boolean('settings_main')
                  ->default(0);
            $table->boolean('settings_mail')
                  ->default(0);
            $table->boolean('settings_seo')
                  ->default(0);
            $table->boolean('settings_blog')
                  ->default(0);
            $table->boolean('blog_new_post')
                  ->default(0);
            $table->boolean('blog_edit_post')
                  ->default(0);
            $table->boolean('blog_remove_post')
                  ->default(0);
            $table->boolean('blog_new_category')
                  ->default(0);
            $table->boolean('blog_edit_category')
                  ->default(0);
            $table->boolean('blog_remove_category')
                  ->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
