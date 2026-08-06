<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'medical_prescriptions',
            function (Blueprint $table): void {
                $table
                    ->boolean('is_strict')
                    ->default(false)
                    ->index();
            }
        );

        /*
         * Заполняем старые записи на основании формы рецепта.
         */
        DB::statement(
            "
            UPDATE medical_prescriptions
            SET is_strict = CASE
                WHEN prescription_form ILIKE '%148%'
                    THEN TRUE
                ELSE FALSE
            END
            "
        );
    }

    public function down(): void
    {
        Schema::table(
            'medical_prescriptions',
            function (Blueprint $table): void {
                $table->dropColumn('is_strict');
            }
        );
    }
};
