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
 * A doctor sees their inpatient clinic, with patient rows limited to assigned departments.
 * The bootstrap debug account can inspect inpatient clinics without a separate access table.
 * This temporary development exception is disabled when APP_DEBUG=false.
 */
class HospitalPatientAccess
{
    /**
     * The project's bootstrap administrator is a developer-only account.
     * A generic "admins" role is NOT sufficient to see patients in production.
     */
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
        return $this->isDebugAdmin($user);
    }

    public function canRead(User $user, Patient $patient): bool
    {
        // Only the debug bootstrap account can read inpatient records as an admin.
        if ($user->hasRole('admins')) {
            if (!$this->isDebugAdmin($user)) {
                return false;
            }

            $clinics = $this->adminClinicIds($user);
            $departments = HospitalDepartment::query()
                ->whereIn('clinic_id', $clinics)
                ->where('enabled', true)
                ->pluck('id')->map(static fn ($id): int => (int) $id)->all();

            return $this->activeAssignments($clinics, $departments)
                ->where('patient_id', $patient->getKey())->exists();
        }

        if (!$user->hasRole('doctors')) {
            return false;
        }

        $doctor = $user->doctor()->with('clinic')->first();
        if (!$doctor instanceof Doctor || !$doctor->clinic) {
            return false;
        }

        // Existing outpatient attachment, additionally restricted to the doctor's clinic.
        if ((int) $patient->doctor_id === (int) $doctor->getKey()
            && $this->belongsToDoctorClinic($patient, $doctor)) {
            return true;
        }

        $departments = $this->doctorDepartmentIds($doctor);

        return $this->activeAssignments([(int) $doctor->clinic_id], $departments)
            ->where('patient_id', $patient->getKey())->exists();
    }

    /**
     * Deliberately preserves the old production edit rule, without the debug bypass:
     * medcard demographic edits only for the patient's directly assigned doctor.
     * Inpatient assignment does not automatically grant writing permission.
     */
    public function canEdit(User $user, Patient $patient): bool
    {
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
