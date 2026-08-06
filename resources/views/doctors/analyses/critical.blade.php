@extends('doctors.analyses')

@section('analysis-title', 'Критические результаты')

@section('assets')
    @parent
    @vite('resources/sass/medical_card.sass')
@endsection

@section('analysis-content')
    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Приоритетная очередь</div>

                    <h1 class="lab-title">
                        Критические результаты
                    </h1>

                    <p class="lab-description">
                        Результаты, требующие подтверждения просмотра и фиксации действия врача.
                    </p>
                </div>

                <div class="lab-hero__actions">
                    <a class="lab-button lab-button--secondary"
                       href="{{ route('doctors.analyses.journal') }}">
                        Открыть в журнале
                    </a>
                </div>
            </section>

            <section class="lab-stats">
                <article class="lab-stat lab-stat--danger">
                    <div class="lab-stat__label">
                        Всего критических
                    </div>

                    <div class="lab-stat__value">
                        {{ $criticalStatistics['total'] ?? 0 }}
                    </div>

                    <div class="lab-stat__hint">
                        За выбранный период
                    </div>
                </article>

                <article class="lab-stat lab-stat--warning">
                    <div class="lab-stat__label">
                        Не просмотрено
                    </div>

                    <div class="lab-stat__value">
                        {{ $criticalStatistics['unviewed'] ?? 0 }}
                    </div>

                    <div class="lab-stat__hint">
                        Требуют подтверждения врача
                    </div>
                </article>

                <article class="lab-stat lab-stat--success">
                    <div class="lab-stat__label">
                        Просмотрено
                    </div>

                    <div class="lab-stat__value">
                        {{ $criticalStatistics['viewed'] ?? 0 }}
                    </div>

                    <div class="lab-stat__hint">
                        Действие зафиксировано
                    </div>
                </article>

                <article class="lab-stat lab-stat--neutral">
                    <div class="lab-stat__label">
                        Среднее время реакции
                    </div>

                    <div class="lab-stat__value">
                        <span>
                            {{ $criticalStatistics['average_reaction_value'] ?? '—' }}
                        </span>

                        @if(!empty($criticalStatistics['average_reaction_unit']))
                            <span class="lab-stat__unit">
                                {{ $criticalStatistics['average_reaction_unit'] }}
                            </span>
                        @endif
                    </div>

                    <div class="lab-stat__hint">
                        От появления результата до подтверждённого просмотра
                    </div>
                </article>
            </section>

            <section class="lab-panel">
                <div class="lab-panel__header">
                    <div>
                        <h2 class="lab-panel__title">
                            Очередь критических значений
                        </h2>

                        <div class="lab-panel__subtitle">
                            Непросмотренные результаты располагаются первыми
                        </div>
                    </div>
                </div>

                <div class="lab-panel__body">
                    @if($criticalResults->isNotEmpty())
                        <div class="lab-attention-list">
                            @foreach($criticalResults as $item)
                                @php
                                    $criticalDate = $item->critical_date
                                        ?? $item->result_at
                                        ?? $item->research_date
                                        ?? $item->planned_at
                                        ?? $item->created_at;

                                    $displayItems = collect(
                                        $item->critical_results
                                        ?? $item->display_results
                                        ?? []
                                    );

                                    if ($displayItems->isEmpty()) {
                                        $displayItems = collect([
                                            [
                                                'parameter_name' => $item->critical_parameter_name
                                                    ?? $item->research_name
                                                    ?? 'Показатель не указан',

                                                'value' => $item->critical_value
                                                    ?? '—',

                                                'unit' => $item->critical_unit
                                                    ?? null,

                                                'reference' => $item->critical_reference
                                                    ?? null,

                                                'direction' => null,
                                                'severity' => 'critical',
                                            ],
                                        ]);
                                    }

                                    $isViewed = $item->result_showed_at !== null;
                                    $patient = $item->patient;

                                    $patientName = trim(
                                        ($patient?->surname ?? '')
                                        . ' '
                                        . ($patient?->name ?? '')
                                        . ' '
                                        . ($patient?->patronym ?? '')
                                    );
                                @endphp

                                <article class="lab-attention-card lab-attention-card--critical">
                                    <div class="lab-attention-card__top">
                                        <div>
                                            <div class="lab-primary-text">
                                                {{ $item->research_name ?? 'Исследование' }}
                                            </div>

                                            <div class="lab-secondary-text">
                                                @if($patientName !== '')
                                                    {{ $patientName }} ·
                                                @endif

                                                {{ $criticalDate
                                                    ? \Illuminate\Support\Carbon::parse($criticalDate)->format('d.m.Y H:i')
                                                    : 'Дата не указана' }}
                                            </div>

                                            <div class="lab-secondary-text mt-1">
                                                {{ $item->research_type_label ?? 'Исследование' }}
                                            </div>
                                        </div>

                                        <span class="lab-status lab-status--{{ $isViewed ? 'ready' : 'critical' }}">
                                            {{ $isViewed ? 'Просмотрено' : 'Требует внимания' }}
                                        </span>
                                    </div>

                                    <div class="lab-critical-results mt-3">
                                        @foreach($displayItems as $resultItem)
                                            @php
                                                $severity = data_get(
                                                    $resultItem,
                                                    'severity',
                                                    'critical'
                                                );

                                                $direction = data_get(
                                                    $resultItem,
                                                    'direction'
                                                );

                                                $isCriticalResult = $severity === 'critical';

                                                $deviationLabel = match (true) {
                                                    $isCriticalResult && $direction === 'low'
                                                        => 'Ниже критического порога',

                                                    $isCriticalResult && $direction === 'high'
                                                        => 'Выше критического порога',

                                                    !$isCriticalResult && $direction === 'low'
                                                        => 'Ниже нормы',

                                                    !$isCriticalResult && $direction === 'high'
                                                        => 'Выше нормы',

                                                    $isCriticalResult
                                                        => 'Критическое значение',

                                                    default
                                                        => 'Отклонение от нормы',
                                                };

                                                $value = data_get(
                                                    $resultItem,
                                                    'value',
                                                    '—'
                                                );

                                                $unit = data_get(
                                                    $resultItem,
                                                    'unit'
                                                );

                                                $parameterName = data_get(
                                                    $resultItem,
                                                    'parameter_name',
                                                    'Показатель не указан'
                                                );

                                                $reference = data_get(
                                                    $resultItem,
                                                    'reference'
                                                );

                                                $resultRowClass = $isCriticalResult
                                                    ? 'lab-critical-result-row--critical'
                                                    : 'lab-critical-result-row--warning';

                                                $valueClass = $isCriticalResult
                                                    ? 'text-danger'
                                                    : 'text-warning';
                                            @endphp

                                            <div class="lab-critical-result-row
                                                        {{ $resultRowClass }}
                                                        {{ !$loop->first ? 'mt-2' : '' }}">
                                                <div class="row g-3 align-items-center">
                                                    <div class="col-lg-3 col-md-6">
                                                        <div class="lab-secondary-text">
                                                            Показатель
                                                        </div>

                                                        <div class="lab-primary-text">
                                                            {{ $parameterName }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2 col-md-6">
                                                        <div class="lab-secondary-text">
                                                            Получено
                                                        </div>

                                                        <div class="lab-attention-card__value {{ $valueClass }}">
                                                            {{ $value }}

                                                            @if(!empty($unit))
                                                                {{ $unit }}
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6">
                                                        <div class="lab-secondary-text">
                                                            {{ $isCriticalResult
                                                                ? 'Критический порог'
                                                                : 'Референсный интервал' }}
                                                        </div>

                                                        <div class="lab-primary-text">
                                                            {{ $reference ?: 'Не установлен' }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-6">
                                                        <div class="lab-secondary-text">
                                                            Отклонение
                                                        </div>

                                                        <div class="lab-primary-text">
                                                            {{ $deviationLabel }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-1 text-lg-end">
                                                        <span class="lab-result-severity
                                                                     lab-result-severity--{{ $severity }}">
                                                            {{ $isCriticalResult
                                                                ? 'Критично'
                                                                : 'Отклонение' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(!empty($item->comment))
                                        <div class="lab-note mt-3">
                                            <strong>Комментарий врача:</strong>
                                            {{ $item->comment }}
                                        </div>
                                    @endif

                                    @if(($item->critical_results_count ?? 0) > 1)
                                        <div class="lab-note mt-3">
                                            Критических показателей:
                                            <strong>
                                                {{ $item->critical_results_count }}
                                            </strong>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        @if(!empty($item->result_url))
                                            <a class="lab-action lab-action--primary"
                                               href="{{ $item->result_url }}">
                                                Открыть результат
                                            </a>
                                        @endif

                                        @if(!empty($item->dynamics_url))
                                            <a class="lab-action"
                                               href="{{ $item->dynamics_url }}">
                                                Динамика показателя
                                            </a>
                                        @endif

                                        @if(!empty($item->patient_id))
                                            <a class="lab-action"
                                               href="{{ route(
                                                   'doctors.patients.medical_card',
                                                   [
                                                       'patient' => $item->patient_id,
                                                       'tab' => 'labs',
                                                   ]
                                               ) }}">
                                                Медкарта
                                            </a>
                                        @endif

                                        @if(!$isViewed && !empty($item->review_url))
                                            <button type="button"
                                                    class="lab-action"
                                                    data-review-url="{{ $item->review_url }}">
                                                Отметить просмотренным
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="lab-empty">
                            <div class="lab-empty__icon">
                                ✓
                            </div>

                            <h3>
                                Критических результатов нет
                            </h3>

                            <p>
                                Новые критические значения появятся в этой очереди автоматически.
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
