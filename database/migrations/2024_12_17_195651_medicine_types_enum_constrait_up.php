<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Удалить старый check constraint (если существует)
        DB::statement("ALTER TABLE drugs DROP CONSTRAINT IF EXISTS medicines_group_check");

        // Исправить записи, нарушающие constraint
        DB::statement("
            UPDATE drugs
            SET \"group\" = 'App\\Enums\\MedicineTypesEnum'
            WHERE \"group\"::text != 'App\\Enums\\MedicineTypesEnum'
        ");

        // Добавить новый check constraint
        DB::statement("
            ALTER TABLE drugs
            ADD CONSTRAINT medicines_group_check
            CHECK (\"group\"::text = 'App\\Enums\\MedicineTypesEnum'::text)
        ");
    }

    public function down(): void
    {
        // Удалить check constraint
        DB::statement("ALTER TABLE drugs DROP CONSTRAINT IF EXISTS medicines_group_check");
    }
};


