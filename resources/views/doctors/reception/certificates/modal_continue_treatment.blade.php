<div class="modal fade mc-cert-modal" id="certTreatmentModal" tabindex="-1"
     aria-labelledby="certTreatmentModalTitle" aria-describedby="certTreatmentModalDescription" aria-hidden="true"
     data-cert-modal data-cert-title="Для продолжения лечения"
     data-cert-patient="{{ $certificateUiContext['patient_name'] }}"
     data-cert-birth="{{ $certificateUiContext['birth_date'] }}"
     data-cert-card="{{ $certificateUiContext['card_number'] }}"
     data-cert-ai-url="{{ route('doctors.patients.analyze.anamnesis') }}"
     data-cert-ai-mode="continue_treatment"
     data-cert-patient-id="{{ $patient->id }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mc-cert-modal__heading">
                    <span class="mc-certificates__icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                    <div>
                        <div class="mc-certificates__eyebrow">Предварительная форма</div>
                        <h2 class="modal-title" id="certTreatmentModalTitle">Для продолжения лечения</h2>
                        <p id="certTreatmentModalDescription">Передача сведений принимающему врачу или организации</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mc-cert-patient">
                    <div><span>Пациент</span><strong>{{ $certificateUiContext['patient_name'] ?: 'Не указан' }}</strong></div>
                    <div><span>Дата рождения</span><strong>{{ $certificateUiContext['birth_date'] ?: 'Не указана' }}</strong></div>
                    <div><span>Медкарта</span><strong>{{ $certificateUiContext['card_number'] ?: 'Не указана' }}</strong></div>
                </div>
                <section class="mc-cert-section mb-3" aria-labelledby="certTreatmentAiTitle">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="mc-certificates__eyebrow">Способ заполнения</div>
                            <h3 id="certTreatmentAiTitle" class="mb-1">Сформировать сведения по всей медицинской карте</h3>
                            <p class="text-muted small mb-0">
                                ИИ анализирует диагноз, динамику, назначения, эффект и переносимость терапии,
                                значимые исследования и уже зафиксированные рекомендации. Новые назначения ИИ не создаёт.
                            </p>
                        </div>
                        <button type="button" class="btn mc-cert-btn-primary" data-cert-ai-generate>
                            <i class="bi bi-stars" aria-hidden="true"></i>
                            Сформировать ИИ
                        </button>
                    </div>
                    <div class="mt-3 d-flex flex-wrap gap-2" aria-label="Источники анализа">
                        <span class="badge text-bg-light border">Анамнезы</span>
                        <span class="badge text-bg-light border">Эпикризы</span>
                        <span class="badge text-bg-light border">Динамика</span>
                        <span class="badge text-bg-light border">Терапия</span>
                        <span class="badge text-bg-light border">Анализы</span>
                        <span class="badge text-bg-light border">Исследования</span>
                        <span class="badge text-bg-light border">Тесты</span>
                    </div>
                    <div class="small text-muted mt-2" data-cert-ai-status>
                        Принимающую организацию, врача и цель передачи можно указать вручную до или после ИИ-формирования.
                    </div>
                </section>

                <div class="mc-cert-mode" role="group" aria-label="Режим просмотра">
                    <button type="button" class="is-active" data-cert-edit aria-pressed="true" aria-controls="certTreatmentModalEditor">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Заполнение
                    </button>
                    <button type="button" data-cert-preview aria-pressed="false" aria-controls="certTreatmentModalPreview">
                        <i class="bi bi-eye" aria-hidden="true"></i> Предпросмотр
                    </button>
                </div>
                <div class="alert alert-danger" data-cert-error role="alert" hidden></div>
                <div id="certTreatmentModalEditor" data-cert-editor>
                    <section class="mc-cert-section" aria-labelledby="certTreatmentModalDetailsTitle">
                        <h3 id="certTreatmentModalDetailsTitle">Реквизиты документа</h3>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="cert-treatment-date">Дата документа</label>
                                <input id="cert-treatment-date" name="cert_treatment_issued_date" class="form-control" type="date"
                                       value="{{ $certificateUiContext['issued_date'] }}" data-cert-issued-date required>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label" for="cert-treatment-doctor">Врач</label>
                                <input id="cert-treatment-doctor" name="cert_treatment_doctor" class="form-control" type="text" maxlength="255"
                                       value="{{ $certificateUiContext['doctor_name'] }}" data-cert-doctor placeholder="ФИО врача">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-organization">Медицинская организация</label>
                                <input id="cert-treatment-organization" name="cert_treatment_organization" class="form-control" type="text" maxlength="500"
                                       data-cert-organization placeholder="Название медицинской организации">
                            </div>
                        </div>
                    </section>
                    <section class="mc-cert-section" aria-labelledby="certTreatmentModalContentTitle">
                        <h3 id="certTreatmentModalContentTitle">Содержание документа</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-diagnosis">Диагноз</label>
                                <input id="cert-treatment-diagnosis" name="cert_treatment_diagnosis" class="form-control" data-cert-field data-cert-label="Диагноз" type="text" maxlength="500" value="{{ $certificateUiContext['diagnosis'] }}" placeholder="Код МКБ-10 и формулировка диагноза" data-cert-diagnosis >
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="cert-treatment-recipient_organization">Принимающая организация</label>
                                <input id="cert-treatment-recipient_organization" name="cert_treatment_recipient_organization" class="form-control" data-cert-field data-cert-label="Принимающая организация" type="text" maxlength="500" value="" placeholder="Название медицинской организации" >
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="cert-treatment-recipient_doctor">Принимающий врач</label>
                                <input id="cert-treatment-recipient_doctor" name="cert_treatment_recipient_doctor" class="form-control" data-cert-field data-cert-label="Принимающий врач" type="text" maxlength="500" value="" placeholder="ФИО или специальность" >
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-reason">Цель передачи</label>
                                <textarea id="cert-treatment-reason" name="cert_treatment_reason" class="form-control" data-cert-field data-cert-label="Цель передачи" rows="3" maxlength="20000" placeholder="Причина направления для продолжения лечения" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-condition">Текущее состояние</label>
                                <textarea id="cert-treatment-condition" name="cert_treatment_condition" class="form-control" data-cert-field data-cert-label="Текущее состояние" rows="3" maxlength="20000" placeholder="Актуальное состояние пациента" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-therapy">Проводимая терапия</label>
                                <textarea id="cert-treatment-therapy" name="cert_treatment_therapy" class="form-control" data-cert-field data-cert-label="Проводимая терапия" rows="4" maxlength="20000" placeholder="Препараты, дозировки, схемы и длительность приёма" >{{ $certificateUiContext['therapy'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-effect">Эффект и переносимость</label>
                                <textarea id="cert-treatment-effect" name="cert_treatment_effect" class="form-control" data-cert-field data-cert-label="Эффект и переносимость" rows="3" maxlength="20000" placeholder="Достигнутый эффект и переносимость терапии" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-researches">Значимые результаты исследований</label>
                                <textarea id="cert-treatment-researches" name="cert_treatment_researches" class="form-control" data-cert-field data-cert-label="Значимые результаты исследований" rows="3" maxlength="20000" placeholder="Результаты, важные для дальнейшего ведения" >{{ $certificateUiContext['researches'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-treatment-recommendations">Рекомендации и план лечения</label>
                                <textarea id="cert-treatment-recommendations" name="cert_treatment_recommendations" class="form-control" data-cert-field data-cert-label="Рекомендации и план лечения" rows="4" maxlength="20000" placeholder="Дальнейшее лечение, контроль и наблюдение" ></textarea>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="certTreatmentModalPreview" class="mc-cert-preview" data-cert-preview-panel hidden>
                    <div class="mc-cert-preview__notice"><i class="bi bi-info-circle" aria-hidden="true"></i> Макет для проверки. Не сохранён в медицинской карте.</div>
                    <article class="mc-cert-paper" data-cert-paper aria-label="Предварительный документ"></article>
                </div>
            </div>
            <div class="modal-footer">
                <div class="mc-cert-modal__footer-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Без сохранения в медкарту</div>
                <button type="button" class="btn mc-cert-btn-light" data-cert-reset>Сбросить форму</button>
                <button type="button" class="btn mc-cert-btn-light" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn mc-cert-btn-primary" data-cert-preview><i class="bi bi-eye" aria-hidden="true"></i> Предпросмотр</button>
                <button type="button" class="btn mc-cert-btn-outline" data-cert-print><i class="bi bi-printer" aria-hidden="true"></i> Печать макета</button>
            </div>
        </div>
    </div>
</div>
