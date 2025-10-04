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
        Schema::create('lab_research_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->comment('Название шаблона');
            $table->jsonb('lab_parameters')
                ->nullable()
                ->comment('Параметры анализов, из которых состоит шаблон.');
            $table->bigInteger('doctor_id')
                ->unsigned()
                ->nullable()
                ->comment('ID врача, которому принадлежит шаблон. Если 0 - шаблон общий.');
            $table->timestamps();

            $table->index('doctor_id');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_research_templates');
    }
};
