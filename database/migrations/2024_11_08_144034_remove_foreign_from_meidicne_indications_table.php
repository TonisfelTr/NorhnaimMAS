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
            $table->dropForeign('fk_medicine_indication_to_medicine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_indications', function (Blueprint $table) {
            $table->foreign('medicine_id', 'fk_medicine_indication_to_medicine_id')
                  ->on('drugs')
                  ->references('id')
                  ->cascadeOnDelete();
        });
    }
};
