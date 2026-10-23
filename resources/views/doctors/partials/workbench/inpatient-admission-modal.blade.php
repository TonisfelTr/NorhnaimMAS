{{-- Рабочий стол. Та же форма и API госпитализации, но без перехода на отдельную страницу. --}}
<style>
    .nh-inline-admission .adm-eyebrow { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: #e6f8fa; color: #087b89; font-size: 11px; font-weight: 850; }
    .nh-inline-admission .adm-primary { border: 1px solid #078f9f; background: #078f9f; color: #fff; border-radius: 12px; min-height: 42px; padding: 10px 16px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 9px; cursor: pointer; text-decoration: none; }
    .nh-inline-admission .adm-primary:hover { background: #067382; color: #fff; }
    .nh-inline-admission .adm-primary:disabled { opacity: .5; cursor: wait; }
    .nh-inline-admission .adm-secondary { border: 1px solid #d6e2e9; background: #fff; color: #415167; border-radius: 10px; min-height: 40px; padding: 8px 12px; font-size: 12px; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 7px; }
    .nh-inline-admission .adm-secondary:hover { border-color: #8acbd2; color: #046f7b; }
    .nh-inline-admission .adm-label { display: block; margin: 0 0 6px; font-size: 11px; font-weight: 800; color: #53667c; }
    .nh-inline-admission .adm-control { display: block; width: 100%; min-height: 41px; padding: 8px 12px; border: 1px solid #d2dfe6; border-radius: 10px; background: white; font-size: 13px; color: #25364a; outline: none; }
    .nh-inline-admission .adm-control:focus { border-color: #0b98a7; box-shadow: 0 0 0 3px rgba(11,152,167,.11); }
    .nh-inline-admission .modal-dialog { max-width: 720px; }
    .nh-inline-admission .modal-content { border: 0; border-radius: 20px; overflow: visible; box-shadow: 0 25px 65px rgba(15,23,42,.2); }
    .nh-inline-admission .modal-header { padding: 18px 22px; border-bottom: 1px solid #e5edf1; }
    .nh-inline-admission .modal-body { padding: 22px; background: #f9fbfc; }
    .nh-inline-admission .modal-footer { padding: 14px 22px; border-top: 1px solid #e5edf1; }
    .nh-inline-admission .modal-title { font-weight: 850; color: #1d2c40; font-size: 20px; }
    .nh-inline-admission .adm-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .nh-inline-admission .adm-form-grid__full { grid-column: 1 / -1; }
    .nh-inline-admission .adm-help { margin-top: 6px; font-size: 11px; line-height: 1.45; color: #8793a3; }
    .nh-inline-admission .adm-patient-search { position: relative; }
    .nh-inline-admission .adm-results { position: absolute; top: calc(100% + 5px); left: 0; right: 0; z-index: 1200; border: 1px solid #d5e3e9; background: #fff; border-radius: 12px; max-height: 265px; overflow-y: auto; box-shadow: 0 16px 40px rgba(15,23,42,.15); padding: 5px; }
    .nh-inline-admission .adm-result { display: block; width: 100%; padding: 10px; text-align: left; border: 0; border-radius: 8px; background: #fff; cursor: pointer; }
    .nh-inline-admission .adm-result:hover, .nh-inline-admission .adm-result:focus { background: #eaf7f9; }
    .nh-inline-admission .adm-result strong { display: block; font-size: 12px; color: #26384b; }
    .nh-inline-admission .adm-result small { display: block; margin-top: 2px; color: #8290a2; font-size: 10px; }
    .nh-inline-admission .adm-selected { display: none; margin-top: 9px; padding: 10px 12px; background: #e9f9f6; border: 1px solid #bde6db; border-radius: 10px; font-size: 12px; color: #135f52; }
    .nh-inline-admission .adm-selected.is-visible { display: flex; justify-content: space-between; align-items: center; gap: 9px; }
    .nh-inline-admission .adm-selected__clear { background: none; border: 0; color: #aa4a54; font-size: 12px; cursor: pointer; }
    .nh-inline-admission .adm-error { display: none; padding: 12px 14px; margin-bottom: 14px; border: 1px solid #efc0ca; border-radius: 11px; background: #fff1f3; color: #9e3145; font-size: 12px; }
    .nh-inline-admission .adm-error.is-visible { display: block; }
    .nh-inline-admission .adm-note { margin: 0 0 14px; padding: 11px 13px; background: #eaf8fa; border-radius: 11px; font-size: 11px; line-height: 1.5; color: #35707b; }
    .nh-inline-admission .adm-autoplace { display: flex; align-items: flex-start; gap: 11px; padding: 13px 15px; border: 1px solid #cce9ed; border-radius: 12px; background: #f0fbfc; color: #306574; }
    .nh-inline-admission .adm-autoplace i { margin-top: 2px; font-size: 18px; color: #078e9e; }
    .nh-inline-admission .adm-autoplace strong { display: block; color: #1e596d; font-size: 12px; margin-bottom: 3px; }
    .nh-inline-admission .adm-autoplace p { margin: 0; font-size: 11px; line-height: 1.55; }
    .nh-inline-admission .adm-autoplace.is-corridor { background: #fff8ea; border-color: #f4dfba; color: #886027; }
    .nh-inline-admission .adm-autoplace.is-corridor strong, .nh-inline-admission .adm-autoplace.is-corridor i { color: #9a6d22; }
    .nh-inline-admission .select2-container .select2-selection--single { height: 41px; display: flex; align-items: center; border: 1px solid #d2dfe6; border-radius: 10px; }
    .nh-inline-admission .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 39px; padding-left: 12px; color: #25364a; font-size: 13px; }
    .nh-inline-admission .select2-container--default .select2-selection--single .select2-selection__arrow { height: 39px; }
    @media (max-width: 700px) { .nh-inline-admission .adm-form-grid { grid-template-columns: 1fr; } .nh-inline-admission .adm-form-grid__full { grid-column: 1; } }
    .nh-inline-admission .modal-dialog { width: min(720px, calc(100vw - 24px)); margin-left: auto; margin-right: auto; }
    .nh-inline-admission .adm-results { z-index: 1200; }
    .nh-inline-admission .modal-content { overflow: visible; }
    /* Визуальное оформление только этой формы, Bootstrap и остальной UI не меняем. */
    .nh-inline-admission .modal-dialog { max-width: 690px; }
    .nh-inline-admission .modal-content { border: 0; border-radius: 24px; box-shadow: 0 35px 90px rgba(10,47,68,.30); }
    .nh-inline-admission .modal-header.nh-admission__header {
        display: flex; align-items: flex-start; gap: 15px; padding: 24px 28px;
        border: 0; border-radius: 24px 24px 0 0;
        background: linear-gradient(115deg, #04798b, #0bb7bf); color: #fff;
    }
    .nh-inline-admission .modal-title { color: #fff; font-size: 23px; font-weight: 850; letter-spacing: -.025em; }
    .nh-inline-admission .nh-admission__kicker {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px;
        margin-bottom: 9px; border: 1px solid rgba(255,255,255,.28); border-radius: 999px;
        background: rgba(255,255,255,.14); color: #fff; font-size: 11px; font-weight: 800;
    }
    .nh-inline-admission .nh-admission__headline-subtitle { margin: 7px 0 0; color: #ddf8fa; font-size: 12.5px; }
    .nh-inline-admission .nh-admission__close { margin: 2px 0 0 auto; filter: brightness(0) invert(1); opacity: .94; }
    .nh-inline-admission .modal-body { padding: 25px 28px; background: #f6fafb; }
    .nh-inline-admission .modal-footer { background: #fff; border-top: 1px solid #e4eef1; padding: 17px 28px; border-radius: 0 0 24px 24px; }
    .nh-inline-admission .adm-note { border: 1px solid #caebee; border-radius: 12px; background: #effafb; padding: 13px 15px; color: #48717e; font-size: 12px; margin: 0 0 18px; }
    .nh-inline-admission .nh-admission__institution {
        display: flex; align-items: flex-start; gap: 14px; padding: 16px 17px;
        background: #fff; border: 1px solid #d9eaed; border-radius: 15px;
        box-shadow: 0 6px 16px rgba(20,72,90,.035); margin-bottom: 18px;
    }
    .nh-inline-admission .nh-admission__institution-icon { width: 43px; height: 43px; display: grid; place-items: center;
        flex: 0 0 43px; border-radius: 13px; background: #e6f8f8; color: #078e9f; font-size: 20px; }
    .nh-inline-admission .nh-admission__institution-copy { flex: 1 1 0; min-width: 0; }
    .nh-inline-admission .nh-admission__institution-label { color: #79909e; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .045em; margin-bottom: 5px; }
    .nh-inline-admission .nh-admission__institution-name { font-size: 15px; font-weight: 850; color: #1e485d; }
    .nh-inline-admission .nh-admission__institution-parent { margin-top: 4px; font-size: 12px; color: #688491; }
    .nh-inline-admission .nh-admission__institution .form-select { margin-top: 4px; min-height: 41px; color: #214858; }
    .nh-inline-admission .nh-admission__section-label { display: flex; align-items: center; gap: 8px; margin: 16px 0 12px; color: #1d4a5e; font-size: 14px; font-weight: 850; }
    .nh-inline-admission .nh-admission__section-number { display: grid; place-items: center; width: 25px; height: 25px; border-radius: 8px; background: #e3f6f7; color: #078795; font-size: 12px; }
    .nh-inline-admission .adm-form-grid__full { min-width: 0; }
    .nh-inline-admission .adm-control { border-radius: 11px; min-height: 44px; font-size: 13px; }
    .nh-inline-admission .adm-label { color: #456678; font-size: 12px; margin-bottom: 7px; }
    .nh-inline-admission .adm-autoplace { margin-top: 2px; border-radius: 13px; }
    .nh-inline-admission .adm-primary { font-size: 13px; border-radius: 11px; min-height: 44px; background: #079ba9; }
    .nh-inline-admission .adm-secondary { min-height: 44px; border-radius: 11px; }
    @media(max-width: 600px) {
        .nh-inline-admission .modal-dialog { width: calc(100vw - 16px); margin: 8px auto; }
        .nh-inline-admission .modal-header.nh-admission__header { padding: 19px; }
        .nh-inline-admission .modal-body { padding: 17px; }
        .nh-inline-admission .modal-footer { padding: 13px 17px; }
        .nh-inline-admission .nh-admission__institution { padding: 13px; }
        .nh-inline-admission .modal-title { font-size: 20px; }
    }
</style>

<div class="modal fade adm-modal nh-inline-admission" id="inpatientAdmitModal"
     data-clinic-id="{{ ($isAdmin && $quickActionInpatientClinics->count() === 1) ? $quickActionInpatientClinics->first()->id : '' }}" tabindex="-1" aria-labelledby="inpatientAdmitTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="inpatientAdmitForm" autocomplete="off">
      <div class="modal-header nh-admission__header">
        <div>
          <span class="nh-admission__kicker"><i class="bi bi-hospital"></i> Стационар · Оформление</span>
          <h2 class="modal-title" id="inpatientAdmitTitle">Новая госпитализация</h2>
          <p class="nh-admission__headline-subtitle">Пациент и отделение — место назначится автоматически</p>
        </div>
        <button type="button" class="btn-close nh-admission__close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <div class="adm-error" id="admFormError" role="alert"></div>
        <div class="nh-admission__institution">
          <span class="nh-admission__institution-icon" aria-hidden="true"><i class="bi bi-buildings"></i></span>
          <div class="nh-admission__institution-copy">
            <div class="nh-admission__institution-label">Госпитализация в учреждение</div>
            @if($isAdmin && $quickActionInpatientClinics->count() > 1)
              <label for="workbenchAdmissionClinic" class="visually-hidden">Стационар</label>
              <select id="workbenchAdmissionClinic" class="form-select" required>
                <option value="">Выбери стационар</option>
                @foreach($quickActionInpatientClinics as $hospital)
                  <option value="{{ $hospital->id }}" data-parent="{{ $hospital->parent_name ?? '' }}">{{ $hospital->name }}</option>
                @endforeach
              </select>
              <div class="nh-admission__institution-parent" id="admHospitalParent">Сначала выбери стационар</div>
            @elseif($isAdmin)
              <div class="nh-admission__institution-name">{{ $quickActionInpatientClinics->first()?->name ?? 'Стационар' }}</div>
              <div class="nh-admission__institution-parent">{{ $quickActionInpatientClinics->first()?->parent_name ? 'Клиника / диспансер: ' . $quickActionInpatientClinics->first()->parent_name : 'Головная организация не назначена' }}</div>
            @else
              <div class="nh-admission__institution-name">{{ $workbenchClinicName ?? 'Учреждение' }}</div>
              <div class="nh-admission__institution-parent">{{ $workbenchParentName ? 'Клиника / диспансер: ' . $workbenchParentName : 'Головная организация не назначена' }}</div>
            @endif
            @if($isAdmin)
              <a href="{{ route('doctors.settings.clinic-affiliations.index') }}" class="small d-inline-block mt-2">Настроить принадлежность стационаров</a>
            @endif
          </div>
        </div>
        <p class="adm-note"><i class="bi bi-info-circle me-1" aria-hidden="true"></i> Койка выбирается системой: сначала в надзорной палате, затем среди остальных. Если мест нет, пациент остаётся в отделении без палаты и койки.</p>
        <div class="nh-admission__section-label"><span class="nh-admission__section-number">1</span> Пациент и отделение</div>
        <input type="hidden" name="patient_id" id="admPatientId">
        <div class="adm-form-grid">
          <div class="adm-form-grid__full">
            <label for="admPatientSearch" class="adm-label">Пациент <span class="text-danger">*</span></label>
            <div class="adm-patient-search">
              <input id="admPatientSearch" type="search" class="adm-control" placeholder="Начните вводить ФИО пациента (от 2 символов)" aria-autocomplete="list" aria-controls="admPatientResults">
              <div class="adm-results d-none" id="admPatientResults" role="listbox"></div>
            </div>
            <div class="adm-selected" id="admPatientSelected"><span id="admPatientSelectedText"></span><button type="button" class="adm-selected__clear" id="admPatientClear">Сменить</button></div>
            <div class="adm-help">Уже госпитализированные пациенты исключаются из поиска, включая ожидающих свободную койку.</div>
          </div>
          <div class="adm-form-grid__full">
            <label for="admDepartmentSelect" class="adm-label">Отделение <span class="text-danger">*</span></label>
            <select id="admDepartmentSelect" name="department_id" class="adm-control" aria-describedby="admDepartmentHelp" required disabled><option value="">Загружаем отделения…</option></select>
            <div class="adm-help" id="admDepartmentHelp" aria-live="polite">Детское отделение — 0–7 лет, подростковое — 8–14 лет. Для оформления в возрастное отделение нужна корректная дата рождения.</div>
          </div>
          <div class="adm-form-grid__full">
            <div class="adm-autoplace" id="admAutoPlacement" role="status" aria-live="polite">
              <i class="bi bi-shuffle" aria-hidden="true"></i>
              <div>
                <strong id="admAutoPlacementTitle">Место определяется автоматически</strong>
                <p id="admAutoPlacementText">Выбери отделение — после сохранения система проверит наличие свободных коек.</p>
              </div>
            </div>
          </div>
          <div class="adm-form-grid__full">
            <div class="nh-admission__section-label"><span class="nh-admission__section-number">2</span> Дополнительно</div>
            <label for="admComment" class="adm-label">Комментарий при оформлении</label>
            <textarea id="admComment" name="comment" class="adm-control" rows="2" maxlength="1000" placeholder="Необязательно"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="adm-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="submit" id="admSubmit" class="adm-primary" disabled><i class="bi bi-check-circle"></i> Госпитализировать</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let contextClinicId = null;
    const clinicSelect = document.getElementById('workbenchAdmissionClinic');
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
        placement: document.getElementById('admAutoPlacement'),
        placementTitle: document.getElementById('admAutoPlacementTitle'),
        placementText: document.getElementById('admAutoPlacementText'),
        submit: document.getElementById('admSubmit'),
        error: document.getElementById('admFormError'),
        comment: document.getElementById('admComment'),
    };
    const endpoints = {
        options: @json(route('doctors.inpatient.admissions.options')),
        patients: @json(route('doctors.inpatient.admissions.patients.search')),
        store: @json(route('doctors.inpatient.admissions.store')),
    };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let departments = [];
    let selectedPatient = null;
    let searchTimer = null;
    let searchController = null;
    let optionsController = null;
    let loadingOptions = false;
    let requesting = false;
    const departmentHelp = elements.departmentHelp.textContent;
    const hospitalParent = document.getElementById('admHospitalParent');

    function updateHospitalLabel() {
        if (!hospitalParent || !clinicSelect) return;
        const option = clinicSelect.selectedOptions[0];
        if (!option || !option.value) {
            hospitalParent.textContent = 'Выбери стационар для оформления';
            return;
        }
        hospitalParent.textContent = option.dataset.parent
            ? 'Клиника / диспансер: ' + option.dataset.parent
            : 'Головная организация не назначена';
    }

    const selectFields = [elements.department];
    if (clinicSelect) selectFields.push(clinicSelect);
    if ($?.fn?.select2) {
        selectFields.forEach(function (field) {
            $(field).select2({ width: '100%', dropdownParent: $(modal), minimumResultsForSearch: 0, language: { noResults: function () { return 'Нет вариантов'; } } });
        });
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
    function updateSubmit() {
        const department = departments.find(function (item) { return String(item.id) === elements.department.value; });
        elements.submit.disabled = requesting || loadingOptions || !selectedPatient
            || department?.patient_eligible !== true;
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
            if (contextClinicId) url.searchParams.set('clinic_id', String(contextClinicId));
            if (departmentId) url.searchParams.set('department_id', departmentId);
            const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
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

    function updatePlacementPreview() {
        const department = departments.find(function (item) { return String(item.id) === elements.department.value; });
        const corridor = department?.patient_eligible !== false && department?.has_free_beds === false;
        elements.placement.classList.toggle('is-corridor', corridor);
        if (!department || department.patient_eligible === false) {
            elements.placementTitle.textContent = 'Место определяется автоматически';
            elements.placementText.textContent = 'Выбери доступное отделение — место будет проверено при сохранении.';
        } else if (corridor) {
            elements.placementTitle.textContent = 'Свободных коек сейчас нет';
            elements.placementText.textContent = 'Пациент всё равно будет госпитализирован в выбранное отделение. Палата и койка останутся пустыми, пока не появится место.';
        } else {
            elements.placementTitle.textContent = 'Автоматическое размещение';
            elements.placementText.textContent = 'При оформлении выберем свободную койку: сначала в надзорной палате, затем в обычной. Доступность мест проверим повторно перед сохранением.';
        }
    }
    function updateDepartmentHelp() {
        const department = departments.find(function (item) { return String(item.id) === elements.department.value; });
        if (department?.patient_eligible === false) {
            elements.departmentHelp.textContent = department.patient_restriction || 'Это отделение недоступно для выбранного пациента.';
        } else if (department?.age_group_label) {
            elements.departmentHelp.textContent = department.age_group_label + '. Палата и койка назначаются автоматически.';
        } else {
            const allUnavailable = departments.length > 0 && departments.every(function (item) { return item.patient_eligible === false; });
            const restriction = allUnavailable
                ? departments.find(function (item) { return item.patient_restriction; })
                : null;
            elements.departmentHelp.textContent = restriction?.patient_restriction || departmentHelp;
        }
        updatePlacementPreview();
    }
    elements.department.addEventListener('change', function () {
        updateDepartmentHelp();
        updateSubmit();
        window.clearTimeout(searchTimer);
        searchController?.abort();
        resetResults();
        const query = elements.search.value.trim();
        if (!selectedPatient && query.length >= 2) searchPatients(query);
    });

    async function loadOptions() {
        optionsController?.abort();
        const controller = new AbortController();
        optionsController = controller;
        const patientId = selectedPatient?.id ?? null;
        const selectedDepartmentId = elements.department.value;
        loadingOptions = true;
        departments = [];
        updateDepartmentHelp();
        fillSelect(elements.department, [], 'Загружаем отделения…');
        try {
            const url = new URL(endpoints.options, window.location.origin);
            if (patientId !== null) url.searchParams.set('patient_id', String(patientId));
            if (contextClinicId) url.searchParams.set('clinic_id', String(contextClinicId));
            const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
            const data = await response.json();
            if (controller.signal.aborted || optionsController !== controller
                || (selectedPatient?.id ?? null) !== patientId) return;
            if (!response.ok) {
                const detail = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(detail || data.message || 'Не удалось загрузить отделения.');
            }
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
            }
            updateDepartmentHelp();
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
        if (clinicSelect && $?.fn?.select2) $(clinicSelect).trigger('change.select2');
        contextClinicId = Number(clinicSelect?.value || modal.dataset.clinicId || 0) || null;
        clearError();
        clearPatient();
        requesting = false;
        updateHospitalLabel();
        if (clinicSelect && !contextClinicId) {
            // Модалка открывается даже без выбранной клиники, сначала даём выбрать её.
            fillSelect(elements.department, [], 'Сначала выбери стационар');
            elements.search.disabled = true;
            return;
        }
        loadOptions().catch(function (error) {
            if (error.name !== 'AbortError') setError(error.message || 'Ошибка загрузки отделений.');
        });
    });
    clinicSelect?.addEventListener('change', function () {
        contextClinicId = Number(clinicSelect.value || 0) || null;
        updateHospitalLabel();
        clearPatient();
        if (!contextClinicId) {
            elements.search.disabled = true;
            fillSelect(elements.department, [], 'Сначала выбери стационар');
            return;
        }
        elements.search.disabled = false;
        loadOptions().catch(function (error) {
            if (error.name !== 'AbortError') setError(error.message || 'Ошибка загрузки отделений.');
        });
    });
    modal.addEventListener('shown.bs.modal', function () {
        if (clinicSelect && !contextClinicId) {
            // Для админа с несколькими стационарами фокус на выбор учреждения.
            if ($?.fn?.select2) $(clinicSelect).select2('open');
            else clinicSelect.focus();
        } else elements.search.focus();
    });
    modal.addEventListener('hidden.bs.modal', function () {
        window.clearTimeout(searchTimer);
        searchController?.abort();
        optionsController?.abort();
        resetResults();
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
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    patient_id: Number(elements.patientId.value),
                    clinic_id: contextClinicId,
                    department_id: Number(elements.department.value),
                    comment: elements.comment.value.trim(),
                }),
            });
            const data = await response.json().catch(function () { return {}; });
            if (!response.ok) {
                const detail = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(detail || data.message || 'Госпитализация не выполнена.');
            }
            // Остаёмся на той же вкладке рабочего стола, не переходим в «Стационар».
            // Перезагрузка нужна, чтобы показать обновлённые списки и показатели.
            window.bootstrap.Modal.getOrCreateInstance(modal).hide();
            const notice = document.createElement('div');
            notice.className = 'alert alert-success';
            notice.setAttribute('role', 'status');
            notice.textContent = data.message || (data.without_bed
                ? 'Пациент госпитализирован без койки — ожидает размещения.'
                : 'Пациент госпитализирован.');
            document.querySelector('.nh-workbench__container')?.prepend(notice);
            window.setTimeout(() => window.location.reload(), 1100);
        } catch (error) {
            setError(error.message || 'Не удалось оформить пациента.');
            requesting = false;
            elements.submit.innerHTML = '<i class="bi bi-check-circle"></i> Госпитализировать';
            updateSubmit();
            // Если койку заняли параллельно, перечитаем свободные места.
            loadOptions().catch(function () {});
        }
    });

});
</script>
