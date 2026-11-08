(function () {
    'use strict';
    if (window.ClinicAppointmentTime) return;

    const storageKey = 'norhnaim.appointment-display-timezone.v1';
    const formatters = new Map();
    function validZone(zone) {
        if (typeof zone !== 'string' || !zone) return false;
        try { new Intl.DateTimeFormat('en', {timeZone: zone}).format(0); return true; }
        catch (_) { return false; }
    }
    function deviceZone() {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    }
    function parts(instant, zone) {
        if (!formatters.has(zone)) {
            formatters.set(zone, new Intl.DateTimeFormat('en-GB', {
                timeZone: zone, year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23'
            }));
        }
        const result = {};
        formatters.get(zone).formatToParts(new Date(instant)).forEach(part => {
            if (part.type !== 'literal') result[part.type] = Number(part.value);
        });
        return result;
    }
    function civilEpoch(p) {
        return Date.UTC(p.year, p.month - 1, p.day, p.hour || 0, p.minute || 0, p.second || 0);
    }
    // Calendar editor uses UTC getters on this civil-date carrier. It is NOT an instant.
    function civilNow(zone, instant) {
        return new Date(civilEpoch(parts(instant === undefined ? Date.now() : instant, zone)));
    }
    function resolveLocal(value, zone) {
        const match = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::00)?$/.exec(value || '');
        if (!match || Number(match[1]) < 1000 || !validZone(zone)) return null;
        const target = {year: +match[1], month: +match[2], day: +match[3], hour: +match[4], minute: +match[5], second: 0};
        const naive = civilEpoch(target);
        const check = new Date(naive);
        if (check.getUTCFullYear() !== target.year || check.getUTCMonth() + 1 !== target.month
            || check.getUTCDate() !== target.day || check.getUTCHours() !== target.hour
            || check.getUTCMinutes() !== target.minute) return null;
        const offsets = new Set();
        for (let hours = -48; hours <= 48; hours += 6) {
            const sample = naive + hours * 3600000;
            offsets.add(civilEpoch(parts(sample, zone)) - sample);
        }
        const candidates = new Set();
        offsets.forEach(offset => {
            const candidate = naive - offset;
            if (civilEpoch(parts(candidate, zone)) === naive) candidates.add(candidate);
        });
        return candidates.size === 1 ? Array.from(candidates)[0] : null;
    }
    function readPreference() {
        try {
            const value = localStorage.getItem(storageKey);
            return value && validZone(value) ? value : 'auto';
        } catch (_) { return 'auto'; }
    }
    let preference = readPreference();
    function selectedZone() { return preference === 'auto' ? deviceZone() : preference; }
    function format(instant, zone) {
        return new Intl.DateTimeFormat('ru-RU', {
            timeZone: zone, day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
        }).format(new Date(instant));
    }
    function formatDate(instant, zone) {
        return new Intl.DateTimeFormat('ru-RU', {
            timeZone: zone, day: '2-digit', month: '2-digit', year: 'numeric'
        }).format(new Date(instant));
    }
    function formatTime(instant, zone) {
        return new Intl.DateTimeFormat('ru-RU', {
            timeZone: zone, hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
        }).format(new Date(instant));
    }
    function setText(node, value) { if (node && node.textContent !== value) node.textContent = value; }
    function updateControl(control) {
        const select = control.querySelector('[data-appointment-zone-select]');
        if (!select) return;
        if (!select.dataset.ready) {
            let zones;
            try { zones = Intl.supportedValuesOf('timeZone'); }
            catch (_) { zones = ['UTC', 'Europe/Kaliningrad', 'Europe/Moscow', 'Europe/Samara', 'Asia/Yekaterinburg', 'Asia/Omsk', 'Asia/Tomsk', 'Asia/Krasnoyarsk', 'Asia/Irkutsk', 'Asia/Yakutsk', 'Asia/Vladivostok', 'Asia/Magadan', 'Asia/Kamchatka']; }
            zones = Array.from(new Set(['UTC', deviceZone(), ...(preference === 'auto' ? [] : [preference]), ...zones])).sort();
            zones.filter(validZone).forEach(zone => {
                const option = document.createElement('option'); option.value = zone; option.textContent = zone; select.appendChild(option);
            });
            select.dataset.ready = '1';
            select.addEventListener('change', function () {
                preference = select.value === 'auto' || validZone(select.value) ? select.value : 'auto';
                try { localStorage.setItem(storageKey, preference); } catch (_) { /* Display still works without storage. */ }
                render();
            });
        }
        select.value = preference;
        setText(control.querySelector('[data-appointment-zone-current]'), 'Часовой пояс отображения: ' + selectedZone() + '. Время записи не изменяется.');
    }
    function render() {
        const zone = selectedZone();
        document.querySelectorAll('[data-appointment-zone-control]').forEach(updateControl);
        document.querySelectorAll('[data-appointment-display]').forEach(node => {
            const iso = node.dataset.appointmentUtc;
            const known = typeof iso === 'string' && /(?:Z|[+-]\d{2}:?\d{2})$/.test(iso);
            const instant = known ? Date.parse(iso) : NaN;
            if (!Number.isFinite(instant)) return;
            setText(node.querySelector('[data-appointment-local-time]'), format(instant, zone));
            setText(node.querySelector('[data-appointment-local-zone-label]'), '(' + zone + ')');
        });
        document.querySelectorAll('[data-appointment-primary]').forEach(node => {
            const iso = node.dataset.appointmentUtc;
            const known = typeof iso === 'string' && /(?:Z|[+-]\d{2}:?\d{2})$/.test(iso);
            const instant = known ? Date.parse(iso) : NaN;
            if (!Number.isFinite(instant)) return;
            setText(node.querySelector('[data-appointment-primary-date]'), formatDate(instant, zone));
            setText(node.querySelector('[data-appointment-primary-time]'), formatTime(instant, zone));
            setText(node.querySelector('[data-appointment-primary-zone]'), zone);
        });
        document.querySelectorAll('[data-appointment-input-zone]').forEach(node => {
            const form = node.closest('form');
            const inputName = node.dataset.appointmentInputName === 'telemed_starts_at' ? 'telemed_starts_at' : 'appointment_datetime';
            const input = form && form.querySelector('[name="' + inputName + '"]');
            const preview = node.querySelector('[data-appointment-input-preview]');
            if (!input || !preview) return;
            const confirm = node.querySelector('[data-appointment-time-confirm]');
            const legacyUnchanged = node.dataset.appointmentLegacy === '1'
                && input.value === node.dataset.appointmentOriginal && !(confirm && confirm.checked);
            if (legacyUnchanged) {
                setText(preview, 'Ваше время: пересчёт станет доступен после уточнения часового пояса.');
                return;
            }
            const instant = resolveLocal(input.value, node.dataset.clinicTimezone);
            setText(preview, instant === null
                ? (input.value ? 'Для этого значения нельзя однозначно определить время. Проверьте дату и перевод часов.' : 'Выберите дату и время приёма.')
                : 'Ваше время: ' + format(instant, zone) + ' (' + zone + ').');
        });
    }
    window.ClinicAppointmentTime = {validZone, resolveLocal, civilNow, format, formatDate, formatTime, render};
    function init() {
        render();
        document.addEventListener('input', event => {
            if (event.target && ['appointment_datetime', 'telemed_starts_at'].includes(event.target.name)) render();
        });
        document.addEventListener('change', event => {
            if (event.target && ['appointment_datetime', 'telemed_starts_at', 'appointment_timezone_confirmed'].includes(event.target.name)) render();
        });
        window.addEventListener('storage', event => {
            if (event.key === storageKey) { preference = readPreference(); render(); }
        });
        let pending = false;
        new MutationObserver(() => {
            if (pending) return;
            pending = true;
            requestAnimationFrame(() => { pending = false; render(); });
        }).observe(document.body, {childList: true, subtree: true});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
})();
