<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('medical_prescriptions')) {
            return;
        }

        if (!Schema::hasColumn('medical_prescriptions', 'clinic_id')) {
            Schema::table('medical_prescriptions', function (Blueprint $table): void {
                $table->unsignedBigInteger('clinic_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('medical_prescriptions')
            && Schema::hasColumn('medical_prescriptions', 'clinic_id')
        ) {
            Schema::table('medical_prescriptions', function (Blueprint $table): void {
                $table->dropColumn('clinic_id');
            });
        }
    }
};
