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
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->boolean('in_progress')
                ->default(false)
                ->index();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('locked_at')
                ->nullable()
                ->index();
            $table->unsignedInteger('pin_attempts')
                ->default(0);
            $table->timestamp('pin_locked_until')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_sessions', function (Blueprint $table) {
            $table->dropColumn(['in_progress','locked_by_user_id','locked_at','pin_attempts','pin_locked_until']);
        });
    }
};
