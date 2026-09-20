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
        Schema::create('consiliums_doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')
                ->comment('ID врача, который участвует в консилиуме');
            $table->foreignId('consilium_id')
                ->comment('ID консилиума')
                ->constrained('consilia');
            $table->timestamps();

            $table->unique(['consilium_id', 'doctor_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consiliums_doctors');
    }
};
