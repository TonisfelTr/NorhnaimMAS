@extends('layouts.patient')



@php
    $patientAssignmentRead = static function ($source, array $keys, $default = null) {
        foreach ($keys as $key) {
            $value = data_get($source, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return $default;
    };

    $patientAssignmentFormatDose = static function ($assignment) use ($patientAssignmentRead) {
        $doseValue = $patientAssignmentRead($assignment, [
            'dosage',
            'dose',
            'dose_value',
            'prescription.dosage',
            'prescription.dose',
            'prescription.dose_value',
        ]);

        $doseUnit = $patientAssignmentRead($assignment, [
            'dosage_unit',
            'dose_unit',
            'unit',
            'measurement_unit',
            'prescription.dosage_unit',
            'prescription.dose_unit',
            'prescription.unit',
            'prescription.measurement_unit',
        ]);

        if ($doseValue && $doseUnit) {
            return trim($doseValue . ' ' . $doseUnit);
        }

        return $doseValue ?: null;
    };

    $patientAssignmentForm = static function ($assignment) use ($patientAssignmentRead) {
        return $patientAssignmentRead($assignment, [
            'drug_form',
            'form',
            'form_name',
            'drug.form_name',
            'drug.form',
            'prescription.drug_form',
            'prescription.form',
            'prescription.form_name',
        ]);
    };

    $patientAssignmentMnn = static function ($assignment) use ($patientAssignmentRead) {
        return $patientAssignmentRead($assignment, [
            'mnn',
            'inn',
            'international_name',
            'drug.mnn',
            'drug.inn',
            'drug.international_name',
            'prescription.mnn',
        ]);
    };
@endphp

@section('title', 'Назначения')

@section('content')
<div class="patient-page patient-assignments-page">
    <header class="patient-page-header patient-assignments-header">
        <div>
            <span class="patient-eyebrow">ЛЕЧЕНИЕ</span>
            <h1>Назначения</h1>
            <p>Схемы лечения и индивидуальные инструкции, зафиксированные вашими врачами.</p>
        </div>
    </header>

    @if(!$patient)
        <section class="patient-assignment-empty">
            <i class="bi bi-person-x"></i>
            <div>
                <h2>Медицинская карта ещё не привязана</h2>
                <p>После привязки медкарты здесь появятся назначения ваших врачей.</p>
            </div>
        </section>
    @else
        <section class="patient-assignment-safety">
            <div class="patient-assignment-safety__icon">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <div>
                <strong>Следуйте именно инструкции врача из конкретного назначения.</strong>
                <p>
                    Не меняйте дозу, кратность или длительность лечения самостоятельно.
                    При подозрении на передозировку выполните действия, указанные врачом ниже.
                    При угрожающем состоянии немедленно обращайтесь в экстренную службу
                    (112 или местный номер экстренной помощи).
                </p>
            </div>
        </section>

        @if($showPriorityClinicSelector)
            <section class="patient-assignment-clinic-preference">
                <div class="patient-assignment-clinic-preference__copy">
                    <span class="patient-eyebrow">МОИ КЛИНИКИ</span>
                    <h2>Приоритетная клиника</h2>
                    <p>Она будет открываться первой в разделе назначений.</p>
                </div>

                <form method="POST"
                      action="{{ route('patients.assignments.priority-clinic') }}"
                      class="patient-assignment-clinic-preference__form">
                    @csrf
                    <label for="patientPriorityClinic">Основная клиника</label>
                    <div class="patient-assignment-clinic-preference__controls">
                        <select id="patientPriorityClinic"
                                name="priority_clinic_id"
                                required>
                            @foreach($priorityClinicMemberships as $membership)
                                <option value="{{ $membership->clinic_id }}"
                                    @if((int) $priorityClinicId === (int) $membership->clinic_id) selected @endif>
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
                        <small class="patient-assignment-clinic-preference__error" role="alert">{{ $message }}</small>
                    @enderror
                </form>
            </section>
            @if(session('success'))
                <div class="patient-assignment-clinic-preference__saved" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif
        @endif

        @if($showClinicTabs)
            <nav class="patient-assignment-clinic-tabs"
                 aria-label="Назначения по клиникам">
                @foreach($clinicTabs as $tab)
                    <a href="{{ route('patients.assignments.index', ['clinic_id' => $tab['clinic_id'] ?? 'unknown']) }}"
                       class="patient-assignment-clinic-tab {{ $selectedClinicKey === $tab['key'] ? 'is-active' : '' }}"
                       @if($selectedClinicKey === $tab['key']) aria-current="page" @endif>
                        <span class="patient-assignment-clinic-tab__icon">
                            <i class="bi bi-hospital"></i>
                        </span>
                        <span class="patient-assignment-clinic-tab__copy">
                            <strong>{{ $tab['name'] }}</strong>
                            <small>
                                @if($tab['total'] > 0)
                                    {{ $tab['active_count'] }} активных
                                    @if($tab['history_count'] > 0)
                                        · {{ $tab['history_count'] }} в истории
                                    @endif
                                @else
                                    Пока нет назначений
                                @endif
                            </small>
                        </span>
                        <span class="patient-assignment-clinic-tab__count">
                            {{ $tab['total'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        @endif

        <section class="patient-assignment-section">
            <div class="patient-assignment-section__head">
                <div>
                    <span class="patient-eyebrow">СЕЙЧАС</span>
                    <h2>Активные назначения</h2>
                </div>
                <span class="patient-assignment-count">{{ $activeAssignments->count() }}</span>
            </div>

            @forelse($activeAssignments as $assignment)
                <article class="patient-assignment-card is-active">
                    <div class="patient-assignment-card__top">
                        <div>
                            <div class="patient-assignment-card__eyebrow">
                                <span class="patient-assignment-status is-active">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Активно
                                </span>
                                <span>
                                    с {{ $assignment->starts_on?->format('d.m.Y') }}
                                    @if($assignment->ends_on)
                                        до {{ $assignment->ends_on->format('d.m.Y') }}
                                    @endif
                                </span>
                            </div>

                            <h3>{{ $assignment->drug_name_snapshot }}</h3>
@php
    $patientAssignmentCurrentDose = $patientAssignmentFormatDose($assignment);
    $patientAssignmentCurrentForm = $patientAssignmentForm($assignment);
    $patientAssignmentCurrentMnn = $patientAssignmentMnn($assignment);
@endphp
@if($patientAssignmentCurrentDose || $patientAssignmentCurrentForm || $patientAssignmentCurrentMnn)
    <div class="patient-assignment-extra-meta">
        @if($patientAssignmentCurrentForm)
            <span class="patient-assignment-extra-meta__chip">
                <i class="bi bi-capsule"></i>
                Форма: {{ $patientAssignmentCurrentForm }}
            </span>
        @endif

        @if($patientAssignmentCurrentDose)
            <span class="patient-assignment-extra-meta__chip">
                <i class="bi bi-speedometer2"></i>
                Дозировка: {{ $patientAssignmentCurrentDose }}
            </span>
        @endif

        @if($patientAssignmentCurrentMnn)
            <span class="patient-assignment-extra-meta__chip">
                <i class="bi bi-tag"></i>
                МНН: {{ $patientAssignmentCurrentMnn }}
            </span>
        @endif
    </div>
@endif

                            @if($assignment->generic_name_snapshot && !$patientAssignmentCurrentMnn)
                                <p class="patient-assignment-generic">
                                    МНН: {{ $assignment->generic_name_snapshot }}
                                </p>
                            @endif
                        </div>

                    </div>

                    <div class="patient-assignment-primary">
                        <div class="patient-assignment-primary__icon">
                            <i class="bi bi-capsule-pill"></i>
                        </div>
                        <div>
                            <span>Как принимать</span>
                            <strong>{{ $assignment->schedule }}</strong>
                        </div>
                    </div>

                    <div class="patient-assignment-grid">
                        <section class="patient-assignment-info is-doctor">
                            <div class="patient-assignment-info__title">
                                <i class="bi bi-person-badge"></i>
                                Инструкция врача
                            </div>
                            <div class="patient-assignment-info__text">
                                {{ $assignment->doctor_instructions }}
                            </div>
                        </section>

                        <section class="patient-assignment-info">
                            <div class="patient-assignment-info__title">
                                <i class="bi bi-bullseye"></i>
                                Почему назначено
                            </div>
                            <div class="patient-assignment-info__text">
                                {{ $assignment->indication_text }}
                            </div>
                        </section>

                        <section class="patient-assignment-info is-warning">
                            <div class="patient-assignment-info__title">
                                <i class="bi bi-exclamation-triangle"></i>
                                Противопоказания и ограничения
                            </div>
                            <div class="patient-assignment-info__text">
                                {{ $assignment->contraindications_text }}
                            </div>
                        </section>

                        @if($assignment->side_effects_text)
                            <section class="patient-assignment-info">
                                <div class="patient-assignment-info__title">
                                    <i class="bi bi-activity"></i>
                                    Возможные нежелательные реакции
                                </div>
                                <div class="patient-assignment-info__text">
                                    {{ $assignment->side_effects_text }}
                                </div>
                            </section>
                        @endif

                        @if($assignment->missed_dose_instructions)
                            <section class="patient-assignment-info">
                                <div class="patient-assignment-info__title">
                                    <i class="bi bi-clock-history"></i>
                                    Если пропущен приём
                                </div>
                                <div class="patient-assignment-info__text">
                                    {{ $assignment->missed_dose_instructions }}
                                </div>
                            </section>
                        @endif

                        @if($assignment->warning_signs)
                            <section class="patient-assignment-info is-warning">
                                <div class="patient-assignment-info__title">
                                    <i class="bi bi-heart-pulse"></i>
                                    Когда связаться с врачом срочно
                                </div>
                                <div class="patient-assignment-info__text">
                                    {{ $assignment->warning_signs }}
                                </div>
                            </section>
                        @endif
                    </div>

                    <section class="patient-assignment-overdose">
                        <div class="patient-assignment-overdose__icon">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                        <div>
                            <span>При подозрении на передозировку</span>
                            <strong>{{ $assignment->overdose_actions }}</strong>
                            <small>
                                При выраженном ухудшении или угрозе жизни —
                                немедленно обращайтесь в экстренную службу.
                            </small>
                        </div>
                    </section>

                    <footer class="patient-assignment-card__footer">
                        <span>
                            <i class="bi bi-hospital"></i>
                            {{ $assignment->clinic?->name ?: ($assignment->clinic_name_snapshot ?: 'Клиника не указана') }}
                        </span>
                        <span>
                            <i class="bi bi-person-badge"></i>
                            {{ $assignment->doctor_name_snapshot }}
                        </span>
                        <span>
                            <i class="bi bi-calendar2-check"></i>
                            Назначено {{ $assignment->created_at?->format('d.m.Y H:i') }}
                        </span>
                    </footer>
                </article>
            @empty
                <div class="patient-assignment-empty is-compact">
                    <i class="bi bi-capsule"></i>
                    <div>
                        <h2>
                            @if($showClinicTabs && $historyAssignments->isEmpty())
                                В этой клинике пока нет назначений
                            @else
                                Активных назначений нет
                            @endif
                        </h2>
                        <p>Если врач назначит лечение, инструкция появится здесь.</p>
                    </div>
                </div>
            @endforelse
        </section>

        @if($historyAssignments->isNotEmpty())
            <section class="patient-assignment-section is-history">
                <div class="patient-assignment-section__head">
                    <div>
                        <span class="patient-eyebrow">ИСТОРИЯ</span>
                        <h2>Завершённые и отменённые</h2>
                    </div>
                    <span class="patient-assignment-count">{{ $historyAssignments->count() }}</span>
                </div>

                <div class="patient-assignment-history-list">
                    @foreach($historyAssignments as $assignment)
                        @php
                            $historyDose = $patientAssignmentFormatDose($assignment);
                            $historyForm = $patientAssignmentForm($assignment);
                        @endphp

                        <details class="patient-assignment-history">
                            <summary>
                                <div class="patient-assignment-history__summary-copy">
                                    <strong>{{ $assignment->drug_name_snapshot }}</strong>

                                    @if($historyForm || $historyDose)
                                        <span class="patient-assignment-extra-meta patient-assignment-extra-meta--history">
                                            @if($historyForm)
                                                <span class="patient-assignment-extra-meta__chip patient-assignment-extra-meta__chip--form">
                                                    {{ $historyForm }}
                                                </span>
                                            @endif
                                            @if($historyDose)
                                                <span class="patient-assignment-extra-meta__chip patient-assignment-extra-meta__chip--dose">
                                                    {{ $historyDose }}
                                                </span>
                                            @endif
                                        </span>
                                    @endif

                                    <span>{{ $assignment->schedule }}</span>
                                </div>

                                <span @class([
                                    'patient-assignment-status',
                                    'is-completed' => $assignment->status === 'completed',
                                    'is-cancelled' => $assignment->status === 'cancelled',
                                ])>
                                    {{ $assignment->status_label }}
                                </span>
                            </summary>

                            <div class="patient-assignment-history__body">
                                <div><b>Показание:</b> {{ $assignment->indication_text }}</div>
                                <div><b>Инструкция врача:</b> {{ $assignment->doctor_instructions }}</div>
                                <div><b>Противопоказания:</b> {{ $assignment->contraindications_text }}</div>

                                @if($assignment->side_effects_text)
                                    <div><b>Нежелательные реакции:</b> {{ $assignment->side_effects_text }}</div>
                                @endif

                                @if($assignment->missed_dose_instructions)
                                    <div><b>Если пропущен приём:</b> {{ $assignment->missed_dose_instructions }}</div>
                                @endif

                                <div><b>Действия при передозировке:</b> {{ $assignment->overdose_actions }}</div>
                                <div class="patient-assignment-history__origin">
                                    <i class="bi bi-hospital"></i>
                                    {{ $assignment->clinic?->name ?: ($assignment->clinic_name_snapshot ?: 'Клиника не указана') }}
                                    <span>·</span>
                                    <i class="bi bi-person-badge"></i>
                                    {{ $assignment->doctor_name_snapshot }}
                                    <span>·</span>
                                    Назначено {{ $assignment->created_at?->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>

<style id="patient-assignments-clinic-style">
    .patient-assignments-page {
        --assignment-ink: #183b39;
        --assignment-muted: #627b78;
        --assignment-line: #dcebe7;
        --assignment-accent: #0c9487;
        font-size: 1rem;
    }

    .patient-assignments-page .patient-page-header h1 {
        font-size: clamp(2rem, 2.5vw, 2.45rem) !important;
        line-height: 1.12 !important;
        font-weight: 800 !important;
        color: var(--assignment-ink);
    }

    .patient-assignments-page .patient-page-header p {
        font-size: 1rem !important;
        line-height: 1.55 !important;
        color: var(--assignment-muted);
    }

    .patient-assignments-page .patient-eyebrow {
        font-size: .78rem;
        letter-spacing: .08em;
        font-weight: 800;
    }

    .patient-assignment-safety {
        padding: 1.05rem 1.15rem;
        gap: .85rem;
    }

    .patient-assignment-safety strong {
        font-size: 1rem;
        line-height: 1.45;
    }

    .patient-assignment-safety p {
        font-size: .92rem;
        line-height: 1.55;
        margin: .3rem 0 0;
    }

    .patient-assignment-clinic-preference {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1rem 1.15rem;
        margin: 1rem 0;
        border: 1px solid var(--assignment-line);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 5px 18px rgba(24, 59, 57, .045);
    }

    .patient-assignment-clinic-preference__copy h2 {
        margin: .18rem 0 .2rem;
        color: var(--assignment-ink);
        font-size: 1.18rem;
        font-weight: 800;
    }

    .patient-assignment-clinic-preference__copy p {
        margin: 0;
        color: var(--assignment-muted);
        font-size: .9rem;
        line-height: 1.45;
    }

    .patient-assignment-clinic-preference__form {
        width: min(100%, 470px);
    }

    .patient-assignment-clinic-preference__form label {
        display: block;
        margin-bottom: .35rem;
        color: #47625f;
        font-size: .78rem;
        font-weight: 800;
    }

    .patient-assignment-clinic-preference__controls {
        display: flex;
        gap: .5rem;
    }

    .patient-assignment-clinic-preference__controls select {
        flex: 1 1 auto;
        min-width: 0;
        min-height: 42px;
        padding: .55rem .75rem;
        border: 1px solid #d4e5e0;
        border-radius: .7rem;
        background: #f9fcfb;
        color: var(--assignment-ink);
        font: inherit;
        font-size: .9rem;
    }

    .patient-assignment-clinic-preference__controls button {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        min-height: 42px;
        padding: .55rem .85rem;
        border: 0;
        border-radius: .7rem;
        background: #087f75;
        color: #fff;
        font-size: .85rem;
        font-weight: 800;
        cursor: pointer;
    }

    .patient-assignment-clinic-preference__controls button:hover {
        background: #066d65;
    }

    .patient-assignment-clinic-preference__error {
        display: block;
        margin-top: .35rem;
        color: #b42318;
        font-size: .78rem;
        line-height: 1.4;
    }

    .patient-assignment-clinic-preference__saved {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin: -.35rem 0 1rem;
        padding: .65rem .85rem;
        border: 1px solid #cce9dd;
        border-radius: .7rem;
        background: #f0faf5;
        color: #176b4a;
        font-size: .88rem;
        font-weight: 700;
    }

    .patient-assignment-clinic-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin: 1rem 0 1.25rem;
    }

    .patient-assignment-clinic-tab {
        display: flex;
        align-items: center;
        gap: .7rem;
        min-width: min(250px, 100%);
        max-width: 100%;
        padding: .7rem .9rem;
        border: 1px solid #dce9e6;
        border-radius: .85rem;
        background: #fff;
        color: #34524f;
        text-decoration: none;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .patient-assignment-clinic-tab:hover {
        border-color: #9ccfc6;
        background: #f8fcfb;
        color: #0b756b;
    }

    .patient-assignment-clinic-tab.is-active {
        border-color: #55b9ad;
        background: #eaf8f5;
        color: #086e65;
        box-shadow: 0 0 0 2px rgba(12, 148, 135, .08);
    }

    .patient-assignment-clinic-tab__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: .7rem;
        background: #eaf5f3;
        color: #0d8c81;
        font-size: 1.05rem;
    }

    .patient-assignment-clinic-tab__copy {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-width: 0;
        gap: .12rem;
    }

    .patient-assignment-clinic-tab__copy strong {
        overflow-wrap: anywhere;
        font-size: .9rem;
        line-height: 1.3;
        font-weight: 800;
    }

    .patient-assignment-clinic-tab__copy small {
        color: #6b8580;
        font-size: .73rem;
        line-height: 1.3;
    }

    .patient-assignment-clinic-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 27px;
        width: 27px;
        height: 27px;
        border-radius: 999px;
        background: rgba(12, 148, 135, .11);
        font-size: .78rem;
        font-weight: 800;
    }

    .patient-assignment-section__head h2 {
        font-size: 1.35rem !important;
        line-height: 1.25 !important;
        font-weight: 800 !important;
        color: var(--assignment-ink);
    }

    .patient-assignment-count {
        font-size: .85rem;
        font-weight: 800;
    }

    .patient-assignment-card {
        font-size: .98rem;
    }

    .patient-assignment-card__top h3 {
        margin: .35rem 0 .15rem;
        font-size: 1.55rem !important;
        line-height: 1.25 !important;
        font-weight: 850 !important;
        color: #173c39;
    }

    .patient-assignment-card__eyebrow {
        font-size: .78rem;
        line-height: 1.4;
    }

    .patient-assignment-status {
        font-size: .72rem;
        font-weight: 800;
    }

    .patient-assignment-extra-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        margin: .38rem 0 .15rem;
    }

    .patient-assignment-extra-meta__chip {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        padding: .22rem .48rem;
        border-radius: 999px;
        background: #eef7f6;
        border: 1px solid #d3ebe8;
        color: #1a5f58;
        font-size: .72rem !important;
        font-weight: 750;
        line-height: 1.15;
    }

    .patient-assignment-extra-meta__chip--form {
        background: #f3f5ff;
        border-color: #dde1fa;
        color: #4c5686;
    }

    .patient-assignment-extra-meta__chip--dose {
        background: #ecf9f6;
        border-color: #cfece6;
        color: #116a61;
    }

    .patient-assignment-generic {
        margin: .2rem 0 0;
        color: #718681;
        font-size: .82rem;
    }

    .patient-assignment-primary {
        padding: .85rem 1rem;
    }

    .patient-assignment-primary > div:last-child > span {
        font-size: .78rem;
        line-height: 1.35;
    }

    .patient-assignment-primary strong {
        font-size: 1.02rem;
        line-height: 1.5;
    }

    .patient-assignment-info {
        padding: .9rem 1rem;
    }

    .patient-assignment-info__title {
        margin-bottom: .45rem;
        font-size: .88rem !important;
        line-height: 1.35;
        font-weight: 800;
    }

    .patient-assignment-info__text {
        font-size: .94rem !important;
        line-height: 1.55 !important;
        overflow-wrap: anywhere;
    }

    .patient-assignment-overdose {
        padding: .9rem 1rem;
        gap: .7rem;
    }

    .patient-assignment-overdose span {
        font-size: .8rem;
        line-height: 1.4;
        font-weight: 800;
    }

    .patient-assignment-overdose strong {
        font-size: .96rem;
        line-height: 1.5;
    }

    .patient-assignment-overdose small {
        font-size: .78rem;
        line-height: 1.45;
    }

    .patient-assignment-card__footer {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem 1rem;
        font-size: .78rem;
        line-height: 1.4;
    }

    .patient-assignment-history {
        padding: .85rem 1rem;
    }

    .patient-assignment-history summary {
        gap: .8rem;
    }

    .patient-assignment-history__summary-copy {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .2rem;
        min-width: 0;
    }

    .patient-assignment-history__summary-copy > strong {
        color: #183b39;
        font-size: 1.02rem;
        line-height: 1.35;
    }

    .patient-assignment-history__summary-copy > span:not(.patient-assignment-extra-meta) {
        color: #607a75;
        font-size: .88rem;
        line-height: 1.4;
    }

    .patient-assignment-extra-meta--history {
        margin: .15rem 0;
    }

    .patient-assignment-history__body {
        display: grid;
        gap: .55rem;
        padding-top: .9rem;
        font-size: .92rem;
        line-height: 1.55;
    }

    .patient-assignment-history__origin {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .35rem;
        margin-top: .25rem;
        color: #68817c;
        font-size: .78rem;
    }

    @media (max-width: 760px) {
        .patient-assignment-clinic-preference {
            align-items: stretch;
            flex-direction: column;
        }

        .patient-assignment-clinic-preference__form {
            width: 100%;
        }

        .patient-assignment-clinic-tab {
            width: 100%;
        }

        .patient-assignment-card__top h3 {
            font-size: 1.35rem !important;
        }

        .patient-assignment-info__text,
        .patient-assignment-history__body {
            font-size: .9rem !important;
        }
    }
</style>

@endsection
