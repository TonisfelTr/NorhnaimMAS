<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hospital_departments', 'sex_type')) {
            Schema::table(
                'hospital_departments',
                function (Blueprint $table): void {
                    $table->string('sex_type', 20)
                        ->default('mixed')
                        ->after('profile')
                        ->comment(
                            'Половой тип отделения: male, female, mixed'
                        );
                }
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hospital_departments', 'sex_type')) {
            Schema::table(
                'hospital_departments',
                function (Blueprint $table): void {
                    $table->dropColumn('sex_type');
                }
            );
        }
    }
};
