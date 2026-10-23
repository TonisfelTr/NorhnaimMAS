<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_bed_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bed_id')
                ->constrained('hospital_beds')
                ->cascadeOnDelete();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->restrictOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['bed_id', 'released_at']);
            $table->index(['patient_id', 'released_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE UNIQUE INDEX hospital_bed_assignments_active_bed_unique '
                . 'ON hospital_bed_assignments (bed_id) '
                . 'WHERE released_at IS NULL'
            );

            DB::statement(
                'CREATE UNIQUE INDEX hospital_bed_assignments_active_patient_unique '
                . 'ON hospital_bed_assignments (patient_id) '
                . 'WHERE released_at IS NULL'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_bed_assignments');
    }
};
