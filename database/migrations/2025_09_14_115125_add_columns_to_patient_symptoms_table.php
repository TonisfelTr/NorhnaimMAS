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
        Schema::table('patient_symptoms', function (Blueprint $table) {
            $table->bigInteger('patient_id')
                ->comment('ID пациента, у которого наблюдается симптом.');
            $table->bigInteger('doctor_id')
                ->comment('ID врача, который зафиксировал диагноз.');
            $table->bigInteger('epicrisis_id')
                ->comment('ID эпикриза, из-за которого добавили симптом.');
            $table->boolean('active')
                ->default(true)
                ->comment('Статус симптома, есть/нет.');

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors');
            $table->foreign('epicrisis_id')
                ->references('id')
                ->on('epicrises');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_symptoms', function (Blueprint $table) {
            $table->dropColumn('patient_id');
            $table->dropColumn('doctor_id');
            $table->dropColumn('epicrisis_id');
            $table->dropColumn('active');
        });
    }
};
