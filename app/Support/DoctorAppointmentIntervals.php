<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Pure interval arithmetic, independent of Laravel, SQL and the clock.
 * Intervals are half-open [start, end), in whole minutes of one local day.
 * These are WORKING intervals; existing appointments are not checked here.
 */
final class DoctorAppointmentIntervals
{
    public static function validDuration(int $minutes): bool
    {
        return $minutes >= 5 && $minutes <= 480 && $minutes % 5 === 0;
    }

    /** Database TIME values with zero seconds are accepted without rounding. */
    public static function parseClock(mixed $clock): ?int
    {
        if (!is_string($clock)
            || !preg_match('/\A([01][0-9]|2[0-3]):([0-5][0-9])(?::00(?:\.0{1,6})?)?\z/', $clock, $match)) {
            return null;
        }

        return (int) $match[1] * 60 + (int) $match[2];
    }

    public static function formatClock(int $minutes): string
    {
        if ($minutes < 0 || $minutes > 1440) {
            throw new InvalidArgumentException('Время находится вне одного календарного дня.');
        }

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /** Sort and merge overlapping or touching working intervals. */
    public static function normalize(array $intervals): array
    {
        foreach ($intervals as $interval) {
            if (!is_array($interval) || count($interval) !== 2
                || !array_key_exists(0, $interval) || !array_key_exists(1, $interval)
                || !is_int($interval[0]) || !is_int($interval[1])
                || $interval[0] < 0 || $interval[1] > 1440 || $interval[0] >= $interval[1]) {
                throw new InvalidArgumentException('Некорректный рабочий интервал.');
            }
        }

        usort($intervals, static fn (array $a, array $b): int => $a[0] <=> $b[0] ?: $a[1] <=> $b[1]);
        $merged = [];
        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;
            if ($last >= 0 && $start <= $merged[$last][1]) {
                $merged[$last][1] = max($merged[$last][1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        return $merged;
    }

    public static function intersect(array $first, array $second): array
    {
        $first = self::normalize($first);
        $second = self::normalize($second);
        $result = [];
        $i = $j = 0;
        while ($i < count($first) && $j < count($second)) {
            $start = max($first[$i][0], $second[$j][0]);
            $end = min($first[$i][1], $second[$j][1]);
            if ($start < $end) {
                $result[] = [$start, $end];
            }
            if ($first[$i][1] < $second[$j][1]) {
                $i++;
            } else {
                $j++;
            }
        }

        return self::normalize($result);
    }

    public static function subtract(array $working, array $blocked): array
    {
        $working = self::normalize($working);
        $blocked = self::normalize($blocked);
        $result = [];
        foreach ($working as [$start, $end]) {
            $cursor = $start;
            foreach ($blocked as [$blockedStart, $blockedEnd]) {
                if ($blockedEnd <= $cursor) {
                    continue;
                }
                if ($blockedStart >= $end) {
                    break;
                }
                if ($blockedStart > $cursor) {
                    $result[] = [$cursor, $blockedStart];
                }
                $cursor = max($cursor, $blockedEnd);
                if ($cursor >= $end) {
                    break;
                }
            }
            if ($cursor < $end) {
                $result[] = [$cursor, $end];
            }
        }

        return $result;
    }

    /** No 15-minute restriction here: e.g. 13:07 + 30 minutes is valid. */
    public static function covers(array $working, int $start, int $duration): bool
    {
        if (!self::validDuration($duration) || $start < 0 || $start + $duration > 1440) {
            return false;
        }
        foreach (self::normalize($working) as [$from, $to]) {
            if ($start >= $from && $start + $duration <= $to) {
                return true;
            }
        }

        return false;
    }

    /** Suggested starts only, aligned to the day; minimumStart comes from the caller's clock. */
    public static function slots(array $working, int $duration, int $step = 15, int $minimumStart = 0): array
    {
        if (!self::validDuration($duration) || $step < 1 || $step > 1440) {
            throw new InvalidArgumentException('Некорректная длительность приёма или шаг времени.');
        }
        $starts = [];
        foreach (self::normalize($working) as [$from, $to]) {
            $from = max($from, $minimumStart, 0);
            $first = (int) (ceil($from / $step) * $step);
            for ($start = $first; $start + $duration <= $to; $start += $step) {
                $starts[] = self::formatClock($start);
            }
        }

        return $starts;
    }

    /**
     * Rules have already been filtered by local date, clinic and target IDs.
     * Shape: {id:int, scope:clinic|buildings|offices|doctors,
     *         mode:closed|blocked|override|extra, start:int, end:int}.
     * Quarantine is normalized by the repository to clinic/closed/[0,1440).
     *
     * Facility rules retain existing precedence: doctor > office > building > clinic.
     * Among equally specific overrides, the lowest ID wins (existing orderBy('id')).
     * A general extra interval opens the facility, but never creates a doctor's shift.
     * A doctor-specific override/extra is an explicit special shift and may open
     * ordinary closed hours; an explicit closure always wins over every opening.
     */
    public static function resolve(array $doctorWeekly, array $facilityWeekly, array $rules): array
    {
        $doctorWeekly = self::normalize($doctorWeekly);
        $facilityWeekly = self::normalize($facilityWeekly);
        $priority = ['clinic' => 1, 'buildings' => 2, 'offices' => 3, 'doctors' => 4];
        foreach ($rules as $rule) {
            if (!is_array($rule) || !isset($rule['id'], $rule['scope'], $rule['mode'], $rule['start'], $rule['end'])
                || !is_int($rule['id']) || !isset($priority[$rule['scope']])
                || !in_array($rule['mode'], ['closed', 'blocked', 'override', 'extra'], true)) {
                throw new InvalidArgumentException('Некорректное исключение из графика.');
            }
            self::normalize([[$rule['start'], $rule['end']]]);
        }
        usort($rules, static fn (array $a, array $b): int =>
            $priority[$b['scope']] <=> $priority[$a['scope']] ?: $a['id'] <=> $b['id']);

        $facility = $facilityWeekly;
        $doctor = $doctorWeekly;
        $facilityOverride = $doctorOverride = false;
        $facilityExtra = $doctorExtra = $blocked = [];
        foreach ($rules as $rule) {
            $interval = [$rule['start'], $rule['end']];
            if (in_array($rule['mode'], ['closed', 'blocked'], true)) {
                $blocked[] = $interval;
            } elseif ($rule['mode'] === 'override') {
                if (!$facilityOverride) {
                    $facility = [$interval];
                    $facilityOverride = true;
                }
                if ($rule['scope'] === 'doctors' && !$doctorOverride) {
                    $doctor = [$interval];
                    $doctorOverride = true;
                }
            } else {
                $facilityExtra[] = $interval;
                if ($rule['scope'] === 'doctors') {
                    $doctorExtra[] = $interval;
                }
            }
        }

        return self::subtract(
            self::intersect(array_merge($doctor, $doctorExtra), array_merge($facility, $facilityExtra)),
            $blocked
        );
    }
}
