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
        Schema::table('anamneses', function (Blueprint $table) {
            $table->bigInteger('diagnose_id')
                ->unsigned();

            $table->foreign('diagnose_id')
                ->references('id')
                ->on('diagnoses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anamneses', function (Blueprint $table) {
            $table->dropColumn('diagnose_id');
        });
    }
};
