<?php

namespace App\Support;

use App\Models\HospitalDepartment;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class HospitalDepartmentEligibility
{
    public const PEDIATRIC_AGE_LIMIT_EXCLUSIVE = 8;

    private const AGE_GROUPS = [
        'children' => [
            'group' => 'children',
            'label' => 'Детское отделение: 0–7 лет',
            'min_age_inclusive' => 0,
            'max_age_exclusive' => self::PEDIATRIC_AGE_LIMIT_EXCLUSIVE,
        ],
        'adolescents' => [
            'group' => 'adolescents',
            'label' => 'Подростковое отделение: 8–14 лет',
            'min_age_inclusive' => 8,
            'max_age_exclusive' => 15,
        ],
        'adults' => [
            'group' => 'adults',
            'label' => 'Взрослое отделение: с 15 лет',
            'min_age_inclusive' => 15,
            'max_age_exclusive' => null,
        ],
    ];

    public static function ageGroup(?HospitalDepartment $department): ?string
    {
        if (!$department) {
            return null;
        }

        $explicitGroup = $department->getAttributes()['patient_age_group'] ?? null;
        if (in_array($explicitGroup, ['children', 'adolescents', 'adults'], true)) {
            return $explicitGroup;
        }
        if ($explicitGroup === 'general') {
            return null;
        }

        // NULL и отсутствующее поле сохраняют правило существующих отделений.
        // Неизвестное старое значение тоже разбираем по name/profile; новые
        // значения этого поля ограничены enum-валидацией в форме настроек.
        $label = mb_strtolower(trim(implode(' ', array_filter([
            $department->name ?? null,
            $department->profile ?? null,
        ]))));

        // Более конкретное «подростковое» имеет приоритет и в смешанном
        // названии вроде «детско-подростковое отделение».
        foreach ([
            'adolescents' => ['подростк', 'adolescent', 'teen', 'youth'],
            'children' => ['детск', 'педиатр', 'child', 'pediatric', 'paediatric'],
            'adults' => ['взросл', 'adult'],
        ] as $group => $markers) {
            foreach ($markers as $marker) {
                if (mb_strpos($label, $marker) !== false) {
                    return $group;
                }
            }
        }

        return null;
    }

    public static function restrictions(?HospitalDepartment $department): ?array
    {
        $group = self::ageGroup($department);

        return $group === null ? null : self::AGE_GROUPS[$group];
    }

    public static function isAgeRestricted(?HospitalDepartment $department): bool
    {
        return self::ageGroup($department) !== null;
    }

    public static function isPediatric(?HospitalDepartment $department): bool
    {
        return self::ageGroup($department) === 'children';
    }

    public static function violation(Patient $patient, ?HospitalDepartment $department): ?string
    {
        $restrictions = self::restrictions($department);
        if ($restrictions === null) {
            return null;
        }

        $departmentKind = match ($restrictions['group']) {
            'children' => 'детском',
            'adolescents' => 'подростковом',
            'adults' => 'взрослом',
        };

        // Читаем исходное значение: ошибочная дата в старой карточке не должна
        // вызвать исключение Eloquent date cast вместо понятной ошибки возраста.
        $rawBirthDate = $patient->getAttributes()['birth_at'] ?? null;
        if ($rawBirthDate === null || $rawBirthDate === '') {
            return "У пациента не указана дата рождения. Для размещения в {$departmentKind} отделении возраст должен быть подтверждён.";
        }

        $birthDate = self::calendarBirthDate($rawBirthDate);
        if ($birthDate === null) {
            return "У пациента некорректная дата рождения. Проверьте её перед размещением в {$departmentKind} отделении.";
        }

        [$oldestBirthDate, $youngestBirthDate, $today] = self::birthDateBounds($restrictions);
        if ($birthDate > $today) {
            return "Дата рождения пациента указана в будущем. Проверьте её перед размещением в {$departmentKind} отделении.";
        }

        if (($oldestBirthDate !== null && $birthDate <= $oldestBirthDate) || $birthDate > $youngestBirthDate) {
            return match ($restrictions['group']) {
                'children' => 'В детское отделение можно разместить только ребёнка младше 8 лет (0–7 полных лет).',
                'adolescents' => 'В подростковое отделение можно разместить только пациента от 8 до 14 полных лет (до 15-летия).',
                'adults' => 'Во взрослое отделение можно разместить только пациента с 15 полных лет.',
            };
        }

        return null;
    }

    public static function assertEligible(Patient $patient, ?HospitalDepartment $department): void
    {
        $violation = self::violation($patient, $department);
        if ($violation !== null) {
            throw ValidationException::withMessages(['patient_id' => $violation]);
        }
    }

    public static function applyToPatients(Builder $query, ?HospitalDepartment $department): Builder
    {
        $restrictions = self::restrictions($department);
        if ($restrictions === null) {
            return $query;
        }

        [$oldestBirthDate, $youngestBirthDate] = self::birthDateBounds($restrictions);
        $column = $query->getModel()->qualifyColumn('birth_at');

        $query->whereNotNull($column);
        if ($oldestBirthDate !== null) {
            $query->whereDate($column, '>', $oldestBirthDate);
        }

        return $query->whereDate($column, '<=', $youngestBirthDate);
    }

    private static function birthDateBounds(array $restrictions): array
    {
        $today = CarbonImmutable::today();

        return [
            $restrictions['max_age_exclusive'] !== null
                ? $today->subYearsNoOverflow($restrictions['max_age_exclusive'])->toDateString()
                : null,
            $today->subYearsNoOverflow($restrictions['min_age_inclusive'])->toDateString(),
            $today->toDateString(),
        ];
    }

    private static function calendarBirthDate($value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        // birth_at хранится как DATE; также принимаем стандартную сериализацию
        // даты Eloquent в timestamp, проверяя календарную дату без нормализации.
        if (!preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})(?:[T ](?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:\.\d+)?(?:Z|[+-](?:[01]\d|2[0-3])(?::?[0-5]\d)?)?)?$/D',
            $value,
            $parts
        ) || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return null;
        }

        return $parts[1] . '-' . $parts[2] . '-' . $parts[3];
    }
}
