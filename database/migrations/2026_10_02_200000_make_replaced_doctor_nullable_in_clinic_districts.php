<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('clinic_districts')
            && Schema::hasColumn('clinic_districts', 'replaced_doctor_id')
        ) {
            DB::statement(
                'ALTER TABLE clinic_districts '
                . 'ALTER COLUMN replaced_doctor_id DROP NOT NULL'
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('clinic_districts')
            && Schema::hasColumn('clinic_districts', 'replaced_doctor_id')
            && !DB::table('clinic_districts')
                ->whereNull('replaced_doctor_id')
                ->exists()
        ) {
            DB::statement(
                'ALTER TABLE clinic_districts '
                . 'ALTER COLUMN replaced_doctor_id SET NOT NULL'
            );
        }
    }
};
