<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A supervisor ward is a PROPERTY of a room, not a new type of department.
        Schema::table('hospital_rooms', function (Blueprint $table): void {
            $table->boolean('is_supervision')->default(false);
        });

        // Hospitalization's department is independent of whether a bed exists.
        Schema::table('hospital_bed_assignments', function (Blueprint $table): void {
            $table->foreignId('department_id')->nullable();
        });

        // Backfill before setting NOT NULL, including released historical admissions.
        DB::statement(<<<'SQL'
UPDATE hospital_bed_assignments AS a
SET department_id = r.department_id
FROM hospital_beds AS b
JOIN hospital_rooms AS r ON r.id = b.room_id
WHERE a.bed_id = b.id
  AND a.department_id IS NULL
SQL);
        $orphanCount = DB::table('hospital_bed_assignments')->whereNull('department_id')->count();
        if ($orphanCount > 0) {
            throw new RuntimeException('Найдены госпитализации без определяемого отделения: ' . $orphanCount);
        }

        DB::statement('ALTER TABLE hospital_bed_assignments ALTER COLUMN department_id SET NOT NULL');
        DB::statement('ALTER TABLE hospital_bed_assignments ALTER COLUMN bed_id DROP NOT NULL');
        Schema::table('hospital_bed_assignments', function (Blueprint $table): void {
            $table->foreign('department_id', 'hba_department_fk')
                ->references('id')->on('hospital_departments')->restrictOnDelete();
            $table->index(['department_id', 'released_at'], 'hba_department_active_idx');
        });

        // Past and future transfers may start/end with a patient in the corridor.
        if (!Schema::hasTable('hospital_department_transfers')) {
            throw new RuntimeException('Сначала примени миграцию create_hospital_department_transfers_table.');
        }
        foreach (['from_room_id', 'from_bed_id', 'to_room_id', 'to_bed_id'] as $column) {
            DB::statement('ALTER TABLE hospital_department_transfers ALTER COLUMN ' . $column . ' DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Never destroy a corridor admission or historical transfer to roll back schema.
        if (DB::table('hospital_bed_assignments')->whereNull('bed_id')->exists()) {
            throw new RuntimeException('Нельзя откатить миграцию: имеются госпитализации без койки.');
        }
        foreach (['from_room_id', 'from_bed_id', 'to_room_id', 'to_bed_id'] as $column) {
            if (DB::table('hospital_department_transfers')->whereNull($column)->exists()) {
                throw new RuntimeException('Нельзя откатить миграцию: история переводов содержит пустое место.');
            }
        }
        foreach (['from_room_id', 'from_bed_id', 'to_room_id', 'to_bed_id'] as $column) {
            DB::statement('ALTER TABLE hospital_department_transfers ALTER COLUMN ' . $column . ' SET NOT NULL');
        }
        Schema::table('hospital_bed_assignments', function (Blueprint $table): void {
            $table->dropIndex('hba_department_active_idx');
            $table->dropForeign('hba_department_fk');
        });
        DB::statement('ALTER TABLE hospital_bed_assignments ALTER COLUMN bed_id SET NOT NULL');
        Schema::table('hospital_bed_assignments', function (Blueprint $table): void {
            $table->dropColumn('department_id');
        });
        Schema::table('hospital_rooms', function (Blueprint $table): void {
            $table->dropColumn('is_supervision');
        });
    }
};
