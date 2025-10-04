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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('User ID of author')
                ->index()
                ->constrained()
                ->onDelete('cascade');
            $table->string('name')
                ->index();
            $table->text('description');
            $table->string('photo');
            $table->foreignId('topics_category_id')
                ->comment('Category ID of parent category')
                ->index()
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
