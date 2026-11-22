@extends('layouts.patient')

@section('title', 'Мои диагнозы')

@section('content')
<link rel="stylesheet" href="{{ asset('css/patient-diagnoses.css') }}?v=20261123-context2">
<script defer src="{{ asset('js/patient-diagnoses-tabs.js') }}?v=20261123-tabs1"></script>
<div class="patient-page patient-diagnoses">
    <header class="patient-page-header patient-diagnoses__heading">
        <div>
            <span class="patient-eyebrow">МОЁ ЗДОРОВЬЕ</span>
            <h1>Мои диагнозы</h1>
            <p>Информация о диагнозах, зафиксированных врачами каждой клиники.</p>
        </div>
        <div class="patient-diagnoses__heading-icon" aria-hidden="true"><i class="bi bi-clipboard2-pulse"></i></div>
    </header>

    @if($clinics->isEmpty())
        <section class="patient-diagnoses__empty">
            <i class="bi bi-hospital" aria-hidden="true"></i>
            <h2>Клиника пока не определена</h2>
            <p>После прикрепления к клинике информация о диагнозах появится здесь.</p>
        </section>
    @else
        <section class="patient-diagnoses__selector" aria-label="Выбор клиники">
            <div>
                <span class="patient-diagnoses__eyebrow">МЕДИЦИНСКАЯ КАРТА</span>
                <h2>Диагнозы по клиникам</h2>
                <p>Сведения одной клиники не объединяются с диагнозами других клиник.</p>
            </div>
            <form method="GET" action="{{ route('patients.diagnoses.index') }}">
                <label for="patientDiagnosisClinic">Клиника</label>
                <select id="patientDiagnosisClinic" class="form-select" name="clinic_id" onchange="this.form.submit()">
                    @foreach($clinics as $membership)
                        <option value="{{ $membership->clinic_id }}" @selected($clinicId === (int) $membership->clinic_id)>
                            {{ $membership->clinic->name }}{{ $membership->is_primary ? ' · основная' : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="patient-diagnoses__submit">Показать</button>
            </form>
        </section>

        @php
            $diagnosesActiveTab = request()->query('tab', 'current');
            $diagnosesActiveTab = is_string($diagnosesActiveTab)
                && in_array($diagnosesActiveTab, ['current', 'history', 'visits'], true)
                    ? $diagnosesActiveTab : 'current';
        @endphp

        <nav class="patient-diagnoses__tabs" role="tablist" aria-label="Разделы диагнозов">
            <a id="patient-diagnoses-tab-current"
               class="patient-diagnoses__tab {{ $diagnosesActiveTab === 'current' ? 'is-active' : '' }}"
               href="{{ route('patients.diagnoses.index', ['clinic_id' => $clinicId, 'tab' => 'current']) }}"
               role="tab" data-diagnoses-tab="current"
               aria-controls="patient-diagnoses-panel-current"
               aria-selected="{{ $diagnosesActiveTab === 'current' ? 'true' : 'false' }}"
               tabindex="{{ $diagnosesActiveTab === 'current' ? '0' : '-1' }}">
                <i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>
                <span>Основной диагноз</span>
            </a>
            <a id="patient-diagnoses-tab-history"
               class="patient-diagnoses__tab {{ $diagnosesActiveTab === 'history' ? 'is-active' : '' }}"
               href="{{ route('patients.diagnoses.index', ['clinic_id' => $clinicId, 'tab' => 'history']) }}"
               role="tab" data-diagnoses-tab="history"
               aria-controls="patient-diagnoses-panel-history"
               aria-selected="{{ $diagnosesActiveTab === 'history' ? 'true' : 'false' }}"
               tabindex="{{ $diagnosesActiveTab === 'history' ? '0' : '-1' }}">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <span>История диагнозов</span>
                <span class="patient-diagnoses__tab-count">{{ $history->total() }}</span>
            </a>
            <a id="patient-diagnoses-tab-visits"
               class="patient-diagnoses__tab {{ $diagnosesActiveTab === 'visits' ? 'is-active' : '' }}"
               href="{{ route('patients.diagnoses.index', ['clinic_id' => $clinicId, 'tab' => 'visits']) }}"
               role="tab" data-diagnoses-tab="visits"
               aria-controls="patient-diagnoses-panel-visits"
               aria-selected="{{ $diagnosesActiveTab === 'visits' ? 'true' : 'false' }}"
               tabindex="{{ $diagnosesActiveTab === 'visits' ? '0' : '-1' }}">
                <i class="bi bi-journal-medical" aria-hidden="true"></i>
                <span>Диагнозы по приёмам</span>
                <span class="patient-diagnoses__tab-count">{{ $visitDiagnoses->total() }}</span>
            </a>
        </nav>

        <section class="patient-diagnoses__section patient-diagnoses__panel"
                 id="patient-diagnoses-panel-current" role="tabpanel"
                 aria-labelledby="patient-diagnoses-tab-current" tabindex="0"
                 @if($diagnosesActiveTab !== 'current') hidden @endif>
            <div class="patient-diagnoses__section-head">
                <div>
                    <div class="patient-diagnoses__clinic-context">
                        @if($current?->diagnose?->code)
                            <span class="patient-diagnoses__clinic-context-code">{{ $current->diagnose->code }}</span>
                            <span class="patient-diagnoses__clinic-context-divider" aria-hidden="true">·</span>
                        @endif
                        <span class="patient-diagnoses__clinic-context-name">{{ $selectedClinic?->name }}</span>
                    </div>
                    <h2 id="patient-diagnoses-current-title">Основной диагноз клиники</h2>
                </div>
            </div>
            @if($current && $current->diagnose)
                <article class="patient-diagnoses__current">
                    <div class="patient-diagnoses__current-top">
                        <span class="patient-diagnoses__code">{{ $current->diagnose->code }}</span>
                        <span class="patient-diagnoses__tag">Установлен врачом</span>
                    </div>
                    <h3>{{ $current->diagnose->title }}</h3>
                    <div class="patient-diagnoses__meta">
                        <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $current->set_at?->format('d.m.Y H:i') ?? 'Дата не указана' }}</span>
                        <span><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $current->doctor_name_snapshot ?: 'Врач не указан' }}</span>
                    </div>
                </article>
            @else
                <div class="patient-diagnoses__empty patient-diagnoses__empty--compact">
                    <i class="bi bi-clipboard2" aria-hidden="true"></i>
                    <p>Основной диагноз в этой клинике пока не зафиксирован.</p>
                </div>
            @endif
        </section>

        <section class="patient-diagnoses__section patient-diagnoses__panel"
                 id="patient-diagnoses-panel-history" role="tabpanel"
                 aria-labelledby="patient-diagnoses-tab-history" tabindex="0"
                 @if($diagnosesActiveTab !== 'history') hidden @endif>
            <div class="patient-diagnoses__section-head">
                <div>
                    <div class="patient-diagnoses__clinic-context">
                        @if($current?->diagnose?->code)
                            <span class="patient-diagnoses__clinic-context-code">{{ $current->diagnose->code }}</span>
                            <span class="patient-diagnoses__clinic-context-divider" aria-hidden="true">·</span>
                        @endif
                        <span class="patient-diagnoses__clinic-context-name">{{ $selectedClinic?->name }}</span>
                    </div>
                    <h2 id="patient-diagnoses-history-title">История установленных диагнозов</h2>
                </div>
                <span class="patient-diagnoses__total">{{ $history->total() }}</span>
            </div>
            <div class="patient-diagnoses__list">
                @forelse($history as $entry)
                    <article class="patient-diagnoses__entry">
                        <div class="patient-diagnoses__entry-main">
                            <span class="patient-diagnoses__code">{{ $entry->diagnose?->code }}</span>
                            <h3>{{ $entry->diagnose?->title }}</h3>
                            <div class="patient-diagnoses__meta">
                                <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $entry->set_at?->format('d.m.Y H:i') ?? 'Дата не указана' }}</span>
                                <span><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $entry->doctor_name_snapshot ?: 'Врач не указан' }}</span>
                            </div>
                        </div>
                        @if($current && $entry->id === $current->id)
                            <span class="patient-diagnoses__tag">Текущий</span>
                        @endif
                    </article>
                @empty
                    <p class="patient-diagnoses__muted">Установленных основных диагнозов в этой клинике пока нет.</p>
                @endforelse
            </div>
            @if($history->hasPages())
                <div class="patient-diagnoses__pagination">{{ $history->appends(['tab' => 'history'])->links() }}</div>
            @endif
        </section>

        <section class="patient-diagnoses__section patient-diagnoses__panel"
                 id="patient-diagnoses-panel-visits" role="tabpanel"
                 aria-labelledby="patient-diagnoses-tab-visits" tabindex="0"
                 @if($diagnosesActiveTab !== 'visits') hidden @endif>
            <div class="patient-diagnoses__section-head">
                <div>
                    <div class="patient-diagnoses__clinic-context">
                        @if($current?->diagnose?->code)
                            <span class="patient-diagnoses__clinic-context-code">{{ $current->diagnose->code }}</span>
                            <span class="patient-diagnoses__clinic-context-divider" aria-hidden="true">·</span>
                        @endif
                        <span class="patient-diagnoses__clinic-context-name">{{ $selectedClinic?->name }}</span>
                    </div>
                    <h2 id="patient-diagnoses-visits-title">Диагнозы по завершённым приёмам</h2>
                </div>
                <span class="patient-diagnoses__total">{{ $visitDiagnoses->total() }}</span>
            </div>
            <div class="patient-diagnoses__list">
                @forelse($visitDiagnoses as $visit)
                    <article class="patient-diagnoses__entry">
                        <div class="patient-diagnoses__entry-main">
                            @if($visit['code'] !== '')<span class="patient-diagnoses__code">{{ $visit['code'] }}</span>@endif
                            <h3>{{ $visit['title'] !== '' ? $visit['title'] : 'Диагноз в протоколе приёма' }}</h3>
                            <div class="patient-diagnoses__meta">
                                <span><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $visit['date'] }}</span>
                                <span><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $visit['doctor'] }}</span>
                            </div>
                        </div>
                        <span class="patient-diagnoses__tag patient-diagnoses__tag--secondary">Протокол приёма</span>
                    </article>
                @empty
                    <p class="patient-diagnoses__muted">Диагнозов в завершённых протоколах этой клиники пока нет.</p>
                @endforelse
            </div>
            @if($visitDiagnoses->hasPages())
                <div class="patient-diagnoses__pagination">{{ $visitDiagnoses->appends(['tab' => 'visits'])->links() }}</div>
            @endif
        </section>

        <p class="patient-diagnoses__notice">Основной диагноз устанавливает врач. Диагноз из протокола отдельного приёма не обязательно является текущим основным диагнозом клиники. Если сведения требуют уточнения, обратитесь к своему лечащему врачу.</p>
    @endif
</div>
@endsection
