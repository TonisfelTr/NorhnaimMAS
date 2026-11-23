<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctors', static function (Blueprint $table): void {
            $table->boolean('telemedicine_sms_enabled')->default(false);
            // Private mobile; encrypted by the Doctor model, never taken from the cabinet phone.
            $table->text('telemedicine_sms_phone')->nullable();
        });

        Schema::create('telemedicine_reminder_dispatches', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('event_key', 64)->unique();
            $table->unsignedBigInteger('registry_record_id')->index();
            $table->unsignedBigInteger('start_epoch');
            $table->unsignedBigInteger('recipient_id');
            $table->string('channel', 24);
            // accepted = SMS provider accepted; not a delivery confirmation.
            $table->string('status', 24)->default('pending');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemedicine_reminder_dispatches');
        Schema::table('doctors', static function (Blueprint $table): void {
            $table->dropColumn(['telemedicine_sms_enabled', 'telemedicine_sms_phone']);
        });
    }
};
