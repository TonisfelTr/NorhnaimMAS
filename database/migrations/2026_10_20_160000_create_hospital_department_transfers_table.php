<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only audit of transfers between departments. Names are snapshotted:
        // the history stays legible if a department is later renamed or a user leaves.
        Schema::create('hospital_department_transfers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('from_department_id');
            $table->string('from_department_name');
            $table->unsignedBigInteger('to_department_id');
            $table->string('to_department_name');
            $table->unsignedBigInteger('from_room_id');
            $table->unsignedBigInteger('to_room_id');
            $table->unsignedBigInteger('from_bed_id');
            $table->unsignedBigInteger('to_bed_id');
            $table->unsignedBigInteger('previous_attending_doctor_id')->nullable();
            $table->unsignedBigInteger('transferred_by_user_id');
            $table->string('transferred_by_name');
            $table->dateTimeTz('transferred_at');
            $table->timestampsTz();

            $table->index(['assignment_id', 'id'], 'hdt_assignment_latest_idx');
            $table->index(['patient_id', 'transferred_at'], 'hdt_patient_time_idx');
            $table->index(['clinic_id', 'to_department_id', 'transferred_at'], 'hdt_dest_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_department_transfers');
    }
};
