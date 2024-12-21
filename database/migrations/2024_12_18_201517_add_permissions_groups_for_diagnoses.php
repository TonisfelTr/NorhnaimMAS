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
        Schema::table("groups", function (Blueprint $table) {
           $table->boolean('diagnose_create')->default(false);
           $table->boolean('diagnose_remove')->default(false);
           $table->boolean('diagnose_edit')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("groups", function (Blueprint $table) {
            $table->dropColumn('diagnose_edit');
            $table->dropColumn('diagnose_create');
            $table->dropColumn('diagnose_remove');
        });
    }
};
