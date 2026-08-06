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
                        id="createLabAssignmentButton"
                        class="lab-button lab-button--primary"
                        type="button"
                        aria-controls="createLabAssignmentModal"
                        aria-expanded="false"
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
                            {{ $assignments->total() }} записей
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
                                            @foreach(['Назначено', 'Материал принят', 'Выполняется', 'Готово'] as $index => $label)
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
                                            <a
                                                class="lab-action"
                                                href="{{ $row['medical_card_url'] }}"
                                            >
                                                Медкарта
                                            </a>

                                            <a
                                                class="lab-action"
                                                href="{{ $row['print_url'] }}"
                                                target="_blank"
                                                rel="noopener"
                                            >
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

    <div
        class="assignment-modal"
        id="createLabAssignmentModal"
        role="dialog"
        aria-label="Новое лабораторное направление"
        aria-hidden="true"
        data-open="{{ $assignmentModalOpen ? '1' : '0' }}"
        data-patient-search-url="{{ $patientSearchUrl }}"
        data-parameter-search-url="{{ $parameterSearchUrl }}"
    >
        <div class="modal-dialog">
            <form
                class="modal-content"
                method="POST"
                action="{{ $assignmentStoreUrl }}"
            >
                @csrf

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Новое лабораторное направление</h2>
                        <div class="assignment-modal__subtitle">
                            Выберите пациента, биоматериал и показатели для печати направления.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-assignment-close
                        aria-label="Закрыть"
                    ></button>
                </div>

                <div class="modal-body">
                    @if($errors->assignment->any())
                        <div class="alert alert-danger mb-4">
                            <strong>Не удалось сохранить направление.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->assignment->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="assignment-form-grid">
                        <div class="assignment-field assignment-col-8">
                            <label
                                class="assignment-label assignment-required"
                                for="assignmentPatientSearch"
                            >
                                Пациент
                            </label>

                            <div class="assignment-autocomplete">
                                <input
                                    id="assignmentPatientSearch"
                                    class="assignment-control"
                                    type="search"
                                    name="patient_label"
                                    value="{{ old('patient_label') }}"
                                    autocomplete="off"
                                    placeholder="Начните вводить ФИО пациента"
                                >

                                <input
                                    id="assignmentPatientId"
                                    type="hidden"
                                    name="patient_id"
                                    value="{{ old('patient_id') }}"
                                >

                                <div
                                    id="assignmentPatientMenu"
                                    class="assignment-autocomplete__menu"
                                ></div>
                            </div>

                            <div class="assignment-hint">
                                Введите не менее двух символов и выберите пациента из списка.
                            </div>

                            @error('patient_id', 'assignment')
                                <div class="assignment-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-col-4">
                            <label
                                class="assignment-label assignment-required"
                                for="assignmentSampleType"
                            >
                                Биоматериал
                            </label>

                            <select
                                id="assignmentSampleType"
                                class="assignment-control"
                                name="sample_type"
                            >
                                <option value="">Выберите материал</option>
                                @foreach($sampleTypes as $sampleType)
                                    <option
                                        value="{{ $sampleType }}"
                                        @selected(old('sample_type') === $sampleType)
                                    >
                                        {{ mb_strtoupper(mb_substr($sampleType, 0, 1)) . mb_substr($sampleType, 1) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('sample_type', 'assignment')
                                <div class="assignment-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-col-12">
                            <label
                                class="assignment-label assignment-required"
                                for="assignmentParameterSearch"
                            >
                                Состав направления
                            </label>

                            <div class="assignment-autocomplete">
                                <input
                                    id="assignmentParameterSearch"
                                    class="assignment-control"
                                    type="search"
                                    autocomplete="off"
                                    placeholder="Сначала выберите биоматериал"
                                    disabled
                                >

                                <div
                                    id="assignmentParameterMenu"
                                    class="assignment-autocomplete__menu"
                                ></div>
                            </div>

                            <div class="assignment-selected">
                                <div class="assignment-selected__head">
                                    <div class="assignment-selected__title">
                                        Выбранные показатели
                                    </div>
                                    <div
                                        id="assignmentSelectedCount"
                                        class="assignment-selected__count"
                                    >
                                        {{ $oldSelectedParameters->count() }}
                                    </div>
                                </div>

                                <div
                                    id="assignmentSelectedList"
                                    class="assignment-selected__list"
                                >
                                    @foreach($oldSelectedParameters as $parameter)
                                        <div
                                            class="assignment-selected__item"
                                            data-selected-parameter
                                            data-id="{{ $parameter->id }}"
                                            data-name="{{ $parameter->name }}"
                                            data-unit="{{ $parameter->unit }}"
                                            data-group="{{ $parameter->group }}"
                                            data-sample-type="{{ $parameter->sample_type }}"
                                        >
                                            <input
                                                type="hidden"
                                                name="parameter_ids[]"
                                                value="{{ $parameter->id }}"
                                            >
                                            <span>{{ $parameter->name }}</span>
                                            <button
                                                class="assignment-selected__remove"
                                                type="button"
                                                data-remove-parameter
                                                aria-label="Удалить показатель"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <div
                                    id="assignmentSelectedEmpty"
                                    class="assignment-selected__empty {{ $oldSelectedParameters->isNotEmpty() ? 'd-none' : '' }}"
                                >
                                    Показатели ещё не добавлены.
                                </div>
                            </div>

                            @error('parameter_ids', 'assignment')
                                <div class="assignment-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-col-6">
                            <label class="assignment-label" for="assignmentTitle">
                                Название направления
                            </label>
                            <input
                                id="assignmentTitle"
                                class="assignment-control"
                                type="text"
                                name="laboratory"
                                value="{{ old('laboratory') }}"
                                maxlength="255"
                                placeholder="Сформируется автоматически"
                            >
                            <div class="assignment-hint">
                                Название можно изменить вручную перед сохранением.
                            </div>
                        </div>

                        <div class="assignment-field assignment-col-6">
                            <label
                                class="assignment-label assignment-required"
                                for="assignmentPlannedAt"
                            >
                                Планируемая дата сдачи
                            </label>
                            <input
                                id="assignmentPlannedAt"
                                class="assignment-control"
                                type="date"
                                name="planned_at"
                                value="{{ old('planned_at', $defaultPlannedAt) }}"
                            >

                            @error('planned_at', 'assignment')
                                <div class="assignment-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-field assignment-col-4">
                            <label
                                class="assignment-label assignment-required"
                                for="assignmentPriority"
                            >
                                Приоритет
                            </label>
                            <select
                                id="assignmentPriority"
                                class="assignment-control"
                                name="priority"
                            >
                                <option value="normal" @selected(old('priority', 'normal') === 'normal')>
                                    Обычный
                                </option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>
                                    Срочный
                                </option>
                            </select>
                        </div>

                        <div class="assignment-field assignment-col-8">
                            <label class="assignment-label" for="assignmentComment">
                                Клиническая информация / комментарий
                            </label>
                            <textarea
                                id="assignmentComment"
                                class="assignment-control"
                                name="comment"
                                maxlength="2000"
                                placeholder="Укажите диагноз, жалобы или важные сведения для лаборатории"
                            >{{ old('comment') }}</textarea>
                        </div>

                        <div class="assignment-col-12">
                            <div class="assignment-modal__note">
                                <i class="bi bi-database-check"></i>
                                <div>
                                    После сохранения направление попадёт в таблицу
                                    <strong>lab_researches</strong>. Список показателей сохраняется
                                    в поле <strong>parameters</strong>, поэтому направление можно
                                    распечатать до получения результатов.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="lab-button lab-button--secondary"
                        data-bs-dismiss="modal"
                    >
                        Отмена
                    </button>

                    <button
                        type="submit"
                        name="submit_action"
                        value="save"
                        class="lab-button lab-button--secondary"
                    >
                        <i class="bi bi-check2-circle"></i>
                        Сохранить
                    </button>

                    <button
                        type="submit"
                        name="submit_action"
                        value="save_print"
                        class="lab-button lab-button--primary"
                    >
                        <i class="bi bi-printer"></i>
                        Сохранить и печатать
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('createLabAssignmentModal');
            const modalToggleButton = document.getElementById('createLabAssignmentButton');
            const modalCloseButton = modalElement?.querySelector('[data-assignment-close]');

            if (!modalElement || !modalToggleButton) {
                return;
            }

            const patientInput = document.getElementById('assignmentPatientSearch');
            const patientIdInput = document.getElementById('assignmentPatientId');
            const patientMenu = document.getElementById('assignmentPatientMenu');
            const sampleType = document.getElementById('assignmentSampleType');
            const parameterInput = document.getElementById('assignmentParameterSearch');
            const parameterMenu = document.getElementById('assignmentParameterMenu');
            const selectedList = document.getElementById('assignmentSelectedList');
            const selectedEmpty = document.getElementById('assignmentSelectedEmpty');
            const selectedCount = document.getElementById('assignmentSelectedCount');
            const titleInput = document.getElementById('assignmentTitle');

            const patientSearchUrl = modalElement.dataset.patientSearchUrl || '';
            const parameterSearchUrl = modalElement.dataset.parameterSearchUrl || '';

            let patientTimer = null;
            let parameterTimer = null;
            let patientController = null;
            let parameterController = null;
            let automaticTitle = titleInput.value.trim() === '';

            function positionAssignmentModal() {
                if (!modalElement.classList.contains('is-open')) {
                    return;
                }

                const buttonRect = modalToggleButton.getBoundingClientRect();
                const viewportPadding = 16;
                const modalWidth = modalElement.offsetWidth;
                const modalHeight = modalElement.offsetHeight;

                let left = buttonRect.right - modalWidth;
                left = Math.max(
                    viewportPadding,
                    Math.min(left, window.innerWidth - modalWidth - viewportPadding)
                );

                let top = buttonRect.bottom + 12;

                if (top + modalHeight > window.innerHeight - viewportPadding) {
                    top = Math.max(
                        viewportPadding,
                        buttonRect.top - modalHeight - 12
                    );
                }

                modalElement.style.left = left + 'px';
                modalElement.style.top = top + 'px';
            }

            function openAssignmentModal() {
                modalElement.classList.add('is-open');
                modalElement.setAttribute('aria-hidden', 'false');
                modalToggleButton.setAttribute('aria-expanded', 'true');

                window.requestAnimationFrame(function () {
                    positionAssignmentModal();
                    patientInput.focus();
                });
            }

            function closeAssignmentModal() {
                closeMenu(patientMenu);
                closeMenu(parameterMenu);
                modalElement.classList.remove('is-open');
                modalElement.setAttribute('aria-hidden', 'true');
                modalToggleButton.setAttribute('aria-expanded', 'false');
            }

            function escapeText(value) {
                return String(value ?? '').trim();
            }

            function normalizeItems(payload) {
                if (Array.isArray(payload)) {
                    return payload;
                }

                const candidates = [
                    payload?.results,
                    payload?.items,
                    payload?.data,
                    payload?.data?.results,
                    payload?.data?.items
                ];

                return candidates.find(Array.isArray) || [];
            }

            function openMenu(menu) {
                menu.classList.add('is-open');
            }

            function closeMenu(menu) {
                menu.classList.remove('is-open');
                menu.replaceChildren();
            }

            function showState(menu, text) {
                const state = document.createElement('div');
                state.className = 'assignment-autocomplete__state';
                state.textContent = text;
                menu.replaceChildren(state);
                openMenu(menu);
            }

            function buildSearchUrl(baseUrl, params) {
                const url = new URL(baseUrl, window.location.origin);

                Object.entries(params).forEach(function ([key, value]) {
                    if (value !== null && value !== undefined && value !== '') {
                        url.searchParams.set(key, value);
                    }
                });

                return url.toString();
            }

            function selectedItems() {
                return Array.from(
                    selectedList.querySelectorAll('[data-selected-parameter]')
                );
            }

            function updateSelectedState() {
                const items = selectedItems();
                selectedCount.textContent = String(items.length);
                selectedEmpty.classList.toggle('d-none', items.length > 0);

                if (!automaticTitle) {
                    return;
                }

                const names = items
                    .map(function (item) {
                        return item.dataset.name || '';
                    })
                    .filter(Boolean);

                if (names.length === 0) {
                    titleInput.value = '';
                } else if (names.length === 1) {
                    titleInput.value = names[0];
                } else {
                    titleInput.value = names[0] + ' и ещё ' + (names.length - 1);
                }
            }

            function hasSelectedParameter(id) {
                return selectedItems().some(function (item) {
                    return String(item.dataset.id) === String(id);
                });
            }

            function addParameter(item) {
                const id = item.id ?? item.value;
                const name = escapeText(item.name ?? item.text ?? item.title);

                if (!id || !name || hasSelectedParameter(id)) {
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'assignment-selected__item';
                wrapper.dataset.selectedParameter = '';
                wrapper.dataset.id = String(id);
                wrapper.dataset.name = name;
                wrapper.dataset.unit = escapeText(item.unit);
                wrapper.dataset.group = escapeText(item.group);
                wrapper.dataset.sampleType = escapeText(item.sample_type);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'parameter_ids[]';
                hidden.value = String(id);

                const label = document.createElement('span');
                label.textContent = name;

                const remove = document.createElement('button');
                remove.className = 'assignment-selected__remove';
                remove.type = 'button';
                remove.dataset.removeParameter = '';
                remove.setAttribute('aria-label', 'Удалить показатель');
                remove.textContent = '×';

                wrapper.append(hidden, label, remove);
                selectedList.append(wrapper);
                updateSelectedState();
            }

            function clearParameters() {
                selectedItems().forEach(function (item) {
                    item.remove();
                });
                updateSelectedState();
            }

            function renderPatients(items) {
                patientMenu.replaceChildren();

                if (!items.length) {
                    showState(patientMenu, 'Пациенты не найдены');
                    return;
                }

                items.forEach(function (item) {
                    const id = item.id ?? item.value;
                    const text = escapeText(item.text ?? item.full_name ?? item.fio);

                    if (!id || !text) {
                        return;
                    }

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'assignment-autocomplete__item';
                    button.textContent = text;

                    if (item.birth_at) {
                        const meta = document.createElement('span');
                        meta.className = 'assignment-autocomplete__item-meta';
                        meta.textContent = 'Дата рождения: ' + item.birth_at;
                        button.append(meta);
                    }

                    button.addEventListener('click', function () {
                        patientIdInput.value = String(id);
                        patientInput.value = text;
                        closeMenu(patientMenu);
                    });

                    patientMenu.append(button);
                });

                openMenu(patientMenu);
            }

            function renderParameters(items) {
                parameterMenu.replaceChildren();

                const prepared = items.filter(function (item) {
                    const id = item.id ?? item.value;
                    return id && !hasSelectedParameter(id);
                });

                if (!prepared.length) {
                    showState(parameterMenu, 'Подходящие показатели не найдены');
                    return;
                }

                prepared.forEach(function (item) {
                    const name = escapeText(item.name ?? item.text ?? item.title);

                    if (!name) {
                        return;
                    }

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'assignment-autocomplete__item';
                    button.textContent = name;

                    const metaValues = [
                        item.group,
                        item.unit,
                        item.sample_type
                    ].map(escapeText).filter(Boolean);

                    if (metaValues.length) {
                        const meta = document.createElement('span');
                        meta.className = 'assignment-autocomplete__item-meta';
                        meta.textContent = metaValues.join(' · ');
                        button.append(meta);
                    }

                    button.addEventListener('click', function () {
                        addParameter(item);
                        parameterInput.value = '';
                        closeMenu(parameterMenu);
                        parameterInput.focus();
                    });

                    parameterMenu.append(button);
                });

                openMenu(parameterMenu);
            }

            patientInput.addEventListener('input', function () {
                patientIdInput.value = '';
                clearTimeout(patientTimer);

                const query = patientInput.value.trim();

                if (query.length < 2 || patientSearchUrl === '') {
                    closeMenu(patientMenu);
                    return;
                }

                patientTimer = window.setTimeout(async function () {
                    patientController?.abort();
                    patientController = new AbortController();
                    showState(patientMenu, 'Поиск пациента…');

                    try {
                        const response = await fetch(
                            buildSearchUrl(patientSearchUrl, {q: query}),
                            {
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                signal: patientController.signal
                            }
                        );

                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }

                        renderPatients(normalizeItems(await response.json()));
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showState(patientMenu, 'Не удалось загрузить пациентов');
                        }
                    }
                }, 280);
            });

            sampleType.addEventListener('change', function () {
                closeMenu(parameterMenu);
                parameterInput.value = '';
                parameterInput.disabled = sampleType.value === '';
                parameterInput.placeholder = sampleType.value
                    ? 'Введите название показателя'
                    : 'Сначала выберите биоматериал';

                const wrongMaterialSelected = selectedItems().some(function (item) {
                    return item.dataset.sampleType
                        && item.dataset.sampleType !== sampleType.value;
                });

                if (wrongMaterialSelected) {
                    clearParameters();
                }
            });

            parameterInput.addEventListener('input', function () {
                clearTimeout(parameterTimer);

                const query = parameterInput.value.trim();

                if (
                    query.length < 2
                    || parameterSearchUrl === ''
                    || sampleType.value === ''
                ) {
                    closeMenu(parameterMenu);
                    return;
                }

                parameterTimer = window.setTimeout(async function () {
                    parameterController?.abort();
                    parameterController = new AbortController();
                    showState(parameterMenu, 'Поиск показателей…');

                    try {
                        const response = await fetch(
                            buildSearchUrl(parameterSearchUrl, {
                                q: query,
                                sample_type: sampleType.value
                            }),
                            {
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                signal: parameterController.signal
                            }
                        );

                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }

                        renderParameters(normalizeItems(await response.json()));
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showState(parameterMenu, 'Не удалось загрузить показатели');
                        }
                    }
                }, 280);
            });

            selectedList.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-parameter]');

                if (!button) {
                    return;
                }

                button.closest('[data-selected-parameter]')?.remove();
                updateSelectedState();
            });

            titleInput.addEventListener('input', function () {
                automaticTitle = titleInput.value.trim() === '';
            });

            modalToggleButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (modalElement.classList.contains('is-open')) {
                    closeAssignmentModal();
                    return;
                }

                openAssignmentModal();
            });

            modalCloseButton?.addEventListener('click', function () {
                closeAssignmentModal();
                modalToggleButton.focus();
            });

            document.addEventListener('click', function (event) {
                if (!patientMenu.contains(event.target) && event.target !== patientInput) {
                    closeMenu(patientMenu);
                }

                if (!parameterMenu.contains(event.target) && event.target !== parameterInput) {
                    closeMenu(parameterMenu);
                }

                if (
                    modalElement.classList.contains('is-open')
                    && !modalElement.contains(event.target)
                    && !modalToggleButton.contains(event.target)
                ) {
                    closeAssignmentModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Escape'
                    && modalElement.classList.contains('is-open')
                ) {
                    closeAssignmentModal();
                    modalToggleButton.focus();
                }
            });

            window.addEventListener('resize', positionAssignmentModal);
            window.addEventListener('scroll', positionAssignmentModal, true);

            sampleType.dispatchEvent(new Event('change'));
            updateSelectedState();

            if (modalElement.dataset.open === '1') {
                openAssignmentModal();
            }
        });
    </script>
@endsection
