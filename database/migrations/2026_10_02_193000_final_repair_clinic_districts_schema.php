<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_districts')) {
            Schema::create('clinic_districts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('clinic_id')->index();
                $table->string('code', 50);
                $table->string('name', 255);
                $table->string('description', 500)->nullable();
                $table->unsignedBigInteger('doctor_id')->nullable()->index();
                $table->unsignedBigInteger('home_visit_doctor_id')->nullable()->index();
                $table->json('address_rules')->nullable();
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });

            return;
        }

        // Таблица уже существует, но ранняя миграция создала её в неполном виде.
        if (!Schema::hasColumn('clinic_districts', 'clinic_id')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->unsignedBigInteger('clinic_id')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'code')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->string('code', 50)->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'name')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->string('name', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'description')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->string('description', 500)->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'doctor_id')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->unsignedBigInteger('doctor_id')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'home_visit_doctor_id')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->unsignedBigInteger('home_visit_doctor_id')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'address_rules')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->json('address_rules')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'enabled')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->boolean('enabled')->default(true);
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'created_at')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasColumn('clinic_districts', 'updated_at')) {
            Schema::table('clinic_districts', function (Blueprint $table): void {
                $table->timestamp('updated_at')->nullable();
            });
        }

        // Восстанавливаем clinic_id по основному врачу, если старые строки уже есть.
        if (
            Schema::hasColumn('clinic_districts', 'clinic_id')
            && Schema::hasColumn('clinic_districts', 'doctor_id')
        ) {
            DB::statement(<<<'SQL'
                UPDATE clinic_districts AS cd
                SET clinic_id = d.clinic_id
                FROM doctors AS d
                WHERE cd.clinic_id IS NULL
                  AND cd.doctor_id = d.id
                  AND d.clinic_id IS NOT NULL
            SQL);
        }

        // В текущем окружении рабочая клиника — 91. Остаточные старые строки
        // без врача относим к ней, чтобы они не потерялись из интерфейса.
        if (DB::table('clinics')->where('id', 91)->exists()) {
            DB::table('clinic_districts')
                ->whereNull('clinic_id')
                ->update(['clinic_id' => 91]);
        }

        DB::table('clinic_districts')
            ->whereNull('enabled')
            ->update(['enabled' => true]);

        // Индексы создаём отдельно и безопасно для PostgreSQL.
        DB::statement('CREATE INDEX IF NOT EXISTS clinic_districts_clinic_id_idx ON clinic_districts (clinic_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS clinic_districts_doctor_id_idx ON clinic_districts (doctor_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS clinic_districts_home_visit_doctor_id_idx ON clinic_districts (home_visit_doctor_id)');
    }

    public function down(): void
    {
        // Repair-миграцию не откатываем: часть колонок могла существовать ранее.
    }
};
