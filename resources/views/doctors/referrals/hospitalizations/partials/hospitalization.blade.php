<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Направление в стационар</title>

    <style>

        @page {
            size: A4 portrait;
            margin: 4mm 5mm;
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
            font-family: Arial, "Trebuchet MS", sans-serif;
            font-size: 11.5pt;
            line-height: 1.06;
        }

        body {
            width: 100%;
        }

        .document {
            width: 100%;
            margin: 0 auto;
        }

        .document-header {
            margin-bottom: 1.2mm;
            text-align: center;
        }

        .document-title {
            margin: 0;
            font-size: 18pt;
            line-height: 1;
            font-weight: 700;
            text-transform: uppercase;
        }

        .document-subtitle {
            margin-top: .4mm;
            font-size: 10.8pt;
        }

        .section {
            margin-top: 1mm;
            break-inside: auto;
        }

        .section-title {
            margin: 0 0 .4mm;
            font-size: 11.2pt;
            line-height: 1.05;
            font-weight: 700;
        }

        .section-number {
            display: inline-block;
            width: 5.5mm;
        }

        .row {
            display: flex;
            align-items: flex-end;
            gap: 1mm;
            min-height: 3.25mm;
            margin-bottom: .15mm;
        }

        .row.wrap {
            flex-wrap: wrap;
        }

        .label {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .fill {
            min-width: 0;
            flex: 1 1 auto;
            min-height: 3mm;
            padding: 0 .45mm .12mm;
            border-bottom: .18mm solid #000;
            overflow-wrap: anywhere;
        }

        .fill.short {
            flex: 0 0 24mm;
        }

        .fill.medium {
            flex: 0 0 48mm;
        }

        .fill.long {
            flex: 1 1 70mm;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .3mm 3mm;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .3mm 2mm;
        }

        .checks {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5mm 3.6mm;
            min-height: 3.3mm;
        }

        .check {
            display: inline-flex;
            align-items: center;
            gap: .7mm;
            white-space: nowrap;
        }

        .box {
            display: inline-flex;
            width: 3.5mm;
            height: 3.5mm;
            align-items: center;
            justify-content: center;
            border: .18mm solid #000;
            font-size: 9.2pt;
            line-height: 1;
        }

        .ruled {
            min-height: 8.5mm;
            padding: .15mm .35mm 0;
            background:
                repeating-linear-gradient(
                    to bottom,
                    transparent 0,
                    transparent 4.2mm,
                    #000 4.2mm,
                    #000 4.35mm
                );
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .ruled.two {
            min-height: 7.2mm;
        }

        .ruled.three {
            min-height: 9.8mm;
        }

        .ruled.four {
            min-height: 12.5mm;
        }

        .value {
            background: #fff;
            padding-right: .4mm;
            box-decoration-break: clone;
            -webkit-box-decoration-break: clone;
        }

        .small {
            font-size: 9.2pt;
        }

        .muted {
            color: #222;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1.7fr .55fr;
            gap: 1.5mm 5mm;
            align-items: end;
        }

        .stamp {
            min-height: 8.5mm;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            font-weight: 700;
        }

        .no-break {
            break-inside: avoid;
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
<!-- HOSPITALIZATION_PRINT_TEMPLATE_V3 -->
<main class="document">
    <header class="document-header">
        <h1 class="document-title">Направление в стационар</h1>
        <div class="document-subtitle">
            для плановой или экстренной госпитализации
        </div>
    </header>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">1.</span>Заголовочные данные
        </h2>

        <div class="row">
            <span class="label">Медицинская организация/частный врач:</span>
            <span class="fill">{{ $organizationName }}</span>
        </div>

        <div class="row">
            <span class="label">Адрес медицинской организации/офиса частного врача:</span>
            <span class="fill">{{ $organizationAddress }}</span>
        </div>

        <div class="grid-2">
            <div class="row">
                <span class="label">Телефон:</span>
                <span class="fill">{{ $organizationPhone }}</span>
            </div>

            <div class="row">
                <span class="label">E-mail:</span>
                <span class="fill">{{ $organizationEmail }}</span>
            </div>

            <div class="row">
                <span class="label">Дата и время оформления:</span>
                <span class="fill">{{ $issuedAt }}</span>
            </div>

            <div class="row">
                <span class="label">№ направления:</span>
                <span class="fill">{{ $documentNumber }}</span>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">2.</span>Вид госпитализации и способ направления
        </h2>

        <div class="checks">
            <span class="label">Вид госпитализации:</span>

            <span class="check">
                <span class="box">{{ $plannedMark }}</span>
                Плановая
            </span>

            <span class="check">
                <span class="box">{{ $emergencyMark }}</span>
                Экстренная
            </span>

            <span class="check">
                <span class="box">{{ $immediateMark }}</span>
                Неотложная
            </span>
        </div>

        <div class="checks">
            <span class="label">Способ направления:</span>

            <span class="check">
                <span class="box">{{ $selfTransportMark }}</span>
                Самостоятельно
            </span>

            <span class="check">
                <span class="box">{{ $ambulanceTransportMark }}</span>
                СМП
            </span>

            <span class="check">
                <span class="box"></span>
                Перевод из другой организации
            </span>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">3.</span>Сведения о пациенте
        </h2>

        <div class="row">
            <span class="label">Фамилия, имя, отчество:</span>
            <span class="fill">{{ $patientName }}</span>
        </div>

        <div class="grid-3">
            <div class="row">
                <span class="label">Дата рождения:</span>
                <span class="fill">{{ $birthDate }}</span>
            </div>

            <div class="row">
                <span class="label">Возраст:</span>
                <span class="fill">{{ $patientAge }}</span>
            </div>

            <div class="row">
                <span class="label">Пол:</span>
                <span class="fill">{{ $sex }}</span>
            </div>
        </div>

        <div class="row">
            <span class="label">Адрес регистрации:</span>
            <span class="fill">{{ $registrationAddress }}</span>
        </div>
        <div class="grid-2">
            <div class="row">
                <span class="label">ОМС:</span>
                <span class="fill">{{ $oms }}</span>
            </div>

            <div class="row">
                <span class="label">СНИЛС:</span>
                <span class="fill">{{ $snils }}</span>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">4.</span>Диагноз
        </h2>

        <div class="row">
            <span class="label">Предварительный диагноз:</span>
            <span class="fill">{{ $preliminaryDiagnosis }}</span>
        </div>

        <div class="row">
            <span class="label">Установленный диагноз пациента (если имеется):</span>
            <span class="fill">{{ $establishedDiagnosis }}</span>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">5.</span>Жалобы, анамнез и клиническое состояние
        </h2>

        <div class="ruled four">
            <span class="value">{{ $clinicalSummary }}</span>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">8.</span>Обоснование госпитализации
        </h2>

        <div class="ruled three">
            <span class="value">{{ $reason }}</span>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">9.</span>Стационар назначения
        </h2>

        <div class="row">
            <span class="label">Наименование стационара:</span>
            <span class="fill">{{ $destinationOrganization }}</span>
        </div>

        <div class="row">
            <span class="label">Адрес стационара:</span>
            <span class="fill">{{ $destinationAddress }}</span>
        </div>

        <div class="row">
            <span class="label">Цель госпитализации:</span>
            <span class="fill">{{ $treatmentGoal }}</span>
        </div>

        <div class="grid-2">
            <div class="row">
                <span class="label">Плановая дата:</span>
                <span class="fill">{{ $plannedAt }}</span>
            </div>

            <div class="row">
                <span class="label">Ожидаемая длительность:</span>
                <span class="fill">{{ $expectedDuration }}</span>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">
            <span class="section-number">10.</span>Особые отметки
        </h2>

        <div class="row">
            <span class="label">Дополнительные сведения:</span>
            <span class="fill">{{ $additionalNotes }}</span>
        </div>

        <div class="checks">
            <span class="label">Вызов СМП:</span>
            <span class="check">
                <span class="box">{{ $ambulanceRequiredMark }}</span>
                требуется
            </span>
            <span class="check">
                <span class="box">{{ $ambulanceNotRequiredMark }}</span>
                не требуется
            </span>
        </div>

        <div class="row">
            <span class="label">Транспортировка:</span>
            <span class="fill">{{ $transport }}</span>
        </div>
    </section>

    <section class="section no-break">
        <h2 class="section-title">
            <span class="section-number">11.</span>Подписи и отметки
        </h2>

        <div class="signature-grid">
            <div>
                <div class="row">
                    <span class="label">Направляющий врач:</span>
                    <span class="fill">{{ $doctorName }}</span>
                </div>

                <div class="row">
                    <span class="label">Должность / специальность:</span>
                    <span class="fill">
                        {{ $doctorSpeciality }}
                    </span>
                </div>

                <div class="row">
                    <span class="label">Подпись:</span>
                    <span class="fill"></span>
                </div>
            </div>

            <div class="stamp">М.П.</div>
        </div>

        <div class="row" style="margin-top: 3mm;">
            <span class="label">Пациент / законный представитель ознакомлен:</span>
            <span class="fill"></span>
        </div>

        <div class="row">
            <span class="label">Подпись пациента / представителя:</span>
            <span class="fill medium"></span>
            <span class="label">Дата:</span>
            <span class="fill short"></span>
        </div>
    </section>
</main>

<script>
    (function () {
        const page = document.querySelector('.document');

        if (!page) {
            return;
        }

        const MM_TO_PX = 96 / 25.4;
        const PRINTABLE_HEIGHT_MM = 289; // A4 297mm - 5mm сверху - 5mm снизу
        const printableHeight = PRINTABLE_HEIGHT_MM * MM_TO_PX;

        function fitToSingleA4() {
            page.style.zoom = '1';
            page.style.width = '100%';

            const contentHeight = page.scrollHeight;

            if (contentHeight <= printableHeight) {
                return;
            }

            const scale = Math.min(
                1,
                (printableHeight / contentHeight) * 0.985
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
