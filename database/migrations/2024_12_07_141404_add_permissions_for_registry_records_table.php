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
            $table->boolean('registry_create')
                ->default(false);
            $table->boolean('registry_remove')
                ->default(false);
            $table->boolean('registry_edit')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('registry_create');
            $table->dropColumn('registry_remove');
            $table->dropColumn('registry_edit');
        });
    }
};
