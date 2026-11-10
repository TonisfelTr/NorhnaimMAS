<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_medical_document_tasks')) {
            return;
        }

        Schema::create('ai_medical_document_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('mode', 64);
            $table->string('status', 24)->default('queued');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->json('request_payload');
            $table->json('result_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestampTz('cancel_requested_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->timestampTz('anamneses_saved_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_medical_document_tasks');
    }
};
