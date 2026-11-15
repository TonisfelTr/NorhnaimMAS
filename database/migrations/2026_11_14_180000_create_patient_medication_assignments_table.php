<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medication_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('drug_id')
                ->nullable()
                ->constrained('drugs')
                ->nullOnDelete();

            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->string('drug_name_snapshot');
            $table->string('generic_name_snapshot')->nullable();
            $table->string('doctor_name_snapshot');
            $table->string('clinic_name_snapshot')->nullable();

            $table->string('status', 20)->default('active');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();

            $table->string('dosage', 255);
            $table->text('schedule');
            $table->text('doctor_instructions');

            $table->text('indication_text');
            $table->text('contraindications_text');
            $table->text('side_effects_text')->nullable();
            $table->text('missed_dose_instructions')->nullable();

            // This is deliberately required: the patient must always have
            // clinician-authored overdose instructions for the assignment.
            $table->text('overdose_actions');
            $table->text('warning_signs')->nullable();

            // Snapshot of reference data shown to the doctor when the assignment
            // was created. It is not used as a substitute for doctor's instructions.
            $table->json('reference_snapshot')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medication_assignments');
    }
};
