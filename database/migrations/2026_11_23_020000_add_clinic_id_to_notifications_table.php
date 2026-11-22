<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications') || Schema::hasColumn('notifications', 'clinic_id')) {
            return;
        }
        Schema::table('notifications', function (Blueprint $table): void {
            $table->unsignedBigInteger('clinic_id')->nullable()->index('notifications_clinic_idx');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'clinic_id')) {
            Schema::table('notifications', static function (Blueprint $table): void {
                $table->dropIndex('notifications_clinic_idx');
                $table->dropColumn('clinic_id');
            });
        }
    }
};
