<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('hospital_departments')
            || !Schema::hasTable('hospital_buildings')
        ) {
            return;
        }

        DB::transaction(function (): void {
            /*
             * hospital_departments был создан раньше hospital_buildings,
             * поэтому первоначально building_id ссылался на clinic_buildings.
             * Снимаем этот FK, переносим используемые корпуса в отдельный
             * стационарный справочник и затем ставим новый FK.
             */
            Schema::table(
                'hospital_departments',
                function (Blueprint $table): void {
                    $table->dropForeign(['building_id']);
                }
            );

            $legacyBuildingIds = DB::table('hospital_departments')
                ->whereNotNull('building_id')
                ->pluck('building_id')
                ->map(static fn ($id): int => (int) $id)
                ->unique()
                ->values();

            if ($legacyBuildingIds->isNotEmpty()) {
                $legacyBuildings = DB::table('clinic_buildings')
                    ->whereIn('id', $legacyBuildingIds->all())
                    ->get()
                    ->keyBy('id');

                foreach ($legacyBuildingIds as $legacyBuildingId) {
                    $legacy = $legacyBuildings->get($legacyBuildingId);

                    if (!$legacy) {
                        DB::table('hospital_departments')
                            ->where('building_id', $legacyBuildingId)
                            ->update(['building_id' => null]);

                        continue;
                    }

                    $hospitalBuildingId = DB::table('hospital_buildings')
                        ->where('clinic_id', $legacy->clinic_id)
                        ->where('number', $legacy->number)
                        ->value('id');

                    if (!$hospitalBuildingId) {
                        $hospitalBuildingId = DB::table(
                            'hospital_buildings'
                        )->insertGetId([
                            'clinic_id' => $legacy->clinic_id,
                            'number' => $legacy->number,
                            'name' => $legacy->name,
                            'address' => $legacy->address,
                            'enabled' => $legacy->enabled,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('hospital_departments')
                        ->where('building_id', $legacyBuildingId)
                        ->update([
                            'building_id' => $hospitalBuildingId,
                        ]);
                }
            }

            Schema::table(
                'hospital_departments',
                function (Blueprint $table): void {
                    $table->foreign('building_id')
                        ->references('id')
                        ->on('hospital_buildings')
                        ->nullOnDelete();
                }
            );
        });
    }

    public function down(): void
    {
        if (
            !Schema::hasTable('hospital_departments')
            || !Schema::hasTable('clinic_buildings')
        ) {
            return;
        }

        DB::transaction(function (): void {
            Schema::table(
                'hospital_departments',
                function (Blueprint $table): void {
                    $table->dropForeign(['building_id']);
                }
            );

            if (Schema::hasTable('hospital_buildings')) {
                $hospitalBuildingIds = DB::table('hospital_departments')
                    ->whereNotNull('building_id')
                    ->pluck('building_id')
                    ->map(static fn ($id): int => (int) $id)
                    ->unique()
                    ->values();

                $hospitalBuildings = DB::table('hospital_buildings')
                    ->whereIn('id', $hospitalBuildingIds->all())
                    ->get()
                    ->keyBy('id');

                foreach ($hospitalBuildingIds as $hospitalBuildingId) {
                    $hospital = $hospitalBuildings->get(
                        $hospitalBuildingId
                    );

                    if (!$hospital) {
                        DB::table('hospital_departments')
                            ->where('building_id', $hospitalBuildingId)
                            ->update(['building_id' => null]);

                        continue;
                    }

                    $clinicBuildingId = DB::table('clinic_buildings')
                        ->where('clinic_id', $hospital->clinic_id)
                        ->where('number', $hospital->number)
                        ->value('id');

                    if (!$clinicBuildingId) {
                        $clinicBuildingId = DB::table('clinic_buildings')
                            ->where('clinic_id', $hospital->clinic_id)
                            ->where('name', $hospital->name)
                            ->value('id');
                    }

                    DB::table('hospital_departments')
                        ->where('building_id', $hospitalBuildingId)
                        ->update([
                            'building_id' => $clinicBuildingId ?: null,
                        ]);
                }
            } else {
                DB::table('hospital_departments')
                    ->whereNotNull('building_id')
                    ->update(['building_id' => null]);
            }

            Schema::table(
                'hospital_departments',
                function (Blueprint $table): void {
                    $table->foreign('building_id')
                        ->references('id')
                        ->on('clinic_buildings')
                        ->nullOnDelete();
                }
            );
        });
    }
};
