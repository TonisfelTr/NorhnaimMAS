@extends('doctors.prescriptions')

@section('prescription-title', 'Журнал рецептов')

@section('assets')
    @parent

    <style>
        .rx-drug-code {
            display: inline-flex;
            margin-top: 6px;
            padding: 4px 7px;
            border-radius: 8px;
            background: #f2f4f7;
            color: #667085;
            font-size: 11px;
            font-weight: 750;
        }

        .rx-category-list {
            display: grid;
            gap: 10px;
        }

        .rx-category {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 13px;
            border: 1px solid #edf1f5;
            border-radius: 14px;
            background: #fff;
        }

        .rx-category__main {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .rx-category__icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 10px;
            background: #f2f7f8;
            color: var(--rx-primary-dark);
        }

        .rx-category__name {
            overflow: hidden;
            color: #344054;
            font-size: 12px;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rx-category__value {
            min-width: 30px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--rx-primary-dark);
            font-size: 11px;
            font-weight: 900;
            text-align: center;
        }

        .rx-empty {
            padding: 52px 24px;
            color: var(--rx-muted);
            text-align: center;
        }

        .rx-empty__icon {
            display: grid;
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            place-items: center;
            border-radius: 18px;
            background: #e8f8fa;
            color: var(--rx-primary-dark);
            font-size: 25px;
        }

        .rx-row-form {
            display: inline-flex;
            margin: 0;
        }

        .rx-page-button[aria-disabled="true"] {
            pointer-events: none;
            opacity: .45;
        }

        .rx-latin-name {
            display: block;
            margin-top: 3px;
            color: #98a2b3;
            font-size: 11px;
            font-style: italic;
        }
    </style>
@endsection

@section('prescription-content')
    <main class="rx-page">
        <div class="rx-container">
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <section class="rx-hero">
                <div class="rx-hero__content">
                    <div class="rx-kicker">
                        <i class="bi bi-journal-medical"></i>
                        Выписанные лекарственные назначения
                    </div>

                    <h1 class="rx-title">Журнал рецептов</h1>

                    <p class="rx-description">
                        Здесь отображаются реальные рецепты и лекарственные назначения,
                        оформленные текущим врачом. Таблица показывает пациента, препарат,
                        схему приёма, дату, тип рецепта и доступные действия.
                    </p>
                </div>

                <div class="rx-hero__actions">
                    <a
                        class="rx-button rx-button--secondary"
                        href="{{ route('doctors.prescriptions.base') }}"
                    >
                        <i class="bi bi-capsule-pill"></i>
                        Открыть базу препаратов
                    </a>

                    <a
                        class="rx-button rx-button--primary"
                        href="{{ route('doctors.prescriptions.new') }}"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Создать рецепт
                    </a>
                </div>
            </section>

            <section class="rx-stats" aria-label="Сводка по рецептам">
                <article class="rx-stat">
                    <div class="rx-stat__head">
                        <div class="rx-stat__label">
                            Рецептов оформлено сегодня
                        </div>

                        <div class="rx-stat__icon">
                            <i class="bi bi-file-earmark-medical"></i>
                        </div>
                    </div>

                    <div class="rx-stat__value">
                        {{ $newPrescriptionsCount }}
                    </div>

                    <div class="rx-stat__hint">
                        Количество рецептов текущего врача за сегодня
                    </div>
                </article>

                <article class="rx-stat rx-stat--blue">
                    <div class="rx-stat__head">
                        <div class="rx-stat__label">
                            Пациентов с рецептами сегодня
                        </div>

                        <div class="rx-stat__icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>

                    <div class="rx-stat__value">
                        {{ $uniquePatientsCount }}
                    </div>

                    <div class="rx-stat__hint">
                        Каждый пациент учитывается один раз
                    </div>
                </article>

                <article class="rx-stat rx-stat--warning">
                    <div class="rx-stat__head">
                        <div class="rx-stat__label">
                            Рецептов строгого учёта
                        </div>

                        <div class="rx-stat__icon">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                    </div>

                    <div class="rx-stat__value">
                        {{ $strictPrescriptionsCount }}
                    </div>

                    <div class="rx-stat__hint">
                        Рецепты формы № 148, оформленные сегодня
                    </div>
                </article>

                <article class="rx-stat rx-stat--success">
                    <div class="rx-stat__head">
                        <div class="rx-stat__label">
                            Повторно оформленных рецептов
                        </div>

                        <div class="rx-stat__icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>

                    <div class="rx-stat__value">
                        {{ $repeatedPrescriptionsCount }}
                    </div>

                    <div class="rx-stat__hint">
                        Препарат уже назначался этому пациенту ранее
                    </div>
                </article>
            </section>

            <div class="rx-grid">
                <div>
                    <section class="rx-panel mb-4">
                        <div class="rx-panel__header">
                            <div>
                                <h2 class="rx-panel__title">
                                    Поиск и фильтры
                                </h2>

                                <div class="rx-panel__subtitle">
                                    Поиск выполняется по пациенту, препарату,
                                    типу рецепта и периоду
                                </div>
                            </div>
                        </div>

                        <div class="rx-panel__body">
                            <form
                                class="rx-filters"
                                action="{{ route('doctors.prescriptions.index') }}"
                                method="GET"
                            >
                                <div class="rx-filter--5">
                                    <label class="rx-label" for="rxSearch">
                                        Пациент или препарат
                                    </label>

                                    <input
                                        id="rxSearch"
                                        class="rx-control"
                                        type="search"
                                        name="search"
                                        value="{{ $filters['search'] }}"
                                        placeholder="Введите ФИО или название препарата"
                                    >
                                </div>

                                <div class="rx-filter--3">
                                    <label class="rx-label" for="rxStatus">
                                        Тип рецепта
                                    </label>

                                    <select
                                        id="rxStatus"
                                        class="rx-control"
                                        name="status"
                                    >
                                        <option
                                            value=""
                                            @selected($filters['status'] === '')
                                        >
                                            Все типы
                                        </option>

                                        <option
                                            value="regular"
                                            @selected($filters['status'] === 'regular')
                                        >
                                            Обычный
                                        </option>

                                        <option
                                            value="strict"
                                            @selected($filters['status'] === 'strict')
                                        >
                                            Строгий учёт
                                        </option>

                                        <option
                                            value="repeated"
                                            @selected($filters['status'] === 'repeated')
                                        >
                                            Повторный
                                        </option>
                                    </select>
                                </div>

                                <div class="rx-filter--2">
                                    <label class="rx-label" for="rxPeriod">
                                        Период
                                    </label>

                                    <select
                                        id="rxPeriod"
                                        class="rx-control"
                                        name="period"
                                    >
                                        <option
                                            value="all"
                                            @selected($filters['period'] === 'all')
                                        >
                                            Всё время
                                        </option>

                                        <option
                                            value="today"
                                            @selected($filters['period'] === 'today')
                                        >
                                            Сегодня
                                        </option>

                                        <option
                                            value="week"
                                            @selected($filters['period'] === 'week')
                                        >
                                            7 дней
                                        </option>

                                        <option
                                            value="month"
                                            @selected($filters['period'] === 'month')
                                        >
                                            30 дней
                                        </option>
                                    </select>
                                </div>

                                <div class="rx-filter--2">
                                    <button
                                        class="rx-button rx-button--primary w-100"
                                        type="submit"
                                    >
                                        <i class="bi bi-search"></i>
                                        Найти
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <section class="rx-panel">
                        <div class="rx-panel__header">
                            <div>
                                <h2 class="rx-panel__title">
                                    Рецепты и лекарственные назначения
                                </h2>

                                <div class="rx-panel__subtitle">
                                    В таблице показывается, кому, что, когда
                                    и по какой схеме назначено
                                </div>
                            </div>

                            <span class="rx-count">
                                {{ $prescriptions->total() }} записей
                            </span>
                        </div>

                        @if($rows->isNotEmpty())
                            <div class="rx-table-wrap">
                                <table class="rx-table">
                                    <thead>
                                    <tr>
                                        <th>Пациент</th>
                                        <th>Препарат</th>
                                        <th>Схема приёма</th>
                                        <th>Дата</th>
                                        <th>Тип рецепта</th>
                                        <th style="text-align: right;">
                                            Действия
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td>
                                                <div class="rx-patient">
                                                    <div class="rx-avatar">
                                                        {{ $row['initials'] }}
                                                    </div>

                                                    <div>
                                                        <div class="rx-primary-text">
                                                            {{ $row['patient_name'] }}
                                                        </div>

                                                        <div class="rx-secondary-text">
                                                            Дата рождения:
                                                            {{ $row['patient_birth_date'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="rx-primary-text">
                                                    {{ $row['drug_name'] }}
                                                </div>

                                                @if($row['drug_latin_name'] !== '')
                                                    <span class="rx-latin-name">
                                                        {{ $row['drug_latin_name'] }}
                                                    </span>
                                                @endif

                                                <div class="rx-secondary-text">
                                                    {{ $row['drug_meta'] }}
                                                </div>

                                                <span class="rx-drug-code">
                                                    {{ $row['document_number'] }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="rx-primary-text">
                                                    {{ $row['usage_instructions'] }}
                                                </div>

                                                <div class="rx-secondary-text">
                                                    {{ $row['prescription_form'] }}
                                                </div>
                                            </td>

                                            <td>
                                                <div class="rx-primary-text">
                                                    {{ $row['issued_date'] }}
                                                </div>
                                            </td>

                                            <td>
                                                <span
                                                    class="rx-status rx-status--{{ $row['status_class'] }}"
                                                >
                                                    {{ $row['status_label'] }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="rx-actions">
                                                    @if($row['medical_card_url'])
                                                        <a
                                                            class="rx-action"
                                                            href="{{ $row['medical_card_url'] }}"
                                                            title="Открыть медкарту"
                                                        >
                                                            <i class="bi bi-person-vcard"></i>
                                                        </a>
                                                    @endif

                                                    <a
                                                        class="rx-action"
                                                        href="{{ $row['print_url'] }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        title="Печать"
                                                    >
                                                        <i class="bi bi-printer"></i>
                                                    </a>

                                                    <form
                                                        class="rx-row-form"
                                                        method="POST"
                                                        action="{{ $row['repeat_url'] }}"
                                                    >
                                                        @csrf

                                                        <button
                                                            class="rx-action rx-action--primary"
                                                            type="submit"
                                                            title="Повторить рецепт"
                                                        >
                                                            <i class="bi bi-arrow-repeat"></i>
                                                            Повторить
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="rx-pagination">
                                <div>
                                    Показаны записи
                                    {{ $prescriptions->firstItem() }}
                                    –
                                    {{ $prescriptions->lastItem() }}
                                    из
                                    {{ $prescriptions->total() }}
                                </div>

                                @if($prescriptions->hasPages())
                                    <div
                                        class="rx-pagination__buttons"
                                        aria-label="Пагинация"
                                    >
                                        @if($prescriptions->onFirstPage())
                                            <span
                                                class="rx-page-button"
                                                aria-disabled="true"
                                            >
                                                <i class="bi bi-chevron-left"></i>
                                            </span>
                                        @else
                                            <a
                                                class="rx-page-button"
                                                href="{{ $prescriptions->previousPageUrl() }}"
                                                aria-label="Предыдущая страница"
                                            >
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        @endif

                                        @foreach(
                                            $prescriptions->getUrlRange(
                                                max(1, $prescriptions->currentPage() - 2),
                                                min($prescriptions->lastPage(), $prescriptions->currentPage() + 2)
                                            ) as $page => $url
                                        )
                                            <a
                                                class="rx-page-button {{ $page === $prescriptions->currentPage() ? 'is-active' : '' }}"
                                                href="{{ $url }}"
                                            >
                                                {{ $page }}
                                            </a>
                                        @endforeach

                                        @if($prescriptions->hasMorePages())
                                            <a
                                                class="rx-page-button"
                                                href="{{ $prescriptions->nextPageUrl() }}"
                                                aria-label="Следующая страница"
                                            >
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        @else
                                            <span
                                                class="rx-page-button"
                                                aria-disabled="true"
                                            >
                                                <i class="bi bi-chevron-right"></i>
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="rx-empty">
                                <div class="rx-empty__icon">
                                    <i class="bi bi-journal-x"></i>
                                </div>

                                <h3>Рецепты не найдены</h3>

                                <p>
                                    Измените фильтры или оформите новый рецепт.
                                </p>
                            </div>
                        @endif
                    </section>
                </div>

                <aside>
                    <section class="rx-panel">
                        <div class="rx-panel__header">
                            <div>
                                <h2 class="rx-panel__title">
                                    Рецепты по группам препаратов
                                </h2>

                                <div class="rx-panel__subtitle">
                                    Распределение сегодняшних рецептов
                                    по фармакологическим группам
                                </div>
                            </div>
                        </div>

                        <div class="rx-panel__body">
                            @if($groupStatistics->isNotEmpty())
                                <div class="rx-category-list">
                                    @foreach($groupStatistics as $group)
                                        <div class="rx-category">
                                            <div class="rx-category__main">
                                                <div class="rx-category__icon">
                                                    <i class="bi {{ $group['icon'] }}"></i>
                                                </div>

                                                <div class="rx-category__name">
                                                    {{ $group['name'] }}
                                                </div>
                                            </div>

                                            <div class="rx-category__value">
                                                {{ $group['total'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small">
                                    Сегодня рецептов по группам препаратов нет.
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="rx-panel">
                        <div class="rx-panel__header">
                            <div>
                                <h2 class="rx-panel__title">
                                    Как читать этот раздел
                                </h2>

                                <div class="rx-panel__subtitle">
                                    Пояснение показателей и действий
                                </div>
                            </div>
                        </div>

                        <div class="rx-panel__body">
                            <div class="rx-info-list mb-3">
                                <div class="rx-info-row">
                                    <span class="rx-info-row__label">
                                        Текущий врач
                                    </span>

                                    <span class="rx-info-row__value">
                                        {{ $doctorFullName }}
                                    </span>
                                </div>

                                <div class="rx-info-row">
                                    <span class="rx-info-row__label">
                                        Всего рецептов
                                    </span>

                                    <span class="rx-info-row__value">
                                        {{ $totalPrescriptionsCount }}
                                    </span>
                                </div>

                                <div class="rx-info-row">
                                    <span class="rx-info-row__label">
                                        Записей по фильтру
                                    </span>

                                    <span class="rx-info-row__value">
                                        {{ $prescriptions->total() }}
                                    </span>
                                </div>
                            </div>

                            <div class="rx-note rx-note--warning">
                                <i class="bi bi-info-circle"></i>

                                <div>
                                    Повторным считается рецепт на препарат,
                                    который уже назначался этому же пациенту ранее.
                                    Перед печатью проверьте пациента, препарат,
                                    дозировку и схему приёма.
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>
@endsection
