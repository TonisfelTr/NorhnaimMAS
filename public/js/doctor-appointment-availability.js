(function (factory) {
    'use strict';

    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    if (typeof document !== 'undefined') {
        api.boot(document);
    }
})(function () {
    'use strict';

    const EMPTY = 'Выберите врача, дату и время приёма.';
    const DURATION_ERROR = 'Укажите длительность приёма от 5 до 480 минут с шагом 5 минут.';
    const NETWORK_ERROR = 'Не удалось проверить рабочий график. Повторите проверку.';
    const LOADING = 'Проверяем рабочее время врача…';
    const OUTSIDE = 'Врач не работает в выбранное время либо приём выходит за границы смены. Выберите другое время.';
    const PAST = 'Выберите время не раньше текущего времени клиники.';
    const TIME_FORMAT = 'Укажите дату и время приёма с точностью до минуты.';
    const FORMAT_ERROR = 'Выберите формат приёма: очный или телемедицина.';
    const CALENDAR_DRAFT = 'Завершите ввод даты и времени, чтобы проверить рабочий график.';
    const CALENDAR_SELECTION = 'Календарь не смог выбрать указанное время. Введите дату и время заново.';

    function parseLocalDateTime(value) {
        if (typeof value !== 'string') return null;
        const match = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2}))?$/.exec(value);
        if (!match) return null;

        const year = Number(match[1]);
        const month = Number(match[2]);
        const day = Number(match[3]);
        const hour = Number(match[4]);
        const minute = Number(match[5]);
        const second = Number(match[6] || 0);
        const leap = year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
        const monthDays = [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        if (year < 1000 || month < 1 || month > 12 || day < 1 || day > monthDays[month - 1]
            || hour > 23 || minute > 59 || second > 59) return null;

        return {
            date: value.slice(0, 10),
            time: value.slice(11, 16),
            normalized: value.slice(0, 16) + ':' + String(second).padStart(2, '0'),
            seconds: hour * 3600 + minute * 60 + second,
            // This is an ordering scale for clinic wall time, not a timezone conversion.
            ordinalSeconds: Date.UTC(year, month - 1, day, hour, minute, second) / 1000,
            second: second
        };
    }

    function parseDuration(value) {
        if (typeof value !== 'string' || !/^[1-9]\d{0,2}$/.test(value)) return null;
        const minutes = Number(value);
        return minutes >= 5 && minutes <= 480 && minutes % 5 === 0 ? minutes : null;
    }

    function parseAppointmentFormat(value) {
        return value === 'in_person' || value === 'telemedicine' ? value : null;
    }

    function originalAppointmentFormat(value) {
        if (value === undefined || value === null || value === '') return 'in_person';
        return typeof value === 'string' ? parseAppointmentFormat(value.trim() || 'in_person') : null;
    }

    function formAppointmentFormat(form) {
        const fields = Array.from(form.querySelectorAll('[name="appointment_format"]'));
        if (!fields.length) return 'in_person';

        const submitted = fields.filter(function (field) {
            return !field.disabled
                && !(typeof field.matches === 'function' && field.matches(':disabled'))
                && (field.type !== 'radio' || field.checked);
        });
        if (!submitted.length) return null;
        // For a scalar PHP field, the last successful control wins, including
        // the common hidden fallback followed by a checked radio/select.
        const field = submitted[submitted.length - 1];
        if (field.multiple) return null;
        return parseAppointmentFormat(field.value);
    }

    function timeToMinutes(value, allowEndOfDay) {
        if (allowEndOfDay && value === '24:00') return 1440;
        if (typeof value !== 'string' || !/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(value)) return null;
        return Number(value.slice(0, 2)) * 60 + Number(value.slice(3, 5));
    }

    function formatMinutes(value) {
        return String(Math.floor(value / 60)).padStart(2, '0')
            + ':' + String(value % 60).padStart(2, '0');
    }

    function fitsIntervals(time, duration, intervals) {
        const start = timeToMinutes(time, false);
        if (start === null || !Number.isInteger(duration) || duration <= 0) return false;
        return intervals.some(function (interval) {
            const left = timeToMinutes(interval.start, false);
            const right = timeToMinutes(interval.end, true);
            return left !== null && right !== null && left <= start && start + duration <= right;
        });
    }

    function parsePayload(payload, expectedDate, expectedDuration, expectedFormat = 'in_person') {
        if (!payload || typeof payload !== 'object' || Array.isArray(payload)
            || payload.date !== expectedDate || payload.duration_minutes !== expectedDuration
            || typeof payload.timezone !== 'string' || !payload.timezone.trim()
            || !Array.isArray(payload.intervals) || !Array.isArray(payload.slots)
            || payload.intervals.length > 500 || payload.slots.length > 1440) {
            throw new Error('Invalid availability response');
        }

        // An old response without a format describes in-person availability.
        // Telemedicine must be acknowledged explicitly by the server.
        const responseFormat = payload.appointment_format === undefined
            ? 'in_person' : parseAppointmentFormat(payload.appointment_format);
        if (!parseAppointmentFormat(expectedFormat) || responseFormat !== expectedFormat) {
            throw new Error('Availability response has a different appointment format');
        }

        const intervals = payload.intervals.map(function (interval) {
            if (!interval || typeof interval !== 'object') throw new Error('Invalid interval');
            const start = timeToMinutes(interval.start, false);
            const end = timeToMinutes(interval.end, true);
            if (start === null || end === null || start >= end) throw new Error('Invalid interval');
            return {start: interval.start, end: interval.end};
        });

        const slots = Array.from(new Set(payload.slots.map(function (time) {
            if (timeToMinutes(time, false) === null || !fitsIntervals(time, expectedDuration, intervals)) {
                throw new Error('Invalid start time');
            }
            return time;
        }))).sort();

        const now = payload.now === undefined || payload.now === null
            ? null : parseLocalDateTime(payload.now);
        if (payload.now !== undefined && payload.now !== null && !now) {
            throw new Error('Invalid clinic time');
        }

        return {
            date: expectedDate,
            duration_minutes: expectedDuration,
            appointment_format: responseFormat,
            timezone: payload.timezone.slice(0, 100),
            intervals: intervals,
            slots: slots,
            now: now,
            message: typeof payload.message === 'string' ? payload.message.slice(0, 800) : ''
        };
    }

    function sameSchedule(current, original) {
        const value = parseLocalDateTime(current.datetime);
        const saved = parseLocalDateTime(original.datetime);
        const format = parseAppointmentFormat(current.appointmentFormat === undefined
            ? 'in_person' : current.appointmentFormat);
        if (!/^[1-9]\d*$/.test(original.recordId) || !value || !saved || value.second !== 0) return false;
        return current.doctorId === original.doctorId
            && value.normalized === saved.normalized
            && current.duration === original.duration
            && format !== null
            && format === originalAppointmentFormat(original.appointmentFormat);
    }

    function init(container) {
        if (container.dataset.availabilityInitialized === '1') return null;
        const form = container.closest('form');
        if (!form) return null;

        const document = container.ownerDocument;
        const window = document.defaultView;
        const appointment = form.querySelector('[name="appointment_datetime"]');
        const doctor = form.querySelector('[name="doctor_id"]');
        const duration = container.querySelector('[data-availability-duration]');
        const choices = container.querySelector('[data-availability-slots]');
        const status = container.querySelector('[data-availability-message]');
        const hours = container.querySelector('[data-availability-hours]');
        const zone = container.querySelector('[data-availability-timezone]');
        const retry = container.querySelector('[data-availability-retry]');

        if (!appointment || !doctor || !duration || !choices || !status || !hours || !zone || !retry) {
            return null;
        }
        container.dataset.availabilityInitialized = '1';

        const original = {
            recordId: container.dataset.availabilityRecordId || '',
            doctorId: container.dataset.availabilityOriginalDoctor || '',
            datetime: container.dataset.availabilityOriginalDatetime || '',
            duration: container.dataset.availabilityOriginalDuration || '',
            appointmentFormat: container.dataset.availabilityOriginalFormat
        };
        const clock = function () { return window.performance.now(); };
        let generation = 0;
        let controller = null;
        let state = {phase: 'idle', key: '', data: null, receivedAt: 0};
        let validationMessage = EMPTY;
        const ownedValidity = new Map();
        const originalMinimum = appointment.getAttribute('min') || '';
        let minimumOwned = false;
        let appliedNativeMinimum = '';
        let minimumPicker = null;
        let originalPickerMinimum = '';
        let appliedPickerMinimum = '';
        let disposed = false;

        const describedBy = new Set((appointment.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
        if (status.id) describedBy.add(status.id);
        appointment.setAttribute('aria-describedby', Array.from(describedBy).join(' '));

        function calendar() {
            const picker = appointment._receptionCalendar;
            return picker && picker.altInput ? picker : null;
        }

        function visibleAppointment() {
            const picker = calendar();
            return picker ? picker.altInput : appointment;
        }

        function dateMinimumString(date) {
            if (!date || typeof date.getFullYear !== 'function' || Number.isNaN(date.getTime())) return '';
            return String(date.getFullYear()).padStart(4, '0') + '-'
                + String(date.getMonth() + 1).padStart(2, '0') + '-'
                + String(date.getDate()).padStart(2, '0') + 'T'
                + String(date.getHours()).padStart(2, '0') + ':'
                + String(date.getMinutes()).padStart(2, '0');
        }

        function applyMinimum(minimum) {
            const picker = calendar();
            if (picker && picker !== minimumPicker) {
                minimumPicker = picker;
                const existing = dateMinimumString(picker.minDate);
                originalPickerMinimum = minimumOwned && existing === appliedNativeMinimum
                    ? originalMinimum : existing;
                appliedPickerMinimum = existing;
            }

            if (minimum !== null) {
                const effective = originalMinimum > minimum ? originalMinimum : minimum;
                appointment.setAttribute('min', effective);
                minimumOwned = true;
                appliedNativeMinimum = effective;
            } else if (minimumOwned) {
                if (originalMinimum) appointment.setAttribute('min', originalMinimum);
                else appointment.removeAttribute('min');
                minimumOwned = false;
                appliedNativeMinimum = '';
            }

            if (picker && typeof picker.set === 'function') {
                const effective = minimum === null ? originalPickerMinimum
                    : originalPickerMinimum > minimum ? originalPickerMinimum : minimum;
                if (effective !== appliedPickerMinimum) {
                    picker.set('minDate', effective || null);
                    appliedPickerMinimum = effective;
                }
            }
        }

        function clinicMinimum() {
            const seconds = latestClinicNow();
            if (seconds === null) return null;
            const at = new Date(Math.ceil(seconds / 60) * 60000);
            return at.toISOString().slice(0, 16);
        }

        function inputs() {
            return {
                doctorId: String(doctor.value || '').trim(),
                datetime: appointment.value,
                duration: duration.value,
                appointmentFormat: formAppointmentFormat(form)
            };
        }

        function unchanged() {
            const confirmation = form.querySelector('[data-appointment-time-confirm]');
            return !(confirmation && confirmation.checked) && !duration.validity.badInput && sameSchedule(inputs(), original);
        }

        function keyFor(current, parsed, minutes) {
            return current.doctorId + '|' + parsed.date + '|' + minutes + '|'
                + current.appointmentFormat + '|' + original.recordId;
        }

        function ownValidity(message) {
            validationMessage = message;
            const fields = Array.from(new Set([appointment, visibleAppointment()]));
            fields.forEach(function (field) {
                const ids = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
                if (status.id) ids.add(status.id);
                field.setAttribute('aria-describedby', Array.from(ids).join(' '));
                if (message) {
                    field.setCustomValidity(message);
                    ownedValidity.set(field, message);
                    field.setAttribute('aria-invalid', 'true');
                } else if (ownedValidity.has(field)) {
                    // A hidden canonical input has no visible validationMessage.
                    if (field.validationMessage === ownedValidity.get(field) || field.type === 'hidden') {
                        field.setCustomValidity('');
                        field.removeAttribute('aria-invalid');
                    }
                    ownedValidity.delete(field);
                }
            });
        }

        function show(message, kind) {
            status.textContent = message;
            status.classList.remove('text-danger', 'text-success', 'text-muted');
            status.classList.add(kind === 'error' ? 'text-danger' : kind === 'success' ? 'text-success' : 'text-muted');
        }

        function resetChoices(message) {
            choices.replaceChildren();
            const option = document.createElement('option');
            option.value = '';
            option.textContent = message;
            choices.append(option);
            choices.disabled = true;
            hours.textContent = '';
            zone.textContent = '';
        }

        function stopPending() {
            generation += 1;
            if (controller) controller.abort();
            controller = null;
            container.setAttribute('aria-busy', 'false');
        }

        function latestClinicNow() {
            if (!state.data || !state.data.now) return null;
            return state.data.now.ordinalSeconds + Math.max(0, clock() - state.receivedAt) / 1000;
        }

        function inPast(parsed) {
            const now = latestClinicNow();
            return now !== null && parsed.ordinalSeconds < now;
        }

        function renderChoices(parsed) {
            const data = state.data;
            choices.replaceChildren();
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Выберите время по графику';
            choices.append(placeholder);

            const validSlots = data.slots.filter(function (time) {
                return !inPast(parseLocalDateTime(data.date + 'T' + time));
            });
            if (parsed && !inPast(parsed) && fitsIntervals(parsed.time, data.duration_minutes, data.intervals)
                && !validSlots.includes(parsed.time)) {
                validSlots.push(parsed.time);
                validSlots.sort();
            }
            validSlots.forEach(function (time) {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time + '–' + formatMinutes(timeToMinutes(time, false) + data.duration_minutes);
                choices.append(option);
            });
            choices.disabled = validSlots.length === 0;
            choices.value = parsed && validSlots.includes(parsed.time) ? parsed.time : '';
            if (validSlots.length === 0) placeholder.textContent = 'Нет подходящего времени по графику';
            hours.textContent = data.intervals.length
                ? 'Рабочие интервалы: ' + data.intervals.map(function (range) { return range.start + '–' + range.end; }).join(', ') + '.'
                : '';
            zone.textContent = 'Часовой пояс клиники: ' + data.timezone + '.';
        }

        function validateReady(current, parsed, minutes) {
            applyMinimum(clinicMinimum());
            renderChoices(parsed);
            let message = '';
            if (!parsed || parsed.second !== 0) message = TIME_FORMAT;
            else if (inPast(parsed)) message = PAST;
            else if (!state.data.intervals.length) message = state.data.message || 'На выбранную дату у врача нет рабочих часов.';
            else if (!fitsIntervals(parsed.time, minutes, state.data.intervals)) message = OUTSIDE;

            ownValidity(message);
            show(message || ('Время подходит по графику: ' + parsed.time + '–'
                + formatMinutes(timeToMinutes(parsed.time, false) + minutes) + ', ' + minutes + ' мин.'),
            message ? 'error' : 'success');
            return !message;
        }

        function serverError(payload, statusCode) {
            if (statusCode === 401 || statusCode === 419) return 'Сессия завершена. Обновите страницу и войдите снова.';
            if (statusCode === 403) return 'Нет доступа к расписанию выбранного врача.';
            if (statusCode === 422 && payload && typeof payload === 'object') {
                const errors = payload.errors;
                if (errors && typeof errors === 'object') {
                    for (const field of ['doctor_id', 'date', 'appointment_duration_minutes', 'appointment_format', 'record_id']) {
                        const message = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                        if (typeof message === 'string' && message.trim()) return message.slice(0, 800);
                    }
                }
                if (typeof payload.message === 'string' && payload.message.trim()) return payload.message.slice(0, 800);
            }
            return NETWORK_ERROR;
        }

        async function load(current, parsed, minutes, key) {
            stopPending();
            const requestGeneration = generation;
            controller = new window.AbortController();
            const requestController = controller;
            let timedOut = false;
            state = {phase: 'loading', key: key, data: null, receivedAt: 0};
            resetChoices('Проверяем рабочее время…');
            retry.hidden = true;
            container.setAttribute('aria-busy', 'true');
            ownValidity(LOADING);
            show(LOADING, 'muted');

            const timeout = window.setTimeout(function () {
                timedOut = true;
                requestController.abort();
            }, 15000);

            try {
                const url = new window.URL(container.dataset.availabilityUrl, window.location.href);
                if (url.origin !== window.location.origin) throw new Error('Invalid endpoint origin');
                url.searchParams.set('doctor_id', current.doctorId);
                url.searchParams.set('date', parsed.date);
                url.searchParams.set('appointment_duration_minutes', String(minutes));
                url.searchParams.set('appointment_format', current.appointmentFormat);
                if (original.recordId) url.searchParams.set('record_id', original.recordId);

                const response = await window.fetch(url.toString(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    signal: requestController.signal
                });
                let payload = null;
                try { payload = await response.json(); } catch (_) { /* Treat an HTML login/error page as unavailable. */ }
                if (disposed || requestGeneration !== generation) return;
                if (!response.ok || response.redirected) {
                    const failure = new Error('Availability request failed');
                    failure.userMessage = serverError(payload, response.status);
                    throw failure;
                }
                const data = parsePayload(payload, parsed.date, minutes, current.appointmentFormat);
                state = {phase: 'ready', key: key, data: data, receivedAt: clock()};
                refresh();
            } catch (error) {
                if (disposed || requestGeneration !== generation) return;
                const message = timedOut ? 'Проверка графика заняла слишком много времени. Повторите проверку.'
                    : error && typeof error.userMessage === 'string' ? error.userMessage : NETWORK_ERROR;
                state = {phase: 'error', key: key, data: null, receivedAt: 0};
                resetChoices('Проверка недоступна');
                ownValidity(message);
                show(message, 'error');
                retry.hidden = false;
            } finally {
                window.clearTimeout(timeout);
                if (requestGeneration === generation) {
                    controller = null;
                    container.setAttribute('aria-busy', 'false');
                }
            }
        }

        function refresh(force) {
            if (disposed) return false;
            const picker = calendar();
            if (picker && picker.manualDirty) {
                stopPending();
                state = {phase: 'draft', key: '', data: null, receivedAt: 0};
                resetChoices('Завершите ввод даты и времени');
                retry.hidden = true;
                ownValidity(CALENDAR_DRAFT);
                show(CALENDAR_DRAFT, 'muted');
                return false;
            }
            const current = inputs();
            const isUnchanged = unchanged();
            duration.required = !isUnchanged;

            if (isUnchanged) {
                if (state.phase !== 'unchanged') stopPending();
                state = {phase: 'unchanged', key: '', data: null, receivedAt: 0};
                resetChoices('Параметры приёма не изменены');
                retry.hidden = true;
                applyMinimum(null);
                ownValidity('');
                show('Параметры приёма не изменены. При переносе или смене формата укажите длительность и время по графику.', 'muted');
                return true;
            }

            const parsed = parseLocalDateTime(current.datetime);
            const minutes = parseDuration(current.duration);
            if (!/^[1-9]\d*$/.test(current.doctorId) || !parsed || parsed.second !== 0
                || minutes === null || current.appointmentFormat === null) {
                stopPending();
                state = {phase: 'idle', key: '', data: null, receivedAt: 0};
                resetChoices('Сначала укажите врача, дату и длительность');
                retry.hidden = true;
                const message = current.appointmentFormat === null ? FORMAT_ERROR
                    : current.datetime && (!parsed || parsed.second !== 0)
                        ? TIME_FORMAT : minutes === null ? DURATION_ERROR : EMPTY;
                ownValidity(message);
                show(message, 'muted');
                return false;
            }

            const key = keyFor(current, parsed, minutes);
            if (force !== true && state.key === key) {
                if (state.phase === 'ready') return validateReady(current, parsed, minutes);
                ownValidity(validationMessage);
                return false;
            }

            load(current, parsed, minutes, key);
            return false;
        }

        function report() {
            ownValidity(validationMessage || LOADING);
            const field = visibleAppointment();
            field.focus();
            field.reportValidity();
        }

        function commitCalendarDraft() {
            const picker = calendar();
            if (!picker || !picker.manualDirty) return true;
            if (typeof picker.commitTypedValue !== 'function') return false;

            // Parse the draft before checking availability. Restoring only our own
            // derived minimum also permits returning to an unchanged historical visit.
            applyMinimum(null);
            if (picker.commitTypedValue(true)) return true;
            stopPending();
            state = {phase: 'draft', key: '', data: null, receivedAt: 0};
            resetChoices('Исправьте дату и время');
            const message = picker.altInput.validationMessage || TIME_FORMAT;
            ownValidity(message);
            show(message, 'error');
            return false;
        }

        function submitGuard(event) {
            if (!commitCalendarDraft() || !refresh()) {
                event.preventDefault();
                event.stopImmediatePropagation();
                report();
            }
        }

        doctor.addEventListener('input', refresh);
        doctor.addEventListener('change', refresh);
        appointment.addEventListener('input', refresh);
        appointment.addEventListener('change', refresh);
        duration.addEventListener('input', refresh);
        duration.addEventListener('change', refresh);
        function formatChanged(event) {
            if (event.target && ['appointment_format', 'appointment_timezone_confirmed'].includes(event.target.name)) refresh();
        }
        // Delegation also covers a format control inserted by existing telemedicine UI.
        // Do not replace its handlers or alter the control itself.
        form.addEventListener('input', formatChanged, true);
        form.addEventListener('change', formatChanged, true);
        function calendarChanged(event) {
            const picker = calendar();
            if (picker && event.target === picker.altInput) refresh();
        }
        form.addEventListener('input', calendarChanged);
        form.addEventListener('change', calendarChanged);
        form.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') calendarChanged(event);
        });
        choices.addEventListener('change', function () {
            if (state.phase !== 'ready' || !choices.value) return;
            const value = state.data.date + 'T' + choices.value;
            const picker = calendar();
            if (picker && typeof picker.setDate === 'function') {
                picker.setDate(value, false);
                if (appointment.value !== value) {
                    // A widget boundary or a local DST normalization must never
                    // silently turn a chosen slot into a different appointment.
                    if (typeof picker.clear === 'function') picker.clear(false);
                    appointment.value = '';
                    stopPending();
                    state = {phase: 'idle', key: '', data: null, receivedAt: 0};
                    resetChoices('Выберите дату и время заново');
                    ownValidity(CALENDAR_SELECTION);
                    show(CALENDAR_SELECTION, 'error');
                    return;
                }
                if (typeof picker.fireChange === 'function') {
                    picker.fireChange();
                    refresh();
                    return;
                }
            } else appointment.value = value;
            appointment.dispatchEvent(new window.Event('input', {bubbles: true}));
            appointment.dispatchEvent(new window.Event('change', {bubbles: true}));
        });
        retry.addEventListener('click', function () { refresh(true); });
        form.addEventListener('submit', submitGuard, true);
        form.addEventListener('click', function (event) {
            const control = event.target.closest ? event.target.closest('button, input') : null;
            if (!control || control.form !== form || control.type !== 'submit' || control.disabled) return;
            if (!commitCalendarDraft() || !refresh()) {
                event.preventDefault();
                event.stopImmediatePropagation();
                report();
            }
        }, true);

        // Advance clinic time without relying on the browser's configured timezone or clock.
        const tick = window.setInterval(function () {
            if (!disposed && state.phase === 'ready') refresh();
        }, 15000);
        window.addEventListener('pagehide', function (event) {
            stopPending();
            if (!event.persisted) {
                disposed = true;
                window.clearInterval(tick);
            }
        });
        window.addEventListener('pageshow', function (event) {
            if (event.persisted && !disposed) refresh(true);
        });

        refresh();
        return {refresh: refresh};
    }

    function boot(document) {
        const start = function () {
            document.querySelectorAll('[data-doctor-availability]').forEach(init);
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once: true});
        else start();
    }

    return {parseLocalDateTime: parseLocalDateTime, parseDuration: parseDuration,
        parseAppointmentFormat: parseAppointmentFormat,
        fitsIntervals: fitsIntervals, parsePayload: parsePayload, sameSchedule: sameSchedule,
        boot: boot, init: init};
});
