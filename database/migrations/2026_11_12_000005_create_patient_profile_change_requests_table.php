<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_profile_change_requests')) {
            return;
        }

        Schema::create('patient_profile_change_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('review_target', 30);
            $table->string('status', 20)->default('pending');
            $table->longText('payload');
            $table->json('changed_fields');
            $table->text('reason');
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewed_by_role', 50)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['patient_id', 'status'],
                'patient_profile_change_requests_patient_status_idx'
            );

            $table->index(
                ['review_target', 'status'],
                'patient_profile_change_requests_target_status_idx'
            );
        });

        DB::statement("
            CREATE UNIQUE INDEX patient_profile_change_requests_one_pending_idx
            ON patient_profile_change_requests (patient_id)
            WHERE status = 'pending'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profile_change_requests');
    }
};
