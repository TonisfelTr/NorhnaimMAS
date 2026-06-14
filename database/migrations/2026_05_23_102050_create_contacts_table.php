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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->comment('ID пациента, которому принадлежит контакт')
                ->constrained('patients', 'id')
                ->onUpdate('cascade');
            $table->string('name')
                ->comment('Название контакта');
            $table->string('phone')
                ->comment('Телефон контакта');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
