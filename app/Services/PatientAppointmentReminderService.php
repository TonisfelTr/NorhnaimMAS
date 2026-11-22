<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Hourly reminder for records that have an explicit UTC start timestamp. */
final class PatientAppointmentReminderService
{
    public function send(): int
    {
        if (!Schema::hasTable('registry_records')
            || !Schema::hasColumn('registry_records', 'appointment_starts_at_utc')) {
            return 0;
        }

        // A two-hour window tolerates a missed hourly run; event key prevents duplicates.
        $from = now('UTC')->addHours(23);
        $until = now('UTC')->addHours(25);
        $query = DB::table('registry_records')
            ->whereNotNull('appointment_starts_at_utc')
            ->whereBetween('appointment_starts_at_utc', [$from, $until])
            ->where('patient_id', '>', 0)
            ->orderBy('id');
        if (Schema::hasColumn('registry_records', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $sent = 0;
        foreach ($query->select(['id', 'patient_id', 'clinic_id'])->cursor() as $row) {
            if (app(PatientPortalEventNotifier::class)->notifyOnce(
                (int) $row->patient_id,
                'appointment:reminder:day-before:' . $row->id,
                'appointment',
                'Напоминание о приёме',
                'У вас запланирован приём примерно через сутки. Уточните время в разделе «История обращений».',
                '/patient/history',
                'alarm',
                (int) $row->clinic_id
            )) {
                ++$sent;
            }
        }
        return $sent;
    }
}
