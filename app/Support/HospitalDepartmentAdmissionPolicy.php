<?php

namespace App\Support;

use App\Models\HospitalDepartment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Единая точка проверки допустимости госпитализации в отделение.
 *
 * Возраст: существующие правила + явно определённые возрастные профили.
 * Пол: HospitalDepartmentSexEligibility.
 * Клинический профиль (наркология, психосоматика, геронтология и т.д.) —
 * классификация учреждения, НЕ автоматический диагноз или разрешение на лечение.
 *
 * Вызывать И ПРИ ПОИСКЕ, И НЕПОСРЕДСТВЕННО ПЕРЕД ЗАПИСЬЮ в БД.
 */
final class HospitalDepartmentAdmissionPolicy
{
    private const AGE_PRESETS = [
        'children' => [
            'group' => 'children',
            'label' => 'Детское: 0–7 лет',
            'min_age_inclusive' => 0,
            'max_age_exclusive' => 8,
        ],
        'adolescents' => [
            'group' => 'adolescents',
            'label' => 'Подростковое: 8–15 лет',
            'min_age_inclusive' => 8,
            'max_age_exclusive' => 16,
        ],
        'adults' => [
            'group' => 'adults',
            'label' => 'Взрослое: с 16 лет',
            'min_age_inclusive' => 16,
            'max_age_exclusive' => null,
        ],
    ];

    /**
     * Возрастной профиль включается только явной записью профиля.
     * Старые/произвольные названия остаются под управлением старого
     * HospitalDepartmentEligibility (не меняем их правила молча).
     */
    public static function agePreset(?HospitalDepartment $department): ?array
    {
        if ($department === null) {
            return null;
        }

        $profile = mb_strtolower(trim((string) ($department->profile ?? '')));
        $group = match ($profile) {
            'детский', 'детская психиатрия' => 'children',
            'подростковый', 'подростковая психиатрия' => 'adolescents',
            'взрослый', 'взрослая психиатрия' => 'adults',
            default => null,
        };

        return $group !== null ? self::AGE_PRESETS[$group] : null;
    }

    public static function profileCategory(?HospitalDepartment $department): ?string
    {
        if ($department === null) {
            return null;
        }
        $profile = mb_strtolower(trim((string) ($department->profile ?? '')));

        return match ($profile) {
            'детский', 'детская психиатрия' => 'children',
            'подростковый', 'подростковая психиатрия' => 'adolescents',
            'взрослый', 'взрослая психиатрия' => 'adults',
            'геронтологический' => 'gerontological',
            'психосоматический' => 'psychosomatic',
            'наркологический' => 'narcological',
            default => $profile === '' ? null : 'custom',
        };
    }

    /**
     * Возрастной диапазон для интерфейса; существующие правила остаются
     * в силе и применяются дополнительно к явному возрастному профилю.
     */
    public static function restrictions(?HospitalDepartment $department): ?array
    {
        $custom = self::explicitAgeLimits($department);
        return $custom ?? self::agePreset($department)
            ?? HospitalDepartmentEligibility::restrictions($department);
    }

    /** Explicit limits are inclusive; null means an unbounded side. */
    public static function explicitAgeLimits(?HospitalDepartment $department): ?array
    {
        if ($department === null) return null;
        $min = $department->age_min_years;
        $max = $department->age_max_years;
        if ($min === null && $max === null) return null;
        $label = 'Возраст: ' . ($min === null ? 'без нижней границы' : 'от ' . $min)
            . ' до ' . ($max === null ? 'без верхней границы' : $max . ' лет включительно');
        return [
            'group' => 'custom',
            'label' => $label,
            'min_age_inclusive' => $min === null ? 0 : (int) $min,
            'max_age_exclusive' => $max === null ? null : (int) $max + 1,
        ];
    }

    public static function sexLabel(?HospitalDepartment $department): ?string
    {
        return HospitalDepartmentSexEligibility::label($department);
    }

    public static function violation(Patient $patient, ?HospitalDepartment $department): ?string
    {
        if ($department === null) {
            return 'Отделение не выбрано.';
        }

        // Старые возрастные ограничения не отключаются.
        $existingAgeViolation = HospitalDepartmentEligibility::violation($patient, $department);
        if ($existingAgeViolation !== null) {
            return $existingAgeViolation;
        }

        $preset = self::explicitAgeLimits($department) ?? self::agePreset($department);
        if ($preset !== null) {
            $age = self::patientAge($patient);
            if ($age === null) {
                return 'Дата рождения пациента отсутствует или некорректна. Уточните её перед госпитализацией.';
            }
            $min = (int) $preset['min_age_inclusive'];
            $max = $preset['max_age_exclusive'];
            if ($age < $min || ($max !== null && $age >= (int) $max)) {
                return 'Возраст пациента (' . $age . ' лет) не соответствует отделению: ' . $preset['label'] . '.';
            }
        }

        return HospitalDepartmentSexEligibility::violation($patient, $department);
    }

    public static function assertEligible(
        Patient $patient,
        HospitalDepartment $department,
        string $field = 'department_id'
    ): void {
        $error = self::violation($patient, $department);
        if ($error !== null) {
            throw ValidationException::withMessages([$field => $error]);
        }
    }

    /**
     * Сужает список поиска пациента. Не заменяет проверку перед INSERT/UPDATE.
     */
    public static function applyToPatients(Builder $query, ?HospitalDepartment $department): Builder
    {
        $query = HospitalDepartmentEligibility::applyToPatients($query, $department);
        $query = HospitalDepartmentSexEligibility::applyToPatients($query, $department);

        $preset = self::explicitAgeLimits($department) ?? self::agePreset($department);
        if ($preset === null) {
            return $query;
        }

        $today = Carbon::today();
        $min = (int) $preset['min_age_inclusive'];
        $max = $preset['max_age_exclusive'];

        // Возрастное окно вычисляется по полной дате рождения, не по году.
        $query->whereNotNull('patients.birth_at')
            ->whereDate('patients.birth_at', '<=', $today->toDateString());
        if ($min > 0) {
            $query->whereDate(
                'patients.birth_at', '<=',
                $today->copy()->subYearsNoOverflow($min)->toDateString()
            );
        }
        if ($max !== null) {
            $query->whereDate(
                'patients.birth_at', '>',
                $today->copy()->subYearsNoOverflow((int) $max)->toDateString()
            );
        }

        return $query;
    }

    private static function patientAge(Patient $patient): ?int
    {
        try {
            // Patient::getBirthAtAttribute() может выдавать d.m.Y,
            // поэтому используем ИСХОДНОЕ значение даты из БД.
            $raw = $patient->getRawOriginal('birth_at');
            if (!$raw) {
                return null;
            }
            $birth = Carbon::parse($raw);
            if ($birth->isFuture()) {
                return null;
            }
            return (int) $birth->age;
        } catch (Throwable) {
            return null;
        }
    }
}
