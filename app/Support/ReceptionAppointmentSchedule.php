<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\RegistryRecord;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/** Read-only presentation of existing registry_records; never creates another booking. */
final class ReceptionAppointmentSchedule
{
    public function filters(Request $request): array
    {
        $user = $request->user();
        $doctor = $user && method_exists($user, 'doctor') ? $user->doctor()->first() : null;
        $nurse = $user && method_exists($user, 'nurse') ? $user->nurse()->first() : null;
        $clinicId = $doctor?->clinic_id ?? $nurse?->clinic_id;
        $zone = new DateTimeZone(ClinicAppointmentTime::timezone($clinicId === null ? null : (int) $clinicId));
        try {
            return self::normalizeFilters($request->only([
                'reception_period', 'reception_date', 'reception_format',
            ]), new DateTimeImmutable('now', $zone));
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }
    }

    public static function normalizeFilters(array $input, DateTimeImmutable $now): array
    {
        $periods = [
            'upcoming' => 'Сегодня и будущие', 'today' => 'Сегодня',
            'tomorrow' => 'Завтра', 'all' => 'Все даты',
        ];
        $formats = ['all' => 'Все форматы', 'telemedicine' => 'Телемедицина', 'in_person' => 'Очный приём'];
        foreach (['reception_period', 'reception_date', 'reception_format'] as $key) {
            if (isset($input[$key]) && !is_string($input[$key])) {
                throw new InvalidArgumentException('Неверный формат фильтра расписания.');
            }
        }
        $period = $input['reception_period'] ?? 'upcoming';
        $format = $input['reception_format'] ?? 'all';
        $date = trim($input['reception_date'] ?? '');
        if (!isset($periods[$period]) || !isset($formats[$format])) {
            throw new InvalidArgumentException('Неизвестный период или формат приёма.');
        }
        $start = null;
        $end = null;
        if ($date !== '') {
            $start = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $now->getTimezone());
            if (!$start || $start->format('Y-m-d') !== $date) {
                throw new InvalidArgumentException('Дата приёма должна быть действительной датой в формате ГГГГ-ММ-ДД.');
            }
            $end = $start->modify('+1 day');
        } elseif ($period !== 'all') {
            $start = $now->setTime(0, 0);
            if ($period === 'tomorrow') $start = $start->modify('+1 day');
            if ($period !== 'upcoming') $end = $start->modify('+1 day');
        }
        return [
            'period' => $period, 'date' => $date, 'format' => $format,
// clinic-telemedicine-timezones:timezone-schedule-bounds:v1:begin
'from' => $start?->format('Y-m-d H:i:s'), 'until' => $end?->format('Y-m-d H:i:s'),
            'from_utc' => $start?->setTimezone(new DateTimeZone('UTC'))->format(DateTimeInterface::ATOM),
            'until_utc' => $end?->setTimezone(new DateTimeZone('UTC'))->format(DateTimeInterface::ATOM),
// clinic-telemedicine-timezones:timezone-schedule-bounds:v1:end
            'direction' => $period === 'all' && $date === '' ? 'desc' : 'asc',
            'timezone' => $now->getTimezone()->getName(),
            'now_sql' => $now->format('Y-m-d H:i:s'),
            'period_options' => array_map(static fn ($value, $label) => [
                'value' => $value, 'label' => $label, 'selected' => $period === $value,
            ], array_keys($periods), array_values($periods)),
            'format_options' => array_map(static fn ($value, $label) => [
                'value' => $value, 'label' => $label, 'selected' => $format === $value,
            ], array_keys($formats), array_values($formats)),
        ];
    }

    /** Adds date/format constraints to, not instead of, existing access scopes. */
    public function applyFilters(Builder $query, array $filters): void
    {
        $query->whereNull('registry_records.deleted_at');

        if ($filters['from'] !== null || $filters['until'] !== null) {
            $fromUtc = $filters['from_utc'] === null ? null
                : ClinicAppointmentTime::queryTimestamp(
                    $query,
                    new DateTimeImmutable($filters['from_utc'])
                );
            $untilUtc = $filters['until_utc'] === null ? null
                : ClinicAppointmentTime::queryTimestamp(
                    $query,
                    new DateTimeImmutable($filters['until_utc'])
                );

            if ($query->getModel()->getConnection()->getDriverName() === 'pgsql') {
                if ($fromUtc !== null) {
                    $query->whereRaw(
                        'COALESCE(registry_records.appointment_starts_at_utc, registry_records.for_datetime) >= ?',
                        [$fromUtc]
                    );
                }

                if ($untilUtc !== null) {
                    $query->whereRaw(
                        'COALESCE(registry_records.appointment_starts_at_utc, registry_records.for_datetime) < ?',
                        [$untilUtc]
                    );
                }
            } else {
                $query->where(
                    static function (Builder $dates) use (
                        $filters,
                        $fromUtc,
                        $untilUtc
                    ): void {
                        $dates->where(
                            static function (Builder $known) use (
                                $fromUtc,
                                $untilUtc
                            ): void {
                                $known->whereNotNull('appointment_starts_at_utc');

                                if ($fromUtc !== null) {
                                    $known->where(
                                        'appointment_starts_at_utc',
                                        '>=',
                                        $fromUtc
                                    );
                                }

                                if ($untilUtc !== null) {
                                    $known->where(
                                        'appointment_starts_at_utc',
                                        '<',
                                        $untilUtc
                                    );
                                }
                            }
                        )->orWhere(
                            static function (Builder $legacy) use (
                                $filters
                            ): void {
                                $legacy->whereNull('appointment_starts_at_utc');

                                if ($filters['from'] !== null) {
                                    $legacy->where(
                                        'for_datetime',
                                        '>=',
                                        $filters['from']
                                    );
                                }

                                if ($filters['until'] !== null) {
                                    $legacy->where(
                                        'for_datetime',
                                        '<',
                                        $filters['until']
                                    );
                                }
                            }
                        );
                    }
                );
            }
        }

        if ($filters['format'] !== 'all') {
            $query->where('appointment_format', $filters['format']);
        }

        $query->reorder();
        ClinicAppointmentTime::orderByStart(
            $query,
            $filters['direction']
        );
    }

    /** At most two batched status reads, restricted to already-visible page record IDs. */
    public function decorateRows(array $rows, Collection $records): array
    {
        $byId = $records->keyBy('id');
        $ids = $records->pluck('id')->all();
        $confirmations = [];
        $encounters = [];
        if ($ids !== [] && Schema::hasTable('medical_visit_confirmations')) {
            foreach (DB::table('medical_visit_confirmations')->whereIn('registry_record_id', $ids)
                ->get(['registry_record_id', 'outcome']) as $row) {
                $confirmations[(int) $row->registry_record_id][] = (string) $row->outcome;
            }
        }
        if ($ids !== [] && Schema::hasTable('medical_encounters')
            && Schema::hasColumn('medical_encounters', 'consultation_status')) {
            foreach (DB::table('medical_encounters')->whereIn('registry_record_id', $ids)
                ->get(['registry_record_id', 'consultation_status']) as $row) {
                $encounters[(int) $row->registry_record_id][] = (string) $row->consultation_status;
            }
        }
        $zone = new DateTimeZone((string) config('app.timezone', 'UTC'));
        $now = new DateTimeImmutable('now', $zone);
        // clinic-telemedicine-timezones:timezone-schedule-zone-cache:v1:begin
        $clinicTimezones = [];
        // clinic-telemedicine-timezones:timezone-schedule-zone-cache:v1:end
        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            $record = $byId->get($id);
            if (!$record instanceof RegistryRecord) continue;
// clinic-telemedicine-timezones:timezone-schedule-rows:v1:begin
$clinicKey = (int) ($record->clinic_id ?? 0);
            $clinicZone = $clinicTimezones[$clinicKey] ??= ClinicAppointmentTime::timezone($record->clinic_id === null ? null : $clinicKey);
            $description = ClinicAppointmentTime::describe($record, $clinicZone);
            $start = ClinicAppointmentTime::startInstant($record)?->setTimezone(new DateTimeZone($clinicZone));
            $row['schedule'] = self::describe($start,
                (string) $record->getAttribute('appointment_format'),
                $confirmations[$id] ?? [], $encounters[$id] ?? [], $now->setTimezone(new DateTimeZone($clinicZone)));
            $row['schedule'] = array_replace($row['schedule'], $description);
            if ($description['legacy'] && $description['date'] !== null && $row['schedule']['state'] === 'unknown') {
                $row['schedule']['state_label'] = 'Уточните часовой пояс';
                $row['schedule']['state_class'] = 'text-bg-warning';
            }
// clinic-telemedicine-timezones:timezone-schedule-rows:v1:end
            // The main doctor column retains the patient's CURRENT attending doctor.
            // An explicitly different appointment doctor is labelled separately.
            $doctor = $record->relationLoaded('doctor') ? $record->getRelation('doctor') : null;
            $patient = $record->relationLoaded('patient') ? $record->getRelation('patient') : null;
            $differentDoctor = $patient && (int) $record->doctor_id !== (int) $patient->doctor_id;
            $row['schedule']['appointment_doctor'] = $differentDoctor
                ? ($doctor ? trim(implode(' ', array_filter([
                    $doctor->surname, $doctor->name, $doctor->patronym,
                ]))) : 'Врач #' . (int) $record->doctor_id) : '';
            $row['schedule']['journal_url'] = ($row['schedule']['is_telemedicine'] && !empty($row['medical_card_url']))
                ? $row['medical_card_url'] . (str_contains($row['medical_card_url'], '?') ? '&' : '?')
                    . 'tab=telemedicine-log' : null;
        }
        unset($row);
        return $rows;
    }

    public static function parseStart(mixed $raw, DateTimeZone $zone): ?DateTimeImmutable
    {
        if ($raw instanceof DateTimeInterface) return DateTimeImmutable::createFromInterface($raw)->setTimezone($zone);
        if (!is_string($raw) || trim($raw) === '') return null;
        // Database timestamps, not free-form relative dates or a fallback to "now".
        if (!preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}.*)?$/D', trim($raw))) return null;
        try {
            $date = new DateTimeImmutable($raw, $zone);
            $errors = DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) return null;
            return $date->setTimezone($zone);
        } catch (\Exception) {
            return null;
        }
    }

    /** Pure presentation rule: elapsed clock time never proves a clinical outcome. */
    public static function describe(?DateTimeImmutable $start, string $format, array $confirmations,
        array $consultations, DateTimeImmutable $now): array
    {
        $held = in_array('held', $confirmations, true) || in_array('completed', $consultations, true);
        $notHeld = in_array('not_held', $confirmations, true) || in_array('not_held', $consultations, true);
        $inProgress = in_array('in_progress', $consultations, true);
        if (($held && $notHeld) || ($inProgress && $notHeld)) {
            [$state, $label, $style] = ['conflict', 'Проверьте результат', 'text-bg-warning'];
        } elseif ($held) {
            [$state, $label, $style] = ['held', 'Состоялся', 'text-bg-success'];
        } elseif ($notHeld) {
            [$state, $label, $style] = ['not_held', 'Не состоялся', 'text-bg-secondary'];
        } elseif ($inProgress) {
            [$state, $label, $style] = ['in_progress', 'В процессе', 'text-bg-primary'];
        } elseif ($start === null) {
            [$state, $label, $style] = ['unknown', 'Время не указано', 'text-bg-secondary'];
        } elseif ($start > $now) {
            [$state, $label, $style] = ['planned', 'Запланировано', 'text-bg-info'];
        } else {
            [$state, $label, $style] = ['unconfirmed', 'Результат не отмечен', 'text-bg-warning'];
        }
        $day = $start?->format('Y-m-d');
        return [
            'date' => $start?->format('d.m.Y'), 'time' => $start?->format('H:i'),
            'iso' => $start?->format(DateTimeInterface::ATOM),
            'relative_day' => $day === $now->format('Y-m-d') ? 'Сегодня'
                : ($day === $now->modify('+1 day')->format('Y-m-d') ? 'Завтра' : ''),
            'state' => $state, 'state_label' => $label, 'state_class' => $style,
            'is_telemedicine' => $format === 'telemedicine',
            'format_label' => match ($format) {
                'telemedicine' => 'Телемедицина', 'in_person' => 'Очный приём', default => 'Формат не указан',
            },
            'format_icon' => $format === 'telemedicine' ? 'bi-camera-video' : 'bi-calendar-event',
        ];
    }
}
