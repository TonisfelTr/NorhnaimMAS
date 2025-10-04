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
        Schema::create('epicrisis_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epicrisis_id')->constrained('epicrises')->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['epicrisis_id','symptom_id'], 'epicrisis_symptoms_unique');
            $table->index('symptom_id', 'epicrisis_symptoms_symptom_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epicrisis_symptoms');
    }
};
