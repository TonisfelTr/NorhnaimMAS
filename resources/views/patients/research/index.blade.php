@extends('layouts.patient')

@section('title', 'Исследования')

@section('content')
<link rel="stylesheet" href="{{ asset('css/patient-research.css') }}?v=20261119-datetime-fio">

<div class="patient-page patient-research-page">
    <header class="patient-page-header patient-research-header">
        <div>
            <span class="patient-eyebrow">МОЁ ЗДОРОВЬЕ</span>
            <h1>Исследования</h1>
            <p>Результаты лабораторных исследований и психодиагностических тестов.</p>
        </div>
        @if (! $showClinicTabs && $selectedClinicName)
            <div class="patient-research-current-clinic">
                <i class="bi bi-hospital"></i>
                <span>{{ $selectedClinicName }}</span>
            </div>
        @endif
    </header>

    @if ($showPriorityClinicSelector)
        <section class="patient-research-clinic-preference">
            <div class="patient-research-clinic-preference__copy">
                <span class="patient-eyebrow">МОИ КЛИНИКИ</span>
                <h2>Приоритетная клиника</h2>
                <p>Она будет открываться первой в разделе исследований.</p>
            </div>

            <form
                method="POST"
                action="{{ route('patients.research.priority-clinic') }}"
                class="patient-research-clinic-preference__form"
            >
                @csrf
                <label for="researchPriorityClinic">Основная клиника</label>
                <div class="patient-research-clinic-preference__controls">
                    <select
                        id="researchPriorityClinic"
                        name="priority_clinic_id"
                        required
                    >
                        @foreach ($clinicMemberships as $membership)
                            <option
                                value="{{ $membership->clinic_id }}"
                                @if ((int) $priorityClinicId === (int) $membership->clinic_id) selected @endif
                            >
                                {{ $membership->clinic->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit">
                        <i class="bi bi-check2-circle"></i>
                        Сохранить
                    </button>
                </div>
                @error('priority_clinic_id')
                    <small class="patient-research-clinic-preference__error" role="alert">{{ $message }}</small>
                @enderror
            </form>
        </section>
    @endif

    @if (session('success'))
        <div class="patient-research-flash" role="status">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($showClinicTabs)
        <nav class="patient-research-clinic-tabs" aria-label="Исследования по клиникам">
            @foreach ($clinicTabs as $tab)
                <a
                    href="{{ route('patients.research.index', ['clinic_id' => $tab['clinic_id']]) }}"
                    class="patient-research-clinic-tab {{ (int) $selectedClinicId === (int) $tab['clinic_id'] ? 'is-active' : '' }}"
                >
                    <span class="patient-research-clinic-tab__icon">
                        <i class="bi bi-hospital"></i>
                    </span>
                    <span class="patient-research-clinic-tab__copy">
                        <strong>{{ $tab['name'] }}</strong>
                        <small>
                            {{ $tab['laboratory_count'] }} лаб. · {{ $tab['test_count'] }} тест.
                        </small>
                    </span>
                    <span class="patient-research-clinic-tab__count">{{ $tab['total'] }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if (! $hasEligibleClinics)
        <section class="patient-research-empty patient-research-empty--wide">
            <span class="patient-research-empty__icon"><i class="bi bi-hospital"></i></span>
            <h2>Клиника пока не определена</h2>
            <p>Исследования появятся здесь после подтверждённого приёма у психиатра в клинике, связанной с вашей медицинской картой.</p>
        </section>
    @else
        <ul
            id="patientResearchTabs"
            class="nav nav-tabs patient-research-tabs"
            role="tablist"
            aria-label="Виды исследований"
        >
            <li class="nav-item" role="presentation">
                <button
                    id="patient-laboratory-tab"
                    class="nav-link active"
                    type="button"
                    role="tab"
                    data-bs-toggle="tab"
                    data-bs-target="#patient-laboratory-pane"
                    aria-controls="patient-laboratory-pane"
                    aria-selected="true"
                >
                    <i class="bi bi-droplet-half"></i>
                    <span>Лабораторные тесты</span>
                    <span class="patient-research-tabs__count">{{ $laboratoryResearches->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    id="patient-psychiatric-tab"
                    class="nav-link"
                    type="button"
                    role="tab"
                    data-bs-toggle="tab"
                    data-bs-target="#patient-psychiatric-pane"
                    aria-controls="patient-psychiatric-pane"
                    aria-selected="false"
                >
                    <i class="bi bi-clipboard2-pulse"></i>
                    <span>Психиатрические тесты</span>
                    <span class="patient-research-tabs__count">{{ $psychiatricTests->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content patient-research-tab-content">
            <section
                id="patient-laboratory-pane"
                class="tab-pane fade show active"
                role="tabpanel"
                aria-labelledby="patient-laboratory-tab"
                tabindex="0"
            >
                <div class="patient-research-section-heading">
                    <div>
                        <span class="patient-eyebrow">ЛАБОРАТОРИЯ</span>
                        <h2>Лабораторные исследования</h2>
                        <p>Результаты анализов, единицы измерения и референсные значения.</p>
                    </div>
                    <span class="patient-research-section-count">{{ $laboratoryResearches->count() }}</span>
                </div>

                @forelse ($laboratoryResearches as $research)
                    <article class="patient-research-card">
                        <header class="patient-research-card__header">
                            <div>
                                <h3>{{ $research['title'] }}</h3>
                                <div class="patient-research-card__meta">
                                    <span class="patient-research-datetime"><i class="bi bi-calendar3"></i> {{ $research['date_label'] }}</span>
                                    @if ($research['sample_type'] !== '')
                                        <span><i class="bi bi-eyedropper"></i> {{ $research['sample_type'] }}</span>
                                    @endif
                                    @if ($research['doctor_name'] !== '')
                                        <span><i class="bi bi-person-badge"></i> {{ $research['doctor_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <span
                                class="patient-research-status patient-research-status--{{
                                    $research['status'] === 'ready'
                                        ? 'ready'
                                        : ($research['status'] === 'processing' ? 'processing' : 'neutral')
                                }}"
                            >
                                {{ $research['status_label'] }}
                            </span>
                        </header>

                        @if ($research['status'] === 'ready')
                            <div class="patient-research-card__actions">
                                <button
                                    type="button"
                                    class="patient-research-open-results"
                                    data-research-modal-open="patient-research-modal-{{ $research['id'] }}"
                                    aria-haspopup="dialog"
                                    aria-controls="patient-research-modal-{{ $research['id'] }}"
                                >
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                    <span>Посмотреть результаты</span>
                                </button>
                            </div>
                        @else
                            <div class="patient-research-inline-note">
                                <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                                Результаты появятся здесь после завершения исследования и внесения данных лабораторией.
                            </div>
                        @endif
                    </article>

                    @if ($research['status'] === 'ready')
                        <dialog
                            id="patient-research-modal-{{ $research['id'] }}"
                            class="patient-research-modal"
                            aria-labelledby="patient-research-modal-title-{{ $research['id'] }}"
                            aria-modal="true"
                        >
                            <div class="patient-research-modal__panel">
                                <header class="patient-research-modal__header">
                                    <div class="patient-research-modal__heading">
                                        <span class="patient-research-modal__icon" aria-hidden="true">
                                            <i class="bi bi-clipboard2-pulse"></i>
                                        </span>
                                        <h3 id="patient-research-modal-title-{{ $research['id'] }}">
                                            Результаты исследования
                                        </h3>
                                    </div>
                                    <button
                                        type="button"
                                        class="patient-research-modal__close"
                                        data-research-modal-close
                                        aria-label="Закрыть результаты"
                                    >
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                </header>

                                <div class="patient-research-modal__body">
                                    <div class="patient-research-modal__summary">
                                        <div class="patient-research-modal__summary-copy">
                                            <strong>{{ $research['title'] }}</strong>
                                            <div class="patient-research-modal__meta">
                                                <span class="patient-research-datetime"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $research['date_label'] }}</span>
                                                @if ($research['sample_type'] !== '')
                                                    <span><i class="bi bi-eyedropper" aria-hidden="true"></i> {{ $research['sample_type'] }}</span>
                                                @endif
                                                @if ($research['doctor_name'] !== '')
                                                    <span><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $research['doctor_name'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="patient-research-status patient-research-status--ready">
                                            {{ $research['status_label'] }}
                                        </span>
                                    </div>

                                    @if (count($research['results']) > 0)
                                        <div class="patient-research-results-wrap">
                                            <table class="patient-research-results">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Показатель</th>
                                                        <th scope="col">Результат</th>
                                                        <th scope="col">Единицы</th>
                                                        <th scope="col">Референсные значения</th>
                                                        <th scope="col">Оценка</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($research['results'] as $result)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $result['parameter_name'] }}</strong>
                                                            </td>
                                                            <td class="patient-research-results__value">
                                                                {{ $result['value'] !== '' ? $result['value'] : '—' }}
                                                            </td>
                                                            <td>{{ $result['unit'] !== '' ? $result['unit'] : '—' }}</td>
                                                            <td>{{ $result['reference_text'] !== '' ? $result['reference_text'] : 'Не указаны' }}</td>
                                                            <td>
                                                                @if ($result['deviation'] === 'low')
                                                                    <span class="patient-research-result-flag patient-research-result-flag--warning">Ниже нормы</span>
                                                                @elseif ($result['deviation'] === 'high')
                                                                    <span class="patient-research-result-flag patient-research-result-flag--warning">Выше нормы</span>
                                                                @elseif ($result['deviation'] === 'normal')
                                                                    <span class="patient-research-result-flag patient-research-result-flag--normal">В норме</span>
                                                                @else
                                                                    <span class="patient-research-result-flag patient-research-result-flag--muted">Не оценено</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="patient-research-card__footnote">
                                            Референсные значения зависят от характеристик пациента и лабораторной методики.
                                            Результаты интерпретирует нейросеть или врач.
                                        </p>
                                    @else
                                        <div class="patient-research-modal__empty">
                                            <span class="patient-research-modal__empty-icon" aria-hidden="true">
                                                <i class="bi bi-clipboard2-data"></i>
                                            </span>
                                            <strong>Значения показателей отсутствуют</strong>
                                            <p>Для этого исследования нет записанных результатов анализов.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </dialog>
                    @endif
                @empty
                    <section class="patient-research-empty">
                        <span class="patient-research-empty__icon"><i class="bi bi-droplet"></i></span>
                        <h3>Лабораторных исследований пока нет</h3>
                        <p>Когда врач назначит анализы в выбранной клинике, они появятся здесь.</p>
                    </section>
                @endforelse
            </section>

            <section
                id="patient-psychiatric-pane"
                class="tab-pane fade"
                role="tabpanel"
                aria-labelledby="patient-psychiatric-tab"
                tabindex="0"
            >
                <div class="patient-research-section-heading">
                    <div>
                        <span class="patient-eyebrow">ПСИХОДИАГНОСТИКА</span>
                        <h2>Психиатрические тесты</h2>
                        <p>Назначенные врачом методики и доступные результаты прохождения.</p>
                    </div>
                    <span class="patient-research-section-count">{{ $psychiatricTests->count() }}</span>
                </div>

                @forelse ($psychiatricTests as $test)
                    <article class="patient-research-card patient-research-card--test">
                        <header class="patient-research-card__header">
                            <div>
                                <div class="patient-research-card__eyebrow">
                                    @if ($test['test_code'] !== '')
                                        {{ $test['test_code'] }}
                                    @else
                                        Психодиагностическая методика
                                    @endif
                                </div>
                                <h3>{{ $test['test_name'] }}</h3>
                                <div class="patient-research-card__meta">
                                    @if ($test['assigned_at_label'] !== '—')
                                        <span><i class="bi bi-calendar3"></i> Назначен: {{ $test['assigned_at_label'] }}</span>
                                    @endif
                                    @if ($test['due_at_label'] !== '—')
                                        <span><i class="bi bi-calendar-check"></i> Срок: {{ $test['due_at_label'] }}</span>
                                    @endif
                                    @if ($test['doctor_name'] !== '')
                                        <span><i class="bi bi-person-badge"></i> {{ $test['doctor_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <span
                                class="patient-research-status patient-research-status--{{
                                    $test['is_completed']
                                        ? 'ready'
                                        : ($test['status'] === 'cancelled' ? 'neutral' : 'processing')
                                }}"
                            >
                                {{ $test['status_label'] }}
                            </span>
                        </header>

                        @if ($test['description'] !== '')
                            <p class="patient-research-card__description">{{ $test['description'] }}</p>
                        @endif

                        @if ($test['is_completed'])
                            @if (count($test['results']) > 0)
                                <div class="patient-psych-results">
                                    <h4>Результаты</h4>
                                    @foreach ($test['results'] as $result)
                                        <div class="patient-psych-result">
                                            <div class="patient-psych-result__top">
                                                <strong>{{ $result['title'] }}</strong>
                                                @if ($result['score'] !== '')
                                                    <span>{{ $result['score'] }} балл.</span>
                                                @endif
                                            </div>
                                            @if ($result['range'] !== '')
                                                <small>Диапазон: {{ $result['range'] }}</small>
                                            @endif
                                            @if ($result['interpretation'] !== '')
                                                <p>{{ $result['interpretation'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="patient-research-inline-note">
                                    <i class="bi bi-info-circle"></i>
                                    Тест завершён. Итоговый результат пока не опубликован.
                                </div>
                            @endif
                            @if ($test['completed_at_label'] !== '—')
                                <div class="patient-research-card__completed">
                                    <i class="bi bi-check-circle"></i>
                                    Завершён: {{ $test['completed_at_label'] }}
                                </div>
                            @endif
                        @elseif ($test['status'] !== 'cancelled')
                            <div class="patient-research-inline-note">
                                <i class="bi bi-info-circle"></i>
                                Следуйте инструкции врача. После завершения и проверки результат появится в этом разделе.
                            </div>
                        @endif
                    </article>
                @empty
                    <section class="patient-research-empty">
                        <span class="patient-research-empty__icon"><i class="bi bi-clipboard2-pulse"></i></span>
                        <h3>Психиатрические тесты пока не назначены</h3>
                        <p>Когда врач назначит вам психодиагностическую методику, она появится здесь вместе со статусом прохождения и результатом.</p>
                    </section>
                @endforelse
            </section>
        </div>
    @endif
</div>
<script src="{{ asset('js/patient-research.js') }}?v=20261119-compact" defer></script>
@endsection
