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
        Schema::rename('doctors_pricelists', 'doctor_pricelists');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('doctor_pricelists', 'doctors_pricelists');
    }
};
