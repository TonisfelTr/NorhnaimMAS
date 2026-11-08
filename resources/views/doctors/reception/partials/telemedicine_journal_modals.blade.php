@if($telemedManager['can_create'])
<div class="modal fade mc-modal-ui mc-modal-ui--teal mc-tm-modal" id="modalCreateTelemedicineSession"
     tabindex="-1" aria-labelledby="modalCreateTelemedicineSessionTitle" aria-hidden="true"
     data-open-after-error="{{ $telemedManager['create_errors'] ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="{{ $telemedManager['create_url'] }}" data-telemed-form>
            @csrf
            <input type="hidden" name="telemed_request_id" value="{{ $telemedManager['request_id'] }}">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title h5" id="modalCreateTelemedicineSessionTitle">Новый сеанс телемедицины</h3>
                    <p class="mc-tm-modal__subtitle">Запись на видеоконсультацию</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                @if($telemedManager['create_errors'])
                    <div class="alert alert-danger" role="alert">
                        @foreach($telemedManager['create_errors'] as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif
                <label for="mcTmDoctor" class="form-label">Врач</label>
                <select id="mcTmDoctor" name="telemed_doctor_id" class="form-select" required>
                    @foreach($telemedManager['doctors'] as $doctorOption)
                        <option value="{{ $doctorOption['id'] }}" @selected($doctorOption['selected'])>{{ $doctorOption['name'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">По умолчанию выбран текущий лечащий врач. Выбор другого врача для сеанса не меняет лечащего врача пациента.</div>
                <label for="mcTmStartsAt" class="form-label mt-3">Дата и время начала</label>
                <input type="datetime-local" class="form-control" id="mcTmStartsAt" name="telemed_starts_at"
                       value="{{ $telemedManager['starts_at'] }}" min="{{ $telemedManager['minimum'] }}" step="60" required>
                <div class="form-text">Дата и время вводятся по часовому поясу врача: {{ $telemedManager['timezone'] }}.</div>
                <label for="mcTmDuration" class="form-label mt-3">Длительность приёма, мин.</label>
                <input type="number" class="form-control" id="mcTmDuration" name="telemed_duration_minutes"
                       value="{{ $telemedManager['duration_minutes'] }}" min="5" max="480" step="5" required>
                <div class="form-text">Весь приём должен укладываться в рабочую смену врача. График, отпуск и исключения проверяются при сохранении.</div>
                <div data-appointment-input-zone data-appointment-input-name="telemed_starts_at"
                     data-clinic-timezone="{{ $telemedManager['timezone'] }}" data-appointment-legacy="0">
                    <div class="small text-muted mt-2" data-appointment-input-preview role="status" aria-live="polite"></div>
                    @include('shared.appointment_timezone_control')
                </div>

{{-- NORHNAIM_TELEMED_AVAILABLE_SLOTS_V1 --}}
<div class="border rounded-3 p-3 mt-3 bg-light-subtle"
     data-telemedicine-slot-picker
     data-slots-url="{{ route('doctors.patients.telemedicine.available-slots', ['patient' => $patient->id]) }}"
     data-doctor-timezone-url-template="{{ route('doctors.settings.doctor-appointment-timezone.update', ['doctor' => '__DOCTOR__']) }}">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
        <div>
            <div class="fw-semibold">
                <i class="bi bi-calendar2-check me-1"></i>
                Свободное время врача
            </div>
            <div class="small text-muted">
                Рабочие дни и смены берутся напрямую из расписания врача.
                Занятые записи исключаются автоматически.
            </div>
        </div>

        <div>
            <label class="form-label small mb-1"
                   for="mcTmSlotsDate">
                Начиная с даты
            </label>
            <div class="input-group input-group-sm">
                <input type="date"
                       class="form-control"
                       id="mcTmSlotsDate"
                       data-tm-slots-date
                       min="{{ substr((string) $telemedManager['minimum'], 0, 10) }}"
                       value="{{ substr((string) $telemedManager['starts_at'], 0, 10) }}">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-tm-slots-refresh
                        title="Обновить">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
    </div>


<div class="mt-3">
    <label class="form-label small mb-1"
           for="mcTmDoctorTimezone">
        Часовой пояс врача
    </label>
    <select class="form-select form-select-sm"
            id="mcTmDoctorTimezone"
            data-tm-doctor-timezone>
        <option value="">Загрузка...</option>
    </select>
    <div class="small text-muted mt-1"
         data-tm-doctor-timezone-hint>
        Время смены врача интерпретируется в его часовом поясе.
    </div>
</div>

    <div class="d-flex flex-wrap gap-2 mt-3"
         data-tm-slot-days></div>

    <div class="d-flex flex-wrap gap-2 mt-3"
         data-tm-slot-list></div>

    <div class="small text-muted mt-2"
         data-tm-slots-state
         aria-live="polite">
        Свободные окна загрузятся после открытия формы.
    </div>
</div>
                <div class="mc-tm-modal__note mt-3"><i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>Будет создана запись в журнале и расписании. Консультация не запускается и не подтверждается автоматически.</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary" data-telemed-submit>Создать сеанс</button>
            </div>
        </form>
    </div>
</div>
@endif
<div class="modal fade mc-modal-ui mc-tm-modal" id="modalDeleteTelemedicineSession" tabindex="-1"
     aria-labelledby="modalDeleteTelemedicineSessionTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" data-telemed-form data-telemed-delete-form>
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <div>
                    <h3 class="modal-title h5" id="modalDeleteTelemedicineSessionTitle">Удалить сеанс?</h3>
                    <p class="mc-tm-modal__subtitle" data-telemed-delete-caption></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mc-tm-modal__note"><i class="bi bi-archive" aria-hidden="true"></i>
                    <span>Сеанс исчезнет из действующих записей и перейдёт в архив удалённых. События, причина удаления и медицинская история сохранятся.</span></div>
                <label class="form-label mt-3" for="mcTmDeleteReason">Причина удаления</label>
                <textarea id="mcTmDeleteReason" class="form-control" name="telemed_delete_reason"
                          rows="3" minlength="5" maxlength="1000" required></textarea>
                <label class="mc-tm-confirm mt-3">
                    <input type="checkbox" class="form-check-input" name="telemed_delete_confirmed" value="1" required>
                    <span>Подтверждаю удаление выбранного сеанса из действующего расписания.</span>
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Не удалять</button>
                <button type="submit" class="btn btn-danger" data-telemed-submit disabled>Удалить сеанс</button>
            </div>
        </form>
    </div>
</div>

@once
    <link rel="stylesheet" href="{{ asset('css/telemedicine-available-slots.css') }}?v=1.2.0">
    <script src="{{ asset('js/telemedicine-available-slots.js') }}?v=1.2.0" defer></script>
@endonce
