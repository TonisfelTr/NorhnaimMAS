<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('test_items')->onDelete('cascade');
            $table->string('label');
            $table->integer('value')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_item_options');
    }
};
