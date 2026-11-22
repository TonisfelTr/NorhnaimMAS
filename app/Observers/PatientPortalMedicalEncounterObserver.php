<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MedicalEncounter;
use App\Models\RegistryRecord;
use App\Services\PatientPortalNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class PatientPortalMedicalEncounterObserver implements ShouldHandleEventsAfterCommit
{
    public function created(MedicalEncounter $encounter): void
    {
        $this->notifyTerminalOutcome($encounter);
    }

    public function updated(MedicalEncounter $encounter): void
    {
        if (!$encounter->wasChanged('consultation_status')) {
            return;
        }

        $this->notifyTerminalOutcome($encounter);
    }

    private function notifyTerminalOutcome(MedicalEncounter $encounter): void
    {
        $status = (string) $encounter->consultation_status;
        if (!in_array($status, ['completed', 'not_held'], true)) {
            return;
        }

        $record = RegistryRecord::withTrashed()->find((int) $encounter->registry_record_id);
        if (!$record) {
            return;
        }

        if ($status === 'completed') {
            app(PatientPortalNotificationService::class)->notifyForRecord(
                $record,
                'consultation',
                'Консультация завершена',
                'Врач отметил, что консультация состоялась. Доступные сведения можно посмотреть в личном кабинете.',
                'clipboard2-check'
            );
            return;
        }

        app(PatientPortalNotificationService::class)->notifyForRecord(
            $record,
            'consultation',
            'Обновлён статус приёма',
            'Врач зафиксировал, что приём не состоялся. Для уточнения обратитесь в клинику.',
            'info-circle'
        );
    }
}
