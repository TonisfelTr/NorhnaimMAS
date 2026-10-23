@extends('layouts.doctor')

@section('title', 'Выписки стационара')

@section('assets')
    @parent

    <style>
        :root {
            --ip-primary: #078f9f;
            --ip-primary-dark: #05727e;
            --ip-primary-soft: #e8f8fa;
            --ip-bg: #f4f7fb;
            --ip-card: #fff;
            --ip-border: #dfe7ed;
            --ip-text: #1f2b3d;
            --ip-muted: #758295;
            --ip-success: #169b55;
            --ip-warning: #d68a12;
            --ip-blue: #3478d4;
        }

        .ip-topbar {
            min-height: 68px;
            padding: 0;
            background: linear-gradient(90deg, var(--ip-primary), #10aaba);
            box-shadow: 0 10px 28px rgba(7, 143, 159, .18);
        }

        .ip-topbar__inner {
            min-height: 68px;
            padding: 10px 24px;
            gap: 18px;
        }

        .ip-brand,
        .ip-nav-link {
            color: #fff !important;
            text-decoration: none;
        }

        .ip-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 850;
            white-space: nowrap;
        }

        .ip-brand__icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, .18);
        }

        .ip-nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ip-nav-link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border-radius: 12px;
            color: rgba(255, 255, 255, .90) !important;
            font-size: 13px;
            font-weight: 750;
            transition: .16s ease;
            white-space: nowrap;
        }

        .ip-nav-link:hover,
        .ip-nav-link.active {
            background: rgba(255, 255, 255, .18);
            color: #fff !important;
        }

        .ip-context {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-left: auto;
            padding: 7px 11px;
            border: 1px solid rgba(255,255,255,.24);
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
        }

        .ip-page {
            min-height: calc(100vh - 68px);
            padding: 24px 28px 44px;
            background: var(--ip-bg);
        }

        .ip-container {
            width: min(1480px, 100%);
            margin: 0 auto;
        }

        .ip-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
            padding: 22px 24px;
            border: 1px solid var(--ip-border);
            border-radius: 20px;
            background:
                radial-gradient(circle at 100% 0, rgba(8, 166, 182, .12), transparent 32%),
                #fff;
            box-shadow: 0 12px 34px rgba(15, 23, 42, .045);
        }

        .ip-kicker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 9px;
            padding: 5px 10px;
            border-radius: 999px;
            background: var(--ip-primary-soft);
            color: var(--ip-primary-dark);
            font-size: 11px;
            font-weight: 850;
        }

        .ip-title {
            margin: 0;
            color: var(--ip-text);
            font-size: clamp(27px, 3vw, 36px);
            font-weight: 880;
            letter-spacing: -.035em;
        }

        .ip-description {
            max-width: 850px;
            margin: 8px 0 0;
            color: var(--ip-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .ip-hero__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .ip-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid #d5e0e6;
            border-radius: 12px;
            background: #fff;
            color: #344054;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .ip-button:hover {
            border-color: #bad2d7;
            background: #f7fbfc;
            color: var(--ip-primary-dark);
        }

        .ip-button--primary {
            border-color: var(--ip-primary);
            background: var(--ip-primary);
            color: #fff;
        }

        .ip-button--primary:hover {
            border-color: var(--ip-primary-dark);
            background: var(--ip-primary-dark);
            color: #fff;
        }

        .ip-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .ip-stat {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 15px 17px;
            border: 1px solid var(--ip-border);
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 9px 24px rgba(15, 23, 42, .035);
        }

        .ip-stat__icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: var(--ip-primary-soft);
            color: var(--ip-primary-dark);
            font-size: 18px;
        }

        .ip-stat--blue .ip-stat__icon {
            background: #edf4ff;
            color: var(--ip-blue);
        }

        .ip-stat--warning .ip-stat__icon {
            background: #fff7e8;
            color: var(--ip-warning);
        }

        .ip-stat--success .ip-stat__icon {
            background: #ebf8f0;
            color: var(--ip-success);
        }

        .ip-stat__value {
            color: var(--ip-text);
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
        }

        .ip-stat__label {
            margin-top: 4px;
            color: var(--ip-muted);
            font-size: 10px;
            font-weight: 750;
        }

        .ip-panel {
            overflow: hidden;
            border: 1px solid var(--ip-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 13px 36px rgba(15, 23, 42, .045);
        }

        .ip-panel + .ip-panel {
            margin-top: 16px;
        }

        .ip-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--ip-border);
        }

        .ip-panel__title {
            margin: 0;
            color: var(--ip-text);
            font-size: 17px;
            font-weight: 850;
        }

        .ip-panel__subtitle {
            margin-top: 3px;
            color: var(--ip-muted);
            font-size: 11px;
        }

        .ip-filter-tabs {
            display: flex;
            gap: 6px;
            padding: 5px;
            border: 1px solid #dbe5ea;
            border-radius: 12px;
            background: #f8fafc;
        }

        .ip-filter-tab {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 9px;
            color: #657386;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
        }

        .ip-filter-tab.active {
            background: #fff;
            color: var(--ip-primary-dark);
            box-shadow: 0 2px 8px rgba(15, 23, 42, .07);
        }

        .ip-filter-form {
            display: grid;
            grid-template-columns: minmax(240px, 1.5fr) minmax(220px, .8fr) auto;
            gap: 10px;
            padding: 15px 18px;
            border-bottom: 1px solid var(--ip-border);
            background: #fbfcfd;
        }

        .ip-control {
            width: 100%;
            min-height: 40px;
            padding: 8px 11px;
            border: 1px solid #d7e0e6;
            border-radius: 11px;
            background: #fff;
            color: var(--ip-text);
            font-size: 12px;
        }

        .ip-control:focus {
            border-color: var(--ip-primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(7,143,159,.09);
        }

        .ip-table-wrap {
            overflow-x: auto;
        }

        .ip-table {
            width: 100%;
            min-width: 980px;
            margin: 0;
            border-collapse: collapse;
        }

        .ip-table th {
            padding: 12px 16px;
            border-bottom: 1px solid var(--ip-border);
            background: #f8fafc;
            color: #6d7888;
            font-size: 10px;
            font-weight: 850;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: .045em;
        }

        .ip-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #edf1f4;
            color: var(--ip-text);
            font-size: 12px;
            vertical-align: middle;
        }

        .ip-table tr:last-child td {
            border-bottom: 0;
        }

        .ip-table tbody tr:hover {
            background: #f8fcfd;
        }

        .ip-patient {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 220px;
        }

        .ip-avatar {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border-radius: 12px;
            background: var(--ip-primary-soft);
            color: var(--ip-primary-dark);
            font-size: 16px;
        }

        .ip-main-text {
            font-size: 12px;
            font-weight: 850;
        }

        .ip-sub-text {
            margin-top: 2px;
            color: var(--ip-muted);
            font-size: 10px;
        }

        .ip-location {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #405064;
            font-weight: 750;
        }

        .ip-location i {
            color: var(--ip-primary);
        }

        .ip-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            background: #eaf8ef;
            color: #15803d;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .ip-badge--released {
            background: #eef2f7;
            color: #52606d;
        }

        .ip-empty {
            padding: 54px 20px;
            color: var(--ip-muted);
            text-align: center;
        }

        .ip-empty i {
            display: block;
            margin-bottom: 10px;
            color: #9bcfd5;
            font-size: 34px;
        }

        .ip-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 18px;
            border-top: 1px solid var(--ip-border);
            color: var(--ip-muted);
            font-size: 11px;
        }

        .ip-pagination__buttons {
            display: flex;
            gap: 6px;
        }

        .ip-page-btn {
            display: inline-flex;
            min-width: 34px;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            padding: 6px 9px;
            border: 1px solid #dce5eb;
            border-radius: 9px;
            background: #fff;
            color: #475467;
            text-decoration: none;
        }

        .ip-page-btn.disabled {
            opacity: .4;
            pointer-events: none;
        }

        @media (max-width: 1100px) {
            .ip-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 780px) {
            .ip-topbar__inner,
            .ip-nav,
            .ip-hero,
            .ip-panel__header,
            .ip-pagination {
                align-items: stretch;
                flex-direction: column;
            }

            .ip-context {
                width: fit-content;
                margin-left: 0;
            }

            .ip-page {
                padding: 18px 12px 32px;
            }

            .ip-stats,
            .ip-filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('main')
    <nav class="ip-topbar">
        <div class="container-fluid ip-topbar__inner d-flex align-items-center">
            <a class="ip-brand" href="{{ route('doctors.inpatient.discharges.index') }}">
                <span class="ip-brand__icon"><i class="bi bi-hospital"></i></span>
                <span>Стационар</span>
            </a>

            <div class="ip-nav">
                <a class="ip-nav-link active" href="{{ route('doctors.inpatient.discharges.index') }}">
                    <i class="bi bi-file-earmark-medical"></i>
                    Выписки
                </a>

                <a class="ip-nav-link" href="{{ route('doctors.inpatient.rooms.index') }}">
                    <i class="bi bi-door-open"></i>
                    Палаты и койки
                </a>

                <a class="ip-nav-link" href="{{ route('doctors.prescriptions.index') }}">
                    <i class="bi bi-capsule-pill"></i>
                    Лекарственные назначения
                </a>
            </div>

            <div class="ip-context">
                <i class="bi bi-building"></i>
                {{ $clinic->name }}
            </div>
        </div>
    </nav>

    <main class="ip-page">
        <div class="ip-container">
            <section class="ip-hero">
                <div>
                    <div class="ip-kicker">
                        <i class="bi bi-hospital"></i>
                        Стационарные пациенты
                    </div>
                    <h1 class="ip-title">Выписки из стационара</h1>
                    <p class="ip-description">
                        Здесь отображаются пациенты, размещённые на койках стационара, и выписанные сегодня.
                        Из этой панели можно открыть медицинскую карту пациента и подготовить клинические документы к выписке.
                    </p>
                </div>

                <div class="ip-hero__actions">
                    <a class="ip-button" href="{{ route('doctors.inpatient.rooms.index') }}">
                        <i class="bi bi-grid-3x3-gap"></i>
                        Палаты и койки
                    </a>
                </div>
            </section>

            <section class="ip-stats">
                <article class="ip-stat">
                    <span class="ip-stat__icon"><i class="bi bi-person-heart"></i></span>
                    <div>
                        <div class="ip-stat__value">{{ $activeCount }}</div>
                        <div class="ip-stat__label">сейчас в стационаре</div>
                    </div>
                </article>

                <article class="ip-stat ip-stat--blue">
                    <span class="ip-stat__icon"><i class="bi bi-box-arrow-in-right"></i></span>
                    <div>
                        <div class="ip-stat__value">{{ $admittedTodayCount }}</div>
                        <div class="ip-stat__label">размещено сегодня</div>
                    </div>
                </article>

                <article class="ip-stat ip-stat--warning">
                    <span class="ip-stat__icon"><i class="bi bi-calendar-week"></i></span>
                    <div>
                        <div class="ip-stat__value">{{ $longStayCount }}</div>
                        <div class="ip-stat__label">на лечении более 7 дней</div>
                    </div>
                </article>

                <article class="ip-stat ip-stat--success">
                    <span class="ip-stat__icon"><i class="bi bi-box-arrow-right"></i></span>
                    <div>
                        <div class="ip-stat__value">{{ $releasedTodayCount }}</div>
                        <div class="ip-stat__label">выписано сегодня</div>
                    </div>
                </article>
            </section>

            <section class="ip-panel">
                <div class="ip-panel__header">
                    <div>
                        <h2 class="ip-panel__title">
                            {{ $listMode === 'active' ? 'Пациенты на лечении' : 'Выписанные сегодня' }}
                        </h2>
                        <div class="ip-panel__subtitle">
                            Размещение пациента, дата поступления и быстрый переход в медицинскую карту
                        </div>
                    </div>

                    <div class="ip-filter-tabs">
                        <a class="ip-filter-tab {{ $listMode === 'active' ? 'active' : '' }}"
                           href="{{ route('doctors.inpatient.discharges.index', array_filter(['department_id' => $departmentId ?: null, 'q' => $search ?: null, 'status' => 'active'])) }}">
                            <i class="bi bi-person-check"></i>
                            На лечении
                        </a>
                        <a class="ip-filter-tab {{ $listMode === 'released_today' ? 'active' : '' }}"
                           href="{{ route('doctors.inpatient.discharges.index', array_filter(['department_id' => $departmentId ?: null, 'q' => $search ?: null, 'status' => 'released_today'])) }}">
                            <i class="bi bi-check2-circle"></i>
                            Выписаны сегодня
                        </a>
                    </div>
                </div>

                <form class="ip-filter-form" method="GET" action="{{ route('doctors.inpatient.discharges.index') }}">
                    <input type="hidden" name="status" value="{{ $listMode }}">

                    <input class="ip-control"
                           type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Поиск пациента по ФИО">

                    <select class="ip-control" name="department_id">
                        <option value="">Все отделения</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected($departmentId === (int) $department->id)>
                                {{ $department->number ? '№' . $department->number . ' · ' : '' }}{{ $department->name }}
                            </option>
                        @endforeach
                    </select>

                    <button class="ip-button ip-button--primary" type="submit">
                        <i class="bi bi-search"></i>
                        Найти
                    </button>
                </form>

                @if($assignments->count())
                    <div class="ip-table-wrap">
                        <table class="ip-table">
                            <thead>
                            <tr>
                                <th>Пациент</th>
                                <th>Отделение</th>
                                <th>Размещение</th>
                                <th>{{ $listMode === 'active' ? 'Поступил' : 'Выписан' }}</th>
                                <th>Дней</th>
                                <th>Статус</th>
                                <th style="text-align:right">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="ip-patient">
                                            <span class="ip-avatar"><i class="bi bi-person"></i></span>
                                            <div>
                                                <div class="ip-main-text">{{ $assignment['patient_name'] }}</div>
                                                <div class="ip-sub-text">
                                                    {{ $assignment['birth_at'] ? 'Дата рождения: ' . $assignment['birth_at'] : 'Дата рождения не указана' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ip-main-text">{{ $assignment['department'] }}</div>
                                        @if($assignment['department_number'])
                                            <div class="ip-sub-text">Отделение №{{ $assignment['department_number'] }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="ip-location">
                                            <i class="bi bi-door-open"></i>
                                            Палата {{ $assignment['room'] }}, койка {{ $assignment['bed'] }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ip-main-text">
                                            {{ $listMode === 'active' ? $assignment['assigned_at'] : ($assignment['released_at'] ?? '—') }}
                                        </div>
                                    </td>
                                    <td>{{ $assignment['days'] ?? '—' }}</td>
                                    <td>
                                        @if($listMode === 'active')
                                            <span class="ip-badge"><i class="bi bi-circle-fill"></i> На лечении</span>
                                        @else
                                            <span class="ip-badge ip-badge--released"><i class="bi bi-check2"></i> Выписан</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right">
                                        @if($assignment['medical_card_url'])
                                            <a class="ip-button" href="{{ $assignment['medical_card_url'] }}">
                                                <i class="bi bi-folder2-open"></i>
                                                Открыть карту
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="ip-pagination">
                        <div>
                            Показано {{ $assignments->firstItem() }}–{{ $assignments->lastItem() }} из {{ $assignments->total() }}
                        </div>

                        <div class="ip-pagination__buttons">
                            <a class="ip-page-btn {{ $assignments->onFirstPage() ? 'disabled' : '' }}"
                               href="{{ $assignments->previousPageUrl() ?: '#' }}"
                               aria-label="Предыдущая страница">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <span class="ip-page-btn">{{ $assignments->currentPage() }} / {{ $assignments->lastPage() }}</span>
                            <a class="ip-page-btn {{ $assignments->hasMorePages() ? '' : 'disabled' }}"
                               href="{{ $assignments->nextPageUrl() ?: '#' }}"
                               aria-label="Следующая страница">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="ip-empty">
                        <i class="bi bi-person-check"></i>
                        <strong>{{ $listMode === 'active' ? 'Пациентов на лечении не найдено' : 'Сегодня выписок пока нет' }}</strong>
                        <div class="mt-1">Измените фильтры или проверьте размещение пациентов по койкам.</div>
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
