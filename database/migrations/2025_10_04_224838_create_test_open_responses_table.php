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
        Schema::create('test_open_responses', function (Blueprint $t) {
            $t->id()->comment('Первичный ключ');
            $t->foreignId('session_id')
                ->constrained('test_sessions')
                ->cascadeOnDelete()
                ->comment('ID сессии тестирования');
            $t->foreignId('stimulus_id')
                ->constrained('test_stimuli')
                ->cascadeOnDelete()
                ->comment('ID стимула, на который дан ответ');
            $t->unsignedInteger('sequence_no')
                ->default(1)
                ->comment('Номер ответа на данный стимул (если несколько реплик)');
            $t->longText('response_text')
                ->nullable()
                ->comment('Текст открытого ответа');
            $t->string('audio_path')
                ->nullable()
                ->comment('Путь к аудиозаписи ответа (если велась)');
            $t->integer('latency_ms')
                ->nullable()
                ->comment('Латентность (мс) — задержка до начала ответа');
            $t->integer('duration_ms')
                ->nullable()
                ->comment('Длительность ответа (мс)');
            $t->json('meta')
                ->nullable()
                ->comment('Доп. данные: подсказки, уточнения, события');
            $t->timestamps();
            $t->index(['session_id','stimulus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_open_responses');
    }
};
