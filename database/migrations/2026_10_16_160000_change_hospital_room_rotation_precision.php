<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Ротация раньше хранилась в smallint, поэтому углы были только
         * целыми. Для свободного вращения сохраняем до двух знаков после
         * запятой: например 17.35°.
         *
         * Проект использует PostgreSQL, поэтому изменение делаем через
         * ALTER COLUMN без требования doctrine/dbal.
         */
        DB::statement('ALTER TABLE hospital_beds ALTER COLUMN rotation DROP DEFAULT');
        DB::statement(
            'ALTER TABLE hospital_beds ALTER COLUMN rotation TYPE numeric(5,2) USING rotation::numeric'
        );
        DB::statement('ALTER TABLE hospital_beds ALTER COLUMN rotation SET DEFAULT 0');

        DB::statement('ALTER TABLE hospital_room_items ALTER COLUMN rotation DROP DEFAULT');
        DB::statement(
            'ALTER TABLE hospital_room_items ALTER COLUMN rotation TYPE numeric(5,2) USING rotation::numeric'
        );
        DB::statement('ALTER TABLE hospital_room_items ALTER COLUMN rotation SET DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE hospital_beds ALTER COLUMN rotation DROP DEFAULT');
        DB::statement(
            'ALTER TABLE hospital_beds ALTER COLUMN rotation TYPE smallint USING ROUND(rotation)::smallint'
        );
        DB::statement('ALTER TABLE hospital_beds ALTER COLUMN rotation SET DEFAULT 0');

        DB::statement('ALTER TABLE hospital_room_items ALTER COLUMN rotation DROP DEFAULT');
        DB::statement(
            'ALTER TABLE hospital_room_items ALTER COLUMN rotation TYPE smallint USING ROUND(rotation)::smallint'
        );
        DB::statement('ALTER TABLE hospital_room_items ALTER COLUMN rotation SET DEFAULT 0');
    }
};
