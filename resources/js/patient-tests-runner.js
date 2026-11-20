(() => {
    'use strict';

    const root = document.getElementById('patient-test-runner');
    if (!root) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const urls = {
        payload: root.dataset.payloadUrl,
        save: root.dataset.saveUrl,
        finish: root.dataset.finishUrl,
        result: root.dataset.resultUrl,
        back: root.dataset.returnUrl,
    };

    const state = {
        payload: null,
        index: 0,
        answers: {},
        sortOrder: [],
        saving: false,
        pendingSaveCount: 0,
        saveQueue: Promise.resolve(),
        savedSnapshots: new Map(),
        dirtySaves: new Map(),
        pendingSaves: new Map(),
        saveTimer: null,
        lastError: '',
        finished: false,
    };

    const text = (value) => String(value ?? '');
    const hasValue = (value) => {
        if (value === null || value === undefined) return false;
        if (Array.isArray(value)) return value.length > 0;
        return String(value).trim() !== '';
    };

    async function requestJson(url, options = {}) {
        const extraHeaders = options.headers || {};
        const fetchOptions = { ...options };
        delete fetchOptions.headers;

        const response = await fetch(url, {
            credentials: 'same-origin',
            ...fetchOptions,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...extraHeaders,
            },
        });

        let data = {};
        try {
            data = await response.json();
        } catch (_) {
            data = {};
        }

        if (!response.ok) {
            const message = response.status === 429
                ? 'Сервер временно ограничил частоту сохранения.'
                : (
                    data.message
                    || Object.values(data.errors || {}).flat()[0]
                    || `Не удалось выполнить запрос (${response.status}).`
                );

            const error = new Error(message);
            error.status = response.status;

            const retryAfter = response.headers.get('Retry-After');
            if (retryAfter) {
                const seconds = Number(retryAfter);
                const retryAt = Date.parse(retryAfter);
                if (Number.isFinite(seconds) && seconds > 0) {
                    error.retryAfterMs = seconds * 1000;
                } else if (Number.isFinite(retryAt)) {
                    error.retryAfterMs = Math.max(0, retryAt - Date.now());
                }
            }

            throw error;
        }

        return data;
    }

    const wait = (milliseconds) => new Promise((resolve) => {
        window.setTimeout(resolve, milliseconds);
    });

    async function requestSaveWithRetry(body) {
        const fallbackDelays = [1000, 2500, 5000];
        let retry = 0;

        while (true) {
            try {
                return await requestJson(urls.save, {
                    method: 'POST',
                    keepalive: true,
                    body: JSON.stringify(body),
                });
            } catch (error) {
                if (error.status !== 429 || retry >= fallbackDelays.length) {
                    if (error.status === 429) {
                        error.message = 'Сервер пока не принимает новые сохранения. Ответ остался на этой странице. Подождите немного и повторите действие.';
                    }
                    throw error;
                }

                const delay = Number.isFinite(error.retryAfterMs)
                    ? Math.max(1000, error.retryAfterMs)
                    : fallbackDelays[retry];

                setNotice(
                    `Сервер временно занят. Повторяем сохранение через ${Math.ceil(delay / 1000)} сек.`,
                    'muted'
                );

                retry += 1;
                await wait(delay);
            }
        }
    }

    /**
     * Все изменения сессии проходят через одну последовательную очередь.
     * Одинаковый запрос не отправляется повторно, если он уже сохранён
     * или такой же запрос уже ожидает выполнения.
     */
    function queueSave(key, body, notice = 'Сохраняем ответ…') {
        const snapshot = JSON.stringify(body);

        if (state.savedSnapshots.get(key) === snapshot) {
            state.dirtySaves.delete(key);
            return Promise.resolve({ ok: true, saved: true, skipped: true });
        }

        const existing = state.pendingSaves.get(key);
        if (existing && existing.snapshot === snapshot) {
            return existing.promise;
        }

        const entry = {
            snapshot,
            body: { ...body },
            started: false,
            promise: null,
        };

        state.dirtySaves.set(key, {
            snapshot,
            body: { ...body },
        });

        state.pendingSaveCount += 1;
        state.saving = true;

        if (notice) {
            setNotice(notice, 'muted');
        }

        const operation = state.saveQueue
            .catch(() => undefined)
            .then(async () => {
                // Пока запрос стоял в очереди, такой же снимок мог сохраниться.
                if (state.savedSnapshots.get(key) === snapshot) {
                    return { ok: true, saved: true, skipped: true };
                }

                entry.started = true;
                const result = await requestSaveWithRetry(body);
                state.savedSnapshots.set(key, snapshot);

                const dirty = state.dirtySaves.get(key);
                if (dirty && dirty.snapshot === snapshot) {
                    state.dirtySaves.delete(key);
                }

                return result;
            });

        entry.promise = operation.finally(() => {
            state.pendingSaveCount = Math.max(0, state.pendingSaveCount - 1);
            state.saving = state.pendingSaveCount > 0;

            if (state.pendingSaves.get(key) === entry) {
                state.pendingSaves.delete(key);
            }
        });

        state.pendingSaves.set(key, entry);
        state.saveQueue = entry.promise.catch(() => undefined);

        return entry.promise;
    }

    async function flushPendingSaves() {
        const dirtyEntries = Array.from(state.dirtySaves.entries());

        if (dirtyEntries.length) {
            const tasks = dirtyEntries.map(([key, entry]) => (
                queueSave(key, entry.body, null)
            ));

            await Promise.all(tasks);
        }

        await state.saveQueue;

        if (state.dirtySaves.size > 0) {
            throw new Error(
                'Не все ответы удалось сохранить. Проверьте соединение и повторите попытку.'
            );
        }
    }

    function sendUnloadFallback(key, body) {
        const snapshot = JSON.stringify(body);

        if (state.savedSnapshots.get(key) === snapshot) {
            return;
        }

        const pending = state.pendingSaves.get(key);
        // Обычный запрос уже начался и использует keepalive:true.
        if (pending && pending.snapshot === snapshot && pending.started) {
            return;
        }

        fetch(urls.save, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: snapshot,
        }).catch(() => {});
    }

    function escapeHtml(value) {
        return text(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        })[char]);
    }

    function setNotice(message, type = 'muted') {
        const notice = root.querySelector('[data-run-notice]');
        if (!notice) return;
        notice.textContent = message;
        notice.className = `patient-run-notice is-${type}`;
    }

    function currentType() {
        const type = text(state.payload?.test?.type || 'questionnaire').toLowerCase();
        return type === 'card_sort' ? 'sort' : type;
    }

    function safeColor(value) {
        const color = text(value);
        return /^#[0-9a-f]{3,8}$/i.test(color) ? color : '#dddddd';
    }

    function currentItems() {
        return state.payload?.items || [];
    }

    function currentCards() {
        return state.payload?.cards || [];
    }

    function currentSortCards() {
        return state.payload?.sort || [];
    }

    function answeredCount() {
        if (currentType() === 'image') {
            return currentCards().filter((card) => hasValue(state.answers[String(card.id)])).length;
        }
        if (currentType() === 'sort') {
            return state.sortOrder.length;
        }
        return currentItems().filter((item) => hasValue(state.answers[String(item.id)])).length;
    }

    function totalCount() {
        if (currentType() === 'image') return currentCards().length;
        if (currentType() === 'sort') return currentSortCards().length;
        return currentItems().length;
    }

    function renderShell() {
        const test = state.payload.test || {};
        root.innerHTML = `
            <div class="patient-run-heading">
                <div class="patient-run-eyebrow">${escapeHtml(test.code || 'ТЕСТИРОВАНИЕ')}</div>
                <h1>${escapeHtml(test.title || 'Тестирование')}</h1>
                ${test.description ? `<p class="patient-run-description">${escapeHtml(test.description)}</p>` : ''}
                ${test.instructions ? `<div class="patient-run-instructions">${escapeHtml(test.instructions)}</div>` : ''}
            </div>
            <div class="patient-run-progress">
                <div class="patient-run-progress__labels">
                    <span data-run-progress-label>Подготовка теста</span>
                    <span data-run-progress-count></span>
                </div>
                <div class="patient-run-progress__track"><div data-run-progress-bar></div></div>
            </div>
            <div data-run-notice class="patient-run-notice is-muted" role="status">Ответы сохраняются автоматически. Вы можете перейти в другой раздел и вернуться позже.</div>
            <div data-run-body></div>
            <div class="patient-run-footer">
                <button type="button" class="patient-run-button is-secondary" data-run-exit>
                    <i class="bi bi-arrow-left"></i> Сохранить и выйти
                </button>
                <button type="button" class="patient-run-button is-primary" data-run-finish>
                    Завершить тестирование
                </button>
            </div>
        `;

        root.querySelector('[data-run-exit]')?.addEventListener('click', async () => {
            try {
                await flushCurrent();
                await flushPendingSaves();
                await state.saveQueue;
            } catch (error) {
                setNotice(error.message, 'error');
                return;
            }
            window.location.href = urls.back;
        });

        root.querySelector('[data-run-finish]')?.addEventListener('click', finishTest);

        updateProgress();
    }

    function updateProgress() {
        const total = totalCount();
        const answered = answeredCount();
        const percent = total > 0 ? Math.round(answered / total * 100) : 0;

        const label = root.querySelector('[data-run-progress-label]');
        const count = root.querySelector('[data-run-progress-count]');
        const bar = root.querySelector('[data-run-progress-bar]');

        if (label) label.textContent = currentType() === 'sort'
            ? 'Расположите карточки в нужном порядке'
            : 'Прогресс прохождения';
        if (count) count.textContent = `${answered} из ${total}`;
        if (bar) bar.style.width = `${percent}%`;
    }

    function renderQuestionnaire() {
        const items = currentItems();
        const body = root.querySelector('[data-run-body]');

        if (!items.length) {
            body.innerHTML = '<div class="patient-run-empty">В этом тесте пока нет вопросов.</div>';
            return;
        }

        state.index = Math.max(0, Math.min(state.index, items.length - 1));
        const item = items[state.index];
        const answer = state.answers[String(item.id)] ?? '';
        const options = item.options || [];

        body.innerHTML = `
            <section class="patient-run-question">
                <div class="patient-run-question__top">
                    <span>ВОПРОС ${state.index + 1} ИЗ ${items.length}</span>
                    <span>${hasValue(answer) ? 'Ответ сохранён' : 'Ожидает ответа'}</span>
                </div>
                <h2>${escapeHtml(item.title || 'Вопрос')}</h2>
                ${item.help ? `<p class="patient-run-help">${escapeHtml(item.help)}</p>` : ''}
                ${options.length ? `
                    <div class="patient-run-options">
                        ${options.map((option, index) => `
                            <label class="patient-run-option">
                                <input
                                    type="radio"
                                    name="patient-run-answer"
                                    value="${escapeHtml(option.value)}"
                                    ${String(answer) === String(option.value) ? 'checked' : ''}
                                >
                                <span class="patient-run-option__marker"></span>
                                <span>${escapeHtml(option.label)}</span>
                            </label>
                        `).join('')}
                    </div>
                ` : `
                    <textarea
                        class="patient-run-textarea"
                        data-run-text
                        rows="5"
                        maxlength="20000"
                        placeholder="Введите ваш ответ..."
                    >${escapeHtml(answer)}</textarea>
                `}
            </section>
            <div class="patient-run-question-nav">
                <button type="button" class="patient-run-button is-secondary" data-run-prev ${state.index === 0 ? 'disabled' : ''}>
                    <i class="bi bi-chevron-left"></i> Назад
                </button>
                <span class="patient-run-nav-hint">Можно вернуться к предыдущим вопросам</span>
                <button type="button" class="patient-run-button is-primary" data-run-next ${state.index >= items.length - 1 ? 'disabled' : ''}>
                    Далее <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        `;

        body.querySelectorAll('input[name="patient-run-answer"]').forEach((input) => {
            input.addEventListener('change', async () => {
                state.answers[String(item.id)] = input.value;
                try {
                    await saveQuestion(item, input.value);
                    setNotice('Ответ сохранён', 'success');
                } catch (error) {
                    setNotice(error.message, 'error');
                }
                renderQuestionnaire();
            });
        });

        const textarea = body.querySelector('[data-run-text]');
        textarea?.addEventListener('input', () => {
            state.answers[String(item.id)] = textarea.value;
            setNotice('Сохраняем ответ…', 'muted');
            clearTimeout(state.saveTimer);
            state.saveTimer = setTimeout(async () => {
                try {
                    await saveQuestion(item, textarea.value);
                    setNotice('Ответ сохранён', 'success');
                } catch (error) {
                    setNotice(error.message, 'error');
                }
                updateProgress();
            }, 450);
            updateProgress();
        });

        body.querySelector('[data-run-prev]')?.addEventListener('click', async () => {
            try {
                await flushCurrent();
                await flushPendingSaves();
                state.index = Math.max(0, state.index - 1);
                await saveCurrentIndex();
                renderQuestionnaire();
                updateProgress();
            } catch (error) {
                setNotice(error.message, 'error');
            }
        });

        body.querySelector('[data-run-next]')?.addEventListener('click', async () => {
            try {
                await flushCurrent();
                await flushPendingSaves();
                state.index = Math.min(items.length - 1, state.index + 1);
                await saveCurrentIndex();
                renderQuestionnaire();
                updateProgress();
            } catch (error) {
                setNotice(error.message, 'error');
            }
        });

        updateProgress();
    }

    function renderImageCards() {
        const cards = currentCards();
        const body = root.querySelector('[data-run-body]');

        if (!cards.length) {
            body.innerHTML = '<div class="patient-run-empty">В этом тесте пока нет карточек.</div>';
            return;
        }

        state.index = Math.max(0, Math.min(state.index, cards.length - 1));
        const card = cards[state.index];
        const answer = state.answers[String(card.id)] ?? '';

        body.innerHTML = `
            <section class="patient-run-question">
                <div class="patient-run-question__top">
                    <span>КАРТОЧКА ${state.index + 1} ИЗ ${cards.length}</span>
                    <span>${hasValue(answer) ? 'Ответ сохранён' : 'Ожидает ответа'}</span>
                </div>
                <h2>${escapeHtml(card.title || `Карточка ${state.index + 1}`)}</h2>
                ${card.image_url ? `<div class="patient-run-image-wrap"><img src="${escapeHtml(card.image_url)}" alt="${escapeHtml(card.title || 'Изображение теста')}"></div>` : ''}
                ${card.question ? `<p class="patient-run-help">${escapeHtml(card.question)}</p>` : ''}
                <textarea class="patient-run-textarea" data-run-image-answer rows="5" maxlength="20000" placeholder="Введите ваш ответ...">${escapeHtml(answer)}</textarea>
            </section>
            <div class="patient-run-question-nav">
                <button type="button" class="patient-run-button is-secondary" data-run-prev ${state.index === 0 ? 'disabled' : ''}>
                    <i class="bi bi-chevron-left"></i> Назад
                </button>
                <span class="patient-run-nav-hint">Ответы на карточки сохраняются автоматически</span>
                <button type="button" class="patient-run-button is-primary" data-run-next ${state.index >= cards.length - 1 ? 'disabled' : ''}>
                    Далее <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        `;

        const textarea = body.querySelector('[data-run-image-answer]');
        textarea?.addEventListener('input', () => {
            state.answers[String(card.id)] = textarea.value;
            setNotice('Сохраняем ответ…', 'muted');
            clearTimeout(state.saveTimer);
            state.saveTimer = setTimeout(async () => {
                if (!hasValue(textarea.value)) {
                    updateProgress();
                    return;
                }
                try {
                    await saveImageCard(card, textarea.value);
                    setNotice('Ответ сохранён', 'success');
                } catch (error) {
                    setNotice(error.message, 'error');
                }
                updateProgress();
            }, 450);
            updateProgress();
        });

        body.querySelector('[data-run-prev]')?.addEventListener('click', async () => {
            try {
                await flushCurrent();
                await flushPendingSaves();
                state.index = Math.max(0, state.index - 1);
                await saveCurrentIndex();
                renderImageCards();
            } catch (error) {
                setNotice(error.message, 'error');
            }
        });

        body.querySelector('[data-run-next]')?.addEventListener('click', async () => {
            try {
                await flushCurrent();
                await flushPendingSaves();
                state.index = Math.min(cards.length - 1, state.index + 1);
                await saveCurrentIndex();
                renderImageCards();
            } catch (error) {
                setNotice(error.message, 'error');
            }
        });

        updateProgress();
    }

    function renderSortCards() {
        const cards = currentSortCards();
        const body = root.querySelector('[data-run-body]');

        if (!cards.length) {
            body.innerHTML = '<div class="patient-run-empty">В этом тесте пока нет карточек для сортировки.</div>';
            return;
        }

        const cardById = new Map(cards.map((card) => [String(card.id), card]));
        const ordered = state.sortOrder.map((id) => cardById.get(String(id))).filter(Boolean);

        body.innerHTML = `
            <section class="patient-run-question">
                <div class="patient-run-question__top">
                    <span>ПОРЯДОК КАРТОЧЕК</span>
                    <span>${ordered.length} из ${cards.length}</span>
                </div>
                <h2>${escapeHtml(state.payload.test?.instructions || 'Расположите карточки в нужном порядке')}</h2>
                <p class="patient-run-help">Используйте стрелки, чтобы перемещать карточки вверх или вниз. Порядок сохраняется автоматически.</p>
                <div class="patient-run-sort-list">
                    ${ordered.map((card, index) => `
                        <div class="patient-run-sort-item" data-sort-id="${escapeHtml(card.id)}">
                            <span class="patient-run-sort-item__number">${index + 1}</span>
                            ${card.type === 'image' && card.image_url
                                ? `<img src="${escapeHtml(card.image_url)}" alt="">`
                                : card.type === 'color'
                                    ? `<span class="patient-run-sort-color" style="background:${safeColor(card.color)}"></span>`
                                    : ''}
                            <span class="patient-run-sort-item__text">${escapeHtml(card.title || card.text || `Карточка ${index + 1}`)}</span>
                            <span class="patient-run-sort-item__actions">
                                <button type="button" data-sort-up="${escapeHtml(card.id)}" ${index === 0 ? 'disabled' : ''} aria-label="Переместить вверх"><i class="bi bi-chevron-up"></i></button>
                                <button type="button" data-sort-down="${escapeHtml(card.id)}" ${index === ordered.length - 1 ? 'disabled' : ''} aria-label="Переместить вниз"><i class="bi bi-chevron-down"></i></button>
                            </span>
                        </div>
                    `).join('')}
                </div>
            </section>
        `;

        body.querySelectorAll('[data-sort-up]').forEach((button) => {
            button.addEventListener('click', () => moveSortCard(button.dataset.sortUp, -1));
        });
        body.querySelectorAll('[data-sort-down]').forEach((button) => {
            button.addEventListener('click', () => moveSortCard(button.dataset.sortDown, 1));
        });

        updateProgress();
    }

    async function moveSortCard(id, direction) {
        const index = state.sortOrder.findIndex((value) => String(value) === String(id));
        const next = index + direction;
        if (index < 0 || next < 0 || next >= state.sortOrder.length) return;

        const order = [...state.sortOrder];
        [order[index], order[next]] = [order[next], order[index]];
        state.sortOrder = order;

        renderSortCards();
        try {
            await saveSortOrder();
            setNotice('Порядок сохранён', 'success');
        } catch (error) {
            setNotice(error.message, 'error');
        }
    }

    async function saveQuestion(item, answer) {
        clearTimeout(state.saveTimer);

        return queueSave(
            `question:${item.id}`,
            {
                item_id: item.id,
                answer: answer,
                current_index: state.index,
                stimulus_id: item.stimulus_id || null,
            }
        );
    }

    async function saveImageCard(card, answer) {
        clearTimeout(state.saveTimer);

        return queueSave(
            `image:${card.id}`,
            {
                card_id: card.id,
                answer: answer,
                current_index: state.index,
            }
        );
    }

    async function saveSortOrder() {
        return queueSave(
            'sort-order',
            {
                sort_order: [...state.sortOrder],
                current_index: state.sortOrder.length,
            },
            'Сохраняем порядок…'
        );
    }

    async function saveCurrentIndex() {
        const type = currentType();
        const total = type === 'image'
            ? currentCards().length
            : type === 'sort'
                ? currentSortCards().length
                : currentItems().length;
        const maxIndex = type === 'sort' ? total : Math.max(0, total - 1);
        const index = Math.max(0, Math.min(state.index, maxIndex));

        await queueSave(
            'current-index',
            { current_index: index },
            'Сохраняем позицию…'
        );

        if (state.payload?.state) {
            state.payload.state.current_index = index;
        }
    }

    async function flushCurrent() {
        clearTimeout(state.saveTimer);

        const type = currentType();
        if (type === 'questionnaire') {
            const item = currentItems()[state.index];
            if (!item) return;
            const value = state.answers[String(item.id)];
            if (hasValue(value)) {
                await saveQuestion(item, value);
            }
        } else if (type === 'image') {
            const card = currentCards()[state.index];
            if (!card) return;
            const value = state.answers[String(card.id)];
            if (hasValue(value)) {
                await saveImageCard(card, value);
            }
        } else if (type === 'sort') {
            await saveSortOrder();
        }
    }

    function firstMissingIndex() {
        const type = currentType();
        if (type === 'image') {
            return currentCards().findIndex((card) => !hasValue(state.answers[String(card.id)]));
        }
        if (type === 'sort') {
            return state.sortOrder.length === currentSortCards().length ? -1 : 0;
        }
        return currentItems().findIndex((item) => item.required !== false && !hasValue(state.answers[String(item.id)]));
    }

    async function finishTest() {
        if (state.finished) return;

        try {
            await flushCurrent();
            await flushPendingSaves();
            await state.saveQueue;

            const missing = firstMissingIndex();
            if (missing >= 0) {
                if (currentType() === 'questionnaire' || currentType() === 'image') {
                    state.index = missing;
                    currentType() === 'image' ? renderImageCards() : renderQuestionnaire();
                }
                setNotice('Заполните все обязательные вопросы перед завершением.', 'error');
                return;
            }

            const confirmed = window.confirm('Завершить тестирование? После отправки изменить ответы будет нельзя.');
            if (!confirmed) return;

            state.finished = true;
            const button = root.querySelector('[data-run-finish]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Отправляем…';
            }

            const result = await requestJson(urls.finish, {
                method: 'POST',
                body: JSON.stringify({}),
            });

            if (!result.ok) {
                throw new Error(result.message || 'Не удалось завершить тестирование.');
            }

            window.location.href = urls.result;
        } catch (error) {
            state.finished = false;
            const button = root.querySelector('[data-run-finish]');
            if (button) {
                button.disabled = false;
                button.textContent = 'Завершить тестирование';
            }
            setNotice(error.message, 'error');
        }
    }

    async function load() {
        try {
            const payload = await requestJson(urls.payload, {
                method: 'GET',
                headers: { 'Cache-Control': 'no-store' },
            });

            if (!payload.ok) throw new Error(payload.message || 'Не удалось загрузить тест.');

            state.payload = payload;
            state.answers = { ...(payload.state?.answers || {}) };
            state.index = Number(payload.state?.current_index || 0);
            const savedSortOrder = Array.isArray(payload.state?.sort_order)
                ? [...payload.state.sort_order]
                : [];
            state.sortOrder = savedSortOrder.length
                ? savedSortOrder
                : currentSortCards().map((card) => card.id);

            renderShell();

            if (currentType() === 'image') {
                renderImageCards();
            } else if (currentType() === 'sort') {
                renderSortCards();
            } else {
                renderQuestionnaire();
            }
        } catch (error) {
            root.innerHTML = `
                <div class="patient-run-error">
                    <div class="patient-run-error__icon"><i class="bi bi-exclamation-triangle"></i></div>
                    <h2>Не удалось открыть тестирование</h2>
                    <p>${escapeHtml(error.message)}</p>
                    <button type="button" class="patient-run-button is-primary" data-run-retry>Повторить</button>
                    <a class="patient-run-button is-secondary" href="${escapeHtml(urls.back)}">Вернуться к тестированиям</a>
                </div>
            `;
            root.querySelector('[data-run-retry]')?.addEventListener('click', load);
        }
    }

    // При закрытии страницы отправляем только действительно несохранённое
    // состояние. Основные запросы очереди уже используют keepalive:true.
    window.addEventListener('beforeunload', () => {
        clearTimeout(state.saveTimer);

        const type = currentType();

        if (type === 'questionnaire') {
            const item = currentItems()[state.index];
            const answer = item ? state.answers[String(item.id)] : null;

            if (item && hasValue(answer)) {
                sendUnloadFallback(
                    `question:${item.id}`,
                    {
                        item_id: item.id,
                        answer,
                        current_index: state.index,
                        stimulus_id: item.stimulus_id || null,
                    }
                );
            }
        } else if (type === 'image') {
            const card = currentCards()[state.index];
            const answer = card ? state.answers[String(card.id)] : null;

            if (card && hasValue(answer)) {
                sendUnloadFallback(
                    `image:${card.id}`,
                    {
                        card_id: card.id,
                        answer,
                        current_index: state.index,
                    }
                );
            }
        } else if (type === 'sort' && state.sortOrder.length) {
            sendUnloadFallback(
                'sort-order',
                {
                    sort_order: [...state.sortOrder],
                    current_index: state.sortOrder.length,
                }
            );
        }
    });

    load();
})();
