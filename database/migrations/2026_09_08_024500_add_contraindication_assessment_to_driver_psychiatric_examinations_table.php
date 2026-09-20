<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('driver_psychiatric_examinations', 'contraindication_assessment')) {
            Schema::table('driver_psychiatric_examinations', function (Blueprint $table): void {
                $table->string('contraindication_assessment', 40)
                    ->nullable()
                    ->after('purpose');
            });
        }

        // На старых данных не оставляем номер положительной справки,
        // если ВК уже зафиксировала медицинские противопоказания.
        DB::table('driver_psychiatric_examinations')
            ->where('result', 'commission_contraindications')
            ->update([
                'certificate_number' => null,
                'certificate_date' => null,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('driver_psychiatric_examinations', 'contraindication_assessment')) {
            Schema::table('driver_psychiatric_examinations', function (Blueprint $table): void {
                $table->dropColumn('contraindication_assessment');
            });
        }
    }
};
