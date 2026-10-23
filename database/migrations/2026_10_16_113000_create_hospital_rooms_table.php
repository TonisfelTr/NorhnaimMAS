<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->cascadeOnDelete();
            $table->foreignId('department_id')
                ->constrained('hospital_departments')
                ->cascadeOnDelete();
            $table->string('number', 50);
            $table->string('name')->nullable();
            $table->string('floor', 50)->nullable();
            $table->unsignedSmallInteger('canvas_width')->default(900);
            $table->unsignedSmallInteger('canvas_height')->default(560);
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['department_id', 'number'],
                'hospital_rooms_department_number_unique'
            );
            $table->index(['clinic_id', 'department_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_rooms');
    }
};
