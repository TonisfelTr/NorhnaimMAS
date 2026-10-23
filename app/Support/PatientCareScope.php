<?php

namespace App\Support;

use App\Models\HospitalBedAssignment;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Область текущего лечения для рабочих списков и выбора пациента.
 * Применяется явно: общая медицинская карта и история не получают global scope.
 */
final class PatientCareScope
{
    public static function activeAssignments(): Builder
    {
        return HospitalBedAssignment::query()
            ->whereNotNull('patient_id')
            ->whereNull('released_at')
            ->whereHas('bed', static function (Builder $bed): void {
                $bedClinicColumn = $bed->getModel()->qualifyColumn('clinic_id');

                $bed->whereHas('room', static function (Builder $room) use ($bedClinicColumn): void {
                    $room->whereColumn(
                        $room->getModel()->qualifyColumn('clinic_id'),
                        $bedClinicColumn
                    );
                });
            });
    }

    public static function forPatients(Builder $query, string $scope): Builder
    {
        return self::applyScope($query, $query->getModel()->qualifyColumn('id'), $scope);
    }

    public static function forRecords(Builder $query, string $scope): Builder
    {
        return self::applyScope($query, $query->getModel()->qualifyColumn('patient_id'), $scope);
    }

    public static function isCurrentlyInpatient(int $patientId): bool
    {
        return self::activeAssignments()->where('patient_id', $patientId)->exists();
    }

    private static function applyScope(Builder $query, string $patientColumn, string $scope): Builder
    {
        if ($scope === 'all') {
            return $query;
        }

        if (!in_array($scope, ['inpatient', 'outpatient'], true)) {
            throw new InvalidArgumentException('Unknown patient care scope.');
        }

        // NULL в подзапросе NOT IN мог бы исключить всех амбулаторных пациентов.
        // activeAssignments() всегда ограничен непустыми patient_id.
        $assignments = self::activeAssignments();
        $patientIds = $assignments->select(
            $assignments->getModel()->qualifyColumn('patient_id')
        );

        return $scope === 'inpatient'
            ? $query->whereIn($patientColumn, $patientIds)
            : $query->whereNotIn($patientColumn, $patientIds);
    }
}
