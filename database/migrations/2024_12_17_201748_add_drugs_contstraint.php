<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            ALTER TABLE drugs
            DROP CONSTRAINT IF EXISTS medicines_group_check
        ');

        DB::statement('
            ALTER TABLE drugs
            ADD CONSTRAINT medicines_group_check
            CHECK ("group" IN (\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\'))
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем check constraint
        DB::statement('
            ALTER TABLE drugs
            DROP CONSTRAINT IF EXISTS medicines_group_check
        ');
    }
};
