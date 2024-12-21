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
            $table->renameColumn('user_banning', 'banning_user');
            $table->boolean('banning_edit')->default(false);
            $table->boolean('banning_remove')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->renameColumn('banning_user', 'user_banning');
            $table->dropColumn('banning_edit');
            $table->dropColumn('banning_remove');
        });
    }
};
