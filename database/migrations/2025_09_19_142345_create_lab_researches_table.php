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
        Schema::create('lab_researches', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')
                ->comment('ID пациента, которому был проведён анализ');
            $table->bigInteger('doctor_id')
                ->comment('ID врача, который дал направление.');
            $table->date('research_date')
                ->nullable()
                ->comment('Дата, когда был произведён забор материала на анализ.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_researches');
    }
};
