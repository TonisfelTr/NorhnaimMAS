<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Doctor;
use App\Models\EmployeeWorkSchedule;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\ClinicAppointmentTime;
use App\Support\DoctorAppointmentTimezone;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class TelemedicineAvailableSlotService
{
    public function calendar(
        Doctor $doctor,
        Patient $patient,
        int $clinicId,
        string $fromDate,
        int $durationMinutes,
        int $days = 7
    ): array {
        $this->validateDuration($durationMinutes);

        $days = max(1, min(14, $days));
        $clinicTimezone = ClinicAppointmentTime::timezone($clinicId);
        $timezone = DoctorAppointmentTimezone::resolve($doctor);
        $zone = new DateTimeZone($timezone);

        $base = ClinicAppointmentTime::parseLocalInTimezone(
            $fromDate . 'T00:00',
            $timezone
        )->setTime(0, 0);

        $rangeStart = $base->setTimezone(new DateTimeZone('UTC'));
        $rangeEnd = $base
            ->modify('+' . $days . ' days')
            ->setTimezone(new DateTimeZone('UTC'));

        $busy = $this->busyIntervals(
            $doctor,
            $patient,
            $rangeStart,
            $rangeEnd
        );

        $step = (int) config(
            'doctor_appointment_availability.slot_step_minutes',
            15
        );

        if ($step < 1 || $step > 1440) {
            throw new \LogicException(
                'Некорректный шаг свободных слотов.'
            );
        }

        $now = new DateTimeImmutable('now', $zone);
        $result = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $day = $base->modify('+' . $offset . ' days');
            $date = $day->format('Y-m-d');

            $intervals = $this->doctorWorkIntervals(
                $doctor,
                $clinicId,
                $day
            );

            $slots = [];

            foreach ($intervals as [$startMinute, $endMinute]) {
                for (
                    $minute = $startMinute;
                    $minute + $durationMinutes <= $endMinute;
                    $minute += $step
                ) {
                    $clock = $this->formatMinute($minute);

                    $local = ClinicAppointmentTime::parseLocalInTimezone(
                        $date . 'T' . $clock,
                        $timezone
                    );

                    if ($local < $now) {
                        continue;
                    }

                    $startUtc = $local->setTimezone(
                        new DateTimeZone('UTC')
                    );
                    $endUtc = $startUtc->modify(
                        '+' . $durationMinutes . ' minutes'
                    );

                    if (
                        $this->overlapsAny(
                            $startUtc,
                            $endUtc,
                            $busy
                        )
                    ) {
                        continue;
                    }

                    $slots[] = [
                        'time' => $clock,
                        'local_value' => $date . 'T' . $clock,
                        'starts_at_utc' => $startUtc->format(
                            DateTimeInterface::ATOM
                        ),
                    ];
                }
            }

            $message = null;

            if ($intervals === []) {
                $message =
                    'В этот день у врача нет рабочей смены '
                    . 'в расписании.';
            } elseif ($slots === []) {
                $message =
                    'Рабочая смена есть, но свободных окон '
                    . 'для выбранной длительности уже нет.';
            }

            $result[] = [
                'date' => $date,
                'intervals' => array_map(
                    fn (array $interval): array => [
                        'start' => $this->formatMinute($interval[0]),
                        'end' => $this->formatMinute($interval[1]),
                    ],
                    $intervals
                ),
                'message' => $message,
                'slots' => $slots,
            ];
        }

        return [
            'timezone' => $timezone,
            'doctor_timezone' => $timezone,
            'doctor_timezone_explicit' =>
                DoctorAppointmentTimezone::explicit($doctor),
            'clinic_timezone' => $clinicTimezone,
            'duration_minutes' => $durationMinutes,
            'days' => $result,
        ];
    }

    public function assertFree(
        Doctor $doctor,
        Patient $patient,
        DateTimeInterface $start,
        int $durationMinutes
    ): void {
        $this->validateDuration($durationMinutes);

        $clinicId = (int) ($doctor->clinic_id ?? 0);

        if ($clinicId <= 0) {
            throw ValidationException::withMessages([
                'telemed_starts_at' =>
                    'Не удалось определить клинику врача.',
            ]);
        }

        $timezone = DoctorAppointmentTimezone::resolve($doctor);
        $zone = new DateTimeZone($timezone);

        $local = DateTimeImmutable::createFromInterface($start)
            ->setTimezone($zone);

        if ($local < new DateTimeImmutable('now', $zone)) {
            throw ValidationException::withMessages([
                'telemed_starts_at' =>
                    'Выберите будущее время записи.',
            ]);
        }

        $intervals = $this->doctorWorkIntervals(
            $doctor,
            $clinicId,
            $local->setTime(0, 0)
        );

        $startMinute =
            ((int) $local->format('H') * 60)
            + (int) $local->format('i');

        $insideShift = false;

        foreach ($intervals as [$shiftStart, $shiftEnd]) {
            if (
                $startMinute >= $shiftStart
                && $startMinute + $durationMinutes <= $shiftEnd
            ) {
                $insideShift = true;
                break;
            }
        }

        if (!$insideShift) {
            $label = $intervals === []
                ? 'В этот день у врача нет рабочей смены.'
                : 'Запись должна полностью укладываться '
                    . 'в рабочую смену врача: '
                    . implode(
                        ', ',
                        array_map(
                            fn (array $interval): string =>
                                $this->formatMinute($interval[0])
                                . '–'
                                . $this->formatMinute($interval[1]),
                            $intervals
                        )
                    )
                    . '.';

            throw ValidationException::withMessages([
                'telemed_starts_at' => $label,
            ]);
        }

        $startUtc = $local->setTimezone(new DateTimeZone('UTC'));
        $endUtc = $startUtc->modify(
            '+' . $durationMinutes . ' minutes'
        );

        $busy = $this->busyIntervals(
            $doctor,
            $patient,
            $startUtc->modify('-1 day'),
            $endUtc->modify('+1 day')
        );

        if ($this->overlapsAny($startUtc, $endUtc, $busy)) {
            throw ValidationException::withMessages([
                'telemed_starts_at' =>
                    'Выбранное время уже занято другой записью '
                    . 'врача или пациента.',
            ]);
        }
    }

    /**
     * Источник истины для телемедицины:
     * рабочие смены врача из employee_work_schedules.
     *
     * @return array<int,array{0:int,1:int}>
     */
    private function doctorWorkIntervals(
        Doctor $doctor,
        int $clinicId,
        DateTimeImmutable $day
    ): array {
        if (
            !Schema::hasTable('employee_work_schedules')
            || $clinicId <= 0
            || (int) $doctor->clinic_id !== $clinicId
        ) {
            return [];
        }

        $rows = EmployeeWorkSchedule::query()
            ->where('clinic_id', $clinicId)
            ->where('employee_type', 'doctor')
            ->where('employee_id', $doctor->getKey())
            ->where('day_number', (int) $day->format('N'))
            ->where('enabled', true)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->orderBy('start_time')
            ->get();

        $intervals = [];

        foreach ($rows as $row) {
            $start = $this->clockMinute(
                $row->getRawOriginal('start_time')
                    ?? $row->start_time
            );
            $end = $this->clockMinute(
                $row->getRawOriginal('end_time')
                    ?? $row->end_time
            );

            if (
                $start === null
                || $end === null
                || $start >= $end
            ) {
                continue;
            }

            $intervals[] = [$start, $end];
        }

        if ($intervals === []) {
            return [];
        }

        usort(
            $intervals,
            static fn (array $a, array $b): int =>
                $a[0] <=> $b[0]
        );

        $merged = [];

        foreach ($intervals as $interval) {
            if ($merged === []) {
                $merged[] = $interval;
                continue;
            }

            $lastIndex = count($merged) - 1;

            if ($interval[0] <= $merged[$lastIndex][1]) {
                $merged[$lastIndex][1] = max(
                    $merged[$lastIndex][1],
                    $interval[1]
                );
                continue;
            }

            $merged[] = $interval;
        }

        return $merged;
    }

    private function validateDuration(int $duration): void
    {
        if (
            $duration < 5
            || $duration > 480
            || $duration % 5 !== 0
        ) {
            throw ValidationException::withMessages([
                'telemed_duration_minutes' =>
                    'Укажите длительность от 5 до 480 минут '
                    . 'с шагом 5 минут.',
            ]);
        }
    }

    private function clockMinute(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if (
            !preg_match(
                '/\A([01][0-9]|2[0-3]):([0-5][0-9])'
                . '(?::[0-5][0-9])?\z/',
                $value,
                $match
            )
        ) {
            return null;
        }

        return ((int) $match[1] * 60) + (int) $match[2];
    }

    private function formatMinute(int $minute): string
    {
        return sprintf(
            '%02d:%02d',
            intdiv($minute, 60),
            $minute % 60
        );
    }

    /**
     * @return array<int,array{
     *   start:DateTimeImmutable,
     *   end:DateTimeImmutable,
     *   id:int
     * }>
     */
    private function busyIntervals(
        Doctor $doctor,
        Patient $patient,
        DateTimeImmutable $rangeStartUtc,
        DateTimeImmutable $rangeEndUtc
    ): array {
        $columns = [
            'id',
            'doctor_id',
            'patient_id',
            'for_datetime',
        ];

        if (
            Schema::hasColumn(
                'registry_records',
                'appointment_duration_minutes'
            )
        ) {
            $columns[] = 'appointment_duration_minutes';
        }

        if (
            Schema::hasColumn(
                'registry_records',
                'appointment_starts_at_utc'
            )
        ) {
            $columns[] = 'appointment_starts_at_utc';
        }

        $query = RegistryRecord::query()
            ->whereNull('deleted_at')
            ->whereNotNull('for_datetime')
            ->where(
                static function (Builder $scope) use (
                    $doctor,
                    $patient
                ): void {
                    $scope
                        ->where(
                            'doctor_id',
                            $doctor->getKey()
                        )
                        ->orWhere(
                            'patient_id',
                            $patient->getKey()
                        );
                }
            );

        $wideFrom = $rangeStartUtc->modify('-2 days');
        $wideUntil = $rangeEndUtc->modify('+2 days');

        $driver = $query
            ->getModel()
            ->getConnection()
            ->getDriverName();

        $format = $driver === 'pgsql'
            ? 'Y-m-d H:i:sP'
            : 'Y-m-d H:i:s';

        $records = $query
            ->where(
                'for_datetime',
                '>=',
                $wideFrom->format($format)
            )
            ->where(
                'for_datetime',
                '<',
                $wideUntil->format($format)
            )
            ->get($columns);

        $defaultDuration = (int) config(
            'doctor_appointment_availability'
            . '.default_duration_minutes',
            30
        );

        $busy = [];

        foreach ($records as $record) {
            $start = ClinicAppointmentTime::startInstant($record);

            if ($start === null) {
                $raw = $record->getRawOriginal('for_datetime')
                    ?? $record->getAttribute('for_datetime');

                try {
                    if ($raw instanceof DateTimeInterface) {
                        $start = DateTimeImmutable::createFromInterface(
                            $raw
                        )->setTimezone(new DateTimeZone('UTC'));
                    } elseif (
                        is_string($raw)
                        && trim($raw) !== ''
                    ) {
                        $start = (
                            new DateTimeImmutable($raw)
                        )->setTimezone(new DateTimeZone('UTC'));
                    }
                } catch (\Throwable) {
                    $start = null;
                }
            }

            if (!$start) {
                continue;
            }

            $duration = (int) (
                $record->getAttribute(
                    'appointment_duration_minutes'
                )
                ?: $defaultDuration
            );

            if ($duration < 5 || $duration > 480) {
                $duration = $defaultDuration;
            }

            $end = $start->modify(
                '+' . $duration . ' minutes'
            );

            if (
                $end <= $rangeStartUtc
                || $start >= $rangeEndUtc
            ) {
                continue;
            }

            $busy[] = [
                'start' => $start,
                'end' => $end,
                'id' => (int) $record->getKey(),
            ];
        }

        return $busy;
    }

    private function overlapsAny(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        array $busy
    ): bool {
        foreach ($busy as $period) {
            if (
                $start < $period['end']
                && $end > $period['start']
            ) {
                return true;
            }
        }

        return false;
    }
}
