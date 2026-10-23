<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_duties', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('department_id');
            $table->string('employee_type', 20);
            $table->unsignedBigInteger('employee_id');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(
                ['clinic_id', 'starts_at', 'ends_at'],
                'hospital_duties_clinic_period_idx'
            );
            $table->index(
                ['clinic_id', 'department_id', 'starts_at'],
                'hospital_duties_department_idx'
            );
            $table->index(
                ['clinic_id', 'employee_type', 'employee_id', 'starts_at'],
                'hospital_duties_employee_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_duties');
    }
};
