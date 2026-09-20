<div class="modal fade mc-cert-modal" id="certExtractModal" tabindex="-1"
     aria-labelledby="certExtractModalTitle" aria-describedby="certExtractModalDescription" aria-hidden="true"
     data-cert-modal data-cert-title="Выписка из медицинской карты"
     data-cert-patient="{{ $certificateUiContext['patient_name'] }}"
     data-cert-birth="{{ $certificateUiContext['birth_date'] }}"
     data-cert-card="{{ $certificateUiContext['card_number'] }}"
     data-cert-ai-url="{{ route('doctors.patients.analyze.anamnesis') }}"
     data-cert-ai-mode="medical_extract"
     data-cert-patient-id="{{ $patient->id }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mc-cert-modal__heading">
                    <span class="mc-certificates__icon"><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i></span>
                    <div>
                        <div class="mc-certificates__eyebrow">Предварительная форма</div>
                        <h2 class="modal-title" id="certExtractModalTitle">Выписка из медицинской карты</h2>
                        <p id="certExtractModalDescription">Сводные сведения за выбранный период</p>
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
                <section class="mc-cert-section mb-3" aria-labelledby="certExtractAiTitle">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="mc-certificates__eyebrow">Способ заполнения</div>
                            <h3 id="certExtractAiTitle" class="mb-1">Сформировать по медицинской карте</h3>
                            <p class="text-muted small mb-0">
                                ИИ анализирует клинические записи пациента за выбранный период: анамнезы, эпикризы,
                                симптомы, терапию, лабораторные и инструментальные исследования и результаты тестов.
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
                        Выберите период ниже. Поля можно заполнить вручную либо сформировать черновик ИИ и затем отредактировать.
                    </div>
                </section>

                <div class="mc-cert-mode" role="group" aria-label="Режим просмотра">
                    <button type="button" class="is-active" data-cert-edit aria-pressed="true" aria-controls="certExtractModalEditor">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Заполнение
                    </button>
                    <button type="button" data-cert-preview aria-pressed="false" aria-controls="certExtractModalPreview">
                        <i class="bi bi-eye" aria-hidden="true"></i> Предпросмотр
                    </button>
                </div>
                <div class="alert alert-danger" data-cert-error role="alert" hidden></div>
                <div id="certExtractModalEditor" data-cert-editor>
                    <section class="mc-cert-section" aria-labelledby="certExtractModalDetailsTitle">
                        <h3 id="certExtractModalDetailsTitle">Реквизиты документа</h3>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="cert-extract-date">Дата документа</label>
                                <input id="cert-extract-date" name="cert_extract_issued_date" class="form-control" type="date"
                                       value="{{ $certificateUiContext['issued_date'] }}" data-cert-issued-date required>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label" for="cert-extract-doctor">Врач</label>
                                <input id="cert-extract-doctor" name="cert_extract_doctor" class="form-control" type="text" maxlength="255"
                                       value="{{ $certificateUiContext['doctor_name'] }}" data-cert-doctor placeholder="ФИО врача">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-organization">Медицинская организация</label>
                                <input id="cert-extract-organization" name="cert_extract_organization" class="form-control" type="text" maxlength="500"
                                       data-cert-organization placeholder="Название медицинской организации">
                            </div>
                        </div>
                    </section>
                    <section class="mc-cert-section" aria-labelledby="certExtractModalContentTitle">
                        <h3 id="certExtractModalContentTitle">Содержание документа</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <fieldset class="mc-cert-period">
                                    <legend>Период выписки</legend>
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cert_extract_period" id="cert-extract-period-all" value="all" data-cert-period checked>
                                            <label class="form-check-label" for="cert-extract-period-all">Всё время наблюдения</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="cert_extract_period" id="cert-extract-period-range" value="range" data-cert-period>
                                            <label class="form-check-label" for="cert-extract-period-range">Выбрать период</label>
                                        </div>
                                    </div>
                                    <div class="row g-3" data-cert-period-fields hidden>
                                        <div class="col-sm-6">
                                            <label class="form-label" for="cert-extract-period-from">С</label>
                                            <input class="form-control" id="cert-extract-period-from" name="cert_extract_period_from" type="date" data-cert-period-from disabled>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label" for="cert-extract-period-to">По</label>
                                            <input class="form-control" id="cert-extract-period-to" name="cert_extract_period_to" type="date" data-cert-period-to disabled>
                                        </div>
                                    </div>
                                    <div class="form-text">Выбранный период используется и для ИИ-формирования выписки, и в печатном макете.</div>
                                </fieldset>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-diagnosis">Диагноз</label>
                                <input id="cert-extract-diagnosis" name="cert_extract_diagnosis" class="form-control" data-cert-field data-cert-label="Диагноз" type="text" maxlength="500" value="{{ $certificateUiContext['diagnosis'] }}" placeholder="Код МКБ-10 и формулировка диагноза" data-cert-diagnosis >
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-recipient">Куда предоставляется</label>
                                <input id="cert-extract-recipient" name="cert_extract_recipient" class="form-control" data-cert-field data-cert-label="Куда предоставляется" type="text" maxlength="500" value="" placeholder="Организация или получатель" >
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-anamnesis">Анамнез и течение заболевания</label>
                                <textarea id="cert-extract-anamnesis" name="cert_extract_anamnesis" class="form-control" data-cert-field data-cert-label="Анамнез и течение заболевания" rows="4" maxlength="20000" placeholder="Сведения о наблюдении и течении заболевания" >{{ $certificateUiContext['anamnesis'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-researches">Обследования и исследования</label>
                                <textarea id="cert-extract-researches" name="cert_extract_researches" class="form-control" data-cert-field data-cert-label="Обследования и исследования" rows="4" maxlength="20000" placeholder="Дата, вид исследования, результат" >{{ $certificateUiContext['researches'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-treatment">Проведённое лечение</label>
                                <textarea id="cert-extract-treatment" name="cert_extract_treatment" class="form-control" data-cert-field data-cert-label="Проведённое лечение" rows="3" maxlength="20000" placeholder="Препараты, схемы и продолжительность лечения" >{{ $certificateUiContext['therapy'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-condition">Состояние пациента</label>
                                <textarea id="cert-extract-condition" name="cert_extract_condition" class="form-control" data-cert-field data-cert-label="Состояние пациента" rows="3" maxlength="20000" placeholder="Состояние на момент составления выписки" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-extract-recommendations">Рекомендации</label>
                                <textarea id="cert-extract-recommendations" name="cert_extract_recommendations" class="form-control" data-cert-field data-cert-label="Рекомендации" rows="3" maxlength="20000" placeholder="Дальнейшее наблюдение, обследования и лечение" ></textarea>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="certExtractModalPreview" class="mc-cert-preview" data-cert-preview-panel hidden>
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
