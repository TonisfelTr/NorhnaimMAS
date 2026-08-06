    @extends('doctors.analyses')

@section('analysis-title', 'Динамика лабораторных показателей')

@section('assets')
    @parent
    <style>
        .lab-dynamics-page {
            --lab-accent: #0793a4;
            --lab-accent-dark: #057786;
            --lab-accent-soft: #eaf8fa;
            --lab-border: #e4ebf1;
            --lab-text: #172033;
            --lab-muted: #6b7280;
            --lab-success: #16835b;
            --lab-success-soft: #eaf7f1;
            --lab-danger: #c43d4d;
            --lab-danger-soft: #fff0f2;
            --lab-warning: #a96608;
            --lab-warning-soft: #fff7e8;
        }

        .lab-filters {
            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr)
                minmax(0, 1.2fr)
                minmax(145px, .65fr)
                minmax(150px, 170px);
            gap: 16px;
            align-items: end;
            width: 100%;
            min-width: 0;
        }

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
            margin-bottom: 7px;
            color: #445166;
            font-size: 13px;
            font-weight: 600;
        }

        .lab-filter-hint {
            display: block;
            margin-top: 6px;
            color: #8a94a4;
            font-size: 11px;
            line-height: 1.35;
        }

        .lab-filters .lab-control,
        .lab-filters .lab-button,
        .lab-static-control {
            box-sizing: border-box;
            width: 100%;
            height: 44px;
            min-height: 44px;
            border-radius: 10px;
        }

        .lab-static-control {
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 0 13px;
            border: 1px solid #d9e1e8;
            background: #f8fafc;
            color: #1f2937;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lab-filters .select2-container {
            width: 100% !important;
            min-width: 0;
        }

        .lab-filters .select2-container--default .select2-selection--single,
        .lab-filters .select2-container--bootstrap-5 .select2-selection {
            box-sizing: border-box;
            height: 44px !important;
            min-height: 44px !important;
            border: 1px solid #d9e1e8 !important;
            border-radius: 10px !important;
            background: #fff;
        }

        .lab-filters .select2-container--default.select2-container--focus
        .select2-selection--single,
        .lab-filters .select2-container--default.select2-container--open
        .select2-selection--single {
            border-color: var(--lab-accent) !important;
            box-shadow: 0 0 0 3px rgba(7, 147, 164, .12);
        }

        .lab-filters .select2-container--default .select2-selection--single {
            display: flex;
            align-items: center;
            padding: 0 38px 0 13px;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__rendered {
            width: 100%;
            padding: 0;
            color: #1f2937;
            line-height: 42px !important;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__placeholder {
            color: #9aa3b1;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__arrow {
            top: 2px;
            right: 7px;
            height: 40px !important;
        }

        .lab-filters .select2-container--default
        .select2-selection--single
        .select2-selection__clear {
            height: 40px;
            line-height: 39px;
        }

        .select2-container--open {
            z-index: 1100;
        }

        .lab-button--loading {
            cursor: wait;
            opacity: .78;
        }

        .lab-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 370px);
            gap: 22px;
            align-items: start;
        }

        .lab-detail-grid > main,
        .lab-detail-grid > aside {
            min-width: 0;
        }

        .lab-detail-grid > aside {
            position: sticky;
            top: 18px;
        }

        .lab-panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
        }

        .lab-panel__subtitle {
            margin-top: 5px;
            color: #7c8798;
            font-size: 13px;
            line-height: 1.45;
        }

        .lab-context-badge {
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
            min-height: 28px;
            padding: 4px 10px;
            border: 1px solid #cfe7ea;
            border-radius: 999px;
            background: var(--lab-accent-soft);
            color: var(--lab-accent-dark);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .lab-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .lab-summary-card {
            min-width: 0;
            padding: 15px 16px;
            border: 1px solid var(--lab-border);
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #fafcfd 100%);
        }

        .lab-summary-card__label {
            margin-bottom: 7px;
            color: var(--lab-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .lab-summary-card__value {
            overflow: hidden;
            color: var(--lab-text);
            font-size: 19px;
            font-weight: 750;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lab-summary-card__meta {
            margin-top: 6px;
            color: #8892a0;
            font-size: 11px;
            line-height: 1.35;
        }

        .lab-trend-text--up {
            color: var(--lab-danger);
        }

        .lab-trend-text--down {
            color: #3475a8;
        }

        .lab-trend-text--stable {
            color: var(--lab-success);
        }

        .lab-chart-section {
            overflow: hidden;
            border: 1px solid var(--lab-border);
            border-radius: 14px;
            background: #fff;
        }

        .lab-chart-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px 6px;
        }

        .lab-chart-toolbar__title {
            margin: 0;
            color: var(--lab-text);
            font-size: 14px;
            font-weight: 700;
        }

        .lab-chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 12px;
            color: #778295;
            font-size: 11px;
        }

        .lab-chart-legend__item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .lab-chart-legend__line,
        .lab-chart-legend__band {
            display: inline-block;
            width: 18px;
            height: 3px;
            border-radius: 99px;
            background: var(--lab-accent);
        }

        .lab-chart-legend__band {
            height: 8px;
            background: #e5f5ee;
        }

        .lab-chart-wrap {
            overflow-x: auto;
            padding: 4px 8px 10px;
        }

        .lab-chart {
            display: block;
            width: 100%;
            min-width: 690px;
            height: auto;
        }

        .lab-chart__reference-band {
            fill: #edf9f3;
        }

        .lab-chart__grid-line {
            stroke: #e9eef3;
            stroke-width: 1;
        }

        .lab-chart__axis-text {
            fill: #7b8794;
            font-size: 12px;
        }

        .lab-chart__axis-unit {
            fill: #98a1ad;
            font-size: 11px;
            font-weight: 600;
        }

        .lab-chart__line {
            fill: none;
            stroke: var(--lab-accent);
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lab-chart__point {
            fill: #ffffff;
            stroke: var(--lab-accent);
            stroke-width: 3;
            transition: r .15s ease;
        }

        .lab-chart__point:hover {
            r: 7;
        }

        .lab-records {
            margin-top: 22px;
        }

        .lab-records__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .lab-records__title {
            margin: 0;
            color: var(--lab-text);
            font-size: 16px;
            font-weight: 700;
        }

        .lab-records__count {
            color: #8993a2;
            font-size: 12px;
        }

        .lab-records__actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .lab-record-action {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border: 1px solid #cfe7ea;
            border-radius: 9px;
            background: #fff;
            color: var(--lab-accent-dark);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
        }

        .lab-record-action:hover {
            border-color: var(--lab-accent);
            background: var(--lab-accent-soft);
            color: var(--lab-accent-dark);
        }

        .lab-record-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: center;
            gap: 6px;
            width: 100%;
            min-width: 0;
        }

        .lab-record-delete-form {
            display: block;
            width: 100%;
            margin: 0;
        }

        .lab-record-actions .lab-record-action,
        .lab-record-actions .lab-record-delete {
            width: 100%;
            min-width: 0;
            padding-right: 6px;
            padding-left: 6px;
            box-sizing: border-box;
            white-space: normal;
        }

        .lab-record-delete {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border: 1px solid #f0c0c7;
            border-radius: 9px;
            background: #fff;
            color: var(--lab-danger);
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            cursor: pointer;
        }

        .lab-record-delete:hover,
        .lab-record-delete:focus-visible {
            border-color: var(--lab-danger);
            background: var(--lab-danger-soft);
            color: var(--lab-danger);
            outline: none;
        }

        .lab-record-type {
            display: inline-flex;
            min-height: 24px;
            align-items: center;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 750;
            white-space: nowrap;
        }

        .lab-record-type--lab {
            background: #eaf8fa;
            color: #057786;
        }

        .lab-record-type--instrumental {
            background: #eef2ff;
            color: #4f46a5;
        }

        .lab-record-name {
            min-width: 0;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .lab-record-name__title {
            color: #263143;
            font-weight: 700;
            text-decoration: none;
        }

        .lab-record-name__title:hover {
            color: var(--lab-accent-dark);
            text-decoration: underline;
        }

        .lab-record-name__detail {
            margin-top: 4px;
            color: #8a94a4;
            font-size: 11px;
            line-height: 1.35;
        }

        .lab-record-doctor {
            min-width: 0;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .lab-record-doctor__name {
            color: #263143;
            font-weight: 600;
            line-height: 1.35;
        }

        .lab-record-doctor__accreditation {
            margin-top: 4px;
            color: #7c8798;
            font-size: 11px;
            line-height: 1.35;
        }

        .lab-record-result {
            min-width: 0;
            max-width: none;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .lab-records__table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            border: 1px solid var(--lab-border);
            border-radius: 12px;
        }

        .lab-records__table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            border-collapse: collapse;
            background: #fff;
        }

        .lab-records__table th,
        .lab-records__table td {
            min-width: 0;
            padding: 12px 10px;
            border-bottom: 1px solid #edf1f5;
            text-align: left;
            vertical-align: middle;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .lab-records__table th:nth-child(1),
        .lab-records__table td:nth-child(1) {
            width: 11%;
        }

        .lab-records__table th:nth-child(2),
        .lab-records__table td:nth-child(2) {
            width: 9%;
            white-space: nowrap;
        }

        .lab-records__table th:nth-child(3),
        .lab-records__table td:nth-child(3) {
            width: 8%;
        }

        .lab-records__table th:nth-child(4),
        .lab-records__table td:nth-child(4) {
            width: 13%;
        }

        .lab-records__table th:nth-child(5),
        .lab-records__table td:nth-child(5) {
            width: 18%;
        }

        .lab-records__table th:nth-child(6),
        .lab-records__table td:nth-child(6) {
            width: 19%;
        }

        .lab-records__table th:nth-child(7),
        .lab-records__table td:nth-child(7) {
            width: 10%;
        }

        .lab-records__table th:nth-child(8),
        .lab-records__table td:nth-child(8) {
            width: 12%;
        }

        .lab-records__table tr:last-child td {
            border-bottom: 0;
        }

        .lab-records__table th {
            background: #f8fafc;
            color: #667085;
            font-size: 12px;
            font-weight: 650;
        }

        .lab-records__table td {
            color: #263143;
            font-size: 13px;
        }

        .lab-records__table td:last-child,
        .lab-records__table th:last-child {
            text-align: center;
        }

        .lab-table-result {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-weight: 700;
        }

        .lab-table-result__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #a7b0bc;
        }

        .lab-table-result__dot--normal {
            background: var(--lab-success);
        }

        .lab-table-result__dot--high,
        .lab-table-result__dot--low {
            background: var(--lab-danger);
        }

        .lab-summary-panel .lab-panel__body {
            padding-top: 18px;
        }

        .lab-summary-panel .lab-note {
            margin-top: 16px;
        }

        .lab-summary-profile {
            padding: 0 0 16px;
            border-bottom: 1px solid #edf1f5;
        }

        .lab-summary-profile__eyebrow {
            margin-bottom: 4px;
            color: #8490a1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lab-summary-profile__patient {
            color: var(--lab-text);
            font-size: 15px;
            font-weight: 700;
            line-height: 1.35;
        }

        .lab-summary-profile__parameter {
            margin-top: 10px;
            color: var(--lab-accent-dark);
            font-size: 20px;
            font-weight: 800;
            line-height: 1.25;
        }

        .lab-summary-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 11px;
        }

        .lab-summary-chip {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 4px 9px;
            border-radius: 999px;
            background: #f1f5f8;
            color: #657184;
            font-size: 11px;
            font-weight: 650;
        }

        .lab-summary-latest {
            margin: 16px 0;
            padding: 16px;
            border: 1px solid #d7eaed;
            border-radius: 14px;
            background: linear-gradient(145deg, #f2fbfc 0%, #ffffff 65%);
        }

        .lab-summary-latest__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .lab-summary-latest__label {
            color: #667085;
            font-size: 12px;
            font-weight: 650;
        }

        .lab-summary-latest__value {
            margin-top: 8px;
            color: var(--lab-text);
            font-size: 29px;
            font-weight: 850;
            line-height: 1.05;
        }

        .lab-summary-latest__date {
            margin-top: 7px;
            color: #7f8a9a;
            font-size: 12px;
        }

        .lab-result-status {
            display: inline-flex;
            align-items: center;
            min-height: 25px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 750;
            line-height: 1.2;
            text-align: center;
        }

        .lab-result-status--normal {
            background: var(--lab-success-soft);
            color: var(--lab-success);
        }

        .lab-result-status--high,
        .lab-result-status--low {
            background: var(--lab-danger-soft);
            color: var(--lab-danger);
        }

        .lab-result-status--unknown {
            background: #f1f4f7;
            color: #778292;
        }

        .lab-summary-comparison {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .lab-summary-comparison__item {
            padding: 12px;
            border: 1px solid var(--lab-border);
            border-radius: 11px;
            background: #fff;
        }

        .lab-summary-comparison__label {
            margin-bottom: 6px;
            color: #7b8696;
            font-size: 11px;
            font-weight: 650;
        }

        .lab-summary-comparison__value {
            color: var(--lab-text);
            font-size: 14px;
            font-weight: 750;
            line-height: 1.25;
        }

        .lab-summary-comparison__meta {
            margin-top: 4px;
            color: #929ba8;
            font-size: 10px;
        }

        .lab-summary-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 14px;
        }

        .lab-summary-stat {
            min-width: 0;
            padding: 10px 8px;
            border-radius: 10px;
            background: #f7f9fb;
            text-align: center;
        }

        .lab-summary-stat__label {
            margin-bottom: 5px;
            color: #8b95a4;
            font-size: 10px;
        }

        .lab-summary-stat__value {
            overflow: hidden;
            color: #263143;
            font-size: 12px;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lab-note-list {
            display: block;
        }

        .lab-note-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(110px, max-content);
            align-items: center;
            column-gap: 18px;
            min-height: 44px;
            border-bottom: 1px solid #edf1f5;
        }

        .lab-note-row:last-child {
            border-bottom: 0;
        }

        .lab-note-row__label {
            min-width: 0;
            color: #758092;
            font-size: 12px;
            line-height: 1.35;
        }

        .lab-note-row__value {
            min-width: 0;
            color: #202b3d;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .lab-note-row__value--up {
            color: var(--lab-danger);
        }

        .lab-note-row__value--down {
            color: #3475a8;
        }

        .lab-note-row__value--stable {
            color: var(--lab-success);
        }

        .lab-empty {
            display: flex;
            min-height: 290px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            color: #748093;
            text-align: center;
        }

        .lab-empty--compact {
            min-height: 240px;
        }

        .lab-empty__icon {
            display: grid;
            width: 56px;
            height: 56px;
            margin-bottom: 14px;
            place-items: center;
            border-radius: 16px;
            background: var(--lab-accent-soft);
            color: var(--lab-accent-dark);
        }

        .lab-empty__icon svg {
            width: 28px;
            height: 28px;
        }

        .lab-empty h3 {
            margin: 0 0 7px;
            color: #263143;
            font-size: 16px;
            font-weight: 750;
        }

        .lab-empty p {
            max-width: 390px;
            margin: 0;
            font-size: 13px;
            line-height: 1.55;
        }

        .lab-note {
            padding: 13px 15px;
            border: 1px solid #dce8ec;
            border-radius: 10px;
            background: #f4fafb;
            color: #607181;
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 1250px) {
            .lab-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lab-filters > .lab-filter--1 {
                max-width: 240px;
            }

            .lab-detail-grid {
                grid-template-columns: 1fr;
            }

            .lab-detail-grid > aside {
                position: static;
                width: 100%;
            }
        }

        @media (max-width: 760px) {
            .lab-records__table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .lab-records__table {
                min-width: 860px;
                table-layout: fixed;
            }

            .lab-summary-grid {
                grid-template-columns: 1fr;
            }

            .lab-chart-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .lab-chart-legend {
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .lab-filters {
                grid-template-columns: 1fr;
            }

            .lab-filters > .lab-filter--1 {
                max-width: none;
            }

            .lab-panel-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .lab-summary-comparison {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('analysis-content')

    <div class="lab-page lab-dynamics-page" data-dynamics-version="2026-08-01-history-table-v12">
        <div class="lab-container">
            <section class="lab-panel mb-4">
                <div class="lab-panel__header">
                    <div class="lab-panel-heading">
                        <div>
                            <h2 class="lab-panel__title">Выбор данных</h2>
                            <div class="lab-panel__subtitle">
                                Выберите пациента и числовой лабораторный показатель.
                                График строится по числовым анализам, а общая таблица ниже показывает также
                                инструментальные исследования.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lab-panel__body">
                    <form
                        id="dynamicsFilterForm"
                        method="GET"
                        action="{{ request()->url() }}"
                        class="lab-filters"
                    >

                        <div class="lab-filter--3">
                            <label class="lab-label" for="dynamicsDoctorSelect">
                                Лечащий врач
                            </label>

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

                                <div class="lab-static-control" title="{{ $selectedDoctorName ?? '' }}">
                                    {{ $selectedDoctorName ?? 'Текущий врач' }}
                                </div>
                            @endif
                        </div>

                        <div class="lab-filter--3">
                            <label class="lab-label" for="dynamicsPatientSelect">
                                Пациент
                            </label>

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

                            <span class="lab-filter-hint" id="dynamicsPatientHint">
                                @if(($canChooseDoctor ?? false) && empty($selectedDoctorId))
                                    Можно выбрать пациента сразу. Врач используется как дополнительный фильтр.
                                @else
                                    Показаны пациенты выбранного лечащего врача с анализами или исследованиями.
                                @endif
                            </span>

                            <span
                                class="lab-filter-hint"
                                id="dynamicsPatientAjaxStatus"
                                aria-live="polite"
                            ></span>
                        </div>

                        <div class="lab-filter--3">
                            <label class="lab-label" for="dynamicsParameterSelect">
                                Показатель
                            </label>

                            <select
                                id="dynamicsParameterSelect"
                                class="lab-control"
                                name="parameter"
                                @disabled(empty($selectedPatientId))
                            >
                                <option value="">Выберите показатель</option>

                                @foreach($parameters ?? [] as $parameter)
                                    <option
                                        value="{{ data_get($parameter, 'id') }}"
                                        @selected(
                                            (string) data_get($parameter, 'id')
                                            === $selectedParameterValue
                                        )
                                    >
                                        {{ data_get($parameter, 'name') }}
                                        @if(trim((string) data_get($parameter, 'unit')) !== '')
                                            — {{ data_get($parameter, 'unit') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <span class="lab-filter-hint">
                                Здесь отображаются параметры внутри анализов, а не названия анализов.
                            </span>
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label" for="dynamicsPeriodSelect">
                                Период
                            </label>

                            <select
                                id="dynamicsPeriodSelect"
                                class="lab-control"
                                name="period"
                            >
                                <option value="year" @selected(($selectedPeriod ?? 'year') === 'year')>
                                    1 год
                                </option>
                                <option value="half_year" @selected(($selectedPeriod ?? 'year') === 'half_year')>
                                    6 месяцев
                                </option>
                                <option value="quarter" @selected(($selectedPeriod ?? 'year') === 'quarter')>
                                    3 месяца
                                </option>
                                <option value="all" @selected(($selectedPeriod ?? 'year') === 'all')>
                                    Весь период
                                </option>
                            </select>
                        </div>

                        <div class="lab-filter--1">
                            <button
                                id="dynamicsSubmitButton"
                                class="lab-button lab-button--primary w-100"
                                type="submit"
                            >
                                @if(empty($selectedPatientId))
                                    Применить фильтры
                                @elseif(empty($selectedParameterId))
                                    Показать историю
                                @else
                                    Показать динамику
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <div class="lab-detail-grid">
                <main>
                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <div class="lab-panel-heading">
                                <div>
                                    <h2 class="lab-panel__title">
                                        Динамика показателей
                                    </h2>

                                    <div class="lab-panel__subtitle">
                                        @if(!empty($selectedPatientName) && !empty($selectedParameterName))
                                            {{ $selectedPatientName }} · {{ $periodLabel }}
                                        @else
                                            Выберите данные для построения графика.
                                        @endif
                                    </div>
                                </div>

                                @if($pointCount > 0)
                                    <span class="lab-context-badge">
                                        {{ $pointCountLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="lab-panel__body">
                            @if(!empty($selectedPatientId) && !empty($selectedParameterId))
                                @if($pointCount === 0)
                                    <div class="lab-note">
                                        За период «{{ $periodLabel }}» у пациента нет числовых
                                        результатов по выбранному показателю.
                                    </div>
                                @else
                                    <div class="lab-summary-grid">
                                        <div class="lab-summary-card">
                                            <div class="lab-summary-card__label">
                                                Последний результат
                                            </div>
                                            <div class="lab-summary-card__value">
                                                {{ $lastResultText }}
                                            </div>
                                            <div class="lab-summary-card__meta">
                                                {{ data_get($summaryData, 'last.date', 'Дата не указана') }}
                                            </div>
                                        </div>

                                        <div class="lab-summary-card">
                                            <div class="lab-summary-card__label">
                                                Изменение за период
                                            </div>
                                            <div class="lab-summary-card__value lab-trend-text--{{ $trendClass }}">
                                                {{ $changeText }}
                                            </div>
                                            <div class="lab-summary-card__meta">
                                                {{ $trendLabel }}
                                            </div>
                                        </div>

                                        <div class="lab-summary-card">
                                            <div class="lab-summary-card__label">
                                                Референсный интервал
                                            </div>
                                            <div class="lab-summary-card__value">
                                                {{ $referenceLabel ?: 'Не задан' }}
                                                @if($referenceLabel && $unit !== '')
                                                    {{ $unit }}
                                                @endif
                                            </div>
                                            <div class="lab-summary-card__meta">
                                                {{ $lastStatusLabel }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($pointCount >= 2)
                                        <div class="lab-chart-section">
                                            <div class="lab-chart-toolbar">
                                                <h3 class="lab-chart-toolbar__title">
                                                    Изменение показателя во времени
                                                </h3>

                                                <div class="lab-chart-legend">
                                                    <span class="lab-chart-legend__item">
                                                        <span class="lab-chart-legend__line"></span>
                                                        Результат
                                                    </span>

                                                    @if($referenceBand)
                                                        <span class="lab-chart-legend__item">
                                                            <span class="lab-chart-legend__band"></span>
                                                            Референс
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="lab-chart-wrap">
                                                <svg
                                                    class="lab-chart"
                                                    viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                                                    role="img"
                                                    aria-label="График динамики показателя {{ $selectedParameterName }}"
                                                >
                                                    @if($referenceBand)
                                                        <rect
                                                            class="lab-chart__reference-band"
                                                            x="{{ $paddingLeft }}"
                                                            y="{{ $referenceBand['y'] }}"
                                                            width="{{ $plotWidth }}"
                                                            height="{{ $referenceBand['height'] }}"
                                                            rx="4"
                                                        />
                                                    @endif

                                                    @foreach($yTicks as $tick)
                                                        <line
                                                            class="lab-chart__grid-line"
                                                            x1="{{ $paddingLeft }}"
                                                            y1="{{ $tick['y'] }}"
                                                            x2="{{ $chartRightX }}"
                                                            y2="{{ $tick['y'] }}"
                                                        />

                                                        <text
                                                            class="lab-chart__axis-text"
                                                            x="{{ $chartYAxisLabelX }}"
                                                            y="{{ $tick['y'] + 4 }}"
                                                            text-anchor="end"
                                                        >
                                                            {{ $tick['label'] }}
                                                        </text>
                                                    @endforeach

                                                    @if($unit !== '')
                                                        <text
                                                            class="lab-chart__axis-unit"
                                                            x="{{ $paddingLeft }}"
                                                            y="16"
                                                        >
                                                            {{ $unit }}
                                                        </text>
                                                    @endif

                                                    <polyline
                                                        class="lab-chart__line"
                                                        points="{{ $polyline }}"
                                                    />

                                                    @foreach($svgPoints as $point)
                                                        <circle
                                                            class="lab-chart__point"
                                                            cx="{{ $point['x'] }}"
                                                            cy="{{ $point['y'] }}"
                                                            r="5"
                                                        >
                                                            <title>
                                                                {{ $point['date'] }}:
                                                                {{ $point['result_text'] }}
                                                            </title>
                                                        </circle>

                                                        @if($point['show_label'])
                                                            <text
                                                                class="lab-chart__axis-text"
                                                                x="{{ $point['x'] }}"
                                                                y="{{ $chartXAxisLabelY }}"
                                                                text-anchor="middle"
                                                            >
                                                                {{ $point['date'] }}
                                                            </text>
                                                        @endif
                                                    @endforeach
                                                </svg>
                                            </div>
                                        </div>
                                    @else
                                        <div class="lab-note">
                                            Найден один результат. Он показан в сводке, но для линии
                                            динамики требуется минимум две даты исследования.
                                        </div>
                                    @endif
                                @endif
                            @elseif(!empty($selectedPatientId))
                                <div class="lab-note">
                                    Ниже показаны анализы и инструментальные исследования выбранного пациента.
                                    Для построения графика выберите числовой лабораторный показатель.
                                </div>
                            @else
                                <div class="lab-note">
                                    Ниже показаны анализы и инструментальные исследования
                                    за выбранный период. Врач и пациент используются как фильтры.
                                </div>
                            @endif

                            <div class="lab-records">
                                <div class="lab-records__header">
                                    <div>
                                        <h3 class="lab-records__title">
                                            История анализов и исследований
                                        </h3>
                                        <span class="lab-records__count">
                                            {{ $historyRowsCount }} записей за период «{{ $periodLabel }}»
                                        </span>
                                    </div>

                                    @if(!empty($selectedPatientId))
                                        <div class="lab-records__actions">
                                            @if(!empty($addLabUrl))
                                                <a class="lab-record-action" href="{{ $addLabUrl }}">
                                                    + Добавить анализ
                                                </a>
                                            @endif

                                            @if(!empty($addInstrumentalUrl))
                                                <a class="lab-record-action" href="{{ $addInstrumentalUrl }}">
                                                    + Добавить исследование
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if($historyRowsData->isEmpty())
                                    <div class="lab-note">
                                        За выбранный период анализы и исследования не найдены.
                                    </div>
                                @else
                                    <div class="lab-records__table-wrap">
                                        <table class="lab-records__table">
                                            <thead>
                                            <tr>
                                                <th>Тип</th>
                                                <th>Дата</th>
                                                <th>Статус</th>
                                                <th>Пациент</th>
                                                <th>Врач</th>
                                                <th>Показатель / исследование</th>
                                                <th>Результат</th>
                                                <th>Действия</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($historyRowsData as $row)
                                                <tr>
                                                    <td>
                                                        <span
                                                            class="lab-record-type lab-record-type--{{ $row['type'] }}">
                                                            {{ $row['type_label'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $row['date'] }}</td>
                                                    <td>{{ $row['status'] }}</td>
                                                    <td>{{ $row['patient'] }}</td>
                                                    <td class="lab-record-doctor">
                                                        <div class="lab-record-doctor__name">
                                                            {{ $row['doctor'] }}
                                                        </div>
                                                        @if(!empty($row['doctor_accreditation']))
                                                            <div class="lab-record-doctor__accreditation">
                                                                Аккредитация: {{ $row['doctor_accreditation'] }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="lab-record-name">
                                                        @if($row['url'])
                                                            <a class="lab-record-name__title" href="{{ $row['url'] }}">
                                                                {{ $row['name'] }}
                                                            </a>
                                                        @else
                                                            <span class="lab-record-name__title">
                                                                {{ $row['name'] }}
                                                            </span>
                                                        @endif

                                                        @if($row['detail'])
                                                            <div class="lab-record-name__detail">
                                                                {{ $row['detail'] }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="lab-record-result">
                                                        {{ $row['result'] }}
                                                    </td>
                                                    <td class="lab-record-actions-cell">
                                                        @php
                                                            $canView = !empty($row['url']);
                                                            $canDelete = ($row['status'] ?? null) === 'ordered'
                                                                && !empty($row['delete_url']);
                                                        @endphp

                                                        <div class="lab-record-actions">
                                                            @if($canView)
                                                                <a
                                                                    class="lab-record-action"
                                                                    href="{{ $row['url'] }}"
                                                                >
                                                                    Просмотр
                                                                </a>
                                                            @endif

                                                            @if($canDelete)
                                                                <form
                                                                    class="lab-record-delete-form"
                                                                    method="POST"
                                                                    action="{{ $row['delete_url'] }}"
                                                                    onsubmit="return confirm('Удалить назначенную запись «{{ data_get($row, 'name', 'исследование') }}»?');"
                                                                >
                                                                    @csrf

                                                                    <button
                                                                        class="lab-record-delete"
                                                                        type="submit"
                                                                    >
                                                                        Удалить
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            @if(!$canView && !$canDelete)
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                </main>

                <aside>
                    <section class="lab-panel lab-summary-panel">
                        <div class="lab-panel__header">
                            <div class="lab-panel-heading">
                                <h2 class="lab-panel__title">Сводка</h2>

                                @if($pointCount > 0)
                                    <span class="lab-context-badge">
                                        {{ data_get($summaryData, 'period', $periodLabel) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="lab-panel__body">
                            @if(empty($selectedPatientId))
                                <div class="lab-summary-profile">
                                    <div class="lab-summary-profile__eyebrow">
                                        {{ empty($selectedDoctorId) ? 'Фильтр по врачу' : 'Выбранный врач' }}
                                    </div>
                                    <div class="lab-summary-profile__patient">
                                        {{ $selectedDoctorName ?: 'Все врачи' }}
                                    </div>

                                    <div class="lab-summary-profile__parameter">
                                        Пациент не выбран
                                    </div>

                                    <div class="lab-summary-chips">
                                        <span class="lab-summary-chip">
                                            {{ $periodLabel ?? '1 год' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="lab-note-list">
                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Пациент</span>
                                        <span class="lab-note-row__value">Не выбран</span>
                                    </div>
                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Период</span>
                                        <span class="lab-note-row__value">
                                            {{ $periodLabel ?? '1 год' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="lab-note">
                                    Откройте поле «Пациент»: список пациентов с готовыми
                                    результатами загружается сразу. Врач используется только как фильтр.
                                </div>
                            @elseif(empty($selectedParameterId))
                                <div class="lab-summary-profile">
                                    <div class="lab-summary-profile__eyebrow">Пациент</div>
                                    <div class="lab-summary-profile__patient">
                                        {{ data_get($patientOverviewData, 'patient', $selectedPatientName ?: '—') }}
                                    </div>

                                    <div class="lab-summary-profile__parameter">
                                        Выберите показатель
                                    </div>

                                    <div class="lab-summary-chips">
                                        <span class="lab-summary-chip">
                                            {{ data_get($patientOverviewData, 'researches_count', 0) }} исследований
                                        </span>
                                        <span class="lab-summary-chip">
                                            {{ data_get($patientOverviewData, 'period', $periodLabel) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="lab-note-list">
                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Доступно показателей</span>
                                        <span class="lab-note-row__value">
                                            {{ data_get($patientOverviewData, 'parameters_count', 0) }}
                                        </span>
                                    </div>
                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Последнее исследование</span>
                                        <span class="lab-note-row__value">
                                            {{ data_get($patientOverviewData, 'last_research_date', '—') ?: '—' }}
                                        </span>
                                    </div>
                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Исследование</span>
                                        <span class="lab-note-row__value lab-note-row__value--wrap">
                                            {{ data_get($patientOverviewData, 'last_research', '—') ?: '—' }}
                                        </span>
                                    </div>
                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Материал</span>
                                        <span class="lab-note-row__value lab-note-row__value--wrap">
                                            {{ data_get($patientOverviewData, 'last_material', '—') ?: '—' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="lab-note">
                                    Выберите конкретный параметр из результатов анализов —
                                    после этого появятся значения, референс и динамика.
                                </div>
                            @elseif($pointCount === 0)
                                <div class="lab-empty lab-empty--compact">
                                    <div class="lab-empty__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path d="M8 12h8"/>
                                        </svg>
                                    </div>
                                    <h3>Результатов нет</h3>
                                    <p>Измените период или выберите другой показатель.</p>
                                </div>
                            @else
                                <div class="lab-summary-profile">
                                    <div class="lab-summary-profile__eyebrow">Пациент</div>
                                    <div class="lab-summary-profile__patient">
                                        {{ data_get($summaryData, 'patient', '—') }}
                                    </div>

                                    <div class="lab-summary-profile__parameter">
                                        {{ data_get($summaryData, 'parameter', '—') }}
                                    </div>

                                    <div class="lab-summary-chips">
                                        <span class="lab-summary-chip">
                                            {{ $summaryPointsLabel }}
                                        </span>
                                        <span class="lab-summary-chip">
                                            {{ data_get($summaryData, 'period', $periodLabel) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="lab-summary-latest">
                                    <div class="lab-summary-latest__top">
                                        <span class="lab-summary-latest__label">
                                            Последний результат
                                        </span>
                                        <span class="lab-result-status lab-result-status--{{ $lastStatus }}">
                                            {{ $lastStatusLabel }}
                                        </span>
                                    </div>

                                    <div class="lab-summary-latest__value">
                                        {{ $lastResultText }}
                                    </div>

                                    <div class="lab-summary-latest__date">
                                        {{ data_get($summaryData, 'last.date', 'Дата не указана') }}
                                    </div>
                                </div>

                                <div class="lab-summary-comparison">
                                    <div class="lab-summary-comparison__item">
                                        <div class="lab-summary-comparison__label">
                                            Первый результат
                                        </div>
                                        <div class="lab-summary-comparison__value">
                                            {{ $firstResultText }}
                                        </div>
                                        <div class="lab-summary-comparison__meta">
                                            {{ data_get($summaryData, 'first.date', '—') }}
                                        </div>
                                    </div>

                                    <div class="lab-summary-comparison__item">
                                        <div class="lab-summary-comparison__label">
                                            Изменение
                                        </div>
                                        <div class="lab-summary-comparison__value lab-trend-text--{{ $trendClass }}">
                                            {{ $changeText }}
                                        </div>
                                        <div class="lab-summary-comparison__meta">
                                            {{ $trendLabel }}
                                        </div>
                                    </div>
                                </div>

                                <div class="lab-summary-stats">
                                    <div class="lab-summary-stat">
                                        <div class="lab-summary-stat__label">Минимум</div>
                                        <div class="lab-summary-stat__value">
                                            {{ $minimumText }}
                                        </div>
                                    </div>

                                    <div class="lab-summary-stat">
                                        <div class="lab-summary-stat__label">Максимум</div>
                                        <div class="lab-summary-stat__value">
                                            {{ $maximumText }}
                                        </div>
                                    </div>

                                    <div class="lab-summary-stat">
                                        <div class="lab-summary-stat__label">Среднее</div>
                                        <div class="lab-summary-stat__value">
                                            {{ $averageText }}
                                        </div>
                                    </div>
                                </div>

                                <div class="lab-note-list">
                                    @if(data_get($summaryData, 'research'))
                                        <div class="lab-note-row">
                                            <span class="lab-note-row__label">Исследование</span>
                                            <span class="lab-note-row__value">
                                                {{ data_get($summaryData, 'research') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if(data_get($summaryData, 'material'))
                                        <div class="lab-note-row">
                                            <span class="lab-note-row__label">Материал</span>
                                            <span class="lab-note-row__value">
                                                {{ data_get($summaryData, 'material') }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">
                                            Референсный интервал
                                        </span>
                                        <span class="lab-note-row__value">
                                            @if($referenceLabel)
                                                {{ $referenceLabel }}{{ $unit !== '' ? ' ' . $unit : '' }}
                                            @else
                                                Не задан
                                            @endif
                                        </span>
                                    </div>

                                    <div class="lab-note-row">
                                        <span class="lab-note-row__label">Тенденция</span>
                                        <span class="lab-note-row__value lab-note-row__value--{{ $trendClass }}">
                                            {{ $trendLabel }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $ = window.jQuery;

            if (!$ || !$.fn || !$.fn.select2) {
                console.error('Select2 не подключён.');
                return;
            }

            const form = document.getElementById('dynamicsFilterForm');
            const submitButton = document.getElementById('dynamicsSubmitButton');
            const periodSelect = document.getElementById('dynamicsPeriodSelect');
            const $doctorSelect = $('#dynamicsDoctorSelect');
            const $patientSelect = $('#dynamicsPatientSelect');
            const $parameterSelect = $('#dynamicsParameterSelect');
            const patientAjaxStatus = document.getElementById(
                'dynamicsPatientAjaxStatus'
            );

            function setPatientStatus(message, isError = false) {
                if (!patientAjaxStatus) {
                    return;
                }

                patientAjaxStatus.textContent = message || '';
                patientAjaxStatus.style.color = isError ? '#c43d4d' : '';
            }

            let isSubmitting = false;

            function setSubmitLoading() {
                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.classList.add('lab-button--loading');
                submitButton.textContent = 'Загрузка…';
            }

            function submitForm() {
                if (!form || isSubmitting) {
                    return;
                }

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                isSubmitting = true;
                setSubmitLoading();

                /*
                 * Вызываем нативную отправку формы явно. Это исключает
                 * повторный вызов click/submit-обработчиков.
                 */
                HTMLFormElement.prototype.submit.call(form);
            }

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
                if (
                    $element.length
                    && $element.hasClass('select2-hidden-accessible')
                ) {
                    $element.select2('destroy');
                }
            }

            if ($doctorSelect.is('select')) {
                destroySelect2($doctorSelect);

                $doctorSelect.select2({
                    width: '100%',
                    placeholder: 'Выберите лечащего врача',
                    allowClear: true,
                    minimumInputLength: 0,
                    ajax: {
                        url: $doctorSelect.data('search-url'),
                        dataType: 'json',
                        delay: 250,
                        cache: false,
                        data: function (params) {
                            const term = String(params.term || '').trim();

                            return {
                                q: term,
                                page: params.page || 1
                            };
                        },
                        processResults: prepareResults
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

                $doctorSelect.on('select2:select', function () {
                    setPatientStatus(
                        'Врач выбран. Список пациентов отфильтрован.'
                    );

                    $patientSelect
                        .prop('disabled', false)
                        .val(null)
                        .trigger('change.select2');

                    $parameterSelect
                        .val(null)
                        .prop('disabled', true)
                        .trigger('change.select2');

                    window.setTimeout(function () {
                        $patientSelect.select2('open');
                    }, 0);
                });

                $doctorSelect.on('select2:clear', function () {
                    setPatientStatus(
                        'Фильтр по врачу очищен. Доступны все пациенты с анализами или исследованиями.'
                    );

                    $doctorSelect
                        .val(null)
                        .trigger('change.select2');

                    $patientSelect
                        .prop('disabled', false)
                        .val(null)
                        .trigger('change.select2');

                    $parameterSelect
                        .val(null)
                        .prop('disabled', true)
                        .trigger('change.select2');
                });
            }

            destroySelect2($patientSelect);

            $patientSelect.select2({
                width: '100%',
                placeholder: 'Выберите или введите ФИО пациента',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: $patientSelect.data('search-url'),
                    dataType: 'json',
                    delay: 250,
                    cache: false,
                    data: function (params) {
                        return {
                            q: String(params.term || '').trim(),
                            page: params.page || 1,
                            doctor_id: $doctorSelect.val() || ''
                        };
                    },
                    processResults: function (payload) {
                        const prepared = prepareResults(payload);
                        const meta = payload?.meta || {};

                        if (prepared.results.length > 0) {
                            setPatientStatus(
                                'Найдено пациентов: ' + prepared.results.length
                            );
                        } else if (meta.message) {
                            let diagnostic = meta.message;

                            if (
                                Number.isFinite(Number(meta.researches_total))
                                && Number.isFinite(Number(meta.researches_ready))
                                && Number.isFinite(Number(meta.researches_ready_with_results))
                            ) {
                                diagnostic += ' Всего исследований: '
                                    + meta.researches_total
                                    + '; готовых: '
                                    + meta.researches_ready
                                    + '; готовых с результатами: '
                                    + meta.researches_ready_with_results
                                    + '.';
                            }

                            setPatientStatus(diagnostic, true);
                        } else {
                            setPatientStatus(
                                'Пациенты по заданным условиям не найдены.',
                                true
                            );
                        }

                        return prepared;
                    },
                    error: function (xhr) {
                        console.error('Ошибка загрузки пациентов:', xhr);
                        setPatientStatus(
                            'Ошибка загрузки пациентов. Проверьте Network и laravel.log.',
                            true
                        );
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

            $patientSelect.on('select2:opening', function () {
                setPatientStatus(
                    $doctorSelect.val()
                        ? 'Загружаем пациентов выбранного врача…'
                        : 'Загружаем всех пациентов с анализами или исследованиями…'
                );
            });

            $patientSelect.on('select2:select', function () {
                $parameterSelect
                    .val(null)
                    .trigger('change.select2');

                /*
                 * После выбора пациента сервер должен заново загрузить
                 * доступные лабораторные показатели.
                 */
                submitForm();
            });

            $patientSelect.on('select2:clear', function () {
                $parameterSelect
                    .val(null)
                    .prop('disabled', true)
                    .trigger('change.select2');

                window.setTimeout(submitForm, 0);
            });

            if ($parameterSelect.length && !$parameterSelect.prop('disabled')) {
                destroySelect2($parameterSelect);

                $parameterSelect.select2({
                    width: '100%',
                    placeholder: 'Выберите показатель',
                    allowClear: false,
                    minimumResultsForSearch: 8,
                    language: {
                        noResults: function () {
                            return 'Показатель не найден';
                        }
                    }
                });

                $parameterSelect.on('select2:select', function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
            }

            if (periodSelect) {
                periodSelect.addEventListener('change', function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
            }

            if (submitButton) {
                submitButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    submitForm();
                });
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    submitForm();
                });
            }
        });
    </script>
@endpush
