<?php

namespace App\Support;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Scheduling permission only; does not grant clinical editing or confirmation. */
final class TelemedicineJournalAccess
{
    public function canManage(?User $user, Patient $patient): bool
    {
        return $this->explain($user, $patient)['allowed'];
    }

    /** For Tinker: identifiers and reasons, never patient names or diagnoses. */
    public function explain(?User $user, Patient $patient): array
    {
        $context = $this->patientContext($patient);
        $result = array_merge($context, [
            'allowed' => false, 'mode' => null, 'reason' => '',
            'user_id' => $user?->getKey(), 'admin_clinic_ids' => [],
        ]);
        if (!$user || (int) $user->getKey() <= 0) {
            return array_replace($result, ['reason' => 'Требуется вход в систему.']);
        }
        if (!$context['valid']) return array_replace($result, ['reason' => $context['reason']]);
        $clinicId = $context['clinic_id'];
        $result['admin_clinic_ids'] = $this->adminClinicIds($user);
        if ($clinicId > 0 && in_array($clinicId, $result['admin_clinic_ids'], true)) {
            return array_replace($result, ['allowed' => true, 'mode' => 'clinic_admin']);
        }
        // An additional admins role is NOT a veto when the user is this doctor.
        if ($user->hasRole('doctors')) {
            $doctors = Doctor::query()->where('user_id', $user->getKey())->get();
            foreach ($doctors as $doctor) {
                if ((int) $doctor->getKey() === $context['doctor_id']
                    && max(0, (int) $doctor->clinic_id) === $clinicId) {
                    return array_replace($result, ['allowed' => true, 'mode' => 'attending']);
                }
            }
            if ($clinicId > 0 && $context['doctor_id'] > 0) {
                $actingIds = $this->actingDoctorIds($context, /* clinic-telemedicine-timezones:timezone-acting-current-shift:v1:begin */ Carbon::now(\App\Support\ClinicAppointmentTime::timezone($clinicId)) /* clinic-telemedicine-timezones:timezone-acting-current-shift:v1:end */);
                foreach ($doctors as $doctor) {
                    if ((int) $doctor->clinic_id === $clinicId
                        && in_array((int) $doctor->getKey(), $actingIds, true)) {
                        return array_replace($result, ['allowed' => true, 'mode' => 'acting']);
                    }
                }
            }
        }
        $reason = $user->hasRole('clinic_admin') && $result['admin_clinic_ids'] === []
            ? 'Роль администратора есть, но его привязка к клинике не настроена.'
            : 'Управление сеансами доступно администратору этой клиники, лечащему врачу и его действующему И.О.';
        return array_replace($result, ['reason' => $reason]);
    }

    /** Always use the persisted assignment, not a cached patient->doctor relation. */
    public function patientContext(Patient $patient): array
    {
        $row = Patient::query()->whereKey($patient->getKey())->first();
        $doctorId = max(0, (int) ($row?->getAttribute('doctor_id') ?? 0));
        $doctor = $doctorId > 0 ? Doctor::query()->find($doctorId) : null;
        $explicitClinic = max(0, (int) ($row?->getAttribute('clinic_id') ?? 0));
        $doctorClinic = max(0, (int) ($doctor?->clinic_id ?? 0));
        $clinicId = $explicitClinic ?: $doctorClinic;
        $valid = $row !== null;
        $reason = 'Пациент не найден.';
        if ($valid && $doctorId > 0 && !$doctor) {
            $valid = false;
            $reason = 'Назначенный лечащий врач не найден.';
        }
        if ($valid && $explicitClinic > 0 && $doctor && $explicitClinic !== $doctorClinic) {
            $valid = false;
            $reason = 'Клиника пациента и клиника лечащего врача не совпадают. Проверьте назначение.';
        }
        if ($valid && $clinicId > 0 && !$this->liveTable('clinics')->where('id', $clinicId)->exists()) {
            $valid = false;
            $reason = 'Клиника не найдена или удалена.';
        }
        return [
            'patient_id' => (int) $patient->getKey(), 'doctor_id' => $doctorId,
            'clinic_id' => $clinicId, 'cabinet_id' => max(0, (int) ($row?->getAttribute('cabinet_id') ?? 0)),
            'valid' => $valid, 'reason' => $valid ? '' : $reason,
        ];
    }

    /** No global admins/email/debug bypass. A real clinic binding is required. */
    public function adminClinicIds(?User $user): array
    {
        if (!$user || !$user->hasRole('clinic_admin')) return [];
        $ids = Doctor::query()->where('user_id', $user->getKey())->pluck('clinic_id')->all();
        // Server configuration supports administrators WITHOUT fabricated doctor profiles.
        $map = (array) config('telemedicine_journal_access.clinic_admin_user_clinics', []);
        foreach ((array) ($map[(string) $user->getKey()] ?? []) as $id) $ids[] = $id;
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
        if ($ids === []) return [];
        return $this->liveTable('clinics')->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function hasAdministrativeClinic(?User $user): bool
    {
        return $this->adminClinicIds($user) !== [];
    }

    public function manageableAppointments(?User $user, Patient $patient): Builder
    {
        $scope = $this->explain($user, $patient);
        $query = RegistryRecord::query()->withTrashed()->where('patient_id', $patient->getKey());
        if (!$scope['allowed']) return $query->whereRaw('1 = 0');
        if ($scope['clinic_id'] > 0) return $query->where('clinic_id', $scope['clinic_id']);
        return $query->where('doctor_id', $scope['doctor_id'])
            ->where(fn ($q) => $q->whereNull('clinic_id')->orWhere('clinic_id', 0));
    }

    /** Current substitution, not a future appointment's date or a historical assignment. */
    private function actingDoctorIds(array $context, Carbon $now): array
    {
        foreach (['clinic_cabinets', 'employee_work_schedules'] as $table) {
            if (!Schema::hasTable($table)) return [];
        }
        $clinicId = $context['clinic_id'];
        $baseId = $context['doctor_id'];
        $date = $now->toDateString();
        $cabinets = $this->liveTable('clinic_cabinets')->where('clinic_id', $clinicId)
            ->where('doctor_id', $baseId)->where('enabled', true)->where('cabinet_type', 'doctor');
        if ($context['cabinet_id'] > 0) $cabinets->where('id', $context['cabinet_id']);
        $cabinets = $cabinets->get();
        if ($cabinets->isEmpty()) return [];
        $schedules = $this->liveTable('employee_work_schedules')->where('clinic_id', $clinicId)
            ->where('employee_type', 'doctor')->where('employee_id', $baseId)
            ->where('enabled', true)->where('day_number', $now->isoWeekday())->get();
        $vacations = $this->approvedVacations($clinicId, $date);
        $baseVacations = $vacations->where('employee_id', $baseId)->values();
        $manual = Schema::hasTable('doctor_schedule_replacements')
            ? $this->liveTable('doctor_schedule_replacements')->where('clinic_id', $clinicId)
                ->where('shift_date', $date)->whereIn('clinic_cabinet_id', $cabinets->pluck('id')->all())->get()
            : collect();
        $ids = [];
        foreach ($cabinets as $cabinet) {
            foreach ($schedules as $schedule) {
                if (!self::withinShift($now, $schedule->start_time, $schedule->end_time)) continue;
                $overrides = $manual->where('clinic_cabinet_id', $cabinet->id)->values();
                $candidate = 0;
                if ($overrides->isNotEmpty()) {
                    // Invalid/ambiguous override must not revive an older substitute.
                    if ($overrides->count() !== 1) continue;
                    $override = $overrides->first();
                    if ((int) $override->base_doctor_id !== $baseId
                        || (int) $override->schedule_id !== (int) $schedule->id) continue;
                    $candidate = (int) $override->replacement_doctor_id;
                } elseif ($baseVacations->isNotEmpty()) {
                    if ($baseVacations->count() !== 1) continue;
                    $candidate = (int) $baseVacations->first()->replace_doctor_id;
                } else {
                    // Legacy explicit office substitution: only during the base doctor's shift.
                    $candidate = (int) ($cabinet->replace_doctor_id ?? 0);
                }
                if ($candidate <= 0 || $candidate === $baseId
                    || $vacations->where('employee_id', $candidate)->isNotEmpty()) continue;
                if (Doctor::query()->whereKey($candidate)->where('clinic_id', $clinicId)->exists()) {
                    $ids[] = $candidate;
                }
            }
        }
        return array_values(array_unique($ids));
    }

    private function approvedVacations(int $clinicId, string $date)
    {
        if (!Schema::hasTable('employee_vacations')) return collect();
        return $this->liveTable('employee_vacations')->where('clinic_id', $clinicId)
            ->where('employee_type', 'doctor')->where('status', 'approved')
            ->whereDate('date_from', '<=', $date)->whereDate('date_to', '>=', $date)->get();
    }

    /** The installed schedule module supports same-day shifts. End is exclusive. */
    private static function withinShift(Carbon $now, $from, $to): bool
    {
        $from = (string) $from;
        $to = (string) $to;
        $pattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
        if (!preg_match($pattern, $from) || !preg_match($pattern, $to)) return false;
        if (strlen($from) === 5) $from .= ':00';
        if (strlen($to) === 5) $to .= ':00';
        $time = $now->format('H:i:s');
        return $from < $to && $time >= $from && $time < $to;
    }

    private function liveTable(string $table)
    {
        $query = DB::table($table);
        if (Schema::hasColumn($table, 'deleted_at')) $query->whereNull('deleted_at');
        return $query;
    }
}
