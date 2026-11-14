<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\ClinicalRecordOriginService;

class PatientDiagnosisObserver
{
    public function created(Patient $patient): void
    {
        if ($patient->diagnose_id) {
            app(ClinicalRecordOriginService::class)
                ->captureDiagnosis($patient);
        }
    }

    public function updated(Patient $patient): void
    {
        if ($patient->wasChanged('diagnose_id') && $patient->diagnose_id) {
            app(ClinicalRecordOriginService::class)
                ->captureDiagnosis($patient);
        }
    }
}
