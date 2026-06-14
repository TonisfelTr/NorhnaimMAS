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
        Schema::create('patient_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->comment('ID пациента')
                ->constrained();
            $table->unsignedMediumInteger('height')
                ->comment('Рост пациента в сантиметрах');
            $table->float('weight')
                ->comment('Вес пациента в килограммах.');
            $table->unsignedTinyInteger('author')
                ->comment('Автор - 1 пациент, 2 - врач');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_parameters');
    }
};
