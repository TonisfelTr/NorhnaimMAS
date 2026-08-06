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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            // Доктор, назначивщший правление
            $table->foreignId('doctor_id')->constrained('doctors');
            // ID пациента
            $table->foreignId('patient_id')->constrained('patients');
            // Доктор к кому идёт направление
            $table->foreignId('referral_id')->constrained('referrals');
            $table->text('direction_reset')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
