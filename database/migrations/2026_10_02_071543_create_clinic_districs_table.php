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
        Schema::create('clinic_districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->references('id');
            $table->foreignId('replaced_doctor_id')
                ->constrained('doctors')
                ->references('id');
            $table->boolean('enabled')
                ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_districts');
    }
};
