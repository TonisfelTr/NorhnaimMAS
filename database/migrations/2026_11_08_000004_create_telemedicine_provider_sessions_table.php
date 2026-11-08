<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemedicine_provider_sessions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('registry_record_id')->unique();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->unsignedBigInteger('doctor_id');
            $table->string('provider', 16);
            $table->string('mode', 24);
            $table->string('status', 16);
            $table->unsignedBigInteger('preparation_started_epoch');
            $table->string('room_id', 100)->nullable()->unique();
            $table->text('webrtc_url')->nullable();
            $table->string('telemost_conference_id', 128)->nullable();
            // Conference URLs are bearer access data. Never select them into a view.
            $table->text('join_url_ciphertext')->nullable();
            $table->string('join_url_hash', 64)->nullable()->unique();
            $table->string('last_error_code', 40)->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestampTz('prepared_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemedicine_provider_sessions');
    }
};
