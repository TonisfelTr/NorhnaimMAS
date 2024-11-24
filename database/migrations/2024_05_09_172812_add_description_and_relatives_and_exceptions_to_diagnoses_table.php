<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->text('description')
                ->after('code')
                ->nullable();
            $table->json('relatives')
                ->after('description')
                ->nullable();
            $table->json('exceptions')
                ->after('relatives')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('relatives');
            $table->dropColumn('exceptions');
        });
    }
};
