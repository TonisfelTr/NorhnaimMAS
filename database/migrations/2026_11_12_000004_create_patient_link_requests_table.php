<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_link_requests')) {
            return;
        }

        Schema::create('patient_link_requests', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('patient_id')
                ->nullable()
                ->constrained('patients')
                ->nullOnDelete();

            $table->string('status', 30)->default('pending_review');

            /*
             * В payload хранится зашифрованный массив с паспортными,
             * страховыми и персональными данными, введёнными пользователем.
             */
            $table->longText('payload');

            $table->text('verification_email')->nullable();
            $table->string('verification_code_hash', 255)->nullable();
            $table->string('verification_channel', 20)->default('email');
            $table->timestamp('verification_expires_at')->nullable();
            $table->unsignedTinyInteger('verification_attempts')->default(0);
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'status'],
                'patient_link_requests_user_status_idx'
            );

            $table->index(
                ['patient_id', 'status'],
                'patient_link_requests_patient_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_link_requests');
    }
};
