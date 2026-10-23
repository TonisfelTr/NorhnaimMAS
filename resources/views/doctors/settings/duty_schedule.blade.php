@extends('doctors.settings')

@section('settings-title', 'График дежурств')

@section('assets')
    @parent

    <style>
        .duty-page .settings-container {
            width: min(1660px, 100%);
        }

        .duty-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        .duty-page-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 9px;
            flex-wrap: wrap;
        }

        .duty-week-switcher {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px;
            border: 1px solid #dce6eb;
            border-radius: 14px;
            background: #fff;
        }

        .duty-week-button {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 10px;
            color: #5f6f7f;
            text-decoration: none;
            transition: .15s ease;
        }

        .duty-week-button:hover {
            background: #eaf9fb;
            color: var(--settings-primary-dark);
        }

        .duty-week-label {
            min-width: 205px;
            padding: 0 8px;
            color: #223147;
            font-size: 12px;
            font-weight: 900;
            text-align: center;
            white-space: nowrap;
        }

        .duty-today-button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid #d8e4e9;
            border-radius: 12px;
            background: #fff;
            color: #36505d;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .duty-today-button:hover {
            border-color: #b9d9df;
            background: #f2fbfc;
            color: #087986;
        }

        .duty-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .duty-stat {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 13px;
            padding: 14px 16px;
            border: 1px solid #e0e8ed;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .035);
        }

        .duty-stat__icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: #e8f8fa;
            color: #087d8b;
            font-size: 17px;
        }

        .duty-stat.is-current .duty-stat__icon {
            background: #eaf8ef;
            color: #15803d;
        }

        .duty-stat__value {
            color: #192638;
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
        }

        .duty-stat__label {
            margin-top: 4px;
            color: #7b8794;
            font-size: 10px;
            font-weight: 750;
        }

        .duty-toolbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            padding: 14px;
            border: 1px solid #e1e9ee;
            border-radius: 17px;
            background: #fff;
        }

        .duty-toolbar__filters {
            display: flex;
            min-width: 0;
            align-items: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .duty-filter {
            min-width: 220px;
        }

        .duty-filter--employee {
            min-width: 300px;
        }

        .duty-filter label {
            display: block;
            margin-bottom: 5px;
            color: #607080;
            font-size: 9px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .duty-toolbar .settings-control {
            min-height: 40px;
            padding: 7px 10px;
            font-size: 12px;
        }

        .duty-filter-reset {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            color: #788695;
            font-size: 11px;
            font-weight: 750;
            text-decoration: none;
        }

        .duty-filter-reset:hover {
            color: #087986;
        }

        .duty-add-button {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 15px;
            border: 1px solid var(--settings-primary);
            border-radius: 12px;
            background: var(--settings-primary);
            color: #fff;
            font-size: 12px;
            font-weight: 850;
        }

        .duty-add-button:hover {
            border-color: var(--settings-primary-dark);
            background: var(--settings-primary-dark);
        }

        .duty-add-button:disabled {
            cursor: not-allowed;
            opacity: .5;
        }

        .duty-schema-warning {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            padding: 14px 16px;
            border: 1px solid #f3d6a4;
            border-radius: 15px;
            background: #fffaf0;
            color: #8a5a13;
            font-size: 11px;
            line-height: 1.5;
        }

        .duty-schema-warning i {
            margin-top: 1px;
            font-size: 17px;
        }

        .duty-board-scroll {
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .duty-board {
            display: grid;
            min-width: 1260px;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 9px;
        }

        .duty-day {
            min-height: 515px;
            overflow: hidden;
            border: 1px solid #dde7ed;
            border-radius: 17px;
            background: #fff;
        }

        .duty-day.is-today {
            border-color: #72c8d1;
            box-shadow: 0 0 0 2px rgba(4, 134, 150, .08);
        }

        .duty-day__head {
            display: flex;
            min-height: 68px;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 11px 12px;
            border-bottom: 1px solid #e7edf1;
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .duty-day.is-today .duty-day__head {
            background: #f0fbfc;
        }

        .duty-day__date {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .duty-day__number {
            display: grid;
            width: 37px;
            height: 37px;
            flex: 0 0 37px;
            place-items: center;
            border-radius: 11px;
            background: #f0f4f7;
            color: #223147;
            font-size: 14px;
            font-weight: 900;
        }

        .duty-day.is-today .duty-day__number {
            background: #0b98a7;
            color: #fff;
        }

        .duty-day__weekday {
            display: block;
            color: #263748;
            font-size: 11px;
            font-weight: 900;
        }

        .duty-day__month {
            display: block;
            margin-top: 2px;
            color: #8793a1;
            font-size: 9px;
        }

        .duty-day__add {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border: 1px solid #dbe7eb;
            border-radius: 9px;
            background: #fff;
            color: #56808a;
            transition: .14s ease;
        }

        .duty-day__add:hover {
            border-color: #9ed5dc;
            background: #eaf9fb;
            color: #087986;
        }

        .duty-day__body {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 9px;
        }

        .duty-card {
            position: relative;
            width: 100%;
            padding: 11px;
            border: 1px solid #dbe6eb;
            border-radius: 13px;
            background: #fbfdfe;
            color: #243746;
            text-align: left;
            transition: .14s ease;
        }

        .duty-card:hover {
            border-color: #9bcfd6;
            background: #f3fbfc;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
        }

        .duty-card.is-active {
            border-color: #7bc798;
            background: #f2fbf5;
        }

        .duty-card__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 7px;
        }

        .duty-card__time {
            color: #086f7b;
            font-size: 11px;
            font-weight: 900;
        }

        .duty-card.is-active .duty-card__time {
            color: #15803d;
        }

        .duty-card__status {
            padding: 3px 6px;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .duty-card__name {
            display: block;
            margin-top: 7px;
            overflow: hidden;
            color: #1f2d40;
            font-size: 11px;
            font-weight: 850;
            line-height: 1.35;
            text-overflow: ellipsis;
        }

        .duty-card__role {
            display: block;
            margin-top: 2px;
            overflow: hidden;
            color: #7b8794;
            font-size: 9px;
            line-height: 1.35;
            text-overflow: ellipsis;
        }

        .duty-card__department {
            display: inline-flex;
            max-width: 100%;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
            padding: 4px 7px;
            border-radius: 999px;
            background: #edf5f7;
            color: #516a72;
            font-size: 8px;
            font-weight: 800;
        }

        .duty-card__department span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .duty-card__comment {
            display: -webkit-box;
            margin-top: 8px;
            overflow: hidden;
            color: #687786;
            font-size: 8px;
            line-height: 1.45;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .duty-empty-day {
            display: grid;
            min-height: 130px;
            place-items: center;
            padding: 18px 10px;
            border: 1px dashed #d8e1e7;
            border-radius: 12px;
            color: #98a3af;
            font-size: 9px;
            text-align: center;
        }

        .duty-empty-day i {
            display: block;
            margin-bottom: 7px;
            font-size: 19px;
            opacity: .75;
        }

        .duty-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 26px 80px rgba(15, 23, 42, .22);
        }

        .duty-modal .modal-header,
        .duty-modal .modal-footer {
            border-color: #e8eef2;
        }

        .duty-modal .modal-body {
            padding: 20px;
            background: #f7f9fb;
        }

        .duty-form-section {
            padding: 16px;
            border: 1px solid #e2e9ee;
            border-radius: 16px;
            background: #fff;
        }

        .duty-form-section + .duty-form-section {
            margin-top: 12px;
        }

        .duty-form-section__title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 13px;
            color: #253448;
            font-size: 12px;
            font-weight: 850;
        }

        .duty-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .duty-form-grid .is-full {
            grid-column: 1 / -1;
        }

        .duty-modal .settings-label {
            font-size: 10px;
        }

        .duty-modal .settings-control {
            min-height: 42px;
            font-size: 12px;
        }

        .duty-modal textarea.settings-control {
            min-height: 82px;
        }

        .duty-modal-error {
            display: none;
            margin-bottom: 12px;
            padding: 10px 12px;
            border: 1px solid #fecaca;
            border-radius: 11px;
            background: #fff1f2;
            color: #b42335;
            font-size: 10px;
            line-height: 1.45;
        }

        .duty-modal-error.is-visible {
            display: block;
        }

        .duty-delete-button {
            margin-right: auto;
            border-color: #fecdd3;
            background: #fff;
            color: #be123c;
        }

        .duty-delete-button:hover {
            border-color: #fda4af;
            background: #fff1f2;
            color: #9f1239;
        }

        .duty-save-button {
            border-color: var(--settings-primary);
            background: var(--settings-primary);
            color: #fff;
        }

        .duty-save-button:hover {
            border-color: var(--settings-primary-dark);
            background: var(--settings-primary-dark);
            color: #fff;
        }

        @media (max-width: 1100px) {
            .duty-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .duty-page-header,
            .duty-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .duty-page-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767px) {
            .duty-stats,
            .duty-form-grid {
                grid-template-columns: 1fr;
            }

            .duty-filter,
            .duty-filter--employee {
                width: 100%;
                min-width: 0;
            }

            .duty-toolbar__filters {
                align-items: stretch;
                flex-direction: column;
            }

            .duty-week-label {
                min-width: 165px;
                font-size: 10px;
            }
        }


        /* Обычная модалка + строковые выпадающие списки */
        .duty-modal .modal-dialog {
            width: calc(100% - 32px);
            max-width: 1080px;
            margin: 1.75rem auto;
        }

        .duty-modal .modal-content {
            min-height: 0;
            max-height: calc(100vh - 56px);
            overflow: visible;
            border: 0;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 26px 80px rgba(15, 23, 42, .22);
        }

        .duty-modal .modal-header,
        .duty-modal .modal-footer {
            flex: 0 0 auto;
            padding: 16px 20px;
            border-color: #e2e8ee;
            background: #fff;
        }

        .duty-modal .modal-header {
            border-radius: 22px 22px 0 0;
        }

        .duty-modal .modal-footer {
            border-radius: 0 0 22px 22px;
        }

        .duty-modal .modal-body {
            overflow-y: auto;
            padding: 20px;
            background: #f7f9fb;
        }

        .duty-modal__inner {
            width: 100%;
            margin: 0;
        }

        .duty-modal__layout {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(330px, .92fr);
            gap: 16px;
            align-items: start;
        }

        .duty-modal__hint {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid #d8ecef;
            border-radius: 12px;
            background: #f2fbfc;
            color: #557178;
            font-size: 10px;
            line-height: 1.5;
        }

        .duty-modal__hint i {
            color: var(--settings-primary);
        }

        .duty-picker {
            position: relative;
        }

        .duty-picker__toggle {
            display: flex;
            width: 100%;
            min-height: 44px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 12px;
            border: 1px solid #d8e0e5;
            border-radius: 12px;
            background: #fff;
            color: #263746;
            text-align: left;
            transition: .16s ease;
        }

        .duty-picker__toggle:hover,
        .duty-picker.is-open .duty-picker__toggle {
            border-color: #88cdd5;
            box-shadow: 0 0 0 3px rgba(4, 134, 150, .08);
        }

        .duty-picker__toggle-copy {
            min-width: 0;
        }

        .duty-picker__toggle-copy strong,
        .duty-picker__toggle-copy small {
            display: block;
        }

        .duty-picker__toggle-copy strong {
            overflow: hidden;
            font-size: 12px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .duty-picker__toggle-copy small {
            margin-top: 2px;
            overflow: hidden;
            color: #7b8794;
            font-size: 9px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .duty-picker__toggle > i {
            flex: 0 0 auto;
            color: #82909d;
            transition: transform .16s ease;
        }

        .duty-picker.is-open .duty-picker__toggle > i {
            transform: rotate(180deg);
        }

        .duty-picker__menu {
            position: absolute;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            z-index: 1090;
            display: none;
            max-height: 390px;
            overflow: auto;
            padding: 7px;
            border: 1px solid #dce5eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 20px 54px rgba(15, 23, 42, .18);
        }

        .duty-picker.is-open .duty-picker__menu {
            display: block;
        }

        .duty-picker__search-wrap {
            position: sticky;
            top: -7px;
            z-index: 2;
            margin: -7px -7px 6px;
            padding: 7px;
            border-bottom: 1px solid #edf1f4;
            background: #fff;
        }

        .duty-picker__search {
            width: 100%;
            min-height: 38px;
            padding: 8px 10px;
            border: 1px solid #dce4e9;
            border-radius: 10px;
            outline: 0;
            font-size: 11px;
        }

        .duty-picker__search:focus {
            border-color: var(--settings-primary);
            box-shadow: 0 0 0 3px rgba(4, 134, 150, .08);
        }

        .duty-picker__row {
            display: grid;
            width: 100%;
            grid-template-columns: 36px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 9px 10px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #263746;
            text-align: left;
            transition: .14s ease;
        }

        .duty-picker__row + .duty-picker__row {
            margin-top: 3px;
        }

        .duty-picker__row:hover,
        .duty-picker__row.is-selected {
            background: #eefafb;
            color: #075f6b;
        }

        .duty-picker__row[hidden] {
            display: none !important;
        }

        .duty-picker__avatar {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 11px;
            background: #e8f8fa;
            color: #087b89;
            font-size: 11px;
            font-weight: 900;
        }

        .duty-picker__avatar.is-nurse {
            background: #f0f7ff;
            color: #3b6ea8;
        }

        .duty-picker__row-copy {
            min-width: 0;
        }

        .duty-picker__row-copy strong,
        .duty-picker__row-copy small {
            display: block;
        }

        .duty-picker__row-copy strong {
            overflow: hidden;
            font-size: 11px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .duty-picker__row-copy small {
            margin-top: 2px;
            overflow: hidden;
            color: #7c8997;
            font-size: 9px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .duty-picker__badge {
            padding: 3px 7px;
            border-radius: 999px;
            background: #edf4f6;
            color: #60717c;
            font-size: 8px;
            font-weight: 850;
            white-space: nowrap;
        }

        .duty-picker__empty {
            display: none;
            padding: 18px 10px;
            color: #8995a3;
            font-size: 10px;
            text-align: center;
        }

        .duty-picker__empty.is-visible {
            display: block;
        }

        .duty-filter .duty-picker__toggle {
            min-height: 40px;
            padding: 7px 10px;
        }

        .duty-filter .duty-picker__menu {
            min-width: 320px;
        }

        @media (max-width: 900px) {
            .duty-modal__layout {
                grid-template-columns: 1fr;
            }

            .duty-modal .modal-header,
            .duty-modal .modal-footer,
            .duty-modal .modal-body {
                padding-right: 16px;
                padding-left: 16px;
            }
        }

    </style>
@endsection

@section('sub-main')
    <main class="settings-page duty-page">
        <div class="settings-container">
            <div class="duty-page-header">
                <div>
                    <span class="settings-type-badge">
                        <i class="bi bi-moon-stars"></i>
                        Стационар
                    </span>
                    <h1 class="settings-page-title mt-2">График дежурств</h1>
                    <p class="settings-page-description">
                        Дежурства врачей и медицинских сестёр по отделениям стационара.
                        Смена может переходить через полночь — время сохраняется единым интервалом.
                    </p>
                </div>

                <div class="duty-page-actions">
                    <div class="duty-week-switcher" aria-label="Переключение недели">
                        <a class="duty-week-button" href="{{ $previousWeekUrl }}" title="Предыдущая неделя">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <div class="duty-week-label">{{ $weekLabel }}</div>
                        <a class="duty-week-button" href="{{ $nextWeekUrl }}" title="Следующая неделя">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>

                    <a class="duty-today-button" href="{{ $currentWeekUrl }}">
                        <i class="bi bi-calendar-check"></i>
                        Сегодня
                    </a>
                </div>
            </div>

            <div class="duty-stats">
                <div class="duty-stat">
                    <span class="duty-stat__icon"><i class="bi bi-moon-stars"></i></span>
                    <div>
                        <div class="duty-stat__value">{{ $stats['duties'] }}</div>
                        <div class="duty-stat__label">дежурств на неделе</div>
                    </div>
                </div>
                <div class="duty-stat">
                    <span class="duty-stat__icon"><i class="bi bi-people"></i></span>
                    <div>
                        <div class="duty-stat__value">{{ $stats['employees'] }}</div>
                        <div class="duty-stat__label">сотрудников в графике</div>
                    </div>
                </div>
                <div class="duty-stat">
                    <span class="duty-stat__icon"><i class="bi bi-building"></i></span>
                    <div>
                        <div class="duty-stat__value">{{ $stats['departments'] }}</div>
                        <div class="duty-stat__label">отделений задействовано</div>
                    </div>
                </div>
                <div class="duty-stat is-current">
                    <span class="duty-stat__icon"><i class="bi bi-person-check"></i></span>
                    <div>
                        <div class="duty-stat__value">{{ $stats['active_now'] }}</div>
                        <div class="duty-stat__label">дежурят прямо сейчас</div>
                    </div>
                </div>
            </div>

            @if(!$schemaReady)
                <div class="duty-schema-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        Таблица дежурств ещё не создана. Выполните миграции,
                        после чего добавление и редактирование станет доступно.
                    </div>
                </div>
            @endif

            <form class="duty-toolbar" method="GET" action="{{ route('doctors.settings.duty-schedule') }}">
                <input type="hidden" name="week" value="{{ $weekStart }}">

                <div class="duty-toolbar__filters">
                    <div class="duty-filter">
                        <label>Отделение</label>
                        <div class="duty-picker js-duty-picker" data-submit-on-select="1">
                            <input type="hidden" name="department_id" value="{{ $selectedDepartmentId ?: '' }}" data-picker-value>
                            <button class="duty-picker__toggle" type="button" data-picker-toggle>
                                <span class="duty-picker__toggle-copy" data-picker-label>
                                    @php($selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId))
                                    <strong>{{ $selectedDepartment ? (($selectedDepartment->number ? '№' . $selectedDepartment->number . ' · ' : '') . $selectedDepartment->name) : 'Все отделения' }}</strong>
                                    <small>{{ $selectedDepartment ? 'Отделение стационара' : 'Без фильтра по отделению' }}</small>
                                </span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="duty-picker__menu">
                                <button class="duty-picker__row" type="button" data-picker-option data-value="" data-title="Все отделения" data-subtitle="Без фильтра по отделению">
                                    <span class="duty-picker__avatar"><i class="bi bi-building"></i></span>
                                    <span class="duty-picker__row-copy"><strong>Все отделения</strong><small>Показать весь стационар</small></span>
                                    <span class="duty-picker__badge">Все</span>
                                </button>
                                @foreach($departments as $department)
                                    <button class="duty-picker__row" type="button"
                                            data-picker-option
                                            data-value="{{ $department->id }}"
                                            data-title="{{ $department->number ? '№' . $department->number . ' · ' : '' }}{{ $department->name }}"
                                            data-subtitle="Отделение стационара">
                                        <span class="duty-picker__avatar"><i class="bi bi-building"></i></span>
                                        <span class="duty-picker__row-copy">
                                            <strong>{{ $department->number ? '№' . $department->number . ' · ' : '' }}{{ $department->name }}</strong>
                                            <small>Отделение стационара</small>
                                        </span>
                                        <span class="duty-picker__badge">Отделение</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="duty-filter duty-filter--employee">
                        <label>Сотрудник</label>
                        <div class="duty-picker js-duty-picker" data-submit-on-select="1" id="dutyEmployeeFilterPicker">
                            <input type="hidden" name="employee" value="{{ $selectedEmployeeKey }}" data-picker-value>
                            <button class="duty-picker__toggle" type="button" data-picker-toggle>
                                <span class="duty-picker__toggle-copy" data-picker-label>
                                    @php($selectedEmployee = $filterEmployees->firstWhere('key', $selectedEmployeeKey))
                                    <strong>{{ $selectedEmployee['name'] ?? 'Все сотрудники' }}</strong>
                                    <small>{{ $selectedEmployee['role'] ?? 'Без фильтра по сотруднику' }}</small>
                                </span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="duty-picker__menu">
                                <div class="duty-picker__search-wrap">
                                    <input class="duty-picker__search" type="search" placeholder="Найти сотрудника..." data-picker-search>
                                </div>
                                <button class="duty-picker__row" type="button" data-picker-option data-value="" data-title="Все сотрудники" data-subtitle="Без фильтра по сотруднику">
                                    <span class="duty-picker__avatar"><i class="bi bi-people"></i></span>
                                    <span class="duty-picker__row-copy"><strong>Все сотрудники</strong><small>Врачи и медицинские сёстры</small></span>
                                    <span class="duty-picker__badge">Все</span>
                                </button>
                                @foreach($filterEmployees as $employee)
                                    <button class="duty-picker__row js-filter-employee-row"
                                            type="button"
                                            data-picker-option
                                            data-employee-key="{{ $employee['key'] }}"
                                            data-value="{{ $employee['key'] }}"
                                            data-title="{{ $employee['name'] }}"
                                            data-subtitle="{{ $employee['role'] }}"
                                            data-search-text="{{ mb_strtolower($employee['name'] . ' ' . $employee['role']) }}">
                                        <span class="duty-picker__avatar {{ $employee['type'] === 'nurse' ? 'is-nurse' : '' }}">
                                            {{ mb_strtoupper(mb_substr($employee['name'], 0, 1)) }}
                                        </span>
                                        <span class="duty-picker__row-copy">
                                            <strong>{{ $employee['name'] }}</strong>
                                            <small>{{ $employee['role'] }}</small>
                                        </span>
                                        <span class="duty-picker__badge">{{ $employee['type'] === 'nurse' ? 'Медсестра' : 'Врач' }}</span>
                                    </button>
                                @endforeach
                                <div class="duty-picker__empty" data-picker-empty>Подходящих сотрудников нет</div>
                            </div>
                        </div>
                    </div>

                    @if($selectedDepartmentId || $selectedEmployeeKey)
                        <a class="duty-filter-reset"
                           href="{{ route('doctors.settings.duty-schedule', ['week' => $weekStart]) }}">
                            <i class="bi bi-x-circle"></i>
                            Сбросить
                        </a>
                    @endif
                </div>

                <button class="duty-add-button"
                        id="addDutyButton"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#dutyModal"
                        @disabled(!$schemaReady || $departments->isEmpty() || $employees->isEmpty())>
                    <i class="bi bi-plus-lg"></i>
                    Добавить дежурство
                </button>
            </form>

            <div class="duty-board-scroll">
                <div class="duty-board">
                    @foreach($days as $day)
                        <section class="duty-day {{ $day['is_today'] ? 'is-today' : '' }}">
                            <header class="duty-day__head">
                                <div class="duty-day__date">
                                    <span class="duty-day__number">{{ $day['day'] }}</span>
                                    <span>
                                        <span class="duty-day__weekday">{{ $day['weekday'] }}</span>
                                        <span class="duty-day__month">{{ $day['month'] }}</span>
                                    </span>
                                </div>

                                <button class="duty-day__add js-duty-add-day"
                                        type="button"
                                        data-date="{{ $day['date'] }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#dutyModal"
                                        title="Добавить дежурство"
                                        @disabled(!$schemaReady || $departments->isEmpty() || $employees->isEmpty())>
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </header>

                            <div class="duty-day__body">
                                @forelse($day['duties'] as $duty)
                                    <button class="duty-card js-duty-edit {{ $duty['is_active'] ? 'is-active' : '' }}"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#dutyModal"
                                            data-id="{{ $duty['id'] }}"
                                            data-department-id="{{ $duty['department_id'] }}"
                                            data-employee-key="{{ $duty['employee_key'] }}"
                                            data-starts-at="{{ $duty['starts_at'] }}"
                                            data-ends-at="{{ $duty['ends_at'] }}"
                                            data-comment="{{ $duty['comment'] }}">
                                        <span class="duty-card__top">
                                            <span class="duty-card__time">{{ $duty['day_time_label'] }}</span>
                                            @if($duty['is_active'])
                                                <span class="duty-card__status">сейчас</span>
                                            @endif
                                        </span>
                                        <span class="duty-card__name">{{ $duty['employee_name'] }}</span>
                                        <span class="duty-card__role">{{ $duty['employee_role'] }}</span>
                                        <span class="duty-card__department">
                                            <i class="bi bi-building"></i>
                                            <span>{{ $duty['department_name'] }}</span>
                                        </span>
                                        @if($duty['comment'])
                                            <span class="duty-card__comment">{{ $duty['comment'] }}</span>
                                        @endif
                                    </button>
                                @empty
                                    <div class="duty-empty-day">
                                        <span>
                                            <i class="bi bi-calendar2-plus"></i>
                                            Дежурства не назначены
                                        </span>
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade duty-modal"
         id="dutyModal"
         tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="dutyModalTitle">Добавление дежурства</h5>
                        <div class="text-muted small mt-1">
                            Выберите отделение, сотрудника и интервал дежурства. Сотрудники в утверждённом отпуске в выбранные даты в списке не отображаются.
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="duty-modal__inner">
                        <div class="duty-modal-error" id="dutyModalError"></div>

                        <div class="duty-modal__layout">
                            <div class="duty-form-section">
                                <div class="duty-form-section__title">
                                    <i class="bi bi-person-badge"></i>
                                    Назначение
                                </div>

                                <div class="duty-form-grid">
                                    <div class="is-full">
                                        <label class="settings-label">Отделение</label>
                                        <div class="duty-picker js-duty-picker" id="dutyDepartmentPicker">
                                            <input type="hidden" id="dutyDepartment" value="" data-picker-value>
                                            <button class="duty-picker__toggle" type="button" data-picker-toggle>
                                                <span class="duty-picker__toggle-copy" data-picker-label>
                                                    <strong>Выберите отделение</strong>
                                                    <small>Отделение стационара</small>
                                                </span>
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <div class="duty-picker__menu">
                                                @foreach($departments as $department)
                                                    <button class="duty-picker__row" type="button"
                                                            data-picker-option
                                                            data-value="{{ $department->id }}"
                                                            data-title="{{ $department->number ? '№' . $department->number . ' · ' : '' }}{{ $department->name }}"
                                                            data-subtitle="Отделение стационара">
                                                        <span class="duty-picker__avatar"><i class="bi bi-building"></i></span>
                                                        <span class="duty-picker__row-copy">
                                                            <strong>{{ $department->number ? '№' . $department->number . ' · ' : '' }}{{ $department->name }}</strong>
                                                            <small>Отделение стационара</small>
                                                        </span>
                                                        <span class="duty-picker__badge">Отделение</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="is-full">
                                        <label class="settings-label">Сотрудник</label>
                                        <div class="duty-picker js-duty-picker" id="dutyEmployeePicker">
                                            <input type="hidden" id="dutyEmployee" value="" data-picker-value>
                                            <button class="duty-picker__toggle" type="button" data-picker-toggle>
                                                <span class="duty-picker__toggle-copy" data-picker-label>
                                                    <strong>Выберите сотрудника</strong>
                                                    <small>Врач или медицинская сестра</small>
                                                </span>
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <div class="duty-picker__menu">
                                                <div class="duty-picker__search-wrap">
                                                    <input class="duty-picker__search" type="search" placeholder="ФИО или должность..." data-picker-search>
                                                </div>
                                                @foreach($employees as $employee)
                                                    <button class="duty-picker__row js-duty-employee-row"
                                                            type="button"
                                                            data-picker-option
                                                            data-employee-key="{{ $employee['key'] }}"
                                                            data-employee-type="{{ $employee['type'] }}"
                                                            data-employee-id="{{ $employee['id'] }}"
                                                            data-value="{{ $employee['key'] }}"
                                                            data-title="{{ $employee['name'] }}"
                                                            data-subtitle="{{ $employee['role'] }}"
                                                            data-search-text="{{ mb_strtolower($employee['name'] . ' ' . $employee['role']) }}">
                                                        <span class="duty-picker__avatar {{ $employee['type'] === 'nurse' ? 'is-nurse' : '' }}">
                                                            {{ mb_strtoupper(mb_substr($employee['name'], 0, 1)) }}
                                                        </span>
                                                        <span class="duty-picker__row-copy">
                                                            <strong>{{ $employee['name'] }}</strong>
                                                            <small>{{ $employee['role'] }}</small>
                                                        </span>
                                                        <span class="duty-picker__badge">{{ $employee['type'] === 'nurse' ? 'Медсестра' : 'Врач' }}</span>
                                                    </button>
                                                @endforeach
                                                <div class="duty-picker__empty" id="dutyEmployeeEmpty">
                                                    На выбранный интервал доступных сотрудников нет
                                                </div>
                                            </div>
                                        </div>
                                        <div class="duty-modal__hint">
                                            <i class="bi bi-person-x"></i>
                                            <span>Отпуска, отгулы и другие внесённые отсутствия учитываются автоматически. Если отсутствие пересекается с выбранным интервалом, сотрудник вообще не показывается в списке.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="duty-form-section">
                                <div class="duty-form-section__title">
                                    <i class="bi bi-clock-history"></i>
                                    Время дежурства
                                </div>

                                <div class="duty-form-grid">
                                    <div>
                                        <label class="settings-label" for="dutyStartsAt">Начало</label>
                                        <input class="settings-control" id="dutyStartsAt" type="datetime-local">
                                    </div>
                                    <div>
                                        <label class="settings-label" for="dutyEndsAt">Окончание</label>
                                        <input class="settings-control" id="dutyEndsAt" type="datetime-local">
                                    </div>
                                    <div class="is-full">
                                        <label class="settings-label" for="dutyComment">Комментарий</label>
                                        <textarea class="settings-control"
                                                  id="dutyComment"
                                                  maxlength="2000"
                                                  placeholder="Например: ответственное дежурство, замещение, особенности смены"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="settings-button duty-delete-button d-none"
                            id="deleteDutyButton"
                            type="button">
                        <i class="bi bi-trash"></i>
                        Удалить
                    </button>
                    <button class="settings-button settings-button--secondary"
                            type="button"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>
                    <button class="settings-button duty-save-button"
                            id="saveDutyButton"
                            type="button">
                        <i class="bi bi-check2"></i>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const initDutySchedule = () => {
                const modalElement = document.getElementById('dutyModal');

                if (!modalElement || modalElement.dataset.dutyInitialized === '1') {
                    return;
                }

                modalElement.dataset.dutyInitialized = '1';

                const modalTitle = document.getElementById('dutyModalTitle');
                const modalError = document.getElementById('dutyModalError');
                const departmentInput = document.getElementById('dutyDepartment');
                const employeeInput = document.getElementById('dutyEmployee');
                const startsAtInput = document.getElementById('dutyStartsAt');
                const endsAtInput = document.getElementById('dutyEndsAt');
                const commentInput = document.getElementById('dutyComment');
                const saveButton = document.getElementById('saveDutyButton');
                const deleteButton = document.getElementById('deleteDutyButton');
                const addButton = document.getElementById('addDutyButton');
                const employeeEmpty = document.getElementById('dutyEmployeeEmpty');
                const employeeRows = Array.from(document.querySelectorAll('.js-duty-employee-row'));
                const filterEmployeeRows = Array.from(document.querySelectorAll('.js-filter-employee-row'));

                const storeUrl = @json($dutyStoreUrl);
                const updateUrlTemplate = @json($dutyUpdateUrlTemplate);
                const deleteUrlTemplate = @json($dutyDeleteUrlTemplate);
                const csrfToken = @json($csrfToken);
                const selectedDepartmentId = @json($selectedDepartmentId);
                const weekStart = @json($weekStart);
                const absenceRanges = @json($employeeAbsenceRanges ?? []);

                let editingDutyId = null;

                const pad = value => String(value).padStart(2, '0');

                const formatLocalDateTime = date => [
                    date.getFullYear(), '-', pad(date.getMonth() + 1), '-', pad(date.getDate()),
                    'T', pad(date.getHours()), ':', pad(date.getMinutes()),
                ].join('');

                const defaultIntervalForDate = dateString => {
                    const start = new Date(`${dateString}T08:00:00`);
                    const end = new Date(start.getTime() + 24 * 60 * 60 * 1000);
                    return { start: formatLocalDateTime(start), end: formatLocalDateTime(end) };
                };

                const closeAllPickers = except => {
                    document.querySelectorAll('.js-duty-picker.is-open').forEach(picker => {
                        if (picker !== except) {
                            picker.classList.remove('is-open');
                        }
                    });
                };

                const pickerSetValue = (picker, value, title = '', subtitle = '') => {
                    if (!picker) return;
                    const input = picker.querySelector('[data-picker-value]');
                    const label = picker.querySelector('[data-picker-label]');
                    if (input) input.value = value ?? '';
                    if (label) {
                        label.innerHTML = `<strong>${title || 'Не выбрано'}</strong><small>${subtitle || ''}</small>`;
                    }
                    picker.querySelectorAll('[data-picker-option]').forEach(row => {
                        row.classList.toggle('is-selected', String(row.dataset.value ?? '') === String(value ?? ''));
                    });
                };

                document.querySelectorAll('.js-duty-picker').forEach(picker => {
                    const toggle = picker.querySelector('[data-picker-toggle]');
                    const input = picker.querySelector('[data-picker-value]');
                    const search = picker.querySelector('[data-picker-search]');
                    const options = Array.from(picker.querySelectorAll('[data-picker-option]'));
                    const empty = picker.querySelector('[data-picker-empty]');

                    const applySearch = () => {
                        const needle = (search?.value || '').trim().toLocaleLowerCase('ru');
                        let visible = 0;
                        options.forEach(option => {
                            const absenceHidden = option.dataset.absenceHidden === '1';
                            const text = (option.dataset.searchText || option.textContent || '').toLocaleLowerCase('ru');
                            const matched = !needle || text.includes(needle);
                            option.hidden = absenceHidden || !matched;
                            if (!option.hidden) visible++;
                        });
                        if (empty) empty.classList.toggle('is-visible', visible === 0);
                    };

                    picker._applySearch = applySearch;

                    toggle?.addEventListener('click', event => {
                        event.stopPropagation();
                        const willOpen = !picker.classList.contains('is-open');
                        closeAllPickers(picker);
                        picker.classList.toggle('is-open', willOpen);
                        if (willOpen) {
                            applySearch();
                            if (search) setTimeout(() => search.focus(), 0);
                        }
                    });

                    options.forEach(option => {
                        option.addEventListener('click', () => {
                            pickerSetValue(
                                picker,
                                option.dataset.value || '',
                                option.dataset.title || option.textContent.trim(),
                                option.dataset.subtitle || ''
                            );
                            picker.classList.remove('is-open');
                            if (search) search.value = '';
                            applySearch();
                            if (picker.dataset.submitOnSelect === '1') {
                                picker.closest('form')?.submit();
                            }
                        });
                    });

                    search?.addEventListener('input', applySearch);

                    if (input?.value) {
                        const current = options.find(option => String(option.dataset.value || '') === String(input.value));
                        if (current) {
                            pickerSetValue(picker, input.value, current.dataset.title || '', current.dataset.subtitle || '');
                        }
                    }
                });

                document.addEventListener('click', event => {
                    if (!event.target.closest('.js-duty-picker')) {
                        closeAllPickers();
                    }
                });

                const clearError = () => {
                    if (!modalError) return;
                    modalError.textContent = '';
                    modalError.classList.remove('is-visible');
                };

                const showError = message => {
                    if (!modalError) {
                        window.alert(message);
                        return;
                    }
                    modalError.textContent = message;
                    modalError.classList.add('is-visible');
                    modalError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                };

                const absenceOverlaps = (employeeKey, startValue, endValue) => {
                    if (!employeeKey || !startValue || !endValue) return false;
                    const start = new Date(startValue);
                    const end = new Date(endValue);
                    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end <= start) return false;
                    return (absenceRanges[employeeKey] || []).some(range => {
                        const vacationStart = new Date(`${range.from}T00:00:00`);
                        const vacationEnd = new Date(`${range.to}T00:00:00`);
                        vacationEnd.setDate(vacationEnd.getDate() + 1);
                        return vacationStart < end && vacationEnd > start;
                    });
                };

                const updateEmployeeAvailability = () => {
                    const startValue = startsAtInput.value;
                    const endValue = endsAtInput.value;
                    let visible = 0;

                    employeeRows.forEach(row => {
                        const unavailable = absenceOverlaps(row.dataset.employeeKey, startValue, endValue);
                        row.dataset.absenceHidden = unavailable ? '1' : '0';
                        row.hidden = unavailable;
                        if (!unavailable) visible++;
                    });

                    const picker = document.getElementById('dutyEmployeePicker');
                    picker?._applySearch?.();
                    employeeEmpty?.classList.toggle('is-visible', visible === 0);

                    if (employeeInput.value && absenceOverlaps(employeeInput.value, startValue, endValue)) {
                        pickerSetValue(picker, '', 'Выберите сотрудника', 'Врач или медицинская сестра');
                    }
                };

                const updateFilterAbsenceRows = () => {
                    const weekFrom = `${weekStart}T00:00`;
                    const endDate = new Date(`${weekStart}T00:00:00`);
                    endDate.setDate(endDate.getDate() + 7);
                    const weekTo = formatLocalDateTime(endDate);
                    filterEmployeeRows.forEach(row => {
                        const unavailable = absenceOverlaps(row.dataset.employeeKey, weekFrom, weekTo);
                        row.dataset.absenceHidden = unavailable ? '1' : '0';
                        row.hidden = unavailable;
                    });
                    document.getElementById('dutyEmployeeFilterPicker')?._applySearch?.();
                };

                const resetModal = dateString => {
                    editingDutyId = null;
                    modalTitle.textContent = 'Добавление дежурства';
                    deleteButton.classList.add('d-none');
                    clearError();

                    const departmentPicker = document.getElementById('dutyDepartmentPicker');
                    const employeePicker = document.getElementById('dutyEmployeePicker');
                    const selectedDepartmentRow = departmentPicker?.querySelector(`[data-picker-option][data-value="${selectedDepartmentId || ''}"]`);
                    pickerSetValue(
                        departmentPicker,
                        selectedDepartmentId || '',
                        selectedDepartmentRow?.dataset.title || 'Выберите отделение',
                        selectedDepartmentRow?.dataset.subtitle || 'Отделение стационара'
                    );
                    pickerSetValue(employeePicker, '', 'Выберите сотрудника', 'Врач или медицинская сестра');
                    commentInput.value = '';

                    const interval = defaultIntervalForDate(dateString || weekStart);
                    startsAtInput.value = interval.start;
                    endsAtInput.value = interval.end;
                    updateEmployeeAvailability();
                };

                modalElement.addEventListener('show.bs.modal', event => {
                    const trigger = event.relatedTarget;
                    modalTitle.textContent = trigger?.classList?.contains('js-duty-edit')
                        ? 'Редактирование дежурства'
                        : 'Добавление дежурства';
                });

                addButton?.addEventListener('click', () => resetModal(weekStart));
                document.querySelectorAll('.js-duty-add-day').forEach(button => {
                    button.addEventListener('click', () => resetModal(button.dataset.date || weekStart));
                });

                document.querySelectorAll('.js-duty-edit').forEach(button => {
                    button.addEventListener('click', () => {
                        editingDutyId = Number(button.dataset.id);
                        modalTitle.textContent = 'Редактирование дежурства';
                        clearError();

                        startsAtInput.value = button.dataset.startsAt || '';
                        endsAtInput.value = button.dataset.endsAt || '';
                        commentInput.value = button.dataset.comment || '';
                        updateEmployeeAvailability();

                        const departmentPicker = document.getElementById('dutyDepartmentPicker');
                        const employeePicker = document.getElementById('dutyEmployeePicker');
                        const departmentRow = departmentPicker?.querySelector(`[data-picker-option][data-value="${button.dataset.departmentId || ''}"]`);
                        const employeeRow = employeePicker?.querySelector(`[data-picker-option][data-value="${button.dataset.employeeKey || ''}"]`);

                        pickerSetValue(
                            departmentPicker,
                            button.dataset.departmentId || '',
                            departmentRow?.dataset.title || 'Выберите отделение',
                            departmentRow?.dataset.subtitle || 'Отделение стационара'
                        );

                        if (employeeRow && !employeeRow.hidden) {
                            pickerSetValue(
                                employeePicker,
                                button.dataset.employeeKey || '',
                                employeeRow.dataset.title || '',
                                employeeRow.dataset.subtitle || ''
                            );
                        } else {
                            pickerSetValue(employeePicker, '', 'Выберите сотрудника', 'Сотрудник недоступен на этот интервал');
                        }

                        deleteButton.classList.remove('d-none');
                    });
                });

                startsAtInput?.addEventListener('change', updateEmployeeAvailability);
                endsAtInput?.addEventListener('change', updateEmployeeAvailability);

                const request = async (url, payload) => {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const validationMessage = data?.errors ? Object.values(data.errors).flat()[0] : null;
                        throw new Error(validationMessage || data?.message || 'Не удалось сохранить дежурство.');
                    }
                    return data;
                };

                saveButton?.addEventListener('click', async () => {
                    clearError();
                    const selectedEmployeeRow = document.querySelector(`.js-duty-employee-row[data-value="${CSS.escape(employeeInput.value || '')}"]`);

                    if (!departmentInput.value) {
                        showError('Выберите отделение.');
                        return;
                    }
                    if (!selectedEmployeeRow || selectedEmployeeRow.hidden) {
                        showError('Выберите доступного сотрудника. Сотрудники в отпуске, отгуле или другом отсутствии в выбранные даты не показываются.');
                        return;
                    }
                    if (!startsAtInput.value || !endsAtInput.value) {
                        showError('Укажите начало и окончание дежурства.');
                        return;
                    }

                    const payload = {
                        department_id: Number(departmentInput.value),
                        employee_type: selectedEmployeeRow.dataset.employeeType,
                        employee_id: Number(selectedEmployeeRow.dataset.employeeId),
                        starts_at: startsAtInput.value,
                        ends_at: endsAtInput.value,
                        comment: commentInput.value.trim(),
                    };

                    const url = editingDutyId
                        ? updateUrlTemplate.replace('__DUTY__', editingDutyId)
                        : storeUrl;
                    const originalText = saveButton.innerHTML;
                    saveButton.disabled = true;
                    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Сохраняем…';

                    try {
                        await request(url, payload);
                        window.location.reload();
                    } catch (error) {
                        showError(error.message);
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalText;
                    }
                });

                deleteButton?.addEventListener('click', async () => {
                    if (!editingDutyId) return;
                    if (!window.confirm('Удалить это дежурство из графика?')) return;
                    clearError();
                    deleteButton.disabled = true;
                    try {
                        await request(deleteUrlTemplate.replace('__DUTY__', editingDutyId), {});
                        window.location.reload();
                    } catch (error) {
                        showError(error.message);
                        deleteButton.disabled = false;
                    }
                });

                updateFilterAbsenceRows();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDutySchedule, { once: true });
            } else {
                initDutySchedule();
            }
        })();
    </script>
@endsection
