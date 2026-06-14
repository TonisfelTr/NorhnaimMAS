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
        Schema::create('test_stimuli', function (Blueprint $t) {
            $t->id()->comment('Первичный ключ');
            $t->foreignId('test_id')
                ->constrained('tests')
                ->cascadeOnDelete()
                ->comment('ID теста, к которому относится стимул');
            $t->unsignedInteger('order')
                ->default(0)
                ->comment('Порядок предъявления стимула в тесте');
            $t->string('media_type')
                ->default('image')
                ->comment('Тип стимула: image|audio|video|text');
            $t->string('uri')
                ->comment('URI файла или внешней ссылки на стимул');
            $t->text('prompt')
                ->nullable()
                ->comment('Инструкция или сопроводительный текст к стимулу');
            $t->json('meta')
                ->nullable()
                ->comment('Доп. параметры: защита, лицензия, размеры и др.');
            $t->timestamps();
            $t->unique(['test_id','order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_stimuli');
    }
};
