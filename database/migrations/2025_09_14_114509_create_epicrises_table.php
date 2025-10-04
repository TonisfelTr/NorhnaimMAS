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
        Schema::create('epicrises', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->comment('ID пациента, на которого составлен эпикриз.')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('doctor_id')
                ->comment('ID доктора, составивший эпикриз.')
                ->constrained('doctors')
                ->cascadeOnDelete();

            $table->text('text')->comment('Содержание эпикриза');
            $table->timestamps();

            $table->index('patient_id', 'epicrises_patient_id_idx');
            $table->index('doctor_id', 'epicrises_doctor_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epicrises');
    }
};
