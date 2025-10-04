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
            $table->bigInteger('clinic_id')
                ->nullable()
                ->comment('ID клиники, в которой работает врач. Если пусто - то частный врач.')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->bigInteger('clinic_id')
                ->nullable(false)
                ->change();
        });
    }
};
