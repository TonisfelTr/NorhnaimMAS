<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Лабораторное направление №{{ $labResearch->id }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f5;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.45;
        }

        .print-toolbar {
            display: flex;
            width: 210mm;
            margin: 18px auto 10px;
            justify-content: flex-end;
            gap: 8px;
        }

        .print-button {
            min-height: 38px;
            padding: 8px 14px;
            border: 1px solid #0793a4;
            border-radius: 8px;
            background: #0793a4;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px;
            padding: 16mm 17mm;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .12);
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 22px;
            padding-bottom: 13px;
            border-bottom: 2px solid #0793a4;
        }

        .clinic-name {
            color: #057786;
            font-size: 18px;
            font-weight: 800;
        }

        .clinic-details {
            margin-top: 5px;
            color: #5f6b7a;
            font-size: 11px;
            line-height: 1.5;
        }

        .document-number {
            flex: 0 0 auto;
            text-align: right;
        }

        .document-number__label {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
        }

        .document-number__value {
            margin-top: 3px;
            font-size: 18px;
            font-weight: 800;
        }

        h1 {
            margin: 22px 0 5px;
            font-size: 22px;
            line-height: 1.25;
            text-align: center;
        }

        .subtitle {
            margin-bottom: 20px;
            color: #6b7280;
            text-align: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
            margin-bottom: 20px;
            border: 1px solid #dce3e9;
            border-radius: 10px;
            overflow: hidden;
        }

        .info-item {
            min-height: 68px;
            padding: 11px 13px;
            border-right: 1px solid #e6ebef;
            border-bottom: 1px solid #e6ebef;
        }

        .info-item:nth-child(2n) {
            border-right: 0;
        }

        .info-item:nth-last-child(-n + 2) {
            border-bottom: 0;
        }

        .info-label {
            margin-bottom: 5px;
            color: #778292;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .section-title {
            margin: 20px 0 8px;
            color: #057786;
            font-size: 15px;
            font-weight: 800;
        }

        .parameter-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dce3e9;
        }

        .parameter-table th,
        .parameter-table td {
            padding: 9px 10px;
            border-right: 1px solid #e6ebef;
            border-bottom: 1px solid #e6ebef;
            text-align: left;
            vertical-align: top;
        }

        .parameter-table th:last-child,
        .parameter-table td:last-child {
            border-right: 0;
        }

        .parameter-table tr:last-child td {
            border-bottom: 0;
        }

        .parameter-table th {
            background: #f5f8fa;
            color: #5f6b7a;
            font-size: 11px;
        }

        .group-row td {
            background: #eaf8fa;
            color: #057786;
            font-weight: 800;
        }

        .comment-box {
            min-height: 70px;
            padding: 12px 13px;
            border: 1px solid #dce3e9;
            border-radius: 9px;
            white-space: pre-wrap;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 34px;
            margin-top: 34px;
        }

        .signature-line {
            padding-top: 26px;
            border-bottom: 1px solid #525f6f;
        }

        .signature-label {
            margin-top: 5px;
            color: #778292;
            font-size: 10px;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e2e8ee;
            color: #8a94a4;
            font-size: 10px;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: #fff;
            }

            .print-toolbar {
                display: none;
            }

            .sheet {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<div class="print-toolbar">
    <button class="print-button" type="button" onclick="window.print()">
        Печать
    </button>
</div>

<main class="sheet">
    <header class="header">
        <div>
            <div class="clinic-name">
                {{ data_get($clinic, 'name', config('app.name', 'Медицинская организация')) }}
            </div>
            <div class="clinic-details">
                @if(data_get($clinic, 'address'))
                    <div>{{ data_get($clinic, 'address') }}</div>
                @endif
                @if(data_get($clinic, 'phone'))
                    <div>Телефон: {{ data_get($clinic, 'phone') }}</div>
                @endif
                @if(data_get($clinic, 'email'))
                    <div>{{ data_get($clinic, 'email') }}</div>
                @endif
            </div>
        </div>

        <div class="document-number">
            <div class="document-number__label">Номер направления</div>
            <div class="document-number__value">№ {{ $labResearch->id }}</div>
        </div>
    </header>

    <h1>Направление на лабораторное исследование</h1>
    <div class="subtitle">
        {{ $labResearch->laboratory ?: 'Лабораторное исследование' }}
    </div>

    <section class="info-grid">
        <div class="info-item">
            <div class="info-label">Пациент</div>
            <div class="info-value">{{ $patientName }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Дата рождения</div>
            <div class="info-value">{{ $patientBirthDate }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Направивший врач</div>
            <div class="info-value">{{ $doctorName }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Биоматериал</div>
            <div class="info-value">{{ $labResearch->sample_type ?: 'Не указан' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Планируемая дата сдачи</div>
            <div class="info-value">
                {{ $labResearch->planned_at ? \Illuminate\Support\Carbon::parse($labResearch->planned_at)->format('d.m.Y') : '—' }}
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Приоритет</div>
            <div class="info-value">{{ $priorityLabel }}</div>
        </div>
    </section>

    <div class="section-title">Назначенные показатели</div>
    <table class="parameter-table">
        <thead>
        <tr>
            <th style="width: 44px">№</th>
            <th>Показатель</th>
            <th style="width: 140px">Единица измерения</th>
        </tr>
        </thead>
        <tbody>
        @php($parameterNumber = 0)
        @forelse($parameterGroups as $groupName => $groupParameters)
            <tr class="group-row">
                <td colspan="3">{{ $groupName }}</td>
            </tr>
            @foreach($groupParameters as $parameter)
                @php($parameterNumber++)
                <tr>
                    <td>{{ $parameterNumber }}</td>
                    <td>{{ $parameter->name }}</td>
                    <td>{{ $parameter->unit ?: '—' }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="3">Показатели не найдены.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if(trim((string) $labResearch->comment) !== '')
        <div class="section-title">Комментарий врача</div>
        <div class="comment-box">{{ $labResearch->comment }}</div>
    @endif

    <section class="signatures">
        <div>
            <div class="signature-line"></div>
            <div class="signature-label">Подпись врача</div>
        </div>
        <div>
            <div class="signature-line"></div>
            <div class="signature-label">Подпись пациента</div>
        </div>
    </section>

    <footer class="footer">
        <span>Сформировано: {{ $printedAt }}</span>
        <span>Направление №{{ $labResearch->id }}</span>
    </footer>
</main>

@if($autoPrint)
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.print();
            }, 250);
        });
    </script>
@endif
</body>
</html>
