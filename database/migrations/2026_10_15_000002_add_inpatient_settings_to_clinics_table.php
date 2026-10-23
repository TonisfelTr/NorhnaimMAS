<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clinics', 'inpatient_mode')) {
            Schema::table('clinics', function (Blueprint $table): void {
                $table
                    ->string('inpatient_mode', 32)
                    ->default('round_the_clock')
                    ->after('clinic_type');
            });
        }

        if (!Schema::hasColumn('clinics', 'inpatient_working_hours')) {
            Schema::table('clinics', function (Blueprint $table): void {
                $table
                    ->json('inpatient_working_hours')
                    ->nullable()
                    ->after('inpatient_mode');
            });
        }

        if (!Schema::hasColumn('clinics', 'inpatient_visiting_hours')) {
            Schema::table('clinics', function (Blueprint $table): void {
                $table
                    ->json('inpatient_visiting_hours')
                    ->nullable()
                    ->after('inpatient_working_hours');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('clinics', 'inpatient_visiting_hours')
                ? 'inpatient_visiting_hours'
                : null,
            Schema::hasColumn('clinics', 'inpatient_working_hours')
                ? 'inpatient_working_hours'
                : null,
            Schema::hasColumn('clinics', 'inpatient_mode')
                ? 'inpatient_mode'
                : null,
        ]));

        if (!$columns) {
            return;
        }

        Schema::table('clinics', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
