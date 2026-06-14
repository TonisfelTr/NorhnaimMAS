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
        Schema::create('medicine_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')
                ->comment('ID лекарства')
                ->constrained('drugs');
            $table->foreignId('interacts_with_drug_id')
                ->comment('ID лекарства, с которым взаимодействует')
                ->constrained('drugs');
            $table->text('severity');
            $table->text('comment')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_interactions');
    }
};
