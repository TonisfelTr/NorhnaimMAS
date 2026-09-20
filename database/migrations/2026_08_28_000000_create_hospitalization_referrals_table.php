<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitalization_referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();

            $table->string('type', 24);
            $table->string('status', 24)->default('draft');
            $table->string('source_mode', 24)->default('manual');

            $table->string('destination_organization')->nullable();
            $table->string('destination_address')->nullable();
            $table->string('department')->nullable();

            $table->string('diagnosis_code', 32)->nullable();
            $table->string('diagnosis_title')->nullable();

            $table->text('clinical_summary')->nullable();
            $table->text('reason')->nullable();

            $table->timestampTz('planned_at')->nullable();
            $table->string('urgency_level', 32)->nullable();
            $table->string('transport_type', 64)->nullable();

            $table->jsonb('details')->nullable();
            $table->jsonb('ai_analysis')->nullable();
            $table->timestampTz('ai_analyzed_at')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->timestampsTz();

            $table->index(['type', 'status', 'created_at']);
            $table->index(['patient_id', 'type', 'created_at']);
            $table->index(['doctor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitalization_referrals');
    }
};
