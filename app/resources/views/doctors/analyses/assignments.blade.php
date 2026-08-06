@extends('doctors.analyses')

@section('analysis-title', 'Назначения лабораторных исследований')

@section('assets')
    @parent
    <style>
        .lab-assignments-page {
            --assignment-accent: #0793a4;
            --assignment-accent-dark: #057786;
            --assignment-accent-soft: #eaf8fa;
            --assignment-border: #e4ebf1;
            --assignment-text: #172033;
            --assignment-muted: #6b7280;
            --assignment-success: #16835b;
            --assignment-success-soft: #eaf7f1;
            --assignment-danger: #c43d4d;
            --assignment-danger-soft: #fff0f2;
            --assignment-warning: #a96608;
            --assignment-warning-soft: #fff7e8;
        }

        .assignment-toolbar,
        .assignment-filter-grid,
        .assignment-card__header,
        .assignment-card__footer,
        .assignment-modal__header,
        .assignment-modal__footer {
            display: flex;
            align-items: center;
        }

        .assignment-toolbar {
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .assignment-toolbar__title {
            margin: 0;
            color: var(--assignment-text);
            font-size: 24px;
            font-weight: 800;
        }

        .assignment-toolbar__subtitle {
            margin-top: 5px;
            color: var(--assignment-muted);
            font-size: 13px;
        }

        .assignment-button {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 9px 15px;
            border: 1px solid var(--assignment-border);
            border-radius: 10px;
            background: #fff;
            color: var(--assignment-text);
            font: inherit;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
        }

        .assignment-button:hover,
        .assignment-button:focus-visible {
            border-color: var(--assignment-accent);
            color: var(--assignment-accent-dark);
            outline: none;
        }

        .assignment-button--primary {
            border-color: var(--assignment-accent);
            background: var(--assignment-accent);
            color: #fff;
        }

        .assignment-button--primary:hover,
        .assignment-button--primary:focus-visible {
            border-color: var(--assignment-accent-dark);
            background: var(--assignment-accent-dark);
            color: #fff;
        }

        .assignment-button--soft {
            border-color: #cfe7ea;
            background: var(--assignment-accent-soft);
            color: var(--assignment-accent-dark);
        }

        .assignment-panel {
            margin-bottom: 18px;
            border: 1px solid var(--assignment-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(32, 43, 61, .04);
        }

        .assignment-panel__body {
            padding: 18px;
        }

        .assignment-filter-grid {
            display: grid;
            grid-template-columns: minmax(220px, 1.6fr) minmax(160px, .8fr) minmax(160px, .8fr) auto;
            gap: 12px;
            align-items: end;
        }

        .assignment-field {
            min-width: 0;
        }

        .assignment-label {
            display: block;
            margin-bottom: 7px;
            color: #445166;
            font-size: 12px;
            font-weight: 700;
        }

        .assignment-control,
        .assignment-textarea {
            box-sizing: border-box;
            width: 100%;
            border: 1px solid #d9e1e8;
            border-radius: 10px;
            background: #fff;
            color: var(--assignment-text);
            font: inherit;
            font-size: 13px;
        }

        .assignment-control {
            height: 42px;
            padding: 0 12px;
        }

        .assignment-textarea {
            min-height: 94px;
            padding: 11px 12px;
            resize: vertical;
        }

        .assignment-control:focus,
        .assignment-textarea:focus {
            border-color: var(--assignment-accent);
            box-shadow: 0 0 0 3px rgba(7, 147, 164, .12);
            outline: none;
        }

        .assignment-filter-actions {
            display: flex;
            gap: 8px;
        }

        .assignment-alert {
            margin-bottom: 16px;
            padding: 13px 15px;
            border: 1px solid var(--assignment-border);
            border-radius: 11px;
            font-size: 13px;
            line-height: 1.5;
        }

        .assignment-alert--success {
            border-color: #cce9dc;
            background: var(--assignment-success-soft);
            color: var(--assignment-success);
        }

        .assignment-alert--danger {
            border-color: #f0c0c7;
            background: var(--assignment-danger-soft);
            color: var(--assignment-danger);
        }

        .assignment-alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .assignment-list {
            display: grid;
            gap: 12px;
        }

        .assignment-card {
            overflow: hidden;
            border: 1px solid var(--assignment-border);
            border-radius: 14px;
            background: #fff;
        }

        .assignment-card__header {
            justify-content: space-between;
            gap: 14px;
            padding: 15px 17px;
            border-bottom: 1px solid #edf1f5;
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .assignment-patient {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 11px;
        }

        .assignment-patient__avatar {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: var(--assignment-accent-soft);
            color: var(--assignment-accent-dark);
            font-size: 13px;
            font-weight: 850;
        }

        .assignment-patient__name {
            overflow: hidden;
            color: var(--assignment-text);
            font-size: 15px;
            font-weight: 800;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .assignment-patient__meta {
            margin-top: 3px;
            color: var(--assignment-muted);
            font-size: 11px;
        }

        .assignment-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 7px;
        }

        .assignment-badge {
            display: inline-flex;
            min-height: 26px;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            background: #f1f4f7;
            color: #657184;
            font-size: 11px;
            font-weight: 750;
        }

        .assignment-badge--progress {
            background: var(--assignment-warning-soft);
            color: var(--assignment-warning);
        }

        .assignment-badge--success {
            background: var(--assignment-success-soft);
            color: var(--assignment-success);
        }

        .assignment-badge--danger {
            background: var(--assignment-danger-soft);
            color: var(--assignment-danger);
        }

        .assignment-card__body {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) repeat(3, minmax(135px, .65fr));
            gap: 16px;
            padding: 17px;
        }

        .assignment-info {
            min-width: 0;
        }

        .assignment-info__label {
            margin-bottom: 5px;
            color: #8993a2;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .assignment-info__value {
            color: #263143;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .assignment-info__subvalue {
            margin-top: 4px;
            color: var(--assignment-muted);
            font-size: 11px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .assignment-card__footer {
            justify-content: space-between;
            gap: 12px;
            padding: 12px 17px;
            border-top: 1px solid #edf1f5;
            background: #fbfcfd;
        }

        .assignment-card__hint {
            color: var(--assignment-muted);
            font-size: 11px;
        }

        .assignment-card__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .assignment-empty {
            padding: 42px 20px;
            border: 1px dashed #ccd6df;
            border-radius: 14px;
            background: #fbfcfd;
            color: var(--assignment-muted);
            text-align: center;
        }

        .assignment-empty h3 {
            margin: 0 0 7px;
            color: var(--assignment-text);
            font-size: 16px;
        }

        .assignment-pagination {
            margin-top: 18px;
        }

        .assignment-modal {
            position: fixed;
            z-index: 3000;
            inset: 0;
            display: none;
            overflow-y: auto;
            padding: 30px 16px;
            background: rgba(15, 23, 42, .58);
        }

        .assignment-modal.is-open {
            display: block;
        }

        .assignment-modal__dialog {
            width: min(820px, 100%);
            margin: 0 auto;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
        }

        .assignment-modal__header {
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--assignment-border);
        }

        .assignment-modal__title {
            margin: 0;
            color: var(--assignment-text);
            font-size: 19px;
            font-weight: 800;
        }

        .assignment-modal__close {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 0;
            border-radius: 10px;
            background: #f1f4f7;
            color: #657184;
            font-size: 22px;
            cursor: pointer;
        }

        .assignment-modal__body {
            padding: 20px;
        }

        .assignment-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .assignment-field--full {
            grid-column: 1 / -1;
        }

        .assignment-help,
        .assignment-error {
            display: block;
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.4;
        }

        .assignment-help {
            color: #8792a2;
        }

        .assignment-error {
            color: var(--assignment-danger);
        }

        .assignment-search {
            position: relative;
        }

        .assignment-search-results {
            position: absolute;
            z-index: 20;
            top: calc(100% + 5px);
            right: 0;
            left: 0;
            display: none;
            overflow-y: auto;
            max-height: 245px;
            border: 1px solid #d9e1e8;
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(32, 43, 61, .13);
        }

        .assignment-search-results.is-open {
            display: block;
        }

        .assignment-search-result,
        .assignment-search-message {
            width: 100%;
            padding: 10px 12px;
            border: 0;
            border-bottom: 1px solid #edf1f5;
            background: #fff;
            color: var(--assignment-text);
            font: inherit;
            font-size: 12px;
            line-height: 1.4;
            text-align: left;
        }

        button.assignment-search-result {
            cursor: pointer;
        }

        button.assignment-search-result:hover,
        button.assignment-search-result:focus-visible {
            background: var(--assignment-accent-soft);
            outline: none;
        }

        .assignment-search-result:last-child,
        .assignment-search-message:last-child {
            border-bottom: 0;
        }

        .assignment-search-result__meta {
            display: block;
            margin-top: 2px;
            color: var(--assignment-muted);
            font-size: 10px;
        }

        .assignment-selected-list {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            min-height: 40px;
            margin-top: 9px;
            padding: 8px;
            border: 1px dashed #cfd8e2;
            border-radius: 10px;
            background: #fbfcfd;
        }

        .assignment-selected-list:empty::before {
            content: 'Показатели пока не выбраны';
            align-self: center;
            color: #929ba8;
            font-size: 11px;
        }

        .assignment-selected-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 8px;
            border-radius: 999px;
            background: var(--assignment-accent-soft);
            color: var(--assignment-accent-dark);
            font-size: 11px;
            font-weight: 700;
        }

        .assignment-selected-item button {
            display: grid;
            width: 18px;
            height: 18px;
            place-items: center;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: rgba(5, 119, 134, .12);
            color: var(--assignment-accent-dark);
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
        }

        .assignment-modal__footer {
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 9px;
            padding: 15px 20px;
            border-top: 1px solid var(--assignment-border);
            background: #fbfcfd;
        }

        body.assignment-modal-open {
            overflow: hidden;
        }

        @media (max-width: 980px) {
            .assignment-filter-grid,
            .assignment-card__body {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .assignment-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 680px) {
            .assignment-toolbar,
            .assignment-card__header,
            .assignment-card__footer {
                align-items: stretch;
                flex-direction: column;
            }

            .assignment-filter-grid,
            .assignment-card__body,
            .assignment-form-grid {
                grid-template-columns: 1fr;
            }

            .assignment-field--full,
            .assignment-filter-actions {
                grid-column: auto;
            }

            .assignment-filter-actions,
            .assignment-card__actions {
                justify-content: stretch;
            }

            .assignment-filter-actions .assignment-button,
            .assignment-card__actions .assignment-button,
            .assignment-toolbar > .assignment-button {
                width: 100%;
            }

            .assignment-badges {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('analysis-content')
    <div class="lab-page lab-assignments-page">
        <div class="lab-container">
            <div class="assignment-toolbar">
                <div>
                    <h1 class="assignment-toolbar__title">Назначения анализов</h1>
                    <div class="assignment-toolbar__subtitle">
                        Создание лабораторных направлений и контроль сроков выполнения.
                    </div>
                </div>

                <button
                    class="assignment-button assignment-button--primary"
                    id="openAssignmentModal"
                    type="button"
                >
                    + Новое направление
                </button>
            </div>

            @if(session('success'))
                <div class="assignment-alert assignment-alert--success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->assignment->any())
                <div class="assignment-alert assignment-alert--danger">
                    <strong>Направление не сохранено.</strong>
                    <ul>
                        @foreach($errors->assignment->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="assignment-panel">
                <div class="assignment-panel__body">
                    <form
                        class="assignment-filter-grid"
                        method="GET"
                        action="{{ route('doctors.analyses.assignments') }}"
                    >
                        <div class="assignment-field">
                            <label class="assignment-label" for="assignmentSearch">
                                Поиск
                            </label>
                            <input
                                class="assignment-control"
                                id="assignmentSearch"
                                name="search"
                                type="search"
                                value="{{ request('search') }}"
                                placeholder="Пациент или название направления"
                            >
                        </div>

                        <div class="assignment-field">
                            <label class="assignment-label" for="assignmentStage">
                                Этап
                            </label>
                            <select class="assignment-control" id="assignmentStage" name="stage">
                                <option value="">Все этапы</option>
                                <option value="assigned" @selected(request('stage') === 'assigned')>Назначено</option>
                                <option value="processing" @selected(request('stage') === 'processing')>Выполняется</option>
                                <option value="ready" @selected(request('stage') === 'ready')>Готово</option>
                                <option value="overdue" @selected(request('stage') === 'overdue')>Просрочено</option>
                            </select>
                        </div>

                        <div class="assignment-field">
                            <label class="assignment-label" for="assignmentPeriod">
                                Создано
                            </label>
                            <select class="assignment-control" id="assignmentPeriod" name="period">
                                <option value="">За всё время</option>
                                <option value="today" @selected(request('period') === 'today')>Сегодня</option>
                                <option value="week" @selected(request('period') === 'week')>За 7 дней</option>
                                <option value="month" @selected(request('period') === 'month')>За 30 дней</option>
                            </select>
                        </div>

                        <div class="assignment-filter-actions">
                            <button class="assignment-button assignment-button--primary" type="submit">
                                Применить
                            </button>
                            <a class="assignment-button" href="{{ route('doctors.analyses.assignments') }}">
                                Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <div class="assignment-list">
                @forelse($rows as $row)
                    <article class="assignment-card">
                        <div class="assignment-card__header">
                            <div class="assignment-patient">
                                <div class="assignment-patient__avatar">
                                    {{ data_get($row, 'initials', 'П') }}
                                </div>
                                <div style="min-width: 0">
                                    <div class="assignment-patient__name">
                                        {{ data_get($row, 'patient_name', 'Пациент не указан') }}
                                    </div>
                                    <div class="assignment-patient__meta">
                                        {{ data_get($row, 'patient_birth_date', 'Дата рождения не указана') }}
                                    </div>
                                </div>
                            </div>

                            <div class="assignment-badges">
                                <span class="assignment-badge assignment-badge--{{ data_get($row, 'status_class', 'progress') }}">
                                    {{ data_get($row, 'stage_label', 'Назначено') }}
                                </span>
                                <span class="assignment-badge">
                                    {{ data_get($row, 'priority_label', 'Обычно') }}
                                </span>
                            </div>
                        </div>

                        <div class="assignment-card__body">
                            <div class="assignment-info">
                                <div class="assignment-info__label">Исследование</div>
                                <div class="assignment-info__value">
                                    {{ data_get($row, 'research_name', 'Лабораторное направление') }}
                                </div>
                                <div class="assignment-info__subvalue">
                                    {{ data_get($row, 'material', 'Биоматериал не указан') }}
                                </div>
                                @if(data_get($row, 'parameter_preview'))
                                    <div class="assignment-info__subvalue">
                                        {{ data_get($row, 'parameter_preview') }}
                                        @if((int) data_get($row, 'parameter_more_count', 0) > 0)
                                            и ещё {{ data_get($row, 'parameter_more_count') }}
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="assignment-info">
                                <div class="assignment-info__label">Назначено</div>
                                <div class="assignment-info__value">
                                    {{ data_get($row, 'assigned_date', '—') }}
                                </div>
                                <div class="assignment-info__subvalue">
                                    {{ data_get($row, 'assigned_by', 'Врач не указан') }}
                                </div>
                            </div>

                            <div class="assignment-info">
                                <div class="assignment-info__label">Плановая дата</div>
                                <div class="assignment-info__value">
                                    {{ data_get($row, 'due_date', '—') }}
                                </div>
                                <div class="assignment-info__subvalue">
                                    {{ data_get($row, 'due_hint', 'Срок не указан') }}
                                </div>
                            </div>

                            <div class="assignment-info">
                                <div class="assignment-info__label">Номер</div>
                                <div class="assignment-info__value">
                                    № {{ data_get($row, 'id', '—') }}
                                </div>
                                <div class="assignment-info__subvalue">
                                    Этап {{ data_get($row, 'stage_index', 1) }} из 4
                                </div>
                            </div>
                        </div>

                        <div class="assignment-card__footer">
                            <div class="assignment-card__hint">
                                После получения результатов направление появится в медицинской карте пациента.
                            </div>
                            <div class="assignment-card__actions">
                                @if(data_get($row, 'medical_card_url'))
                                    <a class="assignment-button" href="{{ data_get($row, 'medical_card_url') }}">
                                        Медицинская карта
                                    </a>
                                @endif
                                @if(data_get($row, 'print_url'))
                                    <a
                                        class="assignment-button assignment-button--soft"
                                        href="{{ data_get($row, 'print_url') }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Печать направления
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="assignment-empty">
                        <h3>Назначения не найдены</h3>
                        <div>Измените фильтры или создайте новое лабораторное направление.</div>
                    </div>
                @endforelse
            </div>

            @if($assignments->hasPages())
                <div class="assignment-pagination">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>

    <div
        class="assignment-modal {{ $assignmentModalOpen ? 'is-open' : '' }}"
        id="assignmentModal"
        aria-hidden="{{ $assignmentModalOpen ? 'false' : 'true' }}"
    >
        <div class="assignment-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="assignmentModalTitle">
            <form id="assignmentCreateForm" method="POST" action="{{ $assignmentStoreUrl }}">
                @csrf

                <div class="assignment-modal__header">
                    <div>
                        <h2 class="assignment-modal__title" id="assignmentModalTitle">
                            Новое лабораторное направление
                        </h2>
                        <div class="assignment-help">
                            Выберите пациента, биоматериал и хотя бы один показатель.
                        </div>
                    </div>
                    <button class="assignment-modal__close" id="closeAssignmentModal" type="button" aria-label="Закрыть">
                        ×
                    </button>
                </div>

                <div class="assignment-modal__body">
                    <div class="assignment-form-grid">
                        <div class="assignment-field assignment-field--full">
                            <label class="assignment-label" for="assignmentPatientSearch">
                                Пациент <span aria-hidden="true">*</span>
                            </label>
                            <div class="assignment-search">
                                <input
                                    class="assignment-control"
                                    id="assignmentPatientSearch"
                                    type="search"
                                    autocomplete="off"
                                    value="{{ old('patient_id') ? 'Пациент #' . old('patient_id') : '' }}"
                                    placeholder="Начните вводить ФИО пациента"
                                >
                                <input
                                    id="assignmentPatientId"
                                    name="patient_id"
                                    type="hidden"
                                    value="{{ old('patient_id') }}"
                                >
                                <div class="assignment-search-results" id="assignmentPatientResults"></div>
                            </div>
                            @error('patient_id', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="assignment-field">
                            <label class="assignment-label" for="assignmentSampleType">
                                Биоматериал <span aria-hidden="true">*</span>
                            </label>
                            <select class="assignment-control" id="assignmentSampleType" name="sample_type" required>
                                <option value="">Выберите биоматериал</option>
                                @foreach($sampleTypes as $sampleType)
                                    <option value="{{ $sampleType }}" @selected(old('sample_type') === $sampleType)>
                                        {{ $sampleType }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sample_type', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="assignment-field">
                            <label class="assignment-label" for="assignmentPlannedAt">
                                Планируемая дата сдачи <span aria-hidden="true">*</span>
                            </label>
                            <input
                                class="assignment-control"
                                id="assignmentPlannedAt"
                                name="planned_at"
                                type="date"
                                value="{{ old('planned_at', $defaultPlannedAt) }}"
                                required
                            >
                            @error('planned_at', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-field--full">
                            <label class="assignment-label" for="assignmentParameterSearch">
                                Лабораторные показатели <span aria-hidden="true">*</span>
                            </label>
                            <div class="assignment-search">
                                <input
                                    class="assignment-control"
                                    id="assignmentParameterSearch"
                                    type="search"
                                    autocomplete="off"
                                    placeholder="Введите название показателя"
                                    @disabled(!old('sample_type'))
                                >
                                <div class="assignment-search-results" id="assignmentParameterResults"></div>
                            </div>

                            <div class="assignment-selected-list" id="assignmentSelectedParameters">
                                @foreach($oldSelectedParameters as $parameter)
                                    <span
                                        class="assignment-selected-item"
                                        data-parameter-id="{{ $parameter->id }}"
                                        data-parameter-name="{{ $parameter->name }}"
                                        data-parameter-unit="{{ $parameter->unit }}"
                                    >
                                        <span>
                                            {{ $parameter->name }}
                                            @if(trim((string) $parameter->unit) !== '')
                                                — {{ $parameter->unit }}
                                            @endif
                                        </span>
                                        <input name="parameter_ids[]" type="hidden" value="{{ $parameter->id }}">
                                        <button type="button" aria-label="Удалить показатель">×</button>
                                    </span>
                                @endforeach
                            </div>
                            <span class="assignment-help">
                                Список зависит от выбранного биоматериала.
                            </span>
                            @error('parameter_ids', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-field--full">
                            <label class="assignment-label" for="assignmentLaboratory">
                                Название направления
                            </label>
                            <input
                                class="assignment-control"
                                id="assignmentLaboratory"
                                name="laboratory"
                                type="text"
                                maxlength="255"
                                value="{{ old('laboratory') }}"
                                placeholder="Будет сформировано автоматически, если оставить пустым"
                            >
                            @error('laboratory', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="assignment-field">
                            <label class="assignment-label" for="assignmentPriority">
                                Приоритет
                            </label>
                            <select class="assignment-control" id="assignmentPriority" name="priority" required>
                                <option value="normal" @selected(old('priority', 'normal') === 'normal')>Обычный</option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>Срочно</option>
                            </select>
                            @error('priority', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-field--full">
                            <label class="assignment-label" for="assignmentComment">
                                Комментарий
                            </label>
                            <textarea
                                class="assignment-textarea"
                                id="assignmentComment"
                                name="comment"
                                maxlength="2000"
                                placeholder="Подготовка, особенности забора материала или другая информация"
                            >{{ old('comment') }}</textarea>
                            @error('comment', 'assignment')
                                <span class="assignment-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="assignment-modal__footer">
                    <button class="assignment-button" id="cancelAssignmentModal" type="button">
                        Отмена
                    </button>
                    <button class="assignment-button assignment-button--soft" name="submit_action" type="submit" value="save_print">
                        Сохранить и распечатать
                    </button>
                    <button class="assignment-button assignment-button--primary" name="submit_action" type="submit" value="save">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('assignmentModal');
            const openButton = document.getElementById('openAssignmentModal');
            const closeButton = document.getElementById('closeAssignmentModal');
            const cancelButton = document.getElementById('cancelAssignmentModal');
            const createForm = document.getElementById('assignmentCreateForm');

            const patientInput = document.getElementById('assignmentPatientSearch');
            const patientIdInput = document.getElementById('assignmentPatientId');
            const patientResults = document.getElementById('assignmentPatientResults');

            const sampleTypeSelect = document.getElementById('assignmentSampleType');
            const parameterInput = document.getElementById('assignmentParameterSearch');
            const parameterResults = document.getElementById('assignmentParameterResults');
            const selectedParameters = document.getElementById('assignmentSelectedParameters');

            const patientSearchUrl = @json($patientSearchUrl);
            const parameterSearchUrl = @json($parameterSearchUrl);

            let patientRequestNumber = 0;
            let parameterRequestNumber = 0;
            let patientTimer = null;
            let parameterTimer = null;

            function openModal() {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('assignment-modal-open');
                window.setTimeout(function () {
                    patientInput.focus();
                }, 0);
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('assignment-modal-open');
                hideResults(patientResults);
                hideResults(parameterResults);
            }

            function hideResults(container) {
                container.classList.remove('is-open');
                container.replaceChildren();
            }

            function showMessage(container, message) {
                const row = document.createElement('div');
                row.className = 'assignment-search-message';
                row.textContent = message;
                container.replaceChildren(row);
                container.classList.add('is-open');
            }

            function debounce(callback, delay, timerName) {
                if (timerName === 'patient') {
                    window.clearTimeout(patientTimer);
                    patientTimer = window.setTimeout(callback, delay);
                    return;
                }

                window.clearTimeout(parameterTimer);
                parameterTimer = window.setTimeout(callback, delay);
            }

            async function fetchJson(url, params) {
                const query = new URLSearchParams(params);
                const response = await fetch(url + '?' + query.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                return response.json();
            }

            function normalizeItems(payload) {
                if (Array.isArray(payload)) {
                    return payload;
                }

                return payload && Array.isArray(payload.results)
                    ? payload.results
                    : [];
            }

            function renderPatientResults(items) {
                patientResults.replaceChildren();

                if (items.length === 0) {
                    showMessage(patientResults, 'Пациенты не найдены.');
                    return;
                }

                items.forEach(function (item) {
                    const button = document.createElement('button');
                    button.className = 'assignment-search-result';
                    button.type = 'button';
                    button.textContent = item.text || ('Пациент #' + item.id);

                    if (item.birth_at) {
                        const meta = document.createElement('span');
                        meta.className = 'assignment-search-result__meta';
                        meta.textContent = 'Дата рождения: ' + item.birth_at;
                        button.appendChild(meta);
                    }

                    button.addEventListener('click', function () {
                        patientIdInput.value = item.id;
                        patientInput.value = item.text || ('Пациент #' + item.id);
                        hideResults(patientResults);
                    });

                    patientResults.appendChild(button);
                });

                patientResults.classList.add('is-open');
            }

            function selectedParameterIds() {
                return new Set(
                    Array.from(
                        selectedParameters.querySelectorAll('[data-parameter-id]')
                    ).map(function (item) {
                        return String(item.dataset.parameterId);
                    })
                );
            }

            function addParameter(item) {
                if (!item || item.id === undefined || item.id === null) {
                    return;
                }

                const id = String(item.id);

                if (selectedParameterIds().has(id)) {
                    hideResults(parameterResults);
                    return;
                }

                const chip = document.createElement('span');
                chip.className = 'assignment-selected-item';
                chip.dataset.parameterId = id;
                chip.dataset.parameterName = item.name || item.text || '';
                chip.dataset.parameterUnit = item.unit || '';

                const label = document.createElement('span');
                label.textContent = (item.name || item.text || ('Показатель #' + id))
                    + (item.unit ? ' — ' + item.unit : '');

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'parameter_ids[]';
                hidden.value = id;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Удалить показатель');
                remove.textContent = '×';
                remove.addEventListener('click', function () {
                    chip.remove();
                });

                chip.append(label, hidden, remove);
                selectedParameters.appendChild(chip);
                parameterInput.value = '';
                hideResults(parameterResults);
            }

            function bindExistingParameterRemoveButtons() {
                selectedParameters
                    .querySelectorAll('.assignment-selected-item button')
                    .forEach(function (button) {
                        button.addEventListener('click', function () {
                            button.closest('.assignment-selected-item')?.remove();
                        });
                    });
            }

            function renderParameterResults(items) {
                parameterResults.replaceChildren();
                const selectedIds = selectedParameterIds();
                const available = items.filter(function (item) {
                    return !selectedIds.has(String(item.id));
                });

                if (available.length === 0) {
                    showMessage(parameterResults, 'Подходящие показатели не найдены.');
                    return;
                }

                available.forEach(function (item) {
                    const button = document.createElement('button');
                    button.className = 'assignment-search-result';
                    button.type = 'button';
                    button.textContent = item.name || item.text || ('Показатель #' + item.id);

                    const details = [item.group, item.unit].filter(Boolean).join(' · ');
                    if (details) {
                        const meta = document.createElement('span');
                        meta.className = 'assignment-search-result__meta';
                        meta.textContent = details;
                        button.appendChild(meta);
                    }

                    button.addEventListener('click', function () {
                        addParameter(item);
                    });

                    parameterResults.appendChild(button);
                });

                parameterResults.classList.add('is-open');
            }

            async function searchPatients() {
                const requestNumber = ++patientRequestNumber;
                showMessage(patientResults, 'Поиск…');

                try {
                    const payload = await fetchJson(patientSearchUrl, {
                        q: patientInput.value.trim()
                    });

                    if (requestNumber !== patientRequestNumber) {
                        return;
                    }

                    renderPatientResults(normalizeItems(payload));
                } catch (error) {
                    console.error('Ошибка поиска пациентов:', error);
                    showMessage(patientResults, 'Не удалось загрузить пациентов.');
                }
            }

            async function searchParameters() {
                const sampleType = sampleTypeSelect.value;

                if (!sampleType) {
                    showMessage(parameterResults, 'Сначала выберите биоматериал.');
                    return;
                }

                const requestNumber = ++parameterRequestNumber;
                showMessage(parameterResults, 'Поиск…');

                try {
                    const payload = await fetchJson(parameterSearchUrl, {
                        q: parameterInput.value.trim(),
                        sample_type: sampleType
                    });

                    if (requestNumber !== parameterRequestNumber) {
                        return;
                    }

                    renderParameterResults(normalizeItems(payload));
                } catch (error) {
                    console.error('Ошибка поиска показателей:', error);
                    showMessage(parameterResults, 'Не удалось загрузить показатели.');
                }
            }

            openButton.addEventListener('click', openModal);
            closeButton.addEventListener('click', closeModal);
            cancelButton.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('#assignmentPatientSearch, #assignmentPatientResults')) {
                    hideResults(patientResults);
                }

                if (!event.target.closest('#assignmentParameterSearch, #assignmentParameterResults')) {
                    hideResults(parameterResults);
                }
            });

            patientInput.addEventListener('input', function () {
                patientIdInput.value = '';
                debounce(searchPatients, 250, 'patient');
            });

            patientInput.addEventListener('focus', function () {
                debounce(searchPatients, 0, 'patient');
            });

            sampleTypeSelect.addEventListener('change', function () {
                parameterInput.disabled = !sampleTypeSelect.value;
                parameterInput.value = '';
                selectedParameters.replaceChildren();
                hideResults(parameterResults);

                if (sampleTypeSelect.value) {
                    parameterInput.focus();
                }
            });

            parameterInput.addEventListener('input', function () {
                debounce(searchParameters, 250, 'parameter');
            });

            parameterInput.addEventListener('focus', function () {
                debounce(searchParameters, 0, 'parameter');
            });

            createForm.addEventListener('submit', function (event) {
                if (!patientIdInput.value) {
                    event.preventDefault();
                    patientInput.focus();
                    showMessage(patientResults, 'Выберите пациента из найденного списка.');
                    return;
                }

                if (selectedParameterIds().size === 0) {
                    event.preventDefault();
                    parameterInput.focus();
                    showMessage(parameterResults, 'Добавьте хотя бы один показатель.');
                }
            });

            bindExistingParameterRemoveButtons();

            if (modal.classList.contains('is-open')) {
                document.body.classList.add('assignment-modal-open');
            }
        });
    </script>
@endpush
