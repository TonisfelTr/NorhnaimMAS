<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'clinic_buildings',
            function (Blueprint $table): void {
                $table->id();
                $table
                    ->foreignId('clinic_id')
                    ->constrained('clinics')
                    ->cascadeOnDelete();

                $table->string('name');
                $table->string('address')->nullable();
                $table->boolean('enabled')->default(true);
                $table->timestamps();

                $table->unique([
                    'clinic_id',
                    'name',
                ]);
            }
        );

        Schema::table(
            'clinic_cabinets',
            function (Blueprint $table): void {
                $table
                    ->foreignId('building_id')
                    ->nullable()
                    ->after('clinic_id')
                    ->constrained('clinic_buildings')
                    ->restrictOnDelete();
            }
        );

        foreach (
            DB::table('clinics')
                ->select('id')
                ->orderBy('id')
                ->get()
            as $clinic
        ) {
            $buildingId = DB::table(
                'clinic_buildings'
            )->insertGetId([
                'clinic_id' => $clinic->id,
                'name' => 'Основной корпус',
                'address' => null,
                'enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('clinic_cabinets')
                ->where('clinic_id', $clinic->id)
                ->update([
                    'building_id' => $buildingId,
                ]);
        }

        if (
            DB::table('clinic_cabinets')
                ->whereNull('building_id')
                ->exists()
        ) {
            throw new \RuntimeException(
                'Не всем кабинетам удалось назначить корпус.'
            );
        }

        $duplicates = DB::table('clinic_cabinets')
            ->select(
                'building_id',
                'cabinet'
            )
            ->groupBy(
                'building_id',
                'cabinet'
            )
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new \RuntimeException(
                'В одном корпусе обнаружены дубли номеров кабинетов.'
            );
        }

        DB::statement(
            'ALTER TABLE clinic_cabinets '
            . 'DROP CONSTRAINT IF EXISTS '
            . 'clinic_cabinets_clinic_id_cabinet_unique'
        );

        DB::statement(
            'DROP INDEX IF EXISTS '
            . 'clinic_cabinets_clinic_id_cabinet_unique'
        );

        DB::statement(
            'ALTER TABLE clinic_cabinets '
            . 'ALTER COLUMN building_id SET NOT NULL'
        );

        DB::statement(
            'CREATE UNIQUE INDEX '
            . 'clinic_cabinets_building_id_cabinet_unique '
            . 'ON clinic_cabinets '
            . '(building_id, cabinet)'
        );
    }

    public function down(): void
    {
        DB::statement(
            'DROP INDEX IF EXISTS '
            . 'clinic_cabinets_building_id_cabinet_unique'
        );

        Schema::table(
            'clinic_cabinets',
            function (Blueprint $table): void {
                $table->dropConstrainedForeignId(
                    'building_id'
                );
            }
        );

        Schema::dropIfExists('clinic_buildings');
    }
};
