<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_identity_documents', function (Blueprint $table): void {
            $table->id()
                ->comment('Уникальный идентификатор документа пациента');

            $table->foreignId('patient_id')
                ->comment('Пациент, которому принадлежит документ')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->string('document_type', 50)
                ->comment('Тип документа: passport_rf, birth_certificate, foreign_passport, residence_permit, temporary_identity, foreign_identity, other');

            $table->string('document_name', 255)
                ->nullable()
                ->comment('Отображаемое наименование документа');

            $table->string('series', 50)
                ->nullable()
                ->comment('Серия документа, если предусмотрена');

            $table->string('number', 100)
                ->nullable()
                ->comment('Номер документа, если предусмотрен');

            $table->text('issued_by')
                ->nullable()
                ->comment('Орган или организация, выдавшие документ');

            $table->date('issued_at')
                ->nullable()
                ->comment('Дата выдачи документа');

            $table->string('department_code', 20)
                ->nullable()
                ->comment('Код подразделения или аналогичный реквизит документа');

            $table->boolean('is_primary')
                ->default(false)
                ->comment('Признак основного удостоверяющего личность документа пациента');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Признак актуальности документа');

            $table->timestamp('created_at')
                ->nullable()
                ->comment('Дата и время создания записи');

            $table->timestamp('updated_at')
                ->nullable()
                ->comment('Дата и время последнего изменения записи');

            $table->index(
                ['patient_id', 'document_type'],
                'patient_identity_documents_patient_type_idx'
            );
        });

        /*
         * У пациента может быть только один активный основной документ.
         * PostgreSQL partial unique index удобнее обычного UNIQUE.
         */
        DB::statement('
            CREATE UNIQUE INDEX patient_identity_documents_one_primary_idx
            ON patient_identity_documents (patient_id)
            WHERE is_primary = TRUE AND is_active = TRUE
        ');

        DB::statement("
            COMMENT ON TABLE patient_identity_documents IS
            'Удостоверяющие личность документы пациента: паспорт, свидетельство о рождении и другие документы'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_identity_documents');
    }
};
