@extends('doctors.settings')

@section('settings-title', 'Расписание врачей')

@section('assets')
    @parent

    <style>
        .doctor-schedule-page .settings-container {
            width: min(2100px, 100%);
        }

        .doctor-schedule-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .doctor-schedule-week-nav,
        .doctor-schedule-actions,
        .doctor-schedule-filters {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .doctor-schedule-week-button,
        .doctor-schedule-view-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid #d8e0e5;
            border-radius: 12px;
            background: #fff;
            color: #344054;
            font-size: 13px;
            font-weight: 750;
            transition: .16s ease;
        }

        .doctor-schedule-week-button:hover,
        .doctor-schedule-view-button:hover {
            border-color: #bfcdd6;
            background: #f8fafc;
            color: #111827;
        }

        .doctor-schedule-view-button.active {
            border-color: var(--settings-primary);
            background: #e8f8fa;
            color: var(--settings-primary-dark);
        }

        .doctor-schedule-week-label {
            min-width: 218px;
            text-align: center;
            color: var(--settings-text);
            font-size: 15px;
            font-weight: 800;
        }

        .doctor-schedule-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .doctor-schedule-summary-card {
            position: relative;
            overflow: hidden;
            min-height: 104px;
            padding: 17px 18px;
            border: 1px solid var(--settings-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .045);
        }

        .doctor-schedule-summary-card::before {
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, var(--settings-primary), var(--settings-primary-light));
            content: '';
        }

        .doctor-schedule-summary-card:nth-child(2)::before { background: #64748b; }
        .doctor-schedule-summary-card:nth-child(3)::before { background: #2563eb; }
        .doctor-schedule-summary-card:nth-child(4)::before { background: #f59e0b; }

        .doctor-schedule-summary-label {
            color: var(--settings-muted);
            font-size: 12px;
            font-weight: 750;
        }

        .doctor-schedule-summary-value {
            margin-top: 7px;
            color: var(--settings-text);
            font-size: 28px;
            font-weight: 850;
            line-height: 1;
        }

        .doctor-schedule-summary-hint {
            margin-top: 7px;
            color: #8b95a1;
            font-size: 11px;
        }

        .doctor-schedule-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 15px 18px;
            border-bottom: 1px solid var(--settings-border);
            background: #fbfcfd;
        }

        .doctor-schedule-filter-bar .form-control,
        .doctor-schedule-filter-bar .form-select {
            min-height: 40px;
            border-color: #d8e0e5;
            border-radius: 11px;
            font-size: 13px;
            box-shadow: none;
        }

        .doctor-schedule-filter-bar .form-control:focus,
        .doctor-schedule-filter-bar .form-select:focus {
            border-color: var(--settings-primary);
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .12);
        }

        .doctor-schedule-search {
            position: relative;
            width: min(310px, 100%);
        }

        .doctor-schedule-search i {
            position: absolute;
            top: 50%;
            left: 13px;
            z-index: 2;
            color: #98a2b3;
            transform: translateY(-50%);
        }

        .doctor-schedule-search input {
            padding-left: 36px;
        }

        .doctor-schedule-board-wrap {
            overflow-x: hidden;
            overflow-y: hidden;
            background:
                radial-gradient(circle at top right, rgba(4, 134, 150, .10), transparent 26%),
                linear-gradient(180deg, rgba(4, 134, 150, .05) 0%, rgba(4, 134, 150, .02) 18%, #fff 42%);
        }

        .doctor-schedule-board {
            width: 100%;
            min-width: 1080px;
        }

        .doctor-schedule-grid-header,
        .doctor-schedule-grid-body {
            display: grid;
            grid-template-columns: 76px repeat(7, minmax(0, 1fr));
        }

        @media (max-width: 1199px) {
            .doctor-schedule-board-wrap {
                overflow-x: auto;
            }
        }

        .doctor-schedule-grid-header {
            position: sticky;
            top: 0;
            z-index: 20;
            min-height: 76px;
            border-bottom: 1px solid var(--settings-border);
            background: linear-gradient(180deg, rgba(4, 134, 150, .08) 0%, rgba(4, 134, 150, .03) 55%, rgba(255, 255, 255, .96) 100%);
            backdrop-filter: blur(6px);
        }

        .doctor-schedule-time-head {
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid var(--settings-border);
            color: #98a2b3;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .doctor-schedule-day-head {
            display: flex;
            min-width: 0;
            align-items: center;
            justify-content: center;
            padding: 10px 8px;
            border-right: 1px solid var(--settings-border);
            text-align: center;
        }

        .doctor-schedule-day-head:last-child {
            border-right: 0;
        }

        .doctor-schedule-day-head.is-today {
            background: #f1fbfc;
        }

        .doctor-schedule-day-name {
            color: #667085;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .doctor-schedule-day-date {
            margin-top: 3px;
            color: var(--settings-text);
            font-size: 18px;
            font-weight: 850;
        }

        .doctor-schedule-day-head.is-today .doctor-schedule-day-date {
            color: var(--settings-primary-dark);
        }

        .doctor-schedule-grid-body {
            height: 720px;
        }

        .doctor-schedule-time-column {
            position: relative;
            border-right: 1px solid var(--settings-border);
            background: linear-gradient(180deg, rgba(4, 134, 150, .08) 0%, rgba(4, 134, 150, .03) 16%, #fbfcfd 40%);
        }

        .doctor-schedule-time-label {
            position: absolute;
            right: 11px;
            color: #7a8695;
            font-size: 11px;
            font-weight: 700;
            transform: translateY(-50%);
        }

        .doctor-schedule-day-column {
            position: relative;
            overflow: hidden;
            border-right: 1px solid var(--settings-border);
            background:
                linear-gradient(180deg, rgba(4, 134, 150, .08) 0%, rgba(4, 134, 150, .025) 14%, rgba(255, 255, 255, .98) 32%),
                linear-gradient(to bottom,
                    rgba(226, 232, 240, .92) 1px,
                    transparent 1px),
                linear-gradient(to bottom,
                    rgba(226, 232, 240, .44) 1px,
                    transparent 1px);
            background-position: 0 0, 0 0, 0 30px;
            background-size: 100% 100%, 100% 60px, 100% 60px;
        }

        .doctor-schedule-day-column:last-child {
            border-right: 0;
        }

        .doctor-schedule-day-column.is-today {
            background:
                linear-gradient(180deg, rgba(4, 134, 150, .14) 0%, rgba(4, 134, 150, .05) 15%, rgba(252, 255, 255, .98) 34%),
                linear-gradient(to bottom,
                    rgba(226, 232, 240, .92) 1px,
                    transparent 1px),
                linear-gradient(to bottom,
                    rgba(226, 232, 240, .44) 1px,
                    transparent 1px);
            background-position: 0 0, 0 0, 0 30px;
            background-size: 100% 100%, 100% 60px, 100% 60px;
        }

        .doctor-schedule-day-column.is-drop-target {
            background:
                linear-gradient(180deg, rgba(4, 134, 150, .18) 0%, rgba(4, 134, 150, .08) 16%, rgba(238, 250, 251, .98) 36%),
                linear-gradient(to bottom,
                    rgba(226, 232, 240, .92) 1px,
                    transparent 1px),
                linear-gradient(to bottom,
                    rgba(226, 232, 240, .44) 1px,
                    transparent 1px);
            background-position: 0 0, 0 0, 0 30px;
            background-size: 100% 100%, 100% 60px, 100% 60px;
        }

        .doctor-shift-card {
            position: absolute;
            z-index: 4;
            min-width: 0;
            min-height: 30px;
            box-sizing: border-box;
            overflow: hidden;
            border: 1px solid rgba(4, 134, 150, .28);
            border-left: 4px solid var(--settings-primary);
            border-radius: 11px;
            background: linear-gradient(135deg, #f1fbfc 0%, #fff 100%);
            box-shadow: 0 7px 18px rgba(15, 23, 42, .08);
            cursor: grab;
            user-select: none;
            transition: box-shadow .15s ease, transform .15s ease, opacity .15s ease;
        }

        .doctor-shift-card:hover {
            z-index: 8;
            box-shadow: 0 12px 25px rgba(15, 23, 42, .14);
            transform: translateY(-1px);
        }

        .doctor-shift-card.is-dragging,
        .doctor-shift-card.is-resizing {
            z-index: 30;
            opacity: .88;
            cursor: grabbing;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .2);
            transform: none;
        }

        .doctor-shift-card[data-tone="blue"] {
            border-color: rgba(37, 99, 235, .25);
            border-left-color: #2563eb;
            background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);
        }

        .doctor-shift-card[data-tone="violet"] {
            border-color: rgba(124, 58, 237, .22);
            border-left-color: #7c3aed;
            background: linear-gradient(135deg, #f5f3ff 0%, #fff 100%);
        }

        .doctor-shift-card[data-tone="amber"] {
            border-color: rgba(217, 119, 6, .24);
            border-left-color: #d97706;
            background: linear-gradient(135deg, #fffbeb 0%, #fff 100%);
        }

        .doctor-shift-card[data-tone="green"] {
            border-color: rgba(22, 163, 74, .24);
            border-left-color: #16a34a;
            background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
        }

        .doctor-shift-card__body {
            height: 100%;
            padding: 8px 9px 13px;
        }

        .doctor-shift-card__doctor {
            overflow: hidden;
            color: #1f2937;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doctor-shift-card__speciality {
            overflow: hidden;
            margin-top: 2px;
            color: #667085;
            font-size: 10px;
            font-weight: 650;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doctor-shift-card__meta {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 6px;
            color: #475467;
            font-size: 10px;
            font-weight: 750;
        }

        .doctor-shift-card__meta span {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: 4px;
        }

        .doctor-shift-card__time {
            color: var(--settings-primary-dark);
            font-weight: 850;
        }

        .doctor-shift-card__edit {
            position: absolute;
            top: 5px;
            right: 5px;
            display: grid;
            width: 24px;
            height: 24px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: rgba(255, 255, 255, .88);
            color: #667085;
            font-size: 11px;
            opacity: 0;
            transition: .15s ease;
        }

        .doctor-shift-card:hover .doctor-shift-card__edit {
            opacity: 1;
        }

        .doctor-shift-card__resize {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 9px;
            cursor: ns-resize;
        }

        .doctor-shift-card__resize::after {
            position: absolute;
            bottom: 3px;
            left: 50%;
            width: 28px;
            height: 2px;
            border-radius: 999px;
            background: rgba(71, 84, 103, .26);
            content: '';
            transform: translateX(-50%);
        }


        .doctor-shift-card.is-stacked {
            box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
        }

        .doctor-shift-card.is-stacked:not(.is-active) {
            cursor: pointer;
        }

        .doctor-shift-card.is-active {
            z-index: 18 !important;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .16);
            transform: translateY(-1px);
        }

        .doctor-shift-card__doctor--compact {
            font-size: 11px;
        }

        .doctor-shift-card__doctor--tiny {
            font-size: 10px;
        }

        .doctor-shift-card__speciality.is-hidden,
        .doctor-shift-card__meta.is-hidden {
            display: none;
        }

        .doctor-shift-card--more {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-style: dashed;
            border-left-style: solid;
            background: linear-gradient(135deg, rgba(4, 134, 150, .14) 0%, rgba(255, 255, 255, .98) 100%);
        }

        .doctor-shift-card--more .doctor-shift-card__body {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 4px;
            padding: 10px 8px;
        }

        .doctor-shift-card__more-count {
            color: var(--settings-primary-dark);
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
        }

        .doctor-shift-card__more-label {
            color: #516172;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.2;
        }

        .doctor-shift-cluster {
            position: absolute;
            z-index: 5;
            right: 7px;
            left: 7px;
            min-height: 40px;
            overflow: hidden;
            border: 1px solid rgba(4, 134, 150, .28);
            border-left: 4px solid var(--settings-primary);
            border-radius: 12px;
            background: linear-gradient(135deg, #edf9fb 0%, #fff 100%);
            box-shadow: 0 7px 18px rgba(15, 23, 42, .08);
            cursor: pointer;
            user-select: none;
            transition: .15s ease;
        }

        .doctor-shift-cluster:hover {
            z-index: 9;
            border-color: rgba(4, 134, 150, .44);
            box-shadow: 0 12px 26px rgba(15, 23, 42, .14);
            transform: translateY(-1px);
        }

        .doctor-shift-cluster__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 7px;
            padding: 7px 8px 5px;
        }

        .doctor-shift-cluster__count {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: 5px;
            color: var(--settings-primary-dark);
            font-size: 11px;
            font-weight: 850;
            white-space: nowrap;
        }

        .doctor-shift-cluster__time {
            overflow: hidden;
            color: #667085;
            font-size: 9px;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doctor-shift-cluster__list {
            display: grid;
            gap: 3px;
            padding: 0 7px 7px;
        }

        .doctor-shift-cluster__row {
            display: grid;
            grid-template-columns: 7px minmax(0, 1fr) auto;
            align-items: center;
            gap: 5px;
            min-height: 20px;
            padding: 2px 5px;
            border-radius: 7px;
            background: rgba(255, 255, 255, .82);
            color: #344054;
            font-size: 9px;
            font-weight: 750;
        }

        .doctor-shift-cluster__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--settings-primary);
        }

        .doctor-shift-cluster__dot.blue { background: #2563eb; }
        .doctor-shift-cluster__dot.violet { background: #7c3aed; }
        .doctor-shift-cluster__dot.amber { background: #d97706; }
        .doctor-shift-cluster__dot.green { background: #16a34a; }

        .doctor-shift-cluster__name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .doctor-shift-cluster__row-time {
            color: #667085;
            font-size: 8px;
            white-space: nowrap;
        }

        .doctor-shift-cluster__more {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 20px;
            padding: 2px 5px;
            border-radius: 7px;
            background: #dff5f7;
            color: var(--settings-primary-dark);
            font-size: 9px;
            font-weight: 850;
        }

        .doctor-overlap-list {
            display: grid;
            gap: 9px;
        }

        .doctor-overlap-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 12px 13px;
            border: 1px solid #e1e8ed;
            border-radius: 13px;
            background: #fff;
        }

        .doctor-overlap-item__name {
            color: #1f2937;
            font-size: 13px;
            font-weight: 850;
        }

        .doctor-overlap-item__meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 4px;
            color: #667085;
            font-size: 11px;
        }

        .doctor-overlap-item__edit {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 6px 10px;
            border: 1px solid #d8e0e5;
            border-radius: 9px;
            background: #fff;
            color: #344054;
            font-size: 11px;
            font-weight: 750;
        }

        .doctor-schedule-now-line {
            position: absolute;
            z-index: 10;
            right: 0;
            left: 0;
            height: 2px;
            background: #ef4444;
            pointer-events: none;
        }

        .doctor-schedule-now-line::before {
            position: absolute;
            top: 50%;
            left: -4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            content: '';
            transform: translateY(-50%);
        }

        .doctor-schedule-help {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-top: 1px solid var(--settings-border);
            background: #fbfcfd;
            color: #667085;
            font-size: 11px;
            line-height: 1.45;
        }

        .doctor-schedule-help i {
            color: var(--settings-primary);
            font-size: 16px;
        }

        .doctor-schedule-legend {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .doctor-schedule-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #667085;
            font-size: 11px;
            font-weight: 700;
        }

        .doctor-schedule-legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--settings-primary);
        }

        .doctor-schedule-legend-dot.blue { background: #2563eb; }
        .doctor-schedule-legend-dot.violet { background: #7c3aed; }
        .doctor-schedule-legend-dot.amber { background: #d97706; }
        .doctor-schedule-legend-dot.green { background: #16a34a; }

        .doctor-schedule-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
        }

        .doctor-schedule-modal .modal-header {
            padding: 19px 22px;
            border-bottom: 1px solid var(--settings-border);
            background: linear-gradient(135deg, #f8feff 0%, #fff 100%);
        }

        .doctor-schedule-modal .modal-title {
            color: var(--settings-text);
            font-size: 19px;
            font-weight: 850;
        }

        .doctor-schedule-modal .modal-body {
            padding: 22px;
        }

        .doctor-schedule-modal .form-label {
            margin-bottom: 6px;
            color: #475467;
            font-size: 12px;
            font-weight: 750;
        }

        .doctor-schedule-modal .form-control,
        .doctor-schedule-modal .form-select {
            min-height: 43px;
            border-color: #d8e0e5;
            border-radius: 12px;
            font-size: 13px;
        }

        .doctor-schedule-modal .form-control:focus,
        .doctor-schedule-modal .form-select:focus {
            border-color: var(--settings-primary);
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .12);
        }

        .doctor-schedule-modal .modal-footer {
            padding: 15px 22px;
            border-top: 1px solid var(--settings-border);
            background: #fbfcfd;
        }

        .doctor-schedule-modal__delete {
            margin-right: auto;
        }

        .doctor-schedule-empty-day {
            position: absolute;
            top: 50%;
            right: 10px;
            left: 10px;
            color: #c1c9d2;
            font-size: 11px;
            font-weight: 650;
            text-align: center;
            transform: translateY(-50%);
            pointer-events: none;
        }

        @media (max-width: 1100px) {
            .doctor-schedule-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .doctor-schedule-summary {
                grid-template-columns: 1fr;
            }

            .doctor-schedule-filter-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .doctor-schedule-filters,
            .doctor-schedule-search {
                width: 100%;
            }

            .doctor-schedule-filters .form-select {
                flex: 1 1 160px;
            }

            .doctor-schedule-week-label {
                min-width: 170px;
            }
        }
    </style>
@endsection

@section('sub-main')
    <main class="settings-page doctor-schedule-page">
        <div class="settings-container">
            <div class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">Расписание врачей</h1>
                    <p class="settings-page-description">
                        Недельный визуальный редактор рабочих смен. Положение блока соответствует времени начала,
                        высота — длительности смены. Одновременные смены раскладываются каскадом — как карты в солитёре, но по горизонтали, поэтому вся неделя остаётся компактной.
                    </p>
                </div>

                <div class="doctor-schedule-actions">
                    <button type="button"
                            class="settings-button settings-button--primary"
                            id="addDoctorShiftButton">
                        <i class="bi bi-plus-lg"></i>
                        Добавить смену
                    </button>
                </div>
            </div>

            <section class="doctor-schedule-summary" aria-label="Сводка по расписанию">
                <article class="doctor-schedule-summary-card">
                    <div class="doctor-schedule-summary-label">Смен на неделе</div>
                    <div class="doctor-schedule-summary-value" id="scheduleShiftsCount">0</div>
                    <div class="doctor-schedule-summary-hint">Запланированных интервалов</div>
                </article>

                <article class="doctor-schedule-summary-card">
                    <div class="doctor-schedule-summary-label">Врачей</div>
                    <div class="doctor-schedule-summary-value" id="scheduleDoctorsCount">0</div>
                    <div class="doctor-schedule-summary-hint">Уникальных специалистов</div>
                </article>

                <article class="doctor-schedule-summary-card">
                    <div class="doctor-schedule-summary-label">Кабинетов</div>
                    <div class="doctor-schedule-summary-value" id="scheduleOfficesCount">0</div>
                    <div class="doctor-schedule-summary-hint">Используются в расписании</div>
                </article>

                <article class="doctor-schedule-summary-card">
                    <div class="doctor-schedule-summary-label">Рабочих часов</div>
                    <div class="doctor-schedule-summary-value" id="scheduleHoursCount">0</div>
                    <div class="doctor-schedule-summary-hint">Суммарная нагрузка недели</div>
                </article>
            </section>

            <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Недельное расписание</h2>
                        <div class="settings-panel__subtitle">
                            Отображается одна календарная неделя — семь дней одновременно.
                        </div>
                    </div>

                    <div class="doctor-schedule-toolbar">
                        <div class="doctor-schedule-week-nav">
                            <button type="button" class="doctor-schedule-week-button" id="previousWeekButton" title="Предыдущая неделя">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="doctor-schedule-week-button" id="todayWeekButton">
                                Сегодня
                            </button>
                            <div class="doctor-schedule-week-label" id="weekRangeLabel"></div>
                            <button type="button" class="doctor-schedule-week-button" id="nextWeekButton" title="Следующая неделя">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="doctor-schedule-filter-bar">
                    <div class="doctor-schedule-filters">
                        <div class="doctor-schedule-search">
                            <i class="bi bi-search"></i>
                            <input type="search"
                                   class="form-control"
                                   id="scheduleSearch"
                                   placeholder="Врач или кабинет...">
                        </div>

                        <select class="form-select" id="scheduleSpecialityFilter" style="width: 220px">
                            <option value="">Все специальности</option>
                        </select>

                        <select class="form-select" id="scheduleOfficeFilter" style="width: 175px">
                            <option value="">Все кабинеты</option>
                        </select>
                    </div>

                    <div class="doctor-schedule-legend" aria-label="Цвета смен">
                        <span class="doctor-schedule-legend-item"><span class="doctor-schedule-legend-dot"></span> Психиатр</span>
                        <span class="doctor-schedule-legend-item"><span class="doctor-schedule-legend-dot blue"></span> Психотерапевт</span>
                        <span class="doctor-schedule-legend-item"><span class="doctor-schedule-legend-dot violet"></span> Психолог</span>
                        <span class="doctor-schedule-legend-item"><span class="doctor-schedule-legend-dot amber"></span> Невролог</span>
                        <span class="doctor-schedule-legend-item"><span class="doctor-schedule-legend-dot green"></span> Другое</span>
                    </div>
                </div>

                <div class="doctor-schedule-board-wrap" id="scheduleBoardScroll">
                    <div class="doctor-schedule-board">
                        <div class="doctor-schedule-grid-header" id="scheduleHeader">
                            <div class="doctor-schedule-time-head">Время</div>
                        </div>

                        <div class="doctor-schedule-grid-body" id="scheduleBody">
                            <div class="doctor-schedule-time-column" id="scheduleTimeColumn"></div>
                        </div>
                    </div>
                </div>

                <div class="doctor-schedule-help">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>
                        Если несколько сотрудников работают одновременно, их карточки накладываются каскадом со сдвигом вправо. Нажмите на карточку, чтобы поднять её наверх, а на «+N» — чтобы увидеть скрытые смены. Изменения пока не записываются в БД.
                    </span>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade doctor-schedule-modal"
         id="doctorShiftModal"
         tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="settings-panel__subtitle mb-1">Расписание врача</div>
                        <h2 class="modal-title" id="doctorShiftModalTitle">Новая смена</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="doctorShiftId">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="doctorShiftDoctor">Врач</label>
                            <select class="form-select" id="doctorShiftDoctor">
                                <option value="Иванов Иван Иванович">Иванов Иван Иванович</option>
                                <option value="Петрова Анна Сергеевна">Петрова Анна Сергеевна</option>
                                <option value="Сидоров Алексей Николаевич">Сидоров Алексей Николаевич</option>
                                <option value="Кузнецова Мария Андреевна">Кузнецова Мария Андреевна</option>
                                <option value="Орлова Елена Викторовна">Орлова Елена Викторовна</option>
                                <option value="Николаев Дмитрий Олегович">Николаев Дмитрий Олегович</option>
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label" for="doctorShiftSpeciality">Специальность</label>
                            <select class="form-select" id="doctorShiftSpeciality">
                                <option value="Врач-психиатр">Врач-психиатр</option>
                                <option value="Врач-психотерапевт">Врач-психотерапевт</option>
                                <option value="Медицинский психолог">Медицинский психолог</option>
                                <option value="Врач-невролог">Врач-невролог</option>
                                <option value="Врач функциональной диагностики">Врач функциональной диагностики</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label" for="doctorShiftOffice">Кабинет</label>
                            <select class="form-select" id="doctorShiftOffice">
                                <option value="201">№ 201</option>
                                <option value="202">№ 202</option>
                                <option value="204">№ 204</option>
                                <option value="207">№ 207</option>
                                <option value="214">№ 214</option>
                                <option value="216">№ 216</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="doctorShiftDay">День недели</label>
                            <select class="form-select" id="doctorShiftDay">
                                <option value="0">Понедельник</option>
                                <option value="1">Вторник</option>
                                <option value="2">Среда</option>
                                <option value="3">Четверг</option>
                                <option value="4">Пятница</option>
                                <option value="5">Суббота</option>
                                <option value="6">Воскресенье</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="doctorShiftStart">Начало</label>
                            <input type="time" class="form-control" id="doctorShiftStart" value="08:00" step="900">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="doctorShiftEnd">Окончание</label>
                            <input type="time" class="form-control" id="doctorShiftEnd" value="14:00" step="900">
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3 mb-0 d-none" id="doctorShiftError"></div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-danger doctor-schedule-modal__delete d-none"
                            id="deleteDoctorShiftButton">
                        <i class="bi bi-trash"></i>
                        Удалить
                    </button>

                    <button type="button" class="settings-button settings-button--secondary" data-bs-dismiss="modal">
                        Отмена
                    </button>
                    <button type="button" class="settings-button settings-button--primary" id="saveDoctorShiftButton">
                        <i class="bi bi-check-lg"></i>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade doctor-schedule-modal"
         id="doctorOverlapModal"
         tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="settings-panel__subtitle mb-1">Одновременно работают</div>
                        <h2 class="modal-title" id="doctorOverlapModalTitle">Сотрудники</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="doctor-overlap-list" id="doctorOverlapList"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const START_HOUR = 8;
            const END_HOUR = 20;
            const MINUTES_PER_PIXEL = 1;
            const SNAP_MINUTES = 15;
            const DAY_HORIZONTAL_PADDING = 7;
            const STACK_OFFSET = 18;
            const MAX_STACK_LAYERS = 5;
            const DAY_STACK_RIGHT_GUTTER = 8;
            const DAY_HEIGHT = (END_HOUR - START_HOUR) * 60 / MINUTES_PER_PIXEL;
            const WEEK_DAYS = [
                'Понедельник',
                'Вторник',
                'Среда',
                'Четверг',
                'Пятница',
                'Суббота',
                'Воскресенье'
            ];
            const WEEK_DAYS_SHORT = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'];
            const MONTHS_GENITIVE = [
                'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
                'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
            ];

            let currentWeekStart = startOfWeek(new Date(2026, 8, 21));
            let nextId = 20;
            let dragging = null;
            let resizing = null;

            let shifts = [
                { id: 1, day: 0, doctor: 'Иванов Иван Иванович', speciality: 'Врач-психиатр', office: '201', start: '08:00', end: '14:00' },
                { id: 14, day: 0, doctor: 'Сидоров Алексей Николаевич', speciality: 'Врач-психиатр', office: '214', start: '09:00', end: '13:00' },
                { id: 15, day: 0, doctor: 'Кузнецова Мария Андреевна', speciality: 'Медицинский психолог', office: '207', start: '10:30', end: '12:30' },
                { id: 2, day: 0, doctor: 'Петрова Анна Сергеевна', speciality: 'Врач-психотерапевт', office: '204', start: '14:15', end: '19:00' },
                { id: 3, day: 1, doctor: 'Кузнецова Мария Андреевна', speciality: 'Медицинский психолог', office: '207', start: '09:00', end: '15:00' },
                { id: 4, day: 1, doctor: 'Сидоров Алексей Николаевич', speciality: 'Врач-психиатр', office: '214', start: '15:15', end: '20:00' },
                { id: 5, day: 2, doctor: 'Иванов Иван Иванович', speciality: 'Врач-психиатр', office: '201', start: '08:00', end: '16:00' },
                { id: 6, day: 2, doctor: 'Орлова Елена Викторовна', speciality: 'Врач-невролог', office: '216', start: '16:15', end: '19:45' },
                { id: 7, day: 3, doctor: 'Петрова Анна Сергеевна', speciality: 'Врач-психотерапевт', office: '204', start: '08:30', end: '13:30' },
                { id: 8, day: 3, doctor: 'Николаев Дмитрий Олегович', speciality: 'Врач функциональной диагностики', office: '202', start: '14:00', end: '18:00' },
                { id: 9, day: 4, doctor: 'Сидоров Алексей Николаевич', speciality: 'Врач-психиатр', office: '214', start: '08:00', end: '14:00' },
                { id: 10, day: 4, doctor: 'Кузнецова Мария Андреевна', speciality: 'Медицинский психолог', office: '207', start: '14:15', end: '19:00' },
                { id: 11, day: 5, doctor: 'Иванов Иван Иванович', speciality: 'Врач-психиатр', office: '201', start: '09:00', end: '14:00' },
                { id: 12, day: 5, doctor: 'Орлова Елена Викторовна', speciality: 'Врач-невролог', office: '216', start: '14:15', end: '18:00' },
                { id: 13, day: 6, doctor: 'Петрова Анна Сергеевна', speciality: 'Врач-психотерапевт', office: '204', start: '10:00', end: '15:00' }
            ];

            const scheduleBoard = document.querySelector('.doctor-schedule-board');
            const scheduleHeader = document.getElementById('scheduleHeader');
            const scheduleBody = document.getElementById('scheduleBody');
            const scheduleTimeColumn = document.getElementById('scheduleTimeColumn');
            const weekRangeLabel = document.getElementById('weekRangeLabel');
            const searchInput = document.getElementById('scheduleSearch');
            const specialityFilter = document.getElementById('scheduleSpecialityFilter');
            const officeFilter = document.getElementById('scheduleOfficeFilter');

            const modalElement = document.getElementById('doctorShiftModal');
            const modal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
            const modalTitle = document.getElementById('doctorShiftModalTitle');
            const shiftIdInput = document.getElementById('doctorShiftId');
            const doctorInput = document.getElementById('doctorShiftDoctor');
            const specialityInput = document.getElementById('doctorShiftSpeciality');
            const officeInput = document.getElementById('doctorShiftOffice');
            const dayInput = document.getElementById('doctorShiftDay');
            const startInput = document.getElementById('doctorShiftStart');
            const endInput = document.getElementById('doctorShiftEnd');
            const modalError = document.getElementById('doctorShiftError');
            const deleteButton = document.getElementById('deleteDoctorShiftButton');

            const overlapModalElement = document.getElementById('doctorOverlapModal');
            const overlapModal = overlapModalElement ? bootstrap.Modal.getOrCreateInstance(overlapModalElement) : null;
            const overlapModalTitle = document.getElementById('doctorOverlapModalTitle');
            const overlapList = document.getElementById('doctorOverlapList');

            function startOfWeek(date) {
                const result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                const day = result.getDay();
                const diff = day === 0 ? -6 : 1 - day;
                result.setDate(result.getDate() + diff);
                result.setHours(0, 0, 0, 0);
                return result;
            }

            function addDays(date, days) {
                const result = new Date(date);
                result.setDate(result.getDate() + days);
                return result;
            }

            function isSameDate(a, b) {
                return a.getFullYear() === b.getFullYear()
                    && a.getMonth() === b.getMonth()
                    && a.getDate() === b.getDate();
            }

            function formatWeekRange(start) {
                const end = addDays(start, 6);
                const sameMonth = start.getMonth() === end.getMonth();
                const sameYear = start.getFullYear() === end.getFullYear();

                if (sameMonth && sameYear) {
                    return `${start.getDate()}–${end.getDate()} ${MONTHS_GENITIVE[end.getMonth()]} ${end.getFullYear()}`;
                }

                if (sameYear) {
                    return `${start.getDate()} ${MONTHS_GENITIVE[start.getMonth()]} – ${end.getDate()} ${MONTHS_GENITIVE[end.getMonth()]} ${end.getFullYear()}`;
                }

                return `${start.getDate()} ${MONTHS_GENITIVE[start.getMonth()]} ${start.getFullYear()} – ${end.getDate()} ${MONTHS_GENITIVE[end.getMonth()]} ${end.getFullYear()}`;
            }

            function timeToMinutes(value) {
                const [hours, minutes] = String(value).split(':').map(Number);
                return hours * 60 + minutes;
            }

            function minutesToTime(totalMinutes) {
                const minutes = Math.max(0, Math.round(totalMinutes));
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
                return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
            }

            function snap(value, step = SNAP_MINUTES) {
                return Math.round(value / step) * step;
            }

            function clamp(value, min, max) {
                return Math.max(min, Math.min(max, value));
            }

            function getTone(speciality) {
                if (speciality.includes('психотерапевт')) return 'blue';
                if (speciality.includes('психолог')) return 'violet';
                if (speciality.includes('невролог')) return 'amber';
                if (speciality.includes('психиатр')) return 'teal';
                return 'green';
            }

            function renderTimeline() {
                scheduleTimeColumn.innerHTML = '';
                scheduleTimeColumn.style.height = `${DAY_HEIGHT}px`;

                for (let hour = START_HOUR; hour <= END_HOUR; hour++) {
                    const label = document.createElement('div');
                    label.className = 'doctor-schedule-time-label';
                    label.style.top = `${(hour - START_HOUR) * 60 / MINUTES_PER_PIXEL}px`;
                    label.textContent = `${String(hour).padStart(2, '0')}:00`;
                    scheduleTimeColumn.appendChild(label);
                }
            }

            function renderHeaderAndDays() {
                scheduleHeader.querySelectorAll('.doctor-schedule-day-head').forEach(node => node.remove());
                scheduleBody.querySelectorAll('.doctor-schedule-day-column').forEach(node => node.remove());

                const today = new Date();

                for (let day = 0; day < 7; day++) {
                    const date = addDays(currentWeekStart, day);
                    const isToday = isSameDate(date, today);

                    const head = document.createElement('div');
                    head.className = `doctor-schedule-day-head${isToday ? ' is-today' : ''}`;
                    head.innerHTML = `
                        <div>
                            <div class="doctor-schedule-day-name">${WEEK_DAYS_SHORT[day]}</div>
                            <div class="doctor-schedule-day-date">${date.getDate()} ${MONTHS_GENITIVE[date.getMonth()]}</div>
                        </div>
                    `;
                    scheduleHeader.appendChild(head);

                    const column = document.createElement('div');
                    column.className = `doctor-schedule-day-column${isToday ? ' is-today' : ''}`;
                    column.dataset.day = String(day);
                    column.style.height = `${DAY_HEIGHT}px`;
                    scheduleBody.appendChild(column);
                }

                weekRangeLabel.textContent = formatWeekRange(currentWeekStart);
            }

            function matchesFilters(shift) {
                const query = searchInput.value.trim().toLowerCase();
                const speciality = specialityFilter.value;
                const office = officeFilter.value;

                const haystack = `${shift.doctor} ${shift.speciality} ${shift.office}`.toLowerCase();

                if (query && !haystack.includes(query)) return false;
                if (speciality && shift.speciality !== speciality) return false;
                if (office && shift.office !== office) return false;

                return true;
            }

            function shortDoctorName(fullName) {
                const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
                if (parts.length === 0) return 'Сотрудник';
                if (parts.length === 1) return parts[0];

                const initials = parts.slice(1, 3)
                    .map(part => `${part.charAt(0).toUpperCase()}.`)
                    .join('');

                return `${parts[0]} ${initials}`;
            }


            function createCard(shift, layout = null) {
                const dayStartMinutes = START_HOUR * 60;
                const top = (timeToMinutes(shift.start) - dayStartMinutes) / MINUTES_PER_PIXEL;
                const height = (timeToMinutes(shift.end) - timeToMinutes(shift.start)) / MINUTES_PER_PIXEL;
                const hasLayout = !!layout;
                const left = hasLayout ? layout.left : DAY_HORIZONTAL_PADDING;
                const widthPx = hasLayout
                    ? layout.width
                    : `calc(100% - ${DAY_HORIZONTAL_PADDING * 2}px)`;

                const card = document.createElement('article');
                card.className = `doctor-shift-card${hasLayout ? ' is-stacked' : ''}`;
                card.dataset.id = String(shift.id);
                card.dataset.tone = getTone(shift.speciality);
                card.style.top = `${top}px`;
                card.style.left = `${left}px`;
                card.style.width = typeof widthPx === 'number' ? `${Math.max(widthPx, 58)}px` : widthPx;
                card.style.height = `${Math.max(height, 30)}px`;
                if (hasLayout && Number.isFinite(layout.zIndex)) {
                    card.style.zIndex = String(layout.zIndex);
                }

                const widthValue = typeof widthPx === 'number' ? widthPx : 220;
                const compact = widthValue < 124;
                const tiny = widthValue < 94;
                const hideSpeciality = widthValue < 120 || height < 56;
                const hideMeta = widthValue < 104 || height < 48;
                const doctorClass = tiny
                    ? 'doctor-shift-card__doctor doctor-shift-card__doctor--tiny'
                    : compact
                        ? 'doctor-shift-card__doctor doctor-shift-card__doctor--compact'
                        : 'doctor-shift-card__doctor';

                card.innerHTML = `
                    <button type="button" class="doctor-shift-card__edit" title="Редактировать">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <div class="doctor-shift-card__body">
                        <div class="${doctorClass}">${escapeHtml(compact ? shortDoctorName(shift.doctor) : shift.doctor)}</div>
                        <div class="doctor-shift-card__speciality${hideSpeciality ? ' is-hidden' : ''}">${escapeHtml(shift.speciality)}</div>
                        <div class="doctor-shift-card__meta${hideMeta ? ' is-hidden' : ''}">
                            <span><i class="bi bi-door-open"></i> каб. ${escapeHtml(shift.office)}</span>
                            <span class="doctor-shift-card__time"><i class="bi bi-clock"></i> ${shift.start}–${shift.end}</span>
                        </div>
                    </div>
                    <div class="doctor-shift-card__resize" title="Изменить длительность"></div>
                `;

                card.querySelector('.doctor-shift-card__edit').addEventListener('click', event => {
                    event.stopPropagation();
                    openEditModal(shift.id);
                });

                card.addEventListener('dblclick', event => {
                    if (!event.target.closest('.doctor-shift-card__resize')) {
                        openEditModal(shift.id);
                    }
                });

                card.addEventListener('click', event => {
                    event.stopPropagation();
                    if (hasLayout) {
                        elevateStackCard(card);
                    }
                });

                card.addEventListener('pointerdown', startDrag);
                card.querySelector('.doctor-shift-card__resize').addEventListener('pointerdown', startResize);

                return card;
            }

            function buildDayOverlapGroups(dayShifts) {
                const ordered = [...dayShifts].sort((a, b) => {
                    const startDiff = timeToMinutes(a.start) - timeToMinutes(b.start);
                    if (startDiff !== 0) return startDiff;
                    return timeToMinutes(a.end) - timeToMinutes(b.end);
                });

                const groups = [];
                let current = [];
                let currentEnd = -Infinity;

                ordered.forEach(shift => {
                    const start = timeToMinutes(shift.start);
                    const end = timeToMinutes(shift.end);

                    if (current.length > 0 && start >= currentEnd) {
                        groups.push({
                            shifts: current,
                            start: Math.min(...current.map(item => timeToMinutes(item.start))),
                            end: Math.max(...current.map(item => timeToMinutes(item.end)))
                        });
                        current = [];
                        currentEnd = -Infinity;
                    }

                    current.push(shift);
                    currentEnd = Math.max(currentEnd, end);
                });

                if (current.length > 0) {
                    groups.push({
                        shifts: current,
                        start: Math.min(...current.map(item => timeToMinutes(item.start))),
                        end: Math.max(...current.map(item => timeToMinutes(item.end)))
                    });
                }

                return groups;
            }

            function openOverlapModal(group, day) {
                if (!overlapModal || !overlapList) return;

                const date = addDays(currentWeekStart, day);
                overlapModalTitle.textContent = `${WEEK_DAYS[day]}, ${date.getDate()} ${MONTHS_GENITIVE[date.getMonth()]} · ${minutesToTime(group.start)}–${minutesToTime(group.end)}`;

                overlapList.innerHTML = group.shifts
                    .slice()
                    .sort((a, b) => timeToMinutes(a.start) - timeToMinutes(b.start))
                    .map(shift => `
                        <article class="doctor-overlap-item">
                            <div>
                                <div class="doctor-overlap-item__name">${escapeHtml(shift.doctor)}</div>
                                <div class="doctor-overlap-item__meta">
                                    <span>${escapeHtml(shift.speciality)}</span>
                                    <span><i class="bi bi-door-open"></i> каб. ${escapeHtml(shift.office)}</span>
                                    <span><i class="bi bi-clock"></i> ${shift.start}–${shift.end}</span>
                                </div>
                            </div>
                            <button type="button"
                                    class="doctor-overlap-item__edit"
                                    data-shift-id="${shift.id}">
                                <i class="bi bi-pencil"></i>
                                Изменить
                            </button>
                        </article>
                    `)
                    .join('');

                overlapList.querySelectorAll('[data-shift-id]').forEach(button => {
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.shiftId);
                        overlapModalElement.addEventListener('hidden.bs.modal', () => {
                            openEditModal(id);
                        }, { once: true });
                        overlapModal.hide();
                    });
                });

                overlapModal.show();
            }

            function createMoreCard(group, day, layout) {
                const dayStartMinutes = START_HOUR * 60;
                const top = (group.start - dayStartMinutes) / MINUTES_PER_PIXEL;
                const height = Math.max((group.end - group.start) / MINUTES_PER_PIXEL, 44);
                const visibleCount = Math.max(0, Math.min(MAX_STACK_LAYERS - 1, group.shifts.length));
                const hiddenCount = Math.max(0, group.shifts.length - visibleCount);

                const card = document.createElement('article');
                card.className = 'doctor-shift-card doctor-shift-card--more is-stacked';
                card.style.top = `${top}px`;
                card.style.left = `${layout.left}px`;
                card.style.width = `${Math.max(layout.width, 58)}px`;
                card.style.height = `${height}px`;
                card.style.zIndex = String(layout.zIndex);
                card.innerHTML = `
                    <div class="doctor-shift-card__body">
                        <div class="doctor-shift-card__more-count">+${hiddenCount}</div>
                        <div class="doctor-shift-card__more-label">ещё врач${hiddenCount === 1 ? '' : hiddenCount < 5 ? 'а' : 'ей'}<br>${minutesToTime(group.start)}–${minutesToTime(group.end)}</div>
                    </div>
                `;
                card.title = 'Показать скрытые смены';
                card.addEventListener('click', event => {
                    event.stopPropagation();
                    openOverlapModal(group, day);
                });
                return card;
            }

            function elevateStackCard(card) {
                const column = card.closest('.doctor-schedule-day-column');
                if (!column) return;
                column.querySelectorAll('.doctor-shift-card.is-stacked').forEach(node => {
                    if (node.classList.contains('doctor-shift-card--more')) return;
                    node.classList.remove('is-active');
                    if (node.dataset.baseZ) {
                        node.style.zIndex = node.dataset.baseZ;
                    }
                });
                if (!card.classList.contains('doctor-shift-card--more')) {
                    card.classList.add('is-active');
                    card.style.zIndex = '18';
                }
            }

            function appendCascadeGroup(column, group, day) {
                const ordered = group.shifts
                    .slice()
                    .sort((a, b) => timeToMinutes(a.start) - timeToMinutes(b.start) || timeToMinutes(a.end) - timeToMinutes(b.end));

                const needsMoreCard = ordered.length > MAX_STACK_LAYERS;
                const visibleShiftCount = needsMoreCard ? MAX_STACK_LAYERS - 1 : ordered.length;
                const renderedLayers = visibleShiftCount + (needsMoreCard ? 1 : 0);
                const availableWidth = column.clientWidth - DAY_HORIZONTAL_PADDING * 2 - DAY_STACK_RIGHT_GUTTER - STACK_OFFSET * Math.max(renderedLayers - 1, 0);
                const cardWidth = Math.max(availableWidth, 74);

                ordered.slice(0, visibleShiftCount).forEach((shift, index) => {
                    const layout = {
                        left: DAY_HORIZONTAL_PADDING + index * STACK_OFFSET,
                        width: cardWidth,
                        zIndex: 4 + index
                    };
                    const card = createCard(shift, layout);
                    card.dataset.baseZ = String(4 + index);
                    column.appendChild(card);
                });

                if (needsMoreCard) {
                    const layout = {
                        left: DAY_HORIZONTAL_PADDING + visibleShiftCount * STACK_OFFSET,
                        width: cardWidth,
                        zIndex: 4 + visibleShiftCount
                    };
                    const moreCard = createMoreCard(group, day, layout);
                    moreCard.dataset.baseZ = String(4 + visibleShiftCount);
                    column.appendChild(moreCard);
                }
            }
            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value ?? '');
                return div.innerHTML;
            }

            function renderShifts() {
                document.querySelectorAll('.doctor-shift-card, .doctor-shift-cluster, .doctor-schedule-empty-day').forEach(node => node.remove());

                const visibleShifts = shifts.filter(matchesFilters);
                const visibleByDay = Array.from({ length: 7 }, () => 0);

                for (let day = 0; day < 7; day++) {
                    const column = scheduleBody.querySelector(`.doctor-schedule-day-column[data-day="${day}"]`);
                    if (!column) continue;

                    const dayShifts = visibleShifts.filter(shift => shift.day === day);
                    const groups = buildDayOverlapGroups(dayShifts);
                    visibleByDay[day] = dayShifts.length;

                    groups.forEach(group => {
                        if (group.shifts.length === 1) {
                            column.appendChild(createCard(group.shifts[0]));
                        } else {
                            appendCascadeGroup(column, group, day);
                        }
                    });
                }

                visibleByDay.forEach((count, day) => {
                    if (count > 0) return;
                    const column = scheduleBody.querySelector(`.doctor-schedule-day-column[data-day="${day}"]`);
                    if (!column) return;
                    const empty = document.createElement('div');
                    empty.className = 'doctor-schedule-empty-day';
                    empty.textContent = 'Нет смен';
                    column.appendChild(empty);
                });

                renderCurrentTimeLine();
                updateStats();
            }

            function renderCurrentTimeLine() {
                document.querySelectorAll('.doctor-schedule-now-line').forEach(node => node.remove());

                const now = new Date();
                const weekStart = startOfWeek(now);
                if (!isSameDate(weekStart, currentWeekStart)) return;

                let day = now.getDay();
                day = day === 0 ? 6 : day - 1;

                const minutes = now.getHours() * 60 + now.getMinutes();
                const min = START_HOUR * 60;
                const max = END_HOUR * 60;
                if (minutes < min || minutes > max) return;

                const column = scheduleBody.querySelector(`.doctor-schedule-day-column[data-day="${day}"]`);
                if (!column) return;

                const line = document.createElement('div');
                line.className = 'doctor-schedule-now-line';
                line.style.top = `${(minutes - min) / MINUTES_PER_PIXEL}px`;
                column.appendChild(line);
            }

            function updateStats() {
                const visible = shifts.filter(matchesFilters);
                const doctors = new Set(visible.map(item => item.doctor));
                const offices = new Set(visible.map(item => item.office));
                const minutes = visible.reduce((sum, item) => sum + timeToMinutes(item.end) - timeToMinutes(item.start), 0);

                document.getElementById('scheduleShiftsCount').textContent = visible.length;
                document.getElementById('scheduleDoctorsCount').textContent = doctors.size;
                document.getElementById('scheduleOfficesCount').textContent = offices.size;
                document.getElementById('scheduleHoursCount').textContent = (minutes / 60).toLocaleString('ru-RU', { maximumFractionDigits: 1 });
            }

            function populateFilters() {
                const specialities = [...new Set(shifts.map(item => item.speciality))].sort();
                const offices = [...new Set(shifts.map(item => item.office))].sort((a, b) => Number(a) - Number(b));
                const currentSpeciality = specialityFilter.value;
                const currentOffice = officeFilter.value;

                specialityFilter.innerHTML = '<option value="">Все специальности</option>';
                specialities.forEach(value => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = value;
                    specialityFilter.appendChild(option);
                });

                officeFilter.innerHTML = '<option value="">Все кабинеты</option>';
                offices.forEach(value => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = `Кабинет № ${value}`;
                    officeFilter.appendChild(option);
                });

                specialityFilter.value = specialities.includes(currentSpeciality) ? currentSpeciality : '';
                officeFilter.value = offices.includes(currentOffice) ? currentOffice : '';
            }

            function openCreateModal(day = 0, start = '08:00') {
                shiftIdInput.value = '';
                modalTitle.textContent = 'Новая смена';
                deleteButton.classList.add('d-none');
                modalError.classList.add('d-none');
                modalError.textContent = '';

                doctorInput.value = doctorInput.options[0]?.value ?? '';
                specialityInput.value = specialityInput.options[0]?.value ?? '';
                officeInput.value = officeInput.options[0]?.value ?? '';
                dayInput.value = String(day);
                startInput.value = start;

                const startMinutes = timeToMinutes(start);
                endInput.value = minutesToTime(Math.min(startMinutes + 360, END_HOUR * 60));
                modal?.show();
            }

            function openEditModal(id) {
                const shift = shifts.find(item => item.id === Number(id));
                if (!shift) return;

                shiftIdInput.value = String(shift.id);
                modalTitle.textContent = 'Изменить смену';
                deleteButton.classList.remove('d-none');
                modalError.classList.add('d-none');
                modalError.textContent = '';

                doctorInput.value = shift.doctor;
                specialityInput.value = shift.speciality;
                officeInput.value = shift.office;
                dayInput.value = String(shift.day);
                startInput.value = shift.start;
                endInput.value = shift.end;
                modal?.show();
            }

            function saveModalShift() {
                const id = Number(shiftIdInput.value || 0);
                const start = startInput.value;
                const end = endInput.value;
                const startMinutes = timeToMinutes(start);
                const endMinutes = timeToMinutes(end);

                if (!start || !end || endMinutes <= startMinutes) {
                    modalError.textContent = 'Время окончания должно быть позже времени начала.';
                    modalError.classList.remove('d-none');
                    return;
                }

                if (startMinutes < START_HOUR * 60 || endMinutes > END_HOUR * 60) {
                    modalError.textContent = `Смена должна находиться в пределах временной шкалы ${String(START_HOUR).padStart(2, '0')}:00–${END_HOUR}:00.`;
                    modalError.classList.remove('d-none');
                    return;
                }

                const payload = {
                    id: id || nextId++,
                    doctor: doctorInput.value,
                    speciality: specialityInput.value,
                    office: officeInput.value,
                    day: Number(dayInput.value),
                    start,
                    end
                };

                if (id) {
                    shifts = shifts.map(item => item.id === id ? payload : item);
                } else {
                    shifts.push(payload);
                }

                modal?.hide();
                populateFilters();
                renderShifts();
            }

            function deleteModalShift() {
                const id = Number(shiftIdInput.value || 0);
                if (!id) return;
                shifts = shifts.filter(item => item.id !== id);
                modal?.hide();
                populateFilters();
                renderShifts();
            }

            function startDrag(event) {
                if (event.button !== 0) return;
                if (event.target.closest('.doctor-shift-card__edit, .doctor-shift-card__resize')) return;

                const card = event.currentTarget;
                const id = Number(card.dataset.id);
                const shift = shifts.find(item => item.id === id);
                if (!shift) return;

                event.preventDefault();
                card.setPointerCapture?.(event.pointerId);
                card.classList.add('is-dragging');

                dragging = {
                    pointerId: event.pointerId,
                    card,
                    shift,
                    startY: event.clientY,
                    originalStart: timeToMinutes(shift.start),
                    duration: timeToMinutes(shift.end) - timeToMinutes(shift.start),
                    targetDay: shift.day
                };

                window.addEventListener('pointermove', onDragMove);
                window.addEventListener('pointerup', endDrag, { once: true });
                window.addEventListener('pointercancel', endDrag, { once: true });
            }

            function onDragMove(event) {
                if (!dragging || event.pointerId !== dragging.pointerId) return;

                const dyMinutes = snap((event.clientY - dragging.startY) * MINUTES_PER_PIXEL);
                const minStart = START_HOUR * 60;
                const maxStart = END_HOUR * 60 - dragging.duration;
                const newStart = clamp(dragging.originalStart + dyMinutes, minStart, maxStart);

                dragging.card.style.top = `${(newStart - minStart) / MINUTES_PER_PIXEL}px`;
                dragging.previewStart = newStart;

                document.querySelectorAll('.doctor-schedule-day-column').forEach(column => column.classList.remove('is-drop-target'));
                const element = document.elementFromPoint(event.clientX, event.clientY);
                const targetColumn = element?.closest?.('.doctor-schedule-day-column');

                if (targetColumn) {
                    dragging.targetDay = Number(targetColumn.dataset.day);
                    targetColumn.classList.add('is-drop-target');
                }
            }

            function endDrag(event) {
                if (!dragging) return;

                document.querySelectorAll('.doctor-schedule-day-column').forEach(column => column.classList.remove('is-drop-target'));

                const startMinutes = dragging.previewStart ?? dragging.originalStart;
                const shift = dragging.shift;
                shift.day = dragging.targetDay;
                shift.start = minutesToTime(startMinutes);
                shift.end = minutesToTime(startMinutes + dragging.duration);
                dragging.card.classList.remove('is-dragging');

                dragging = null;
                window.removeEventListener('pointermove', onDragMove);
                renderShifts();
            }

            function startResize(event) {
                if (event.button !== 0) return;
                event.preventDefault();
                event.stopPropagation();

                const card = event.currentTarget.closest('.doctor-shift-card');
                const id = Number(card.dataset.id);
                const shift = shifts.find(item => item.id === id);
                if (!shift) return;

                card.setPointerCapture?.(event.pointerId);
                card.classList.add('is-resizing');

                resizing = {
                    pointerId: event.pointerId,
                    card,
                    shift,
                    startY: event.clientY,
                    originalEnd: timeToMinutes(shift.end),
                    startMinutes: timeToMinutes(shift.start)
                };

                window.addEventListener('pointermove', onResizeMove);
                window.addEventListener('pointerup', endResize, { once: true });
                window.addEventListener('pointercancel', endResize, { once: true });
            }

            function onResizeMove(event) {
                if (!resizing || event.pointerId !== resizing.pointerId) return;

                const dyMinutes = snap((event.clientY - resizing.startY) * MINUTES_PER_PIXEL);
                const minEnd = resizing.startMinutes + 30;
                const maxEnd = END_HOUR * 60;
                const newEnd = clamp(resizing.originalEnd + dyMinutes, minEnd, maxEnd);
                const height = (newEnd - resizing.startMinutes) / MINUTES_PER_PIXEL;

                resizing.card.style.height = `${height}px`;
                resizing.previewEnd = newEnd;

                const timeNode = resizing.card.querySelector('.doctor-shift-card__time');
                if (timeNode) {
                    timeNode.innerHTML = `<i class="bi bi-clock"></i> ${resizing.shift.start}–${minutesToTime(newEnd)}`;
                }
            }

            function endResize() {
                if (!resizing) return;
                resizing.shift.end = minutesToTime(resizing.previewEnd ?? resizing.originalEnd);
                resizing.card.classList.remove('is-resizing');
                resizing = null;
                window.removeEventListener('pointermove', onResizeMove);
                renderShifts();
            }

            function createShiftFromEmptySpace(event) {
                if (event.target.closest('.doctor-shift-card')) return;
                const column = event.currentTarget;
                const rect = column.getBoundingClientRect();
                const y = clamp(event.clientY - rect.top, 0, DAY_HEIGHT);
                const minutes = snap(START_HOUR * 60 + y * MINUTES_PER_PIXEL);
                const safeMinutes = clamp(minutes, START_HOUR * 60, END_HOUR * 60 - 30);
                openCreateModal(Number(column.dataset.day), minutesToTime(safeMinutes));
            }

            function bindDayDoubleClicks() {
                scheduleBody.querySelectorAll('.doctor-schedule-day-column').forEach(column => {
                    column.addEventListener('dblclick', createShiftFromEmptySpace);
                });
            }

            function renderAll() {
                renderTimeline();
                renderHeaderAndDays();
                bindDayDoubleClicks();
                renderShifts();
            }

            document.getElementById('addDoctorShiftButton')?.addEventListener('click', () => openCreateModal());
            document.getElementById('saveDoctorShiftButton')?.addEventListener('click', saveModalShift);
            deleteButton?.addEventListener('click', deleteModalShift);

            document.getElementById('previousWeekButton')?.addEventListener('click', () => {
                currentWeekStart = addDays(currentWeekStart, -7);
                renderAll();
            });

            document.getElementById('nextWeekButton')?.addEventListener('click', () => {
                currentWeekStart = addDays(currentWeekStart, 7);
                renderAll();
            });

            document.getElementById('todayWeekButton')?.addEventListener('click', () => {
                currentWeekStart = startOfWeek(new Date());
                renderAll();
            });

            searchInput?.addEventListener('input', renderShifts);
            specialityFilter?.addEventListener('change', renderShifts);
            officeFilter?.addEventListener('change', renderShifts);

            populateFilters();
            renderAll();
        })();
    </script>
@endpush
