<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Лабораторное исследование' }}</title>
    <style>
        @page {
            size: A4;
            margin: 16mm;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 8px 28px rgba(0,0,0,.18);
            padding: 18mm 16mm 16mm;
            box-sizing: border-box;
        }

        .print-toolbar {
            width: 210mm;
            margin: 12px auto;
            text-align: right;
        }

        .btn-print {
            padding: 9px 16px;
            border: 1px solid #0f766e;
            background: #0f766e;
            color: #fff;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #115e59;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-right {
            text-align: right;
            width: 180px;
        }

        .clinic-name {
            font-size: 24px;
            font-weight: 700;
            color: #0f766e;
            margin-bottom: 4px;
        }

        .clinic-meta {
            font-size: 12px;
            line-height: 1.5;
            color: #4b5563;
        }

        .doc-form {
            font-size: 12px;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .doc-number {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
        }

        .title {
            text-align: center;
            margin: 20px 0 8px;
            font-size: 28px;
            font-weight: 800;
            color: #111827;
        }

        .subtitle {
            text-align: center;
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 20px;
        }

        .card {
            border: 1px solid #dbe3ea;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 8px 10px;
        }

        .label {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .value {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            line-height: 1.4;
        }

        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f766e;
            margin: 18px 0 10px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #dbe3ea;
            padding: 10px 8px;
            font-size: 14px;
            vertical-align: top;
        }

        table.data-table th {
            background: #f8fafc;
            font-weight: 700;
            color: #111827;
            text-align: left;
        }

        .comment-box {
            border: 1px solid #dbe3ea;
            border-radius: 12px;
            padding: 14px 16px;
            min-height: 70px;
            font-size: 14px;
            line-height: 1.5;
            color: #111827;
        }

        .signatures {
            margin-top: 42px;
            display: table;
            width: 100%;
        }

        .sign-col {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 16px;
        }

        .sign-line {
            border-top: 1px solid #9ca3af;
            margin-top: 34px;
            padding-top: 6px;
            font-size: 12px;
            color: #4b5563;
        }

        .footer {
            margin-top: 28px;
            display: table;
            width: 100%;
            font-size: 12px;
            color: #6b7280;
        }

        .footer-left,
        .footer-right {
            display: table-cell;
        }

        .footer-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
            font-weight: 400;
        }

        @media print {
            body {
                background: #fff;
            }

            .print-toolbar {
                display: none !important;
            }

            .print-page {
                box-shadow: none;
                margin: 0;
                width: auto;
                min-height: auto;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="print-toolbar">
    <button class="btn-print" onclick="window.print()">Печать</button>
</div>

<div class="print-page">
    <div class="header">
        <div class="header-left">
            <div class="clinic-name">{{ $clinicName ?? 'Медицинская организация' }}</div>
            <div class="clinic-meta">
                {{ $clinicAddress ?? 'Адрес организации' }}<br>
                {{ $clinicPhone ?? 'Телефон организации' }}
            </div>
        </div>

        <div class="header-right">
            @if(!empty($documentForm))
                <div class="doc-form">{{ $documentForm }}</div>
            @endif

            @if(!empty($documentNumber))
                <div class="doc-number">№ {{ $documentNumber }}</div>
            @endif
        </div>
    </div>

    <div class="title">{{ $documentTitle }}</div>

    @if(!empty($documentSubtitle))
        <div class="subtitle">{{ $documentSubtitle }}</div>
    @endif

    <div class="card">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-col">
                    <div class="label">Пациент</div>
                    <div class="value">{{ $patient->full_name ?? $patient->name ?? '—' }}</div>
                </div>
                <div class="info-col">
                    <div class="label">Дата рождения</div>
                    <div class="value">{{ $patient->birth_date ?? '—' }}</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-col">
                    <div class="label">Направивший врач</div>
                    <div class="value">{{ $doctorName ?? '—' }}</div>
                </div>
                <div class="info-col">
                    <div class="label">Биоматериал</div>
                    <div class="value">{{ $sampleType ?? '—' }}</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-col">
                    <div class="label">
                        {{ $isResult ? 'Дата исследования' : 'Планируемая дата сдачи' }}
                    </div>
                    <div class="value">{{ $researchDate ?? '—' }}</div>
                </div>
                <div class="info-col">
                    <div class="label">Приоритет</div>
                    <div class="value">{{ $priorityLabel ?? '—' }}</div>
                </div>
            </div>

            @if(!empty($diagnosisText))
                <div class="info-row">
                    <div class="info-col" colspan="2" style="display: table-cell; width: 100%; padding: 8px 10px;">
                        <div class="label">Диагноз / код МКБ</div>
                        <div class="value">{{ $diagnosisText }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="section-title">
        {{ $isResult ? 'Результаты исследования' : 'Назначенные показатели' }}
    </div>

    <table class="data-table">
        <thead>
        <tr>
            <th style="width: 40px;">№</th>
            <th>Показатель</th>
            <th style="width: 180px;">Норма</th>
            @if($isResult)
                <th style="width: 140px;">Результат</th>
            @endif
            <th style="width: 120px;">Ед. изм.</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['name'] ?? '—' }}</td>
                <td>{{ $item['reference'] ?? '—' }}</td>
                @if($isResult)
                    <td>{{ $item['value'] ?? '—' }}</td>
                @endif
                <td>{{ $item['unit'] ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $isResult ? 5 : 4 }}" style="text-align: center;">Нет данных</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if(!empty($comment))
        <div class="section-title">Комментарий врача</div>
        <div class="comment-box">
            {{ $comment }}
        </div>
    @endif

    <div class="signatures">
        <div class="sign-col">
            <div class="sign-line">Подпись врача</div>
        </div>
        <div class="sign-col">
            <div class="sign-line">{{ $isResult ? 'Печать / подпись лаборатории' : 'Подпись пациента' }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">
            Сформировано: {{ $generatedAt ?? now()->format('d.m.Y H:i') }}
        </div>
        <div class="footer-right">
            {{ $documentCode ?? '' }}
        </div>
    </div>
</div>

</body>
</html>
