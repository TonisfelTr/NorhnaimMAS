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
        Schema::table('test_assignments', function (Blueprint $table) {
            $table->foreignId('patient_id')
                ->references('id')
                ->on('patients');
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_assignments', function (Blueprint $table) {
            $table->dropForeign('test_assignments_patient_id_foreign');
            $table->dropColumn('patient_id');
        });
    }
};
