<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing clinic documents keep their doctor_id and are classified as clinic uploads.
        DB::statement('ALTER TABLE medicine_documents ALTER COLUMN doctor_id DROP NOT NULL');

        Schema::table('medicine_documents', function (Blueprint $table): void {
            $table->string('source', 20)->default('clinic');
            $table->string('category', 40)->default('other');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();

            $table->index(['patient_id', 'source', 'created_at'], 'medicine_docs_patient_source_created_idx');
            $table->index(['source', 'reviewed_at'], 'medicine_docs_source_reviewed_idx');
        });
    }

    public function down(): void
    {
        if (DB::table('medicine_documents')->whereNull('doctor_id')->exists()) {
            throw new \RuntimeException(
                'Нельзя откатить миграцию: существуют документы без doctor_id. '
                . 'Сначала перенесите или удалите документы, загруженные пациентами.'
            );
        }

        Schema::table('medicine_documents', function (Blueprint $table): void {
            $table->dropIndex('medicine_docs_patient_source_created_idx');
            $table->dropIndex('medicine_docs_source_reviewed_idx');
            $table->dropForeign(['uploaded_by_user_id']);
            $table->dropForeign(['reviewed_by_doctor_id']);
            $table->dropColumn([
                'source',
                'category',
                'original_name',
                'mime_type',
                'file_size',
                'uploaded_by_user_id',
                'reviewed_at',
                'reviewed_by_doctor_id',
            ]);
        });

        DB::statement('ALTER TABLE medicine_documents ALTER COLUMN doctor_id SET NOT NULL');
    }
};
