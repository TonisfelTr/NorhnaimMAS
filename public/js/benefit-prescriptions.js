/* Paper 148-1/у-04(л). Intentionally independent of the legacy prescription submitter. */
(() => {
    'use strict';

    function boot() {
        const module = document.getElementById('benefitPrescriptionModule');
        const form = document.getElementById('patientPrescriptionForm');
        const modal = document.getElementById('prescriptionModal');
        if (!module || !form || !modal || module.dataset.bound === '1') return;
        module.dataset.bound = '1';

        const panel = document.getElementById('benefitPrescriptionPanel');
        const mode = document.getElementById('benefitPrescriptionMode');
        const fieldset = document.getElementById('benefitPrescriptionFieldset');
        const actions = document.getElementById('benefitPrescriptionActions');
        const errors = document.getElementById('benefitPrescriptionErrors');
        const feedback = document.getElementById('benefitPrescriptionFeedback');
        const main = module.parentElement;
        const side = document.getElementById('prescriptionSuggestionsCard');
        const draftButton = document.getElementById('benefitSaveDraft');
        const issueButton = document.getElementById('benefitIssue');
        const newButton = document.getElementById('benefitNewDraft');
        const busyIndicator = document.getElementById('benefitPrescriptionBusy');
        const savedClinical = document.getElementById('benefitSavedClinical');
        const canWrite = module.dataset.canWrite === '1';
        const canIssue = module.dataset.canIssue === '1';
        if (!panel || !mode || !fieldset || !actions || !errors || !feedback) return;

        // The switch precedes the picker. The longer benefit details span the modal
        // below the existing picker/suggestions grid, inside the same form.
        const clinicalSections = Array.from(main.children).filter(child => child !== module);
        clinicalSections.forEach(section => section.classList.add('benefit-rx-legacy-section'));
        const clinicalLayout = main.closest('.presc-layout');
        if (clinicalLayout && form.contains(clinicalLayout.parentElement)) clinicalLayout.after(panel);
        else main.append(panel);
        const clinicalFields = clinicalSections.flatMap(section => Array.from(section.querySelectorAll('input, select, textarea, button')));
        const disabledBeforeLock = new Map();
        const defaults = new Map(Array.from(fieldset.querySelectorAll('[name]')).map(input => [input.name, input.value]));
        const originalNoValidate = form.noValidate;
        const baseIds = {
            drug_id: 'drug_id_modal', drug_form: 'drug_form_modal', dosage: 'dosage_modal',
            quantity: 'quantity_modal', standard: 'standard_modal',
        };
        const confirmations = new Set([
            'eligibility_confirmed', 'nsu_medicines_retained', 'clinical_confirmed',
            'companion_confirmed', 'extended_validity_confirmed',
        ]);
        const state = {
            active: false, busy: false, status: 'new', savedClinical: false, drug: null,
            drugSequence: 0, drugAbort: null, drugPromise: null, lastDrugId: '',
            lastAutoRp: '', editing: false, editSequence: 0,
        };

        function input(name) {
            return form.elements.namedItem(`benefit[${name}]`);
        }
        function value(name) {
            return String(input(name)?.value ?? '').trim();
        }
        function setValue(name, next) {
            const control = input(name);
            if (control) control.value = next == null ? '' : String(next);
        }
        function uncheck(name) {
            const control = input(name);
            if (control) control.checked = false;
        }
        function finalized() {
            return state.status === 'issued' || state.status === 'void';
        }
        function node(tag, className, text) {
            const element = document.createElement(tag);
            if (className) element.className = className;
            if (text != null) element.textContent = String(text);
            return element;
        }
        function safeUrl(raw) {
            if (!raw || typeof raw !== 'string') return null;
            try {
                const url = new URL(raw, window.location.href);
                return url.origin === window.location.origin && ['http:', 'https:'].includes(url.protocol) ? url : null;
            } catch (_) {
                return null;
            }
        }
        function uuid() {
            if (window.crypto?.randomUUID) return window.crypto.randomUUID();
            if (!window.crypto?.getRandomValues) return '';
            const bytes = window.crypto.getRandomValues(new Uint8Array(16));
            bytes[6] = (bytes[6] & 15) | 64;
            bytes[8] = (bytes[8] & 63) | 128;
            const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
            return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
        }
        function selectedText(id) {
            const control = document.getElementById(id);
            return String(control?.selectedOptions?.[0]?.textContent ?? control?.value ?? '').trim();
        }
        function hasCompanion() {
            return value('companion_form') !== '' && value('companion_form') !== 'none';
        }
        function conditional(id, show) {
            const container = document.getElementById(id);
            if (!container) return;
            container.hidden = !show;
            container.querySelectorAll('input, textarea, select').forEach(control => {
                control.disabled = !show;
                if (!show && control.type === 'checkbox') control.checked = false;
            });
        }
        function syncConditions() {
            conditional('benefitPaymentPercentWrap', value('payment') === '3');
            conditional('benefitNsuWrap', value('program') === 'federal_nsu');
            const paired = hasCompanion();
            conditional('benefitCompanionWrap', paired);
            const days = input('validity_days');
            if (paired) days.value = '15';
            else if (days.value === '15') days.value = '30';
            Array.from(days.options).forEach(option => {
                option.disabled = paired ? option.value !== '15' : option.value === '15';
            });
            const extended = !paired && days.value === '90';
            document.getElementById('benefitValidityBasisWrap').hidden = !extended;
            Array.from(input('validity_basis').options).forEach(option => {
                const isOrdinary = option.value === 'standard' || option.value === 'controlled';
                option.hidden = extended && isOrdinary;
                option.disabled = extended && isOrdinary;
            });
            if (!extended) setValue('validity_basis', paired ? 'controlled' : 'standard');
            else if (['standard', 'controlled'].includes(value('validity_basis'))) setValue('validity_basis', '');
            conditional('benefitExtendedValidityWrap', extended);
        }
        function syncLockedClinical() {
            const locked = state.active && (state.savedClinical || finalized());
            form.classList.toggle('benefit-clinical-readonly', locked);
            clinicalFields.forEach(control => {
                if (locked) {
                    if (!disabledBeforeLock.has(control)) disabledBeforeLock.set(control, control.disabled);
                    control.disabled = true;
                } else if (disabledBeforeLock.has(control)) {
                    control.disabled = disabledBeforeLock.get(control);
                    disabledBeforeLock.delete(control);
                }
            });
            clinicalSections.forEach(section => { section.inert = locked; });
            savedClinical.hidden = !locked;
        }
        function updateUi() {
            mode.checked = state.active;
            mode.disabled = !canWrite || state.busy || state.editing;
            mode.setAttribute('aria-expanded', String(state.active));
            panel.hidden = !state.active;
            actions.hidden = !state.active;
            fieldset.disabled = !state.active || !canWrite || state.busy || state.editing || finalized();
            form.classList.toggle('benefit-mode-active', state.active);
            form.noValidate = state.active ? true : originalNoValidate;
            if (draftButton) {
                draftButton.hidden = finalized();
                draftButton.disabled = !canWrite || state.busy || state.editing;
            }
            if (issueButton) {
                issueButton.hidden = finalized();
                issueButton.disabled = !canIssue || state.busy || state.editing;
            }
            if (newButton) {
                newButton.hidden = state.status === 'new';
                newButton.disabled = !canWrite || state.busy || state.editing;
            }
            if (busyIndicator) busyIndicator.hidden = !state.busy && !state.editing;
            if (side) side.inert = state.busy || (state.active && (state.savedClinical || finalized()));
            // Protect a submitted snapshot while its response is in flight.
            main.inert = state.busy || state.editing;
            syncLockedClinical();
        }
        function activate(next, load = true) {
            if (next && !canWrite) return;
            state.active = next;
            syncConditions();
            updateUi();
            if (next && load && !state.savedClinical && !finalized()) {
                state.drugPromise = loadDrug();
            }
        }
        function clearErrors() {
            errors.replaceChildren();
            errors.hidden = true;
            form.querySelectorAll('[data-benefit-invalid]').forEach(control => {
                control.classList.remove('is-invalid');
                control.removeAttribute('aria-invalid');
                control.removeAttribute('data-benefit-invalid');
            });
        }
        function focusControl(control) {
            if (!control) return;
            let details = control.closest('details');
            while (details) {
                details.open = true;
                details = details.parentElement?.closest('details');
            }
            if (control.type === 'hidden' || control.disabled) {
                errors.focus();
                return;
            }
            control.focus();
            control.scrollIntoView?.({ block: 'center', behavior: 'smooth' });
        }
        function showErrors(payload, fallback) {
            clearErrors();
            errors.hidden = false;
            errors.append(node('strong', '', payload?.message || fallback || 'Не удалось сохранить рецепт.'));
            const entries = payload?.errors && typeof payload.errors === 'object' ? Object.entries(payload.errors) : [];
            if (entries.length) {
                const list = node('ul', 'mb-0 mt-2');
                entries.forEach(([path, messages]) => {
                    const name = path.startsWith('benefit.') ? `benefit[${path.slice(8)}]` : path;
                    const control = form.elements.namedItem(name);
                    if (control && typeof control.setAttribute === 'function') {
                        control.classList.add('is-invalid');
                        control.setAttribute('aria-invalid', 'true');
                        control.setAttribute('data-benefit-invalid', '1');
                    }
                    (Array.isArray(messages) ? messages : [messages]).forEach(message => {
                        const item = node('li');
                        if (control && typeof control.focus === 'function') {
                            const button = node('button', 'benefit-rx-error-link', message);
                            button.type = 'button';
                            button.addEventListener('click', () => focusControl(control));
                            item.append(button);
                        } else item.textContent = String(message);
                        list.append(item);
                    });
                });
                errors.append(list);
            }
            errors.focus();
            errors.scrollIntoView?.({ block: 'nearest', behavior: 'smooth' });
        }
        function note(text) {
            feedback.replaceChildren(node('p', 'mb-0', text));
            feedback.hidden = false;
        }
        async function jsonRequest(rawUrl, options = {}) {
            const url = safeUrl(rawUrl);
            if (!url) throw { message: 'Адрес операции недоступен. Обновите медкарту.' };
            let response;
            try {
                response = await fetch(url.href, {
                    credentials: 'same-origin', cache: 'no-store', ...options,
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {}) },
                });
            } catch (error) {
                if (error?.name === 'AbortError') throw error;
                throw { message: 'Соединение прервалось. Повторите сохранение: тот же запрос не должен создавать второй рецепт.' };
            }
            let data = null;
            try { data = await response.json(); } catch (_) { /* Use a status-specific message below. */ }
            if ([401, 419].includes(response.status) || response.redirected) {
                throw { message: 'Сессия истекла или требуется вход. Сохранённые черновики останутся в медкарте.' };
            }
            if (!response.ok) {
                throw data && typeof data === 'object' ? data : {
                    message: response.status === 403 ? 'Нет права на эту операцию с рецептом.' : 'Операция не выполнена. Обновите медкарту и проверьте состояние рецепта.',
                };
            }
            if (!data || typeof data !== 'object') throw { message: 'Сервер вернул неожиданный ответ. Проверьте сохранение в медкарте.' };
            return data;
        }
        function prefillRp() {
            if (state.savedClinical || finalized() || !state.drug) return;
            const control = input('rp');
            if (control.value.trim() && control.value !== state.lastAutoRp) return;
            const text = [selectedText(baseIds.drug_form), state.drug.generic_name || state.drug.name, selectedText(baseIds.dosage)]
                .filter(part => part && !part.startsWith('Выберите')).join(' ');
            // No automatic Latin declension or silent truncation of a drug name.
            if (text.length > 240) return;
            control.value = text;
            state.lastAutoRp = text;
        }
        async function loadDrug(force = false) {
            if (!state.active || state.savedClinical || finalized()) return;
            const id = String(document.getElementById(baseIds.drug_id)?.value || '').trim();
            if (!force && state.lastDrugId === id && state.drug) {
                prefillRp();
                return;
            }
            const sequence = ++state.drugSequence;
            state.drugAbort?.abort();
            state.drugAbort = new AbortController();
            state.lastDrugId = id;
            state.drug = null;
            const status = document.getElementById('benefitDrugStatus');
            document.getElementById('benefitStrictNotice').hidden = true;
            if (!id) {
                status.textContent = 'Выберите препарат в назначении выше. Черновик можно сохранить и без него.';
                return;
            }
            const url = safeUrl(module.dataset.drugUrl);
            if (!url) {
                status.textContent = 'Сведения о препарате недоступны. Проверьте заполнение текста назначения.';
                return;
            }
            url.searchParams.set('drug_id', id);
            status.textContent = 'Получаем сведения о выбранном препарате…';
            try {
                const drug = await jsonRequest(url.href, { signal: state.drugAbort.signal });
                if (sequence !== state.drugSequence || state.savedClinical || finalized()) return;
                state.drug = drug;
                status.textContent = drug.name ? `Выбран препарат: ${drug.name}. Проверьте текст перед печатью.` : 'Проверьте текст назначения перед печатью.';
                document.getElementById('benefitStrictNotice').hidden = ![true, 1, '1'].includes(drug.strict);
                prefillRp();
            } catch (error) {
                if (sequence !== state.drugSequence || error?.name === 'AbortError') return;
                status.textContent = 'Не удалось получить сведения о препарате. Повторите выбор или проверьте текст назначения вручную.';
            }
        }
        function fillRegimen() {
            if (state.savedClinical || finalized()) return;
            const taking = document.getElementById('taking_drug_modal');
            const frequency = document.getElementById('taking_count_modal');
            const unit = document.getElementById('drug_type_modal')?.textContent?.trim() || '';
            const daysText = document.getElementById('days_count_modal')?.textContent?.trim() || '';
            const days = /^\d+$/.test(daysText) && Number(daysText) > 0 && Number(daysText) <= 180 ? daysText : '';
            const units = taking && !taking.disabled && taking.value ? `${taking.value} ${unit}`.trim() : '';
            const count = frequency && !frequency.disabled ? frequency.value : '';
            const meal = document.getElementById('taking_time_meal_modal');
            const mealText = meal && !meal.disabled ? selectedText('taking_time_meal_modal') : '';
            const generated = units && count
                ? `По ${units}, ${count} раз(а) в день${mealText ? ` ${mealText} еды` : ''}${days ? `, курс ${days} дней` : ''}.`
                : '';
            // Legacy code can populate its hidden instructions only on submit;
            // derive this explicit suggestion from the visible current controls.
            const candidates = { treatment_days: days, doses_per_day: count, units_per_dose: units, signa: generated };
            let changed = false;
            Object.entries(candidates).forEach(([name, next]) => {
                const control = input(name);
                if (control && !control.value.trim() && next && (control.maxLength < 0 || next.length <= control.maxLength)) {
                    control.value = next;
                    changed = true;
                }
            });
            prefillRp();
            if (changed) uncheck('clinical_confirmed');
            note(changed ? 'Пустые поля заполнены по текущей схеме. Проверьте путь введения и текст назначения.' : 'Заполненные поля сохранены. Недостающие сведения можно ввести вручную.');
        }
        function baseSnapshot() {
            return Object.fromEntries(Object.entries(baseIds).map(([name, id]) => [name, String(document.getElementById(id)?.value || '')]));
        }
        function clearClinicalSelection() {
            // Let the existing picker fetch a fresh catalogue item through its normal flow.
            // Do not manufacture Select2 metadata from a saved prescription snapshot.
            const drug = document.getElementById(baseIds.drug_id);
            if (drug) drug.value = '';
            ['drug_form', 'dosage', 'quantity', 'standard'].forEach(name => {
                const control = document.getElementById(baseIds[name]);
                if (!control) return;
                control.value = name === 'standard' ? '1' : '';
                control.disabled = true;
            });
            ['taking_drug_modal', 'taking_count_modal', 'taking_time_meal_modal'].forEach(id => {
                const control = document.getElementById(id);
                if (control) control.disabled = true;
            });
            const hiddenSigna = document.getElementById('usage_instructions__');
            if (hiddenSigna) hiddenSigna.value = '';
            const days = document.getElementById('days_count_modal');
            if (days) days.textContent = '—';
            state.drug = null;
            state.lastDrugId = '';
            if (drug) drug.dispatchEvent(new Event('change', { bubbles: true }));
        }
        function showClinical(base, drug, title = 'Назначение из черновика') {
            savedClinical.querySelector('.benefit-rx-eyebrow').textContent = title;
            document.getElementById('benefitSavedClinicalName').textContent = drug?.name || drug?.generic_name || 'Препарат ещё не выбран';
            const parts = [];
            if (base.drug_form) parts.push(String(base.drug_form));
            if (base.dosage) parts.push(`дозировка: ${base.dosage}`);
            if (base.quantity) parts.push(`количество формы: ${base.quantity}`);
            if (base.standard) parts.push(`стандартов: ${base.standard}`);
            document.getElementById('benefitSavedClinicalDetails').textContent = parts.join(' · ');
        }
        function payload(action) {
            const data = new FormData();
            const csrf = form.querySelector('input[name="_token"]')?.value;
            if (csrf) data.set('_token', csrf);
            if (!state.savedClinical) {
                Object.entries(baseSnapshot()).forEach(([name, current]) => data.set(name, current));
            }
            fieldset.querySelectorAll('[name]').forEach(control => {
                if (!control.name.startsWith('benefit[') || control.disabled) return;
                if ((control.type === 'checkbox' || control.type === 'radio') && !control.checked) return;
                data.set(control.name, control.value);
            });
            data.set('benefit[action]', action);
            data.set('benefit[use_saved_clinical]', state.savedClinical ? '1' : '0');
            data.set('benefit[snils]', String(data.get('benefit[snils]') || '').replace(/[\s-]/g, ''));
            return data;
        }
        function refreshLink(rawUrl) {
            const url = safeUrl(rawUrl);
            if (!url) return null;
            url.searchParams.set('benefit_page', '1');
            const link = node('a', 'benefit-rx-text-link', 'Обновить список рецептов');
            link.href = url.href;
            return link;
        }
        function updateRecordList(result, sent) {
            const records = document.getElementById('benefitPrescriptionRecords');
            if (!records || !result.record?.id) return;
            let article = Array.from(records.querySelectorAll('[data-benefit-record]')).find(row => row.dataset.benefitRecord === String(result.record.id));
            const isNew = !article;
            if (!article) article = node('article', 'benefit-rx-record');
            const record = result.record;
            article.dataset.benefitRecord = String(record.id);
            article.dataset.version = String(record.version);
            article.dataset.status = record.status;
            article.replaceChildren();
            const body = node('div', 'benefit-rx-record__main');
            const tags = node('div', 'benefit-rx-record__tags');
            const status = node('span', `benefit-rx-status benefit-rx-status--${record.status}`, record.status === 'issued' ? 'Готов к печати' : 'Черновик');
            status.setAttribute('data-benefit-record-status', '');
            tags.append(status, node('span', 'benefit-rx-record__number', record.status === 'issued'
                ? `Серия ${sent.get('benefit[series]') || ''} · № ${sent.get('benefit[number]') || ''}` : 'Номер не присвоен'));
            body.append(tags, node('h4', 'benefit-rx-record__name', state.drug?.name || value('rp') || 'Назначение не заполнено'), node('div', 'benefit-rx-record__meta', 'Сохранено в этой сессии'));
            const buttons = node('div', 'benefit-rx-record__actions');
            const editUrl = safeUrl(result.edit_url);
            if (record.status === 'draft' && editUrl) {
                const edit = node('button', 'btn btn-sm btn-outline-primary', 'Открыть черновик');
                edit.type = 'button';
                edit.dataset.benefitEditUrl = editUrl.href;
                edit.dataset.bsToggle = 'modal';
                edit.dataset.bsTarget = '#prescriptionModal';
                buttons.append(edit);
            }
            const printUrl = safeUrl(result.print_url);
            if (record.status === 'issued' && printUrl) {
                const print = node('a', 'btn btn-sm btn-outline-primary', 'Печать');
                print.href = printUrl.href;
                print.target = '_blank';
                print.rel = 'noopener';
                print.setAttribute('data-benefit-record-print', '');
                buttons.append(print);
            }
            article.append(body, buttons);
            if (isNew) records.prepend(article);
            records.querySelector('[data-benefit-empty]')?.remove();
            const listFeedback = document.getElementById('benefitListFeedback');
            if (listFeedback) {
                listFeedback.replaceChildren(node('span', '', 'Рецепт сохранён. '));
                const link = refreshLink(result.card_url);
                if (link) listFeedback.append(link);
                listFeedback.hidden = false;
            }
            // Pagination counts were prepared before this mutation.
            const paginationCount = document.querySelector('#benefitPrescriptionList .benefit-rx-pagination > span');
            if (paginationCount) paginationCount.hidden = true;
        }
        function renderSuccess(result, popup) {
            feedback.replaceChildren(node('p', 'mb-0', result.message || (state.status === 'issued' ? 'Рецепт сохранён; бланк готов к печати.' : 'Черновик сохранён.')));
            feedback.hidden = false;
            const printUrl = safeUrl(result.print_url);
            if (printUrl && state.status === 'issued') {
                const link = node('a', 'btn btn-sm btn-primary mt-2', 'Открыть бланк для печати');
                link.href = printUrl.href;
                link.target = '_blank';
                link.rel = 'noopener';
                feedback.append(link);
                if (popup && !popup.closed) popup.location.replace(printUrl.href);
                else feedback.append(node('p', 'benefit-rx-help mt-2 mb-0', 'Если новая вкладка не открылась, нажмите «Открыть бланк для печати».'));
            } else if (popup && !popup.closed) popup.close();
        }
        async function save(action) {
            if (!state.active || state.busy || state.editing || finalized()) return;
            if (!canWrite || (action === 'issue' && !canIssue)) {
                showErrors(null, action === 'issue' ? 'Оформление бланков для этой клиники пока недоступно. Можно сохранить черновик.' : 'Нет права на сохранение рецепта.');
                return;
            }
            clearErrors();
            let popup = null;
            if (action === 'issue') {
                popup = window.open('', '_blank');
                if (popup) {
                    popup.opener = null;
                    popup.document.title = 'Подготовка рецепта';
                    popup.document.body.append(node('p', '', 'Проверяем реквизиты и готовим сохранённый бланк…'));
                }
            }
            state.busy = true;
            updateUi();
            try {
                if (state.drugPromise) await state.drugPromise;
                const sent = payload(action);
                const clinical = baseSnapshot();
                const result = await jsonRequest(module.dataset.storeUrl, { method: 'POST', body: sent });
                if (!result.record?.id || !['draft', 'issued'].includes(result.record.status)) {
                    throw { message: 'Получен неполный ответ. Проверьте список рецептов перед повторным сохранением.' };
                }
                setValue('id', result.record.id);
                setValue('version', result.record.version);
                state.status = result.record.status;
                if (finalized() && !state.savedClinical) showClinical(clinical, state.drug, 'Сохранённое назначение');
                updateRecordList(result, sent);
                renderSuccess(result, popup);
            } catch (error) {
                if (popup && !popup.closed) popup.close();
                showErrors(error, 'Не удалось сохранить рецепт.');
            } finally {
                state.busy = false;
                updateUi();
            }
        }
        function resetNew(clearClinical = false) {
            state.drugAbort?.abort();
            state.drugSequence++;
            state.drugPromise = null;
            state.drug = null;
            state.lastDrugId = '';
            state.lastAutoRp = '';
            state.status = 'new';
            state.savedClinical = false;
            state.editing = false;
            fieldset.querySelectorAll('[name]').forEach(control => {
                if (control.type === 'checkbox') control.checked = false;
                else control.value = defaults.get(control.name) || '';
            });
            setValue('id', '');
            setValue('version', '');
            setValue('use_saved_clinical', '0');
            setValue('request_key', uuid());
            clearErrors();
            feedback.replaceChildren();
            feedback.hidden = true;
            syncConditions();
            updateUi();
            if (clearClinical) clearClinicalSelection();
            if (!value('request_key')) showErrors(null, 'Не удалось подготовить новый запрос. Обновите медкарту перед сохранением.');
        }
        function waitForModal() {
            if (modal.classList.contains('show')) return Promise.resolve();
            return new Promise(resolve => {
                let timer;
                const done = () => {
                    modal.removeEventListener('shown.bs.modal', done);
                    window.clearTimeout(timer);
                    resolve();
                };
                modal.addEventListener('shown.bs.modal', done, { once: true });
                timer = window.setTimeout(done, 1800);
            });
        }
        async function edit(button) {
            if (state.busy || state.editing || !canWrite) return;
            const sequence = ++state.editSequence;
            const shown = waitForModal();
            state.editing = true;
            state.active = true;
            updateUi();
            try {
                const [data] = await Promise.all([jsonRequest(button.dataset.benefitEditUrl), shown]);
                if (sequence !== state.editSequence) return;
                if (!data.benefit?.id || !data.base) throw { message: 'Данные черновика недоступны. Обновите список.' };
                resetNew();
                Object.entries(data.benefit).forEach(([name, next]) => {
                    if (!confirmations.has(name)) setValue(name, next);
                });
                confirmations.forEach(uncheck);
                state.status = 'draft';
                const completeClinical = Boolean(data.drug) && Object.keys(baseIds).every(name => String(data.base[name] ?? '').trim() !== '');
                state.savedClinical = completeClinical;
                setValue('use_saved_clinical', completeClinical ? '1' : '0');
                if (completeClinical) {
                    state.drug = data.drug;
                    showClinical(data.base, data.drug);
                    document.getElementById('benefitStrictNotice').hidden = ![true, 1, '1'].includes(data.drug.strict);
                    document.getElementById('benefitDrugStatus').textContent = 'Используется препарат из сохранённого черновика.';
                } else {
                    clearClinicalSelection();
                    document.getElementById('benefitStrictNotice').hidden = true;
                }
                state.active = true;
                syncConditions();
                note(completeClinical
                    ? 'Черновик открыт. Перед оформлением повторно подтвердите право на льготу и назначение.'
                    : 'В черновике нет полного назначения или препарат недоступен. Выберите препарат выше и заполните его параметры. Сохранённый текст Rp., D.t.d. и Signa необходимо сверить с новым выбором.');
            } catch (error) {
                if (sequence === state.editSequence) showErrors(error, 'Не удалось открыть черновик.');
            } finally {
                if (sequence === state.editSequence) {
                    state.editing = false;
                    updateUi();
                }
            }
        }
        async function voidRecord(button) {
            const article = button.closest('[data-benefit-record]');
            const panel = article?.querySelector('[data-benefit-void-panel]');
            if (!article || !panel || button.disabled) return;
            const reason = panel.querySelector('[data-benefit-void-reason]');
            const error = panel.querySelector('[data-benefit-void-error]');
            if (!reason?.value.trim() || reason.value.trim().length < 5) {
                error.textContent = 'Опишите причину аннулирования — не менее пяти символов.';
                error.hidden = false;
                reason?.focus();
                return;
            }
            const data = new FormData();
            data.set('_token', form.querySelector('input[name="_token"]')?.value || '');
            data.set('version', article.dataset.version || '');
            data.set('reason', reason.value.trim());
            const controls = Array.from(panel.querySelectorAll('button, textarea'));
            controls.forEach(control => { control.disabled = true; });
            error.hidden = true;
            try {
                const result = await jsonRequest(button.dataset.benefitVoidUrl, { method: 'POST', body: data });
                article.dataset.status = 'void';
                if (result.record?.version != null) article.dataset.version = String(result.record.version);
                const status = article.querySelector('[data-benefit-record-status]');
                if (status) {
                    status.textContent = 'Аннулирован';
                    status.className = 'benefit-rx-status benefit-rx-status--void';
                }
                article.querySelectorAll('[data-benefit-record-print], [data-benefit-void-toggle]').forEach(control => control.remove());
                panel.replaceChildren(node('p', 'benefit-rx-help mb-0', result.message || 'Рецепт аннулирован. Номер сохранён в журнале.'));
                if (value('id') === article.dataset.benefitRecord) {
                    state.status = 'void';
                    note('Этот рецепт аннулирован. Для нового назначения создайте новый рецепт.');
                    updateUi();
                }
            } catch (failure) {
                const reasonErrors = failure?.errors?.reason;
                error.textContent = Array.isArray(reasonErrors) && reasonErrors.length
                    ? reasonErrors.join(' ')
                    : (failure?.message || 'Не удалось аннулировать рецепт. Обновите список и проверьте его состояние.');
                error.hidden = false;
                controls.forEach(control => { control.disabled = false; });
            }
        }

        mode.addEventListener('change', () => activate(mode.checked));
        draftButton?.addEventListener('click', () => save('draft'));
        issueButton?.addEventListener('click', () => save('issue'));
        newButton?.addEventListener('click', () => { resetNew(true); activate(true); });
        document.getElementById('benefitFillRegimen')?.addEventListener('click', fillRegimen);
        fieldset.addEventListener('change', event => {
            const name = event.target.name;
            if (name === 'benefit[program]') {
                const funding = { federal_nsu: '1', regional: '2', municipal: '3' }[value('program')];
                if (funding) setValue('funding', funding);
                if (value('program') === 'federal_nsu') setValue('payment', '1');
                uncheck('eligibility_confirmed');
                uncheck('nsu_medicines_retained');
            }
            if (name === 'benefit[companion_form]') {
                uncheck('companion_confirmed');
                uncheck('extended_validity_confirmed');
            }
            if (['benefit[validity_days]', 'benefit[validity_basis]'].includes(name)) uncheck('extended_validity_confirmed');
            syncConditions();
        });
        fieldset.addEventListener('input', event => {
            const name = event.target.name || '';
            if (['program', 'category_code', 'eligibility_basis', 'funding', 'payment', 'payment_percent'].some(field => name === `benefit[${field}]`)) {
                uncheck('eligibility_confirmed');
            }
            if (['rp', 'dtd', 'signa', 'treatment_days', 'doses_per_day', 'units_per_dose', 'diagnosis_code', 'companion_form'].some(field => name === `benefit[${field}]`)) {
                uncheck('clinical_confirmed');
            }
            if (name === 'benefit[companion_reference]') uncheck('companion_confirmed');
        });
        function clinicalChanged(event) {
            if (!state.active || state.savedClinical || finalized() || state.busy) return;
            if (!event.target?.closest('.benefit-rx-legacy-section')) return;
            uncheck('clinical_confirmed');
            state.drugPromise = loadDrug();
        }
        form.addEventListener('change', clinicalChanged);
        // Select2 may emit only a jQuery event; all data still comes from the server.
        if (window.jQuery) window.jQuery(form).on('change.benefitPrescription', 'select', clinicalChanged);

        // Run before unknown legacy target/document handlers. Never alter ordinary-mode submits.
        window.addEventListener('submit', event => {
            if (event.target !== form || !state.active) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (!state.busy) note('Используйте «Сохранить черновик» или «Сохранить и открыть бланк».');
        }, true);
        window.addEventListener('click', event => {
            if (!state.active || !(event.target instanceof Element)) return;
            if (!event.target.closest('#printRecipeButton_modal, #prescriptionSubmitButton')) return;
            event.preventDefault();
            event.stopImmediatePropagation();
        }, true);
        window.addEventListener('reset', event => {
            if (event.target !== form) return;
            if (state.active || state.busy || state.editing) {
                // Legacy show/hide handlers must not erase an unsaved benefit form.
                event.preventDefault();
                event.stopImmediatePropagation();
            } else window.setTimeout(() => {
                // Bootstrap/legacy code may have started opening an edit since reset fired.
                if (state.active || state.busy || state.editing) return;
                resetNew();
                activate(false, false);
            }, 0);
        }, true);
        document.addEventListener('click', event => {
            if (!(event.target instanceof Element)) return;
            const editButton = event.target.closest('[data-benefit-edit-url]');
            if (editButton) {
                // Bootstrap's data API opens the same modal after this listener.
                edit(editButton);
                return;
            }
            const toggle = event.target.closest('[data-benefit-void-toggle]');
            if (toggle) {
                const article = toggle.closest('[data-benefit-record]');
                const section = article?.querySelector('[data-benefit-void-panel]');
                if (!section) return;
                section.hidden = !section.hidden;
                article.querySelectorAll('[data-benefit-void-toggle]').forEach(button => button.setAttribute('aria-expanded', String(!section.hidden)));
                if (!section.hidden) section.querySelector('textarea')?.focus();
                return;
            }
            const voidButton = event.target.closest('[data-benefit-void-url]');
            if (voidButton) voidRecord(voidButton);
        });
        modal.addEventListener('shown.bs.modal', () => {
            if (state.active && !state.editing) {
                updateUi();
                if (!state.savedClinical && !finalized()) state.drugPromise = loadDrug();
            }
        });
        activate(false, false);
        if (new URLSearchParams(window.location.search).has('benefit_page')) {
            const tab = document.querySelector('[data-bs-toggle="tab"][data-bs-target="#pane-presc"]');
            if (tab) window.setTimeout(() => tab.click(), 0);
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
    else boot();
})();
