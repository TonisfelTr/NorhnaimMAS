<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Doctor;
use DateTimeZone;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class DoctorAppointmentTimezone
{
    public static function resolve(?Doctor $doctor): string
    {
        if (
            $doctor
            && Schema::hasTable('doctors')
            && Schema::hasColumn('doctors', 'appointment_timezone')
        ) {
            $candidate = trim((string) $doctor->getAttribute('appointment_timezone'));

            if ($candidate !== '' && self::isValid($candidate)) {
                return $candidate;
            }
        }

        return ClinicAppointmentTime::timezone($doctor?->clinic_id ?: null);
    }

    public static function explicit(?Doctor $doctor): bool
    {
        if (
            !$doctor
            || !Schema::hasTable('doctors')
            || !Schema::hasColumn('doctors', 'appointment_timezone')
        ) {
            return false;
        }

        $candidate = trim((string) $doctor->getAttribute('appointment_timezone'));

        return $candidate !== '' && self::isValid($candidate);
    }

    public static function normalize(?string $timezone): ?string
    {
        $timezone = trim((string) $timezone);

        if ($timezone === '') {
            return null;
        }

        if (!self::isValid($timezone)) {
            throw ValidationException::withMessages([
                'timezone' => 'Выберите корректный часовой пояс IANA.',
            ]);
        }

        return $timezone;
    }

    private static function isValid(string $timezone): bool
    {
        try {
            new DateTimeZone($timezone);

            return in_array(
                $timezone,
                DateTimeZone::listIdentifiers(),
                true
            );
        } catch (\Throwable) {
            return false;
        }
    }
}
