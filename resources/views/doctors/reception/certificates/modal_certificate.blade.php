<div class="modal fade mc-cert-modal" id="certCertificateModal" tabindex="-1"
     aria-labelledby="certCertificateModalTitle" aria-describedby="certCertificateModalDescription" aria-hidden="true"
     data-cert-modal data-cert-title="Справка"
     data-cert-patient="{{ $certificateUiContext['patient_name'] }}"
     data-cert-birth="{{ $certificateUiContext['birth_date'] }}"
     data-cert-card="{{ $certificateUiContext['card_number'] }}"
     data-cert-ai-url="{{ route('doctors.patients.analyze.anamnesis') }}"
     data-cert-ai-mode="medical_certificate"
     data-cert-patient-id="{{ $patient->id }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mc-cert-modal__heading">
                    <span class="mc-certificates__icon"><i class="bi bi-file-earmark-check" aria-hidden="true"></i></span>
                    <div>
                        <div class="mc-certificates__eyebrow">Предварительная форма</div>
                        <h2 class="modal-title" id="certCertificateModalTitle">Справка</h2>
                        <p id="certCertificateModalDescription">Подтверждённые сведения для предоставления получателю</p>
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
                <section class="mc-cert-section mb-3" aria-labelledby="certCertificateAiTitle">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="mc-certificates__eyebrow">Способ заполнения</div>
                            <h3 id="certCertificateAiTitle" class="mb-1">Сформировать по медицинской карте</h3>
                            <p class="text-muted small mb-0">
                                ИИ использует только подтверждённые сведения пациента и учитывает выбранный вид справки.
                                Диагноз включается только если врач отдельно разрешил это переключателем ниже.
                            </p>
                        </div>
                        <button type="button" class="btn mc-cert-btn-primary" data-cert-ai-generate>
                            <i class="bi bi-stars" aria-hidden="true"></i>
                            Сформировать ИИ
                        </button>
                    </div>
                    <div class="mt-3 d-flex flex-wrap gap-2" aria-label="Источники анализа">
                        <span class="badge text-bg-light border">Наблюдение</span>
                        <span class="badge text-bg-light border">Анамнезы</span>
                        <span class="badge text-bg-light border">Эпикризы</span>
                        <span class="badge text-bg-light border">Терапия</span>
                        <span class="badge text-bg-light border">Исследования</span>
                    </div>
                    <div class="small text-muted mt-2" data-cert-ai-status>
                        Справку можно заполнить вручную либо сформировать черновик ИИ и затем отредактировать.
                    </div>
                </section>

                <div class="mc-cert-mode" role="group" aria-label="Режим просмотра">
                    <button type="button" class="is-active" data-cert-edit aria-pressed="true" aria-controls="certCertificateModalEditor">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Заполнение
                    </button>
                    <button type="button" data-cert-preview aria-pressed="false" aria-controls="certCertificateModalPreview">
                        <i class="bi bi-eye" aria-hidden="true"></i> Предпросмотр
                    </button>
                </div>
                <div class="alert alert-danger" data-cert-error role="alert" hidden></div>
                <div id="certCertificateModalEditor" data-cert-editor>
                    <section class="mc-cert-section" aria-labelledby="certCertificateModalDetailsTitle">
                        <h3 id="certCertificateModalDetailsTitle">Реквизиты документа</h3>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="cert-certificate-date">Дата документа</label>
                                <input id="cert-certificate-date" name="cert_certificate_issued_date" class="form-control" type="date"
                                       value="{{ $certificateUiContext['issued_date'] }}" data-cert-issued-date required>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label" for="cert-certificate-doctor">Врач</label>
                                <input id="cert-certificate-doctor" name="cert_certificate_doctor" class="form-control" type="text" maxlength="255"
                                       value="{{ $certificateUiContext['doctor_name'] }}" data-cert-doctor placeholder="ФИО врача">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-certificate-organization">Медицинская организация</label>
                                <input id="cert-certificate-organization" name="cert_certificate_organization" class="form-control" type="text" maxlength="500"
                                       data-cert-organization placeholder="Название медицинской организации">
                            </div>
                        </div>
                    </section>
                    <section class="mc-cert-section" aria-labelledby="certCertificateModalContentTitle">
                        <h3 id="certCertificateModalContentTitle">Содержание документа</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="cert-certificate-kind">Вид справки</label>
                                <select class="form-select" id="cert-certificate-kind" name="cert_certificate_kind" data-cert-document-kind>
                                    <option value="general">Справка по месту требования</option>
                                    <option value="observation">Справка о наблюдении</option>
                                    <option value="treatment">Справка о лечении</option>
                                </select>
                            </div><div class="col-md-6">
                                <label class="form-label" for="cert-certificate-recipient">Получатель</label>
                                <input id="cert-certificate-recipient" name="cert_certificate_recipient" class="form-control" data-cert-field data-cert-label="Получатель" type="text" maxlength="500" value="По месту требования" placeholder="Организация или получатель" >
                            </div>
                            <div class="col-12">
                                <div class="form-check mc-cert-toggle">
                                    <input class="form-check-input" type="checkbox" id="cert-certificate-include-diagnosis" data-cert-include-diagnosis>
                                    <label class="form-check-label" for="cert-certificate-include-diagnosis">Включить диагноз в справку</label>
                                    <div class="form-text">По умолчанию диагноз не попадает в предпросмотр и печатный макет.</div>
                                </div>
                            </div>
                            <div class="col-12" data-cert-diagnosis-block hidden><div class="row"><div class="col-12">
                                <label class="form-label" for="cert-certificate-diagnosis">Диагноз</label>
                                <input id="cert-certificate-diagnosis" name="cert_certificate_diagnosis" class="form-control" data-cert-field data-cert-label="Диагноз" type="text" maxlength="500" value="{{ $certificateUiContext['diagnosis'] }}" placeholder="Код МКБ-10 и формулировка диагноза" data-cert-diagnosis disabled>
                            </div></div></div>
                            <div class="col-12">
                                <label class="form-label" for="cert-certificate-purpose">Цель выдачи</label>
                                <input id="cert-certificate-purpose" name="cert_certificate_purpose" class="form-control" data-cert-field data-cert-label="Цель выдачи" type="text" maxlength="500" value="" placeholder="Для чего требуется справка" >
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-certificate-text">Текст справки</label>
                                <textarea id="cert-certificate-text" name="cert_certificate_text" class="form-control" data-cert-field data-cert-label="Текст справки" rows="7" maxlength="20000" placeholder="Укажите только подтверждённые сведения для выдачи справки" data-cert-certificate-text></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-certificate-additional">Дополнительные сведения</label>
                                <textarea id="cert-certificate-additional" name="cert_certificate_additional" class="form-control" data-cert-field data-cert-label="Дополнительные сведения" rows="3" maxlength="20000" placeholder="Дополнительные сведения при необходимости" ></textarea>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="certCertificateModalPreview" class="mc-cert-preview" data-cert-preview-panel hidden>
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
