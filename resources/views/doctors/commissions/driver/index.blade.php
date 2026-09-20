@extends('doctors.prescriptions')

@section('title', 'Водительская комиссия')
@section('prescription-title', 'Водительская комиссия')

@section('sub-main')
    @include('doctors.commissions.driver.partials.styles')

    <main class="driver-page">
        <div class="driver-container">
            <header class="driver-hero">
                <div class="driver-hero__main">
                    <div class="driver-hero__icon"><i class="bi bi-car-front"></i></div>
                    <div class="driver-hero__copy">
                        <div class="driver-eyebrow">Комиссии и экспертизы · психиатр</div>
                        <h1>Водительская комиссия</h1>
                        <p>Психиатрическое обследование водителей и кандидатов в водители, направления на психиатрическую ВК и справки психиатра.</p>
                    </div>
                </div>
                <div class="driver-hero__actions">
                    <a class="driver-button driver-button--ghost" target="_blank"
                       href="{{ route('doctors.commissions.driver.create') }}">
                        <i class="bi bi-printer"></i> Пустой бланк
                    </a>
                    <a class="driver-button driver-button--primary" href="{{ route('doctors.commissions.driver.create') }}">
                        <i class="bi bi-plus-lg"></i> Новое обследование
                    </a>
                </div>
            </header>

            @if(session('message'))
                <div class="driver-alert {{ session('status') === 'error' ? 'driver-alert--danger' : 'driver-alert--success' }}">
                    <i class="bi {{ session('status') === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill' }}"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <section class="driver-stats" aria-label="Сводка по водительским психиатрическим обследованиям">
                <article class="driver-stat">
                    <div class="driver-stat__icon"><i class="bi bi-files"></i></div>
                    <div class="driver-stat__copy"><span>Всего обследований</span><strong>{{ $stats['total'] }}</strong></div>
                </article>
                <article class="driver-stat">
                    <div class="driver-stat__icon"><i class="bi bi-pencil-square"></i></div>
                    <div class="driver-stat__copy"><span>Черновики</span><strong>{{ $stats['draft'] }}</strong></div>
                </article>
                <article class="driver-stat">
                    <div class="driver-stat__icon"><i class="bi bi-file-earmark-check"></i></div>
                    <div class="driver-stat__copy"><span>Справки выданы</span><strong>{{ $stats['certificates'] }}</strong></div>
                </article>
                <article class="driver-stat">
                    <div class="driver-stat__icon"><i class="bi bi-people"></i></div>
                    <div class="driver-stat__copy"><span>Направлены на ВК</span><strong>{{ $stats['referred'] }}</strong></div>
                </article>
            </section>

            <section class="driver-panel">
                <div class="driver-panel__head">
                    <div>
                        <h2>Поиск и фильтры</h2>
                        <p>Пациент, номер документа, справки, диагноз, результат или категория ТС.</p>
                    </div>
                    @if(request()->hasAny(['q', 'status', 'result', 'category', 'date_from', 'date_to']))
                        <a class="driver-filter-reset" href="{{ route('doctors.commissions.driver.index') }}">
                            <i class="bi bi-x-circle"></i> Сбросить
                        </a>
                    @endif
                </div>

                <form class="driver-filter-grid" method="GET" action="{{ route('doctors.commissions.driver.index') }}">
                    <div class="driver-field">
                        <label for="driverSearch">Поиск</label>
                        <div class="driver-search-wrap">
                            <i class="bi bi-search"></i>
                            <input id="driverSearch" class="driver-search-input" type="search" name="q"
                                   value="{{ request('q') }}" placeholder="Пациент, документ, справка, диагноз">
                        </div>
                    </div>
                    <div class="driver-field">
                        <label for="driverStatus">Статус</label>
                        <select id="driverStatus" name="status">
                            <option value="">Все статусы</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="driver-field">
                        <label for="driverResult">Результат</label>
                        <select id="driverResult" name="result">
                            <option value="">Все результаты</option>
                            @foreach($resultOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('result') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="driver-field">
                        <label for="driverCategory">Категория ТС</label>
                        <select id="driverCategory" name="category">
                            <option value="">Все категории</option>
                            @foreach($vehicleCategoryOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="driver-field">
                        <label for="driverFrom">С даты</label>
                        <input id="driverFrom" type="date" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="driver-field">
                        <label for="driverTo">По дату</label>
                        <input id="driverTo" type="date" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <button class="driver-button driver-button--secondary" type="submit">
                        <i class="bi bi-funnel"></i> Применить
                    </button>
                </form>
            </section>

            <section class="driver-table-card">
                @if($documents->count())
                    <div class="driver-table-wrap">
                        <table class="driver-table">
                            <thead>
                            <tr>
                                <th>Документ</th>
                                <th>Пациент</th>
                                <th>Дата / цель</th>
                                <th>Категории</th>
                                <th>Результат</th>
                                <th>Статус</th>
                                <th style="text-align:right">Действия</th>
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
                                    $categories = $document->vehicleCategoryLabels();
                                    $driverHardBlocked = $document->hasConfirmedDrivingContraindication();
                                    $driverOutputBlocked = $document->isDrivingDocumentOutputBlocked();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $document->document_number ?: 'Без номера' }}</strong>
                                        @if(!$driverOutputBlocked && $document->certificate_number)
                                            <span class="driver-table__muted">Справка {{ $document->certificate_number }}</span>
                                        @else
                                            <span class="driver-table__muted">ID {{ $document->id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="driver-patient-summary">
                                            <span class="driver-patient-summary__avatar">{{ $initials }}</span>
                                            <span class="driver-patient-summary__copy">
                                                <strong>{{ $document->patientFullName() }}</strong>
                                                <span>{{ $document->diagnosisLabel() }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $document->examination_date?->format('d.m.Y') ?: '—' }}</strong>
                                        <span class="driver-table__muted">{{ $document->purposeLabel() }}</span>
                                    </td>
                                    <td>
                                        @if($categories)
                                            <div class="driver-chip-list">
                                                @foreach(array_slice($categories, 0, 5) as $category)
                                                    <span class="driver-chip">{{ $category }}</span>
                                                @endforeach
                                                @if(count($categories) > 5)
                                                    <span class="driver-chip">+{{ count($categories) - 5 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="driver-table__muted">Не выбраны</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $document->resultLabel() }}</strong>
                                        @if($driverHardBlocked)
                                            <span class="driver-table__muted" style="color:#a33c3c;font-weight:800"><i class="bi bi-lock-fill"></i> Водительский документ заблокирован</span>
                                        @endif
                                        @if($document->commission_protocol_number)
                                            <span class="driver-table__muted">ВК № {{ $document->commission_protocol_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="driver-badge driver-badge--{{ $document->status === 'prepared' ? 'prepared' : 'draft' }}">
                                            {{ $document->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="driver-actions">
                                            <a class="driver-action" title="Просмотр"
                                               href="{{ route('doctors.commissions.driver.show', $document) }}"><i class="bi bi-eye"></i></a>
                                            @if($driverHardBlocked)
                                                <span class="driver-action driver-action--disabled" title="Редактирование заблокировано: подтверждено противопоказание"><i class="bi bi-lock-fill"></i></span>
                                            @else
                                                <a class="driver-action" title="Редактировать"
                                                   href="{{ route('doctors.commissions.driver.edit', $document) }}"><i class="bi bi-pencil"></i></a>
                                            @endif
                                            @if($driverOutputBlocked)
                                                <span class="driver-action driver-action--disabled" title="Печать заблокирована"><i class="bi bi-printer-fill"></i></span>
                                            @else
                                                <a class="driver-action" title="Печать" target="_blank"
                                                   href="{{ route('doctors.commissions.driver.print', $document) }}"><i class="bi bi-printer"></i></a>
                                            @endif
                                            @if($document->status === 'draft')
                                                <form class="driver-action-form" method="POST"
                                                      action="{{ route('doctors.commissions.driver.destroy', $document) }}"
                                                      onsubmit="return confirm('Удалить этот черновик?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="driver-action driver-action--danger" title="Удалить" type="submit"><i class="bi bi-trash3"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="driver-table-footer">
                        <span>Показано <strong>{{ $documents->count() }}</strong> из <strong>{{ $documents->total() }}</strong></span>
                        <div>{{ $documents->links() }}</div>
                    </div>
                @else
                    <div class="driver-empty">
                        <i class="bi bi-car-front"></i>
                        <h3>Обследования не найдены</h3>
                        <p>Создайте первое обследование или измените параметры поиска.</p>
                        <a class="driver-button driver-button--primary" href="{{ route('doctors.commissions.driver.create') }}">
                            <i class="bi bi-plus-lg"></i> Новое обследование
                        </a>
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
