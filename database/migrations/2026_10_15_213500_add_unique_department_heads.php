<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'hospital_departments',
            function (Blueprint $table): void {
                $table->unique(
                    [
                        'clinic_id',
                        'head_doctor_id',
                    ],
                    'hospital_departments_clinic_head_doctor_unique'
                );

                $table->unique(
                    [
                        'clinic_id',
                        'head_nurse_id',
                    ],
                    'hospital_departments_clinic_head_nurse_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'hospital_departments',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'hospital_departments_clinic_head_doctor_unique'
                );

                $table->dropUnique(
                    'hospital_departments_clinic_head_nurse_unique'
                );
            }
        );
    }
};
