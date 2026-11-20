<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tests', 'patient_self_service')) {
            return;
        }

        Schema::table('tests', function (Blueprint $table): void {
            $table->dropIndex('tests_patient_self_service_index');
            $table->dropColumn('patient_self_service');
        });
    }

    public function down(): void
    {
        // Намеренно не восстанавливаем флаг: доступность определяется
        // принадлежностью теста клинике, а не ручным разрешением.
    }
};
