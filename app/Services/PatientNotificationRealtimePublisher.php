<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\PatientNotificationCountsChanged;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Broadcast the new counters AFTER a successful database commit.
 * A WebSocket outage must not break medical record creation or marking read.
 */
final class PatientNotificationRealtimePublisher
{
    public function publishAfterCommit(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        try {
            if (DB::transactionLevel() > 0) {
                DB::afterCommit(fn () => $this->publish($userId));
            } else {
                $this->publish($userId);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function publish(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        try {
            if (!config('broadcasting.connections.reverb.key')) {
                return;
            }
            $user = User::query()->find($userId);
            if (!$user || !$user->hasRole('patients')) {
                return;
            }

            $unread = (int) $user->unreadNotifications()->count();
            $read = (int) $user->notifications()->whereNotNull('read_at')->count();

            event(new PatientNotificationCountsChanged($userId, $unread, $read));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
