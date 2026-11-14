<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\ClinicAppointmentTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class RegistryAppointmentConflictService
{
    public function filterAvailableSlots(
        Doctor $doctor,
        Patient $patient,
        string $date,
        string $timezone,
        array $slots,
        int $durationMinutes
    ): array {
        if ($slots === []) {
            return [];
        }

        $localStart = ClinicAppointmentTime::parseLocalInTimezone($date . 'T00:00', $timezone);
        $nextDate = $localStart->modify('+1 day')->format('Y-m-d');
        $localEnd = ClinicAppointmentTime::parseLocalInTimezone($nextDate . 'T00:00', $timezone);

        $utcStart = $localStart->setTimezone(new DateTimeZone('UTC'));
        $utcEnd = $localEnd->setTimezone(new DateTimeZone('UTC'));
        $busy = $this->busyIntervals($doctor, $patient, $utcStart, $utcEnd);

        $available = [];
        foreach ($slots as $clock) {
            $clock = (string) $clock;
            if (!preg_match('/\A\d{2}:\d{2}\z/', $clock)) {
                continue;
            }

            try {
                $start = ClinicAppointmentTime::parseLocalInTimezone(
                    $date . 'T' . $clock,
                    $timezone
                )->setTimezone(new DateTimeZone('UTC'));
            } catch (ValidationException) {
                continue;
            }

            $end = $start->modify('+' . $durationMinutes . ' minutes');
            if (!$this->overlapsAny($start, $end, $busy)) {
                $available[] = $clock;
            }
        }

        return $available;
    }

    public function assertNoOverlap(
        Doctor $doctor,
        Patient $patient,
        DateTimeInterface $start,
        int $durationMinutes
    ): void {
        $startUtc = DateTimeImmutable::createFromInterface($start)
            ->setTimezone(new DateTimeZone('UTC'));
        $endUtc = $startUtc->modify('+' . $durationMinutes . ' minutes');
        $busy = $this->busyIntervals($doctor, $patient, $startUtc, $endUtc);

        if ($this->overlapsAny($startUtc, $endUtc, $busy)) {
            throw ValidationException::withMessages([
                'appointment_datetime' => 'Выбранное время уже занято другой записью врача или пациента. Обновите календарь и выберите другой интервал.',
            ]);
        }
    }

    private function busyIntervals(
        Doctor $doctor,
        Patient $patient,
        DateTimeImmutable $rangeStartUtc,
        DateTimeImmutable $rangeEndUtc
    ): array {
        $columns = ['id', 'doctor_id', 'patient_id', 'for_datetime'];

        if (Schema::hasColumn('registry_records', 'appointment_duration_minutes')) {
            $columns[] = 'appointment_duration_minutes';
        }
        if (Schema::hasColumn('registry_records', 'appointment_starts_at_utc')) {
            $columns[] = 'appointment_starts_at_utc';
        }

        $query = RegistryRecord::query()
            ->whereNull('deleted_at')
            ->whereNotNull('for_datetime')
            ->where(static function (Builder $scope) use ($doctor, $patient): void {
                $scope->where('doctor_id', (int) $doctor->getKey())
                    ->orWhere('patient_id', (int) $patient->getKey());
            });

        $wideFrom = $rangeStartUtc->modify('-2 days');
        $wideUntil = $rangeEndUtc->modify('+2 days');
        $driver = $query->getModel()->getConnection()->getDriverName();
        $format = $driver === 'pgsql' ? 'Y-m-d H:i:sP' : 'Y-m-d H:i:s';

        $records = $query
            ->where('for_datetime', '>=', $wideFrom->format($format))
            ->where('for_datetime', '<', $wideUntil->format($format))
            ->get($columns);

        $defaultDuration = (int) config('doctor_appointment_availability.default_duration_minutes', 30);
        $busy = [];

        foreach ($records as $record) {
            $recordStart = ClinicAppointmentTime::startInstant($record);

            if ($recordStart === null) {
                $raw = $record->getRawOriginal('for_datetime')
                    ?? $record->getAttribute('for_datetime');

                try {
                    if ($raw instanceof DateTimeInterface) {
                        $recordStart = DateTimeImmutable::createFromInterface($raw)
                            ->setTimezone(new DateTimeZone('UTC'));
                    } elseif (is_string($raw) && trim($raw) !== '') {
                        $recordStart = (new DateTimeImmutable($raw))
                            ->setTimezone(new DateTimeZone('UTC'));
                    }
                } catch (\Throwable) {
                    $recordStart = null;
                }
            }

            if (!$recordStart) {
                continue;
            }

            $duration = (int) (
                $record->getAttribute('appointment_duration_minutes')
                ?: $defaultDuration
            );
            if ($duration < 5 || $duration > 480) {
                $duration = $defaultDuration;
            }

            $recordEnd = $recordStart->modify('+' . $duration . ' minutes');
            if ($recordEnd <= $rangeStartUtc || $recordStart >= $rangeEndUtc) {
                continue;
            }

            $busy[] = ['start' => $recordStart, 'end' => $recordEnd];
        }

        return $busy;
    }

    private function overlapsAny(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        array $busy
    ): bool {
        foreach ($busy as $period) {
            if ($start < $period['end'] && $end > $period['start']) {
                return true;
            }
        }

        return false;
    }
}
