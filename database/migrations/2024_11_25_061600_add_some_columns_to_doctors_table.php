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
        Schema::table('doctors', function (Blueprint $table) {
            $table->text('about')
                ->nullable();
            $table->unsignedInteger('experience_years')
                ->nullable();
            $table->unsignedInteger('experience_months')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('about');
            $table->dropColumn('experience_years');
            $table->dropColumn('experience_months');
        });
    }
};
