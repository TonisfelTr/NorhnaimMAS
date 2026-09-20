<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_representatives', function (Blueprint $table): void {
            $table->id()
                ->comment('Уникальный идентификатор представителя пациента');

            $table->foreignId('patient_id')
                ->comment('Пациент, интересы которого представляет лицо')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->string('representative_type', 50)
                ->comment('Тип представителя: mother, father, adoptive_parent, guardian, trustee, authorized, other');

            $table->string('surname', 255)
                ->comment('Фамилия представителя');

            $table->string('name', 255)
                ->comment('Имя представителя');

            $table->string('patronym', 255)
                ->nullable()
                ->comment('Отчество представителя');

            $table->date('birth_at')
                ->nullable()
                ->comment('Дата рождения представителя');

            $table->string('snils', 14)
                ->nullable()
                ->comment('СНИЛС представителя');

            $table->string('phone', 50)
                ->nullable()
                ->comment('Контактный телефон представителя');

            $table->string('email', 255)
                ->nullable()
                ->comment('Адрес электронной почты представителя');

            $table->boolean('is_primary')
                ->default(false)
                ->comment('Признак основного представителя пациента');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Признак актуальности полномочий или использования представителя');

            $table->timestamp('created_at')
                ->nullable()
                ->comment('Дата и время создания записи');

            $table->timestamp('updated_at')
                ->nullable()
                ->comment('Дата и время последнего изменения записи');

            $table->index(
                ['patient_id', 'representative_type'],
                'patient_representatives_patient_type_idx'
            );
        });

        DB::statement('
            CREATE UNIQUE INDEX patient_representatives_one_primary_idx
            ON patient_representatives (patient_id)
            WHERE is_primary = TRUE AND is_active = TRUE
        ');

        DB::statement("
            COMMENT ON TABLE patient_representatives IS
            'Законные и уполномоченные представители пациента'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_representatives');
    }
};
