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
        Schema::create('clinical_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Аритмия, Артериальная гипертензия и т.д.
            $table->string('slug')->unique(); // arrhythmia, hypertension, qt_prolongation
            $table->string('group')->nullable(); // cardiovascular, neurology, endocrine ...
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_conditions');
    }
};
