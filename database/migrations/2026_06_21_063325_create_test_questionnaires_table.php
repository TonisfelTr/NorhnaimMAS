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
        Schema::create('test_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->references('id')->on('tests');
            $table->string('scoring_type')
                ->comment('Тип подсчёта результатов');
            $table->integer('min_scoring')
                ->comment('Минимальный балл подсчёта');
            $table->integer('max_scoring')
                ->comment('Максимальный балл подсчёта');
            $table->integer('warning_value')
                ->comment('Порог внимания');
            $table->text('description_interpretation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_questionnaires');
    }
};
