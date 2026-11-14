<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinical_record_origins')) {
            Schema::create('clinical_record_origins', function (Blueprint $table): void {
                $table->id();
                $table->string('record_type', 191);
                $table->unsignedBigInteger('record_id');
                $table->unsignedBigInteger('patient_id')->nullable();
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('doctor_name_snapshot')->nullable();
                $table->string('clinic_name_snapshot')->nullable();
                $table->string('source', 50)->default('created');
                $table->timestamp('captured_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['record_type', 'record_id'],
                    'clinical_record_origins_record_unique'
                );
                $table->index(
                    ['patient_id', 'captured_at'],
                    'clinical_record_origins_patient_idx'
                );
                $table->index(
                    ['doctor_id', 'captured_at'],
                    'clinical_record_origins_doctor_idx'
                );
                $table->index(
                    ['clinic_id', 'captured_at'],
                    'clinical_record_origins_clinic_idx'
                );
            });
        }

        if (!Schema::hasTable('patient_diagnosis_history')) {
            Schema::create('patient_diagnosis_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('diagnose_id')->nullable();
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('doctor_name_snapshot')->nullable();
                $table->string('clinic_name_snapshot')->nullable();
                $table->string('source', 50)->default('patient_diagnose_change');
                $table->timestamp('set_at')->nullable();
                $table->timestamps();

                $table->index(
                    ['patient_id', 'set_at'],
                    'patient_diagnosis_history_patient_idx'
                );
                $table->index(
                    ['patient_id', 'diagnose_id'],
                    'patient_diagnosis_history_diagnose_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_diagnosis_history');
        Schema::dropIfExists('clinical_record_origins');
    }
};
