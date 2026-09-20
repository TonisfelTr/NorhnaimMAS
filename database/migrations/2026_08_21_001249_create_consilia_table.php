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
        Schema::create('consilia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('number')
                ->comment('Номер консилиума');
            $table->string('title')
                ->comment('Название консилиума');
            $table->text('description')
                ->comment('Описание, назначение консилиума')
                ->nullable();
            $table->dateTime('datetime')
                ->nullable()
                ->comment('Время проведения консилиума');
            $table->foreignId('diagnosis_id')
                ->nullable()
                ->comment('ID диагноза')
                ->constrained('diagnoses');
            $table->text('conclusion')
                ->comment('Заключение консилиума')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'datetime',
                'title'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consilia');
    }
};
