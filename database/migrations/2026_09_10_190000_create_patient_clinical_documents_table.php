<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_clinical_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('doctor_id');
            $table->string('type', 32);
            $table->string('status', 16)->default('draft');
            $table->date('issued_at');
            $table->string('organization', 255)->nullable();
            $table->string('organization_details', 1000)->nullable();
            $table->string('author_name', 255);
            $table->string('author_position', 255)->nullable();
            $table->string('recipient', 500)->nullable();
            $table->string('purpose', 1000)->nullable();
            $table->boolean('include_diagnosis')->default(false);
            $table->string('diagnosis_code', 64)->nullable();
            $table->string('diagnosis_title', 2000)->nullable();
            $table->string('period_mode', 16)->default('all');
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->json('fields');
            $table->json('patient_snapshot');
            $table->json('source_options');
            $table->json('source_snapshot');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'issued_at']);
            $table->index(['author_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_clinical_documents');
    }
};
