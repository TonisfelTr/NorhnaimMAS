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
        Schema::create('patient_indications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('medicine_id');
            $table->timestamps();

            $table->foreign('patient_id', 'fk_patient_id_in_patient_indications')
                ->on('patients')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('medicine_id', 'fk_medicine_id_in_patient_indications')
                ->on('medicines')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_indications', function (Blueprint $table) {
           $table->dropForeign('fk_patient_id_in_patient_indications');
           $table->dropForeign('fk_medicine_id_in_patient_indications');
        });
        Schema::dropIfExists('patient_indications');
    }
};
