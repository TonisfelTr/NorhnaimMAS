<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hospital_buildings',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('clinic_id')
                    ->comment('Клиника, которой принадлежит корпус стационара')
                    ->constrained('clinics')
                    ->cascadeOnDelete();

                $table->string('number', 50)
                    ->comment('Номер корпуса стационара');

                $table->string('name')
                    ->comment('Название корпуса стационара');

                $table->string('address')
                    ->nullable()
                    ->comment('Адрес корпуса стационара');

                $table->boolean('enabled')
                    ->default(true)
                    ->comment('Активен ли корпус стационара');

                $table->timestamps();

                $table->unique(
                    [
                        'clinic_id',
                        'number',
                    ],
                    'hospital_buildings_clinic_id_number_unique'
                );

                $table->unique(
                    [
                        'clinic_id',
                        'name',
                    ],
                    'hospital_buildings_clinic_id_name_unique'
                );

                $table->index(
                    [
                        'clinic_id',
                        'enabled',
                    ],
                    'hospital_buildings_clinic_enabled_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_buildings');
    }
};
