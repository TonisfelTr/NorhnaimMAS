<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mse_referrals', function (Blueprint $table): void {
            $table->string('consent_document_disk', 64)->nullable();
            $table->string('consent_document_path', 1000)->nullable();
            $table->string('consent_document_original_name')->nullable();
            $table->string('consent_document_mime_type', 160)->nullable();
            $table->unsignedBigInteger('consent_document_size')->nullable();
            $table->timestamp('consent_document_uploaded_at')->nullable();
            $table->foreignId('consent_document_uploaded_by_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mse_referrals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('consent_document_uploaded_by_doctor_id');
            $table->dropColumn([
                'consent_document_disk',
                'consent_document_path',
                'consent_document_original_name',
                'consent_document_mime_type',
                'consent_document_size',
                'consent_document_uploaded_at',
            ]);
        });
    }
};
