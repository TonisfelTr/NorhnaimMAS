<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_districts')) {
            return;
        }

        if (!Schema::hasColumn('clinic_districts', 'transfer_to_district_id')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->unsignedBigInteger('transfer_to_district_id')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'transfer_from')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->date('transfer_from')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'transfer_until')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->date('transfer_until')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'transfer_reason')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->text('transfer_reason')->nullable();
            });
        }

        /*
         * Старое поле сохранилось от первоначальной схемы и больше не
         * используется для временной передачи участков. Оно не должно
         * блокировать INSERT новых участков.
         */
        if (Schema::hasColumn('clinic_districts', 'replaced_doctor_id')) {
            DB::statement(
                'ALTER TABLE clinic_districts '
                . 'ALTER COLUMN replaced_doctor_id DROP NOT NULL'
            );
        }

        DB::statement(
            'CREATE INDEX IF NOT EXISTS '
            . 'clinic_districts_transfer_to_district_id_idx '
            . 'ON clinic_districts (transfer_to_district_id)'
        );

        DB::statement(
            'CREATE INDEX IF NOT EXISTS '
            . 'clinic_districts_transfer_period_idx '
            . 'ON clinic_districts (transfer_from, transfer_until)'
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('clinic_districts')) {
            return;
        }

        DB::statement(
            'DROP INDEX IF EXISTS '
            . 'clinic_districts_transfer_to_district_id_idx'
        );
        DB::statement(
            'DROP INDEX IF EXISTS '
            . 'clinic_districts_transfer_period_idx'
        );

        Schema::table('clinic_districts', function (Blueprint $table): void {
            foreach ([
                'transfer_to_district_id',
                'transfer_from',
                'transfer_until',
                'transfer_reason',
            ] as $column) {
                if (Schema::hasColumn('clinic_districts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
