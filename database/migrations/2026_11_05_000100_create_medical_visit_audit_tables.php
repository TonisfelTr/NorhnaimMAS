<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Separate from medical_encounters: clinical documentation can exist before
        // formal doctor attestation. The attestation is never inferred from WebRTC.
        Schema::create('medical_visit_confirmations', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('registry_record_id')->unique();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('outcome', 16); // held | not_held
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('confirmed_by_user_id');
            $table->timestampTz('confirmed_at');
            $table->timestampsTz();
        });

        // One WebRTC room can belong to ONE scheduled appointment only.
        // The first authenticated signaling event binds it to that appointment.
        Schema::create('telemedicine_room_bindings', static function (Blueprint $table): void {
            $table->id();
            $table->string('room_id', 128)->unique();
            $table->unsignedBigInteger('registry_record_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->timestampsTz();
        });

        // Append-only: includes technical signaling events and clinician actions.
        // No access tokens, IPs, SDP/ICE, medical conversations or media content.
        Schema::create('medical_visit_events', static function (Blueprint $table): void {
            $table->id();
            $table->string('event_uid', 100)->unique();
            $table->unsignedBigInteger('registry_record_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('room_id', 128)->nullable();
            $table->string('event_type', 48);
            $table->string('source', 16); // signaling | clinician
            $table->string('participant_role', 16)->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->json('details')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['registry_record_id', 'occurred_at'], 'mv_events_record_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_visit_events');
        Schema::dropIfExists('telemedicine_room_bindings');
        Schema::dropIfExists('medical_visit_confirmations');
    }
};
