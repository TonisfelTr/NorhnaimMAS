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
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 18px auto;
        }

        .print-toolbar__button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            padding: 9px 15px;
            border: 1px solid #cfd8df;
            border-radius: 9px;
            background: #fff;
            color: #263241;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .print-toolbar__button--primary {
            border-color: #048696;
            background: #048696;
            color: #fff;
        }

        .direction-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px;
            padding: 16mm 15mm;
            background: #fff;
            box-shadow: 0 18px 55px rgba(15, 23, 42, .14);
        }

        .direction-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            padding-bottom: 14px;
            border-bottom: 2px solid #111827;
        }

        .direction-clinic {
            max-width: 125mm;
        }

        .direction-clinic__name {
            font-size: 15px;
            font-weight: 800;
        }

        .direction-clinic__meta {
            margin-top: 4px;
            color: #4b5563;
            font-size: 11px;
        }

        .direction-number {
            min-width: 48mm;
            text-align: right;
        }

        .direction-number__label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .direction-number__value {
            margin-top: 3px;
            font-size: 17px;
            font-weight: 800;
        }

        .direction-title {
            margin: 20px 0 5px;
            text-align: center;
            font-size: 21px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .direction-subtitle {
            margin-bottom: 20px;
            color: #4b5563;
            text-align: center;
        }

        .direction-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
            margin-bottom: 18px;
            border-top: 1px solid #9ca3af;
            border-left: 1px solid #9ca3af;
        }

        .direction-field {
            min-height: 58px;
            padding: 9px 11px;
            border-right: 1px solid #9ca3af;
            border-bottom: 1px solid #9ca3af;
        }

        .direction-field--wide {
            grid-column: span 2;
        }

        .direction-field__label {
            margin-bottom: 3px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .direction-field__value {
            font-size: 13px;
            font-weight: 700;
        }

        .direction-section-title {
            margin: 19px 0 8px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .direction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .direction-table th,
        .direction-table td {
            padding: 8px 9px;
            border: 1px solid #9ca3af;
            vertical-align: top;
        }

        .direction-table th {
            background: #f3f4f6;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
        }

        .direction-comment {
            min-height: 68px;
            padding: 10px 11px;
            border: 1px solid #9ca3af;
            white-space: pre-wrap;
        }

        .direction-signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            margin-top: 38px;
        }

        .direction-signature {
            padding-top: 18px;
            border-top: 1px solid #111827;
            color: #4b5563;
            font-size: 11px;
            text-align: center;
        }

        .direction-footer {
            margin-top: 28px;
            color: #6b7280;
            font-size: 10px;
            text-align: center;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                background: #fff;
            }

            .print-toolbar {
                display: none !important;
            }

            .direction-sheet {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <a
            class="print-toolbar__button"
            href="{{ route('doctors.analyses.assignments') }}"
        >
            ← К назначениям
        </a>

        <button
            class="print-toolbar__button print-toolbar__button--primary"
            type="button"
            onclick="window.print()"
        >
            Печать
        </button>
    </div>

    <main class="direction-sheet">
        <header class="direction-header">
            <div class="direction-clinic">
                <div class="direction-clinic__name">
                    {{ $clinic?->name ?: config('app.name', 'Медицинская организация') }}
                </div>
                <div class="direction-clinic__meta">
                    {{ $clinic?->address ?: 'Адрес медицинской организации не указан' }}
                    @if($clinic?->phone)
                        · {{ $clinic->phone }}
                    @endif
                </div>
            </div>

            <div class="direction-number">
                <div class="direction-number__label">Номер направления</div>
                <div class="direction-number__value">№ {{ $labResearch->id }}</div>
            </div>
        </header>

        <h1 class="direction-title">Лабораторное направление</h1>
        <div class="direction-subtitle">
            {{ $labResearch->laboratory ?: 'Лабораторное исследование' }}
        </div>

        <section class="direction-grid">
            <div class="direction-field direction-field--wide">
                <div class="direction-field__label">Пациент</div>
                <div class="direction-field__value">{{ $patientName }}</div>
            </div>

            <div class="direction-field">
                <div class="direction-field__label">Дата рождения</div>
                <div class="direction-field__value">
                    {{ $patientBirthDate }}
                </div>
            </div>

            <div class="direction-field">
                <div class="direction-field__label">Дата назначения</div>
                <div class="direction-field__value">
                    {{ $labResearch->created_at?->format('d.m.Y H:i') ?: '—' }}
                </div>
            </div>

            <div class="direction-field">
                <div class="direction-field__label">Планируемая дата сдачи</div>
                <div class="direction-field__value">
                    {{ $labResearch->planned_at?->format('d.m.Y') ?: '—' }}
                </div>
            </div>

            <div class="direction-field">
                <div class="direction-field__label">Приоритет</div>
                <div class="direction-field__value">{{ $priorityLabel }}</div>
            </div>

            <div class="direction-field">
                <div class="direction-field__label">Биоматериал</div>
                <div class="direction-field__value">
                    {{ $labResearch->sample_type ?: '—' }}
                </div>
            </div>

            <div class="direction-field">
                <div class="direction-field__label">Направивший врач</div>
                <div class="direction-field__value">{{ $doctorName }}</div>
            </div>
        </section>

        <h2 class="direction-section-title">Назначенные исследования</h2>

        <table class="direction-table">
            <thead>
            <tr>
                <th style="width: 10%;">№</th>
                <th>Показатель / исследование</th>
                <th style="width: 23%;">Группа</th>
                <th style="width: 17%;">Ед. измерения</th>
            </tr>
            </thead>
            <tbody>
            @forelse($parameters as $index => $parameter)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $parameter->name }}</td>
                    <td>{{ $parameter->group ?: '—' }}</td>
                    <td>{{ $parameter->unit ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Показатели не найдены.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if(filled($labResearch->comment))
            <h2 class="direction-section-title">Клиническая информация</h2>
            <div class="direction-comment">{{ $labResearch->comment }}</div>
        @endif

        <div class="direction-signatures">
            <div class="direction-signature">
                Подпись врача / расшифровка
            </div>
            <div class="direction-signature">
                Дата и время приёма материала
            </div>
        </div>

        <div class="direction-footer">
            Направление сформировано в информационной системе
            {{ $printedAt }}.
        </div>
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
