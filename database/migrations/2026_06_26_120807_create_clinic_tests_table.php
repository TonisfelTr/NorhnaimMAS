<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('clinic_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')
                ->comment('ID клиники, которой принадлежит тест.')
                ->constrained('clinics');
            $table->foreignId('test_id')
                ->comment('ID теста')
                ->constrained('tests');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_tests');
    }

};
