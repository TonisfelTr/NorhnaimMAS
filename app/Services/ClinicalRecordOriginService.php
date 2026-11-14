<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Anamnesis;
use App\Models\Clinic;
use App\Models\ClinicalRecordOrigin;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientDiagnosisHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

final class ClinicalRecordOriginService
{
    public function captureRecord(Model $record, string $source = 'created'): void
    {
        if (
            !$record->exists
            || !Schema::hasTable('clinical_record_origins')
            || !$record->getKey()
        ) {
            return;
        }

        [$userId, $doctor, $clinic] = $this->actorContext($record);

        ClinicalRecordOrigin::query()->firstOrCreate(
            [
                'record_type' => get_class($record),
                'record_id' => (int) $record->getKey(),
            ],
            [
                'patient_id' => $this->positiveInt(
                    $record->getAttribute('patient_id')
                ),
                'doctor_id' => $doctor?->getKey(),
                'clinic_id' => $clinic?->getKey(),
                'user_id' => $userId,
                'doctor_name_snapshot' => $this->doctorName($doctor),
                'clinic_name_snapshot' => $clinic?->name,
                'source' => $source,
                'captured_at' => now(),
            ]
        );
    }

    public function decorate(iterable $records): void
    {
        $records = collect($records)
            ->filter(static fn ($record): bool =>
                $record instanceof Model
                && $record->exists
                && $record->getKey() !== null
            );

        if ($records->isEmpty()) {
            return;
        }

        $records
            ->groupBy(static fn (Model $record): string => get_class($record))
            ->each(function (Collection $typedRecords, string $type): void {
                $ids = $typedRecords
                    ->map(static fn (Model $record): int => (int) $record->getKey())
                    ->unique()
                    ->values()
                    ->all();

                $origins = Schema::hasTable('clinical_record_origins')
                    ? ClinicalRecordOrigin::query()
                        ->where('record_type', $type)
                        ->whereIn('record_id', $ids)
                        ->get()
                        ->keyBy('record_id')
                    : collect();

                $legacyDoctorIds = $typedRecords
                    ->map(fn (Model $record): ?int =>
                        $this->positiveInt($record->getAttribute('doctor_id'))
                    )
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $legacyDoctors = $legacyDoctorIds !== []
                    ? Doctor::query()
                        ->whereIn('id', $legacyDoctorIds)
                        ->get(['id', 'surname', 'name', 'patronym'])
                        ->keyBy('id')
                    : collect();

                foreach ($typedRecords as $record) {
                    $origin = $origins->get((int) $record->getKey());

                    if ($origin) {
                        $record->setAttribute('clinical_origin', [
                            'doctor_name' => $origin->doctor_name_snapshot
                                ?: 'Врач не зафиксирован',
                            'clinic_name' => $origin->clinic_name_snapshot
                                ?: 'Клиника не зафиксирована',
                            'captured_at' => $origin->captured_at?->format('d.m.Y H:i'),
                            'exact' => true,
                            'source' => (string) $origin->source,
                        ]);

                        continue;
                    }

                    $legacyDoctorId = $this->positiveInt(
                        $record->getAttribute('doctor_id')
                    );
                    $legacyDoctor = $legacyDoctorId
                        ? $legacyDoctors->get($legacyDoctorId)
                        : null;

                    $record->setAttribute('clinical_origin', [
                        'doctor_name' => $legacyDoctor
                            ? $this->doctorName($legacyDoctor)
                            : 'Врач не зафиксирован',
                        'clinic_name' => 'Клиника не зафиксирована',
                        'captured_at' => null,
                        'exact' => false,
                        'source' => 'legacy',
                    ]);
                }
            });
    }

    public function captureDiagnosis(Patient $patient): void
    {
        if (
            !Schema::hasTable('patient_diagnosis_history')
            || !$patient->diagnose_id
        ) {
            return;
        }

        [$userId, $doctor, $clinic] = $this->actorContext($patient);

        PatientDiagnosisHistory::query()->create([
            'patient_id' => (int) $patient->getKey(),
            'diagnose_id' => (int) $patient->diagnose_id,
            'doctor_id' => $doctor?->getKey(),
            'clinic_id' => $clinic?->getKey(),
            'user_id' => $userId,
            'doctor_name_snapshot' => $this->doctorName($doctor),
            'clinic_name_snapshot' => $clinic?->name,
            'source' => 'patient_diagnose_change',
            'set_at' => now(),
        ]);
    }

    public function currentDiagnosisOrigin(Patient $patient): array
    {
        if ($patient->diagnose_id && Schema::hasTable('patient_diagnosis_history')) {
            $history = PatientDiagnosisHistory::query()
                ->where('patient_id', (int) $patient->getKey())
                ->where('diagnose_id', (int) $patient->diagnose_id)
                ->latest('set_at')
                ->latest('id')
                ->first();

            if ($history) {
                return [
                    'doctor_name' => $history->doctor_name_snapshot
                        ?: 'Врач не зафиксирован',
                    'clinic_name' => $history->clinic_name_snapshot
                        ?: 'Клиника не зафиксирована',
                    'captured_at' => $history->set_at?->format('d.m.Y H:i'),
                    'exact' => true,
                    'source' => (string) $history->source,
                ];
            }
        }

        if ($patient->diagnose_id) {
            $anamnesis = Anamnesis::query()
                ->with('doctor')
                ->where('patient_id', (int) $patient->getKey())
                ->where('diagnose_id', (int) $patient->diagnose_id)
                ->where('is_current', true)
                ->latest('created_at')
                ->first();

            if ($anamnesis) {
                return [
                    'doctor_name' => $this->doctorName($anamnesis->doctor)
                        ?: 'Врач не зафиксирован',
                    'clinic_name' => 'Клиника не зафиксирована',
                    'captured_at' => $anamnesis->created_at?->format('d.m.Y H:i'),
                    'exact' => false,
                    'source' => 'legacy_current_anamnesis',
                ];
            }
        }

        return [
            'doctor_name' => 'Врач не зафиксирован',
            'clinic_name' => 'Клиника не зафиксирована',
            'captured_at' => null,
            'exact' => false,
            'source' => 'legacy',
        ];
    }

    private function actorContext(Model $record): array
    {
        $user = Auth::user();
        $actorDoctor = $user?->doctor()->first();

        $recordDoctorId = $this->positiveInt(
            $record->getAttribute('doctor_id')
        );

        $doctor = $actorDoctor;

        if (!$doctor && $recordDoctorId) {
            $doctor = Doctor::query()->find($recordDoctorId);
        }

        $recordClinicId = $this->positiveInt(
            $record->getAttribute('clinic_id')
        );

        $clinicId = $recordClinicId
            ?: $this->positiveInt($doctor?->clinic_id);

        $clinic = $clinicId
            ? Clinic::query()->find($clinicId)
            : null;

        return [
            $user?->getKey() ? (int) $user->getKey() : null,
            $doctor,
            $clinic,
        ];
    }

    private function doctorName(?Doctor $doctor): ?string
    {
        if (!$doctor) {
            return null;
        }

        $name = trim(implode(' ', array_filter([
            $doctor->surname,
            $doctor->name,
            $doctor->patronym,
        ])));

        return $name !== '' ? $name : 'Врач #' . $doctor->getKey();
    }

    private function positiveInt(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
