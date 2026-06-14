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
        Schema::create('test_results', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('test_sessions')->cascadeOnDelete();
            $t->foreignId('key_id')->nullable()->constrained('test_keys')->nullOnDelete();

            $t->integer('score');
            $t->string('range_text')->nullable();
            $t->text('interpretation')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['session_id', 'key_id']);
            $t->index(['session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
