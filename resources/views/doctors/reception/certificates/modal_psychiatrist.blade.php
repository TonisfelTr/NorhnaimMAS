<div class="modal fade mc-cert-modal" id="certPsychiatristModal" tabindex="-1"
     aria-labelledby="certPsychiatristModalTitle" aria-describedby="certPsychiatristModalDescription" aria-hidden="true"
     data-cert-modal data-cert-title="Заключение психиатра"
     data-cert-patient="{{ $certificateUiContext['patient_name'] }}"
     data-cert-birth="{{ $certificateUiContext['birth_date'] }}"
     data-cert-card="{{ $certificateUiContext['card_number'] }}"
     data-cert-ai-url="{{ route('doctors.patients.analyze.anamnesis') }}"
     data-cert-ai-mode="psychiatrist_conclusion"
     data-cert-patient-id="{{ $patient->id }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mc-cert-modal__heading">
                    <span class="mc-certificates__icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <div>
                        <div class="mc-certificates__eyebrow">Предварительная форма</div>
                        <h2 class="modal-title" id="certPsychiatristModalTitle">Заключение психиатра</h2>
                        <p id="certPsychiatristModalDescription">Осмотр, клиническое заключение и рекомендации</p>
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
                <section class="mc-cert-section mb-3" aria-labelledby="certPsychiatristAiTitle">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="mc-certificates__eyebrow">Способ заполнения</div>
                            <h3 id="certPsychiatristAiTitle" class="mb-1">Сформировать по всей медицинской карте</h3>
                            <p class="text-muted small mb-0">
                                ИИ анализирует релевантные анамнезы, эпикризы, симптомы, состояния, назначения,
                                лабораторные и инструментальные исследования и результаты тестов пациента.
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
                        <span class="badge text-bg-light border">Симптомы</span>
                        <span class="badge text-bg-light border">Терапия</span>
                        <span class="badge text-bg-light border">Анализы</span>
                        <span class="badge text-bg-light border">Исследования</span>
                        <span class="badge text-bg-light border">Тесты</span>
                    </div>
                    <div class="small text-muted mt-2" data-cert-ai-status>
                        Поля можно заполнить вручную либо сформировать черновик ИИ и затем отредактировать.
                    </div>
                </section>

                <div class="mc-cert-mode" role="group" aria-label="Режим просмотра">
                    <button type="button" class="is-active" data-cert-edit aria-pressed="true" aria-controls="certPsychiatristModalEditor">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Заполнение
                    </button>
                    <button type="button" data-cert-preview aria-pressed="false" aria-controls="certPsychiatristModalPreview">
                        <i class="bi bi-eye" aria-hidden="true"></i> Предпросмотр
                    </button>
                </div>
                <div class="alert alert-danger" data-cert-error role="alert" hidden></div>
                <div id="certPsychiatristModalEditor" data-cert-editor>
                    <section class="mc-cert-section" aria-labelledby="certPsychiatristModalDetailsTitle">
                        <h3 id="certPsychiatristModalDetailsTitle">Реквизиты документа</h3>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="cert-psychiatrist-date">Дата документа</label>
                                <input id="cert-psychiatrist-date" name="cert_psychiatrist_issued_date" class="form-control" type="date"
                                       value="{{ $certificateUiContext['issued_date'] }}" data-cert-issued-date required>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label" for="cert-psychiatrist-doctor">Врач</label>
                                <input id="cert-psychiatrist-doctor" name="cert_psychiatrist_doctor" class="form-control" type="text" maxlength="255"
                                       value="{{ $certificateUiContext['doctor_name'] }}" data-cert-doctor placeholder="ФИО врача">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-organization">Медицинская организация</label>
                                <input id="cert-psychiatrist-organization" name="cert_psychiatrist_organization" class="form-control" type="text" maxlength="500"
                                       data-cert-organization placeholder="Название медицинской организации">
                            </div>
                        </div>
                    </section>
                    <section class="mc-cert-section" aria-labelledby="certPsychiatristModalContentTitle">
                        <h3 id="certPsychiatristModalContentTitle">Содержание документа</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-diagnosis">Диагноз</label>
                                <input id="cert-psychiatrist-diagnosis" name="cert_psychiatrist_diagnosis" class="form-control" data-cert-field data-cert-label="Диагноз" type="text" maxlength="500" value="{{ $certificateUiContext['diagnosis'] }}" placeholder="Код МКБ-10 и формулировка диагноза" data-cert-diagnosis >
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-complaints">Жалобы</label>
                                <textarea id="cert-psychiatrist-complaints" name="cert_psychiatrist_complaints" class="form-control" data-cert-field data-cert-label="Жалобы" rows="3" maxlength="20000" placeholder="Жалобы со слов пациента" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-anamnesis">Анамнез</label>
                                <textarea id="cert-psychiatrist-anamnesis" name="cert_psychiatrist_anamnesis" class="form-control" data-cert-field data-cert-label="Анамнез" rows="3" maxlength="20000" placeholder="Анамнестические сведения, подтверждённые врачом" >{{ $certificateUiContext['anamnesis'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-mental_status">Психический статус</label>
                                <textarea id="cert-psychiatrist-mental_status" name="cert_psychiatrist_mental_status" class="form-control" data-cert-field data-cert-label="Психический статус" rows="4" maxlength="20000" placeholder="Описание психического статуса по результатам осмотра" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-conclusion">Заключение психиатра</label>
                                <textarea id="cert-psychiatrist-conclusion" name="cert_psychiatrist_conclusion" class="form-control" data-cert-field data-cert-label="Заключение психиатра" rows="4" maxlength="20000" placeholder="Итоговое заключение специалиста" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-therapy">Терапия</label>
                                <textarea id="cert-psychiatrist-therapy" name="cert_psychiatrist_therapy" class="form-control" data-cert-field data-cert-label="Терапия" rows="3" maxlength="20000" placeholder="Проводимое или назначенное лечение" >{{ $certificateUiContext['therapy'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-psychiatrist-recommendations">Рекомендации</label>
                                <textarea id="cert-psychiatrist-recommendations" name="cert_psychiatrist_recommendations" class="form-control" data-cert-field data-cert-label="Рекомендации" rows="3" maxlength="20000" placeholder="Рекомендации и дальнейший план наблюдения" ></textarea>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="certPsychiatristModalPreview" class="mc-cert-preview" data-cert-preview-panel hidden>
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
