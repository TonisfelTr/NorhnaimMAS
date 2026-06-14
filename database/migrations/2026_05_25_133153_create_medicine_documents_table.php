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
        Schema::create('medicine_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->comment('ID пациента, который нужен для предоставления доступа')
                ->constrained('patients');
            $table->foreignId('doctor_id')
                ->comment('ID доктора, который загрузил документ')
                ->constrained('doctors');
            $table->string('name')
                ->comment('Название документа');
            $table->string('description')
                ->nullable()
                ->comment('Комментарий к загруженному файлу.');
            $table->boolean('medical_file')
                ->default(false)
                ->comment('Медицинский файл или нет');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_documents');
    }
};
