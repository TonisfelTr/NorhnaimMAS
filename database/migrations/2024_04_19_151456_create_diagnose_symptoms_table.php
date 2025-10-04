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
        Schema::create('diagnose_symptoms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diagnose_id');
            $table->unsignedBigInteger('symptom_id');
            $table->timestamps();

            $table->foreign('diagnose_id', 'fk_diagnose_id_to_diagnoses_from_diagnoses_symptoms_table')
                ->on('diagnoses')
                ->references('id');
            $table->foreign('symptom_id', 'fk_symptom_id_to_symptoms_from_diagnoses_symptoms_table')
                ->on('symptoms')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnose_symptoms');
    }
};
