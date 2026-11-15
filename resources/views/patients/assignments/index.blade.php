@extends('layouts.patient')

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

                            @if($assignment->generic_name_snapshot)
                                <p class="patient-assignment-generic">
                                    МНН: {{ $assignment->generic_name_snapshot }}
                                </p>
                            @endif
                        </div>

                        <div class="patient-assignment-dose">
                            <span>Дозировка</span>
                            <strong>{{ $assignment->dosage }}</strong>
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
                            {{ $assignment->clinic_name_snapshot ?: 'Клиника не указана' }}
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
                        <h2>Активных назначений нет</h2>
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
                        <details class="patient-assignment-history">
                            <summary>
                                <div>
                                    <strong>{{ $assignment->drug_name_snapshot }}</strong>
                                    <span>
                                        {{ $assignment->dosage }} · {{ $assignment->schedule }}
                                    </span>
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
                                <div><b>Действия при передозировке:</b> {{ $assignment->overdose_actions }}</div>
                                <div class="text-muted">
                                    {{ $assignment->clinic_name_snapshot ?: 'Клиника не указана' }}
                                    · {{ $assignment->doctor_name_snapshot }}
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
@endsection
