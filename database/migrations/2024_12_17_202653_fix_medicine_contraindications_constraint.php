<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Удалить существующий CHECK constraint, если он есть
        DB::statement('
            ALTER TABLE medicine_contraindications
            DROP CONSTRAINT IF EXISTS medicine_contraindications_contraindication_id_check
        ');

        // Добавить внешний ключ для связи с contraindications_types
        Schema::table('medicine_contraindications', function (Blueprint $table) {
            $table->unsignedBigInteger('contraindication_id')->change();

            $table->foreign('contraindication_id', 'fk_medicine_contraindications_to_contraindications_types')
                  ->references('id')
                  ->on('contraindications_types')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_contraindications', function (Blueprint $table) {
            // Удалить внешний ключ
            $table->dropForeign('fk_medicine_contraindications_to_contraindications_types');
        });

        // Восстановить CHECK constraint
        DB::statement('
            ALTER TABLE medicine_contraindications
            ADD CONSTRAINT medicine_contraindications_contraindication_id_check
            CHECK (contraindication_id > 0)
        ');
    }
};
