<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('doctors')
            && !Schema::hasColumn('doctors', 'appointment_timezone')
        ) {
            Schema::table('doctors', static function (Blueprint $table): void {
                $table->string('appointment_timezone', 64)
                    ->nullable()
                    ->after('clinic_id');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('doctors')
            && Schema::hasColumn('doctors', 'appointment_timezone')
        ) {
            Schema::table('doctors', static function (Blueprint $table): void {
                $table->dropColumn('appointment_timezone');
            });
        }
    }
};
