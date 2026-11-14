<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\ClinicAppointmentTime;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class PatientMonthlyAppointmentLimit
{
    public const LIMIT = 4;

    public function usageForDate(
        Patient $patient,
        Clinic $clinic,
        string $clinicDate
    ): array {
        $timezone = ClinicAppointmentTime::timezone($clinic);
        $zone = new DateTimeZone($timezone);

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $clinicDate, $zone);
        if (!$date || $date->format('Y-m-d') !== $clinicDate) {
            throw ValidationException::withMessages([
                'appointment_datetime' => 'Не удалось определить месяц выбранного приёма.',
            ]);
        }

        $monthStart = $date->modify('first day of this month')->setTime(0, 0, 0);
        $nextMonthStart = $monthStart->modify('+1 month');

        $used = $this->countForMonth($patient, $monthStart, $nextMonthStart);
        $remaining = max(0, self::LIMIT - $used);
        $month = $monthStart->format('Y-m');
        $monthLabel = CarbonImmutable::instance($monthStart)
            ->locale('ru')
            ->translatedFormat('F Y');

        return [
            'month' => $month,
            'month_label' => mb_convert_case($monthLabel, MB_CASE_TITLE, 'UTF-8'),
            'used' => $used,
            'limit' => self::LIMIT,
            'remaining' => $remaining,
            'message' => $remaining <= 0
                ? 'Лимит онлайн-записи на ' . $monthLabel
                    . ' исчерпан: ' . $used . ' из ' . self::LIMIT . '.'
                : 'На ' . $monthLabel . ' использовано ' . $used
                    . ' из ' . self::LIMIT . ' записей. Осталось: ' . $remaining . '.',
            'timezone' => $timezone,
        ];
    }

    public function assertCanBook(
        Patient $patient,
        Clinic $clinic,
        DateTimeInterface $appointmentStart
    ): array {
        $timezone = new DateTimeZone(ClinicAppointmentTime::timezone($clinic));
        $clinicLocalStart = DateTimeImmutable::createFromInterface($appointmentStart)
            ->setTimezone($timezone);

        $usage = $this->usageForDate(
            $patient,
            $clinic,
            $clinicLocalStart->format('Y-m-d')
        );

        if ($usage['remaining'] <= 0) {
            throw ValidationException::withMessages([
                'appointment_datetime' => $usage['message']
                    . ' Новая запись будет доступна со следующего месяца.',
            ]);
        }

        return $usage;
    }

    private function countForMonth(
        Patient $patient,
        DateTimeImmutable $monthStart,
        DateTimeImmutable $nextMonthStart
    ): int {
        $query = RegistryRecord::query()
            ->where('patient_id', (int) $patient->getKey());

        if (Schema::hasColumn('registry_records', 'appointment_starts_at_utc')) {
            $driver = $query->getModel()->getConnection()->getDriverName();
            $utcFormat = $driver === 'pgsql' ? 'Y-m-d H:i:sP' : 'Y-m-d H:i:s';

            $utcStart = $monthStart->setTimezone(new DateTimeZone('UTC'))->format($utcFormat);
            $utcEnd = $nextMonthStart->setTimezone(new DateTimeZone('UTC'))->format($utcFormat);

            // Old records have no reliable UTC snapshot. Count them by the
            // saved calendar date; do not invent a timezone for historical rows.
            $legacyStart = $monthStart->format('Y-m-d H:i:s');
            $legacyEnd = $nextMonthStart->format('Y-m-d H:i:s');

            $query->where(function (Builder $scope) use (
                $utcStart,
                $utcEnd,
                $legacyStart,
                $legacyEnd
            ): void {
                $scope
                    ->where(function (Builder $known) use ($utcStart, $utcEnd): void {
                        $known->whereNotNull('appointment_starts_at_utc')
                            ->where('appointment_starts_at_utc', '>=', $utcStart)
                            ->where('appointment_starts_at_utc', '<', $utcEnd);
                    })
                    ->orWhere(function (Builder $legacy) use ($legacyStart, $legacyEnd): void {
                        $legacy->whereNull('appointment_starts_at_utc')
                            ->where('for_datetime', '>=', $legacyStart)
                            ->where('for_datetime', '<', $legacyEnd);
                    });
            });
        } else {
            $query->where('for_datetime', '>=', $monthStart->format('Y-m-d H:i:s'))
                ->where('for_datetime', '<', $nextMonthStart->format('Y-m-d H:i:s'));
        }

        // RegistryRecord uses SoftDeletes: cancelled records do not consume the limit.
        return (int) $query->count();
    }
}
