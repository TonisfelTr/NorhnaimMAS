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
        Schema::create('patient_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('condition_id')
                ->constrained('clinical_conditions')
                ->restrictOnDelete();

            // active | resolved | history
            $table->string('status', 30)->default('active');

            // mild | moderate | severe
            $table->string('severity', 30)->nullable();

            // manual | diagnosis | measurement | ecg | lab | other
            $table->string('source', 30)->default('manual');

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->text('comment')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['condition_id', 'status']);
            $table->index(['source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_states');
    }
};
