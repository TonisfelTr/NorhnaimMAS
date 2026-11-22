<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PatientDiagnosisHistory;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Models\PatientClinic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The currently assigned diagnosis belongs to a patient AND a specific clinic.
 * No fallback to patients.diagnose_id: that legacy field is patient-global.
 */
final class PatientClinicDiagnosisService
{
    /** F00-F99 and G00-G99 only, including their ICD-10 subcodes. */
    public static function isAllowedCode(string $code): bool
    {
        return preg_match('/^[FG][0-9]{2}(?:\.[A-Z0-9]{1,6})?$/i', trim($code)) === 1;
    }

    /** Only a doctor of an actively linked clinic may change its diagnosis. */
    public function canAssign(User $user, Patient $patient): bool
    {
        if (!$user->hasRole('doctors')
            || !Schema::hasTable('patient_clinics')
            || !Schema::hasTable('patient_diagnosis_history')) {
            return false;
        }

        $doctor = $user->doctor()->with('clinic')->first();
        if (!$doctor || !$doctor->clinic || (int) $doctor->clinic_id <= 0) {
            return false;
        }

        return PatientClinic::query()
            ->where('patient_id', (int) $patient->getKey())
            ->where('clinic_id', (int) $doctor->clinic_id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Serialize changes per patient+clinic via the membership row lock.
     * Return false if the selected diagnosis is already current.
     */
    public function assignForCurrentDoctor(Patient $patient, User $user, Diagnose $diagnose): bool
    {
        abort_unless($this->canAssign($user, $patient), 403);

        // This guard also protects callers that bypass the search endpoint.
        if (!self::isAllowedCode((string) $diagnose->code)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'diagnose_id' => 'Основным диагнозом может быть только код из групп F или G МКБ-10.',
            ])->errorBag('clinicDiagnosis');
        }

        $doctor = $user->doctor()->with('clinic')->firstOrFail();
        $patientId = (int) $patient->getKey();
        $clinicId = (int) $doctor->clinic_id;

        return DB::transaction(function () use ($patientId, $clinicId, $user, $doctor, $diagnose): bool {
            $membership = PatientClinic::query()
                ->where('patient_id', $patientId)
                ->where('clinic_id', $clinicId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();
            abort_unless($membership !== null, 403);

            $current = $this->currentForClinic($patientId, $clinicId);
            if ($current && (int) $current->diagnose_id === (int) $diagnose->getKey()) {
                return false;
            }

            PatientDiagnosisHistory::query()->create([
                'patient_id' => $patientId,
                'diagnose_id' => (int) $diagnose->getKey(),
                'doctor_id' => (int) $doctor->getKey(),
                'clinic_id' => $clinicId,
                'user_id' => (int) $user->getKey(),
                'doctor_name_snapshot' => trim(implode(' ', array_filter([
                    $doctor->surname, $doctor->name, $doctor->patronym,
                ]))),
                'clinic_name_snapshot' => $doctor->clinic?->name,
                'source' => 'clinic_manual_current',
                'set_at' => now(),
            ]);

            // Do NOT mutate patients.diagnose_id: the legacy field is global.
            // The old history rows remain available to the patient.
            return true;
        }, 3);
    }

    public function currentForClinic(int $patientId, int $clinicId): ?PatientDiagnosisHistory
    {
        if ($patientId <= 0 || $clinicId <= 0 || !Schema::hasTable('patient_diagnosis_history')) {
            return null;
        }

        return PatientDiagnosisHistory::query()
            ->with('diagnose')
            ->where('patient_id', $patientId)
            ->where('clinic_id', $clinicId)
            ->whereNotNull('diagnose_id')
            ->whereHas('diagnose')
            ->orderByDesc('set_at')
            ->orderByDesc('id')
            ->first();
    }
}
