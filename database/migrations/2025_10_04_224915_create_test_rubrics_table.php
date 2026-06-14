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
        Schema::create('test_rubrics', function (Blueprint $t) {
            $t->id()->comment('Первичный ключ');
            $t->foreignId('test_id')
                ->constrained('tests')
                ->cascadeOnDelete()
                ->comment('ID теста, к которому относится рубрикатор');
            $t->string('title')
                ->comment('Название рубрики: Абстрактность, Категориальность и т.п.');
            $t->json('scale')
                ->nullable()
                ->comment('Шкала оценки в виде JSON: {"0":"конкретно","1":"частично","2":"абстрактно"}');
            $t->boolean('per_stimulus')
                ->default(true)
                ->comment('Применяется к каждому стимулу (true) или ко всему тесту (false)');
            $t->json('meta')
                ->nullable()
                ->comment('Доп. параметры рубрики: правила подсчёта, веса и т.п.');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_rubrics');
    }
};
