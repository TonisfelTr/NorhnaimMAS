<?php

namespace App\Policies;

use App\Models\TestAssignment;
use App\Models\User;

class TestAssignmentPolicy
{
    public function start(User $user, TestAssignment $assignment): bool
    {
        if ($assignment->clinician_id && $assignment->clinician_id === $user->id) {
            return true;
        }

        if ($user->hasRole('doctor') && $this->userCanAccessPatient($user, $assignment)) {
            return true;
        }

        return false;
    }

    protected function userCanAccessPatient(User $user, TestAssignment $assignment): bool
    {
        $patientId = data_get($assignment->context, 'patient_id');
        if (!$patientId) return false;

        return true;
    }
}
