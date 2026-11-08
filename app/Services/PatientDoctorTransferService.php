<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Support\HospitalPatientAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Передача лечащего врача только внутри текущей клиники.
 * Права доступа не берутся из формы — каждый запрос проверяется на сервере.
 */
final class PatientDoctorTransferService
{
    public function __construct(private readonly HospitalPatientAccess $access)
    {
    }

    public function clinicId(Patient $patient): ?int
    {
        $patient->loadMissing('doctor');

        $clinicId = (int) ($patient->doctor?->clinic_id ?? 0);

        // Нельзя считать двух частнопрактикующих врачей одной клиникой по NULL.
        if ($clinicId < 1) {
            return null;
        }

        // Если у пациента есть свой clinic_id, не позволяем перейти мимо него.
        $patientClinicId = (int) ($patient->clinic_id ?? 0);
        if ($patientClinicId > 0 && $patientClinicId !== $clinicId) {
            return null;
        }

        return $clinicId;
    }

    public function canTransfer(User $user, Patient $patient): bool
    {
        // Авторизованные администраторы — только если existing access разрешил редактирование.
        if ($user->hasAnyRole(['admins', 'clinic_admin'])) {
            return true;
        }
        
        if ($this->clinicId($patient) === null) {
            return false;
        }

        // Тот же контроль доступа, что и у медицинской карты.
        if (! $this->access->canRead($user, $patient) || ! $this->access->canEdit($user, $patient)) {
            return false;
        }

        $fromDoctor = $patient->doctor;
        if ($fromDoctor && (int) $fromDoctor->user_id === (int) $user->getKey()) {
            return true;
        }

        // Явное разрешение: дополнительно проверяем привязку врача к той же клинике.
        if (! $user->can('patients.transfer')) {
            return false;
        }

        return DB::table('doctors')
            ->where('user_id', $user->getKey())
            ->where('clinic_id', $this->clinicId($patient))
            ->exists();
    }

    /** @return Builder<Doctor> */
    public function eligibleDoctors(Patient $patient): Builder
    {
        $clinicId = $this->clinicId($patient);

        // Даже если этот метод ошибочно вызовут без проверки прав,
        // отсутствие определённой клиники не отдаст врачей из других организаций.
        return Doctor::query()
            ->where('doctors.clinic_id', $clinicId ?? -1)
            ->where('doctors.id', '<>', (int) ($patient->doctor_id ?? 0))
            ->whereNotNull('doctors.user_id')
            // Профиль должен принадлежать существующему пользователю с ролью doctors.
            // Это исключает бывших сотрудников, у которых роль врача снята.
            ->whereIn('doctors.user_id', User::query()->role('doctors')->select('users.id'));
    }

    /** @return Collection<int, array{id:int,text:string,profession:string}> */
    public function searchDoctors(Patient $patient, string $search = '', ?int $selectedId = null): Collection
    {
        $query = $this->eligibleDoctors($patient);
        $term = trim($search);

        if ($term !== '') {
            // Проект работает на PostgreSQL. Параметр передаётся binding'ом.
            $query->whereRaw(
                "concat_ws(' ', doctors.surname, doctors.name, doctors.patronym, doctors.profession) ILIKE ?",
                ['%' . $term . '%']
            );
        }

        $items = $query
            ->orderBy('doctors.surname')
            ->orderBy('doctors.name')
            ->limit(40)
            ->get(['doctors.id', 'doctors.surname', 'doctors.name', 'doctors.patronym', 'doctors.profession']);

        if ($selectedId && ! $items->contains('id', $selectedId)) {
            $selected = $this->eligibleDoctors($patient)->find($selectedId);
            if ($selected) {
                $items->prepend($selected);
            }
        }

        return $items->map(static function (Doctor $doctor): array {
            $fullName = trim(implode(' ', array_filter([
                $doctor->surname,
                $doctor->name,
                $doctor->patronym,
            ], static fn ($value): bool => filled($value))));

            return [
                'id' => (int) $doctor->getKey(),
                'text' => $fullName,
                'profession' => (string) ($doctor->profession ?? ''),
            ];
        })->values();
    }
}
