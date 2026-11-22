<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_notification_event_keys')) {
            return;
        }

        Schema::create('patient_notification_event_keys', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('event_key', 190);
            $table->timestamp('created_at');
            $table->unique(['user_id', 'event_key'], 'patient_notification_event_keys_unique');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_notification_event_keys');
    }
};
