import 'suneditor/dist/css/suneditor.min.css';
import suneditor from 'suneditor';
import plugins from 'suneditor/src/plugins';
import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // ============================
    //   АНАМНЕЗ + ИИ (если есть)
    // ============================
    const modal = document.getElementById('modalAddAnamnesis');
    if (modal) {
        // ---------- общие ----------
        const textarea = document.getElementById('anamnesisEditor');
        const form = modal.querySelector('form.modal-content');
        const runBtn = modal.querySelector('#btnRunAiAnalyze');
        const insertBtn = modal.querySelector('#btnInsertSummary');
        const appendBtn = modal.querySelector('#btnAppendSummary');
        const summaryBoxTop = modal.querySelector('#aiResultBox');
        const aiSep = modal.querySelector('#aiSep');
        const aiLoad = modal.querySelector('#aiLoading');
        const diagTitle = modal.querySelector('#aiDiagTitle');
        const diagBox = modal.querySelector('#aiDiagnoses');
        const symTitle = modal.querySelector('#aiSymTitle');
        const symBox = modal.querySelector('#aiSymptoms');
        const hiddenCode = modal.querySelector('#diagCodeInput');
        const hiddenTitle = modal.querySelector('#diagTitleInput');
        const hiddenSyms = modal.querySelector('#symptomsJsonInput');
        const sourceInp = modal.querySelector('#diagSource');

        const analyzeUrl = modal.getAttribute('data-api-analyze');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const DIAG_API = (q, page = 1) => `/api/diagnoses?q=${encodeURIComponent(q)}&page=${page}`;

        // утилиты
        const normalize = s => (s || '').trim().toLowerCase();
        const hide = (el, yes = true) => el && el.classList.toggle('d-none', yes);
        const show = el => hide(el, false);
        const sentenceCount = t => (t.match(/[\.!\?…]+(?=\s|$)/g) || []).length;
        const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[m]));
        const debounce = (fn, ms = 220) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        if (modal.dataset.openOnLoad === '1') new bootstrap.Modal(modal).show();

        // ---------- состояние AI ----------
        let lastSummary = '';
        let originalDiagnoses = [];
        let baselineSymSet = new Set();
        let diagChangeHandler = null;

        function extractSymptoms(data) {
            const arr = [];
            try { (data?.raw?.matched_symptoms || []).forEach(el => { const t = (el?.title || '').trim(); if (t) arr.push(t); }); } catch { }
            return arr;
        }

        function resetAiUI() {
            hide(summaryBoxTop, true); summaryBoxTop.innerHTML = '';
            hide(aiSep, true);
            hide(diagTitle, true); hide(diagBox, true); if (diagBox) diagBox.innerHTML = '';
            hide(symTitle, true); hide(symBox, true); if (symBox) symBox.innerHTML = '';
            insertBtn.disabled = true; appendBtn.disabled = true;
            lastSummary = ''; baselineSymSet = new Set(); originalDiagnoses = [];
        }

        function setBusy(b) {
            runBtn?.toggleAttribute('disabled', b);
            insertBtn?.toggleAttribute('disabled', b || !lastSummary);
            appendBtn?.toggleAttribute('disabled', b || !lastSummary);
            hide(aiLoad, !b);
        }

        function restoreBaselineSymptoms() {
            if (!symBox) return;
            symBox.querySelectorAll('.form-check').forEach(wrap => {
                const ch = wrap.querySelector('.form-check-input');
                const lbl = wrap.querySelector('.form-check-label');
                const txt = lbl?.textContent || '';
                ch.disabled = false;
                ch.checked = baselineSymSet.has(normalize(txt));
                lbl?.classList.remove('text-muted');
                wrap.classList.remove('text-muted');
            });
        }

        function applyDiagnosisMask(code) {
            if (!symBox) return;
            restoreBaselineSymptoms();
            const dx = (originalDiagnoses || []).find(d => (d.code || '') === code);
            const matched = new Set((dx?.matched_symptoms || []).map(normalize));
            if (!matched.size) return;
            symBox.querySelectorAll('.form-check').forEach(wrap => {
                const ch = wrap.querySelector('.form-check-input');
                const lbl = wrap.querySelector('.form-check-label');
                const txt = lbl?.textContent || '';
                const inDx = matched.has(normalize(txt));
                ch.checked = inDx;
                lbl?.classList.toggle('text-muted', !inDx);
                wrap.classList.remove('text-muted');
            });
        }

        function resetHidden() {
            if (hiddenCode) hiddenCode.value = '';
            if (hiddenTitle) hiddenTitle.value = '';
            if (hiddenSyms) hiddenSyms.value = '[]';
        }

        function collectAiSelectedSymptoms() {
            if (!symBox || symBox.classList.contains('d-none')) return [];
            return [...symBox.querySelectorAll('.form-check-input')]
                .filter(ch => ch.checked)
                .map(ch => ch.nextElementSibling?.textContent?.trim())
                .filter(Boolean);
        }

        function syncHiddenFromAI() {
            const chosen = diagBox?.querySelector('input[name="aiDiagnosis"]:checked');
            if (hiddenCode) hiddenCode.value = chosen?.value || '';
            if (hiddenTitle) hiddenTitle.value = chosen?.getAttribute('data-title') || '';
            if (hiddenSyms) hiddenSyms.value = JSON.stringify(collectAiSelectedSymptoms());
        }

        // ---------- редактор ----------
        let editor = null;
        modal.addEventListener('shown.bs.modal', () => {
            if (!editor) {
                editor = suneditor.create('anamnesisEditor', {
                    plugins, height: 360,
                    buttonList: [['undo', 'redo'], ['bold', 'italic', 'underline', 'strike'], ['list', 'align'], ['table', 'link'], ['removeFormat']],
                    defaultStyle: 'font-family: Arial, "Segoe UI", "Helvetica Neue", Tahoma, sans-serif; font-size:14px;',
                    placeholder: 'Введите анамнез...',
                    onChange: contents => { if (textarea) textarea.value = contents; }
                });
                if (textarea && textarea.value) editor.setContents(textarea.value);
            }
            setTimeout(() => { try { editor?.core?.setToolbarPosition?.(); } catch { } }, 0);
            textarea?.removeAttribute('required');
            resetAiUI(); resetHidden();
        });

        form?.addEventListener('submit', () => { if (editor && textarea) textarea.value = editor.getContents(); });

        // ---------- анализ ИИ ----------
        runBtn?.addEventListener('click', async () => {
            if (!editor || !analyzeUrl) return;
            const plainText = (editor.getText() || '').trim();
            resetAiUI();
            if (sentenceCount(plainText) < 15) {
                summaryBoxTop.innerHTML = `<div class="text-danger">Для анализа нужно не менее 15 предложений.</div>`;
                show(summaryBoxTop);
                return;
            }
            setBusy(true);
            try {
                const resp = await fetch(analyzeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                    credentials: 'same-origin',
                    body: JSON.stringify({ text: plainText })
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const data = await resp.json();

                originalDiagnoses = Array.isArray(data.diagnoses) ? data.diagnoses : [];
                const primary = (data.diagnoses || [])[0] || null;
                const keySyms = extractSymptoms(data);

                const dxLine = primary ? `${escapeHtml(primary.title ?? '—')} (${escapeHtml(primary.code ?? '—')})` : '—';
                const symLine = keySyms.length ? keySyms.map(s => escapeHtml(s)).join(', ') : '—';
                const flags = Array.isArray(data.flags) ? data.flags.join('. ') : '';

                summaryBoxTop.innerHTML = `
            <div class="mb-1"><strong>Предположительно:</strong> ${dxLine}</div>
            <div><strong>Ключевые симптомы:</strong> ${symLine}</div>
            ${flags ? `<div class="text-danger">${escapeHtml(flags)}</div>` : ''}`;
                show(summaryBoxTop); show(aiSep);

                const top5 = (data.diagnoses || []).slice(0, 5);
                if (top5.length && diagBox) {
                    diagBox.innerHTML = top5.map((d, i) => `
              <div class="form-check mb-1">
                <input class="form-check-input" type="radio" name="aiDiagnosis" id="diag_${i}"
                       value="${escapeHtml(d.code ?? '')}"
                       data-title="${escapeHtml(d.title ?? '')}">
                <label class="form-check-label" for="diag_${i}">
                  <span class="badge bg-light text-dark border me-2">${escapeHtml(d.code ?? '—')}</span>
                  ${escapeHtml(d.title ?? '')}
                  ${d.score != null ? `<span class="text-muted"> (${escapeHtml(String(d.score))}%)</span>` : ''}
                </label>
              </div>`).join('');
                    show(diagTitle); show(diagBox);

                    if (diagChangeHandler) diagBox.removeEventListener('change', diagChangeHandler);
                    diagChangeHandler = e => {
                        const t = e.target;
                        if (t && t.name === 'aiDiagnosis') {
                            if (hiddenCode) hiddenCode.value = t.value || '';
                            if (hiddenTitle) hiddenTitle.value = t.getAttribute('data-title') || '';
                            applyDiagnosisMask(t.value); syncHiddenFromAI();
                        }
                    };
                    diagBox.addEventListener('change', diagChangeHandler);
                }

                if (keySyms.length && symBox) {
                    symBox.innerHTML = keySyms.map((title, i) => `
              <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" id="sym_${i}" checked>
                <label class="form-check-label" for="sym_${i}">${escapeHtml(title)}</label>
              </div>`).join('');
                    show(symTitle); show(symBox);
                    const base = [...symBox.querySelectorAll('.form-check-label')].map(l => l.textContent?.trim()).filter(Boolean);
                    baselineSymSet = new Set(base.map(normalize));
                }

                const first = diagBox?.querySelector('input[name="aiDiagnosis"]');
                if (first) {
                    first.checked = true;
                    if (hiddenCode) hiddenCode.value = first.value || '';
                    if (hiddenTitle) hiddenTitle.value = first.getAttribute('data-title') || '';
                    applyDiagnosisMask(first.value); syncHiddenFromAI();
                }

                lastSummary = (data?.raw?.matched_symptoms || []).map(el => (el?.title || '').trim()).filter(Boolean).join(', ');
                if (lastSummary) { insertBtn.disabled = false; appendBtn.disabled = false; }
            } catch (e) {
                summaryBoxTop.innerHTML = `<div class="text-danger">Ошибка анализа.</div>`;
                show(summaryBoxTop); console.error(e);
            } finally { setBusy(false); }
        });

        insertBtn?.addEventListener('click', () => {
            if (!editor || !lastSummary) return;
            editor.insertHTML(`<p><em>— Сводка ИИ —</em><br>${escapeHtml(lastSummary)}</p>`);
        });
        appendBtn?.addEventListener('click', () => {
            if (!editor || !lastSummary) return;
            const cur = editor.getContents() || '';
            editor.setContents(cur + `<p><em>— Сводка ИИ —</em><br>${escapeHtml(lastSummary)}</p>`);
        });

        form?.addEventListener('submit', () => {
            const source = sourceInp?.value || 'ai';
            if (source === 'ai') syncHiddenFromAI();
            else if (typeof modal._syncManual === 'function') modal._syncManual();
        });

        // ---------- ручная вкладка ----------
        (function initManualTab() {
            const pane = document.getElementById('pane-manual'); if (!pane) return;
            const listBox = pane.querySelector('#m-sym-list');
            const addInp = pane.querySelector('#m-sym-input');
            const addBtn = pane.querySelector('#m-sym-add');
            const clearBtn = pane.querySelector('#m-sym-clear');
            const quickBtns = pane.querySelectorAll('.m-quick');
            const setCurrentChk = pane.querySelector('#m-set-current');

            let manualSymptoms = [];

            function getCheckedSymptoms() {
                if (!listBox) return [];
                return [...listBox.querySelectorAll('.m-sym-chk')]
                    .filter(ch => ch.checked)
                    .map(ch => ch.nextElementSibling?.textContent?.trim())
                    .filter(Boolean);
            }
            function syncHidden() {
                if (hiddenSyms) hiddenSyms.value = JSON.stringify(getCheckedSymptoms());
                let cur = modal.querySelector('input[name="set_as_current"]');
                if (!cur) { cur = document.createElement('input'); cur.type = 'hidden'; cur.name = 'set_as_current'; form?.appendChild(cur); }
                cur.value = setCurrentChk?.checked ? '1' : '0';
            }
            function renderSymptoms() {
                if (!listBox) return;
                const has = manualSymptoms.length > 0;
                listBox.classList.toggle('d-none', !has);
                if (clearBtn) clearBtn.disabled = !has;
                listBox.innerHTML = has ? manualSymptoms.map((s, i) => `
              <div class="form-check m-1">
                <input class="form-check-input m-sym-chk" type="checkbox" id="m_sym_${i}" data-idx="${i}" checked>
                <label class="form-check-label" for="m_sym_${i}">${escapeHtml(s)}</label>
                <button type="button" class="btn btn-link btn-sm text-danger ms-2 m-sym-del" data-idx="${i}">удалить</button>
              </div>`).join('') : '';
                listBox.querySelectorAll('.m-sym-del').forEach(btn => btn.addEventListener('click', () => {
                    const idx = +btn.getAttribute('data-idx'); manualSymptoms.splice(idx, 1); renderSymptoms(); syncHidden();
                }));
                listBox.querySelectorAll('.m-sym-chk').forEach(ch => ch.addEventListener('change', syncHidden));
            }
            function addSymptom(text) {
                const t = (text || '').trim(); if (!t) return;
                if (manualSymptoms.some(s => s.toLowerCase() === t.toLowerCase())) return;
                manualSymptoms.push(t); renderSymptoms(); syncHidden();
            }
            function clearSymptoms() { manualSymptoms = []; renderSymptoms(); syncHidden(); }

            addBtn?.addEventListener('click', () => { addSymptom(addInp?.value); if (addInp) { addInp.value = ''; addInp.focus(); } });
            addInp?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addSymptom(addInp.value); addInp.value = ''; } });
            clearBtn?.addEventListener('click', clearSymptoms);
            quickBtns.forEach(btn => btn.addEventListener('click', () => addSymptom(btn.getAttribute('data-sym'))));

            const aiTabBtn = modal.querySelector('#tab-ai');
            const manualTabBtn = modal.querySelector('#tab-manual');
            aiTabBtn?.addEventListener('shown.bs.tab', () => { if (sourceInp) sourceInp.value = 'ai'; syncHiddenFromAI(); });
            manualTabBtn?.addEventListener('shown.bs.tab', () => { if (sourceInp) sourceInp.value = 'manual'; syncHidden(); });

            renderSymptoms(); syncHidden(); modal._syncManual = syncHidden;
        })();

        // ---------- поиск диагноза ----------
        (function initDiagSearch() {
            const input = document.getElementById('diagnosisSearch');
            const menu = document.getElementById('diagnosisMenu');
            if (!input || !menu) return;

            if (menu.parentElement !== document.body) document.body.appendChild(menu);

            let items = []; let active = -1; let page = 1; let more = false; let lastQ = '';
            const place = () => {
                const r = input.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.left = Math.round(r.left) + 'px';
                menu.style.top = Math.round(r.bottom) + 'px';
                menu.style.width = Math.round(r.width) + 'px';
                menu.style.margin = '0'; menu.style.zIndex = '1095';
                const menuH = Math.min(menu.scrollHeight, 260);
                const bottomSpace = window.innerHeight - r.bottom;
                if (bottomSpace < menuH + 8) menu.style.top = Math.max(8, Math.round(r.top - menuH)) + 'px';
            };
            const render = () => {
                menu.innerHTML = '';
                if (!items.length) { menu.classList.remove('show'); return; }
                items.forEach((it, i) => {
                    const btn = document.createElement('button');
                    btn.type = 'button'; btn.className = 'dropdown-item d-flex justify-content-between';
                    if (i === active) btn.classList.add('active');
                    btn.innerHTML = `<span><strong>${escapeHtml(it.code)}</strong> — ${escapeHtml(it.title)}</span>`;
                    btn.addEventListener('click', () => pick(i));
                    menu.appendChild(btn);
                });
                if (more) {
                    const moreBtn = document.createElement('button');
                    moreBtn.type = 'button'; moreBtn.className = 'dropdown-item text-center'; moreBtn.textContent = 'Показать ещё…';
                    moreBtn.addEventListener('click', () => load(lastQ, page + 1, true));
                    menu.appendChild(moreBtn);
                }
                place(); menu.classList.add('show');
            };
            const pick = i => {
                const it = items[i]; if (!it) return;
                input.value = `${it.code} — ${it.title}`;
                if (hiddenCode) hiddenCode.value = it.code;
                if (hiddenTitle) hiddenTitle.value = it.title;
                menu.classList.remove('show');
            };
            const load = async (q, p = 1, append = false) => {
                lastQ = q; page = p;
                const res = await fetch(DIAG_API(q, p));
                const data = await res.json().catch(() => ({}));
                const arr = (data && data.results) ? data.results : [];
                more = !!(data && data.pagination && data.pagination.more);
                items = append ? items.concat(arr) : arr;
                active = items.length ? 0 : -1;
                render();
            };
            const onInput = debounce(() => {
                if (hiddenCode) hiddenCode.value = '';
                if (hiddenTitle) hiddenTitle.value = '';
                const q = input.value.trim();
                if (!q) { menu.classList.remove('show'); items = []; return; }
                load(q, 1, false);
            });

            input.addEventListener('input', onInput);
            input.addEventListener('focus', () => { if (input.value.trim()) onInput(); });
            input.addEventListener('keydown', e => {
                if (!menu.classList.contains('show')) return;
                if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(active + 1, items.length - 1); render(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(active - 1, 0); render(); }
                else if (e.key === 'Enter') { e.preventDefault(); if (active >= 0) pick(active); }
                else if (e.key === 'Escape') { menu.classList.remove('show'); }
            });
            const _repos = () => { if (menu.classList.contains('show')) place(); };
            window.addEventListener('resize', _repos);
            window.addEventListener('scroll', _repos, true);
            modal?.addEventListener('scroll', _repos, true);
            modal?.querySelector('.modal-body')?.addEventListener('scroll', _repos, true);
            document.addEventListener('click', e => { if (!menu.contains(e.target) && e.target !== input) menu.classList.remove('show'); });

            form?.addEventListener('submit', () => {
                if (hiddenCode?.value && hiddenTitle?.value) return;
                const txt = input.value.trim(); if (!txt) return;
                const m = txt.match(/^\s*([A-ZА-Я]\d{2}(?:\.\d)?)\s*[—–-]\s*(.+)$/i);
                if (m) { if (hiddenCode) hiddenCode.value = m[1].toUpperCase(); if (hiddenTitle) hiddenTitle.value = m[2].trim(); }
                else if (/^[A-ZА-Я]\d{2}(\.\d)?$/i.test(txt)) { if (hiddenCode) hiddenCode.value = txt.toUpperCase(); }
                else { if (hiddenTitle) hiddenTitle.value = txt; }
            });
        })();
    } // конец блока анамнеза

    function resolveGenderFrom(el) {
        const g = (el.getAttribute('data-gender') || '').trim().toLowerCase();
        const s = (el.getAttribute('data-sex') || '').trim().toLowerCase();
        const parse = (x) => {
            if (x === 'm' || x === 'male' || x === 'м') return { sex: 'm', unknown: false };
            if (x === 'f' || x === 'female' || x === 'ж') return { sex: 'f', unknown: false };
            if (x === 'any') return { sex: 'any', unknown: false };
            if (!x || x === 'null' || x === 'undefined') return { sex: null, unknown: true };
            return { sex: null, unknown: true };
        };
        const fromGender = parse(g);
        if (!fromGender.unknown) return fromGender;
        return parse(s);
    }

    // ===================================================
    //  После "Сохранить и ещё": вновь открыть таб + модалку
    // ===================================================
    (function reopenLabOrderAfterSave() {
        const FLAG = 'mc:reopenLabOrder';
        const orderModal = document.getElementById('modalAddLabOrder');
        const orderForm = orderModal?.querySelector('form.modal-content');

        orderForm?.addEventListener('submit', (e) => {
            const s = e.submitter;
            if (s?.name === 'save_and_new') sessionStorage.setItem(FLAG, '1');
            else sessionStorage.removeItem(FLAG);
        });
        orderModal?.querySelector('button[name="save_and_new"]')
            ?.addEventListener('click', () => sessionStorage.setItem(FLAG, '1'));

        if (sessionStorage.getItem(FLAG) === '1') {
            sessionStorage.removeItem(FLAG);
            const labsTabBtn = document.querySelector('[data-bs-toggle="tab"][data-bs-target="#pane-labs"]');
            if (labsTabBtn) new bootstrap.Tab(labsTabBtn).show();
            if (orderModal) setTimeout(() => new bootstrap.Modal(orderModal).show(), 50);
        }
    })();

    // ===================================================
    //        УПРОЩЁННАЯ МОДАЛКА РЕДАКТИРОВАНИЯ (#modalAddLab)
    // ===================================================
    function initLabResultsEditor(modalSelector) {
        const labModal = document.querySelector(modalSelector);
        if (!labModal) return;

        const apiUrl = labModal.getAttribute('data-api-params') || '';
        const { sex, unknown: genderUnknown } = resolveGenderFrom(labModal);
        const age = parseInt(labModal.getAttribute('data-age') || '') || null;

        const form = labModal.querySelector('form.modal-content');
        const tblBody = labModal.querySelector('#selParamsTable tbody');
        const selCount = labModal.querySelector('#selCount');
        const hideNormalChk = labModal.querySelector('#toggleHideNormal');

        const plannedAtInp = labModal.querySelector('input[name="collected_at"]');
        const labNameInp = labModal.querySelector('input[name="lab_name"]');
        const commentInp = labModal.querySelector('textarea[name="comment"]');
        const statusSel = labModal.querySelector('select[name="status"]');
        const resultOrderId = labModal.querySelector('#resultOrderId');

        const updateUrlTpl = labModal.getAttribute('data-update-url') || form?.getAttribute('action') || '';

        const esc = s => String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        const parseMaybeArray = v => {
            if (v == null) return [];
            if (Array.isArray(v)) return v;
            if (typeof v !== 'string') return [];
            try { const a = JSON.parse(v); return Array.isArray(a) ? a : []; } catch { return []; }
        };

        const selected = new Map();
        function updateSelCount() { if (selCount) selCount.textContent = `(${selected.size})`; }

        const normalizeParam = (p) => {
            const refs = Array.isArray(p.references)
                ? p.references
                : (Array.isArray(p.ref_ranges) ? p.ref_ranges : []);
            return {
                id: String(p.id),
                name: p.name || '',
                short: p.short || null,
                unit: p.unit || '—',
                group: p.group || null,
                data_type: p.data_type || 'text',
                decimals: p.decimals ?? null,
                normal_min_any: p.normal_min_any ?? null,
                normal_max_any: p.normal_max_any ?? null,
                normal_min_m: p.normal_min_m ?? null,
                normal_max_m: p.normal_max_m ?? null,
                normal_min_f: p.normal_min_f ?? null,
                normal_max_f: p.normal_max_f ?? null,
                ref_ranges: refs.map(r => ({
                    sex: String(r.sex || 'any').toLowerCase(),
                    age_min_y: +r.age_min_y,
                    age_max_y: +r.age_max_y,
                    min: +r.min,
                    max: +r.max,
                })),
                allowed_values: parseMaybeArray(p.allowed_values),
                normal_values: parseMaybeArray(p.normal_values),
            };
        };

        function pickRange(p) {
            if (sex === 'm' && p.normal_min_m != null) return { min: +p.normal_min_m, max: +p.normal_max_m };
            if (sex === 'f' && p.normal_min_f != null) return { min: +p.normal_min_f, max: +p.normal_max_f };
            if (p.normal_min_any != null) return { min: +p.normal_min_any, max: +p.normal_max_any };
            const rr = Array.isArray(p.ref_ranges) ? p.ref_ranges : [];
            let best = null;
            rr.forEach(r => {
                const sx = (r.sex || 'any').toLowerCase();
                const sexOk = (sx === 'any' || sx === sex);
                const ageOk = age == null || ((+r.age_min_y) <= age && age <= (+r.age_max_y));
                if (sexOk && ageOk) { if (!best) best = r; if ((best.sex || 'any') === 'any' && sx !== 'any') best = r; }
            });
            return best ? { min: +best.min, max: +best.max } : null;
        }

        const badgeClass = k => k === 'ok' ? 'bg-success' : (k === 'low' ? 'bg-warning text-dark' : (k === 'high' || k === 'abnormal' ? 'bg-danger' : 'bg-secondary'));
        function assess(p, raw) {
            if (p.data_type === 'numeric') {
                const v = Number(raw); if (Number.isNaN(v)) return ['invalid', '—'];
                const r = pickRange(p); if (!r) return ['unknown', '—'];
                if (v < r.min) return ['low', 'низкий'];
                if (v > r.max) return ['high', 'высокий'];
                return ['ok', 'норма'];
            } else if (p.data_type === 'categorical') {
                const a = p.allowed_values || [], n = p.normal_values || [];
                if (!a.length) return ['invalid', '—'];
                if (!a.includes(raw)) return ['invalid', '—'];
                return [n.includes(raw) ? 'ok' : 'abnormal', n.includes(raw) ? 'норма' : 'откл.'];
            }
            return ['invalid', '—'];
        }

        function setTableLoading(isLoading) {
            const existing = labModal.querySelector('tr[data-loading="1"]');
            const colCount = 6;
            if (isLoading) {
                if (existing) return;
                const tr = document.createElement('tr');
                tr.setAttribute('data-loading', '1');
                tr.innerHTML = `
            <td colspan="${colCount}" class="text-center py-4">
              <div class="d-inline-flex align-items-center">
                <div class="spinner-border me-2" role="status" aria-hidden="true"></div>
                <span>Загружаем параметры…</span>
              </div>
            </td>`;
                tblBody.appendChild(tr);
            } else {
                existing?.remove();
            }
        }

        function refTextFromRefs(refs = [], sexStr = 'any', ageNum = null, showND = false) {
            if (showND) return 'Н/Д';                      // <- главное условие
            if (!Array.isArray(refs) || !refs.length) return '—';
            const norm = s => (s || 'any').toLowerCase();
            const fits = r => (ageNum == null || (+r.age_min_y) <= ageNum && ageNum <= (+r.age_max_y));

            // универсальный для возраста
            const any = refs.find(r => norm(r.sex) === 'any' && fits(r));
            if (any && (sexStr === 'any' || sexStr == null)) return `${any.min}–${any.max}`;

            // пол-специфичный
            const m = refs.find(r => norm(r.sex) === 'm' && fits(r));
            const f = refs.find(r => norm(r.sex) === 'f' && fits(r));
            if (sexStr === 'm' && m) return `${m.min}–${m.max}`;
            if (sexStr === 'f' && f) return `${f.min}–${f.max}`;

            // fallback
            if (any) return `${any.min}–${any.max}`;
            if (m || f) return `М: ${m ? `${m.min}–${m.max}` : '—'}; Ж: ${f ? `${f.min}–${f.max}` : '—'}`;
            return '—';
        }

        function addRow(p, presetValue = '') {
            if (!p || selected.has(p.id)) return;
            selected.set(p.id, p);

            const tr = document.createElement('tr');
            tr.dataset.id = p.id;

            const cellParam = `
          <td>
            <div class="fw-semibold">${esc(p.name)}</div>
            <div class="text-muted small">${esc(p.short || p.id)}</div>
            <input type="hidden" name="params[${p.id}][id]" value="${p.id}">
          </td>`;
            const cellRef = `<td class="text-muted small">${refTextFromRefs(p.ref_ranges, sex ?? 'any', age, genderUnknown)}</td>`;
            const cellUnit = `<td>${esc(p.unit || '—')}</td>`;

            let valCell = '';
            if (p.data_type === 'numeric') {
                const step = (p.decimals != null) ? (1 / Math.pow(10, +p.decimals)) : 'any';
                valCell = `<td><input type="number" step="${step}" name="params[${p.id}][value]" class="form-control form-control-sm" data-val value="${esc(presetValue)}"></td>`;
            } else if (p.data_type === 'categorical') {
                const opts = (p.allowed_values || []).map(v => {
                    const isNorm = (p.normal_values || []).includes(v);
                    const sel = (v === presetValue) ? 'selected' : '';
                    return `<option ${sel} value="${esc(v)}">${esc(v)}${isNorm ? ' (норма)' : ''}</option>`;
                }).join('');
                valCell = `<td><select name="params[${p.id}][value]" class="form-select form-select-sm" data-val>${opts}</select></td>`;
            } else {
                valCell = `<td><input type="text" name="params[${p.id}][value]" class="form-control form-control-sm" data-val value="${esc(presetValue)}"></td>`;
            }

            const flagCell = `<td class="text-center"><span class="badge bg-secondary" data-flag>—</span></td>`;
            tr.innerHTML = cellParam + cellRef + cellUnit + valCell + flagCell;

            const valEl = tr.querySelector('[data-val]');
            const flagEl = tr.querySelector('[data-flag]');
            const recalc = () => {
                const v = (valEl.tagName === 'SELECT') ? valEl.value : valEl.value;
                const [kind, text] = assess(p, v);
                flagEl.className = 'badge ' + badgeClass(kind);
                flagEl.textContent = text;
                if (hideNormalChk?.checked) tr.style.display = (kind === 'ok' && v !== '') ? 'none' : '';
            };
            // флаг меняется при вводе и смене значения
            valEl.addEventListener('input', recalc);
            valEl.addEventListener('change', recalc);
            hideNormalChk?.addEventListener('change', recalc);

            tblBody.appendChild(tr);
            updateSelCount();
            if (presetValue !== '') recalc();
        }

        function clearSelected() {
            selected.clear();
            tblBody.innerHTML = '';
            updateSelCount();
        }

        // публичные хелперы
        labModal._prefillMeta = meta => {
            if (plannedAtInp) plannedAtInp.value = meta?.plannedAt || plannedAtInp.value || '';
            if (labNameInp) labNameInp.value = meta?.laboratory || '';
            if (commentInp) commentInp.value = meta?.comment || '';
            if (statusSel) statusSel.value = meta?.status || 'ready';
            if (resultOrderId) resultOrderId.value = meta?.orderId || '';
            if (form && updateUrlTpl && meta?.orderId) form.action = updateUrlTpl.replace('__ID__', meta.orderId);
        };

        labModal._fillParamsByIds = async (ids = [], valuesMap = {}) => {
            clearSelected(); if (!ids.length) return;
            setTableLoading(true);
            try {
                const url = `${apiUrl}?ids=${encodeURIComponent(ids.join(','))}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const raw = await res.json();
                const map = new Map(raw.map(p => [String(p.id), normalizeParam(p)]));
                ids.forEach(id => {
                    const p = map.get(String(id));
                    const preset = valuesMap && Object.prototype.hasOwnProperty.call(valuesMap, String(id))
                        ? String(valuesMap[String(id)]) : '';
                    if (p) addRow(p, preset);
                });
            } catch (e) { console.error('fillParamsByIds error', e); }
            finally { setTableLoading(false); }
        };

        // ==== ВАЛИДАЦИЯ при статусе "Готово" ====
        function toggleRequiredForStatus() {
            const need = (statusSel?.value || '') === 'ready';
            tblBody.querySelectorAll('[data-val]').forEach(el => {
                if (need) el.setAttribute('required', 'required');
                else el.removeAttribute('required');
                // снимем подсветку если статус не "Готово"
                if (!need) el.classList.remove('is-invalid');
            });
        }
        statusSel?.addEventListener('change', toggleRequiredForStatus);
        toggleRequiredForStatus();

        form?.addEventListener('submit', (e) => {
            if ((statusSel?.value || '') !== 'ready') return; // проверяем только когда "Готово"

            let ok = true;
            const controls = tblBody.querySelectorAll('[data-val]');
            controls.forEach(el => {
                let valid = true;
                if (el.tagName === 'SELECT') {
                    valid = el.value !== '' && el.value != null;
                } else if (el.type === 'number') {
                    valid = el.value !== '' && !Number.isNaN(Number(el.value));
                } else {
                    valid = (el.value || '').trim() !== '';
                }
                el.classList.toggle('is-invalid', !valid);
                if (!valid) ok = false;
            });

            if (!ok) {
                e.preventDefault();
                e.stopPropagation();
                const first = tblBody.querySelector('.is-invalid');
                first?.focus();
                first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                alert('Статус «Готово»: заполните значения для всех параметров.');
            }
        });
    }
    initLabResultsEditor('#modalAddLab');

    // ===================================================
    //       МОДАЛКА ЗАКАЗА/ШАБЛОНОВ (#modalAddLabOrder)
    // ===================================================
    function initLabParamSearch(modalSelector) {
        const labModal = document.querySelector(modalSelector);
        if (!labModal) return;

        const mode = (labModal.getAttribute('data-mode') || 'order').toLowerCase();
        const apiUrl = labModal.getAttribute('data-api-params') || '';
        const { sex, unknown: genderUnknown } = resolveGenderFrom(labModal);
        const age = parseInt(labModal.getAttribute('data-age') || '') || null;

        const sampleType = labModal.querySelector('#sampleType');
        const qInp = labModal.querySelector('#paramSearch');
        const grpSel = labModal.querySelector('#paramGroup');
        const tbl = labModal.querySelector('#selParamsTable tbody');
        const selCount = labModal.querySelector('#selCount');
        const hideNormalChk = labModal.querySelector('#toggleHideNormal');

        const saveApi = labModal.getAttribute('data-api-save-template') || '';
        const saveBtn = labModal.querySelector('#btnOrderTplSave');
        const saveNameInp = labModal.querySelector('#orderTplName');

        if (!qInp || !grpSel || !tbl || !selCount) return;

        const menuId = labModal.id + '_paramMenu';
        let paramMenu = document.getElementById(menuId);
        if (!paramMenu) { paramMenu = document.createElement('div'); paramMenu.id = menuId; paramMenu.className = 'dropdown-menu'; document.body.appendChild(paramMenu); }

        let currentOwner = null;
        const d = (fn, ms = 200) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
        const esc = s => String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        const normGroup = v => { const s = String(v ?? '').trim().toLowerCase(); return (s === '' || s === 'all' || s === 'any' || s === '*') ? '' : s; };

        let items = [];
        let active = -1;
        let selected = new Map();

        async function loadGroupsByMaterial(material) {
            const groupLabels = {
                "cbc": "ОАК",
                "cbc_indices": "Эритроцитарные индексы",
                "diff": "Лейкоформула (%)",
                "diff_abs": "Лейкоформула (абс.)",
                "urinalysis": "ОАМ",
                "metabolic": "Метаболические",
                "liver": "Печёночные",
                "renal": "Почечные",
                "electrolytes": "Электролиты",
                "lipids": "Липиды",
                "iron": "Железо",
                "vitamins": "Витамины",
                "endocrine": "Эндокринные",
                "drug_levels": "Уровни препаратов",
                "inflammation": "Маркеры воспаления",
                "pregnancy": "Маркеры беременности",
                "special": "Специфические",
                "infection": "Инфекционные",
                "muscle": "Мышцы",
                "cardiac": "Кардио",
                "thyorid": "Гармоны щитовидной железы",
                "pancres": "Панкреонные"
            };

            grpSel.innerHTML = '<option value="">Все группы</option>'; // сбросить старое
            if (!material) return; // если "Все материалы" — показывать все группы

            try {
                const res = await fetch(`/api/doctors/search-params/groups?material=${encodeURIComponent(material)}`);
                const answer = await res.json();
                if (Array.isArray(answer.groups)) {
                    answer.groups.forEach(g => {
                        if (g) {
                            const label = groupLabels[g] || g;
                            grpSel.innerHTML += `<option value="${g}">${label}</option>`;
                        }
                    });
                }
            } catch (e) {}
        }

        sampleType?.addEventListener('change', async () => {
            const material = sampleType.value;
            await loadGroupsByMaterial(material);
            grpSel.value = '';        // сбрасываем выбранную группу
            grpSel.dispatchEvent(new Event('change')); // чтобы обновился список параметров
            clearSelected();
        });

        function getSelectedIds() { return Array.from(selected.keys()).map(String); }
        function updateSelCount() { selCount.textContent = `(${selected.size})`; updateSaveBtn(); }

        function updateSaveBtn() {
            if (!saveBtn) return;
            const nameOk = !!(saveNameInp?.value.trim());
            saveBtn.disabled = !(nameOk && selected.size > 0);
        }
        saveNameInp?.addEventListener('input', updateSaveBtn);
        updateSaveBtn();

        const parseMaybeArray = v => {
            if (v == null) return null;
            if (Array.isArray(v)) return v;
            if (typeof v !== 'string') return null;
            const s = v.trim(); if (!s || s.toLowerCase() === 'null') return null;
            try { const arr = JSON.parse(s); return Array.isArray(arr) ? arr : null; } catch { return null; }
        };

        const normalizeParam = (p) => {
            const refs = Array.isArray(p.references)
                ? p.references
                : (Array.isArray(p.ref_ranges) ? p.ref_ranges : []);
            return {
                id: String(p.id),
                name: p.name || '',
                short: p.short || null,
                unit: p.unit || null,
                group: p.group || null,
                data_type: p.data_type || 'text',
                decimals: p.decimals ?? null,
                normal_min_any: p.normal_min_any ?? null,
                normal_max_any: p.normal_max_any ?? null,
                normal_min_m: p.normal_min_m ?? null,
                normal_max_m: p.normal_max_m ?? null,
                normal_min_f: p.normal_min_f ?? null,
                normal_max_f: p.normal_max_f ?? null,
                ref_ranges: refs.map(r => ({
                    sex: String(r.sex || 'any').toLowerCase(),
                    age_min_y: +r.age_min_y,
                    age_max_y: +r.age_max_y,
                    min: +r.min,
                    max: +r.max,
                })),
                allowed_values: parseMaybeArray(p.allowed_values) || [],
                normal_values: parseMaybeArray(p.normal_values) || [],
            };
        };

        function refTextFromRefs(refs = [], sexStr = 'any', ageNum = null) {
            if (!Array.isArray(refs) || !refs.length) return '—';
            const norm = s => (s || 'any').toLowerCase();
            const fits = r => (ageNum == null || (+r.age_min_y) <= ageNum && ageNum <= (+r.age_max_y));

            const any = refs.find(r => norm(r.sex) === 'any' && fits(r));
            if (any) return `${any.min}–${any.max}`;

            const m = refs.find(r => norm(r.sex) === 'm' && fits(r));
            const f = refs.find(r => norm(r.sex) === 'f' && fits(r));
            if (m || f) return `М: ${m ? `${m.min}–${m.max}` : '—'}; Ж: ${f ? `${f.min}–${f.max}` : '—'}`;

            return '—';
        }

        function pickRange(p) {
            if (sex === 'm' && p.normal_min_m != null) return { min: +p.normal_min_m, max: +p.normal_max_m };
            if (sex === 'f' && p.normal_min_f != null) return { min: +p.normal_min_f, max: +p.normal_max_f };
            if (p.normal_min_any != null) return { min: +p.normal_min_any, max: +p.normal_max_any };
            const rr = Array.isArray(p.ref_ranges) ? p.ref_ranges : [];
            let best = null;
            rr.forEach(r => {
                const sx = (r.sex || 'any').toLowerCase();
                const sexOk = (sx === 'any') || sx === sex;
                const ageOk = age == null || ((+r.age_min_y) <= age && age <= (+r.age_max_y));
                if (sexOk && ageOk) { if (!best) best = r; if ((best.sex || 'any') === 'any' && sx !== 'any') best = r; }
            });
            return best ? { min: +best.min, max: +best.max } : null;
        }

        function badgeClass(kind) {
            if (kind === 'ok') return 'bg-success';
            if (kind === 'low') return 'bg-warning text-dark';
            if (kind === 'high' || kind === 'abnormal') return 'bg-danger';
            return 'bg-secondary';
        }
        function assess(p, rawValue) {
            if (p.data_type === 'numeric') {
                const v = Number(rawValue);
                if (Number.isNaN(v)) return ['invalid', '—'];
                const r = pickRange(p); if (!r) return ['unknown', '—'];
                if (v < r.min) return ['low', 'низкий'];
                if (v > r.max) return ['high', 'высокий'];
                return ['ok', 'норма'];
            } else if (p.data_type === 'categorical') {
                const allowed = p.allowed_values || [];
                const normal = p.normal_values || [];
                if (!allowed.length) return ['invalid', '—'];
                if (!allowed.includes(rawValue)) return ['invalid', '—'];
                return [normal.includes(rawValue) ? 'ok' : 'abnormal', normal.includes(rawValue) ? 'норма' : 'откл.'];
            }
            return ['invalid', '—'];
        }

        function place() {
            if (currentOwner !== qInp || !paramMenu.classList.contains('show')) return;
            const r = qInp.getBoundingClientRect();
            paramMenu.style.position = 'fixed';
            paramMenu.style.left = Math.round(r.left) + 'px';
            paramMenu.style.top = Math.round(r.bottom) + 'px';
            paramMenu.style.width = Math.round(r.width) + 'px';
            paramMenu.style.margin = '0';
            paramMenu.style.zIndex = '1095';
            paramMenu.style.maxHeight = '260px';
            paramMenu.style.overflowY = 'auto';
            const menuH = Math.min(paramMenu.scrollHeight, 260);
            const bottomSpace = window.innerHeight - r.bottom;
            if (bottomSpace < menuH + 8) paramMenu.style.top = Math.max(8, Math.round(r.top - menuH)) + 'px';
        }

        function addRow(p) {
            if (selected.has(p.id)) return;
            selected.set(p.id, p);

            // убрать из выпадайки, если открыта
            items = items.filter(x => String(x.id) !== String(p.id));
            if (paramMenu.classList.contains('show') && currentOwner === qInp) renderMenu();

            const tr = document.createElement('tr');
            tr.dataset.id = p.id;

            const cellParam = `
          <td>
            <div class="fw-semibold">${p.name}</div>
            <div class="text-muted small">${p.short || p.id}</div>
            ${mode === 'result'
                ? `<input type="hidden" name="params[${p.id}][id]" value="${p.id}">`
                : `<input type="hidden" name="param_ids[]" value="${p.id}">`}
          </td>`;
            const cellRef  = `<td class="text-muted small">${refTextFromRefs(p.ref_ranges, sex ?? 'any', age, genderUnknown)}</td>`;
            const cellUnit = `<td>${p.unit || '—'}</td>`;

            if (mode === 'result') {
                const step = (p.decimals != null) ? (1 / Math.pow(10, +p.decimals)) : 'any';
                let valCell = '';
                if (p.data_type === 'numeric') {
                    valCell = `<td><input type="number" step="${step}" name="params[${p.id}][value]" class="form-control form-control-sm" data-val></td>`;
                } else if (p.data_type === 'categorical') {
                    const opts = (p.allowed_values || []).map(v => {
                        const isNorm = (p.normal_values || []).includes(v);
                        return `<option value="${esc(v)}">${esc(v)}${isNorm ? ' (норма)' : ''}</option>`;
                    }).join('');
                    valCell = `<td><select name="params[${p.id}][value]" class="form-select form-select-sm" data-val>${opts}</select></td>`;
                } else {
                    valCell = `<td><input type="text" name="params[${p.id}][value]" class="form-control form-control-sm" data-val></td>`;
                }
                const flagCell = `<td class="text-center"><span class="badge bg-secondary" data-flag>—</span></td>`;
                const delCell = `<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove>&times;</button></td>`;
                tr.innerHTML = cellParam + cellRef + cellUnit + valCell + flagCell + delCell;

                const valEl = tr.querySelector('[data-val]');
                const flagEl = tr.querySelector('[data-flag]');
                const recalc = d(() => {
                    const [kind, text] = assess(p, valEl.value);
                    flagEl.className = 'badge ' + badgeClass(kind);
                    flagEl.textContent = text;
                    if (hideNormalChk?.checked) tr.style.display = (kind === 'ok' && valEl.value !== '') ? 'none' : '';
                }, 120);
                valEl.addEventListener('input', recalc);
                valEl.addEventListener('change', recalc); // <— чтобы select тоже триггерил
                hideNormalChk?.addEventListener('change', recalc);
                tr.querySelector('[data-remove]').addEventListener('click', () => {
                    selected.delete(p.id); tr.remove(); updateSelCount();
                });
            } else {
                const delCell = `<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove>&times;</button></td>`;
                tr.innerHTML = cellParam + cellRef + cellUnit + delCell;
                tr.querySelector('[data-remove]').addEventListener('click', () => {
                    selected.delete(p.id); tr.remove(); updateSelCount();
                });
            }

            tbl.appendChild(tr);
            updateSelCount();
        }

        function renderMenu() {
            paramMenu.innerHTML = '';
            if (!items.length) { paramMenu.classList.remove('show'); return; }
            items.forEach((p, i) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dropdown-item d-flex justify-content-between align-items-center';
                if (i === active) btn.classList.add('active');
                btn.innerHTML = `<span><strong>${esc(p.short || p.id)}</strong> — ${esc(p.name)} ${p.unit ? `<span class="text-muted">(${esc(p.unit)})</span>` : ''}</span><span class="text-muted small">${p.group || ''}</span>`;
                btn.addEventListener('click', () => { addRow(p); paramMenu.classList.remove('show'); });
                paramMenu.appendChild(btn);
            });
            currentOwner = qInp; paramMenu.classList.add('show'); place();
        }

        function clearSelected() { selected.clear(); tbl.innerHTML = ''; updateSelCount(); }

        function showMenuLoading() {
            paramMenu.innerHTML = `
          <div class="dropdown-item text-center">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            Загрузка…
          </div>`;
            place(); paramMenu.classList.add('show');
        }

        function setTableLoading(isLoading) {
            const colCount = (mode === 'result') ? 6 : 4;
            const existing = tbl.querySelector('tr[data-loading="1"]');
            if (isLoading) {
                if (existing) return;
                const tr = document.createElement('tr');
                tr.setAttribute('data-loading', '1');
                tr.innerHTML = `
            <td colspan="${colCount}" class="text-center py-4">
              <div class="d-inline-flex align-items-center">
                <div class="spinner-border me-2" role="status" aria-hidden="true"></div>
                <span>Загружаем параметры…</span>
              </div>
            </td>`;
                tbl.appendChild(tr);
            } else if (existing) existing.remove();
        }

        async function loadParams() {
            const q = (qInp.value || '').trim();
            const g = normGroup(grpSel?.value || '');
            const m = document.getElementById('sampleType').value;
            const url = `${apiUrl}?q=${encodeURIComponent(q)}&group=${encodeURIComponent(g)}&material=${m}&limit=50`;

            showMenuLoading();
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                const raw = Array.isArray(data) ? data : (Array.isArray(data?.items) ? data.items : []);

                const chosen = new Set(getSelectedIds());
                items = raw.map(normalizeParam).filter(p => !chosen.has(String(p.id)));

                active = items.length ? 0 : -1;
                renderMenu();
            } catch (e) {
                console.warn('lab params fetch error', e);
                paramMenu.innerHTML = '<div class="dropdown-item text-danger">Ошибка загрузки</div>';
                currentOwner = qInp; paramMenu.classList.add('show'); place();
            }
        }

        // шаблоны
        const tplSelect = labModal.querySelector('#orderTplSelect');
        const btnTplApply = labModal.querySelector('#btnOrderTplApply');

        const getTemplatesMap = () => { try { return JSON.parse(labModal.getAttribute('data-templates') || '{}'); } catch { return {}; } };
        let templatesMap = getTemplatesMap();

        btnTplApply?.addEventListener('click', async () => {
            const tplId = tplSelect?.value || '';
            if (!tplId) { clearSelected(); return; }

            templatesMap = getTemplatesMap();

            let ids = templatesMap[tplId] ?? templatesMap[String(tplId)] ?? templatesMap[Number(tplId)] ?? [];
            if (!Array.isArray(ids) || !ids.length) { clearSelected(); return; }

            clearSelected(); setTableLoading(true);
            try {
                const url = `${apiUrl}?ids=${encodeURIComponent(ids.join(','))}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const raw = await res.json();
                const map = new Map(raw.map(p => [String(p.id), normalizeParam(p)]));
                ids.forEach(id => { const p = map.get(String(id)); if (p) addRow(p); });
                setTableLoading(false);
                sampleType.value = raw[0].sample_type;
            } catch (e) { console.error('Ошибка применения шаблона', e); }
        });

        tplSelect?.addEventListener('change', () => { if (!tplSelect.value) clearSelected(); });

        // сохранение шаблонов
        const showSavedToast = (txt = 'Сохранено') => {
            const t = document.getElementById('toastSaved');
            if (!t) return;
            t.querySelector('.toast-body').textContent = txt;
            new bootstrap.Toast(t).show();
        };
        saveBtn?.addEventListener('click', async () => {
            if (!saveApi) return alert('API для сохранения шаблонов не настроено.');
            const name = (saveNameInp?.value || '').trim();
            const ids = getSelectedIds();
            if (!name || ids.length === 0) return;

            const origHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Сохраняю...';
            try {
                const resp = await fetch(saveApi, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ name, lab_parameters: ids })
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const data = await resp.json();

                if (tplSelect && data?.id) {
                    let opt = tplSelect.querySelector(`option[value="${data.id}"]`);
                    if (!opt) { opt = document.createElement('option'); opt.value = String(data.id); tplSelect.appendChild(opt); }
                    opt.textContent = `${data.name} （личный）`;
                    tplSelect.value = String(data.id);
                    sampleType.value = data.sampleType;
                }
                const normalized = (data.lab_parameters || ids).map(n => Number(n));
                templatesMap[String(data.id)] = normalized;
                labModal.setAttribute('data-templates', JSON.stringify(templatesMap));

                showSavedToast('Шаблон сохранён');
            } catch (e) { console.error(e); alert('Не удалось сохранить шаблон'); }
            finally { saveBtn.innerHTML = origHtml; saveBtn.disabled = false; updateSaveBtn(); }
        });

        // события поиска
        qInp.addEventListener('input', d(loadParams, 200));
        qInp.addEventListener('focus', loadParams);
        grpSel.addEventListener('change', loadParams);

        qInp.addEventListener('keydown', e => {
            if (!paramMenu.classList.contains('show') || currentOwner !== qInp) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(active + 1, items.length - 1); renderMenu(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(active - 1, 0); renderMenu(); }
            else if (e.key === 'Enter') { e.preventDefault(); if (active >= 0 && items[active]) { addRow(items[active]); paramMenu.classList.remove('show'); } }
            else if (e.key === 'Escape') { paramMenu.classList.remove('show'); }
        });

        const _repos = () => { place(); };
        window.addEventListener('resize', _repos);
        window.addEventListener('scroll', _repos, true);
        labModal.addEventListener('scroll', _repos, true);
        labModal.querySelector('.modal-body')?.addEventListener('scroll', _repos, true);

        document.addEventListener('click', e => {
            if (currentOwner !== qInp) return;
            if (!paramMenu.contains(e.target) && e.target !== qInp) paramMenu.classList.remove('show');
        });

        labModal.addEventListener('shown.bs.modal', () => { qInp.focus(); loadParams(); });
        labModal.addEventListener('hidden.bs.modal', () => { if (currentOwner === qInp) paramMenu.classList.remove('show'); });

        // экспорт хелпера
        labModal._fillParamsByIds = async (ids = []) => {
            if (!Array.isArray(ids) || !ids.length) { clearSelected(); return; }
            clearSelected(); setTableLoading(true);
            try {
                const url = `${apiUrl}?ids=${encodeURIComponent(ids.join(','))}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + resp.status);
                const raw = await res.json();
                const map = new Map(raw.map(p => [String(p.id), normalizeParam(p)]));
                ids.forEach(id => { const p = map.get(String(id)); if (p) addRow(p); });
            } finally { setTableLoading(false); }
        };

        labModal._prefillMeta = (meta = {}) => {
            labModal.querySelector('#resultOrderId')?.setAttribute('value', meta.orderId || '');

            const colAt = labModal.querySelector('input[name="collected_at"]');
            if (colAt) colAt.value = meta.plannedAt || new Date().toISOString().slice(0, 16);

            labModal.querySelector('input[name="lab_name"]')?.setAttribute('value', meta.laboratory || '');
            const comment = labModal.querySelector('textarea[name="comment"]');
            if (comment) comment.value = meta.comment || '';

            const statusSel2 = labModal.querySelector('select[name="status"]');
            if (statusSel2) statusSel2.value = meta.status || 'ready';

            const formEl = labModal.querySelector('form.modal-content');
            const urlTpl = labModal.getAttribute('data-update-url') || formEl?.getAttribute('action') || '';
            if (formEl && urlTpl && meta.orderId) {
                formEl.setAttribute('action', urlTpl.replace('__ID__', meta.orderId));
            }
        };
    }
    initLabParamSearch('#modalAddLabOrder');

    // ===================================================
    //     КНОПКА «Внести изменения» — открыть модалку
    // ===================================================
    (function initOrderEditButtons() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-lab-edit]');
            if (!btn) return;

            const modalEl = document.getElementById('modalAddLab');
            if (!modalEl) return;

            let ids = [];
            try { ids = JSON.parse(btn.dataset.paramIds || '[]'); }
            catch { ids = String(btn.dataset.paramIds || '').split(',').map(s => +s).filter(Boolean); }

            const meta = {
                orderId: btn.dataset.id || '',
                plannedAt: btn.dataset.plannedAt || '',
                laboratory: btn.dataset.laboratory || '',
                comment: btn.dataset.comment || '',
                status: 'ready',
                sampleType: btn.dataset.sampleType || ''
            };

            let valuesMap = {};
            try { valuesMap = JSON.parse(btn.dataset.values || '{}'); } catch { }

            modalEl._prefillMeta?.(meta);
            await modalEl._fillParamsByIds?.(ids, valuesMap);

            new bootstrap.Modal(modalEl).show();
        });
    })();

    // ===================================================
    //           УДАЛЕНИЕ ИССЛЕДОВАНИЯ (DELETE)
    // ===================================================
    (function initLabDeleteButtons() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-lab-delete]');
            if (!btn) return;
            if (btn.disabled) return;

            const url = btn.dataset.deleteUrl || '';
            const id = btn.dataset.id || '';
            if (!url || !id) return console.warn('Нет URL для удаления');

            if (!confirm('Удалить исследование? Действие необратимо.')) return;

            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);

                btn.closest('tr')?.remove();

                const t = document.getElementById('toastSaved');
                if (t) {
                    t.querySelector('.toast-body').textContent = 'Удалено';
                    new bootstrap.Toast(t).show();
                }
            } catch (err) {
                console.error(err);
                alert('Не удалось удалить исследование. Попробуйте обновить страницу.');
            } finally {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }
        });
    })();

    function initLabViewModal(modalSelector) {
        const labModal = document.querySelector(modalSelector);
        if (!labModal) return;

        const apiUrl = labModal.getAttribute('data-api-params') || '';

        const genderInfo = resolveGenderFrom(labModal);
        const genderUnknown = !!genderInfo.unknown;
        const sex = genderInfo.sex || 'any';
        const age = parseInt(labModal.getAttribute('data-age') || '') || null;

        const tblBody   = labModal.querySelector('#v_paramsTable tbody');
        const selCount  = labModal.querySelector('#v_selCount');
        const hideOkChk = labModal.querySelector('#v_toggleHideNormal');

        const vCollected = labModal.querySelector('#v_collected_at');
        const vStatus    = labModal.querySelector('#v_status');
        const vLab       = labModal.querySelector('#v_lab_name');
        const vComment   = labModal.querySelector('#v_comment');

        const esc = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
        const parseMaybeArray = v => {
            if (v == null) return [];
            if (Array.isArray(v)) return v;
            if (typeof v !== 'string') return [];
            try { const a = JSON.parse(v); return Array.isArray(a) ? a : []; } catch { return []; }
        };

        const normalizeParam = p => ({
            id: String(p.id),
            name: p.name || '',
            short: p.short || null,
            unit: p.unit || '—',
            group: p.group || null,
            data_type: p.data_type || 'text',
            decimals: p.decimals ?? null,
            // обе схемы поддержим (ref_ranges или references)
            ref_ranges: Array.isArray(p.ref_ranges) ? p.ref_ranges
                : Array.isArray(p.references) ? p.references : [],
            allowed_values: parseMaybeArray(p.allowed_values),
            normal_values:  parseMaybeArray(p.normal_values),
            normal_min_any: p.normal_min_any ?? null,
            normal_max_any: p.normal_max_any ?? null,
            normal_min_m:   p.normal_min_m   ?? null,
            normal_max_m:   p.normal_max_m   ?? null,
            normal_min_f:   p.normal_min_f   ?? null,
            normal_max_f:   p.normal_max_f   ?? null,
        });

        function refTextFromRefs(refs = []) {
            if (genderUnknown) return 'Н/Д';
            if (!Array.isArray(refs) || !refs.length) return '—';
            const norm = s => (s || 'any').toLowerCase();
            const fits = r => (age == null || (+r.age_min_y) <= age && age <= (+r.age_max_y));
            const any = refs.find(r => norm(r.sex) === 'any' && fits(r));
            if (any) return `${any.min}–${any.max}`;
            const m = refs.find(r => norm(r.sex) === 'm' && fits(r));
            const f = refs.find(r => norm(r.sex) === 'f' && fits(r));
            if (m || f) return `М: ${m ? `${m.min}–${m.max}` : '—'}; Ж: ${f ? `${f.min}–${f.max}` : '—'}`;
            return '—';
        }

        function pickRange(p) {
            if (genderUnknown) return null;
            if (sex === 'm' && p.normal_min_m != null) return { min:+p.normal_min_m, max:+p.normal_max_m };
            if (sex === 'f' && p.normal_min_f != null) return { min:+p.normal_min_f, max:+p.normal_max_f };
            if (p.normal_min_any != null)            return { min:+p.normal_min_any, max:+p.normal_max_any };
            const rr = Array.isArray(p.ref_ranges) ? p.ref_ranges : [];
            let best = null;
            rr.forEach(r => {
                const sx = (r.sex || 'any').toLowerCase();
                const sexOk = (sx === 'any') || sx === sex;
                const ageOk = age == null || ((+r.age_min_y) <= age && age <= (+r.age_max_y));
                if (sexOk && ageOk) { if (!best) best = r; if ((best.sex || 'any') === 'any' && sx !== 'any') best = r; }
            });
            return best ? { min:+best.min, max:+best.max } : null;
        }

        const badgeClass = k =>
            k === 'ok' ? 'bg-success'
                : (k === 'low' ? 'bg-warning text-dark'
                    : ((k === 'high' || k === 'abnormal') ? 'bg-danger' : 'bg-secondary'));

        function assess(p, raw) {
            if (p.data_type === 'numeric') {
                const v = Number(raw);
                if (Number.isNaN(v)) return ['invalid','—'];
                const r = pickRange(p);
                if (!r) return ['unknown', '—'];
                if (v < r.min) return ['low',  'низкий'];
                if (v > r.max) return ['high', 'высокий'];
                return ['ok', 'норма'];
            } else if (p.data_type === 'categorical') {
                const a = p.allowed_values || [], n = p.normal_values || [];
                if (!a.length) return ['invalid','—'];
                if (!a.includes(raw)) return ['invalid','—'];
                return [n.includes(raw) ? 'ok' : 'abnormal', n.includes(raw) ? 'норма' : 'откл.'];
            }
            return ['invalid','—'];
        }

        const updateSelCount = () => { selCount.textContent = `(${tblBody.querySelectorAll('tr[data-id]').length})`; };

        function setTableLoading(isLoading) {
            const existing = tblBody.querySelector('tr[data-loading="1"]');
            if (isLoading) {
                if (existing) return;
                const tr = document.createElement('tr');
                tr.setAttribute('data-loading','1');
                tr.innerHTML = `<td colspan="5" class="text-center py-4">
          <div class="d-inline-flex align-items-center">
            <div class="spinner-border me-2" role="status" aria-hidden="true"></div>
            <span>Загружаем параметры…</span>
          </div>
        </td>`;
                tblBody.appendChild(tr);
            } else {
                existing?.remove();
            }
        }

        function addRow(p, value) {
            const tr = document.createElement('tr');
            tr.dataset.id = p.id;

            const refTxt = refTextFromRefs(p.ref_ranges);
            const [kind, text] = assess(p, value);

            tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(p.name)}</div>
        <div class="text-muted small">${esc(p.short || p.id)}</div>
      </td>
      <td class="text-muted small">${esc(refTxt)}</td>
      <td>${esc(p.unit || '—')}</td>
      <td>${value === '' || value == null ? '—' : esc(String(value))}</td>
      <td class="text-center"><span class="badge ${badgeClass(kind)}">${text}</span></td>
    `;

            // скрывать «нормальные»
            const applyHide = () => {
                const valEmpty = (value === '' || value == null);
                const hide = hideOkChk?.checked && !valEmpty && kind === 'ok';
                tr.style.display = hide ? 'none' : '';
            };
            hideOkChk?.addEventListener('change', applyHide);
            applyHide();

            tblBody.appendChild(tr);
            updateSelCount();
        }

        labModal._fillReadonly = async (ids = [], valuesMap = {}, meta = {}) => {
            tblBody.innerHTML = '';
            updateSelCount();

            const date = new Date(meta.collected_at);
            const day = String(date.getDate()).padStart(2, "0");
            const month = String(date.getMonth() + 1).padStart(2, "0"); // месяцы с 0
            const year = date.getFullYear();

            // мета-данные
            vCollected.textContent = `${day}.${month}.${year}`  || '—';
            vStatus.textContent = 'Готово';
            vLab.textContent = meta.laboratory || '—';
            vComment.textContent = meta.comment || '—';

            if (!ids.length) return;

            setTableLoading(true);
            try {
                const url = `${apiUrl}?ids=${encodeURIComponent(ids.join(','))}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const raw = await res.json();
                const map = new Map(raw.map(p => [String(p.id), normalizeParam(p)]));
                ids.forEach(id => {
                    const p = map.get(String(id));
                    const val = valuesMap?.[String(id)] ?? valuesMap?.[id] ?? '';
                    if (p) addRow(p, val);
                });
            } finally {
                setTableLoading(false);
            }
        };
    }
    initLabViewModal('#modalViewLab');

    (function initOrderViewButtons() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-lab-view]');
            if (!btn) return;

            const modalEl = document.getElementById('modalViewLab');
            if (!modalEl) return;

            // ids параметров
            let ids = [];
            try { ids = JSON.parse(btn.dataset.paramIds || '[]'); }
            catch { ids = String(btn.dataset.paramIds || '').split(',').map(s => +s).filter(Boolean); }

            // значения (id => value)
            let values = {};
            try { values = JSON.parse(btn.dataset.values || '{}'); } catch {}

            const meta = {
                collected_at: btn.dataset.collectedAt || btn.dataset.plannedAt || '',
                laboratory: btn.dataset.laboratory || '',
                comment: btn.dataset.comment || ''
            };

            await modalEl._fillReadonly?.(ids, values, meta);
            new bootstrap.Modal(modalEl).show();
        });
    })();

    document.querySelectorAll('[data-research-print]').forEach(function (el) {
        el.addEventListener('click', function (event) {
            const researchID = this.dataset.researchId;
            const patientID = this.dataset.patientId;
            const url = `/doctors/patients/${patientID}/researches/${researchID}/print`;
            const iframe = document.createElement('iframe');

            iframe.style.position = 'fixed';
            iframe.style.left = '-9999px';
            iframe.style.top = '0';
            iframe.style.width = '600px'; // обеспечить рендеринг в нужной верстке
            iframe.style.height = '1120px';
            iframe.style.border = 'none';
            iframe.src = url;

            // когда фрейм загрузится — печатаем
            iframe.onload = () => {
                try {
                    // Вызов должен быть в цепочке пользовательского события — у нас тут handler click, ок.
                    iframe.contentWindow.focus(); // иногда помогает
                    iframe.contentWindow.print();
                    // можно удалить iframe после небольшой задержки
                    setTimeout(() => document.body.removeChild(iframe), 1000);
                } catch (err) {
                    console.error('Печать через iframe невозможна:', err);
                    document.body.removeChild(iframe);
                }
            };

            document.body.appendChild(iframe);
        })
    })
});
