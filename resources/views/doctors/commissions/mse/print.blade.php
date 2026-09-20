<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->document_number ?: 'Направление на МСЭ' }} — форма 088/у</title>
    <style>
        * { box-sizing: border-box; }
        html { background: #edf1f3; }
        body { margin: 0; color: #111; background: #edf1f3; font-family: "Times New Roman", Times, serif; font-size: 11.2pt; line-height: 1.27; }
        .toolbar { width: 210mm; display: flex; justify-content: flex-end; gap: 8px; margin: 14px auto 0; font-family: Arial, sans-serif; }
        .toolbar button { color: #fff; background: #087f8e; border: 0; border-radius: 8px; padding: 10px 17px; font-weight: 700; cursor: pointer; }
        .sheet { width: 210mm; min-height: 297mm; margin: 12px auto; padding: 13mm 14mm 15mm; background: #fff; box-shadow: 0 5px 24px rgba(0,0,0,.12); page-break-after: always; }
        .sheet:last-child { page-break-after: auto; }
        .approval { width: 94mm; margin-left: auto; margin-right: 0; font-size: 9.2pt; line-height: 1.2; text-align: left; }
        .approval__title { margin-bottom: 2px; font-weight: 700; }
        .approval__line { display: block; }
        .approval strong { display: block; margin-top: 5px; }
        .form-code { margin-top: 8px; font-weight: 700; }
        .title { margin: 15px 0 11px; text-align: center; }
        .title h1 { margin: 0; font-size: 14pt; line-height: 1.22; text-transform: uppercase; }
        .title p { margin: 5px 0 0; font-size: 10pt; }
        .document-number { margin-top: 7px; font-size: 11pt; font-weight: 700; }
        .protocol { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .protocol th, .protocol td { border: 1px solid #222; padding: 5px 7px; vertical-align: top; }
        .protocol th { width: 28%; font-weight: 700; text-align: left; }
        .section-title { margin: 12px 0 5px; padding: 4px 6px; border: 1px solid #222; background: #f2f2f2; font-size: 11pt; font-weight: 700; text-align: center; text-transform: uppercase; page-break-after: avoid; }
        .field { display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: 5px; margin: 5px 0; page-break-inside: avoid; }
        .field__number { font-weight: 700; }
        .field__label { font-weight: 700; }
        .field__value { min-height: 17px; margin-top: 2px; border-bottom: 1px solid #777; white-space: pre-wrap; overflow-wrap: anywhere; }
        .field__value--box { min-height: 29px; border: 1px solid #777; padding: 4px 6px; }
        .field__hint { color: #555; font-size: 8.7pt; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 13px; }
        .checks { display: grid; gap: 3px; margin: 4px 0 0; }
        .checks--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 15px; }
        .check { display: flex; align-items: flex-start; gap: 5px; page-break-inside: avoid; }
        .check__box { flex: 0 0 auto; width: 15px; font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10pt; line-height: 1.2; }
        .data-table { width: 100%; border-collapse: collapse; margin: 6px 0 9px; page-break-inside: auto; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; white-space: pre-wrap; overflow-wrap: anywhere; }
        .data-table th { background: #f3f3f3; font-size: 9pt; text-align: left; }
        .data-table tr { page-break-inside: avoid; }
        .empty-row { color: #555; font-style: italic; text-align: center; }
        .signatures { margin-top: 17px; }
        .signature { display: grid; grid-template-columns: 175px minmax(95px, .8fr) minmax(210px, 1.45fr); gap: 10px; align-items: end; margin-top: 15px; page-break-inside: avoid; }
        .signature__field { min-width: 0; }
        .signature__line { display: flex; min-height: 22px; align-items: flex-end; justify-content: center; padding: 0 3px 2px; border-bottom: 1px solid #111; overflow-wrap: anywhere; }
        .signature__caption { margin-top: 2px; color: #555; font-size: 8pt; line-height: 1.05; text-align: center; }
        .footer { display: flex; justify-content: space-between; gap: 20px; margin-top: 17px; font-size: 9pt; }
        .print-note { margin-top: 16px; border: 1px solid #888; padding: 6px 8px; font-size: 8.8pt; }
        .page-mark { margin-top: 12px; color: #555; font-size: 8pt; text-align: right; }
        @page { size: A4; margin: 0; }
        @media print {
            html, body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
@php
    $snapshot = (array) ($document->patient_snapshot ?: []);
    $details = (array) ($document->details ?: []);
    $text = static function (mixed $value, string $empty = '—'): string {
        $value = trim((string) $value);
        return $value !== '' ? $value : $empty;
    };
    $date = static function (mixed $value, string $empty = '—'): string {
        if (!$value) return $empty;
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    };
    $option = static fn (array $options, mixed $value, string $empty = '—'): string =>
        $value && isset($options[$value]) ? $options[$value] : $empty;
    $mark = static fn (bool $checked): string => $checked ? '☒' : '☐';
    $temporaryRows = collect(data_get($details, 'clinical.temporary_disability', []))->filter(fn ($row) => is_array($row))->values();
    $examinationRows = collect(data_get($details, 'clinical.examinations', []))->filter(fn ($row) => is_array($row))->values();
    $commissionMembers = collect(data_get($details, 'commission.members', []))->filter()->values();
    $identity = collect([
        data_get($snapshot, 'identity.name'),
        filled(data_get($snapshot, 'identity.series')) ? 'серия ' . data_get($snapshot, 'identity.series') : null,
        filled(data_get($snapshot, 'identity.number')) ? '№ ' . data_get($snapshot, 'identity.number') : null,
        filled(data_get($snapshot, 'identity.issue_date')) ? 'выдан ' . $date(data_get($snapshot, 'identity.issue_date')) : null,
        data_get($snapshot, 'identity.issued_by'),
    ])->filter()->implode(', ');
    $organizationName = data_get($details, 'organization.name');
    $organizationAddress = data_get($details, 'organization.address');
    $organizationOgrn = data_get($details, 'organization.ogrn');
    $chairman = data_get($details, 'commission.chairman');

    $hasPreviousIpraData = collect([
        data_get($details, 'clinical.ipra.number'),
        data_get($details, 'clinical.ipra.protocol_number'),
        data_get($details, 'clinical.ipra.protocol_date'),
        data_get($details, 'clinical.rehabilitation_results.function_restoration'),
        data_get($details, 'clinical.rehabilitation_results.function_compensation'),
        data_get($details, 'clinical.rehabilitation_results.notes'),
    ])->contains(static fn (mixed $value): bool => filled($value));

    $showPreviousIpra = (bool) data_get($snapshot, 'disability', false)
        || filled(data_get($details, 'previous_mse.disability_group'))
        || in_array('ipra', (array) ($document->purposes ?: []), true)
        || $hasPreviousIpraData;

    $patientAge = data_get($snapshot, 'age');
    $patientAgeMonths = data_get($snapshot, 'age_months');
    if (!is_numeric($patientAge) && filled(data_get($snapshot, 'birth_date'))) {
        try {
            $birthForAge = \Illuminate\Support\Carbon::parse(data_get($snapshot, 'birth_date'))->startOfDay();
            $patientAge = $birthForAge->age;
            $patientAgeMonths = $birthForAge->diffInMonths(today()->startOfDay());
        } catch (\Throwable) {
            $patientAge = null;
            $patientAgeMonths = null;
        }
    }
    $patientAge = is_numeric($patientAge) ? (int) $patientAge : null;
    $patientAgeLabel = $patientAge === 0 && is_numeric($patientAgeMonths)
        ? (int) $patientAgeMonths . ' полных месяцев'
        : ($patientAge !== null ? $patientAge . ' полных лет' : '—');
    $isUnderThree = $patientAge !== null && $patientAge < 3;
@endphp

<div class="toolbar">
    <button type="button" onclick="window.print()">Печать формы 088/у</button>
</div>

<article class="sheet">
    <div class="approval">
        <div class="approval__title">Приложение № 1</div>
        <span class="approval__line">к приказу Министерства труда и социальной защиты Российской Федерации</span>
        <span class="approval__line">и Министерства здравоохранения Российской Федерации</span>
        <span class="approval__line">от 12 августа 2022 г. № 488н/551н</span>
        <strong>Медицинская документация</strong>
        <div class="form-code">Учетная форма № 088/у</div>
    </div>

    <header class="title">
        <h1>Направление на медико-социальную экспертизу медицинской организацией</h1>
        <p>Печатное представление сведений, подготовленных в медицинской информационной системе</p>
        <div class="document-number">
            {{ $document->document_number ?: 'Без номера' }} от {{ $document->document_date?->format('d.m.Y') ?: '—' }}
        </div>
    </header>

    <table class="protocol">
        <tr><th>Решение врачебной комиссии</th><td>Протокол № {{ $text($document->protocol_number) }} от {{ $document->protocol_date?->format('d.m.Y') ?: '—' }}</td></tr>
        <tr><th>Письменное согласие</th><td>от {{ $document->consent_date?->format('d.m.Y') ?: '—' }}</td></tr>
        <tr>
            <th>Подписанное разрешение</th>
            <td>
                @if($document->hasConsentDocument())
                    Загружено: {{ $document->consentDocumentOriginalName() }}
                    ({{ $document->consentDocumentSizeLabel() }})
                @else
                    Не загружено
                @endif
            </td>
        </tr>
        <tr><th>Вид направления</th><td>{{ $document->referralKindLabel() }}</td></tr>
        <tr><th>Форма проведения МСЭ</th><td>{{ $document->examinationFormatLabel() }}</td></tr>
        <tr><th>Способ уведомления</th><td>{{ $document->notificationMethodLabel() }}</td></tr>
    </table>

    <div class="section-title">Сведения о медицинской организации и цели направления</div>

    <div class="field"><div class="field__number">1.</div><div><div class="field__label">Наименование медицинской организации</div><div class="field__value">{{ $text($organizationName) }}</div></div></div>
    <div class="field"><div class="field__number">2.</div><div><div class="field__label">ОГРН медицинской организации</div><div class="field__value">{{ $text($organizationOgrn) }}</div></div></div>
    <div class="field"><div class="field__number">3.</div><div><div class="field__label">Адрес медицинской организации</div><div class="field__value">{{ $text($organizationAddress) }}</div></div></div>
    <div class="field"><div class="field__number">4.</div><div><div class="field__label">Бюро медико-социальной экспертизы</div><div class="field__value">{{ $text(data_get($details, 'organization.bureau_name')) }}</div></div></div>

    <div class="field">
        <div class="field__number">5.</div>
        <div>
            <div class="field__label">Цель проведения медико-социальной экспертизы</div>
            <div class="checks checks--2">
                @foreach($purposeOptions as $key => $label)
                    <div class="check"><span class="check__box">{{ $mark(in_array($key, $document->purposes ?: [], true)) }}</span><span>{{ $label }}</span></div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="field">
        <div class="field__number">6.</div>
        <div>
            <div class="field__label">Особые условия проведения медико-социальной экспертизы</div>
            <div class="checks">
                <div class="check"><span class="check__box">{{ $mark((bool) $document->requires_home_examination) }}</span><span>МСЭ необходимо провести на дому</span></div>
                <div class="check"><span class="check__box">{{ $mark((bool) $document->palliative_care) }}</span><span>Гражданин нуждается в паллиативной медицинской помощи</span></div>
                <div class="check"><span class="check__box">{{ $mark((bool) $document->primary_prosthetics) }}</span><span>Гражданин нуждается в первичном протезировании после ампутации</span></div>
            </div>
        </div>
    </div>

    <div class="section-title">Сведения о гражданине</div>

    <div class="field"><div class="field__number">7.</div><div><div class="field__label">Фамилия, имя, отчество</div><div class="field__value">{{ $document->patientFullName() }}</div></div></div>
    <div class="grid-2">
        <div class="field"><div class="field__number">8.</div><div><div class="field__label">Пол</div><div class="field__value">{{ $document->patientSexLabel() }}</div></div></div>
        <div class="field"><div class="field__number">9.</div><div><div class="field__label">Дата рождения и возраст</div><div class="field__value">{{ $document->patientBirthDateLabel() }}; {{ $patientAgeLabel }}</div></div></div>
    </div>
    <div class="field"><div class="field__number">10.</div><div><div class="field__label">Гражданство</div><div class="field__value">{{ $option($citizenshipOptions, data_get($snapshot, 'citizenship.type')) }}{{ filled(data_get($snapshot, 'citizenship.country')) ? ': ' . data_get($snapshot, 'citizenship.country') : '' }}</div></div></div>
    <div class="field"><div class="field__number">11.</div><div><div class="field__label">Отношение к воинской обязанности</div><div class="field__value">{{ $option($militaryDutyOptions, data_get($snapshot, 'military_duty')) }}</div></div></div>
    <div class="field"><div class="field__number">12.</div><div><div class="field__label">Адрес места жительства (места пребывания)</div><div class="field__value">{{ data_get($snapshot, 'homeless') ? 'Определённое место жительства отсутствует' : $text(data_get($snapshot, 'address')) }}</div></div></div>
    <div class="field"><div class="field__number">13.</div><div><div class="field__label">Место нахождения гражданина</div><div class="field__value">{{ $option($locationTypeOptions, data_get($snapshot, 'location.type')) }}; {{ $text(data_get($snapshot, 'location.name')) }}; {{ $text(data_get($snapshot, 'location.address')) }}{{ filled(data_get($snapshot, 'location.ogrn')) ? '; ОГРН ' . data_get($snapshot, 'location.ogrn') : '' }}</div></div></div>
    <div class="field"><div class="field__number">14.</div><div><div class="field__label">Контактные сведения</div><div class="field__value">Телефон: {{ $text(data_get($snapshot, 'phone')) }}; электронная почта: {{ $text(data_get($snapshot, 'email')) }}</div></div></div>
    <div class="grid-2">
        <div class="field"><div class="field__number">15.</div><div><div class="field__label">СНИЛС</div><div class="field__value">{{ $text(data_get($snapshot, 'snils')) }}</div></div></div>
        <div class="field"><div class="field__number">16.</div><div><div class="field__label">Полис ОМС</div><div class="field__value">{{ $text(data_get($snapshot, 'oms')) }}</div></div></div>
    </div>
    <div class="field"><div class="field__number">17.</div><div><div class="field__label">Документ, удостоверяющий личность</div><div class="field__value">{{ $text($identity) }}</div></div></div>

    <div class="page-mark">Страница 1</div>
</article>

<article class="sheet">
    <div class="section-title">Представитель гражданина и результаты предыдущей МСЭ</div>

    <div class="field"><div class="field__number">18.</div><div><div class="field__label">Законный или уполномоченный представитель гражданина</div><div class="field__value field__value--box">ФИО: {{ $text(data_get($snapshot, 'representative.full_name')) }}; дата рождения: {{ $date(data_get($snapshot, 'representative.birth_date')) }}; документ о полномочиях: {{ $text(data_get($snapshot, 'representative.authority_document')) }}; документ, удостоверяющий личность: {{ $text(data_get($snapshot, 'representative.identity')) }}; телефон: {{ $text(data_get($snapshot, 'representative.phone')) }}; электронная почта: {{ $text(data_get($snapshot, 'representative.email')) }}; СНИЛС: {{ $text(data_get($snapshot, 'representative.snils')) }}</div></div></div>
    <div class="field"><div class="field__number">19.</div><div><div class="field__label">Организация, исполняющая обязанности законного представителя</div><div class="field__value field__value--box">{{ $text(data_get($snapshot, 'guardian_organization.name')) }}; адрес: {{ $text(data_get($snapshot, 'guardian_organization.address')) }}; ОГРН: {{ $text(data_get($snapshot, 'guardian_organization.ogrn')) }}</div></div></div>
    <div class="field"><div class="field__number">20.</div><div><div class="field__label">Ранее установленная инвалидность</div><div class="field__value field__value--box">{{ $option($disabilityGroupOptions, data_get($details, 'previous_mse.disability_group')) }}; установлена до {{ $date(data_get($details, 'previous_mse.disability_until')) }}; длительность: {{ $option($disabilityPeriodOptions, data_get($details, 'previous_mse.disability_period')) }}; причина: {{ $option($disabilityCauseOptions, data_get($details, 'previous_mse.disability_cause')) }}{{ filled(data_get($details, 'previous_mse.disability_cause_other')) ? '; ' . data_get($details, 'previous_mse.disability_cause_other') : '' }}</div></div></div>
    <div class="field"><div class="field__number">21.</div><div><div class="field__label">Ранее установленная степень утраты профессиональной трудоспособности и иные результаты</div><div class="field__value field__value--box">Степень: {{ filled(data_get($details, 'previous_mse.professional_loss_percent')) ? data_get($details, 'previous_mse.professional_loss_percent') . '%' : '—' }}; срок: {{ $text(data_get($details, 'previous_mse.professional_loss_term')) }}; установлена до {{ $date(data_get($details, 'previous_mse.professional_loss_until')) }}. {{ $text(data_get($details, 'previous_mse.professional_loss_other'), '') }}</div></div></div>

    <div class="section-title">Образование и трудовая деятельность</div>

    <div class="field"><div class="field__number">22.</div><div><div class="field__label">Сведения об образовании</div><div class="field__value field__value--box">Организация: {{ $text(data_get($details, 'education.organization')) }}; уровень: {{ $text(data_get($details, 'education.level')) }}; профессия / специальность / квалификация: {{ $text(data_get($details, 'education.profession')) }}</div></div></div>
    <div class="field"><div class="field__number">23.</div><div><div class="field__label">Основная профессия (специальность)</div><div class="field__value">{{ $text(data_get($details, 'employment.profession')) }}</div></div></div>
    <div class="field"><div class="field__number">24.</div><div><div class="field__label">Квалификация и стаж</div><div class="field__value">{{ $text(data_get($details, 'employment.qualification')) }}; стаж: {{ $text(data_get($details, 'employment.experience')) }}</div></div></div>
    <div class="field"><div class="field__number">25.</div><div><div class="field__label">Выполняемая работа, характер и условия труда</div><div class="field__value field__value--box">{{ $text(data_get($details, 'employment.current_work')) }}

                Условия труда: {{ $text(data_get($details, 'employment.conditions')) }}

                Работодатель: {{ $text(data_get($details, 'employment.organization')) }}, адрес: {{ $text(data_get($details, 'employment.address')) }}</div></div></div>

    <div class="section-title">Клинико-анамнестические сведения</div>

    <div class="field"><div class="field__number">26.</div><div><div class="field__label">Год начала наблюдения в медицинской организации</div><div class="field__value">{{ $text(data_get($details, 'clinical.observed_since_year')) }}</div></div></div>
    <div class="field"><div class="field__number">27.</div><div><div class="field__label">Анамнез заболевания</div><div class="field__value field__value--box">{{ $text(data_get($details, 'clinical.disease_history')) }}</div></div></div>
    <div class="field"><div class="field__number">28.</div><div><div class="field__label">Анамнез жизни</div><div class="field__value field__value--box">{{ $text(data_get($details, 'clinical.life_history')) }}</div></div></div>

    <div class="field">
        <div class="field__number">29.</div>
        <div>
            <div class="field__label">Периоды временной нетрудоспособности за последние 12 месяцев</div>
            <table class="data-table">
                <thead><tr><th>Начало</th><th>Окончание</th><th>Длительность</th><th>Диагноз</th></tr></thead>
                <tbody>
                @forelse($temporaryRows as $row)
                    <tr>
                        <td>{{ $date(data_get($row, 'start_date')) }}</td>
                        <td>{{ $date(data_get($row, 'end_date')) }}</td>
                        <td>{{ $text(data_get($row, 'duration')) }}</td>
                        <td>{{ $text(data_get($row, 'diagnosis')) }}</td>
                    </tr>
                @empty
                    <tr><td class="empty-row" colspan="4">Сведения не указаны</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="field"><div class="field__number">30.</div><div><div class="field__label">Электронный листок нетрудоспособности</div><div class="field__value">{{ data_get($details, 'clinical.has_eln') ? 'Сформирован, № ' . $text(data_get($details, 'clinical.eln_number')) : 'Не сформирован' }}</div></div></div>
    <div class="field">
        <div class="field__number">31.</div>
        <div>
            <div class="field__label">Сведения об ИПРА (при наличии)</div>
            @if($hasPreviousIpraData)
                <div class="field__value">ИПРА № {{ $text(data_get($details, 'clinical.ipra.number')) }}; протокол № {{ $text(data_get($details, 'clinical.ipra.protocol_number')) }} от {{ $date(data_get($details, 'clinical.ipra.protocol_date')) }}</div>
            @else
                <div class="field__value">&nbsp;</div>
            @endif
        </div>
    </div>
    <div class="field">
        <div class="field__number">32.</div>
        <div>
            <div class="field__label">Результаты мероприятий предыдущей ИПРА (при наличии)</div>
            @if($hasPreviousIpraData)
                <div class="field__value field__value--box">Восстановление функций: {{ $option($rehabilitationEffectOptions, data_get($details, 'clinical.rehabilitation_results.function_restoration')) }}; компенсация функций: {{ $option($rehabilitationEffectOptions, data_get($details, 'clinical.rehabilitation_results.function_compensation')) }}.
                    {{ $text(data_get($details, 'clinical.rehabilitation_results.notes'), '') }}</div>
            @else
                <div class="field__value field__value--box">&nbsp;</div>
            @endif
        </div>
    </div>

    <div class="page-mark">Страница 2</div>
</article>

<article class="sheet">
    <div class="section-title">Объективные и клинико-функциональные данные</div>

    <div class="field"><div class="field__number">33.</div><div><div class="field__label">Антропометрические данные и физическое развитие</div><div class="field__value field__value--box">Рост: {{ $text(data_get($details, 'clinical.anthropometry.height_cm')) }} см; масса тела: {{ $text(data_get($details, 'clinical.anthropometry.weight_kg')) }} кг; ИМТ: {{ $text(data_get($details, 'clinical.anthropometry.bmi')) }}; телосложение: {{ $text(data_get($details, 'clinical.anthropometry.body_build')) }}{{ filled(data_get($details, 'clinical.anthropometry.daily_excretions_ml')) ? '; суточные физиологические отправления: ' . data_get($details, 'clinical.anthropometry.daily_excretions_ml') . ' мл' : '' }}{{ filled(data_get($details, 'clinical.anthropometry.waist_cm')) ? '; окружность талии: ' . data_get($details, 'clinical.anthropometry.waist_cm') . ' см' : '' }}{{ filled(data_get($details, 'clinical.anthropometry.hip_cm')) ? '; окружность бёдер: ' . data_get($details, 'clinical.anthropometry.hip_cm') . ' см' : '' }}@if($isUnderThree); масса тела при рождении: {{ $text(data_get($details, 'clinical.anthropometry.birth_weight_g')) }} г; физическое развитие: {{ $text(data_get($details, 'clinical.anthropometry.physical_development')) }}@endif</div></div></div>
    <div class="field"><div class="field__number">34.</div><div><div class="field__label">Жалобы и психический статус</div><div class="field__value field__value--box">Жалобы пациента / сведения со слов родственников: {{ $text(data_get($details, 'clinical.complaints')) }}

                Психический статус и стойкие нарушения психических функций: {{ $text(data_get($details, 'clinical.health_status')) }}</div></div></div>

    <div class="field">
        <div class="field__number">35.</div>
        <div>
            <div class="field__label">Результаты обследований, психодиагностики и консультаций специалистов</div>
            <table class="data-table">
                <thead><tr><th style="width: 85px">Дата</th><th style="width: 80px">Код</th><th style="width: 190px">Наименование</th><th>Результат</th></tr></thead>
                <tbody>
                @forelse($examinationRows as $row)
                    <tr>
                        <td>{{ $date(data_get($row, 'date')) }}</td>
                        <td>{{ $text(data_get($row, 'code')) }}</td>
                        <td>{{ $text(data_get($row, 'name')) }}</td>
                        <td>{{ $text(data_get($row, 'result')) }}</td>
                    </tr>
                @empty
                    <tr><td class="empty-row" colspan="4">Сведения не указаны</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="field"><div class="field__number">36.</div><div><div class="field__label">Клинический диагноз</div><div class="field__value field__value--box">{{ $document->diagnosisLabel() }}</div></div></div>
    <div class="field"><div class="field__number">37.</div><div><div class="field__label">Осложнения основного заболевания</div><div class="field__value field__value--box">{{ $text(data_get($details, 'clinical.complications')) }}</div></div></div>
    <div class="field"><div class="field__number">38.</div><div><div class="field__label">Сопутствующие заболевания и коды МКБ</div><div class="field__value field__value--box">{{ $text(data_get($details, 'clinical.concomitant_diseases')) }}

                Коды МКБ: {{ $text(data_get($details, 'clinical.concomitant_codes')) }}</div></div></div>

    <div class="section-title">Прогноз и рекомендации</div>

    <div class="field"><div class="field__number">39.</div><div><div class="field__label">Клинический прогноз, терапия и психосоциальная реабилитация</div><div class="field__value field__value--box">Клинический прогноз: {{ $option($prognosisOptions, data_get($details, 'rehabilitation.clinical_prognosis')) }}.
                Реабилитационный потенциал: {{ $option($rehabilitationPotentialOptions, data_get($details, 'rehabilitation.potential')) }}.
                Реабилитационный прогноз: {{ $option($prognosisOptions, data_get($details, 'rehabilitation.prognosis')) }}.

                Психосоциальная и медицинская реабилитация: {{ $text(data_get($details, 'rehabilitation.medical_rehabilitation')) }}

                Психофармакотерапия и поддерживающее лечение: {{ $text(data_get($details, 'rehabilitation.medications_devices')) }}

                Специализированная психиатрическая помощь: {{ $text(data_get($details, 'rehabilitation.special_medical_care')) }}

                Дополнительные сведения: {{ $text(data_get($details, 'rehabilitation.additional_notes')) }}</div></div></div>

    <div class="field"><div class="field__number">40.</div><div><div class="field__label">Заключительная часть и подписи врачебной комиссии</div></div></div>

    <div class="signatures">
        <div class="signature">
            <div>Председатель врачебной комиссии</div>
            <div class="signature__field"><div class="signature__line"></div><div class="signature__caption">подпись</div></div>
            <div class="signature__field"><div class="signature__line">{{ $text($chairman, '') }}</div><div class="signature__caption">расшифровка подписи</div></div>
        </div>
        @foreach($commissionMembers as $member)
            <div class="signature">
                <div>Член врачебной комиссии</div>
                <div class="signature__field"><div class="signature__line"></div><div class="signature__caption">подпись</div></div>
                <div class="signature__field"><div class="signature__line">{{ $member }}</div><div class="signature__caption">расшифровка подписи</div></div>
            </div>
        @endforeach
        @if($commissionMembers->isEmpty())
            <div class="signature">
                <div>Член врачебной комиссии</div>
                <div class="signature__field"><div class="signature__line"></div><div class="signature__caption">подпись</div></div>
                <div class="signature__field"><div class="signature__line"></div><div class="signature__caption">расшифровка подписи</div></div>
            </div>
        @endif
    </div>

    <footer class="footer">
        <span>Дата направления: {{ $document->document_date?->format('d.m.Y') ?: '—' }}</span>
        <span>М.П. (при наличии)</span>
    </footer>

    <div class="print-note">
        Сформировано медицинской информационной системой. Перед подписанием необходимо сверить сведения с медицинской документацией и локальным порядком оформления формы № 088/у.
    </div>
    <div class="page-mark">Страница 3</div>
</article>
</body>
</html>
