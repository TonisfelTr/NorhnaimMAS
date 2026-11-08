<?php

use App\Models\RegistryRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (new RegistryRecord())->getTable();
        if (Schema::hasColumn($tableName, 'appointment_duration_minutes')) {
            throw new RuntimeException('Поле appointment_duration_minutes уже существует. Проверьте его назначение до применения миграции.');
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->unsignedSmallInteger('appointment_duration_minutes')
                ->nullable()
                ->comment('Продолжительность приёма; NULL — в старой записи не указана');
        });
    }

    public function down(): void
    {
        Schema::table((new RegistryRecord())->getTable(), function (Blueprint $table): void {
            $table->dropColumn('appointment_duration_minutes');
        });
    }
};
