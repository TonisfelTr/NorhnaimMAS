<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            // NULL keeps the legacy config value; an empty string clears it explicitly.
            $table->string('benefit_okato_prefix', 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table->dropColumn('benefit_okato_prefix');
        });
    }
};
