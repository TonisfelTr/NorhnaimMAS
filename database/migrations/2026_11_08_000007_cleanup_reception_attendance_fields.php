<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('medical_encounters')) {
            return;
        }

        $dropMarkedAt = Schema::hasColumn('medical_encounters', 'attendance_marked_at');
        $dropMarkedBy = Schema::hasColumn('medical_encounters', 'attendance_marked_by_user_id');

        if (!$dropMarkedAt && !$dropMarkedBy) {
            return;
        }

        Schema::table('medical_encounters', static function (Blueprint $table) use ($dropMarkedAt, $dropMarkedBy): void {
            if ($dropMarkedBy) {
                $table->dropColumn('attendance_marked_by_user_id');
            }
            if ($dropMarkedAt) {
                $table->dropColumn('attendance_marked_at');
            }
        });
    }

    public function down(): void
    {
        // Эти поля больше не используются для явки.
    }
};
