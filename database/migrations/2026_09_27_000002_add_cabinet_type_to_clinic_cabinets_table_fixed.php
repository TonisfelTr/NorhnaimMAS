<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clinic_cabinets', 'cabinet_type')) {
            Schema::table(
                'clinic_cabinets',
                function (Blueprint $table): void {
                    $table->string('cabinet_type', 32)
                        ->default('doctor')
                        ->index();
                }
            );
        }

        /*
         * Старые записи можно определить только по уже существующим данным:
         *
         * doctor_id IS NOT NULL -> doctor
         * doctor_id IS NULL     -> procedure
         *
         * functional автоматически определить нельзя, потому что до этой
         * миграции отдельного признака такого кабинета в таблице не было.
         * После миграции новые кабинеты функциональной диагностики
         * сохраняются со значением cabinet_type = functional.
         */
        DB::table('clinic_cabinets')
            ->whereNotNull('doctor_id')
            ->update([
                'cabinet_type' => 'doctor',
            ]);

        DB::table('clinic_cabinets')
            ->whereNull('doctor_id')
            ->update([
                'cabinet_type' => 'procedure',
            ]);

        /*
         * Разрешённые значения:
         * doctor
         * procedure
         * functional
         */
        DB::statement(
            <<<'SQL'
ALTER TABLE clinic_cabinets
DROP CONSTRAINT IF EXISTS clinic_cabinets_cabinet_type_check
SQL
        );

        DB::statement(
            <<<'SQL'
ALTER TABLE clinic_cabinets
ADD CONSTRAINT clinic_cabinets_cabinet_type_check
CHECK (cabinet_type IN ('doctor', 'procedure', 'functional'))
SQL
        );
    }

    public function down(): void
    {
        DB::statement(
            <<<'SQL'
ALTER TABLE clinic_cabinets
DROP CONSTRAINT IF EXISTS clinic_cabinets_cabinet_type_check
SQL
        );

        if (Schema::hasColumn('clinic_cabinets', 'cabinet_type')) {
            Schema::table(
                'clinic_cabinets',
                function (Blueprint $table): void {
                    $table->dropColumn('cabinet_type');
                }
            );
        }
    }
};
