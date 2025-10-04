<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('symptom_aliases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('symptom_id');
            $table->string('alias');
            $table->timestamps();

            $table->foreign('symptom_id')->references('id')->on('symptoms')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('symptom_aliases');
    }
};
