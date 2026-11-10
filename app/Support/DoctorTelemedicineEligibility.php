<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Doctor;
use Illuminate\Validation\ValidationException;

final class DoctorTelemedicineEligibility
{
    public function allows(Doctor $doctor): bool
    {
        return (bool) $doctor->ready_for_telemedicine;
    }

    /**
     * @throws ValidationException
     */
    public function assertAllowed(Doctor $doctor): void
    {
        if ($this->allows($doctor)) {
            return;
        }

        $doctorName = trim(
            implode(
                ' ',
                array_filter([
                    $doctor->surname,
                    $doctor->name,
                    $doctor->patronym,
                ])
            )
        );

        throw ValidationException::withMessages([
            'appointment_format' =>
                ($doctorName !== '' ? $doctorName . ': ' : '')
                . 'врач не отмечен как готовый к телемедицине. '
                . 'Создание или изменение записи в формате телемедицины запрещено.',
        ]);
    }
}
