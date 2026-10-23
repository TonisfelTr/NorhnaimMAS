{{-- Модальное оформление пациента на странице Госпитализация. Без отдельного перехода. --}}
<div class="modal fade adm-modal" id="inpatientAdmitModal" data-admission-scheme-version="2026-10-18.10" tabindex="-1" aria-labelledby="inpatientAdmitTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="inpatientAdmitForm" autocomplete="off">
      <div class="modal-header">
        <div>
          <div class="adm-eyebrow mb-2"><i class="bi bi-hospital"></i> Поступление в стационар</div>
          <h2 class="modal-title" id="inpatientAdmitTitle">Оформление пациента</h2>
          <p class="adm-admit-subtitle">Выберите пациента и место на схеме палаты — сохранение только после подтверждения.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <div class="adm-error" id="admFormError" role="alert"></div>
        <input type="hidden" name="patient_id" id="admPatientId">
        <div class="adm-admit-layout">
          <section class="adm-admit-panel" aria-label="Данные оформления">
            <h3 class="adm-admit-panel-title"><i class="bi bi-person-check"></i> Данные поступления</h3>
            <p class="adm-admit-note"><i class="bi bi-info-circle"></i><span>После выбора палаты справа появится её схема. Занятые койки показаны для ориентира, но выбрать можно только свободную.</span></p>
            <div class="adm-form-grid">
              <div class="adm-form-grid__full">
                <label for="admPatientSearch" class="adm-label">Пациент <span class="text-danger">*</span></label>
                <div class="adm-patient-search">
                  <input id="admPatientSearch" type="search" class="adm-control" placeholder="Начните вводить ФИО (от 2 символов)" aria-autocomplete="list" aria-controls="admPatientResults">
                  <div class="adm-results d-none" id="admPatientResults" role="listbox"></div>
                </div>
                <div class="adm-selected" id="admPatientSelected"><span id="admPatientSelectedText"></span><button type="button" class="adm-selected__clear" id="admPatientClear">Сменить</button></div>
                <div class="adm-help">Пациенты, которые уже размещены в стационаре, исключаются из поиска.</div>
              </div>
              <div class="adm-form-grid__full">
                <label for="admDepartmentSelect" class="adm-label">Отделение <span class="text-danger">*</span></label>
                <select id="admDepartmentSelect" name="department_id" class="adm-control" aria-describedby="admDepartmentHelp" required disabled><option value="">Загружаем отделения…</option></select>
                <div class="adm-help" id="admDepartmentHelp" aria-live="polite">Детское — 0–7 лет, подростковое — 8–14 лет, взрослое — с 15 лет. Возраст и пол пациента проверяются при выборе отделения.</div>
              </div>
              <div class="adm-form-grid__full">
                <label for="admRoomSelect" class="adm-label">Палата <span class="text-danger">*</span></label>
                <select id="admRoomSelect" name="room_id" class="adm-control" required disabled><option value="">Сначала выберите отделение</option></select>
              </div>
              <div class="adm-form-grid__full">
                <label for="admBedSelect" class="adm-label">Свободная койка <span class="text-danger">*</span></label>
                <select id="admBedSelect" name="bed_id" class="adm-control" required disabled><option value="">Сначала выберите палату</option></select>
                <div class="adm-help">Можно выбрать койку из списка или нажать на неё на схеме справа.</div>
              </div>
              <div class="adm-form-grid__full">
                <label for="admComment" class="adm-label">Комментарий при оформлении</label>
                <textarea id="admComment" name="comment" class="adm-control" rows="2" maxlength="1000" placeholder="Необязательно"></textarea>
              </div>
            </div>
          </section>
          <section class="adm-scheme-panel" aria-labelledby="admSchemeTitle">
            <div class="adm-scheme-top">
              <div>
                <span class="adm-scheme-kicker">План размещения</span>
                <h3 class="adm-scheme-title" id="admSchemeTitle">Схема палаты</h3>
              </div>
              <span id="admSchemeFreeCount" class="adm-scheme-free-count is-empty"><i class="bi bi-check-circle"></i> Выберите палату</span>
            </div>
            <p class="adm-scheme-description" id="admSchemeDescription">Выберите отделение и палату — покажем её сохранённую схему.</p>
            <div class="adm-scheme-viewport" id="admSchemeViewport">
              <div class="adm-scheme-state" id="admSchemeState">
                <i class="bi bi-grid-3x3-gap"></i>
                <strong id="admSchemeStateTitle">Схема пока не выбрана</strong>
                <span id="admSchemeStateText">После выбора палаты здесь появятся койки и расположение мебели.</span>
              </div>
              <div class="adm-scheme-canvas" id="admSchemeCanvas" role="group" aria-label="Интерактивная схема палаты" hidden></div>
              <div class="adm-scheme-fallback" id="admSchemeFallback" hidden>
                <p class="adm-scheme-fallback-note">Для этой палаты ещё не настроена схема расположения. Койки ниже показаны списком, а не на плане.</p>
                <div class="adm-scheme-fallback-list" id="admSchemeFallbackList"></div>
              </div>
            </div>
            <div class="adm-scheme-legend" aria-label="Условные обозначения">
              <span><b></b>Свободна</span>
              <span><b class="is-occupied"></b>Занята</span>
              <span><b class="is-selected"></b>Выбрана</span>
              <span><b class="is-item"></b>Мебель</span>
            </div>
            <p class="adm-scheme-status" id="admSchemeStatus" aria-live="polite">Выберите палату для просмотра схемы.</p>
          </section>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="adm-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="submit" id="admSubmit" class="adm-primary" disabled><i class="bi bi-check-circle"></i> Оформить и разместить</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('inpatientAdmitModal');
    const form = document.getElementById('inpatientAdmitForm');
    if (!modal || !form) return;

    const $ = window.jQuery;
    const elements = {
        search: document.getElementById('admPatientSearch'),
        results: document.getElementById('admPatientResults'),
        patientId: document.getElementById('admPatientId'),
        selected: document.getElementById('admPatientSelected'),
        selectedText: document.getElementById('admPatientSelectedText'),
        clear: document.getElementById('admPatientClear'),
        department: document.getElementById('admDepartmentSelect'),
        departmentHelp: document.getElementById('admDepartmentHelp'),
        room: document.getElementById('admRoomSelect'),
        bed: document.getElementById('admBedSelect'),
        submit: document.getElementById('admSubmit'),
        error: document.getElementById('admFormError'),
        comment: document.getElementById('admComment'),
        schemeCanvas: document.getElementById('admSchemeCanvas'),
        schemeFallback: document.getElementById('admSchemeFallback'),
        schemeFallbackList: document.getElementById('admSchemeFallbackList'),
        schemeState: document.getElementById('admSchemeState'),
        schemeStateTitle: document.getElementById('admSchemeStateTitle'),
        schemeStateText: document.getElementById('admSchemeStateText'),
        schemeTitle: document.getElementById('admSchemeTitle'),
        schemeDescription: document.getElementById('admSchemeDescription'),
        schemeCount: document.getElementById('admSchemeFreeCount'),
        schemeStatus: document.getElementById('admSchemeStatus'),
    };
    const endpoints = {
        options: @json(route('doctors.inpatient.admissions.options')),
        patients: @json(route('doctors.inpatient.admissions.patients.search')),
        store: @json(route('doctors.inpatient.admissions.store')),
        index: @json(route('doctors.reception.index')),
    };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let rooms = [];
    let departments = [];
    let selectedPatient = null;
    let searchTimer = null;
    let searchController = null;
    let optionsController = null;
    let loadingOptions = false;
    let requesting = false;
    let schemeController = null;
    let currentScheme = null;
    const departmentHelp = elements.departmentHelp.textContent;

    const selectFields = [elements.department, elements.room, elements.bed];
    if ($?.fn?.select2) {
        selectFields.forEach(function (field) {
            $(field).select2({ width: '100%', dropdownParent: $(modal), minimumResultsForSearch: 0, language: { noResults: function () { return 'Нет вариантов'; } } });
        });
    }

    // Select2 emits its `change` via jQuery.trigger('change'); this does not
    // reach listeners installed using addEventListener. jQuery .on observes
    // both Select2 changes and native change events, including a bed chosen
    // by clicking the ward plan. Internal change.select2 refreshes must NOT
    // reset dependent fields, hence the separate namespace.
    function onPlacementSelectChange(select, handler) {
        if ($?.fn?.select2) {
            $(select).on('change.inpatientPlacement', handler);
        } else {
            select.addEventListener('change', handler);
        }
    }

    function fillSelect(select, items, placeholder) {
        select.replaceChildren();
        const empty = new Option(placeholder, '');
        select.add(empty);
        items.forEach(function (item) {
            const option = new Option(item.label, String(item.id));
            option.disabled = item.disabled === true;
            if (item.restriction) option.title = item.restriction;
            select.add(option);
        });
        select.value = '';
        select.disabled = items.length === 0;
        if ($?.fn?.select2) $(select).trigger('change.select2');
        updateSubmit();
    }
    // Схема палаты загружается по существующему /options?room_id=...:
    // без нового маршрута, отдельно от списка вариантов оформления.
    function showSchemeState(title, message) {
        elements.schemeCanvas.hidden = true;
        elements.schemeFallback.hidden = true;
        elements.schemeState.hidden = false;
        elements.schemeStateTitle.textContent = title;
        elements.schemeStateText.textContent = message;
    }

    function clearRoomScheme(title = 'Схема пока не выбрана', message = 'Выберите палату, чтобы увидеть расположение коек.') {
        schemeController?.abort();
        schemeController = null;
        currentScheme = null;
        elements.schemeCanvas.replaceChildren();
        elements.schemeFallbackList.replaceChildren();
        showSchemeState(title, message);
        elements.schemeTitle.textContent = 'Схема палаты';
        elements.schemeDescription.textContent = 'Выберите отделение и палату — покажем её сохранённую схему.';
        elements.schemeCount.classList.add('is-empty');
        elements.schemeCount.textContent = 'Выберите палату';
        elements.schemeStatus.textContent = 'Выберите палату для просмотра схемы.';
    }

    const itemKinds = {
        cabinet: { label: 'Шкаф', icon: 'bi-archive' },
        bedside_table: { label: 'Тумбочка', icon: 'bi-square' },
        column: { label: 'Колонна', icon: 'bi-square-fill' },
        entrance: { label: 'Вход', icon: 'bi-door-open' },
        window: { label: 'Окно', icon: 'bi-window' },
        socket: { label: 'Розетка', icon: 'bi-plug' },
        toilet: { label: 'Туалет', icon: 'bi-badge-wc' },
    };

    function numberOr(value, fallback) {
        const result = Number(value);
        return Number.isFinite(result) ? result : fallback;
    }

    function placeOnCanvas(node, element, layout, isBed, isLegacy) {
        const canvasWidth = Math.max(1, numberOr(layout.canvas_width, 900));
        const canvasHeight = Math.max(1, numberOr(layout.canvas_height, 560));
        const width = Math.max(1, numberOr(element.width, 100));
        const height = Math.max(1, numberOr(element.height, 70));
        const rotation = ((numberOr(element.rotation, 0) % 360) + 360) % 360;
        let x = numberOr(element.x, 0);
        let y = numberOr(element.y, 0);

        // Координаты кроватей в актуальном редакторе относятся к видимому
        // прямоугольнику после поворота. Для CSS rotate() восстанавливаем
        // позицию внутреннего неповёрнутого прямоугольника.
        if (isBed && !isLegacy) {
            const angle = rotation * Math.PI / 180;
            const visualWidth = Math.abs(Math.cos(angle)) * width + Math.abs(Math.sin(angle)) * height;
            const visualHeight = Math.abs(Math.sin(angle)) * width + Math.abs(Math.cos(angle)) * height;
            x += (visualWidth - width) / 2;
            y += (visualHeight - height) / 2;
        }

        node.style.left = (x / canvasWidth * 100) + '%';
        node.style.top = (y / canvasHeight * 100) + '%';
        node.style.width = (width / canvasWidth * 100) + '%';
        node.style.height = (height / canvasHeight * 100) + '%';
        node.style.transform = 'rotate(' + rotation + 'deg)';
    }

    function makeBedButton(bed, layout = null, isLegacy = false) {
        const available = bed.is_available === true;
        const occupied = bed.occupied === true;
        const title = 'Койка №' + String(bed.number ?? '—');
        const state = occupied ? 'Занята' : available ? 'Свободна' : 'Недоступна';
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.bedId = String(bed.id);
        button.className = layout ? 'adm-scheme-object adm-scheme-bed' : 'adm-scheme-fallback-bed';
        if (occupied) button.classList.add('is-occupied');
        if (!occupied && !available) button.classList.add('is-inactive');
        button.setAttribute('aria-label', title + ' — ' + state);
        button.setAttribute('aria-pressed', 'false');
        button.title = title + ' — ' + state;
        button.disabled = !available;

        const number = document.createElement('span');
        number.textContent = layout ? '№' + String(bed.number ?? '—') : title;
        const small = document.createElement('small');
        small.textContent = state;
        button.append(number, small);

        if (layout) {
            placeOnCanvas(button, bed, layout, true, isLegacy);
            const angle = ((numberOr(bed.rotation, 0) % 360) + 360) % 360;
            button.style.setProperty('--scheme-label-rotation', '-' + angle + 'deg');
            if (Math.abs(angle - 90) < 15 || Math.abs(angle - 270) < 15) button.classList.add('is-turned');
        }
        if (available) {
            button.addEventListener('click', function () {
                // Занятые и выключенные койки на схеме некликабельны.
                if (!Array.from(elements.bed.options).some(option => option.value === String(bed.id) && !option.disabled)) {
                    elements.schemeStatus.textContent = 'Койка больше недоступна. Выберите другую.';
                    return;
                }
                elements.bed.value = String(bed.id);
                if ($?.fn?.select2) $(elements.bed).trigger('change.select2');
                elements.bed.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
        return button;
    }

    function updateSchemeSelection() {
        const selectedId = elements.bed.value;
        [elements.schemeCanvas, elements.schemeFallback].forEach(function (parent) {
            parent.querySelectorAll('[data-bed-id]').forEach(function (button) {
                const selected = button.dataset.bedId === selectedId && selectedId !== '';
                button.classList.toggle('is-selected', selected);
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
        });
        if (!currentScheme) return;
        const bed = (currentScheme.beds || []).find(item => String(item.id) === selectedId);
        elements.schemeStatus.textContent = bed
            ? 'Выбрана койка №' + bed.number + '. После проверки пациента нажмите «Оформить и разместить».'
            : 'Нажмите на свободную койку на схеме или выберите её в списке слева.';
    }

    function renderRoomScheme(layout, room) {
        elements.schemeCanvas.replaceChildren();
        elements.schemeFallbackList.replaceChildren();
        elements.schemeState.hidden = true;
        elements.schemeTitle.textContent = room?.label || 'Схема палаты';

        const beds = Array.isArray(layout.beds) ? layout.beds : [];
        const items = Array.isArray(layout.items) ? layout.items : [];
        const free = beds.filter(item => item.is_available === true).length;
        const occupied = beds.filter(item => item.occupied === true).length;
        elements.schemeDescription.textContent = 'Расположение коек и мебели из сохранённой схемы палаты. Койку можно выбрать одним нажатием.';
        elements.schemeCount.classList.toggle('is-empty', free === 0);
        elements.schemeCount.textContent = free + ' свободно · ' + occupied + ' занято';

        if (!beds.length) {
            showSchemeState('В палате нет коек', 'Добавьте койки в настройках палат.');
            elements.schemeStatus.textContent = 'Нет мест для размещения.';
            return;
        }

        const bedPositions = new Set(beds.map(item => Number(item.x || 0) + ':' + Number(item.y || 0)));
        const layoutMissing = (beds.length > 1 && bedPositions.size === 1)
            || (beds.length === 1 && Number(beds[0].x || 0) === 0
                && Number(beds[0].y || 0) === 0 && items.length === 0);

        if (layoutMissing) {
            // Не выдаём наложенные друг на друга координаты за реальный план.
            elements.schemeFallback.hidden = false;
            elements.schemeCanvas.hidden = true;
            beds.forEach(bed => elements.schemeFallbackList.appendChild(makeBedButton(bed)));
            elements.schemeDescription.textContent = 'У палаты нет настроенной расстановки. Доступен список коек.';
            updateSchemeSelection();
            return;
        }

        elements.schemeFallback.hidden = true;
        elements.schemeCanvas.hidden = false;
        const canvasWidth = Math.max(1, numberOr(layout.canvas_width, 900));
        const canvasHeight = Math.max(1, numberOr(layout.canvas_height, 560));
        elements.schemeCanvas.style.aspectRatio = canvasWidth + ' / ' + canvasHeight;

        items.forEach(function (item) {
            const definition = itemKinds[item.type] || { label: 'Элемент', icon: 'bi-square' };
            const node = document.createElement('div');
            node.className = 'adm-scheme-object adm-scheme-item';
            if (itemKinds[item.type]) node.classList.add('is-' + item.type);
            const symbol = document.createElement('i');
            symbol.className = 'bi ' + definition.icon;
            const name = document.createElement('span');
            name.textContent = item.label || definition.label;
            node.title = name.textContent;
            node.append(symbol, name);
            placeOnCanvas(node, item, layout, false, false);
            elements.schemeCanvas.appendChild(node);
        });

        const legacyCoordinates = beds.some(item => Number(item.x || 0) < 0 || Number(item.y || 0) < 0);
        beds.forEach(bed => elements.schemeCanvas.appendChild(makeBedButton(bed, layout, legacyCoordinates)));
        updateSchemeSelection();
    }

    async function loadRoomScheme() {
        schemeController?.abort();
        const roomId = elements.room.value;
        const room = rooms.find(item => String(item.id) === roomId);
        clearRoomScheme();
        if (!roomId || !room) return;

        elements.schemeTitle.textContent = room.label;
        elements.schemeDescription.textContent = 'Загружаем сохранённую схему палаты…';
        elements.schemeCount.classList.add('is-empty');
        elements.schemeCount.textContent = 'Загрузка…';
        showSchemeState('Загрузка схемы', 'Получаем расположение коек и мебели.');
        elements.schemeStatus.textContent = 'Проверяем занятость коек…';

        const controller = new AbortController();
        schemeController = controller;
        try {
            const url = new URL(endpoints.options, window.location.origin);
            url.searchParams.set('room_id', roomId);
            const response = await fetch(url, {
                headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store',
            });
            const data = await response.json().catch(function () { return {}; });
            if (!response.ok) throw new Error(data.message || 'Схема палаты недоступна.');
            if (controller.signal.aborted || schemeController !== controller || elements.room.value !== roomId) return;
            const layout = data.room_layout;
            if (!layout || String(layout.id) !== roomId || !Array.isArray(layout.beds)) {
                throw new Error('Сервер не вернул координаты палаты. Проверьте обновление контроллера.');
            }

            // Пересверяем список с текущей занятостью (она могла измениться
            // после первоначальной загрузки вариантов размещения).
            const previous = elements.bed.value;
            const choices = layout.beds.filter(bed => bed.is_available === true).map(function (bed) {
                return { id: bed.id, label: 'Койка №' + bed.number + (bed.label ? ' — ' + bed.label : '') };
            });
            room.beds = choices;
            fillSelect(elements.bed, choices, choices.length ? 'Выберите свободную койку' : 'Нет свободных коек');
            if (choices.some(bed => String(bed.id) === previous)) {
                elements.bed.value = previous;
                if ($?.fn?.select2) $(elements.bed).trigger('change.select2');
            }
            currentScheme = layout;
            renderRoomScheme(layout, room);
            updateSubmit();
        } catch (error) {
            if (controller.signal.aborted || schemeController !== controller) return;
            showSchemeState('Схема не загрузилась', error.message || 'Не удалось получить схему палаты.');
            elements.schemeCount.classList.add('is-empty');
            elements.schemeCount.textContent = 'Нет данных';
            elements.schemeStatus.textContent = 'Можно выбрать свободную койку из списка слева.';
            elements.schemeDescription.textContent = 'Проверьте подключение или попробуйте выбрать палату ещё раз.';
        } finally {
            if (schemeController === controller) schemeController = null;
        }
    }

    function updateSubmit() {
        const department = departments.find(function (item) { return String(item.id) === elements.department.value; });
        elements.submit.disabled = requesting || loadingOptions || !selectedPatient
            || department?.patient_eligible !== true || !elements.room.value || !elements.bed.value;
    }
    function setError(message) {
        elements.error.textContent = message || 'Не удалось выполнить действие.';
        elements.error.classList.add('is-visible');
    }
    function clearError() {
        elements.error.textContent = '';
        elements.error.classList.remove('is-visible');
    }
    function resetResults() {
        elements.results.replaceChildren();
        elements.results.classList.add('d-none');
    }
    function clearPatient() {
        window.clearTimeout(searchTimer);
        searchController?.abort();
        optionsController?.abort();
        selectedPatient = null;
        elements.patientId.value = '';
        elements.search.value = '';
        elements.selected.classList.remove('is-visible');
        elements.search.disabled = false;
        resetResults();
        updateSubmit();
    }
    function selectPatient(patient) {
        window.clearTimeout(searchTimer);
        searchController?.abort();
        selectedPatient = patient;
        elements.patientId.value = String(patient.id);
        elements.search.value = patient.name;
        elements.search.disabled = true;
        elements.selectedText.textContent = patient.name + (patient.birth_at ? ' · ' + patient.birth_at : '');
        elements.selected.classList.add('is-visible');
        resetResults();
        clearError();
        updateSubmit();
        loadOptions().catch(function (error) {
            if (error.name !== 'AbortError') setError(error.message || 'Не удалось проверить доступные отделения.');
        });
    }
    function renderPatients(items) {
        resetResults();
        elements.results.classList.remove('d-none');
        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'adm-help p-3';
            empty.textContent = 'Свободных для оформления пациентов не найдено.';
            elements.results.appendChild(empty);
            return;
        }
        items.forEach(function (patient) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'adm-result';
            button.setAttribute('role', 'option');
            const name = document.createElement('strong');
            name.textContent = patient.name;
            const birth = document.createElement('small');
            birth.textContent = patient.birth_at ? 'Дата рождения: ' + patient.birth_at : 'Дата рождения не указана';
            button.append(name, birth);
            button.addEventListener('click', function () { selectPatient(patient); });
            elements.results.appendChild(button);
        });
    }
    async function searchPatients(query) {
        searchController?.abort();
        const controller = new AbortController();
        searchController = controller;
        const departmentId = elements.department.value;
        const isCurrentSearch = function () {
            return searchController === controller && !controller.signal.aborted
                && !selectedPatient && elements.search.value.trim() === query
                && elements.department.value === departmentId;
        };
        try {
            const url = new URL(endpoints.patients, window.location.origin);
            url.searchParams.set('q', query);
            if (departmentId) url.searchParams.set('department_id', departmentId);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
            const data = await response.json();
            if (!isCurrentSearch()) return;
            if (!response.ok) throw new Error(data.message || 'Ошибка поиска пациентов.');
            renderPatients(Array.isArray(data.items) ? data.items : []);
        } catch (error) {
            if (isCurrentSearch() && error.name !== 'AbortError') setError(error.message || 'Не удалось найти пациента.');
        }
    }
    elements.search.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchController?.abort();
        resetResults();
        clearError();
        const q = elements.search.value.trim();
        if (q.length < 2) { searchController?.abort(); resetResults(); return; }
        searchTimer = window.setTimeout(function () { searchPatients(q); }, 280);
    });
    elements.search.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            const first = elements.results.querySelector('button');
            if (first) { event.preventDefault(); first.click(); }
        }
        if (event.key === 'Escape') resetResults();
    });
    elements.clear.addEventListener('click', function () {
        clearPatient();
        clearError();
        loadOptions().catch(function (error) {
            if (error.name !== 'AbortError') setError(error.message || 'Ошибка загрузки отделений.');
        });
        elements.search.focus();
    });

    function updateDepartmentHelp() {
        const department = departments.find(function (item) { return String(item.id) === elements.department.value; });
        if (department?.patient_eligible === false) {
            elements.departmentHelp.textContent = department.patient_restriction || 'Это отделение недоступно для выбранного пациента.';
            return;
        }
        if (department) {
            elements.departmentHelp.textContent = department.age_group_label
                ? department.age_group_label + '. Выберите палату и свободную койку.'
                : departmentHelp;
            return;
        }
        const allUnavailable = departments.length > 0 && departments.every(function (item) { return item.patient_eligible === false; });
        const restriction = allUnavailable
            ? departments.find(function (item) { return item.patient_restriction; })
            : null;
        elements.departmentHelp.textContent = restriction?.patient_restriction || departmentHelp;
    }
    function updateRooms() {
        const list = rooms.filter(function (room) { return String(room.department_id) === elements.department.value; });
        fillSelect(elements.room, list, list.length ? 'Выберите палату' : 'Нет палат в отделении');
        fillSelect(elements.bed, [], 'Сначала выберите палату');
        clearRoomScheme();
        updateDepartmentHelp();
    }
    onPlacementSelectChange(elements.department, function () {
        updateRooms();
        window.clearTimeout(searchTimer);
        searchController?.abort();
        resetResults();
        const query = elements.search.value.trim();
        if (!selectedPatient && query.length >= 2) searchPatients(query);
    });
    onPlacementSelectChange(elements.room, function () {
        const room = rooms.find(function (item) { return String(item.id) === elements.room.value; });
        const beds = room?.beds ?? [];
        fillSelect(elements.bed, beds, beds.length ? 'Выберите койку' : 'Нет свободных коек');
        loadRoomScheme();
    });

    onPlacementSelectChange(elements.bed, function () {
        updateSubmit();
        updateSchemeSelection();
    });

    async function loadOptions() {
        optionsController?.abort();
        const controller = new AbortController();
        optionsController = controller;
        const patientId = selectedPatient?.id ?? null;
        const selectedDepartmentId = elements.department.value;
        loadingOptions = true;
        departments = [];
        rooms = [];
        clearRoomScheme('Загрузка вариантов', 'Сначала выберите отделение и палату.');
        updateDepartmentHelp();
        fillSelect(elements.department, [], 'Загружаем отделения…');
        fillSelect(elements.room, [], 'Сначала выберите отделение');
        fillSelect(elements.bed, [], 'Сначала выберите палату');
        try {
            const url = new URL(endpoints.options, window.location.origin);
            if (patientId !== null) url.searchParams.set('patient_id', String(patientId));
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
            const data = await response.json();
            if (controller.signal.aborted || optionsController !== controller
                || (selectedPatient?.id ?? null) !== patientId) return;
            if (!response.ok) {
                const detail = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(detail || data.message || 'Не удалось загрузить койки.');
            }
            rooms = Array.isArray(data.rooms) ? data.rooms : [];
            departments = Array.isArray(data.departments) ? data.departments : [];
            const choices = departments.map(function (item) {
                return {
                    id: item.id,
                    label: item.name + ' · ' + item.building
                        + (item.age_group_label ? ' · ' + item.age_group_label : '')
                        + (item.patient_eligible === false ? ' · недоступно' : ''),
                    disabled: item.patient_eligible === false,
                    restriction: item.patient_restriction,
                };
            });
            fillSelect(elements.department, choices, choices.length ? 'Выберите отделение' : 'Нет доступных отделений');
            const previous = departments.find(function (item) { return String(item.id) === selectedDepartmentId; });
            if (previous && previous.patient_eligible !== false) {
                elements.department.value = String(previous.id);
                if ($?.fn?.select2) $(elements.department).trigger('change.select2');
                updateRooms();
            }
            updateDepartmentHelp();
            if (rooms.every(function (room) { return !room.beds.length; })) {
                setError('Свободных коек сейчас нет. Добавьте койку в настройках либо освободите занятую.');
            }
        } catch (error) {
            if (!controller.signal.aborted && optionsController === controller) throw error;
        } finally {
            if (optionsController === controller) {
                loadingOptions = false;
                updateSubmit();
            }
        }
    }

    modal.addEventListener('show.bs.modal', function () {
        form.reset();
        clearError();
        clearPatient();
        requesting = false;
        rooms = [];
        clearRoomScheme();
        loadOptions().catch(function (error) {
            if (error.name !== 'AbortError') setError(error.message || 'Ошибка загрузки палат.');
        });
    });
    modal.addEventListener('shown.bs.modal', function () { elements.search.focus(); });
    modal.addEventListener('hidden.bs.modal', function () {
        window.clearTimeout(searchTimer);
        searchController?.abort();
        optionsController?.abort();
        schemeController?.abort();
        resetResults();
        clearRoomScheme();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (elements.submit.disabled) return;
        requesting = true;
        updateSubmit();
        clearError();
        elements.submit.textContent = 'Оформляем…';
        try {
            const response = await fetch(endpoints.store, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    patient_id: Number(elements.patientId.value),
                    department_id: Number(elements.department.value),
                    room_id: Number(elements.room.value),
                    bed_id: Number(elements.bed.value),
                    comment: elements.comment.value.trim(),
                }),
            });
            const data = await response.json().catch(function () { return {}; });
            if (!response.ok) {
                const detail = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(detail || data.message || 'Госпитализация не выполнена.');
            }
            // Сохранили пациента: обновляем текущую таблицу, не переходим
            // на отдельную страницу оформления госпитализации.
            window.location.reload();
        } catch (error) {
            setError(error.message || 'Не удалось оформить пациента.');
            requesting = false;
            elements.submit.innerHTML = '<i class="bi bi-check-circle"></i> Оформить и разместить';
            updateSubmit();
            // Если койку заняли параллельно, перечитаем свободные места.
            loadOptions().catch(function () {});
        }
    });

    const pageUrl = new URL(window.location.href);
    if (pageUrl.searchParams.get('admit') === '1') {
        window.bootstrap?.Modal.getOrCreateInstance(modal).show();
        pageUrl.searchParams.delete('admit');
        window.history.replaceState({}, '', pageUrl.pathname + pageUrl.search + pageUrl.hash);
    }
});
</script>
