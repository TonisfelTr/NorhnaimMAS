<?php

namespace App\Observers;

use App\Events\LaboratoryResearchChanged;
use App\Models\Doctor;
use App\Models\LabResearch;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * One notification point for all Eloquent creation paths:
 * LabAssignmentController, AnalysesPanelController and future modules.
 * The event is queued only after the surrounding transaction commits.
 */
final class LabResearchObserver
{
    public function created(LabResearch $research): void
    {
        $this->dispatchAfterCommit($research, 'created');
    }

    public function updated(LabResearch $research): void
    {
        // Rebuilding a view acknowledgement (result_showed_at) does not affect
        // the laboratory queue and should not generate noise.
        if (! $research->wasChanged([
            'status', 'patient_id', 'doctor_id', 'laboratory', 'planned_at',
            'priority', 'parameters', 'sample_type',
        ])) {
            return;
        }

        $previousDoctorId = $research->wasChanged('doctor_id')
            ? (int) $research->getOriginal('doctor_id')
            : null;

        $this->dispatchAfterCommit($research, 'updated', $previousDoctorId);
    }

    public function deleted(LabResearch $research): void
    {
        $this->dispatchAfterCommit($research, 'deleted');
    }

    private function dispatchAfterCommit(
        LabResearch $research,
        string $change,
        ?int $previousDoctorId = null
    ): void {
        // Capture primitives *before* the model state may change again within
        // the transaction. If a direction moves clinics, notify both lists.
        $doctorIds = array_values(array_unique(array_filter([
            (int) $research->doctor_id,
            $previousDoctorId,
        ], static fn (?int $id): bool => $id !== null && $id > 0)));

        if ($doctorIds === []) {
            return;
        }

        $clinicIds = Doctor::query()
            ->whereIn('id', $doctorIds)
            ->whereNotNull('clinic_id')
            ->pluck('clinic_id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($clinicIds === []) {
            return;
        }

        $researchId = (int) $research->getKey();
        $send = static function () use ($clinicIds, $researchId, $change): void {
            foreach ($clinicIds as $clinicId) {
                try {
                    // Queued broadcast; a temporary queue error must not undo
                    // a legitimate medical order already committed to the DB.
                    event(new LaboratoryResearchChanged($clinicId, $researchId, $change));
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($send);
        } else {
            $send();
        }
    }
}
