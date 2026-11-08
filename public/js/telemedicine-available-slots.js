(function () {
    'use strict';

    const STORAGE_KEY = 'norhnaim.appointment-display-timezone.v1';

    function validZone(zone) {
        if (!zone || zone === 'auto') return false;

        try {
            new Intl.DateTimeFormat('en', {timeZone: zone}).format(0);
            return true;
        } catch (_) {
            return false;
        }
    }

    function viewerZone() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (validZone(saved)) return saved;
        } catch (_) {
        }

        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    }

    function viewerTime(iso) {
        const instant = Date.parse(iso);

        if (!Number.isFinite(instant)) return '';

        return new Intl.DateTimeFormat('ru-RU', {
            timeZone: viewerZone(),
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        }).format(new Date(instant));
    }

    function dayLabel(value) {
        const date = new Date(value + 'T12:00:00Z');

        if (!Number.isFinite(date.getTime())) return value;

        const weekday = new Intl.DateTimeFormat('ru-RU', {
            weekday: 'short',
            timeZone: 'UTC'
        }).format(date).replace('.', '');

        const day = new Intl.DateTimeFormat('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            timeZone: 'UTC'
        }).format(date);

        return weekday + ', ' + day;
    }

    function initPicker(picker) {
        if (picker.dataset.tmSlotsReady === '1') return;
        picker.dataset.tmSlotsReady = '1';

        const form = picker.closest('form');
        if (!form) return;

        const doctor = form.querySelector('[name="telemed_doctor_id"]');
        const startsAt = form.querySelector('[name="telemed_starts_at"]');
        const duration = form.querySelector('[name="telemed_duration_minutes"]');
        const dateInput = picker.querySelector('[data-tm-slots-date]');
        const daysBox = picker.querySelector('[data-tm-slot-days]');
        const slotsBox = picker.querySelector('[data-tm-slot-list]');
        const state = picker.querySelector('[data-tm-slots-state]');
        const refresh = picker.querySelector('[data-tm-slots-refresh]');
        const doctorTimezone = picker.querySelector(
            '[data-tm-doctor-timezone]'
        );
        const doctorTimezoneHint = picker.querySelector(
            '[data-tm-doctor-timezone-hint]'
        );
        const timezoneUrlTemplate =
            picker.dataset.doctorTimezoneUrlTemplate || '';

        if (!doctor || !startsAt || !duration || !dateInput) return;

        const modal = picker.closest('.modal');
        const modalBody = picker.closest('.modal-body');
        const modalDialog = modal?.querySelector('.modal-dialog');

        modalDialog?.classList.add('tm-booking-dialog-wide');

        if (modalBody) {
            modalBody.classList.add('tm-booking-body-grid');

            Array.from(modalBody.children).forEach(child => {
                if (child === picker) {
                    child.classList.add('tm-booking-sidebar');
                } else {
                    child.classList.add('tm-booking-main');
                }
            });
        }


        function timezoneOptions() {
            if (typeof Intl.supportedValuesOf === 'function') {
                try {
                    return Intl.supportedValuesOf('timeZone');
                } catch (_) {
                }
            }

            return [
                'Europe/Kaliningrad',
                'Europe/Moscow',
                'Europe/Samara',
                'Asia/Yekaterinburg',
                'Asia/Omsk',
                'Asia/Novosibirsk',
                'Asia/Tomsk',
                'Asia/Krasnoyarsk',
                'Asia/Irkutsk',
                'Asia/Yakutsk',
                'Asia/Vladivostok',
                'Asia/Magadan',
                'Asia/Kamchatka',
                'Europe/Tallinn',
                'UTC'
            ];
        }

        function syncInputZone(timezone) {
            const zoneBox = form.querySelector(
                '[data-appointment-input-zone]'
            );

            if (zoneBox && timezone) {
                zoneBox.dataset.clinicTimezone = timezone;
            }

            const hint = Array.from(
                form.querySelectorAll('.form-text')
            ).find(node =>
                node.textContent.includes(
                    'Дата и время вводятся по часовому поясу'
                )
            );

            if (hint && timezone) {
                hint.innerHTML =
                    'Дата и время вводятся по часовому поясу врача: '
                    + '<strong>' + timezone + '</strong>.';
            }

            if (window.ClinicAppointmentTime?.render) {
                window.ClinicAppointmentTime.render();
            }
        }

        function renderDoctorTimezone(data) {
            if (!doctorTimezone) return;

            const resolved = String(
                data?.doctor_timezone
                || data?.timezone
                || ''
            );
            const clinic = String(
                data?.clinic_timezone || ''
            );
            const explicit =
                data?.doctor_timezone_explicit === true;

            const zones = timezoneOptions()
                .filter(Boolean)
                .sort((a, b) => a.localeCompare(b));

            doctorTimezone.innerHTML = '';

            const fallback = document.createElement('option');
            fallback.value = '';
            fallback.textContent =
                clinic
                    ? 'Как у клиники (' + clinic + ')'
                    : 'Как у клиники';
            doctorTimezone.appendChild(fallback);

            zones.forEach(zone => {
                const option = document.createElement('option');
                option.value = zone;
                option.textContent = zone;
                doctorTimezone.appendChild(option);
            });

            if (
                explicit
                && resolved
                && !Array.from(doctorTimezone.options)
                    .some(option => option.value === resolved)
            ) {
                const option = document.createElement('option');
                option.value = resolved;
                option.textContent = resolved;
                doctorTimezone.appendChild(option);
            }

            doctorTimezone.value = explicit ? resolved : '';

            if (doctorTimezoneHint) {
                doctorTimezoneHint.textContent = explicit
                    ? 'Рабочее время врача: ' + resolved + '.'
                    : 'Для врача отдельный пояс не задан; '
                        + 'сейчас используется пояс клиники: '
                        + (resolved || clinic || 'не указан') + '.';
            }

            syncInputZone(resolved);
        }

        async function saveDoctorTimezone() {
            if (
                !doctorTimezone
                || !timezoneUrlTemplate
                || !doctor.value
            ) {
                return;
            }

            const url = timezoneUrlTemplate.replace(
                '__DOCTOR__',
                encodeURIComponent(String(doctor.value))
            );

            const csrf = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content || '';

            doctorTimezone.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})
                    },
                    body: JSON.stringify({
                        timezone: doctorTimezone.value || null
                    })
                });

                const data = await response
                    .json()
                    .catch(() => ({}));

                if (!response.ok) {
                    const first = Object.values(
                        data.errors || {}
                    ).flat().find(Boolean);

                    throw new Error(
                        first
                        || data.message
                        || ('HTTP ' + response.status)
                    );
                }

                setState(
                    'Часовой пояс врача сохранён.',
                    'success'
                );
                await load();
            } catch (error) {
                setState(
                    error.message
                    || 'Не удалось сохранить часовой пояс врача.',
                    'danger'
                );
            } finally {
                doctorTimezone.disabled = false;
            }
        }

        let payload = null;
        let activeDate = '';
        let controller = null;
        let timer = null;

        function setState(message, tone = 'muted') {
            if (!state) return;

            state.className = 'small mt-2';
            state.classList.add(
                tone === 'danger'
                    ? 'text-danger'
                    : tone === 'success'
                        ? 'text-success'
                        : 'text-muted'
            );
            state.textContent = message || '';
        }

        function selectedLocal() {
            return String(startsAt.value || '').slice(0, 16);
        }

        function renderSlots(day) {
            slotsBox.innerHTML = '';

            if (!day) {
                setState('Выберите дату.');
                return;
            }

            activeDate = day.date;

            daysBox
                .querySelectorAll('[data-tm-slot-day]')
                .forEach(button => {
                    const active = button.dataset.tmSlotDay === activeDate;
                    button.classList.toggle('btn-primary', active);
                    button.classList.toggle('btn-outline-secondary', !active);
                });

            if (!Array.isArray(day.slots) || day.slots.length === 0) {
                const empty = document.createElement('div');
                empty.className =
                    'alert alert-light border py-2 px-3 mb-0 w-100';
                empty.textContent =
                    day.message || 'Свободных окон на этот день нет.';
                slotsBox.appendChild(empty);
                setState('');
                return;
            }

            const selected = selectedLocal();

            day.slots.forEach(slot => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className =
                    'btn btn-sm btn-outline-primary text-start';
                button.dataset.tmSlotValue = slot.local_value;

                if (selected === slot.local_value) {
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-primary');
                }

                const clinic = document.createElement('span');
                clinic.className = 'd-block fw-semibold';
                clinic.textContent = slot.time;

                const local = document.createElement('span');
                local.className = 'd-block small opacity-75';
                local.textContent =
                    viewerTime(slot.starts_at_utc) + ' ваше';

                button.append(clinic, local);

                button.addEventListener('click', function () {
                    startsAt.value = slot.local_value;
                    startsAt.dispatchEvent(
                        new Event('input', {bubbles: true})
                    );
                    startsAt.dispatchEvent(
                        new Event('change', {bubbles: true})
                    );

                    if (window.ClinicAppointmentTime?.render) {
                        window.ClinicAppointmentTime.render();
                    }

                    renderSlots(day);
                });

                slotsBox.appendChild(button);
            });

            setState(
                'Время крупно — время врача ('
                    + (payload?.doctor_timezone
                        || payload?.timezone
                        || 'часовой пояс не задан')
                    + '); строкой ниже — ваше время.'
            );
        }

        function renderDays() {
            daysBox.innerHTML = '';

            const days = Array.isArray(payload?.days)
                ? payload.days.filter(day =>
                    Array.isArray(day.intervals)
                    && day.intervals.length > 0
                )
                : [];

            if (days.length === 0) {
                slotsBox.innerHTML = '';
                setState(
                    'В ближайшие 7 дней у врача нет рабочих смен '
                    + 'в расписании.'
                );
                return;
            }

            days.forEach(day => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className =
                    'btn btn-sm btn-outline-secondary';
                button.dataset.tmSlotDay = day.date;

                button.textContent = dayLabel(day.date);

                button.addEventListener('click', function () {
                    renderSlots(day);
                });

                daysBox.appendChild(button);
            });

            const selectedDate =
                String(startsAt.value || '').slice(0, 10);

            const day =
                days.find(item => item.date === activeDate)
                || days.find(item => item.date === selectedDate)
                || days[0];

            renderSlots(day);
        }

        async function load() {
            const doctorId = Number(doctor.value || 0);
            const durationMinutes = Number(duration.value || 0);
            const date = String(dateInput.value || '').trim();

            if (!doctorId || !durationMinutes || !date) {
                daysBox.innerHTML = '';
                slotsBox.innerHTML = '';
                setState(
                    'Выберите врача, дату и длительность приёма.'
                );
                return;
            }

            controller?.abort();
            controller = new AbortController();
            const current = controller;

            daysBox.innerHTML = '';
            slotsBox.innerHTML = '';
            setState('Ищем свободные окна…');

            try {
                const url = new URL(
                    picker.dataset.slotsUrl,
                    window.location.origin
                );

                url.searchParams.set('doctor_id', String(doctorId));
                url.searchParams.set('date', date);
                url.searchParams.set(
                    'telemed_duration_minutes',
                    String(durationMinutes)
                );
                url.searchParams.set('days', '7');

                const response = await fetch(url.toString(), {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: current.signal
                });

                const data = await response
                    .json()
                    .catch(() => ({}));

                if (!response.ok) {
                    const first = Object.values(data.errors || {})
                        .flat()
                        .find(Boolean);

                    throw new Error(
                        first
                        || data.message
                        || ('HTTP ' + response.status)
                    );
                }

                if (controller !== current) return;

                payload = data;
                activeDate = date;
                renderDoctorTimezone(data);
                renderDays();
            } catch (error) {
                if (error.name === 'AbortError') return;

                payload = null;
                setState(
                    error.message
                    || 'Не удалось получить свободное время.',
                    'danger'
                );
            } finally {
                if (controller === current) {
                    controller = null;
                }
            }
        }

        function delayedLoad() {
            clearTimeout(timer);
            timer = setTimeout(load, 180);
        }

        function syncDate() {
            const value = String(startsAt.value || '');

            if (/^\d{4}-\d{2}-\d{2}T/.test(value)) {
                dateInput.value = value.slice(0, 10);
            }
        }

        doctor.addEventListener('change', delayedLoad);
        duration.addEventListener('input', delayedLoad);
        duration.addEventListener('change', delayedLoad);
        dateInput.addEventListener('change', load);
        refresh?.addEventListener('click', load);
        doctorTimezone?.addEventListener(
            'change',
            saveDoctorTimezone
        );

        startsAt.addEventListener('change', function () {
            syncDate();
            if (payload) renderDays();
        });

        picker.closest('.modal')?.addEventListener(
            'shown.bs.modal',
            function () {
                syncDate();
                load();
            }
        );

        syncDate();
    }

    function init() {
        document
            .querySelectorAll('[data-telemedicine-slot-picker]')
            .forEach(initPicker);
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            init,
            {once: true}
        );
    } else {
        init();
    }
})();
