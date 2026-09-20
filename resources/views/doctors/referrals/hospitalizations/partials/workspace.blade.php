@php
    $isEmergency = $mode === 'emergency';
    $patientState = $selectedPatientContext ?? [
        'has_current_diagnosis' => false,
        'anamneses_count' => 0,
        'prescriptions_count' => 0,
        'is_first_visit' => false,
    ];
    $isFirstVisit = (bool) ($patientState['is_first_visit'] ?? false);
    $hasCurrentDiagnosis = (bool) ($patientState['has_current_diagnosis'] ?? false);
    $hasAnamneses = (int) ($patientState['anamneses_count'] ?? 0) > 0;
    $hasPrescriptions = (int) ($patientState['prescriptions_count'] ?? 0) > 0;
    $hasAnyExistingSource = $hasCurrentDiagnosis || $hasAnamneses || $hasPrescriptions;
@endphp

<header class="hospital-hero">
    <div class="hospital-hero__icon">
        <i class="bi {{ $pageIcon }}"></i>
    </div>

    <div class="hospital-hero__body">
        <div class="hospital-hero__eyebrow">
            <i class="bi bi-hospital"></i>
            Направление пациента в стационар
        </div>
        <h1>{{ $pageTitle }}</h1>
        <p>{{ $pageSubtitle }}</p>

        <div class="hospital-hero__chips">
            <span>
                <i class="bi bi-stars"></i>
                Qwen3 14B — подготовка проекта
            </span>
            <span>
                <i class="bi bi-shield-check"></i>
                MedGemma — независимая проверка
            </span>
            <span>
                <i class="bi bi-pencil-square"></i>
                Ручное подтверждение врача
            </span>
        </div>
    </div>

    <div class="hospital-hero__status {{ $isEmergency ? 'is-emergency' : '' }}">
        <span class="hospital-hero__status-dot"></span>
        {{ $isEmergency ? 'Неотложный сценарий' : 'Плановый сценарий' }}
    </div>
</header>

@if($isEmergency)
    <div class="hospital-safety-banner">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <div>
            <strong>ИИ не заменяет первичную оценку угрозы жизни.</strong>
            При непосредственной опасности сначала действуйте по локальному протоколу экстренной помощи,
            затем оформляйте запись.
        </div>
    </div>
@endif

<div class="hospital-content-grid">
    <section class="hospital-workspace-card">
        <div class="hospital-workspace-card__head">
            <div>
                <div class="hospital-section-eyebrow">Рабочая область</div>
                <h2>Подготовка направления</h2>
                <p>Выберите способ заполнения. Результат ИИ всегда можно отредактировать вручную.</p>
            </div>

            <div class="hospital-workspace-card__badge">
                <i class="bi bi-lock"></i>
                Не отправляется автоматически
            </div>
        </div>

        <div class="hospital-key-fields">
            <section class="hospital-key-card hospital-key-card--destination">
                <div class="hospital-card-title-row">
                    <span class="hospital-card-title-row__icon">
                        <i class="bi bi-geo-alt"></i>
                    </span>
                    <div>
                        <h3>Стационар назначения</h3>
                        <p>Адрес сохраняется в направлении и обязателен перед подготовкой/отправкой документа.</p>
                    </div>
                </div>

                <div class="hospital-form-grid hospital-form-grid--2">
                    <div class="hospital-field">
                        <label class="hospital-label" for="destinationOrganization">Медицинская организация</label>
                        <input id="destinationOrganization"
                               type="text"
                               name="destination_organization"
                               form="hospitalReferralForm"
                               class="form-control hospital-control"
                               maxlength="255"
                               value="{{ old('destination_organization') }}"
                               placeholder="Название стационара">
                    </div>

                    <div class="hospital-field">
                        <label class="hospital-label" for="destinationDepartment">Профиль / отделение</label>
                        <input id="destinationDepartment"
                               type="text"
                               name="department"
                               form="hospitalReferralForm"
                               class="form-control hospital-control"
                               maxlength="255"
                               value="{{ old('department') }}"
                               placeholder="Психиатрическое, неврологическое и т. п.">
                    </div>

                    <div class="hospital-field hospital-field--full">
                        <label class="hospital-label" for="destinationAddress">
                            Адрес стационара <span class="hospital-required-mark">*</span>
                        </label>
                        <input id="destinationAddress"
                               type="text"
                               name="destination_address"
                               form="hospitalReferralForm"
                               class="form-control hospital-control"
                               maxlength="500"
                               value="{{ old('destination_address') }}"
                               placeholder="Город, улица, дом, корпус / приёмное отделение"
                               autocomplete="street-address">
                        <div class="hospital-field-hint">
                            Для черновика адрес можно оставить пустым. Для статусов «Подготовлено», «Направлено» и «Госпитализирован» он обязателен.
                        </div>
                    </div>
                </div>
            </section>

            <section class="hospital-key-card hospital-key-card--diagnosis">
                <div class="hospital-card-title-row">
                    <span class="hospital-card-title-row__icon">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </span>
                    <div>
                        <h3>Предварительный диагноз</h3>
                        <p>
                            При первичном обращении заполняется после анализа анамнеза либо вручную врачом.
                            Установленный диагноз пациента при этом не изменяется.
                        </p>
                    </div>
                </div>

                <div class="hospital-preliminary-state" data-preliminary-state>
                    @if($selectedPatient && $hasCurrentDiagnosis)
                        <i class="bi bi-info-circle"></i>
                        <span>
                            В карте уже есть текущий диагноз:
                            <strong>{{ data_get($selectedPatient, 'diagnose.code') }}</strong>
                            {{ data_get($selectedPatient, 'diagnose.title') ?: data_get($selectedPatient, 'diagnose.name') }}.
                            Он используется только как контекст.
                        </span>
                    @elseif($selectedPatient)
                        <i class="bi bi-stars"></i>
                        <span>Текущего диагноза в карте нет. После анализа анамнеза ИИ предложит предварительный диагноз.</span>
                    @else
                        <i class="bi bi-info-circle"></i>
                        <span>Выберите пациента. Предварительный диагноз можно получить из анамнеза или ввести вручную.</span>
                    @endif
                </div>

                <div class="hospital-form-grid hospital-form-grid--3">
                    <div class="hospital-field">
                        <label class="hospital-label" for="diagnosisCode">Код</label>
                        <input id="diagnosisCode"
                               type="text"
                               name="diagnosis_code"
                               form="hospitalReferralForm"
                               class="form-control hospital-control"
                               maxlength="32"
                               value="{{ old('diagnosis_code') }}"
                               placeholder="Например, F32.2"
                               data-preliminary-diagnosis-code>
                    </div>

                    <div class="hospital-field hospital-field--2">
                        <label class="hospital-label" for="diagnosisTitle">Название предварительного диагноза</label>
                        <input id="diagnosisTitle"
                               type="text"
                               name="diagnosis_title"
                               form="hospitalReferralForm"
                               class="form-control hospital-control"
                               maxlength="255"
                               value="{{ old('diagnosis_title') }}"
                               placeholder="Будет заполнено после анализа или врачом вручную"
                               data-preliminary-diagnosis-title>
                    </div>
                </div>

                <div class="hospital-preliminary-ai-note d-none" data-preliminary-ai-note>
                    <i class="bi bi-cpu"></i>
                    Заполнено по результату ИИ. Проверьте формулировку перед сохранением.
                </div>
            </section>
        </div>

        <div class="hospital-tabs" role="tablist" aria-label="Способ заполнения">
            <button type="button"
                    class="hospital-tab active"
                    data-hospital-tab="ai"
                    aria-selected="true">
                <i class="bi bi-cpu"></i>
                ИИ-анализ
            </button>

            <button type="button"
                    class="hospital-tab"
                    data-hospital-tab="manual"
                    aria-selected="false">
                <i class="bi bi-pencil-square"></i>
                Вручную
            </button>
        </div>

        <div class="hospital-tab-panel active" data-hospital-panel="ai">
            <div class="hospital-ai-grid">
                <div class="hospital-ai-input-card">
                    <div class="hospital-card-title-row">
                        <span class="hospital-card-title-row__icon">
                            <i class="bi bi-database-check"></i>
                        </span>
                        <div>
                            <h3>Что анализировать</h3>
                            <p data-source-description>
                                {{ $isFirstVisit
                                    ? 'Первичное обращение: опишите анамнез и текущее состояние пациента.'
                                    : 'Отметьте доступные записи пациента и при необходимости добавьте текущую клиническую ситуацию.' }}
                            </p>
                        </div>
                    </div>

                    <div class="hospital-first-visit-notice {{ ($selectedPatient && $isFirstVisit) ? '' : 'd-none' }}"
                         data-first-visit-notice>
                        <span class="hospital-first-visit-notice__icon">
                            <i class="bi bi-person-plus"></i>
                        </span>
                        <div>
                            <strong>Первичное обращение</strong>
                            <p>
                                В карте пока нет диагноза, предыдущих анамнезов и назначений.
                                Поэтому источники с checkbox'ами скрыты: анализ выполняется по введённому ниже анамнезу.
                            </p>
                        </div>
                    </div>

                    <div class="hospital-source-list {{ ($selectedPatient && !$hasAnyExistingSource) ? 'd-none' : '' }}"
                         data-ai-source-list>
                        <label class="hospital-source-option {{ ($selectedPatient && !$hasCurrentDiagnosis) ? 'd-none' : '' }}"
                               data-ai-source-option="diagnosis">
                            <input type="checkbox"
                                   data-ai-source="diagnosis"
                                   @checked(!$selectedPatient || $hasCurrentDiagnosis)>
                            <span class="hospital-source-option__check"><i class="bi bi-check"></i></span>
                            <span>
                                <strong>Текущий диагноз</strong>
                                <small>Только как клинический контекст; предварительный диагноз формируется отдельно</small>
                            </span>
                        </label>

                        <label class="hospital-source-option {{ ($selectedPatient && !$hasAnamneses) ? 'd-none' : '' }}"
                               data-ai-source-option="anamneses">
                            <input type="checkbox"
                                   data-ai-source="anamneses"
                                   @checked(!$selectedPatient || $hasAnamneses)>
                            <span class="hospital-source-option__check"><i class="bi bi-check"></i></span>
                            <span>
                                <strong>Последние записи анамнеза</strong>
                                <small>До пяти последних клинических записей</small>
                            </span>
                        </label>

                        <label class="hospital-source-option {{ ($selectedPatient && !$hasPrescriptions) ? 'd-none' : '' }}"
                               data-ai-source-option="prescriptions">
                            <input type="checkbox" data-ai-source="prescriptions">
                            <span class="hospital-source-option__check"><i class="bi bi-check"></i></span>
                            <span>
                                <strong>Последние назначения</strong>
                                <small>Используются только как дополнительный клинический контекст</small>
                            </span>
                        </label>
                    </div>

                    <label class="form-label hospital-label mt-3" for="hospitalClinicalText" data-clinical-label>
                        {{ ($selectedPatient && $isFirstVisit)
                            ? 'Анамнез и текущее состояние'
                            : 'Текущее состояние или дополнительная запись' }}
                    </label>
                    <textarea id="hospitalClinicalText"
                              class="form-control hospital-textarea"
                              rows="9"
                              maxlength="24000"
                              placeholder="Опишите жалобы, анамнез, динамику, психический и соматический статус, риски, результаты осмотра и цель направления..."></textarea>

                    <div class="hospital-textarea-footer">
                        <span>
                            <i class="bi bi-info-circle"></i>
                            При первичном обращении именно этот текст является основным материалом для предварительного диагноза
                        </span>
                        <span data-clinical-counter>0 / 24000</span>
                    </div>

                    <button type="button" class="hospital-analyze-button" data-hospital-analyze>
                        <span class="hospital-analyze-button__idle">
                            <i class="bi bi-cpu"></i>
                            Проанализировать записи
                        </span>
                        <span class="hospital-analyze-button__loading">
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            Qwen и MedGemma анализируют данные…
                        </span>
                    </button>
                </div>

                <div class="hospital-ai-result-card" data-ai-result>
                    <div class="hospital-ai-empty">
                        <div class="hospital-ai-empty__icon">
                            <i class="bi bi-stars"></i>
                        </div>
                        <h3>Результат появится здесь</h3>
                        <p>
                            Сначала Qwen подготовит проект и предварительный диагноз, затем MedGemma отдельно
                            перепроверит клиническую согласованность, срочность и недостающие сведения.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="hospital-tab-panel" data-hospital-panel="manual">
            @include('doctors.referrals.hospitalizations.partials.form', [
                'mode' => $mode,
            ])
        </div>
    </section>

    @include('doctors.referrals.hospitalizations.partials.history')
</div>
