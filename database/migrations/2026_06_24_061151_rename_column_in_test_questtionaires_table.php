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
        Schema::table('test_questionnaires', function (Blueprint $table) {
            $table->renameColumn('warning_value', 'attention_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_questionnaires', function (Blueprint $table) {
            $table->renameColumn('attention_score', 'warning_value');
        });
    }
};
