<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Clinic;
use App\Models\RegistryRecord;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/** NORHNAIM_VIEWER_TIMEZONE_V1: absolute instants + viewer-local presentation. */
final class ClinicAppointmentTime
{
    public static function timezone(Clinic|int|null $clinic): string
    {
        if (is_int($clinic)) {
            $clinic = Clinic::query()->find($clinic);
        }
        $value = $clinic?->getAttribute('timezone');
        if ($value === null || $value === '') {
            $value = config('app.timezone', 'UTC');
        }
        if (!is_string($value) || !in_array($value, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
            throw new \LogicException('В настройках клиники указан некорректный часовой пояс.');
        }
        return $value;
    }

    /** datetime-local has no offset: resolve it in the authenticated clinic only. */
    public static function parseLocal(string $value, Clinic|int|null $clinic): DateTimeImmutable
    {
        return self::parseLocalInTimezone($value, self::timezone($clinic));
    }

    /** Reuse an already resolved clinic zone when checking many offered slots. */
    public static function parseLocalInTimezone(string $value, string $timezone): DateTimeImmutable
    {
        $zone = new DateTimeZone($timezone);
        $civil = self::minuteValue($value);
        if ($civil === null || (int) substr($civil, 0, 4) < 1000) {
            self::reject('Укажите корректные дату и время приёма с точностью до минуты.');
        }
        $naive = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $civil, new DateTimeZone('UTC'));
        $errors = DateTimeImmutable::getLastErrors();
        if (!$naive || $naive->format('Y-m-d\TH:i') !== $civil
            || ($errors !== false && ($errors['warning_count'] || $errors['error_count']))) {
            self::reject('Укажите действительную календарную дату и время приёма.');
        }
        // A local clock can occur zero or twice during a timezone transition.
        // Never silently normalize a gap or choose one side of an overlap.
        $offsets = [$zone->getOffset($naive)];
        $transitions = $zone->getTransitions($naive->getTimestamp() - 172800, $naive->getTimestamp() + 172800);
        foreach ($transitions ?: [] as $transition) {
            $offsets[] = (int) $transition['offset'];
        }
        $matches = [];
        foreach (array_unique($offsets) as $offset) {
            $candidate = $naive->setTimestamp($naive->getTimestamp() - $offset)->setTimezone($zone);
            if ($candidate->format('Y-m-d\TH:i') === $civil) {
                $matches[$candidate->getTimestamp()] = $candidate;
            }
        }
        if (count($matches) !== 1) {
            self::reject(count($matches) === 0
                ? 'Это время отсутствует в часовом поясе клиники из-за перевода часов. Выберите другое время.'
                : 'Это время встречается дважды в часовом поясе клиники из-за перевода часов. Выберите другое время.');
        }
        return array_values($matches)[0];
    }

    public static function minuteValue(string $value): ?string
    {
        return preg_match('/\A([0-9]{4}-[0-9]{2}-[0-9]{2})[ T]([0-9]{2}:[0-9]{2})(?::00)?\z/', $value, $m)
            ? $m[1] . 'T' . $m[2] : null;
    }

    public static function startInstant(RegistryRecord $record): ?DateTimeImmutable
    {
        // New records have an explicit canonical UTC snapshot.
        $instant = self::instantValue($record->getAttribute('appointment_starts_at_utc'), true);
        if ($instant !== null) {
            return $instant;
        }

        /*
         * PostgreSQL timestamptz already represents an absolute instant.
         * Older Norhnaim rows may not have appointment_starts_at_utc, but their
         * raw for_datetime value carries an offset (+00, +03:00, ...). Treat
         * that value as the instant instead of refusing timezone conversion.
         *
         * A naive legacy string WITHOUT an offset remains ambiguous and is
         * deliberately NOT converted in the browser.
         */
        $raw = $record->getRawOriginal('for_datetime');
        if ($raw === null) {
            $raw = $record->getAttribute('for_datetime');
        }

        return self::instantValue($raw, false);
    }

    private static function instantValue(mixed $raw, bool $assumeUtc): ?DateTimeImmutable
    {
        if ($raw instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($raw)
                ->setTimezone(new DateTimeZone('UTC'));
        }

        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $value = trim($raw);
        $hasOffset = preg_match('/(?:Z|[+-][0-9]{2}(?::?[0-9]{2})?)\z/i', $value) === 1;

        if (!$assumeUtc && !$hasOffset) {
            return null;
        }

        try {
            $date = new DateTimeImmutable(
                $value,
                $assumeUtc ? new DateTimeZone('UTC') : null
            );
            $errors = DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) {
                return null;
            }

            return $date->setTimezone(new DateTimeZone('UTC'));
        } catch (\Exception) {
            return null;
        }
    }

    /** Previous app-zone presentation only. This is not a known instant. */
    public static function legacyLocal(RegistryRecord $record): ?DateTimeImmutable
    {
        $raw = $record->getRawOriginal('for_datetime') ?? $record->getAttribute('for_datetime');
        return ReceptionAppointmentSchedule::parseStart($raw, new DateTimeZone(self::timezone(null)));
    }

    public static function displayDate(RegistryRecord $record, ?string $viewerTimezone = null): ?DateTimeImmutable
    {
        $instant = self::startInstant($record);
        if ($instant === null) {
            return self::legacyLocal($record);
        }
        $zone = $viewerTimezone ?? $record->getAttribute('appointment_timezone');
        if (!is_string($zone) || !in_array($zone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
            $zone = self::timezone($record->clinic_id === null ? null : (int) $record->clinic_id);
        }
        return $instant->setTimezone(new DateTimeZone($zone));
    }

    public static function describe(RegistryRecord $record, ?string $viewerTimezone = null): array
    {
        $instant = self::startInstant($record);
        $local = self::displayDate($record, $viewerTimezone);
        $legacy = $instant === null;
        $clinicTimezone = self::timezone(
            $record->clinic_id === null ? null : (int) $record->clinic_id
        );
        $clinicLocal = $instant?->setTimezone(new DateTimeZone($clinicTimezone));

        return [
            'date' => $local?->format('d.m.Y'),
            'time' => $local?->format('H:i'),
            'datetime_local' => $local?->format('Y-m-d\TH:i'),
            'iso_utc' => $instant?->format(DateTimeInterface::ATOM),
            'iso' => $instant?->format(DateTimeInterface::ATOM),
            'timezone' => $legacy ? null : $local?->getTimezone()->getName(),
            'timezone_label' => $legacy ? 'часовой пояс не определён' : $local?->getTimezone()->getName(),
            'clinic_timezone' => $legacy ? null : $clinicTimezone,
            'clinic_date' => $clinicLocal?->format('d.m.Y'),
            'clinic_time' => $clinicLocal?->format('H:i'),
            'legacy' => $legacy,
        ];
    }

    public static function formContext(?RegistryRecord $record, Clinic|int|null $clinic, array $oldInput = []): array
    {
        $timezone = self::timezone($clinic);
        $description = $record ? self::describe($record, $timezone) : null;
        $original = $description['datetime_local'] ?? '';
        $value = array_key_exists('appointment_datetime', $oldInput) ? $oldInput['appointment_datetime'] : $original;
        return [
            'clinic_timezone' => $timezone,
            'value' => is_string($value) ? $value : '',
            'original_datetime' => $original,
            'legacy' => $record !== null && ($description['legacy'] ?? true),
            'confirmed' => in_array($oldInput['appointment_timezone_confirmed'] ?? false, [true, 1, '1'], true),
            'appointment_time' => $description,
        ];
    }

    /** Assign only; the caller keeps its existing transaction, scope and save. */
public static function stamp(
    RegistryRecord $record,
    DateTimeInterface $start,
    int $clinicId,
    ?string $appointmentTimezone = null
): void
{
    $resolved = trim((string) $appointmentTimezone);

    if ($resolved === '') {
        $resolved = self::timezone(
            $clinicId > 0 ? $clinicId : null
        );
    }

    try {
        $zone = new DateTimeZone($resolved);
    } catch (\Throwable) {
        self::reject('Некорректный часовой пояс записи.');
    }

    $local = DateTimeImmutable::createFromInterface($start)
        ->setTimezone($zone);
    $utc = $local->setTimezone(new DateTimeZone('UTC'));
    $pgsql = $record->getConnection()->getDriverName() === 'pgsql';

    // for_datetime остаётся совместимым зеркалом для старых экранов.
    $mirror = DateTimeImmutable::createFromFormat(
        '!Y-m-d H:i:s',
        $local->format('Y-m-d H:i:s'),
        new DateTimeZone(self::timezone(null))
    );

    if (
        !$mirror
        || $mirror->format('Y-m-d H:i:s')
            !== $local->format('Y-m-d H:i:s')
    ) {
        self::reject(
            'Это время нельзя представить в прежнем формате '
            . 'расписания. Выберите другое время.'
        );
    }

    $attributes = $record->getAttributes();
    $attributes['appointment_starts_at_utc'] = $utc->format(
        $pgsql ? 'Y-m-d H:i:sP' : 'Y-m-d H:i:s'
    );
    $attributes['appointment_timezone'] = $zone->getName();
    $attributes['for_datetime'] = $mirror->format(
        $pgsql ? 'Y-m-d H:i:sP' : 'Y-m-d H:i:s'
    );

    $record->setRawAttributes($attributes);
}

    /** Compare real instants. PostgreSQL for_datetime is timestamptz. */
    public static function whereComparedToNow(Builder $query, string $operator, ?DateTimeInterface $now = null): void
    {
        if (!in_array($operator, ['>', '<='], true)) {
            throw new InvalidArgumentException('Unsupported appointment comparison.');
        }

        $now = $now
            ? DateTimeImmutable::createFromInterface($now)
            : new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $utcSql = self::queryTimestamp(
            $query,
            $now->setTimezone(new DateTimeZone('UTC'))
        );

        if ($query->getModel()->getConnection()->getDriverName() === 'pgsql') {
            $query->whereRaw(
                'COALESCE(registry_records.appointment_starts_at_utc, registry_records.for_datetime) '
                    . $operator . ' ?',
                [$utcSql]
            );
            return;
        }

        // Non-PostgreSQL compatibility: preserve the historical civil fallback.
        $legacySql = $now
            ->setTimezone(new DateTimeZone(self::timezone(null)))
            ->format('Y-m-d H:i:s');

        $query->where(static function (Builder $q) use ($operator, $utcSql, $legacySql): void {
            $q->where(static function (Builder $known) use ($operator, $utcSql): void {
                $known->whereNotNull('appointment_starts_at_utc')
                    ->where('appointment_starts_at_utc', $operator, $utcSql);
            })->orWhere(static function (Builder $legacy) use ($operator, $legacySql): void {
                $legacy->whereNull('appointment_starts_at_utc')
                    ->where('for_datetime', $operator, $legacySql);
            });
        });
    }

    public static function queryTimestamp(Builder $query, DateTimeInterface $date): string
    {
        return $date->format($query->getModel()->getConnection()->getDriverName() === 'pgsql'
            ? 'Y-m-d H:i:sP' : 'Y-m-d H:i:s');
    }

    public static function orderByStart(Builder $query, string $direction = 'asc'): void
    {
        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Unsupported appointment ordering.');
        }
        // Known instants sort together correctly even after a clinic zone change.
        // Historical rows retain their old sort value, whose instant is unknown.
        $query->orderByRaw('COALESCE(registry_records.appointment_starts_at_utc, registry_records.for_datetime) ' . $direction)
            ->orderBy('registry_records.id', $direction);
    }

    private static function reject(string $message): never
    {
        throw ValidationException::withMessages(['appointment_datetime' => $message]);
    }
}
