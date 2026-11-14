<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('patients', 'phone')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table): void {
            $table->string('phone', 50)
                ->nullable()
                ->comment('Контактный телефон пациента');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('patients', 'phone')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn('phone');
        });
    }
};
