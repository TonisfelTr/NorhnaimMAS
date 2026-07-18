<?php

use App\Enums\Tests\PatientTestSessionsStatusesEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_test_sessions', function (Blueprint $table) {
            $table->uuid('id')
                ->primary();
            $table->uuid('patient_id');
            $table->uuid('assignment_id');
            $table->string('token', 128)
                ->unique();
            $table->timestamp('expires_at');
            $table->enum('status', PatientTestSessionsStatusesEnum::values());

            $table->timestamps();
            $table->index(['patient_id', 'assignment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_test_sessions');
    }
};
