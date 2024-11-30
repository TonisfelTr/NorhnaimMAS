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
    public function up()
    {
        // Удаляем старое ограничение, если оно существует
        DB::statement('ALTER TABLE drugs DROP CONSTRAINT IF EXISTS medicines_group_check');

        // Добавляем новое ограничение, связанное с MedicineTypesEnum
        DB::statement("
            ALTER TABLE drugs
            ADD CONSTRAINT medicines_group_check
            CHECK (\"group\" IN ('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'))
        ");
    }

    public function down()
    {
        // Удаляем новое ограничение
        DB::statement('ALTER TABLE drugs DROP CONSTRAINT IF EXISTS medicines_group_check');

        // Восстанавливаем старое ограничение, если оно требуется
        DB::statement("
            ALTER TABLE drugs
            ADD CONSTRAINT medicines_group_check
            CHECK ((\"group\"::text = 'App\\Enums\\MedicineTypesEnum'::text))
        ");
    }
};
