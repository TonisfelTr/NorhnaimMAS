/* Calendar compact statuses: progressive enhancement; no network or API changes. */
(function () {
    'use strict';

    function conciseReason(message) {
        if (/прошедш|прошл[а-яё ]*дат|дата.*прошл/i.test(message)) {
            return { label: 'Дата прошла', variant: 'is-past' };
        }
        if (/лимит|исчерпан|запис[ьи].*законч|остаток\s*0|осталось\s*0/i.test(message)) {
            return { label: 'Лимит записей', variant: 'is-limit' };
        }
        if (/занят|уже записан/i.test(message)) {
            return { label: 'Время занято', variant: '' };
        }
        if (/длительност|вариантов времени|рабочий интервал/i.test(message)) {
            return { label: 'Нет слотов', variant: '' };
        }
        if (/нет .*рабоч|нет .*интервал|график клиники|рабочих интервалов/i.test(message)) {
            return { label: 'Нет графика', variant: '' };
        }
        if (/свободн/i.test(message)) {
            return { label: 'Нет времени', variant: '' };
        }
        return { label: 'Нет записи', variant: '' };
    }

    function enhanceCalendar(grid) {
        grid.querySelectorAll('.patient-booking-day__empty').forEach(function (node) {
            if (!node.isConnected) return;
            const message = (node.textContent || '').trim();
            if (!message) return;

            const reason = conciseReason(message);
            const details = document.createElement('details');
            details.className = 'patient-booking-day__compact-state patient-booking-day__reason';
            if (reason.variant) details.classList.add(reason.variant);

            const summary = document.createElement('summary');
            summary.title = 'Нажмите, чтобы узнать причину недоступности';

            const icon = document.createElement('i');
            icon.className = 'bi bi-info-circle';
            icon.setAttribute('aria-hidden', 'true');

            const label = document.createElement('span');
            label.textContent = reason.label;

            summary.append(icon, label);
            const full = document.createElement('p');
            full.className = 'patient-booking-day__reason-text';
            full.textContent = message;

            details.append(summary, full);
            node.replaceWith(details);
        });
    }

    function initialize() {
        const grid = document.getElementById('bookingWeekGrid');
        if (!grid || !grid.closest('.patient-booking-page')) return;

        enhanceCalendar(grid);
        const observer = new MutationObserver(function () {
            enhanceCalendar(grid);
        });
        observer.observe(grid, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
}());
