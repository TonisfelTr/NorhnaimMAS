<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_schedule_exceptions')) {
            return;
        }

        DB::table('clinic_schedule_exceptions')
            ->where('mode', 'shortened')
            ->update([
                'mode' => 'override',
            ]);

        DB::table('clinic_schedule_exceptions')
            ->where('mode', 'limited')
            ->update([
                'mode' => 'blocked',
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('clinic_schedule_exceptions')) {
            return;
        }

        DB::table('clinic_schedule_exceptions')
            ->where('mode', 'override')
            ->update([
                'mode' => 'shortened',
            ]);

        DB::table('clinic_schedule_exceptions')
            ->where('mode', 'blocked')
            ->update([
                'mode' => 'limited',
            ]);
    }
};
