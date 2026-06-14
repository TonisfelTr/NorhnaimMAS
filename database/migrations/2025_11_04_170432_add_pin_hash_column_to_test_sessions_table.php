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
            $table->string('pin_hash')
                ->nullable()
                ->index();
            $table->dropColumn('pin_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_assignments', function (Blueprint $table) {
            $table->dropColumn('pin_hash');
            $table->string('pin_code', 8);
        });
    }
};
