<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppointmentFormatToRegistryRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::table('registry_records', function (Blueprint $table): void {
            $table->string('appointment_format', 32)
                ->default('in_person')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('registry_records', function (Blueprint $table): void {
            $table->dropColumn('appointment_format');
        });
    }
}
