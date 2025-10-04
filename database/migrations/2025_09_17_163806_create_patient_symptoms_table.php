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
        Schema::create('patient_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('symptom_id')->constrained();
            $table->bigInteger('anamnesis_id')
                ->unsigned()
                ->nullable();
            $table->bigInteger('epicrisis_id')
                ->unsigned()
                ->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('anamnesis_id');
            $table->index('epicrisis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_symptoms');
    }
};
