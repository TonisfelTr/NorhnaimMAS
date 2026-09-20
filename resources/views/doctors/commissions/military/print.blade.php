<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $printData['pageTitle'] }}</title>
    <style>
        :root {
            --ink: #24364a;
            --muted: #6f7f8f;
            --line: #c9d3dc;
            --line-strong: #9fadb9;
            --surface: #f5f8fa;
            --accent: #49687f;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            background: #edf1f4;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.45;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 14px auto;
            padding: 18mm 17mm;
            background: #fff;
            box-shadow: 0 8px 30px rgba(36, 54, 74, .12);
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent);
        }

        .header__org {
            margin-bottom: 14px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 7px;
            color: var(--ink);
            font-family: "Times New Roman", Times, serif;
            font-size: 21px;
            line-height: 1.2;
            letter-spacing: .025em;
            text-transform: uppercase;
        }

        .number {
            color: #3f5366;
            font-size: 14px;
            font-weight: 700;
        }

        .meta {
            width: 100%;
            margin: 18px 0 20px;
            border: 1px solid var(--line-strong);
            border-collapse: collapse;
        }

        .meta td {
            padding: 7px 10px;
            vertical-align: top;
            border: 1px solid var(--line);
        }

        .meta td:first-child {
            width: 31%;
            color: #3f5366;
            background: var(--surface);
            font-size: 12px;
            font-weight: 700;
        }

        .section {
            margin: 16px 0;
            page-break-inside: avoid;
        }

        .section h2 {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 6px;
            color: #3f5366;
            font-size: 12px;
            line-height: 1.25;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .section h2::before {
            width: 3px;
            height: 14px;
            flex: 0 0 3px;
            background: var(--accent);
            border-radius: 2px;
            content: "";
        }

        .section div {
            color: #202d3a;
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            line-height: 1.5;
            text-align: justify;
            white-space: pre-wrap;
        }

        .empty {
            color: var(--muted);
            font-style: italic;
        }

        .note {
            margin-top: 22px;
            padding: 10px 12px;
            color: #4f6070;
            background: var(--surface);
            border-left: 3px solid var(--accent);
            font-size: 11px;
            line-height: 1.45;
            page-break-inside: avoid;
        }

        .signature {
            margin-top: 24px;
            border: 1px solid var(--line-strong);
            page-break-inside: avoid;
        }

        .signature__title {
            padding: 7px 10px;
            color: #3f5366;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .signature__row {
            display: grid;
            grid-template-columns: 25% 20% 37% 18%;
            min-height: 42mm;
        }

        .signature__cell {
            display: flex;
            min-width: 0;
            padding: 7px 9px 6px;
            flex-direction: column;
            justify-content: flex-end;
            border-right: 1px solid var(--line);
        }

        .signature__cell:last-child { border-right: 0; }

        .signature__space { flex: 1 1 auto; }

        .signature__value {
            min-height: 23px;
            padding: 0 3px 3px;
            overflow-wrap: anywhere;
            border-bottom: 1px solid #536474;
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.25;
            text-align: center;
        }

        .signature__caption {
            min-height: 15px;
            padding-top: 3px;
            color: var(--muted);
            font-size: 9px;
            line-height: 1.2;
            text-align: center;
        }

        .signature__seal {
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            justify-content: center;
            color: #607181;
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            font-weight: 700;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 7px;
            color: var(--muted);
            border-top: 1px solid var(--line);
            font-size: 10px;
        }

        @page { size: A4; margin: 14mm 16mm; }

        @media print {
            body { background: #fff; }
            .sheet {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<article class="sheet">
    <header class="header">
        <div class="header__org">Медицинская организация</div>
        <h1>{{ $printData['documentType'] }}</h1>
        <div class="number">{{ $printData['documentHeading'] }}</div>
    </header>

    <table class="meta">
        <tr><td>Пациент</td><td>{{ $printData['patientFullName'] }}</td></tr>
        <tr><td>Дата рождения</td><td>{{ $printData['patientBirthDate'] }}</td></tr>
        <tr><td>Диагноз</td><td>{{ $printData['diagnosis'] }}</td></tr>
        <tr><td>Организация-получатель</td><td>{{ $printData['requestingOrganization'] }}</td></tr>
        <tr><td>Основание</td><td>{{ $printData['requestBasis'] }}</td></tr>
    </table>

    @foreach($printData['sections'] as $section)
        <section class="section">
            <h2>{{ $section['title'] }}</h2>
            <div>{{ $section['value'] }}</div>
        </section>
    @endforeach

    <div class="note">
        Документ содержит медицинские сведения и заключение врача. Категория годности к военной службе устанавливается уполномоченной военно-врачебной комиссией.
    </div>

    <div class="signature">
        <div class="signature__title">Подпись медицинского работника и печать</div>
        <div class="signature__row">
            <div class="signature__cell">
                <div class="signature__space"></div>
                <div class="signature__value">{{ auth()->user()->doctor->status }}</div>
                <div class="signature__caption">должность</div>
            </div>
            <div class="signature__cell">
                <div class="signature__space"></div>
                <div class="signature__value"></div>
                <div class="signature__caption">подпись</div>
            </div>
            <div class="signature__cell">
                <div class="signature__space"></div>
                <div class="signature__value">{{ $printData['creatorName'] }}</div>
                <div class="signature__caption">фамилия, имя, отчество</div>
            </div>
            <div class="signature__cell">
                <div class="signature__seal">М.П.</div>
                <div class="signature__caption">место печати</div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <span>Дата: {{ $printData['documentDate'] }}</span>
        <span>{{ $printData['footerDocumentNumber'] }}</span>
    </footer>
</article>
</body>
</html>
