@php
    $consentMode = in_array(($consentMode ?? 'auto'), ['auto', 'manual'], true)
        ? $consentMode
        : 'auto';
    $isManual = $consentMode === 'manual';
    $isPreview = (bool) ($isPreview ?? false);
    $documentExists = isset($document)
        && $document instanceof \App\Models\MseReferral
        && $document->exists;

    $consentValues = array_replace([
        'document_number' => '',
        'document_date' => '',
        'applicant_type' => '',
        'applicant_full_name' => '',
        'applicant_birth_date' => '',
        'applicant_registered_address' => '',
        'applicant_residential_address' => '',
        'applicant_identity' => '',
        'applicant_snils' => '',
        'representative_authority_document' => '',
        'patient_full_name' => '',
        'patient_birth_date' => '',
        'bureau_name' => '',
        'examination_format' => '',
        'doctor_name' => '',
        'doctor_position' => '',
        'organization_name' => '',
        'purpose_text' => '',
        'notification_method' => '',
        'applicant_signature_name' => '',
        'doctor_signature_name' => '',
        'consent_date' => '',
    ], (array) ($consentValues ?? []));

    /*
     * Ручной режим принципиально не использует сведения из формы:
     * на печать должен уйти полностью пустой бланк для заполнения ручкой.
     */
    if ($isManual) {
        $consentValues = array_fill_keys(array_keys($consentValues), '');
    }

    $value = static fn (string $key): string =>
        trim((string) data_get($consentValues, $key, ''));

    $display = static function (string $key) use ($value): string {
        $result = $value($key);

        return $result !== '' ? $result : "\u{00A0}";
    };

    $patientTitle = $isManual
        ? 'Пустой бланк'
        : ($value('patient_full_name') !== ''
            ? $value('patient_full_name')
            : 'Заполненное согласие');

    $autoPrint = $isPreview || request()->boolean('autoprint');
@endphp
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Согласие на МСЭ — {{ $patientTitle }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 11mm 13mm 10mm;
        }

        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            color: #183341;
            background: #edf3f5;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at 8% 4%, rgba(8, 126, 138, .10), transparent 27%),
                #edf3f5;
        }

        button,
        a {
            font: inherit;
        }

        .toolbar {
            position: sticky;
            z-index: 20;
            top: 0;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;

            padding: 12px 18px;

            border-bottom: 1px solid #d7e3e7;
            background: rgba(255, 255, 255, .97);

            box-shadow: 0 10px 30px rgba(28, 63, 78, .08);
            backdrop-filter: blur(12px);
        }

        .toolbar__copy {
            min-width: 0;

            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar__icon {
            width: 42px;
            height: 42px;

            flex: 0 0 42px;

            display: grid;
            place-items: center;

            border-radius: 13px;

            background: linear-gradient(135deg, #087e8a, #315b87);
            color: #fff;

            font-size: 13px;
            font-weight: 900;
        }

        .toolbar__copy strong,
        .toolbar__copy span {
            display: block;
        }

        .toolbar__copy strong {
            overflow: hidden;

            color: #173f4b;

            font-size: 14px;
            font-weight: 850;

            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .toolbar__copy > div > span {
            margin-top: 3px;

            color: #748794;

            font-size: 12px;
        }

        .mode-badge {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
            width: fit-content;

            margin-top: 5px !important;
            padding: 4px 8px;

            border: 1px solid {{ $isManual ? '#ead4a5' : '#bce3e7' }};
            border-radius: 999px;

            background: {{ $isManual ? '#fff5df' : '#e8f8fa' }};
            color: {{ $isManual ? '#6f4b13' : '#086a75' }} !important;

            font-size: 10px !important;
            font-weight: 800;
        }

        .toolbar__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .toolbar__button {
            min-height: 40px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            padding: 0 14px;

            border: 1px solid #cad9de;
            border-radius: 11px;

            background: #fff;
            color: #31566a;

            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;

            cursor: pointer;
        }

        .toolbar__button:hover {
            border-color: #acd6db;
            background: #f1fafb;
            color: #08727e;
        }

        .toolbar__button--print {
            border-color: #08727e;
            background: linear-gradient(135deg, #087e8a, #176d79);
            color: #fff;

            box-shadow: 0 8px 18px rgba(8, 126, 138, .20);
        }

        .toolbar__button--print:hover {
            background: linear-gradient(135deg, #076e79, #155f69);
            color: #fff;
        }

        .print-note {
            width: min(210mm, calc(100% - 30px));

            display: flex;
            align-items: flex-start;
            gap: 11px;

            margin: 16px auto 0;
            padding: 12px 14px;

            border: 1px solid {{ $isManual ? '#ead9b7' : '#d3e1e5' }};
            border-radius: 14px;

            background: {{ $isManual ? '#fffaf0' : 'rgba(255, 255, 255, .92)' }};
            color: #496577;

            box-shadow: 0 9px 24px rgba(31, 63, 77, .05);

            font-size: 12px;
            line-height: 1.5;
        }

        .print-note__icon {
            width: 31px;
            height: 31px;

            flex: 0 0 31px;

            display: grid;
            place-items: center;

            border-radius: 10px;

            background: {{ $isManual ? '#fff0cf' : '#e8f7f9' }};
            color: {{ $isManual ? '#9a6811' : '#087e8a' }};

            font-weight: 900;
        }

        .print-note strong {
            color: #2e4c5f;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;

            margin: 16px auto 28px;
            padding: 11mm 13mm 10mm;

            background: #fff;
            color: #000;

            box-shadow: 0 18px 52px rgba(28, 52, 65, .14);

            font-family: "Times New Roman", Times, serif;
            font-size: 11.2pt;
            line-height: 1.36;
        }

        .approval {
            width: 87mm;

            margin: 0 0 10mm auto;

            font-size: 10pt;
            line-height: 1.35;
        }

        .approval strong {
            display: block;
            text-align: center;
        }

        h1 {
            margin: 0 0 5mm;

            font-size: 13pt;
            line-height: 1.25;
            text-align: center;
        }

        .document-meta {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 2mm;

            margin: 0 0 5mm;

            font-size: 10pt;
        }

        p {
            margin: 0 0 2.6mm;
            text-align: justify;
            text-indent: 9mm;
        }

        .no-indent {
            text-indent: 0;
        }

        .form-label {
            margin: 3.5mm 0 1.5mm;

            font-weight: 700;
            text-indent: 0;
        }

        .fill-inline {
            min-width: 35mm;

            display: inline-block;

            padding: 0 1.2mm .35mm;
            border-bottom: 1px solid #000;

            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: anywhere;
            vertical-align: baseline;
        }

        .fill-inline--xs {
            min-width: 24mm;
        }

        .fill-inline--sm {
            min-width: 39mm;
        }

        .fill-inline--md {
            min-width: 64mm;
        }

        .fill-inline--lg {
            min-width: 105mm;
        }

        .fill-inline--wide {
            min-width: 150mm;
        }

        .fill-block {
            min-height: 7.5mm;

            margin: 1.2mm 0 2.8mm;
            padding: .9mm 1.2mm .45mm;

            border-bottom: 1px solid #000;

            font-weight: 700;
            line-height: 1.3;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .fill-block--two-lines {
            min-height: 13.5mm;

            background-image:
                linear-gradient(to bottom, transparent calc(50% - .3px), #000 50%, transparent calc(50% + .7px));
            background-repeat: no-repeat;
            background-position: 0 0;
        }

        .field-caption {
            margin-top: -1.8mm;
            margin-bottom: 2.4mm;

            color: #565656;

            font-size: 8pt;
            line-height: 1.15;
            text-align: center;
        }

        .choice-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 3mm 8mm;

            margin: 2mm 0 4mm;
            padding-left: 7mm;
        }

        .choice-row--3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .choice {
            display: flex;
            align-items: flex-start;
            gap: 2.2mm;

            font-size: 10.2pt;
            line-height: 1.25;

            break-inside: avoid;
        }

        .choice-box {
            width: 4.8mm;
            height: 4.8mm;

            flex: 0 0 4.8mm;

            display: grid;
            place-items: center;

            margin-top: .1mm;

            border: 1px solid #000;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            font-weight: 900;
            line-height: 1;
        }

        .representative-fields {
            margin: 3mm 0 4mm;
            padding: 3mm 4mm;

            border: 1px solid #8b8b8b;

            break-inside: avoid;
        }

        .representative-fields__title {
            margin-bottom: 1.5mm;

            font-size: 9.7pt;
            font-weight: 700;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12mm;

            margin-top: 8mm;

            break-inside: avoid;
        }

        .signature-box {
            min-width: 0;
        }

        .signature-line {
            min-height: 9mm;
            border-bottom: 1px solid #000;
        }

        .signature-caption {
            margin-top: 1mm;

            color: #555;

            font-size: 7.8pt;
            line-height: 1.15;
            text-align: center;
        }

        .signature-name {
            min-height: 7mm;

            margin-top: 4mm;
            padding: .7mm 1mm .3mm;

            border-bottom: 1px solid #000;

            font-weight: 700;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .date-line {
            margin-top: 7mm;
        }

        .print-footer {
            margin-top: 8mm;
            padding-top: 3mm;

            border-top: 1px solid #999;

            color: #555;

            font-size: 8pt;
            line-height: 1.25;
        }

        .print-footer p {
            margin: 1.2mm 0 0;
            text-indent: 0;
        }

        @media (max-width: 900px) {
            .toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .toolbar__actions {
                width: 100%;
                justify-content: flex-start;
            }

            .sheet {
                width: calc(100% - 20px);
                min-height: auto;
                padding: 28px;
            }
        }

        @media print {
            html,
            body {
                background: #fff;
            }

            .toolbar,
            .print-note {
                display: none !important;
            }

            .sheet {
                width: auto;
                min-height: auto;

                margin: 0;
                padding: 0;

                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<header class="toolbar">
    <div class="toolbar__copy">
        <span class="toolbar__icon">МСЭ</span>
        <div>
            <strong>Печатная форма согласия на направление и проведение МСЭ</strong>
            <span>{{ $patientTitle }}</span>
            <span class="mode-badge">
                {{ $isManual
                    ? 'Пустой бланк — заполнение ручкой после печати'
                    : 'Автоматически заполнено из текущей формы' }}
            </span>
        </div>
    </div>

    <div class="toolbar__actions">
        @if($documentExists)
            <a class="toolbar__button"
               href="{{ route('doctors.commissions.mse.edit', $document) }}#mseConsentDocuments">
                ← К направлению
            </a>
            <a class="toolbar__button"
               target="_blank"
               rel="noopener"
               href="{{ route('doctors.commissions.mse.print', $document) }}">
                Форма 088/у
            </a>
        @else
            <button class="toolbar__button" type="button" data-close-print>
                Закрыть
            </button>
        @endif

        <button class="toolbar__button toolbar__button--print"
                type="button"
                data-print-consent>
            Распечатать
        </button>
    </div>
</header>

<div class="print-note">
    <span class="print-note__icon">i</span>
    <div>
        @if($isManual)
            <strong>Это полностью пустой бланк.</strong>
            Сведения из формы МСЭ не подставляются. После печати пациент и врач заполняют поля ручкой.
        @else
            <strong>Сведения перенесены из текущей формы МСЭ.</strong>
            Проверьте их перед печатью. Для исправления вернитесь в основную форму, измените данные и сформируйте документ повторно.
        @endif
    </div>
</div>

<main class="sheet">
    <div class="approval">
        <strong>Приложение</strong>
        к приказу Министерства здравоохранения<br>
        Российской Федерации<br>
        от 7 июня 2022 г. № 385н
    </div>

    <h1>
        Согласие гражданина (его законного или уполномоченного представителя)<br>
        на направление и проведение медико-социальной экспертизы
    </h1>

    <div class="document-meta">
        <span>Направление №</span>
        <span class="fill-inline fill-inline--sm">{{ $display('document_number') }}</span>
        <span>от</span>
        <span class="fill-inline fill-inline--sm">{{ $display('document_date') }}</span>
    </div>

    <p class="form-label">Согласие подписывает:</p>
    <div class="choice-row">
        <div class="choice">
            <span class="choice-box">{{ $value('applicant_type') === 'citizen' ? 'X' : '' }}</span>
            <span>гражданин лично</span>
        </div>
        <div class="choice">
            <span class="choice-box">{{ $value('applicant_type') === 'representative' ? 'X' : '' }}</span>
            <span>законный или уполномоченный представитель</span>
        </div>
    </div>

    <p>
        Я,
        <span class="fill-inline fill-inline--wide">{{ $display('applicant_full_name') }}</span>,
    </p>
    <div class="field-caption">
        фамилия, имя, отчество (при наличии) гражданина либо его законного или уполномоченного представителя
    </div>

    <p>
        <span class="fill-inline fill-inline--sm">{{ $display('applicant_birth_date') }}</span>
        года рождения, зарегистрированный(-ая) по адресу:
    </p>
    <div class="fill-block">{{ $display('applicant_registered_address') }}</div>

    <p class="no-indent">проживающий(-ая) по адресу:</p>
    <div class="fill-block">{{ $display('applicant_residential_address') }}</div>

    <p>
        Документ, удостоверяющий личность:
        <span class="fill-inline fill-inline--lg">{{ $display('applicant_identity') }}</span>.
    </p>

    <p>
        СНИЛС:
        <span class="fill-inline fill-inline--md">{{ $display('applicant_snils') }}</span>.
    </p>

    <div class="representative-fields">
        <div class="representative-fields__title">
            Документ, подтверждающий полномочия представителя
            (заполняется, если согласие подписывает представитель):
        </div>
        <div class="fill-block">{{ $display('representative_authority_document') }}</div>
    </div>

    <p>
        Даю согласие на направление и проведение медико-социальной экспертизы гражданина:
    </p>
    <div class="fill-block">{{ $display('patient_full_name') }}</div>
    <div class="field-caption">
        фамилия, имя, отчество (при наличии) гражданина, направляемого на МСЭ
    </div>

    <p>
        Дата рождения гражданина:
        <span class="fill-inline fill-inline--sm">{{ $display('patient_birth_date') }}</span>.
    </p>

    <p>
        Согласие распространяется на направление и проведение медико-социальной экспертизы,
        включая необходимые медицинские вмешательства и применение специального диагностического оборудования
        в бюро медико-социальной экспертизы:
    </p>
    <div class="fill-block">{{ $display('bureau_name') }}</div>

    <p class="form-label">Предпочтительная форма проведения медико-социальной экспертизы:</p>
    <div class="choice-row">
        <div class="choice">
            <span class="choice-box">
                {{ $value('examination_format') === \App\Models\MseReferral::EXAMINATION_IN_PERSON ? 'X' : '' }}
            </span>
            <span>с личным присутствием</span>
        </div>
        <div class="choice">
            <span class="choice-box">
                {{ $value('examination_format') === \App\Models\MseReferral::EXAMINATION_WITHOUT_PRESENCE ? 'X' : '' }}
            </span>
            <span>без личного присутствия</span>
        </div>
    </div>

    <p>
        Лечащим врачом (заведующим отделением)
        <span class="fill-inline fill-inline--lg">{{ $display('doctor_position') }}</span>
    </p>
    <div class="fill-block">{{ $display('doctor_name') }}</div>
    <div class="field-caption">
        фамилия, имя, отчество лечащего врача / заведующего отделением
    </div>

    <p class="no-indent">медицинской организации:</p>
    <div class="fill-block">{{ $display('organization_name') }}</div>

    <p>
        мне в доступной форме разъяснён порядок направления на медико-социальную экспертизу в целях:
    </p>
    <div class="fill-block fill-block--two-lines">{{ $display('purpose_text') }}</div>

    <p>
        Мне разъяснён порядок проведения медико-социальной экспертизы, цели, задачи, прогнозируемые результаты
        и социально-правовые последствия рекомендуемых реабилитационных мероприятий. Разъяснено, что я имею
        право отказаться от одного или нескольких видов медицинских вмешательств либо потребовать их прекращения,
        отказаться от направления на медико-социальную экспертизу, а также отказаться от выполнения программы
        дополнительного обследования и от получения индивидуальной программы реабилитации или абилитации путём
        подачи соответствующего заявления.
    </p>

    <p>
        Мне также разъяснено, что настоящее согласие может быть оформлено на бумажном носителе с подписями
        гражданина (его законного или уполномоченного представителя) и лечащего врача (заведующего отделением)
        либо в форме электронного документа с использованием предусмотренных законодательством электронных подписей.
    </p>

    <p class="form-label">Предпочтительный способ получения уведомления о проведении МСЭ:</p>
    <div class="choice-row choice-row--3">
        <div class="choice">
            <span class="choice-box">
                {{ $value('notification_method') === \App\Models\MseReferral::NOTIFICATION_PHONE_SMS ? 'X' : '' }}
            </span>
            <span>телефонная связь, мобильная связь или СМС</span>
        </div>
        <div class="choice">
            <span class="choice-box">
                {{ $value('notification_method') === \App\Models\MseReferral::NOTIFICATION_REGISTERED_MAIL ? 'X' : '' }}
            </span>
            <span>заказное почтовое отправление</span>
        </div>
        <div class="choice">
            <span class="choice-box">
                {{ $value('notification_method') === \App\Models\MseReferral::NOTIFICATION_GOSUSLUGI ? 'X' : '' }}
            </span>
            <span>ЕПГУ «Госуслуги»</span>
        </div>
    </div>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-caption">
                подпись гражданина либо его законного или уполномоченного представителя
            </div>
            <div class="signature-name">{{ $display('applicant_signature_name') }}</div>
            <div class="signature-caption">расшифровка подписи</div>
        </div>

        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-caption">
                подпись лечащего врача / заведующего отделением
            </div>
            <div class="signature-name">{{ $display('doctor_signature_name') }}</div>
            <div class="signature-caption">расшифровка подписи</div>
        </div>
    </div>

    <p class="date-line no-indent">
        Дата оформления согласия:
        <span class="fill-inline fill-inline--md">{{ $display('consent_date') }}</span>.
    </p>

    <div class="print-footer">
        @if($isManual)
            <p>Пустые поля и выбранные варианты заполняются разборчиво от руки после печати.</p>
        @else
            <p>Перед подписанием необходимо проверить автоматически подставленные сведения и выбранные варианты.</p>
        @endif
        <p>После подписания документ необходимо отсканировать или сфотографировать и загрузить в карточку направления.</p>
    </div>
</main>

<script>
    (() => {
        const shouldAutoPrint = @json($autoPrint);

        document.querySelector('[data-print-consent]')?.addEventListener('click', () => {
            window.print();
        });

        document.querySelector('[data-close-print]')?.addEventListener('click', () => {
            window.close();
        });

        if (shouldAutoPrint) {
            window.addEventListener('load', () => {
                window.setTimeout(() => window.print(), 350);
            });
        }
    })();
</script>
</body>
</html>
