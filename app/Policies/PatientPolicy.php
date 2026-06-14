<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

final class PatientPolicy
{
    public function view(User $user, Patient $patient): bool
    {
        return $user->hasRole('doctor')
            && $user->doctor
            && (int) $patient->doctor_id === (int) $user->doctor->id;
    }
}
