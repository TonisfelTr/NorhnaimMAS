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
            gap: 10px;
        }

        .clinic-day {
            display: grid;
            grid-template-columns: minmax(180px, .9fr) minmax(360px, 1.45fr) minmax(140px, .55fr);
            align-items: center;
            gap: 16px;
            min-height: 78px;
            padding: 14px 16px;
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
            gap: 12px;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .clinic-day__hours-label {
            flex: 0 0 auto;
            color: #8792a2;
            font-size: 11px;
            font-weight: 750;
        }

        .clinic-time {
            width: 118px;
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
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
        }

        .clinic-day__switch-wrap {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 9px;
        }

        .clinic-day__switch-label {
            min-width: 68px;
            color: #475569;
            font-size: 11px;
            font-weight: 760;
            text-align: right;
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
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 15px;
        }

        .clinic-modal-col-12 { grid-column: span 12; }
        .clinic-modal-col-8 { grid-column: span 8; }
        .clinic-modal-col-6 { grid-column: span 6; }
        .clinic-modal-col-4 { grid-column: span 4; }

        .clinic-modal-section-title {
            grid-column: span 12;
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .clinic-radio-card {
            position: relative;
            display: block;
            min-height: 86px;
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 12px;
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

        @media (max-width: 1100px) {
            .clinic-schedule-overview {
                grid-template-columns: 1fr;
            }

            .clinic-day {
                grid-template-columns: 1fr;
            }

            .clinic-day__switch-wrap {
                justify-content: flex-start;
            }

            .clinic-day__switch-label {
                min-width: 0;
                text-align: left;
            }

            .clinic-exception {
                grid-template-columns: 1fr 1.4fr;
            }

            .clinic-exception__actions {
                justify-content: flex-start;
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

            .clinic-day__hours {
                flex-wrap: wrap;
            }

            .clinic-time {
                flex: 1 1 120px;
                width: auto;
            }

            .clinic-exception {
                grid-template-columns: 1fr;
            }

            .clinic-radio-grid,
            .clinic-target-list {
                grid-template-columns: 1fr;
            }

            .clinic-modal-col-8,
            .clinic-modal-col-6,
            .clinic-modal-col-4 {
                grid-column: span 12;
            }
        }
    </style>
@endsection

@section('sub-main')
    @php
        $weekDays = [
            ['key' => 'monday', 'short' => 'ПН', 'name' => 'Понедельник', 'enabled' => true, 'from' => '08:00', 'to' => '20:00'],
            ['key' => 'tuesday', 'short' => 'ВТ', 'name' => 'Вторник', 'enabled' => true, 'from' => '08:00', 'to' => '20:00'],
            ['key' => 'wednesday', 'short' => 'СР', 'name' => 'Среда', 'enabled' => true, 'from' => '08:00', 'to' => '20:00'],
            ['key' => 'thursday', 'short' => 'ЧТ', 'name' => 'Четверг', 'enabled' => true, 'from' => '08:00', 'to' => '20:00'],
            ['key' => 'friday', 'short' => 'ПТ', 'name' => 'Пятница', 'enabled' => true, 'from' => '08:00', 'to' => '20:00'],
            ['key' => 'saturday', 'short' => 'СБ', 'name' => 'Суббота', 'enabled' => true, 'from' => '09:00', 'to' => '15:00'],
            ['key' => 'sunday', 'short' => 'ВС', 'name' => 'Воскресенье', 'enabled' => false, 'from' => '09:00', 'to' => '15:00'],
        ];

        $demoDoctors = [
            'Иванов Иван Иванович — врач-психиатр',
            'Петров Пётр Сергеевич — врач-психиатр участковый',
            'Сидорова Анна Викторовна — врач-психотерапевт',
            'Кузнецов Алексей Олегович — врач-психиатр',
        ];

        $demoOffices = [
            'Кабинет №201',
            'Кабинет №207',
            'Кабинет №214',
            'Процедурный кабинет №118',
        ];
    @endphp

    <main class="settings-page clinic-schedule-page">
        <div class="settings-container">
            <div class="settings-page-header clinic-schedule-toolbar">
                <div>
                    <h1 class="settings-page-title">График клиники</h1>
                    <p class="settings-page-description">
                        Настройте обычное рабочее время организации и отдельные периоды, когда работа клиники,
                        кабинетов или отдельных врачей ограничивается.
                    </p>
                </div>

                <div class="clinic-schedule-toolbar__actions">
                    <button type="button"
                            class="settings-button settings-button--secondary"
                            id="copyWeekScheduleBtn">
                        <i class="bi bi-copy"></i>
                        Скопировать будни
                    </button>

                    <button type="button"
                            class="settings-button settings-button--primary"
                            data-bs-toggle="modal"
                            data-bs-target="#clinicExceptionModal">
                        <i class="bi bi-plus-lg"></i>
                        Добавить ограничение
                    </button>
                </div>
            </div>

            <section class="clinic-schedule-overview">
                <article class="clinic-schedule-stat">
                    <div class="clinic-schedule-stat__label">Обычный режим</div>
                    <div class="clinic-schedule-stat__value">6 рабочих дней</div>
                    <div class="clinic-schedule-stat__hint">Пн–Пт 08:00–20:00 · Сб 09:00–15:00</div>
                </article>

                <article class="clinic-schedule-stat clinic-schedule-stat--warning">
                    <div class="clinic-schedule-stat__label">Ближайшее ограничение</div>
                    <div class="clinic-schedule-stat__value">24 сентября</div>
                    <div class="clinic-schedule-stat__hint">Санитарные мероприятия · 14:00–17:00</div>
                </article>

                <article class="clinic-schedule-stat clinic-schedule-stat--neutral">
                    <div class="clinic-schedule-stat__label">Активные правила</div>
                    <div class="clinic-schedule-stat__value"><span id="exceptionCounter">3</span> ограничения</div>
                    <div class="clinic-schedule-stat__hint">Включая карантин, сокращённый режим и ограничения врачей</div>
                </article>
            </section>

            <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Обычная рабочая неделя</h2>
                        <div class="settings-panel__subtitle">
                            Базовый график применяется всегда, если на конкретную дату не задано отдельное ограничение.
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
                            Сейчас это интерфейс-прототип: изменения рабочей недели остаются только на странице.
                            После подключения backend здесь будет сохраняться постоянный график конкретной клиники.
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

            <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Особые периоды и ограничения</h2>
                        <div class="settings-panel__subtitle">
                            Исключения перекрывают обычный график на указанные даты и могут действовать на всю клинику,
                            отдельные кабинеты или конкретных врачей.
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
                        <article class="clinic-exception clinic-exception--danger">
                            <div class="clinic-exception__period">
                                <div class="clinic-exception__date">01.10.2026 — 07.10.2026</div>
                                <div class="clinic-exception__time">Круглосуточно</div>
                            </div>

                            <div class="clinic-exception__main">
                                <div class="clinic-exception__title">
                                    Карантин
                                    <span class="clinic-badge clinic-badge--danger">Приём закрыт</span>
                                </div>
                                <div class="clinic-exception__description">
                                    Плановый приём пациентов временно не проводится. Правило действует на организацию целиком.
                                </div>
                            </div>

                            <div class="clinic-exception__scope">
                                <span class="clinic-exception__scope-label">Действует на</span>
                                <span class="clinic-exception__scope-value">Всю клинику</span>
                            </div>

                            <div class="clinic-exception__actions">
                                <button type="button" class="clinic-icon-button js-edit-exception" title="Изменить">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="clinic-icon-button clinic-icon-button--danger js-delete-exception" title="Удалить">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </article>

                        <article class="clinic-exception clinic-exception--warning">
                            <div class="clinic-exception__period">
                                <div class="clinic-exception__date">24.09.2026</div>
                                <div class="clinic-exception__time">14:00 — 17:00</div>
                            </div>

                            <div class="clinic-exception__main">
                                <div class="clinic-exception__title">
                                    Санитарные мероприятия
                                    <span class="clinic-badge clinic-badge--warning">Ограничен приём</span>
                                </div>
                                <div class="clinic-exception__description">
                                    В выбранных кабинетах приём пациентов приостанавливается на время проведения обработки помещений.
                                </div>
                            </div>

                            <div class="clinic-exception__scope">
                                <span class="clinic-exception__scope-label">Действует на</span>
                                <span class="clinic-exception__scope-value">Кабинеты №201, №214</span>
                            </div>

                            <div class="clinic-exception__actions">
                                <button type="button" class="clinic-icon-button js-edit-exception" title="Изменить">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="clinic-icon-button clinic-icon-button--danger js-delete-exception" title="Удалить">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </article>

                        <article class="clinic-exception clinic-exception--info">
                            <div class="clinic-exception__period">
                                <div class="clinic-exception__date">28.09.2026</div>
                                <div class="clinic-exception__time">08:00 — 13:00</div>
                            </div>

                            <div class="clinic-exception__main">
                                <div class="clinic-exception__title">
                                    Организационное мероприятие
                                    <span class="clinic-badge clinic-badge--info">Сокращённый режим</span>
                                </div>
                                <div class="clinic-exception__description">
                                    Для выбранных специалистов установлен отдельный интервал работы на указанную дату.
                                </div>
                            </div>

                            <div class="clinic-exception__scope">
                                <span class="clinic-exception__scope-label">Действует на</span>
                                <span class="clinic-exception__scope-value">Иванов И.И., Сидорова А.В.</span>
                            </div>

                            <div class="clinic-exception__actions">
                                <button type="button" class="clinic-icon-button js-edit-exception" title="Изменить">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="clinic-icon-button clinic-icon-button--danger js-delete-exception" title="Удалить">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </article>
                    </div>

                    <div class="clinic-empty-inline d-none" id="clinicExceptionsEmpty">
                        Особых периодов пока нет. Добавьте правило, если нужно временно изменить работу клиники,
                        кабинета или отдельных врачей.
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
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="clinicExceptionModalLabel">Новое ограничение работы</h2>
                        <div class="settings-panel__subtitle">
                            Укажите период, режим ограничения и кого оно затрагивает.
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <form id="clinicExceptionForm">
                        <div class="clinic-modal-grid">
                            <div class="clinic-modal-section-title">Причина</div>

                            <div class="clinic-modal-col-6">
                                <label class="settings-label" for="exceptionType">Тип мероприятия</label>
                                <select class="settings-control" id="exceptionType" required>
                                    <option value="quarantine">Карантин</option>
                                    <option value="sanitary" selected>Санитарные мероприятия</option>
                                    <option value="technical">Технические работы</option>
                                    <option value="organizational">Организационное мероприятие</option>
                                    <option value="other">Другое</option>
                                </select>
                            </div>

                            <div class="clinic-modal-col-6">
                                <label class="settings-label" for="exceptionTitle">Название / пояснение</label>
                                <input type="text"
                                       class="settings-control"
                                       id="exceptionTitle"
                                       value="Санитарные мероприятия"
                                       placeholder="Например: генеральная уборка">
                            </div>

                            <div class="clinic-modal-section-title">Период</div>

                            <div class="clinic-modal-col-4">
                                <label class="settings-label" for="exceptionDateFrom">Дата с</label>
                                <input type="date" class="settings-control" id="exceptionDateFrom" value="2026-09-24" required>
                            </div>

                            <div class="clinic-modal-col-4">
                                <label class="settings-label" for="exceptionDateTo">Дата по</label>
                                <input type="date" class="settings-control" id="exceptionDateTo" value="2026-09-24" required>
                            </div>

                            <div class="clinic-modal-col-4">
                                <label class="settings-label" for="exceptionAllDay">Период дня</label>
                                <select class="settings-control" id="exceptionAllDay">
                                    <option value="time" selected>По времени</option>
                                    <option value="all_day">Весь день</option>
                                </select>
                            </div>

                            <div class="clinic-modal-col-6 js-exception-time-field">
                                <label class="settings-label" for="exceptionTimeFrom">Время с</label>
                                <input type="time" class="settings-control" id="exceptionTimeFrom" value="14:00">
                            </div>

                            <div class="clinic-modal-col-6 js-exception-time-field">
                                <label class="settings-label" for="exceptionTimeTo">Время по</label>
                                <input type="time" class="settings-control" id="exceptionTimeTo" value="17:00">
                            </div>

                            <div class="clinic-modal-section-title">Что меняется</div>

                            <div class="clinic-modal-col-12">
                                <div class="clinic-radio-grid">
                                    <label class="clinic-radio-card">
                                        <input type="radio" name="restriction_mode" value="closed" checked>
                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-slash-circle"></i>
                                            Приём прекращён
                                        </span>
                                        <span class="clinic-radio-card__text">
                                            В выбранный период пациентов не принимают.
                                        </span>
                                    </label>

                                    <label class="clinic-radio-card">
                                        <input type="radio" name="restriction_mode" value="shortened">
                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-clock-history"></i>
                                            Изменено время
                                        </span>
                                        <span class="clinic-radio-card__text">
                                            Применяется отдельный рабочий интервал.
                                        </span>
                                    </label>

                                    <label class="clinic-radio-card">
                                        <input type="radio" name="restriction_mode" value="limited">
                                        <span class="clinic-radio-card__title">
                                            <i class="bi bi-person-x"></i>
                                            Ограничен приём
                                        </span>
                                        <span class="clinic-radio-card__text">
                                            Организация работает, но приём ограничен.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="clinic-modal-section-title">На кого действует</div>

                            <div class="clinic-modal-col-4">
                                <label class="settings-label" for="exceptionScope">Область действия</label>
                                <select class="settings-control" id="exceptionScope">
                                    <option value="clinic">Вся клиника</option>
                                    <option value="offices" selected>Отдельные кабинеты</option>
                                    <option value="doctors">Отдельные врачи</option>
                                </select>
                            </div>

                            <div class="clinic-modal-col-8">
                                <div class="clinic-target-box js-target-box is-hidden" data-target="clinic">
                                    <div class="settings-info-box">
                                        <span class="settings-info-box__icon"><i class="bi bi-hospital"></i></span>
                                        <div>
                                            Ограничение будет действовать на всю организацию независимо от кабинета
                                            или расписания конкретного врача.
                                        </div>
                                    </div>
                                </div>

                                <div class="clinic-target-box js-target-box" data-target="offices">
                                    <div class="clinic-target-list">
                                        @foreach($demoOffices as $index => $office)
                                            <label class="clinic-target-item">
                                                <input type="checkbox" value="{{ $office }}" {{ $index < 2 ? 'checked' : '' }}>
                                                <span>{{ $office }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="clinic-target-box js-target-box is-hidden" data-target="doctors">
                                    <div class="clinic-target-list">
                                        @foreach($demoDoctors as $index => $doctor)
                                            <label class="clinic-target-item">
                                                <input type="checkbox" value="{{ $doctor }}" {{ $index < 2 ? 'checked' : '' }}>
                                                <span>{{ $doctor }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="clinic-modal-section-title">Комментарий</div>

                            <div class="clinic-modal-col-12">
                                <label class="settings-label" for="exceptionComment">Описание ограничения</label>
                                <textarea class="settings-control"
                                          id="exceptionComment"
                                          rows="3"
                                          placeholder="Что происходит и как меняется работа подразделения">Приём пациентов временно приостанавливается на время проведения мероприятия.</textarea>
                            </div>
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

            const initialWeekState = weekEditor
                ? Array.from(weekEditor.querySelectorAll('.clinic-day')).map((day) => ({
                    key: day.dataset.day,
                    enabled: day.querySelector('.js-day-toggle')?.checked ?? false,
                    from: day.querySelector('.js-time-from')?.value ?? '',
                    to: day.querySelector('.js-time-to')?.value ?? '',
                }))
                : [];

            function syncDayState(day) {
                if (!day) return;

                const toggle = day.querySelector('.js-day-toggle');
                const from = day.querySelector('.js-time-from');
                const to = day.querySelector('.js-time-to');
                const status = day.querySelector('.js-day-status');
                const switchLabel = day.querySelector('.js-switch-label');
                const enabled = Boolean(toggle?.checked);

                day.classList.toggle('is-closed', !enabled);

                if (from) from.disabled = !enabled;
                if (to) to.disabled = !enabled;
                if (status) status.textContent = enabled ? 'Рабочий день' : 'Выходной';
                if (switchLabel) switchLabel.textContent = enabled ? 'Открыто' : 'Закрыто';
            }

            if (weekEditor) {
                weekEditor.querySelectorAll('.clinic-day').forEach((day) => {
                    day.querySelector('.js-day-toggle')?.addEventListener('change', () => syncDayState(day));
                });
            }

            copyWeekScheduleBtn?.addEventListener('click', () => {
                if (!weekEditor) return;

                const monday = weekEditor.querySelector('[data-day="monday"]');
                const mondayFrom = monday?.querySelector('.js-time-from')?.value;
                const mondayTo = monday?.querySelector('.js-time-to')?.value;

                ['tuesday', 'wednesday', 'thursday', 'friday'].forEach((dayKey) => {
                    const day = weekEditor.querySelector(`[data-day="${dayKey}"]`);
                    const toggle = day?.querySelector('.js-day-toggle');
                    const from = day?.querySelector('.js-time-from');
                    const to = day?.querySelector('.js-time-to');

                    if (toggle) toggle.checked = true;
                    if (from && mondayFrom) from.value = mondayFrom;
                    if (to && mondayTo) to.value = mondayTo;
                    syncDayState(day);
                });
            });

            resetWeekBtn?.addEventListener('click', () => {
                initialWeekState.forEach((state) => {
                    const day = weekEditor?.querySelector(`[data-day="${state.key}"]`);
                    const toggle = day?.querySelector('.js-day-toggle');
                    const from = day?.querySelector('.js-time-from');
                    const to = day?.querySelector('.js-time-to');

                    if (toggle) toggle.checked = state.enabled;
                    if (from) from.value = state.from;
                    if (to) to.value = state.to;
                    syncDayState(day);
                });
            });

            saveWeekBtn?.addEventListener('click', () => {
                const originalText = saveWeekBtn.innerHTML;
                saveWeekBtn.innerHTML = '<i class="bi bi-check-circle"></i> Сохранено на странице';
                saveWeekBtn.disabled = true;

                window.setTimeout(() => {
                    saveWeekBtn.innerHTML = originalText;
                    saveWeekBtn.disabled = false;
                }, 1600);
            });

            const exceptionScope = document.getElementById('exceptionScope');
            const exceptionAllDay = document.getElementById('exceptionAllDay');
            const exceptionType = document.getElementById('exceptionType');
            const exceptionTitle = document.getElementById('exceptionTitle');
            const exceptionList = document.getElementById('clinicExceptionList');
            const exceptionCounter = document.getElementById('exceptionCounter');
            const exceptionsEmpty = document.getElementById('clinicExceptionsEmpty');
            const saveExceptionBtn = document.getElementById('saveExceptionBtn');
            const exceptionModalEl = document.getElementById('clinicExceptionModal');
            const exceptionModalTitle = document.getElementById('clinicExceptionModalLabel');

            let editingException = null;

            function syncScope() {
                const selected = exceptionScope?.value || 'clinic';

                document.querySelectorAll('.js-target-box').forEach((box) => {
                    box.classList.toggle('is-hidden', box.dataset.target !== selected);
                });
            }

            function syncAllDay() {
                const allDay = exceptionAllDay?.value === 'all_day';
                document.querySelectorAll('.js-exception-time-field').forEach((field) => {
                    field.classList.toggle('d-none', allDay);
                });
            }

            const typeLabels = {
                quarantine: 'Карантин',
                sanitary: 'Санитарные мероприятия',
                technical: 'Технические работы',
                organizational: 'Организационное мероприятие',
                other: 'Другое мероприятие',
            };

            exceptionType?.addEventListener('change', () => {
                if (!exceptionTitle) return;
                exceptionTitle.value = typeLabels[exceptionType.value] || 'Ограничение работы';
            });

            exceptionScope?.addEventListener('change', syncScope);
            exceptionAllDay?.addEventListener('change', syncAllDay);
            syncScope();
            syncAllDay();

            function formatDate(dateValue) {
                if (!dateValue) return 'Дата не указана';

                const [year, month, day] = dateValue.split('-');
                return `${day}.${month}.${year}`;
            }

            function collectTargets(scope) {
                if (scope === 'clinic') return ['Вся клиника'];

                const box = document.querySelector(`.js-target-box[data-target="${scope}"]`);
                const checked = Array.from(box?.querySelectorAll('input[type="checkbox"]:checked') || []);

                return checked.map((input) => {
                    const value = String(input.value || '');
                    return value.replace(/ — .*$/, '');
                });
            }

            function getModeMeta(mode) {
                if (mode === 'shortened') {
                    return {
                        badge: 'Сокращённый режим',
                        badgeClass: 'clinic-badge--info',
                        cardClass: 'clinic-exception--info',
                    };
                }

                if (mode === 'limited') {
                    return {
                        badge: 'Ограничен приём',
                        badgeClass: 'clinic-badge--warning',
                        cardClass: 'clinic-exception--warning',
                    };
                }

                return {
                    badge: 'Приём закрыт',
                    badgeClass: 'clinic-badge--danger',
                    cardClass: 'clinic-exception--danger',
                };
            }

            function updateCounter() {
                const count = exceptionList?.querySelectorAll('.clinic-exception').length || 0;
                if (exceptionCounter) exceptionCounter.textContent = count;
                exceptionsEmpty?.classList.toggle('d-none', count > 0);
            }

            function bindExceptionButtons(root = document) {
                root.querySelectorAll('.js-delete-exception').forEach((button) => {
                    if (button.dataset.bound === '1') return;
                    button.dataset.bound = '1';

                    button.addEventListener('click', () => {
                        button.closest('.clinic-exception')?.remove();
                        updateCounter();
                    });
                });

                root.querySelectorAll('.js-edit-exception').forEach((button) => {
                    if (button.dataset.bound === '1') return;
                    button.dataset.bound = '1';

                    button.addEventListener('click', () => {
                        editingException = button.closest('.clinic-exception');
                        if (exceptionModalTitle) exceptionModalTitle.textContent = 'Изменить ограничение работы';
                        if (saveExceptionBtn) saveExceptionBtn.innerHTML = '<i class="bi bi-check2"></i> Сохранить изменения';
                        bootstrap.Modal.getOrCreateInstance(exceptionModalEl).show();
                    });
                });
            }

            bindExceptionButtons();
            updateCounter();

            exceptionModalEl?.addEventListener('hidden.bs.modal', () => {
                editingException = null;
                if (exceptionModalTitle) exceptionModalTitle.textContent = 'Новое ограничение работы';
                if (saveExceptionBtn) saveExceptionBtn.innerHTML = '<i class="bi bi-check2"></i> Добавить правило';
            });

            saveExceptionBtn?.addEventListener('click', () => {
                const dateFrom = document.getElementById('exceptionDateFrom')?.value || '';
                const dateTo = document.getElementById('exceptionDateTo')?.value || '';
                const allDay = exceptionAllDay?.value === 'all_day';
                const timeFrom = document.getElementById('exceptionTimeFrom')?.value || '';
                const timeTo = document.getElementById('exceptionTimeTo')?.value || '';
                const mode = document.querySelector('input[name="restriction_mode"]:checked')?.value || 'closed';
                const scope = exceptionScope?.value || 'clinic';
                const targets = collectTargets(scope);
                const comment = document.getElementById('exceptionComment')?.value?.trim() || 'Временное изменение режима работы.';
                const title = exceptionTitle?.value?.trim() || typeLabels[exceptionType?.value] || 'Ограничение работы';
                const modeMeta = getModeMeta(mode);

                if (!dateFrom || !dateTo) {
                    window.alert('Укажите дату начала и окончания ограничения.');
                    return;
                }

                if (scope !== 'clinic' && targets.length === 0) {
                    window.alert('Выберите хотя бы один кабинет или одного врача.');
                    return;
                }

                const period = dateFrom === dateTo
                    ? formatDate(dateFrom)
                    : `${formatDate(dateFrom)} — ${formatDate(dateTo)}`;

                const time = allDay
                    ? 'Весь день'
                    : `${timeFrom || '00:00'} — ${timeTo || '23:59'}`;

                const scopeText = targets.join(', ');

                const cardHtml = `
                    <div class="clinic-exception__period">
                        <div class="clinic-exception__date">${period}</div>
                        <div class="clinic-exception__time">${time}</div>
                    </div>
                    <div class="clinic-exception__main">
                        <div class="clinic-exception__title">
                            ${title}
                            <span class="clinic-badge ${modeMeta.badgeClass}">${modeMeta.badge}</span>
                        </div>
                        <div class="clinic-exception__description">${comment}</div>
                    </div>
                    <div class="clinic-exception__scope">
                        <span class="clinic-exception__scope-label">Действует на</span>
                        <span class="clinic-exception__scope-value">${scopeText}</span>
                    </div>
                    <div class="clinic-exception__actions">
                        <button type="button" class="clinic-icon-button js-edit-exception" title="Изменить"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="clinic-icon-button clinic-icon-button--danger js-delete-exception" title="Удалить"><i class="bi bi-trash3"></i></button>
                    </div>
                `;

                if (editingException) {
                    editingException.className = `clinic-exception ${modeMeta.cardClass}`;
                    editingException.innerHTML = cardHtml;
                    bindExceptionButtons(editingException);
                } else if (exceptionList) {
                    const article = document.createElement('article');
                    article.className = `clinic-exception ${modeMeta.cardClass}`;
                    article.innerHTML = cardHtml;
                    exceptionList.prepend(article);
                    bindExceptionButtons(article);
                }

                updateCounter();
                bootstrap.Modal.getOrCreateInstance(exceptionModalEl).hide();
            });
        })();
    </script>
@endpush
