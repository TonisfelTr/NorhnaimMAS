<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'hospital_department_doctors',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('hospital_department_id')
                    ->constrained('hospital_departments')
                    ->cascadeOnDelete();

                $table->foreignId('doctor_id')
                    ->constrained('doctors')
                    ->cascadeOnDelete();

                $table->timestamps();

                /*
                 * Один постоянный врач может относиться только к одному
                 * отделению стационара. Временные И.О. по отпускам здесь
                 * не хранятся и на это ограничение не влияют.
                 */
                $table->unique(
                    'doctor_id',
                    'hospital_department_doctors_doctor_unique'
                );

                $table->unique(
                    ['hospital_department_id', 'doctor_id'],
                    'hospital_department_doctors_pair_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_department_doctors');
    }
};
