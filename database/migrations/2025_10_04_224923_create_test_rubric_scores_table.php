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
        Schema::create('test_rubric_scores', function (Blueprint $t) {
            $t->id()->comment('Первичный ключ');
            $t->foreignId('open_response_id')
                ->constrained('test_open_responses')
                ->cascadeOnDelete()
                ->comment('ID открытого ответа');
            $t->foreignId('rubric_id')
                ->constrained('test_rubrics')
                ->cascadeOnDelete()
                ->comment('ID рубрики, по которой проводится оценка');
            $t->unsignedTinyInteger('score')
                ->comment('Выставленный балл по рубрике (0/1/2 и т.п.)');
            $t->text('notes')
                ->nullable()
                ->comment('Комментарий к выставленной оценке');
            $t->timestamps();
            $t->unique(['open_response_id','rubric_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_rubric_scores');
    }
};
