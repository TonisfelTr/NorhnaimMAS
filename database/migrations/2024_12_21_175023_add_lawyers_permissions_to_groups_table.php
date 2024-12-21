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
            $table->boolean('lawyer_add')->default(false);
            $table->boolean('lawyer_remove')->default(false);
            $table->boolean('lawyer_edit')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('lawyer_add');
            $table->dropColumn('lawyer_remove');
            $table->dropColumn('lawyer_edit');
        });
    }
};
