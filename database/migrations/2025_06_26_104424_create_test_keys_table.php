<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('item_ids')->nullable(); // список id вопросов, входящих в шкалу
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_keys');
    }
};
