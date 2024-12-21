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
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('doctors_add')->default(false);
            $table->boolean('doctors_edit')->default(false);
            $table->boolean('doctors_remove')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('doctors_add');
            $table->dropColumn('doctors_edit');
            $table->dropColumn('doctors_remove');
        });
    }
};
