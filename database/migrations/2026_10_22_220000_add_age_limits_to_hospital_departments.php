<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hospital_departments', function (Blueprint $table): void {
            $table->unsignedSmallInteger('age_min_years')->nullable();
            $table->unsignedSmallInteger('age_max_years')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hospital_departments', function (Blueprint $table): void {
            $table->dropColumn(['age_min_years', 'age_max_years']);
        });
    }
};
