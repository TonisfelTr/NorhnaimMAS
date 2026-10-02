<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasColumn(
                'clinic_cabinets',
                'functional_methods'
            )
        ) {
            Schema::table(
                'clinic_cabinets',
                function (Blueprint $table): void {
                    $table->jsonb('functional_methods')
                        ->nullable();
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'clinic_cabinets',
                'functional_methods'
            )
        ) {
            Schema::table(
                'clinic_cabinets',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'functional_methods'
                    );
                }
            );
        }
    }
};
