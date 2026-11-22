<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Notifications\PatientPortalNotification;
use App\Support\ClinicAppointmentTime;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class PatientPortalNotificationService
{
    public function notifyForRecord(
        RegistryRecord $record,
        string $category,
        string $title,
        string $message,
        string $icon = 'bell'
    ): void {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $patient = Patient::query()->with('user')->find((int) $record->patient_id);
        $user = $patient?->user;

        if (!$user || !$user->hasRole('patients')) {
            return;
        }

        $user->notify(new PatientPortalNotification(
            $category,
            $title,
            $message,
            route('patients.history.index', [], false),
            $icon
        ));
    }

    public function appointmentDateLabel(RegistryRecord $record): string
    {
        try {
            $description = ClinicAppointmentTime::describe($record);
            $date = trim((string) ($description['date'] ?? ''));
            $time = trim((string) ($description['time'] ?? ''));

            if ($date !== '' && $time !== '') {
                return $date . ' в ' . $time;
            }
        } catch (Throwable) {
            // A malformed legacy date must not prevent notification delivery.
        }

        return 'Дата и время указаны в истории обращений';
    }
}
