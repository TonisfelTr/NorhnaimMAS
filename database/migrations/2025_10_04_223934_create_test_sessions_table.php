<?php

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
        Schema::create('test_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('test_id')->constrained('tests')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('clinician_id')->nullable()->constrained('users')->nullOnDelete();

            $t->string('status')->default('in_progress');
            $t->jsonb('context')->nullable();
            $t->timestamps();

            $t->index(['test_id', 'user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sessions');
    }
};
