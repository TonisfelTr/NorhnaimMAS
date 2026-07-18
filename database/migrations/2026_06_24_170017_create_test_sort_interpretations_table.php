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
        Schema::create('test_sort_interpretations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')
                ->comment('ID теста, к которому осуществляется инструкция интерпретации.')
                ->references('id')
                ->on('tests')
                ->onDelete('cascade');
            $table->string('resource_title')
                ->nullable()
                ->comment('Название профиля');
            $table->text('resource_about')
                ->nullable()
                ->comment('Описание профиля');
            $table->string('left_label')
                ->comment('Левая граница шкалы');
            $table->string('right_label')
                ->comment("Правая граница шкалы");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sort_interpretations');
    }
};
