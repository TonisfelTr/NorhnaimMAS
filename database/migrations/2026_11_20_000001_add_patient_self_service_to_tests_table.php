<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tests', 'patient_self_service')) {
            Schema::table('tests', function (Blueprint $table): void {
                $table->boolean('patient_self_service')
                    ->default(false)
                    ->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tests', 'patient_self_service')) {
            Schema::table('tests', function (Blueprint $table): void {
                $table->dropIndex(['patient_self_service']);
                $table->dropColumn('patient_self_service');
            });
        }
    }
};
