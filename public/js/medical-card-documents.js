/* Отдельный модуль. Не меняет обработчики существующей medical_card.js. */
(() => {
    'use strict';
    if (window.__medicalCardDocumentsBound) return;
    window.__medicalCardDocumentsBound = true;

    const templates = {
        stage: 'ЭТАПНЫЙ ЭПИКРИЗ\n\nПериод наблюдения: \n\nЖалобы: \n\nАнамнез и течение заболевания: \n\nПсихический и соматический статус: \n\nРезультаты обследований: \n\nПроведённое лечение: \n\nДинамика состояния: \n\nКлинический диагноз: \n\nПлан дальнейшего лечения и наблюдения: ',
        transfer: 'ПЕРЕВОДНОЙ ЭПИКРИЗ\n\nПериод лечения: \n\nКуда переводится пациент: \n\nПричина перевода: \n\nЖалобы и анамнез: \n\nОбследования и результаты: \n\nПроведённое лечение: \n\nСостояние на момент перевода: \n\nКлинический диагноз: \n\nАктуальная терапия (проверено врачом): \n\nРекомендации принимающему врачу: ',
        discharge: 'ВЫПИСНОЙ ЭПИКРИЗ\n\nПериод лечения: \n\nПричина обращения / поступления: \n\nЖалобы и анамнез: \n\nСостояние при начале лечения: \n\nРезультаты обследований: \n\nКлинический диагноз: \n\nПроведённое лечение и динамика: \n\nСостояние при выписке: \n\nИсход лечения: \n\nРекомендации и план наблюдения: ',
    };

    function initForm() {
        const form = document.getElementById('mcdForm');
        if (!form) return;
        let dirty = false;
        let pending = null;
        const errorBox = form.querySelector('[data-mcd-sources-error]');
        const preview = form.querySelector('[data-mcd-sources-preview]');
        const count = form.querySelector('[data-mcd-preview-count]');
        const previewButton = form.querySelector('[data-mcd-preview-sources]');
        const period = form.querySelector('[data-mcd-period-mode]');
        const periodRange = form.querySelector('[data-mcd-period-range]');
        const diagnosis = form.querySelector('[data-mcd-diagnosis-toggle]');
        const diagnosisFields = form.querySelector('[data-mcd-diagnosis-fields]');

        function syncPeriod() {
            const range = period?.value === 'range';
            if (periodRange) periodRange.hidden = !range;
            periodRange?.querySelectorAll('input').forEach(input => {
                input.disabled = !range;
                input.required = range;
            });
        }
        function syncDiagnosis() {
            if (diagnosisFields) diagnosisFields.hidden = !diagnosis?.checked;
        }
        syncPeriod();
        syncDiagnosis();
        period?.addEventListener('change', syncPeriod);
        diagnosis?.addEventListener('change', syncDiagnosis);
        form.addEventListener('input', () => { dirty = true; });
        form.addEventListener('change', event => {
            dirty = true;
            if (['issued_at', 'period_mode', 'period_from', 'period_to', 'source_options[]'].includes(event.target.name)) {
                pending?.abort();
                preview.replaceChildren();
                count.textContent = 'Параметры изменены. Обновите просмотр записей.';
            }
        });
        window.addEventListener('beforeunload', event => {
            if (!dirty) return;
            event.preventDefault();
            event.returnValue = '';
        });
        form.addEventListener('submit', event => {
            const intent = event.submitter?.value || 'draft';
            if (intent === 'final') {
                if (!form.querySelector('input[name="reviewed"]')?.checked) {
                    event.preventDefault();
                    window.alert('Перед оформлением подтвердите проверку документа.');
                    return;
                }
                if (!window.confirm('Оформить документ? После оформления его нельзя будет изменить.')) {
                    event.preventDefault();
                    return;
                }
            }
            dirty = false;
            // submitter НЕ отключаем: его name/value должны попасть в POST.
            form.setAttribute('aria-busy', 'true');
            setTimeout(() => form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; }), 0);
        });
        window.addEventListener('pageshow', () => {
            form.removeAttribute('aria-busy');
            form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = false; });
        });

        previewButton?.addEventListener('click', async () => {
            pending?.abort();
            const controller = new AbortController();
            pending = controller;
            errorBox.hidden = true;
            previewButton.disabled = true;
            count.textContent = 'Загрузка записей…';
            const all = new FormData(form);
            const data = new FormData();
            ['_token', 'issued_at', 'period_mode', 'period_from', 'period_to'].forEach(key => {
                if (all.has(key)) data.set(key, all.get(key));
            });
            all.getAll('source_options[]').forEach(value => data.append('source_options[]', value));
            try {
                const response = await fetch(form.dataset.mcdSourcesUrl, {
                    method: 'POST', credentials: 'same-origin', signal: controller.signal,
                    headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    body: data,
                });
                const isJson = (response.headers.get('content-type') || '').includes('application/json');
                const result = isJson ? await response.json() : null;
                if (!response.ok || !result || typeof result.html !== 'string') {
                    const details = result?.errors ? Object.values(result.errors).flat().join('\n') : '';
                    throw new Error(details || (response.status === 419
                        ? 'Сессия истекла. Скопируйте несохранённый текст и обновите страницу.'
                        : result?.message || 'Не удалось загрузить записи. Проверьте доступ и обновите страницу.'));
                }
                if (pending !== controller || controller.signal.aborted) return;
                // HTML приходит только от своего endpoint: Blade экранирует каждое значение.
                preview.innerHTML = result.html;
                count.textContent = `Записей: ${result.count}`;
            } catch (error) {
                if (error.name === 'AbortError') return;
                errorBox.textContent = error.message;
                errorBox.hidden = false;
                count.textContent = '';
            } finally {
                if (pending === controller) {
                    previewButton.disabled = false;
                    pending = null;
                }
            }
        });
    }

    function bindEpicrisisTemplates() {
        document.addEventListener('click', event => {
            const button = event.target.closest('[data-mcd-epicrisis-template]');
            if (!button) return;
            const modal = document.getElementById('modalAddEpicrisis');
            const view = modal?.__epicrisisDocView;
            const errorBox = modal?.querySelector('[data-mcd-epicrisis-error]');
            if (errorBox) errorBox.hidden = true;
            const text = templates[button.dataset.mcdEpicrisisTemplate];
            if (!text) return;
            if (!view) {
                if (errorBox) {
                    errorBox.textContent = 'Редактор эпикриза ещё не готов. Дождитесь загрузки существующей medical_card.js.';
                    errorBox.hidden = false;
                }
                return;
            }
            if (view.state.doc.toString().trim() && !window.confirm('Заменить текст эпикриза выбранной структурой?')) return;
            view.dispatch({ changes: {from: 0, to: view.state.doc.length, insert: text} });
            view.focus();
        });
    }

    function openLegacyEpicrisis() {
        const query = new URLSearchParams(window.location.search);
        if (query.get('tab') !== 'epicrisis') return;
        const tab = document.querySelector('#cardTabs [data-bs-target="#pane-epicrisis"]');
        const modal = document.getElementById('modalAddEpicrisis');
        if (!tab) return;
        if (window.bootstrap?.Tab) window.bootstrap.Tab.getOrCreateInstance(tab).show();
        else tab.click();
        if (query.get('open_epicrisis') !== '1') return;
        const open = () => {
            if (modal && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(modal).show();
            else document.querySelector('#pane-epicrisis [data-bs-target="#modalAddEpicrisis"]')?.click();
        };
        // Открытие после обработчиков load / DOMContentLoaded существующей медкарты.
        setTimeout(open, 150);
    }

    function boot() {
        initForm();
        bindEpicrisisTemplates();
        document.querySelectorAll('[data-mcd-print]').forEach(button => {
            button.addEventListener('click', () => window.print());
        });
        if (document.body?.hasAttribute('data-mcd-autoprint')) {
            const printWhenReady = () => {
                const fontsReady = document.fonts?.ready || Promise.resolve();
                fontsReady.then(() => setTimeout(() => window.print(), 100));
            };
            if (document.readyState === 'complete') printWhenReady();
            else window.addEventListener('load', printWhenReady, {once: true});
        }
        if (document.readyState === 'complete') openLegacyEpicrisis();
        else window.addEventListener('load', openLegacyEpicrisis, {once: true});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, {once: true});
    else boot();
})();
