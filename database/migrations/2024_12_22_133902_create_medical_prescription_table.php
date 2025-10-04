<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medical_prescriptions', function (Blueprint $table) {
            $table->id(); // Уникальный идентификатор рецепта
            $table->string('doctor_name'); // Имя врача (ФИО)
            $table->string('patient_name'); // Имя пациента (ФИО)
            $table->string('generic_name'); // Непатентованное название лекарства
            $table->string('drug_form'); // Форма лекарства (таблетки, капсулы и т.д.)
            $table->string('dosage'); // Дозировка лекарства
            $table->integer('quantity'); // Количество лекарств (например, 60 таблеток)
            $table->integer('standards'); //Количество стандартов
            $table->text('usage_instructions'); // Схема приёма (например, 2 раза в день по одной таблетке)
            $table->string('prescription_form'); // Форма рецепта (например, 148/у-88)
            $table->date('issued_at'); // Дата выписки рецепта
            $table->string('validity_period'); // Срок действия рецепта (например, 60 дней, 1 год)
            $table->string('series')->nullable(); // Серия рецептурного бланка
            $table->string('number')->unique()->nullable(); // Уникальный номер рецепта в рамках серии
            $table->timestamps(); // created_at и updated_at
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_prescriptions');
    }
};
