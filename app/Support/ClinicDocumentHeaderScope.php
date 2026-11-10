<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use LogicException;

final class ClinicDocumentHeaderScope
{
    public static function currentClinicForUser(?User $user): ?Clinic
    {
        if (!$user) {
            return null;
        }

        $doctor = method_exists($user, 'doctor')
            ? $user->doctor()->with('clinic')->first()
            : null;

        if ($doctor?->clinic) {
            return $doctor->clinic;
        }

        $nurse = method_exists($user, 'nurse')
            ? $user->nurse()->with('clinic')->first()
            : null;

        return $nurse?->clinic;
    }

    public static function root(Clinic $clinic): Clinic
    {
        if (
            !Schema::hasColumn('clinics', 'parent_clinic_id')
            || !$clinic->getAttribute('parent_clinic_id')
        ) {
            return $clinic;
        }

        $current = $clinic;
        $seen = [];

        for ($step = 0; $step < 32; $step++) {
            $id = (int) $current->getKey();

            if (isset($seen[$id])) {
                throw new LogicException(
                    'В иерархии clinics.parent_clinic_id обнаружен цикл.'
                );
            }

            $seen[$id] = true;

            $parentId = (int) ($current->getAttribute('parent_clinic_id') ?? 0);

            if ($parentId <= 0) {
                return $current;
            }

            $parent = Clinic::query()
                ->whereKey($parentId)
                ->whereNull('deleted_at')
                ->first();

            if (!$parent) {
                return $current;
            }

            $current = $parent;
        }

        throw new LogicException(
            'Слишком глубокая иерархия clinics.parent_clinic_id.'
        );
    }

    public static function patientClinic(Patient $patient): ?Clinic
    {
        if (
            Schema::hasColumn('patients', 'clinic_id')
            && (int) $patient->getAttribute('clinic_id') > 0
        ) {
            $clinic = Clinic::query()
                ->whereKey((int) $patient->getAttribute('clinic_id'))
                ->whereNull('deleted_at')
                ->first();

            if ($clinic) {
                return $clinic;
            }
        }

        if (
            Schema::hasTable('registry_records')
            && Schema::hasColumn('registry_records', 'clinic_id')
        ) {
            $clinicId = \App\Models\RegistryRecord::query()
                ->withTrashed()
                ->where('patient_id', $patient->getKey())
                ->whereNotNull('clinic_id')
                ->orderByDesc('for_datetime')
                ->orderByDesc('id')
                ->value('clinic_id');

            if ((int) $clinicId > 0) {
                $clinic = Clinic::query()
                    ->whereKey((int) $clinicId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($clinic) {
                    return $clinic;
                }
            }
        }

        $doctor = $patient->doctor()->first(['id', 'clinic_id']);

        if ($doctor && (int) ($doctor->clinic_id ?? 0) > 0) {
            return Clinic::query()
                ->whereKey((int) $doctor->clinic_id)
                ->whereNull('deleted_at')
                ->first();
        }

        return null;
    }

    public static function rootForPatient(Patient $patient): ?Clinic
    {
        $clinic = self::patientClinic($patient);

        return $clinic ? self::root($clinic) : null;
    }

    /**
     * Все учреждения, наследующие настройки от одной головной клиники.
     *
     * @return Collection<int,Clinic>
     */
    public static function family(Clinic $root): Collection
    {
        $columns = ['id', 'name', 'clinic_type'];

        if (Schema::hasColumn('clinics', 'parent_clinic_id')) {
            $columns[] = 'parent_clinic_id';
        }

        $all = Clinic::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get($columns);

        if (!Schema::hasColumn('clinics', 'parent_clinic_id')) {
            return $all
                ->where('id', $root->getKey())
                ->values();
        }

        $ids = [(int) $root->getKey() => true];
        $changed = true;

        while ($changed) {
            $changed = false;

            foreach ($all as $clinic) {
                $clinicId = (int) $clinic->getKey();
                $parentId = (int) ($clinic->parent_clinic_id ?? 0);

                if (
                    $parentId > 0
                    && isset($ids[$parentId])
                    && !isset($ids[$clinicId])
                ) {
                    $ids[$clinicId] = true;
                    $changed = true;
                }
            }
        }

        return $all
            ->filter(
                static fn (Clinic $clinic): bool =>
                    isset($ids[(int) $clinic->getKey()])
            )
            ->values();
    }
}
