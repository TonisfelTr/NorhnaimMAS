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
        Schema::table('symptom_aliases', function (Blueprint $table) {
            $table->unique(['symptom_id', 'alias'], 'symptom_aliases_symptom_alias_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('symptom_aliases', function (Blueprint $table) {
            $table->dropUnique('symptom_aliases_symptom_alias_unique');
        });
    }
};
