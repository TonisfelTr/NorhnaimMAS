<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();

            $table->string('type', 32)->index();
            $table->string('status', 32)->default('draft')->index();
            $table->string('number', 64)->nullable()->unique();
            $table->date('document_date')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_documents');
    }
};
