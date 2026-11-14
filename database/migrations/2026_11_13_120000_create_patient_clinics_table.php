<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_clinics')) {
            Schema::create('patient_clinics', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('patient_id')
                    ->constrained('patients')
                    ->cascadeOnDelete();
                $table->foreignId('clinic_id')
                    ->constrained('clinics')
                    ->cascadeOnDelete();
                $table->foreignId('doctor_id')
                    ->nullable()
                    ->constrained('doctors')
                    ->nullOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('attached_at')->nullable();
                $table->timestamp('detached_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['patient_id', 'clinic_id'],
                    'patient_clinics_patient_clinic_unique'
                );
                $table->index(
                    ['patient_id', 'is_active'],
                    'patient_clinics_patient_active_idx'
                );
            });
        }

        $now = now();

        // 1. Preserve the current legacy clinic as the primary membership.
        DB::table('patients')
            ->select(['id', 'clinic_id', 'doctor_id'])
            ->whereNotNull('clinic_id')
            ->orderBy('id')
            ->chunkById(500, function ($patients) use ($now): void {
                foreach ($patients as $patient) {
                    $clinicId = (int) $patient->clinic_id;
                    if ($clinicId <= 0) {
                        continue;
                    }

                    $doctorId = null;

                    if (!empty($patient->doctor_id)) {
                        $doctorClinicId = DB::table('doctors')
                            ->where('id', (int) $patient->doctor_id)
                            ->value('clinic_id');

                        if ((int) $doctorClinicId === $clinicId) {
                            $doctorId = (int) $patient->doctor_id;
                        }
                    }

                    DB::table('patient_clinics')->updateOrInsert(
                        [
                            'patient_id' => (int) $patient->id,
                            'clinic_id' => $clinicId,
                        ],
                        [
                            'doctor_id' => $doctorId,
                            'is_primary' => true,
                            'is_active' => true,
                            'attached_at' => $now,
                            'detached_at' => null,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            });

        // 2. Every clinic found in the patient's registry history is a real
        // membership too. Soft-deleted appointments are intentionally included:
        // cancelling a visit does not mean the person stopped being a patient
        // of that clinic.
        if (
            Schema::hasTable('registry_records')
            && Schema::hasColumn('registry_records', 'clinic_id')
        ) {
            DB::table('registry_records')
                ->select(['patient_id', 'clinic_id'])
                ->whereNotNull('patient_id')
                ->whereNotNull('clinic_id')
                ->distinct()
                ->orderBy('patient_id')
                ->chunk(1000, function ($rows) use ($now): void {
                    foreach ($rows as $row) {
                        $patientId = (int) $row->patient_id;
                        $clinicId = (int) $row->clinic_id;

                        if ($patientId <= 0 || $clinicId <= 0) {
                            continue;
                        }

                        DB::table('patient_clinics')->insertOrIgnore([
                            'patient_id' => $patientId,
                            'clinic_id' => $clinicId,
                            'doctor_id' => null,
                            'is_primary' => false,
                            'is_active' => true,
                            'attached_at' => $now,
                            'detached_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                });
        }

        // 3. Historical patients without patients.clinic_id still get one
        // deterministic primary membership.
        $patientIds = DB::table('patient_clinics')
            ->select('patient_id')
            ->distinct()
            ->pluck('patient_id');

        foreach ($patientIds as $patientId) {
            $hasPrimary = DB::table('patient_clinics')
                ->where('patient_id', (int) $patientId)
                ->where('is_primary', true)
                ->exists();

            if ($hasPrimary) {
                continue;
            }

            $firstId = DB::table('patient_clinics')
                ->where('patient_id', (int) $patientId)
                ->orderBy('id')
                ->value('id');

            if ($firstId) {
                DB::table('patient_clinics')
                    ->where('id', (int) $firstId)
                    ->update([
                        'is_primary' => true,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_clinics');
    }
};
