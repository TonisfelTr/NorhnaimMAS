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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('serial', 10)->comment('Серия паспорта');
            $table->string('number', 15)->comment('Номер паспорта');
            $table->text('issued_by')->comment('Кем выдан');
            $table->date('issued_at')->comment('Дата выдачи паспорта');
            $table->string('department_code', 7)->nullable()->comment('Код подразделения');
            $table->text('birth_place')->nullable()->comment('Место рождения');
            $table->string('snils', 14)->nullable()->comment('СНИЛС');
            $table->string('oms', 16)->nullable()->comment('Номер полиса ОМС');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'serial',
                'number',
                'issued_by',
                'issued_at',
                'department_code',
                'birth_place',
                'snils',
                'oms',
            ]);
        });
    }
};
