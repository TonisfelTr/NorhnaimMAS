<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_encounters', function (Blueprint $table): void {
            $table->id();
            // The original appointment is preserved even if the registry soft-deletes it.
            $table->foreignId('registry_record_id')->unique()
                ->constrained('registry_records')->restrictOnDelete();
            $table->string('attendance_status', 16)->default('unknown');
            $table->string('consultation_status', 20)->default('not_started');
            $table->string('visit_type', 20)->nullable();
            $table->string('noncompletion_reason', 1000)->nullable();
            $table->string('patient_condition', 255)->nullable();
            $table->string('disability_group', 50)->nullable();
            $table->string('disability_details', 500)->nullable();
            $table->text('complaints')->nullable();
            $table->text('disease_history')->nullable();
            $table->text('life_history')->nullable();
            $table->text('social_history')->nullable();
            $table->text('mental_status')->nullable();
            $table->string('diagnosis_code', 24)->nullable();
            $table->string('diagnosis_title', 255)->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('service_code', 32)->nullable();
            $table->string('service_name', 255)->nullable();
            $table->foreignId('created_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            // This records the manual clinical outcome, NOT an e-signature or WebRTC fact.
            $table->timestamp('completed_at')->nullable();
            $table->index('consultation_status');
            $table->index('attendance_status');
            $table->timestamps();
        });

        Schema::create('medical_encounter_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medical_encounter_id')
                ->constrained('medical_encounters')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            // Only changed FIELD NAMES: avoid duplicating medical content in log records.
            $table->jsonb('changed_fields');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['medical_encounter_id', 'created_at'], 'med_enc_audits_enc_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_encounter_audits');
        Schema::dropIfExists('medical_encounters');
    }
};
