@php
    $isEmergency = ($type ?? 'planned') === 'emergency';
    $selectedPatientName = $selectedPatient
        ? collect([
            $selectedPatient->surname,
            $selectedPatient->name,
            $selectedPatient->patronym,
        ])->filter()->implode(' ')
        : '';
@endphp

<aside class="hospital-sidebar">
    <div class="hospital-sidebar__sticky">
        <section class="hospital-sidebar-card hospital-sidebar-card--nav">
            <div class="hospital-sidebar-card__eyebrow">Госпитализация</div>
            <h2 class="hospital-sidebar-card__title">Тип направления</h2>

            <nav class="hospital-mode-nav" aria-label="Тип госпитализации">
                <a href="{{ route('doctors.referrals.hospital.planned') }}"
                   class="hospital-mode-link {{ !$isEmergency ? 'active' : '' }}">
                    <span class="hospital-mode-link__icon">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                    <span class="hospital-mode-link__copy">
                        <strong>Плановая</strong>
                        <small>Подготовка к лечению</small>
                    </span>
                    <i class="bi bi-chevron-right hospital-mode-link__arrow"></i>
                </a>

                <a href="{{ route('doctors.referrals.hospital.emergency') }}"
                   class="hospital-mode-link hospital-mode-link--emergency {{ $isEmergency ? 'active' : '' }}">
                    <span class="hospital-mode-link__icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                    <span class="hospital-mode-link__copy">
                        <strong>Экстренная</strong>
                        <small>Неотложное направление</small>
                    </span>
                    <i class="bi bi-chevron-right hospital-mode-link__arrow"></i>
                </a>
            </nav>
        </section>

        <section class="hospital-sidebar-card">
            <div class="hospital-sidebar-card__head">
                <span class="hospital-sidebar-card__head-icon">
                    <i class="bi bi-person-vcard"></i>
                </span>
                <div>
                    <div class="hospital-sidebar-card__eyebrow">Пациент</div>
                    <h2 class="hospital-sidebar-card__title">Выберите карту</h2>
                </div>
            </div>

            <label class="form-label hospital-label" for="hospitalizationPatient">
                Поиск пациента
            </label>

            <select id="hospitalizationPatient"
                    class="form-select hospital-patient-select"
                    data-placeholder="Фамилия, имя или отчество">
                @if($selectedPatient)
                    <option value="{{ $selectedPatient->id }}" selected>
                        {{ $selectedPatientName }}
                    </option>
                @endif
            </select>

            <div id="hospitalPatientCard"
                 class="hospital-patient-card {{ $selectedPatient ? '' : 'is-empty' }}">
                <div class="hospital-patient-card__avatar" data-patient-avatar>
                    @if($selectedPatient)
                        {{ mb_substr((string) $selectedPatient->surname, 0, 1) }}{{ mb_substr((string) $selectedPatient->name, 0, 1) }}
                    @else
                        <i class="bi bi-person"></i>
                    @endif
                </div>

                <div class="hospital-patient-card__body">
                    <div class="hospital-patient-card__name" data-patient-name>
                        {{ $selectedPatientName !== '' ? $selectedPatientName : 'Пациент не выбран' }}
                    </div>
                    <div class="hospital-patient-card__meta" data-patient-meta>
                        @if($selectedPatient)
                            {{ $selectedPatient->birth_at ? 'Дата рождения: ' . $selectedPatient->birth_at : 'Дата рождения не указана' }}
                        @else
                            Начните вводить фамилию
                        @endif
                    </div>
                    <div class="hospital-patient-card__diagnosis" data-patient-diagnosis>
                        @if($selectedPatient?->diagnose)
                            {{ $selectedPatient->diagnose->code }} — {{ $selectedPatient->diagnose->title ?? $selectedPatient->diagnose->name }}
                        @else
                            Текущий диагноз не указан — можно сформировать предварительный
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="hospital-sidebar-card hospital-sidebar-card--notice">
            <div class="hospital-notice-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <strong>Решение остаётся за врачом</strong>
                <p>
                    Qwen формирует проект, MedGemma перепроверяет клиническую согласованность.
                    Тип госпитализации и содержание направления подтверждаются вручную.
                </p>
            </div>
        </section>
    </div>
</aside>
