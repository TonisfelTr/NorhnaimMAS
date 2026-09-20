<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_commissions', function (Blueprint $table): void {
            $table->id();
            $table->string('protocol_number', 64)->nullable()->unique();

            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('created_by_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();

            $table->unsignedBigInteger('clinic_id')->nullable()->index();

            $table->dateTime('meeting_at');
            $table->string('meeting_type', 32)->default('planned');
            $table->string('status', 32)->default('draft');
            $table->string('category', 64)->default('complex_case');

            $table->string('diagnosis_code', 32)->nullable();
            $table->string('diagnosis_title')->nullable();

            $table->string('secretary_name')->nullable();
            $table->string('secretary_position')->nullable();
            $table->text('unscheduled_basis')->nullable();

            $table->text('agenda');
            $table->text('decision')->nullable();
            $table->text('rationale')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('protocol_ready_at')->nullable();
            $table->timestamp('medical_record_entered_at')->nullable();

            $table->timestamps();

            $table->index(['clinic_id', 'meeting_at']);
            $table->index(['patient_id', 'meeting_at']);
            $table->index(['status', 'meeting_at']);
        });

        Schema::create('medical_commission_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medical_commission_id')
                ->constrained('medical_commissions')
                ->cascadeOnDelete();
            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->restrictOnDelete();

            $table->string('role', 32)->default('member');
            $table->boolean('present')->default(true);
            $table->string('vote', 16)->nullable();
            $table->text('special_opinion')->nullable();
            $table->timestamps();

            $table->unique(['medical_commission_id', 'doctor_id'], 'medical_commission_member_unique');
            $table->index(['medical_commission_id', 'present']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_commission_members');
        Schema::dropIfExists('medical_commissions');
    }
};
