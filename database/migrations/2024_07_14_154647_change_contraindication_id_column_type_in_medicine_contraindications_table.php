<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE medicine_contraindications ALTER COLUMN contraindication_id TYPE bigint USING contraindication_id::bigint');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE medicine_contraindications ALTER COLUMN contraindication_id TYPE text USING contraindication_id::text');
    }
};
