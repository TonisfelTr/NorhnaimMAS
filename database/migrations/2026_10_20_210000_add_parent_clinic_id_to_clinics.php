<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            // Учреждение и его стационары уже хранятся в clinics.
            // nullable: существующие стационары не должны исчезнуть после миграции.
            $table->foreignId('parent_clinic_id')
                ->nullable()
                ->constrained('clinics')
                ->nullOnDelete();
            $table->index('parent_clinic_id', 'clinics_parent_clinic_id_idx');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("COMMENT ON COLUMN clinics.parent_clinic_id IS 'Головная клиника/диспансер, которому принадлежит стационар (clinic_type=inpatient)'");
            DB::statement('ALTER TABLE clinics ADD CONSTRAINT clinics_parent_not_self_chk CHECK (parent_clinic_id IS NULL OR parent_clinic_id <> id)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE clinics DROP CONSTRAINT IF EXISTS clinics_parent_not_self_chk');
        }
        Schema::table('clinics', function (Blueprint $table): void {
            $table->dropForeign(['parent_clinic_id']);
            $table->dropIndex('clinics_parent_clinic_id_idx');
            $table->dropColumn('parent_clinic_id');
        });
    }
};
