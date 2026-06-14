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
        Schema::create('test_session_protocols', function (Blueprint $t) {
            $t->id()->comment('Первичный ключ');
            $t->foreignId('session_id')
                ->constrained('test_sessions')
                ->cascadeOnDelete()
                ->comment('ID сессии тестирования');
            $t->text('context_notes')
                ->nullable()
                ->comment('Контекст проведения: обстановка, усталость, контакт, инструкция');
            $t->text('examiner_notes')
                ->nullable()
                ->comment('Заметки психолога во время проведения');
            $t->json('env')
                ->nullable()
                ->comment('Окружение: устройство, освещённость, кабинет и т.п.');
            $t->timestamps();
            $t->unique('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_session_protocols');
    }
};
