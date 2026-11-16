@extends('layouts.patient')

@php
    $patientAssignmentClinicNameV15 = static function ($assignment) {
        foreach ([
            'clinic.name',
            'clinic.title',
            'clinic_name',
            'origin.clinic_name',
            'clinical_origin.clinic_name',
            'prescription.clinic.name',
            'prescription.clinic_name',
        ] as $key) {
            $value = data_get($assignment, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return 'Клиника не указана';
    };

    $patientAssignmentClinicIdV15 = static function ($assignment) {
        foreach ([
            'clinic_id',
            'clinic.id',
            'origin.clinic_id',
            'clinical_origin.clinic_id',
            'prescription.clinic_id',
            'prescription.clinic.id',
        ] as $key) {
            $value = data_get($assignment, $key);

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return 'unknown';
    };

    $patientAssignmentUnitV15 = static function ($assignment) {
        foreach ([
            'dosage_unit',
            'dose_unit',
            'unit',
            'measurement_unit',
            'prescription.dosage_unit',
            'prescription.dose_unit',
            'prescription.unit',
        ] as $key) {
            $value = data_get($assignment, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        $dosage = (string) (
            data_get($assignment, 'dosage')
            ?? data_get($assignment, 'dose')
            ?? data_get($assignment, 'prescription.dosage')
            ?? ''
        );

        if (
            preg_match(
                '/\b(мкг\/мл|мг\/мл|мкг|мг|мл|г|ЕД|МЕ)\b/ui',
                $dosage,
                $matches
            )
        ) {
            return $matches[1];
        }

        $form = mb_strtolower(
            trim((string) (
                data_get($assignment, 'drug_form')
                ?? data_get($assignment, 'form')
                ?? data_get($assignment, 'prescription.drug_form')
                ?? ''
            ))
        );

        if (
            preg_match(
                '/таблет|капсул|драже|порош/ui',
                $form
            )
        ) {
            return 'мг';
        }

        if (
            preg_match(
                '/раствор|суспенз|сироп|капл|ампул|флакон/ui',
                $form
            )
        ) {
            return 'мл';
        }

        return null;
    };

    $patientAssignmentDoseV15 = static function ($assignment) use ($patientAssignmentUnitV15) {
        $dosage = trim((string) (
            data_get($assignment, 'dosage')
            ?? data_get($assignment, 'dose')
            ?? data_get($assignment, 'prescription.dosage')
            ?? ''
        ));

        if ($dosage === '') {
            return null;
        }

        if (
            preg_match(
                '/\b(мкг\/мл|мг\/мл|мкг|мг|мл|г|ЕД|МЕ)\b/ui',
                $dosage
            )
        ) {
            return $dosage;
        }

        $unit = $patientAssignmentUnitV15($assignment);

        return $unit
            ? trim($dosage . ' ' . $unit)
            : $dosage;
    };

    $patientAssignmentFormV15 = static function ($assignment) {
        foreach ([
            'drug_form',
            'form',
            'form_name',
            'drug.form_name',
            'drug.form',
            'prescription.drug_form',
            'prescription.form',
        ] as $key) {
            $value = data_get($assignment, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    };
@endphp

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
                    @php
    $patientAssignmentClinicGroupsV15 = collect($historyAssignments)
        ->groupBy(function ($assignment) use ($patientAssignmentClinicIdV15, $patientAssignmentClinicNameV15) {
            return $patientAssignmentClinicIdV15($assignment) . '|' . $patientAssignmentClinicNameV15($assignment);
        });
@endphp

@if($patientAssignmentClinicGroupsV15->count() > 1)
    <div class="patient-assignment-clinic-tabs"
         data-patient-assignment-clinic-tabs>
        @foreach($patientAssignmentClinicGroupsV15 as $patientAssignmentClinicKeyV15 => $patientAssignmentClinicItemsV15)
            @php
                [$patientAssignmentClinicIdTabV15, $patientAssignmentClinicNameTabV15] =
                    array_pad(explode('|', $patientAssignmentClinicKeyV15, 2), 2, '');
            @endphp

            <button type="button"
                    class="patient-assignment-clinic-tab"
                    data-clinic-tab="{ $patientAssignmentClinicIdTabV15 }">
                <i class="bi bi-building"></i>
                <span>{ $patientAssignmentClinicNameTabV15 }</span>
                <span class="patient-assignment-clinic-tab__count">
                    { $patientAssignmentClinicItemsV15->count() }
                </span>
            </button>
        @endforeach
    </div>
@endif

@foreach($historyAssignments as $assignment)
    @php
        $patientAssignmentClinicIdCurrentV15 = $patientAssignmentClinicIdV15($assignment);
        $patientAssignmentDoseCurrentV15 = $patientAssignmentDoseV15($assignment);
        $patientAssignmentFormCurrentV15 = $patientAssignmentFormV15($assignment);
    @endphp

    <div class="patient-assignment-clinic-item"
         data-assignment-clinic-id="{{ $patientAssignmentClinicIdCurrentV15 }}">
                        
@if($patientAssignmentFormCurrentV15 || $patientAssignmentDoseCurrentV15)
            <div class="patient-assignment-extra-meta">
                @if($patientAssignmentFormCurrentV15)
                    <span class="patient-assignment-extra-meta__chip patient-assignment-extra-meta__chip--form">
                        <i class="bi bi-capsule"></i>
                        {{ $patientAssignmentFormCurrentV15 }}
                    </span>
                @endif

                @if($patientAssignmentDoseCurrentV15)
                    <span class="patient-assignment-extra-meta__chip patient-assignment-extra-meta__chip--dose">
                        <i class="bi bi-speedometer2"></i>
                        {{ $patientAssignmentDoseCurrentV15 }}
                    </span>
                @endif
            </div>
        @endif
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
                        </div>
@endforeach
                </div>
            </section>
        @endif
    @endif
</div>

<style id="patient-assignments-v14-style">
    .patient-assignment-card__title,
    .patient-assignment-drug-title,
    .patient-assignment-name,
    .assignments-page .card h1,
    .assignments-page .card h2,
    .assignments-page .card h3,
    .assignments-page .card h4 {
        font-size: 1.8rem !important;
        line-height: 1.2 !important;
        font-weight: 800 !important;
    }

    .patient-assignment-card__subtitle,
    .patient-assignment-mnn,
    .patient-assignment-secondary,
    .assignments-page .card .small,
    .assignments-page .card .text-muted {
        font-size: 0.98rem !important;
        line-height: 1.45 !important;
    }

    .patient-assignment-card__section-title,
    .patient-assignment-card__label,
    .assignments-page .card .fw-semibold {
        font-size: 1.02rem !important;
        line-height: 1.4 !important;
    }

    .patient-assignment-extra-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin: .45rem 0 .2rem;
    }

    .patient-assignment-extra-meta__chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        background: #eef7f6;
        border: 1px solid #d3ebe8;
        color: #1a5f58;
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.15;
    }

    .patient-assignment-dose-box__label {
        font-size: .85rem !important;
        color: #6d8590;
        line-height: 1.2;
        margin-bottom: .15rem;
    }

    .patient-assignment-dose-box__value {
        font-size: 1.15rem !important;
        font-weight: 800;
        line-height: 1.15;
    }
</style>

<style id="patient-assignments-v15-style">
    .patient-assignment-clinic-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin: 0 0 1rem;
    }

    .patient-assignment-clinic-tab {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .55rem .85rem;
        border: 1px solid #d8e6e3;
        border-radius: .7rem;
        background: #fff;
        color: #31514d;
        font-size: .95rem;
        font-weight: 750;
        cursor: pointer;
        transition: .15s ease;
    }

    .patient-assignment-clinic-tab:hover {
        border-color: #9dcfc8;
        background: #f7fbfa;
    }

    .patient-assignment-clinic-tab.is-active {
        border-color: #57b9ae;
        background: #eaf8f5;
        color: #0c7067;
        box-shadow: 0 0 0 .12rem rgba(15, 140, 130, .06);
    }

    .patient-assignment-clinic-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.45rem;
        height: 1.45rem;
        padding: 0 .35rem;
        border-radius: 999px;
        background: rgba(15, 140, 130, .10);
        font-size: .78rem;
    }

    .patient-assignment-clinic-item[hidden] {
        display: none !important;
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
</style>

<script>
(() => {
    const root = document.querySelector('[data-patient-assignment-clinic-tabs]');
    if (!root) return;

    const tabs = Array.from(
        root.querySelectorAll('[data-clinic-tab]')
    );

    const items = Array.from(
        document.querySelectorAll('[data-assignment-clinic-id]')
    );

    if (tabs.length <= 1 || items.length === 0) {
        root.remove();
        return;
    }

    const selectClinic = (clinicId) => {
        tabs.forEach((tab) => {
            tab.classList.toggle(
                'is-active',
                tab.dataset.clinicTab === clinicId
            );
        });

        items.forEach((item) => {
            item.hidden =
                item.dataset.assignmentClinicId !== clinicId;
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            selectClinic(tab.dataset.clinicTab);
        });
    });

    selectClinic(tabs[0].dataset.clinicTab);
})();
</script>

@endsection
