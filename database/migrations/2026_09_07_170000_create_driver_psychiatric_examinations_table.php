<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_psychiatric_examinations', function (Blueprint $table): void {
            $table->id();
            $table->string('document_number', 64)->nullable()->unique();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('created_by_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();
            $table->unsignedBigInteger('clinic_id')->nullable();

            $table->date('examination_date');
            $table->string('status', 24)->default('draft');
            $table->string('purpose', 40)->default('candidate');
            $table->string('contraindication_assessment', 40)->nullable();
            $table->json('vehicle_categories')->nullable();

            // Исторический снимок диагноза и пациента на момент оформления.
            $table->string('diagnosis_code', 32)->nullable();
            $table->string('diagnosis_title')->nullable();
            $table->json('patient_snapshot')->nullable();

            // Психиатрическое обследование.
            $table->longText('complaints')->nullable();
            $table->longText('disease_history')->nullable();
            $table->longText('psychiatric_history')->nullable();
            $table->longText('hospitalizations')->nullable();
            $table->longText('current_treatment')->nullable();
            $table->longText('mental_status')->nullable();
            $table->longText('cognitive_behavioral_features')->nullable();
            $table->json('risk_flags')->nullable();
            $table->boolean('medical_record_reviewed')->default(false);

            // Решение психиатра / психиатрической ВК.
            $table->string('result', 64)->nullable();
            $table->longText('result_rationale')->nullable();
            $table->string('commission_protocol_number', 100)->nullable();
            $table->date('commission_protocol_date')->nullable();
            $table->longText('commission_decision')->nullable();
            $table->boolean('commission_refusal')->default(false);
            $table->longText('conclusion_text')->nullable();

            // Внутренняя справка психиатра. Это не форма 003-В/у.
            $table->string('certificate_number', 64)->nullable()->unique();
            $table->date('certificate_date')->nullable();

            $table->timestamp('prepared_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'examination_date']);
            $table->index(['patient_id', 'examination_date']);
            $table->index(['status', 'examination_date']);
            $table->index(['result', 'examination_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_psychiatric_examinations');
    }
};
