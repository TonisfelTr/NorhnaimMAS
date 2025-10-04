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
            $table->string('sample_type')
                ->comment('Тип материала, который сдали на анализ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_researches', function (Blueprint $table) {
            $table->dropColumn('sample_type');
        });
    }
};
