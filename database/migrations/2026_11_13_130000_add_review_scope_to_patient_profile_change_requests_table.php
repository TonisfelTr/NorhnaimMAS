<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_profile_change_requests')) {
            return;
        }

        Schema::table('patient_profile_change_requests', function (Blueprint $table): void {
            if (!Schema::hasColumn('patient_profile_change_requests', 'review_clinic_id')) {
                $table->foreignId('review_clinic_id')
                    ->nullable()
                    ->after('review_target')
                    ->constrained('clinics')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('patient_profile_change_requests', 'review_doctor_id')) {
                $table->foreignId('review_doctor_id')
                    ->nullable()
                    ->after('review_clinic_id')
                    ->constrained('doctors')
                    ->nullOnDelete();
            }
        });

        // Backfill old requests from the legacy single-clinic fields.
        DB::table('patient_profile_change_requests')
            ->select(['id', 'patient_id', 'review_target'])
            ->whereNull('review_clinic_id')
            ->orderBy('id')
            ->chunkById(500, function ($requests): void {
                foreach ($requests as $request) {
                    $patient = DB::table('patients')
                        ->select(['clinic_id', 'doctor_id'])
                        ->where('id', (int) $request->patient_id)
                        ->first();

                    if (!$patient) {
                        continue;
                    }

                    $clinicId = $patient->clinic_id
                        ? (int) $patient->clinic_id
                        : null;
                    $doctorId = null;

                    if (
                        $request->review_target === 'doctor'
                        && !empty($patient->doctor_id)
                    ) {
                        $doctor = DB::table('doctors')
                            ->select(['id', 'clinic_id'])
                            ->where('id', (int) $patient->doctor_id)
                            ->first();

                        if ($doctor) {
                            $doctorId = (int) $doctor->id;
                            $clinicId = $doctor->clinic_id
                                ? (int) $doctor->clinic_id
                                : $clinicId;
                        }
                    }

                    DB::table('patient_profile_change_requests')
                        ->where('id', (int) $request->id)
                        ->update([
                            'review_clinic_id' => $clinicId,
                            'review_doctor_id' => $doctorId,
                        ]);
                }
            });

        if (
            Schema::hasColumn('patient_profile_change_requests', 'review_doctor_id')
            && ! $this->indexExists(
                'patient_profile_change_requests',
                'patient_profile_change_requests_review_doctor_status_idx'
            )
        ) {
            Schema::table('patient_profile_change_requests', function (Blueprint $table): void {
                $table->index(
                    ['review_doctor_id', 'status'],
                    'patient_profile_change_requests_review_doctor_status_idx'
                );
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('patient_profile_change_requests')) {
            return;
        }

        Schema::table('patient_profile_change_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('patient_profile_change_requests', 'review_doctor_id')) {
                $table->dropForeign(['review_doctor_id']);
            }
            if (Schema::hasColumn('patient_profile_change_requests', 'review_clinic_id')) {
                $table->dropForeign(['review_clinic_id']);
            }
        });

        Schema::table('patient_profile_change_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('patient_profile_change_requests', 'review_doctor_id')) {
                $table->dropColumn('review_doctor_id');
            }
            if (Schema::hasColumn('patient_profile_change_requests', 'review_clinic_id')) {
                $table->dropColumn('review_clinic_id');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('tablename', $table)
                ->where('indexname', $index)
                ->exists();
        }

        if ($driver === 'mysql') {
            return collect(DB::select("SHOW INDEX FROM {$table}"))
                ->contains(static fn ($row): bool =>
                    ($row->Key_name ?? null) === $index
                );
        }

        return false;
    }
};
