<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Существующие клиники не меняются. Стационары ссылаются на
        // амбулаторию через nullable FK в той же таблице clinics.
        if (!Schema::hasColumn('clinics', 'parent_clinic_id')) {
            Schema::table('clinics', function (Blueprint $table): void {
                $table->foreignId('parent_clinic_id')
                    ->nullable()
                    ->constrained('clinics')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Не удаляем колонку автоматически: другая миграция могла
        // создать её раньше. Удаление разрушило бы связи учреждений.
        // Для удаления необходима отдельная согласованная миграция.
    }
};
