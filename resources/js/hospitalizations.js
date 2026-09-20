import '../sass/hospitalizations.sass';

const qs = (selector, root = document) => root?.querySelector?.(selector) || null;
const qsa = (selector, root = document) => Array.from(root?.querySelectorAll?.(selector) || []);
const csrf = () => qs('meta[name="csrf-token"]')?.getAttribute('content') || '';

const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
}[char]));

const toArray = value => Array.isArray(value) ? value : [];

const positiveId = value => {
    if (value === null || value === undefined || value === '') return null;
    const parsed = Number(value);
    return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
};

function recommendationLabel(value) {
    return {
        supports_planned: 'Данные поддерживают плановую госпитализацию',
        supports_emergency: 'Данные поддерживают экстренную госпитализацию',
        consider_planned: 'Рассмотреть плановую госпитализацию',
        consider_emergency: 'Рассмотреть экстренную госпитализацию',
        outpatient_or_observation: 'Рассмотреть амбулаторное ведение или наблюдение',
        insufficient_data: 'Недостаточно данных'
    }[value] || 'Требуется оценка врача';
}

function verdictLabel(value) {
    return {
        consistent: 'Рецензент подтверждает согласованность',
        partially_consistent: 'Рецензент подтверждает частично',
        inconsistent: 'Рецензент обнаружил несоответствия',
        insufficient_data: 'Рецензенту недостаточно данных'
    }[value] || 'Рецензия не получена';
}

function recommendationClass(value) {
    if (['supports_emergency', 'consider_emergency'].includes(value)) {
        return 'is-danger';
    }

    if (['outpatient_or_observation', 'insufficient_data'].includes(value)) {
        return 'is-warning';
    }

    return '';
}

function initHospitalizationPage() {
    const root = qs('#hospitalizationRoot');
    if (!root) return;

    const mode = root.dataset.mode === 'emergency' ? 'emergency' : 'planned';
    const analyzeUrl = root.dataset.analyzeUrl || '';
    const patientSearchUrl = root.dataset.patientSearchUrl || '';
    const patientDetailsTemplate = root.dataset.patientDetailsUrl || '';
    const patientContextTemplate = root.dataset.patientContextUrl || '';
    const addressSuggestUrl = root.dataset.addressSuggestUrl || '';
    const hospitalSearchUrl = root.dataset.hospitalSearchUrl || '';
    const reverseGeocodeUrl = root.dataset.reverseGeocodeUrl || '';

    const patientSearchInput = qs('[data-patient-search-input]', root);
    const patientSuggestions = qs('[data-patient-suggestions]', root);
    const patientSearchClear = qs('[data-patient-search-clear]', root);
    const patientSearchHint = qs('[data-patient-search-hint]', root);
    const patientName = qs('[data-patient-name]', root);
    const patientMeta = qs('[data-patient-meta]', root);
    const patientDiagnosis = qs('[data-patient-diagnosis]', root);
    const patientAvatar = qs('[data-patient-avatar]', root);
    const patientCard = qs('#hospitalPatientCard', root);
    const formPatientId = qs('[data-form-patient-id]', root);
    const formPatientLabel = qs('[data-form-patient-label]', root);
    const clinicalText = qs('#hospitalClinicalText', root);
    const clinicalCounter = qs('[data-clinical-counter]', root);
    const clinicalLabel = qs('[data-clinical-label]', root);
    const sourceDescription = qs('[data-source-description]', root);
    const sourceList = qs('[data-ai-source-list]', root);
    const firstVisitNotice = qs('[data-first-visit-notice]', root);
    const analyzeButton = qs('[data-hospital-analyze]', root);
    const aiResult = qs('[data-ai-result]', root);
    const form = qs('[data-hospital-form]', root);
    const referralIdInput = qs('[data-referral-id]', root);
    const sourceMode = qs('[data-source-mode]', root);
    const aiAnalysisInput = qs('[data-ai-analysis]', root);
    const printBlankButton = qs('[data-print-blank]', root);
    const printFilledButton = qs('[data-print-filled]', root);
    const preliminaryCode = qs('[data-preliminary-diagnosis-code]', root);
    const preliminaryTitle = qs('[data-preliminary-diagnosis-title]', root);
    const preliminaryState = qs('[data-preliminary-state]', root);
    const preliminaryAiNote = qs('[data-preliminary-ai-note]', root);
    const destinationOrganization = qs('#destinationOrganization', root);
    const destinationAddress = qs('#destinationAddress', root);
    const destinationDepartment = qs('#destinationDepartment', root);
    const destinationSearchInput = qs('[data-destination-search-input]', root);
    const destinationSuggestions = qs('[data-destination-suggestions]', root);
    const destinationSearchHint = qs('[data-destination-search-hint]', root);
    const destinationSearchSpinner = qs('[data-destination-search-spinner]', root);

    let selectedPatient = positiveId(formPatientId?.value) ? String(positiveId(formPatientId?.value)) : '';
    let selectedPatientSearchLabel = patientSearchInput?.value?.trim() || '';
    let patientSearchTimer = null;
    let patientSearchController = null;
    let patientSearchItems = [];
    let patientSearchActiveIndex = -1;
    let selectedPatientData = null;
    let lastAnalysis = null;
    let patientContextState = null;
    let destinationSearchTimer = null;
    let destinationSearchController = null;
    let destinationSearchItems = [];

    function switchTab(name) {
        qsa('[data-hospital-tab]', root).forEach(button => {
            const active = button.dataset.hospitalTab === name;
            button.classList.toggle('active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        qsa('[data-hospital-panel]', root).forEach(panel => {
            panel.classList.toggle('active', panel.dataset.hospitalPanel === name);
        });
    }

    qsa('[data-hospital-tab]', root).forEach(button => {
        button.addEventListener('click', () => switchTab(button.dataset.hospitalTab || 'ai'));
    });

    function initialsFromName(value) {
        return String(value || '')
            .trim()
            .split(/\s+/u)
            .slice(0, 2)
            .map(part => part.charAt(0).toUpperCase())
            .join('') || 'П';
    }

    function syncPreliminaryDiagnosisTitle() {
        if (!preliminaryCode || !preliminaryTitle) return;

        const option = preliminaryCode.selectedOptions?.[0] || null;
        preliminaryTitle.value = option?.dataset?.title || '';
    }

    function setPreliminaryDiagnosis(code = '', title = '', fromAi = false) {
        // ИИ не имеет права выбирать диагноз направления.
        if (fromAi) return;

        if (!preliminaryCode) return;

        const normalized = String(code || '').trim().toUpperCase();
        const options = Array.from(preliminaryCode.options || []);
        const matching = options.find(
            option => String(option.value || '').toUpperCase() === normalized
        );

        preliminaryCode.value = matching ? matching.value : '';
        syncPreliminaryDiagnosisTitle();
    }

    function hasSelectedDiagnosis() {
        const code = String(preliminaryCode?.value || '').trim().toUpperCase();

        return code !== ''
            && String(preliminaryTitle?.value || '').trim() !== '';
    }

    function requireSelectedDiagnosis() {
        syncPreliminaryDiagnosisTitle();

        if (hasSelectedDiagnosis()) {
            return true;
        }

        switchTab('manual');
        alert('Выберите код диагноза по МКБ.');
        preliminaryCode?.focus();

        return false;
    }

    preliminaryCode?.addEventListener('change', () => {
        syncPreliminaryDiagnosisTitle();

        if (lastAnalysis && sourceMode?.value === 'ai') {
            sourceMode.value = 'combined';
        }
    });

    syncPreliminaryDiagnosisTitle();

    function resetAnalysisForPatientChange() {
        lastAnalysis = null;
        setPreliminaryDiagnosis('', '', false);

        if (sourceMode) sourceMode.value = 'manual';
        if (aiAnalysisInput) aiAnalysisInput.value = '';

        if (aiResult) {
            aiResult.innerHTML = `
                <div class="hospital-ai-empty">
                    <div class="hospital-ai-empty__icon"><i class="bi bi-stars"></i></div>
                    <h3>Результат появится здесь</h3>
                    <p>Заполните анамнез или выберите доступные записи пациента и запустите анализ.</p>
                </div>
            `;
        }
    }

    function updatePatientCard(data = null) {
        selectedPatientData = data && typeof data === 'object' ? data : null;
        const fullName = String(data?.full_name || data?.text || '').trim();
        const birth = data?.birth_at_display || data?.birth_at || '';
        const age = data?.age != null ? `${data.age} лет` : '';
        const gender = data?.gender || '';
        const diagnosisCode = data?.diagnosis?.code || '';
        const diagnosisTitle = data?.diagnosis?.title || '';

        if (!fullName) {
            if (patientName) patientName.textContent = 'Пациент не выбран';
            if (patientMeta) patientMeta.textContent = 'Начните вводить фамилию';
            if (patientDiagnosis) patientDiagnosis.textContent = 'Диагноз: не установлен';
            if (patientAvatar) patientAvatar.innerHTML = '<i class="bi bi-person"></i>';
            patientCard?.classList.add('is-empty');
            if (formPatientLabel) formPatientLabel.textContent = 'Пациент не выбран';
            updatePreliminaryState(null, null);
            return;
        }

        if (selectedPatient) selectedPatientSearchLabel = fullName;
        if (patientName) patientName.textContent = fullName;
        if (patientMeta) {
            patientMeta.textContent = [
                birth ? `Дата рождения: ${birth}` : '',
                age,
                gender ? `Пол: ${gender}` : ''
            ].filter(Boolean).join(' · ') || 'Основные данные не указаны';
        }
        if (patientDiagnosis) {
            const diagnosisValue = [diagnosisCode, diagnosisTitle]
                .filter(Boolean)
                .join(' — ');
            patientDiagnosis.textContent = diagnosisValue
                ? `Диагноз: ${diagnosisValue}`
                : 'Диагноз: не установлен';
        }
        if (patientAvatar) patientAvatar.textContent = initialsFromName(fullName);
        patientCard?.classList.remove('is-empty');
        if (formPatientLabel) formPatientLabel.textContent = fullName;

        // Если в черновике/форме диагноз ещё не выбран, подставляем текущий диагноз
        // из карты пациента. Врач по-прежнему может выбрать другой код вручную.
        if (!hasSelectedDiagnosis() && diagnosisCode) {
            setPreliminaryDiagnosis(diagnosisCode, diagnosisTitle, false);
        }

        updatePreliminaryState(data, patientContextState);
    }

    function updatePreliminaryState(patientData = null, state = patientContextState) {
        if (!preliminaryState) return;

        if (!selectedPatient) {
            preliminaryState.innerHTML = `
                <i class="bi bi-info-circle"></i>
                <span>Сначала выберите пациента, затем выберите код диагноза по МКБ.</span>
            `;
            return;
        }

        const hasCurrentDiagnosis = Boolean(state?.has_current_diagnosis);
        const diagnosisCode = patientData?.diagnosis?.code || '';
        const diagnosisTitle = patientData?.diagnosis?.title || '';

        if (hasCurrentDiagnosis && (diagnosisCode || diagnosisTitle)) {
            const selectedCode = String(preliminaryCode?.value || '').trim();
            const usesCurrentDiagnosis = Boolean(
                selectedCode
                && diagnosisCode
                && selectedCode.toUpperCase() === String(diagnosisCode).trim().toUpperCase()
            );

            preliminaryState.innerHTML = usesCurrentDiagnosis
                ? `
                    <i class="bi bi-check-circle"></i>
                    <span>
                        Текущий диагноз из карты:
                        <strong>${escapeHtml(diagnosisCode)}</strong>
                        ${escapeHtml(diagnosisTitle)}.
                        Он подставлен в направление автоматически; при необходимости врач может выбрать другой код.
                    </span>
                `
                : `
                    <i class="bi bi-info-circle"></i>
                    <span>
                        Текущий диагноз пациента:
                        <strong>${escapeHtml(diagnosisCode)}</strong>
                        ${escapeHtml(diagnosisTitle)}.
                        Для направления выбран другой код МКБ.
                    </span>
                `;
        } else {
            preliminaryState.innerHTML = `
                <i class="bi bi-stars"></i>
                <span>Текущего диагноза в карте нет. Для направления выберите код диагноза по МКБ.</span>
            `;
        }
    }

    function updateSourceAvailability(state = null) {
        patientContextState = state && typeof state === 'object' ? state : null;

        const hasDiagnosis = Boolean(patientContextState?.has_current_diagnosis);
        const hasAnamneses = Number(patientContextState?.anamneses_count || 0) > 0;
        const hasPrescriptions = Number(patientContextState?.prescriptions_count || 0) > 0;
        const firstVisit = Boolean(patientContextState?.is_first_visit);
        const availability = {
            diagnosis: hasDiagnosis,
            anamneses: hasAnamneses,
            prescriptions: hasPrescriptions
        };

        qsa('[data-ai-source-option]', root).forEach(option => {
            const key = option.dataset.aiSourceOption || '';
            const available = Boolean(availability[key]);
            option.classList.toggle('d-none', !available);

            const input = qs('[data-ai-source]', option);
            if (input) {
                input.checked = available && key !== 'prescriptions';
                input.disabled = !available;
            }
        });

        const hasAnySource = Object.values(availability).some(Boolean);
        sourceList?.classList.toggle('d-none', !hasAnySource);
        firstVisitNotice?.classList.toggle('d-none', !(selectedPatient && firstVisit));

        if (clinicalLabel) {
            clinicalLabel.textContent = firstVisit
                ? 'Анамнез и текущее состояние'
                : 'Текущее состояние или дополнительная запись';
        }

        if (sourceDescription) {
            sourceDescription.textContent = firstVisit
                ? 'Первичное обращение: опишите анамнез и текущее состояние пациента.'
                : 'Отметьте доступные записи пациента и при необходимости добавьте текущую клиническую ситуацию.';
        }

        updatePreliminaryState(selectedPatientData, patientContextState);
    }

    async function loadPatientDetails(patientId) {
        if (!patientId || !patientDetailsTemplate) {
            updatePatientCard(null);
            return null;
        }

        try {
            const response = await fetch(
                patientDetailsTemplate.replace('__ID__', encodeURIComponent(patientId)),
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            updatePatientCard(data);
            return data;
        } catch (error) {
            console.error('[hospitalization patient]', error);
            const fallback = {
                full_name: patientSearchInput?.value?.trim() || `Пациент #${patientId}`
            };
            updatePatientCard(fallback);
            return fallback;
        }
    }

    async function loadPatientContext(patientId) {
        if (!patientId || !patientContextTemplate) {
            updateSourceAvailability(null);
            return null;
        }

        try {
            const response = await fetch(
                patientContextTemplate.replace('__ID__', encodeURIComponent(patientId)),
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            updateSourceAvailability(data);
            return data;
        } catch (error) {
            console.error('[hospitalization patient context]', error);
            updateSourceAvailability(null);
            return null;
        }
    }

    function patientResultDiagnosis(data = null) {
        const code = String(data?.diagnosis?.code || '').trim();
        const title = String(data?.diagnosis?.title || '').trim();

        if (!code && !title) {
            return 'Диагноз: не установлен';
        }

        return `Диагноз: ${[code, title].filter(Boolean).join(' — ')}`;
    }

    function hidePatientSuggestions() {
        patientSearchItems = [];
        patientSearchActiveIndex = -1;

        if (patientSuggestions) {
            patientSuggestions.classList.add('d-none');
            patientSuggestions.innerHTML = '';
        }
    }

    function setPatientSearchHint(message = '', state = '') {
        if (!patientSearchHint) return;

        patientSearchHint.textContent = message || 'Поиск выполняется в реальном времени по фамилии, имени и отчеству.';
        patientSearchHint.classList.toggle('is-loading', state === 'loading');
        patientSearchHint.classList.toggle('is-error', state === 'error');
    }

    function renderPatientSuggestions(items = [], query = '') {
        if (!patientSuggestions) return;

        patientSearchItems = toArray(items).filter(item => item && item.id);
        patientSearchActiveIndex = -1;

        if (!patientSearchItems.length) {
            patientSuggestions.innerHTML = `
                <div class="hospital-patient-suggestions__empty">
                    <i class="bi bi-person-x"></i>
                    <div>
                        <strong>Пациенты не найдены</strong>
                        <span>По запросу «${escapeHtml(query)}» совпадений нет.</span>
                    </div>
                </div>
            `;
            patientSuggestions.classList.remove('d-none');
            return;
        }

        patientSuggestions.innerHTML = patientSearchItems.map((item, index) => {
            const fullName = String(item.full_name || item.text || `Пациент #${item.id}`).trim();
            const birth = String(item.birth_at_display || item.birth_at || '').trim();
            const diagnosis = patientResultDiagnosis(item);

            return `
                <button type="button"
                        class="hospital-patient-suggestion"
                        role="option"
                        data-patient-result-index="${index}">
                    <span class="hospital-patient-suggestion__avatar">
                        ${escapeHtml(initialsFromName(fullName))}
                    </span>
                    <span class="hospital-patient-suggestion__body">
                        <strong>${escapeHtml(fullName)}</strong>
                        <span class="hospital-patient-suggestion__meta">
                            <span><i class="bi bi-calendar3"></i> ${escapeHtml(birth || 'Дата рождения не указана')}</span>
                            <span class="${item?.diagnosis?.code || item?.diagnosis?.title ? '' : 'is-empty'}">
                                <i class="bi bi-activity"></i> ${escapeHtml(diagnosis)}
                            </span>
                        </span>
                    </span>
                    <i class="bi bi-chevron-right hospital-patient-suggestion__arrow"></i>
                </button>
            `;
        }).join('');

        patientSuggestions.classList.remove('d-none');

        qsa('[data-patient-result-index]', patientSuggestions).forEach(button => {
            button.addEventListener('mousedown', event => event.preventDefault());
            button.addEventListener('click', () => {
                const item = patientSearchItems[Number(button.dataset.patientResultIndex)] || null;
                if (item) selectPatient(item);
            });
        });
    }

    async function performPatientSearch(query) {
        const term = String(query || '').trim();

        if (!patientSearchUrl || term.length < 1) {
            hidePatientSuggestions();
            setPatientSearchHint();
            return;
        }

        patientSearchController?.abort();
        patientSearchController = new AbortController();
        setPatientSearchHint('Ищем пациентов…', 'loading');

        try {
            const url = new URL(patientSearchUrl, window.location.origin);
            url.searchParams.set('q', term);

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                signal: patientSearchController.signal
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json();
            renderPatientSuggestions(payload?.results, term);
            setPatientSearchHint(`Найдено: ${toArray(payload?.results).length}. Выберите пациента из списка.`);
        } catch (error) {
            if (error?.name === 'AbortError') return;

            console.error('[hospitalization patient search]', error);
            hidePatientSuggestions();
            setPatientSearchHint('Не удалось выполнить поиск пациента.', 'error');
        }
    }

    function clearPatientSelection(keepQuery = false) {
        selectedPatient = '';
        selectedPatientData = null;
        selectedPatientSearchLabel = '';
        patientContextState = null;

        if (formPatientId) formPatientId.value = '';
        if (referralIdInput) referralIdInput.value = '';
        if (!keepQuery && patientSearchInput) patientSearchInput.value = '';
        patientSearchClear?.classList.add('d-none');

        resetAnalysisForPatientChange();
        updatePatientCard(null);
        updateSourceAvailability(null);
        hidePatientSuggestions();

        const url = new URL(window.location.href);
        url.searchParams.delete('patient_id');
        window.history.replaceState({}, '', url);
    }

    function syncPatient(patientId, data = null) {
        const normalizedId = positiveId(
            patientId ?? data?.id ?? data?.patient_id
        );

        selectedPatient = normalizedId ? String(normalizedId) : '';
        selectedPatientData = data && typeof data === 'object' ? data : null;

        if (formPatientId) formPatientId.value = selectedPatient;
        if (referralIdInput) referralIdInput.value = '';
        resetAnalysisForPatientChange();

        const url = new URL(window.location.href);
        if (selectedPatient) {
            url.searchParams.set('patient_id', selectedPatient);
        } else {
            url.searchParams.delete('patient_id');
        }
        window.history.replaceState({}, '', url);

        if (!selectedPatient) {
            updatePatientCard(null);
            updateSourceAvailability(null);
            return;
        }

        if (selectedPatientData) {
            updatePatientCard(selectedPatientData);
        }

        patientSearchClear?.classList.remove('d-none');

        Promise.all([
            loadPatientContext(selectedPatient),
            loadPatientDetails(selectedPatient)
        ]).catch(() => {});
    }

    function selectPatient(item) {
        const fullName = String(item?.full_name || item?.text || '').trim();
        selectedPatientSearchLabel = fullName;

        if (patientSearchInput) {
            patientSearchInput.value = fullName;
        }

        hidePatientSuggestions();
        setPatientSearchHint('Пациент выбран. Чтобы изменить — начните вводить другое ФИО.');
        syncPatient(item?.id ?? item?.patient_id ?? '', item);
    }

    function activatePatientSuggestion(index) {
        const buttons = qsa('[data-patient-result-index]', patientSuggestions);
        if (!buttons.length) return;

        patientSearchActiveIndex = Math.max(0, Math.min(index, buttons.length - 1));
        buttons.forEach((button, itemIndex) => {
            const active = itemIndex === patientSearchActiveIndex;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        buttons[patientSearchActiveIndex]?.scrollIntoView({block: 'nearest'});
    }

    function initPatientSearch() {
        if (!patientSearchInput) return;

        patientSearchInput.addEventListener('input', () => {
            const query = patientSearchInput.value.trim();
            const selectedName = selectedPatientSearchLabel || String(
                selectedPatientData?.full_name
                || selectedPatientData?.text
                || ''
            ).trim();

            if (selectedPatient && selectedName && query !== selectedName) {
                clearPatientSelection(true);
            }

            window.clearTimeout(patientSearchTimer);

            if (!query) {
                hidePatientSuggestions();
                setPatientSearchHint();
                return;
            }

            patientSearchTimer = window.setTimeout(() => {
                performPatientSearch(query);
            }, 180);
        });

        patientSearchInput.addEventListener('focus', () => {
            const query = patientSearchInput.value.trim();
            if (!selectedPatient && query) performPatientSearch(query);
        });

        patientSearchInput.addEventListener('keydown', event => {
            const buttons = qsa('[data-patient-result-index]', patientSuggestions);

            if (event.key === 'ArrowDown' && buttons.length) {
                event.preventDefault();
                activatePatientSuggestion(patientSearchActiveIndex + 1);
                return;
            }

            if (event.key === 'ArrowUp' && buttons.length) {
                event.preventDefault();
                activatePatientSuggestion(patientSearchActiveIndex <= 0
                    ? buttons.length - 1
                    : patientSearchActiveIndex - 1);
                return;
            }

            if (event.key === 'Enter' && patientSearchActiveIndex >= 0) {
                event.preventDefault();
                const item = patientSearchItems[patientSearchActiveIndex] || null;
                if (item) selectPatient(item);
                return;
            }

            if (event.key === 'Escape') {
                hidePatientSuggestions();
            }
        });

        patientSearchClear?.addEventListener('click', () => {
            clearPatientSelection(false);
            patientSearchInput.focus();
        });

        document.addEventListener('click', event => {
            const searchBox = qs('[data-patient-search]', root);
            if (searchBox && !searchBox.contains(event.target)) {
                hidePatientSuggestions();
            }
        });

        if (selectedPatient) {
            patientSearchClear?.classList.remove('d-none');
            Promise.all([
                loadPatientContext(selectedPatient),
                loadPatientDetails(selectedPatient)
            ]).catch(() => {});
        } else {
            updateSourceAvailability(null);
        }
    }


    function setDestinationSearchState(message = '', state = '') {
        if (destinationSearchHint) {
            destinationSearchHint.textContent = message || 'Введите город, чтобы найти психиатрический стационар.';
            destinationSearchHint.classList.toggle('is-error', state === 'error');
            destinationSearchHint.classList.toggle('is-success', state === 'success');
        }

        destinationSearchSpinner?.classList.toggle('d-none', state !== 'loading');
    }

    function hideDestinationSuggestions() {
        destinationSearchItems = [];

        if (destinationSuggestions) {
            destinationSuggestions.classList.add('d-none');
            destinationSuggestions.innerHTML = '';
        }
    }

    function formatDistance(value) {
        const distance = Number(value || 0);

        if (!Number.isFinite(distance) || distance <= 0) {
            return '';
        }

        if (distance < 1000) {
            return `${Math.round(distance)} м от центра поиска`;
        }

        return `${(distance / 1000).toFixed(distance < 10000 ? 1 : 0)} км от центра поиска`;
    }

    function destinationCategory(item = {}) {
        return [
            item.speciality_label,
            item.categories
        ].filter(Boolean).join(' · ');
    }

    function renderDestinationSuggestions(items = [], cityLabel = '') {
        if (!destinationSuggestions) return;

        destinationSearchItems = toArray(items)
            .filter(item => item && item.name && Array.isArray(item.coords))
            .sort((a, b) => Number(a.distance_m || 0) - Number(b.distance_m || 0));

        if (!destinationSearchItems.length) {
            destinationSuggestions.innerHTML = `
                <div class="hospital-destination-suggestions__empty">
                    <i class="bi bi-hospital"></i>
                    <div>
                        <strong>Подходящих учреждений не найдено</strong>
                        <span>
                            В OpenStreetMap/Nominatim для ${escapeHtml(cityLabel || 'этого города')} не найден психиатрический стационар
                            или НИИ психического здоровья. Организацию и адрес можно ввести вручную.
                        </span>
                    </div>
                </div>
            `;
            destinationSuggestions.classList.remove('d-none');
            return;
        }

        destinationSuggestions.innerHTML = destinationSearchItems.map((item, index) => {
            const address = String(item.address || '').trim();
            const category = destinationCategory(item);
            const distance = formatDistance(item.distance_m);

            return `
                <button type="button"
                        class="hospital-destination-suggestion"
                        role="option"
                        data-destination-result-index="${index}">
                    <span class="hospital-destination-suggestion__icon">
                        <i class="bi bi-hospital"></i>
                    </span>
                    <span class="hospital-destination-suggestion__body">
                        <strong>${escapeHtml(item.name)}</strong>
                        <span class="hospital-destination-suggestion__address">
                            <i class="bi bi-geo-alt"></i>
                            ${escapeHtml(address || 'Адрес уточним после выбора')}
                        </span>
                        <span class="hospital-destination-suggestion__meta">
                            ${category ? `<span>${escapeHtml(category)}</span>` : ''}
                            ${distance ? `<span>${escapeHtml(distance)}</span>` : ''}
                        </span>
                    </span>
                    <i class="bi bi-chevron-right hospital-destination-suggestion__arrow"></i>
                </button>
            `;
        }).join('');

        destinationSuggestions.classList.remove('d-none');

        qsa('[data-destination-result-index]', destinationSuggestions).forEach(button => {
            button.addEventListener('mousedown', event => event.preventDefault());
            button.addEventListener('click', () => {
                const item = destinationSearchItems[Number(button.dataset.destinationResultIndex)] || null;
                if (item) selectDestination(item);
            });
        });
    }

    function bestCitySuggestion(payload = {}, term = '') {
        const suggestions = toArray(payload?.suggestions)
            .filter(item => item && item.geo_lat && item.geo_lon);

        if (!suggestions.length) {
            return null;
        }

        const normalizedTerm = String(term || '').trim().toLowerCase();

        return suggestions.find(item => {
            const city = String(item.city || '').toLowerCase();
            const value = String(item.value || '').toLowerCase();

            return normalizedTerm && (
                city.includes(normalizedTerm)
                || value === normalizedTerm
                || value.startsWith(`${normalizedTerm},`)
            );
        }) || suggestions[0];
    }

    async function resolveDestinationAddress(item) {
        const existing = String(item?.address || '').trim();
        if (existing) return existing;

        const lon = Number(item?.coords?.[0]);
        const lat = Number(item?.coords?.[1]);

        if (!reverseGeocodeUrl || !Number.isFinite(lat) || !Number.isFinite(lon)) {
            return '';
        }

        try {
            const url = new URL(reverseGeocodeUrl, window.location.origin);
            url.searchParams.set('lat', String(lat));
            url.searchParams.set('lon', String(lon));

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return '';

            const payload = await response.json();
            return String(payload?.address || payload?.display_name || '').trim();
        } catch (error) {
            console.error('[hospitalization destination reverse]', error);
            return '';
        }
    }

    async function selectDestination(item) {
        hideDestinationSuggestions();

        if (destinationOrganization) {
            destinationOrganization.value = String(item?.name || '').trim();
            destinationOrganization.dispatchEvent(new Event('input', {bubbles: true}));
        }

        if (destinationSearchInput) {
            destinationSearchInput.value = String(item?.name || '').trim();
        }

        const category = String(item?.speciality_label || '').toLowerCase();

        if (
            destinationDepartment
            && !destinationDepartment.value.trim()
            && (category.includes('психиатр') || String(item?.speciality || '').toLowerCase().includes('psychiatr'))
        ) {
            destinationDepartment.value = 'Психиатрический профиль';
        }

        setDestinationSearchState('Стационар выбран. Определяем адрес…', 'loading');

        const address = await resolveDestinationAddress(item);

        if (destinationAddress && address) {
            destinationAddress.value = address;
            destinationAddress.dispatchEvent(new Event('input', {bubbles: true}));
        }

        setDestinationSearchState(
            address
                ? 'Организация и адрес заполнены. При необходимости их можно отредактировать вручную.'
                : 'Организация выбрана, но точный адрес не найден. Укажите адрес вручную.',
            address ? 'success' : 'error'
        );
    }

    async function performDestinationSearch(term) {
        const query = String(term || '').trim();

        if (query.length < 2) {
            destinationSearchController?.abort();
            hideDestinationSuggestions();
            setDestinationSearchState('Введите не менее двух символов города.');
            return;
        }

        if (!addressSuggestUrl || !hospitalSearchUrl) {
            hideDestinationSuggestions();
            setDestinationSearchState(
                'Автопоиск стационаров не настроен. Организацию и адрес можно заполнить вручную.',
                'error'
            );
            return;
        }

        destinationSearchController?.abort();
        destinationSearchController = new AbortController();
        const signal = destinationSearchController.signal;

        setDestinationSearchState('Определяем город…', 'loading');

        try {
            const cityUrl = new URL(addressSuggestUrl, window.location.origin);
            cityUrl.searchParams.set('query', query);

            const cityResponse = await fetch(cityUrl.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                signal
            });

            if (!cityResponse.ok) {
                throw new Error(`Адресный поиск: HTTP ${cityResponse.status}`);
            }

            const cityPayload = await cityResponse.json();
            const city = bestCitySuggestion(cityPayload, query);

            if (!city) {
                hideDestinationSuggestions();
                setDestinationSearchState('Не удалось определить город. Уточните название.', 'error');
                return;
            }

            const lon = Number(city.geo_lon);
            const lat = Number(city.geo_lat);

            if (!Number.isFinite(lon) || !Number.isFinite(lat)) {
                hideDestinationSuggestions();
                setDestinationSearchState('Для города не удалось получить координаты.', 'error');
                return;
            }

            setDestinationSearchState('Ищем психиатрические стационары и НИИ ПЗ…', 'loading');

            const hospitalUrl = new URL(hospitalSearchUrl, window.location.origin);
            hospitalUrl.searchParams.set('kind', 'organization');
            hospitalUrl.searchParams.set('q', 'психиатрические стационары');
            hospitalUrl.searchParams.set('city', String(city.city || city.value || query));
            hospitalUrl.searchParams.set('ll', `${lon},${lat}`);
            hospitalUrl.searchParams.set('spn', '1.20,0.90');
            hospitalUrl.searchParams.set('results', '40');

            const hospitalResponse = await fetch(hospitalUrl.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                signal
            });

            if (!hospitalResponse.ok) {
                throw new Error(`Поиск стационаров: HTTP ${hospitalResponse.status}`);
            }

            const hospitalPayload = await hospitalResponse.json();
            const items = toArray(hospitalPayload?.items);

            renderDestinationSuggestions(items, city.city || city.value || query);
            setDestinationSearchState(
                items.length
                    ? `Найдено учреждений: ${items.length}. Выберите стационар из списка.`
                    : 'В этом городе подходящие учреждения в OpenStreetMap не найдены.',
                items.length ? '' : 'error'
            );
        } catch (error) {
            if (error?.name === 'AbortError') return;

            console.error('[hospitalization destination search]', error);
            hideDestinationSuggestions();
            setDestinationSearchState(
                'Не удалось выполнить поиск стационара. Организацию и адрес можно заполнить вручную.',
                'error'
            );
        }
    }

    destinationSearchInput?.addEventListener('input', () => {
        clearTimeout(destinationSearchTimer);

        const value = destinationSearchInput.value.trim();

        destinationSearchTimer = window.setTimeout(
            () => performDestinationSearch(value),
            650
        );
    });

    destinationSearchInput?.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            hideDestinationSuggestions();
        }
    });

    document.addEventListener('click', event => {
        if (
            destinationSuggestions
            && !destinationSuggestions.classList.contains('d-none')
            && !event.target.closest('[data-destination-search]')
        ) {
            hideDestinationSuggestions();
        }
    });

    function updateCounter() {
        if (!clinicalCounter || !clinicalText) return;
        clinicalCounter.textContent = `${clinicalText.value.length} / 24000`;
    }

    clinicalText?.addEventListener('input', updateCounter);
    updateCounter();

    function listHtml(items, emptyText) {
        const values = toArray(items).filter(Boolean);
        if (!values.length) {
            return `<div class="text-muted small">${escapeHtml(emptyText)}</div>`;
        }

        return `<ul>${values.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`;
    }

    function redFlagsHtml(flags) {
        const values = toArray(flags).filter(item => item && item.label);
        if (!values.length) {
            return '<div class="text-muted small">Подтверждённые красные флаги не выделены.</div>';
        }

        return `<ul>${values.map(flag => {
            const evidence = flag.evidence ? ` — «${flag.evidence}»` : '';
            return `<li><strong>${escapeHtml(flag.label)}</strong>${escapeHtml(evidence)}</li>`;
        }).join('')}</ul>`;
    }

    function renderAnalysis(data) {
        lastAnalysis = data;

        const primary = data?.primary || {};
        const review = data?.medical_review || null;
        const diagnosis = primary.preliminary_diagnosis || primary.diagnosis || {};
        const qwenConfidence = Math.round(Number(primary.confidence || 0) * 100);
        const medConfidence = review && Number.isFinite(Number(review.confidence))
            ? Math.round(Number(review.confidence || 0) * 100)
            : null;
        const reviewText = verdictLabel(review?.verdict);
        const attentionCount = toArray(primary.red_flags).length;
        const urgent = Boolean(review?.urgent);

        if (sourceMode) sourceMode.value = 'ai';
        if (aiAnalysisInput) aiAnalysisInput.value = JSON.stringify(data);
        if (referralIdInput && data?.referral_id) {
            referralIdInput.value = String(data.referral_id);
        }

        // Остальные проектные поля заполняем сразу, но не переключаем врача
        // с вкладки анализа на ручную форму.
        applyAnalysisToForm({switchToManual: false, markCombined: false});

        aiResult.innerHTML = `
            <div class="hospital-ai-compact">
                <div class="hospital-ai-compact__confidence">
                    <div class="hospital-ai-score">
                        <span>Qwen</span>
                        <strong>${qwenConfidence}%</strong>
                    </div>
                    <div class="hospital-ai-score ${medConfidence === null ? 'is-muted' : ''}">
                        <span>MedGemma</span>
                        <strong>${medConfidence === null ? '—' : `${medConfidence}%`}</strong>
                    </div>
                </div>

                <div class="hospital-ai-compact__diagnosis">
                    <span>Диагностическая гипотеза ИИ</span>
                    <strong>${escapeHtml([diagnosis.code, diagnosis.title].filter(Boolean).join(' — ') || 'Не сформирована')}</strong>
                </div>

                <div class="hospital-ai-compact__summary">
                    ${escapeHtml(primary.reasoning_summary || 'Краткое заключение не сформировано.')}
                </div>

                <div class="hospital-ai-compact__review ${review?.verdict === 'inconsistent' ? 'is-warning' : ''}">
                    <i class="bi bi-shield-check"></i>
                    <span><strong>MedGemma:</strong> ${escapeHtml(reviewText)}</span>
                </div>

                ${(urgent || attentionCount > 0) ? `
                    <div class="hospital-ai-compact__attention">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>${urgent
                            ? 'MedGemma отметила возможную срочность — требуется оценка врача.'
                            : `Выделено клинических сигналов внимания: ${attentionCount}.`}</span>
                    </div>
                ` : ''}

                <div class="hospital-ai-compact__saved">
                    <i class="bi bi-database-check"></i>
                    ${data?.saved_to_database
                        ? `Черновик #${escapeHtml(data.referral_id || '')} сохранён в базе автоматически.`
                        : 'Результат анализа получен.'}
                </div>
            </div>
        `;
    }

    function renderAnalysisError(message) {
        aiResult.innerHTML = `
            <div class="hospital-ai-empty">
                <div class="hospital-ai-empty__icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3>Не удалось выполнить анализ</h3>
                <p>${escapeHtml(message || 'Повторите запрос позже или заполните направление вручную.')}</p>
            </div>
        `;
    }

    async function analyze() {
        const patientId = positiveId(
            selectedPatient || formPatientId?.value
        );

        if (!patientId) {
            // JSON.stringify(NaN) превращает NaN в null. Именно это приводит
            // к Laravel-ошибке «patient_id field is required». До HTTP-запроса
            // допускаем только реальный положительный integer id пациента.
            clearPatientSelection(true);
            renderAnalysisError('Сначала выберите пациента из результатов поиска.');
            patientSearchInput?.focus();
            return;
        }

        selectedPatient = String(patientId);
        if (formPatientId) formPatientId.value = selectedPatient;

        const referralId = positiveId(referralIdInput?.value);
        const sources = {};
        qsa('[data-ai-source]', root).forEach(input => {
            sources[input.dataset.aiSource] = !input.disabled && Boolean(input.checked);
        });

        const hasSource = Object.values(sources).some(Boolean);
        if (!hasSource && !clinicalText?.value.trim()) {
            const firstVisit = Boolean(patientContextState?.is_first_visit);
            renderAnalysisError(firstVisit
                ? 'При первичном обращении заполните анамнез и текущее состояние пациента.'
                : 'Выберите хотя бы один доступный источник данных или добавьте текущую клиническую запись.');
            clinicalText?.focus();
            return;
        }

        analyzeButton.disabled = true;
        analyzeButton.classList.add('is-loading');

        try {
            const response = await fetch(analyzeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    type: mode,
                    patient_id: patientId,
                    referral_id: referralId,
                    clinical_text: clinicalText?.value.trim() || '',
                    sources,
                    form: {
                        destination_organization: destinationOrganization?.value?.trim() || '',
                        destination_address: destinationAddress?.value?.trim() || '',
                        department: destinationDepartment?.value?.trim() || ''
                    }
                })
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || data?.error) {
                const validationMessage = data?.errors
                    ? Object.values(data.errors).flat().filter(Boolean)[0]
                    : null;
                throw new Error(validationMessage || data?.message || `HTTP ${response.status}`);
            }

            if (data?.patient_state) {
                updateSourceAvailability(data.patient_state);
            }
            renderAnalysis(data);
        } catch (error) {
            console.error('[hospitalization analyze]', error);
            renderAnalysisError(error.message || 'Ошибка соединения с анализатором.');
        } finally {
            analyzeButton.disabled = false;
            analyzeButton.classList.remove('is-loading');
        }
    }

    analyzeButton?.addEventListener('click', analyze);

    function setValue(selector, value) {
        const element = qs(selector, root);
        if (!element || value == null || value === '') return;
        element.value = String(value);
        element.dispatchEvent(new Event('input', {bubbles: true}));
        element.dispatchEvent(new Event('change', {bubbles: true}));
    }

    function applyAnalysisToForm(options = {}) {
        if (!lastAnalysis) return;

        const draft = lastAnalysis.draft || {};
        const switchToManual = options.switchToManual !== false;
        const markCombined = options.markCombined !== false;
        setValue('#clinicalSummary', draft.clinical_summary);
        setValue('#hospitalReason', draft.reason);
        setValue('#destinationDepartment', draft.department);
        setValue('#treatmentGoal', draft.treatment_goal);
        setValue('#stabilizationMeasures', draft.stabilization_measures);
        setValue('#redFlags', draft.red_flags);

        if (draft.urgency_level && draft.urgency_level !== 'routine') {
            setValue('#urgencyLevel', draft.urgency_level);
        }
        if (draft.transport_type && draft.transport_type !== 'unknown') {
            setValue('#transportType', draft.transport_type);
        }

        if (sourceMode) sourceMode.value = markCombined ? 'combined' : 'ai';
        if (aiAnalysisInput) aiAnalysisInput.value = JSON.stringify(lastAnalysis);

        if (switchToManual) {
            switchTab('manual');
            qs('#clinicalSummary', root)?.focus();
        }
    }

    [
        preliminaryCode,
        preliminaryTitle,
        destinationOrganization,
        destinationAddress,
        destinationDepartment
    ].filter(Boolean).forEach(element => {
        element.addEventListener('input', () => {
            if (lastAnalysis && sourceMode?.value === 'ai') {
                sourceMode.value = 'combined';
            }
        });
    });

    root.addEventListener('input', event => {
        if (event.isTrusted && lastAnalysis && sourceMode?.value === 'ai') {
            sourceMode.value = 'combined';
        }
    });

    root.addEventListener('change', event => {
        if (event.isTrusted && lastAnalysis && sourceMode?.value === 'ai') {
            sourceMode.value = 'combined';
        }
    });

    function hospitalPrintBaseUrl() {
        const pathname = window.location.pathname.replace(/\/+$/, '');

        if (pathname.endsWith('/planned')) {
            return pathname.slice(0, -'/planned'.length);
        }

        if (pathname.endsWith('/emergency')) {
            return pathname.slice(0, -'/emergency'.length);
        }

        return pathname;
    }

    function createHiddenPrintFrame() {
        document
            .getElementById('hospitalizationPrintFrame')
            ?.remove();

        const frame = document.createElement('iframe');

        frame.id = 'hospitalizationPrintFrame';
        frame.name = 'hospitalizationPrintFrame';
        frame.title = 'Печать направления в стационар';

        Object.assign(frame.style, {
            position: 'fixed',
            left: '-10000px',
            top: '0',
            width: '210mm',
            height: '297mm',
            border: '0',
            opacity: '0',
            pointerEvents: 'none'
        });

        return frame;
    }

    function printLoadedFrame(frame) {
        const printWindow = frame?.contentWindow;

        if (!printWindow) {
            frame?.remove();
            throw new Error('Не удалось открыть печатный документ.');
        }

        let cleaned = false;

        const cleanup = () => {
            if (cleaned) {
                return;
            }

            cleaned = true;

            setTimeout(() => {
                frame.remove();
            }, 700);
        };

        printWindow.addEventListener(
            'afterprint',
            cleanup,
            {once: true}
        );

        // Страховочная очистка, если браузер не отдаст afterprint.
        window.setTimeout(cleanup, 120000);

        window.setTimeout(() => {
            try {
                printWindow.focus();
                printWindow.print();
            } catch (error) {
                cleanup();
                console.error('[hospitalization print]', error);
                alert('Не удалось открыть системное окно печати.');
            }
        }, 140);
    }

    function printBlankInHiddenFrame() {
        const frame = createHiddenPrintFrame();
        const urlObject = new URL(
            `${hospitalPrintBaseUrl()}/print/blank`,
            window.location.origin
        );

        // Печатный GET нельзя брать из HTTP-кэша:
        // иначе после изменения hospitalization.blade.php браузер способен
        // показать старую версию документа из iframe-кэша.
        urlObject.searchParams.set('_print', String(Date.now()));

        const url = urlObject.toString();

        frame.addEventListener(
            'load',
            () => {
                printLoadedFrame(frame);
            },
            {once: true}
        );

        frame.src = url;
        document.body.appendChild(frame);
    }

    function printFilledInHiddenFrame() {
        if (!form || !selectedPatient) {
            alert('Сначала выберите пациента.');
            patientSearchInput?.focus();
            return;
        }

        if (!requireSelectedDiagnosis()) {
            return;
        }

        if (formPatientId) {
            formPatientId.value = selectedPatient;
        }

        const frame = createHiddenPrintFrame();
        const targetName = frame.name;
        const previewForm = document.createElement('form');

        previewForm.method = 'POST';
        previewForm.action = `${hospitalPrintBaseUrl()}/print/preview`;
        previewForm.target = targetName;
        previewForm.style.display = 'none';

        const data = new FormData(form);

        // FormData(form) включает и поля, связанные с формой через form="hospitalReferralForm".
        for (const [name, value] of data.entries()) {
            if (value instanceof File) {
                continue;
            }

            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = name;
            input.value = String(value);

            previewForm.appendChild(input);
        }

        // На случай, если CSRF-поле по какой-то причине отсутствует в FormData.
        if (!previewForm.querySelector('input[name="_token"]')) {
            const token = csrf();

            if (token) {
                const csrfInput = document.createElement('input');

                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = token;

                previewForm.appendChild(csrfInput);
            }
        }

        document.body.appendChild(previewForm);

        /*
         * Сначала загружаем технический about:blank.
         * Только после его load отправляем POST в target iframe.
         * Так стартовый load iframe не может случайно вызвать печать пустой страницы.
         */
        frame.addEventListener(
            'load',
            () => {
                frame.addEventListener(
                    'load',
                    () => {
                        previewForm.remove();
                        printLoadedFrame(frame);
                    },
                    {once: true}
                );

                previewForm.submit();
            },
            {once: true}
        );

        frame.src = 'about:blank';
        document.body.appendChild(frame);
    }

    printBlankButton?.addEventListener(
        'click',
        () => {
            printBlankButton.disabled = true;

            try {
                printBlankInHiddenFrame();
            } finally {
                window.setTimeout(() => {
                    printBlankButton.disabled = false;
                }, 600);
            }
        }
    );

    printFilledButton?.addEventListener(
        'click',
        () => {
            if (!selectedPatient) {
                alert('Сначала выберите пациента.');
                patientSearchInput?.focus();
                return;
            }

            printFilledButton.disabled = true;

            try {
                printFilledInHiddenFrame();
            } finally {
                window.setTimeout(() => {
                    printFilledButton.disabled = false;
                }, 600);
            }
        }
    );

    form?.addEventListener('submit', event => {
        if (!selectedPatient) {
            event.preventDefault();
            switchTab('manual');
            alert('Выберите пациента перед сохранением направления.');
            patientSearchInput?.focus();
            return;
        }

        if (!requireSelectedDiagnosis()) {
            event.preventDefault();
            return;
        }

        if (formPatientId) formPatientId.value = selectedPatient;
    });

    initPatientSearch();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHospitalizationPage, {once: true});
} else {
    initHospitalizationPage();
}
