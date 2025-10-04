<?php

use App\Enums\ContraindicationsTypesEnum;
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
        Schema::create('medicine_contraindications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->enum('type', [ 1, 2 ])
                ->comment('1 - absolute, 2 - relative');
            $table->unsignedBigInteger('contraindication_id');
            $table->timestamps();

            $table->foreign('medicine_id', 'fk_medicine_id_from_medicine_contraindications')
                ->on('medicines')
                ->references('id')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_contraindications');
    }
};
