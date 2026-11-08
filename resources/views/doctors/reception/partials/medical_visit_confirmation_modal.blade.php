<div class="modal fade mc-modal-ui mc-modal-ui--teal mc-audit-modal" id="modalConfirmMedicalVisit"
     tabindex="-1" aria-hidden="true" aria-labelledby="modalConfirmMedicalVisitTitle">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" method="post"
              action="{{ route('doctors.patients.visits.confirm', ['patient' => $patient->getKey(), 'registryRecord' => '__RECORD__']) }}"
              data-medical-visit-form
              data-action-template="{{ route('doctors.patients.visits.confirm', ['patient' => $patient->getKey(), 'registryRecord' => '__RECORD__']) }}">
            @csrf
            <div class="modal-header">
                <div>
                    <h3 class="modal-title h5" id="modalConfirmMedicalVisitTitle">Подтверждение приёма</h3>
                    <p class="mc-audit-modal__subtitle mb-0" data-medical-visit-caption>Укажите фактический результат консультации</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mc-audit-modal__notice mb-3">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    <span>Отметка вносится от имени авторизованного врача и сохраняется в истории действий.</span>
                </div>
                <fieldset class="mc-audit-radio-grid" aria-label="Результат приёма">
                    <label class="mc-audit-radio-card">
                        <input type="radio" name="outcome" value="held" checked data-medical-visit-outcome>
                        <span class="mc-audit-radio-card__surface">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            <span><strong>Состоялся</strong><small>Пациент участвовал, консультация проведена</small></span>
                        </span>
                    </label>
                    <label class="mc-audit-radio-card">
                        <input type="radio" name="outcome" value="not_held" data-medical-visit-outcome>
                        <span class="mc-audit-radio-card__surface">
                            <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                            <span><strong>Не состоялся</strong><small>Встреча не состоялась или была сорвана</small></span>
                        </span>
                    </label>
                </fieldset>
                <div class="mt-3" data-medical-visit-held-fields>
                    <label class="mc-audit-attestation">
                        <input type="checkbox" class="form-check-input" name="attested" value="1" data-medical-visit-attested>
                        <span>Я подтверждаю, что пациент участвовал и медицинская консультация была действительно проведена.</span>
                    </label>
                </div>
                <div class="mt-3 d-none" data-medical-visit-notheld-fields>
                    <label for="medicalVisitNotHeldReason" class="form-label">Почему приём не состоялся?</label>
                    <textarea class="form-control" name="reason" id="medicalVisitNotHeldReason"
                              rows="3" maxlength="1200" placeholder="Например: пациент не подключился; техническая ошибка на стороне клиники"></textarea>
                </div>
                <div class="mt-3 d-none" data-medical-visit-correction-fields>
                    <label for="medicalVisitCorrectionReason" class="form-label">Причина изменения ранее сохранённой отметки</label>
                    <textarea class="form-control" name="correction_reason" id="medicalVisitCorrectionReason"
                              rows="2" maxlength="600" placeholder="Укажите, почему требуется исправление (от 10 символов)"></textarea>
                    <div class="form-text">Предыдущее подтверждение останется в журнале.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary" data-medical-visit-submit>
                    <i class="bi bi-patch-check me-1" aria-hidden="true"></i> Сохранить отметку
                </button>
            </div>
        </form>
    </div>
</div>
