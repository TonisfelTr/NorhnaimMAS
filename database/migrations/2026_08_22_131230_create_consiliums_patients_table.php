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
        Schema::create('consiliums_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consilium_id')
                ->comment('ID консилиума, в котором проводится работа с пациентом.')
                ->constrained('consilia');
            $table->foreignId('patient_id')
                ->comment('ID пациента');
            $table->timestamps();

            $table->unique(['consilium_id', 'patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consiliums_patients');
    }
};
