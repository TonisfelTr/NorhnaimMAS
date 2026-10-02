<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'employee_work_schedules',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('clinic_id');
                $table->string('employee_type', 16);
                $table->unsignedBigInteger('employee_id');
                $table->unsignedTinyInteger('day_number');
                $table->boolean('enabled')->default(false);
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->timestamps();

                $table->unique(
                    [
                        'clinic_id',
                        'employee_type',
                        'employee_id',
                        'day_number',
                    ],
                    'employee_work_schedules_unique'
                );

                $table->index(
                    [
                        'clinic_id',
                        'employee_type',
                        'employee_id',
                    ],
                    'employee_work_schedules_employee_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_work_schedules');
    }
};
