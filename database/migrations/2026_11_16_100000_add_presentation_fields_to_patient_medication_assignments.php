<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_medication_assignments')) {
            return;
        }

        Schema::table('patient_medication_assignments', function (Blueprint $table): void {
            if (!Schema::hasColumn('patient_medication_assignments', 'drug_form')) {
                $table->string('drug_form', 255)->nullable();
            }

            if (!Schema::hasColumn('patient_medication_assignments', 'dosage_unit')) {
                $table->string('dosage_unit', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('patient_medication_assignments')) {
            return;
        }

        Schema::table('patient_medication_assignments', function (Blueprint $table): void {
            if (Schema::hasColumn('patient_medication_assignments', 'drug_form')) {
                $table->dropColumn('drug_form');
            }

            if (Schema::hasColumn('patient_medication_assignments', 'dosage_unit')) {
                $table->dropColumn('dosage_unit');
            }
        });
    }
};
