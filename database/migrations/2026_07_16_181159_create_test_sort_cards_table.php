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
        Schema::create('test_sort_cards', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('test_id')
                ->constrained('tests')
                ->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title', 255);
            $table->text('text')->nullable();
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->index([
                'test_id',
                'sort',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sort_cards');
    }
};
