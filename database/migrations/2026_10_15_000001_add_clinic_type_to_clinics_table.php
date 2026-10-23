<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table
                ->string('clinic_type', 20)
                ->default('outpatient');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table->dropColumn('clinic_type');
        });
    }
};
