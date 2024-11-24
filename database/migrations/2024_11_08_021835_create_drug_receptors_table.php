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
        Schema::create('drug_receptors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('drug_id');
            $table->unsignedBigInteger('receptor_id');
            $table->timestamps();

            $table->foreign('drug_id', 'fk_drug_id_to_drugs')
                ->references('id')
                ->on('drugs');
            $table->foreign('receptor_id','fk_receptor_id_to_receptors')
                ->references('id')
                ->on('receptors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_receptors', function (Blueprint $table) {
           $table->dropForeign('fk_drug_id_to_drugs');
           $table->dropForeign('fk_receptor_id_to_receptors');
        });
        Schema::dropIfExists('drug_receptors');
    }
};
