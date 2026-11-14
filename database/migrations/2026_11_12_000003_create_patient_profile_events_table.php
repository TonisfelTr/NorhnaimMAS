<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_profile_events')) {
            return;
        }

        Schema::create('patient_profile_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('title', 255);
            $table->json('changes')->nullable();
            $table->json('meta')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestamps();

            $table->index(
                ['patient_id', 'occurred_at'],
                'patient_profile_events_patient_occurred_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profile_events');
    }
};
