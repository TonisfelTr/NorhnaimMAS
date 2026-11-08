<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registry_records', function (Blueprint $table): void {
            $table->timestampTz('appointment_starts_at_utc')->nullable()->index();
            $table->string('appointment_timezone', 64)->nullable();
        });
        // No UPDATE: the meaning of historical for_datetime has not been confirmed.
    }

    public function down(): void
    {
        Schema::table('registry_records', function (Blueprint $table): void {
            $table->dropIndex(['appointment_starts_at_utc']);
            $table->dropColumn(['appointment_starts_at_utc', 'appointment_timezone']);
        });
    }
};
