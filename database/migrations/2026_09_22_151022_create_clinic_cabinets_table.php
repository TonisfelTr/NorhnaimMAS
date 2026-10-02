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
        Schema::create('clinic_cabinets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')
                ->comment('ID клиники, к которой относится кабинет')
                ->constrained('clinics')
                ->references('id')
                ->onDelete('cascade');
            $table->foreignId('doctor_id')
                ->comment('Указатель на ID доктора')
                ->constrained('doctors')
                ->references('id');
            $table->foreignId('nurse_id')
                ->nullable()
                ->comment('Указатель на ID медсестры из таблицы врачей')
                ->constrained('doctors')
                ->references('id');
            $table->foreignId('replace_doctor_id')
                ->nullable()
                ->comment('ID доктора, который замещает основного. Если null - то нет замещающего.')
                ->constrained('doctors')
                ->references('id');
            $table->foreignId('replace_nurse_id')
                ->nullable()
                ->comment('ID медсестры, которая замещает основную. Если null - то нет замещающей.')
                ->constrained('doctors')
                ->references('id');
            $table->string('cabinet')
                ->comment('Номер кабинета');
            $table->string('name')
                ->nullable()
                ->comment('Название кабинета');
            $table->boolean('enabled')
                ->comment('Переключатель работы кабинета. Если выключен - не показывается в списке кабинетов.')
                ->default(true);
            $table->integer('count_107')
                ->comment('Количество рецептов формы 107, которое должно быть в кабинете');
            $table->integer('count_148')
                ->comment('Количество рецептов формы 148, которое должно быть в кабинете');
            $table->string('phone')
                ->nullable()
                ->comment('Номер телефона в кабинете');
            $table->timestamps();

            $table->index([
                'doctor_id',
                'cabinet',
                'name'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_cabinets');
    }
};
