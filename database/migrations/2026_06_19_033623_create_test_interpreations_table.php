<?php

use App\Enums\Tests\TestAnswersTypeEnum;
use App\Enums\Tests\TestCardDisplayModeEnum;
use App\Enums\Tests\TestInterpretationTypeEnum;
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
        Schema::create('test_card_interpretations', function (Blueprint $table) {
            $table->id();
            $table->enum('interpretation_type', TestInterpretationTypeEnum::all())
                ->default('manual')
                ->comment('Определение пути интерпретации теста');
            $table->enum('answers_type', TestAnswersTypeEnum::all())
                ->default('FREE_TEXT')
                ->comment('Способ ответа на вопросы теста');
            $table->enum('display_mode', TestCardDisplayModeEnum::all())
                ->default('SEQUENTIAL')
                ->comment('Режим отображения карточек - последовательно или все сразу');
            $table->foreignId('test_id')
                ->constrained('tests')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_card_interpretations');
    }
};
