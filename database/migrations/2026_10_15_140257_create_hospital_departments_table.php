<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('hospital_departments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')
                ->comment('Клиника')
                ->constrained('clinics')
                ->cascadeOnDelete();

            $table->foreignId('building_id')
                ->nullable()
                ->comment('Корпус, в котором находится отделение')
                ->constrained('hospital_buildings')
                ->nullOnDelete();

            $table->foreignId('head_doctor_id')
                ->nullable()
                ->comment('Заведующий отделением')
                ->constrained('doctors')
                ->nullOnDelete();

            $table->foreignId('head_nurse_id')
                ->nullable()
                ->comment('Старшая медицинская сестра отделения')
                ->constrained('nurses')
                ->nullOnDelete();

            $table->string('name')
                ->comment('Название отделения');

            $table->string('number', 50)
                ->nullable()
                ->comment('Номер отделения');

            $table->string('profile')
                ->nullable()
                ->comment('Профиль отделения');

            $table->string('inpatient_mode', 30)
                ->nullable()
                ->comment('Режим: day, night, round_the_clock');

            $table->boolean('use_clinic_visiting_hours')
                ->default(true)
                ->comment('Использовать общее время посещений клиники');

            $table->json('visiting_hours')
                ->nullable()
                ->comment('Индивидуальный график посещений отделения');

            $table->text('description')
                ->nullable()
                ->comment('Описание отделения');

            $table->boolean('enabled')
                ->default(true)
                ->comment('Активно ли отделение');

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->unique(
                ['clinic_id', 'number'],
                'hospital_departments_clinic_number_unique'
            );

            $table->index([
                'clinic_id',
                'enabled',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_departments');
    }
};
