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
        Schema::create('anamneses', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('patient_id');
            $table->bigInteger('doctor_id');
            $table->text('text');
            $table->timestamps();

            $table->index('patient_id', 'anamneses_patient_id_idx');
            $table->index('doctor_id', 'anamneses_doctor_id_idx');

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anamneses');
    }
};
