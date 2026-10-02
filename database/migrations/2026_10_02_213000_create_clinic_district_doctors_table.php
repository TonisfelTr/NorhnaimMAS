<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clinic_district_doctors')) {
            return;
        }

        Schema::create(
            'clinic_district_doctors',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('clinic_id')
                    ->constrained('clinics')
                    ->cascadeOnDelete();

                $table->foreignId('district_id')
                    ->constrained('clinic_districts')
                    ->cascadeOnDelete();

                $table->foreignId('doctor_id')
                    ->constrained('doctors')
                    ->cascadeOnDelete();

                $table->string('role', 32)
                    ->default('additional');

                $table->string('source_district_name')
                    ->nullable();

                $table->timestamps();

                $table->unique([
                    'district_id',
                    'doctor_id',
                ]);

                $table->index([
                    'clinic_id',
                    'district_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_district_doctors');
    }
};
