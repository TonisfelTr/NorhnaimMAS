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
        Schema::create('banned', function (Blueprint $table) {
           $table->id();
           $table->unsignedBigInteger('admin_id');
           $table->unsignedBigInteger('user_id');
           $table->unsignedBigInteger('rule_id');
           $table->datetime('from');
           $table->datetime('to');
           $table->timestamps();
           $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banned');
    }
};
