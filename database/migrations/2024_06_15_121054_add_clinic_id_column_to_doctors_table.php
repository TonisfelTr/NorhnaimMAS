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
        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_id')
                ->nullable();

            $table->foreign('clinic_id', 'fk_doctors_to_clinics_table')
                ->on('clinics')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Проверка существования внешнего ключа перед его удалением
            if (Schema::hasColumn('doctors', 'clinic_id')) {
                $table->dropForeign(['clinic_id']); // безопаснее без имени constraint
                $table->dropColumn('clinic_id');
            }
        });
    }
};
