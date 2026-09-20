<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mse_referrals', function (Blueprint $table): void {
            $table->id();
            $table->string('document_number', 64)->nullable()->unique();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('created_by_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();
            $table->unsignedBigInteger('clinic_id')->nullable();

            $table->date('document_date');
            $table->string('status', 24)->default('draft');
            $table->string('protocol_number', 100)->nullable();
            $table->date('protocol_date')->nullable();
            $table->string('referral_kind', 24)->default('initial');
            $table->date('consent_date')->nullable();
            $table->string('examination_format', 32)->default('without_presence');
            $table->string('notification_method', 32)->nullable();

            $table->boolean('requires_home_examination')->default(false);
            $table->boolean('palliative_care')->default(false);
            $table->boolean('primary_prosthetics')->default(false);
            $table->json('purposes')->nullable();

            // Исторический снимок диагноза и данных пациента на дату оформления.
            $table->string('diagnosis_code', 32)->nullable();
            $table->string('diagnosis_title')->nullable();
            $table->json('patient_snapshot')->nullable();

            // Остальные поля формы 088/у сгруппированы в JSON для расширения
            // без изменения схемы таблицы при локальной настройке МИС.
            $table->json('details')->nullable();

            $table->timestamp('prepared_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'document_date']);
            $table->index(['patient_id', 'document_date']);
            $table->index(['status', 'document_date']);
            $table->index(['referral_kind', 'document_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mse_referrals');
    }
};
