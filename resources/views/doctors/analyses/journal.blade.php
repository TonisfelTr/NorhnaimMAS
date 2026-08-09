@extends('doctors.analyses')

@section('analysis-title', 'Журнал лабораторных исследований')
@section('assets')
    @parent
@endsection
@section('analysis-content')
    @php
        /*
         * Контроллер journal() передаёт именно $labResearches.
         * Здесь больше не используются старые переменные:
         * $instrumentalResearches, $results, $rows.
         */
        $labResearches = $labResearches ?? collect();
    @endphp

    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Полный журнал</div>

                    <h1 class="lab-title">
                        Все лабораторные исследования
                    </h1>

                    <p class="lab-description">
                        Полная история назначенных, выполняемых и завершённых
                        лабораторных исследований текущего врача.
                    </p>
                </div>
            </section>

            <section class="lab-panel mb-4">
                <div class="lab-panel__header">
                    <h2 class="lab-panel__title">
                        Фильтры
                    </h2>
                </div>

                <div class="lab-panel__body">
                    <form
                        method="GET"
                        action="{{ route('doctors.analyses.journal') }}"
                        class="lab-filters"
                    >
                        <div class="lab-filter--4">
                            <label class="lab-label">
                                Пациент или исследование
                            </label>

                            <input
                                class="lab-control"
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="ФИО, анализ, показатель..."
                            >
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label">
                                Дата с
                            </label>

                            <input
                                class="lab-control"
                                type="date"
                                name="date_from"
                                value="{{ request('date_from') }}"
                            >
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label">
                                Дата по
                            </label>

                            <input
                                class="lab-control"
                                type="date"
                                name="date_to"
                                value="{{ request('date_to') }}"
                            >
                        </div>

                        <div class="lab-filter--2">
                            <label class="lab-label">
                                Статус
                            </label>

                            <select
                                class="lab-control"
                                name="status"
                            >
                                <option value="">
                                    Все статусы
                                </option>

                                <option
                                    value="new"
                                    @selected(request('status') === 'new')
                                >
                                    Новый результат
                                </option>

                                <option
                                    value="ordered"
                                    @selected(request('status') === 'ordered')
                                >
                                    Назначено
                                </option>

                                <option
                                    value="processing"
                                    @selected(request('status') === 'processing')
                                >
                                    В работе
                                </option>

                                <option
                                    value="overdue"
                                    @selected(request('status') === 'overdue')
                                >
                                    Просрочено
                                </option>

                                <option
                                    value="ready"
                                    @selected(request('status') === 'ready')
                                >
                                    Готово
                                </option>

                                <option
                                    value="warning"
                                    @selected(request('status') === 'warning')
                                >
                                    Отклонение
                                </option>

                                <option
                                    value="critical"
                                    @selected(request('status') === 'critical')
                                >
                                    Критический
                                </option>

                                <option
                                    value="viewed"
                                    @selected(request('status') === 'viewed')
                                >
                                    Просмотрено врачом
                                </option>
                            </select>
                        </div>

                        <div class="lab-filter--2 d-flex gap-2">
                            <button
                                class="lab-button lab-button--primary flex-fill"
                                type="submit"
                            >
                                Найти
                            </button>

                            <a
                                class="lab-button lab-button--secondary"
                                href="{{ route('doctors.analyses.journal') }}"
                            >
                                Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="lab-panel">
                <div class="lab-panel__header">
                    <div>
                        <h2 class="lab-panel__title">
                            Журнал исследований
                        </h2>

                        <div class="lab-panel__subtitle">
                            {{ $labResearches->count() }} записей
                        </div>
                    </div>
                </div>

                @if($labResearches->isNotEmpty())
                    <div class="lab-table-wrap">
                        <table class="table lab-table">
                            <thead>
                            <tr>
                                <th>Пациент</th>
                                <th>Исследование</th>
                                <th>Результат</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th class="text-end">
                                    Действия
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($labResearches as $row)
                                @php
                                    $patient = $row->patient;

                                    $patientName = trim(
                                        ($patient?->surname ?? '')
                                        . ' '
                                        . ($patient?->name ?? '')
                                        . ' '
                                        . ($patient?->patronym ?? '')
                                    );

                                    $initials = mb_strtoupper(
                                        mb_substr(
                                            (string) ($patient?->surname ?? ''),
                                            0,
                                            1
                                        )
                                        . mb_substr(
                                            (string) ($patient?->name ?? ''),
                                            0,
                                            1
                                        )
                                    );

                                    $birthDate = $patient?->birth_at
                                        ? \Illuminate\Support\Carbon::parse(
                                            $patient->birth_at
                                        )->format('d.m.Y')
                                        : null;

                                    $displayStatus = (string) (
                                        $row->display_status
                                        ?? $row->status
                                        ?? ''
                                    );

                                    $statusLabel = (string) (
                                        $row->status_label
                                        ?? match ($displayStatus) {
                                            'critical' => 'Критично',
                                            'warning' => 'Отклонение',
                                            'ready' => 'Готово',
                                            'processing' => 'В работе',
                                            'ordered' => 'Назначено',
                                            'overdue' => 'Просрочено',
                                            default => $displayStatus !== ''
                                                ? ucfirst($displayStatus)
                                                : '—',
                                        }
                                    );
                                @endphp

                                <tr>
                                    <td>
                                        <div class="lab-patient">
                                            <div class="lab-avatar">
                                                {{ $initials ?: 'П' }}
                                            </div>

                                            <div>
                                                <div class="lab-primary-text">
                                                    {{ $patientName !== ''
                                                        ? $patientName
                                                        : 'Пациент не указан' }}
                                                </div>

                                                <div class="lab-secondary-text">
                                                    {{ $birthDate
                                                        ?: 'Дата рождения не указана' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ $row->laboratory
                                                ?: 'Лабораторный анализ' }}
                                        </div>

                                        <div class="lab-secondary-text">
                                            {{ $row->sample_type
                                                ?: 'Материал не указан' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div
                                            class="lab-result-value
                                            @if($displayStatus === 'critical')
                                                text-danger
                                            @elseif($displayStatus === 'warning')
                                                text-warning
                                            @elseif($displayStatus === 'ready')
                                                text-success
                                            @endif"
                                        >
                                            {{ $row->summary_value ?? '—' }}
                                        </div>

                                        @if(!empty($row->summary_text))
                                            <div class="lab-secondary-text">
                                                {{ $row->summary_text }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="lab-primary-text">
                                            {{ $row->display_date ?? '—' }}
                                        </div>

                                        @if(!empty($row->display_time))
                                            <div class="lab-secondary-text">
                                                {{ $row->display_time }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <span
                                            class="lab-status
                                            lab-status--{{ $displayStatus }}"
                                        >
                                            {{ $statusLabel }}
                                        </span>

                                        @if(
                                            $row->status === 'ready'
                                            && $row->result_showed_at === null
                                        )
                                            <div class="lab-secondary-text mt-1">
                                                Не просмотрено
                                            </div>
                                        @elseif(
                                            $row->status === 'ready'
                                            && $row->result_showed_at !== null
                                        )
                                            <div class="lab-secondary-text mt-1">
                                                Просмотрено
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="lab-actions">
                                            @if(!empty($row->result_url))
                                                <a
                                                    class="lab-action lab-action--primary"
                                                    href="{{ $row->result_url }}"
                                                >
                                                    {{ $row->status === 'ready'
                                                        ? 'Результат'
                                                        : 'Открыть' }}
                                                </a>
                                            @else
                                                <a
                                                    class="lab-action lab-action--primary"
                                                    href="{{ route(
                                                        'doctors.patients.medical_card',
                                                        [
                                                            'patient' => $row->patient_id,
                                                            'tab' => 'labs',
                                                            'research' => $row->id,
                                                        ]
                                                    ) }}"
                                                >
                                                    Открыть
                                                </a>
                                            @endif
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
                            <div class="lab-empty__icon">
                                📋
                            </div>

                            <h3>
                                Исследования не найдены
                            </h3>

                            <p>
                                Измените параметры поиска или назначьте
                                новое лабораторное исследование.
                            </p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
