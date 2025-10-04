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
        Schema::create('lab_research_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_research_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->string('value');
            $table->timestamps();

            $table->index('lab_research_id');
            $table->index('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_research_results');
    }
};
