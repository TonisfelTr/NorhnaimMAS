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
        Schema::table('test_sections', function (Blueprint $table) {
            $table->smallInteger('order')
                ->default(1);
        });

        Schema::table('test_rubrics', function (Blueprint $table) {
            $table->integer('max_score')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sections', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('test_rubrics', function (Blueprint $table) {
            $table->dropColumn('max_score');
        });
    }
};
