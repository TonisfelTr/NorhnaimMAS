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
        Schema::create('test_response_codes', function (Blueprint $t) {
            $t->id()->comment('Первичный ключ');
            $t->foreignId('open_response_id')
                ->constrained('test_open_responses')
                ->cascadeOnDelete()
                ->comment('ID открытого ответа, который кодируется');
            $t->foreignId('coder_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID эксперта/психолога, проставившего код');
            $t->string('code_type')
                ->comment('Тип кода: детерминант, контент, месторасположение и т.п.');
            $t->string('code_value')
                ->comment('Конкретное значение кода, например "форма", "животные"');
            $t->unsignedTinyInteger('score')
                ->nullable()
                ->comment('Балльное значение кода, если применяется');
            $t->text('notes')
                ->nullable()
                ->comment('Комментарии к коду');
            $t->json('meta')
                ->nullable()
                ->comment('Доп. метаданные кодирования');
            $t->timestamps();
            $t->index(['open_response_id','code_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_response_codes');
    }
};
