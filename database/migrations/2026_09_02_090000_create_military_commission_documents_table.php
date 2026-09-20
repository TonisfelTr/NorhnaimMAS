<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('military_commission_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('document_number', 64)->nullable()->unique();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('created_by_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();
            $table->unsignedBigInteger('clinic_id')->nullable();

            $table->date('document_date');
            $table->string('document_type', 40)->default('medical_information');
            $table->string('status', 24)->default('draft');

            $table->string('requesting_organization')->nullable();
            $table->string('request_number', 100)->nullable();
            $table->date('request_date')->nullable();
            $table->text('purpose')->nullable();

            // Исторический снимок диагноза на дату формирования документа.
            $table->string('diagnosis_code', 32)->nullable();
            $table->string('diagnosis_title')->nullable();

            $table->text('complaints')->nullable();
            $table->text('anamnesis')->nullable();
            $table->text('objective_status')->nullable();
            $table->text('examination_results')->nullable();
            $table->text('treatment_history')->nullable();
            $table->text('medical_conclusion')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('prepared_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'document_date']);
            $table->index(['patient_id', 'document_date']);
            $table->index(['status', 'document_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('military_commission_documents');
    }
};
