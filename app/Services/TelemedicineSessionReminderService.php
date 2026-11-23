<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\RegistryRecord;
use App\Notifications\DoctorTelemedicineReminderNotification;
use App\Support\ClinicAppointmentTime;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/**
 * Send per-appointment reminders from the server scheduler.
 * One reminder per channel, recipient and appointment start. Never include PHI in SMS.
 */
final class TelemedicineSessionReminderService
{
    /** @return array{checked:int,doctor_notices:int,patient_sms:int,doctor_sms:int,errors:int} */
    public function run(bool $dryRun = false): array
    {
        $stats = ['checked' => 0, 'doctor_notices' => 0, 'patient_sms' => 0, 'doctor_sms' => 0, 'errors' => 0];
        if (!Schema::hasTable('telemedicine_reminder_dispatches')
            || !Schema::hasTable('registry_records')
            || !Schema::hasColumn('registry_records', 'appointment_starts_at_utc')
            || !Schema::hasTable('telemedicine_provider_sessions')) {
            return $stats;
        }

        $now = CarbonImmutable::now('UTC');
        $noticeLead = max(1, min(240, (int) config('telemedicine_reminders.doctor_notice_minutes_before', 30)));
        $smsLead = max(1, min(240, (int) config('telemedicine_reminders.sms_minutes_before', 15)));
        $window = max($noticeLead, $smsLead);
        $sender = app(TelemedicineSmsGateway::class);

        // Explicit UTC start timestamps only; past bookings, cancelled bookings and unverified
        // legacy civil times cannot produce reminders.
        RegistryRecord::query()
            ->where('appointment_format', 'telemedicine')
            ->whereNull('deleted_at')
            ->whereNotNull('appointment_starts_at_utc')
            ->whereBetween('appointment_starts_at_utc', [
                $now->format('Y-m-d H:i:sP'),
                $now->addMinutes($window)->format('Y-m-d H:i:sP'),
            ])
            ->orderBy('id')
            ->chunkById(100, function ($records) use (&$stats, $now, $sender, $smsLead, $noticeLead, $dryRun): void {
                foreach ($records as $record) {
                    ++$stats['checked'];
                    try {
                        $start = ClinicAppointmentTime::startInstant($record);
                        if (!$start) continue;
                        $remaining = $start->getTimestamp() - $now->getTimestamp();
                        if ($remaining <= 0) continue;
                        $clinic = Clinic::query()->find($record->clinic_id);
                        if (!$clinic || !ClinicTelemedicineSettings::enabled($clinic)) continue;
                        $doctor = Doctor::query()->with('user')->whereKey($record->doctor_id)
                            ->where('clinic_id', $clinic->getKey())->first();
                        if (!$doctor || !$doctor->user || !$doctor->ready_for_telemedicine) continue;

                        $when = ClinicAppointmentTime::displayDate($record)?->format('d.m.Y H:i');
                        if (!$when) continue;
                        $when = $when . ' (' . ClinicAppointmentTime::timezone($clinic) . ')';
                        $startEpoch = $start->getTimestamp();

                        // In-panel reminder is independent of SMS consent and SMS provider state.
                        if ($remaining <= $noticeLead * 60) {
                            if ($dryRun) {
                                ++$stats['doctor_notices'];
                            } elseif ($this->doctorNotificationOnce($record, $doctor, $clinic, $when, $startEpoch)) {
                                ++$stats['doctor_notices'];
                            }
                        }

                        if ($remaining > $smsLead * 60 || !$sender->configured()) continue;
                        $session = DB::table('telemedicine_provider_sessions')
                            ->where('registry_record_id', $record->getKey())
                            ->where('status', 'ready')
                            ->where('patient_id', $record->patient_id)
                            ->where('doctor_id', $doctor->getKey())
                            ->where('clinic_id', $clinic->getKey())
                            ->first(['id', 'public_id']);
                        if (!$session) continue;

                        // No signed invitation or WebRTC bearer token is sent to clck.ru.
                        $shortener = app(TelemedicineShortLinkService::class);
                        if (Schema::hasColumn('patients', 'phone')) {
                            $patientPhone = DB::table('patients')->where('id', $record->patient_id)->value('phone');
                            if (TelemedicineSmsGateway::normalizePhone(is_string($patientPhone) ? $patientPhone : null)) {
                                $link = $dryRun ? 'dry-run' : $shortener->url((string) $session->public_id, 'patient', $startEpoch);
                                if ($link) {
                                    $text = 'Норхнейм: видеоконсультация ' . $when . '. Ваша ссылка: ' . $link;
                                    if ($dryRun || $this->sendOnce($sender, $record, $startEpoch, (int) $record->patient_id,
                                        'patient_sms', (string) $patientPhone, $text)) ++$stats['patient_sms'];
                                }
                            }
                        }

                        // Never send the doctor link to the cabinet telephone; opt-in and a
                        // private mobile number must both be present in the physician profile.
                        if ($doctor->telemedicine_sms_enabled
                            && TelemedicineSmsGateway::normalizePhone($doctor->telemedicine_sms_phone)) {
                            $link = $dryRun ? 'dry-run' : $shortener->url((string) $session->public_id, 'doctor', $startEpoch);
                            if ($link) {
                                $text = 'Норхнейм: ваш видеосеанс ' . $when . '. Ссылка врача: ' . $link;
                                if ($dryRun || $this->sendOnce($sender, $record, $startEpoch, (int) $doctor->getKey(),
                                    'doctor_sms', (string) $doctor->telemedicine_sms_phone, $text)) ++$stats['doctor_sms'];
                            }
                        }
                    } catch (Throwable $e) {
                        // The next eligible reminder must still be processed. Never log SMS body/URL.
                        ++$stats['errors'];
                        report(new RuntimeException('Telemedicine reminder item failed. Verify setup and dispatch journal.')); 
                    }
                }
            });

        return $stats;
    }

    private function eventKey(RegistryRecord $record, int $startEpoch, int $recipient, string $channel): string
    {
        return hash('sha256', implode(':', [(int) $record->getKey(), $startEpoch, $recipient, $channel]));
    }

    private function reserve(RegistryRecord $record, int $startEpoch, int $recipient, string $channel): bool
    {
        $now = now();
        return DB::table('telemedicine_reminder_dispatches')->insertOrIgnore([
            'event_key' => $this->eventKey($record, $startEpoch, $recipient, $channel),
            'registry_record_id' => (int) $record->getKey(),
            'start_epoch' => $startEpoch,
            'recipient_id' => $recipient,
            'channel' => $channel,
            'status' => 'pending',
            'created_at' => $now, 'updated_at' => $now,
        ]) === 1;
    }

    private function finish(RegistryRecord $record, int $startEpoch, int $recipient, string $channel, string $status): void
    {
        DB::table('telemedicine_reminder_dispatches')
            ->where('event_key', $this->eventKey($record, $startEpoch, $recipient, $channel))
            ->update(['status' => $status, 'updated_at' => now()]);
    }

    private function doctorNotificationOnce(RegistryRecord $record, Doctor $doctor, Clinic $clinic, string $when, int $startEpoch): bool
    {
        if (!Schema::hasTable('notifications')) return false;
        $user = $doctor->user;
        if (!$user || !$user->hasRole('doctors')) return false;

        return DB::transaction(function () use ($record, $doctor, $clinic, $user, $when, $startEpoch): bool {
            if (!$this->reserve($record, $startEpoch, (int) $user->getKey(), 'doctor_panel')) return false;
            $url = route('doctors.telemedicine-session.show', [
                'patient' => (int) $record->patient_id,
                'recordId' => (int) $record->getKey(),
            ], false);
            $user->notify(new DoctorTelemedicineReminderNotification($when, $url, (int) $clinic->getKey()));
            $this->finish($record, $startEpoch, (int) $user->getKey(), 'doctor_panel', 'accepted');
            return true;
        }, 3);
    }

    private function sendOnce(TelemedicineSmsGateway $sender, RegistryRecord $record, int $startEpoch,
        int $recipient, string $channel, string $phone, string $message): bool
    {
        // Insert BEFORE calling provider: an unknown HTTP result is never retried
        // automatically, because the first SMS may already have been accepted.
        if (!$this->reserve($record, $startEpoch, $recipient, $channel)) return false;
        try {
            $sender->send($phone, $message);
            $this->finish($record, $startEpoch, $recipient, $channel, 'accepted');
            return true;
        } catch (Throwable) {
            $this->finish($record, $startEpoch, $recipient, $channel, 'unknown');
            return false;
        }
    }
}
