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
        Schema::create('instrumental_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
            $table->foreignId('doctor_id')
                ->constrained('doctors');
            $table->string('study_type', 50);
            $table->string('name');
            $table->string('body_area')
                ->nullable();
            $table->string('status', 30)
                ->default('ordered');
            $table->string('priority', 20)
                ->default('normal');
            $table->boolean('with_contrast')
                ->default(false);
            $table->timestamp('planned_at')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->timestamp('result_at')->nullable();
            $table->string('organization')->nullable();
            $table->string('specialist_name')->nullable();
            $table->text('indication')->nullable();
            $table->text('description')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamp('result_showed_at')->nullable();
            $table->timestamps();

            $table->index([
                'patient_id',
                'status',
                'doctor_id',
                'study_type'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrumental_research');
    }
};
