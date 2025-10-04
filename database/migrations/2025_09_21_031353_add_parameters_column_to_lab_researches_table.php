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
        Schema::table('lab_researches', function (Blueprint $table) {
            $table->jsonb('parameters')
                ->comment('Параметры, которые исследуются в анализе');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_researches', function (Blueprint $table) {
            $table->dropColumn('parameters');
        });
    }
};
