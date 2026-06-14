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
        Schema::table('medicine_indications', function (Blueprint $table) {
            $table->boolean('indicated_in_russia')->default(false);
            $table->boolean('indicated_by_fda')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_indications', function (Blueprint $table) {
            $table->dropColumn('indicated_in_russia');
            $table->dropColumn('indicated_by_fda');
        });
    }
};
