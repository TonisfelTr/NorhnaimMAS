    @if($canTransferPatient)
        <div class="modal fade mc-modal-ui mc-modal-ui--blue mc-doctor-transfer-modal"
             id="modalTransferPatientDoctor"
             tabindex="-1"
             role="dialog"
             aria-hidden="true"
             aria-labelledby="modalTransferPatientDoctorTitle"
             data-candidates-url="{{ route('doctors.patients.doctor-transfer.candidates', $patient->id) }}"
             data-open-on-error="{{ $errors->patientTransfer->any() ? '1' : '0' }}"
             data-old-doctor-id="{{ old('doctor_id', '') }}">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form class="modal-content"
                      method="post"
                      data-transfer-form
                      action="{{ route('doctors.patients.doctor-transfer.store', $patient->id) }}">
                    @csrf
                    <input type="hidden" name="expected_doctor_id" value="{{ $patient->doctor_id }}">
                    <div class="modal-header">
                        <div class="mc-modal-heading">
                            <span class="mc-modal-heading__icon" aria-hidden="true">
                                <i class="bi bi-person-gear"></i>
                            </span>
                            <div class="mc-modal-heading__content">
                                <div class="mc-modal-heading__eyebrow">Ответственный за пациента</div>
                                <h5 class="modal-title" id="modalTransferPatientDoctorTitle">
                                    Передать пациента врачу
                                </h5>
                                <div class="mc-modal-heading__subtitle">
                                    Новый лечащий врач будет назначен после подтверждения.
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        @if($errors->patientTransfer->any())
                            <div class="alert alert-danger" role="alert">
                                <div class="fw-semibold mb-1">Не удалось передать пациента</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->patientTransfer->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mc-transfer-panel">
                            <div class="mc-transfer-panel__eyebrow">Текущий лечащий врач</div>
                            <div class="mc-transfer-panel__name">
                                <i class="bi bi-person-badge text-primary" aria-hidden="true"></i>
                                {{ $assignedDoctorName }}
                            </div>
                        </div>

                        <div class="mc-transfer-arrow" aria-hidden="true">
                            <i class="bi bi-arrow-down-circle"></i>
                        </div>

                        <div class="mc-transfer-panel">
                            <label for="patientTransferSearch" class="form-label">Найти врача в клинике</label>
                            <input id="patientTransferSearch"
                                   type="search"
                                   class="form-control mc-transfer-search"
                                   autocomplete="off"
                                   data-transfer-search
                                   placeholder="Поиск по ФИО или должности">

                            <label for="patientTransferDoctorId" class="form-label label-required">
                                Новый лечащий врач
                            </label>
                            <select id="patientTransferDoctorId"
                                    name="doctor_id"
                                    class="form-select @error('doctor_id', 'patientTransfer') is-invalid @enderror"
                                    required
                                    disabled
                                    data-transfer-select>
                                <option value="">Загружаем список врачей…</option>
                            </select>
                            <span class="mc-transfer-status" data-transfer-status aria-live="polite"></span>

                            <div class="mc-transfer-preview" data-transfer-summary aria-live="polite">
                                Выберите врача из списка, чтобы подтвердить передачу.
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label label-required" for="patientTransferReason">
                                Причина передачи
                            </label>
                            <textarea id="patientTransferReason"
                                      name="reason"
                                      rows="3"
                                      minlength="5"
                                      maxlength="1000"
                                      required
                                      class="form-control @error('reason', 'patientTransfer') is-invalid @enderror"
                                      placeholder="Например: смена лечащего врача по решению клиники">{{ old('reason') }}</textarea>
                            <div class="form-text">
                                Причина и ответственный сотрудник сохраняются в истории передачи.
                            </div>
                        </div>

                        <div class="mc-transfer-warning">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>История приёмов, назначения, рецепты и результаты исследований остаются
                                в медицинской карте. Передача меняет только текущего лечащего врача.</span>
                        </div>

                        <div class="form-check mc-transfer-confirm mt-3">
                            <input type="checkbox" class="form-check-input"
                                   name="confirm_transfer" value="1"
                                   id="patientTransferConfirm" required data-transfer-confirm>
                            <label class="form-check-label" for="patientTransferConfirm">
                                Подтверждаю назначение нового лечащего врача пациенту
                                <strong>{{ $patient->full_name }}</strong>.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary" disabled data-transfer-submit>
                            <i class="bi bi-arrow-left-right me-1" aria-hidden="true"></i>
                            Передать пациента
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
