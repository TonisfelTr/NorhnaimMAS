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
        Schema::table('banned', function (Blueprint $table) {
            $table->foreign('admin_id', 'fk_admin_id_in_banned_table')
                ->on('users')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'fk_user_id_in_banned_table')
                  ->on('users')
                  ->references('id')
                  ->cascadeOnDelete();

            $table->foreign('rule_id', 'fk_rule_id_in_banned_table')
                  ->on('rules')
                  ->references('id')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banned', function (Blueprint $table) {
            $table->dropForeign('fk_admin_id_in_banned_table');
            $table->dropForeign('fk_user_id_in_banned_table');
            $table->dropForeign('fk_rule_id_in_banned_table');
        });
    }
};
