<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_room_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')
                ->constrained('hospital_rooms')
                ->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('x')->default(40);
            $table->unsignedSmallInteger('y')->default(40);
            $table->unsignedSmallInteger('width')->default(90);
            $table->unsignedSmallInteger('height')->default(90);
            $table->smallInteger('rotation')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_room_items');
    }
};
