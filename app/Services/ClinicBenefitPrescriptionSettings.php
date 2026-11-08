<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Validation\ValidationException;

final class ClinicBenefitPrescriptionSettings
{
    private const ATTRIBUTE = 'benefit_prescription_settings';

    private const FIELDS = [
        'prescribing_enabled', 'kind', 'ogrn', 'code', 'series',
        'numbering_source', 'license_number', 'license_date', 'license_authority',
    ];

    public static function profile(Clinic $clinic): array
    {
        $clinicId = $clinic->getKey();
        $legacy = $clinicId ? config('benefit_prescriptions.clinics.' . $clinicId, []) : [];
        $profile = is_array($legacy) ? $legacy : [];
        $attributes = $clinic->getAttributes();
        $settings = self::decodeSettings($attributes[self::ATTRIBUTE] ?? null);
        $settingsError = null;

        if ($settings === null) {
            $settingsError = 'Сохранённые настройки льготных рецептов повреждены: ожидается JSON-объект. Оформление отключено.';
        } else {
            foreach (self::FIELDS as $field) {
                if (!array_key_exists($field, $settings)) {
                    continue;
                }

                if ($field === 'prescribing_enabled') {
                    $enabled = self::booleanValue($settings[$field]);
                    $profile[$field] = $enabled ?? false;

                    if ($enabled === null) {
                        $settingsError = 'Сохранённые настройки содержат некорректное значение разрешения оформления льготных рецептов. Оформление отключено.';
                    }
                } elseif ($settings[$field] === null || is_string($settings[$field])) {
                    $profile[$field] = $settings[$field] ?? '';
                } else {
                    $profile[$field] = '';
                    $settingsError = 'Сохранённые реквизиты льготных рецептов содержат значение неверного типа. Оформление отключено.';
                }
            }
        }

        // Existing clinic fields are the source for the printed contact details.
        foreach (['name', 'address'] as $field) {
            $value = self::nonEmptyText($attributes[$field] ?? null);

            if ($value !== null) {
                $profile[$field] = $value;
            }
        }

        $phone = self::firstPhone($attributes['phones'] ?? null)
            ?? self::nonEmptyText($attributes['phone'] ?? null);

        if ($phone !== null) {
            $profile['phone'] = $phone;
        }

        // NULL preserves the legacy setting; an empty string explicitly clears it.
        if (array_key_exists('benefit_okato_prefix', $attributes)
            && $attributes['benefit_okato_prefix'] !== null) {
            $profile['okato_prefix'] = is_string($attributes['benefit_okato_prefix'])
                ? $attributes['benefit_okato_prefix']
                : '';
        }

        if ($settingsError !== null) {
            $profile['prescribing_enabled'] = false;
            $profile['_settings_error'] = $settingsError;
        }

        $prefix = is_string($profile['okato_prefix'] ?? null) ? $profile['okato_prefix'] : '';
        $series = is_string($profile['series'] ?? null) ? $profile['series'] : '';

        // A stored suggestion must match the current prefix. Do not invent a series.
        if ($series !== '' && (
            preg_match('/\A[0-9]{2}\z/', $prefix) !== 1
            || !str_starts_with($series, $prefix)
        )) {
            $profile['series'] = '';
        }

        return $profile;
    }

    public static function store(Clinic $clinic, array $validated): void
    {
        if (!array_key_exists('benefit_clinic', $validated)) {
            return;
        }

        $submitted = $validated['benefit_clinic'];

        if (!is_array($submitted)) {
            throw ValidationException::withMessages([
                'benefit_clinic' => 'Проверьте формат настроек льготных рецептов.',
            ]);
        }

        $updates = [];

        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $submitted)) {
                continue;
            }

            if ($field === 'prescribing_enabled') {
                $enabled = self::booleanValue($submitted[$field]);

                if ($enabled === null) {
                    throw ValidationException::withMessages([
                        'benefit_clinic.prescribing_enabled' => 'Выберите, разрешено ли оформление льготных рецептов.',
                    ]);
                }

                $updates[$field] = $enabled;
                continue;
            }

            if ($submitted[$field] !== null && !is_string($submitted[$field])) {
                throw ValidationException::withMessages([
                    'benefit_clinic.' . $field => 'Реквизит должен быть строкой.',
                ]);
            }

            $value = $submitted[$field] ?? '';
            $updates[$field] = $field === 'series' && $value !== ''
                ? BenefitPrescriptionRules::normalizeNumber($value)
                : $value;
        }

        if ($updates === []) {
            return;
        }

        $attributes = $clinic->getAttributes();
        $settings = self::decodeSettings($attributes[self::ATTRIBUTE] ?? null);

        if ($settings === null) {
            throw ValidationException::withMessages([
                'benefit_clinic' => 'Сохранённые настройки льготных рецептов повреждены. Обратитесь к ответственному за сопровождение МИС: автоматическая перезапись отключена.',
            ]);
        }

        try {
            $encoded = json_encode((object) array_replace($settings, $updates),
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([
                'benefit_clinic' => 'Не удалось сохранить реквизиты в формате JSON. Проверьте введённый текст.',
            ]);
        }

        // This column intentionally needs no model cast. The caller saves its clinic.
        $clinic->setAttribute(self::ATTRIBUTE, $encoded);
    }

    private static function booleanValue(mixed $value): ?bool
    {
        if (in_array($value, [true, 1, '1'], true)) {
            return true;
        }

        return in_array($value, [false, 0, '0'], true) ? false : null;
    }

    /** NULL is an unconfigured profile; malformed JSON and JSON lists fail closed. */
    private static function decodeSettings(mixed $raw): ?array
    {
        if ($raw === null) {
            return [];
        }

        if (is_array($raw)) {
            return $raw === [] || !array_is_list($raw) ? $raw : null;
        }

        if (!is_string($raw)) {
            return null;
        }

        try {
            $decoded = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return $decoded instanceof \stdClass ? get_object_vars($decoded) : null;
    }

    private static function nonEmptyText(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private static function firstPhone(mixed $phones): ?string
    {
        if (is_string($phones)) {
            try {
                $phones = json_decode($phones, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return null;
            }
        }

        if (!is_array($phones)) {
            return null;
        }

        foreach ($phones as $item) {
            if (!is_array($item)) {
                continue;
            }

            $phone = self::nonEmptyText($item['phone'] ?? null);

            if ($phone !== null) {
                return $phone;
            }
        }

        return null;
    }
}
