<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TestSession;
use App\Models\User;

final class TestSessionPolicy
{
    public function view(User $user, TestSession $session): bool
    {
        return $user->hasRole('doctor')
            && $user->doctor
            && $session->patient
            && (int) $session->patient->doctor_id === (int) $user->doctor->id;
    }

    public function cancel(User $user, TestSession $session): bool
    {
        return $this->view($user, $session)
            && $session->status === 'assigned';
    }

    public function finish(User $user, TestSession $session): bool
    {
        return $this->view($user, $session)
            && \in_array($session->status, ['submitted', 'in_review', 'coding_done'], true);
    }

    public function update(User $user, TestSession $session): bool
    {
        return $this->view($user, $session)
            && ! \in_array($session->status, ['completed', 'cancelled'], true);
    }
}
