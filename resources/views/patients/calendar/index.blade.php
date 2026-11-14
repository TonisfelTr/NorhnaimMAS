@extends('layouts.patient')

@section('title', 'Календарь и запись на приём')

@section('content')
<div class="patient-page patient-booking-page">
    <header class="patient-page-header">
        <div>
            <span class="patient-eyebrow">ПЛАНИРОВАНИЕ ЛЕЧЕНИЯ</span>
            <h1>Календарь и запись</h1>
            <p>Выберите врача, формат консультации и свободное время.</p>
        </div>
        <a href="{{ route('patients.dashboard') }}" class="patient-booking-back">
            <i class="bi bi-arrow-left"></i> Обзор
        </a>
    </header>

    @if(session('success'))
        <div class="patient-booking-alert is-success" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="patient-booking-alert is-error" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div>
                <strong>Не удалось оформить запись</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($blockedReason)
        <section class="patient-booking-blocked">
            <div class="patient-booking-blocked__icon"><i class="bi bi-calendar-x"></i></div>
            <div>
                <h2>Онлайн-запись пока недоступна</h2>
                <p>{{ $blockedReason }}</p>
                @if(!$patient)
                    <a href="{{ route('patients.profile.index', ['tab' => 'identity']) }}" class="patient-booking-primary">
                        Перейти к привязке медкарты
                    </a>
                @endif
            </div>
        </section>
    @else
        <div class="patient-booking-layout">
            <section class="patient-booking-card">
                <div class="patient-booking-card__header">
                    <div>
                        <span class="patient-eyebrow">СВОБОДНОЕ ВРЕМЯ</span>
                        <h2>Выберите время приёма</h2>
                        <p>Свободные интервалы показываются одновременно в вашем часовом поясе и в часовом поясе врача.</p>
                    </div>
                    <span class="patient-booking-duration">
                        <i class="bi bi-clock"></i> {{ $durationMinutes }} минут
                    </span>
                </div>

                <div class="patient-booking-controls">
                    <div class="patient-booking-field patient-clinic-search">
                        <label for="bookingClinicSearch">Клиника</label>
                        <input
                            type="search"
                            id="bookingClinicSearch"
                            class="patient-clinic-search__input"
                            placeholder="Название клиники, город или адрес"
                            autocomplete="off"
                            minlength="2"
                            aria-controls="bookingClinicSearchResults"
                            aria-autocomplete="list"
                        >
                        <input type="hidden" id="bookingClinic" value="{{ $selectedClinicId ?: '' }}">

                        <div
                            class="patient-clinic-selected"
                            id="bookingClinicSelected"
                            @if(!$clinic) hidden @endif
                        >
                            <div>
                                <strong id="bookingClinicSelectedName">{{ $clinic?->name ?? '' }}</strong>
                                <small id="bookingClinicSelectedAddress">
                                    @if($clinic)
                                        {{ $clinic->city }}{{ $clinic->city && $clinic->address ? ' · ' : '' }}{{ $clinic->address }}
                                    @endif
                                </small>
                            </div>
                            <span><i class="bi bi-check-circle-fill"></i> Выбрана</span>
                        </div>

                        <div
                            class="patient-clinic-search__results"
                            id="bookingClinicSearchResults"
                            role="listbox"
                            hidden
                        ></div>
                        <small class="patient-clinic-search__hint">
                            Поиск по всем амбулаторным клиникам. Стационары не отображаются.
                        </small>
                    </div>

                    <label class="patient-booking-field">
                        <span>Врач</span>
                        <select id="bookingDoctor" @disabled(!$clinic || empty($doctors))>
                            @forelse($doctors as $doctor)
                                <option
                                    value="{{ $doctor['id'] }}"
                                    data-formats="{{ implode(',', $doctor['formats']) }}"
                                    @selected((int) $doctor['id'] === (int) $selectedDoctorId)
                                >
                                    {{ $doctor['name'] }} — {{ $doctor['speciality'] }}{{ $doctor['assigned'] ? ' · лечащий врач' : '' }}
                                </option>
                            @empty
                                <option value="">
                                    {{ $clinic ? 'Нет врачей для онлайн-записи' : 'Сначала выберите клинику' }}
                                </option>
                            @endforelse
                        </select>
                    </label>

                    <div class="patient-booking-field">
                        <span>Формат приёма</span>
                        <div class="patient-booking-format-switch" id="bookingFormatSwitch">
                            <button type="button" data-format="in_person" class="{{ $selectedFormat === 'in_person' ? 'is-active' : '' }}" @disabled(!in_array('in_person', $selectedFormats, true))>
                                <i class="bi bi-hospital"></i> Очный приём
                            </button>
                            <button type="button" data-format="telemedicine" class="{{ $selectedFormat === 'telemedicine' ? 'is-active' : '' }}" @disabled(!in_array('telemedicine', $selectedFormats, true))>
                                <i class="bi bi-camera-video"></i> Телемедицина
                            </button>
                        </div>
                    </div>

                    <label class="patient-booking-field patient-booking-date-field">
                        <span>Дата внутри нужной недели</span>
                        <input type="date" id="bookingFromDate" value="{{ $todayDate }}" min="{{ $todayDate }}">
                    </label>
                </div>

                <div class="patient-booking-timezone-legend">
                    <span><i class="bi bi-person"></i> Крупное время — ваше</span>
                    <span><i class="bi bi-clock-history"></i> Мелкое время — врача</span>
                </div>

                <div id="bookingMonthlyQuota" class="patient-booking-monthly-quota" aria-live="polite">
                    <span class="patient-booking-spinner"></span>
                    Проверяем месячный лимит записей…
                </div>

                <div class="patient-booking-week-nav">
                    <div>
                        <strong id="bookingWeekLabel">Понедельник — воскресенье</strong>
                        <span id="bookingTimezoneLabel">Определяем часовые пояса…</span>
                    </div>
                    <div>
                        <button type="button" id="bookingPrevWeek" aria-label="Предыдущие 7 дней"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" id="bookingNextWeek" aria-label="Следующие 7 дней"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>

                <div id="bookingAvailabilityState" class="patient-booking-loading">
                    <span class="patient-booking-spinner"></span> Загружаем свободное время…
                </div>
                <div id="bookingWeekGrid" class="patient-booking-week" aria-live="polite"></div>

                <div class="patient-booking-selection" id="bookingSelection" hidden>
                    <div class="patient-booking-selection__icon"><i class="bi bi-calendar2-check"></i></div>
                    <div class="patient-booking-selection__body">
                        <strong>Вы выбрали время</strong>
                        <span id="bookingSelectionText"></span>
                    </div>
                    <form method="POST" action="{{ route('patients.calendar.store') }}" id="bookingConfirmForm">
                        @csrf
                        <input type="hidden" name="clinic_id" id="bookingSelectedClinic" value="{{ $selectedClinicId }}">
                        <input type="hidden" name="doctor_id" id="bookingSelectedDoctor" value="{{ $selectedDoctorId }}">
                        <input type="hidden" name="appointment_format" id="bookingSelectedFormat" value="{{ $selectedFormat }}">
                        <input type="hidden" name="appointment_datetime" id="bookingSelectedDatetime">
                        <button type="submit" class="patient-booking-primary" id="bookingSubmit">
                            Записаться <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </section>

            <aside class="patient-booking-aside">
                <section class="patient-booking-card patient-booking-info">
                    <span class="patient-eyebrow">ВАШ ВЫБОР</span>
                    <h2>Как проходит запись</h2>
                    <ol>
                        <li><span>1</span>Выберите клинику, врача и формат консультации.</li>
                        <li><span>2</span>Найдите свободное время в сетке.</li>
                        <li><span>3</span>Подтвердите запись — она появится в вашем календаре.</li>
                    </ol>
                    <div class="patient-booking-info__note">
                        <i class="bi bi-shield-check"></i>
                        Занятость врача и пациента проверяется перед сохранением записи.
                    </div>
                </section>

                <section class="patient-booking-card patient-booking-upcoming">
                    <div class="patient-booking-card__header">
                        <div>
                            <span class="patient-eyebrow">ВАШЕ РАСПИСАНИЕ</span>
                            <h2>Предстоящие приёмы</h2>
                        </div>
                        <span class="patient-booking-count">{{ count($upcomingAppointments) }}</span>
                    </div>

                    @forelse($upcomingAppointments as $appointment)
                        <article class="patient-booking-upcoming__item">
                            <div class="patient-booking-upcoming__date">
                                @if(!empty($appointment['starts_at_utc']))
                                    <strong data-patient-date data-utc="{{ $appointment['starts_at_utc'] }}">—</strong>
                                    <span data-patient-time data-utc="{{ $appointment['starts_at_utc'] }}">—</span>
                                    <small>Вы</small>
                                @else
                                    <strong>—</strong>
                                    <span>Часовой пояс не подтверждён</span>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $appointment['doctor'] }}</strong>
                                <span>{{ $appointment['clinic'] }} · {{ $appointment['format'] }}</span>
                                <small class="patient-booking-upcoming__doctor-time">
                                    Время врача: {{ $appointment['date'] }} · {{ $appointment['time'] }}
                                    ({{ $appointment['doctor_timezone'] }})
                                </small>
                            </div>
                        </article>
                    @empty
                        <div class="patient-booking-upcoming__empty">
                            <i class="bi bi-calendar2"></i>
                            <p>Предстоящих приёмов пока нет.</p>
                        </div>
                    @endforelse
                </section>
            </aside>
        </div>
    @endif
</div>
@endsection

@if(!$blockedReason)
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clinicSelect = document.getElementById('bookingClinic');
    const clinicSearchInput = document.getElementById('bookingClinicSearch');
    const clinicSearchResults = document.getElementById('bookingClinicSearchResults');
    const clinicSelectedCard = document.getElementById('bookingClinicSelected');
    const doctorSelect = document.getElementById('bookingDoctor');
    const formatSwitch = document.getElementById('bookingFormatSwitch');
    const dateInput = document.getElementById('bookingFromDate');
    const weekGrid = document.getElementById('bookingWeekGrid');
    const state = document.getElementById('bookingAvailabilityState');
    const weekLabel = document.getElementById('bookingWeekLabel');
    const timezoneLabel = document.getElementById('bookingTimezoneLabel');
    const monthlyQuota = document.getElementById('bookingMonthlyQuota');
    const selection = document.getElementById('bookingSelection');
    const selectionText = document.getElementById('bookingSelectionText');
    const selectedClinicInput = document.getElementById('bookingSelectedClinic');
    const selectedDoctorInput = document.getElementById('bookingSelectedDoctor');
    const selectedFormatInput = document.getElementById('bookingSelectedFormat');
    const selectedDatetimeInput = document.getElementById('bookingSelectedDatetime');
    const availabilityUrl = @json(route('patients.calendar.availability'));
    const clinicSearchUrl = @json(route('patients.calendar.clinics.search'));
    const durationMinutes = @json($durationMinutes);
    const patientTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    let activeFormat = @json($selectedFormat);
    let currentZones = {
        patient_timezone: patientTimezone,
        doctor_timezone: ''
    };
    let requestNumber = 0;

    function localDateValue(date) {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: patientTimezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).formatToParts(date);

        const values = {};
        parts.forEach((part) => {
            if (part.type !== 'literal') values[part.type] = part.value;
        });

        return values.year + '-' + values.month + '-' + values.day;
    }

    function selectedDoctorOption() {
        return doctorSelect.options[doctorSelect.selectedIndex] || null;
    }

    function allowedFormats() {
        const option = selectedDoctorOption();
        return option ? (option.dataset.formats || '').split(',').filter(Boolean) : [];
    }

    function updateFormatButtons() {
        const allowed = allowedFormats();

        formatSwitch.querySelectorAll('[data-format]').forEach((button) => {
            const enabled = allowed.includes(button.dataset.format);
            button.disabled = !enabled;
            button.classList.toggle('is-active', enabled && button.dataset.format === activeFormat);
            button.setAttribute('aria-pressed', enabled && button.dataset.format === activeFormat ? 'true' : 'false');
        });

        if (!allowed.includes(activeFormat)) {
            activeFormat = allowed[0] || '';
        }

        selectedFormatInput.value = activeFormat;
    }

    function clearSelection() {
        selection.hidden = true;
        selectedDatetimeInput.value = '';
        weekGrid.querySelectorAll('.patient-booking-slot.is-selected').forEach((button) => {
            button.classList.remove('is-selected');
        });
    }

    function dateLabel(value) {
        return new Intl.DateTimeFormat('ru-RU', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            timeZone: 'UTC'
        }).format(new Date(value + 'T00:00:00Z'));
    }

    function shortDateLabel(value) {
        return new Intl.DateTimeFormat('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            timeZone: 'UTC'
        }).format(new Date(value + 'T00:00:00Z'));
    }

    function updateUpcomingTimes() {
        document.querySelectorAll('[data-patient-date][data-utc]').forEach((element) => {
            const instant = new Date(element.dataset.utc);
            if (Number.isNaN(instant.getTime())) return;

            element.textContent = new Intl.DateTimeFormat('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                timeZone: patientTimezone
            }).format(instant);
        });

        document.querySelectorAll('[data-patient-time][data-utc]').forEach((element) => {
            const instant = new Date(element.dataset.utc);
            if (Number.isNaN(instant.getTime())) return;

            element.textContent = new Intl.DateTimeFormat('ru-RU', {
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23',
                timeZone: patientTimezone
            }).format(instant);
        });
    }

    function updateWeekNavigation() {
        const previousButton = document.getElementById('bookingPrevWeek');
        const date = new Date(dateInput.value + 'T00:00:00');
        date.setDate(date.getDate() - 7);
        const previousValue = [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');

        previousButton.disabled = previousValue < dateInput.min;
    }

    function renderMonthlyLimits(limits) {
        monthlyQuota.replaceChildren();

        if (!limits || !limits.length) {
            monthlyQuota.hidden = true;
            return;
        }

        monthlyQuota.hidden = false;

        limits.forEach((item) => {
            const line = document.createElement('div');
            line.className = 'patient-booking-monthly-quota__line'
                + (item.remaining <= 0 ? ' is-full' : '');

            const icon = document.createElement('i');
            icon.className = item.remaining <= 0
                ? 'bi bi-exclamation-circle'
                : 'bi bi-calendar-check';

            const text = document.createElement('span');
            text.textContent = item.month_label + ': использовано '
                + item.used + ' из ' + item.limit
                + ' · осталось ' + item.remaining;

            line.append(icon, text);
            monthlyQuota.appendChild(line);
        });
    }

    function renderDays(days, zones) {
        weekGrid.replaceChildren();

        if (!days.length) {
            const empty = document.createElement('div');
            empty.className = 'patient-booking-empty';
            empty.textContent = 'Нет доступного расписания для выбранной недели.';
            weekGrid.appendChild(empty);
            return;
        }

        const sameTimezone = zones.patient_timezone === zones.doctor_timezone;

        days.forEach((day) => {
            const card = document.createElement('section');
            card.className = 'patient-booking-day';

            const header = document.createElement('header');
            header.className = 'patient-booking-day__header';

            const weekday = document.createElement('span');
            weekday.textContent = day.weekday || '';

            const date = document.createElement('strong');
            date.textContent = dateLabel(day.date);

            header.append(weekday, date);
            card.appendChild(header);

            if (day.intervals && day.intervals.length) {
                const hours = document.createElement('div');
                hours.className = 'patient-booking-day__hours';

                const lines = [];
                day.intervals.forEach((item) => {
                    if (sameTimezone) {
                        lines.push('График: ' + item.patient_label);
                    } else {
                        lines.push('Вы: ' + item.patient_label);
                        lines.push('Врач: ' + item.doctor_label);
                    }
                });

                hours.textContent = lines.join('\n');
                card.appendChild(hours);
            }

            const slots = document.createElement('div');
            slots.className = 'patient-booking-day__slots';

            (day.slots || []).forEach((slot) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'patient-booking-slot';

                const patientTime = document.createElement('span');
                patientTime.className = 'patient-booking-slot__patient-time';
                patientTime.textContent = slot.patient_time;

                const doctorTime = document.createElement('small');
                doctorTime.className = 'patient-booking-slot__doctor-time';

                if (sameTimezone) {
                    doctorTime.textContent = 'Вы и врач';
                } else {
                    const doctorDateSuffix = slot.doctor_date !== slot.patient_date
                        ? ' · ' + shortDateLabel(slot.doctor_date)
                        : '';
                    doctorTime.textContent = 'Врач ' + slot.doctor_time + doctorDateSuffix;
                }

                button.append(patientTime, doctorTime);
                button.title = sameTimezone
                    ? 'Ваше и время врача: ' + slot.patient_time + ' (' + zones.patient_timezone + ')'
                    : 'Ваше время: ' + slot.patient_time + ' (' + zones.patient_timezone + '); '
                        + 'время врача: ' + slot.doctor_time + ' (' + zones.doctor_timezone + ')';

                button.addEventListener('click', () => {
                    clearSelection();
                    button.classList.add('is-selected');

                    const doctorOption = selectedDoctorOption();
                    const doctorName = doctorOption
                        ? doctorOption.textContent.split(' — ')[0].trim()
                        : 'Выбранный врач';
                    const formatLabel = activeFormat === 'telemedicine'
                        ? 'Телемедицина'
                        : 'Очный приём';

                    selectedClinicInput.value = clinicSelect.value;
                    selectedDoctorInput.value = doctorSelect.value;
                    selectedFormatInput.value = activeFormat;

                    // Store the canonical clinic-local datetime. The server resolves
                    // it to one absolute instant; the two displayed clocks are only views.
                    selectedDatetimeInput.value = slot.clinic_datetime;

                    const timeSummary = sameTimezone
                        ? 'Ваше и время врача: ' + dateLabel(slot.patient_date) + ' ' + slot.patient_time
                            + ' (' + zones.patient_timezone + ')'
                        : 'Ваше время: ' + dateLabel(slot.patient_date) + ' ' + slot.patient_time
                            + ' (' + zones.patient_timezone + ') · '
                            + 'время врача: ' + dateLabel(slot.doctor_date) + ' ' + slot.doctor_time
                            + ' (' + zones.doctor_timezone + ')';

                    const clinicName = document.getElementById('bookingClinicSelectedName')
                        ? document.getElementById('bookingClinicSelectedName').textContent.trim()
                        : 'Клиника';

                    selectionText.textContent = clinicName + ' · ' + doctorName
                        + ' · ' + timeSummary
                        + ' · ' + formatLabel + ' · ' + durationMinutes + ' мин.';
                    selection.hidden = false;
                });

                slots.appendChild(button);
            });

            if (!(day.slots || []).length) {
                const empty = document.createElement('p');
                empty.className = 'patient-booking-day__empty';
                empty.textContent = day.message || 'Свободного времени нет.';
                slots.appendChild(empty);
            }

            card.appendChild(slots);
            weekGrid.appendChild(card);
        });
    }

    let clinicSearchTimer = null;
    let clinicSearchAbort = null;
    let clinicSearchPage = 1;
    let clinicSearchTerm = '';

    function selectClinic(clinic) {
        const url = new URL(window.location.href);
        url.searchParams.set('clinic_id', String(clinic.id));
        window.location.assign(url.toString());
    }

    function appendClinicSearchGroups(data, append) {
        if (!append) {
            clinicSearchResults.replaceChildren();
        }

        const existingGroups = Array.from(
            clinicSearchResults.querySelectorAll('[data-clinic-group]')
        );

        (data.groups || []).forEach((group) => {
            let groupNode = existingGroups.find(
                (node) => node.dataset.clinicGroup === String(group.id)
            );

            if (!groupNode) {
                groupNode = document.createElement('section');
                groupNode.className = 'patient-clinic-search__group';
                groupNode.dataset.clinicGroup = String(group.id);

                const heading = document.createElement('div');
                heading.className = 'patient-clinic-search__group-title';
                heading.textContent = group.name;

                const items = document.createElement('div');
                items.className = 'patient-clinic-search__group-items';

                groupNode.append(heading, items);
                clinicSearchResults.appendChild(groupNode);
            }

            const itemsNode = groupNode.querySelector(
                '.patient-clinic-search__group-items'
            );

            (group.clinics || []).forEach((clinic) => {
                if (itemsNode.querySelector('[data-clinic-id="' + clinic.id + '"]')) {
                    return;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'patient-clinic-search__result';
                button.dataset.clinicId = String(clinic.id);
                button.setAttribute('role', 'option');

                const name = document.createElement('strong');
                name.textContent = clinic.name;

                const detail = document.createElement('small');
                detail.textContent = [clinic.city, clinic.address]
                    .filter(Boolean)
                    .join(' · ');

                button.append(name, detail);
                button.addEventListener('click', () => selectClinic(clinic));
                itemsNode.appendChild(button);
            });
        });

        const oldMore = clinicSearchResults.querySelector(
            '.patient-clinic-search__more'
        );
        if (oldMore) oldMore.remove();

        if (data.has_more) {
            const more = document.createElement('button');
            more.type = 'button';
            more.className = 'patient-clinic-search__more';
            more.textContent = 'Показать ещё клиники';
            more.addEventListener('click', () => {
                more.disabled = true;
                more.textContent = 'Загружаем…';
                searchClinics(clinicSearchPage + 1, true);
            });
            clinicSearchResults.appendChild(more);
        }

        if (!clinicSearchResults.querySelector('[data-clinic-group]') && !append) {
            const empty = document.createElement('div');
            empty.className = 'patient-clinic-search__empty';
            empty.textContent = 'Амбулаторные клиники по вашему запросу не найдены.';
            clinicSearchResults.appendChild(empty);
        }

        clinicSearchResults.hidden = false;
    }

    async function searchClinics(page, append) {
        if (clinicSearchAbort) {
            clinicSearchAbort.abort();
        }

        clinicSearchAbort = new AbortController();

        const params = new URLSearchParams({
            q: clinicSearchTerm,
            page: String(page)
        });

        try {
            const response = await fetch(clinicSearchUrl + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                signal: clinicSearchAbort.signal
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Не удалось выполнить поиск клиник.');
            }

            clinicSearchPage = Number(data.page || page);
            appendClinicSearchGroups(data, append);
        } catch (error) {
            if (error.name === 'AbortError') return;

            clinicSearchResults.replaceChildren();
            const message = document.createElement('div');
            message.className = 'patient-clinic-search__empty';
            message.textContent = error.message || 'Ошибка поиска клиник.';
            clinicSearchResults.appendChild(message);
            clinicSearchResults.hidden = false;
        }
    }

    function scheduleClinicSearch() {
        window.clearTimeout(clinicSearchTimer);

        if (clinicSearchAbort) {
            clinicSearchAbort.abort();
        }

        const term = clinicSearchInput.value.trim();
        if (term.length < 2) {
            if (clinicSearchAbort) clinicSearchAbort.abort();
            clinicSearchResults.replaceChildren();
            clinicSearchResults.hidden = true;
            return;
        }

        clinicSearchTerm = term;
        clinicSearchPage = 1;
        clinicSearchResults.replaceChildren();

        const loading = document.createElement('div');
        loading.className = 'patient-clinic-search__empty';
        loading.textContent = 'Ищем амбулаторные клиники…';
        clinicSearchResults.appendChild(loading);
        clinicSearchResults.hidden = false;

        clinicSearchTimer = window.setTimeout(() => {
            searchClinics(1, false);
        }, 250);
    }

    async function loadAvailability() {
        clearSelection();
        updateFormatButtons();
        updateWeekNavigation();

        if (!clinicSelect.value) {
            state.hidden = false;
            state.textContent = 'Сначала найдите и выберите амбулаторную клинику.';
            monthlyQuota.hidden = true;
            weekGrid.replaceChildren();
            return;
        }

        if (!doctorSelect.value || !activeFormat || !dateInput.value) {
            state.hidden = false;
            state.textContent = 'В выбранной клинике нет доступного врача для онлайн-записи или не выбран формат приёма.';
            weekGrid.replaceChildren();
            return;
        }

        const currentRequest = ++requestNumber;
        state.hidden = false;
        state.textContent = 'Загружаем полную неделю…';
        weekGrid.replaceChildren();

        const params = new URLSearchParams({
            clinic_id: clinicSelect.value,
            doctor_id: doctorSelect.value,
            from_date: dateInput.value,
            appointment_format: activeFormat,
            patient_timezone: patientTimezone
        });

        try {
            const response = await fetch(availabilityUrl + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (currentRequest !== requestNumber) return;
            if (!response.ok) {
                throw new Error(data.message || 'Не удалось загрузить расписание.');
            }

            currentZones = {
                patient_timezone: data.patient_timezone,
                doctor_timezone: data.doctor_timezone
            };

            timezoneLabel.textContent = 'Вы: ' + data.patient_timezone
                + ' · Врач: ' + data.doctor_timezone;

            renderMonthlyLimits(data.monthly_limits || []);

            weekLabel.textContent = data.days && data.days.length
                ? dateLabel(data.days[0].date) + ' — ' + dateLabel(data.days[data.days.length - 1].date)
                : 'Понедельник — воскресенье';

            renderDays(data.days || [], currentZones);
            state.hidden = true;
            updateWeekNavigation();
        } catch (error) {
            if (currentRequest !== requestNumber) return;
            state.hidden = false;
            state.textContent = error.message || 'Не удалось загрузить расписание. Попробуйте ещё раз.';
            monthlyQuota.hidden = true;
            weekGrid.replaceChildren();
        }
    }

    clinicSearchInput.addEventListener('input', scheduleClinicSearch);

    clinicSearchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            clinicSearchResults.hidden = true;
            clinicSearchInput.blur();
        }
    });

    document.addEventListener('click', (event) => {
        if (
            !event.target.closest('.patient-clinic-search')
            && !event.target.closest('#bookingClinicSearchResults')
        ) {
            clinicSearchResults.hidden = true;
        }
    });

    doctorSelect.addEventListener('change', loadAvailability);

    formatSwitch.querySelectorAll('[data-format]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            activeFormat = button.dataset.format;
            loadAvailability();
        });
    });

    dateInput.addEventListener('change', loadAvailability);

    document.getElementById('bookingPrevWeek').addEventListener('click', () => {
        const date = new Date(dateInput.value + 'T00:00:00');
        date.setDate(date.getDate() - 7);
        const value = [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');

        if (value >= dateInput.min) {
            dateInput.value = value;
            loadAvailability();
        }
    });

    document.getElementById('bookingNextWeek').addEventListener('click', () => {
        const date = new Date(dateInput.value + 'T00:00:00');
        date.setDate(date.getDate() + 7);
        dateInput.value = [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');
        loadAvailability();
    });

    // The patient's timezone is taken from the browser, not inferred from the clinic.
    const todayForPatient = localDateValue(new Date());
    dateInput.min = todayForPatient;
    dateInput.value = todayForPatient;

    updateUpcomingTimes();
    updateFormatButtons();
    loadAvailability();
});
</script>
@endsection
@endif
