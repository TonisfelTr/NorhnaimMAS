<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')
                ->comment('Направивший врач')
                ->constrained('doctors');
            $table->foreignId('to_doctor_id')
                ->nullable()
                ->comment('Врач, к которому направлен пациент')
                ->constrained('doctors');
            $table->foreignId('patient_id')
                ->comment('Пациент')
                ->constrained('patients');
            $table->foreignId('diagnose_id')
                ->nullable()
                ->comment('Предварительный диагноз')
                ->constrained('diagnoses');
            $table->string('type', 50)
                ->default('specialist')
                ->comment(
                    'Тип: specialist, consultation, hospital, commission, external'
                );
            $table->string('speciality', 255)
                ->nullable()
                ->comment('Специальность, к которой направляется пациент');
            $table->string('organization', 255)
                ->nullable()
                ->comment('Медицинская организация для внешнего направления');
            $table->string('external_doctor_name', 255)
                ->nullable()
                ->comment('ФИО внешнего специалиста');
            $table->text('purpose')
                ->nullable()
                ->comment('Цель направления / клинический вопрос');
            $table->text('comment')
                ->nullable()
                ->comment('Дополнительный комментарий');
            $table->string('priority', 30)
                ->default('normal')
                ->comment('Приоритет: normal, urgent');
            $table->string('status', 30)
                ->default('created')
                ->comment(
                    'Статус: created, accepted, completed, cancelled'
                );
            $table->dateTime('planned_at')
                ->nullable()
                ->comment('Планируемая дата консультации');
            $table->dateTime('accepted_at')
                ->nullable();
            $table->dateTime('completed_at')
                ->nullable();
            $table->dateTime('cancelled_at')
                ->nullable();
            $table->text('result')
                ->nullable()
                ->comment('Результат консультации / заключение');
            $table->text('cancel_reason')
                ->nullable();
            $table->timestamps();

            /*
             * Для быстрых выборок.
             */
            $table->index([
                'patient_id',
                'status',
            ]);

            $table->index([
                'to_doctor_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_destinations');
    }
};
