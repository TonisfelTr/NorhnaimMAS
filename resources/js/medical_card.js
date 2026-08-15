import * as bootstrap from 'bootstrap';
import { EditorState, EditorSelection, StateEffect, StateField } from '@codemirror/state';
import {
    Decoration,
    EditorView,
    drawSelection,
    dropCursor,
    keymap,
} from '@codemirror/view';
import {
    defaultKeymap,
    history,
    historyKeymap,
    redo,
    undo,
} from '@codemirror/commands';

window.bootstrap = bootstrap;

const qs = (selector, root = document) => root?.querySelector?.(selector) || null;
const qsa = (selector, root = document) => Array.from(root?.querySelectorAll?.(selector) || []);
const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';
const toArray = value => Array.isArray(value) ? value : [];
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
}[char]));

function safeJsonParse(value, fallback = null) {
    if (value == null || value === '') return fallback;
    if (typeof value !== 'string') return value;

    try {
        return JSON.parse(value);
    } catch {
        return fallback;
    }
}

function debounce(fn, delay = 250) {
    let timer = null;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function openBootstrapTab(target) {
    const button = qs(`[data-bs-toggle="tab"][data-bs-target="${target}"]`);

    if (!button) {
        return;
    }

    bootstrap.Tab.getOrCreateInstance(button).show();
}

function getBootstrapModal(element) {
    if (!element) {
        return null;
    }

    return bootstrap.Modal.getOrCreateInstance(element);
}

function openResearchModal(trigger, modalSelector) {
    if (!trigger) {
        return;
    }

    const modal = qs(modalSelector);

    if (!modal) {
        console.warn(
            `Модальное окно ${modalSelector} не найдено`
        );

        return;
    }

    const labsTabButton = qs(
        '[data-bs-toggle="tab"][data-bs-target="#pane-labs"]'
    );

    const labsPane = qs('#pane-labs');

    const showModal = () => {
        getBootstrapModal(modal)?.show(trigger);
    };

    if (
        labsPane?.classList.contains('active')
        && labsPane?.classList.contains('show')
    ) {
        setTimeout(showModal, 0);

        return;
    }

    if (!labsTabButton) {
        setTimeout(showModal, 0);

        return;
    }

    labsTabButton.addEventListener(
        'shown.bs.tab',
        showModal,
        { once: true }
    );

    bootstrap.Tab.getOrCreateInstance(
        labsTabButton
    ).show();
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        },
        ...options
    });

    const contentType = response.headers.get('content-type') || '';
    const payload = contentType.includes('application/json')
        ? await response.json().catch(() => null)
        : await response.text().catch(() => null);

    if (!response.ok) {
        const message = typeof payload === 'object' && payload?.message
            ? payload.message
            : `HTTP ${response.status}`;

        throw new Error(message);
    }

    return payload;
}

async function fetchHtml(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        },
        ...options
    });

    const text = await response.text();

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return text;
}

function showToast(message = 'Сохранено', type = 'success') {
    const toastElement = qs('#toastSaved');

    if (!toastElement) {
        return;
    }

    const body = qs('.toast-body', toastElement);

    if (body) {
        body.textContent = message;
    }

    toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');
    toastElement.classList.add(`text-bg-${type}`);

    bootstrap.Toast.getOrCreateInstance(toastElement).show();
}

function printHtml(html) {
    const iframe = document.createElement('iframe');

    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';

    document.body.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    iframe.onload = () => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => iframe.remove(), 500);
    };
}

function initTooltips() {
    qsa('[data-bs-toggle="tooltip"]').forEach(element => {
        bootstrap.Tooltip.getOrCreateInstance(element);
    });
}

function initConfirmForms() {
    qsa('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            const message = form.dataset.confirm || 'Подтвердите действие.';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

function initDefaultTab() {
    const root = qs('#medicalCardRoot');
    const params = new URLSearchParams(window.location.search);
    const fromSession = root?.dataset?.activeTab;
    const fromQuery = params.get('tab');
    const fromHash = window.location.hash;

    if (fromSession) {
        openBootstrapTab(`#pane-${fromSession}`);
        return;
    }

    if (fromQuery) {
        openBootstrapTab(`#pane-${fromQuery}`);
        return;
    }

    if (fromHash && qs(fromHash)) {
        openBootstrapTab(fromHash);
        return;
    }

    const firstTab = qs('#cardTabs [data-bs-toggle="tab"]');

    if (firstTab) {
        bootstrap.Tab.getOrCreateInstance(firstTab).show();
    }
}

function initContactsTabState() {
    const flag = 'mc:openContactsTab';

    qsa('[data-contact-form]').forEach(form => {
        form.addEventListener('submit', () => sessionStorage.setItem(flag, '1'));
    });

    if (sessionStorage.getItem(flag) === '1') {
        sessionStorage.removeItem(flag);
        setTimeout(() => openBootstrapTab('#pane-contacts'), 50);
    }
}

function initLabOrderReopen() {
    const flag = 'mc:reopenLabOrder';
    const modal = qs('#modalAddLabOrder');
    const form = modal?.querySelector('form.modal-content');

    form?.addEventListener('submit', event => {
        if (event.submitter?.name === 'save_and_new') {
            sessionStorage.setItem(flag, '1');
        } else {
            sessionStorage.removeItem(flag);
        }
    });

    if (document.querySelector('.modal[data-open-on-error="1"]')) {
        sessionStorage.removeItem(flag);
        return;
    }

    if (sessionStorage.getItem(flag) !== '1') {
        return;
    }

    sessionStorage.removeItem(flag);

    setTimeout(() => {
        openBootstrapTab('#pane-labs');
        getBootstrapModal(modal)?.show();
    }, 50);
}

function initModalValidationErrors() {
    const modal = document.querySelector(
        '.modal[data-open-on-error="1"]'
    );

    if (!modal) {
        return false;
    }

    /*
     * Ошибка формы важнее deep-link на просмотр исследования.
     * Удаляем параметры до события window.load, чтобы встроенный
     * Blade-скрипт тоже не открыл старую модалку результата.
     */
    const url = new URL(window.location.href);
    url.searchParams.delete('research');
    url.searchParams.delete('instrumental_research');

    window.history.replaceState(
        {},
        '',
        url.pathname + url.search + url.hash
    );

    const tabTarget = modal.dataset.errorTab;

    if (tabTarget) {
        openBootstrapTab(tabTarget);
    }

    window.setTimeout(() => {
        document.querySelectorAll('.modal.show').forEach(openModal => {
            if (openModal !== modal) {
                getBootstrapModal(openModal)?.hide();
            }
        });

        getBootstrapModal(modal)?.show();
    }, 75);

    return true;
}

function normalizeParam(item) {
    if (typeof item === 'number' || typeof item === 'string') {
        return {
            id: item,
            name: `Параметр #${item}`,
            unit: '',
            group: '',
            min: null,
            max: null,
            ref: '',
            criticalLow: null,
            criticalHigh: null,
            criticalRef: '',
            value: ''
        };
    }

    item = item || {};

    const nested =
        item.parameter ||
        item.lab_parameter ||
        item.labParameter ||
        item.lab_param ||
        item.param ||
        item.analysis_parameter ||
        null;

    const id =
        item.id ??
        item.param_id ??
        item.parameter_id ??
        item.lab_parameter_id ??
        item.lab_param_id ??
        item.labParameterId ??
        item.parameterId ??
        item.analysis_parameter_id ??
        nested?.id ??
        nested?.param_id ??
        nested?.parameter_id ??
        nested?.lab_parameter_id ??
        null;

    const name =
        item.name ??
        item.title ??
        item.text ??
        item.label ??
        item.caption ??
        item.param_name ??
        item.parameter_name ??
        item.lab_parameter_name ??
        item.lab_param_name ??
        item.analysis_parameter_name ??
        nested?.name ??
        nested?.title ??
        nested?.label ??
        nested?.caption ??
        '';

    const unit =
        item.unit ??
        item.units ??
        item.unit_name ??
        item.measure ??
        item.measurement_unit ??
        item.measure_unit ??
        item.ed ??
        item.ed_izm ??
        item.unit_title ??
        nested?.unit ??
        nested?.units ??
        nested?.unit_name ??
        nested?.measure ??
        nested?.measurement_unit ??
        '';

    const min =
        item.min ??
        item.ref_min ??
        item.reference_min ??
        item.normal_min ??
        item.min_value ??
        item.value_min ??
        item.lower ??
        item.lower_bound ??
        item.from ??
        nested?.min ??
        nested?.ref_min ??
        nested?.reference_min ??
        nested?.normal_min ??
        nested?.min_value ??
        nested?.lower_bound ??
        null;

    const max =
        item.max ??
        item.ref_max ??
        item.reference_max ??
        item.normal_max ??
        item.max_value ??
        item.value_max ??
        item.upper ??
        item.upper_bound ??
        item.to ??
        nested?.max ??
        nested?.ref_max ??
        nested?.reference_max ??
        nested?.normal_max ??
        nested?.max_value ??
        nested?.upper_bound ??
        null;

    const ref =
        item.ref ??
        item.reference ??
        item.reference_interval ??
        item.ref_interval ??
        item.normal_range ??
        item.norm ??
        item.norma ??
        item.range ??
        item.reference_range ??
        item.ref_text ??
        item.normal_text ??
        nested?.ref ??
        nested?.reference ??
        nested?.reference_interval ??
        nested?.ref_interval ??
        nested?.normal_range ??
        nested?.reference_range ??
        '';

    const criticalLow =
        item.critical_low ??
        item.criticalLow ??
        item.critical_min ??
        item.criticalMin ??
        nested?.critical_low ??
        nested?.criticalLow ??
        nested?.critical_min ??
        nested?.criticalMin ??
        null;

    const criticalHigh =
        item.critical_high ??
        item.criticalHigh ??
        item.critical_max ??
        item.criticalMax ??
        nested?.critical_high ??
        nested?.criticalHigh ??
        nested?.critical_max ??
        nested?.criticalMax ??
        null;

    const criticalRef =
        item.critical_ref ??
        item.criticalRef ??
        item.critical_reference ??
        nested?.critical_ref ??
        nested?.criticalRef ??
        nested?.critical_reference ??
        '';

    return {
        id,
        name: name || (id ? `Параметр #${id}` : ''),
        unit: unit || '',
        group:
            item.group ??
            item.group_code ??
            item.category ??
            nested?.group ??
            nested?.group_code ??
            '',
        min,
        max,
        ref,
        criticalLow,
        criticalHigh,
        criticalRef,
        value:
            item.result_value ??
            item.value ??
            item.result ??
            item.numeric_value ??
            item.text_value ??
            item.answer ??
            ''
    };
}

function getResultValue(payload) {
    if (payload == null) {
        return '';
    }

    if (typeof payload !== 'object') {
        return payload;
    }

    return payload.value
        ?? payload.result
        ?? payload.result_value
        ?? payload.numeric_value
        ?? payload.text_value
        ?? '';
}

function isMissingLabValue(value) {
    if (value == null) {
        return true;
    }

    const normalized = String(value).trim();

    return normalized === ''
        || normalized === '—'
        || normalized === '-';
}

function getDatasetParams(button) {
    return safeJsonParse(button?.dataset?.params, null)
        ?? safeJsonParse(button?.dataset?.paramIds, []);
}

function mergeParamWithResult(rawParam, values) {
    const param = normalizeParam(rawParam);
    const resultPayload = values?.[param.id] ?? values?.[String(param.id)] ?? null;

    if (resultPayload && typeof resultPayload === 'object') {
        const resultParam = normalizeParam(resultPayload);

        return {
            ...param,
            name: param.name && !String(param.name).startsWith('Параметр #') ? param.name : resultParam.name,
            unit: param.unit || resultParam.unit,
            group: param.group || resultParam.group,
            min: param.min ?? resultParam.min,
            max: param.max ?? resultParam.max,
            ref: param.ref || resultParam.ref,
            criticalLow: param.criticalLow ?? resultParam.criticalLow ?? null,
            criticalHigh: param.criticalHigh ?? resultParam.criticalHigh ?? null,
            criticalRef: param.criticalRef || resultParam.criticalRef || '',
            value: getResultValue(resultPayload)
        };
    }

    const resultValue = getResultValue(resultPayload);

    return {
        ...param,
        value: resultValue !== ''
            ? resultValue
            : (param.value ?? '')
    };
}

function getRefText(param) {
    if (param.ref) {
        return param.ref;
    }

    if (param.min != null && param.max != null) {
        return `${param.min}–${param.max}`;
    }

    if (param.min != null) {
        return `от ${param.min}`;
    }

    if (param.max != null) {
        return `до ${param.max}`;
    }

    return '—';
}

function getCriticalRefText(param) {
    if (param.criticalRef) {
        return param.criticalRef;
    }

    const parts = [];

    if (param.criticalLow != null) {
        parts.push(`≤ ${param.criticalLow}`);
    }

    if (param.criticalHigh != null) {
        parts.push(`≥ ${param.criticalHigh}`);
    }

    return parts.join(' или ');
}


function evaluateParamFlag(value, param) {
    const normalized = String(value ?? '')
        .replace(/\s+/g, '')
        .replace(',', '.')
        .trim();

    if (!normalized) {
        return {
            kind: 'empty',
            text: '—',
            className: 'lab-param-flag lab-param-flag--empty bg-light text-dark',
            title: ''
        };
    }

    const number = Number(normalized);

    if (!Number.isFinite(number)) {
        return {
            kind: 'text',
            text: 'текст',
            className: 'lab-param-flag lab-param-flag--text bg-light text-dark',
            title: ''
        };
    }

    const criticalLow = param.criticalLow != null
        ? Number(String(param.criticalLow).replace(',', '.'))
        : null;

    const criticalHigh = param.criticalHigh != null
        ? Number(String(param.criticalHigh).replace(',', '.'))
        : null;

    if (
        Number.isFinite(criticalLow)
        && number <= criticalLow
    ) {
        return {
            kind: 'critical',
            text: 'критично низкий',
            className: 'lab-param-flag lab-param-flag--critical bg-danger',
            title: param.criticalRef
                ? `Критический порог: ${param.criticalRef}`
                : ''
        };
    }

    if (
        Number.isFinite(criticalHigh)
        && number >= criticalHigh
    ) {
        return {
            kind: 'critical',
            text: 'критично высокий',
            className: 'lab-param-flag lab-param-flag--critical bg-danger',
            title: param.criticalRef
                ? `Критический порог: ${param.criticalRef}`
                : ''
        };
    }

    const min = param.min != null
        ? Number(String(param.min).replace(',', '.'))
        : null;

    const max = param.max != null
        ? Number(String(param.max).replace(',', '.'))
        : null;

    if (
        Number.isFinite(min)
        && number < min
    ) {
        return {
            kind: 'deviation',
            text: 'ниже нормы',
            className: 'lab-param-flag lab-param-flag--deviation bg-warning text-dark',
            title: ''
        };
    }

    if (
        Number.isFinite(max)
        && number > max
    ) {
        return {
            kind: 'deviation',
            text: 'выше нормы',
            className: 'lab-param-flag lab-param-flag--deviation bg-warning text-dark',
            title: ''
        };
    }

    if (
        !Number.isFinite(min)
        && !Number.isFinite(max)
    ) {
        return {
            kind: 'unknown',
            text: 'нет референса',
            className: 'lab-param-flag lab-param-flag--unknown bg-secondary',
            title: ''
        };
    }

    return {
        kind: 'normal',
        text: 'норма',
        className: 'lab-param-flag lab-param-flag--normal bg-success',
        title: ''
    };
}

const paramDetailsCache = new Map();

function hasRealParamName(param) {
    const name = String(param?.name || '').trim();
    return name !== '' && !name.startsWith('Параметр #');
}

function withCommonParamFilters(url, modal) {
    const sampleType = qs('#sampleType', modal)?.value;

    if (sampleType) {
        url.searchParams.set('sample_type', sampleType);
        url.searchParams.set('sampleType', sampleType);
        url.searchParams.set('sample', sampleType);
    }

    if (modal?.dataset?.gender) {
        url.searchParams.set('gender', modal.dataset.gender);
    }

    if (modal?.dataset?.sex) {
        url.searchParams.set('sex', modal.dataset.sex);
    }

    if (modal?.dataset?.age) {
        url.searchParams.set('age', modal.dataset.age);
    }

    return url;
}

async function resolveParamsBatch(ids, apiUrl, modal = null) {
    const uniqueIds = Array.from(new Set(
        toArray(ids)
            .map(id => {
                const p = normalizeParam(id);
                return p.id;
            })
            .filter(id => id !== null && id !== undefined && id !== '')
            .map(id => String(id))
    ));

    const result = new Map();
    const missingIds = [];

    uniqueIds.forEach(id => {
        if (paramDetailsCache.has(id)) {
            result.set(id, paramDetailsCache.get(id));
        } else {
            missingIds.push(id);
        }
    });

    if (!missingIds.length || !apiUrl) {
        return result;
    }

    try {
        const url = withCommonParamFilters(new URL(apiUrl, window.location.origin), modal);

        missingIds.forEach(id => {
            url.searchParams.append('ids[]', id);
        });

        url.searchParams.set('ids', missingIds.join(','));

        const data = await fetchJson(url.toString());

        const items = toArray(data?.results || data?.data || data)
            .map(normalizeParam)
            .filter(item => item.id !== null && item.id !== undefined && item.id !== '');

        items.forEach(item => {
            const key = String(item.id);
            paramDetailsCache.set(key, item);
            result.set(key, item);
        });

        missingIds.forEach(id => {
            if (!result.has(id)) {
                const fallback = normalizeParam(id);
                paramDetailsCache.set(id, fallback);
                result.set(id, fallback);
            }
        });

        return result;
    } catch (e) {
        missingIds.forEach(id => {
            const fallback = normalizeParam(id);
            paramDetailsCache.set(id, fallback);
            result.set(id, fallback);
        });

        return result;
    }
}

async function hydrateParams(params, apiUrl, modal = null) {
    const normalized = toArray(params).map(normalizeParam);

    const idsForLoad = normalized
        .filter(param => param.id && !hasRealParamName(param))
        .map(param => param.id);

    const resolvedMap = await resolveParamsBatch(idsForLoad, apiUrl, modal);

    return normalized.map(param => {
        if (!param.id) {
            return param;
        }

        const resolved = resolvedMap.get(String(param.id));

        if (!resolved) {
            return param;
        }

        return {
            ...param,
            name: hasRealParamName(param)
                ? param.name
                : resolved.name || param.name || `Параметр #${param.id}`,

            unit: param.unit || resolved.unit || '',

            group: param.group || resolved.group || '',

            min: param.min ?? resolved.min ?? null,
            max: param.max ?? resolved.max ?? null,

            ref: param.ref || resolved.ref || '',

            criticalLow:
                param.criticalLow
                ?? resolved.criticalLow
                ?? null,

            criticalHigh:
                param.criticalHigh
                ?? resolved.criticalHigh
                ?? null,

            criticalRef:
                param.criticalRef
                || resolved.criticalRef
                || ''
        };
    });
}
function initLabParamSearch() {
    const modal = qs('#modalAddLabOrder');

    if (!modal) {
        return;
    }

    const search = qs('#paramSearch', modal);
    const menu = qs('#paramMenu', modal);
    const group = qs('#paramGroup', modal);
    const table = qs('#orderParamsTable, #selParamsTable', modal);
    const tbody = qs('tbody', table);
    const counter = qs('#orderSelCount, #selCount', modal);
    const apiUrl = modal.dataset.apiParams;
    const selected = new Map();

    if (!search || !menu || !tbody || !apiUrl) {
        return;
    }

    function updateCounter() {
        if (counter) {
            counter.textContent = `(${selected.size})`;
        }
    }

    function renderSelected() {
        tbody.innerHTML = Array.from(selected.values()).map(param => `
            <tr data-param-id="${escapeHtml(param.id)}">
                <td>
                    <input type="hidden" name="param_ids[]" value="${escapeHtml(param.id)}">
                    <div class="fw-semibold">${escapeHtml(param.name)}</div>
                    ${param.group ? `<div class="small text-muted">${escapeHtml(param.group)}</div>` : ''}
                </td>
                <td>${escapeHtml(getRefText(param))}</td>
                <td>${escapeHtml(param.unit || '—')}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-link p-1 text-danger" data-remove-param="${escapeHtml(param.id)}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        updateCounter();
    }

    async function searchParams() {
        const url = new URL(apiUrl, window.location.origin);
        url.searchParams.set('q', search.value || '');

        if (group?.value) {
            url.searchParams.set('group', group.value);
        }

        withCommonParamFilters(url, modal);

        const data = await fetchJson(url.toString());

        return toArray(data?.results || data?.data || data)
            .map(normalizeParam)
            .filter(item => item.id);
    }

    function renderMenu(items) {
        if (!items.length) {
            menu.innerHTML = '<div class="dropdown-item text-muted small">Ничего не найдено</div>';
            menu.classList.add('show');
            return;
        }

        menu.innerHTML = items.slice(0, 30).map(param => `
            <button type="button" class="dropdown-item" data-param='${escapeHtml(JSON.stringify(param))}'>
                <span class="fw-semibold">${escapeHtml(param.name || `Параметр #${param.id}`)}</span>
                <span class="text-muted small ms-1">${escapeHtml(param.unit || '')}</span>
            </button>
        `).join('');

        menu.classList.add('show');
    }

    const runSearch = debounce(async () => {
        try {
            renderMenu(await searchParams());
        } catch {
            menu.innerHTML = '<div class="dropdown-item text-danger small">Ошибка поиска</div>';
            menu.classList.add('show');
        }
    }, 250);

    search.addEventListener('focus', runSearch);
    search.addEventListener('input', runSearch);
    group?.addEventListener('change', runSearch);

    menu.addEventListener('click', event => {
        const button = event.target.closest('[data-param]');

        if (!button) {
            return;
        }

        const param = safeJsonParse(button.dataset.param);

        if (param?.id && !selected.has(String(param.id))) {
            selected.set(String(param.id), normalizeParam(param));
            renderSelected();
        }

        search.value = '';
        menu.classList.remove('show');
    });

    tbody.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-param]');

        if (!button) {
            return;
        }

        selected.delete(String(button.dataset.removeParam));
        renderSelected();
    });

    document.addEventListener('click', event => {
        if (!modal.contains(event.target)) {
            menu.classList.remove('show');
        }
    });

    const templates = safeJsonParse(modal.dataset.templates, {});
    const templateSelect = qs('#orderTplSelect', modal);
    const applyTemplate = qs('#btnOrderTplApply', modal);
    const templateName = qs('#orderTplName', modal);
    const saveTemplate = qs('#btnOrderTplSave', modal);

    applyTemplate?.addEventListener('click', async () => {
        const rawTemplateParams = toArray(templates?.[templateSelect?.value] || []);

        if (!rawTemplateParams.length) {
            return;
        }

        applyTemplate.disabled = true;

        try {
            const resolved = await hydrateParams(rawTemplateParams, apiUrl, modal);

            selected.clear();

            resolved.forEach(param => {
                if (!param.id) {
                    return;
                }

                selected.set(String(param.id), param);
            });

            renderSelected();
        } finally {
            applyTemplate.disabled = false;
        }
    });

    saveTemplate?.addEventListener('click', async () => {
        const url = modal.dataset.apiSaveTemplate;

        if (!url || !templateName?.value.trim() || selected.size === 0) {
            return;
        }

        try {
            await fetchJson(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({
                    name: templateName.value.trim(),
                    lab_parameters: Array.from(selected.keys()).map(Number)
                })
            });

            showToast('Шаблон сохранён');
        } catch (error) {
            alert(`Не удалось сохранить шаблон: ${error.message}`);
        }
    });
}

function initLabResultModal() {
    const modal = qs('#modalAddLab');

    if (!modal) {
        return;
    }

    const form = qs('form.modal-content', modal);
    const tbody = qs('#resultParamsTable tbody, #selParamsTable tbody', modal);
    const counter = qs('#resultSelCount, #selCount', modal);
    const orderId = qs('#resultOrderId', modal);
    const hideNormal = qs('#toggleHideNormal', modal);

    let params = [];

    function updateCounter() {
        if (counter) {
            counter.textContent = `(${params.length})`;
        }
    }

    function render() {
        if (!tbody) {
            return;
        }

        tbody.innerHTML = params.map(param => {
            const flag = evaluateParamFlag(param.value, param);

            return `
                <tr data-param-id="${escapeHtml(param.id)}"
                    class="lab-result-row lab-result-row--${escapeHtml(flag.kind)} ${hideNormal?.checked && flag.kind === 'normal' ? 'd-none' : ''}">
                    <td>
                        <input type="hidden" name="param_ids[]" value="${escapeHtml(param.id)}">
                        <div class="fw-semibold">${escapeHtml(param.name)}</div>
                    </td>
                    <td>
                        <div>${escapeHtml(getRefText(param))}</div>
                        ${getCriticalRefText(param)
                ? `<div class="lab-critical-reference">Крит.: ${escapeHtml(getCriticalRefText(param))}</div>`
                : ''}
                    </td>
                    <td>${escapeHtml(param.unit || '—')}</td>
                    <td>
                        <input class="form-control form-control-sm" name="values[${escapeHtml(param.id)}]" value="${escapeHtml(param.value || '')}" data-result-value="${escapeHtml(param.id)}">
                    </td>
                    <td class="text-center">
                        <span class="badge ${flag.className}"
                              title="${escapeHtml(flag.title || '')}">
                            ${escapeHtml(flag.text)}
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-link p-1 text-danger" data-remove-result-param="${escapeHtml(param.id)}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        updateCounter();
    }

    modal.addEventListener('show.bs.modal', async event => {
        const button = event.relatedTarget;

        if (!button?.matches('[data-lab-edit]')) {
            return;
        }

        const id = button.dataset.id;

        if (orderId) {
            orderId.value = id || '';
        }

        if (form && modal.dataset.updateUrl && id) {
            form.action = modal.dataset.updateUrl.replace('__ID__', id);
        }

        const values = safeJsonParse(button.dataset.values, {}) || {};
        params = toArray(getDatasetParams(button)).map(item => mergeParamWithResult(item, values));
        render();

        params = await hydrateParams(params, modal.dataset.apiParams, modal);

        const collected = qs('[name="collected_at"]', modal);
        const status = qs('[name="status"]', modal);
        const lab = qs('[name="lab_name"]', modal);
        const comment = qs('[name="comment"]', modal);

        if (collected && button.dataset.plannedAt) {
            collected.value = button.dataset.plannedAt;
        }

        if (status) {
            status.value = 'ready';
        }

        if (lab) {
            lab.value = button.dataset.laboratory || '';
        }

        if (comment) {
            comment.value = button.dataset.comment || '';
        }

        render();
    });

    tbody?.addEventListener('input', event => {
        const input = event.target.closest('[data-result-value]');

        if (!input) {
            return;
        }

        const param = params.find(item => String(item.id) === String(input.dataset.resultValue));

        if (param) {
            param.value = input.value;
        }

        render();
    });

    tbody?.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-result-param]');

        if (!button) {
            return;
        }

        params = params.filter(item => String(item.id) !== String(button.dataset.removeResultParam));
        render();
    });

    hideNormal?.addEventListener('change', render);
}

function initLabViewModal() {
    const modal = qs('#modalViewLab');

    if (!modal) {
        return;
    }

    const tbody = qs('#v_paramsTable tbody', modal);
    const counter = qs('#v_selCount', modal);
    const hideNormal = qs('#v_toggleHideNormal', modal);

    const canFillMissing =
        modal.dataset.canFillMissing === '1';

    const fillMissingUrlTemplate =
        modal.dataset.fillMissingUrlTemplate || '';

    let params = [];
    let currentResearchId = null;
    let currentTriggerButton = null;

    function getFillMissingUrl(parameterId) {
        if (
            !fillMissingUrlTemplate
            || !currentResearchId
            || !parameterId
        ) {
            return '';
        }

        return fillMissingUrlTemplate
            .replace(
                '__RESEARCH__',
                encodeURIComponent(currentResearchId)
            )
            .replace(
                '__PARAMETER__',
                encodeURIComponent(parameterId)
            );
    }

    function renderValueCell(param) {
        if (
            canFillMissing
            && isMissingLabValue(param.value)
        ) {
            return `
                <div class="lab-missing-result-editor">
                    <span
                        class="lab-missing-result-editor__dash"
                        title="Результат лабораторией не заполнен"
                    >—</span>

                    <input
                        type="text"
                        class="form-control form-control-sm lab-missing-result-editor__input"
                        data-missing-result-input
                        data-parameter-id="${escapeHtml(param.id)}"
                        placeholder="Введите значение"
                        autocomplete="off"
                    >

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary lab-missing-result-editor__save"
                        data-save-missing-result
                        data-parameter-id="${escapeHtml(param.id)}"
                        title="Сохранить значение"
                    >
                        <i class="bi bi-check-lg"></i>
                        <span>Сохранить</span>
                    </button>
                </div>
            `;
        }

        return escapeHtml(
            isMissingLabValue(param.value)
                ? '—'
                : String(param.value)
        );
    }

    function render() {
        if (!tbody) {
            return;
        }

        tbody.innerHTML = params.map(param => {
            const flag = evaluateParamFlag(
                param.value,
                param
            );

            return `
                <tr
                    class="lab-result-row
                           lab-result-row--${escapeHtml(flag.kind)}
                           ${hideNormal?.checked && flag.kind === 'normal' ? 'd-none' : ''}"
                    data-parameter-id="${escapeHtml(param.id)}"
                >
                    <td>
                        <div class="fw-semibold">
                            ${escapeHtml(param.name)}
                        </div>
                    </td>

                    <td>
                        <div>
                            ${escapeHtml(getRefText(param))}
                        </div>

                        ${getCriticalRefText(param)
                ? `<div class="lab-critical-reference">
                                   Крит.: ${escapeHtml(getCriticalRefText(param))}
                               </div>`
                : ''}
                    </td>

                    <td>
                        ${escapeHtml(param.unit || '—')}
                    </td>

                    <td>
                        ${renderValueCell(param)}
                    </td>

                    <td class="text-center">
                        <span
                            class="badge ${flag.className}"
                            title="${escapeHtml(flag.title || '')}"
                        >
                            ${escapeHtml(flag.text)}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');

        if (counter) {
            counter.textContent = `(${params.length})`;
        }
    }

    modal.addEventListener(
        'show.bs.modal',
        async event => {
            const button = event.relatedTarget;

            if (!button?.matches('[data-lab-view]')) {
                return;
            }

            currentTriggerButton = button;
            currentResearchId = button.dataset.id || null;

            const values =
                safeJsonParse(
                    button.dataset.values,
                    {}
                ) || {};

            params = toArray(
                getDatasetParams(button)
            ).map(item => {
                return mergeParamWithResult(
                    item,
                    values
                );
            });

            render();

            params = await hydrateParams(
                params,
                modal.dataset.apiParams,
                modal
            );

            qs('#v_collected_at', modal).textContent =
                button.dataset.collectedAt || '—';

            qs('#v_status', modal).textContent =
                'Готово';

            qs('#v_lab_name', modal).textContent =
                button.dataset.laboratory || '—';

            qs('#v_comment', modal).textContent =
                button.dataset.comment || '—';

            render();

            /*
             * Фиксируем просмотр результата в БД.
             */
            const viewUrl = button.dataset.viewUrl;

            if (!viewUrl || button.dataset.viewed === '1') {
                return;
            }

            try {
                const response = await fetch(
                    viewUrl,
                    {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf(),
                        },
                    }
                );

                const result = await response.json()
                    .catch(() => ({}));

                const success =
                    result.success === true
                    || result.status === 'success';

                if (!response.ok || !success) {
                    throw new Error(
                        result.message
                        || `HTTP ${response.status}`
                    );
                }

                button.dataset.viewed = '1';
            } catch (error) {
                console.error(
                    'Не удалось зафиксировать просмотр анализа:',
                    error
                );

                showToast?.(
                    'Результат открыт, но просмотр не был зафиксирован'
                );
            }
        }
    );

    tbody?.addEventListener(
        'click',
        async event => {
            const saveButton =
                event.target.closest(
                    '[data-save-missing-result]'
                );

            if (!saveButton || !canFillMissing) {
                return;
            }

            const parameterId =
                saveButton.dataset.parameterId;

            const row =
                saveButton.closest('tr');

            const input =
                row?.querySelector(
                    '[data-missing-result-input]'
                );

            if (
                !parameterId
                || !currentResearchId
                || !input
            ) {
                return;
            }

            const value =
                String(input.value ?? '').trim();

            if (value === '') {
                input.classList.add('is-invalid');
                input.focus();
                return;
            }

            input.classList.remove('is-invalid');

            const url =
                getFillMissingUrl(parameterId);

            if (!url) {
                showToast?.(
                    'Не удалось сформировать адрес сохранения'
                );
                return;
            }

            const oldButtonHtml =
                saveButton.innerHTML;

            input.disabled = true;
            saveButton.disabled = true;

            saveButton.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm"
                    aria-hidden="true"
                ></span>
                <span>Сохраняем</span>
            `;

            try {
                const response = await fetch(
                    url,
                    {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type':
                                'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        body: JSON.stringify({
                            value,
                        }),
                    }
                );

                const result = await response.json()
                    .catch(() => ({}));

                if (
                    !response.ok
                    || result.success !== true
                ) {
                    throw new Error(
                        result.message
                        || `HTTP ${response.status}`
                    );
                }

                const param = params.find(
                    item =>
                        String(item.id)
                        === String(parameterId)
                );

                if (param) {
                    param.value = result.value;
                }

                /*
                 * Обновляем data-values на кнопке.
                 * Благодаря этому повторное открытие модалки
                 * не вернёт старый прочерк.
                 */
                if (currentTriggerButton) {
                    const values =
                        safeJsonParse(
                            currentTriggerButton
                                .dataset.values,
                            {}
                        ) || {};

                    const oldPayload =
                        values[String(parameterId)];

                    if (
                        oldPayload
                        && typeof oldPayload === 'object'
                        && !Array.isArray(oldPayload)
                    ) {
                        values[String(parameterId)] = {
                            ...oldPayload,
                            value: result.value,
                        };
                    } else {
                        values[String(parameterId)] =
                            result.value;
                    }

                    currentTriggerButton.dataset.values =
                        JSON.stringify(values);
                }

                render();

                showToast?.(
                    'Значение анализа сохранено'
                );
            } catch (error) {
                console.error(
                    'Не удалось сохранить значение анализа:',
                    error
                );

                input.disabled = false;
                saveButton.disabled = false;
                saveButton.innerHTML = oldButtonHtml;

                showToast?.(
                    error.message
                    || 'Не удалось сохранить значение'
                );
            }
        }
    );

    tbody?.addEventListener(
        'keydown',
        event => {
            if (event.key !== 'Enter') {
                return;
            }

            const input =
                event.target.closest(
                    '[data-missing-result-input]'
                );

            if (!input) {
                return;
            }

            event.preventDefault();

            input
                .closest('tr')
                ?.querySelector(
                    '[data-save-missing-result]'
                )
                ?.click();
        }
    );

    hideNormal?.addEventListener(
        'change',
        render
    );
}

function initLabActions() {
    qsa('[data-lab-edit]').forEach(button => {
        button.addEventListener('click', () => {
            getBootstrapModal(qs('#modalAddLab'))?.show(button);
        });
    });

    qsa('[data-lab-view]').forEach(button => {
        button.addEventListener('click', () => {
            getBootstrapModal(qs('#modalViewLab'))?.show(button);
        });
    });

    qsa('[data-lab-delete]').forEach(button => {
        button.addEventListener('click', async () => {
            const url = button.dataset.deleteUrl;

            if (!url || !window.confirm('Удалить направление?')) {
                return;
            }

            try {
                const response = await fetch(url, {
                    method: 'post',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json, text/html;q=0.9, */*;q=0.8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf()
                    }
                });

                if (!response.ok) {
                    const text = await response.text().catch(() => '');
                    // throw new Error(text || `HTTP ${response.status}`);
                }

                button.closest('tr')?.remove();
                showToast('Удалено');
            } catch (error) {
                alert(`Не удалось удалить: ${error.message}`);
            }
        });
    });

    qsa('[data-research-print]').forEach(button => {
        button.addEventListener('click', async () => {
            const url = button.dataset.printUrl || button.dataset.url;

            if (!url) {
                return;
            }

            try {
                printHtml(await fetchHtml(url));
            } catch (error) {
                //alert(`Не удалось напечатать: ${error.message}`);
                window.location = url;
            }
        });
    });
}

function initPrescriptionIndicationSwitch() {
    const modal = qs('#prescriptionModal');

    if (!modal) {
        return;
    }

    const form = qs('#patientPrescriptionForm', modal);
    const radios = qsa('input[name="indication_source"]', modal);

    const hiddenRussia = qs('#indicated_in_russia_hidden', modal);
    const hiddenFda = qs('#indicated_by_fda_hidden', modal);
    const hint = qs('#drug_select_hint', modal);
    const filterLabel = qs('#prescriptionSuggestionsFilterLabel', modal);

    const drugSelect = qs('#drug_id_modal', modal);
    const drugForm = qs('#drug_form_modal', modal);
    const dosage = qs('#dosage_modal', modal);
    const quantity = qs('#quantity_modal', modal);
    const standard = qs('#standard_modal', modal);
    const takingDrug = qs('#taking_drug_modal', modal);
    const takingCount = qs('#taking_count_modal', modal);
    const takingMeal = qs('#taking_time_meal_modal', modal);
    const printButton = qs('#printRecipeButton_modal', modal);

    const suggestionsEmpty = qs('#prescriptionSuggestionsEmpty', modal);
    const suggestionsList = qs('#prescriptionSuggestionsList', modal);
    const suggestionsLoading = qs('#prescriptionSuggestionsLoading', modal);

    function getSelectedSource() {
        return qs('input[name="indication_source"]:checked', modal)?.value || '';
    }

    function sourceTitle(source) {
        if (source === 'russia') return 'Показан в РФ';
        if (source === 'fda') return 'Показан FDA';

        return 'Без фильтра показаний';
    }

    function sourceHint(source) {
        if (source === 'russia') {
            return 'Показываются препараты с показанием в рекомендациях РФ для диагноза пациента.';
        }

        if (source === 'fda') {
            return 'Показываются препараты с показанием FDA для диагноза пациента.';
        }

        return 'Показываются все доступные препараты.';
    }

    function resetSelect(select, placeholder, disabled = true) {
        if (!select) {
            return;
        }

        select.innerHTML = '';

        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;

        select.appendChild(option);
        select.value = '';
        select.disabled = disabled;
    }

    function resetPrescriptionFields() {
        resetSelect(drugForm, 'Выберите форму', true);
        resetSelect(dosage, 'Выберите дозировку', true);
        resetSelect(quantity, 'Выберите количество', true);

        if (standard) {
            standard.value = 1;
            standard.disabled = true;
            standard.min = 1;
            standard.removeAttribute('max');
        }

        if (takingDrug) {
            takingDrug.value = 1;
            takingDrug.disabled = true;
        }

        if (takingCount) {
            takingCount.value = 1;
            takingCount.disabled = true;
        }

        if (takingMeal) {
            takingMeal.value = 1;
            takingMeal.disabled = true;
        }

        const drugType = qs('#drug_type_modal', modal);
        const takingTime = qs('#taking_time_modal', modal);
        const daysCount = qs('#days_count_modal', modal);
        const daysLabel = qs('#days_label_modal', modal);
        const usage = qs('#usage_instructions__', modal);

        if (drugType) drugType.textContent = 'драже';
        if (takingTime) takingTime.textContent = 'раз';
        if (daysCount) daysCount.textContent = '—';
        if (daysLabel) daysLabel.textContent = 'дней';
        if (usage) usage.value = '';

        if (printButton) {
            printButton.disabled = true;
        }
    }

    function resetDrugSelect() {
        if (!drugSelect) {
            return;
        }

        drugSelect.value = '';

        if (window.jQuery && window.jQuery.fn?.select2 && window.jQuery(drugSelect).hasClass('select2-hidden-accessible')) {
            window.jQuery(drugSelect).val(null).trigger('change');
        }
    }

    function resetSuggestions(source) {
        if (suggestionsLoading) {
            suggestionsLoading.classList.add('d-none');
        }

        if (suggestionsList) {
            suggestionsList.classList.add('d-none');
            suggestionsList.innerHTML = '';
        }

        if (suggestionsEmpty) {
            suggestionsEmpty.classList.remove('d-none');
            suggestionsEmpty.textContent = source
                ? 'Фильтр изменён. Выберите препарат или обновите подборку по диагнозу.'
                : 'Выберите режим показаний или диагноз, чтобы показать рекомендации по МНН.';
        }
    }

    function syncIndicationState({ resetDrug = false } = {}) {
        const source = getSelectedSource();

        if (hiddenRussia) {
            hiddenRussia.value = source === 'russia' ? '1' : '0';
        }

        if (hiddenFda) {
            hiddenFda.value = source === 'fda' ? '1' : '0';
        }

        if (hint) {
            hint.textContent = sourceHint(source);
        }

        if (filterLabel) {
            filterLabel.textContent = sourceTitle(source);
        }

        if (resetDrug) {
            resetDrugSelect();
            resetPrescriptionFields();
            resetSuggestions(source);
        }
    }

    // Важно: принудительно обрабатываем клик по карточке.
    // При input display:none браузеры иногда ведут себя нестабильно в сложных модалках.
    qsa('.presc-radio-card', modal).forEach(card => {
        card.addEventListener('click', () => {
            const input = qs('input[name="indication_source"]', card);

            if (!input) {
                return;
            }

            input.checked = true;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            syncIndicationState({ resetDrug: true });
        });
    });

    modal.addEventListener('shown.bs.modal', () => {
        syncIndicationState({ resetDrug: false });
    });

    form?.addEventListener('submit', () => {
        syncIndicationState({ resetDrug: false });
    });

    syncIndicationState({ resetDrug: false });
}

function initPrescriptionDrugSelect2() {
    const modal = qs('#prescriptionModal');
    const form = qs('#patientPrescriptionForm', modal);
    const select = qs('#drug_id_modal', modal);

    const drugForm = qs('#drug_form_modal', modal);
    const dosage = qs('#dosage_modal', modal);
    const quantity = qs('#quantity_modal', modal);
    const standard = qs('#standard_modal', modal);
    const takingDrug = qs('#taking_drug_modal', modal);
    const takingCount = qs('#taking_count_modal', modal);
    const takingMeal = qs('#taking_time_meal_modal', modal);
    const validity = qs('#validity_period_modal', modal);
    const printButton = qs('#printRecipeButton_modal', modal);
    const formAlert = qs('#prescription_form_alert', modal);

    const suggestionsEmpty = qs('#prescriptionSuggestionsEmpty', modal);
    const suggestionsLoading = qs('#prescriptionSuggestionsLoading', modal);
    const suggestionsList = qs('#prescriptionSuggestionsList', modal);
    const suggestionsCount = qs('#prescriptionSuggestionsCount', modal);

    if (!modal || !form || !select) {
        return;
    }

    if (!window.jQuery || !window.jQuery.fn?.select2) {
        console.warn('Select2 не подключён: поиск препаратов недоступен.');
        return;
    }

    const $ = window.jQuery;
    const $select = $(select);

    const sourceUrl = String(select.dataset.source || '').trim();
    const suggestionsUrl = String(form.dataset.suggestionsUrl || '').trim();

    if (!sourceUrl) {
        console.warn('Для #drug_id_modal не указан data-source.');
        return;
    }

    let currentSuggestions = [];
    let selectedDrugRequestToken = 0;

    function indicationSource() {
        return $('input[name="indication_source"]:checked', modal).val() || '';
    }

    function diagnosisCode() {
        return String($('#diagnosis_code', modal).val() || '').trim();
    }

    function patientId() {
        return String($('#patientPrescriptionForm input[name="patient_id"]').val() || '').trim();
    }

    function birthAt() {
        return String($('#birth_at__', modal).val() || '').trim();
    }

    function requestParams(term = '') {
        const source = indicationSource();

        return {
            q: term || '',
            term: term || '',
            patient_id: patientId(),
            diagnosis_code: diagnosisCode(),
            birth_at: birthAt(),
            indication_source: source,
            indicated_in_russia: source === 'russia' ? 1 : 0,
            indicated_by_fda: source === 'fda' ? 1 : 0
        };
    }

    function showFormError(message = '') {
        if (!formAlert) {
            if (message) console.error(message);
            return;
        }

        formAlert.textContent = message;
        formAlert.classList.toggle('d-none', !message);
    }

    function normalizeItems(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        const candidates = [
            payload?.items,
            payload?.results,
            payload?.recommendations,
            payload?.drugs,
            payload?.data?.items,
            payload?.data?.results,
            payload?.data?.recommendations,
            payload?.data?.drugs,
            payload?.data
        ];

        return candidates.find(Array.isArray) || [];
    }

    function normalizeObject(payload) {
        if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
            return null;
        }

        const candidates = [
            payload.preset,
            payload.item,
            payload.result,
            payload.data?.preset,
            payload.data?.item,
            payload.data?.result,
            payload.data
        ];

        return candidates.find(value => value && typeof value === 'object' && !Array.isArray(value)) || null;
    }

    async function fetchJson(url) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json')
            ? await response.json()
            : null;

        if (!response.ok) {
            throw new Error(
                payload?.message
                || payload?.error
                || `Ошибка HTTP ${response.status}`
            );
        }

        return payload;
    }

    function hasCyrillic(value) {
        return /[А-Яа-яЁё]/.test(String(value || ''));
    }

    function firstFilled(...values) {
        return values.find(value => String(value || '').trim() !== '') || '';
    }

    function itemId(item) {
        const raw = item?.raw || item || {};

        return raw.id
            || raw.drug_id
            || raw.drugId
            || raw.medicine_id
            || raw.preparation_id
            || item?.id
            || '';
    }

    function itemRussianName(item) {
        const raw = item?.raw || item || {};
        const candidates = [
            raw.drug_name,
            raw.name_ru,
            raw.russian_name,
            raw.name_rus,
            raw.ru_name,
            raw.inn,
            raw.name,
            raw.title,
            raw.text,
            raw.generic_name,
            raw.latin_name
        ];

        return candidates.find(hasCyrillic)
            || firstFilled(...candidates)
            || `Препарат #${itemId(item)}`;
    }

    function itemLatinName(item) {
        const raw = item?.raw || item || {};
        const candidates = [
            raw.latin_name,
            raw.latin,
            raw.name_latin,
            raw.inn_latin,
            raw.mnn_latin,
            raw.generic_latin,
            raw.generic_name,
            raw.mnn,
            raw.inn
        ];

        return candidates.find(value => {
            const text = String(value || '').trim();
            return text !== '' && !hasCyrillic(text);
        }) || '';
    }

    function itemDescription(item) {
        const raw = item?.raw || item || {};

        return firstFilled(
            raw.description,
            raw.short_description,
            raw.annotation,
            raw.reason,
            raw.indication_reason,
            raw.indication_text,
            raw.comment,
            raw.note
        );
    }

    function toSelect2Item(item) {
        const raw = item?.raw || item || {};

        return {
            id: String(itemId(raw)),
            text: itemRussianName(raw),
            name: raw.name || raw.drug_name || itemRussianName(raw),
            latin_name: itemLatinName(raw),
            generic_name: raw.generic_name || raw.inn || '',
            strict: Boolean(raw.strict),
            raw
        };
    }

    function toSelect2Items(payload) {
        return normalizeItems(payload)
            .map(toSelect2Item)
            .filter(item => item.id !== '');
    }

    function setSuggestionsState(state, message = '') {
        suggestionsLoading?.classList.toggle('d-none', state !== 'loading');
        suggestionsEmpty?.classList.toggle('d-none', state !== 'empty');
        suggestionsList?.classList.toggle('d-none', state !== 'list');

        if (state === 'empty' && suggestionsEmpty) {
            suggestionsEmpty.innerHTML = `
                <i class="bi bi-capsule"></i>
                <span>${escapeHtml(message || 'Подходящие препараты не найдены.')}</span>
            `;
        }
    }

    function renderSuggestions(items) {
        currentSuggestions = Array.isArray(items) ? items : [];

        if (suggestionsCount) {
            suggestionsCount.textContent = String(currentSuggestions.length);
        }

        if (!currentSuggestions.length) {
            if (suggestionsList) suggestionsList.innerHTML = '';
            setSuggestionsState('empty', 'По выбранному фильтру препараты не найдены.');
            return;
        }

        suggestionsList.innerHTML = currentSuggestions.map(item => {
            const raw = item?.raw || item || {};
            const id = itemId(raw);
            const title = escapeHtml(itemRussianName(raw));
            const latin = escapeHtml(itemLatinName(raw));
            const description = escapeHtml(itemDescription(raw));
            const rf = raw.indicated_in_russia === true || raw.indicated_in_russia === 1 || raw.indicated_in_russia === '1';
            const fda = raw.indicated_by_fda === true || raw.indicated_by_fda === 1 || raw.indicated_by_fda === '1';
            const strict = raw.strict === true || raw.strict === 1 || raw.strict === '1';

            return `
                <div class="presc-suggestion-item" data-suggestion-id="${escapeHtml(id)}">
                    <div class="presc-suggestion-main">
                        <div class="presc-suggestion-title">${title}</div>
                        ${latin ? `<div class="presc-suggestion-latin">${latin}</div>` : ''}
                        ${description ? `<div class="presc-suggestion-description">${description}</div>` : ''}
                    </div>

                    <div class="presc-suggestion-footer">
                        <div class="presc-suggestion-badges">
                            ${rf ? '<span class="presc-suggestion-badge presc-suggestion-badge--rf">РФ</span>' : ''}
                            ${fda ? '<span class="presc-suggestion-badge presc-suggestion-badge--fda">FDA</span>' : ''}
                            ${strict ? '<span class="presc-suggestion-badge">148-1/у-88</span>' : ''}
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary presc-suggestion-pick"
                            data-pick-suggested-drug
                            data-drug-payload="${encodeURIComponent(JSON.stringify(raw))}"
                        >
                            Выбрать
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        setSuggestionsState('list');
    }

    async function loadSuggestions() {
        setSuggestionsState('loading');

        const params = requestParams('');
        let items = [];
        let aiError = null;

        if (suggestionsUrl) {
            try {
                const url = new URL(suggestionsUrl, window.location.origin);

                Object.entries(params).forEach(([key, value]) => {
                    if (value !== '' && value !== null && value !== undefined) {
                        url.searchParams.set(key, value);
                    }
                });

                const payload = await fetchJson(url.toString());
                items = normalizeItems(payload);
            } catch (error) {
                aiError = error;
                console.warn('[Prescription] AI recommendations unavailable, fallback to drug search', error);
            }
        }

        /*
         * Если ИИ-сервис недоступен или вернул пустой ответ, показываем
         * безопасный резервный список из обычного поиска лекарств.
         */
        if (!items.length) {
            try {
                const fallbackUrl = new URL(sourceUrl, window.location.origin);

                Object.entries({ ...params, page: 1 }).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        fallbackUrl.searchParams.set(key, value);
                    }
                });

                const payload = await fetchJson(fallbackUrl.toString());
                items = normalizeItems(payload);
            } catch (fallbackError) {
                console.error('[Prescription] fallback recommendations failed', fallbackError);

                renderSuggestions([]);
                setSuggestionsState(
                    'empty',
                    aiError?.message || fallbackError.message || 'Не удалось загрузить подбор препаратов.'
                );
                return;
            }
        }

        renderSuggestions(items);
    }

    function resetSelect(selectEl, placeholder, disabled = true) {
        if (!selectEl) return;

        selectEl.innerHTML = '';
        selectEl.appendChild(new Option(placeholder, ''));
        selectEl.value = '';
        selectEl.disabled = disabled;
    }

    function resetPrescriptionFields() {
        resetSelect(drugForm, 'Выберите форму', true);
        resetSelect(dosage, 'Выберите дозировку', true);
        resetSelect(quantity, 'Выберите количество', true);

        if (standard) {
            standard.value = 1;
            standard.disabled = true;
            standard.min = 1;
            standard.removeAttribute('max');
        }

        if (takingDrug) {
            takingDrug.value = 1;
            takingDrug.disabled = true;
        }

        if (takingCount) {
            takingCount.value = 1;
            takingCount.disabled = true;
        }

        if (takingMeal) {
            takingMeal.value = 1;
            takingMeal.disabled = true;
        }

        const drugType = qs('#drug_type_modal', modal);
        const takingTime = qs('#taking_time_modal', modal);
        const daysCount = qs('#days_count_modal', modal);
        const daysLabel = qs('#days_label_modal', modal);
        const usage = qs('#usage_instructions__', modal);
        const strictMessage = qs('#strict_message_modal', modal);
        const validityBlock = qs('#validity_block_modal', modal);

        if (drugType) drugType.textContent = 'единице препарата';
        if (takingTime) takingTime.textContent = 'раз';
        if (daysCount) daysCount.textContent = '—';
        if (daysLabel) daysLabel.textContent = 'дней';
        if (usage) usage.value = '';

        strictMessage?.classList.add('d-none');
        validityBlock?.classList.remove('d-none');

        if (printButton) printButton.disabled = true;
    }

    function enableRegimenFields() {
        if (standard) standard.disabled = false;
        if (takingDrug) takingDrug.disabled = false;
        if (takingCount) takingCount.disabled = false;
        if (takingMeal) takingMeal.disabled = false;
        if (printButton) printButton.disabled = false;
    }

    function firstNonEmptyOption(selectEl) {
        if (!selectEl) return null;
        return Array.from(selectEl.options).find(option => String(option.value).trim() !== '') || null;
    }

    function selectFirstOption(selectEl) {
        const option = firstNonEmptyOption(selectEl);
        if (!option) return false;

        selectEl.value = option.value;
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function ensureOptionAndSelect(selectEl, value, label = null) {
        if (!selectEl || value === undefined || value === null || String(value).trim() === '') {
            return false;
        }

        const stringValue = String(value).trim();
        const stringLabel = String(label ?? value).trim();

        if (!Array.from(selectEl.options).some(option => String(option.value) === stringValue)) {
            selectEl.appendChild(new Option(stringLabel, stringValue));
        }

        selectEl.disabled = false;
        selectEl.value = stringValue;
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function dayWord(number) {
        const n = Math.abs(Number(number)) % 100;
        const n1 = n % 10;

        if (n > 10 && n < 20) return 'дней';
        if (n1 > 1 && n1 < 5) return 'дня';
        if (n1 === 1) return 'день';
        return 'дней';
    }

    function drugFormLabel(value) {
        const text = String(value || '').toLowerCase();

        if (text.includes('capsule') || text.includes('капсул')) return 'капсуле';
        if (text.includes('tablet') || text.includes('таб')) return 'таблетке';
        if (text.includes('drop') || text.includes('капл')) return 'капле';
        if (text.includes('solution') || text.includes('раствор')) return 'мл';
        if (text.includes('ampoule') || text.includes('ампул')) return 'ампуле';
        if (text.includes('syrup') || text.includes('сироп')) return 'мл';
        if (text.includes('dragee') || text.includes('драже')) return 'драже';

        return 'единице препарата';
    }

    function drugFormTitle(value) {
        const original = String(value || '').trim();
        const text = original.toLowerCase();

        const map = {
            capsules: 'Капсулы',
            capsule: 'Капсулы',
            tablets: 'Таблетки',
            tablet: 'Таблетки',
            pills: 'Таблетки',
            pill: 'Таблетки',
            dragees: 'Драже',
            dragee: 'Драже',
            drops: 'Капли',
            drop: 'Капли',
            solution: 'Раствор',
            syrup: 'Сироп',
            ampules: 'Ампулы',
            ampoules: 'Ампулы',
            ampoule: 'Ампулы',
            injection: 'Инъекции',
            injections: 'Инъекции',
            powder: 'Порошок',
            suspension: 'Суспензия',
            spray: 'Спрей',
            ointment: 'Мазь',
            cream: 'Крем',
            gel: 'Гель',
            patch: 'Пластырь',
            suppositories: 'Суппозитории',
            suppository: 'Суппозитории'
        };

        return map[text] || original;
    }

    function updatePrescriptionDaysAndText(raw = {}) {
        const quantityValue = Number(quantity?.value || raw.quantity || 0);
        const standardValue = Number(standard?.value || raw.standard || 1);
        const takingDrugValue = Number(takingDrug?.value || raw.taking_drug || 1);
        const takingCountValue = Number(takingCount?.value || raw.taking_count || 1);

        const daysNode = qs('#days_count_modal', modal);
        const daysLabelNode = qs('#days_label_modal', modal);
        const drugTypeNode = qs('#drug_type_modal', modal);
        const takingTimeNode = qs('#taking_time_modal', modal);
        const usageInput = qs('#usage_instructions__', modal);

        const totalUnits = Number(raw.total_units || (quantityValue * standardValue));
        const perDay = takingDrugValue * takingCountValue;
        const days = totalUnits > 0 && perDay > 0 ? Math.floor(totalUnits / perDay) : 0;

        if (daysNode) daysNode.textContent = days > 0 ? String(days) : '—';
        if (daysLabelNode) daysLabelNode.textContent = dayWord(days);
        if (drugTypeNode) drugTypeNode.textContent = drugFormLabel(drugForm?.value || raw.drug_form);
        if (takingTimeNode) takingTimeNode.textContent = takingCountValue === 1 ? 'раз' : 'раза';

        if (usageInput) {
            if (raw.usage_instructions) {
                usageInput.value = String(raw.usage_instructions);
            } else {
                const meal = String(takingMeal?.value || raw.taking_time_meal || '1') === '2'
                    ? 'до еды'
                    : 'после еды';

                usageInput.value = `По ${takingDrugValue} ${drugFormLabel(drugForm?.value || raw.drug_form)} ${takingCountValue} ${takingCountValue === 1 ? 'раз' : 'раза'} в день ${meal}`;
            }
        }
    }

    function toggleStrict(raw = {}) {
        const strict = raw.strict === true || raw.strict === 1 || raw.strict === '1';
        const strictMessage = qs('#strict_message_modal', modal);
        const validityBlock = qs('#validity_block_modal', modal);

        strictMessage?.classList.toggle('d-none', !strict);
        validityBlock?.classList.toggle('d-none', strict);

        if (standard) {
            standard.min = 1;

            if (strict) standard.max = 3;
            else standard.removeAttribute('max');
        }
    }

    function fillDosageAndQuantity() {
        const rows = safeJsonParse(drugForm?.dataset.rows, []) || [];
        const selectedForm = String(drugForm?.value || '');

        const filtered = rows.filter(row => String(row.form || row.drug_form || '') === selectedForm);

        resetSelect(dosage, 'Выберите дозировку', false);
        resetSelect(quantity, 'Выберите количество', false);

        const dosages = [...new Set(
            filtered
                .map(row => row.dosage ?? row.dose)
                .filter(value => value !== null && value !== undefined && String(value).trim() !== '')
        )];

        const quantities = [...new Set(
            filtered
                .map(row => row.quantity ?? row.count ?? row.volume)
                .filter(value => value !== null && value !== undefined && String(value).trim() !== '')
        )];

        dosages.forEach(value => dosage.appendChild(new Option(String(value), String(value))));
        quantities.forEach(value => quantity.appendChild(new Option(String(value), String(value))));

        dosage.disabled = dosages.length === 0;
        quantity.disabled = quantities.length === 0;
    }

    async function loadDrugForms(item) {
        const raw = item?.raw || item || {};
        const latinName = itemLatinName(raw)
            || raw.latin_name
            || item?.latin_name
            || raw.text
            || item?.text;

        if (!latinName) {
            throw new Error('У препарата не указано МНН для загрузки форм.');
        }

        const formsUrl = `/api/doctors/search-drugs/${encodeURIComponent(latinName)}/forms`;
        const payload = await fetchJson(formsUrl);
        const rows = normalizeItems(payload);

        resetSelect(drugForm, 'Выберите форму', false);
        resetSelect(dosage, 'Выберите дозировку', true);
        resetSelect(quantity, 'Выберите количество', true);

        drugForm.dataset.rows = JSON.stringify(rows);

        const forms = [...new Set(
            rows
                .map(row => row.form || row.drug_form)
                .filter(value => value !== null && value !== undefined && String(value).trim() !== '')
        )];

        forms.forEach(value => {
            drugForm.appendChild(new Option(drugFormTitle(value), String(value)));
        });

        drugForm.disabled = forms.length === 0;
        enableRegimenFields();

        if (forms.length) {
            drugForm.value = String(forms[0]);
            fillDosageAndQuantity();
            selectFirstOption(dosage);
            selectFirstOption(quantity);
        }

        updatePrescriptionDaysAndText(raw);
        return rows;
    }

    function applySuggestedPrescriptionDefaults(raw = {}) {
        if (!raw || typeof raw !== 'object') return;

        if (raw.drug_form) {
            ensureOptionAndSelect(drugForm, raw.drug_form, drugFormTitle(raw.drug_form));
            fillDosageAndQuantity();
        }

        if (raw.dosage !== undefined && raw.dosage !== null) {
            ensureOptionAndSelect(dosage, raw.dosage, raw.dosage);
        }

        if (raw.quantity !== undefined && raw.quantity !== null) {
            ensureOptionAndSelect(quantity, raw.quantity, raw.quantity);
        }

        if (standard) {
            standard.disabled = false;
            standard.value = raw.standard ?? 1;
        }

        if (takingDrug) {
            takingDrug.disabled = false;
            takingDrug.value = raw.taking_drug ?? 1;
        }

        if (takingCount) {
            takingCount.disabled = false;
            takingCount.value = String(raw.taking_count ?? 1);
        }

        if (takingMeal) {
            takingMeal.disabled = false;
            takingMeal.value = String(raw.taking_time_meal ?? 1);
        }

        if (validity && raw.validity_period) {
            ensureOptionAndSelect(validity, raw.validity_period, String(raw.validity_period) === '365' ? '1 год' : `${raw.validity_period} дней`);
        }

        toggleStrict(raw);
        updatePrescriptionDaysAndText(raw);

        if (printButton) printButton.disabled = false;
    }

    function sameDrug(left, right) {
        const leftId = String(itemId(left));
        const rightId = String(itemId(right));

        if (leftId && rightId && leftId === rightId) return true;

        const leftLatin = itemLatinName(left).toLowerCase();
        const rightLatin = itemLatinName(right).toLowerCase();

        return leftLatin !== '' && rightLatin !== '' && leftLatin === rightLatin;
    }

    async function loadPresetForDrug(item) {
        const raw = item?.raw || item || {};

        const localPreset = currentSuggestions.find(candidate => sameDrug(candidate, raw));
        if (localPreset) return localPreset?.raw || localPreset;

        if (!suggestionsUrl) return null;

        try {
            const url = new URL(suggestionsUrl, window.location.origin);
            const params = {
                ...requestParams(''),
                drug_id: itemId(raw),
                latin_name: itemLatinName(raw)
            };

            Object.entries(params).forEach(([key, value]) => {
                if (value !== '' && value !== null && value !== undefined) {
                    url.searchParams.set(key, value);
                }
            });

            const payload = await fetchJson(url.toString());
            const objectPreset = normalizeObject(payload);

            if (objectPreset && (
                objectPreset.drug_form
                || objectPreset.dosage
                || objectPreset.quantity
                || objectPreset.usage_instructions
            )) {
                return objectPreset;
            }

            return normalizeItems(payload).find(candidate => sameDrug(candidate, raw)) || null;
        } catch (error) {
            console.warn('[Prescription] preset unavailable, using forms defaults', error);
            return null;
        }
    }

    async function processSelectedDrug(item) {
        const token = ++selectedDrugRequestToken;
        const normalized = toSelect2Item(item);

        showFormError('');
        resetPrescriptionFields();

        try {
            await loadDrugForms(normalized);

            if (token !== selectedDrugRequestToken) return;

            const preset = await loadPresetForDrug(normalized);

            if (token !== selectedDrugRequestToken) return;

            applySuggestedPrescriptionDefaults({
                ...(normalized.raw || {}),
                ...(preset?.raw || preset || {})
            });
        } catch (error) {
            console.error('[Prescription] cannot prepare selected drug', error);
            showFormError(error.message || 'Не удалось загрузить параметры выбранного препарата.');
        }
    }

    async function selectSuggestedDrug(raw) {
        const item = toSelect2Item(raw);

        $select.find('option').filter(function () {
            return String(this.value) === String(item.id);
        }).remove();

        $select.append(new Option(item.text, item.id, true, true));
        $select.val(item.id).trigger('change.select2');

        if ($select.data('select2')) {
            $select
                .next('.select2-container')
                .find('.select2-selection__rendered')
                .text(item.text)
                .attr('title', item.text);
        }

        await processSelectedDrug(item);
    }

    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }

    $select.select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $(modal),
        placeholder: select.dataset.placeholder || 'Начните вводить препарат',
        allowClear: true,
        minimumInputLength: 0,
        multiple: false,
        ajax: {
            url: sourceUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    ...requestParams(params.term || ''),
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: toSelect2Items(data),
                    pagination: {
                        more: Boolean(data?.pagination?.more || data?.more)
                    }
                };
            },
            cache: false
        }
    });

    $select
        .off('select2:select.prescriptionDrug')
        .on('select2:select.prescriptionDrug', async function (event) {
            await processSelectedDrug(event.params.data || {});
        });

    $select
        .off('select2:clear.prescriptionDrug')
        .on('select2:clear.prescriptionDrug', function () {
            ++selectedDrugRequestToken;
            resetPrescriptionFields();
            showFormError('');
        });

    drugForm?.addEventListener('change', function () {
        fillDosageAndQuantity();
        selectFirstOption(dosage);
        selectFirstOption(quantity);
        updatePrescriptionDaysAndText();
    });

    [dosage, quantity, standard, takingDrug, takingCount, takingMeal].forEach(element => {
        element?.addEventListener('change', () => updatePrescriptionDaysAndText());
        element?.addEventListener('input', () => updatePrescriptionDaysAndText());
    });

    suggestionsList?.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-pick-suggested-drug]');
        if (!button) return;

        event.preventDefault();

        let raw = {};

        try {
            raw = JSON.parse(decodeURIComponent(button.dataset.drugPayload || '{}'));
        } catch (error) {
            console.error('[Prescription] invalid suggestion payload', error);
            return;
        }

        button.disabled = true;

        try {
            await selectSuggestedDrug(raw);
        } finally {
            button.disabled = false;
        }
    });

    $('input[name="indication_source"]', modal)
        .off('change.prescriptionSelect2')
        .on('change.prescriptionSelect2', async function () {
            ++selectedDrugRequestToken;
            $select.val(null).trigger('change');
            resetPrescriptionFields();

            try {
                await loadSuggestions();
            } catch (error) {
                console.error(error);
                setSuggestionsState('empty', error.message);
            }
        });

    $(modal)
        .off('shown.bs.modal.prescriptionDrug')
        .on('shown.bs.modal.prescriptionDrug', async function () {
            try {
                await loadSuggestions();
            } catch (error) {
                console.error(error);
                setSuggestionsState('empty', error.message);
            }
        });

    const docsForm = document.getElementById('pane-docs');
    const fileInput = document.getElementById('document-upload__upload');
    const dropzone = document.getElementById('documentUploadDropzone');
    const selectFileButton = document.getElementById('documentUploadSelectFile');
    const clearFileButton = document.getElementById('documentUploadClearFile');
    const fileInfo = document.getElementById('documentUploadFileInfo');
    const fileName = document.getElementById('documentUploadFileName');
    const fileMeta = document.getElementById('documentUploadFileMeta');
    const uploadError = document.getElementById('documentUploadError');

    function showDocumentUploadError(message) {
        if (!uploadError) {
            alert(message);
            return;
        }

        uploadError.textContent = message;
        uploadError.classList.remove('d-none');
    }

    function hideDocumentUploadError() {
        if (!uploadError) {
            return;
        }

        uploadError.textContent = '';
        uploadError.classList.add('d-none');
    }

    function formatDocumentFileSize(bytes) {
        if (!bytes && bytes !== 0) {
            return '';
        }

        const units = ['Б', 'КБ', 'МБ', 'ГБ'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex += 1;
        }

        return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
    }

    function renderSelectedDocumentFile() {
        const file = fileInput?.files?.[0];

        if (!file) {
            fileInfo?.classList.remove('is-visible');
            if (fileName) fileName.textContent = '—';
            if (fileMeta) fileMeta.textContent = '—';
            return;
        }

        if (fileName) fileName.textContent = file.name;
        if (fileMeta) fileMeta.textContent = formatDocumentFileSize(file.size);
        fileInfo?.classList.add('is-visible');
    }

    function setSingleDocumentFile(file) {
        if (!fileInput || !file) {
            return;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        renderSelectedDocumentFile();
        hideDocumentUploadError();
    }

    fileInput?.addEventListener('change', function () {
        if (fileInput.files.length > 1) {
            const firstFile = fileInput.files[0];
            setSingleDocumentFile(firstFile);
            showDocumentUploadError('Можно загрузить только один файл за раз. Оставлен первый выбранный файл.');
            return;
        }

        renderSelectedDocumentFile();
        hideDocumentUploadError();
    });

    selectFileButton?.addEventListener('click', function (event) {
        event.preventDefault();
        fileInput?.click();
    });

    dropzone?.addEventListener('click', function (event) {
        if (event.target.closest('button')) {
            return;
        }

        fileInput?.click();
    });

    dropzone?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput?.click();
        }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone?.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone?.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone?.addEventListener('drop', function (event) {
        const files = event.dataTransfer?.files;

        if (!files || !files.length) {
            return;
        }

        if (files.length > 1) {
            showDocumentUploadError('Можно загрузить только один файл за раз. Перетащите один файл.');
            return;
        }

        setSingleDocumentFile(files[0]);
    });

    clearFileButton?.addEventListener('click', function () {
        if (fileInput) {
            fileInput.value = '';
        }

        renderSelectedDocumentFile();
        hideDocumentUploadError();
    });

    docsForm?.addEventListener('submit', function (event) {
        const submitter = event.submitter;

        if (submitter && submitter.closest('#modalUploadDocument') && !fileInput?.files?.length) {
            event.preventDefault();
            showDocumentUploadError('Выберите файл для загрузки.');
        }
    });
}


// Epicrisis document editor: CodeMirror используется как WYSIWYG-подобный лист документа,
// без HTML-кода в поле ввода. В textarea[name="text"] сохраняется готовый HTML,
// а в text_json — структурированная модель для будущей генерации .docx.
(() => {
    const EPICRISIS_DIAGNOSES_URL = '/api/diagnoses';

    const addDocMarkEffect = StateEffect.define();
    const clearDocMarksEffect = StateEffect.define();
    const setDocMarksEffect = StateEffect.define();

    const epicrisisDocField = StateField.define({
        create(state) {
            return {
                marks: [],
                decorations: buildDocDecorations(state, []),
            };
        },

        update(value, transaction) {
            const docLength = transaction.state.doc.length;
            let marks = value.marks
                .map(mark => mapDocMark(mark, transaction.changes, docLength))
                .filter(Boolean);

            for (const effect of transaction.effects) {
                if (effect.is(setDocMarksEffect)) {
                    marks = normalizeDocMarks(effect.value, docLength);
                    continue;
                }

                if (effect.is(addDocMarkEffect)) {
                    const items = Array.isArray(effect.value) ? effect.value : [effect.value];
                    marks.push(...items);
                    continue;
                }

                if (effect.is(clearDocMarksEffect)) {
                    marks = clearDocMarks(marks, effect.value);
                }
            }

            marks = normalizeDocMarks(marks, docLength);

            return {
                marks,
                decorations: buildDocDecorations(transaction.state, marks),
            };
        },

        provide: field => EditorView.decorations.from(field, value => value.decorations),
    });

    function initEpicrisisDocumentEditor() {
        const modal = document.querySelector('#modalAddEpicrisis');

        if (!modal || modal.dataset.docEditorBound === '1') {
            return;
        }

        const form = modal.querySelector('form');
        const textarea = modal.querySelector('textarea[name="text"]');

        if (!form || !textarea) {
            return;
        }

        modal.dataset.docEditorBound = '1';
        injectEpicrisisDocumentStyles();

        const jsonInput = ensureHiddenInput(form, 'text_json', 'epicrisisTextJson');
        const legacyToolbar = modal.querySelector('.epicrisis-toolbar');
        const legacyEditor = modal.querySelector('#epicrisisTextEditor');
        const legacyEditorValue = legacyEditor?.innerHTML?.trim() || legacyEditor?.textContent?.trim() || '';
        const initialDocument = normalizeInitialDocument(textarea.value || legacyEditorValue || '');

        const toolbar = buildEpicrisisToolbar();
        const existingDocEditor = modal.querySelector('#epicrisisDocEditor');
        const editorHost = existingDocEditor || document.createElement('div');
        editorHost.id = 'epicrisisDocEditor';
        editorHost.className = 'epicrisis-doc-editor';
        editorHost.innerHTML = '';
        editorHost.removeAttribute('contenteditable');

        if (legacyToolbar) {
            legacyToolbar.replaceWith(toolbar);
        } else {
            textarea.insertAdjacentElement('beforebegin', toolbar);
        }

        if (legacyEditor && legacyEditor !== editorHost) {
            legacyEditor.replaceWith(editorHost);
        } else if (!editorHost.isConnected) {
            textarea.insertAdjacentElement('beforebegin', editorHost);
        }

        const label = modal.querySelector('label[for="epicrisisTextEditor"]');
        if (label) {
            label.setAttribute('for', 'epicrisisDocEditor');
        }

        textarea.classList.add('d-none');
        textarea.required = false;
        textarea.style.display = 'none';

        const pendingInlineMarks = new Set();

        const view = new EditorView({
            state: EditorState.create({
                doc: initialDocument.text,
                extensions: [
                    history(),
                    drawSelection(),
                    dropCursor(),
                    keymap.of([
                        { key: 'Backspace', run: deleteBackward, shift: deleteBackward },
                        { key: 'Delete', run: deleteForward },
                        ...defaultKeymap,
                        ...historyKeymap,
                    ]),
                    EditorView.inputHandler.of((view, from, to, text) => {
                        return insertTextWithPendingMarks(view, from, to, text, pendingInlineMarks);
                    }),
                    EditorView.lineWrapping,
                    epicrisisDocField,
                    epicrisisDocumentTheme(),
                    EditorView.updateListener.of(update => {
                        if (update.docChanged) {
                            syncEpicrisisHiddenFields(update.view, textarea, jsonInput);
                        }
                    }),
                ],
            }),
            parent: editorHost,
        });

        modal.__epicrisisDocView = view;

        if (initialDocument.marks.length) {
            view.dispatch({
                effects: setDocMarksEffect.of(initialDocument.marks),
            });
        }

        syncEpicrisisHiddenFields(view, textarea, jsonInput);
        syncToolbarState(toolbar, pendingInlineMarks);

        toolbar.addEventListener('mousedown', event => {
            const button = event.target.closest('[data-epicrisis-doc-command]');

            if (button) {
                event.preventDefault();
            }
        });

        toolbar.addEventListener('click', event => {
            const button = event.target.closest('[data-epicrisis-doc-command]');

            if (!button) {
                return;
            }

            event.preventDefault();

            runEpicrisisDocCommand(
                button.dataset.epicrisisDocCommand,
                view,
                textarea,
                jsonInput,
                pendingInlineMarks,
                toolbar,
            );
        });

        qsa('[data-epicrisis-template]', modal).forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                loadEpicrisisTemplate(view);
                syncEpicrisisHiddenFields(view, textarea, jsonInput);
                view.focus();
            });
        });

        form.addEventListener('submit', event => {
            syncEpicrisisHiddenFields(view, textarea, jsonInput);

            const text = view.state.doc.toString().replace(/\s+/g, ' ').trim();

            if (!text) {
                event.preventDefault();
                view.focus();
                showEpicrisisEditorMessage('Заполните текст эпикриза.');
            }
        });

        modal.addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                view.requestMeasure();
                view.focus();
            }, 80);
        });
    }

    function initEpicrisisDiagnosisSearch() {
        const modal = document.querySelector('#modalAddEpicrisis');

        if (!modal || modal.dataset.diagnosisSearchBound === '1') {
            return;
        }

        const form = modal.querySelector('form');

        if (!form) {
            return;
        }

        modal.dataset.diagnosisSearchBound = '1';

        const select = modal.querySelector('#epicrisisDiagnosisSelect');
        const diagnosisIdInput = ensureHiddenInput(form, 'diagnose_id', 'epicrisisDiagnosisId');
        const hiddenCodeInput = ensureHiddenInput(form, 'mkb10', 'epicrisisMkb10');
        const diagnosisTitleInput = ensureHiddenInput(form, 'diagnosis_title', 'epicrisisDiagnosisTitle');
        let visibleInput = modal.querySelector('#epicrisisDiagnosisFallback') || modal.querySelector('#epicrisisDiagnosisSearch');

        if (select) {
            select.classList.add('d-none');
            select.removeAttribute('name');
        }

        if (!visibleInput) {
            visibleInput = document.createElement('input');
            visibleInput.id = 'epicrisisDiagnosisSearch';
            visibleInput.type = 'text';
            hiddenCodeInput.insertAdjacentElement('beforebegin', visibleInput);
        }

        visibleInput.type = 'text';
        visibleInput.classList.remove('d-none');
        visibleInput.classList.add('form-control');
        visibleInput.autocomplete = 'off';
        visibleInput.placeholder = visibleInput.placeholder || 'Начните вводить код или название диагноза';
        visibleInput.removeAttribute('name');

        hiddenCodeInput.type = 'hidden';
        diagnosisTitleInput.type = 'hidden';

        let wrapper = visibleInput.closest('.epicrisis-diagnosis-search');

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'epicrisis-diagnosis-search position-relative';
            visibleInput.insertAdjacentElement('beforebegin', wrapper);
            wrapper.appendChild(visibleInput);
        } else {
            wrapper.classList.add('position-relative');
        }

        const menu = document.createElement('div');
        menu.className = 'dropdown-menu epicrisis-diagnosis-menu';
        wrapper.appendChild(menu);

        let selectedBox = modal.querySelector('#epicrisisSelectedDiagnosis');

        if (!selectedBox) {
            selectedBox = document.createElement('div');
            selectedBox.id = 'epicrisisSelectedDiagnosis';
            selectedBox.className = 'epicrisis-selected-diagnosis d-none';
            selectedBox.innerHTML = `
                <div class="small text-muted mb-1">Выбранный диагноз</div>
                <div class="fw-semibold" id="epicrisisSelectedDiagnosisCode">—</div>
                <div class="small" id="epicrisisSelectedDiagnosisTitle">—</div>
            `;
            wrapper.insertAdjacentElement('afterend', selectedBox);
        }

        const selectedCode = modal.querySelector('#epicrisisSelectedDiagnosisCode') || selectedBox.querySelector('[data-diagnosis-code]');
        const selectedTitle = modal.querySelector('#epicrisisSelectedDiagnosisTitle') || selectedBox.querySelector('[data-diagnosis-title]');
        const searchUrls = buildDiagnosisSearchUrls(form.dataset.diagnosesUrl || modal.dataset.diagnosesUrl || EPICRISIS_DIAGNOSES_URL);

        const hideMenu = () => {
            menu.classList.remove('show');
            menu.innerHTML = '';
        };

        const setDiagnosis = diagnosis => {
            const id = isNumericId(diagnosis.id) ? String(diagnosis.id) : '';
            const code = diagnosis.code || '';
            const title = diagnosis.title || '';
            const label = diagnosis.text || [code, title].filter(Boolean).join(' — ');

            visibleInput.value = label;

            diagnosisIdInput.value = id;
            hiddenCodeInput.value = code;
            diagnosisTitleInput.value = title;

            selectedBox.classList.toggle('d-none', !code && !title);

            if (selectedCode) {
                selectedCode.textContent = code || '—';
            }

            if (selectedTitle) {
                selectedTitle.textContent = title || '—';
            }

            hideMenu();
        };

        const renderMenu = (items, message = '') => {
            menu.innerHTML = '';

            if (message) {
                const row = document.createElement('div');
                row.className = 'dropdown-item text-muted small';
                row.textContent = message;
                menu.appendChild(row);
                menu.classList.add('show');
                return;
            }

            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'dropdown-item text-muted small';
                empty.textContent = 'Ничего не найдено';
                menu.appendChild(empty);
                menu.classList.add('show');
                return;
            }

            items.forEach(item => {
                const button = document.createElement('button');

                button.type = 'button';
                button.className = 'dropdown-item epicrisis-diagnosis-item';
                button.innerHTML = `
                    <div class="fw-semibold">${escapeHtml(item.code || '—')}</div>
                    <div class="small text-muted">${escapeHtml(item.title || item.text || '')}</div>
                `;

                button.addEventListener('mousedown', event => event.preventDefault());
                button.addEventListener('click', () => setDiagnosis(item));

                menu.appendChild(button);
            });

            menu.classList.add('show');
        };

        const search = debounce(async term => {
            if (term.length < 2) {
                hideMenu();
                return;
            }

            try {
                renderMenu([], 'Ищем диагнозы...');
                const data = await fetchDiagnosisData(searchUrls, term);
                const items = normalizeDiagnosisResponse(data);
                renderMenu(items);
            } catch (error) {
                console.error(error);
                renderMenu([], 'Не удалось загрузить диагнозы');
            }
        }, 250);

        visibleInput.addEventListener('input', () => {
            const value = visibleInput.value.trim();

            diagnosisIdInput.value = '';
            hiddenCodeInput.value = value;
            diagnosisTitleInput.value = '';

            selectedBox.classList.add('d-none');

            search(value);
        });

        visibleInput.addEventListener('focus', () => {
            const term = visibleInput.value.trim();

            if (term.length >= 2) {
                search(term);
            }
        });

        visibleInput.addEventListener('blur', () => {
            setTimeout(hideMenu, 160);
        });
    }

    function runEpicrisisDocCommand(command, view, textarea, jsonInput, pendingInlineMarks, toolbar) {
        switch (command) {
            case 'undo':
                undo(view);
                break;

            case 'redo':
                redo(view);
                break;

            case 'bold':
            case 'italic':
            case 'underline':
                applyInlineMarkOrTogglePending(view, command, pendingInlineMarks, toolbar);
                break;

            case 'h3':
            case 'h4':
            case 'p':
            case 'ul':
            case 'ol':
                applyBlockMark(view, command);
                break;

            case 'align-left':
            case 'align-center':
            case 'align-right':
                applyAlignmentMark(view, command);
                break;

            case 'clear':
                pendingInlineMarks.clear();
                syncToolbarState(toolbar, pendingInlineMarks);
                clearSelectedFormatting(view);
                break;

            case 'template':
                loadEpicrisisTemplate(view);
                break;

            default:
                break;
        }

        syncEpicrisisHiddenFields(view, textarea, jsonInput);
    }

    function applyInlineMarkOrTogglePending(view, type, pendingInlineMarks, toolbar) {
        const effects = [];

        for (const range of view.state.selection.ranges) {
            if (range.empty) {
                continue;
            }

            effects.push(addDocMarkEffect.of({
                id: makeDocMarkId(),
                kind: 'inline',
                type,
                from: Math.min(range.from, range.to),
                to: Math.max(range.from, range.to),
            }));
        }

        if (!effects.length) {
            if (pendingInlineMarks.has(type)) {
                pendingInlineMarks.delete(type);
            } else {
                pendingInlineMarks.add(type);
            }

            syncToolbarState(toolbar, pendingInlineMarks);
            view.focus();
            return;
        }

        view.dispatch({
            effects,
            scrollIntoView: true,
        });

        view.focus();
    }

    function insertTextWithPendingMarks(view, from, to, text, pendingInlineMarks) {
        if (!text || !pendingInlineMarks.size) {
            return false;
        }

        const insertTo = from + text.length;
        const effects = Array.from(pendingInlineMarks).map(type => addDocMarkEffect.of({
            id: makeDocMarkId(),
            kind: 'inline',
            type,
            from,
            to: insertTo,
        }));

        view.dispatch({
            changes: { from, to, insert: text },
            selection: { anchor: insertTo },
            effects,
            scrollIntoView: true,
        });

        return true;
    }

    function applyBlockMark(view, type) {
        const effects = [];

        selectedLineRanges(view).forEach(line => {
            effects.push(clearDocMarksEffect.of({
                from: line.from,
                to: line.to,
                categories: ['block'],
            }));

            effects.push(addDocMarkEffect.of({
                id: makeDocMarkId(),
                kind: 'block',
                type,
                from: line.from,
                to: line.to,
            }));
        });

        view.dispatch({
            effects,
            scrollIntoView: true,
        });

        view.focus();
    }

    function applyAlignmentMark(view, type) {
        const effects = [];

        selectedLineRanges(view).forEach(line => {
            effects.push(clearDocMarksEffect.of({
                from: line.from,
                to: line.to,
                categories: ['align'],
            }));

            effects.push(addDocMarkEffect.of({
                id: makeDocMarkId(),
                kind: 'block',
                type,
                from: line.from,
                to: line.to,
            }));
        });

        view.dispatch({
            effects,
            scrollIntoView: true,
        });

        view.focus();
    }

    function clearSelectedFormatting(view) {
        const effects = [];

        view.state.selection.ranges.forEach(range => {
            if (range.empty) {
                const line = view.state.doc.lineAt(range.from);

                effects.push(clearDocMarksEffect.of({
                    from: line.from,
                    to: Math.max(line.to, line.from + 1),
                    categories: ['bold', 'italic', 'underline', 'block', 'align'],
                }));

                return;
            }

            effects.push(clearDocMarksEffect.of({
                from: Math.min(range.from, range.to),
                to: Math.max(range.from, range.to),
                categories: ['bold', 'italic', 'underline', 'block', 'align'],
            }));
        });

        view.dispatch({
            effects,
            scrollIntoView: true,
        });

        view.focus();
    }

    function deleteBackward(view) {
        const spec = view.state.changeByRange(range => {
            const from = Math.min(range.from, range.to);
            const to = Math.max(range.from, range.to);

            if (from !== to) {
                return {
                    changes: { from, to, insert: '' },
                    range: EditorSelection.cursor(from),
                };
            }

            if (from <= 0) {
                return { range: EditorSelection.cursor(from) };
            }

            const deleteFrom = previousCharBoundary(view.state, from);

            return {
                changes: { from: deleteFrom, to: from, insert: '' },
                range: EditorSelection.cursor(deleteFrom),
            };
        });

        view.dispatch(spec);
        return true;
    }

    function deleteForward(view) {
        const spec = view.state.changeByRange(range => {
            const from = Math.min(range.from, range.to);
            const to = Math.max(range.from, range.to);

            if (from !== to) {
                return {
                    changes: { from, to, insert: '' },
                    range: EditorSelection.cursor(from),
                };
            }

            if (from >= view.state.doc.length) {
                return { range: EditorSelection.cursor(from) };
            }

            const deleteTo = nextCharBoundary(view.state, from);

            return {
                changes: { from, to: deleteTo, insert: '' },
                range: EditorSelection.cursor(from),
            };
        });

        view.dispatch(spec);
        return true;
    }

    function previousCharBoundary(state, pos) {
        const start = Math.max(0, pos - 8);
        const chunk = state.sliceDoc(start, pos);
        const chars = Array.from(chunk);
        const char = chars[chars.length - 1];

        return char ? pos - char.length : Math.max(0, pos - 1);
    }

    function nextCharBoundary(state, pos) {
        const end = Math.min(state.doc.length, pos + 8);
        const chunk = state.sliceDoc(pos, end);
        const chars = Array.from(chunk);
        const char = chars[0];

        return char ? pos + char.length : Math.min(state.doc.length, pos + 1);
    }

    function loadEpicrisisTemplate(view) {
        const template = createEpicrisisTemplate();

        view.dispatch({
            changes: {
                from: 0,
                to: view.state.doc.length,
                insert: template.text,
            },
            effects: setDocMarksEffect.of(template.marks),
            selection: {
                anchor: template.text.length,
            },
            scrollIntoView: true,
        });

        view.focus();
    }

    function createEpicrisisTemplate() {
        const lines = [
            'Эпикриз',
            '',
            'Жалобы:',
            '',
            'Анамнез заболевания:',
            '',
            'Объективный / психический статус:',
            '',
            'Проведённое обследование:',
            '',
            'Диагноз:',
            '',
            'Проведённое лечение:',
            '',
            'Состояние при завершении наблюдения:',
            '',
            'Рекомендации:',
            '',
        ];

        const text = lines.join('\n');
        const offsets = getLineOffsets(text);
        const marks = [];

        marks.push({
            id: makeDocMarkId(),
            kind: 'block',
            type: 'h3',
            from: offsets[0].from,
            to: offsets[0].to,
        });

        [
            'Жалобы:',
            'Анамнез заболевания:',
            'Объективный / психический статус:',
            'Проведённое обследование:',
            'Диагноз:',
            'Проведённое лечение:',
            'Состояние при завершении наблюдения:',
            'Рекомендации:',
        ].forEach(label => {
            const index = text.indexOf(label);

            if (index >= 0) {
                marks.push({
                    id: makeDocMarkId(),
                    kind: 'inline',
                    type: 'bold',
                    from: index,
                    to: index + label.length,
                });
            }
        });

        return { text, marks };
    }

    function buildEpicrisisToolbar() {
        const toolbar = document.createElement('div');

        toolbar.className = 'epicrisis-doc-toolbar';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Форматирование эпикриза');

        toolbar.innerHTML = `
            <div class="btn-group btn-group-sm me-1 mb-1">
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="undo" title="Отменить">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="redo" title="Повторить">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>

            <div class="btn-group btn-group-sm me-1 mb-1">
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="bold" title="Жирный"><strong>Ж</strong></button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="italic" title="Курсив"><em>К</em></button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="underline" title="Подчёркивание"><u>Ч</u></button>
            </div>

            <div class="btn-group btn-group-sm me-1 mb-1">
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="h3" title="Крупный заголовок">H3</button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="h4" title="Заголовок">H4</button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="p" title="Обычный абзац">P</button>
            </div>

            <div class="btn-group btn-group-sm me-1 mb-1">
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="ul" title="Маркированный список"><i class="bi bi-list-ul"></i></button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="ol" title="Нумерованный список"><i class="bi bi-list-ol"></i></button>
            </div>

            <div class="btn-group btn-group-sm me-1 mb-1">
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="align-left" title="По левому краю"><i class="bi bi-text-left"></i></button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="align-center" title="По центру"><i class="bi bi-text-center"></i></button>
                <button type="button" class="btn btn-light" data-epicrisis-doc-command="align-right" title="По правому краю"><i class="bi bi-text-right"></i></button>
            </div>

            <div class="btn-group btn-group-sm me-1 mb-1">
                <button type="button" class="btn btn-outline-secondary" data-epicrisis-doc-command="clear" title="Очистить форматирование"><i class="bi bi-eraser"></i></button>
                <button type="button" class="btn btn-outline-primary" data-epicrisis-doc-command="template">Шаблон</button>
            </div>
        `;

        return toolbar;
    }

    function syncToolbarState(toolbar, pendingInlineMarks) {
        ['bold', 'italic', 'underline'].forEach(command => {
            const button = toolbar.querySelector(`[data-epicrisis-doc-command="${command}"]`);

            if (!button) {
                return;
            }

            button.classList.toggle('active', pendingInlineMarks.has(command));
            button.setAttribute('aria-pressed', pendingInlineMarks.has(command) ? 'true' : 'false');
        });
    }

    function syncEpicrisisHiddenFields(view, textarea, jsonInput) {
        const documentModel = serializeEpicrisisDocument(view);
        const html = epicrisisDocumentToHtml(documentModel);

        textarea.value = html;
        jsonInput.value = JSON.stringify(documentModel);
    }

    function serializeEpicrisisDocument(view) {
        const fieldValue = view.state.field(epicrisisDocField, false);

        return {
            version: 1,
            format: 'epicrisis-document',
            text: view.state.doc.toString(),
            marks: fieldValue ? normalizeDocMarks(fieldValue.marks, view.state.doc.length) : [],
        };
    }

    function epicrisisDocumentToHtml(documentModel) {
        const text = documentModel.text || '';
        const marks = normalizeDocMarks(documentModel.marks || [], text.length);
        const offsets = getLineOffsets(text);

        let html = '';
        let openList = null;

        offsets.forEach(line => {
            const lineText = text.slice(line.from, line.to);
            const blockMark = getLineBlockMark(marks, line, 'block');
            const alignMark = getLineBlockMark(marks, line, 'align');
            const alignAttribute = getAlignHtmlAttribute(alignMark);
            const body = renderInlineText(lineText, line.from, marks);

            if (blockMark?.type === 'ul' || blockMark?.type === 'ol') {
                if (openList !== blockMark.type) {
                    if (openList) {
                        html += `</${openList}>`;
                    }

                    openList = blockMark.type;
                    html += `<${openList}>`;
                }

                html += `<li${alignAttribute}>${body || '&nbsp;'}</li>`;
                return;
            }

            if (openList) {
                html += `</${openList}>`;
                openList = null;
            }

            const tag = blockMark?.type === 'h3' || blockMark?.type === 'h4' ? blockMark.type : 'p';
            html += `<${tag}${alignAttribute}>${body || '<br>'}</${tag}>`;
        });

        if (openList) {
            html += `</${openList}>`;
        }

        return html.trim();
    }

    function renderInlineText(lineText, lineFrom, marks) {
        if (!lineText.length) {
            return '';
        }

        const inlineMarks = marks
            .filter(mark => mark.kind === 'inline')
            .filter(mark => rangesOverlap(mark.from, mark.to, lineFrom, lineFrom + lineText.length));

        if (!inlineMarks.length) {
            return escapeHtml(lineText);
        }

        const points = new Set([0, lineText.length]);

        inlineMarks.forEach(mark => {
            points.add(clamp(mark.from - lineFrom, 0, lineText.length));
            points.add(clamp(mark.to - lineFrom, 0, lineText.length));
        });

        const sortedPoints = [...points].sort((a, b) => a - b);
        let html = '';

        for (let index = 0; index < sortedPoints.length - 1; index += 1) {
            const from = sortedPoints[index];
            const to = sortedPoints[index + 1];

            if (to <= from) {
                continue;
            }

            const segment = lineText.slice(from, to);
            const activeMarks = inlineMarks.filter(mark => mark.from <= lineFrom + from && mark.to >= lineFrom + to);
            html += wrapInlineHtml(escapeHtml(segment), activeMarks);
        }

        return html;
    }

    function wrapInlineHtml(html, marks) {
        const activeTypes = new Set(marks.map(mark => mark.type));
        let result = html;

        if (activeTypes.has('underline')) {
            result = `<u>${result}</u>`;
        }

        if (activeTypes.has('italic')) {
            result = `<em>${result}</em>`;
        }

        if (activeTypes.has('bold')) {
            result = `<strong>${result}</strong>`;
        }

        return result;
    }

    function normalizeInitialDocument(value) {
        const raw = String(value || '').trim();

        if (!raw) {
            return { text: '', marks: [] };
        }

        if (raw.startsWith('{')) {
            try {
                const parsed = JSON.parse(raw);

                if (parsed && parsed.format === 'epicrisis-document') {
                    const text = String(parsed.text || '');

                    return {
                        text,
                        marks: normalizeDocMarks(parsed.marks || [], text.length),
                    };
                }
            } catch {
                // Ниже обработаем как HTML/текст.
            }
        }

        return htmlOrTextToPlainDocument(raw);
    }

    function htmlOrTextToPlainDocument(value) {
        const container = document.createElement('div');
        container.innerHTML = String(value || '');

        const text = (container.textContent || String(value || ''))
            .replace(/\r\n/g, '\n')
            .replace(/\n{3,}/g, '\n\n')
            .trim();

        return { text, marks: [] };
    }

    function selectedLineRanges(view) {
        const ranges = [];
        const seen = new Set();

        for (const selectionRange of view.state.selection.ranges) {
            const from = Math.min(selectionRange.from, selectionRange.to);
            const to = Math.max(selectionRange.from, selectionRange.to);
            let line = view.state.doc.lineAt(from);

            while (true) {
                if (!seen.has(line.number)) {
                    seen.add(line.number);

                    ranges.push({
                        from: line.from,
                        to: Math.max(line.to, line.from + 1),
                        number: line.number,
                    });
                }

                if (line.to >= to || line.number >= view.state.doc.lines) {
                    break;
                }

                line = view.state.doc.line(line.number + 1);
            }
        }

        return ranges;
    }

    function buildDocDecorations(state, marks) {
        const ranges = [];

        marks.forEach(mark => {
            if (mark.kind === 'inline') {
                const className = getInlineDecorationClass(mark.type);

                if (!className || mark.to <= mark.from) {
                    return;
                }

                ranges.push(Decoration.mark({ class: className }).range(mark.from, mark.to));
                return;
            }

            if (mark.kind === 'block') {
                const className = getBlockDecorationClass(mark.type);

                if (!className) {
                    return;
                }

                const position = clamp(mark.from, 0, state.doc.length);
                const line = state.doc.lineAt(position);

                ranges.push(Decoration.line({ class: className }).range(line.from));
            }
        });

        return Decoration.set(ranges, true);
    }

    function mapDocMark(mark, changes, docLength) {
        if (!mark) {
            return null;
        }

        if (changes.empty) {
            return {
                ...mark,
                from: clamp(mark.from, 0, docLength),
                to: clamp(mark.to, 0, Math.max(docLength, 1)),
            };
        }

        const from = clamp(changes.mapPos(mark.from, 1), 0, docLength);
        const to = clamp(changes.mapPos(mark.to, -1), 0, Math.max(docLength, 1));

        if (mark.kind === 'block') {
            return {
                ...mark,
                from,
                to: Math.max(from, to),
            };
        }

        if (to <= from) {
            return null;
        }

        return { ...mark, from, to };
    }

    function normalizeDocMarks(marks, docLength) {
        return (marks || [])
            .map(mark => {
                const kind = mark.kind === 'block' ? 'block' : 'inline';
                const from = clamp(Number(mark.from || 0), 0, docLength);
                const to = clamp(Number(mark.to || 0), 0, Math.max(docLength, 1));

                return {
                    id: mark.id || makeDocMarkId(),
                    kind,
                    type: String(mark.type || ''),
                    from,
                    to: kind === 'block' ? Math.max(from, to) : to,
                };
            })
            .filter(mark => mark.type && (mark.kind === 'block' || mark.to > mark.from));
    }

    function clearDocMarks(marks, payload) {
        const from = Number(payload?.from || 0);
        const to = Number(payload?.to || from + 1);
        const categories = payload?.categories || [];

        return marks.filter(mark => {
            const category = getDocMarkCategory(mark);

            if (!categories.includes(category)) {
                return true;
            }

            return !rangesOverlap(mark.from, Math.max(mark.to, mark.from + 1), from, to);
        });
    }

    function getDocMarkCategory(mark) {
        if (mark.kind === 'inline') {
            return mark.type;
        }

        if (['h3', 'h4', 'p', 'ul', 'ol'].includes(mark.type)) {
            return 'block';
        }

        if (mark.type.startsWith('align-')) {
            return 'align';
        }

        return mark.type;
    }

    function getLineBlockMark(marks, line, category) {
        const lineFrom = line.from;
        const lineTo = Math.max(line.to, line.from + 1);
        const candidates = marks
            .filter(mark => mark.kind === 'block')
            .filter(mark => getDocMarkCategory(mark) === category)
            .filter(mark => mark.from <= lineFrom && Math.max(mark.to, mark.from + 1) >= lineTo);

        return candidates[candidates.length - 1] || null;
    }

    function getInlineDecorationClass(type) {
        return {
            bold: 'epicrisis-doc-bold',
            italic: 'epicrisis-doc-italic',
            underline: 'epicrisis-doc-underline',
        }[type] || '';
    }

    function getBlockDecorationClass(type) {
        return {
            h3: 'epicrisis-doc-h3',
            h4: 'epicrisis-doc-h4',
            p: 'epicrisis-doc-p',
            ul: 'epicrisis-doc-list-ul',
            ol: 'epicrisis-doc-list-ol',
            'align-left': 'epicrisis-doc-align-left',
            'align-center': 'epicrisis-doc-align-center',
            'align-right': 'epicrisis-doc-align-right',
        }[type] || '';
    }

    function getAlignHtmlAttribute(mark) {
        if (!mark) {
            return '';
        }

        if (mark.type === 'align-center') {
            return ' style="text-align: center;"';
        }

        if (mark.type === 'align-right') {
            return ' style="text-align: right;"';
        }

        return '';
    }

    function getLineOffsets(text) {
        const lines = String(text || '').split('\n');
        const offsets = [];
        let position = 0;

        lines.forEach((line, index) => {
            offsets.push({
                number: index + 1,
                from: position,
                to: position + line.length,
            });

            position += line.length + 1;
        });

        if (!offsets.length) {
            offsets.push({ number: 1, from: 0, to: 0 });
        }

        return offsets;
    }

    function buildDiagnosisSearchUrls(rawUrl) {
        const urls = [];
        const add = value => {
            if (!value || urls.includes(value)) {
                return;
            }

            urls.push(value);
        };

        const normalized = String(rawUrl || '').trim() || EPICRISIS_DIAGNOSES_URL;

        add(normalized.replace(/\/$/, ''));
        add('/api/diagnoses');

        return urls;
    }

    async function fetchDiagnosisData(urls, term) {
        let lastError = null;

        for (const sourceUrl of urls) {
            try {
                const url = new URL(sourceUrl, window.location.origin);
                url.searchParams.set('q', term);
                url.searchParams.set('term', term);
                url.searchParams.set('search', term);

                return await fetchJson(url.toString());
            } catch (error) {
                lastError = error;
            }
        }

        throw lastError || new Error('Diagnosis search failed');
    }

    function normalizeDiagnosisResponse(data) {
        const rows = Array.isArray(data)
            ? data
            : data?.results || data?.data || data?.items || [];

        return rows
            .map(item => {
                const rawId = item.diagnose_id ?? item.diagnosis_id ?? item.real_id ?? item.model_id ?? item.value ?? item.id ?? '';
                const id = isNumericId(rawId) ? String(rawId) : '';

                const rawText = String(item.text || item.label || item.name || item.title || item.id || '').trim();

                let code = String(item.code || item.mkb10 || item.icd10 || item.icd_code || '').trim();
                let title = String(item.title || item.name || item.diagnosis_title || item.label || '').trim();

                if (!code && rawText) {
                    const match = rawText.match(/^([A-ZА-Я]\d{2}(?:\.\d+)?)\s*[—-]\s*(.+)$/iu);

                    if (match) {
                        code = match[1].trim();
                        title = title || match[2].trim();
                    }
                }

                if (!title && rawText) {
                    title = code && rawText.startsWith(code)
                        ? rawText.replace(code, '').replace(/^[\s—-]+/, '').trim()
                        : rawText;
                }

                const text = item.text || [code, title].filter(Boolean).join(' — ');

                return {
                    id,
                    code,
                    title,
                    text,
                };
            })
            .filter(item => item.code || item.title || item.text);
    }

    function isNumericId(value) {
        return typeof value === 'number'
            ? Number.isInteger(value) && value > 0
            : /^\d+$/.test(String(value || '').trim());
    }

    function epicrisisDocumentTheme() {
        return EditorView.theme({
            '&': {
                border: '1px solid #e5e7eb',
                borderRadius: '16px',
                background: '#eef2f7',
                overflow: 'hidden',
            },
            '&.cm-focused': {
                outline: '0',
                boxShadow: '0 0 0 .25rem rgba(13, 110, 253, .12)',
                borderColor: 'rgba(13, 110, 253, .55)',
            },
            '.cm-scroller': {
                maxHeight: 'calc(100vh - 300px)',
                overflow: 'auto',
                fontFamily: 'Arial, "Times New Roman", serif',
                fontSize: '15px',
                lineHeight: '1.6',
            },
            '.cm-content': {
                width: '794px',
                maxWidth: 'calc(100% - 28px)',
                minHeight: '720px',
                margin: '24px auto',
                padding: '64px 72px',
                background: '#fff',
                boxShadow: '0 14px 38px rgba(15, 23, 42, .16)',
                borderRadius: '2px',
                caretColor: '#111827',
                counterReset: 'epicrisis-doc-ol',
                whiteSpace: 'pre-wrap',
            },
            '.cm-line': {
                padding: '0',
                minHeight: '1.55em',
            },
            '.cm-selectionBackground': {
                background: 'rgba(13, 110, 253, .22) !important',
            },
            '.epicrisis-doc-bold': {
                fontWeight: '700',
            },
            '.epicrisis-doc-italic': {
                fontStyle: 'italic',
            },
            '.epicrisis-doc-underline': {
                textDecoration: 'underline',
            },
            '.epicrisis-doc-h3': {
                fontSize: '1.35rem',
                fontWeight: '700',
                lineHeight: '1.35',
                marginTop: '.35rem',
                marginBottom: '.45rem',
            },
            '.epicrisis-doc-h4': {
                fontSize: '1.1rem',
                fontWeight: '700',
                lineHeight: '1.35',
                marginTop: '.25rem',
                marginBottom: '.35rem',
            },
            '.epicrisis-doc-list-ul': {
                paddingLeft: '1.4rem',
                position: 'relative',
            },
            '.epicrisis-doc-list-ul::before': {
                content: '"•"',
                position: 'absolute',
                left: '.25rem',
            },
            '.epicrisis-doc-list-ol': {
                paddingLeft: '1.6rem',
                position: 'relative',
                counterIncrement: 'epicrisis-doc-ol',
            },
            '.epicrisis-doc-list-ol::before': {
                content: 'counter(epicrisis-doc-ol) "."',
                position: 'absolute',
                left: '.25rem',
                fontWeight: '600',
            },
            '.epicrisis-doc-align-left': {
                textAlign: 'left',
            },
            '.epicrisis-doc-align-center': {
                textAlign: 'center',
            },
            '.epicrisis-doc-align-right': {
                textAlign: 'right',
            },
        });
    }

    function injectEpicrisisDocumentStyles() {
        if (document.querySelector('#epicrisisDocumentEditorStyles')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'epicrisisDocumentEditorStyles';
        style.textContent = `
            .epicrisis-doc-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 4px;
                padding: 8px;
                margin-bottom: 8px;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                background: #f8fafc;
            }

            .epicrisis-doc-toolbar .btn {
                border-radius: 9px;
                min-width: 34px;
                font-size: 13px;
            }

            .epicrisis-doc-toolbar .btn.active {
                color: #0d6efd;
                background: #e8f1ff;
                border-color: #9ec5fe;
            }

            .epicrisis-diagnosis-search {
                width: 100%;
            }

            .epicrisis-diagnosis-menu {
                width: 100%;
                max-height: 280px;
                overflow-y: auto;
                z-index: 2200;
                border-radius: 12px;
                box-shadow: 0 12px 32px rgba(15, 23, 42, .14);
            }

            .epicrisis-diagnosis-item {
                white-space: normal;
                text-align: left;
            }

            .epicrisis-selected-diagnosis {
                border: 1px solid #dbeafe;
                background: #eff6ff;
                border-radius: 14px;
                padding: 12px;
                color: #1e3a8a;
                margin-top: 10px;
            }

            @media print {
                .epicrisis-doc-toolbar {
                    display: none;
                }

                .epicrisis-doc-editor {
                    border: 0 !important;
                }

                .epicrisis-doc-editor .cm-content {
                    box-shadow: none !important;
                    margin: 0 !important;
                }
            }
        `;

        document.head.appendChild(style);
    }

    function ensureHiddenInput(form, name, id) {
        let input = form.querySelector(`[name="${name}"]`);

        if (input) {
            if (id && !input.id) {
                input.id = id;
            }

            return input;
        }

        input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.id = id;
        form.appendChild(input);

        return input;
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function rangesOverlap(aFrom, aTo, bFrom, bTo) {
        return aFrom < bTo && aTo > bFrom;
    }

    function makeDocMarkId() {
        return `mark_${Date.now()}_${Math.random().toString(36).slice(2)}`;
    }

    function showEpicrisisEditorMessage(message) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, 'warning');
            return;
        }

        showToast(message, 'warning');
    }

    document.addEventListener('DOMContentLoaded', () => {
        initEpicrisisDiagnosisSearch();
        initEpicrisisDocumentEditor();
    });
})();

function initTestAssignments() {
    const root = qs('#testsDoctorArea');

    if (!root || root.dataset.testsInit === '1') {
        return;
    }

    root.dataset.testsInit = '1';

    const assignmentsList = qs('#assignmentsList', root);
    const emptyState = qs('#testsEmpty', root);
    const assignModal = qs('#assignTestModal');
    const assignForm = qs('#assignTestForm');
    const assignSelect = qs('#assignTestSelect');

    const assignAccessEmpty = qs('#assignTestAccessEmpty', assignModal);
    const assignAccessDetails = qs('#assignTestAccessDetails', assignModal);
    const assignTypeIcon = qs('#assignTestTypeIcon', assignModal);
    const assignTestName = qs('#assignTestName', assignModal);
    const assignTestMeta = qs('#assignTestMeta', assignModal);
    const assignAccessLabel = qs('#assignTestAccessLabel', assignModal);
    const assignAccessHint = qs('#assignTestAccessHint', assignModal);
    const assignCanAssign = qs('#assignCanAssign', assignModal);
    const assignCanConduct = qs('#assignCanConduct', assignModal);
    const assignCanViewResults = qs('#assignCanViewResults', assignModal);
    const assignWarning = qs('#assignTestWarning', assignModal);
    const assignSubmitButton = qs('#assignTestSubmitBtn', assignForm);

    const assignAccessSourceType = qs('#assignTestAccessSourceType', assignForm);
    const assignAccessRuleId = qs('#assignTestAccessRuleId', assignForm);
    const assignPracticeContext = qs('#assignTestPracticeContext', assignForm);

    function setAssignText(element, value) {
        if (element) {
            element.textContent = value || '—';
        }
    }

    function setAssignValue(element, value) {
        if (element) {
            element.value = value || '';
        }
    }

    function setAssignPermission(element, enabled) {
        element?.classList.toggle('is-active', Boolean(enabled));
    }

    function updateAssignSubmitState() {
        const item = window.__selectedAssignTestData
            ? normalizeTestItem(window.__selectedAssignTestData)
            : null;

        if (assignSubmitButton) {
            assignSubmitButton.disabled = !item || item.can_assign === false;
        }
    }

    function getAssignAccessDefaults(item) {
        const sourceType = String(item.access_source_type || '').toLowerCase();

        switch (sourceType) {
            case 'builtin':
            case 'system':
                return {
                    label: 'Системный тест',
                    hint: 'Общий тест «из коробки», доступен всем врачам.'
                };

            case 'owner':
            case 'doctor':
                return {
                    label: 'Личный тест врача',
                    hint: 'Тест создан текущим врачом.'
                };

            case 'clinic':
                return {
                    label: 'Тест клиники',
                    hint: 'Тест доступен через клинику, к которой привязан врач.'
                };

            case 'global':
            case 'granted':
            case 'doctor_access':
                return {
                    label: 'Доступ выдан врачу',
                    hint: 'Врачу отдельно выдан доступ к этому тесту.'
                };

            default:
                return {
                    label: 'Доступный тест',
                    hint: 'Тест разрешён для назначения текущим врачом.'
                };
        }
    }

    function setAssignTypeIcon(type) {
        if (!assignTypeIcon) {
            return;
        }

        const typeName = String(type || '').toLowerCase();

        const iconClass = {
            questionnaire: 'bi-ui-checks',
            image: 'bi-images',
            card_sort: 'bi-grid-3x3-gap'
        }[typeName] || 'bi-ui-checks';

        assignTypeIcon.innerHTML = `<i class="bi ${iconClass}"></i>`;
    }

    function resetAssignAccessPanel() {
        window.__selectedAssignTestData = null;

        assignAccessEmpty?.classList.remove('d-none');
        assignAccessDetails?.classList.add('d-none');

        setAssignText(assignTestName, '—');
        setAssignText(assignTestMeta, '—');
        setAssignText(assignAccessLabel, '—');
        setAssignText(assignAccessHint, '—');

        setAssignValue(assignAccessSourceType, '');
        setAssignValue(assignAccessRuleId, '');
        setAssignValue(assignPracticeContext, '');

        setAssignPermission(assignCanAssign, false);
        setAssignPermission(assignCanConduct, false);
        setAssignPermission(assignCanViewResults, false);

        assignWarning?.classList.add('d-none');
        qs('span', assignWarning)?.replaceChildren();

        updateAssignSubmitState();
    }

    function renderAssignAccessPanel(rawItem) {
        const item = normalizeTestItem(rawItem);

        if (!item) {
            resetAssignAccessPanel();
            return;
        }

        window.__selectedAssignTestData = item;

        assignAccessEmpty?.classList.add('d-none');
        assignAccessDetails?.classList.remove('d-none');

        setAssignTypeIcon(item.type);

        const meta = [
            item.code ? `Код: ${item.code}` : '',
            item.type_label || getAssignTestTypeLabel(item.type),
            item.duration ? `${item.duration} мин.` : ''
        ].filter(Boolean).join(' · ');

        const accessDefaults = getAssignAccessDefaults(item);

        setAssignText(assignTestName, item.name || item.text);
        setAssignText(assignTestMeta, meta || '—');
        setAssignText(assignAccessLabel, item.access_label || accessDefaults.label);
        setAssignText(assignAccessHint, item.access_hint || accessDefaults.hint);

        setAssignValue(assignAccessSourceType, item.access_source_type);
        setAssignValue(assignAccessRuleId, item.access_rule_id);
        setAssignValue(assignPracticeContext, item.practice_context);

        setAssignPermission(assignCanAssign, item.can_assign);
        setAssignPermission(assignCanConduct, item.can_conduct);
        setAssignPermission(assignCanViewResults, item.can_view_results);

        const warningText = item.warning || '';

        if (assignWarning) {
            const warningSpan = qs('span', assignWarning);

            if (warningText) {
                if (warningSpan) {
                    warningSpan.textContent = warningText;
                }

                assignWarning.classList.remove('d-none');
            } else {
                assignWarning.classList.add('d-none');

                if (warningSpan) {
                    warningSpan.textContent = '';
                }
            }
        }

        updateAssignSubmitState();
    }

    const startModal = qs('#testStartModal');
    const pinValue = qs('#test-pin', startModal);
    const pinTimer = qs('#pin-timer', startModal);
    const patientLink = qs('#patient-link', startModal);
    const patientLinkText = qs('#patient-link-text', startModal);
    const pinQrCanvas = qs('#pinQrCanvas', startModal);
    const copyPinButton = qs('#copy-pin', startModal);
    const copyLinkButton = qs('#copy-link', startModal);
    const openHereButton = qs('#open-here', startModal);

    const urlAssignments = root.dataset.urlAssignments || '';
    const urlStartTemplate = root.dataset.urlStart || '';
    const urlCancelTemplate = root.dataset.urlCancel || '';
    const urlResultTemplate = root.dataset.urlResult || '';
    const urlFinishTemplate = root.dataset.urlFinish || '';
    const urlSessionShowTemplate = root.dataset.urlSessionShow || '';
    const testingBase = String(root.dataset.testingBase || '').replace(/\/+$/, '');

    let pinTimerId = null;
    let currentPatientUrl = '';
    let currentPin = '';

    function replaceId(template, id) {
        return String(template || '')
            .replace('__SID__', encodeURIComponent(id))
            .replace('__AID__', encodeURIComponent(id));
    }

    function normalizeArray(payload) {
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload?.sessions)) return payload.sessions;
        if (Array.isArray(payload?.assignments)) return payload.assignments;
        if (Array.isArray(payload?.items)) return payload.items;
        if (Array.isArray(payload?.data)) return payload.data;
        if (Array.isArray(payload?.results)) return payload.results;

        return [];
    }

    function statusMeta(statusValue) {
        const status = String(statusValue || '').trim().toLowerCase();

        const map = {
            assigned: ['Назначен', 'bg-secondary'],
            started: ['Начат', 'bg-info'],
            in_progress: ['В процессе', 'bg-primary'],
            awaiting_unlock: ['Ожидает врача', 'bg-warning text-dark'],
            submitted: ['Пройден', 'bg-warning text-dark'],
            in_review: ['Проверяется', 'bg-warning text-dark'],
            coding_done: ['Оценка внесена', 'bg-info'],
            completed: ['Готово', 'bg-success'],
            finished: ['Завершён', 'bg-success'],
            cancelled: ['Отменён', 'bg-danger'],
            canceled: ['Отменён', 'bg-danger'],
            timeout: ['Истекло время', 'bg-dark']
        };

        const item = map[status] || [statusValue || '—', 'bg-light text-dark border'];

        return {
            label: item[0],
            badge: item[1],
            raw: status
        };
    }

    function assignmentId(item) {
        return item?.assignment_id || item?.assignmentId || item?.id || '';
    }

    function sessionId(item) {
        return item?.session_id || item?.sessionId || item?.last_session_id || item?.id || '';
    }

    function testTitle(item) {
        const code = item?.code || item?.test_code || item?.alias || '';
        const name = item?.test_name || item?.name || item?.title || item?.text || 'Тест';

        return code
            ? `<strong>${escapeHtml(code)}</strong> — ${escapeHtml(name)}`
            : escapeHtml(name);
    }

    function renderAssignments(items) {
        if (!assignmentsList) {
            return;
        }

        if (!items.length) {
            assignmentsList.innerHTML = '';
            emptyState?.classList.remove('d-none');
            return;
        }

        emptyState?.classList.add('d-none');

        assignmentsList.innerHTML = items.map(item => {
            const aid = assignmentId(item);
            const sid = sessionId(item);
            const status = statusMeta(item.status || item.assignment_status || item.session_status || item.status_label);
            const createdAt = item.created_at || item.assigned_at || item.started_at || '';

            const canOpenSession = Boolean(sid && urlSessionShowTemplate);
            const canShowResult = Boolean(
                sid &&
                urlResultTemplate &&
                ['submitted', 'in_review', 'coding_done', 'completed', 'finished'].includes(status.raw)
            );
            const canStart = Boolean(
                aid &&
                urlStartTemplate &&
                !['completed', 'finished', 'cancelled', 'canceled', 'timeout'].includes(status.raw)
            );
            const canFinish = Boolean(
                sid &&
                urlFinishTemplate &&
                ['submitted', 'in_review', 'coding_done'].includes(status.raw)
            );
            const canCancel = Boolean(
                sid &&
                urlCancelTemplate &&
                !['completed', 'finished', 'cancelled', 'canceled'].includes(status.raw)
            );

            return `
                <div class="mc-card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0">
                            <div class="fw-semibold">${testTitle(item)}</div>
                            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mt-1">
                                ${createdAt ? `<span>${escapeHtml(createdAt)}</span>` : ''}
                                <span class="badge ${status.badge}">${escapeHtml(status.label)}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap justify-content-end">
                            ${canStart ? `<button type="button" class="btn btn-outline-success btn-sm" data-test-start="${escapeHtml(aid)}">Запустить</button>` : ''}
                            ${canOpenSession ? `<a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" href="${escapeHtml(replaceId(urlSessionShowTemplate, sid))}">Открыть</a>` : ''}
                            ${canShowResult ? `<a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="${escapeHtml(replaceId(urlResultTemplate, sid))}">Результат</a>` : ''}
                            ${canFinish ? `<button type="button" class="btn btn-outline-primary btn-sm" data-test-finish="${escapeHtml(sid)}">Завершить</button>` : ''}
                            ${canCancel ? `<button type="button" class="btn btn-outline-danger btn-sm" data-test-cancel="${escapeHtml(sid)}">Отменить</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function loadAssignments() {
        if (!urlAssignments) {
            return;
        }

        const freshUrl = `${urlAssignments}${urlAssignments.includes('?') ? '&' : '?'}_=${Date.now()}`;

        try {
            const payload = await fetchJson(freshUrl, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'Cache-Control': 'no-store'
                }
            });

            renderAssignments(normalizeArray(payload));
        } catch (error) {
            console.error('[tests] Не удалось загрузить назначения:', error);
            renderAssignments([]);
        }
    }

    function normalizeTestItem(item) {
        if (!item) {
            return null;
        }

        const id = item.id ?? item.value ?? item.test_id ?? '';
        const code = item.code || item.alias || item.test_code || '';
        const name = item.name || item.title || item.text || item.test_name || '';
        const text = code && name ? `${code} — ${name}` : (name || code || id);

        if (!id) {
            return null;
        }

        return {
            id: String(id),
            text: String(text),

            name: String(name || text),
            code: String(code || ''),
            type: item.type || item.test_type || '',
            type_label: item.type_label || item.typeLabel || '',
            duration: item.duration || item.estimated_minutes || item.estimatedMinutes || '',
            description: item.description || item.about || item.short_description || '',

            access_source_type: item.access_source_type || item.accessSourceType || '',
            access_label: item.access_label || item.accessLabel || '',
            access_hint: item.access_hint || item.accessHint || '',
            access_rule_id: item.access_rule_id || item.accessRuleId || '',
            practice_context: item.practice_context || item.practiceContext || '',

            can_assign: item.can_assign ?? item.canAssign ?? true,
            can_conduct: item.can_conduct ?? item.canConduct ?? false,
            can_view_results: item.can_view_results ?? item.canViewResults ?? false,

            warning: item.warning || ''
        };
    }

    function getAssignTestTypeLabel(type) {
        switch (String(type || '').toLowerCase()) {
            case 'questionnaire':
                return 'Опросник';

            case 'image':
                return 'Тест с изображениями';

            case 'card_sort':
                return 'Сортировка карточек';

            default:
                return 'Тест';
        }
    }

    function getAssignAccessClass(type) {
        switch (String(type || '').toLowerCase()) {
            case 'builtin':
            case 'system':
                return 'is-system';

            case 'owner':
            case 'doctor':
                return 'is-doctor';

            case 'clinic':
                return 'is-clinic';

            case 'global':
                return 'is-global';

            default:
                return 'is-default';
        }
    }

    function formatAssignTestOption(item) {
        if (item.loading) {
            return item.text;
        }

        const name = escapeHtml(item.name || item.text || 'Без названия');
        const code = item.code ? escapeHtml(item.code) : '';
        const typeLabel = escapeHtml(item.type_label || getAssignTestTypeLabel(item.type));
        const duration = item.duration || '';
        const description = item.description
            ? escapeHtml(item.description)
            : 'Описание теста не заполнено.';

        const accessLabel = escapeHtml(item.access_label || 'Доступ разрешён');
        const accessClass = getAssignAccessClass(item.access_source_type);

        const meta = [
            code ? 'Код: ' + code : '',
            typeLabel,
            duration ? duration + ' мин.' : ''
        ].filter(Boolean).join(' · ');

        return window.jQuery(`
        <div class="assign-test-option">
            <div class="assign-test-option__top">
                <div class="assign-test-option__title">
                    ${name}
                </div>

                <span class="assign-test-option__access ${accessClass}">
                    ${accessLabel}
                </span>
            </div>

            ${meta ? `
                <div class="assign-test-option__meta">
                    ${escapeHtml(meta)}
                </div>
            ` : ''}

            <div class="assign-test-option__description">
                ${description}
            </div>
        </div>
    `);
    }

    function formatAssignTestSelection(item) {
        if (!item.id) {
            return item.text || 'Начните вводить название теста...';
        }

        const name = escapeHtml(item.name || item.text || 'Без названия');
        const code = item.code ? escapeHtml(item.code) : '';
        const accessLabel = item.access_label ? escapeHtml(item.access_label) : '';

        return window.jQuery(`
        <div class="assign-test-selected-item">
            <span class="assign-test-selected-item__name">
                ${name}
            </span>

            ${code ? `
                <span class="assign-test-selected-item__code">
                    ${code}
                </span>
            ` : ''}

            ${accessLabel ? `
                <span class="assign-test-selected-item__access">
                    ${accessLabel}
                </span>
            ` : ''}
        </div>
    `);
    }

    function responseToSelect2(data) {
        return normalizeArray(data)
            .map(normalizeTestItem)
            .filter(Boolean);
    }

    async function preloadNativeSelect() {
        if (!assignSelect || assignSelect.options.length > 0) {
            return;
        }

        const source = assignSelect.dataset.source || root.dataset.testsSearch;

        if (!source) {
            return;
        }

        try {
            const url = new URL(source, window.location.origin);

            if (!url.searchParams.has('limit')) {
                url.searchParams.set('limit', '30');
            }

            const payload = await fetchJson(url.toString());
            const items = responseToSelect2(payload);

            assignSelect.innerHTML = '<option value="">Выберите тест</option>' + items.map(item => (
                `<option value="${escapeHtml(item.id)}">${escapeHtml(item.text)}</option>`
            )).join('');
        } catch (error) {
            console.error('[tests] Не удалось загрузить список тестов:', error);
        }
    }

    function initAssignSelect() {
        if (!assignSelect) {
            return;
        }

        assignSelect.name = assignSelect.name || 'test_id';

        const source = assignSelect.dataset.source || root.dataset.testsSearch;

        if (window.jQuery?.fn?.select2 && source) {
            const $ = window.jQuery;
            const $select = $(assignSelect);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: assignModal ? $(assignModal) : $(document.body),
                dropdownCssClass: 'assign-test-select-dropdown',
                placeholder: assignSelect.dataset.placeholder || assignSelect.getAttribute('placeholder') || 'Начните вводить название теста...',
                allowClear: true,
                minimumInputLength: 0,

                ajax: {
                    url: source,
                    dataType: 'json',
                    delay: 250,
                    cache: false,

                    data(params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },

                    processResults(data) {
                        return {
                            results: responseToSelect2(data),
                            pagination: {
                                more: Boolean(data?.pagination?.more || data?.more)
                            }
                        };
                    }
                },

                templateResult: formatAssignTestOption,
                templateSelection: formatAssignTestSelection,

                escapeMarkup(markup) {
                    return markup;
                },

                language: {
                    searching() {
                        return 'Ищем доступные тесты...';
                    },

                    noResults() {
                        return 'Доступные тесты не найдены';
                    },

                    loadingMore() {
                        return 'Загружаем ещё...';
                    }
                }
            });

            $select
                .off('select2:select.assignTest')
                .on('select2:select.assignTest', function (event) {
                    renderAssignAccessPanel(event.params.data || null);

                    assignSelect.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });

            $select
                .off('select2:clear.assignTest')
                .on('select2:clear.assignTest', function () {
                    resetAssignAccessPanel();

                    assignSelect.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });

            return;
        }

        preloadNativeSelect();
    }

    assignSelect?.addEventListener('change', () => {
        if (!assignSelect.value) {
            resetAssignAccessPanel();
            return;
        }

        if (window.__selectedAssignTestData) {
            renderAssignAccessPanel(window.__selectedAssignTestData);
        }
    });

    function cleanupModalBackdrops() {
        setTimeout(() => {
            if (document.querySelector('.modal.show')) {
                return;
            }

            qsa('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
        }, 80);
    }

    assignModal?.addEventListener('shown.bs.modal', initAssignSelect);

    assignForm?.addEventListener('submit', async event => {
        event.preventDefault();

        const selectedId = assignSelect?.value || '';

        if (!selectedId) {
            alert('Выберите тест.');
            return;
        }

        const submitButton = event.submitter || qs('[type="submit"]', assignForm);

        submitButton?.setAttribute('disabled', 'disabled');

        try {
            const formData = new FormData(assignForm);

            await fetchJson(assignForm.action || root.dataset.urlAssign, {
                method: assignForm.method || 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf()
                },
                body: formData
            });

            getBootstrapModal(assignModal)?.hide();
            cleanupModalBackdrops();

            assignForm.reset();

            if (window.jQuery?.fn?.select2 && window.jQuery(assignSelect).hasClass('select2-hidden-accessible')) {
                window.jQuery(assignSelect).val(null).trigger('change');
            }

            resetAssignAccessPanel();

            await loadAssignments();
            showToast('Тест назначен');
        } catch (error) {
            console.error('[tests] Не удалось назначить тест:', error);
            alert(`Не удалось назначить тест: ${error.message}`);
        } finally {
            updateAssignSubmitState();
        }
    });

    function stopPinTimer() {
        if (pinTimerId !== null) {
            clearInterval(pinTimerId);
            pinTimerId = null;
        }
    }

    function startPinTimer(expiresAt) {
        stopPinTimer();

        if (!pinTimer || !expiresAt) {
            if (pinTimer) {
                pinTimer.textContent = '';
            }

            return;
        }

        const deadline = new Date(expiresAt).getTime();

        if (Number.isNaN(deadline)) {
            pinTimer.textContent = '';
            return;
        }

        function tick() {
            const secondsLeft = Math.floor((deadline - Date.now()) / 1000);

            if (secondsLeft <= 0) {
                pinTimer.textContent = 'PIN истёк';
                pinTimer.classList.remove('text-bg-light');
                pinTimer.classList.add('text-bg-danger');
                stopPinTimer();
                return;
            }

            const minutes = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
            const seconds = String(secondsLeft % 60).padStart(2, '0');

            pinTimer.classList.add('text-bg-light');
            pinTimer.classList.remove('text-bg-danger');
            pinTimer.textContent = `Действует ${minutes}:${seconds}`;
        }

        tick();
        pinTimerId = setInterval(tick, 1000);
    }

    function clearQrCanvas() {
        if (!pinQrCanvas) {
            return;
        }

        const ctx = pinQrCanvas.getContext('2d');

        if (ctx) {
            ctx.clearRect(0, 0, pinQrCanvas.width, pinQrCanvas.height);
        }
    }

    async function renderQr(url) {
        clearQrCanvas();

        if (!pinQrCanvas || !url) {
            return;
        }

        try {
            if (window.QRCode?.toCanvas) {
                await window.QRCode.toCanvas(pinQrCanvas, url, {
                    width: 224,
                    margin: 1
                });

                return;
            }

            const ctx = pinQrCanvas.getContext('2d');

            if (ctx) {
                ctx.font = '13px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('QR-библиотека2 не подключена', pinQrCanvas.width / 2, 104);
                ctx.fillText('Используйте ссылку выше', pinQrCanvas.width / 2, 124);
            }
        } catch (error) {
            console.error('[tests] Не удалось отрисовать QR:', error);
        }
    }

    async function openStartModal(payload) {
        const token = payload?.token || payload?.session_token || '';

        const patientUrl = payload?.kiosk_url
            || payload?.patient_url
            || payload?.run_url
            || payload?.url
            || payload?.show_url
            || (token && testingBase ? `${testingBase}/session/${token}` : '');

        currentPin = String(payload?.pin || payload?.pin_code || payload?.code || '—');
        currentPatientUrl = String(patientUrl || '');

        if (pinValue) {
            pinValue.textContent = currentPin;
        }

        if (patientLink) {
            patientLink.href = currentPatientUrl || '#';
        }

        if (patientLinkText) {
            patientLinkText.textContent = currentPatientUrl || '—';
        }

        await renderQr(currentPatientUrl);
        startPinTimer(payload?.expires_at || payload?.expiresAt || payload?.expires || '');

        if (startModal) {
            getBootstrapModal(startModal)?.show();
        } else if (currentPatientUrl) {
            window.open(currentPatientUrl, '_blank');
        }
    }

    copyPinButton?.addEventListener('click', () => {
        if (currentPin && currentPin !== '—') {
            navigator.clipboard?.writeText(currentPin);
            showToast('PIN скопирован');
        }
    });

    copyLinkButton?.addEventListener('click', () => {
        if (currentPatientUrl) {
            navigator.clipboard?.writeText(currentPatientUrl);
            showToast('Ссылка скопирована');
        }
    });

    openHereButton?.addEventListener('click', () => {
        if (currentPatientUrl) {
            window.open(currentPatientUrl, '_blank', 'noopener');
        }
    });

    startModal?.addEventListener('hidden.bs.modal', stopPinTimer);

    document.addEventListener('click', async event => {
        const startButton = event.target.closest('[data-test-start]');
        const finishButton = event.target.closest('[data-test-finish]');
        const cancelButton = event.target.closest('[data-test-cancel]');

        if (!startButton && !finishButton && !cancelButton) {
            return;
        }

        event.preventDefault();

        try {
            if (startButton) {
                const assignment = startButton.dataset.testStart;

                const payload = await fetchJson(replaceId(urlStartTemplate, assignment), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        Accept: 'application/json'
                    }
                });

                await openStartModal(payload || {});
                await loadAssignments();
                return;
            }

            if (finishButton) {
                const sid = finishButton.dataset.testFinish;

                await fetchJson(replaceId(urlFinishTemplate, sid), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        Accept: 'application/json'
                    }
                });

                await loadAssignments();
                showToast('Тест завершён');
                return;
            }

            if (cancelButton) {
                if (!window.confirm('Отменить тестирование?')) {
                    return;
                }

                const sid = cancelButton.dataset.testCancel;

                await fetchJson(replaceId(urlCancelTemplate, sid), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        Accept: 'application/json'
                    }
                });

                await loadAssignments();
                showToast('Тест отменён');
            }
        } catch (error) {
            console.error('[tests] Действие не выполнено:', error);
            alert(`Действие не выполнено: ${error.message}`);
        }
    });

    window.__testsReloadAssignments = loadAssignments;

    loadAssignments();
}


function initPatientInfoForm() {
    const patientInfoForm = document.querySelector('#pane-patient-info form.patient-info-form-card');
    if (!patientInfoForm) {
        return;
    }

    const medicalCardRoot = document.querySelector('#medicalCardRoot');
    const addressSuggestUrl = patientInfoForm.dataset.addressSuggestUrl || medicalCardRoot?.dataset.addressSuggestUrl || '';
    const insuranceSuggestUrl = patientInfoForm.dataset.insuranceSuggestUrl || medicalCardRoot?.dataset.insuranceSuggestUrl || '';
    const registrationAddress = document.querySelector('#patientRegistrationAddress');
    const actualAddress = document.querySelector('#patientActualAddress');
    const registrationPostalCode = document.querySelector('#patientRegistrationPostalCode');
    const actualPostalCode = document.querySelector('#patientActualPostalCode');
    const legacyAddress = document.querySelector('#patientLegacyAddress');
    const sameAsRegistration = document.querySelector('#sameAsRegistrationAddress');
    const copyAddressButton = document.querySelector('#copyRegistrationAddressToActual');
    const identityDocument = document.querySelector('#patientIdentityDocument');
    const insuranceCompany = document.querySelector('#patientInsuranceCompany');
    const insurancePolicyNumber = document.querySelector('#patientInsurancePolicyNumber');
    const insurancePolicyLegacy = document.querySelector('#patientInsurancePolicy');

    const localInsuranceOrganizations = [
        { value: 'АО «МАКС-М»', search: 'макс м makc max max-m макс-м максм' },
        { value: 'ООО «Капитал МС»', search: 'капитал мс капитал медицинское страхование' },
        { value: 'АО «СОГАЗ-Мед»', search: 'согаз мед согаз-мед sogaz' },
        { value: 'ООО «АльфаСтрахование-ОМС»', search: 'альфастрахование омс альфа страхование альфастрахование-омс' },
        { value: 'ООО «СК «Ингосстрах-М»', search: 'ингосстрах м ингосстрах-м ingos' },
        { value: 'ООО «РЕСО-Мед»', search: 'ресо мед ресо-мед reso' },
        { value: 'АО «Страховая компания «СОГАЗ-Мед»', search: 'страховая компания согаз мед' },
        { value: 'ООО «ВТБ Медицинское страхование»', search: 'втб медицинское страхование втб мс' },
        { value: 'ООО «СМК РЕСО-Мед»', search: 'смк ресо мед' },
        { value: 'ООО «МСК Медстрах»', search: 'мск медстрах медицинское страхование' }
    ];

    function onlyDigits(value, maxLength = null) {
        let digits = String(value || '').replace(/\D/g, '');

        if (maxLength) {
            digits = digits.slice(0, maxLength);
        }

        return digits;
    }

    function formatSnils(value) {
        const digits = onlyDigits(value, 11);
        const first = digits.slice(0, 3);
        const second = digits.slice(3, 6);
        const third = digits.slice(6, 9);
        const control = digits.slice(9, 11);
        let result = first;

        if (second) {
            result += '-' + second;
        }

        if (third) {
            result += '-' + third;
        }

        if (control) {
            result += ' ' + control;
        }

        return result;
    }

    function formatDepartmentCode(value) {
        const digits = onlyDigits(value, 6);

        if (digits.length <= 3) {
            return digits;
        }

        return digits.slice(0, 3) + '-' + digits.slice(3, 6);
    }

    function normalizePhoneDigits(value) {
        let digits = onlyDigits(value, 11);

        if (digits.startsWith('8')) {
            digits = '7' + digits.slice(1);
        } else if (digits.startsWith('9')) {
            digits = '7' + digits;
        }

        return digits.slice(0, 11);
    }

    function formatPhone(value) {
        const digits = normalizePhoneDigits(value);

        if (!digits) {
            return '';
        }

        const country = digits.slice(0, 1);
        const code = digits.slice(1, 4);
        const first = digits.slice(4, 7);
        const second = digits.slice(7, 9);
        const third = digits.slice(9, 11);
        let result = '+' + country;

        if (code) {
            result += ' (' + code;
            if (code.length === 3) {
                result += ')';
            }
        }

        if (first) {
            result += ' ' + first;
        }

        if (second) {
            result += '-' + second;
        }

        if (third) {
            result += '-' + third;
        }

        return result;
    }

    function normalizeEmail(value) {
        return String(value || '').replace(/\s+/g, '').toLowerCase();
    }

    function normalizePolicy(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .replace(/[^0-9a-zA-Zа-яА-ЯёЁ№/\- ]/g, '')
            .trim()
            .slice(0, 32);
    }

    function bindInputMask(selector, formatter, eventName = 'input') {
        document.querySelectorAll(selector).forEach(function (field) {
            const apply = function () {
                const formatted = formatter(field.value, field);

                if (field.value !== formatted) {
                    field.value = formatted;
                }
            };

            field.addEventListener(eventName, apply);
            field.addEventListener('change', apply);
            field.addEventListener('blur', apply);
            apply();
        });
    }

    function bindFieldMasks() {
        bindInputMask('.js-mask-snils', formatSnils);
        bindInputMask('.js-mask-department-code', formatDepartmentCode);
        bindInputMask('.js-mask-phone', formatPhone);
        bindInputMask('.js-mask-email', normalizeEmail);
        bindInputMask('.js-mask-policy', normalizePolicy);
        bindInputMask('.js-mask-digits', function (value, field) {
            return onlyDigits(value, Number(field.dataset.maskMax || 0) || null);
        });
    }

    function syncLegacyInsurancePolicy() {
        if (!insurancePolicyLegacy) {
            return;
        }

        const company = insuranceCompany?.value.trim() || '';
        const number = insurancePolicyNumber?.value.trim() || '';
        insurancePolicyLegacy.value = [company, number].filter(Boolean).join(' — ');
    }

    function syncLegacyAddress() {
        if (legacyAddress && registrationAddress) {
            legacyAddress.value = registrationAddress.value.trim();
        }
    }

    function normalizePostalCode(postalCode) {
        return String(postalCode || '').replace(/\D/g, '').slice(0, 6);
    }

    function mergeAddressWithPostalCode(address, postalCode) {
        const cleanAddress = String(address || '').trim();
        const cleanPostalCode = normalizePostalCode(postalCode);

        if (!cleanPostalCode) {
            return cleanAddress;
        }

        if (cleanAddress.startsWith(cleanPostalCode)) {
            return cleanAddress;
        }

        return cleanPostalCode + ', ' + cleanAddress;
    }

    function setPostalCode(field, postalCode) {
        const targetSelector = field?.dataset?.postalTarget || '';
        const target = targetSelector ? document.querySelector(targetSelector) : null;

        if (!target) {
            return;
        }

        target.value = normalizePostalCode(postalCode);
        target.dispatchEvent(new Event('input', { bubbles: true }));
        target.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function copyRegistrationPostalToActual() {
        if (!registrationPostalCode || !actualPostalCode) {
            return;
        }

        actualPostalCode.value = registrationPostalCode.value;
        actualPostalCode.dispatchEvent(new Event('input', { bubbles: true }));
        actualPostalCode.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function copyRegistrationToActual() {
        if (!registrationAddress || !actualAddress) {
            return;
        }

        actualAddress.value = registrationAddress.value;
        copyRegistrationPostalToActual();
        actualAddress.dispatchEvent(new Event('input', { bubbles: true }));
        syncLegacyAddress();
    }

    function syncSameAddressState() {
        if (!sameAsRegistration || !registrationAddress || !actualAddress) {
            return;
        }

        if (sameAsRegistration.checked) {
            copyRegistrationToActual();
            actualAddress.setAttribute('readonly', 'readonly');
            actualAddress.classList.add('bg-light');
            actualPostalCode?.setAttribute('readonly', 'readonly');
            actualPostalCode?.classList.add('bg-light');
        } else {
            actualAddress.removeAttribute('readonly');
            actualAddress.classList.remove('bg-light');
            actualPostalCode?.removeAttribute('readonly');
            actualPostalCode?.classList.remove('bg-light');
        }
    }

    function collectPassport() {
        if (!identityDocument) {
            return;
        }

        const series = document.querySelector('#patientPassportSeries')?.value.trim() || '';
        const number = document.querySelector('#patientPassportNumber')?.value.trim() || '';
        const issuedAt = document.querySelector('#patientPassportIssuedAt')?.value.trim() || '';
        const departmentCode = document.querySelector('#patientPassportDepartmentCode')?.value.trim() || '';
        const issuedBy = document.querySelector('#patientPassportIssuedBy')?.value.trim() || '';

        const parts = [];
        if (series || number) {
            parts.push(['Паспорт', series, number].filter(Boolean).join(' '));
        } else {
            parts.push('Паспорт');
        }

        if (issuedAt) {
            parts.push('выдан ' + issuedAt);
        }

        if (issuedBy) {
            parts.push(issuedBy);
        }

        if (departmentCode) {
            parts.push('код подразделения ' + departmentCode);
        }

        identityDocument.value = parts.filter(Boolean).join(', ');
    }

    function normalizeSuggestions(payload) {
        let rawItems = [];

        if (Array.isArray(payload)) {
            rawItems = payload;
        } else if (payload && Array.isArray(payload.suggestions)) {
            rawItems = payload.suggestions;
        } else if (payload && Array.isArray(payload.data)) {
            rawItems = payload.data;
        } else if (payload && Array.isArray(payload.items)) {
            rawItems = payload.items;
        }

        return rawItems.map(function (item) {
            if (typeof item === 'string') {
                return {
                    value: item,
                    unrestricted_value: item,
                    postal_code: '',
                    fias_id: ''
                };
            }

            const data = item.data || {};
            const value = item.value || item.unrestricted_value || item.address || item.name || data.result || '';

            return {
                value: value,
                unrestricted_value: item.unrestricted_value || value,
                postal_code: item.postal_code || data.postal_code || '',
                fias_id: item.fias_id || data.fias_id || data.fias_code || '',
                region: item.region || data.region_with_type || '',
                city: item.city || data.city_with_type || data.settlement_with_type || '',
                street: item.street || data.street_with_type || '',
                house: item.house || data.house || '',
                flat: item.flat || data.flat || ''
            };
        }).filter(function (item) {
            return item.value;
        });
    }

    function hideSuggestBox(box) {
        if (!box) {
            return;
        }

        box.classList.remove('is-visible');
        box.innerHTML = '';
    }

    function renderSuggestions(field, box, suggestions) {
        if (!box || !suggestions.length) {
            hideSuggestBox(box);
            return;
        }

        box.innerHTML = suggestions.slice(0, 8).map(function (item, index) {
            const meta = [item.postal_code, item.city, item.street].filter(Boolean).join(' · ');

            return '<button type="button" class="patient-info-address-suggest__item" data-index="' + index + '">' +
                '<span class="d-block fw-semibold">' + escapeHtml(item.value) + '</span>' +
                (meta ? '<span class="d-block small text-muted mt-1">' + escapeHtml(meta) + '</span>' : '') +
                '</button>';
        }).join('');

        box.classList.add('is-visible');

        box.querySelectorAll('.patient-info-address-suggest__item').forEach(function (button) {
            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            button.addEventListener('click', function () {
                const item = suggestions[Number(button.dataset.index)] || null;

                if (!item) {
                    return;
                }

                const addressValue = item.value || item.unrestricted_value || '';
                const postalCode = item.postal_code || '';

                field.value = mergeAddressWithPostalCode(addressValue, postalCode);
                field.dataset.unrestrictedValue = item.unrestricted_value || addressValue;
                field.dataset.postalCode = postalCode;
                field.dataset.fiasId = item.fias_id || '';
                setPostalCode(field, postalCode);

                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));

                hideSuggestBox(box);
                field.focus();
            });
        });
    }

    function buildAddressSuggestUrl(baseUrl, query) {
        const separator = baseUrl.includes('?') ? '&' : '?';

        // q — для твоего текущего роута, query — для совместимости с контроллером DaData из примера.
        return baseUrl + separator + 'q=' + encodeURIComponent(query) + '&query=' + encodeURIComponent(query);
    }

    function normalizeInsuranceSearch(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/ё/g, 'е')
            .replace(/[«»"'`().,]/g, ' ')
            .replace(/[–—_]/g, '-')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function findLocalInsuranceSuggestions(query) {
        const normalizedQuery = normalizeInsuranceSearch(query);
        const compactQuery = normalizedQuery.replace(/[\s-]+/g, '');

        if (normalizedQuery.length < 2) {
            return [];
        }

        return localInsuranceOrganizations.filter(function (item) {
            const haystack = normalizeInsuranceSearch(item.value + ' ' + (item.search || ''));
            const compactHaystack = haystack.replace(/[\s-]+/g, '');

            return haystack.includes(normalizedQuery) || compactHaystack.includes(compactQuery);
        }).map(function (item) {
            return {
                value: item.value,
                unrestricted_value: item.value,
                inn: item.inn || '',
                kpp: item.kpp || '',
                ogrn: item.ogrn || '',
                status: item.status || ''
            };
        });
    }

    function mapLocalInsuranceSuggestion(item) {
        return {
            value: item.value,
            unrestricted_value: item.value,
            inn: item.inn || '',
            kpp: item.kpp || '',
            ogrn: item.ogrn || '',
            status: item.status || ''
        };
    }

    function getDefaultInsuranceSuggestions() {
        return localInsuranceOrganizations.slice(0, 8).map(mapLocalInsuranceSuggestion);
    }

    function normalizeInsuranceSuggestions(payload) {
        const rawItems = Array.isArray(payload)
            ? payload
            : (payload.suggestions || payload.items || payload.data || payload.results || []);

        return rawItems.map(function (item) {
            if (typeof item === 'string') {
                return {
                    value: item,
                    unrestricted_value: item,
                    inn: '',
                    kpp: '',
                    ogrn: ''
                };
            }

            const data = item.data || {};
            const name = data.name || {};
            const value = item.value
                || item.unrestricted_value
                || item.name
                || name.short_with_opf
                || name.full_with_opf
                || name.short
                || name.full
                || '';

            return {
                value: value,
                unrestricted_value: item.unrestricted_value || name.full_with_opf || value,
                inn: item.inn || data.inn || '',
                kpp: item.kpp || data.kpp || '',
                ogrn: item.ogrn || data.ogrn || '',
                address: item.address || data.address?.value || data.address?.unrestricted_value || '',
                status: data.state?.status || item.status || ''
            };
        }).filter(function (item) {
            return item.value;
        });
    }

    function renderInsuranceSuggestions(field, box, suggestions) {
        if (!box || !suggestions.length) {
            hideSuggestBox(box);
            return;
        }

        box.innerHTML = suggestions.slice(0, 8).map(function (item, index) {
            const meta = [item.inn ? 'ИНН ' + item.inn : '', item.kpp ? 'КПП ' + item.kpp : '', item.status].filter(Boolean).join(' · ');

            return '<button type="button" class="patient-info-address-suggest__item" data-index="' + index + '">' +
                '<span class="d-block fw-semibold">' + escapeHtml(item.value) + '</span>' +
                (meta ? '<span class="d-block small text-muted mt-1">' + escapeHtml(meta) + '</span>' : '') +
                '</button>';
        }).join('');

        box.classList.add('is-visible');

        box.querySelectorAll('.patient-info-address-suggest__item').forEach(function (button) {
            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            button.addEventListener('click', function () {
                const item = suggestions[Number(button.dataset.index)] || null;

                if (!item) {
                    return;
                }

                field.value = item.value || item.unrestricted_value || '';
                field.dataset.unrestrictedValue = item.unrestricted_value || field.value;
                field.dataset.inn = item.inn || '';
                field.dataset.kpp = item.kpp || '';
                field.dataset.ogrn = item.ogrn || '';

                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));

                hideSuggestBox(box);
                field.focus();
            });
        });
    }

    function buildInsuranceSuggestUrl(baseUrl, query) {
        const separator = baseUrl.includes('?') ? '&' : '?';

        return baseUrl + separator + 'q=' + encodeURIComponent(query) + '&query=' + encodeURIComponent(query);
    }

    function bindInsuranceAutocomplete(field) {
        const box = document.querySelector(field.dataset.suggestBox || '');
        let timer = null;
        let controller = null;

        field.addEventListener('input', function () {
            syncLegacyInsurancePolicy();

            if (!box || field.hasAttribute('readonly')) {
                return;
            }

            const query = field.value.trim();
            clearTimeout(timer);

            if (query.length < 2) {
                hideSuggestBox(box);
                return;
            }

            const localSuggestions = findLocalInsuranceSuggestions(query);

            if (!insuranceSuggestUrl) {
                renderInsuranceSuggestions(field, box, localSuggestions);
                return;
            }

            timer = setTimeout(function () {
                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();

                fetch(buildInsuranceSuggestUrl(insuranceSuggestUrl, query), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Insurance suggest request failed');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        const remoteSuggestions = normalizeInsuranceSuggestions(payload);
                        renderInsuranceSuggestions(field, box, remoteSuggestions.length ? remoteSuggestions : localSuggestions);
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            renderInsuranceSuggestions(field, box, localSuggestions);
                        }
                    });
            }, 300);
        });

        field.addEventListener('focus', function () {
            if (field.value.trim().length >= 2) {
                field.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        field.addEventListener('dblclick', function () {
            if (!box || field.hasAttribute('readonly')) {
                return;
            }

            clearTimeout(timer);
            renderInsuranceSuggestions(field, box, getDefaultInsuranceSuggestions());
        });

        field.addEventListener('blur', function () {
            setTimeout(function () {
                hideSuggestBox(box);
            }, 180);
        });
    }

    function bindAddressAutocomplete(field) {
        const box = document.querySelector(field.dataset.suggestBox || '');
        let timer = null;
        let controller = null;

        field.addEventListener('input', function () {
            syncLegacyAddress();

            if (sameAsRegistration?.checked && field === registrationAddress) {
                copyRegistrationToActual();
            }

            if (!addressSuggestUrl || !box || field.hasAttribute('readonly')) {
                return;
            }

            const query = field.value.trim();
            clearTimeout(timer);

            if (query.length < 3) {
                hideSuggestBox(box);
                return;
            }

            timer = setTimeout(function () {
                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();

                fetch(buildAddressSuggestUrl(addressSuggestUrl, query), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Address suggest request failed');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        renderSuggestions(field, box, normalizeSuggestions(payload));
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            hideSuggestBox(box);
                        }
                    });
            }, 300);
        });

        field.addEventListener('focus', function () {
            if (field.value.trim().length >= 3) {
                field.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        field.addEventListener('blur', function () {
            setTimeout(function () {
                hideSuggestBox(box);
            }, 180);
        });
    }

    bindFieldMasks();

    document.querySelectorAll('.js-address-autocomplete').forEach(bindAddressAutocomplete);
    document.querySelectorAll('.js-insurance-autocomplete').forEach(bindInsuranceAutocomplete);
    document.querySelectorAll('.js-insurance-part').forEach(function (field) {
        field.addEventListener('input', syncLegacyInsurancePolicy);
        field.addEventListener('change', syncLegacyInsurancePolicy);
    });
    document.querySelectorAll('.js-passport-part').forEach(function (field) {
        field.addEventListener('input', collectPassport);
        field.addEventListener('change', collectPassport);
    });

    sameAsRegistration?.addEventListener('change', syncSameAddressState);
    copyAddressButton?.addEventListener('click', function () {
        copyRegistrationToActual();
        if (sameAsRegistration) {
            sameAsRegistration.checked = true;
        }
        syncSameAddressState();
    });

    registrationAddress?.addEventListener('input', syncLegacyAddress);
    registrationPostalCode?.addEventListener('input', function () {
        registrationPostalCode.value = registrationPostalCode.value.replace(/\D/g, '').slice(0, 6);

        if (sameAsRegistration?.checked) {
            copyRegistrationPostalToActual();
        }
    });
    actualPostalCode?.addEventListener('input', function () {
        actualPostalCode.value = actualPostalCode.value.replace(/\D/g, '').slice(0, 6);
    });
    patientInfoForm.addEventListener('submit', function () {
        syncSameAddressState();
        syncLegacyAddress();
        syncLegacyInsurancePolicy();
        collectPassport();
    });

    syncSameAddressState();
    syncLegacyAddress();
    syncLegacyInsurancePolicy();
    collectPassport();
}

function initLabResultDeepLink() {
    if (document.querySelector('.modal[data-open-on-error="1"]')) {
        return;
    }

    const query = new URLSearchParams(window.location.search);
    const researchId = query.get('research');

    if (!researchId || !/^\d+$/.test(researchId)) {
        return;
    }

    const button = qs(
        `[data-lab-view][data-id="${researchId}"]`
    );

    if (!button) {
        console.warn(
            `Исследование #${researchId} не найдено на странице`
        );

        return;
    }

    openResearchModal(
        button,
        '#modalViewLab'
    );
}

function initInstrumentalResearchModals() {
    const editModal = qs('#modalEditInstrumentalResearch');
    const editForm = qs('#editInstrumentalResearchForm');

    editModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;

        if (!button?.matches('[data-instrumental-edit]')) {
            return;
        }

        const payload = safeJsonParse(
            button.dataset.payload,
            {}
        ) || {};

        if (!editForm) {
            return;
        }

        editForm.action = button.dataset.updateUrl || '';

        editForm.elements.namedItem('study_type').value =
            payload.study_type || '';

        editForm.elements.namedItem('name').value =
            payload.name || '';

        editForm.elements.namedItem('body_area').value =
            payload.body_area || '';

        editForm.elements.namedItem('priority').value =
            payload.priority || 'normal';

        editForm.elements.namedItem('status').value =
            payload.status || 'ordered';

        editForm.elements.namedItem('planned_at').value =
            payload.planned_at || '';

        editForm.elements.namedItem('organization').value =
            payload.organization || '';

        editForm.elements.namedItem('indication').value =
            payload.indication || '';

        editForm.elements.namedItem('with_contrast').checked =
            Boolean(payload.with_contrast);
    });

    const resultModal = qs('#modalInstrumentalResult');
    const resultForm = qs('#instrumentalResultForm');

    resultModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;

        if (!button?.matches('[data-instrumental-result]')) {
            return;
        }

        const payload = safeJsonParse(
            button.dataset.payload,
            {}
        ) || {};

        if (!resultForm) {
            return;
        }

        resultForm.action = button.dataset.resultUrl || '';

        const resultName = qs('#instrumentalResultName');

        if (resultName) {
            resultName.textContent =
                payload.name || 'Исследование';
        }

        resultForm.elements.namedItem('performed_at').value =
            payload.performed_at || '';

        resultForm.elements.namedItem('result_at').value =
            payload.result_at || '';

        resultForm.elements.namedItem('organization').value =
            payload.organization || '';

        resultForm.elements.namedItem('specialist_name').value =
            payload.specialist_name || '';

        resultForm.elements.namedItem('description').value =
            payload.description || '';

        resultForm.elements.namedItem('conclusion').value =
            payload.conclusion || '';

        resultForm.elements.namedItem('recommendations').value =
            payload.recommendations || '';

        renderInstrumentalMediaList(
            qs('#instrumentalResultExistingFiles'),
            payload.files,
            false
        );
    });

    const viewModal = qs('#modalViewInstrumentalResearch');

    viewModal?.addEventListener('show.bs.modal', async event => {
        const button = event.relatedTarget;

        if (!button?.matches('[data-instrumental-view]')) {
            return;
        }

        const payload = safeJsonParse(
            button.dataset.payload,
            {}
        ) || {};

        const viewName = qs('#viewInstrumentalName');
        const viewType = qs('#viewInstrumentalType');
        const viewPerformedAt = qs('#viewInstrumentalPerformedAt');
        const viewResultAt = qs('#viewInstrumentalResultAt');
        const viewOrganization = qs('#viewInstrumentalOrganization');
        const viewSpecialist = qs('#viewInstrumentalSpecialist');
        const viewBodyArea = qs('#viewInstrumentalBodyArea');
        const viewContrast = qs('#viewInstrumentalContrast');
        const viewDescription = qs('#viewInstrumentalDescription');
        const viewConclusion = qs('#viewInstrumentalConclusion');
        const viewRecommendations = qs('#viewInstrumentalRecommendations');

        if (viewName) {
            viewName.textContent =
                payload.name || 'Результат исследования';
        }

        if (viewType) {
            viewType.textContent =
                payload.type_label
                || payload.study_type?.toUpperCase()
                || 'Исследование';
        }

        if (viewPerformedAt) {
            viewPerformedAt.textContent =
                formatInstrumentalDate(payload.performed_at);
        }

        if (viewResultAt) {
            viewResultAt.textContent =
                formatInstrumentalDate(payload.result_at);
        }

        if (viewOrganization) {
            viewOrganization.textContent =
                payload.organization || 'Не указана';
        }

        if (viewSpecialist) {
            viewSpecialist.textContent =
                payload.specialist_name || 'Не указан';
        }

        if (viewBodyArea) {
            viewBodyArea.textContent =
                payload.body_area || 'Не указана';
        }

        if (viewContrast) {
            viewContrast.textContent =
                payload.with_contrast
                    ? 'С контрастом'
                    : 'Без контраста';

            viewContrast.classList.toggle(
                'is-active',
                Boolean(payload.with_contrast)
            );
        }

        if (viewDescription) {
            viewDescription.textContent =
                payload.description || 'Описание не заполнено';
        }

        if (viewConclusion) {
            viewConclusion.textContent =
                payload.conclusion || 'Заключение не заполнено';
        }

        if (viewRecommendations) {
            viewRecommendations.textContent =
                payload.recommendations || 'Рекомендации отсутствуют';
        }

        renderInstrumentalMediaList(
            qs('#viewInstrumentalFiles'),
            payload.files,
            true
        );

        const viewedUrl = button.dataset.viewedUrl;

        if (!viewedUrl || button.dataset.viewed === '1') {
            return;
        }

        try {
            const response = await fetch(viewedUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf(),
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            button.dataset.viewed = '1';
        } catch (error) {
            console.error(
                'Не удалось зафиксировать просмотр исследования:',
                error
            );
        }
    });

    const deleteModal =
        qs('#modalDeleteInstrumentalResearch');

    const deleteForm =
        qs('#deleteInstrumentalResearchForm');

    deleteModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;

        if (!button?.matches('[data-instrumental-delete]')) {
            return;
        }

        if (deleteForm) {
            deleteForm.action =
                button.dataset.deleteUrl || '';
        }

        const deleteName =
            qs('#deleteInstrumentalResearchName');

        if (deleteName) {
            deleteName.textContent =
                `«${button.dataset.name || 'Исследование'}»`;
        }
    });

    const query =
        new URLSearchParams(window.location.search);

    /*
     * Переход со страницы лабораторных назначений:
     * /doctors/patients/{id}?open=lab
     *
     * Открываем вкладку «Анализы и исследования» и сразу
     * модальное окно создания лабораторного направления.
     */
    if (query.get('open') === 'lab') {
        const createLabButton = qs(
            '[data-bs-toggle="modal"][data-bs-target="#modalAddLabOrder"]'
        );

        if (createLabButton) {
            openResearchModal(
                createLabButton,
                '#modalAddLabOrder'
            );

            const url = new URL(window.location.href);
            url.searchParams.delete('open');

            window.history.replaceState(
                {},
                '',
                url.pathname + url.search + url.hash
            );
        } else {
            console.warn(
                'Не найдена кнопка открытия #modalAddLabOrder'
            );
        }
    }

    /*
     * Переход из верхнего меню:
     * /doctors/patients/{id}?open=instrumental
     *
     * Сначала открываем вкладку «Анализы и исследования»,
     * затем модальное окно создания инструментального исследования.
     */
    if (query.get('open') === 'instrumental') {
        const createButton = qs(
            '[data-bs-toggle="modal"][data-bs-target="#modalAddInstrumentalResearch"]'
        );

        if (createButton) {
            openResearchModal(
                createButton,
                '#modalAddInstrumentalResearch'
            );

            /*
             * Убираем одноразовый параметр, чтобы F5
             * повторно не открывал модальное окно.
             */
            const url = new URL(window.location.href);
            url.searchParams.delete('open');

            window.history.replaceState(
                {},
                '',
                url.pathname + url.search + url.hash
            );
        } else {
            console.warn(
                'Не найдена кнопка открытия #modalAddInstrumentalResearch'
            );
        }
    }

    if (query.get('section') === 'instrumental') {
        openBootstrapTab('#pane-labs');

        setTimeout(() => {
            qs('#instrumentalResearchesPanel')
                ?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
        }, 150);
    }

    const instrumentalResearchId =
        query.get('instrumental_research');

    if (
        instrumentalResearchId
        && /^\d+$/.test(instrumentalResearchId)
    ) {
        const viewButton = qs(
            `[data-instrumental-view][data-id="${instrumentalResearchId}"]`
        );

        if (viewButton) {
            openResearchModal(
                viewButton,
                '#modalViewInstrumentalResearch'
            );
        }
    }
}

function formatInstrumentalFileSize(size) {
    const bytes = Number(size || 0);

    if (bytes < 1024) {
        return `${bytes} Б`;
    }

    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} КБ`;
    }

    return `${(bytes / 1024 / 1024).toFixed(1)} МБ`;
}

function formatInstrumentalDate(value) {
    if (!value) {
        return '—';
    }

    const dateTimeMatch = String(value).match(
        /^(\d{4})-(\d{2})-(\d{2})(?:T|\s)(\d{2}):(\d{2})/
    );

    if (dateTimeMatch) {
        return `${dateTimeMatch[3]}.${dateTimeMatch[2]}.${dateTimeMatch[1]} `
            + `${dateTimeMatch[4]}:${dateTimeMatch[5]}`;
    }

    const dateMatch = String(value).match(
        /^(\d{4})-(\d{2})-(\d{2})/
    );

    if (dateMatch) {
        return `${dateMatch[3]}.${dateMatch[2]}.${dateMatch[1]}`;
    }

    return String(value);
}

function getInstrumentalMediaIcon(mimeType) {
    const mime = String(mimeType || '').toLowerCase();

    if (mime === 'application/pdf') {
        return 'bi-file-earmark-pdf';
    }

    if (mime.startsWith('image/')) {
        return 'bi-file-earmark-image';
    }

    if (mime.includes('zip')) {
        return 'bi-file-earmark-zip';
    }

    return 'bi-file-earmark-medical';
}

function renderInstrumentalMediaList(
    container,
    files,
    showEmptyState = true
) {
    if (!container) {
        return;
    }

    files = Array.isArray(files)
        ? files
        : [];

    if (!files.length) {
        container.innerHTML = showEmptyState
            ? `
                <div class="instrumental-view-files-empty">
                    <div class="instrumental-view-files-empty__icon">
                        <i class="bi bi-folder2-open"></i>
                    </div>

                    <div>
                        <div class="instrumental-view-files-empty__title">
                            Файлы не прикреплены
                        </div>

                        <div class="instrumental-view-files-empty__text">
                            У исследования отсутствуют цифровые материалы
                        </div>
                    </div>
                </div>
            `
            : '';

        return;
    }

    container.innerHTML = files.map(function (file) {
        const fileName = file.name
            || file.file_name
            || 'Файл исследования';

        const fileSize = formatInstrumentalFileSize(
            file.size
        );

        const icon = getInstrumentalMediaIcon(
            file.mime_type
        );

        return `
            <a class="instrumental-view-file"
               href="${escapeHtml(file.view_url || '#')}"
               target="_blank"
               rel="noopener">

                <span class="instrumental-view-file__icon">
                    <i class="bi ${icon}"></i>
                </span>

                <span class="instrumental-view-file__main">
                    <span class="instrumental-view-file__name">
                        ${escapeHtml(fileName)}
                    </span>

                    <span class="instrumental-view-file__meta">
                        ${escapeHtml(file.mime_type || 'Файл')}
                        ${fileSize ? ` · ${escapeHtml(fileSize)}` : ''}
                    </span>
                </span>

                <span class="instrumental-view-file__action">
                    <i class="bi bi-box-arrow-up-right"></i>
                    Открыть
                </span>
            </a>
        `;
    }).join('');
}

function getInstrumentalFilesWord(count) {
    const lastTwoDigits = count % 100;
    const lastDigit = count % 10;

    if (lastTwoDigits >= 11 && lastTwoDigits <= 19) {
        return 'файлов';
    }

    if (lastDigit === 1) {
        return 'файл';
    }

    if (lastDigit >= 2 && lastDigit <= 4) {
        return 'файла';
    }

    return 'файлов';
}

function initInstrumentalResultFiles() {
    const modal = qs('#modalInstrumentalResult');
    const input = qs('#instrumentalResultFiles');
    const summary = qs('#instrumentalResultFilesSummary');

    if (!input || !summary) {
        return;
    }

    function renderSelectedFiles() {
        const files = Array.from(input.files || []);

        if (!files.length) {
            summary.innerHTML = `
                <div class="text-muted">
                    Файлы не выбраны
                </div>
            `;

            return;
        }

        const totalSize = files.reduce(function (sum, file) {
            return sum + Number(file.size || 0);
        }, 0);

        const filesList = files.map(function (file) {
            return `
                <div class="d-flex align-items-center gap-2 mt-1">
                    <i class="bi bi-file-earmark-check text-success"></i>

                    <span class="text-truncate">
                        ${escapeHtml(file.name)}
                    </span>

                    <span class="text-muted text-nowrap">
                        ${formatInstrumentalFileSize(file.size)}
                    </span>
                </div>
            `;
        }).join('');

        summary.innerHTML = `
            <div class="d-flex align-items-center gap-2 text-success">
                <i class="bi bi-check-circle-fill"></i>

                <strong>
                    Выбрано:
                    ${files.length}
                    ${getInstrumentalFilesWord(files.length)}
                </strong>

                <span class="text-muted">
                    · ${formatInstrumentalFileSize(totalSize)}
                </span>
            </div>

            <div class="instrumental-result-selected-files">
                ${filesList}
            </div>
        `;
    }

    input.addEventListener('change', renderSelectedFiles);

    modal?.addEventListener('show.bs.modal', function () {
        input.value = '';
        renderSelectedFiles();
    });
}


$('.js-patient-select').select2({

    placeholder:
        'Введите ФИО пациента',
    allowClear: true,
    width: '100%',
    minimumInputLength: 2,
    ajax: {
        url:'{{ route("api.doctors.analyses.patients-search") }}',
        dataType: 'json',
        delay: 300,
        data: function(params) {
            return {
                q: params.term
            };
        },
        processResults: function(data) {
            return {
                results: data
            };
        }
    }
});

$('.js-patient-select').select2({
    placeholder: 'Введите ФИО пациента',
    allowClear: true,
    width: '100%',
    minimumInputLength: 2,
    ajax: {

        url: 'api/doctors/search-patients',

        dataType: 'json',

        delay: 300,

        data: function(params) {
            return {
                q: params.term
            };
        },
        processResults: function(data) {
            return {
                results: data
            };
        }
    },

    templateResult: function(patient) {
        if (!patient.id) {
            return patient.text;
        }
        return $(
            '<div class="patient-option">' +
            '<strong>' + patient.text + '</strong>' +
            '</div>'
        );
    },

    templateSelection: function(patient) {
        return patient.text || patient.placeholder;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
    initPatientInfoForm();
    initConfirmForms();
    initDefaultTab();
    initContactsTabState();
    initLabOrderReopen();
    initLabParamSearch();
    initLabResultModal();
    initLabViewModal();
    initLabActions();
    initModalValidationErrors();
    initLabResultDeepLink();
    initInstrumentalResearchModals();
    initInstrumentalResultFiles();
    initPrescriptionIndicationSwitch();
    initPrescriptionDrugSelect2();
    initTestAssignments();
});
