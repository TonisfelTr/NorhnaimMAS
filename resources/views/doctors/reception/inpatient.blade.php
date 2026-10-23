@extends('doctors.reception')

@section('title', 'Госпитализация')

@section('assets')
    @parent
    <style>
        .adm-page { min-height: calc(100vh - 64px); padding: 28px 24px 60px; background: #f4f7fb; }
        .adm-container { max-width: 1500px; margin: 0 auto; }
        .adm-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 18px; }
        .adm-eyebrow { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: #e6f8fa; color: #087b89; font-size: 11px; font-weight: 850; }
        .adm-title { margin: 8px 0 5px; font-size: clamp(23px, 2.4vw, 32px); font-weight: 880; letter-spacing: -.03em; color: #1d2c40; }
        .adm-description { max-width: 870px; font-size: 13px; line-height: 1.6; color: #718196; margin: 0; }
        .adm-primary { border: 1px solid #078f9f; background: #078f9f; color: #fff; border-radius: 12px; min-height: 42px; padding: 10px 16px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 9px; cursor: pointer; text-decoration: none; }
        .adm-primary:hover { background: #067382; color: #fff; }
        .adm-primary:disabled { opacity: .5; cursor: wait; }
        .adm-secondary { border: 1px solid #d6e2e9; background: #fff; color: #415167; border-radius: 10px; min-height: 40px; padding: 8px 12px; font-size: 12px; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 7px; }
        .adm-secondary:hover { border-color: #8acbd2; color: #046f7b; }
        .adm-stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; margin-bottom: 16px; }
        .adm-stat { display: flex; align-items: center; gap: 12px; padding: 16px 18px; border: 1px solid #dfe8ee; border-radius: 17px; background: #fff; box-shadow: 0 8px 25px rgba(15, 23, 42, .025); }
        .adm-stat__icon { display: grid; place-items: center; height: 44px; width: 44px; flex: 0 0 44px; border-radius: 13px; background: #e8f8fa; color: #067c8a; font-size: 19px; }
        .adm-stat__value { font-size: 23px; font-weight: 900; line-height: 1.1; color: #1d2c40; }
        .adm-stat__label { margin-top: 3px; font-size: 11px; font-weight: 750; color: #8290a0; }
        .adm-card { border: 1px solid #dfe8ee; border-radius: 18px; background: #fff; box-shadow: 0 10px 28px rgba(15,23,42,.03); overflow: hidden; }
        .adm-card__head { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; padding: 16px 18px; border-bottom: 1px solid #eef1f5; }
        .adm-card__title { margin: 0; font-size: 16px; font-weight: 850; color: #213248; }
        .adm-card__subtitle { margin: 4px 0 0; font-size: 11px; color: #8390a0; }
        .adm-filters { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 10px; padding: 14px 18px; background: #fbfcfd; border-bottom: 1px solid #edf1f4; }
        .adm-filter { flex: 1 1 230px; min-width: 0; }
        .adm-label { display: block; margin: 0 0 6px; font-size: 11px; font-weight: 800; color: #53667c; }
        .adm-control { display: block; width: 100%; min-height: 41px; padding: 8px 12px; border: 1px solid #d2dfe6; border-radius: 10px; background: white; font-size: 13px; color: #25364a; outline: none; }
        .adm-control:focus { border-color: #0b98a7; box-shadow: 0 0 0 3px rgba(11,152,167,.11); }
        .adm-table-wrap { overflow-x: auto; }
        .adm-table { width: 100%; min-width: 840px; border-collapse: collapse; }
        .adm-table th { background: #f8fafc; padding: 13px 16px; text-transform: uppercase; font-size: 10px; font-weight: 850; letter-spacing: .025em; color: #65758a; }
        .adm-table td { padding: 14px 16px; border-top: 1px solid #edf1f5; font-size: 12px; color: #47586c; vertical-align: middle; }
        .adm-table tr:hover td { background: #fafdfe; }
        .adm-table__name { font-size: 12px; font-weight: 850; color: #26374a; }
        .adm-table__secondary { margin-top: 3px; font-size: 10px; color: #8794a3; }
        .adm-pill { display: inline-flex; gap: 5px; align-items: center; padding: 5px 9px; border-radius: 999px; background: #e7f8f1; color: #12704f; font-size: 10px; font-weight: 800; white-space: nowrap; }
        .adm-transfer-history { margin-top: 8px; padding: 8px 10px; border-left: 3px solid #dc9c36; border-radius: 7px; background: #fff8ee; color: #805c2a; font-size: 10.5px; line-height: 1.55; }
        .adm-transfer-history strong { color: #6d4b1e; }
        .adm-empty { display: grid; gap: 8px; place-content: center; text-align: center; padding: 62px 24px; color: #8996a7; }
        .adm-empty i { font-size: 35px; color: #8ccbd4; }
        .adm-empty strong { color: #41536a; font-size: 15px; }
        .adm-pagination { padding: 13px 18px; border-top: 1px solid #edf1f5; }
        .adm-modal .modal-dialog { max-width: 720px; }
        .adm-modal .modal-content { border: 0; border-radius: 20px; overflow: visible; box-shadow: 0 25px 65px rgba(15,23,42,.2); }
        .adm-modal .modal-header { padding: 18px 22px; border-bottom: 1px solid #e5edf1; }
        .adm-modal .modal-body { padding: 22px; background: #f9fbfc; }
        .adm-modal .modal-footer { padding: 14px 22px; border-top: 1px solid #e5edf1; }
        .adm-modal .modal-title { font-weight: 850; color: #1d2c40; font-size: 20px; }
        .adm-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .adm-form-grid__full { grid-column: 1 / -1; }
        .adm-help { margin-top: 6px; font-size: 11px; line-height: 1.45; color: #8793a3; }
        .adm-patient-search { position: relative; }
        .adm-results { position: absolute; top: calc(100% + 5px); left: 0; right: 0; z-index: 1200; border: 1px solid #d5e3e9; background: #fff; border-radius: 12px; max-height: 265px; overflow-y: auto; box-shadow: 0 16px 40px rgba(15,23,42,.15); padding: 5px; }
        .adm-result { display: block; width: 100%; padding: 10px; text-align: left; border: 0; border-radius: 8px; background: #fff; cursor: pointer; }
        .adm-result:hover, .adm-result:focus { background: #eaf7f9; }
        .adm-result strong { display: block; font-size: 12px; color: #26384b; }
        .adm-result small { display: block; margin-top: 2px; color: #8290a2; font-size: 10px; }
        .adm-selected { display: none; margin-top: 9px; padding: 10px 12px; background: #e9f9f6; border: 1px solid #bde6db; border-radius: 10px; font-size: 12px; color: #135f52; }
        .adm-selected.is-visible { display: flex; justify-content: space-between; align-items: center; gap: 9px; }
        .adm-selected__clear { background: none; border: 0; color: #aa4a54; font-size: 12px; cursor: pointer; }
        .adm-error { display: none; padding: 12px 14px; margin-bottom: 14px; border: 1px solid #efc0ca; border-radius: 11px; background: #fff1f3; color: #9e3145; font-size: 12px; }
        .adm-error.is-visible { display: block; }
        .adm-note { margin: 0 0 14px; padding: 11px 13px; background: #eaf8fa; border-radius: 11px; font-size: 11px; line-height: 1.5; color: #35707b; }
        .adm-autoplace { display: flex; align-items: flex-start; gap: 11px; padding: 13px 15px; border: 1px solid #cce9ed; border-radius: 12px; background: #f0fbfc; color: #306574; }
        .adm-autoplace i { margin-top: 2px; font-size: 18px; color: #078e9e; }
        .adm-autoplace strong { display: block; color: #1e596d; font-size: 12px; margin-bottom: 3px; }
        .adm-autoplace p { margin: 0; font-size: 11px; line-height: 1.55; }
        .adm-autoplace.is-corridor { background: #fff8ea; border-color: #f4dfba; color: #886027; }
        .adm-autoplace.is-corridor strong, .adm-autoplace.is-corridor i { color: #9a6d22; }
        .adm-modal .select2-container .select2-selection--single { height: 41px; display: flex; align-items: center; border: 1px solid #d2dfe6; border-radius: 10px; }
        .adm-modal .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 39px; padding-left: 12px; color: #25364a; font-size: 13px; }
        .adm-modal .select2-container--default .select2-selection--single .select2-selection__arrow { height: 39px; }
        @media(max-width: 950px) { .adm-stats { grid-template-columns: 1fr; } }
        @media(max-width: 700px) { .adm-page { padding: 18px 12px 35px; } .adm-form-grid { grid-template-columns: 1fr; } .adm-form-grid__full { grid-column: 1; } .adm-head .adm-primary { width: 100%; } }
    </style>
@endsection

@section('sub-main')
<main class="adm-page">
  <div class="adm-container">
    <header class="adm-head">
      <div>
        <span class="adm-eyebrow"><i class="bi bi-hospital"></i> Госпитализация</span>
        <h1 class="adm-title">Госпитализированные пациенты</h1>
        <p class="adm-description">Текущие госпитализации, переводы и размещение пациентов. Для нового поступления открой «Рабочий стол → Пациенты стационара».</p>
      </div>
    </header>

    @if(request()->boolean('admitted'))
      <div class="alert {{ request()->boolean('without_bed') ? 'alert-warning' : 'alert-success' }} border-0 rounded-3" role="status">
        <i class="bi {{ request()->boolean('without_bed') ? 'bi-exclamation-circle' : 'bi-check-circle' }} me-2"></i>
        {{ request()->boolean('without_bed')
            ? 'Пациент госпитализирован в выбранное отделение без палаты и койки. После освобождения места потребуется разместить его вручную.'
            : 'Пациент госпитализирован. Свободная койка назначена автоматически.' }}
      </div>
    @endif

    <section class="adm-stats" aria-label="Сводка коечного фонда">
      <div class="adm-stat"><span class="adm-stat__icon"><i class="bi bi-people"></i></span><div><div class="adm-stat__value">{{ $admissionsCount }}</div><div class="adm-stat__label">госпитализировано сейчас</div></div></div>
      <div class="adm-stat"><span class="adm-stat__icon"><i class="bi bi-person-check"></i></span><div><div class="adm-stat__value">{{ $admittedTodayCount }}</div><div class="adm-stat__label">поступили сегодня и остаются</div></div></div>
      <div class="adm-stat"><span class="adm-stat__icon"><i class="bi bi-door-open"></i></span><div><div class="adm-stat__value">{{ $availableBeds }}</div><div class="adm-stat__label">свободных коек</div></div></div>
    </section>

    <section class="adm-card" aria-label="Текущие госпитализации">
      <div class="adm-card__head">
        <div><h2 class="adm-card__title">Пациенты в стационаре</h2><p class="adm-card__subtitle">Действующие размещения по палатам и койкам</p></div>
        <span class="adm-pill"><i class="bi bi-check-circle"></i> {{ $admissions->total() }} записей</span>
      </div>
      <form class="adm-filters" method="GET" action="{{ route('doctors.reception.index') }}">
        <div class="adm-filter">
          <label class="adm-label" for="admissionQ">Пациент</label>
          <input id="admissionQ" name="q" class="adm-control" type="search" value="{{ $search }}" placeholder="ФИО пациента">
        </div>
        <div class="adm-filter">
          <label class="adm-label" for="admissionDepartment">Отделение</label>
          <select id="admissionDepartment" name="department_id" class="adm-control">
            <option value="">Все отделения</option>
            @foreach($departments as $department)
              <option value="{{ $department->id }}" @selected((int) $selectedDepartmentId === (int) $department->id)>{{ $department->name }}</option>
            @endforeach
          </select>
        </div>
        <button class="adm-primary" type="submit"><i class="bi bi-search"></i> Найти</button>
        <a class="adm-secondary" href="{{ route('doctors.reception.index') }}">Сбросить</a>
      </form>

      @if($admissions->count())
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead><tr><th>Пациент</th><th>Отделение</th><th>Палата / койка</th><th>Дата поступления</th><th>Статус</th><th>Медкарта</th></tr></thead>
            <tbody>
              @foreach($admissions as $assignment)
                <tr>
                  <td>
                    <div class="adm-table__name">{{ $assignment->patient?->fullname ?? 'Пациент не найден' }}</div>
                    <div class="adm-table__secondary">ID: {{ $assignment->patient_id }}</div>
                    @if($assignment->bed_id === null && $canTransfer)
                      <form method="POST" action="{{ route('doctors.inpatient.discharges.corridor') }}"
                            class="mt-2" onsubmit="return confirm('Подтвердить выписку пациента без койки?');">
                        @csrf
                        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                        <input type="hidden" name="patient_id" value="{{ $assignment->patient_id }}">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Выписать</button>
                      </form>
                    @endif
                    @if($canTransfer)
                      <button type="button" class="btn btn-sm btn-outline-info mt-2" data-inpatient-transfer
                              data-assignment-id="{{ $assignment->id }}"
                              data-patient-id="{{ $assignment->patient_id }}"
                              data-patient-name="{{ $assignment->patient?->surname }} {{ $assignment->patient?->name }} {{ $assignment->patient?->patronym }}"
                              data-current-department="{{ $assignment->department_id }}"
                              data-current-bed="{{ $assignment->bed_id }}">
                          <i class="bi bi-arrow-left-right"></i> Перевести
                      </button>
                    @endif
                  </td>
                  <td>
                    <div class="adm-table__name">{{ $assignment->department?->name ?? '—' }}</div>
                    <div class="adm-table__secondary">{{ $assignment->department?->building?->name ?? '' }}</div>
                    @if($assignment->latestDepartmentTransfer)
                      <div class="adm-transfer-history">
                        <strong>Переведён из {{ $assignment->latestDepartmentTransfer->from_department_name }}</strong>
                        <div>В {{ $assignment->latestDepartmentTransfer->to_department_name }}</div>
                        <div>{{ $assignment->latestDepartmentTransfer->transferred_at?->format('d.m.Y H:i:s') ?? '—' }} · Перевёл: {{ $assignment->latestDepartmentTransfer->transferred_by_name }}</div>
                      </div>
                    @endif
                  </td>
                  <td>
                    @if($assignment->bed_id === null)
                        <span style="color:#a65c15;font-weight:800;"><i class="bi bi-exclamation-triangle"></i> Без палаты и койки</span>
                        <div class="adm-table__secondary">Ожидает свободное место в отделении</div>
                    @else
                        <div class="adm-table__name">Палата №{{ $assignment->bed?->room?->number ?? '—' }}</div>
                        <div class="adm-table__secondary">Койка №{{ $assignment->bed?->number ?? '—' }}</div>
                    @endif
                  </td>
                  <td>{{ $assignment->assigned_at?->format('d.m.Y H:i') ?? '—' }}</td>
                  <td><span class="adm-pill"><i class="bi bi-check-circle-fill"></i> В стационаре</span></td>
                  <td>
                    @if($assignment->patient)
                      <a class="adm-secondary" href="{{ route('doctors.patients.medical_card', ['patient' => $assignment->patient->id]) }}" title="Открыть медицинскую карту"><i class="bi bi-journal-medical"></i> Открыть</a>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if($admissions->hasPages())
          <div class="adm-pagination">{{ $admissions->links() }}</div>
        @endif
      @else
        <div class="adm-empty"><i class="bi bi-hospital"></i><strong>Пациентов с таким фильтром нет</strong><span>Выберите другое отделение или оформите поступление.</span></div>
      @endif
    </section>
  </div>
</main>

<div class="modal fade adm-modal" id="inpatientTransferModal" tabindex="-1" aria-labelledby="inpatientTransferTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="inpatientTransferForm">
      <div class="modal-header">
        <h2 class="modal-title" id="inpatientTransferTitle">Перевод в другое отделение</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <p id="inpatientTransferPatient" class="fw-bold mb-3"></p>
        <div id="inpatientTransferError" class="alert alert-danger d-none" role="alert"></div>
        <label class="adm-label" for="inpatientTransferDepartment">Новое отделение</label>
        <select required id="inpatientTransferDepartment" class="adm-control">
          <option value="">Выберите отделение</option>
          @foreach($departments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
          @endforeach
        </select>
        <div id="inpatientTransferCapacityNotice" class="adm-autoplace mt-3 d-none"
             role="status" aria-live="polite" aria-atomic="true">
          <i id="inpatientTransferCapacityIcon" class="bi bi-info-circle" aria-hidden="true"></i>
          <div>
            <strong id="inpatientTransferCapacityTitle">Проверяем наличие мест</strong>
            <p id="inpatientTransferCapacityText">Уточняем свободные койки в выбранном отделении.</p>
          </div>
        </div>
        <p class="adm-help mt-2">При переводе койка назначается автоматически: сначала в надзорной палате, затем в другой свободной. Отсутствие коек <strong>не запрещает перевод</strong>. Прежний лечащий врач будет снят.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="adm-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="submit" class="adm-primary" id="inpatientTransferSubmit">Перевести</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade adm-modal" id="inpatientAdmitModal" tabindex="-1" aria-labelledby="inpatientAdmitTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="inpatientAdmitForm" autocomplete="off">
      <div class="modal-header">
        <div><div class="adm-eyebrow mb-2"><i class="bi bi-hospital"></i> Поступление в стационар</div><h2 class="modal-title" id="inpatientAdmitTitle">Оформление пациента</h2></div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <div class="adm-error" id="admFormError" role="alert"></div>
        <p class="adm-note">Выбери пациента и отделение. При сохранении система сама подберёт место: сначала свободную койку в надзорной палате, затем в другой палате. Если всё занято — оформит госпитализацию без палаты и койки.</p>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contextClinicId = @json($contextClinicId);
    const transferModalEl = document.getElementById('inpatientTransferModal');
    const transferForm = document.getElementById('inpatientTransferForm');
    if (!transferModalEl || !transferForm) return;
    const department = document.getElementById('inpatientTransferDepartment');
    const error = document.getElementById('inpatientTransferError');
    const submit = document.getElementById('inpatientTransferSubmit');
    const capacityNotice = document.getElementById('inpatientTransferCapacityNotice');
    const capacityIcon = document.getElementById('inpatientTransferCapacityIcon');
    const capacityTitle = document.getElementById('inpatientTransferCapacityTitle');
    const capacityText = document.getElementById('inpatientTransferCapacityText');
    const optionsUrl = @json(route('doctors.inpatient.admissions.options'));
    let record = null;
    let capacityRequest = null;

    const hideCapacityNotice = () => {
        capacityNotice.classList.add('d-none');
    };

    const showCapacityNotice = (title, message, overflow = false, icon = 'bi-info-circle') => {
        capacityTitle.textContent = title;
        capacityText.textContent = message;
        capacityNotice.classList.toggle('is-corridor', overflow);
        capacityIcon.className = `bi ${icon}`;
        capacityNotice.classList.remove('d-none');
    };

    // Только информируем сотрудника; отсутствие койки не отключает «Перевести».
    // Окончательный выбор места всё равно выполняется сервером в транзакции.
    const checkTransferCapacity = async () => {
        capacityRequest?.abort();
        capacityRequest = null;
        if (!record || !department.value || department.value === record.current_department_id) {
            hideCapacityNotice();
            return;
        }

        const controller = new AbortController();
        capacityRequest = controller;
        showCapacityNotice('Проверяем наличие мест', 'Уточняем свободные койки в отделении.');

        const url = new URL(optionsUrl, window.location.origin);
        url.searchParams.set('patient_id', String(record.patient_id));
        if (contextClinicId !== null && contextClinicId !== undefined) {
            url.searchParams.set('clinic_id', String(contextClinicId));
        }

        try {
            const response = await fetch(url.toString(), {
                method: 'GET', credentials: 'same-origin',
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('Не удалось проверить свободные места.');
            const data = await response.json();
            if (controller !== capacityRequest) return;
            const selected = (Array.isArray(data.departments) ? data.departments : [])
                .find(item => String(item.id) === department.value);

            if (!selected) {
                showCapacityNotice('Данные отделения изменились',
                    'Проверь выбранное отделение перед переводом.', false, 'bi-info-circle');
            } else if (selected.patient_eligible === false) {
                // Переполнение НЕ ограничение. А вот возраст/пол по-прежнему проверяются.
                showCapacityNotice('Проверь ограничения отделения',
                    selected.patient_restriction || 'Пациент не соответствует правилам этого отделения.',
                    true, 'bi-exclamation-triangle');
            } else if (selected.has_free_beds === false) {
                showCapacityNotice('Отделение переполнено',
                    'Свободных коек нет. Перевод разрешён: пациент будет числиться в новом отделении без палаты и койки (ожидает размещения).',
                    true, 'bi-exclamation-triangle');
            } else {
                hideCapacityNotice();
            }
        } catch (exception) {
            if (controller.signal.aborted || controller !== capacityRequest) return;
            showCapacityNotice('Не удалось проверить наличие мест',
                'Перевод не блокируется из-за недоступности сведений о койках. При сохранении сервер проверит места и ограничения пациента.',
                false, 'bi-info-circle');
        } finally {
            if (controller === capacityRequest) capacityRequest = null;
        }
    };

    department.addEventListener('change', checkTransferCapacity);
    transferModalEl.addEventListener('hidden.bs.modal', () => {
        capacityRequest?.abort();
        capacityRequest = null;
        hideCapacityNotice();
    });
    document.querySelectorAll('[data-inpatient-transfer]').forEach(button => {
        button.addEventListener('click', () => {
            record = {
                patient_id: Number(button.dataset.patientId),
                assignment_id: Number(button.dataset.assignmentId),
                current_bed_id: button.dataset.currentBed ? Number(button.dataset.currentBed) : null,
                current_department_id: String(button.dataset.currentDepartment),
            };
            department.value = '';
            Array.from(department.options).forEach(o => {
                o.disabled = Boolean(o.value && o.value === record.current_department_id);
            });
            capacityRequest?.abort();
            capacityRequest = null;
            hideCapacityNotice();
            error.classList.add('d-none');
            document.getElementById('inpatientTransferPatient').textContent = button.dataset.patientName || '';
            window.bootstrap?.Modal.getOrCreateInstance(transferModalEl).show();
        });
    });
    transferForm.addEventListener('submit', async e => {
        e.preventDefault();
        if (!record || !department.value || department.value === record.current_department_id) return;
        submit.disabled = true;
        error.classList.add('d-none');
        try {
            const response = await fetch(@json(route('doctors.inpatient.admissions.store')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json', 'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    intent: 'edit_placement',
                    clinic_id: contextClinicId,
                    patient_id: record.patient_id,
                    assignment_id: record.assignment_id,
                    current_bed_id: record.current_bed_id,
                    department_id: Number(department.value),
                }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Не удалось перевести пациента.');
            window.location.reload();
        } catch (err) {
            error.textContent = err.message || 'Ошибка перевода.';
            error.classList.remove('d-none');
            submit.disabled = false;
        }
    });
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contextClinicId = @json($contextClinicId);
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
        index: @json($isDebugAdministrator
            ? route('doctors.inpatient.admissions.index')
            : route('doctors.reception.index')),
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

    const selectFields = [elements.department];
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
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
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
        clearError();
        clearPatient();
        requesting = false;
        loadOptions().catch(function (error) {
            if (error.name !== 'AbortError') setError(error.message || 'Ошибка загрузки палат.');
        });
    });
    modal.addEventListener('shown.bs.modal', function () { elements.search.focus(); });
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
            const url = new URL(endpoints.index, window.location.origin);
            if (contextClinicId) url.searchParams.set('clinic_id', String(contextClinicId));
            url.searchParams.set('admitted', '1');
            if (data.without_bed === true) url.searchParams.set('without_bed', '1');
            window.location.assign(url);
        } catch (error) {
            setError(error.message || 'Не удалось оформить пациента.');
            requesting = false;
            elements.submit.innerHTML = '<i class="bi bi-check-circle"></i> Госпитализировать';
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
@endpush
