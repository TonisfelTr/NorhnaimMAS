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
        Schema::create('lab_reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parameter_id')
                ->unsigned()
                ->comment('ID параметра, для которого описаны данные референсы.');
            $table->string('sex');
            $table->smallInteger('age_min_y');
            $table->smallInteger('age_max_y');
            $table->float('max');
            $table->float('min');
            $table->timestamps();

            $table->foreign('parameter_id')
                ->references('id')
                ->on('lab_parameters')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_reference_ranges');
    }
};
