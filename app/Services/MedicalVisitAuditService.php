<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\HospitalPatientAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Access scope and append-only event writer for a specific scheduled visit.
 * The appointment, not the patient alone, is the unit of telemedicine logging.
 */
class MedicalVisitAuditService
{
    public function visibleAppointments($viewer, Patient $patient, HospitalPatientAccess $access): Builder
    {
        $query = RegistryRecord::query()->withTrashed()
            ->where('patient_id', $patient->getKey());

        // Keep the same clinic/doctor visibility rules as MedicalCardController.
        if (!$viewer || !$access->isDebugAdmin($viewer)) {
            $doctor = $viewer?->doctor()->first();
            if ($doctor?->clinic_id) {
                $query->where('clinic_id', $doctor->clinic_id);
            } elseif ($doctor) {
                $query->where('doctor_id', $doctor->getKey());
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    /**
     * Data is sanitized by the caller. This method intentionally never updates
     * previous audit rows, so corrections remain traceable.
     */
    public function appendEvent(
        int $appointmentId,
        int $patientId,
        string $type,
        string $source,
        ?string $role = null,
        ?int $actorUserId = null,
        ?string $roomId = null,
        array $details = [],
        ?\DateTimeInterface $occurredAt = null,
        ?string $eventUid = null
    ): void {
        DB::table('medical_visit_events')->insert([
            'event_uid' => $eventUid ?? (string) Str::uuid(),
            'registry_record_id' => $appointmentId,
            'patient_id' => $patientId,
            'room_id' => $roomId,
            'event_type' => $type,
            'source' => $source,
            'participant_role' => $role,
            'actor_user_id' => $actorUserId,
            'details' => $details !== [] ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null,
            'occurred_at' => $occurredAt ?? now(),
            'created_at' => now(),
        ]);
    }
}
