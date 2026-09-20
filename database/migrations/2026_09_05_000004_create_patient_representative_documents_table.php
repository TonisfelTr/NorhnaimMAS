<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_representative_documents', function (Blueprint $table): void {
            $table->id()
                ->comment('Уникальный идентификатор документа представителя');

            $table->foreignId('representative_id')
                ->comment('Представитель, которому относится документ')
                ->constrained('patient_representatives')
                ->cascadeOnDelete();

            $table->string('document_purpose', 30)
                ->comment('Назначение документа: identity — удостоверение личности, authority — подтверждение полномочий');

            $table->string('document_type', 50)
                ->comment('Тип документа: passport_rf, birth_certificate, guardianship_order, power_of_attorney, other и т. п.');

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
                ->comment('Признак основного документа данного назначения у представителя');

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
                ['representative_id', 'document_purpose'],
                'patient_representative_documents_rep_purpose_idx'
            );

            $table->index(
                ['representative_id', 'document_type'],
                'patient_representative_documents_rep_type_idx'
            );
        });

        /*
         * У представителя может быть один активный основной документ
         * на каждое назначение: один основной identity и один authority.
         */
        DB::statement('
            CREATE UNIQUE INDEX patient_representative_documents_one_primary_idx
            ON patient_representative_documents (representative_id, document_purpose)
            WHERE is_primary = TRUE AND is_active = TRUE
        ');

        DB::statement("
            COMMENT ON TABLE patient_representative_documents IS
            'Документы законного или уполномоченного представителя: удостоверяющие личность и подтверждающие полномочия'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_representative_documents');
    }
};
