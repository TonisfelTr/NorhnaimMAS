<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Notifications\PatientPortalNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Patient-only database notifications for clinical events.
 * Each event is stored atomically with its unique deduplication key.
 * No diagnoses, lab values or treatment details are included in messages.
 */
final class PatientPortalEventNotifier
{
    public function notifyOnce(
        int $patientId,
        string $eventKey,
        string $category,
        string $title,
        string $message,
        string $url,
        string $icon = 'bell',
        ?int $clinicId = null
    ): bool {
        if ($patientId <= 0 || $eventKey === '' || strlen($eventKey) > 190) {
            return false;
        }

        try {
            if (!Schema::hasTable('notifications') || !Schema::hasTable('patient_notification_event_keys')) {
                return false;
            }

            $patient = Patient::query()->without('doctor')->with('user')->find($patientId);
            $user = $patient?->user;
            if (!$user || !$user->hasRole('patients')) {
                return false;
            }

            // We only ever link into authenticated patient pages.
            if (!Str::startsWith($url, '/') || Str::startsWith($url, '//')) {
                return false;
            }

            // NORHNAIM_NOTIFICATION_CLINICS_V1: clinic belongs to the originating event.
            $clinicId = app(PatientNotificationClinicResolver::class)
                ->resolve($patientId, $eventKey, $clinicId);

            $created = DB::transaction(static function () use (
                $user, $eventKey, $category, $title, $message, $url, $icon, $clinicId
            ): bool {
                $time = now();
                $inserted = DB::table('patient_notification_event_keys')->insertOrIgnore([
                    'user_id' => (int) $user->getKey(),
                    'event_key' => $eventKey,
                    'created_at' => $time,
                ]);
                if ($inserted !== 1) {
                    return false;
                }

                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => PatientPortalNotification::class,
                    'notifiable_type' => $user->getMorphClass(),
                    'notifiable_id' => (int) $user->getKey(),
                    'clinic_id' => $clinicId,
                    'data' => json_encode([
                        'category' => $category,
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'icon' => $icon,
                        'clinic_id' => $clinicId,
                    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'read_at' => null,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
                return true;
            }, 3);
            if ($created) {
                app(\App\Services\PatientNotificationRealtimePublisher::class)
                    ->publishAfterCommit((int) $user->getKey());
            }
            return $created;
        } catch (Throwable $e) {
            // Clinical record saving must never fail because a notification could not be delivered.
            report($e);
            return false;
        }
    }
}
