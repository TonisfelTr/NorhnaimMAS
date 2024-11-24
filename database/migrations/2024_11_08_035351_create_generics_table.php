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
        Schema::create('generics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('drug_id');
            $table->timestamps();

            $table->foreign('drug_id', 'fk_drug_id_from_generics_to_drugs_table')
                ->references('id')
                ->on('drugs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generics', function (Blueprint $table) {
           $table->dropForeign('fk_drug_id_from_generics_to_drugs_table');
        });
        Schema::dropIfExists('generics');
    }
};
