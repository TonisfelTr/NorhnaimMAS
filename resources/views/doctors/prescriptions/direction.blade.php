<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Направление на исследования — Форма 057/у-04-1 (шаблон)</title>
    <style>
        /* Параметры страницы для A4 */
        @page { size: A4; margin: 15mm; }
        html,body{font-family: "DejaVu Sans", Roboto, Arial, sans-serif; color:#111;}
        body{margin:0; padding:0; font-size:12px; line-height:1.35; -webkit-font-smoothing:antialiased}

        .sheet{width:100%; max-width:170mm; margin:0 auto; box-sizing:border-box; padding:6mm 6mm 0 6mm}
        .header{display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:8px}
        .clinic{font-weight:700; line-height:1.15}
        .clinic .name{font-size:13px}
        .clinic .addr{font-size:11px; color:#333; margin-top:4px}

        .meta{font-size:11px; text-align:right; color:#222}
        .meta .form-no {font-weight:700; margin-bottom:2px}
        .meta .cito {font-size:14px; font-weight:700; color:#111; margin-top:6px}

        h1{font-size:16px; margin:6px 0 8px 0; text-align:center; letter-spacing:0.2px}
        .sub{font-size:12px; margin-bottom:6px}

        /* Инфо о пациенте */
        table.info{width:100%; border-collapse:collapse; margin-bottom:8px}
        table.info td{padding:6px 6px; vertical-align:top}
        .info .label{font-weight:600; min-width:110px; display:inline-block; color:#111}
        .info .value{display:inline-block; min-width:160px; border-bottom:1px dotted #999; padding:2px 4px; color:#111}

        /* Блок МКБ (видно и чётко отделено) */
        .icd-box {border:1px dashed #999; padding:8px; margin-bottom:10px; min-height:36px; background:transparent}
        .icd-box .code {font-weight:700; margin-bottom:4px; display:block}

        /* Таблица результатов — главная часть */
        .tests { margin-bottom:10px; }
        table.tests-table { width:100%; border-collapse:collapse; font-size:12px; }
        table.tests-table thead th {
            text-align:left;
            padding:8px 10px;
            background:#f6f6f6;
            border-bottom:2px solid #222;
            font-weight:700;
        }
        table.tests-table tbody td {
            padding:7px 10px;
            border-top:1px solid #e1e1e1;
            vertical-align:middle;
        }
        table.tests-table tbody tr:nth-child(even) td { background: #fbfbfb; }
        table.tests-table .col-name { width:34%; }
        table.tests-table .col-ref { width:28%; color:#333 }
        table.tests-table .col-result { width:22%; text-align:center; font-weight:600 }
        table.tests-table .col-unit { width:16%; text-align:right; color:#333 }

        /* Подпись/штамп */
        .sign{display:flex; justify-content:space-between; align-items:flex-end; gap:12px; margin-top:14px}
        .sign .left{font-size:12px}
        .sign .right{width:220px; text-align:center}
        .stamp{height:56px; border:1px dashed #333; margin-top:6px}

        .small{font-size:11px; color:#444}

        /* Печатный вид: убрать UI элементы */
        @media print{
            body{background:transparent}
            .no-print{display:none}
            .sheet{box-shadow:none; margin:0}
        }

        /* Мелкие вспомогательные */
        .muted{color:#666}

        /* рядом с .meta */
        .meta { font-size:11px; text-align:right; color:#222; display:flex; flex-direction:column; align-items:flex-end; gap:4px; }

        /* Cito + box row */
        .cito-row { display:inline-flex; align-items:center; gap:8px; font-weight:700; }

        /* сам квадратик для ручной пометки */
        .cito-box {
            width:20px;
            height:20px;
            border:1px solid #222;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-size:12px;
            line-height:1;
            padding:0;
            box-sizing:border-box;
            background:transparent;
        }

        /* если нужно оставить место пустым при печати, можно вписать &nbsp; внутрь .cito-box */
    </style>
</head>
<body>
<div class="sheet">
    <div class="header">
        <div class="clinic">
            <div class="name">{{ $clinic->name }}</div>
            <div class="addr small">{{ $clinic->address }} | тел. {{ $clinic->phone }}</div>
        </div>

        <div class="meta" role="region" aria-label="Информация формы">
            <div class="form-no">Форма № 057/у-04-1</div>

            <div class="cito-row" style="margin-top:6px;">
                <div class="cito" aria-hidden="true">Cito</div>
                <div class="cito-box" contenteditable="true" aria-label="Поле для пометки Cito" title="Впишите букву или цифру вручную"></div>
            </div>
        </div>
    </div>

    <h1>Направление на исследования</h1>

    <table class="info" role="presentation">
        <tr>
            <td style="width:60%">
                <div><span class="label">Дата:</span> <span class="value">{{ Carbon\Carbon::now()->format('d.m.Y') }}</span></div>
                <div style="margin-top:6px"><span class="label">Врач (Ф.И.О., должность):</span><span class="value">{{ optional($patient->doctor()->first())->full_name ?? '—' }}, {{ optional($patient->doctor()->first())->status ?? '' }}</span></div>
                <div style="margin-top:6px"><span class="label">Пациент:</span> <span class="value">{{ $patient->full_name }}</span></div>
                <div style="margin-top:6px"><span class="label">Дата рождения:</span> <span class="value">{{ $patient->birth_at }}</span></div>
                <div style="margin-top:6px"><span class="label">Адрес:</span> <span class="value">{{ $patient->address_registration }}</span></div>
                <div style="margin-top:6px"><span class="label">Полис ОМС:</span> <span class="value">{{ $patient->oms }}</span></div>
                <br>
                <div style="margin-top:6px"><span class="label">Исследуемый материал:</span> <span class="value">{{ $labResearch->sample_type }}</span></div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom:6px"><strong>Код болезни по МКБ:</strong></div>
    <div class="icd-box">
        <span class="code">{{ optional($patient->diagnose()->first())->code ?? '—' }} {{ optional($patient->diagnose()->first())->title ?? '' }}</span>
        {{-- при желании: краткое описание/стадия и т.п. --}}
    </div>

    <div class="tests">
        <table class="tests-table" role="table" aria-label="Результаты анализов">
            <thead>
            <tr>
                <th class="col-name">Наименование показателя</th>
                <th class="col-ref">Показатели нормы</th>
                <th class="col-result">Результат анализа</th>
                <th class="col-unit">Ед. изм.</th>
            </tr>
            </thead>
            <tbody>
            @forelse($parameters as $parameter)
                <tr>
                    <td class="col-name">{{ $parameter['parameter_name'] ?? '—' }}</td>
                    <td class="col-ref">{!! nl2br(e($parameter['reference_text'] ?: '—')) !!}</td>
                    <td class="col-result">{{ $parameter['formatted_result'] !== '' ? $parameter['formatted_result'] : ($parameter['result_raw'] ?? '—') }}</td>
                    <td class="col-unit">{{ $parameter['unit'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:12px 8px; color:#666">Параметры не выбраны</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="sign">
        <div class="left">
            <div style="margin-top:6px"><strong>Подпись:</strong> ____________________</div>
        </div>
        <div class="right">
            <div class="small">Место для печати/штампа</div>
            <div class="stamp" aria-hidden="true"></div>
        </div>
    </div>
</div>
</body>
</html>
