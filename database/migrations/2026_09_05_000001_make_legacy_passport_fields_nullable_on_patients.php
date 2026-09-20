<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Старые паспортные поля в patients оставляем для обратной совместимости,
         * но делаем nullable, потому что у пациента младше 14 лет паспорта РФ
         * может не быть. Новые документы будут храниться отдельно.
         */
        DB::statement('ALTER TABLE patients ALTER COLUMN serial DROP NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN number DROP NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN issued_by DROP NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN issued_at DROP NOT NULL');

        DB::statement("
            COMMENT ON COLUMN patients.serial IS
            'LEGACY: серия паспорта пациента. Новые удостоверяющие личность документы хранятся в patient_identity_documents'
        ");

        DB::statement("
            COMMENT ON COLUMN patients.number IS
            'LEGACY: номер паспорта пациента. Новые удостоверяющие личность документы хранятся в patient_identity_documents'
        ");

        DB::statement("
            COMMENT ON COLUMN patients.issued_by IS
            'LEGACY: орган, выдавший паспорт пациента. Новые документы хранятся в patient_identity_documents'
        ");

        DB::statement("
            COMMENT ON COLUMN patients.issued_at IS
            'LEGACY: дата выдачи паспорта пациента. Новые документы хранятся в patient_identity_documents'
        ");

        DB::statement("
            COMMENT ON COLUMN patients.department_code IS
            'LEGACY: код подразделения паспорта пациента. Новые документы хранятся в patient_identity_documents'
        ");
    }

    public function down(): void
    {
        /*
         * ВНИМАНИЕ:
         * SET NOT NULL завершится ошибкой, если после применения миграции
         * появились пациенты без паспортных данных (например, дети).
         */
        DB::statement('ALTER TABLE patients ALTER COLUMN serial SET NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN number SET NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN issued_by SET NOT NULL');
        DB::statement('ALTER TABLE patients ALTER COLUMN issued_at SET NOT NULL');

        DB::statement("COMMENT ON COLUMN patients.serial IS 'Серия паспорта'");
        DB::statement("COMMENT ON COLUMN patients.number IS 'Номер паспорта'");
        DB::statement("COMMENT ON COLUMN patients.issued_by IS 'Кем выдан'");
        DB::statement("COMMENT ON COLUMN patients.issued_at IS 'Дата выдачи паспорта'");
        DB::statement("COMMENT ON COLUMN patients.department_code IS 'Код подразделения'");
    }
};
