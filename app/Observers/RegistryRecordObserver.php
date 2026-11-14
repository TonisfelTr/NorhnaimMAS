<?php

namespace App\Observers;

use App\Models\PatientClinic;
use App\Models\RegistryRecord;

class RegistryRecordObserver
{
    public function saved(RegistryRecord $record): void
    {
        $patientId = (int) $record->patient_id;
        $clinicId = (int) $record->clinic_id;

        if ($patientId <= 0 || $clinicId <= 0) {
            return;
        }

        $membership = PatientClinic::query()->firstOrNew([
            'patient_id' => $patientId,
            'clinic_id' => $clinicId,
        ]);

        if (!$membership->exists) {
            $membership->attached_at = now();
            $membership->is_primary = false;
        }

        $membership->is_active = true;
        $membership->detached_at = null;
        $membership->save();
    }
}
