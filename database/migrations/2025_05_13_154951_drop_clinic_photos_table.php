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
        Schema::dropIfExists('clinic_photos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('clinic_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('photo');
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });
    }
};
