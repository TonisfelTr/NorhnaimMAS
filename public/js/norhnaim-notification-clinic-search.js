/* NORHNAIM_NOTIFICATION_CLINIC_SEARCH_V1
 * Searchable enhancement for clinic notification select.
 * The original select and its GET form remain the no-JS fallback.
 */
(() => {
    'use strict';

    const normalize = (value) => String(value ?? '')
        .toLocaleLowerCase('ru-RU')
        .replaceAll('ё', 'е')
        .replace(/\s+/g, ' ')
        .trim();

    function setup(select) {
        if (!select || select.dataset.norhnaimSearchReady === '1') return;
        const form = select.closest('form');
        if (!form || select.name !== 'clinic_id') return;

        const entries = Array.from(select.options).map((option) => ({
            value: option.value,
            label: option.textContent.trim(),
            normalized: normalize(option.textContent),
        }));
        if (!entries.length) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'ncs-select';
        wrapper.dataset.notificationClinicSearch = '1';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'ncs-select__trigger';
        trigger.setAttribute('aria-label', 'Выберите клинику');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        const triggerLabel = document.createElement('span');
        triggerLabel.className = 'ncs-select__trigger-label';
        triggerLabel.textContent = entries.find((entry) => entry.value === select.value)?.label ?? entries[0].label;
        const arrow = document.createElement('span');
        arrow.className = 'ncs-select__chevron';
        arrow.setAttribute('aria-hidden', 'true');
        trigger.append(triggerLabel, arrow);

        const panel = document.createElement('div');
        panel.className = 'ncs-select__panel';
        panel.hidden = true;
        const searchWrap = document.createElement('div');
        searchWrap.className = 'ncs-select__search-wrap';
        const searchIcon = document.createElement('span');
        searchIcon.className = 'ncs-select__search-icon';
        searchIcon.setAttribute('aria-hidden', 'true');
        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'ncs-select__search';
        search.autocomplete = 'off';
        search.placeholder = 'Поиск клиники…';
        search.setAttribute('aria-label', 'Поиск клиники по названию');
        searchWrap.append(searchIcon, search);

        const list = document.createElement('div');
        list.className = 'ncs-select__list';
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', 'Выберите клинику');
        const listId = 'notification-clinic-search-options';
        list.id = listId;
        trigger.setAttribute('aria-controls', listId);
        panel.append(searchWrap, list);
        wrapper.append(trigger, panel);

        // Build the enhancement before disabling the native select.
        select.insertAdjacentElement('beforebegin', wrapper);
        select.classList.add('ncs-select__native-hidden');
        select.dataset.norhnaimSearchReady = '1';

        function close(restoreFocus = false) {
            if (panel.hidden) return;
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            wrapper.classList.remove('is-open');
            if (restoreFocus) trigger.focus();
        }

        function selectClinic(value) {
            close(false);
            if (select.value === value) {
                trigger.focus();
                return;
            }
            select.value = value;
            triggerLabel.textContent = entries.find((entry) => entry.value === value)?.label ?? entries[0].label;
            // Keep existing filtering, tab and pagination behavior server-side.
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }

        function render() {
            const query = normalize(search.value);
            const matching = entries.filter((entry) => entry.normalized.includes(query));
            list.replaceChildren();
            if (!matching.length) {
                const empty = document.createElement('div');
                empty.className = 'ncs-select__empty';
                empty.textContent = 'Клиники не найдены';
                list.append(empty);
                return;
            }
            for (const entry of matching) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'ncs-select__option';
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', entry.value === select.value ? 'true' : 'false');
                button.dataset.value = entry.value;
                const text = document.createElement('span');
                text.className = 'ncs-select__option-label';
                text.textContent = entry.label;
                const check = document.createElement('span');
                check.className = 'ncs-select__option-check';
                check.setAttribute('aria-hidden', 'true');
                check.textContent = '✓';
                button.append(text, check);
                button.addEventListener('click', () => selectClinic(entry.value));
                list.append(button);
            }
        }

        function open() {
            if (!panel.hidden) return;
            panel.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            wrapper.classList.add('is-open');
            search.value = '';
            render();
            search.focus();
        }

        function focusOption(index) {
            const items = Array.from(list.querySelectorAll('.ncs-select__option'));
            if (!items.length) return;
            const normalizedIndex = Math.max(0, Math.min(items.length - 1, index));
            items[normalizedIndex].focus();
        }

        trigger.addEventListener('click', () => panel.hidden ? open() : close(false));
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                open();
                focusOption(event.key === 'ArrowDown' ? 0 : list.querySelectorAll('.ncs-select__option').length - 1);
            }
        });
        search.addEventListener('input', render);
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                close(true);
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                focusOption(event.key === 'ArrowDown' ? 0 : list.querySelectorAll('.ncs-select__option').length - 1);
            } else if (event.key === 'Enter') {
                const first = list.querySelector('.ncs-select__option');
                if (first) {
                    event.preventDefault();
                    selectClinic(first.dataset.value);
                } else {
                    // A search that found nothing should not submit the form.
                    event.preventDefault();
                }
            }
        });
        list.addEventListener('keydown', (event) => {
            const items = Array.from(list.querySelectorAll('.ncs-select__option'));
            const index = items.indexOf(document.activeElement);
            if (event.key === 'Escape') {
                event.preventDefault();
                close(true);
            } else if (event.key === 'ArrowDown' && index >= 0) {
                event.preventDefault();
                focusOption(Math.min(index + 1, items.length - 1));
            } else if (event.key === 'ArrowUp' && index >= 0) {
                event.preventDefault();
                if (index === 0) search.focus();
                else focusOption(index - 1);
            } else if (event.key === 'Home') {
                event.preventDefault();
                focusOption(0);
            } else if (event.key === 'End') {
                event.preventDefault();
                focusOption(items.length - 1);
            }
        });
        document.addEventListener('pointerdown', (event) => {
            if (!wrapper.contains(event.target)) close(false);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) close(true);
        });
    }

    function init() {
        setup(document.getElementById('notificationClinicSelect'));
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, {once: true});
    } else {
        init();
    }
})();
