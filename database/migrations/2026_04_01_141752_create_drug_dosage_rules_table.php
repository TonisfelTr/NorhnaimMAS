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
        Schema::create('drug_dosage_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')
                ->comment('ID лекарства')
                ->constrained('drugs');
            $table->string('diagnosis_code')
                ->comment('Кодировка диагноза');
            $table->unsignedInteger('age_min');
            $table->unsignedInteger('age_max');
            $table->unsignedInteger('weight_min');
            $table->unsignedInteger('weight_max');
            $table->enum('sex', ['any', 'male', 'female'])
                ->default('any');
            $table->enum('indication_source', ['ru', 'fda', 'both'])
                ->default('both');
            $table->float('single_dose');
            $table->float('daily_dose');
            $table->unsignedTinyInteger('frequency');
            $table->text('route');
            $table->text('usage_instruction');
            $table->text('titration');
            $table->string('max_dose', 16);
            $table->unsignedInteger('duration');
            $table->unsignedInteger('priority');
            $table->boolean('is_active')
                ->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_dosage_rules');
    }
};
