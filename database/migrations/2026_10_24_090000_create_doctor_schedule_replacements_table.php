<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_schedule_replacements', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('clinic_cabinet_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('base_doctor_id');
            $table->unsignedBigInteger('replacement_doctor_id');
            $table->date('shift_date');
            $table->string('comment', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(
                ['clinic_id', 'clinic_cabinet_id', 'shift_date'],
                'doctor_schedule_replacements_clinic_office_date_unique'
            );
            $table->index(['clinic_id', 'shift_date'], 'doctor_schedule_replacements_clinic_date_idx');
            $table->index(['clinic_id', 'replacement_doctor_id', 'shift_date'], 'doctor_schedule_replacements_doctor_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedule_replacements');
    }
};
