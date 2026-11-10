<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Фотографии клиники хранятся только через Spatie Media Library.
     *
     * Удаляем только legacy-поля изображений/путей к изображениям.
     * Логотипы и служебные документы этой миграцией не затрагиваются.
     */
    public function up(): void
    {
        $legacyImageColumns = [
            'photo',
            'photos',
            'photo_path',
            'cover_photo',
            'cover_photo_path',
            'image',
            'image_path',
            'cover_image',
            'cover_image_path',
        ];

        foreach ($legacyImageColumns as $column) {
            if (!Schema::hasColumn('clinics', $column)) {
                continue;
            }

            Schema::table(
                'clinics',
                function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * Намеренно не восстанавливаем legacy-поля:
         * после перехода на Spatie источником фотографий является media.
         */
    }
};
