document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('createLabAssignmentModal');
    const modalToggleButton = document.getElementById('createLabAssignmentButton');

    if (!modalElement || !modalToggleButton) {
        return;
    }

    const modalCloseButtons = Array.from(
        modalElement.querySelectorAll('[data-assignment-close]')
    );
    const patientInput = document.getElementById('assignmentPatientSearch');
    const patientIdInput = document.getElementById('assignmentPatientId');
    const patientMenu = document.getElementById('assignmentPatientMenu');
    const sampleType = document.getElementById('assignmentSampleType');
    const parameterInput = document.getElementById('assignmentParameterSearch');
    const parameterMenu = document.getElementById('assignmentParameterMenu');
    const selectedList = document.getElementById('assignmentSelectedList');
    const selectedEmpty = document.getElementById('assignmentSelectedEmpty');
    const selectedCount = document.getElementById('assignmentSelectedCount');
    const titleInput = document.getElementById('assignmentTitle');
    const commentInput = document.getElementById('assignmentComment');

    if (
        !patientInput
        || !patientIdInput
        || !patientMenu
        || !sampleType
        || !parameterInput
        || !parameterMenu
        || !selectedList
        || !selectedEmpty
        || !selectedCount
        || !titleInput
    ) {
        return;
    }

    const patientSearchUrl = modalElement.dataset.patientSearchUrl || '';
    const parameterSearchUrl = modalElement.dataset.parameterSearchUrl || '';
    const patientDetailsUrlTemplate =
        modalElement.dataset.patientDetailsUrl
        || '/api/doctors/search-patients/for/__PATIENT__';

    let patientTimer = null;
    let parameterTimer = null;
    let patientController = null;
    let patientDetailsController = null;
    let parameterController = null;
    let automaticTitle = titleInput.value.trim() === '';

    injectPatientCardStyles();
    const patientCard = createPatientCard();

    function injectPatientCardStyles() {
        if (document.getElementById('assignmentPatientCardStyles')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'assignmentPatientCardStyles';
        style.textContent = `
            .assignment-patient-card {
                margin-top: 12px;
                padding: 14px;
                border: 1px solid #d7e7ea;
                border-radius: 14px;
                background: #f7fbfc;
            }

            .assignment-patient-card.d-none {
                display: none !important;
            }

            .assignment-patient-card__head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 12px;
            }

            .assignment-patient-card__name {
                color: #1f2937;
                font-size: 14px;
                font-weight: 800;
                line-height: 1.3;
            }

            .assignment-patient-card__meta {
                margin-top: 3px;
                color: #667085;
                font-size: 11px;
                line-height: 1.45;
            }

            .assignment-patient-card__source {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                flex: 0 0 auto;
                padding: 5px 9px;
                border-radius: 999px;
                background: #e4f6f8;
                color: #087f8c;
                font-size: 10px;
                font-weight: 700;
                white-space: nowrap;
            }

            .assignment-patient-card__grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px 14px;
            }

            .assignment-patient-card__grid > div {
                display: flex;
                min-width: 0;
                flex-direction: column;
                gap: 3px;
            }

            .assignment-patient-card__grid span {
                color: #8792a2;
                font-size: 10px;
                line-height: 1.3;
            }

            .assignment-patient-card__grid strong {
                overflow: hidden;
                color: #344054;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.35;
                text-overflow: ellipsis;
            }

            .assignment-patient-card__wide {
                grid-column: span 2;
            }

            .assignment-patient-card__full {
                grid-column: 1 / -1;
            }

            .assignment-patient-card__loading {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #667085;
                font-size: 12px;
            }

            .assignment-patient-card__spinner {
                width: 14px;
                height: 14px;
                border: 2px solid #cce7eb;
                border-top-color: #0f8fa2;
                border-radius: 50%;
                animation: assignmentPatientSpin .65s linear infinite;
            }

            @keyframes assignmentPatientSpin {
                to {
                    transform: rotate(360deg);
                }
            }

            @media (max-width: 900px) {
                .assignment-patient-card__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .assignment-patient-card__wide {
                    grid-column: span 2;
                }
            }

            @media (max-width: 560px) {
                .assignment-patient-card__head {
                    flex-direction: column;
                }

                .assignment-patient-card__grid {
                    grid-template-columns: 1fr;
                }

                .assignment-patient-card__wide,
                .assignment-patient-card__full {
                    grid-column: auto;
                }
            }
        `;
        document.head.append(style);
    }

    function createPatientCard() {
        const existing = document.getElementById('assignmentPatientCard');

        if (existing) {
            return existing;
        }

        const card = document.createElement('div');
        card.id = 'assignmentPatientCard';
        card.className = 'assignment-patient-card d-none';
        card.innerHTML = `
            <div class="assignment-patient-card__head">
                <div>
                    <div
                        id="assignmentPatientFullName"
                        class="assignment-patient-card__name"
                    >—</div>
                    <div
                        id="assignmentPatientMeta"
                        class="assignment-patient-card__meta"
                    >—</div>
                </div>

                <span class="assignment-patient-card__source">
                    <i class="bi bi-database-check"></i>
                    Данные из медкарты
                </span>
            </div>

            <div class="assignment-patient-card__grid">
                <div>
                    <span>№ медкарты</span>
                    <strong id="assignmentPatientMedcard">—</strong>
                </div>

                <div>
                    <span>Полис ОМС</span>
                    <strong id="assignmentPatientOms">—</strong>
                </div>

                <div>
                    <span>СНИЛС</span>
                    <strong id="assignmentPatientSnils">—</strong>
                </div>

                <div>
                    <span>Телефон</span>
                    <strong id="assignmentPatientPhone">—</strong>
                </div>

                <div class="assignment-patient-card__wide">
                    <span>Диагноз</span>
                    <strong id="assignmentPatientDiagnosis">—</strong>
                </div>

                <div class="assignment-patient-card__wide">
                    <span>Лечащий врач</span>
                    <strong id="assignmentPatientDoctor">—</strong>
                </div>

                <div class="assignment-patient-card__full">
                    <span>Адрес регистрации</span>
                    <strong id="assignmentPatientAddress">—</strong>
                </div>
            </div>
        `;

        const field = patientInput.closest('.assignment-field');

        if (field) {
            field.append(card);
        } else {
            patientInput.parentElement?.append(card);
        }

        return card;
    }

    function getPatientCardElement(id) {
        return document.getElementById(id);
    }

    function setPatientCardValue(id, value) {
        const element = getPatientCardElement(id);

        if (element) {
            element.textContent = value || '—';
        }
    }

    function showPatientCardLoading() {
        if (!patientCard) {
            return;
        }

        patientCard.classList.remove('d-none');
        patientCard.innerHTML = `
            <div class="assignment-patient-card__loading">
                <span class="assignment-patient-card__spinner"></span>
                Загружаем данные пациента…
            </div>
        `;
    }

    function resetPatientCardMarkup() {
        if (!patientCard) {
            return;
        }

        patientCard.innerHTML = `
            <div class="assignment-patient-card__head">
                <div>
                    <div
                        id="assignmentPatientFullName"
                        class="assignment-patient-card__name"
                    >—</div>
                    <div
                        id="assignmentPatientMeta"
                        class="assignment-patient-card__meta"
                    >—</div>
                </div>

                <span class="assignment-patient-card__source">
                    <i class="bi bi-database-check"></i>
                    Данные из медкарты
                </span>
            </div>

            <div class="assignment-patient-card__grid">
                <div>
                    <span>№ медкарты</span>
                    <strong id="assignmentPatientMedcard">—</strong>
                </div>
                <div>
                    <span>Полис ОМС</span>
                    <strong id="assignmentPatientOms">—</strong>
                </div>
                <div>
                    <span>СНИЛС</span>
                    <strong id="assignmentPatientSnils">—</strong>
                </div>
                <div>
                    <span>Телефон</span>
                    <strong id="assignmentPatientPhone">—</strong>
                </div>
                <div class="assignment-patient-card__wide">
                    <span>Диагноз</span>
                    <strong id="assignmentPatientDiagnosis">—</strong>
                </div>
                <div class="assignment-patient-card__wide">
                    <span>Лечащий врач</span>
                    <strong id="assignmentPatientDoctor">—</strong>
                </div>
                <div class="assignment-patient-card__full">
                    <span>Адрес регистрации</span>
                    <strong id="assignmentPatientAddress">—</strong>
                </div>
            </div>
        `;
    }

    function clearPatientDetails() {
        patientDetailsController?.abort();

        if (!patientCard) {
            return;
        }

        resetPatientCardMarkup();
        patientCard.classList.add('d-none');
    }

    function formatDateForDisplay(value) {
        if (!value) {
            return '';
        }

        if (/^\d{2}\.\d{2}\.\d{4}$/.test(value)) {
            return value;
        }

        const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);

        if (!match) {
            return String(value);
        }

        return `${match[3]}.${match[2]}.${match[1]}`;
    }

    function calculateAge(value) {
        if (!value) {
            return null;
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return null;
        }

        const now = new Date();
        let age = now.getFullYear() - date.getFullYear();
        const monthDiff = now.getMonth() - date.getMonth();

        if (
            monthDiff < 0
            || (monthDiff === 0 && now.getDate() < date.getDate())
        ) {
            age -= 1;
        }

        return age >= 0 ? age : null;
    }

    function ageLabel(age) {
        if (age === null || age === undefined || age === '') {
            return '';
        }

        const value = Number(age);

        if (!Number.isFinite(value)) {
            return '';
        }

        const mod10 = value % 10;
        const mod100 = value % 100;

        if (mod10 === 1 && mod100 !== 11) {
            return `${value} год`;
        }

        if (
            mod10 >= 2
            && mod10 <= 4
            && (mod100 < 12 || mod100 > 14)
        ) {
            return `${value} года`;
        }

        return `${value} лет`;
    }

    function genderLabel(value) {
        const normalized = String(value || '').trim().toUpperCase();

        if (['M', 'MALE', 'М', 'МУЖ', 'МУЖСКОЙ'].includes(normalized)) {
            return 'мужской';
        }

        if (['F', 'FEMALE', 'Ж', 'ЖЕН', 'ЖЕНСКИЙ'].includes(normalized)) {
            return 'женский';
        }

        return value ? String(value) : '';
    }

    function buildFullName(patient) {
        if (patient.full_name) {
            return String(patient.full_name).trim();
        }

        return [
            patient.surname,
            patient.name,
            patient.patronym
        ].filter(Boolean).join(' ').trim();
    }

    function buildDoctorName(patient) {
        if (typeof patient.doctor === 'string') {
            return patient.doctor.trim();
        }

        if (patient.doctor?.name) {
            return String(patient.doctor.name).trim();
        }

        return [
            patient.doctor?.surname,
            patient.doctor?.first_name,
            patient.doctor?.name,
            patient.doctor?.patronym
        ].filter(Boolean).join(' ').trim();
    }

    function buildDiagnosis(patient) {
        const diagnosis = patient.diagnosis || patient.diagnose || {};

        return [
            diagnosis.code || patient.diagnosis_code,
            diagnosis.title
                || diagnosis.name
                || patient.diagnosis_title
        ].filter(Boolean).join(' — ').trim();
    }

    function buildPatientDetailsUrl(patientId) {
        return patientDetailsUrlTemplate.replace(
            '__PATIENT__',
            encodeURIComponent(patientId)
        );
    }

    async function loadPatientDetails(patientId) {
        if (!patientId || !patientDetailsUrlTemplate) {
            clearPatientDetails();
            return;
        }

        patientDetailsController?.abort();
        patientDetailsController = new AbortController();
        showPatientCardLoading();

        try {
            const response = await fetch(
                buildPatientDetailsUrl(patientId),
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: patientDetailsController.signal
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const patient = await response.json();

            resetPatientCardMarkup();

            const fullName = buildFullName(patient);
            const birthAtRaw = patient.birth_at || patient.birth_date || '';
            const birthAtDisplay =
                patient.birth_at_display
                || formatDateForDisplay(birthAtRaw);
            const age =
                patient.age !== null && patient.age !== undefined
                    ? patient.age
                    : calculateAge(birthAtRaw);
            const gender = genderLabel(
                patient.gender || patient.sex
            );
            const diagnosis = buildDiagnosis(patient);
            const doctorName = buildDoctorName(patient);

            const meta = [
                birthAtDisplay ? `Дата рождения: ${birthAtDisplay}` : '',
                ageLabel(age),
                gender ? `Пол: ${gender}` : ''
            ].filter(Boolean).join(' · ');

            setPatientCardValue(
                'assignmentPatientFullName',
                fullName || patientInput.value
            );
            setPatientCardValue(
                'assignmentPatientMeta',
                meta || 'Основные сведения не заполнены'
            );
            setPatientCardValue(
                'assignmentPatientMedcard',
                patient.medcard_number
                    || patient.medical_card_number
                    || patient.card_number
            );
            setPatientCardValue(
                'assignmentPatientOms',
                patient.oms
                    || patient.insurance_policy
                    || patient.policy
            );
            setPatientCardValue(
                'assignmentPatientSnils',
                patient.snils
            );
            setPatientCardValue(
                'assignmentPatientPhone',
                patient.phone
            );
            setPatientCardValue(
                'assignmentPatientDiagnosis',
                diagnosis
            );
            setPatientCardValue(
                'assignmentPatientDoctor',
                doctorName
            );
            setPatientCardValue(
                'assignmentPatientAddress',
                patient.address_registration
            );

            if (
                fullName
                && (
                    patientInput.value.trim() === ''
                    || patientInput.value.trim().startsWith('Пациент #')
                )
            ) {
                patientInput.value = fullName;
            }

            if (
                commentInput
                && commentInput.value.trim() === ''
                && diagnosis
            ) {
                commentInput.value = `Диагноз: ${diagnosis}`;
            }

            patientCard.classList.remove('d-none');
            positionAssignmentModal();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error(
                'Не удалось загрузить данные пациента:',
                error
            );

            resetPatientCardMarkup();
            setPatientCardValue(
                'assignmentPatientFullName',
                patientInput.value || 'Пациент'
            );
            setPatientCardValue(
                'assignmentPatientMeta',
                'Не удалось загрузить дополнительные сведения'
            );
            patientCard.classList.remove('d-none');
        }
    }

    function positionAssignmentModal() {
        if (!modalElement.classList.contains('is-open')) {
            return;
        }

        const buttonRect = modalToggleButton.getBoundingClientRect();
        const viewportPadding = 16;
        const modalWidth = modalElement.offsetWidth;

        let left = buttonRect.right - modalWidth;
        left = Math.max(
            viewportPadding,
            Math.min(
                left,
                window.innerWidth - modalWidth - viewportPadding
            )
        );

        const top = Math.max(
            viewportPadding,
            buttonRect.bottom + 12
        );

        const availableHeight = Math.max(
            280,
            window.innerHeight - top - viewportPadding
        );

        modalElement.style.left = `${left}px`;
        modalElement.style.top = `${top}px`;
        modalElement.style.maxHeight = `${availableHeight}px`;

        const modalContent =
            modalElement.querySelector('.modal-content');

        if (modalContent) {
            modalContent.style.maxHeight = `${availableHeight}px`;
        }
    }

    function openAssignmentModal() {
        modalElement.classList.add('is-open');
        modalElement.setAttribute('aria-hidden', 'false');
        modalToggleButton.setAttribute('aria-expanded', 'true');

        window.requestAnimationFrame(function () {
            positionAssignmentModal();
            patientInput.focus();
        });
    }

    function closeAssignmentModal() {
        closeMenu(patientMenu);
        closeMenu(parameterMenu);
        modalElement.classList.remove('is-open');
        modalElement.setAttribute('aria-hidden', 'true');
        modalToggleButton.setAttribute('aria-expanded', 'false');
    }

    function escapeText(value) {
        return String(value ?? '').trim();
    }

    function normalizeItems(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        const candidates = [
            payload?.results,
            payload?.items,
            payload?.data,
            payload?.data?.results,
            payload?.data?.items
        ];

        return candidates.find(Array.isArray) || [];
    }

    function openMenu(menu) {
        menu.classList.add('is-open');
    }

    function closeMenu(menu) {
        menu.classList.remove('is-open');
        menu.replaceChildren();
    }

    function showState(menu, text) {
        const state = document.createElement('div');
        state.className = 'assignment-autocomplete__state';
        state.textContent = text;
        menu.replaceChildren(state);
        openMenu(menu);
    }

    function buildSearchUrl(baseUrl, params) {
        const url = new URL(baseUrl, window.location.origin);

        Object.entries(params).forEach(function ([key, value]) {
            if (
                value !== null
                && value !== undefined
                && value !== ''
            ) {
                url.searchParams.set(key, value);
            }
        });

        return url.toString();
    }

    function selectedItems() {
        return Array.from(
            selectedList.querySelectorAll(
                '[data-selected-parameter]'
            )
        );
    }

    function updateSelectedState() {
        const items = selectedItems();
        selectedCount.textContent = String(items.length);
        selectedEmpty.classList.toggle(
            'd-none',
            items.length > 0
        );

        if (!automaticTitle) {
            return;
        }

        const names = items
            .map(function (item) {
                return item.dataset.name || '';
            })
            .filter(Boolean);

        if (names.length === 0) {
            titleInput.value = '';
        } else if (names.length === 1) {
            titleInput.value = names[0];
        } else {
            titleInput.value =
                `${names[0]} и ещё ${names.length - 1}`;
        }
    }

    function hasSelectedParameter(id) {
        return selectedItems().some(function (item) {
            return String(item.dataset.id) === String(id);
        });
    }

    function addParameter(item) {
        const id = item.id ?? item.value;
        const name = escapeText(
            item.name ?? item.text ?? item.title
        );

        if (!id || !name || hasSelectedParameter(id)) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'assignment-selected__item';
        wrapper.dataset.selectedParameter = '';
        wrapper.dataset.id = String(id);
        wrapper.dataset.name = name;
        wrapper.dataset.unit = escapeText(item.unit);
        wrapper.dataset.group = escapeText(item.group);
        wrapper.dataset.sampleType =
            escapeText(item.sample_type);

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'parameter_ids[]';
        hidden.value = String(id);

        const label = document.createElement('span');
        label.textContent = name;

        const remove = document.createElement('button');
        remove.className = 'assignment-selected__remove';
        remove.type = 'button';
        remove.dataset.removeParameter = '';
        remove.setAttribute(
            'aria-label',
            'Удалить показатель'
        );
        remove.textContent = '×';

        wrapper.append(hidden, label, remove);
        selectedList.append(wrapper);
        updateSelectedState();
        positionAssignmentModal();
    }

    function clearParameters() {
        selectedItems().forEach(function (item) {
            item.remove();
        });
        updateSelectedState();
    }

    function renderPatients(items) {
        patientMenu.replaceChildren();

        if (!items.length) {
            showState(patientMenu, 'Пациенты не найдены');
            return;
        }

        items.forEach(function (item) {
            const id = item.id ?? item.value;
            const text = escapeText(
                item.text
                ?? item.full_name
                ?? item.fio
            );

            if (!id || !text) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className =
                'assignment-autocomplete__item';
            button.textContent = text;

            if (item.birth_at) {
                const meta = document.createElement('span');
                meta.className =
                    'assignment-autocomplete__item-meta';
                meta.textContent =
                    `Дата рождения: ${formatDateForDisplay(item.birth_at)}`;
                button.append(meta);
            }

            button.addEventListener('click', function () {
                patientIdInput.value = String(id);
                patientInput.value = text;
                closeMenu(patientMenu);
                loadPatientDetails(id);
            });

            patientMenu.append(button);
        });

        openMenu(patientMenu);
    }

    function renderParameters(items) {
        parameterMenu.replaceChildren();

        const prepared = items.filter(function (item) {
            const id = item.id ?? item.value;
            return id && !hasSelectedParameter(id);
        });

        if (!prepared.length) {
            showState(
                parameterMenu,
                'Подходящие показатели не найдены'
            );
            return;
        }

        prepared.forEach(function (item) {
            const name = escapeText(
                item.name ?? item.text ?? item.title
            );

            if (!name) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className =
                'assignment-autocomplete__item';
            button.textContent = name;

            const metaValues = [
                item.group,
                item.unit,
                item.sample_type
            ].map(escapeText).filter(Boolean);

            if (metaValues.length) {
                const meta = document.createElement('span');
                meta.className =
                    'assignment-autocomplete__item-meta';
                meta.textContent = metaValues.join(' · ');
                button.append(meta);
            }

            button.addEventListener('click', function () {
                addParameter(item);
                parameterInput.value = '';
                closeMenu(parameterMenu);
                parameterInput.focus();
            });

            parameterMenu.append(button);
        });

        openMenu(parameterMenu);
    }

    patientInput.addEventListener('input', function () {
        patientIdInput.value = '';
        clearPatientDetails();
        clearTimeout(patientTimer);

        const query = patientInput.value.trim();

        if (
            query.length < 2
            || patientSearchUrl === ''
        ) {
            closeMenu(patientMenu);
            return;
        }

        patientTimer = window.setTimeout(
            async function () {
                patientController?.abort();
                patientController = new AbortController();
                showState(
                    patientMenu,
                    'Поиск пациента…'
                );

                try {
                    const response = await fetch(
                        buildSearchUrl(
                            patientSearchUrl,
                            {q: query}
                        ),
                        {
                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',
                                'Accept':
                                    'application/json'
                            },
                            signal:
                                patientController.signal
                        }
                    );

                    if (!response.ok) {
                        throw new Error(
                            `HTTP ${response.status}`
                        );
                    }

                    renderPatients(
                        normalizeItems(
                            await response.json()
                        )
                    );
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showState(
                            patientMenu,
                            'Не удалось загрузить пациентов'
                        );
                    }
                }
            },
            280
        );
    });

    sampleType.addEventListener('change', function () {
        closeMenu(parameterMenu);
        parameterInput.value = '';
        parameterInput.disabled =
            sampleType.value === '';
        parameterInput.placeholder =
            sampleType.value
                ? 'Введите название показателя'
                : 'Сначала выберите биоматериал';

        const wrongMaterialSelected =
            selectedItems().some(function (item) {
                return (
                    item.dataset.sampleType
                    && item.dataset.sampleType
                        !== sampleType.value
                );
            });

        if (wrongMaterialSelected) {
            clearParameters();
        }
    });

    parameterInput.addEventListener('input', function () {
        clearTimeout(parameterTimer);

        const query = parameterInput.value.trim();

        if (
            query.length < 2
            || parameterSearchUrl === ''
            || sampleType.value === ''
        ) {
            closeMenu(parameterMenu);
            return;
        }

        parameterTimer = window.setTimeout(
            async function () {
                parameterController?.abort();
                parameterController =
                    new AbortController();
                showState(
                    parameterMenu,
                    'Поиск показателей…'
                );

                try {
                    const response = await fetch(
                        buildSearchUrl(
                            parameterSearchUrl,
                            {
                                q: query,
                                sample_type:
                                    sampleType.value
                            }
                        ),
                        {
                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',
                                'Accept':
                                    'application/json'
                            },
                            signal:
                                parameterController.signal
                        }
                    );

                    if (!response.ok) {
                        throw new Error(
                            `HTTP ${response.status}`
                        );
                    }

                    renderParameters(
                        normalizeItems(
                            await response.json()
                        )
                    );
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showState(
                            parameterMenu,
                            'Не удалось загрузить показатели'
                        );
                    }
                }
            },
            280
        );
    });

    selectedList.addEventListener(
        'click',
        function (event) {
            const button = event.target.closest(
                '[data-remove-parameter]'
            );

            if (!button) {
                return;
            }

            button
                .closest('[data-selected-parameter]')
                ?.remove();

            updateSelectedState();
            positionAssignmentModal();
        }
    );

    titleInput.addEventListener('input', function () {
        automaticTitle =
            titleInput.value.trim() === '';
    });

    modalToggleButton.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                modalElement.classList.contains(
                    'is-open'
                )
            ) {
                closeAssignmentModal();
                return;
            }

            openAssignmentModal();
        }
    );

    modalCloseButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeAssignmentModal();
            modalToggleButton.focus();
        });
    });

    document.addEventListener('click', function (event) {
        if (
            !patientMenu.contains(event.target)
            && event.target !== patientInput
        ) {
            closeMenu(patientMenu);
        }

        if (
            !parameterMenu.contains(event.target)
            && event.target !== parameterInput
        ) {
            closeMenu(parameterMenu);
        }

        if (
            modalElement.classList.contains('is-open')
            && !modalElement.contains(event.target)
            && !modalToggleButton.contains(event.target)
        ) {
            closeAssignmentModal();
        }
    });

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape'
                && modalElement.classList.contains(
                    'is-open'
                )
            ) {
                closeAssignmentModal();
                modalToggleButton.focus();
            }
        }
    );

    window.addEventListener(
        'resize',
        positionAssignmentModal
    );

    window.addEventListener(
        'scroll',
        positionAssignmentModal,
        true
    );

    sampleType.dispatchEvent(new Event('change'));
    updateSelectedState();

    if (patientIdInput.value) {
        loadPatientDetails(patientIdInput.value);
    }

    if (modalElement.dataset.open === '1') {
        openAssignmentModal();
    }
});
