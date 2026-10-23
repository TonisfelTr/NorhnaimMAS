<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hospital_admin_clinic_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'clinic_id'], 'haca_user_clinic_unique');
        });

        Schema::create('hospital_medcard_view_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('patient_id'); // Retain audit history if a patient is deleted.
            $table->string('mode', 24);
            $table->timestampTz('accessed_at');
            $table->index(['user_id', 'accessed_at'], 'hmva_user_date_idx');
            $table->index(['patient_id', 'accessed_at'], 'hmva_patient_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_medcard_view_audits');
        Schema::dropIfExists('hospital_admin_clinic_accesses');
    }
};
