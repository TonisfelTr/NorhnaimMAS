<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_doctor_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('from_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('to_doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamps();
            $table->index(['patient_id', 'created_at'], 'patient_doctor_transfers_patient_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_doctor_transfers');
    }
};
