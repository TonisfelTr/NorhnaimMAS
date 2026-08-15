@extends('doctors.analyses')

@section('analysis-title', 'Назначения исследований')

@section('assets')
    @parent

    <style>
        .assignment-flash {
            margin-bottom: 18px;
        }

        .assignment-direction-preview {
            max-width: 360px;
            margin-top: 5px;
            color: var(--lab-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .assignment-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .22);
        }

        .assignment-modal .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--lab-border);
            background: linear-gradient(135deg, #fff 0%, #effafb 100%);
        }

        .assignment-modal .modal-title {
            color: var(--lab-text);
            font-size: 21px;
            font-weight: 850;
        }

        .assignment-modal__subtitle {
            margin-top: 4px;
            color: var(--lab-muted);
            font-size: 13px;
        }

        .assignment-modal .modal-body {
            padding: 24px;
            background: #fbfdff;
        }

        .assignment-modal .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--lab-border);
            background: #fff;
        }

        .assignment-form-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 16px;
        }

        .assignment-col-12 { grid-column: span 12; }
        .assignment-col-8 { grid-column: span 8; }
        .assignment-col-6 { grid-column: span 6; }
        .assignment-col-4 { grid-column: span 4; }

        .assignment-field {
            position: relative;
        }

        .assignment-label {
            display: block;
            margin-bottom: 7px;
            color: #344054;
            font-size: 13px;
            font-weight: 800;
        }

        .assignment-required::after {
            content: ' *';
            color: #dc3545;
        }

        .assignment-control {
            width: 100%;
            min-height: 45px;
            padding: 10px 13px;
            border: 1px solid #d8e0e5;
            border-radius: 12px;
            background: #fff;
            color: var(--lab-text);
            font-size: 14px;
        }

        textarea.assignment-control {
            min-height: 94px;
            resize: vertical;
        }

        .assignment-control:focus {
            border-color: var(--lab-primary);
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .12);
        }

        .assignment-control:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        .assignment-hint {
            margin-top: 6px;
            color: #7b8794;
            font-size: 12px;
            line-height: 1.45;
        }

        .assignment-error {
            margin-top: 6px;
            color: #dc3545;
            font-size: 12px;
            font-weight: 650;
        }

        .assignment-autocomplete {
            position: relative;
        }

        .assignment-autocomplete__menu {
            position: absolute;
            z-index: 1080;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            display: none;
            max-height: 260px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #dbe5eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .15);
        }

        .assignment-autocomplete__menu.is-open {
            display: block;
        }

        .assignment-autocomplete__item,
        .assignment-autocomplete__state {
            width: 100%;
            padding: 10px 11px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #263241;
            font-size: 13px;
            text-align: left;
        }

        button.assignment-autocomplete__item:hover {
            background: #edf9fa;
            color: var(--lab-primary-dark);
        }

        .assignment-autocomplete__item-meta {
            display: block;
            margin-top: 3px;
            color: #84909c;
            font-size: 11px;
        }

        .assignment-selected {
            min-height: 92px;
            margin-top: 12px;
            padding: 12px;
            border: 1px dashed #cbd8df;
            border-radius: 14px;
            background: #fff;
        }

        .assignment-selected__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .assignment-selected__title {
            color: #344054;
            font-size: 13px;
            font-weight: 800;
        }

        .assignment-selected__count {
            display: inline-flex;
            min-width: 28px;
            min-height: 28px;
            align-items: center;
            justify-content: center;
            padding: 3px 9px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--lab-primary-dark);
            font-size: 12px;
            font-weight: 850;
        }

        .assignment-selected__list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .assignment-selected__empty {
            color: #8a96a3;
            font-size: 12px;
        }

        .assignment-selected__item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 9px 7px 11px;
            border: 1px solid #d5e8eb;
            border-radius: 999px;
            background: #f1fbfc;
            color: #24525a;
            font-size: 12px;
            font-weight: 700;
        }

        .assignment-selected__remove {
            display: grid;
            width: 20px;
            height: 20px;
            place-items: center;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: rgba(4, 134, 150, .12);
            color: var(--lab-primary-dark);
            line-height: 1;
        }

        .assignment-modal__note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #dbecef;
            border-radius: 13px;
            background: #f2fbfc;
            color: #41636a;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 991.98px) {
            .assignment-col-8,
            .assignment-col-6,
            .assignment-col-4 {
                grid-column: span 12;
            }
        }


        /*
         * Окно привязано к кнопке «Новое назначение» и открывается
         * непосредственно под ней, без затемнения всей страницы.
         */
        .assignment-modal {
            position: fixed;
            z-index: 1095;
            display: none;
            width: min(860px, calc(100vw - 32px));
            max-height: calc(100vh - 24px);
        }

        .assignment-modal.is-open {
            display: block;
        }

        .assignment-modal::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 24px;
            width: 16px;
            height: 16px;
            border-top: 1px solid var(--lab-border);
            border-left: 1px solid var(--lab-border);
            background: #fff;
            transform: rotate(45deg);
            z-index: 2;
        }

        .assignment-modal .modal-dialog {
            width: 100%;
            max-width: none;
            margin: 0;
            transform: none;
        }

        .assignment-modal .modal-content {
            display: flex;
            max-height: calc(100vh - 24px);
            flex-direction: column;
        }

        .assignment-modal .modal-body {
            overflow-y: auto;
        }

        @media (max-width: 767.98px) {
            .assignment-modal {
                right: 12px !important;
                left: 12px !important;
                width: auto;
            }

            .assignment-modal::before {
                display: none;
            }
        }

        .assignment-patient-card {
            margin-top: 12px;
            padding: 14px;
            border: 1px solid #d7e7ea;
            border-radius: 14px;
            background: #f7fbfc;
        }

        .assignment-patient-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .assignment-patient-card__name {
            color: #1f2937;
            font-size: 14px;
            font-weight: 800;
        }

        .assignment-patient-card__meta {
            margin-top: 3px;
            color: #667085;
            font-size: 11px;
        }

        .assignment-patient-card__source {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #e4f6f8;
            color: #087f8c;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .assignment-patient-card__grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px 14px;
        }

        .assignment-patient-card__grid > div {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 3px;
        }

        .assignment-patient-card__grid span {
            color: #8792a2;
            font-size: 10px;
        }

        .assignment-patient-card__grid strong {
            overflow: hidden;
            color: #344054;
            font-size: 12px;
            text-overflow: ellipsis;
        }

        .assignment-patient-card__wide {
            grid-column: span 2;
        }

        .lab-patient-search-modal {
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            box-shadow:
                0 24px 60px rgba(15, 23, 42, .16),
                0 4px 16px rgba(15, 23, 42, .08);
        }

        .lab-patient-search-modal .modal-header {
            padding: 24px 24px 8px;
        }

        .lab-patient-search-modal .modal-title {
            color: #253047;
            font-size: 20px;
            font-weight: 800;
        }

        .lab-patient-search-modal .modal-body {
            padding: 18px 24px 24px;
        }

        .lab-patient-search-input {
            position: relative;
        }

        .lab-patient-search-input > i {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: 15px;
            color: #8995a7;
            font-size: 16px;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .lab-patient-search-input .form-control {
            height: 48px;
            padding-right: 15px;
            padding-left: 43px;
            border: 1px solid #e0e7ef;
            border-radius: 13px;
            background: #f8fafc;
            font-size: 14px;
        }

        .lab-patient-search-input .form-control:focus {
            border-color: #1595a5;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(21, 149, 165, .10);
        }

        .lab-patient-search-state {
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 9px;
            color: #98a2b3;
            font-size: 13px;
            text-align: center;
        }

        .lab-patient-search-state > i {
            color: #b9c3ce;
            font-size: 28px;
        }

        .lab-patient-search-results {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 12px;
            max-height: 390px;
            overflow-y: auto;
        }

        .lab-patient-search-result {
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px 13px;
            border: 1px solid transparent;
            border-radius: 13px;
            background: #fff;
            cursor: pointer;
            text-align: left;
            transition:
                background .15s ease,
                border-color .15s ease,
                transform .15s ease;
        }

        .lab-patient-search-result:hover {
            border-color: #d5eef1;
            background: #f0f9fa;
        }

        .lab-patient-search-result__avatar {
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 11px;
            border-radius: 11px;
            background: #edf8fa;
            color: #128b9b;
            font-size: 17px;
        }

        .lab-patient-search-result__content {
            min-width: 0;
            flex: 1;
        }

        .lab-patient-search-result__name {
            display: block;
            color: #263247;
            font-size: 14px;
            font-weight: 750;
        }

        .lab-patient-search-result__meta {
            display: block;
            margin-top: 3px;
            color: #8a95a5;
            font-size: 12px;
        }

        .lab-patient-search-result__arrow {
            margin-left: 10px;
            color: #a4adba;
            font-size: 14px;
        }

    </style>
@endsection

@section('analysis-content')
    <div class="lab-page">
        <div class="lab-container">
            @if(session('success'))
                <div class="alert alert-success assignment-flash">
                    {{ session('success') }}
                </div>
            @endif

            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Контроль выполнения</div>
                    <h1 class="lab-title">Назначения лабораторных исследований</h1>
                    <p class="lab-description">
                        Создание, контроль и последующая печать лабораторных направлений.
                    </p>
                </div>

                <div class="lab-hero__actions">
                    <button
                        class="lab-button lab-button--primary"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#labPatientSearchModal"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Новое назначение
                    </button>
                </div>
            </section>

            <section class="lab-panel mb-4">
                <div class="lab-panel__header">
                    <h2 class="lab-panel__title">Фильтры</h2>
                </div>

                <div class="lab-panel__body">
                    <form
                        method="GET"
                        action="{{ route('doctors.analyses.assignments') }}"
                        class="lab-filters"
                    >
                        <div class="lab-filter--4">
                            <label class="lab-label" for="assignmentSearch">
                                Пациент или направление
                            </label>
                            <input
                                id="assignmentSearch"
                                class="lab-control"
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Введите ФИО или название..."
                            >
                        </div>

                        <div class="lab-filter--3">
                            <label class="lab-label" for="assignmentStage">
                                Этап
                            </label>
                            <select id="assignmentStage" class="lab-control" name="stage">
                                <option value="">Все этапы</option>
                                <option value="assigned" @selected(request('stage') === 'assigned')>
                                    Назначено
                                </option>
                                <option value="processing" @selected(request('stage') === 'processing')>
                                    Выполняется
                                </option>
                                <option value="ready" @selected(request('stage') === 'ready')>
                                    Готово
                                </option>
                                <option value="overdue" @selected(request('stage') === 'overdue')>
                                    Просрочено
                                </option>
                            </select>
                        </div>

                        <div class="lab-filter--3">
                            <label class="lab-label" for="assignmentPeriod">
                                Период
                            </label>
                            <select id="assignmentPeriod" class="lab-control" name="period">
                                <option value="">Весь период</option>
                                <option value="today" @selected(request('period') === 'today')>
                                    Сегодня
                                </option>
                                <option value="week" @selected(request('period') === 'week')>
                                    7 дней
                                </option>
                                <option value="month" @selected(request('period') === 'month')>
                                    30 дней
                                </option>
                            </select>
                        </div>

                        <div class="lab-filter--2">
                            <button class="lab-button lab-button--primary w-100" type="submit">
                                Применить
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="lab-panel">
                <div class="lab-panel__header">
                    <div>
                        <h2 class="lab-panel__title">Текущие назначения</h2>
                        <div class="lab-panel__subtitle">
                            {{ $rows->count() }} записей
                        </div>
                    </div>
                </div>

                @if($rows->isNotEmpty())
                    <div class="lab-table-wrap">
                        <table class="table lab-table">
                            <thead>
                            <tr>
                                <th>Пациент</th>
                                <th>Направление</th>
                                <th>Назначено</th>
                                <th>Срок</th>
                                <th>Этап</th>
                                <th class="text-end">Действия</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>
                                        <div class="lab-patient">
                                            <div class="lab-avatar">{{ $row['initials'] }}</div>
                                            <div>
                                                <div class="lab-primary-text">
                                                    {{ $row['patient_name'] }}
                                                </div>
                                                <div class="lab-secondary-text">
                                                    {{ $row['patient_birth_date'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ $row['research_name'] }}
                                        </div>
                                        <div class="lab-secondary-text">
                                            {{ $row['material'] }} · {{ $row['priority_label'] }}
                                        </div>

                                        @if($row['parameter_preview'] !== '')
                                            <div class="assignment-direction-preview">
                                                {{ $row['parameter_preview'] }}
                                                @if($row['parameter_more_count'] > 0)
                                                    и ещё {{ $row['parameter_more_count'] }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ $row['assigned_date'] }}
                                        </div>
                                        <div class="lab-secondary-text">
                                            {{ $row['assigned_by'] }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ $row['due_date'] }}
                                        </div>
                                        <div class="lab-secondary-text">
                                            {{ $row['due_hint'] }}
                                        </div>
                                    </td>

                                    <td style="min-width: 320px;">
                                        <span class="lab-status lab-status--{{ $row['status_class'] }}">
                                            {{ $row['stage_label'] }}
                                        </span>

                                        <div class="lab-stepper">
                                            @foreach(['Назначено', 'Выполняется', 'Готово', "Просмотрено"] as $index => $label)
                                                <div
                                                    class="lab-step {{ ($index + 1) < $row['stage_index'] ? 'is-done' : (($index + 1) === $row['stage_index'] ? 'is-current' : '') }}"
                                                >
                                                    {{ $label }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td>
                                        <div class="lab-actions">
                                            <a class="lab-action" href="{{ route('doctors.patients.medical_card', $row['patient_id']) }}">
                                                Медкарта
                                            </a>

                                            <a class="lab-action" href="{{ $row['print_url'] }}" target="_blank" rel="noopener">
                                                <i class="bi bi-printer"></i>
                                                Печать
                                            </a>

                                            <a
                                                class="lab-action lab-action--primary"
                                                href="{{ $row['assignment_url'] }}"
                                            >
                                                Открыть
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="lab-pagination">
                        {{ $assignments->links() }}
                    </div>
                @else
                    <div class="lab-panel__body">
                        <div class="lab-empty">
                            <div class="lab-empty__icon">📝</div>
                            <h3>Назначений не найдено</h3>
                            <p>Создайте новое направление или измените параметры поиска.</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>


    <div class="modal fade"
         id="labPatientSearchModal"
         tabindex="-1"
         aria-labelledby="labPatientSearchModalTitle"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content lab-patient-search-modal">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="labPatientSearchModalTitle">
                            Найти пациента
                        </h5>
                        <div class="small text-muted mt-1">
                            Выберите пациента для создания лабораторного направления
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="lab-patient-search-input">
                        <i class="bi bi-search"></i>

                        <input type="search"
                               class="form-control"
                               id="labPatientSearchInput"
                               placeholder="ФИО, телефон или дата рождения"
                               autocomplete="off">
                    </div>

                    <div id="labPatientSearchHint"
                         class="lab-patient-search-state">
                        <i class="bi bi-person-search"></i>
                        <div>Начните вводить данные пациента</div>
                    </div>

                    <div id="labPatientSearchLoading"
                         class="lab-patient-search-state d-none">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <div>Поиск пациента...</div>
                    </div>

                    <div id="labPatientSearchResults"
                         class="lab-patient-search-results"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('labPatientSearchModal');
            const input = document.getElementById('labPatientSearchInput');
            const results = document.getElementById('labPatientSearchResults');
            const hint = document.getElementById('labPatientSearchHint');
            const loading = document.getElementById('labPatientSearchLoading');

            if (!modal || !input || !results) {
                return;
            }

            const searchUrl = @json(route('api.patients.search'));

            let timer = null;
            let controller = null;

            const escapeHtml = function (value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            };

            const showHint = function (icon, text) {
                if (!hint) {
                    return;
                }

                hint.innerHTML = `
                    <i class="bi ${icon}"></i>
                    <div>${escapeHtml(text)}</div>
                `;

                hint.classList.remove('d-none');
            };

            const hideHint = function () {
                hint?.classList.add('d-none');
            };

            const showLoading = function () {
                loading?.classList.remove('d-none');
            };

            const hideLoading = function () {
                loading?.classList.add('d-none');
            };

            modal.addEventListener('shown.bs.modal', function () {
                input.focus();
            });

            modal.addEventListener('hidden.bs.modal', function () {
                clearTimeout(timer);

                if (controller) {
                    controller.abort();
                    controller = null;
                }

                input.value = '';
                results.innerHTML = '';
                hideLoading();

                showHint(
                    'bi-person-search',
                    'Начните вводить ФИО, телефон или дату рождения'
                );
            });

            input.addEventListener('input', function () {
                clearTimeout(timer);

                const query = input.value.trim();

                if (query.length < 2) {
                    if (controller) {
                        controller.abort();
                        controller = null;
                    }

                    results.innerHTML = '';
                    hideLoading();

                    showHint(
                        query.length
                            ? 'bi-keyboard'
                            : 'bi-person-search',
                        query.length
                            ? 'Введите минимум 2 символа'
                            : 'Начните вводить ФИО, телефон или дату рождения'
                    );

                    return;
                }

                timer = setTimeout(function () {
                    searchPatients(query);
                }, 250);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                const query = input.value.trim();

                if (query.length < 2) {
                    return;
                }

                clearTimeout(timer);
                searchPatients(query);
            });

            async function searchPatients(query) {
                if (controller) {
                    controller.abort();
                }

                const currentController = new AbortController();
                controller = currentController;

                results.innerHTML = '';
                hideHint();
                showLoading();

                try {
                    const url = new URL(searchUrl, window.location.origin);
                    url.searchParams.set('q', query);

                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: currentController.signal
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    const data = await response.json();

                    const patients = Array.isArray(data)
                        ? data
                        : (
                            Array.isArray(data?.results)
                                ? data.results
                                : (
                                    Array.isArray(data?.data)
                                        ? data.data
                                        : []
                                )
                        );

                    if (controller !== currentController) {
                        return;
                    }

                    renderPatients(patients);
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error('Ошибка поиска пациентов:', error);

                    results.innerHTML = '';

                    showHint(
                        'bi-exclamation-circle',
                        'Не удалось выполнить поиск пациентов'
                    );
                } finally {
                    if (controller === currentController) {
                        hideLoading();
                        controller = null;
                    }
                }
            }

            function renderPatients(patients) {
                results.innerHTML = '';

                if (!Array.isArray(patients) || !patients.length) {
                    showHint(
                        'bi-person-x',
                        'Пациенты не найдены'
                    );

                    return;
                }

                hideHint();

                patients.forEach(function (patient) {
                    const patientId = patient.id;

                    const fullName =
                        patient.text ||
                        patient.full_name ||
                        patient.name ||
                        [
                            patient.last_name,
                            patient.first_name,
                            patient.middle_name
                        ].filter(Boolean).join(' ') ||
                        'Пациент';

                    const birthDate =
                        patient.birth_at ||
                        patient.birth_date ||
                        patient.birthday ||
                        '';

                    const phone =
                        patient.phone ||
                        patient.phone_number ||
                        '';

                    const item = document.createElement('button');

                    item.type = 'button';
                    item.className = 'lab-patient-search-result';

                    let meta = '';

                    if (birthDate) {
                        meta += 'Дата рождения: ' + escapeHtml(birthDate);
                    }

                    if (phone) {
                        if (meta) {
                            meta += ' · ';
                        }

                        meta += escapeHtml(phone);
                    }

                    item.innerHTML = `
                        <span class="lab-patient-search-result__avatar">
                            <i class="bi bi-person"></i>
                        </span>

                        <span class="lab-patient-search-result__content">
                            <span class="lab-patient-search-result__name">
                                ${escapeHtml(fullName)}
                            </span>

                            ${
                        meta
                            ? `
                                        <span class="lab-patient-search-result__meta">
                                            ${meta}
                                        </span>
                                      `
                            : ''
                    }
                        </span>

                        <span class="lab-patient-search-result__arrow">
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    `;

                    item.addEventListener('click', function () {
                        if (!patientId) {
                            return;
                        }

                        window.location.href =
                            '/doctors/patients/' +
                            encodeURIComponent(patientId) +
                            '?open=lab';
                    });

                    results.appendChild(item);
                });
            }
        });
    </script>

@endsection
