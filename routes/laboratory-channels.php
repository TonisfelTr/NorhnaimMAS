<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Each laboratory account is allowed to subscribe to ONE assigned clinic.
Broadcast::channel(
    'laboratory.clinic.{clinicId}',
    static function (User $user, string $clinicId): bool {
        return ctype_digit($clinicId)
            && (int) $clinicId > 0
            && method_exists($user, 'hasRole')
            && $user->hasRole('laborant')
            && (int) ($user->laboratory_clinic_id ?? 0) === (int) $clinicId;
    },
    ['guards' => ['web']]
);
