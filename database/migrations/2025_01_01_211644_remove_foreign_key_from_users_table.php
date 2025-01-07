<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignKeyFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаление внешнего ключа
            $table->dropForeign('users_fk_group_id');

            // Удаление самого столбца, если это требуется
            $table->dropColumn('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавление столбца обратно
            $table->unsignedBigInteger('group_id')->nullable();

            // Восстановление внешнего ключа
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }
}
