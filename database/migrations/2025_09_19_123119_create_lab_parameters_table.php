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
        Schema::create('lab_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->comment('Название исследуемого параметра');
            $table->string('unit')
                ->nullable()
                ->comment('Единица измерения');
            $table->enum('data_type', ['numeric', 'categorical', 'text', 'boolean'])
                ->comment('Тип значения');
            $table->string('sample_type')
                ->comment('Исследуемый материал');
            $table->jsonb('allowed_values')
                ->nullable()
                ->comment('Разрешённые значения');
            $table->jsonb('normal_values')
                ->nullable()
                ->comment('Значения в норме');
            $table->text('notes')
                ->nullable()
                ->comment('Записки');
            $table->string('group')
                ->comment('Группа анализов');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_parameters');
    }
};
