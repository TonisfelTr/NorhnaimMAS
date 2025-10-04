<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('diagnose_symptoms', function (Blueprint $table) {
            // Удаляем существующие внешние ключи
            $table->dropForeign('fk_diagnose_id_to_diagnoses_from_diagnoses_symptoms_table');
            $table->dropForeign('fk_symptom_id_to_symptoms_from_diagnoses_symptoms_table');

            // Добавляем внешние ключи с каскадным удалением
            $table->foreign('diagnose_id', 'fk_diagnose_id_to_diagnoses_from_diagnoses_symptoms_table')
                  ->references('id')
                  ->on('diagnoses')
                  ->onDelete('cascade'); // Каскадное удаление

            $table->foreign('symptom_id', 'fk_symptom_id_to_symptoms_from_diagnoses_symptoms_table')
                  ->references('id')
                  ->on('symptoms')
                  ->onDelete('cascade'); // Каскадное удаление
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnose_symptoms', function (Blueprint $table) {
            // Удаляем внешние ключи с каскадным удалением
            $table->dropForeign('fk_diagnose_id_to_diagnoses_from_diagnoses_symptoms_table');
            $table->dropForeign('fk_symptom_id_to_symptoms_from_diagnoses_symptoms_table');

            // Восстанавливаем исходные внешние ключи без каскадного удаления
            $table->foreign('diagnose_id', 'fk_diagnose_id_to_diagnoses_from_diagnoses_symptoms_table')
                  ->references('id')
                  ->on('diagnoses');

            $table->foreign('symptom_id', 'fk_symptom_id_to_symptoms_from_diagnoses_symptoms_table')
                  ->references('id')
                  ->on('symptoms');
        });
    }
};
