<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->renameColumn('phone', 'phones');
        });

        DB::statement("
            ALTER TABLE clinics
            ALTER COLUMN phones TYPE jsonb
            USING CASE
                WHEN phones IS NULL OR TRIM(phones) = '' THEN NULL
                ELSE jsonb_build_array(
                    jsonb_build_object(
                        'phone', phones,
                        'label', 'Основная регистратура'
                    )
                )
            END
        ");

        Schema::table('clinics', function (Blueprint $table) {
            $table->jsonb('services')
                ->comment('Сервисы, доступные в клинике')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('services');
        });

        DB::statement("
            ALTER TABLE clinics
            ALTER COLUMN phones TYPE varchar(255)
            USING CASE
                WHEN phones IS NULL THEN NULL
                WHEN jsonb_typeof(phones) = 'array'
                    THEN phones->0->>'phone'
                ELSE NULL
            END
        ");

        Schema::table('clinics', function (Blueprint $table) {
            $table->renameColumn('phones', 'phone');
        });
    }
};
