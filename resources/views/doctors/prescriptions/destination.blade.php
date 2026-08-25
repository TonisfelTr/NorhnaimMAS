<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Направление</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;

            background: #fff;
            color: #111;

            font-family:
                Arial,
                "DejaVu Sans",
                sans-serif;

            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-size: 12pt;
            line-height: 1.25;
        }

        .sheet {
            position: relative;

            width: 210mm;
            min-height: 297mm;

            padding: 12mm 14mm 13mm;

            background: #fff;
        }

        .sheet::before {
            content: "";

            position: absolute;
            inset: 5mm;

            border: .35mm solid #111;

            pointer-events: none;
        }

        .head {
            position: relative;

            min-height: 43mm;

            padding-top: 7mm;
        }

        .stamp-box {
            position: absolute;

            top: 0;
            left: 0;

            width: 65mm;
            height: 30mm;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 4mm;

            border: .35mm dashed #444;
            border-radius: 2mm;

            color: #333;

            font-size: 9pt;
            line-height: 1.35;

            text-align: center;
            text-transform: uppercase;
        }

        .title {
            padding-left: 70mm;
            padding-top: 6mm;

            text-align: center;
        }

        .title h1 {
            margin: 0;

            font-size: 20pt;
            font-weight: 700;

            letter-spacing: .2mm;

            text-transform: uppercase;
        }

        .title .sub {
            margin-top: 4mm;

            font-size: 12pt;
            font-weight: 600;

            text-transform: uppercase;
        }

        .fields {
            margin-top: 4mm;
        }

        .row {
            display: flex;
            align-items: flex-end;

            gap: 3mm;

            min-height: 10mm;
        }

        .row.large {
            min-height: 12mm;
        }

        .label {
            flex: 0 0 auto;

            font-weight: 600;

            white-space: nowrap;
        }

        .value {
            min-width: 0;
            flex: 1;

            min-height: 6mm;

            padding: 0 1mm 1mm;

            border-bottom: .25mm solid #333;

            font-size: 11.5pt;
        }

        .hint {
            margin-top: 1mm;

            color: #444;

            font-size: 8.5pt;

            text-align: center;
        }

        .two {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 8mm;
        }

        .block {
            margin-top: 4mm;
        }

        .block-label {
            margin-bottom: 2mm;

            font-weight: 600;
        }

        .multi {
            min-height: 20mm;

            padding: 1mm 1mm 0;

            background-image:
                linear-gradient(
                    to bottom,
                    transparent 7.3mm,
                    #333 7.3mm,
                    #333 7.55mm,
                    transparent 7.55mm
                );

            background-size:
                100% 8mm;

            font-size: 11.5pt;
            line-height: 8mm;

            white-space: pre-wrap;
        }

        .footer {
            margin-top: 15mm;
        }

        .referrer {
            margin-bottom: 15mm;
        }

        .referrer-note {
            max-width: 100mm;

            margin-top: 2mm;

            color: #333;

            font-size: 8.5pt;
            line-height: 1.35;
        }

        .sign-row {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18mm;

            align-items: end;
        }

        .sign-item {
            display: flex;
            align-items: flex-end;

            gap: 3mm;

            min-height: 14mm;
        }

        .sign-line {
            flex: 1;

            height: 9mm;

            border-bottom: .25mm solid #333;
        }

        .screen-only {
            position: fixed;

            z-index: 20;

            top: 12px;
            right: 12px;
        }

        .print-btn {
            padding: 8px 12px;

            border: 1px solid #444;
            border-radius: 5px;

            background: #fff;
            color: #111;

            cursor: pointer;

            font: inherit;
        }

        @media print {
            .screen-only {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="sheet">

    <header class="head">

        <div class="stamp-box">
            Место для печати<br>
            медицинской организации<br>
            (или врача при частной практике)
        </div>


        <div class="title">

            <h1>
                @if($isExternal)
                    Направление к специалисту
                @else
                    Направление к врачу
                @endif
            </h1>

            <div class="sub">
                Медицинский документ
            </div>

        </div>

    </header>


    <main class="fields">

        <div class="row large">

            <div class="label">
                Пациент:
            </div>

            <div class="value">
                {{ $patientName }}
            </div>

        </div>

        <div class="hint">
            (Фамилия, Имя, Отчество)
        </div>


        <div class="row">

            <div class="label">
                Дата рождения:
            </div>

            <div class="value">
                {{ $birthAt }}
            </div>

        </div>


        <div class="row">

            <div class="label">
                Место работы:
            </div>

            <div class="value">
                {{ $workplace }}
            </div>

        </div>


        <div class="two">

            <div class="row">

                <div class="label">
                    № карты:
                </div>

                <div class="value">
                    {{ $cardNumber }}
                </div>

            </div>


            <div class="row">

                <div class="label">
                    СНИЛС:
                </div>

                <div class="value">
                    {{ $snils }}
                </div>

            </div>

        </div>


        @if($isExternal)

            <div class="row">

                <div class="label">
                    Медицинская организация:
                </div>

                <div class="value">
                    {{ $organization }}
                </div>

            </div>


            <div class="row">

                <div class="label">
                    Специальность:
                </div>

                <div class="value">
                    {{ $speciality }}
                </div>

            </div>


            @if(filled($externalDoctorName))

                <div class="row">

                    <div class="label">
                        Врач (ФИО):
                    </div>

                    <div class="value">
                        {{ $externalDoctorName }}
                    </div>

                </div>

            @endif

        @else

            <div class="row">

                <div class="label">
                    Направлен к врачу
                    (специальность):
                </div>

                <div class="value">
                    {{ $speciality }}
                </div>

            </div>


            <div class="row">

                <div class="label">
                    Врач (ФИО):
                </div>

                <div class="value">
                    {{ $recipientDoctorName }}
                </div>

            </div>

        @endif


        <div class="block">

            <div class="block-label">
                Цель направления:
            </div>

            <div class="multi">{{ $purpose }}</div>

        </div>


        <div class="block">

            <div class="block-label">
                Клинический диагноз:
            </div>

            <div class="multi">{{ $diagnosisText }}</div>

        </div>


        <div class="row">

            <div class="label">
                Дата направления:
            </div>

            <div class="value">
                {{ $referralDate }}
            </div>

        </div>


        <div class="block">

            <div class="block-label">
                Особые сведения
                (при необходимости):
            </div>

            <div class="multi">{{ $comment }}</div>

        </div>

    </main>


    <footer class="footer">

        <div class="referrer">

            <div class="row">

                <div class="label">
                    Кто направляет:
                </div>

                <div class="value">
                    {{ $referrerLabel }}
                </div>

            </div>


            <div class="referrer-note">
                Наименование медицинской организации
                или Фамилия И. О. врача при частной практике
            </div>

        </div>


        <div class="sign-row">

            <div class="sign-item">

                <div class="label">
                    Подпись врача:
                </div>

                <div class="sign-line"></div>

            </div>


            <div class="sign-item">

                <div class="label">
                    Печать:
                </div>

                <div class="sign-line"></div>

            </div>

        </div>

    </footer>

</div>

</body>
</html>

