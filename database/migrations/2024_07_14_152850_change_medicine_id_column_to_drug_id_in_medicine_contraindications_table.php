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
        Schema::table('medicine_contraindications', function (Blueprint $table) {
            $table->renameColumn('medicine_id', 'drug_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_contraindications', function (Blueprint $table) {
            $table->renameColumn('drug_id', 'medicine_id');
        });
    }
};
