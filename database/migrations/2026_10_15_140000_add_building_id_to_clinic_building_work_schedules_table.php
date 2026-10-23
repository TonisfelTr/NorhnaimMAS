<?php

use App\Models\ClinicWorkShedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_work_shedules', function (Blueprint $table) {
            $table->foreignId('building_id')
                ->comment('Ключ, указывающий на корпус')
                ->nullable()
                ->constrained('clinic_buildings');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_work_shedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('building_id');
        });
    }
};
