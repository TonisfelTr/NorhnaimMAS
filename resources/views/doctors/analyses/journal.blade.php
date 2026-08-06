@extends('doctors.analyses')

@section('analysis-title', 'Журнал лабораторных исследований')

@section('analysis-content')
    @php
        $results = $results ?? collect();
        $rows = method_exists($results, 'items')
            ? collect($results->items())
            : collect($results);
        $labBase = url('/doctors/analyses');
    @endphp

    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Полный журнал</div>
                    <h1 class="lab-title">Все лабораторные исследования</h1>
                    <p class="lab-description">
                        Поиск результатов по пациенту, периоду, типу исследования и статусу.
                    </p>
                </div>
            </section>

            <section class="lab-panel mb-4">
                <div class="lab-panel__header">
                    <h2 class="lab-panel__title">Фильтры</h2>
                </div>

                <div class="lab-panel__body">
                    <form method="GET" class="lab-filters">
                        <div class="lab-filter--4">
                            <label class="lab-label">Пациент или исследование</label>
                            <input
                                class="lab-control"
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="ФИО, название исследования..."
                            >
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label">Дата с</label>
                            <input class="lab-control" type="date" name="date_from" value="{{ request('date_from') }}">
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label">Дата по</label>
                            <input class="lab-control" type="date" name="date_to" value="{{ request('date_to') }}">
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label">Статус</label>
                            <select class="lab-control" name="status">
                                <option value="">Все статусы</option>
                                <option value="new" @selected(request('status') === 'new')>Новый</option>
                                <option value="normal" @selected(request('status') === 'normal')>Норма</option>
                                <option value="warning" @selected(request('status') === 'warning')>Отклонение</option>
                                <option value="critical" @selected(request('status') === 'critical')>Критический</option>
                                <option value="pending" @selected(request('status') === 'pending')>Ожидается</option>
                                <option value="ready" @selected(request('status') === 'ready')>Готов</option>
                            </select>
                        </div>

                        <div class="lab-filter--2 d-flex gap-2">
                            <button class="lab-button lab-button--primary flex-fill" type="submit">
                                Найти
                            </button>

                            <a class="lab-button lab-button--secondary" href="{{ route('doctors.analyses.journal') }}">
                                Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="lab-panel">
                <div class="lab-panel__header">
                    <div>
                        <h2 class="lab-panel__title">Журнал исследований</h2>
                        <div class="lab-panel__subtitle">
                            {{ method_exists($results, 'total') ? $results->total() : $rows->count() }} записей
                        </div>
                    </div>
                </div>

                @if($instrumentalResearches->isNotEmpty())
                    <div class="lab-table-wrap">
                        <table class="table lab-table">
                            <thead>
                            <tr>
                                <th>Пациент</th>
                                <th>Исследование</th>
                                <th>Результаты</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th class="text-end">Действия</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($instrumentalResearches as $row)
                                <tr>
                                    <td>
                                        <div class="lab-patient">
                                            <div class="lab-avatar">{{ ucfirst(mb_substr($row->patient->name, 0, 1)) . ucfirst(mb_substr($row->patient->surname, 0, 1)) }}</div>
                                            <div>
                                                <div class="lab-primary-text">{{ $row->patient->surname }} {{ $row->patient->name }} {{ $row->patient->patronym }}</div>
                                                <div class="lab-secondary-text">
                                                    {{ $row->patient->birth_at }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ $row->name }}
                                        </div>
                                        <div class="lab-secondary-text">
                                            {{ $row->organization ?? 'Лаборатория не указана' }}
                                        </div>
                                    </td>

                                    <td>
                                        @php
                                            $mediaCount = $row
                                                ->getMedia(
                                                    \App\Models\InstrumentalResearch::MEDIA_COLLECTION_RESULTS
                                                )
                                                ->count();
                                        @endphp

                                        @if($row->status === 'ready')
                                            <div class="lab-result-value text-success">
                                                Заключение готово
                                            </div>

                                            <div class="lab-secondary-text">
                                                @if($mediaCount > 0)
                                                    {{ $mediaCount }}
                                                    {{ $mediaCount === 1 ? 'цифровой материал' : 'цифровых материала' }}
                                                @else
                                                    Без цифровых материалов
                                                @endif
                                            </div>
                                        @elseif($row->status === 'performed')
                                            <div class="lab-result-value text-warning">
                                                Ожидается заключение
                                            </div>

                                            <div class="lab-secondary-text">
                                                Исследование выполнено
                                            </div>
                                        @else
                                            <div class="lab-result-value">
                                                Результата пока нет
                                            </div>

                                            <div class="lab-secondary-text">
                                                Исследование ещё не завершено
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ \Carbon\Carbon::parse($row->result_at)->format('d.m.Y') }}
                                        </div>
                                        <div class="lab-secondary-text">
                                            {{ data_get($row, 'result_time', '') }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="lab-status lab-status--{{ $row->status }}">
                                            {{ $row->status == 'ready' ? 'Готово' : 'В обработке' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="lab-actions">
                                            @if(data_get($row, 'medical_card_url'))
                                                <a class="lab-action" href="{{ data_get($row, 'medical_card_url') }}">
                                                    Медкарта
                                                </a>
                                            @endif

                                                <a class="lab-action lab-action--primary"
                                                    href="{{ route('doctors.patients.medical_card', [
                                                            'patient' => data_get($row, 'patient_id'),
                                                            'tab' => 'labs',
                                                            'section' => 'instrumental',
                                                            'instrumental_research' => data_get($row, 'id'),
]                                                            ) }}">Открыть</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($results, 'links'))
                        <div class="lab-pagination">
                            {{ $results->withQueryString()->links() }}
                        </div>
                    @endif
                @else
                    <div class="lab-panel__body">
                        <div class="lab-empty">
                            <div class="lab-empty__icon">📋</div>
                            <h3>Исследования не найдены</h3>
                            <p>Измените параметры поиска или загрузите новый результат.</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
