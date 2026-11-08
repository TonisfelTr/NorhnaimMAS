<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_disability_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->restrictOnDelete();
            // unknown = данных нет; present = инвалидность заявлена; absent = по представленным сведениям не указана.
            $table->string('status', 16)->default('unknown');
            $table->string('disability_group', 16)->nullable();
            $table->date('established_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('indefinite')->default(false);
            $table->string('cause', 500)->nullable();
            // The document is independently checked by authorized staff, not verified by the state API.
            $table->string('source_type', 40)->default('patient_statement');
            $table->string('document_name', 255)->nullable();
            $table->string('document_number', 100)->nullable();
            $table->date('document_issued_at')->nullable();
            $table->string('issued_by', 255)->nullable();
            $table->foreignId('evidence_document_id')->nullable()
                ->constrained('medicine_documents')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('patient_disability_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_disability_record_id')
                ->constrained('patient_disability_records')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            // Changed field names only: do not replicate protected medical data in the audit table.
            $table->jsonb('changed_fields');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['patient_disability_record_id', 'created_at'], 'patient_disability_audit_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_disability_audits');
        Schema::dropIfExists('patient_disability_records');
    }
};
