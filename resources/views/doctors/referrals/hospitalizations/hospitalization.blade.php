<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Направление для оказания медицинской помощи — 057/у</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt;
            line-height: 1.18;
        }

        .document {
            width: 100%;
            margin: 0 auto;
        }

        .top-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(58mm, .75fr);
            gap: 8mm;
            align-items: start;
            margin-bottom: 5mm;
        }

        .source-block {
            font-size: 9.7pt;
        }

        .source-value {
            min-height: 5mm;
            margin-bottom: 1.1mm;
            padding: 0 .5mm .5mm;
            border-bottom: .2mm solid #000;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .source-caption {
            font-size: 8.6pt;
            line-height: 1.12;
        }

        .form-meta {
            text-align: center;
            font-size: 9.6pt;
            line-height: 1.22;
        }

        .form-meta strong {
            display: block;
            margin: .5mm 0;
            font-size: 10.2pt;
        }

        .title {
            margin: 1mm 0 3mm;
            text-align: center;
            font-size: 14.8pt;
            line-height: 1.08;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title-number {
            display: inline-block;
            min-width: 38mm;
            padding: 0 1mm .35mm;
            border-bottom: .25mm solid #000;
            text-transform: none;
            font-weight: 700;
        }

        .line-row {
            display: flex;
            align-items: flex-end;
            gap: 1.2mm;
            min-height: 5mm;
            margin-bottom: .8mm;
        }

        .label {
            flex: 0 0 auto;
        }

        .fill {
            min-width: 0;
            flex: 1 1 auto;
            min-height: 4.7mm;
            padding: 0 .6mm .35mm;
            border-bottom: .2mm solid #000;
            overflow-wrap: anywhere;
        }

        .fill.short {
            flex: 0 0 20mm;
        }

        .fill.medium {
            flex: 0 0 44mm;
        }

        .hint {
            margin: -.15mm 0 1mm;
            text-align: center;
            font-size: 8.7pt;
            line-height: 1.1;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 5mm;
        }

        .compact {
            margin-bottom: .2mm;
        }

        .options-line {
            margin: .9mm 0;
            line-height: 1.35;
        }

        .option {
            white-space: nowrap;
        }

        .option.selected {
            font-weight: 700;
            text-decoration: underline;
            text-decoration-thickness: .25mm;
            text-underline-offset: .5mm;
        }

        .diagnosis-value,
        .reason-value {
            min-height: 13mm;
            margin-top: .3mm;
            padding: .3mm .6mm 0;
            background:
                repeating-linear-gradient(
                    to bottom,
                    transparent 0,
                    transparent 4.8mm,
                    #000 4.8mm,
                    #000 5mm
                );
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .reason-value {
            min-height: 22mm;
        }

        .value-on-lines {
            background: #fff;
            padding-right: 1mm;
            box-decoration-break: clone;
            -webkit-box-decoration-break: clone;
        }

        .doctor-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 36mm 46mm;
            gap: 3mm;
            align-items: end;
            margin-top: 1mm;
        }

        .signature-cell {
            min-height: 8mm;
            border-bottom: .2mm solid #000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: .5mm;
        }

        .signature-caption {
            margin-top: .5mm;
            text-align: center;
            font-size: 8.5pt;
        }

        .stamp {
            margin-top: 3mm;
            font-weight: 700;
        }

        .internal-note {
            display: none;
        }

        @media print {
            html,
            body {
                width: 100%;
                height: auto;
            }

            .document {
                width: auto;
            }
        }
    </style>
</head>
<body>
<!--
    Учетная форма № 057/у утверждена приказом Минздрава России от 02.09.2025 № 519н.
    По порядку ведения форма является медицинской документацией медицинской организации,
    оказывающей помощь в амбулаторных условиях. При электронном ведении документ формируется
    в МИС/ГИС и подписывается УКЭП медицинского работника.
-->
<main class="document">
    <div class="top-grid">
        <div class="source-block">
            <div class="source-value">{{ $organizationName }}</div>
            <div class="source-value">{{ $organizationAddress }}</div>
            <div class="source-value">{{ $organizationOgrn }}</div>
            <div class="source-caption">
                Наименование и адрес медицинской организации в пределах места нахождения
                (для индивидуального предпринимателя — Ф.И.О. и адрес осуществления медицинской деятельности),
                ОГРН (ОГРНИП)
            </div>
        </div>

        <div class="form-meta">
            <div>Медицинская документация</div>
            <strong>Учетная форма № 057/у</strong>
            <div>Утверждена приказом Министерства здравоохранения Российской Федерации</div>
            <div>от 2 сентября 2025 г. № 519н</div>
        </div>
    </div>

    <h1 class="title">
        Направление для оказания медицинской помощи №
        <span class="title-number">{{ $documentNumber }}</span>
    </h1>

    <div class="line-row">
        <span class="label">Дата заполнения направления: число</span>
        <span class="fill short">{{ $issuedDay }}</span>
        <span class="label">месяц</span>
        <span class="fill short">{{ $issuedMonth }}</span>
        <span class="label">год</span>
        <span class="fill short">{{ $issuedYear }}</span>
    </div>

    <div class="line-row">
        <span class="fill">{{ $destinationOrganization }}</span>
    </div>
    <div class="hint">(наименование медицинской организации, куда направлен пациент)</div>

    <div class="line-row">
        <span class="label">Полис обязательного медицинского страхования:</span>
        <span class="fill">{{ $oms }}</span>
    </div>

    <div class="line-row">
        <span class="label">дата выдачи полиса обязательного медицинского страхования: число</span>
        <span class="fill short">{{ $omsIssuedDay }}</span>
        <span class="label">месяц</span>
        <span class="fill short">{{ $omsIssuedMonth }}</span>
        <span class="label">год</span>
        <span class="fill short">{{ $omsIssuedYear }}</span>
    </div>

    <div class="line-row">
        <span class="label">данные о страховой медицинской организации:</span>
        <span class="fill">{{ $insuranceOrganization }}</span>
    </div>

    <div class="line-row">
        <span class="label">Фамилия, имя, отчество (при наличии) пациента:</span>
        <span class="fill">{{ $patientName }}</span>
    </div>

    <div class="line-row">
        <span class="label">Дата рождения:</span>
        <span class="fill medium">{{ $birthDate }}</span>
        <span class="label">Пол:</span>
        <span class="option {{ $sexCode === '1' ? 'selected' : '' }}">муж — 1</span>,
        <span class="option {{ $sexCode === '2' ? 'selected' : '' }}">жен — 2</span>
    </div>

    <div class="line-row compact">
        <span class="label">Регистрация по месту жительства:</span>
        <span class="fill">{{ $registrationAddress }}</span>
    </div>

    <div class="line-row compact">
        <span class="label">Регистрация по месту пребывания:</span>
        <span class="fill">{{ $stayAddress }}</span>
    </div>

    <div class="options-line">
        <span class="label">Местность:</span>
        <span class="option {{ $localityCode === '1' ? 'selected' : '' }}">городская — 1</span>,
        <span class="option {{ $localityCode === '2' ? 'selected' : '' }}">сельская — 2</span>
    </div>

    <div class="options-line">
        <span class="label">Занятость:</span>
        <span class="option {{ $employmentCode === '1' ? 'selected' : '' }}">работает — 1</span>,
        <span class="option {{ $employmentCode === '2' ? 'selected' : '' }}">военная или приравненная служба — 2</span>,
        <span class="option {{ $employmentCode === '3' ? 'selected' : '' }}">пенсионер — 3</span>,
        <span class="option {{ $employmentCode === '4' ? 'selected' : '' }}">обучающийся — 4</span>,
        <span class="option {{ $employmentCode === '5' ? 'selected' : '' }}">не работает — 5</span>,
        <span class="option {{ $employmentCode === '6' ? 'selected' : '' }}">прочие — 6</span>
    </div>

    <div>
        Код диагноза по Международной статистической классификации болезней
        и проблем, связанных со здоровьем
    </div>
    <div class="diagnosis-value"><span class="value-on-lines">{{ $diagnosis }}</span></div>

    <div class="line-row">
        <span class="label">Направляется для оказания медицинской помощи:</span>
        <span class="fill">{{ $referralPurpose }}</span>
    </div>

    <div class="options-line">
        <span class="label">форма:</span>
        <span class="option {{ $careFormCode === '1' ? 'selected' : '' }}">экстренная — 1</span>,
        <span class="option {{ $careFormCode === '2' ? 'selected' : '' }}">неотложная — 2</span>,
        <span class="option {{ $careFormCode === '3' ? 'selected' : '' }}">плановая — 3</span>;
    </div>

    <div class="options-line">
        <span class="label">вид:</span>
        <span class="option {{ $careTypeCode === '1' ? 'selected' : '' }}">первичная медико-санитарная, в том числе специализированная — 1</span>;
        <span class="option {{ $careTypeCode === '2' ? 'selected' : '' }}">специализированная, в том числе высокотехнологичная — 2</span>;
        <span class="option {{ $careTypeCode === '3' ? 'selected' : '' }}">паллиативная — 3</span>;
        <span class="option {{ $careTypeCode === '4' ? 'selected' : '' }}">скорая специализированная — 4</span>;
    </div>

    <div class="options-line">
        <span class="label">условия:</span>
        <span class="option {{ $careConditionsCode === '1' ? 'selected' : '' }}">амбулаторно — 1</span>;
        <span class="option {{ $careConditionsCode === '2' ? 'selected' : '' }}">в дневном стационаре — 2</span>;
        <span class="option {{ $careConditionsCode === '3' ? 'selected' : '' }}">стационарно — 3</span>.
    </div>

    <div>
        Обоснование (показания) направления с указанием числа назначаемых курсов (циклов) лечения
    </div>
    <div class="reason-value"><span class="value-on-lines">{{ $reason }}</span></div>

    <div class="line-row">
        <span class="label">Должность, специальность медицинского работника, направившего пациента:</span>
        <span class="fill">{{ $doctorPositionSpeciality }}</span>
    </div>

    <div class="doctor-grid">
        <div>
            <div class="signature-cell"></div>
            <div class="signature-caption">подпись</div>
        </div>
        <div></div>
        <div>
            <div class="signature-cell">{{ $doctorName }}</div>
            <div class="signature-caption">Фамилия, имя, отчество (при наличии)</div>
        </div>
    </div>

    <div class="stamp">М.П. (при наличии)</div>
</main>

<script>
    (function () {
        const page = document.querySelector('.document');
        if (!page) return;

        const MM_TO_PX = 96 / 25.4;
        const PRINTABLE_HEIGHT_MM = 281;
        const printableHeight = PRINTABLE_HEIGHT_MM * MM_TO_PX;

        function fitToSingleA4() {
            page.style.zoom = '1';
            page.style.width = '100%';

            const contentHeight = page.scrollHeight;
            if (contentHeight <= printableHeight) return;

            const scale = Math.max(
                0.82,
                Math.min(1, (printableHeight / contentHeight) * 0.992)
            );

            page.style.zoom = String(scale);
            page.style.width = (100 / scale) + '%';
        }

        fitToSingleA4();
        requestAnimationFrame(fitToSingleA4);
    })();
</script>
</body>
</html>
