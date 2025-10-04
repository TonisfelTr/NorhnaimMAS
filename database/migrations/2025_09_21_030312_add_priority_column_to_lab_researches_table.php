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
        Schema::table('lab_researches', function (Blueprint $table) {
            $table->string('status');
            $table->string('priority')->after('status');
            $table->text('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_researches', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('priority');
            $table->dropColumn('comment');
        });
    }
};
