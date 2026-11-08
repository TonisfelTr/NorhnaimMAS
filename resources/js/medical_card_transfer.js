import { Modal } from 'bootstrap';

function initPatientDoctorTransfer() {
    const modal = document.getElementById('modalTransferPatientDoctor');
    if (!modal) return;

    const form = modal.querySelector('[data-transfer-form]');
    const select = modal.querySelector('[data-transfer-select]');
    const search = modal.querySelector('[data-transfer-search]');
    const status = modal.querySelector('[data-transfer-status]');
    const submit = modal.querySelector('[data-transfer-submit]');
    const checkbox = modal.querySelector('[data-transfer-confirm]');
    const selectedSummary = modal.querySelector('[data-transfer-summary]');
    const candidatesUrl = modal.dataset.candidatesUrl;
    const initialSelected = modal.dataset.oldDoctorId || '';

    if (!form || !select || !search || !status || !submit || !checkbox || !candidatesUrl) return;

    let request = null;
    let debounceTimer = null;
    let serial = 0;
    let initialized = false;

    const updateSubmitState = () => {
        submit.disabled = !select.value || !checkbox.checked;
        const chosen = select.options[select.selectedIndex];
        if (selectedSummary) {
            selectedSummary.textContent = chosen?.value
                ? `Новый лечащий врач: ${chosen.textContent}`
                : 'Выберите врача из списка, чтобы подтвердить передачу.';
        }
    };

    async function loadDoctors(restoreId = '') {
        request?.abort();
        request = new AbortController();
        const callId = ++serial;
        const query = new URL(candidatesUrl, window.location.origin);
        query.searchParams.set('q', search.value.trim());
        if (restoreId) query.searchParams.set('selected_id', restoreId);

        status.textContent = 'Загружаем врачей…';
        select.disabled = true;
        submit.disabled = true;

        try {
            const response = await fetch(query.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
                signal: request.signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json();
            if (callId !== serial) return;

            select.replaceChildren(new Option('Выберите врача', ''));
            const items = Array.isArray(payload.doctors) ? payload.doctors : [];
            for (const doctor of items) {
                const name = String(doctor.text || '');
                const detail = String(doctor.profession || '').trim();
                select.add(new Option(detail ? `${name} — ${detail}` : name, String(doctor.id)));
            }

            select.disabled = items.length === 0;
            if (restoreId && items.some(doctor => String(doctor.id) === String(restoreId))) {
                select.value = String(restoreId);
            }
            status.textContent = items.length
                ? `Найдено врачей: ${items.length}. Поиск ограничен текущей клиникой.`
                : 'Подходящих врачей не найдено.';
            updateSubmitState();
        } catch (error) {
            if (error.name === 'AbortError') return;
            select.replaceChildren(new Option('Не удалось загрузить список врачей', ''));
            status.textContent = 'Не удалось загрузить врачей. Проверьте соединение и попробуйте ещё раз.';
            select.disabled = true;
            updateSubmitState();
        }
    }

    search.addEventListener('input', () => {
        checkbox.checked = false;
        updateSubmitState();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadDoctors(), 280);
    });
    select.addEventListener('change', updateSubmitState);
    checkbox.addEventListener('change', updateSubmitState);

    modal.addEventListener('shown.bs.modal', () => {
        if (!initialized) {
            initialized = true;
            loadDoctors(initialSelected);
        }
    });

    modal.addEventListener('hidden.bs.modal', () => {
        request?.abort();
        clearTimeout(debounceTimer);
        initialized = false;
        search.value = '';
        // Форма не отправлена — не оставляем случайно выбранного врача.
        select.replaceChildren(new Option('Выберите врача', ''));
        select.disabled = true;
        checkbox.checked = false;
        updateSubmitState();
    });

    form.addEventListener('submit', event => {
        if (!select.value || !checkbox.checked) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
    });

    updateSubmitState();
    if (modal.dataset.openOnError === '1') {
        Modal.getOrCreateInstance(modal).show();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPatientDoctorTransfer, { once: true });
} else {
    initPatientDoctorTransfer();
}
