@extends('doctors.settings')

@section('settings-title', 'График клиники')

@section('assets')
    @parent

    <style>
        .clinic-schedule-page {
            --schedule-danger: #c24141;
            --schedule-danger-bg: #fff1f1;
            --schedule-warning: #b36a08;
            --schedule-warning-bg: #fff7e8;
            --schedule-success: #15803d;
            --schedule-success-bg: #edf9f1;
            --schedule-blue: #2563eb;
            --schedule-blue-bg: #eef4ff;
        }

        .clinic-schedule-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .clinic-schedule-toolbar__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .clinic-schedule-overview {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .clinic-schedule-overview--all {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .clinic-schedule-stat {
            position: relative;
            overflow: hidden;
            min-height: 112px;
            padding: 18px 19px;
            border: 1px solid var(--settings-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .045);
        }

        .clinic-schedule-stat::before {
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, var(--settings-primary), var(--settings-primary-light));
            content: '';
        }

        .clinic-schedule-stat--warning::before {
            background: #f0a12b;
        }

        .clinic-schedule-stat--neutral::before {
            background: #94a3b8;
        }

        .clinic-schedule-stat__label {
            color: var(--settings-muted);
            font-size: 12px;
            font-weight: 780;
        }

        .clinic-schedule-stat__value {
            margin-top: 7px;
            color: var(--settings-text);
            font-size: 25px;
            font-weight: 850;
            line-height: 1.08;
        }

        .clinic-schedule-stat__hint {
            margin-top: 7px;
            color: #8a96a5;
            font-size: 11px;
            line-height: 1.45;
        }

        .clinic-week {
            display: grid;
            grid-template-columns:
                repeat(
                    7,
                    minmax(150px, 1fr)
                );
            gap: 10px;
            overflow-x: auto;
            padding: 2px 2px 7px;
            scrollbar-width: thin;
        }

        .clinic-day {
            display: flex;
            min-width: 150px;
            min-height: 220px;
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
            padding: 14px;
            border: 1px solid #e4eaf0;
            border-radius: 16px;
            background: #fff;
            transition: .16s ease;
        }

        .clinic-day:hover {
            border-color: #d3e2e6;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }

        .clinic-day.is-closed {
            background: #f8fafc;
        }

        .clinic-day__identity {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
        }

        .clinic-day__number {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: #eaf8fa;
            color: var(--settings-primary-dark);
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .clinic-day.is-closed .clinic-day__number {
            background: #eef2f6;
            color: #718096;
        }

        .clinic-day__name {
            color: var(--settings-text);
            font-size: 14px;
            font-weight: 800;
        }

        .clinic-day__status {
            margin-top: 3px;
            color: var(--settings-muted);
            font-size: 11px;
        }

        .clinic-day__hours {
            display: grid;
            grid-template-columns: 1fr;
            gap: 7px;
            margin-top: auto;
        }

        .clinic-day__hours-label {
            color: #8792a2;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .035em;
        }

        .clinic-time {
            width: 100%;
            min-height: 40px;
            padding: 7px 10px;
            border: 1px solid #d8e0e5;
            border-radius: 11px;
            background: #fff;
            color: var(--settings-text);
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        .clinic-time:focus {
            border-color: var(--settings-primary);
            outline: 0;
            box-shadow: 0 0 0 .18rem rgba(4, 134, 150, .12);
        }

        .clinic-time:disabled {
            background: #f1f5f9;
            color: #a0a8b3;
        }

        .clinic-time-divider {
            display: none;
        }

        .clinic-day__switch-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 9px;
            padding-top: 11px;
            border-top: 1px solid #edf2f5;
        }

        .clinic-day__switch-label {
            min-width: 0;
            color: #475569;
            font-size: 11px;
            font-weight: 760;
            text-align: left;
        }

        .clinic-switch {
            position: relative;
            display: inline-flex;
            width: 46px;
            height: 26px;
            flex: 0 0 46px;
            align-items: center;
        }

        .clinic-switch input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .clinic-switch__track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #cbd5e1;
            cursor: pointer;
            transition: .18s ease;
        }

        .clinic-switch__track::after {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 7px rgba(15, 23, 42, .2);
            content: '';
            transition: .18s ease;
        }

        .clinic-switch input:checked + .clinic-switch__track {
            background: var(--settings-primary);
        }

        .clinic-switch input:checked + .clinic-switch__track::after {
            transform: translateX(20px);
        }

        .clinic-special-list {
            display: grid;
            gap: 12px;
        }

        .clinic-exception {
            position: relative;
            display: grid;
            grid-template-columns: minmax(180px, .65fr) minmax(320px, 1.35fr) minmax(210px, .75fr) auto;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border: 1px solid #e4eaf0;
            border-radius: 17px;
            background: #fff;
        }

        .clinic-exception--danger {
            border-left: 4px solid var(--schedule-danger);
        }

        .clinic-exception--warning {
            border-left: 4px solid #e08a16;
        }

        .clinic-exception--info {
            border-left: 4px solid var(--schedule-blue);
        }

        .clinic-exception--success {
            border-left: 4px solid var(--schedule-success);
        }

        .clinic-exception__period {
            min-width: 0;
        }

        .clinic-exception__date {
            color: var(--settings-text);
            font-size: 13px;
            font-weight: 820;
        }

        .clinic-exception__time {
            margin-top: 4px;
            color: var(--settings-muted);
            font-size: 11px;
        }

        .clinic-exception__main {
            min-width: 0;
        }

        .clinic-exception__title {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            color: var(--settings-text);
            font-size: 14px;
            font-weight: 820;
        }

        .clinic-exception__description {
            margin-top: 5px;
            color: var(--settings-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .clinic-exception__scope {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .clinic-exception__scope-label {
            color: #8a96a5;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .clinic-exception__scope-value {
            color: #334155;
            font-size: 12px;
            font-weight: 720;
            line-height: 1.4;
        }

        .clinic-exception__actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        .clinic-icon-button {
            display: inline-grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 1px solid #dbe3e9;
            border-radius: 10px;
            background: #fff;
            color: #52606d;
            cursor: pointer;
            transition: .15s ease;
        }

        .clinic-icon-button:hover {
            border-color: #c7d4dc;
            background: #f8fafc;
            color: #111827;
        }

        .clinic-icon-button--danger:hover {
            border-color: #f2c8c8;
            background: #fff4f4;
            color: #b42323;
        }

        .clinic-badge {
            display: inline-flex;
            min-height: 25px;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .clinic-badge--danger {
            background: var(--schedule-danger-bg);
            color: var(--schedule-danger);
        }

        .clinic-badge--warning {
            background: var(--schedule-warning-bg);
            color: var(--schedule-warning);
        }

        .clinic-badge--info {
            background: var(--schedule-blue-bg);
            color: var(--schedule-blue);
        }

        .clinic-badge--success {
            background: var(--schedule-success-bg);
            color: var(--schedule-success);
        }

        .clinic-schedule-note {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-top: 14px;
            padding: 13px 14px;
            border: 1px solid #e1edf0;
            border-radius: 14px;
            background: #f7fcfd;
            color: #536772;
            font-size: 11px;
            line-height: 1.55;
        }

        .clinic-schedule-note i {
            margin-top: 1px;
            color: var(--settings-primary-dark);
            font-size: 14px;
        }

        .clinic-modal .modal-dialog {
            width: min(1480px, calc(100vw - 34px));
            max-width: 1480px;
        }

        .clinic-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .2);
        }

        .clinic-modal .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--settings-border);
            background: linear-gradient(180deg, #fff, #fbfcfd);
        }

        .clinic-modal .modal-title {
            font-size: 18px;
            font-weight: 820;
        }

        .clinic-modal .modal-body {
            padding: 20px;
        }

        .clinic-modal .modal-footer {
            padding: 14px 20px 18px;
            border-top: 1px solid var(--settings-border);
            background: #fbfcfd;
        }

        .clinic-modal-grid {
            display: grid;
            grid-template-columns:
                minmax(0, .95fr)
                minmax(0, 1.15fr)
                minmax(0, 1fr);
            gap: 14px;
            align-items: stretch;
        }

        .clinic-modal-panel {
            min-width: 0;
            padding: 15px;
            border: 1px solid #e1e8ed;
            border-radius: 16px;
            background: #fbfcfd;
        }

        .clinic-modal-panel--targets {
            grid-column: span 2;
        }

        .clinic-modal-panel--comment {
            grid-column: span 1;
        }

        .clinic-modal-panel__title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 12px;
            color: #667085;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .clinic-modal-panel__title i {
            color: var(--settings-primary);
            font-size: 13px;
        }

        .clinic-modal-panel__body {
            display: grid;
            gap: 12px;
        }

        .clinic-modal-fields-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .clinic-modal-fields-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .clinic-modal-target-layout {
            display: grid;
            grid-template-columns: minmax(190px, .35fr) minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .clinic-modal-building-filter {
            display: grid;
            gap: 9px;
            margin-bottom: 10px;
        }

        .clinic-modal-building-tabs {
            display: flex;
            gap: 7px;
            overflow-x: auto;
            padding: 1px 1px 5px;
            scrollbar-width: thin;
        }

        .clinic-modal-building-tab {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 7px;
            min-height: 35px;
            padding: 7px 11px;
            border: 1px solid #d8e3e8;
            border-radius: 10px;
            background: #fff;
            color: #5d6c7b;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
            transition: .16s ease;
        }

        .clinic-modal-building-tab:hover {
            border-color: #a9dce2;
            background: #f4fbfc;
            color: #087f8e;
        }

        .clinic-modal-building-tab.is-active {
            border-color: #52c4d0;
            background: #eaf9fb;
            color: #007d8c;
            box-shadow:
                0 0 0 2px
                rgba(8, 145, 158, .05);
        }

        .clinic-modal-building-tab__count {
            display: inline-grid;
            min-width: 19px;
            height: 19px;
            place-items: center;
            padding: 0 5px;
            border-radius: 999px;
            background: #eef3f6;
            color: #748392;
            font-size: 9px;
            font-weight: 900;
        }

        .clinic-modal-building-tab.is-active
        .clinic-modal-building-tab__count {
            background: #d3f0f3;
            color: #087f8e;
        }

        .clinic-modal-building-filter__hint {
            color: #8794a2;
            font-size: 10px;
            line-height: 1.4;
        }

        .clinic-modal-building-filter.d-none {
            display: none !important;
        }

        .clinic-modal-panel--comment textarea {
            min-height: 132px;
            resize: vertical;
        }

        .clinic-modal-col-12,
        .clinic-modal-col-8,
        .clinic-modal-col-6,
        .clinic-modal-col-4 {
            grid-column: auto;
        }

        .clinic-modal-section-title {
            margin-top: 2px;
            padding-top: 4px;
            color: #667085;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .clinic-radio-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .clinic-radio-card {
            position: relative;
            display: block;
            min-height: 72px;
            padding: 13px;
            border: 1px solid #dce4ea;
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition: .15s ease;
        }

        .clinic-radio-card:hover {
            border-color: #c5d6dc;
        }

        .clinic-radio-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .clinic-radio-card:has(input:checked) {
            border-color: var(--settings-primary);
            background: #f2fbfc;
            box-shadow: 0 0 0 2px rgba(4, 134, 150, .08);
        }

        .clinic-radio-card.is-disabled {
            opacity: .46;
            background: #f7f8fa;
            cursor: not-allowed;
        }

        .clinic-radio-card.is-disabled:hover {
            border-color: #dce4ea;
        }

        .clinic-mode-hint {
            margin-top: 10px;
            padding: 10px 11px;
            border: 1px solid #dce9ed;
            border-radius: 11px;
            background: #f7fcfd;
            color: #647586;
            font-size: 10px;
            line-height: 1.5;
        }

        .clinic-radio-card__title {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #344054;
            font-size: 12px;
            font-weight: 820;
        }

        .clinic-radio-card__text {
            margin-top: 5px;
            color: #7a8797;
            font-size: 10px;
            line-height: 1.45;
        }

        .clinic-target-box {
            padding: 14px;
            border: 1px solid #e1e8ed;
            border-radius: 14px;
            background: #f8fafc;
        }

        .clinic-target-box.is-hidden {
            display: none;
        }

        .clinic-target-list {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .clinic-target-item {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 36px;
            padding: 7px 9px;
            border: 1px solid #e1e7ec;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-size: 11px;
            font-weight: 650;
        }

        .clinic-target-item input {
            accent-color: var(--settings-primary);
        }

        .clinic-empty-inline {
            padding: 24px;
            border: 1px dashed #cbd5e1;
            border-radius: 15px;
            background: #f8fafc;
            color: var(--settings-muted);
            font-size: 12px;
            text-align: center;
        }


        .clinic-building-filter {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }

        .clinic-building-tabs {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 2px 2px 5px;
            scrollbar-width: thin;
        }

        .clinic-building-tab {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 8px 13px;
            border: 1px solid #d7e4e8;
            border-radius: 12px;
            background: #fff;
            color: #536273;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            transition: .16s ease;
        }

        .clinic-building-tab:hover {
            border-color: #a8dbe1;
            background: #f4fbfc;
            color: #087f8e;
        }

        .clinic-building-tab.is-active {
            border-color: #55c6d2;
            background: #eaf9fb;
            color: #007d8c;
        }

        .clinic-building-tab__count {
            display: inline-grid;
            min-width: 22px;
            height: 22px;
            place-items: center;
            padding: 0 6px;
            border-radius: 999px;
            background: #eef3f6;
            color: #657585;
            font-size: 10px;
            font-weight: 900;
        }

        .clinic-building-tab.is-active .clinic-building-tab__count {
            background: #d5f1f4;
            color: #087f8e;
        }

        .clinic-building-filter__context {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #778595;
            font-size: 11px;
            line-height: 1.45;
        }

        .clinic-building-filter__context i {
            color: var(--settings-primary);
        }

        .clinic-target-item__meta {
            display: block;
            margin-top: 2px;
            color: #8a98a8;
            font-size: 10px;
            font-weight: 600;
        }

        .clinic-target-empty {
            grid-column: 1 / -1;
            padding: 14px;
            border: 1px dashed #d7e4e8;
            border-radius: 12px;
            color: #8090a0;
            font-size: 11px;
            text-align: center;
        }

        .clinic-exception.is-building-hidden {
            display: none;
        }

        @media (max-width: 1100px) {
            .clinic-schedule-overview {
                grid-template-columns: 1fr;
            }

            .clinic-week {
                grid-template-columns:
                    repeat(
                        7,
                        minmax(175px, 175px)
                    );
            }

            .clinic-exception {
                grid-template-columns: 1fr 1.4fr;
            }

            .clinic-exception__actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 1180px) {
            .clinic-modal .modal-dialog {
                width: min(1080px, calc(100vw - 28px));
            }

            .clinic-modal-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .clinic-modal-panel--targets,
            .clinic-modal-panel--comment {
                grid-column: span 2;
            }

            .clinic-target-list {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .clinic-schedule-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .clinic-schedule-toolbar__actions {
                width: 100%;
            }

            .clinic-schedule-toolbar__actions .settings-button {
                flex: 1 1 180px;
            }

            .clinic-week {
                grid-template-columns:
                    repeat(
                        7,
                        minmax(170px, 170px)
                    );
            }

            .clinic-day {
                min-width: 170px;
            }

            .clinic-time {
                width: 100%;
            }

            .clinic-exception {
                grid-template-columns: 1fr;
            }

            .clinic-modal-grid {
                grid-template-columns: 1fr;
            }

            .clinic-modal-panel--targets,
            .clinic-modal-panel--comment {
                grid-column: span 1;
            }

            .clinic-modal-target-layout,
            .clinic-modal-fields-2,
            .clinic-modal-fields-3 {
                grid-template-columns: 1fr;
            }

            .clinic-radio-grid,
            .clinic-target-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('sub-main')
    <main class="settings-page clinic-schedule-page">
        <div class="settings-container">
            <div class="settings-page-header clinic-schedule-toolbar">
                <div>
                    <h1 class="settings-page-title">График клиники</h1>
                    <p class="settings-page-description">
                        Настройте отдельный рабочий график каждого корпуса и особые периоды, когда работа клиники,
                        корпуса, кабинетов или отдельных врачей ограничивается.
                    </p>
                </div>

                <div class="clinic-schedule-toolbar__actions">
                    @if($selectedBuildingId)
                        <button type="button"
                                class="settings-button settings-button--secondary"
                                id="copyWeekScheduleBtn">
                            <i class="bi bi-copy"></i>
                            Скопировать будни
                        </button>
                    @endif

                    <button type="button"
                            class="settings-button settings-button--primary"
                            data-bs-toggle="modal"
                            data-bs-target="#clinicExceptionModal">
                        <i class="bi bi-plus-lg"></i>
                        Добавить правило
                    </button>
                </div>
            </div>


            <div class="clinic-building-filter">
                <div class="clinic-building-tabs"
                     id="clinicBuildingTabs"
                     aria-label="Корпуса и отделения">
                    <button type="button"
                            class="clinic-building-tab {{ $selectedBuildingKey === 'all' ? 'is-active' : '' }}"
                            data-building-tab="all"
                            data-building-url="{{ route('doctors.settings.clinic-schedule') }}">
                        <i class="bi bi-buildings"></i>
                        Все корпуса
                        <span class="clinic-building-tab__count">
                            {{ count($clinicOffices) }}
                        </span>
                    </button>

                    @foreach($buildingTabs as $buildingTab)
                        <button type="button"
                                class="clinic-building-tab {{ $selectedBuildingKey === $buildingTab['key'] ? 'is-active' : '' }}"
                                data-building-tab="{{ $buildingTab['key'] }}"
                                data-building-label="{{ $buildingTab['label'] }}"
                                data-building-url="{{ route('doctors.settings.clinic-schedule', ['building_id' => $buildingTab['id']]) }}">
                            <i class="bi bi-building"></i>
                            {{ $buildingTab['label'] }}
                            <span class="clinic-building-tab__count">
                                {{ $buildingTab['offices_count'] }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="clinic-building-filter__context">
                    <i class="bi bi-info-circle"></i>
                    <span id="clinicBuildingFilterContext">
                        @if($selectedBuildingId)
                            Настраивается отдельный график: {{ $selectedBuildingLabel }}.
                            Особые правила ниже также показывают, к каким корпусам они относятся.
                        @else
                            Выбраны все корпуса. Недельные тайминги скрыты — выберите конкретный корпус для настройки его графика.
                            Особые периоды показаны по всей клинике с указанием корпуса.
                        @endif
                    </span>
                </div>
            </div>

            <section class="clinic-schedule-overview {{ $selectedBuildingId ? '' : 'clinic-schedule-overview--all' }}">
                @if($selectedBuildingId)
                    <article class="clinic-schedule-stat">
                        <div class="clinic-schedule-stat__label">Обычный режим · {{ $selectedBuildingLabel }}</div>
                        <div class="clinic-schedule-stat__value">{{ $workingDaysCount }} рабочих дней</div>
                        <div class="clinic-schedule-stat__hint">{{ $weekSummary ?: 'Рабочие дни не заданы' }}</div>
                    </article>
                @endif

                <article class="clinic-schedule-stat clinic-schedule-stat--warning">
                    <div class="clinic-schedule-stat__label">Ближайшее особое правило</div>
                    <div class="clinic-schedule-stat__value">
                        @if($nearestException)
                            {{ $nearestException['date_text'] }}
                        @else
                            Нет
                        @endif
                    </div>
                    <div class="clinic-schedule-stat__hint">
                        @if($nearestException)
                            {{ $nearestException['title'] }} · {{ $nearestException['time_text'] }}
                        @else
                            Особых правил пока нет
                        @endif
                    </div>
                </article>

                <article class="clinic-schedule-stat clinic-schedule-stat--neutral">
                    <div class="clinic-schedule-stat__label">Правила</div>
                    <div class="clinic-schedule-stat__value">
                        <span id="exceptionCounter">{{ $clinicExceptions->count() }}</span>
                        правил
                    </div>
                    <div class="clinic-schedule-stat__hint">
                        Правила могут действовать на всю клинику, корпуса, кабинеты или отдельных врачей
                    </div>
                </article>
            </section>

            @if($selectedBuildingId)
                <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Обычная рабочая неделя · {{ $selectedBuildingLabel }}</h2>
                        <div class="settings-panel__subtitle">
                            Этот базовый график действует только для выбранного корпуса, если на конкретную дату не задано особое правило.
                        </div>
                    </div>

                    <span class="settings-type-badge">
                        <i class="bi bi-calendar3-week"></i>
                        Постоянный график
                    </span>
                </div>

                <div class="settings-panel__body">
                    <div class="clinic-week" id="clinicWeekEditor">
                        @foreach($weekDays as $day)
                            <div class="clinic-day {{ $day['enabled'] ? '' : 'is-closed' }}"
                                 data-day="{{ $day['key'] }}">
                                <div class="clinic-day__identity">
                                    <div class="clinic-day__number">{{ $day['short'] }}</div>
                                    <div>
                                        <div class="clinic-day__name">{{ $day['name'] }}</div>
                                        <div class="clinic-day__status js-day-status">
                                            {{ $day['enabled'] ? 'Рабочий день' : 'Выходной' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="clinic-day__hours">
                                    <span class="clinic-day__hours-label">Работаем</span>
                                    <input type="time"
                                           class="clinic-time js-time-from"
                                           value="{{ $day['from'] }}"
                                           {{ $day['enabled'] ? '' : 'disabled' }}>
                                    <span class="clinic-time-divider">—</span>
                                    <input type="time"
                                           class="clinic-time js-time-to"
                                           value="{{ $day['to'] }}"
                                           {{ $day['enabled'] ? '' : 'disabled' }}>
                                </div>

                                <div class="clinic-day__switch-wrap">
                                    <span class="clinic-day__switch-label js-switch-label">
                                        {{ $day['enabled'] ? 'Открыто' : 'Закрыто' }}
                                    </span>
                                    <label class="clinic-switch">
                                        <input type="checkbox"
                                               class="js-day-toggle"
                                               {{ $day['enabled'] ? 'checked' : '' }}>
                                        <span class="clinic-switch__track"></span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="clinic-schedule-note">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            Рабочая неделя задаётся отдельно для каждого корпуса.
                            Кабинеты и врачи этого корпуса не могут работать за пределами указанного времени.
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="button"
                                class="settings-button settings-button--secondary"
                                id="resetWeekBtn">
                            Сбросить изменения
                        </button>
                        <button type="button"
                                class="settings-button settings-button--primary"
                                id="saveWeekBtn">
                            <i class="bi bi-check2"></i>
                            Сохранить график
                        </button>
                    </div>
                </div>
                </section>
            @endif

            <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Особые периоды и режимы работы</h2>
                        <div class="settings-panel__subtitle">
                            Исключения перекрывают обычный график на указанные даты и могут действовать на всю клинику,
                            отдельный корпус, кабинеты или конкретных врачей.
                        </div>
                    </div>

                    <button type="button"
                            class="settings-button settings-button--secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#clinicExceptionModal">
                        <i class="bi bi-calendar-plus"></i>
                        Новое правило
                    </button>
                </div>

                <div class="settings-panel__body">
                    <div class="clinic-special-list" id="clinicExceptionList">
                        @foreach($clinicExceptions as $exception)
                            <article class="clinic-exception {{ $exception['card_class'] }}"
                                     data-exception-id="{{ $exception['id'] }}"
                                     data-building-keys="{{ implode(',', $exception['building_keys']) }}">
                                <div class="clinic-exception__period">
                                    <div class="clinic-exception__date">
                                        {{ $exception['date_text'] }}
                                    </div>
                                    <div class="clinic-exception__time">
                                        {{ $exception['time_text'] }}
                                    </div>
                                </div>

                                <div class="clinic-exception__main">
                                    <div class="clinic-exception__title">
                                        {{ $exception['title'] }}
                                        <span class="clinic-badge {{ $exception['badge_class'] }}">
                                            {{ $exception['badge'] }}
                                        </span>
                                    </div>

                                    <div class="clinic-exception__description">
                                        {{ $exception['description'] }}
                                    </div>
                                </div>

                                <div class="clinic-exception__scope">
                                    <span class="clinic-exception__scope-label">
                                        Корпус
                                    </span>
                                    <span class="clinic-exception__scope-value">
                                        {{ $exception['building_text'] }}
                                    </span>
                                    <span class="clinic-exception__scope-label mt-1">
                                        Действует на
                                    </span>
                                    <span class="clinic-exception__scope-value">
                                        {{ $exception['scope_text'] }}
                                    </span>
                                </div>

                                <div class="clinic-exception__actions">
                                    <button type="button"
                                            class="clinic-icon-button js-edit-exception"
                                            title="Изменить"
                                            data-exception-id="{{ $exception['id'] }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <button type="button"
                                            class="clinic-icon-button clinic-icon-button--danger js-delete-exception"
                                            title="Удалить"
                                            data-exception-id="{{ $exception['id'] }}">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="clinic-empty-inline {{ $clinicExceptions->isEmpty() ? '' : 'd-none' }}" id="clinicExceptionsEmpty">
                        Особых периодов пока нет. Добавьте правило, если нужно временно изменить работу клиники,
                        корпуса, кабинета или отдельных врачей.
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade clinic-modal"
         id="clinicExceptionModal"
         tabindex="-1"
         aria-labelledby="clinicExceptionModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title"
                            id="clinicExceptionModalLabel">
                            Новое правило графика
                        </h2>

                        <div class="settings-panel__subtitle">
                            Укажите причину, период, как изменится приём и на кого действует правило.
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <form id="clinicExceptionForm">
                        <div class="clinic-modal-grid">
                            <section class="clinic-modal-panel">
                                <div class="clinic-modal-panel__title">
                                    <i class="bi bi-card-text"></i>
                                    Причина
                                </div>

                                <div class="clinic-modal-panel__body">
                                    <div>
                                        <label class="settings-label"
                                               for="exceptionType">
                                            Тип мероприятия
                                        </label>

                                        <select class="settings-control"
                                                id="exceptionType"
                                                required>
                                            <option value="quarantine">
                                                Карантин
                                            </option>
                                            <option value="sanitary">
                                                Санитарные мероприятия
                                            </option>
                                            <option value="technical">
                                                Технические работы
                                            </option>
                                            <option value="organizational">
                                                Организационное мероприятие
                                            </option>
                                            <option value="other">
                                                Другое
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="settings-label"
                                               for="exceptionTitle">
                                            Название / пояснение
                                        </label>

                                        <input type="text"
                                               class="settings-control"
                                               id="exceptionTitle"
                                               value="Карантин"
                                               placeholder="Например: генеральная уборка">
                                    </div>
                                </div>
                            </section>

                            <section class="clinic-modal-panel">
                                <div class="clinic-modal-panel__title">
                                    <i class="bi bi-calendar-range"></i>
                                    Период
                                </div>

                                <div class="clinic-modal-panel__body">
                                    <div class="clinic-modal-fields-2">
                                        <div>
                                            <label class="settings-label"
                                                   for="exceptionDateFrom">
                                                Дата с
                                            </label>

                                            <input type="date"
                                                   class="settings-control"
                                                   id="exceptionDateFrom"
                                                   value="{{ now()->format('Y-m-d') }}"
                                                   required>
                                        </div>

                                        <div>
                                            <label class="settings-label"
                                                   for="exceptionDateTo">
                                                Дата по
                                            </label>

                                            <input type="date"
                                                   class="settings-control"
                                                   id="exceptionDateTo"
                                                   value="{{ now()->format('Y-m-d') }}"
                                                   required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="settings-label"
                                               for="exceptionAllDay">
                                            Период дня
                                        </label>

                                        <select class="settings-control"
                                                id="exceptionAllDay">
                                            <option value="time">
                                                По времени
                                            </option>
                                            <option value="all_day"
                                                    selected>
                                                Весь день
                                            </option>
                                        </select>
                                    </div>

                                    <div class="clinic-modal-fields-2">
                                        <div class="js-exception-time-field">
                                            <label class="settings-label"
                                                   for="exceptionTimeFrom">
                                                Время с
                                            </label>

                                            <input type="time"
                                                   class="settings-control"
                                                   id="exceptionTimeFrom"
                                                   value="14:00">
                                        </div>

                                        <div class="js-exception-time-field">
                                            <label class="settings-label"
                                                   for="exceptionTimeTo">
                                                Время по
                                            </label>

                                            <input type="time"
                                                   class="settings-control"
                                                   id="exceptionTimeTo"
                                                   value="17:00">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="clinic-modal-panel">
                                <div class="clinic-modal-panel__title">
                                    <i class="bi bi-sliders"></i>
                                    Как изменится приём
                                </div>

                                <div class="clinic-radio-grid"
                                     id="exceptionModeGrid">
                                    <label class="clinic-radio-card"
                                           data-mode-card="closed">
                                        <input type="radio"
                                               name="restriction_mode"
                                               value="closed"
                                               checked>

                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-slash-circle"></i>
                                            Приём закрыт
                                        </span>

                                        <span class="clinic-radio-card__text">
                                            Пациентов нельзя записывать в течение выбранного периода.
                                        </span>
                                    </label>

                                    <label class="clinic-radio-card"
                                           data-mode-card="blocked">
                                        <input type="radio"
                                               name="restriction_mode"
                                               value="blocked">

                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-pause-circle"></i>
                                            Приём приостановлен
                                        </span>

                                        <span class="clinic-radio-card__text">
                                            Указанный интервал исключается из обычного рабочего дня.
                                        </span>
                                    </label>

                                    <label class="clinic-radio-card"
                                           data-mode-card="override">
                                        <input type="radio"
                                               name="restriction_mode"
                                               value="override">

                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-clock-history"></i>
                                            Особый график
                                        </span>

                                        <span class="clinic-radio-card__text">
                                            Обычный график на эти даты заменяется указанным интервалом.
                                        </span>
                                    </label>

                                    <label class="clinic-radio-card"
                                           data-mode-card="extra">
                                        <input type="radio"
                                               name="restriction_mode"
                                               value="extra">

                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-calendar-plus"></i>
                                            Дополнительный приём
                                        </span>

                                        <span class="clinic-radio-card__text">
                                            Добавляет рабочее время поверх обычного графика, в том числе в выходной.
                                        </span>
                                    </label>
                                </div>

                                <div class="clinic-mode-hint"
                                     id="exceptionModeHint">
                                    Режим определяет, как правило изменит доступное время в регистратуре.
                                </div>
                            </section>

                            <section class="clinic-modal-panel clinic-modal-panel--targets">
                                <div class="clinic-modal-panel__title">
                                    <i class="bi bi-diagram-3"></i>
                                    На кого действует
                                </div>

                                <div class="clinic-modal-target-layout">
                                    <div>
                                        <label class="settings-label"
                                               for="exceptionScope">
                                            Область действия
                                        </label>

                                        <select class="settings-control"
                                                id="exceptionScope">
                                            <option value="clinic"
                                                    selected>
                                                Вся клиника
                                            </option>
                                            <option value="buildings">
                                                Корпус целиком
                                            </option>
                                            <option value="offices">
                                                Отдельные кабинеты
                                            </option>
                                            <option value="doctors">
                                                Отдельные врачи
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="clinic-modal-building-filter"
                                             id="exceptionBuildingFilter">
                                            <div class="clinic-modal-building-tabs"
                                                 id="exceptionBuildingTabs"
                                                 aria-label="Корпуса и отделения для правила">
                                                <button type="button"
                                                        class="clinic-modal-building-tab is-active"
                                                        data-modal-building-tab="all">
                                                    <i class="bi bi-buildings"></i>
                                                    Все корпуса
                                                    <span class="clinic-modal-building-tab__count">
                                                        {{ count($clinicOffices) }}
                                                    </span>
                                                </button>

                                                @foreach($buildingTabs as $buildingTab)
                                                    <button type="button"
                                                            class="clinic-modal-building-tab"
                                                            data-modal-building-tab="{{ $buildingTab['key'] }}"
                                                            data-modal-building-label="{{ $buildingTab['label'] }}">
                                                        <i class="bi bi-building"></i>
                                                        {{ $buildingTab['label'] }}
                                                        <span class="clinic-modal-building-tab__count">
                                                            {{ $buildingTab['offices_count'] }}
                                                        </span>
                                                    </button>
                                                @endforeach
                                            </div>

                                            <div class="clinic-modal-building-filter__hint"
                                                 id="exceptionBuildingFilterHint">
                                                Переключайте корпуса — уже выбранные кабинеты и врачи сохраняются.
                                            </div>
                                        </div>

                                        <div class="clinic-target-box js-target-box is-hidden"
                                             data-target="clinic">
                                            <div class="settings-info-box">
                                                <span class="settings-info-box__icon">
                                                    <i class="bi bi-hospital"></i>
                                                </span>

                                                <div>
                                                    Ограничение будет действовать на всю организацию независимо от кабинета
                                                    или расписания конкретного врача.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="clinic-target-box js-target-box is-hidden"
                                             data-target="buildings">
                                            <div class="clinic-target-list"
                                                 id="exceptionBuildingTargets">
                                                @forelse($buildingTabs as $buildingTab)
                                                    <label class="clinic-target-item">
                                                        <input type="checkbox"
                                                               value="{{ $buildingTab['id'] }}"
                                                               data-label="{{ $buildingTab['label'] }}">
                                                        <span>
                                                            {{ $buildingTab['label'] }}
                                                            <small class="clinic-target-item__meta">
                                                                {{ $buildingTab['offices_count'] }} кабинетов
                                                            </small>
                                                        </span>
                                                    </label>
                                                @empty
                                                    <div class="clinic-target-empty">
                                                        В клинике нет активных корпусов.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="clinic-target-box js-target-box"
                                             data-target="offices">
                                            <div class="clinic-target-list"
                                                 id="exceptionOfficeTargets">
                                                @forelse($clinicOffices as $office)
                                                    <label class="clinic-target-item"
                                                           data-building-key="{{ $office['building_key'] }}">
                                                        <input type="checkbox"
                                                               value="{{ $office['id'] }}"
                                                               data-label="{{ $office['label'] }}"
                                                               data-building-key="{{ $office['building_key'] }}">

                                                        <span>
                                                            {{ $office['label'] }}

                                                            <small class="clinic-target-item__meta">
                                                                {{ $office['building_label'] }}
                                                            </small>
                                                        </span>
                                                    </label>
                                                @empty
                                                    <div class="clinic-target-empty">
                                                        В клинике нет активных кабинетов.
                                                    </div>
                                                @endforelse

                                                <div class="clinic-target-empty d-none"
                                                     id="exceptionOfficeTargetsEmpty">
                                                    В выбранном корпусе нет активных кабинетов.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="clinic-target-box js-target-box is-hidden"
                                             data-target="doctors">
                                            <div class="clinic-target-list"
                                                 id="exceptionDoctorTargets">
                                                @forelse($clinicDoctors as $doctor)
                                                    <label class="clinic-target-item"
                                                           data-building-keys='@json($doctor['building_keys'])'>
                                                        <input type="checkbox"
                                                               value="{{ $doctor['id'] }}"
                                                               data-label="{{ $doctor['label'] }}"
                                                               data-building-keys='@json($doctor['building_keys'])'>

                                                        <span>
                                                            {{ $doctor['label'] }}
                                                        </span>
                                                    </label>
                                                @empty
                                                    <div class="clinic-target-empty">
                                                        В клинике нет доступных врачей.
                                                    </div>
                                                @endforelse

                                                <div class="clinic-target-empty d-none"
                                                     id="exceptionDoctorTargetsEmpty">
                                                    В выбранном корпусе нет врачей, закреплённых за кабинетами.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="clinic-modal-panel clinic-modal-panel--comment">
                                <div class="clinic-modal-panel__title">
                                    <i class="bi bi-chat-left-text"></i>
                                    Комментарий
                                </div>

                                <label class="settings-label"
                                       for="exceptionComment">
                                    Комментарий / пояснение
                                </label>

                                <textarea class="settings-control"
                                          id="exceptionComment"
                                          rows="5"
                                          placeholder="Что происходит и как меняется работа подразделения"></textarea>
                            </section>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="settings-button settings-button--secondary"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="button"
                            class="settings-button settings-button--primary"
                            id="saveExceptionBtn">
                        <i class="bi bi-check2"></i>
                        Добавить правило
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const weekEditor = document.getElementById('clinicWeekEditor');
            const copyWeekScheduleBtn = document.getElementById('copyWeekScheduleBtn');
            const resetWeekBtn = document.getElementById('resetWeekBtn');
            const saveWeekBtn = document.getElementById('saveWeekBtn');

            const buildingTabs = Array.from(
                document.querySelectorAll('[data-building-tab]')
            );

            const buildingFilterContext =
                document.getElementById('clinicBuildingFilterContext');

            const exceptionList =
                document.getElementById('clinicExceptionList');

            const exceptionCounter =
                document.getElementById('exceptionCounter');

            const exceptionsEmpty =
                document.getElementById('clinicExceptionsEmpty');

            const exceptionModalEl =
                document.getElementById('clinicExceptionModal');

            const exceptionModalTitle =
                document.getElementById('clinicExceptionModalLabel');

            const exceptionForm =
                document.getElementById('clinicExceptionForm');

            const exceptionType =
                document.getElementById('exceptionType');

            const exceptionTitle =
                document.getElementById('exceptionTitle');

            const exceptionDateFrom =
                document.getElementById('exceptionDateFrom');

            const exceptionDateTo =
                document.getElementById('exceptionDateTo');

            const exceptionAllDay =
                document.getElementById('exceptionAllDay');

            const exceptionTimeFrom =
                document.getElementById('exceptionTimeFrom');

            const exceptionTimeTo =
                document.getElementById('exceptionTimeTo');

            const exceptionScope =
                document.getElementById('exceptionScope');

            const exceptionComment =
                document.getElementById('exceptionComment');

            const saveExceptionBtn =
                document.getElementById('saveExceptionBtn');

            const exceptionModeHint =
                document.getElementById('exceptionModeHint');

            const exceptionModeInputs = Array.from(
                document.querySelectorAll(
                    'input[name="restriction_mode"]'
                )
            );

            const exceptionModeCards = Array.from(
                document.querySelectorAll(
                    '[data-mode-card]'
                )
            );

            const exceptionBuildingFilter =
                document.getElementById('exceptionBuildingFilter');

            const exceptionBuildingFilterHint =
                document.getElementById('exceptionBuildingFilterHint');

            const exceptionBuildingTabs = Array.from(
                document.querySelectorAll('[data-modal-building-tab]')
            );

            const officeTargetsEmpty =
                document.getElementById('exceptionOfficeTargetsEmpty');

            const doctorTargetsEmpty =
                document.getElementById('exceptionDoctorTargetsEmpty');

            const csrfToken =
                document.querySelector('meta[name="csrf-token"]')?.content
                || @json(csrf_token());

            const weekUpdateUrl =
                @json(route('doctors.settings.clinic-schedule.update'));

            const exceptionBaseUrl =
                @json(route('doctors.settings.clinic-schedule.exceptions.store'));

            const exceptionData =
                @json($clinicExceptionsForJs);

            const todayValue =
                @json(now()->format('Y-m-d'));

            const selectedBuildingId =
                @json($selectedBuildingId);

            let activeBuildingKey =
                document.querySelector(
                    '[data-building-tab].is-active'
                )?.dataset.buildingTab
                || 'all';

            let modalBuildingKey =
                activeBuildingKey !== 'all'
                    ? activeBuildingKey
                    : 'all';

            let editingExceptionId = null;

            const typeLabels = {
                quarantine: 'Карантин',
                sanitary: 'Санитарные мероприятия',
                technical: 'Технические работы',
                organizational: 'Организационное мероприятие',
                other: 'Другое мероприятие',
            };

            const typeModeConfig = {
                quarantine: {
                    defaultMode: 'closed',
                    allowed: ['closed'],
                    hint: 'Карантин всегда действует на всю клинику и полностью блокирует создание новых записей через регистратуру.',
                },
                sanitary: {
                    defaultMode: 'blocked',
                    allowed: ['blocked', 'override'],
                    hint: 'Обычно санитарные мероприятия временно исключают часть рабочего дня. При необходимости можно задать отдельный рабочий интервал.',
                },
                technical: {
                    defaultMode: 'blocked',
                    allowed: ['blocked', 'closed', 'override'],
                    hint: 'Технические работы могут остановить приём на время, полностью закрыть его либо заменить обычный график.',
                },
                organizational: {
                    defaultMode: 'override',
                    allowed: ['override', 'blocked', 'extra'],
                    hint: 'Для собраний, обучения и других мероприятий можно изменить график, исключить интервал или добавить дополнительное время.',
                },
                other: {
                    defaultMode: 'closed',
                    allowed: ['closed', 'blocked', 'override', 'extra'],
                    hint: 'Выберите, как это событие должно изменить доступное время в регистратуре.',
                },
            };

            const modeHints = {
                closed: 'Приём полностью недоступен в выбранный период. Можно закрыть весь день или отдельный интервал.',
                blocked: 'Указанный интервал станет недоступен, а остальная часть обычного графика сохранится.',
                override: 'На выбранные даты обычный недельный график заменяется указанным рабочим интервалом.',
                extra: 'Указанный интервал добавляется к обычному графику и может открыть приём даже в выходной день.',
            };

            const initialWeekState = Array.from(
                weekEditor?.querySelectorAll('.clinic-day')
                || []
            ).map(day => ({
                key: day.dataset.day,
                enabled:
                    day.querySelector('.js-day-toggle')
                        ?.checked
                    || false,
                from:
                    day.querySelector('.js-time-from')
                        ?.value
                    || '',
                to:
                    day.querySelector('.js-time-to')
                        ?.value
                    || '',
            }));

            function syncDayState(day) {
                if (!day) {
                    return;
                }

                const toggle =
                    day.querySelector('.js-day-toggle');

                const from =
                    day.querySelector('.js-time-from');

                const to =
                    day.querySelector('.js-time-to');

                const status =
                    day.querySelector('.js-day-status');

                const switchLabel =
                    day.querySelector('.js-switch-label');

                const enabled = Boolean(
                    toggle?.checked
                );

                day.classList.toggle(
                    'is-closed',
                    !enabled
                );

                if (from) {
                    from.disabled = !enabled;
                }

                if (to) {
                    to.disabled = !enabled;
                }

                if (status) {
                    status.textContent = enabled
                        ? 'Рабочий день'
                        : 'Выходной';
                }

                if (switchLabel) {
                    switchLabel.textContent = enabled
                        ? 'Открыто'
                        : 'Закрыто';
                }
            }

            weekEditor
                ?.querySelectorAll('.clinic-day')
                .forEach(day => {
                    syncDayState(day);

                    day.querySelector('.js-day-toggle')
                        ?.addEventListener(
                            'change',
                            () => syncDayState(day)
                        );
                });

            copyWeekScheduleBtn?.addEventListener(
                'click',
                () => {
                    const monday =
                        weekEditor?.querySelector(
                            '[data-day="mon"]'
                        );

                    if (!monday) {
                        return;
                    }

                    const enabled =
                        monday.querySelector(
                            '.js-day-toggle'
                        )?.checked
                        || false;

                    const from =
                        monday.querySelector(
                            '.js-time-from'
                        )?.value
                        || '';

                    const to =
                        monday.querySelector(
                            '.js-time-to'
                        )?.value
                        || '';

                    ['tue', 'wed', 'thu', 'fri']
                        .forEach(key => {
                            const day =
                                weekEditor?.querySelector(
                                    `[data-day="${key}"]`
                                );

                            if (!day) {
                                return;
                            }

                            const toggle =
                                day.querySelector(
                                    '.js-day-toggle'
                                );

                            const dayFrom =
                                day.querySelector(
                                    '.js-time-from'
                                );

                            const dayTo =
                                day.querySelector(
                                    '.js-time-to'
                                );

                            if (toggle) {
                                toggle.checked =
                                    enabled;
                            }

                            if (dayFrom) {
                                dayFrom.value =
                                    from;
                            }

                            if (dayTo) {
                                dayTo.value =
                                    to;
                            }

                            syncDayState(day);
                        });
                }
            );

            resetWeekBtn?.addEventListener(
                'click',
                () => {
                    initialWeekState.forEach(
                        state => {
                            const day =
                                weekEditor?.querySelector(
                                    `[data-day="${state.key}"]`
                                );

                            const toggle =
                                day?.querySelector(
                                    '.js-day-toggle'
                                );

                            const from =
                                day?.querySelector(
                                    '.js-time-from'
                                );

                            const to =
                                day?.querySelector(
                                    '.js-time-to'
                                );

                            if (toggle) {
                                toggle.checked =
                                    state.enabled;
                            }

                            if (from) {
                                from.value =
                                    state.from;
                            }

                            if (to) {
                                to.value =
                                    state.to;
                            }

                            syncDayState(day);
                        }
                    );
                }
            );

            async function parseResponse(response) {
                let data = null;

                try {
                    data = await response.json();
                } catch (error) {
                    data = null;
                }

                if (response.ok) {
                    return data;
                }

                const validationErrors =
                    data?.errors
                        ? Object.values(
                            data.errors
                        ).flat()
                        : [];

                const message =
                    validationErrors[0]
                    || data?.message
                    || 'Не удалось сохранить изменения.';

                throw new Error(message);
            }

            saveWeekBtn?.addEventListener(
                'click',
                async () => {
                    const schedule = {};

                    weekEditor
                        ?.querySelectorAll('.clinic-day')
                        .forEach(day => {
                            const key =
                                day.dataset.day;

                            schedule[key] = {
                                enabled: Boolean(
                                    day.querySelector(
                                        '.js-day-toggle'
                                    )?.checked
                                ),
                                from:
                                    day.querySelector(
                                        '.js-time-from'
                                    )?.value
                                    || '',
                                to:
                                    day.querySelector(
                                        '.js-time-to'
                                    )?.value
                                    || '',
                            };
                        });

                    const originalHtml =
                        saveWeekBtn.innerHTML;

                    saveWeekBtn.disabled = true;
                    saveWeekBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span> Сохранение';

                    try {
                        const response = await fetch(
                            weekUpdateUrl,
                            {
                                method: 'POST',
                                headers: {
                                    'Accept':
                                        'application/json',
                                    'Content-Type':
                                        'application/json',
                                    'X-CSRF-TOKEN':
                                        csrfToken,
                                },
                                body: JSON.stringify({
                                    building_id: selectedBuildingId,
                                    schedule,
                                }),
                            }
                        );

                        if (
                            !response.ok
                        ) {
                            await parseResponse(
                                response
                            );
                        }

                        saveWeekBtn.innerHTML =
                            '<i class="bi bi-check-circle"></i> Сохранено';

                        window.setTimeout(
                            () => {
                                window.location.reload();
                            },
                            500
                        );
                    } catch (error) {
                        window.alert(
                            error.message
                            || 'Не удалось сохранить график.'
                        );

                        saveWeekBtn.disabled =
                            false;

                        saveWeekBtn.innerHTML =
                            originalHtml;
                    }
                }
            );

            function parseBuildingKeys(value) {
                const raw =
                    String(value || '')
                        .trim();

                if (!raw) {
                    return [];
                }

                if (
                    raw.startsWith('[')
                ) {
                    try {
                        const parsed =
                            JSON.parse(raw);

                        return Array.isArray(
                            parsed
                        )
                            ? parsed.map(String)
                            : [];
                    } catch (error) {
                        return [];
                    }
                }

                return raw
                    .split(',')
                    .map(
                        item =>
                            item.trim()
                    )
                    .filter(Boolean);
            }

            function filterExceptionsByBuilding() {
                exceptionList
                    ?.querySelectorAll(
                        '.clinic-exception'
                    )
                    .forEach(card => {
                        const keys =
                            parseBuildingKeys(
                                card.dataset
                                    .buildingKeys
                            );

                        const visible =
                            activeBuildingKey
                                === 'all'
                            || keys.includes(
                                'all'
                            )
                            || keys.includes(
                                activeBuildingKey
                            );

                        card.classList.toggle(
                            'is-building-hidden',
                            !visible
                        );
                    });
            }

            function updateCounter() {
                const count = Array.from(
                    exceptionList
                        ?.querySelectorAll(
                            '.clinic-exception'
                        )
                    || []
                )
                    .filter(
                        card =>
                            !card.classList.contains(
                                'is-building-hidden'
                            )
                    )
                    .length;

                if (exceptionCounter) {
                    exceptionCounter
                        .textContent = count;
                }

                exceptionsEmpty
                    ?.classList.toggle(
                        'd-none',
                        count > 0
                    );
            }

            function syncBuildingFilter() {
                buildingTabs.forEach(
                    button => {
                        button.classList.toggle(
                            'is-active',
                            button.dataset
                                .buildingTab
                                === activeBuildingKey
                        );
                    }
                );

                filterExceptionsByBuilding();
                updateCounter();

                const activeButton =
                    buildingTabs.find(
                        button =>
                            button.dataset
                                .buildingTab
                                === activeBuildingKey
                    );

                if (
                    buildingFilterContext
                ) {
                    buildingFilterContext
                        .textContent =
                        activeBuildingKey === 'all'
                            ? 'Выбраны все корпуса. Недельные тайминги скрыты; особые правила показаны по всей клинике.'
                            : `Настраивается отдельный график: ${
                                activeButton
                                    ?.dataset
                                    .buildingLabel
                                || activeButton
                                    ?.textContent
                                    ?.trim()
                                || 'выбранный корпус'
                            }.`;
                }

                const url =
                    new URL(
                        window.location.href
                    );

                if (
                    activeBuildingKey
                    === 'all'
                ) {
                    url.searchParams.delete(
                        'building_id'
                    );
                } else {
                    url.searchParams.set(
                        'building_id',
                        activeBuildingKey
                    );
                }

                window.history
                    .replaceState(
                        {},
                        '',
                        url
                    );
            }

            buildingTabs.forEach(
                button => {
                    button.addEventListener(
                        'click',
                        () => {
                            const url =
                                button.dataset.buildingUrl;

                            if (url) {
                                window.location.href = url;
                            }
                        }
                    );
                }
            );

            function itemMatchesModalBuilding(
                item
            ) {
                if (
                    modalBuildingKey
                    === 'all'
                ) {
                    return true;
                }

                const single =
                    item.dataset
                        .buildingKey;

                if (
                    single
                    && single
                        === modalBuildingKey
                ) {
                    return true;
                }

                return parseBuildingKeys(
                    item.dataset
                        .buildingKeys
                )
                    .includes(
                        modalBuildingKey
                    );
            }

            function syncModalBuildingFilter() {
                exceptionBuildingTabs
                    .forEach(
                        button => {
                            button.classList.toggle(
                                'is-active',
                                button.dataset
                                    .modalBuildingTab
                                    === modalBuildingKey
                            );
                        }
                    );

                [
                    {
                        box: document.getElementById(
                            'exceptionOfficeTargets'
                        ),
                        empty:
                            officeTargetsEmpty,
                    },
                    {
                        box: document.getElementById(
                            'exceptionDoctorTargets'
                        ),
                        empty:
                            doctorTargetsEmpty,
                    },
                ].forEach(
                    ({box, empty}) => {
                        if (!box) {
                            return;
                        }

                        let visibleCount = 0;

                        box.querySelectorAll(
                            '.clinic-target-item'
                        )
                            .forEach(item => {
                                const visible =
                                    itemMatchesModalBuilding(
                                        item
                                    );

                                item.classList.toggle(
                                    'd-none',
                                    !visible
                                );

                                if (visible) {
                                    visibleCount++;
                                }
                            });

                        empty?.classList.toggle(
                            'd-none',
                            visibleCount > 0
                        );
                    }
                );

                if (
                    exceptionBuildingFilterHint
                ) {
                    const button =
                        exceptionBuildingTabs
                            .find(
                                item =>
                                    item.dataset
                                        .modalBuildingTab
                                    === modalBuildingKey
                            );

                    exceptionBuildingFilterHint
                        .textContent =
                        modalBuildingKey === 'all'
                            ? 'Показаны все корпуса. Уже выбранные кабинеты и врачи сохраняются при переключении фильтра.'
                            : `Показан ${
                                button
                                    ?.dataset
                                    .modalBuildingLabel
                                || button
                                    ?.textContent
                                    ?.trim()
                                || 'выбранный корпус'
                            }. Выбор в других корпусах сохраняется.`;
                }
            }

            exceptionBuildingTabs
                .forEach(
                    button => {
                        button.addEventListener(
                            'click',
                            () => {
                                modalBuildingKey =
                                    button.dataset
                                        .modalBuildingTab
                                    || 'all';

                                syncModalBuildingFilter();
                            }
                        );
                    }
                );

            function syncScope() {
                const selected =
                    exceptionScope?.value
                    || 'clinic';

                document
                    .querySelectorAll(
                        '.js-target-box'
                    )
                    .forEach(
                        box => {
                            box.classList.toggle(
                                'is-hidden',
                                box.dataset
                                    .target
                                    !== selected
                            );
                        }
                    );

                const usesBuildingFilter = [
                    'offices',
                    'doctors',
                ].includes(selected);

                exceptionBuildingFilter
                    ?.classList.toggle(
                        'd-none',
                        !usesBuildingFilter
                    );

                if (usesBuildingFilter) {
                    syncModalBuildingFilter();
                }
            }

            function selectedExceptionMode() {
                return exceptionModeInputs
                    .find(input => input.checked)
                    ?.value
                    || 'closed';
            }

            function syncAllDay() {
                const allDay =
                    exceptionAllDay?.value
                    === 'all_day';

                document
                    .querySelectorAll(
                        '.js-exception-time-field'
                    )
                    .forEach(
                        field => {
                            field.classList.toggle(
                                'd-none',
                                allDay
                            );
                        }
                    );
            }

            function syncModeTimeBehavior() {
                const mode = selectedExceptionMode();
                const requiresInterval = [
                    'blocked',
                    'override',
                    'extra',
                ].includes(mode);

                const allDayOption =
                    exceptionAllDay
                        ?.querySelector(
                            'option[value="all_day"]'
                        );

                if (allDayOption) {
                    allDayOption.disabled =
                        requiresInterval;
                }

                if (
                    requiresInterval
                    && exceptionAllDay
                    && exceptionAllDay.value
                        === 'all_day'
                ) {
                    exceptionAllDay.value =
                        'time';
                }

                if (exceptionModeHint) {
                    exceptionModeHint.textContent =
                        modeHints[mode]
                        || modeHints.closed;
                }

                syncAllDay();
            }

            function applyTypeModeRules(
                selectDefault = false
            ) {
                const type = exceptionType?.value
                    || 'other';
                const config = typeModeConfig[type]
                    || typeModeConfig.other;

                exceptionModeInputs.forEach(
                    input => {
                        const allowed =
                            config.allowed.includes(
                                input.value
                            );

                        input.disabled = !allowed;
                    }
                );

                exceptionModeCards.forEach(
                    card => {
                        card.classList.toggle(
                            'is-disabled',
                            !config.allowed.includes(
                                card.dataset.modeCard
                            )
                        );
                    }
                );

                const selected =
                    selectedExceptionMode();

                if (
                    selectDefault
                    || !config.allowed.includes(
                        selected
                    )
                ) {
                    const fallback =
                        exceptionModeInputs.find(
                            input =>
                                input.value
                                === config.defaultMode
                        );

                    if (fallback) {
                        fallback.checked = true;
                    }
                }

                if (
                    exceptionModeHint
                    && !modeHints[
                        selectedExceptionMode()
                    ]
                ) {
                    exceptionModeHint.textContent =
                        config.hint;
                }

                syncModeTimeBehavior();
            }

            function clearTargetSelection() {
                document
                    .querySelectorAll(
                        '.js-target-box input[type="checkbox"]'
                    )
                    .forEach(
                        input => {
                            input.checked =
                                false;
                        }
                    );
            }

            function applyQuarantineRules() {
                const isQuarantine =
                    exceptionType?.value
                    === 'quarantine';

                if (exceptionScope) {
                    if (isQuarantine) {
                        exceptionScope.value =
                            'clinic';
                    }

                    exceptionScope.disabled =
                        isQuarantine;
                }

                if (exceptionAllDay) {
                    if (isQuarantine) {
                        exceptionAllDay.value =
                            'all_day';
                    }

                    exceptionAllDay.disabled =
                        isQuarantine;
                }

                if (isQuarantine) {
                    clearTargetSelection();
                }

                syncScope();
                syncModeTimeBehavior();
            }

            function resetExceptionForm() {
                editingExceptionId = null;

                exceptionForm?.reset();

                if (exceptionType) {
                    exceptionType.value =
                        'quarantine';
                }

                if (exceptionTitle) {
                    exceptionTitle.value =
                        typeLabels.quarantine;
                }

                if (exceptionDateFrom) {
                    exceptionDateFrom.value =
                        todayValue;
                }

                if (exceptionDateTo) {
                    exceptionDateTo.value =
                        todayValue;
                }

                if (exceptionAllDay) {
                    exceptionAllDay.value =
                        'all_day';
                }

                if (exceptionTimeFrom) {
                    exceptionTimeFrom.value =
                        '09:00';
                }

                if (exceptionTimeTo) {
                    exceptionTimeTo.value =
                        '18:00';
                }

                const closedMode =
                    exceptionModeInputs.find(
                        input =>
                            input.value === 'closed'
                    );

                if (closedMode) {
                    closedMode.checked = true;
                }

                if (exceptionScope) {
                    exceptionScope.value =
                        'clinic';
                }

                if (exceptionComment) {
                    exceptionComment.value =
                        '';
                }

                clearTargetSelection();

                if (selectedBuildingId) {
                    const selectedBuildingTarget = document.querySelector(
                        `.js-target-box[data-target="buildings"] input[value="${selectedBuildingId}"]`
                    );

                    if (selectedBuildingTarget) {
                        selectedBuildingTarget.checked = true;
                    }
                }

                if (
                    exceptionModalTitle
                ) {
                    exceptionModalTitle
                        .textContent =
                        'Новое правило графика';
                }

                if (
                    saveExceptionBtn
                ) {
                    saveExceptionBtn
                        .innerHTML =
                        '<i class="bi bi-check2"></i> Добавить правило';
                }

                syncScope();
                applyTypeModeRules(true);
                applyQuarantineRules();
            }

            function fillExceptionForm(
                data
            ) {
                editingExceptionId =
                    Number(data.id);

                if (exceptionType) {
                    exceptionType.value =
                        data.type
                        || 'other';
                }

                if (exceptionTitle) {
                    exceptionTitle.value =
                        data.title
                        || '';
                }

                if (exceptionDateFrom) {
                    exceptionDateFrom.value =
                        data.date_from
                        || todayValue;
                }

                if (exceptionDateTo) {
                    exceptionDateTo.value =
                        data.date_to
                        || data.date_from
                        || todayValue;
                }

                if (exceptionAllDay) {
                    exceptionAllDay.value =
                        data.all_day
                            ? 'all_day'
                            : 'time';
                }

                if (exceptionTimeFrom) {
                    exceptionTimeFrom.value =
                        data.time_from
                        || '09:00';
                }

                if (exceptionTimeTo) {
                    exceptionTimeTo.value =
                        data.time_to
                        || '18:00';
                }

                const incomingMode =
                    data.mode === 'shortened'
                        ? 'override'
                        : data.mode === 'limited'
                            ? 'blocked'
                            : data.mode || 'closed';

                const modeInput =
                    exceptionModeInputs.find(
                        input =>
                            input.value
                            === incomingMode
                    );

                if (modeInput) {
                    modeInput.checked = true;
                }

                if (exceptionScope) {
                    exceptionScope.value =
                        data.scope
                        || 'clinic';
                }

                if (exceptionComment) {
                    exceptionComment.value =
                        data.comment
                        || '';
                }

                clearTargetSelection();

                const targetIds =
                    new Set(
                        (
                            data.target_ids
                            || []
                        ).map(
                            id =>
                                String(id)
                        )
                    );

                document
                    .querySelectorAll(
                        '.js-target-box input[type="checkbox"]'
                    )
                    .forEach(
                        input => {
                            input.checked =
                                targetIds.has(
                                    String(
                                        input.value
                                    )
                                );
                        }
                    );

                if (
                    exceptionModalTitle
                ) {
                    exceptionModalTitle
                        .textContent =
                        'Изменить правило графика';
                }

                if (
                    saveExceptionBtn
                ) {
                    saveExceptionBtn
                        .innerHTML =
                        '<i class="bi bi-check2"></i> Сохранить изменения';
                }

                syncScope();
                applyTypeModeRules(false);
                applyQuarantineRules();
            }

            function selectedTargetIds(
                scope
            ) {
                if (
                    scope === 'clinic'
                ) {
                    return [];
                }

                const box =
                    document.querySelector(
                        `.js-target-box[data-target="${scope}"]`
                    );

                return Array.from(
                    box?.querySelectorAll(
                        'input[type="checkbox"]:checked'
                    )
                    || []
                )
                    .map(
                        input =>
                            Number(
                                input.value
                            )
                    )
                    .filter(
                        id =>
                            Number.isInteger(
                                id
                            )
                            && id > 0
                    );
            }

            exceptionType
                ?.addEventListener(
                    'change',
                    () => {
                        if (exceptionTitle) {
                            exceptionTitle.value =
                                typeLabels[
                                    exceptionType.value
                                ]
                                || 'Ограничение работы';
                        }

                        if (
                            exceptionType.value === 'quarantine'
                            && exceptionAllDay
                        ) {
                            exceptionAllDay.value = 'all_day';
                        }

                        applyTypeModeRules(true);
                        applyQuarantineRules();
                    }
                );

            exceptionScope
                ?.addEventListener(
                    'change',
                    syncScope
                );

            exceptionAllDay
                ?.addEventListener(
                    'change',
                    syncModeTimeBehavior
                );

            exceptionModeInputs.forEach(
                input => {
                    input.addEventListener(
                        'change',
                        () => {
                            if (input.checked) {
                                syncModeTimeBehavior();
                            }
                        }
                    );
                }
            );

            document
                .querySelectorAll(
                    '[data-bs-target="#clinicExceptionModal"]'
                )
                .forEach(
                    button => {
                        if (
                            button.classList
                                .contains(
                                    'js-edit-exception'
                                )
                        ) {
                            return;
                        }

                        button.addEventListener(
                            'click',
                            () => {
                                resetExceptionForm();
                            }
                        );
                    }
                );

            document
                .querySelectorAll(
                    '.js-edit-exception'
                )
                .forEach(
                    button => {
                        button.addEventListener(
                            'click',
                            () => {
                                const id =
                                    String(
                                        button.dataset
                                            .exceptionId
                                        || ''
                                    );

                                const data =
                                    exceptionData[id];

                                if (!data) {
                                    window.alert(
                                        'Не удалось получить данные правила.'
                                    );

                                    return;
                                }

                                fillExceptionForm(
                                    data
                                );

                                const pageBuildingExists =
                                    exceptionBuildingTabs
                                        .some(
                                            tab =>
                                                tab.dataset
                                                    .modalBuildingTab
                                                === activeBuildingKey
                                        );

                                modalBuildingKey =
                                    pageBuildingExists
                                    && activeBuildingKey
                                        !== 'all'
                                        ? activeBuildingKey
                                        : 'all';

                                syncModalBuildingFilter();

                                bootstrap.Modal
                                    .getOrCreateInstance(
                                        exceptionModalEl
                                    )
                                    .show();
                            }
                        );
                    }
                );

            document
                .querySelectorAll(
                    '.js-delete-exception'
                )
                .forEach(
                    button => {
                        button.addEventListener(
                            'click',
                            async () => {
                                const id =
                                    Number(
                                        button.dataset
                                            .exceptionId
                                    );

                                if (
                                    !id
                                    || !window.confirm(
                                        'Удалить это правило?'
                                    )
                                ) {
                                    return;
                                }

                                button.disabled =
                                    true;

                                try {
                                    const response =
                                        await fetch(
                                            `${exceptionBaseUrl}/${id}/delete`,
                                            {
                                                method:
                                                    'POST',
                                                headers: {
                                                    'Accept':
                                                        'application/json',
                                                    'X-CSRF-TOKEN':
                                                        csrfToken,
                                                },
                                            }
                                        );

                                    await parseResponse(
                                        response
                                    );

                                    window.location.reload();
                                } catch (error) {
                                    button.disabled =
                                        false;

                                    window.alert(
                                        error.message
                                        || 'Не удалось удалить правило.'
                                    );
                                }
                            }
                        );
                    }
                );

            exceptionModalEl
                ?.addEventListener(
                    'show.bs.modal',
                    () => {
                        const pageBuildingExists =
                            exceptionBuildingTabs
                                .some(
                                    button =>
                                        button.dataset
                                            .modalBuildingTab
                                        === activeBuildingKey
                                );

                        modalBuildingKey =
                            pageBuildingExists
                            && activeBuildingKey
                                !== 'all'
                                ? activeBuildingKey
                                : 'all';

                        syncModalBuildingFilter();
                        syncScope();
                        syncAllDay();
                        applyQuarantineRules();
                    }
                );

            exceptionModalEl
                ?.addEventListener(
                    'hidden.bs.modal',
                    () => {
                        editingExceptionId =
                            null;
                    }
                );

            saveExceptionBtn
                ?.addEventListener(
                    'click',
                    async () => {
                        const isQuarantine =
                            exceptionType?.value
                            === 'quarantine';

                        const scope =
                            isQuarantine
                                ? 'clinic'
                                : exceptionScope?.value
                                    || 'clinic';

                        const allDay =
                            isQuarantine
                            || exceptionAllDay?.value
                                === 'all_day';

                        const targetIds =
                            isQuarantine
                                ? []
                                : selectedTargetIds(
                                    scope
                                );

                        if (
                            !exceptionDateFrom
                                ?.value
                            || !exceptionDateTo
                                ?.value
                        ) {
                            window.alert(
                                'Укажите дату начала и окончания правила.'
                            );

                            return;
                        }

                        if (
                            scope !== 'clinic'
                            && targetIds.length
                                === 0
                        ) {
                            window.alert(
                                'Выберите хотя бы один корпус, кабинет или одного врача.'
                            );

                            return;
                        }

                        if (
                            !allDay
                            && (
                                !exceptionTimeFrom
                                    ?.value
                                || !exceptionTimeTo
                                    ?.value
                            )
                        ) {
                            window.alert(
                                'Укажите время начала и окончания правила.'
                            );

                            return;
                        }

                        const payload = {
                            type:
                                exceptionType
                                    ?.value
                                || 'other',
                            title:
                                exceptionTitle
                                    ?.value
                                    ?.trim()
                                || 'Ограничение работы',
                            date_from:
                                exceptionDateFrom
                                    .value,
                            date_to:
                                exceptionDateTo
                                    .value,
                            all_day:
                                allDay,
                            time_from:
                                allDay
                                    ? null
                                    : exceptionTimeFrom
                                        ?.value
                                        || null,
                            time_to:
                                allDay
                                    ? null
                                    : exceptionTimeTo
                                        ?.value
                                        || null,
                            mode:
                                isQuarantine
                                    ? 'closed'
                                    : document
                                        .querySelector(
                                            'input[name="restriction_mode"]:checked'
                                        )
                                        ?.value
                                        || 'closed',
                            scope,
                            target_ids:
                                targetIds,
                            comment:
                                exceptionComment
                                    ?.value
                                    ?.trim()
                                || null,
                        };

                        const url =
                            editingExceptionId
                                ? `${exceptionBaseUrl}/${editingExceptionId}`
                                : exceptionBaseUrl;

                        const originalHtml =
                            saveExceptionBtn
                                .innerHTML;

                        saveExceptionBtn.disabled =
                            true;

                        saveExceptionBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1"></span> Сохранение';

                        try {
                            const response =
                                await fetch(
                                    url,
                                    {
                                        method:
                                            'POST',
                                        headers: {
                                            'Accept':
                                                'application/json',
                                            'Content-Type':
                                                'application/json',
                                            'X-CSRF-TOKEN':
                                                csrfToken,
                                        },
                                        body:
                                            JSON.stringify(
                                                payload
                                            ),
                                    }
                                );

                            await parseResponse(
                                response
                            );

                            bootstrap.Modal
                                .getOrCreateInstance(
                                    exceptionModalEl
                                )
                                .hide();

                            window.location.reload();
                        } catch (error) {
                            saveExceptionBtn.disabled =
                                false;

                            saveExceptionBtn.innerHTML =
                                originalHtml;

                            window.alert(
                                error.message
                                || 'Не удалось сохранить правило.'
                            );
                        }
                    }
                );

            syncBuildingFilter();
            syncModalBuildingFilter();
            syncScope();
            syncAllDay();
        })();
    </script>
@endpush