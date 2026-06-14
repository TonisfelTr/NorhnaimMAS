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
        Schema::table('epicrises', function (Blueprint $table) {
            $table->foreignId('diagnose_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epicrises', function (Blueprint $table) {
            $table->dropColumn('diagnose_id');
        });
    }
};
