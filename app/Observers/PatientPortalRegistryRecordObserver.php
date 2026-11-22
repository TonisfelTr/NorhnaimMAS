<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\RegistryRecord;
use App\Services\PatientPortalNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Patient-facing notifications only.
 * The existing RegistryRecordObserver remains responsible for PatientClinic membership.
 */
final class PatientPortalRegistryRecordObserver implements ShouldHandleEventsAfterCommit
{
    public function created(RegistryRecord $record): void
    {
        $service = app(PatientPortalNotificationService::class);
        $service->notifyForRecord(
            $record,
            'appointment',
            'Запись создана',
            'Ваша запись добавлена. Дата и время: ' . $service->appointmentDateLabel($record) . '.',
            'calendar-check'
        );
    }

    public function updated(RegistryRecord $record): void
    {
        if (!$record->wasChanged([
            'for_datetime',
            'appointment_starts_at_utc',
            'appointment_timezone',
            'appointment_format',
            'doctor_id',
            'clinic_id',
        ])) {
            return;
        }

        app(PatientPortalNotificationService::class)->notifyForRecord(
            $record,
            'appointment',
            'Запись изменена',
            'Изменились параметры вашей записи. Проверьте дату, время и врача в истории обращений.',
            'calendar-event'
        );
    }

    public function deleted(RegistryRecord $record): void
    {
        if (!$record->trashed()) {
            return;
        }

        app(PatientPortalNotificationService::class)->notifyForRecord(
            $record,
            'appointment',
            'Запись отменена',
            'Ваша запись отменена. Если у вас есть вопросы, обратитесь в клинику.',
            'calendar-x'
        );
    }

    public function restored(RegistryRecord $record): void
    {
        app(PatientPortalNotificationService::class)->notifyForRecord(
            $record,
            'appointment',
            'Запись восстановлена',
            'Ваша запись восстановлена. Проверьте дату и время в истории обращений.',
            'calendar-check'
        );
    }
}
