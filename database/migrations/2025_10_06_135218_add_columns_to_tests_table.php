<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table): void {
            if (!Schema::hasColumn('tests', 'is_public')) {
                $table->boolean('is_public')
                    ->default(false)
                    ->comment('Публичный тест (виден всем врачам)')
                    ->after('description');
            }

            if (!Schema::hasColumn('tests', 'owner_doctor_id')) {
                $table->foreignId('owner_doctor_id')
                    ->nullable()
                    ->comment('Владелец приватного теста (частный врач)')
                    ->constrained('doctors')
                    ->nullOnDelete()
                    ->after('is_public');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table): void {
            if (Schema::hasColumn('tests', 'owner_doctor_id')) {
                $table->dropConstrainedForeignId('owner_doctor_id');
            }
            if (Schema::hasColumn('tests', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};
