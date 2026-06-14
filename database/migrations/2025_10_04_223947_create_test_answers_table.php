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
        Schema::create('test_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('test_sessions')->cascadeOnDelete();
            $t->foreignId('item_id')->constrained('test_items')->cascadeOnDelete();
            $t->foreignId('option_id')->nullable()->constrained('test_item_options')->nullOnDelete();

            $t->integer('value')->nullable();
            $t->json('raw')->nullable();
            $t->timestamps();

            $t->unique(['session_id', 'item_id']);
            $t->index(['session_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};
