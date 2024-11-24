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
            $table->boolean('clinic_add')
                ->default(false);
            $table->boolean('clinic_remove')
                ->default(false);
            $table->boolean('clinic_edit')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('clinic_edit');
            $table->dropColumn('clinic_add');
            $table->dropColumn('clinic_remove');
        });
    }
};
