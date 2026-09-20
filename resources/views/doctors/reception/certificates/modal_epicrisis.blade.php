<div class="modal fade mc-cert-modal" id="certEpicrisisModal" tabindex="-1"
     aria-labelledby="certEpicrisisModalTitle" aria-describedby="certEpicrisisModalDescription" aria-hidden="true"
     data-cert-modal data-cert-title="Эпикриз"
     data-cert-patient="{{ $certificateUiContext['patient_name'] }}"
     data-cert-birth="{{ $certificateUiContext['birth_date'] }}"
     data-cert-card="{{ $certificateUiContext['card_number'] }}"
     data-cert-ai-url="{{ route('doctors.patients.analyze.anamnesis') }}"
     data-cert-ai-mode="epicrisis"
     data-cert-patient-id="{{ $patient->id }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mc-cert-modal__heading">
                    <span class="mc-certificates__icon"><i class="bi bi-journal-medical" aria-hidden="true"></i></span>
                    <div>
                        <div class="mc-certificates__eyebrow">Предварительная форма</div>
                        <h2 class="modal-title" id="certEpicrisisModalTitle">Эпикриз</h2>
                        <p id="certEpicrisisModalDescription">Макет печатной формы без изменения существующих записей</p>
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
                <section class="mc-cert-section mb-3" aria-labelledby="certEpicrisisAiTitle">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="mc-certificates__eyebrow">Способ заполнения</div>
                            <h3 id="certEpicrisisAiTitle" class="mb-1">Сформировать по всей медицинской карте</h3>
                            <p class="text-muted small mb-0">
                                ИИ учитывает выбранный вид эпикриза и анализирует релевантные анамнезы, предыдущие
                                эпикризы, симптомы, динамику состояния, лечение, обследования и результаты тестов.
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
                        <span class="badge text-bg-light border">Динамика</span>
                        <span class="badge text-bg-light border">Терапия</span>
                        <span class="badge text-bg-light border">Анализы</span>
                        <span class="badge text-bg-light border">Исследования</span>
                        <span class="badge text-bg-light border">Тесты</span>
                    </div>
                    <div class="small text-muted mt-2" data-cert-ai-status>
                        Выберите вид эпикриза ниже. Поля можно заполнить вручную либо сформировать черновик ИИ и затем отредактировать.
                    </div>
                </section>

                <div class="mc-cert-mode" role="group" aria-label="Режим просмотра">
                    <button type="button" class="is-active" data-cert-edit aria-pressed="true" aria-controls="certEpicrisisModalEditor">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Заполнение
                    </button>
                    <button type="button" data-cert-preview aria-pressed="false" aria-controls="certEpicrisisModalPreview">
                        <i class="bi bi-eye" aria-hidden="true"></i> Предпросмотр
                    </button>
                </div>
                <div class="alert alert-danger" data-cert-error role="alert" hidden></div>
                <div id="certEpicrisisModalEditor" data-cert-editor>
                    <section class="mc-cert-section" aria-labelledby="certEpicrisisModalDetailsTitle">
                        <h3 id="certEpicrisisModalDetailsTitle">Реквизиты документа</h3>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="cert-epicrisis-date">Дата документа</label>
                                <input id="cert-epicrisis-date" name="cert_epicrisis_issued_date" class="form-control" type="date"
                                       value="{{ $certificateUiContext['issued_date'] }}" data-cert-issued-date required>
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label" for="cert-epicrisis-doctor">Врач</label>
                                <input id="cert-epicrisis-doctor" name="cert_epicrisis_doctor" class="form-control" type="text" maxlength="255"
                                       value="{{ $certificateUiContext['doctor_name'] }}" data-cert-doctor placeholder="ФИО врача">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-organization">Медицинская организация</label>
                                <input id="cert-epicrisis-organization" name="cert_epicrisis_organization" class="form-control" type="text" maxlength="500"
                                       data-cert-organization placeholder="Название медицинской организации">
                            </div>
                        </div>
                    </section>
                    <section class="mc-cert-section" aria-labelledby="certEpicrisisModalContentTitle">
                        <h3 id="certEpicrisisModalContentTitle">Содержание документа</h3>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="mc-cert-inline-note">
                                    <i class="bi bi-journal-check" aria-hidden="true"></i>
                                    <div>Здесь формируется только печатный макет. Сохранённые эпикризы и их редактирование остаются на вкладке «Эпикриз».
                                        <button type="button" class="btn mc-cert-btn-link" data-cert-open-epicrisis>Открыть существующие записи <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-kind">Вид эпикриза</label>
                                <select class="form-select" id="cert-epicrisis-kind" name="cert_epicrisis_kind" data-cert-document-kind>
                                    <option value="primary">Первичный эпикриз</option>
                                    <option value="stage" selected>Этапный эпикриз</option>
                                    <option value="discharge">Выписной эпикриз</option>
                                </select>
                            </div><div class="col-12">
                                <label class="form-label" for="cert-epicrisis-diagnosis">Диагноз</label>
                                <input id="cert-epicrisis-diagnosis" name="cert_epicrisis_diagnosis" class="form-control" data-cert-field data-cert-label="Диагноз" type="text" maxlength="500" value="{{ $certificateUiContext['diagnosis'] }}" placeholder="Код МКБ-10 и формулировка диагноза" data-cert-diagnosis >
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-anamnesis">Анамнез</label>
                                <textarea id="cert-epicrisis-anamnesis" name="cert_epicrisis_anamnesis" class="form-control" data-cert-field data-cert-label="Анамнез" rows="3" maxlength="20000" placeholder="Анамнез и основания для составления эпикриза" >{{ $certificateUiContext['anamnesis'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-condition">Состояние и динамика</label>
                                <textarea id="cert-epicrisis-condition" name="cert_epicrisis_condition" class="form-control" data-cert-field data-cert-label="Состояние и динамика" rows="4" maxlength="20000" placeholder="Динамика клинического состояния" ></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-researches">Результаты обследований</label>
                                <textarea id="cert-epicrisis-researches" name="cert_epicrisis_researches" class="form-control" data-cert-field data-cert-label="Результаты обследований" rows="3" maxlength="20000" placeholder="Проведённые обследования и их результаты" >{{ $certificateUiContext['researches'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-treatment">Проведённое лечение</label>
                                <textarea id="cert-epicrisis-treatment" name="cert_epicrisis_treatment" class="form-control" data-cert-field data-cert-label="Проведённое лечение" rows="3" maxlength="20000" placeholder="Лечение и его результат" >{{ $certificateUiContext['therapy'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-conclusion">Заключение</label>
                                <textarea id="cert-epicrisis-conclusion" name="cert_epicrisis_conclusion" class="form-control" data-cert-field data-cert-label="Заключение" rows="3" maxlength="20000" placeholder="Итоги этапа лечения" >{{ $certificateUiContext['latest_epicrisis'] }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cert-epicrisis-recommendations">Рекомендации</label>
                                <textarea id="cert-epicrisis-recommendations" name="cert_epicrisis_recommendations" class="form-control" data-cert-field data-cert-label="Рекомендации" rows="3" maxlength="20000" placeholder="Дальнейший план лечения и наблюдения" ></textarea>
                            </div>
                        </div>
                    </section>
                </div>
                <div id="certEpicrisisModalPreview" class="mc-cert-preview" data-cert-preview-panel hidden>
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
