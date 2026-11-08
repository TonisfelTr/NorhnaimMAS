<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClinicBuildingWorkSchedule;
use App\Models\ClinicCabinet;
use App\Models\ClinicScheduleException;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\EmployeeVacation;
use App\Models\EmployeeWorkSchedule;
use App\Support\DoctorAppointmentIntervals as Intervals;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Reads the existing weekly schedules and dated exceptions; creates no second schedule.
 * Availability means working time only. Existing booking-conflict checks must remain.
 * No concurrency guarantee is made: a subsequent schedule edit can change availability.
 */
final class DoctorAppointmentAvailability
{
    /**
     * @return array{date:string,timezone:string,now:string,duration_minutes:int,appointment_format:string,
     *   intervals:array<int,array{start:string,end:string}>,slots:array<int,string>,message:?string}
     */
    public function day(
        Doctor $doctor,
        int $clinicId,
        string $date,
        int $durationMinutes,
        string $appointmentFormat = 'in_person'
    ): array {
        $this->validateFormat($appointmentFormat);
        $this->validateDuration($durationMinutes);
        $zone = $this->timezone($clinicId);
        $day = $this->parseDate($date, $zone);
        $now = new DateTimeImmutable('now', $zone);
        [$working, $message] = $this->workingDay($doctor, $clinicId, $day, $appointmentFormat);
        $minimumStart = 0;
        if ($date < $now->format('Y-m-d')) {
            $minimumStart = 1440;
            $message ??= 'На прошедшую дату записаться нельзя.';
        } elseif ($date === $now->format('Y-m-d')) {
            $minimumStart = (int) $now->format('H') * 60 + (int) $now->format('i');
            if ((int) $now->format('s') > 0 || (int) $now->format('u') > 0) {
                $minimumStart++;
            }
        }

        $step = (int) config('doctor_appointment_availability.slot_step_minutes', 15);
        if ($step < 1 || $step > 1440) {
            throw new \LogicException('Некорректный шаг времени в doctor_appointment_availability.slot_step_minutes.');
        }
        $slots = Intervals::slots($working, $durationMinutes, $step, $minimumStart);
        // clinic-telemedicine-timezones:timezone-slot-transitions:v1:begin
        $slots = array_values(array_filter($slots, static function (string $clock) use ($date, $zone, $durationMinutes): bool {
            try {
                $start = \App\Support\ClinicAppointmentTime::parseLocalInTimezone($date . 'T' . $clock, $zone->getName());
                $end = $start->setTimezone(new DateTimeZone('UTC'))
                    ->modify('+' . $durationMinutes . ' minutes')->setTimezone($zone);
                return $start->getOffset() === $end->getOffset()
                    && $start->format('Y-m-d') === $end->format('Y-m-d');
            } catch (ValidationException) {
                return false;
            }
        }));
        // clinic-telemedicine-timezones:timezone-slot-transitions:v1:end
        if ($slots === [] && $message === null) {
            $message = 'Нет предложенных вариантов времени для выбранной длительности. '
                . 'Весь приём должен укладываться в рабочий интервал.';
        }

        return [
            'date' => $date,
            'timezone' => $zone->getName(),
            'now' => $now->format('Y-m-d\TH:i:s'),
            'duration_minutes' => $durationMinutes,
            'appointment_format' => $appointmentFormat,
            'intervals' => array_map(static fn (array $interval): array => [
                'start' => Intervals::formatClock($interval[0]),
                'end' => Intervals::formatClock($interval[1]),
            ], $working),
            'slots' => $slots,
            'message' => $message,
        ];
    }

    public function assert(
        Doctor $doctor,
        int $clinicId,
        DateTimeInterface $start,
        int $durationMinutes,
        string $appointmentFormat = 'in_person'
    ): void {
        $this->validateFormat($appointmentFormat);
        $this->validateDuration($durationMinutes);
        $zone = $this->timezone($clinicId);
        $local = DateTimeImmutable::createFromInterface($start)->setTimezone($zone);
        if ($local->format('s.u') !== '00.000000') {
            $this->reject('Укажите время начала приёма с точностью до минуты.');
        }
        if ($local < new DateTimeImmutable('now', $zone)) {
            $this->reject('Время приёма уже прошло. Выберите будущую дату и время.');
        }
// clinic-telemedicine-timezones:timezone-duration-transition:v1:begin
$end = $local->setTimezone(new DateTimeZone('UTC'))
            ->modify('+' . $durationMinutes . ' minutes')->setTimezone($zone);
        if ($local->getOffset() !== $end->getOffset()) {
            $this->reject('Приём пересекает перевод часов в часовом поясе клиники. Выберите другой интервал.');
        }
// clinic-telemedicine-timezones:timezone-duration-transition:v1:end
        if ($local->format('Y-m-d') !== $end->format('Y-m-d')) {
            $this->reject('Приём должен полностью укладываться в один рабочий день. Ночные смены не поддерживаются.');
        }
        [$working, $message] = $this->workingDay($doctor, $clinicId, $local->setTime(0, 0), $appointmentFormat);
        $minute = (int) $local->format('H') * 60 + (int) $local->format('i');
        if (!Intervals::covers($working, $minute, $durationMinutes)) {
            $hours = implode(', ', array_map(static fn (array $interval): string =>
                Intervals::formatClock($interval[0]) . '–' . Intervals::formatClock($interval[1]), $working));
            $this->reject($message ?? sprintf(
                'Весь приём длительностью %d мин должен укладываться в рабочее время врача: %s.',
                $durationMinutes,
                $hours
            ));
        }
    }

    private function timezone(int $clinicId): DateTimeZone
    {
        return new DateTimeZone(\App\Support\ClinicAppointmentTime::timezone($clinicId));
    }

    private function parseDate(string $date, DateTimeZone $zone): DateTimeImmutable
    {
        if (!preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $date)) {
            $this->reject('Укажите дату приёма в формате ГГГГ-ММ-ДД.');
        }
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $zone);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$parsed || $parsed->format('Y-m-d') !== $date
            || ($errors !== false && ($errors['warning_count'] || $errors['error_count']))) {
            $this->reject('Указана несуществующая дата приёма.');
        }

        return $parsed;
    }

    private function validateDuration(int $duration): void
    {
        if (!Intervals::validDuration($duration)) {
            throw ValidationException::withMessages([
                'appointment_duration_minutes' => 'Укажите длительность приёма от 5 до 480 минут с шагом 5 минут.',
            ]);
        }
    }

    private function validateFormat(string $appointmentFormat): void
    {
        if (!in_array($appointmentFormat, ['in_person', 'telemedicine'], true)) {
            throw ValidationException::withMessages([
                'appointment_format' => 'Выберите очный приём или телемедицину.',
            ]);
        }
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['appointment_datetime' => $message]);
    }

    /** @return array{0:array<int,array{0:int,1:int}>,1:?string} */
    private function workingDay(
        Doctor $doctor,
        int $clinicId,
        DateTimeImmutable $day,
        string $appointmentFormat
    ): array {
        $telemedicine = $appointmentFormat === 'telemedicine';
        if ($clinicId <= 0 || (int) $doctor->clinic_id !== $clinicId || (int) $doctor->id <= 0) {
            return [[], 'Врач не относится к выбранной клинике.'];
        }
        if (!Schema::hasTable('employee_work_schedules')) {
            return [[], 'Расписание сотрудников недоступно. Обратитесь к администратору клиники.'];
        }
        if ((!$telemedicine && !Schema::hasTable((new ClinicCabinet())->getTable()))
            || !Schema::hasTable((new ClinicWorkShedule())->getTable())) {
            return [[], 'Расписание клиники недоступно. Обратитесь к администратору.'];
        }

        $date = $day->format('Y-m-d');
        $weekday = (int) $day->format('N');
        if (Schema::hasTable('employee_vacations')
            && EmployeeVacation::query()->where('clinic_id', $clinicId)
                ->where('employee_type', 'doctor')->where('employee_id', $doctor->id)
                ->where('status', 'approved')->whereDate('date_from', '<=', $date)
                ->whereDate('date_to', '>=', $date)->exists()) {
            return [[], 'В выбранную дату врач находится в утверждённом отпуске. Выберите другого врача или другую дату.'];
        }

        // Choose one cabinet as the existing validator does, adding a stable ID tie-breaker.
        // All active TYPE_DOCTOR cabinets still participate in exception target matching.
        // Telemedicine never inherits physical cabinet/building restrictions, even if
        // this doctor also has an office for their in-person appointments.
        $cabinets = $telemedicine
            ? collect()
            : ClinicCabinet::query()->where('clinic_id', $clinicId)->where('enabled', true)
                ->where(static function ($query) use ($doctor): void {
                    $query->where('doctor_id', $doctor->id)->orWhere('replace_doctor_id', $doctor->id);
                })
                ->orderByRaw('CASE WHEN doctor_id = ? THEN 0 ELSE 1 END', [$doctor->id])
                ->orderBy('id')->get(['id', 'building_id', 'doctor_id', 'replace_doctor_id', 'cabinet_type']);
        $cabinet = $cabinets->first();
        if (!$telemedicine && !$cabinet) {
            return [[], 'Врач не закреплён за активным кабинетом этой клиники.'];
        }

        $weeklyRows = EmployeeWorkSchedule::query()->where('clinic_id', $clinicId)
            ->forDoctor((int) $doctor->id)->where('day_number', $weekday)->where('enabled', true)
            ->get(['id', 'start_time', 'end_time']);
        if ($weeklyRows->count() > 1) {
            return [[], 'В графике врача найдено несколько смен на один день недели. Администратору необходимо проверить расписание.'];
        }
        $doctorWeekly = [];
        foreach ($weeklyRows as $row) {
            $interval = $this->rowInterval($row);
            if ($interval === null) {
                return [[], 'Рабочее время врача на этот день заполнено некорректно. Проверьте начало и окончание смены.'];
            }
            $doctorWeekly[] = $interval;
        }

        $facilityRow = null;
        if ($telemedicine) {
            // Match the current public clinic validator: no clinic rows means no
            // facility-hour restriction. A missing day in an existing week is closed.
            // This never supplies the doctor's own shift; it is intersected below.
            $clinicRows = ClinicWorkShedule::query()->where('clinic_id', $clinicId)
                ->get(['day_number', 'enabled', 'start_time', 'end_time']);
            $facilityRow = $clinicRows->firstWhere('day_number', $weekday);
            $facilityWeekly = $clinicRows->isEmpty() ? [[0, 1440]] : [];
        } else {
            if ($cabinet->building_id && Schema::hasTable('clinic_building_work_schedules')) {
                $facilityRow = ClinicBuildingWorkSchedule::query()->where('clinic_id', $clinicId)
                    ->where('building_id', (int) $cabinet->building_id)->where('day_number', $weekday)->first();
            }
            $facilityRow ??= ClinicWorkShedule::query()->where('clinic_id', $clinicId)
                ->where('day_number', $weekday)->first();
            // Preserve the existing in-person fallback to the weekly editor defaults.
            $facilityWeekly = $facilityRow ? []
                : ($weekday <= 5 ? [[480, 1200]] : ($weekday === 6 ? [[540, 900]] : []));
        }
        if ($facilityRow) {
            if ((bool) $facilityRow->enabled) {
                $interval = $this->rowInterval($facilityRow);
                if ($interval === null) {
                    return [[], $telemedicine
                        ? 'Рабочее время клиники на этот день заполнено некорректно. Обратитесь к администратору.'
                        : 'Рабочее время корпуса на этот день заполнено некорректно. Обратитесь к администратору.'];
                }
                $facilityWeekly[] = $interval;
            }
        }

        $rules = [];
        if (Schema::hasTable('clinic_schedule_exceptions')) {
            $doctorCabinets = $cabinets->filter(static fn ($row): bool =>
                (string) $row->cabinet_type === (string) ClinicCabinet::TYPE_DOCTOR);
            $cabinetIds = $doctorCabinets->pluck('id')->map(static fn ($id): int => (int) $id)->all();
            $buildingIds = $doctorCabinets->pluck('building_id')->filter()
                ->map(static fn ($id): int => (int) $id)->unique()->values()->all();
            $exceptionRows = ClinicScheduleException::query()->where('clinic_id', $clinicId)
                ->whereDate('date_from', '<=', $date)->whereDate('date_to', '>=', $date)->orderBy('id')->get();
            foreach ($exceptionRows as $rule) {
                $quarantine = (string) $rule->type === 'quarantine';
                $scope = $quarantine ? 'clinic' : (string) $rule->scope;
                if (!in_array($scope, ['clinic', 'buildings', 'offices', 'doctors'], true)) {
                    return [[], 'У особого периода клиники некорректно задана область действия. Обратитесь к администратору.'];
                }
                $targets = $rule->target_ids ?? [];
                if (is_string($targets)) {
                    $targets = json_decode($targets, true);
                }
                $targets = is_array($targets) ? array_map('intval', $targets) : [];
                $applies = match ($scope) {
                    'clinic' => true,
                    'doctors' => in_array((int) $doctor->id, $targets, true),
                    'offices' => array_intersect($cabinetIds, $targets) !== [],
                    'buildings' => array_intersect($buildingIds, $targets) !== [],
                };
                if (!$applies) {
                    continue;
                }
                $mode = $quarantine ? 'closed' : match ((string) $rule->mode) {
                    'shortened' => 'override',
                    'limited' => 'blocked',
                    default => (string) $rule->mode,
                };
                $interval = $quarantine || ($mode === 'closed' && (bool) $rule->all_day)
                    ? [0, 1440]
                    : $this->rowInterval($rule, 'time_from', 'time_to');
                if (!in_array($mode, ['closed', 'blocked', 'override', 'extra'], true)
                    || $interval === null || (!$quarantine && $mode !== 'closed' && (bool) $rule->all_day)) {
                    return [[], 'Временной интервал особого периода клиники заполнен некорректно. Обратитесь к администратору.'];
                }
                $rules[] = ['id' => (int) $rule->id, 'scope' => $scope, 'mode' => $mode,
                    'start' => $interval[0], 'end' => $interval[1]];
            }
        }

        $working = Intervals::resolve($doctorWeekly, $facilityWeekly, $rules);
        return [$working, $working === []
            ? 'На выбранную дату у врача нет доступных рабочих интервалов с учётом графика клиники и действующих ограничений.'
            : null];
    }

    /** Keep raw TIME strings; Carbon casts must not silently repair or round invalid data. */
    private function rowInterval(object $row, string $startField = 'start_time', string $endField = 'end_time'): ?array
    {
        $start = Intervals::parseClock($row->getRawOriginal($startField));
        $end = Intervals::parseClock($row->getRawOriginal($endField));

        return $start !== null && $end !== null && $start < $end ? [$start, $end] : null;
    }
}
