<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_buildings')) {
            return;
        }

        if (
            !Schema::hasColumn(
                'clinic_buildings',
                'number'
            )
        ) {
            Schema::table(
                'clinic_buildings',
                function (Blueprint $table): void {
                    $table
                        ->string('number', 50)
                        ->nullable()
                        ->after('clinic_id');
                }
            );
        }

        /*
         * Старым корпусам присваиваем номера 1, 2, 3...
         * отдельно внутри каждой клиники.
         */
        $clinicIds = DB::table('clinic_buildings')
            ->select('clinic_id')
            ->distinct()
            ->orderBy('clinic_id')
            ->pluck('clinic_id');

        foreach ($clinicIds as $clinicId) {
            $buildings = DB::table('clinic_buildings')
                ->where('clinic_id', $clinicId)
                ->orderBy('id')
                ->get([
                    'id',
                    'number',
                ]);

            $nextNumber = 1;

            foreach ($buildings as $building) {
                if (
                    $building->number !== null
                    && trim((string) $building->number) !== ''
                ) {
                    continue;
                }

                while (
                    DB::table('clinic_buildings')
                        ->where('clinic_id', $clinicId)
                        ->where(
                            'number',
                            (string) $nextNumber
                        )
                        ->exists()
                ) {
                    $nextNumber++;
                }

                DB::table('clinic_buildings')
                    ->where('id', $building->id)
                    ->update([
                        'number' => (string) $nextNumber,
                        'updated_at' => now(),
                    ]);

                $nextNumber++;
            }
        }

        DB::statement(
            'ALTER TABLE clinic_buildings '
            . 'ALTER COLUMN number SET NOT NULL'
        );

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS '
            . 'clinic_buildings_clinic_id_number_unique '
            . 'ON clinic_buildings (clinic_id, number)'
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('clinic_buildings')) {
            return;
        }

        DB::statement(
            'DROP INDEX IF EXISTS '
            . 'clinic_buildings_clinic_id_number_unique'
        );

        if (
            Schema::hasColumn(
                'clinic_buildings',
                'number'
            )
        ) {
            Schema::table(
                'clinic_buildings',
                function (Blueprint $table): void {
                    $table->dropColumn('number');
                }
            );
        }
    }
};
