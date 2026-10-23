<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_beds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->cascadeOnDelete();
            $table->foreignId('room_id')
                ->constrained('hospital_rooms')
                ->cascadeOnDelete();
            $table->string('number', 50);
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('x')->default(40);
            $table->unsignedSmallInteger('y')->default(40);
            $table->unsignedSmallInteger('width')->default(150);
            $table->unsignedSmallInteger('height')->default(74);
            $table->smallInteger('rotation')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(
                ['room_id', 'number'],
                'hospital_beds_room_number_unique'
            );
            $table->index(['clinic_id', 'room_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_beds');
    }
};
