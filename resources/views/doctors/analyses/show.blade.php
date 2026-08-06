@extends('doctors.analyses')

@section('analysis-title', 'Результат лабораторного исследования')

@section('analysis-content')
    @php
        $parameters = collect($parameters ?? []);
        $labBase = url('/doctors/analyses');
        $resultStatus = $resultStatus ?? 'normal';
    @endphp

    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Результат исследования</div>

                    <h1 class="lab-title">
                        {{ $researchName ?? 'Лабораторное исследование' }}
                    </h1>

                    <p class="lab-description">
                        {{ $patientName ?? 'Пациент не указан' }}

                        @if(!empty($patientBirthDate))
                            · {{ $patientBirthDate }}
                        @endif
                    </p>
                </div>

                <div class="lab-hero__actions">
                    <button
                        class="lab-button lab-button--secondary"
                        type="button"
                        onclick="window.print()"
                    >
                        <i class="bi bi-printer"></i>
                        Печать
                    </button>

                    <a
                        class="lab-button lab-button--secondary"
                        href="{{ $backUrl ?? $labBase . '/journal' }}"
                    >
                        Назад
                    </a>

                    @if(!empty($medicalCardUrl))
                        <a class="lab-button lab-button--primary" href="{{ $medicalCardUrl }}">
                            Открыть медкарту
                        </a>
                    @endif
                </div>
            </section>

            <section class="lab-stats">
                <article class="lab-stat">
                    <div class="lab-stat__label">Показателей</div>
                    <div class="lab-stat__value">{{ $parameters->count() }}</div>
                    <div class="lab-stat__hint">В составе исследования</div>
                </article>

                <article class="lab-stat lab-stat--warning">
                    <div class="lab-stat__label">С отклонениями</div>
                    <div class="lab-stat__value">
                        {{ $parameters->whereIn('status', ['high', 'low', 'warning', 'critical'])->count() }}
                    </div>
                    <div class="lab-stat__hint">Вне референсных значений</div>
                </article>

                <article class="lab-stat lab-stat--danger">
                    <div class="lab-stat__label">Критических</div>
                    <div class="lab-stat__value">
                        {{ $parameters->where('status', 'critical')->count() }}
                    </div>
                    <div class="lab-stat__hint">Требуют внимания</div>
                </article>

                <article class="lab-stat lab-stat--neutral">
                    <div class="lab-stat__label">Статус результата</div>
                    <div class="lab-stat__value" style="font-size: 19px; line-height: 1.25;">
                        <span class="lab-status lab-status--{{ $resultStatus }}">
                            {{ $resultStatusLabel ?? 'Готово' }}
                        </span>
                    </div>
                    <div class="lab-stat__hint">
                        {{ $reviewStatusLabel ?? 'Не просмотрено врачом' }}
                    </div>
                </article>
            </section>

            <div class="lab-detail-grid">
                <main>
                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <div>
                                <h2 class="lab-panel__title">Показатели</h2>
                                <div class="lab-panel__subtitle">
                                    Результаты и референсные значения
                                </div>
                            </div>
                        </div>

                        @if($parameters->isNotEmpty())
                            <div class="lab-table-wrap">
                                <table class="table lab-table">
                                    <thead>
                                    <tr>
                                        <th>Показатель</th>
                                        <th>Результат</th>
                                        <th>Референс</th>
                                        <th>Единицы</th>
                                        <th>Оценка</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($parameters as $parameter)
                                        @php
                                            $status = data_get($parameter, 'status', 'normal');
                                            $valueClass = match($status) {
                                                'critical', 'high', 'low' => 'lab-result-value--danger',
                                                'warning' => 'lab-result-value--warning',
                                                default => '',
                                            };
                                        @endphp

                                        <tr>
                                            <td>
                                                <div class="lab-primary-text">
                                                    {{ data_get($parameter, 'name', 'Показатель') }}
                                                </div>

                                                @if(data_get($parameter, 'code'))
                                                    <div class="lab-secondary-text">
                                                        {{ data_get($parameter, 'code') }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="lab-result-value {{ $valueClass }}">
                                                    {{ data_get($parameter, 'value', '—') }}
                                                </div>
                                            </td>

                                            <td>{{ data_get($parameter, 'reference', '—') }}</td>
                                            <td>{{ data_get($parameter, 'unit', '—') }}</td>

                                            <td>
                                                <span class="lab-status lab-status--{{ match($status) {
                                                    'critical' => 'critical',
                                                    'high', 'low', 'warning' => 'warning',
                                                    default => 'normal',
                                                } }}">
                                                    {{ data_get($parameter, 'status_label', 'Норма') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="lab-panel__body">
                                <div class="lab-empty">
                                    <div class="lab-empty__icon">🧫</div>
                                    <h3>Показатели не найдены</h3>
                                    <p>
                                        В результате отсутствуют структурированные
                                        лабораторные показатели.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </section>

                    @if(!empty($doctorComment))
                        <section class="lab-panel">
                            <div class="lab-panel__header">
                                <h2 class="lab-panel__title">Комментарий врача</h2>
                            </div>

                            <div class="lab-panel__body">
                                <div class="lab-note">
                                    {!! nl2br(e($doctorComment)) !!}
                                </div>
                            </div>
                        </section>
                    @endif
                </main>

                <aside>
                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <h2 class="lab-panel__title">Информация</h2>
                        </div>

                        <div class="lab-panel__body">
                            <div class="lab-info-list">
                                <div class="lab-info-row">
                                    <div class="lab-info-row__label">Дата забора</div>
                                    <div class="lab-info-row__value">{{ $collectedAt ?? '—' }}</div>
                                </div>

                                <div class="lab-info-row">
                                    <div class="lab-info-row__label">Дата результата</div>
                                    <div class="lab-info-row__value">{{ $resultAt ?? '—' }}</div>
                                </div>

                                <div class="lab-info-row">
                                    <div class="lab-info-row__label">Биоматериал</div>
                                    <div class="lab-info-row__value">{{ $material ?? '—' }}</div>
                                </div>

                                <div class="lab-info-row">
                                    <div class="lab-info-row__label">Лаборатория</div>
                                    <div class="lab-info-row__value">{{ $laboratoryName ?? '—' }}</div>
                                </div>

                                <div class="lab-info-row">
                                    <div class="lab-info-row__label">Направивший врач</div>
                                    <div class="lab-info-row__value">{{ $orderedBy ?? '—' }}</div>
                                </div>

                                <div class="lab-info-row">
                                    <div class="lab-info-row__label">Номер исследования</div>
                                    <div class="lab-info-row__value">{{ $researchNumber ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="lab-panel">
                        <div class="lab-panel__header">
                            <h2 class="lab-panel__title">Клиническая оценка</h2>
                        </div>

                        <div class="lab-panel__body">
                            <div class="lab-note {{ $parameters->where('status', 'critical')->isNotEmpty() ? 'lab-note--danger' : '' }}">
                                Лабораторные отклонения оцениваются врачом с учётом жалоб,
                                анамнеза, осмотра и других исследований. Автоматическое
                                выделение не является диагнозом.
                            </div>

                            @if(!empty($reviewUrl))
                                <a class="lab-button lab-button--success w-100 mt-3" href="{{ $reviewUrl }}">
                                    Подтвердить просмотр
                                </a>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection
