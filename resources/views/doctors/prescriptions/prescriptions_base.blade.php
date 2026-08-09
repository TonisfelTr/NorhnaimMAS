@extends('doctors.prescriptions')

@section('prescription-title', 'База лекарств')

@section('assets')
    @parent
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.css">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.polyfills.min.js"></script>

    <style>
        .drug-page {
            --drug-primary: #0f8fa2;
            --drug-primary-dark: #0b6e7c;
            --drug-border: #dbe7ee;
            --drug-soft: #eef8fb;
            --drug-text: #223046;
            --drug-muted: #667085;
            --drug-bg: #f8fbfc;
        }

        .drug-page .tagify {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            min-height: 46px;
            border: 1px solid var(--drug-border);
            border-radius: 14px;
            padding: 0.5rem 0.75rem;
            background: #fff;
            box-sizing: border-box;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .drug-page .tagify:focus-within {
            border-color: rgba(15, 143, 162, 0.5);
            box-shadow: 0 0 0 4px rgba(15, 143, 162, 0.08);
        }

        .drug-page .tagify__tag {
            margin: 0 !important;
        }

        .drug-page .tagify__input {
            flex: 1 0 160px;
            min-width: 140px;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1.5;
        }

        .drug-page .tagify__dropdown {
            border: 1px solid var(--drug-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(16, 24, 40, 0.14);
        }

        .drug-page .db-page {
            padding: 28px 0 42px;
        }

        .drug-page .db-container {
            width: min(1480px, calc(100% - 28px));
            margin: 0 auto;
        }

        .drug-page .db-hero {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-start;
            padding: 24px 28px;
            border: 1px solid rgba(15, 143, 162, 0.09);
            border-radius: 26px;
            background: linear-gradient(135deg, #f7fdff 0%, #eef8fb 100%);
            box-shadow: 0 20px 50px rgba(15, 77, 92, 0.08);
        }

        .drug-page .db-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(15, 143, 162, 0.1);
            color: var(--drug-primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .01em;
        }

        .drug-page .db-title {
            margin: 16px 0 10px;
            color: #1f2e43;
            font-size: clamp(28px, 3vw, 48px);
            font-weight: 900;
            line-height: 1.05;
        }

        .drug-page .db-description {
            max-width: 860px;
            margin: 0;
            color: var(--drug-muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .drug-page .db-hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .drug-page .db-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 0 18px;
            border: 1px solid var(--drug-border);
            border-radius: 14px;
            background: #fff;
            color: var(--drug-text);
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: .15s ease;
        }

        .drug-page .db-button:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .drug-page .db-button--primary {
            border-color: var(--drug-primary);
            background: var(--drug-primary);
            color: #fff;
            box-shadow: 0 12px 26px rgba(15, 143, 162, 0.24);
        }

        .drug-page .db-button--secondary:hover {
            border-color: #bcd5dd;
            background: #fbfdfe;
        }

        .drug-page .db-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(280px, .9fr);
            gap: 18px;
            margin-top: 18px;
        }

        .drug-page .db-panel {
            border: 1px solid var(--drug-border);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(16, 24, 40, 0.05);
            overflow: hidden;
        }

        .drug-page .db-panel__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 22px;
            border-bottom: 1px solid #edf2f6;
        }

        .drug-page .db-panel__title {
            margin: 0;
            color: #12304b;
            font-size: 18px;
            font-weight: 900;
        }

        .drug-page .db-panel__subtitle {
            margin-top: 4px;
            color: var(--drug-muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .drug-page .db-panel__body {
            padding: 20px 22px 22px;
        }

        .drug-page .db-summary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #dbe7ee;
            border-radius: 999px;
            background: #f8fbfc;
            color: #274760;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .drug-page .db-info-list {
            display: grid;
            gap: 12px;
        }

        .drug-page .db-info {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 15px;
            border: 1px solid #edf2f6;
            border-radius: 16px;
            background: #fbfdfe;
        }

        .drug-page .db-info__label {
            color: var(--drug-muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .drug-page .db-info__value {
            color: #14314d;
            font-size: 14px;
            font-weight: 900;
            text-align: right;
        }

        .drug-page .db-tip {
            padding: 14px 15px;
            border: 1px solid #f6d79c;
            border-radius: 16px;
            background: #fff8e8;
            color: #8c5a0a;
            font-size: 13px;
            line-height: 1.55;
        }

        .drug-page .db-filters {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 16px;
        }

        .drug-page .db-field {
            display: grid;
            gap: 8px;
        }

        .drug-page .db-col-12 {
            grid-column: span 12;
        }

        .drug-page .db-col-6 {
            grid-column: span 6;
        }

        .drug-page .db-col-4 {
            grid-column: span 4;
        }

        .drug-page .db-label {
            color: #344054;
            font-size: 13px;
            font-weight: 800;
        }

        .drug-page .db-input,
        .drug-page .db-select {
            width: 100%;
            min-height: 46px;
            padding: 0 14px;
            border: 1px solid var(--drug-border);
            border-radius: 14px;
            background: #fff;
            color: var(--drug-text);
            font-size: 14px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .drug-page .db-input:focus,
        .drug-page .db-select:focus {
            outline: none;
            border-color: rgba(15, 143, 162, 0.5);
            box-shadow: 0 0 0 4px rgba(15, 143, 162, 0.08);
        }

        .drug-page .db-checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 14px;
            margin-top: 2px;
        }

        .drug-page .db-checkbox-card {
            padding: 12px 14px;
            border: 1px solid #edf2f6;
            border-radius: 16px;
            background: #fbfdfe;
        }

        .drug-page .db-filter-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .drug-page .db-results-table-wrap {
            overflow-x: auto;
        }

        .drug-page .db-results-table {
            width: 100%;
            min-width: 1120px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .drug-page .db-results-table thead th {
            position: sticky;
            top: 0;
            padding: 14px 16px;
            border-bottom: 1px solid #e8eef2;
            background: #f9fbfc;
            color: #667085;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .drug-page .db-results-table tbody td {
            padding: 18px 16px;
            border-bottom: 1px solid #edf2f6;
            vertical-align: top;
            color: #24364c;
            font-size: 14px;
            line-height: 1.55;
        }

        .drug-page .db-results-table tbody tr:hover {
            background: #fbfeff;
        }

        .drug-page .db-drug-name {
            color: #12304b;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.35;
        }

        .drug-page .db-group-badge,
        .drug-page .db-status-badge,
        .drug-page .db-chip,
        .drug-page .db-chip--danger,
        .drug-page .db-chip--success,
        .drug-page .db-form-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
        }

        .drug-page .db-group-badge {
            margin-top: 8px;
            padding: 6px 10px;
            background: var(--drug-soft);
            color: var(--drug-primary-dark);
        }

        .drug-page .db-status-badge {
            padding: 8px 12px;
            background: #f4fbf7;
            color: #157347;
            white-space: nowrap;
        }

        .drug-page .db-status-badge--muted {
            background: #f7f8fb;
            color: #667085;
        }

        .drug-page .db-form-list,
        .drug-page .db-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .drug-page .db-form-block {
            margin-bottom: 10px;
        }

        .drug-page .db-form-name {
            margin-bottom: 6px;
            color: #344054;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .drug-page .db-form-chip {
            padding: 6px 10px;
            background: #f7f9fb;
            color: #475467;
        }

        .drug-page .db-chip {
            padding: 6px 10px;
            background: #eff8ff;
            color: #175cd3;
        }

        .drug-page .db-chip--success {
            padding: 6px 10px;
            background: #ecfdf3;
            color: #027a48;
        }

        .drug-page .db-indication-list {
            display: grid;
            gap: 8px;
        }

        .drug-page .db-indication {
            display: grid;
            gap: 6px;
            width: fit-content;
            max-width: 100%;
            padding: 8px 10px;
            border: 1px solid #d9f2e3;
            border-radius: 13px;
            background: #f2fbf6;
        }

        .drug-page .db-indication__diagnosis {
            color: #027a48;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.35;
        }

        .drug-page .db-indication__sources {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .drug-page .db-source-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-height: 22px;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 850;
            line-height: 1.2;
            white-space: nowrap;
        }

        .drug-page .db-source-badge--ru {
            border: 1px solid #c9e5ff;
            background: #edf7ff;
            color: #175cd3;
        }

        .drug-page .db-source-badge--fda {
            border: 1px solid #ead7ff;
            background: #f7f0ff;
            color: #7a3fb5;
        }

        .drug-page .db-source-badge--unknown {
            border: 1px solid #e4e7ec;
            background: #f8f9fb;
            color: #667085;
        }

        .drug-page .db-chip--danger {
            padding: 6px 10px;
            background: #fef3f2;
            color: #b42318;
        }

        .drug-page .db-empty-inline {
            color: #98a2b3;
            font-size: 13px;
        }

        .drug-page .db-empty {
            padding: 48px 24px;
            text-align: center;
            color: var(--drug-muted);
        }

        .drug-page .db-empty__icon {
            display: grid;
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            place-items: center;
            border-radius: 18px;
            background: var(--drug-soft);
            color: var(--drug-primary-dark);
            font-size: 24px;
        }

        .drug-page .db-empty__title {
            margin: 0 0 6px;
            color: #12304b;
            font-size: 20px;
            font-weight: 900;
        }

        .drug-page .db-pagination {
            display: flex;
            justify-content: center;
            padding: 22px 0 0;
        }

        .drug-page .db-pagination .pagination {
            margin-bottom: 0;
            gap: 6px;
        }

        .drug-page .db-pagination .page-link {
            border-radius: 12px !important;
            border-color: #dbe7ee;
            color: #274760;
            font-weight: 700;
        }

        .drug-page .db-pagination .page-item.active .page-link {
            border-color: var(--drug-primary);
            background: var(--drug-primary);
            color: #fff;
        }

        @media (max-width: 1180px) {
            .drug-page .db-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .drug-page .db-hero {
                flex-direction: column;
                align-items: stretch;
            }

            .drug-page .db-hero-actions {
                justify-content: flex-start;
            }

            .drug-page .db-col-6,
            .drug-page .db-col-4 {
                grid-column: span 12;
            }
        }

        @media (max-width: 640px) {
            .drug-page .db-container {
                width: min(100% - 18px, 100%);
            }

            .drug-page .db-page {
                padding-top: 16px;
            }

            .drug-page .db-hero,
            .drug-page .db-panel__header,
            .drug-page .db-panel__body {
                padding-left: 16px;
                padding-right: 16px;
            }

            .drug-page .db-checkbox-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('sub-main')
    <main class="drug-page">
        <div class="db-page">
            <div class="db-container">
                <section class="db-hero">
                    <div>
                        <div class="db-kicker">
                            <i class="bi bi-capsule-pill"></i>
                            Фармакологический справочник
                        </div>

                        <h1 class="db-title">База лекарств</h1>

                        <p class="db-description">
                            Подбор препаратов по названию, группе, противопоказаниям и ограничениям.
                            Все фильтры и поля формы сохранены без изменения names, поэтому существующая серверная логика продолжит работать как раньше.
                        </p>
                    </div>

                    <div class="db-hero-actions">
                        <a class="db-button db-button--secondary" href="{{ route('doctors.prescriptions.index') }}">
                            <i class="bi bi-journal-medical"></i>
                            К журналу рецептов
                        </a>

                        <a class="db-button db-button--primary" href="{{ route('doctors.prescriptions.base') }}">
                            <i class="bi bi-arrow-clockwise"></i>
                            Сбросить фильтры
                        </a>
                    </div>
                </section>

                <div class="db-grid">
                    <section class="db-panel">
                        <div class="db-panel__header">
                            <div>
                                <h2 class="db-panel__title">Поиск и фильтры</h2>
                                <div class="db-panel__subtitle">
                                    Существующие names сохранены без изменений. Показания ищутся по коду МКБ
                                    или названию диагноза без загрузки всего справочника на страницу.
                                </div>
                            </div>

                            <div class="db-summary">
                                <i class="bi bi-funnel"></i>
                                {{ $currentPageCount }} на странице
                            </div>
                        </div>

                        <div class="db-panel__body">
                            <form method="get">
                                <div class="db-filters">
                                    <div class="db-field db-col-12">
                                        <label class="db-label" for="name">Название</label>
                                        <input
                                            class="db-input"
                                            type="text"
                                            id="name"
                                            name="name"
                                            value="{{ request('name') }}"
                                            placeholder="Введите название препарата"
                                        >
                                    </div>

                                    <div class="db-field db-col-4">
                                        <label class="db-label" for="group">Группа</label>
                                        <select class="db-select" id="group" name="group">
                                            <option value="">Все группы</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>
                                                    {{ $group->group }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="db-field db-col-4">
                                        <label class="db-label" for="indications">Показания</label>
                                        <input
                                            id="indications"
                                            name="indications"
                                            value="{{ request('indications') }}"
                                            placeholder="Например: F20 или шизофрения"
                                            class="db-input"
                                            autocomplete="off"
                                        >
                                        <input
                                            type="hidden"
                                            name="indications_ids"
                                            id="indications_ids"
                                            value="{{ request('indications_ids') }}"
                                        >
                                    </div>

                                    <div class="db-field db-col-4">
                                        <label class="db-label" for="contraindications">Противопоказания для исключения</label>
                                        <input
                                            id="contraindications"
                                            name="contraindications"
                                            placeholder="Начните вводить противопоказание"
                                            class="db-input"
                                        >
                                        <input type="hidden" name="contraindications_ids" id="contraindications_ids">
                                    </div>

                                    <div class="db-col-12">
                                        <div class="db-label">Дополнительные условия</div>
                                        <div class="db-checkbox-grid">
                                            <div class="db-checkbox-card">
                                                <x-doctor-checkbox name="pregnancy"
                                                                   label="Разрешён при беременности"
                                                                   wrapper-class="mb-0"
                                                                   :checked="request()->has('pregnancy') && request()->get('pregnancy') == 'on'" />
                                            </div>
                                            <div class="db-checkbox-card">
                                                <x-doctor-checkbox name="lactation"
                                                                   label="Разрешён при лактации"
                                                                   wrapper-class="mb-0"
                                                                   :checked="request()->has('lactation') && request()->get('lactation') == 'on'" />
                                            </div>
                                            <div class="db-checkbox-card">
                                                <x-doctor-checkbox name="liver"
                                                                   label="Метаболизм не печенью"
                                                                   wrapper-class="mb-0"
                                                                   :checked="request()->has('liver') && request()->get('liver') == 'on'" />
                                            </div>
                                            <div class="db-checkbox-card">
                                                <x-doctor-checkbox name="kidneys"
                                                                   label="Метаболизм не почками"
                                                                   wrapper-class="mb-0"
                                                                   :checked="request()->has('kidneys') && request()->get('kidneys') == 'on'" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="db-filter-actions">
                                    <a class="db-button db-button--secondary" href="{{ route('doctors.prescriptions.base') }}">
                                        <i class="bi bi-x-circle"></i>
                                        Очистить
                                    </a>

                                    <button class="db-button db-button--primary" type="submit">
                                        <i class="bi bi-search"></i>
                                        Искать
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <aside class="db-panel">
                        <div class="db-panel__header">
                            <div>
                                <h2 class="db-panel__title">Сводка</h2>
                                <div class="db-panel__subtitle">
                                    Краткая информация по текущему списку препаратов.
                                </div>
                            </div>
                        </div>

                        <div class="db-panel__body">
                            <div class="db-info-list">
                                <div class="db-info">
                                    <div class="db-info__label">Всего препаратов в результате</div>
                                    <div class="db-info__value">{{ $totalDrugs }}</div>
                                </div>

                                <div class="db-info">
                                    <div class="db-info__label">Показано на текущей странице</div>
                                    <div class="db-info__value">{{ $currentPageCount }}</div>
                                </div>

                                <div class="db-info">
                                    <div class="db-info__label">Страница</div>
                                    <div class="db-info__value">{{ $currentPage }} / {{ $lastPage }}</div>
                                </div>
                            </div>

                            <div class="db-tip mt-3">
                                <strong>Подсказка.</strong> Если список пустой, сначала попробуйте сбросить фильтры.
                                В поле «Показания» можно ввести код МКБ или часть названия диагноза.
                                Противопоказания по-прежнему исключаются через <code>contraindications_ids</code>.
                            </div>
                        </div>
                    </aside>
                </div>

                <section class="db-panel mt-3 mt-lg-4">
                    <div class="db-panel__header">
                        <div>
                            <h2 class="db-panel__title">Список препаратов</h2>
                            <div class="db-panel__subtitle">
                                Наименование, группа, формы выпуска, тип рецепта, показания и ограничения.
                            </div>
                        </div>

                        <div class="db-summary">
                            <i class="bi bi-table"></i>
                            {{ $totalDrugs }} записей
                        </div>
                    </div>

                    <div class="db-panel__body">
                        @if($drugs->count())
                            <div class="db-results-table-wrap">
                                <table class="db-results-table">
                                    <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Группа препарата</th>
                                        <th class="table__forms-column">Формы</th>
                                        <th>Тип рецепта</th>
                                        <th class="table__indications-column">Показания</th>
                                        <th class="table__contraindications-column">Противопоказания</th>
                                        <th>Доступен по льготе</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($drugs as $drug)
                                        <tr>
                                            <td>
                                                <div class="db-drug-name">{{ $drug->name }}</div>
                                                <div class="db-group-badge">
                                                    <i class="bi bi-capsule"></i>
                                                    {{ $drug->display_group_name }}
                                                </div>
                                            </td>

                                            <td>
                                                {{ $drug->display_group_name }}
                                            </td>

                                            <td>
                                                @forelse($drug->display_forms as $form)
                                                    <div class="db-form-block">
                                                        <div class="db-form-name">
                                                            {{ $form['label'] }}
                                                        </div>
                                                        <div class="db-form-list">
                                                            @forelse($form['entries'] as $entry)
                                                                <span class="db-form-chip">
                                                                    {{ $entry['text'] }}
                                                                </span>
                                                            @empty
                                                                <span class="db-empty-inline">Дозировки не указаны</span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="db-empty-inline">Формы не указаны</span>
                                                @endforelse
                                            </td>

                                            <td>
                                                    <span class="db-status-badge {{ $drug->strict ? '' : 'db-status-badge--muted' }}">
                                                        {{ $drug->display_prescription_form }}
                                                    </span>
                                            </td>

                                            <td>
                                                <div class="db-indication-list">
                                                    @forelse($drug->displayIndications as $indication)
                                                        <div class="db-indication">
                                                            <div class="db-indication__diagnosis">
                                                                {{ $indication['code'] }}
                                                                @if($indication['title'] !== '')
                                                                    — {{ $indication['title'] }}
                                                                @endif
                                                            </div>

                                                            <div class="db-indication__sources">
                                                                @if($indication['indicated_in_russia'])
                                                                    <span
                                                                        class="db-source-badge db-source-badge--ru"
                                                                        title="Показание отмечено в базе как рекомендованное/применяемое в РФ"
                                                                    >
                                                                        <i class="bi bi-shield-check"></i>
                                                                        по рекомендации Минздрава РФ
                                                                    </span>
                                                                @endif

                                                                @if($indication['indicated_by_fda'])
                                                                    <span
                                                                        class="db-source-badge db-source-badge--fda"
                                                                        title="Показание отмечено в базе как рекомендованное/одобренное FDA"
                                                                    >
                                                                        <i class="bi bi-patch-check"></i>
                                                                        по рекомендации FDA
                                                                    </span>
                                                                @endif

                                                                @if(
                                                                    !$indication['indicated_in_russia']
                                                                    && !$indication['indicated_by_fda']
                                                                )
                                                                    <span
                                                                        class="db-source-badge db-source-badge--unknown"
                                                                    >
                                                                        источник рекомендации не указан
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <span class="db-empty-inline">
                                                            Показания не загружены
                                                        </span>
                                                    @endforelse
                                                </div>
                                            </td>

                                            <td>
                                                <div class="db-chip-list">
                                                    @forelse($drug->contraindications as $contraindication)
                                                        <span class="db-chip db-chip--danger">{{ $contraindication->name }}</span>
                                                    @empty
                                                        <span class="db-empty-inline">Нет ограничений</span>
                                                    @endforelse
                                                </div>
                                            </td>

                                            <td>
                                                    <span class="db-status-badge {{ $drug->preferential ? '' : 'db-status-badge--muted' }}">
                                                        {{ $drug->display_preferential_label }}
                                                    </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="db-pagination">
                                {{ $drugs->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="db-empty">
                                <div class="db-empty__icon">
                                    <i class="bi bi-search"></i>
                                </div>
                                <h3 class="db-empty__title">Ничего не найдено</h3>
                                <div>
                                    По текущим фильтрам препараты не найдены. Попробуйте сбросить условия поиска
                                    или изменить группу/ограничения.
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Tagify === 'undefined') {
                return;
            }

            const input = document.querySelector('#contraindications');
            const hiddenInput = document.querySelector('#contraindications_ids');

            if (!input || !hiddenInput) {
                return;
            }

            const allContraindicationsRaw = @json($contraindications);
            const whitelist = Object.entries(
                allContraindicationsRaw || {}
            ).map(([value, label]) => ({
                value: String(value),
                code: String(label)
            }));

            const selectedValues = (
                @json($selectedContraindicationIds)
            ).map(value => String(value));

            const tagify = new Tagify(input, {
                delimiters: null,
                whitelist: whitelist,
                enforceWhitelist: true,
                dropdown: {
                    enabled: 1,
                    closeOnSelect: true,
                    direction: 'bottom',
                    maxItems: 30,
                    fuzzySearch: true,
                    highlightFirst: true,
                    searchKeys: ['code', 'value'],
                    position: 'text'
                },
                templates: {
                    tag: tagData => `
                        <tag title="${tagData.code || tagData.value}"
                             contenteditable="false"
                             spellcheck="false"
                             class="tagify__tag">
                            <div>
                                <span class="tagify__tag-text">
                                    ${tagData.code || tagData.value}
                                </span>
                            </div>
                            <button type="button"
                                    class="tagify__tag__removeBtn"
                                    aria-label="remove tag"></button>
                        </tag>
                    `,
                    dropdownItem: tagData => `
                        <div class="tagify__dropdown__item ${tagData.class || ''}"
                             data-value="${tagData.value}"
                             data-tagify-tag='${JSON.stringify(tagData)}'>
                            <strong>${tagData.code || tagData.value}</strong>
                        </div>
                    `
                }
            });

            const preselectedItems = whitelist.filter(item =>
                selectedValues.includes(String(item.value))
            );

            if (preselectedItems.length) {
                tagify.addTags(preselectedItems);
            }

            const syncHiddenInput = () => {
                hiddenInput.value = JSON.stringify(
                    tagify.value.map(item => String(item.value))
                );
            };

            syncHiddenInput();
            tagify.on('change', syncHiddenInput);
            tagify.on('add', syncHiddenInput);
            tagify.on('remove', syncHiddenInput);
        });
    </script>
@endsection
