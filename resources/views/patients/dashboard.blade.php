@extends('layouts.patient')

@section('title', 'Личный кабинет')

@section('content')
    @php
        $patientName = $patient?->name ?: auth()->user()->login;
    @endphp

    <div class="patient-page">
        <header class="patient-page-header">
            <div>
                <div class="patient-page-header__eyebrow">
                    Личный кабинет пациента
                </div>

                <h1>
                    Здравствуйте, {{ $patientName }}.
                </h1>

                <p>
                    Здесь собраны ближайшие приёмы и основные события вашего лечения.
                </p>
            </div>

            <div class="patient-page-header__date">
                <i class="bi bi-calendar3"></i>
                {{ now()->locale('ru')->translatedFormat('d F Y') }}
            </div>
        </header>

        @if(!$patient)
            <section class="patient-link-warning" role="status">
                <div class="patient-link-warning__icon">
                    <i class="bi bi-person-exclamation"></i>
                </div>

                <div>
                    <strong>Медицинская карта пока не привязана</strong>
                    <p>
                        Учётная запись создана, но связь с существующей медицинской
                        картой ещё не подтверждена. После привязки здесь появятся
                        приёмы, назначения, исследования и документы.
                    </p>
                </div>
            </section>
        @endif

        @if($patient && empty($clinicOptions))
            <section class="patient-link-warning" role="status">
                <div class="patient-link-warning__icon">
                    <i class="bi bi-hospital"></i>
                </div>

                <div>
                    <strong>Клиники пока не привязаны</strong>
                    <p>
                        Медицинская карта подтверждена, но в профиле ещё нет активной связи
                        ни с одной клиникой. Связь появится после обращения или записи.
                    </p>
                </div>
            </section>
        @endif

        @if($patient && !empty($clinicOptions))
            <section class="patient-clinic-context">
                <div class="patient-clinic-context__copy">
                    <div class="patient-section-label">Текущая клиника</div>
                    <strong>{{ $selectedClinic?->name ?: 'Клиника не выбрана' }}</strong>
                    <span>
                        @if($selectedClinic?->city)
                            {{ $selectedClinic->city }}
                        @else
                            Все данные и расписание ниже относятся к выбранной клинике.
                        @endif
                    </span>
                </div>

                <div class="patient-clinic-context__actions">
                    @if(count($clinicOptions) > 1)
                        <form method="GET" action="{{ route('patients.dashboard') }}">
                            <input type="hidden" name="month" value="{{ $currentMonth }}">
                            <label for="patientDashboardClinic" class="visually-hidden">
                                Выберите клинику
                            </label>
                            <select
                                id="patientDashboardClinic"
                                name="clinic_id"
                                onchange="this.form.submit()"
                            >
                                @foreach($clinicOptions as $clinicOption)
                                    <option
                                        value="{{ $clinicOption['id'] }}"
                                        @selected((int) $clinicOption['id'] === (int) $selectedClinicId)
                                    >
                                        {{ $clinicOption['name'] }}{{ $clinicOption['is_primary'] ? ' · основная' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    @if($selectedClinic?->clinic_type !== \App\Models\Clinic::TYPE_INPATIENT)
                        <a
                            href="{{ route('patients.calendar', ['clinic_id' => $selectedClinicId]) }}"
                            class="patient-clinic-context__book"
                        >
                            <i class="bi bi-calendar-plus"></i>
                            Записаться
                        </a>
                    @else
                        <span class="patient-clinic-context__inpatient">
                            <i class="bi bi-hospital"></i>
                            Стационар
                        </span>
                    @endif
                </div>
            </section>
        @endif

        <section class="patient-summary-grid">
            <article class="patient-summary-card patient-summary-card--primary">
                <div class="patient-summary-card__icon">
                    <i class="bi bi-calendar2-heart"></i>
                </div>

                <div class="patient-summary-card__body">
                    <span>Ближайший приём</span>

                    @if($nextAppointment)
                        <strong>
                            {{ $nextAppointment['starts_at']->locale('ru')->translatedFormat('d F, H:i') }}
                        </strong>

                        <p>
                            @include('patients.partials.doctor-tooltip-trigger', [
                                'label' => $nextAppointment['doctor_short'],
                                'doctorName' => $nextAppointment['doctor_name'],
                                'experience' => $nextAppointment['doctor_experience'],
                                'speciality' => $nextAppointment['doctor_speciality'],
                                'initials' => $nextAppointment['doctor_initials'],
                                'cabinet' => $nextAppointment['doctor_cabinet'],
                                'phone' => $nextAppointment['doctor_phone'],
                                'status' => ($nextAppointment['status_code'] ?? 'upcoming') !== 'upcoming'
                                    ? ($nextAppointment['status_tooltip'] ?? '')
                                    : '',
                                'statusCode' => $nextAppointment['status_code'] ?? 'upcoming',
                            ])
                            · {{ $nextAppointment['format_label'] }}
                        </p>

                        <small class="patient-summary-card__clinic">
                            {{ $nextAppointment['clinic_name'] }}
                        </small>
                    @else
                        <strong>Пока не назначен</strong>
                        <p>Новых записей к врачу нет.</p>
                    @endif
                </div>
            </article>

            <article class="patient-summary-card">
                <div class="patient-summary-card__icon">
                    <i class="bi bi-person-badge"></i>
                </div>

                <div class="patient-summary-card__body">
                    <span>Лечащий врач</span>

                    @if($assignedDoctor)
                        <strong>
                            @include('patients.partials.doctor-tooltip-trigger', [
                                'label' => trim(implode(' ', array_filter([
                                    $assignedDoctor->surname,
                                    $assignedDoctor->name,
                                    $assignedDoctor->patronym
                                ]))),
                                'doctorName' => trim(implode(' ', array_filter([
                                    $assignedDoctor->surname,
                                    $assignedDoctor->name,
                                    $assignedDoctor->patronym
                                ]))),
                                'experience' => $assignedDoctorExperience,
                                'speciality' => $assignedDoctorSpeciality,
                                'initials' => $assignedDoctorInitials,
                                'cabinet' => $assignedDoctorCabinet,
                                'phone' => $assignedDoctorPhone,
                            ])
                        </strong>

                        <p>
                            {{ $assignedDoctorSpeciality }}
                        </p>

                        <small class="patient-summary-card__clinic">
                            {{ $selectedClinic?->name }}
                        </small>
                    @else
                        <strong>Не назначен</strong>
                        <p>В выбранной клинике лечащий врач пока не назначен.</p>
                    @endif
                </div>
            </article>

            <article class="patient-summary-card">
                <div class="patient-summary-card__icon">
                    <i class="bi bi-calendar-check"></i>
                </div>

                <div class="patient-summary-card__body">
                    <span>Приёмов в этом месяце</span>
                    <strong>{{ $monthAppointmentsCount }}</strong>
                    <p>{{ $monthLabel }}</p>
                    <small class="patient-summary-card__clinic">
                        {{ $selectedClinic?->name }}
                    </small>
                </div>
            </article>
        </section>

        <div class="patient-dashboard-grid">
            <section
                class="patient-calendar-card"
                id="patient-calendar"
                aria-labelledby="patient-calendar-title"
            >
                <div class="patient-calendar-card__header">
                    <div>
                        <div class="patient-section-label">
                            Расписание
                        </div>

                        <h2 id="patient-calendar-title">
                            {{ $monthLabel }}
                        </h2>

                        <p class="patient-calendar-card__clinic">
                            {{ $selectedClinic?->name }}
                        </p>
                    </div>

                    <div class="patient-calendar-nav">
                        <a
                            href="{{ route('patients.dashboard', ['month' => $prevMonth, 'clinic_id' => $selectedClinicId]) }}#patient-calendar"
                            class="patient-calendar-nav__button"
                            aria-label="Предыдущий месяц"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </a>

                        @if($currentMonth !== $todayMonth)
                            <a
                                href="{{ route('patients.dashboard', ['month' => $todayMonth, 'clinic_id' => $selectedClinicId]) }}#patient-calendar"
                                class="patient-calendar-nav__today"
                            >
                                Сегодня
                            </a>
                        @endif

                        <a
                            href="{{ route('patients.dashboard', ['month' => $nextMonth, 'clinic_id' => $selectedClinicId]) }}#patient-calendar"
                            class="patient-calendar-nav__button"
                            aria-label="Следующий месяц"
                        >
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="patient-calendar">
                    <div class="patient-calendar__weekdays" aria-hidden="true">
                        <span>Пн</span>
                        <span>Вт</span>
                        <span>Ср</span>
                        <span>Чт</span>
                        <span>Пт</span>
                        <span>Сб</span>
                        <span>Вс</span>
                    </div>

                    <div class="patient-calendar__weeks">
                        @foreach($calendarWeeks as $week)
                            <div class="patient-calendar__week">
                                @foreach($week as $day)
                                    <article
                                        @class([
                                            'patient-calendar-day',
                                            'is-outside' => !$day['in_month'],
                                            'is-today' => $day['is_today'],
                                            'has-appointments' => $day['appointments']->isNotEmpty(),
                                        ])
                                        aria-label="{{ $day['date']->locale('ru')->translatedFormat('d F Y') }}"
                                    >
                                        <div class="patient-calendar-day__number">
                                            {{ $day['date']->day }}
                                        </div>

                                        <div class="patient-calendar-day__events">
                                            @foreach($day['appointments']->take(2) as $appointment)
                                                <div
                                                    @class([
                                                        'patient-calendar-event',
                                                        'is-telemedicine' => $appointment['format'] === 'telemedicine',
                                                        'is-not-held' => $appointment['is_not_held'],
                                                        'is-missed' => $appointment['is_missed'],
                                                        'is-held' => $appointment['is_held'],
                                                        'is-arrived' => $appointment['is_arrived'],
                                                        'is-status-unknown' => $appointment['is_status_unknown'],
                                                    ])
                                                    aria-label="{{ $appointment['status_tooltip'] }}"
                                                >
                                                    <span>
                                                        {{ $appointment['starts_at']->format('H:i') }}
                                                    </span>

                                                    <small>
                                                        @include('patients.partials.doctor-tooltip-trigger', [
                                                            'label' => $appointment['doctor_short'],
                                                            'doctorName' => $appointment['doctor_name'],
                                                            'experience' => $appointment['doctor_experience'],
                                                            'speciality' => $appointment['doctor_speciality'],
                                                            'initials' => $appointment['doctor_initials'],
                                                            'cabinet' => $appointment['doctor_cabinet'],
                                                            'phone' => $appointment['doctor_phone'],
                                                            'status' => $appointment['status_code'] !== 'upcoming'
                                                                ? $appointment['status_tooltip']
                                                                : '',
                                                            'statusCode' => $appointment['status_code'],
                                                        ])
                                                    </small>
                                                </div>
                                            @endforeach

                                            @if($day['appointments']->count() > 2)
                                                <div class="patient-calendar-day__more">
                                                    +{{ $day['appointments']->count() - 2 }}
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="patient-calendar-legend">
                    <span>
                        <i class="patient-calendar-legend__dot"></i>
                        Очный приём
                    </span>

                    <span>
                        <i class="patient-calendar-legend__dot is-telemedicine"></i>
                        Телемедицина
                    </span>

                    <span>
                        <i class="patient-calendar-legend__dot is-arrived"></i>
                        Явка подтверждена
                    </span>

                    <span>
                        <i class="patient-calendar-legend__dot is-held"></i>
                        Консультация состоялась
                    </span>

                    <span>
                        <i class="patient-calendar-legend__dot is-status-unknown"></i>
                        Результат не указан
                    </span>

                    <span>
                        <i class="patient-calendar-legend__dot is-not-held"></i>
                        Приём не состоялся
                    </span>
                </div>
            </section>

            <aside class="patient-upcoming-card">
                <div class="patient-upcoming-card__header">
                    <div>
                        <div class="patient-section-label">
                            Ближайшие события
                        </div>
                        <h2>Приёмы</h2>
                    </div>

                    <span class="patient-upcoming-card__count">
                        {{ $displayAppointments->count() }}
                    </span>
                </div>

                @if($displayAppointments->isEmpty())
                    <div class="patient-upcoming-empty">
                        <div class="patient-upcoming-empty__icon">
                            <i class="bi bi-calendar2-check"></i>
                        </div>
                        <strong>Приёмов пока нет</strong>
                        <p>
                            Когда врач или регистратор назначит приём,
                            он появится здесь и в календаре.
                        </p>
                    </div>
                @else
                    <div class="patient-upcoming-list">
                        @foreach($displayAppointments as $appointment)
                            <article
                                @class([
                                    'patient-upcoming-item',
                                    'is-not-held' => $appointment['is_not_held'],
                                    'is-missed' => $appointment['is_missed'],
                                    'is-held' => $appointment['is_held'],
                                    'is-arrived' => $appointment['is_arrived'],
                                    'is-status-unknown' => $appointment['is_status_unknown'],
                                ])
                                @if($appointment['status_code'] !== 'upcoming')
                                    aria-label="{{ $appointment['status_tooltip'] }}"
                                @endif
                            >
                                <div class="patient-upcoming-item__date">
                                    <strong>
                                        {{ $appointment['starts_at']->format('d') }}
                                    </strong>
                                    <span>
                                        {{ mb_strtoupper(
                                            mb_substr(
                                                $appointment['starts_at']
                                                    ->locale('ru')
                                                    ->translatedFormat('M'),
                                                0,
                                                3
                                            )
                                        ) }}
                                    </span>
                                </div>

                                <div class="patient-upcoming-item__body">
                                    <div class="patient-upcoming-item__time">
                                        {{ $appointment['starts_at']->format('H:i') }}

                                        @if($appointment['duration_minutes'])
                                            <span>
                                                · {{ $appointment['duration_minutes'] }} мин
                                            </span>
                                        @endif

                                        @if($appointment['status_message'])
                                            <span class="patient-upcoming-item__status is-{{ $appointment['status_code'] }}">
                                                — {{ $appointment['status_message'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <strong>
                                        @include('patients.partials.doctor-tooltip-trigger', [
                                            'label' => $appointment['doctor_name'],
                                            'doctorName' => $appointment['doctor_name'],
                                            'experience' => $appointment['doctor_experience'],
                                            'speciality' => $appointment['doctor_speciality'],
                                            'initials' => $appointment['doctor_initials'],
                                            'cabinet' => $appointment['doctor_cabinet'],
                                            'phone' => $appointment['doctor_phone'],
                                            'status' => $appointment['status_code'] !== 'upcoming'
                                                ? $appointment['status_tooltip']
                                                : '',
                                            'statusCode' => $appointment['status_code'],
                                        ])
                                    </strong>

                                    <p>
                                        {{ $appointment['clinic_name'] }}
                                    </p>

                                    <span
                                        class="patient-upcoming-item__format {{ $appointment['format'] === 'telemedicine' ? 'is-telemedicine' : '' }}"
                                    >
                                        <i class="bi {{ $appointment['format'] === 'telemedicine' ? 'bi-camera-video' : 'bi-geo-alt' }}"></i>
                                        {{ $appointment['format_label'] }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            function initPatientDoctorTooltips() {
                const triggers = document.querySelectorAll('[data-doctor-tooltip-trigger]');

                if (!triggers.length) {
                    return;
                }

                const tooltip = document.createElement('div');
                tooltip.className = 'patient-doctor-tooltip';
                tooltip.id = 'patient-doctor-tooltip';
                tooltip.setAttribute('role', 'tooltip');
                tooltip.setAttribute('aria-hidden', 'true');
                tooltip.innerHTML = `
                    <div class="patient-doctor-tooltip__avatar"></div>
                    <div class="patient-doctor-tooltip__content">
                        <strong class="patient-doctor-tooltip__name"></strong>
                        <div class="patient-doctor-tooltip__row">
                            <span>Стаж</span>
                            <b class="patient-doctor-tooltip__experience"></b>
                        </div>
                        <div class="patient-doctor-tooltip__row">
                            <span>Специализация</span>
                            <b class="patient-doctor-tooltip__speciality"></b>
                        </div>
                        <div class="patient-doctor-tooltip__row patient-doctor-tooltip__cabinet-row">
                            <span>Кабинет</span>
                            <b class="patient-doctor-tooltip__cabinet"></b>
                        </div>
                        <div class="patient-doctor-tooltip__row patient-doctor-tooltip__phone-row">
                            <span>Телефон</span>
                            <b class="patient-doctor-tooltip__phone"></b>
                        </div>
                        <div class="patient-doctor-tooltip__row patient-doctor-tooltip__status-row" hidden>
                            <span>Статус приёма</span>
                            <b class="patient-doctor-tooltip__status"></b>
                        </div>
                    </div>
                `;

                document.body.appendChild(tooltip);

                const avatar = tooltip.querySelector('.patient-doctor-tooltip__avatar');
                const name = tooltip.querySelector('.patient-doctor-tooltip__name');
                const experience = tooltip.querySelector('.patient-doctor-tooltip__experience');
                const speciality = tooltip.querySelector('.patient-doctor-tooltip__speciality');
                const cabinet = tooltip.querySelector('.patient-doctor-tooltip__cabinet');
                const phone = tooltip.querySelector('.patient-doctor-tooltip__phone');
                const cabinetRow = tooltip.querySelector('.patient-doctor-tooltip__cabinet-row');
                const phoneRow = tooltip.querySelector('.patient-doctor-tooltip__phone-row');
                const statusRow = tooltip.querySelector('.patient-doctor-tooltip__status-row');
                const status = tooltip.querySelector('.patient-doctor-tooltip__status');

                let activeTrigger = null;

                function positionTooltip(trigger) {
                    const triggerRect = trigger.getBoundingClientRect();
                    const tooltipRect = tooltip.getBoundingClientRect();
                    const margin = 10;

                    let left = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
                    left = Math.max(
                        margin,
                        Math.min(left, window.innerWidth - tooltipRect.width - margin)
                    );

                    let top = triggerRect.top - tooltipRect.height - margin;

                    if (top < margin) {
                        top = triggerRect.bottom + margin;
                    }

                    if (top + tooltipRect.height > window.innerHeight - margin) {
                        top = Math.max(margin, window.innerHeight - tooltipRect.height - margin);
                    }

                    tooltip.style.left = `${left}px`;
                    tooltip.style.top = `${top}px`;
                }

                function showTooltip(trigger) {
                    activeTrigger = trigger;

                    name.textContent = trigger.dataset.doctorName || 'Врач';
                    experience.textContent = trigger.dataset.doctorExperience || 'Не указан';
                    speciality.textContent = trigger.dataset.doctorSpeciality || 'Не указана';
                    avatar.textContent = trigger.dataset.doctorInitials || 'ВР';

                    const cabinetValue = (trigger.dataset.doctorCabinet || '').trim();
                    const phoneValue = (trigger.dataset.doctorPhone || '').trim();

                    cabinet.textContent = cabinetValue;
                    phone.textContent = phoneValue;
                    cabinetRow.style.display = cabinetValue ? 'grid' : 'none';
                    phoneRow.style.display = phoneValue ? 'grid' : 'none';

                    const statusValue = (trigger.dataset.doctorVisitStatus || '').trim();
                    const statusCode = (trigger.dataset.doctorVisitStatusCode || '').trim();

                    status.textContent = statusValue;
                    statusRow.hidden = !statusValue;
                    statusRow.className = 'patient-doctor-tooltip__row patient-doctor-tooltip__status-row'
                        + (statusCode ? ' is-' + statusCode : '');

                    tooltip.classList.add('is-visible');
                    tooltip.setAttribute('aria-hidden', 'false');
                    trigger.setAttribute('aria-describedby', tooltip.id);

                    positionTooltip(trigger);
                }

                function hideTooltip(trigger) {
                    if (trigger && trigger !== activeTrigger) {
                        return;
                    }

                    if (activeTrigger) {
                        activeTrigger.removeAttribute('aria-describedby');
                    }

                    activeTrigger = null;
                    tooltip.classList.remove('is-visible');
                    tooltip.setAttribute('aria-hidden', 'true');
                }

                triggers.forEach(function (trigger) {
                    trigger.addEventListener('pointerenter', function () {
                        showTooltip(trigger);
                    });

                    trigger.addEventListener('pointerleave', function () {
                        hideTooltip(trigger);
                    });

                    trigger.addEventListener('focus', function () {
                        showTooltip(trigger);
                    });

                    trigger.addEventListener('blur', function () {
                        hideTooltip(trigger);
                    });

                    trigger.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            hideTooltip(trigger);
                        }
                    });
                });

                window.addEventListener('scroll', function () {
                    hideTooltip();
                }, true);

                window.addEventListener('resize', function () {
                    if (activeTrigger) {
                        positionTooltip(activeTrigger);
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPatientDoctorTooltips, { once: true });
            } else {
                initPatientDoctorTooltips();
            }
        })();
    </script>
@endsection
