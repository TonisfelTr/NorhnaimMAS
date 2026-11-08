<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benefit_prescriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            // Existing models were not supplied. Do not assume their physical table names.
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->uuid('request_key');
            $table->char('last_request_hash', 64);
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 16)->default('draft');
            // Draft numbers live in payload and are not reserved. Issued/void numbers are retained.
            $table->string('series', 20)->nullable();
            $table->string('number', 20)->nullable();
            $table->json('payload');
            $table->json('snapshot');
            $table->timestampTz('issued_at')->nullable();
            $table->timestampTz('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by_user_id')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestampsTz();
            $table->index(['patient_id', 'clinic_id', 'created_at'], 'benefit_patient_clinic_idx');
            $table->unique(['created_by_user_id', 'request_key'], 'benefit_request_unique');
            $table->unique(['clinic_id', 'series', 'number'], 'benefit_number_unique');
        });
        Schema::create('benefit_prescription_events', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('benefit_prescription_id');
            $table->unsignedBigInteger('actor_user_id');
            $table->string('action', 24);
            $table->json('metadata');
            $table->timestampTz('created_at');
            $table->foreign('benefit_prescription_id', 'benefit_events_document_fk')
                ->references('id')->on('benefit_prescriptions')->restrictOnDelete();
            $table->index(['benefit_prescription_id', 'created_at'], 'benefit_events_document_idx');
        });
    }

    public function down(): void
    {
        // Roll back only an unused module. Never destroy issued prescriptions or their journal.
        if (\Illuminate\Support\Facades\DB::table('benefit_prescriptions')->exists()) {
            throw new \RuntimeException('В модуле есть рецепты. Архивируйте данные и выполните отдельную согласованную миграцию.');
        }
        Schema::dropIfExists('benefit_prescription_events');
        Schema::dropIfExists('benefit_prescriptions');
    }
};
