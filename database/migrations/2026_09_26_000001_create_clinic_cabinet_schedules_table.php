<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'clinic_cabinet_schedules',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('clinic_cabinet_id')
                    ->constrained('clinic_cabinets')
                    ->cascadeOnDelete();

                $table->unsignedTinyInteger('day_number');
                $table->boolean('enabled')->default(false);
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->timestamps();

                $table->unique([
                    'clinic_cabinet_id',
                    'day_number',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'clinic_cabinet_schedules'
        );
    }
};
