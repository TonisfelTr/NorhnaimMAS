@extends('doctors.prescriptions')

@section('title', 'МСЭ / форма 088/у')
@section('prescription-title', 'МСЭ / форма 088/у')

@section('sub-main')
    @include('doctors.commissions.mse.partials.styles')


    <style>
        /*
         * Компактный desktop-режим журнала МСЭ.
         * Панель не рассчитана на телефоны, поэтому уменьшаем вертикальные отступы,
         * контролы и таблицу именно на странице журнала, не затрагивая форму 088/у.
         */
        .mse-index-compact {
            padding: 14px 12px 34px;
            overflow-x: hidden;
        }

        .mse-index-compact .mse-container,
        .mse-index-compact .mse-table-card,
        .mse-index-compact .mse-table-wrap {
            min-width: 0;
            max-width: 100%;
        }

        .mse-index-compact .mse-hero {
            gap: 18px;
            padding: 20px 22px;
            margin-bottom: 14px;
            border-radius: 20px;
        }

        .mse-index-compact .mse-hero__main {
            gap: 14px;
            min-width: 0;
        }

        .mse-index-compact .mse-hero__icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            font-size: 22px;
        }

        .mse-index-compact .mse-eyebrow {
            margin-bottom: 3px;
            font-size: 10px;
        }

        .mse-index-compact .mse-hero h1 {
            font-size: clamp(23px, 1.7vw, 29px);
            line-height: 1.12;
        }

        .mse-index-compact .mse-hero p {
            margin-top: 5px;
            font-size: 13px;
            line-height: 1.35;
        }

        .mse-index-compact .mse-button {
            min-height: 40px;
            gap: 7px;
            padding: 8px 13px;
            border-radius: 11px;
            font-size: 13px;
        }

        .mse-index-compact .mse-stats {
            gap: 10px;
            margin-bottom: 14px;
        }

        .mse-index-compact .mse-stat {
            gap: 11px;
            padding: 14px 16px;
            border-radius: 18px;
        }

        .mse-index-compact .mse-stat__icon {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            font-size: 17px;
        }

        .mse-index-compact .mse-stat__copy span {
            font-size: 12px;
        }

        .mse-index-compact .mse-stat__copy strong {
            margin-top: 2px;
            font-size: 22px;
        }

        .mse-index-compact .mse-filter {
            padding: 16px 18px;
            margin-bottom: 14px;
            border-radius: 20px;
        }

        .mse-index-compact .mse-filter__head {
            gap: 14px;
            margin-bottom: 11px;
        }

        .mse-index-compact .mse-filter__head h2 {
            font-size: 17px;
        }

        .mse-index-compact .mse-filter__head p {
            margin-top: 2px;
            font-size: 12px;
        }

        .mse-index-compact .mse-filter__grid {
            grid-template-columns:
                minmax(195px, 1.45fr)
                minmax(108px, .72fr)
                minmax(122px, .82fr)
                minmax(118px, .78fr)
                minmax(116px, .72fr)
                minmax(116px, .72fr)
                auto;
            gap: 8px;
        }

        .mse-index-compact .mse-field label {
            margin-bottom: 5px;
            font-size: 12px;
        }

        .mse-index-compact .mse-field input,
        .mse-index-compact .mse-field select,
        .mse-index-compact .mse-search-input {
            min-height: 40px;
            padding: 8px 10px;
            border-radius: 11px;
            font-size: 13px;
        }

        .mse-index-compact .mse-search-wrap > i {
            left: 12px;
            font-size: 13px;
        }

        .mse-index-compact .mse-search-wrap .mse-search-input {
            padding-left: 34px;
        }

        .mse-index-compact .mse-table-card {
            border-radius: 20px;
        }

        .mse-index-compact .mse-table-wrap {
            overflow-x: auto;
            border-radius: 20px 20px 0 0;
        }

        .mse-index-compact .mse-table {
            width: 100%;
            min-width: 980px;
            table-layout: fixed;
        }

        .mse-index-compact .mse-table th {
            padding: 10px 11px;
            font-size: 10px;
            line-height: 1.25;
        }

        .mse-index-compact .mse-table td {
            padding: 12px 11px;
            font-size: 13px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .mse-index-compact .mse-table th:nth-child(1) { width: 14%; }
        .mse-index-compact .mse-table th:nth-child(2) { width: 26%; }
        .mse-index-compact .mse-table th:nth-child(3) { width: 11%; }
        .mse-index-compact .mse-table th:nth-child(4) { width: 17%; }
        .mse-index-compact .mse-table th:nth-child(5) { width: 13%; }
        .mse-index-compact .mse-table th:nth-child(6) { width: 9%; }
        .mse-index-compact .mse-table th:nth-child(7) { width: 150px; }

        .mse-index-compact .mse-table__muted {
            margin-top: 2px;
            font-size: 11px;
            line-height: 1.35;
        }

        .mse-index-compact .mse-patient-summary {
            gap: 9px;
            min-width: 0;
        }

        .mse-index-compact .mse-patient-summary__avatar {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            font-size: 12px;
        }

        .mse-index-compact .mse-patient-summary__copy strong,
        .mse-index-compact .mse-patient-summary__copy span {
            max-width: 100%;
        }

        .mse-index-compact .mse-patient-summary__copy strong {
            display: block;
            line-height: 1.3;
        }

        .mse-index-compact .mse-form-chip {
            min-height: 23px;
            padding: 3px 7px;
            font-size: 10px;
        }

        .mse-index-compact .mse-badge {
            gap: 6px;
            padding: 6px 9px;
            font-size: 10px;
        }

        .mse-index-compact .mse-badge::before {
            width: 6px;
            height: 6px;
        }

        .mse-index-compact .mse-actions {
            gap: 5px;
        }

        .mse-index-compact .mse-action {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            font-size: 13px;
        }

        .mse-index-compact .mse-table-footer {
            padding: 12px 14px;
            font-size: 12px;
        }

        /*
         * Узкий desktop: при небольшой рабочей области (например, с боковой панелью)
         * не растягиваем фильтр в несколько строк слишком рано.
         */
        @media (max-width: 1700px) {
            .mse-index-compact .mse-filter__grid {
                grid-template-columns:
                    minmax(185px, 1.4fr)
                    minmax(104px, .7fr)
                    minmax(116px, .8fr)
                    minmax(112px, .76fr)
                    minmax(112px, .7fr)
                    minmax(112px, .7fr)
                    auto;
            }
        }

        @media (max-width: 1380px) {
            .mse-index-compact .mse-filter__grid {
                grid-template-columns: minmax(190px, 1.4fr) repeat(3, minmax(120px, .8fr));
            }

            .mse-index-compact .mse-filter__grid .mse-field:nth-of-type(5),
            .mse-index-compact .mse-filter__grid .mse-field:nth-of-type(6),
            .mse-index-compact .mse-filter__grid > .mse-button {
                min-width: 0;
            }
        }
    </style>

    <main class="mse-page mse-index-compact">
        <div class="mse-container">
            <header class="mse-hero">
                <div class="mse-hero__main">
                    <div class="mse-hero__icon"><i class="bi bi-universal-access"></i></div>
                    <div>
                        <div class="mse-eyebrow">Комиссии и экспертизы · форма 088/у</div>
                        <h1>Медико-социальная экспертиза</h1>
                        <p>Журнал направлений на МСЭ, решений врачебной комиссии и подготовленных документов.</p>
                    </div>
                </div>

                <div class="mse-hero__actions">
                    <a class="mse-button mse-button--primary"
                       href="{{ route('doctors.commissions.mse.create') }}">
                        <i class="bi bi-plus-lg"></i>
                        Новое направление
                    </a>
                </div>
            </header>

            @if(session('message'))
                <div class="mse-alert {{ session('status') === 'error' ? 'mse-alert--danger' : 'mse-alert--success' }}">
                    <i class="bi {{ session('status') === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill' }}"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <section class="mse-stats" aria-label="Сводка по направлениям">
                <article class="mse-stat">
                    <div class="mse-stat__icon"><i class="bi bi-files"></i></div>
                    <div class="mse-stat__copy">
                        <span>Всего направлений</span>
                        <strong>{{ $stats['total'] }}</strong>
                    </div>
                </article>
                <article class="mse-stat">
                    <div class="mse-stat__icon"><i class="bi bi-pencil-square"></i></div>
                    <div class="mse-stat__copy">
                        <span>Черновики</span>
                        <strong>{{ $stats['draft'] }}</strong>
                    </div>
                </article>
                <article class="mse-stat">
                    <div class="mse-stat__icon"><i class="bi bi-file-earmark-check"></i></div>
                    <div class="mse-stat__copy">
                        <span>Оформлено</span>
                        <strong>{{ $stats['prepared'] }}</strong>
                    </div>
                </article>
            </section>

            <section class="mse-filter">
                <div class="mse-filter__head">
                    <div>
                        <h2>Поиск и фильтры</h2>
                        <p>Поиск по пациенту, номеру направления, протоколу ВК или диагнозу.</p>
                    </div>
                    @if(request()->hasAny(['q', 'status', 'referral_kind', 'purpose', 'date_from', 'date_to']))
                        <a class="mse-filter-reset" href="{{ route('doctors.commissions.mse.index') }}">
                            <i class="bi bi-x-circle"></i> Сбросить
                        </a>
                    @endif
                </div>

                <form class="mse-filter__grid" method="GET" action="{{ route('doctors.commissions.mse.index') }}">
                    <div class="mse-field">
                        <label for="mseSearch">Поиск</label>
                        <div class="mse-search-wrap">
                            <i class="bi bi-search"></i>
                            <input id="mseSearch" class="mse-search-input" type="search" name="q"
                                   value="{{ request('q') }}" placeholder="Пациент, номер, протокол или диагноз">
                        </div>
                    </div>
                    <div class="mse-field">
                        <label for="mseStatus">Статус</label>
                        <select id="mseStatus" name="status">
                            <option value="">Все статусы</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mse-field">
                        <label for="mseKind">Направление</label>
                        <select id="mseKind" name="referral_kind">
                            <option value="">Первично и повторно</option>
                            @foreach($referralKindOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('referral_kind') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mse-field">
                        <label for="msePurpose">Цель МСЭ</label>
                        <select id="msePurpose" name="purpose">
                            <option value="">Все цели</option>
                            @foreach($purposeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('purpose') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mse-field">
                        <label for="mseFrom">С даты</label>
                        <input id="mseFrom" type="date" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="mse-field">
                        <label for="mseTo">По дату</label>
                        <input id="mseTo" type="date" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <button class="mse-button mse-button--secondary" type="submit">
                        <i class="bi bi-funnel"></i> Применить
                    </button>
                </form>
            </section>

            <section class="mse-table-card">
                @if($documents->count())
                    <div class="mse-table-wrap">
                        <table class="mse-table">
                            <thead>
                            <tr>
                                <th>Направление</th>
                                <th>Пациент</th>
                                <th>Дата и вид</th>
                                <th>Протокол / формат</th>
                                <th>Цели МСЭ</th>
                                <th>Статус</th>
                                <th style="text-align: right">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($documents as $document)
                                @php
                                    $nameParts = preg_split('/\s+/u', trim($document->patientFullName())) ?: [];
                                    $initials = collect($nameParts)
                                        ->take(2)
                                        ->map(fn ($part) => mb_strtoupper(mb_substr((string) $part, 0, 1)))
                                        ->implode('') ?: '—';
                                    $purposeLabels = $document->purposeLabels();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $document->document_number ?: 'Без номера' }}</strong>
                                        <span class="mse-table__muted">ID {{ $document->id }}</span>
                                    </td>
                                    <td>
                                        <div class="mse-patient-summary">
                                            <span class="mse-patient-summary__avatar">{{ $initials }}</span>
                                            <span class="mse-patient-summary__copy">
                                                <strong>{{ $document->patientFullName() }}</strong>
                                                <span>{{ $document->diagnosisLabel() }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $document->document_date?->format('d.m.Y') ?: '—' }}</strong>
                                        <span class="mse-table__muted">{{ $document->referralKindLabel() }}</span>
                                    </td>
                                    <td>
                                        <strong>
                                            {{ $document->protocol_number ? '№ ' . $document->protocol_number : 'Протокол не указан' }}
                                        </strong>
                                        <span class="mse-table__muted">
                                            @if($document->protocol_date)
                                                {{ $document->protocol_date->format('d.m.Y') }} ·
                                            @endif
                                            {{ $document->examinationFormatLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(count($purposeLabels))
                                            <div class="mse-chip-list">
                                                @foreach(array_slice($purposeLabels, 0, 2) as $label)
                                                    <span class="mse-form-chip mse-purpose-chip">{{ preg_replace('/^5\.\d+\.\s*/u', '', $label) }}</span>
                                                @endforeach
                                                @if(count($purposeLabels) > 2)
                                                    <span class="mse-form-chip">+{{ count($purposeLabels) - 2 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="mse-table__muted">Не выбраны</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="mse-badge mse-badge--{{ $document->status === 'prepared' ? 'prepared' : 'draft' }}">
                                            {{ $document->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="mse-actions">
                                            <a class="mse-action" data-tooltip="Просмотреть"
                                               href="{{ route('doctors.commissions.mse.show', $document) }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="mse-action" data-tooltip="Редактировать"
                                               href="{{ route('doctors.commissions.mse.edit', $document) }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a class="mse-action" data-tooltip="Печать" target="_blank"
                                               href="{{ route('doctors.commissions.mse.print', $document) }}">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            @if($document->status === 'draft')
                                                <form class="mse-action-form" method="POST"
                                                      action="{{ route('doctors.commissions.mse.destroy', $document) }}"
                                                      onsubmit="return confirm('Удалить этот черновик?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="mse-action mse-action--danger" data-tooltip="Удалить" type="submit">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="mse-action mse-action--disabled" data-tooltip="Оформленное направление нельзя удалить">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mse-table-footer">
                        <span>Показано <strong>{{ $documents->count() }}</strong> из <strong>{{ $documents->total() }}</strong></span>
                        <div>{{ $documents->links() }}</div>
                    </div>
                @else
                    <div class="mse-empty">
                        <i class="bi bi-universal-access"></i>
                        <h3>Направления не найдены</h3>
                        <p>Создайте первое направление или измените параметры поиска.</p>
                        <a class="mse-button mse-button--primary" href="{{ route('doctors.commissions.mse.create') }}">
                            <i class="bi bi-plus-lg"></i> Новое направление
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
