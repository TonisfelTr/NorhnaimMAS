<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Test;
use App\Models\User;

final class TestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['doctor', 'admins']);
    }

    public function view(User $user, Test $test): bool
    {
        if ($test->is_public) {
            return $user->hasAnyRole(['doctor', 'admins']);
        }

        return $user->hasRole('doctor')
            && $user->doctor
            && (int) $test->owner_doctor_id === (int) $user->doctor->id;
    }

    public function assign(User $user, Test $test): bool
    {
        if (! $user->can('tests.assign')) {
            return false;
        }

        if ($test->is_public) {
            return $user->hasRole('doctor');
        }

        return $user->hasRole('doctor')
            && $user->doctor
            && (int) $test->owner_doctor_id === (int) $user->doctor->id;
    }
}
