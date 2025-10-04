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
        Schema::create('medicine_indications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('diagnose_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('medicine_id', 'fk_medicine_indication_to_medicine_id')
                ->on('medicines')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('diagnose_id', 'fk_medicine_indication_to_diagnose_id')
                ->on('diagnoses')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_indications', function (Blueprint $table) {
           $table->dropForeign('fk_medicine_indication_to_medicine_id');
           $table->dropForeign('fk_medicine_indication_to_diagnose_id');
        });
        Schema::dropIfExists('medicine_indications');
    }
};
