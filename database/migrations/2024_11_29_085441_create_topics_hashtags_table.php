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
        Schema::create('topics_hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')
                ->comment('Topic ID for hashtag')
                ->constrained()
                ->onDelete('cascade');
            $table->string('hashtag')
                ->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics_hashtags');
    }
};
