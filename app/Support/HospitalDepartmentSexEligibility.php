<?php

namespace App\Support;

use App\Models\HospitalDepartment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Проверка пола при размещении пациента в отделении.
 * Возрастные требования проверяет существующий HospitalDepartmentEligibility.
 * Никакие данные пациента или настройки отделения здесь не изменяются.
 */
final class HospitalDepartmentSexEligibility
{
    private const MALE_VALUES = ['M', 'MALE', 'М', 'МУЖ', 'МУЖСКОЙ', 'МУЖЧИНА'];
    private const FEMALE_VALUES = ['F', 'FEMALE', 'Ж', 'ЖЕН', 'ЖЕНСКИЙ', 'ЖЕНЩИНА'];

    private static function requirement(?HospitalDepartment $department): ?string
    {
        if ($department === null) {
            return null;
        }

        $type = mb_strtolower(trim((string) ($department->sex_type ?? 'mixed')));

        // Значение по умолчанию старых отделений — без ограничения пола.
        return $type === '' ? 'mixed' : $type;
    }

    public static function label(?HospitalDepartment $department): ?string
    {
        return match (self::requirement($department)) {
            'male' => 'Только мужчины',
            'female' => 'Только женщины',
            'mixed' => 'Смешанное',
            default => null,
        };
    }

    private static function patientSex(Patient $patient): ?string
    {
        // Фактическое поле в таблице patients — gender (M/F).
        $value = mb_strtoupper(trim((string) ($patient->gender ?? $patient->sex ?? '')));

        return match (true) {
            in_array($value, self::MALE_VALUES, true) => 'male',
            in_array($value, self::FEMALE_VALUES, true) => 'female',
            default => null,
        };
    }

    public static function violation(Patient $patient, ?HospitalDepartment $department): ?string
    {
        $requirement = self::requirement($department);
        if ($requirement === null || $requirement === 'mixed') {
            return null;
        }

        if (!in_array($requirement, ['male', 'female'], true)) {
            return 'В отделении указан неизвестный тип размещения. Проверьте настройки отделения.';
        }

        $sex = self::patientSex($patient);
        if ($sex === null) {
            return 'У пациента не указан или не распознан пол. Для поступления в мужское или женское отделение проверьте данные пациента.';
        }

        if ($sex !== $requirement) {
            return $requirement === 'male'
                ? 'В это отделение разрешено поступление только мужчин. Для выбранного пациента указано несоответствие по полу.'
                : 'В это отделение разрешено поступление только женщин. Для выбранного пациента указано несоответствие по полу.';
        }

        return null;
    }

    public static function assertEligible(
        Patient $patient,
        HospitalDepartment $department,
        string $field = 'department_id'
    ): void {
        $violation = self::violation($patient, $department);
        if ($violation !== null) {
            throw ValidationException::withMessages([$field => $violation]);
        }
    }

    /**
     * Отфильтровывает неподходящих пациентов из поиска для выбранного отделения.
     * Повторная серверная проверка непосредственно перед записью ОБЯЗАТЕЛЬНА.
     */
    public static function applyToPatients(Builder $query, ?HospitalDepartment $department): Builder
    {
        $type = self::requirement($department);
        if ($type === null || $type === 'mixed') {
            return $query;
        }

        $allowed = match ($type) {
            'male' => self::MALE_VALUES,
            'female' => self::FEMALE_VALUES,
            default => [],
        };

        if ($allowed === []) {
            return $query->whereRaw('1 = 0');
        }

        // PostgreSQL, которое уже использует приложение. Параметры передаются
        // через bindings, пользовательские строки в SQL не подставляем.
        $bindings = implode(', ', array_fill(0, count($allowed), '?'));
        return $query->whereRaw(
            'UPPER(TRIM(patients.gender)) IN (' . $bindings . ')',
            $allowed
        );
    }
}
