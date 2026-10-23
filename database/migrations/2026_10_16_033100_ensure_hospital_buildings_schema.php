<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hospital_buildings')) {
            return;
        }

        Schema::table('hospital_buildings', function (Blueprint $table): void {
            if (!Schema::hasColumn('hospital_buildings', 'clinic_id')) {
                $table->unsignedBigInteger('clinic_id')->nullable();
            }

            if (!Schema::hasColumn('hospital_buildings', 'number')) {
                $table->string('number', 50)->nullable();
            }

            if (!Schema::hasColumn('hospital_buildings', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('hospital_buildings', 'address')) {
                $table->string('address')->nullable();
            }

            if (!Schema::hasColumn('hospital_buildings', 'enabled')) {
                $table->boolean('enabled')->default(true);
            }
        });

        /*
         * Миграция 2026_10_15_202633 могла быть уже выполнена в пустом виде
         * (только id + timestamps). Поэтому структуру доводим безопасно новой
         * миграцией, не заставляя откатывать данные отделений.
         * Проект работает на PostgreSQL, поэтому здесь используем его
         * IF NOT EXISTS/каталог ограничений.
         */
        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'hospital_buildings_clinic_id_foreign'
                ) THEN
                    ALTER TABLE hospital_buildings
                        ADD CONSTRAINT hospital_buildings_clinic_id_foreign
                        FOREIGN KEY (clinic_id)
                        REFERENCES clinics(id)
                        ON DELETE CASCADE;
                END IF;
            END
            $$;
        SQL);

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS '
            . 'hospital_buildings_clinic_id_number_unique '
            . 'ON hospital_buildings (clinic_id, number)'
        );

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS '
            . 'hospital_buildings_clinic_id_name_unique '
            . 'ON hospital_buildings (clinic_id, name)'
        );

        DB::statement(
            'CREATE INDEX IF NOT EXISTS '
            . 'hospital_buildings_clinic_enabled_index '
            . 'ON hospital_buildings (clinic_id, enabled)'
        );

        /*
         * Если таблица была создана пустой старой миграцией, после добавления
         * колонок она по-прежнему пуста — можно вернуть ожидаемые NOT NULL.
         * Если пользователь успел вручную добавить неполные строки, миграция
         * не уничтожает их: NOT NULL будет выставлен только при отсутствии NULL.
         */
        if (!DB::table('hospital_buildings')->whereNull('clinic_id')->exists()) {
            DB::statement(
                'ALTER TABLE hospital_buildings '
                . 'ALTER COLUMN clinic_id SET NOT NULL'
            );
        }

        if (!DB::table('hospital_buildings')->whereNull('number')->exists()) {
            DB::statement(
                'ALTER TABLE hospital_buildings '
                . 'ALTER COLUMN number SET NOT NULL'
            );
        }

        if (!DB::table('hospital_buildings')->whereNull('name')->exists()) {
            DB::statement(
                'ALTER TABLE hospital_buildings '
                . 'ALTER COLUMN name SET NOT NULL'
            );
        }

        DB::statement(
            'ALTER TABLE hospital_buildings '
            . 'ALTER COLUMN enabled SET DEFAULT true'
        );

        DB::table('hospital_buildings')
            ->whereNull('enabled')
            ->update(['enabled' => true]);

        DB::statement(
            'ALTER TABLE hospital_buildings '
            . 'ALTER COLUMN enabled SET NOT NULL'
        );
    }

    public function down(): void
    {
        /*
         * Специально не демонтируем колонки/индексы: эта миграция является
         * страховочной для уже выполненной пустой create-миграции. Полное
         * удаление hospital_buildings выполняет исходная create-миграция.
         */
    }
};
