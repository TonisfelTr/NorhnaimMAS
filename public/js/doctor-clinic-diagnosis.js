(() => {
    'use strict';

    const modal = document.getElementById('mcClinicDiagnosisModal');
    if (!modal) return;

    const form = modal.querySelector('[data-clinic-diagnosis-form]');
    const search = modal.querySelector('[data-clinic-diagnosis-search]');
    const results = modal.querySelector('[data-clinic-diagnosis-results]');
    const diagnosisId = modal.querySelector('[data-clinic-diagnosis-id]');
    const chosen = modal.querySelector('[data-clinic-diagnosis-chosen]');
    const chosenName = modal.querySelector('[data-clinic-diagnosis-chosen-name]');
    const save = modal.querySelector('[data-clinic-diagnosis-save]');
    const combobox = modal.querySelector('[data-clinic-diagnosis-combobox]');
    if (!form || !search || !results || !diagnosisId || !chosen || !chosenName || !save || !combobox) return;

    const url = form.dataset.searchUrl;
    let requestId = 0;
    let debounce = null;
    let controller = null;
    let skipNextFocusOpen = false;

    const isAllowed = (code) => /^[FG]\d{2}(?:\.[A-Z0-9]{1,6})?$/i.test(String(code || '').trim());
    const clearNode = () => results.replaceChildren();
    const message = (text, error = false) => {
        clearNode();
        const element = document.createElement('div');
        element.className = 'mc-clinic-diagnosis__message' + (error ? ' is-error' : '');
        element.textContent = text;
        results.append(element);
    };

    function showList() {
        results.hidden = false;
        search.setAttribute('aria-expanded', 'true');
        combobox.classList.add('is-open');
    }

    function hideList() {
        results.hidden = true;
        search.setAttribute('aria-expanded', 'false');
        combobox.classList.remove('is-open');
    }

    function stopSearch() {
        if (debounce !== null) {
            clearTimeout(debounce);
            debounce = null;
        }
        if (controller) {
            controller.abort();
            controller = null;
        }
        requestId += 1;
    }

    function unselect() {
        diagnosisId.value = '';
        chosen.hidden = true;
        chosenName.textContent = '';
        save.disabled = true;
    }

    function choose(item) {
        if (!isAllowed(item.code)) return;
        stopSearch();
        diagnosisId.value = String(item.id);
        chosenName.textContent = item.code + ' — ' + item.title;
        chosen.hidden = false;
        save.disabled = false;
        search.value = item.code + ' — ' + item.title;
        hideList();
    }

    async function performSearch(value) {
        if (controller) controller.abort();
        controller = new AbortController();
        const thisRequest = ++requestId;
        showList();
        message('Загружаем диагнозы F и G…');

        try {
            const endpoint = new URL(url, window.location.href);
            endpoint.searchParams.set('q', value);
            const response = await fetch(endpoint.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            if (thisRequest !== requestId || results.hidden) return;

            clearNode();
            const items = Array.isArray(data.items)
                ? data.items.filter(item => Number.isInteger(item.id) && isAllowed(item.code) && item.title)
                : [];
            if (!items.length) {
                message('В группах F и G диагнозы не найдены. Уточните запрос.');
                return;
            }

            items.forEach((item, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.id = 'mc-clinic-diagnosis-option-' + index;
                button.className = 'mc-clinic-diagnosis__option';
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', 'false');
                const code = document.createElement('strong');
                code.textContent = String(item.code);
                const title = document.createElement('span');
                title.textContent = String(item.title);
                button.append(code, title);
                button.addEventListener('click', () => choose(item));
                results.append(button);
            });
            if (items.length >= 25) {
                const more = document.createElement('div');
                more.className = 'mc-clinic-diagnosis__message mc-clinic-diagnosis__more';
                more.textContent = 'Показаны первые 25 диагнозов. Уточните код или название для поиска.';
                results.append(more);
            }
        } catch (error) {
            if (error.name !== 'AbortError' && thisRequest === requestId) {
                showList();
                message('Не удалось загрузить диагнозы. Проверьте соединение.', true);
            }
        }
    }

    function scheduleSearch(value, delay = 220) {
        stopSearch();
        showList();
        debounce = setTimeout(() => {
            debounce = null;
            performSearch(value);
        }, delay);
    }

    function focusOption(index) {
        const options = Array.from(results.querySelectorAll('.mc-clinic-diagnosis__option'));
        if (!options.length) return;
        options[Math.max(0, Math.min(options.length - 1, index))].focus();
    }

    // The selector opens immediately, even before the patient searches.
    search.addEventListener('focus', () => {
        if (skipNextFocusOpen) {
            skipNextFocusOpen = false;
            return;
        }
        if (diagnosisId.value) search.select();
        if (results.hidden) scheduleSearch(diagnosisId.value ? '' : search.value.trim(), 0);
    });
    search.addEventListener('click', () => {
        if (results.hidden) scheduleSearch(diagnosisId.value ? '' : search.value.trim(), 0);
    });
    search.addEventListener('input', () => {
        unselect();
        scheduleSearch(search.value.trim());
    });

    search.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (results.hidden) scheduleSearch('', 0);
            else focusOption(event.key === 'ArrowDown' ? 0 : results.querySelectorAll('.mc-clinic-diagnosis__option').length - 1);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (!diagnosisId.value) {
                const first = results.querySelector('.mc-clinic-diagnosis__option');
                if (first) first.click();
            }
        } else if (event.key === 'Escape' && !results.hidden) {
            event.stopPropagation();
            stopSearch();
            hideList();
        }
    });

    results.addEventListener('keydown', (event) => {
        const options = Array.from(results.querySelectorAll('.mc-clinic-diagnosis__option'));
        const current = options.indexOf(document.activeElement);
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            stopSearch();
            hideList();
            skipNextFocusOpen = true;
            search.focus();
            return;
        }
        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        if (event.key === 'Home') focusOption(0);
        else if (event.key === 'End') focusOption(options.length - 1);
        else if (event.key === 'ArrowDown') focusOption(current + 1);
        else if (current <= 0) search.focus();
        else focusOption(current - 1);
    });

    modal.querySelectorAll('[data-clinic-diagnosis-group]').forEach(button => {
        button.addEventListener('click', () => {
            const group = button.dataset.clinicDiagnosisGroup;
            if (!['F', 'G'].includes(group)) return;
            unselect();
            search.value = group;
            search.focus();
            scheduleSearch(group, 0);
        });
    });

    modal.addEventListener('pointerdown', (event) => {
        if (!combobox.contains(event.target) && !results.hidden) {
            stopSearch();
            hideList();
        }
    });
    form.addEventListener('submit', (event) => {
        if (!diagnosisId.value) event.preventDefault();
        else save.disabled = true;
    });
    modal.addEventListener('hidden.bs.modal', () => {
        stopSearch();
        search.value = '';
        unselect();
        hideList();
        message('Выберите диагноз из списка F и G.');
    });
    modal.addEventListener('shown.bs.modal', () => {
        search.focus();
        scheduleSearch('', 0);
    });

    if (modal.dataset.openOnError === '1') {
        document.addEventListener('DOMContentLoaded', () => {
            const bs = window.bootstrap;
            if (bs?.Modal) bs.Modal.getOrCreateInstance(modal).show();
        }, { once: true });
    }
})();
