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
        Schema::create('test_sorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')
                ->comment('ID связанного теста')
                ->references('id')
                ->on('tests')
                ->onDelete('cascade');
            $table->string('name')
                ->index()
                ->nullable()
                ->comment('Название карточки');
            $table->string('code')
                ->index()
                ->comment('Системный код карточки');
            $table->string('type')
                ->comment('Тип карточки ');
            $table->string('value')
                ->index()
                ->comment('Значение карточки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sorts');
    }
};
