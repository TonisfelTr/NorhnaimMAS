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
        Schema::table('clinic_work_shedules', function (Blueprint $table) {
            $table->boolean('enabled')->default(false)->comment('Работает ли клиника в день недели');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_work_shedules', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }
};
