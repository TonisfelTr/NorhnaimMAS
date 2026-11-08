<?php

namespace App\Services;

/** Domain invariants, independent of HTTP, Eloquent, and the print layout. */
final class BenefitPrescriptionRules
{
    public static function normalizeNumber(string $value): string
    {
        return mb_strtoupper(trim($value), 'UTF-8');
    }

    // clinic-benefit-requisites:rules-setup-method:v1:begin
    /** @return array<string, string> */
    public static function setupErrors(array $profile): array
    {
        $errors = [];
        $text = static fn (mixed $value): string => is_scalar($value) ? (string) $value : '';

        if (!empty($profile['_settings_error'])) {
            $errors['settings'] = is_string($profile['_settings_error'])
                ? $profile['_settings_error']
                : 'Проверьте формат сохранённых настроек льготных рецептов.';
        }

        if (($profile['prescribing_enabled'] ?? false) !== true) {
            $errors['prescribing_enabled'] = 'Для клиники пока не включено оформление льготных рецептов.';
        }

        foreach ([
            'name' => 'Укажите наименование клиники для рецептурного бланка.',
            'address' => 'Укажите адрес клиники для рецептурного бланка.',
            'phone' => 'Укажите телефон для связи по рецептам.',
            'numbering_source' => 'Укажите источник нумерации льготных рецептов.',
        ] as $field => $message) {
            if (trim($text($profile[$field] ?? null)) === '') {
                $errors[$field] = $message;
            }
        }

        $kind = $profile['kind'] ?? 'organization';

        if (!in_array($kind, ['organization', 'individual'], true)) {
            $errors['kind'] = 'Выберите правовой тип: медицинская организация или индивидуальный предприниматель.';
        }

        $digits = $kind === 'individual' ? 15 : 13;

        if (preg_match('/\A[0-9]{' . $digits . '}\z/', $text($profile['ogrn'] ?? null)) !== 1) {
            $errors['ogrn'] = $kind === 'individual'
                ? 'Укажите ОГРНИП: ровно 15 цифр.'
                : 'Укажите ОГРН медицинской организации: ровно 13 цифр.';
        }

        if ($kind === 'individual') {
            foreach ([
                'license_number' => 'Укажите номер лицензии ИП.',
                'license_date' => 'Укажите дату лицензии ИП.',
                'license_authority' => 'Укажите орган, выдавший лицензию ИП.',
            ] as $field => $message) {
                if (trim($text($profile[$field] ?? null)) === '') {
                    $errors[$field] = $message;
                }
            }
        }

        $rawLicenseDate = $profile['license_date'] ?? null;
        $licenseDate = is_string($rawLicenseDate) ? $rawLicenseDate : '';

        if (($rawLicenseDate !== null && !is_string($rawLicenseDate)) || ($licenseDate !== '' && (
            preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $licenseDate, $dateParts) !== 1
            || !checkdate((int) $dateParts[2], (int) $dateParts[3], (int) $dateParts[1])
        ))) {
            $errors['license_date'] = 'Укажите существующую дату лицензии в формате ГГГГ-ММ-ДД.';
        }

        if (preg_match('/\A[0-9]{2}\z/', $text($profile['okato_prefix'] ?? null)) !== 1) {
            $errors['okato_prefix'] = 'Для клиники не настроены первые две цифры ОКАТО.';
        }

        return $errors;
    }
    // clinic-benefit-requisites:rules-setup-method:v1:end

    /** @return array<string, string> */
    public static function issueErrors(array $benefit, array $context): array
    {
        // clinic-benefit-requisites:rules-issue-setup:v1:begin
        $errors = [];
        $profile = $context['profile'] ?? [];
        $setupErrors = self::setupErrors($profile);

        if ($setupErrors !== []) {
            // Keep the existing string-valued validation error contract.
            $errors['benefit.setup'] = implode(' ', array_values($setupErrors));
        }

        $kind = $profile['kind'] ?? 'organization';
        $prefix = (string) ($profile['okato_prefix'] ?? '');

        if (preg_match('/\A[0-9]{2}\z/', $prefix) === 1
            && !str_starts_with((string) ($benefit['series'] ?? ''), $prefix)) {
            $errors['benefit.series'] = 'Серия должна начинаться с настроенных для клиники двух цифр ОКАТО: ' . $prefix . '.';
        }
        // clinic-benefit-requisites:rules-issue-setup:v1:end

        $program = $benefit['program'] ?? '';
        $funding = (int) ($benefit['funding'] ?? 0);
        $expectedFunding = ['federal_nsu' => 1, 'regional' => 2, 'municipal' => 3];
        if (isset($expectedFunding[$program]) && $funding !== $expectedFunding[$program]) {
            $errors['benefit.funding'] = 'Источник финансирования не соответствует выбранной программе.';
        }
        if ($program === 'federal_nsu' && !self::confirmed($benefit['nsu_medicines_retained'] ?? null)) {
            $errors['benefit.nsu_medicines_retained'] = 'Подтвердите сохранение лекарственной части НСУ на дату выписки.';
        }
        if ($program === 'federal_nsu' && (int) ($benefit['payment'] ?? 0) !== 1) {
            $errors['benefit.payment'] = 'Для лекарственного обеспечения по НСУ выберите «Бесплатно».';
        }
        if ((int) ($benefit['payment'] ?? 0) === 3) {
            $percent = $benefit['payment_percent'] ?? null;
            if (!is_numeric($percent) || (float) $percent <= 0 || (float) $percent >= 100
                || (float) $percent === 50.0) {
                $errors['benefit.payment_percent'] = 'Укажите иной процент оплаты пациентом от 0 до 100, кроме 0, 50 и 100.';
            }
        }

        $companion = $benefit['companion_form'] ?? 'none';
        $days = (int) ($benefit['validity_days'] ?? 0);
        if ($kind === 'individual' && $companion !== 'none') {
            $errors['benefit.companion_form'] = 'Для ИП этот модуль поддерживает только препараты без дополнительного специального рецепта. Для остальных необходима отдельная проверка категории препарата и полномочий.';
        }
        if (($context['drug_strict'] ?? false) && !in_array($companion, ['148_88', '107_np'], true)) {
            $errors['benefit.companion_form'] = 'Для выбранного препарата требуется дополнительный специальный рецепт. Укажите его форму и реквизиты.';
        }
        if ($companion !== 'none') {
            if (trim((string) ($benefit['companion_reference'] ?? '')) === '') {
                $errors['benefit.companion_reference'] = 'Укажите реквизиты оформленного дополнительного рецепта.';
            }
            if (!self::confirmed($benefit['companion_confirmed'] ?? null)) {
                $errors['benefit.companion_confirmed'] = 'Подтвердите, что дополнительный рецепт оформлен.';
            }
            if ($days !== 15 || ($benefit['validity_basis'] ?? '') !== 'controlled') {
                $errors['benefit.validity_days'] = 'Для этого сочетания рецептов установите срок 15 дней и основание «Специальный учёт».';
            }
        } elseif ($days === 15 || ($benefit['validity_basis'] ?? '') === 'controlled') {
            $errors['benefit.companion_form'] = 'Для срока 15 дней укажите дополнительный рецепт, предусмотренный для препарата.';
        }

        $basis = $benefit['validity_basis'] ?? '';
        if ($days === 30 && $basis !== 'standard') {
            $errors['benefit.validity_basis'] = 'Для срока 30 дней выберите обычный срок действия.';
        }
        if ($days === 90) {
            if (!in_array($basis, ['disability_i', 'disabled_child', 'pension_age', 'chronic_long'], true)) {
                $errors['benefit.validity_basis'] = 'Для срока 90 дней необходимо отдельное основание.';
            }
            if (!self::confirmed($benefit['extended_validity_confirmed'] ?? null)) {
                $errors['benefit.extended_validity_confirmed'] = 'Подтвердите основание срока действия 90 дней.';
            }
            if ($basis === 'disability_i' && (($context['disability_group'] ?? '') !== 'I'
                || !($context['disability_current'] ?? false))) {
                $errors['benefit.validity_basis'] = 'В карте нет актуальных сведений об I группе. Уточните сведения или выберите другое действующее основание.';
            }
            if ($basis === 'disabled_child' && (($context['disability_group'] ?? '') !== 'child'
                || !($context['disability_current'] ?? false) || !($context['is_minor'] ?? false))) {
                $errors['benefit.validity_basis'] = 'В карте нет актуальных сведений о категории «ребёнок-инвалид» для несовершеннолетнего пациента.';
            }
        }
        if (!($context['patient_birth_date'] ?? null) || !($context['patient_name'] ?? null)) {
            $errors['benefit.patient'] = 'Заполните ФИО и корректную дату рождения в медицинской карте.';
        }
        if (!($context['doctor_name'] ?? null)) {
            $errors['benefit.doctor'] = 'В карточке врача отсутствуют необходимые ФИО.';
        }
        return $errors;
    }

    public static function confirmed(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'yes', 'on', 'true'], true);
    }
}
