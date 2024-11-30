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
        Schema::create('clinic_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id'); // Внешний ключ на клинику
            $table->string('photo'); // Путь к фотографии
            $table->boolean('is_cover')->default(false); // Флаг обложки
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade'); // Связь с клиникой
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_photos');
    }
};
