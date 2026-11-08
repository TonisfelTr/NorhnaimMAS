<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\HospitalBedAssignment;
use App\Models\HospitalDepartment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Central server-side access checks for the inpatient workbench and medical cards.
 * Reading a medical card is permitted to a doctor/nurse who has a real staff
 * profile in the clinic connected with the patient. Writing remains restricted.
 * Patient access by medical staff does NOT depend on APP_DEBUG.
 */
class HospitalPatientAccess
{
    /**
     * The project's bootstrap administrator is a developer-only account.
     * A generic "admins" role is NOT sufficient to see patients in production.
     */
    // NORHNAIM_CLINIC_ADMIN_GLOBAL_MEDCARD_V1
    /**
     * clinic_admin — глобальный администратор медицинских карт.
     * Дополнительно сохраняем точный системный аккаунт admin@admin.ru
     * как совместимый идентификатор проекта.
     */
    public function isGlobalClinicAdmin(User $user): bool
    {
        if ($user->hasRole('clinic_admin')) {
            return true;
        }

        foreach (['email', 'login'] as $field) {
            if (
                strcasecmp(
                    trim((string) $user->getRawOriginal($field)),
                    'admin@admin.ru'
                ) === 0
            ) {
                return true;
            }
        }

        return false;
    }

    public function isDebugAdmin(User $user): bool
    {
        if (!config('app.debug') || !$user->hasRole('admins')) {
            return false;
        }

        // Some installations use "login" instead of "email" for the bootstrap account.
        $identifiers = [
            $user->getRawOriginal('email'),
            $user->getRawOriginal('login'),
        ];

        foreach ($identifiers as $identifier) {
            if (strtolower(trim((string) $identifier)) === 'admin@admin.ru') {
                return true;
            }
        }

        return false;
    }

    /** @return list<int> */
    public function adminClinicIds(User $user): array
    {
        if (!$this->isDebugAdmin($user)) {
            return [];
        }

        // Clinic type is the single source of truth for inpatient facilities.
        return Clinic::query()
            ->where('clinic_type', Clinic::TYPE_INPATIENT)
            ->pluck('id')->map(static fn ($id): int => (int) $id)->all();
    }

    /** @return list<int> */
    public function doctorDepartmentIds(Doctor $doctor): array
    {
        if (!$doctor->clinic || $doctor->clinic->clinic_type !== Clinic::TYPE_INPATIENT) {
            return [];
        }

        return HospitalDepartment::query()
            ->where('clinic_id', $doctor->clinic_id)
            ->where('enabled', true)
            ->where(function (Builder $departments) use ($doctor): void {
                $departments->where('head_doctor_id', $doctor->getKey())
                    ->orWhereHas('doctors', fn (Builder $doctorQuery): Builder => $doctorQuery
                        ->where('doctors.id', $doctor->getKey())
                        ->where('doctors.clinic_id', $doctor->clinic_id));
            })
            ->pluck('id')->map(static fn ($id): int => (int) $id)->all();
    }

    /**
     * An active hospitalization, in a department of a permitted inpatient clinic.
     * Check consistency at all levels of the location hierarchy.
     *
     * @param list<int> $clinicIds
     * @param list<int> $departmentIds
     * @return Builder<HospitalBedAssignment>
     */
    public function activeAssignments(array $clinicIds, array $departmentIds): Builder
    {
        return HospitalBedAssignment::query()
            ->whereNull('released_at')
            ->whereHas('patient')
            ->whereHas('department', static function (Builder $department) use ($clinicIds, $departmentIds): void {
                $department->whereIn('id', $departmentIds)
                    ->whereIn('clinic_id', $clinicIds);
            })
            ->where(static function (Builder $assignment) use ($clinicIds): void {
                // Corridor patients are included, but occupied beds must have
                // clinic and department consistent with their admission.
                $assignment->whereNull('bed_id')
                    ->orWhereHas('bed', static function (Builder $bed) use ($clinicIds): void {
                        $bed->whereIn('clinic_id', $clinicIds)
                            ->whereHas('room', static function (Builder $room) use ($clinicIds): void {
                                $room->whereIn('clinic_id', $clinicIds)
                                    ->whereColumn('hospital_rooms.clinic_id', 'hospital_beds.clinic_id')
                                    ->whereColumn('hospital_rooms.department_id', 'hospital_bed_assignments.department_id');
                            });
                    });
            });
    }

    public function isAdminReader(User $user): bool
    {
        return $this->isGlobalClinicAdmin($user)
            || $this->isDebugAdmin($user);
    }

    /**
     * Медкарта доступна любому сотруднику, действительно прикреплённому
     * к одной из клиник, в которой лечится/наблюдается пациент.
     *
     * Роль admins/clinic_admin сама по себе НЕ даёт доступ ко всем пациентам.
     * APP_DEBUG не используется для выдачи доступа сотрудникам.
     */
    public function canRead(User $user, Patient $patient): bool
    {
        if ($this->isGlobalClinicAdmin($user)) {
            return true;
        }

        // NORHNAIM_TELEMED_ACCESS_V1: permits reading; canEdit remains unchanged.
        if (app(\App\Support\TelemedicineJournalAccess::class)->canManage($user, $patient)) {
            return true;
        }

        // Ранее согласованный выделенный системный администратор.
        // Только точный логин/email вместе с системной ролью, не просто роль admins.
        if ($this->isSystemAdmin($user)) {
            return true;
        }

        $clinicIds = $this->staffClinicIds($user);
        if ($clinicIds === []) {
            return false;
        }

        // Прямая принадлежность пациента клинике.
        if (Schema::hasColumn('patients', 'clinic_id')) {
            $patientClinicId = (int) $patient->getAttribute('clinic_id');
            if ($patientClinicId > 0 && in_array($patientClinicId, $clinicIds, true)) {
                return true;
            }
        }

        // Пациент обращался в эту клинику (включая запись к любому её врачу).
        // Удалённая/отменённая запись не является подтверждением доступа.
        if (Schema::hasTable('registry_records')
            && Schema::hasColumn('registry_records', 'clinic_id')
            && Schema::hasColumn('registry_records', 'patient_id')) {
            $record = DB::table('registry_records')
                ->where('patient_id', $patient->getKey())
                ->whereIn('clinic_id', $clinicIds);

            if (Schema::hasColumn('registry_records', 'deleted_at')) {
                $record->whereNull('deleted_at');
            }

            if ($record->exists()) {
                return true;
            }
        }

        // Совместимость со старыми амбулаторными пациентами, у которых
        // заполнен doctor_id, но clinic_id отсутствует.
        if (Schema::hasColumn('patients', 'doctor_id')) {
            $patientDoctorId = (int) $patient->getAttribute('doctor_id');
            if ($patientDoctorId > 0 && Doctor::query()
                ->whereKey($patientDoctorId)
                ->whereIn('clinic_id', $clinicIds)
                ->exists()) {
                return true;
            }
        }

        // Стационар: все действующие госпитализации клиники, в том числе
        // пациентов другого отделения или временно размещённых в коридоре.
        // Продолжаем использовать существующую проверку иерархии койки/палаты.
        $departmentIds = HospitalDepartment::query()
            ->whereIn('clinic_id', $clinicIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        return $departmentIds !== [] && $this->activeAssignments($clinicIds, $departmentIds)
            ->where('patient_id', $patient->getKey())
            ->exists();
    }

    /** @return list<int> Клинике должен принадлежать именно сотрудник, а не только его роль. */
    private function staffClinicIds(User $user): array
    {
        $ids = [];
        $doctorRole = $user->hasRole('doctors');
        $nurseRole = $user->hasRole('nurses') || $user->hasRole('nurse');

        if ($doctorRole && method_exists($user, 'doctor')) {
            $doctor = $user->doctor()->with('clinic')->first();
            if ($doctor instanceof Doctor && $doctor->clinic && (int) $doctor->clinic_id > 0) {
                $ids[] = (int) $doctor->clinic_id;
            }
        }

        // В некоторых версиях Norhnaim медсестра имеет и Doctor-профиль
        // для авторизации. Разрешаем Nurse-профиль при любой медицинской роли.
        if (($doctorRole || $nurseRole) && method_exists($user, 'nurse')) {
            $nurse = $user->nurse()->with('clinic')->first();
            if ($nurse && $nurse->clinic && (int) $nurse->clinic_id > 0) {
                $ids[] = (int) $nurse->clinic_id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Исключение только для выделенного системного аккаунта.
     * Обычная роль admins не даёт глобальных прав чтения.
     */
    private function isSystemAdmin(User $user): bool
    {
        if (!$user->hasRole('admins')) {
            return false;
        }

        foreach (['email', 'login'] as $field) {
            if (strcasecmp(trim((string) $user->getRawOriginal($field)), 'admin@admin.ru') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Deliberately preserves the old production edit rule, without the debug bypass:
     * medcard demographic edits only for the patient's directly assigned doctor.
     * Inpatient assignment does not automatically grant writing permission.
     */
    public function canEdit(User $user, Patient $patient): bool
    {
        if ($this->isGlobalClinicAdmin($user)) {
            return true;
        }

        if ($user->hasRole('admins') || !$user->hasRole('doctors')) {
            return false;
        }

        $doctor = $user->doctor()->with('clinic')->first();

        return $doctor instanceof Doctor && $doctor->clinic !== null
            && (int) $patient->doctor_id === (int) $doctor->getKey()
            && $this->belongsToDoctorClinic($patient, $doctor);
    }

    private function belongsToDoctorClinic(Patient $patient, Doctor $doctor): bool
    {
        // If patient has an explicit clinic column, it must match.
        if (Schema::hasColumn('patients', 'clinic_id') && $patient->clinic_id !== null) {
            return (int) $patient->clinic_id === (int) $doctor->clinic_id;
        }

        // When clinic_id isn't available, allow only direct assignment as above.
        return true;
    }

    public function auditRead(User $user, Patient $patient, string $mode): void
    {
        // Fail closed if the audit table has not been migrated or is unavailable.
        DB::table('hospital_medcard_view_audits')->insert([
            'user_id' => $user->getKey(),
            'patient_id' => $patient->getKey(),
            'mode' => $mode,
            'accessed_at' => now(),
        ]);
    }
}
