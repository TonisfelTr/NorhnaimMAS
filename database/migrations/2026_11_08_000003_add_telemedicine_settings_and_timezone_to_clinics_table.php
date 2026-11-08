<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table->json('telemedicine_settings')->nullable();
            $table->text('telemedicine_telemost_token')->nullable();
            $table->string('timezone', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table->dropColumn(['telemedicine_settings', 'telemedicine_telemost_token', 'timezone']);
        });
    }
};
