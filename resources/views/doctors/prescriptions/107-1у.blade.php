<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рецепт 107-1/у</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.25;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, .92);
            border-bottom: 1px solid #dbe3ea;
            backdrop-filter: blur(8px);
        }

        .print-toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            background: #0d6efd;
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 14px;
            cursor: pointer;
        }

        .sheet {
            width: 190mm;
            min-height: 270mm;
            margin: 14px auto;
            padding: 12mm 13mm;
            background: #fff;
            border: 1px solid #d1d5db;
            box-shadow: 0 16px 45px rgba(15, 23, 42, .14);
        }

        .top-grid {
            display: grid;
            grid-template-columns: 1fr 72mm;
            gap: 10mm;
            align-items: start;
        }

        .ministry {
            font-size: 12px;
            line-height: 1.25;
            white-space: pre-line;
        }

        .codes {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .codes td {
            border: 1px solid #111;
            padding: 3px 5px;
            height: 20px;
        }

        .codes .label {
            width: 58%;
        }

        .codes .value {
            width: 42%;
            text-align: center;
        }

        .doc-title {
            margin: 8mm 0 4mm;
            text-align: center;
            font-weight: 700;
            font-size: 17px;
            letter-spacing: .02em;
        }

        .header-grid {
            display: grid;
            grid-template-columns: 1fr 64mm;
            gap: 9mm;
            align-items: stretch;
            margin-bottom: 6mm;
        }

        .stamp {
            min-height: 42mm;
            padding: 4mm;
            border: 1px dashed #111;
        }

        .stamp-title {
            margin-bottom: 2mm;
            font-size: 10px;
            color: #374151;
            text-align: center;
        }

        .clinic-name {
            font-weight: 700;
            font-size: 13px;
            text-align: center;
            margin-bottom: 2mm;
        }

        .clinic-line {
            font-size: 11px;
            margin-top: 1.5mm;
        }

        .form-info {
            font-size: 11px;
            line-height: 1.3;
        }

        .form-info strong {
            font-size: 14px;
        }

        h1 {
            margin: 4mm 0 1mm;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-bottom: 3mm;
        }

        .date-line {
            text-align: center;
            font-size: 14px;
            margin-bottom: 5mm;
        }

        .field {
            margin-top: 3mm;
        }

        .field-label {
            margin-bottom: 1mm;
            font-size: 12px;
        }

        .line {
            min-height: 7mm;
            padding: 1.5mm 2mm .8mm;
            border-bottom: 1px solid #111;
            font-size: 15px;
            font-weight: 600;
        }

        .inline-fields {
            display: grid;
            grid-template-columns: 34mm 1fr;
            align-items: end;
            gap: 2mm;
            margin-top: 2mm;
        }

        .rp-box {
            margin-top: 8mm;
            border: 1px solid #111;
            padding: 4mm;
            min-height: 58mm;
        }

        .rp-title {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            font-size: 13px;
        }

        .rx-line {
            min-height: 8mm;
            padding: 1.7mm 1mm .8mm;
            border-bottom: 1px dashed #111;
            font-size: 16px;
        }

        .rx-line strong {
            font-weight: 700;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 42mm;
            gap: 12mm;
            align-items: end;
            margin-top: 10mm;
        }

        .signature-line {
            margin-top: 8mm;
            border-bottom: 1px solid #111;
            height: 8mm;
        }

        .stamp-place {
            height: 26mm;
            border: 1px dashed #111;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
        }

        .validity {
            margin-top: 7mm;
            text-align: right;
            font-size: 13px;
        }

        .hint {
            color: #4b5563;
            font-size: 10px;
            text-align: center;
        }

        @media print {
            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-toolbar {
                display: none;
            }

            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
@php
    $monthNames = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
    ];

    $dateDay = $dateAsDay ?? now()->format('d');
    $dateMonth = $dateAsMonth ?? ($month ?? $monthNames[(int) now()->format('n')]);
    $dateYear = $dateAsYear ?? now()->format('Y');

    $clinicName = $clinicName
        ?? ($clinic?->name ?? null)
        ?? ($organizationName ?? null)
        ?? 'Наименование медицинской организации';

    $clinicAddress = $clinicAddress
        ?? ($clinic?->address ?? null)
        ?? ($organizationAddress ?? null)
        ?? 'Адрес медицинской организации';

    $clinicPhone = $clinicPhone
        ?? ($clinic?->phone ?? null)
        ?? ($organizationPhone ?? null)
        ?? null;

    $clinicLicense = $clinicLicense
        ?? ($clinic?->license ?? null)
        ?? ($clinic?->license_number ?? null)
        ?? ($license ?? null)
        ?? 'Лицензия: серия, номер, дата выдачи';

    $okudCode = $okudCode ?? '';
    $okpoCode = $okpoCode ?? ($oknoCode ?? '');

    $patientFullName = $patientFullName ?? ($patient?->full_name ?? '—');
    $patientBirthday = $patientBirthday ?? ($patient?->birth_at ?? '—');
    $doctorFullName = $doctorFullName ?? ($doctor?->full_name ?? '—');

    $drugShortForm = $drugShortForm ?? 'Rp';
    $drugLatinName = $drugLatinName ?? ($drugName ?? '—');
    $drugDose = $drugDose ?? '';
    $drugQuantity = $drugQuantity ?? '';
    $drugStandardCount = $drugStandardCount ?? '';
    $drugStandards = $drugStandards ?? '';
    $drugUsingSchema = $drugUsingSchema ?? ($usageInstructions ?? '—');

    $validityText = $validityText ?? '60 дней, 1 года';
@endphp

<div class="print-toolbar">
    <button type="button" onclick="window.print()">Печать рецепта</button>
</div>

<div class="sheet">
    <div class="top-grid">
        <div class="ministry">Министерство здравоохранения
            Российской Федерации</div>

        <table class="codes" aria-label="Коды формы">
            <tr>
                <td class="label">Код формы по ОКУД</td>
                <td class="value">{{ $okudCode }}</td>
            </tr>
            <tr>
                <td class="label">Код учреждения по ОКПО</td>
                <td class="value">{{ $okpoCode }}</td>
            </tr>
        </table>
    </div>

    <div class="doc-title">МЕДИЦИНСКАЯ ДОКУМЕНТАЦИЯ</div>

    <div class="header-grid">
        <div class="stamp">
            <div class="stamp-title">Наименование / штамп медицинской организации</div>
            <div class="clinic-name">{{ $clinicName }}</div>
            <div class="clinic-line"><strong>Адрес:</strong> {{ $clinicAddress }}</div>
            @if($clinicPhone)
                <div class="clinic-line"><strong>Тел.:</strong> {{ $clinicPhone }}</div>
            @endif
            <div class="clinic-line"><strong>{{ $clinicLicense }}</strong></div>
        </div>

        <div class="form-info">
            <strong>Форма № 107-1/у</strong><br>
            Утверждена приказом Министерства здравоохранения Российской Федерации<br>
            от 14 января 2019 г. № 4н
        </div>
    </div>

    <h1>РЕЦЕПТ</h1>
    <div class="subtitle">(взрослый, детский — нужное подчеркнуть)</div>
    <div class="date-line">«<u>{{ $dateDay }}</u>» <u>{{ $dateMonth }}</u> <u>{{ $dateYear }}</u> г.</div>

    <div class="field">
        <div class="field-label">Фамилия, инициалы имени и отчества пациента</div>
        <div class="line">{{ $patientFullName }}</div>
    </div>

    <div class="inline-fields">
        <div class="field-label">Дата рождения</div>
        <div class="line">{{ $patientBirthday }}</div>
    </div>

    <div class="field">
        <div class="field-label">Фамилия, инициалы имени и отчества лечащего врача / фельдшера / акушерки</div>
        <div class="line">{{ $doctorFullName }}</div>
    </div>

    <div class="rp-box">
        <div class="rp-title">
            <span>руб. | коп.</span>
            <strong>Rp:</strong>
        </div>

        <div class="rx-line">
            <strong>{{ $drugShortForm }}.</strong>
            {{ $drugLatinName }}
            @if($drugDose) {{ $drugDose }} mg @endif
            @if($drugQuantity) N. {{ $drugQuantity }} @endif
            @if($drugStandardCount) in {{ $drugStandardCount }} @endif
        </div>

        <div class="rx-line">
            @if($drugStandards)
                D.t.d. N {{ $drugStandards }}
            @else
                &nbsp;
            @endif
        </div>

        <div class="rx-line">
            S. {{ $drugUsingSchema }}
        </div>

        <div class="rx-line">&nbsp;</div>
    </div>

    <div class="footer-grid">
        <div>
            <div>Подпись и печать лечащего врача / фельдшера / акушерки</div>
            <div class="signature-line"></div>
        </div>
        <div>
            <div class="stamp-place">М. П.</div>
        </div>
    </div>

    <div class="validity">
        Рецепт действителен в течение {{ $validityText }} (<u>{{ $validityCustomMonths ?? '_____________' }}</u>)
        <div class="hint">нужное подчеркнуть либо ввести количество месяцев действия</div>
    </div>
</div>
</body>
</html>
