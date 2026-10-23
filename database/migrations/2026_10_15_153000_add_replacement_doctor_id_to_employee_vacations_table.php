<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_vacations', function (Blueprint $table) {
            $table->foreignId('replace_doctor_id')
                ->nullable()
                ->comment('ID врача, исполняющего обязанности на период отпуска')
                ->constrained('doctors')
                ->nullOnDelete();

            $table->foreignId('replace_nurse_id')
                ->nullable()
                ->comment('ID медсестры, исполняющей обязанности на период отпуска')
                ->constrained('nurses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_vacations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('replacement_doctor_id');
            $table->dropConstrainedForeignId('replacement_nurse_id');
        });
    }
};
