<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class HospitalDepartmentAgeLimits
{
    /** Database fields are inclusive; old preset ranges are preserved by policy when both null. */
    public static function validate(array $values, string $profile): array
    {
        $min = $values['age_min_years'] ?? null;
        $max = $values['age_max_years'] ?? null;
        $min = ($min === '' || $min === null) ? null : (int) $min;
        $max = ($max === '' || $max === null) ? null : (int) $max;
        if ($min !== null && ($min < 0 || $min > 125)) {
            throw ValidationException::withMessages(['age_min_years' => 'Возраст должен быть от 0 до 125 лет.']);
        }
        if ($max !== null && ($max < 0 || $max > 125)) {
            throw ValidationException::withMessages(['age_max_years' => 'Возраст должен быть от 0 до 125 лет.']);
        }
        if ($min !== null && $max !== null && $min > $max) {
            throw ValidationException::withMessages(['age_max_years' => 'Максимальный возраст не может быть меньше минимального.']);
        }
        $preset = match (mb_strtolower(trim($profile))) {
            'детский', 'детская психиатрия' => [0, 7],
            'подростковый', 'подростковая психиатрия' => [8, 15],
            'взрослый', 'взрослая психиатрия' => [16, null],
            default => null,
        };
        // Предотвращаем расхождение с существующим обязательным возрастным пресетом.
        if ($preset !== null && ($min !== $preset[0] || $max !== $preset[1])) {
            throw ValidationException::withMessages([
                'age_min_years' => 'Для этого возрастного профиля установлены фиксированные границы: '
                    . $preset[0] . '–' . ($preset[1] ?? 'без верхней границы') . '.',
            ]);
        }
        return ['age_min_years' => $min, 'age_max_years' => $max];
    }
}
