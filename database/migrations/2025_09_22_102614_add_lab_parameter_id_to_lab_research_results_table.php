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
        Schema::table('lab_research_results', function (Blueprint $table) {
            $table->bigInteger('lab_parameter_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_research_results', function (Blueprint $table) {
            $table->dropColumn('lab_parameter_id');
        });
    }
};
