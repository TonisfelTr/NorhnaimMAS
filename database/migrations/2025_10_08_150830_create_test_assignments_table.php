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
        Schema::create('test_assignments', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('test_id');
            $table->unsignedBigInteger('user_id');       // пациент
            $table->unsignedBigInteger('clinician_id')->nullable(); // кто назначил

            $table->unsignedBigInteger('session_id')->nullable();

            // Статус именно НАЗНАЧЕНИЯ (не попытки): assigned|in_progress|completed|cancelled
            $table->string('status', 32)->default('assigned');

            // Сроки и отметки
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Доп. данные (основание, визит, комментарии и т. п.)
            // Если у тебя нет метода jsonb() — замени на json()
            $table->json('context')->nullable();

            $table->timestamps();

            // Внешние ключи (PostgreSQL/общий вариант)
            $table->foreign('test_id')
                ->references('id')->on('tests')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('clinician_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('session_id')
                ->references('id')->on('test_sessions')
                ->nullOnDelete();

            // Индексы под частые выборки
            $table->index(['user_id', 'status'], 'test_assignments_user_status_idx');
            $table->index(['test_id', 'user_id'], 'test_assignments_test_user_idx');
            $table->index('session_id', 'test_assignments_session_idx');

            // Если нужна жёсткая 1:1 связь "сессия ↔ назначение", раскомментируй:
            // $table->unique('session_id', 'test_assignments_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_assignments');
    }
};
