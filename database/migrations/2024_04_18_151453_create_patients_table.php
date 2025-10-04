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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('patronym')
                ->nullable();
            $table->date('birth_at');
            $table->text('address_registration')
                ->nullable();
            $table->text('address_residence')
                ->nullable();
            $table->text('address_job')
                ->nullable();
            $table->unsignedBigInteger('diagnose_id')
                ->nullable();
            $table->boolean('socially_dangerous')
                ->default(false);
            $table->boolean('disability')
                ->default(false);
            $table->boolean('married')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
