@extends('doctors.analyses')

@section('analysis-title', 'Лаборатория — обзор')

@section('analysis-content')
    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Оперативный контроль</div>
                    <h1 class="lab-title">Лабораторные исследования</h1>
                    <p class="lab-description">
                        Просмотр новых результатов, контроль отклонений и отслеживание
                        исследований, требующих внимания врача.
                    </p>
                </div>

                <div class="lab-hero__actions">
                    <a class="lab-button lab-button--secondary" href="{{ route('doctors.analyses.journal') }}">
                        <i class="bi bi-journal-medical"></i>
                        Открыть журнал
                    </a>

                    <a class="lab-button lab-button--primary" href="#">
                        <i class="bi bi-plus-circle"></i>
                        Назначить исследование
                    </a>
                </div>
            </section>

            <section class="lab-stats">
                <article class="lab-stat">
                    <div class="lab-stat__label">Новые результаты</div>
                    <div class="lab-stat__value">{{ $newResearchesResultCount }}</div>
                    <div class="lab-stat__hint">Ещё не просмотрены врачом</div>
                </article>

                <article class="lab-stat lab-stat--danger">
                    <div class="lab-stat__label">Критические значения</div>
                    <div class="lab-stat__value">{{ $criticalResearchesResultCount }}</div>
                    <div class="lab-stat__hint">Требуют срочного внимания</div>
                </article>

                <article class="lab-stat lab-stat--warning">
                    <div class="lab-stat__label">В работе</div>
                    <div class="lab-stat__value">{{ $isProgressResearchesResultsCount }}</div>
                    <div class="lab-stat__hint">Исследования без результата</div>
                </article>

                <article class="lab-stat lab-stat--neutral">
                    <div class="lab-stat__label">Просрочено</div>
                    <div class="lab-stat__value">{{ $outdatedResearchesCount }}</div>
                    <div class="lab-stat__hint">Превышен ожидаемый срок</div>
                </article>
            </section>

            <div class="lab-grid">
                <main>
                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <div>
                                <h2 class="lab-panel__title">Последние результаты</h2>
                                <div class="lab-panel__subtitle">
                                    Новые и недавно обновлённые исследования
                                </div>
                            </div>

                            <a class="lab-action" href="#">
                                Показать все
                            </a>
                        </div>

                        @if($newResearchesResultCount > 0)
                            <div class="lab-table-wrap">
                                <table class="table lab-table">
                                    <thead>
                                    <tr>
                                        <th>Пациент</th>
                                        <th>Исследование</th>
                                        <th>Результат</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th class="text-end">Действия</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($recentResults as $row)
                                        @php($patient = $row->patient)
                                        <tr>
                                            <td>
                                                <div class="lab-patient">
                                                    <div class="lab-avatar">{{ mb_substr(ucfirst($patient->surname), 0, 1) . mb_substr(ucfirst($patient->name), 0, 1) }}</div>
                                                    <div>
                                                        <div class="lab-primary-text">{{ $patient->surname . ' ' . $patient->name . ' ' . $patient->patronym}}</div>
                                                        <div class="lab-secondary-text">
                                                            {{ $patient->birth_at }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="lab-primary-text">
                                                    {{ $row->name }}
                                                </div>
                                                <div class="lab-secondary-text">
                                                    {{ $row->sample_type }}
                                                </div>
                                            </td>

                                            <td>
                                                <div class="lab-result-value {{ data_get($row, 'value_class') }}">
                                                    {{ data_get($row, 'summary_value', '—') }}
                                                </div>
                                                <div class="lab-secondary-text">
                                                    {{ data_get($row, 'summary_text', 'Результат сформирован') }}
                                                </div>
                                            </td>

                                            <td>
                                                <span class="lab-status lab-status--{{ $row->status }}">
                                                    {{ data_get($row, 'status_label', 'Готово') }}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="lab-primary-text">
                                                    {{ data_get($row, 'result_date', '—') }}
                                                </div>
                                                <div class="lab-secondary-text">
                                                    {{ data_get($row, 'result_time', '') }}
                                                </div>
                                            </td>

                                            <td>
                                                <div class="lab-actions">
                                                    @if(data_get($row, 'medical_card_url'))
                                                        <a class="lab-action" href="{{ data_get($row, 'medical_card_url') }}">
                                                            Медкарта
                                                        </a>
                                                    @endif

                                                    <a
                                                        class="lab-action lab-action--primary"
                                                        href="{{ data_get($row, 'result_url', '#') }}"
                                                    >
                                                        Результат
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="lab-panel__body">
                                <div class="lab-empty">
                                    <div class="lab-empty__icon">🧪</div>
                                    <h3>Результатов пока нет</h3>
                                    <p>
                                        После поступления лабораторных результатов они появятся
                                        на этой странице.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </section>
                </main>

                <section class="lab-panel">
                    <div class="lab-panel__header">
                        <div>
                            <h2 class="lab-panel__title">
                                Требуют внимания
                            </h2>

                            <div class="lab-panel__subtitle">
                                Результаты с отклонениями и критическими значениями
                            </div>
                        </div>

                        <span class="lab-count">
            {{ $attentionItems->count() }}
        </span>
                    </div>

                    <div class="lab-panel__body">
                        @if($attentionItems->isNotEmpty())
                            <div class="lab-attention-list">
                                @foreach($attentionItems as $item)
                                    <article
                                        class="lab-attention-card
                        {{ data_get($item, 'critical')
                            ? 'lab-attention-card--critical'
                            : '' }}"
                                    >
                                        <div class="lab-attention-card__top">
                                            <div>
                                                <div class="lab-primary-text">
                                                    {{ $item->patient->full_name }}
                                                </div>

                                                <div class="lab-secondary-text">
                                                    Анализ от
                                                    {{ $item->research_date->format('d.m.Y') }}
                                                </div>
                                            </div>

                                            <span
                                                class="lab-status
                                lab-status--{{ data_get($item, 'critical')
                                    ? 'critical'
                                    : 'warning' }}"
                                            >
                                {{ data_get($item, 'critical')
                                    ? 'Критично'
                                    : 'Отклонение' }}
                            </span>
                                        </div>

                                        <div class="lab-primary-text mt-3">
                                            {{ $item->attention_parameter_name }}
                                        </div>

                                        <div class="lab-attention-card__value">
                                            {{ $item->attention_value }}
                                            {{ $item->attention_unit }}
                                        </div>

                                        <div class="lab-secondary-text">
                                            Референс:
                                            {{ $item->attention_reference }}
                                        </div>

                                        <div class="mt-3">
                                            <a
                                                class="lab-action"
                                                href="{{ $item->result_url }}"
                                            >
                                                Открыть результат
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="lab-empty">
                                <div class="lab-empty__icon">✓</div>

                                <h3>Всё спокойно</h3>

                                <p>
                                    Результатов, требующих срочного внимания, нет.
                                </p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
