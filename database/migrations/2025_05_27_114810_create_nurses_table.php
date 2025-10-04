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
        Schema::create('nurses', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->comment('Имя медсестры');
            $table->string('surname')
                ->comment('Фамилия медсестры');
            $table->string('patronym')
                ->nullable()
                ->comment('Отчество медсестры');
            $table->unsignedBigInteger('clinic_id')
                ->comment('К какой клинике привязан(а)');
            $table->unsignedBigInteger('user_id')
                ->comment('Пользователь, к которому относится');
            $table->string('profession');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurses');
    }
};
