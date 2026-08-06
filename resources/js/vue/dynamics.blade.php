@extends('doctors.analyses')

@section('analysis-title', 'Динамика лабораторных показателей')

@section('assets')
    @parent

    <style>
        .lab-filters {
            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr)
                minmax(0, 1.25fr)
                minmax(120px, .65fr)
                minmax(130px, 140px);
            gap: 16px;
            align-items: end;
            width: 100%;
            min-width: 0;
        }

        /* Родительские стили задают span для этих классов. На этой странице
           каждый фильтр должен занимать только одну колонку. */
        .lab-filters > .lab-filter--3,
        .lab-filters > .lab-filter--2,
        .lab-filters > .lab-filter--1,
        .lab-filters > .lab-filter {
            grid-column: auto !important;
            width: 100%;
            min-width: 0;
        }

        .lab-label {
            display: block;
            margin-bottom: 8px;
        }

        .lab-filters .lab-control,
        .lab-filters .lab-button,
        .lab-static-control {
            box-sizing: border-box;
            width: 100%;
            height: 42px;
            min-height: 42px;
            border-radius: 8px;
        }

        .lab-static-control {
            display: flex;
            align-items: center;
            padding: 0 12px;
            border: 1px solid #d9e1e8;
            background: #f8fafc;
            color: #1f2937;
        }

        .lab-filters .select2-container {
            width: 100% !important;
            min-width: 0;
        }

        .lab-filters .select2-container--default .select2-selection--single,
        .lab-filters .select2-container--bootstrap-5 .select2-selection {
            box-sizing: border-box;
            height: 42px !important;
            min-height: 42px !important;
            border-radius: 8px;
        }

        .lab-filters .select2-container--default .select2-selection--single {
            display: flex;
            align-items: center;
            padding: 0 36px 0 12px;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__rendered {
            width: 100%;
            padding: 0;
            line-height: 40px !important;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__arrow {
            top: 1px;
            right: 6px;
            height: 40px !important;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__clear {
            height: 40px;
            line-height: 38px;
        }

        .select2-container--open {
            z-index: 1100;
        }

        .lab-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
            gap: 20px;
            align-items: start;
        }

        .lab-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .lab-summary-card {
            padding: 14px 16px;
            border: 1px solid #e4ebf1;
            border-radius: 10px;
            background: #f8fafc;
        }

        .lab-summary-card__label {
            margin-bottom: 6px;
            color: #6b7280;
            font-size: 12px;
        }

        .lab-summary-card__value {
            color: #111827;
            font-size: 18px;
            font-weight: 700;
        }

        .lab-chart-wrap {
            overflow-x: auto;
            padding: 8px 0 4px;
        }

        .lab-chart {
            display: block;
            width: 100%;
            min-width: 680px;
            height: auto;
        }

        .lab-chart__grid-line {
            stroke: #e5edf3;
            stroke-width: 1;
        }

        .lab-chart__axis-text {
            fill: #7b8794;
            font-size: 12px;
        }

        .lab-chart__line {
            fill: none;
            stroke: #0793a4;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lab-chart__point {
            fill: #ffffff;
            stroke: #0793a4;
            stroke-width: 3;
        }

        .lab-records {
            margin-top: 20px;
        }

        .lab-records__title {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 700;
        }

        .lab-records__table-wrap {
            overflow-x: auto;
        }

        .lab-records__table {
            width: 100%;
            border-collapse: collapse;
        }

        .lab-records__table th,
        .lab-records__table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e8eef3;
            text-align: left;
            white-space: nowrap;
        }

        .lab-records__table th {
            color: #667085;
            font-size: 12px;
            font-weight: 600;
        }

        .lab-records__table td:last-child,
        .lab-records__table th:last-child {
            text-align: right;
        }

        .lab-note-list {
            display: grid;
            gap: 12px;
        }

        .lab-note-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #edf1f5;
        }

        .lab-note-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .lab-note-row__label {
            color: #6b7280;
        }

        .lab-note-row__value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }

        @media (max-width: 1250px) {
            .lab-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lab-filters > .lab-filter--1 {
                max-width: 220px;
            }
        }

        @media (max-width: 900px) {
            .lab-detail-grid {
                grid-template-columns: 1fr;
            }

            .lab-summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .lab-filters {
                grid-template-columns: 1fr;
            }

            .lab-filters > .lab-filter--1 {
                max-width: none;
            }
        }
    </style>
@endsection

@section('analysis-content')
    @php
        $points = collect($chartPoints ?? [])->values();
        $pointCount = $points->count();

        $chartWidth = 920;
        $chartHeight = 320;
        $paddingLeft = 64;
        $paddingRight = 28;
        $paddingTop = 24;
        $paddingBottom = 52;
        $plotWidth = $chartWidth - $paddingLeft - $paddingRight;
        $plotHeight = $chartHeight - $paddingTop - $paddingBottom;

        $values = $points
            ->pluck('value')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values();

        $minValue = $values->isNotEmpty() ? (float) $values->min() : 0.0;
        $maxValue = $values->isNotEmpty() ? (float) $values->max() : 1.0;

        if ($minValue === $maxValue) {
            $offset = abs($minValue) > 0 ? abs($minValue) * 0.1 : 1.0;
            $minValue -= $offset;
            $maxValue += $offset;
        }

        $range = max(0.000001, $maxValue - $minValue);
        $xDivisor = max(1, $pointCount - 1);

        $svgPoints = $points->map(function ($point, $index) use (
            $paddingLeft,
            $paddingTop,
            $plotWidth,
            $plotHeight,
            $xDivisor,
            $minValue,
            $range
        ) {
            $value = (float) data_get($point, 'value', 0);
            $x = $paddingLeft + ($plotWidth * ($index / $xDivisor));
            $normalized = ($value - $minValue) / $range;
            $y = $paddingTop + $plotHeight - ($plotHeight * $normalized);

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'value' => $value,
                'raw_value' => data_get($point, 'raw_value', data_get($point, 'value')),
                'label' => data_get($point, 'label', data_get($point, 'date', '')),
                'date' => data_get($point, 'date', ''),
            ];
        });

        $polyline = $svgPoints
            ->map(fn ($point) => $point['x'] . ',' . $point['y'])
            ->implode(' ');

        $tickCount = 4;
        $yTicks = collect(range(0, $tickCount))->map(function ($index) use (
            $tickCount,
            $paddingTop,
            $plotHeight,
            $maxValue,
            $range
        ) {
            $ratio = $index / $tickCount;

            return [
                'y' => round($paddingTop + ($plotHeight * $ratio), 2),
                'value' => $maxValue - ($range * $ratio),
            ];
        });

        $selectedParameterValue = (string) request('parameter', '');
        $selectedPeriodValue = (string) ($selectedPeriod ?? request('period', 'year'));
        $lastPoint = $points->last();
        $firstPoint = $points->first();
    @endphp

    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-panel mb-4">
                <div class="lab-panel__header">
                    <h2 class="lab-panel__title">Выбор данных</h2>
                </div>

                <div class="lab-panel__body">
                    <form method="GET" action="{{ request()->url() }}" class="lab-filters">
                        <div class="lab-filter--3">
                            <label class="lab-label" for="dynamicsDoctorSelect">Врач</label>

                            @if($canChooseDoctor ?? false)
                                <select
                                    id="dynamicsDoctorSelect"
                                    class="lab-control js-dynamics-doctor-select"
                                    name="doctor_id"
                                    data-search-url="{{ $doctorSearchUrl }}"
                                >
                                    <option value=""></option>

                                    @if(!empty($selectedDoctorId) && !empty($selectedDoctorName))
                                        <option value="{{ $selectedDoctorId }}" selected>
                                            {{ $selectedDoctorName }}
                                        </option>
                                    @endif
                                </select>
                            @else
                                <input
                                    type="hidden"
                                    id="dynamicsDoctorSelect"
                                    name="doctor_id"
                                    value="{{ $selectedDoctorId ?? '' }}"
                                >
                                <div class="lab-static-control">
                                    {{ $selectedDoctorName ?? 'Текущий врач' }}
                                </div>
                            @endif
                        </div>

                        <div class="lab-filter--3">
                            <label class="lab-label" for="dynamicsPatientSelect">Пациент</label>
                            <select
                                id="dynamicsPatientSelect"
                                class="lab-control js-dynamics-patient-select"
                                name="patient_id"
                                data-search-url="{{ $patientSearchUrl }}"
                            >
                                <option value=""></option>

                                @if(!empty($selectedPatientId) && !empty($selectedPatientName))
                                    <option value="{{ $selectedPatientId }}" selected>
                                        {{ $selectedPatientName }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="lab-filter--3">
                            <label class="lab-label" for="dynamicsParameterSelect">Показатель</label>
                            <select
                                id="dynamicsParameterSelect"
                                class="lab-control"
                                name="parameter"
                            >
                                <option value="">Выберите показатель</option>

                                @foreach($parameters ?? [] as $parameter)
                                    @php
                                        $optionValue = (string) (
                                            data_get($parameter, 'code')
                                            ?: data_get($parameter, 'id')
                                        );
                                    @endphp

                                    <option
                                        value="{{ $optionValue }}"
                                        @selected($selectedParameterValue === $optionValue)
                                    >
                                        {{ data_get($parameter, 'name') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label" for="dynamicsPeriodSelect">Период</label>
                            <select
                                id="dynamicsPeriodSelect"
                                class="lab-control"
                                name="period"
                            >
                                <option value="year" @selected($selectedPeriodValue === 'year')>
                                    1 год
                                </option>
                                <option value="half_year" @selected($selectedPeriodValue === 'half_year')>
                                    6 месяцев
                                </option>
                                <option value="quarter" @selected($selectedPeriodValue === 'quarter')>
                                    3 месяца
                                </option>
                                <option value="all" @selected($selectedPeriodValue === 'all')>
                                    Весь период
                                </option>
                            </select>
                        </div>

                        <div class="lab-filter--1">
                            <button
                                class="lab-button lab-button--primary w-100"
                                type="submit"
                            >
                                Построить
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <div class="lab-detail-grid">
                <main>
                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <div>
                                <h2 class="lab-panel__title">
                                    Динамика показателя
                                </h2>

                                <div class="lab-panel__subtitle">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="lab-panel__body">
                            @if(empty($selectedPatientId))
                                <div class="lab-empty">
                                    <div class="lab-empty__icon">📈</div>
                                    <h3>Пациент не выбран</h3>
                                    <p>Найдите пациента, выберите показатель и нажмите «Построить».</p>
                                </div>
                            @elseif($selectedParameterValue === '')
                                <div class="lab-empty">
                                    <div class="lab-empty__icon">📈</div>
                                    <h3>Показатель не выбран</h3>
                                    <p>Выберите лабораторный показатель для построения динамики.</p>
                                </div>
                            @elseif($pointCount === 0)
                                <div class="lab-empty">
                                    <div class="lab-empty__icon">📈</div>
                                    <h3>Записи не найдены</h3>
                                    <p>
                                        За выбранный период нет числовых результатов по этому показателю.
                                    </p>
                                </div>
                            @else
                                <div class="lab-summary-grid">
                                    <div class="lab-summary-card">
                                        <div class="lab-summary-card__label">Последнее значение</div>
                                        <div class="lab-summary-card__value">
                                            {{ data_get($lastPoint, 'raw_value', data_get($lastPoint, 'value')) }}
                                            @if(!empty($selectedParameterUnit))
                                                {{ $selectedParameterUnit }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="lab-summary-card">
                                        <div class="lab-summary-card__label">Тенденция</div>
                                        <div class="lab-summary-card__value">
                                            {{ $trendLabel ?? 'Недостаточно данных' }}
                                        </div>
                                    </div>

                                    <div class="lab-summary-card">
                                        <div class="lab-summary-card__label">Количество записей</div>
                                        <div class="lab-summary-card__value">{{ $pointCount }}</div>
                                    </div>
                                </div>

                                @if($pointCount >= 2)
                                    <div class="lab-chart-wrap">
                                        <svg
                                            class="lab-chart"
                                            viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                                            role="img"
                                            aria-label="График динамики лабораторного показателя"
                                        >
                                            @foreach($yTicks as $tick)
                                                <line
                                                    class="lab-chart__grid-line"
                                                    x1="{{ $paddingLeft }}"
                                                    y1="{{ $tick['y'] }}"
                                                    x2="{{ $chartWidth - $paddingRight }}"
                                                    y2="{{ $tick['y'] }}"
                                                />
                                                <text
                                                    class="lab-chart__axis-text"
                                                    x="{{ $paddingLeft - 10 }}"
                                                    y="{{ $tick['y'] + 4 }}"
                                                    text-anchor="end"
                                                >
                                                    {{ number_format($tick['value'], 2, ',', ' ') }}
                                                </text>
                                            @endforeach

                                            <polyline
                                                class="lab-chart__line"
                                                points="{{ $polyline }}"
                                            />

                                            @foreach($svgPoints as $index => $point)
                                                <circle
                                                    class="lab-chart__point"
                                                    cx="{{ $point['x'] }}"
                                                    cy="{{ $point['y'] }}"
                                                    r="5"
                                                >
                                                    <title>
                                                        {{ $point['date'] }}: {{ $point['raw_value'] }}
                                                        @if(!empty($selectedParameterUnit))
                                                            {{ $selectedParameterUnit }}
                                                        @endif
                                                    </title>
                                                </circle>

                                                @if(
                                                    $index === 0
                                                    || $index === $svgPoints->count() - 1
                                                    || ($svgPoints->count() <= 8)
                                                )
                                                    <text
                                                        class="lab-chart__axis-text"
                                                        x="{{ $point['x'] }}"
                                                        y="{{ $chartHeight - 20 }}"
                                                        text-anchor="middle"
                                                    >
                                                        {{ $point['date'] }}
                                                    </text>
                                                @endif
                                            @endforeach
                                        </svg>
                                    </div>
                                @else
                                    <div class="lab-note">
                                        Найдена одна запись. Для линии динамики требуется минимум две записи.
                                    </div>
                                @endif

                                <div class="lab-records">
                                    <h3 class="lab-records__title">Записи</h3>

                                    <div class="lab-records__table-wrap">
                                        <table class="lab-records__table">
                                            <thead>
                                            <tr>
                                                <th>Дата</th>
                                                <th>Показатель</th>
                                                <th>Значение</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($points->reverse()->values() as $point)
                                                <tr>
                                                    <td>{{ data_get($point, 'date', '—') }}</td>
                                                    <td>{{ $selectedParameterName ?? '—' }}</td>
                                                    <td>
                                                        {{ data_get($point, 'raw_value', data_get($point, 'value', '—')) }}
                                                        @if(!empty($selectedParameterUnit))
                                                            {{ $selectedParameterUnit }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                </main>

                <aside>
                    @php
                        $summaryData = $summary ?? [];

                        $summaryPeriod = data_get(
                            $summaryData,
                            'period',
                            $periodLabel ?? '1 год'
                        );

                        $summaryUnit = trim((string) data_get(
                            $summaryData,
                            'unit',
                            $selectedParameterUnit ?? ''
                        ));

                        $summaryFirstValue = data_get(
                            $summaryData,
                            'first.value'
                        );

                        $summaryFirstDate = data_get(
                            $summaryData,
                            'first.date'
                        );

                        $summaryLastValue = data_get(
                            $summaryData,
                            'last.value'
                        );

                        $summaryLastDate = data_get(
                            $summaryData,
                            'last.date'
                        );

                        $summaryReference = data_get(
                            $summaryData,
                            'reference'
                        );

                        $summaryDelta = data_get(
                            $summaryData,
                            'change.delta'
                        );

                        $summaryPercent = data_get(
                            $summaryData,
                            'change.percent'
                        );

                        $summaryTrendLabel = data_get(
                            $summaryData,
                            'change.label'
                        );
                    @endphp

                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <h2 class="lab-panel__title">Сводка</h2>
                        </div>

                        <div class="lab-panel__body">
                            <div class="lab-note-list">
                                <div class="lab-note-row">
                                    <span class="lab-note-row__label">Период</span>
                                    <span class="lab-note-row__value">
                                        {{ $summaryPeriod }}
                                    </span>
                                </div>

                                <div class="lab-note-row">
                                    <span class="lab-note-row__label">
                                        Первое значение
                                    </span>
                                    <span
                                        class="lab-note-row__value"
                                        @if($summaryFirstDate)
                                            title="Дата результата: {{ $summaryFirstDate }}"
                                        @endif
                                    >
                                        @if(
                                            $summaryFirstValue !== null
                                            && $summaryFirstValue !== ''
                                        )
                                            {{ $summaryFirstValue }}
                                            @if($summaryUnit !== '')
                                                {{ $summaryUnit }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>

                                <div class="lab-note-row">
                                    <span class="lab-note-row__label">
                                        Последнее значение
                                    </span>
                                    <span
                                        class="lab-note-row__value"
                                        @if($summaryLastDate)
                                            title="Дата результата: {{ $summaryLastDate }}"
                                        @endif
                                    >
                                        @if(
                                            $summaryLastValue !== null
                                            && $summaryLastValue !== ''
                                        )
                                            {{ $summaryLastValue }}
                                            @if($summaryUnit !== '')
                                                {{ $summaryUnit }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>

                                <div class="lab-note-row">
                                    <span class="lab-note-row__label">Референс</span>
                                    <span class="lab-note-row__value">
                                        @if(
                                            $summaryReference !== null
                                            && $summaryReference !== ''
                                        )
                                            {{ $summaryReference }}
                                            @if($summaryUnit !== '')
                                                {{ $summaryUnit }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>

                                <div class="lab-note-row">
                                    <span class="lab-note-row__label">Изменение</span>
                                    <span
                                        class="lab-note-row__value"
                                        @if($summaryTrendLabel)
                                            title="{{ $summaryTrendLabel }}"
                                        @endif
                                    >
                                        @if($summaryDelta !== null)
                                            {{ $summaryDelta > 0 ? '+' : '' }}{{ number_format(
                                                (float) $summaryDelta,
                                                2,
                                                ',',
                                                ' '
                                            ) }}
                                            @if($summaryUnit !== '')
                                                {{ $summaryUnit }}
                                            @endif

                                            @if($summaryPercent !== null)
                                                ({{ $summaryPercent > 0 ? '+' : '' }}{{ number_format(
                                                    (float) $summaryPercent,
                                                    1,
                                                    ',',
                                                    ' '
                                                ) }}%)
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;

                if (!$ || !$.fn || !$.fn.select2) {
                    console.error('Select2 не подключён.');
                    return;
                }

                const $doctorSelect = $('#dynamicsDoctorSelect');
                const $patientSelect = $('#dynamicsPatientSelect');
                const periodSelect = document.getElementById('dynamicsPeriodSelect');

                function extractItems(payload) {
                    if (Array.isArray(payload)) {
                        return payload;
                    }

                    const candidates = [
                        payload?.results,
                        payload?.items,
                        payload?.patients,
                        payload?.doctors,
                        payload?.data,
                        payload?.data?.results,
                        payload?.data?.items,
                        payload?.data?.patients,
                        payload?.data?.doctors,
                        payload?.data?.data
                    ];

                    return candidates.find(Array.isArray) || [];
                }

                function itemId(item) {
                    if (item === null || item === undefined) {
                        return null;
                    }

                    if (typeof item !== 'object') {
                        return item;
                    }

                    return item.id
                        ?? item.value
                        ?? item.patient_id
                        ?? item.doctor_id
                        ?? item.user_id
                        ?? item.employee_id
                        ?? null;
                }

                function itemFullName(item) {
                    if (item === null || item === undefined) {
                        return '';
                    }

                    if (typeof item !== 'object') {
                        return String(item);
                    }

                    return item.text
                        || item.fio
                        || item.label
                        || item.full_name
                        || item.fullname
                        || item.patient_name
                        || item.doctor_name
                        || item.name_full
                        || [
                            item.surname || item.last_name,
                            item.name || item.first_name,
                            item.patronymic
                                || item.patronym
                                || item.middle_name
                        ].filter(Boolean).join(' ')
                        || String(itemId(item) || '');
                }

                function prepareResults(payload) {
                    const results = extractItems(payload)
                        .map(function (item) {
                            return {
                                id: itemId(item),
                                text: itemFullName(item)
                            };
                        })
                        .filter(function (item) {
                            return item.id !== undefined
                                && item.id !== null
                                && item.id !== ''
                                && item.text !== '';
                        });

                    return {
                        results: results,
                        pagination: {
                            more: Boolean(
                                payload?.pagination?.more
                                || payload?.more
                                || payload?.data?.pagination?.more
                            )
                        }
                    };
                }

                function destroySelect2($element) {
                    if ($element.length && $element.hasClass('select2-hidden-accessible')) {
                        $element.select2('destroy');
                    }
                }

                if ($doctorSelect.is('select')) {
                    destroySelect2($doctorSelect);

                    $doctorSelect.select2({
                        width: '100%',
                        placeholder: 'Введите ФИО врача',
                        allowClear: true,
                        minimumInputLength: 1,
                        ajax: {
                            url: $doctorSelect.data('search-url'),
                            dataType: 'json',
                            delay: 250,
                            cache: false,
                            data: function (params) {
                                const term = String(params.term || '').trim();

                                return {
                                    q: term,
                                    search: term,
                                    term: term,
                                    query: term,
                                    page: params.page || 1
                                };
                            },
                            processResults: function (payload) {
                                console.debug('Ответ поиска врачей:', payload);
                                return prepareResults(payload);
                            }
                        },
                        language: {
                            inputTooShort: function () {
                                return 'Введите хотя бы 1 символ';
                            },
                            noResults: function () {
                                return 'Врач не найден';
                            },
                            searching: function () {
                                return 'Поиск врача…';
                            },
                            errorLoading: function () {
                                return 'Не удалось загрузить список врачей';
                            }
                        }
                    });
                }

                destroySelect2($patientSelect);

                $patientSelect.select2({
                    width: '100%',
                    placeholder: 'Введите ФИО пациента',
                    allowClear: true,
                    minimumInputLength: 1,
                    ajax: {
                        url: $patientSelect.data('search-url'),
                        dataType: 'json',
                        delay: 250,
                        cache: false,
                        data: function (params) {
                            const term = String(params.term || '').trim();
                            const data = {
                                q: term,
                                search: term,
                                term: term,
                                query: term,
                                page: params.page || 1
                            };
                            const doctorId = $doctorSelect.val();

                            if (doctorId) {
                                data.doctor_id = doctorId;
                            }

                            return data;
                        },
                        processResults: function (payload) {
                            console.debug('Ответ поиска пациентов:', payload);
                            return prepareResults(payload);
                        }
                    },
                    language: {
                        inputTooShort: function () {
                            return 'Введите хотя бы 1 символ';
                        },
                        noResults: function () {
                            return 'Пациент не найден';
                        },
                        searching: function () {
                            return 'Поиск пациента…';
                        },
                        errorLoading: function () {
                            return 'Не удалось загрузить список пациентов';
                        }
                    }
                });

                if (periodSelect) {
                    periodSelect.addEventListener('change', function () {
                        if (!this.form) {
                            return;
                        }

                        if (typeof this.form.requestSubmit === 'function') {
                            this.form.requestSubmit();
                        } else {
                            this.form.submit();
                        }
                    });
                }

                if ($doctorSelect.is('select')) {
                    $doctorSelect.on('change', function () {
                        $patientSelect.val(null).trigger('change');
                    });
                }
            });
        </script>
    @endpush
@endonce
