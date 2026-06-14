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
        Schema::table('tests', function (Blueprint $table) {
            $table->text('instructions')->nullable();
            $table->smallInteger('estimated_minutes')->nullable();
            $table->json('resource_profile')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('instructions');
            $table->dropColumn('estimated_minutes');
            $table->dropColumn('resource_profile');
        });
    }
};
