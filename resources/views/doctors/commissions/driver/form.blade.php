@extends('doctors.prescriptions')

@section('title', $pageTitle)
@section('prescription-title', $pageTitle)

@section('sub-main')
    @include('doctors.commissions.driver.partials.styles')

    @php
        $value = static function (string $name, mixed $default = '') use ($formValues): mixed {
            return old($name, data_get($formValues, $name, $default));
        };
        $selectedCategories = collect(old('vehicle_categories', data_get($formValues, 'vehicle_categories', [])))
            ->map(fn ($item) => (string) $item)->all();
        $selectedRiskFlags = collect(old('risk_flags', data_get($formValues, 'risk_flags', [])))
            ->map(fn ($item) => (string) $item)->all();
        $currentStatus = (string) $value('status', 'draft');
        $currentResult = (string) $value('result');
        $currentContraAssessment = (string) $value('contraindication_assessment');
        $patientName = trim((string) data_get($initialPatient, 'full_name'));
        $patientMeta = collect([
            data_get($initialPatient, 'birth_at_display') ? 'д. р. ' . data_get($initialPatient, 'birth_at_display') : null,
            data_get($initialPatient, 'age') !== null ? data_get($initialPatient, 'age') . ' лет' : null,
            data_get($initialPatient, 'phone'),
        ])->filter()->implode(' · ');
        $patientDiagnosis = trim((string) data_get($initialPatient, 'diagnosis_label')) ?: 'Диагноз в карте не указан';
        $commissionResultValues = [
            \App\Models\DriverPsychiatricExamination::RESULT_COMMISSION_NO_CONTRAINDICATIONS,
            \App\Models\DriverPsychiatricExamination::RESULT_COMMISSION_CONTRAINDICATIONS,
        ];
    @endphp

    <main id="driverCommissionFormRoot"
          class="driver-page"
          data-patient-search-url="{{ route('api.patients.search') }}"
          data-is-editing="{{ $isEditing ? '1' : '0' }}">
        <script id="driverInitialPatient" type="application/json">@json($initialPatient)</script>

        <div class="driver-container">
            <header class="driver-hero">
                <div class="driver-hero__main">
                    <a class="driver-back" href="{{ route('doctors.commissions.driver.index') }}" title="Назад в журнал">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="driver-hero__icon"><i class="bi bi-car-front"></i></div>
                    <div class="driver-hero__copy">
                        <div class="driver-eyebrow">Водительская комиссия · психиатр</div>
                        <h1>{{ $headingTitle }}</h1>
                        <p>Психиатрическое обследование водителя или кандидата в водители. Наличие диагноза само по себе не определяет итоговое решение.</p>
                    </div>
                </div>
                <div class="driver-hero__actions">
                    <a class="driver-button driver-button--ghost" target="_blank" data-guarded-print data-blank-print
                       data-blank-print-base="{{ route('doctors.commissions.driver.print-blank') }}"
                       href="{{ route('doctors.commissions.driver.print-blank', array_filter([
                           'patient_id' => $selectedPatientId,
                           'contraindication_assessment' => $currentContraAssessment ?: null,
                       ])) }}">
                        <i class="bi bi-file-earmark-text"></i> Пустой бланк
                    </a>
                    <button class="driver-button driver-button--primary" type="submit" form="driverCommissionForm" data-submit-button>
                        <i class="bi bi-check2-circle"></i> Сохранить
                    </button>
                    @if($isEditing)
                        <a class="driver-button driver-button--secondary" target="_blank" data-guarded-print
                           href="{{ route('doctors.commissions.driver.print', $document) }}">
                            <i class="bi bi-printer"></i> Печать
                        </a>
                    @endif
                </div>
            </header>

            @if($errors->any())
                <div class="driver-alert driver-alert--danger">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div>
                        <strong>Проверьте заполнение формы</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form id="driverCommissionForm"
                  method="POST"
                  action="{{ $isEditing
                      ? route('doctors.commissions.driver.update', $document)
                      : route('doctors.commissions.driver.store') }}"
                  data-driver-form>
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                <div class="driver-form-layout">
                    <div class="driver-form-main">
                        <section class="driver-card" id="driverSectionPatient">
                            <div class="driver-card__head">
                                <span class="driver-card__icon"><i class="bi bi-person-vcard"></i></span>
                                <div>
                                    <h2>Пациент и параметры обследования</h2>
                                    <p>Выберите пациента, цель обращения и категории транспортных средств.</p>
                                </div>
                                <span class="driver-step">01</span>
                            </div>

                            <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}" data-patient-id>

                            <div class="driver-field driver-patient-search">
                                <label for="driverPatientSearch">Пациент <span class="required">*</span></label>
                                <div class="driver-control-wrap">
                                    <i class="bi bi-search"></i>
                                    <input id="driverPatientSearch"
                                           class="{{ $errors->has('patient_id') ? 'is-invalid' : '' }}"
                                           type="text"
                                           value="{{ $patientName }}"
                                           autocomplete="off"
                                           placeholder="Начните вводить фамилию, имя или отчество"
                                           data-patient-search>
                                    <button class="driver-clear {{ $selectedPatientId ? '' : 'd-none' }}"
                                            type="button" title="Снять выбор" data-patient-clear>
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="driver-suggestions d-none" data-patient-suggestions></div>
                                @error('patient_id')<small class="driver-field-error">{{ $message }}</small>@enderror
                            </div>

                            <div class="driver-selected-patient">
                                <div class="driver-selected-box" data-patient-card>
                                    <span class="driver-selected-box__icon"><i class="bi bi-person-check"></i></span>
                                    <div>
                                        <strong data-patient-name>{{ $patientName ?: 'Пациент не выбран' }}</strong>
                                        <span data-patient-meta>{{ $patientMeta ?: 'Выберите пациента из списка' }}</span>
                                    </div>
                                </div>
                                <div class="driver-selected-box">
                                    <span class="driver-selected-box__icon"><i class="bi bi-activity"></i></span>
                                    <div>
                                        <strong>Диагноз в медицинской карте</strong>
                                        <span data-patient-diagnosis>{{ $patientDiagnosis }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="driver-alert driver-alert--warning" style="margin-top:10px;margin-bottom:0">
                                <i class="bi bi-info-circle-fill"></i>
                                <span><strong>Важно:</strong> наличие F-диагноза в карте не является автоматическим запретом. Врач оценивает актуальное состояние, течение расстройства и наличие клинически значимых противопоказаний.</span>
                            </div>

                            <article class="driver-f-contraindications" data-f-contraindications>
                                <div class="driver-f-contraindications__head">
                                    <div>
                                        <strong><i class="bi bi-shield-exclamation"></i> F-коды из перечня медицинских противопоказаний</strong>
                                        <small>Распоряжение Правительства РФ № 892-р, действующая редакция от 19.08.2026.</small>
                                    </div>
                                    <span class="driver-f-contraindications__badge">МКБ-10</span>
                                </div>

                                @php
                                    $fDiagnosisCode = trim((string) data_get($initialPatient, 'diagnosis_code'));
                                    $fMatch = $fContraindicationMatch ?? null;
                                @endphp

                                <div class="driver-f-contraindications__status {{ $fMatch ? ($fMatch['specialist'] === 'psychiatrist_narcologist' ? 'is-narcology' : 'is-match') : ($fDiagnosisCode !== '' ? 'is-clear' : 'is-empty') }}"
                                     data-f-contra-status>
                                    @if($fMatch)
                                        <i class="bi {{ $fMatch['specialist'] === 'psychiatrist_narcologist' ? 'bi-capsule' : 'bi-exclamation-triangle-fill' }}"></i>
                                        <span>
                                            <strong>{{ $fDiagnosisCode }} входит в перечень:</strong>
                                            {{ $fMatch['title'] }}. {{ $fMatch['condition'] }}
                                        </span>
                                    @elseif($fDiagnosisCode !== '')
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span><strong>{{ $fDiagnosisCode }} не найден в F-перечне № 892-р.</strong> Это не отменяет оценку иных медицинских противопоказаний.</span>
                                    @else
                                        <i class="bi bi-info-circle"></i>
                                        <span>После выбора пациента система сопоставит код диагноза с действующим перечнем.</span>
                                    @endif
                                </div>

                                <details class="driver-f-contraindications__details" {{ $fMatch ? 'open' : '' }}>
                                    <summary>Показать полный перечень F-кодов</summary>
                                    <div class="driver-f-contraindications__groups">
                                        @foreach($fContraindicationGroups as $group)
                                            <div class="driver-f-group {{ ($group['specialist'] ?? '') === 'psychiatrist_narcologist' ? 'is-narcology' : '' }}">
                                                <div class="driver-f-group__title">
                                                    <span>{{ $group['section'] }}.{{ $group['number'] }}</span>
                                                    <strong>{{ $group['title'] }}</strong>
                                                </div>
                                                <div class="driver-f-group__codes">
                                                    @foreach($group['codes'] as $code)
                                                        <code>{{ $code }}</code>
                                                    @endforeach
                                                </div>
                                                <small>{{ $group['condition'] }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="driver-f-contraindications__footnote">
                                        <i class="bi bi-info-circle"></i>
                                        Раздел I не означает автоматический запрет по одному F-коду: требуется установленный распоряжением характер течения. Раздел II (F10–F19, кроме F17) относится к обследованию психиатром-наркологом.
                                    </div>
                                </details>
                            </article>

                            <div class="driver-contra-assessment {{ $fMatch ? '' : 'd-none' }}" data-contra-assessment-block>
                                <div class="driver-section-caption" style="margin-top:0">
                                    <i class="bi bi-shield-check"></i> Клиническая оценка противопоказания
                                </div>
                                <p class="driver-contra-assessment__intro">
                                    Код входит в перечень № 892-р. Для продолжения работы врач должен указать,
                                    выполнено ли именно нормативное условие противопоказания.
                                </p>
                                <div class="driver-contra-assessment__grid">
                                    @foreach($contraindicationAssessmentOptions as $optionValue => $label)
                                        <label class="driver-contra-assessment__option {{ $optionValue === \App\Models\DriverPsychiatricExamination::CONTRA_ASSESSMENT_CONFIRMED ? 'is-danger' : '' }}">
                                            <input type="radio" name="contraindication_assessment"
                                                   value="{{ $optionValue }}"
                                                   @checked($currentContraAssessment === $optionValue)
                                                   data-contra-assessment>
                                            <span>
                                                <strong>{{ $label }}</strong>
                                                <small>
                                                    @if($optionValue === \App\Models\DriverPsychiatricExamination::CONTRA_ASSESSMENT_NOT_CONFIRMED)
                                                        Течение и выраженность не достигают условия, указанного в перечне.
                                                    @elseif($optionValue === \App\Models\DriverPsychiatricExamination::CONTRA_ASSESSMENT_COMMISSION_REQUIRED)
                                                        Обычное положительное заключение пока не оформляется; требуется ВК / профильный специалист.
                                                    @else
                                                        Водительский документ блокируется: сохранить или распечатать его нельзя.
                                                    @endif
                                                </small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('contraindication_assessment')<small class="driver-field-error">{{ $message }}</small>@enderror
                            </div>

                            <div class="driver-hard-lock d-none" data-driver-hard-lock role="alert">
                                <i class="bi bi-slash-circle-fill"></i>
                                <div>
                                    <strong data-driver-hard-lock-title>Водительский документ заблокирован</strong>
                                    <span data-driver-hard-lock-text>Сохранение и печать недоступны.</span>
                                </div>
                            </div>

                            <div class="driver-section-caption"><i class="bi bi-calendar3"></i> Параметры обследования</div>
                            <div class="driver-grid driver-grid--3">
                                <div class="driver-field">
                                    <label for="driverExaminationDate">Дата обследования <span class="required">*</span></label>
                                    <input id="driverExaminationDate" type="date" name="examination_date" required
                                           value="{{ $value('examination_date') }}">
                                </div>
                                <div class="driver-field">
                                    <label for="driverPurpose">Цель обращения <span class="required">*</span></label>
                                    <select id="driverPurpose" name="purpose" required>
                                        @foreach($purposeOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('purpose') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="driver-field">
                                    <label for="driverStatus">Статус <span class="required">*</span></label>
                                    <select id="driverStatus" name="status" required data-status-select>
                                        @foreach($statusOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $currentStatus === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="driver-section-caption"><i class="bi bi-card-checklist"></i> Категории транспортных средств</div>
                            <div class="driver-check-grid">
                                @foreach($vehicleCategoryOptions as $optionValue => $label)
                                    @php
                                        $categoryDescription = (string) ($vehicleCategoryDescriptions[$optionValue] ?? '');
                                    @endphp
                                    <label class="driver-check-card"
                                           data-driver-tooltip="{{ $categoryDescription }}"
                                           aria-label="Категория {{ $label }}: {{ $categoryDescription }}">
                                        <input type="checkbox" name="vehicle_categories[]" value="{{ $optionValue }}"
                                               @checked(in_array((string) $optionValue, $selectedCategories, true)) data-category-checkbox>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('vehicle_categories')<small class="driver-field-error">{{ $message }}</small>@enderror
                        </section>

                        <section class="driver-card" id="driverSectionHistory">
                            <div class="driver-card__head">
                                <span class="driver-card__icon"><i class="bi bi-journal-medical"></i></span>
                                <div>
                                    <h2>Анамнез и сведения из медицинской карты</h2>
                                    <p>Фиксируем только данные, значимые для психиатрического обследования.</p>
                                </div>
                                <span class="driver-step">02</span>
                            </div>

                            <div class="driver-grid driver-grid--2">
                                <div class="driver-field driver-field--full">
                                    <label for="driverComplaints">Жалобы</label>
                                    <textarea id="driverComplaints" name="complaints" rows="3"
                                              placeholder="Жалобы на момент обследования; при отсутствии — укажите это явно">{{ $value('complaints') }}</textarea>
                                </div>
                                <div class="driver-field">
                                    <label for="driverDiseaseHistory">Анамнез текущего состояния</label>
                                    <textarea id="driverDiseaseHistory" name="disease_history" rows="4"
                                              placeholder="Динамика состояния, обострения, значимые эпизоды...">{{ $value('disease_history') }}</textarea>
                                </div>
                                <div class="driver-field">
                                    <label for="driverPsychiatricHistory">Психиатрический анамнез</label>
                                    <textarea id="driverPsychiatricHistory" name="psychiatric_history" rows="4"
                                              placeholder="Ранее установленные расстройства, наблюдение, ремиссии, обращения...">{{ $value('psychiatric_history') }}</textarea>
                                </div>
                                <div class="driver-field">
                                    <label for="driverHospitalizations">Госпитализации</label>
                                    <textarea id="driverHospitalizations" name="hospitalizations" rows="3"
                                              placeholder="Психиатрические госпитализации и их причины">{{ $value('hospitalizations') }}</textarea>
                                </div>
                                <div class="driver-field">
                                    <label for="driverTreatment">Текущая терапия</label>
                                    <textarea id="driverTreatment" name="current_treatment" rows="3"
                                              placeholder="Психотропная терапия, приверженность, значимые нежелательные эффекты">{{ $value('current_treatment') }}</textarea>
                                </div>
                            </div>
                        </section>

                        <section class="driver-card" id="driverSectionExam">
                            <div class="driver-card__head">
                                <span class="driver-card__icon"><i class="bi bi-person-lines-fill"></i></span>
                                <div>
                                    <h2>Психиатрическое обследование</h2>
                                    <p>Психический статус и признаки, требующие дополнительного освидетельствования.</p>
                                </div>
                                <span class="driver-step">03</span>
                            </div>

                            <div class="driver-grid driver-grid--2">
                                <div class="driver-field driver-field--full">
                                    <label for="driverMentalStatus">Психический статус <span class="required" data-prepared-mark>*</span></label>
                                    <textarea id="driverMentalStatus" name="mental_status" rows="5"
                                              placeholder="Сознание, ориентировка, контакт, мышление, восприятие, аффект, воля, критика, поведение...">{{ $value('mental_status') }}</textarea>
                                </div>
                                <div class="driver-field driver-field--full">
                                    <label for="driverCognitiveBehavior">Когнитивные и поведенческие особенности</label>
                                    <textarea id="driverCognitiveBehavior" name="cognitive_behavioral_features" rows="3"
                                              placeholder="Внимание, память, исполнительные функции, импульсивность, особенности поведения...">{{ $value('cognitive_behavioral_features') }}</textarea>
                                </div>
                            </div>

                            <div class="driver-section-caption"><i class="bi bi-exclamation-triangle"></i> Настораживающие признаки</div>
                            <div class="driver-risk-grid">
                                @foreach($riskFlagOptions as $optionValue => $label)
                                    <label class="driver-risk-card">
                                        <input type="checkbox" name="risk_flags[]" value="{{ $optionValue }}"
                                               @checked(in_array((string) $optionValue, $selectedRiskFlags, true)) data-risk-checkbox>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <small style="display:block;margin-top:7px;color:#8292a8;font-size:10px">Если отмечен клинически значимый признак, форма не позволит оформить обычное заключение «противопоказания не выявлены» без сценария психиатрической ВК.</small>

                            <div class="driver-alert driver-alert--neutral" style="margin-top:10px;margin-bottom:0">
                                <i class="bi bi-capsule"></i>
                                <span><strong>Психиатр-нарколог — отдельный этап.</strong> Оценка противопоказаний, связанных с употреблением алкоголя, наркотических средств и иных психоактивных веществ, не подменяется этим модулем и проводится в рамках обследования врачом-психиатром-наркологом.</span>
                            </div>

                            <div class="driver-section-caption"><i class="bi bi-shield-check"></i> Проверка медицинской документации</div>
                            <input type="hidden" name="medical_record_reviewed" value="0">
                            <label class="driver-inline-check">
                                <input type="checkbox" name="medical_record_reviewed" value="1"
                                       @checked((bool) old('medical_record_reviewed', data_get($formValues, 'medical_record_reviewed', false)))
                                       data-record-reviewed>
                                <span><strong>Медицинская карта проверена.</strong> Учтены сведения о ранее установленных психиатрических диагнозах, лечении и динамике; диагноз сам по себе не использован как автоматическое основание для отказа.</span>
                            </label>
                            @error('medical_record_reviewed')<small class="driver-field-error">{{ $message }}</small>@enderror
                        </section>

                        <section class="driver-card" id="driverSectionConclusion">
                            <div class="driver-card__head">
                                <span class="driver-card__icon"><i class="bi bi-clipboard2-check"></i></span>
                                <div>
                                    <h2>Результат и заключение</h2>
                                    <p>Решение психиатра либо итог психиатрической врачебной комиссии.</p>
                                </div>
                                <span class="driver-step">04</span>
                            </div>

                            <div class="driver-grid driver-grid--2">
                                <div class="driver-field driver-field--full">
                                    <label for="driverResult">Результат обследования <span class="required" data-prepared-mark>*</span></label>
                                    <select id="driverResult" name="result" data-result-select>
                                        <option value="">Решение пока не принято</option>
                                        @foreach($resultOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $currentResult === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('result')<small class="driver-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="driver-field driver-field--full">
                                    <label for="driverRationale">Клиническое обоснование</label>
                                    <textarea id="driverRationale" name="result_rationale" rows="3"
                                              placeholder="Почему выбран данный результат; особенно важно при направлении на ВК">{{ $value('result_rationale') }}</textarea>
                                    @error('result_rationale')<small class="driver-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="driver-result-note">
                                При выявлении симптомов или синдромов, требующих дополнительного психиатрического освидетельствования, выбирайте направление на врачебную комиссию. Итоговое медицинское заключение 003-В/у данным модулем не оформляется.
                            </div>

                            <div class="{{ in_array($currentResult, $commissionResultValues, true) ? '' : 'd-none' }}" data-commission-fields>
                                <div class="driver-section-caption"><i class="bi bi-people"></i> Решение психиатрической врачебной комиссии</div>
                                <div class="driver-grid driver-grid--2">
                                    <div class="driver-field">
                                        <label for="driverCommissionProtocol">Номер протокола ВК</label>
                                        <input id="driverCommissionProtocol" name="commission_protocol_number"
                                               value="{{ $value('commission_protocol_number') }}">
                                        @error('commission_protocol_number')<small class="driver-field-error">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="driver-field">
                                        <label for="driverCommissionDate">Дата протокола ВК</label>
                                        <input id="driverCommissionDate" type="date" name="commission_protocol_date"
                                               value="{{ $value('commission_protocol_date') }}">
                                        @error('commission_protocol_date')<small class="driver-field-error">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="driver-field driver-field--full">
                                        <label for="driverCommissionDecision">Решение ВК</label>
                                        <textarea id="driverCommissionDecision" name="commission_decision" rows="3">{{ $value('commission_decision') }}</textarea>
                                        @error('commission_decision')<small class="driver-field-error">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="{{ $currentResult === \App\Models\DriverPsychiatricExamination::RESULT_REFUSED_COMMISSION ? '' : 'd-none' }}" data-refusal-fields>
                                <div class="driver-section-caption"><i class="bi bi-x-octagon"></i> Отказ от ВК</div>
                                <input type="hidden" name="commission_refusal" value="0">
                                <label class="driver-inline-check">
                                    <input type="checkbox" name="commission_refusal" value="1"
                                           @checked((bool) old('commission_refusal', data_get($formValues, 'commission_refusal', false)))>
                                    <span>Факт отказа пациента от психиатрического освидетельствования врачебной комиссией зафиксирован.</span>
                                </label>
                            </div>

                            <div class="driver-section-caption"><i class="bi bi-file-earmark-text"></i> Текст заключения</div>
                            <div class="driver-field">
                                <textarea id="driverConclusion" name="conclusion_text" rows="4"
                                          placeholder="Итоговый текст документа для печати">{{ $value('conclusion_text') }}</textarea>
                                @error('conclusion_text')<small class="driver-field-error">{{ $message }}</small>@enderror
                            </div>
                        </section>
                    </div>

                    <aside class="driver-sidebar">
                        <section class="driver-sidebar-card">
                            <h3>Готовность</h3>
                            <p><strong data-readiness-percent>0%</strong> ключевых условий для оформления выполнено.</p>
                            <div class="driver-readiness">
                                @foreach([
                                    ['patient', 'Пациент выбран'],
                                    ['categories', 'Категории ТС'],
                                    ['mental', 'Психический статус'],
                                    ['review', 'Медкарта проверена'],
                                    ['result', 'Результат выбран'],
                                    ['conclusion', 'Заключение заполнено'],
                                ] as [$key, $label])
                                    <div class="driver-readiness__item" data-readiness-item="{{ $key }}">
                                        <span class="driver-readiness__dot"><i class="bi bi-check-lg"></i></span>
                                        <span>{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="driver-sidebar-card">
                            <h3>Логика документа</h3>
                            <p>Черновик можно сохранять в процессе обследования. При статусе «Оформлено» обязательные клинические сведения и итоговое решение проверяются и в браузере, и на сервере.</p>
                        </section>

                        <section class="driver-sidebar-card driver-sidebar-actions">
                            <h3>Сохранение</h3>
                            <p>Текущий статус: <strong data-save-status>{{ $statusOptions[$currentStatus] ?? $currentStatus }}</strong>.</p>
                            <button class="driver-button driver-button--primary" type="submit" data-submit-button>
                                <i class="bi bi-check2-circle"></i> Сохранить
                            </button>
                            @if($isEditing)
                                <a class="driver-button driver-button--secondary" target="_blank" data-guarded-print
                                   href="{{ route('doctors.commissions.driver.print', $document) }}">
                                    <i class="bi bi-printer"></i> Печатная форма
                                </a>
                            @endif
                            <a class="driver-button driver-button--ghost" href="{{ route('doctors.commissions.driver.index') }}">
                                <i class="bi bi-arrow-left"></i> В журнал
                            </a>
                        </section>
                    </aside>
                </div>
            </form>
        </div>
    </main>

    <script>
        (() => {
            const root = document.getElementById('driverCommissionFormRoot');
            if (!root) return;

            const form = root.querySelector('[data-driver-form]');
            const patientSearch = root.querySelector('[data-patient-search]');
            const patientId = root.querySelector('[data-patient-id]');
            const patientClear = root.querySelector('[data-patient-clear]');
            const patientSuggestions = root.querySelector('[data-patient-suggestions]');
            const patientName = root.querySelector('[data-patient-name]');
            const patientMeta = root.querySelector('[data-patient-meta]');
            const patientDiagnosis = root.querySelector('[data-patient-diagnosis]');
            const statusSelect = root.querySelector('[data-status-select]');
            const resultSelect = root.querySelector('[data-result-select]');
            const commissionFields = root.querySelector('[data-commission-fields]');
            const refusalFields = root.querySelector('[data-refusal-fields]');
            const conclusionInput = root.querySelector('#driverConclusion');
            const mentalStatusInput = root.querySelector('#driverMentalStatus');
            const recordReviewed = root.querySelector('[data-record-reviewed]');
            const categoryCheckboxes = [...root.querySelectorAll('[data-category-checkbox]')];
            const riskCheckboxes = [...root.querySelectorAll('[data-risk-checkbox]')];
            const submitButtons = [...root.querySelectorAll('[data-submit-button]')];
            const searchUrl = root.dataset.patientSearchUrl || '';
            const fContraindicationGroups = @json(array_values($fContraindicationGroups));
            const fContraStatus = root.querySelector('[data-f-contra-status]');
            const fContraDetails = root.querySelector('.driver-f-contraindications__details');
            const contraAssessmentBlock = root.querySelector('[data-contra-assessment-block]');
            const contraAssessmentInputs = [...root.querySelectorAll('[data-contra-assessment]')];
            const hardLock = root.querySelector('[data-driver-hard-lock]');
            const hardLockTitle = root.querySelector('[data-driver-hard-lock-title]');
            const hardLockText = root.querySelector('[data-driver-hard-lock-text]');
            const guardedPrintLinks = [...root.querySelectorAll('[data-guarded-print]')];
            const blankPrintLink = root.querySelector('[data-blank-print]');
            const contraConfirmed = 'confirmed';
            const contraCommissionRequired = 'commission_required';
            const initialPatientNode = document.getElementById('driverInitialPatient');

            const resultCommissionNo = 'commission_no_contraindications';
            const resultCommissionYes = 'commission_contraindications';
            const resultNoContra = 'no_contraindications';
            const resultRefer = 'refer_commission';
            const resultRefused = 'refused_commission';

            let selectedPatient = null;
            let searchResults = [];
            let searchTimer = null;
            let searchController = null;

            try {
                selectedPatient = JSON.parse(initialPatientNode?.textContent || 'null');
            } catch (_) {
                selectedPatient = null;
            }

            const text = (value) => String(value ?? '').trim();

            const normalizePatient = (item) => {
                const diagnosis = item?.diagnosis && typeof item.diagnosis === 'object'
                    ? item.diagnosis
                    : (item?.diagnose && typeof item.diagnose === 'object' ? item.diagnose : {});
                const fullName = text(item?.full_name)
                    || [item?.surname, item?.name, item?.patronym].filter(Boolean).join(' ')
                    || text(item?.text);
                const birthAt = text(item?.birth_at || item?.birth_date);
                const diagnosisCode = text(item?.diagnosis_code || diagnosis?.code);
                const diagnosisTitle = text(item?.diagnosis_title || diagnosis?.title || diagnosis?.name);
                const diagnosisLabel = text(item?.diagnosis_label)
                    || [diagnosisCode, diagnosisTitle].filter(Boolean).join(' — ')
                    || 'Диагноз в карте не указан';

                return {
                    id: Number(item?.id || item?.patient_id || 0),
                    full_name: fullName,
                    birth_at: birthAt,
                    birth_at_display: text(item?.birth_at_display || item?.birth_date_display || birthAt),
                    age: item?.age ?? null,
                    phone: text(item?.phone || item?.telephone || item?.mobile_phone),
                    diagnosis_code: diagnosisCode,
                    diagnosis_title: diagnosisTitle,
                    diagnosis_label: diagnosisLabel,
                };
            };

            const normalizeFCode = (value) => {
                const match = text(value).toUpperCase().replace(/\s+/g, '').match(/F\d{2}(?:\.\d+)?/);
                return match ? match[0] : '';
            };

            const fCodeMatches = (diagnosisCode, listedCode) => {
                if (!diagnosisCode || !listedCode) return false;
                if (listedCode.includes('.')) return diagnosisCode === listedCode;
                return diagnosisCode === listedCode || diagnosisCode.startsWith(`${listedCode}.`);
            };

            const findFContraindication = (diagnosisCode) => {
                const code = normalizeFCode(diagnosisCode);
                if (!code) return null;

                for (const group of fContraindicationGroups) {
                    const matchedCode = (group.codes || []).find((listedCode) => fCodeMatches(code, String(listedCode).toUpperCase()));
                    if (matchedCode) return { ...group, diagnosis_code: code, matched_code: matchedCode };
                }

                return null;
            };

            const selectedContraAssessment = () =>
                contraAssessmentInputs.find((item) => item.checked)?.value || '';

            const currentContraMatch = () => {
                const code = normalizeFCode(selectedPatient?.diagnosis_code);
                return code ? findFContraindication(code) : null;
            };

            const syncBlankPrintHref = () => {
                if (!blankPrintLink) return;
                const base = blankPrintLink.dataset.blankPrintBase || blankPrintLink.href;
                const url = new URL(base, window.location.origin);
                if (selectedPatient?.id) url.searchParams.set('patient_id', String(selectedPatient.id));
                else url.searchParams.delete('patient_id');

                const assessment = selectedContraAssessment();
                if (assessment) url.searchParams.set('contraindication_assessment', assessment);
                else url.searchParams.delete('contraindication_assessment');

                blankPrintLink.href = url.toString();
            };

            const setActionLock = (locked, title = '', message = '') => {
                submitButtons.forEach((button) => {
                    button.disabled = locked;
                    button.classList.toggle('is-disabled', locked);
                    button.setAttribute('aria-disabled', String(locked));
                });

                guardedPrintLinks.forEach((link) => {
                    link.classList.toggle('is-disabled', locked);
                    link.setAttribute('aria-disabled', String(locked));
                    link.tabIndex = locked ? -1 : 0;
                });

                hardLock?.classList.toggle('d-none', !locked);
                if (locked) {
                    if (hardLockTitle) hardLockTitle.textContent = title || 'Водительский документ заблокирован';
                    if (hardLockText) hardLockText.textContent = message || 'Сохранение и печать недоступны.';
                }
            };

            const syncContraindicationGate = () => {
                syncBlankPrintHref();
                const match = currentContraMatch();
                const assessment = selectedContraAssessment();
                const result = resultSelect?.value || '';

                contraAssessmentBlock?.classList.toggle('d-none', !match);

                if (result === resultCommissionYes) {
                    setActionLock(
                        true,
                        'Медицинское противопоказание подтверждено ВК',
                        'Сохранение водительского документа и любая печать по этому обследованию заблокированы.'
                    );
                    return;
                }

                if (!match) {
                    contraAssessmentInputs.forEach((item) => { item.checked = false; });
                    setActionLock(false);
                    return;
                }

                if (assessment === contraConfirmed) {
                    setActionLock(
                        true,
                        'Медицинское противопоказание подтверждено',
                        'Для этого пациента нельзя сохранить водительский документ, выдать справку или выполнить печать.'
                    );
                    return;
                }

                if (!assessment) {
                    setActionLock(
                        true,
                        'Нужна клиническая оценка',
                        'Диагноз входит в перечень № 892-р. Сначала укажите, выполнено ли нормативное условие противопоказания.'
                    );
                    return;
                }

                setActionLock(false);
            };

            const renderFContraindication = (patient) => {
                if (!fContraStatus) return;

                const code = normalizeFCode(patient?.diagnosis_code || '');
                const match = findFContraindication(code);
                fContraStatus.classList.remove('is-match', 'is-narcology', 'is-clear', 'is-empty');
                fContraStatus.replaceChildren();

                const icon = document.createElement('i');
                const copy = document.createElement('span');

                if (!code) {
                    fContraStatus.classList.add('is-empty');
                    icon.className = 'bi bi-info-circle';
                    copy.textContent = 'После выбора пациента система сопоставит код диагноза с действующим перечнем.';
                } else if (match) {
                    const narcology = match.specialist === 'psychiatrist_narcologist';
                    fContraStatus.classList.add(narcology ? 'is-narcology' : 'is-match');
                    icon.className = `bi ${narcology ? 'bi-capsule' : 'bi-exclamation-triangle-fill'}`;
                    const strong = document.createElement('strong');
                    strong.textContent = `${code} входит в перечень: `;
                    copy.append(strong, document.createTextNode(`${match.title}. ${match.condition}`));
                    if (fContraDetails) fContraDetails.open = true;
                } else {
                    fContraStatus.classList.add('is-clear');
                    icon.className = 'bi bi-check-circle-fill';
                    const strong = document.createElement('strong');
                    strong.textContent = `${code} не найден в F-перечне № 892-р. `;
                    copy.append(strong, document.createTextNode('Это не отменяет оценку иных медицинских противопоказаний.'));
                }

                fContraStatus.append(icon, copy);
                syncContraindicationGate();
            };

            const renderPatient = (patient) => {
                if (!patient?.id) {
                    if (patientName) patientName.textContent = 'Пациент не выбран';
                    if (patientMeta) patientMeta.textContent = 'Выберите пациента из списка';
                    if (patientDiagnosis) patientDiagnosis.textContent = 'Диагноз в карте не указан';
                    patientClear?.classList.add('d-none');
                    renderFContraindication(null);
                    syncContraindicationGate();
                    updateReadiness();
                    return;
                }

                if (patientName) patientName.textContent = patient.full_name || 'Пациент';
                const meta = [
                    patient.birth_at_display ? `д. р. ${patient.birth_at_display}` : '',
                    patient.age !== null && patient.age !== undefined ? `${patient.age} лет` : '',
                    patient.phone,
                ].filter(Boolean).join(' · ');
                if (patientMeta) patientMeta.textContent = meta || 'Основные сведения не указаны';
                if (patientDiagnosis) patientDiagnosis.textContent = patient.diagnosis_label || 'Диагноз в карте не указан';
                patientClear?.classList.remove('d-none');
                renderFContraindication(patient);
                updateReadiness();
            };

            const closeSuggestions = () => {
                patientSuggestions?.classList.add('d-none');
                patientSuggestions?.replaceChildren();
                searchResults = [];
            };

            const renderSearchMessage = (message) => {
                if (!patientSuggestions) return;
                const node = document.createElement('div');
                node.className = 'driver-suggestion-message';
                node.textContent = message;
                patientSuggestions.replaceChildren(node);
                patientSuggestions.classList.remove('d-none');
            };

            const renderSearchResults = (items) => {
                if (!patientSuggestions) return;
                searchResults = items.map(normalizePatient).filter((item) => item.id && item.full_name);
                if (!searchResults.length) {
                    renderSearchMessage('Пациенты не найдены');
                    return;
                }

                const fragment = document.createDocumentFragment();
                searchResults.forEach((patient, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'driver-suggestion';
                    button.dataset.patientIndex = String(index);

                    const icon = document.createElement('span');
                    icon.className = 'driver-suggestion__icon';
                    icon.innerHTML = '<i class="bi bi-person"></i>';

                    const copy = document.createElement('span');
                    copy.className = 'driver-suggestion__copy';
                    const name = document.createElement('strong');
                    name.textContent = patient.full_name;
                    const meta = document.createElement('span');
                    meta.textContent = [
                        patient.birth_at_display ? `д. р. ${patient.birth_at_display}` : '',
                        patient.diagnosis_label && patient.diagnosis_label !== 'Диагноз в карте не указан'
                            ? patient.diagnosis_label
                            : '',
                    ].filter(Boolean).join(' · ');
                    copy.append(name, meta);
                    button.append(icon, copy);
                    fragment.append(button);
                });

                patientSuggestions.replaceChildren(fragment);
                patientSuggestions.classList.remove('d-none');
            };

            const searchPatients = async (query) => {
                if (!searchUrl) return;
                searchController?.abort();
                searchController = new AbortController();
                renderSearchMessage('Поиск…');

                try {
                    const separator = searchUrl.includes('?') ? '&' : '?';
                    const response = await fetch(`${searchUrl}${separator}q=${encodeURIComponent(query)}`, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                        signal: searchController.signal,
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const payload = await response.json();
                    const items = Array.isArray(payload)
                        ? payload
                        : (Array.isArray(payload?.results) ? payload.results : (Array.isArray(payload?.data) ? payload.data : []));
                    renderSearchResults(items);
                } catch (error) {
                    if (error.name !== 'AbortError') renderSearchMessage('Не удалось выполнить поиск');
                }
            };

            const selectPatient = (patient) => {
                const previousPatientId = Number(selectedPatient?.id || 0);
                selectedPatient = normalizePatient(patient);
                if (!selectedPatient.id) return;
                if (previousPatientId !== selectedPatient.id) {
                    contraAssessmentInputs.forEach((item) => { item.checked = false; });
                }
                if (patientId) patientId.value = String(selectedPatient.id);
                if (patientSearch) {
                    patientSearch.value = selectedPatient.full_name;
                    // Если до этого форму пытались сохранить без пациента,
                    // браузер запоминает customValidity. Снимаем ошибку сразу
                    // после выбора пациента, иначе повторное сохранение даже
                    // не дойдёт до обработчика submit.
                    patientSearch.setCustomValidity('');
                    patientSearch.removeAttribute('aria-invalid');
                }
                renderPatient(selectedPatient);
                closeSuggestions();
            };

            const clearPatient = () => {
                selectedPatient = null;
                if (patientId) patientId.value = '';
                if (patientSearch) {
                    patientSearch.value = '';
                    patientSearch.setCustomValidity('');
                    patientSearch.removeAttribute('aria-invalid');
                }
                renderPatient(null);
                closeSuggestions();
            };

            const syncResultFields = () => {
                const value = resultSelect?.value || '';
                const commission = [resultCommissionNo, resultCommissionYes].includes(value);
                commissionFields?.classList.toggle('d-none', !commission);
                refusalFields?.classList.toggle('d-none', value !== resultRefused);

                if (conclusionInput && text(conclusionInput.value) === '') {
                    const defaults = {
                        [resultNoContra]: 'По результатам психиатрического обследования медицинские противопоказания психиатрического профиля к управлению транспортными средствами не выявлены.',
                        [resultRefer]: 'Выявлены симптомы или синдромы, требующие психиатрического освидетельствования врачебной комиссией.',
                        [resultCommissionNo]: 'По результатам психиатрического освидетельствования врачебной комиссией медицинские противопоказания психиатрического профиля не подтверждены.',
                        [resultCommissionYes]: 'По результатам психиатрического освидетельствования врачебной комиссией медицинские противопоказания психиатрического профиля подтверждены.',
                        [resultRefused]: 'Пациент отказался от психиатрического освидетельствования врачебной комиссией; справка психиатра о отсутствии противопоказаний не оформляется.',
                    };
                    conclusionInput.value = defaults[value] || '';
                }
                updateReadiness();
            };

            const syncStatus = () => {
                const label = statusSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
                root.querySelectorAll('[data-save-status]').forEach((node) => node.textContent = label);
                updateReadiness();
            };

            const updateReadiness = () => {
                const state = {
                    patient: Boolean(patientId?.value),
                    categories: categoryCheckboxes.some((item) => item.checked),
                    mental: Boolean(text(mentalStatusInput?.value)),
                    review: Boolean(recordReviewed?.checked),
                    result: Boolean(text(resultSelect?.value)),
                    conclusion: Boolean(text(conclusionInput?.value)),
                };
                const values = Object.values(state);
                const percent = Math.round(values.filter(Boolean).length / values.length * 100);
                const percentNode = root.querySelector('[data-readiness-percent]');
                if (percentNode) percentNode.textContent = `${percent}%`;
                Object.entries(state).forEach(([key, ready]) => {
                    root.querySelector(`[data-readiness-item="${key}"]`)?.classList.toggle('is-ready', ready);
                });
            };

            patientSearch?.addEventListener('input', () => {
                // Любое новое редактирование снимает старую браузерную
                // ошибку «Выберите пациента из списка». При submit она будет
                // выставлена заново, если patient_id действительно пуст.
                patientSearch.setCustomValidity('');
                patientSearch.removeAttribute('aria-invalid');
                const query = patientSearch.value.trim();
                if (selectedPatient && query !== selectedPatient.full_name) {
                    selectedPatient = null;
                    if (patientId) patientId.value = '';
                    renderPatient(null);
                }
                window.clearTimeout(searchTimer);
                if (query.length < 1) {
                    closeSuggestions();
                    return;
                }
                searchTimer = window.setTimeout(() => searchPatients(query), 250);
            });

            patientClear?.addEventListener('click', () => {
                clearPatient();
                patientSearch?.focus();
            });

            root.addEventListener('click', (event) => {
                const resultButton = event.target.closest('[data-patient-index]');
                if (resultButton) {
                    selectPatient(searchResults[Number(resultButton.dataset.patientIndex)]);
                }
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.driver-patient-search')) closeSuggestions();
            });

            resultSelect?.addEventListener('change', () => { syncResultFields(); syncContraindicationGate(); });
            contraAssessmentInputs.forEach((input) => input.addEventListener('change', () => { syncContraindicationGate(); updateReadiness(); }));
            guardedPrintLinks.forEach((link) => link.addEventListener('click', (event) => {
                if (link.getAttribute('aria-disabled') === 'true') {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }));
            statusSelect?.addEventListener('change', syncStatus);
            form?.addEventListener('input', updateReadiness);
            form?.addEventListener('change', updateReadiness);

            form?.addEventListener('submit', (event) => {
                syncContraindicationGate();

                const match = currentContraMatch();
                const assessment = selectedContraAssessment();
                if (match && (!assessment || assessment === contraConfirmed || resultSelect?.value === resultCommissionYes)) {
                    event.preventDefault();
                    hardLock?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                if (!patientId?.value) {
                    event.preventDefault();
                    patientSearch?.setCustomValidity('Выберите пациента из списка.');
                    patientSearch?.setAttribute('aria-invalid', 'true');
                    patientSearch?.reportValidity();
                    patientSearch?.focus();
                    return;
                }
                patientSearch?.setCustomValidity('');

                if (statusSelect?.value === 'prepared') {
                    const blockers = [];
                    if (!categoryCheckboxes.some((item) => item.checked)) blockers.push('Выберите хотя бы одну категорию транспортного средства.');
                    if (!text(mentalStatusInput?.value)) blockers.push('Заполните психический статус.');
                    if (!recordReviewed?.checked) blockers.push('Подтвердите проверку медицинской карты.');
                    if (!text(resultSelect?.value)) blockers.push('Укажите результат обследования.');
                    if (!text(conclusionInput?.value)) blockers.push('Заполните текст заключения.');
                    if (riskCheckboxes.some((item) => item.checked) && resultSelect?.value === resultNoContra) {
                        blockers.push('При отмеченных настораживающих признаках нельзя оформить обычный результат «противопоказания не выявлены»; используйте сценарий психиатрической ВК.');
                    }
                    if ([resultCommissionNo, resultCommissionYes].includes(resultSelect?.value)) {
                        if (!text(root.querySelector('#driverCommissionProtocol')?.value)) blockers.push('Укажите номер протокола психиатрической ВК.');
                        if (!text(root.querySelector('#driverCommissionDate')?.value)) blockers.push('Укажите дату протокола психиатрической ВК.');
                        if (!text(root.querySelector('#driverCommissionDecision')?.value)) blockers.push('Заполните решение психиатрической ВК.');
                    }
                    if (resultSelect?.value === resultRefer && !text(root.querySelector('#driverRationale')?.value)) {
                        blockers.push('Укажите клиническое основание направления на психиатрическую ВК.');
                    }
                    if (blockers.length) {
                        event.preventDefault();
                        window.alert(`Документ пока нельзя оформить.\n\n${blockers.map((item) => '• ' + item).join('\n')}`);
                        return;
                    }
                }

                submitButtons.forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                });
            });

            if (selectedPatient) {
                selectedPatient = normalizePatient(selectedPatient);
                renderPatient(selectedPatient);
            } else {
                renderPatient(null);
            }
            syncResultFields();
            syncStatus();
            syncContraindicationGate();
            updateReadiness();
        })();
    </script>
@endsection
