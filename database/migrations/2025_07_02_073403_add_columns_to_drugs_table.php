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
        Schema::table('drugs', function (Blueprint $table) {
            $table->boolean('pregnancy')
                ->default(false)
                ->comment('Разрешён ли при беременности');
            $table->boolean('lactation')
                ->default(false)
                ->comment('Разрешён ли при лактации');
            $table->smallInteger('liver')
                ->default(0)
                ->comment('Метаболизм печенью');
            $table->smallInteger('kidneys')
                ->default(0)
                ->comment('Метаболизм почками');
            $table->jsonb('cytochromes')
                ->nullable()
                ->comment('Цитохромы, участвующие в метаболизме');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drugs', function (Blueprint $table) {
            $table->dropColumn('pregnancy');
            $table->dropColumn('lactation');
            $table->dropColumn('kidneys');
            $table->dropColumn('cytochromes');
            $table->dropColumn('liver');
        });
    }
};
