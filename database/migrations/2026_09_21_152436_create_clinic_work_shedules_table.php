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
        Schema::create('clinic_work_shedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')
                ->comment('ID клиники, к которой относится день')
                ->references('id')
                ->on('clinics');
            $table->tinyInteger('day_number')
                ->comment('Номер дня недели');
            $table->timeTz('start_time')
                ->comment('Время начала работы');
            $table->timeTz('end_time')
                ->comment('Время окончания работы');
            $table->timestamps();

            $table->index(['day_number', 'clinic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_work_shedules');
    }
};
