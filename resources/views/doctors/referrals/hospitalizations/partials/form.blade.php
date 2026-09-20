@php
    $isEmergency = $mode === 'emergency';
@endphp

<form id="hospitalReferralForm"
      method="post"
      action="{{ route('doctors.referrals.hospital.store') }}"
      class="hospital-form"
      data-hospital-form>
    @csrf

    <input type="hidden" name="type" value="{{ $mode }}">
    <input type="hidden"
           name="patient_id"
           value="{{ old('patient_id', $selectedPatient?->id) }}"
           data-form-patient-id>
    <input type="hidden"
           name="source_mode"
           value="{{ old('source_mode', 'manual') }}"
           data-source-mode>
    <input type="hidden"
           name="ai_analysis"
           value="{{ old('ai_analysis') }}"
           data-ai-analysis>

    <div class="hospital-form-section">
        <div class="hospital-form-section__head">
            <span class="hospital-form-section__icon">
                <i class="bi bi-person-check"></i>
            </span>
            <div>
                <h3>Пациент и статус документа</h3>
                <p>Пациент выбирается в боковой панели и автоматически подставляется в форму.</p>
            </div>
        </div>

        <div class="hospital-form-grid hospital-form-grid--3">
            <div class="hospital-field hospital-field--2">
                <label class="hospital-label">Выбранный пациент</label>
                <div class="hospital-selected-patient" data-form-patient-label>
                    @if($selectedPatient)
                        {{ collect([$selectedPatient->surname, $selectedPatient->name, $selectedPatient->patronym])->filter()->implode(' ') }}
                    @else
                        Пациент не выбран
                    @endif
                </div>
            </div>

            <div class="hospital-field">
                <label class="hospital-label" for="hospitalStatus">Статус</label>
                <select id="hospitalStatus" name="status" class="form-select hospital-control">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'prepared') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="hospital-form-section">
        <div class="hospital-form-section__head">
            <span class="hospital-form-section__icon">
                <i class="bi bi-clipboard2-pulse"></i>
            </span>
            <div>
                <h3>Клиническое обоснование</h3>
                <p>Предварительный диагноз и стационар заполняются в общей области выше. Здесь оформляется содержание направления.</p>
            </div>
        </div>

        <div class="hospital-form-grid hospital-form-grid--2">
            <div class="hospital-field hospital-field--full">
                <label class="hospital-label" for="clinicalSummary">Краткое клиническое состояние</label>
                <textarea id="clinicalSummary"
                          name="clinical_summary"
                          class="form-control hospital-textarea hospital-textarea--form"
                          rows="6"
                          maxlength="20000"
                          placeholder="Жалобы, динамика, психический и соматический статус, значимые обстоятельства...">{{ old('clinical_summary') }}</textarea>
            </div>

            <div class="hospital-field hospital-field--full">
                <label class="hospital-label" for="hospitalReason">
                    {{ $isEmergency ? 'Обоснование экстренного направления' : 'Показания к плановой госпитализации' }}
                </label>
                <textarea id="hospitalReason"
                          name="reason"
                          class="form-control hospital-textarea hospital-textarea--form"
                          rows="5"
                          maxlength="12000"
                          placeholder="Почему требуется стационарное лечение и какие задачи невозможно решить амбулаторно...">{{ old('reason') }}</textarea>
            </div>
        </div>
    </div>

    @if(!$isEmergency)
        <div class="hospital-form-section hospital-form-section--planned">
            <div class="hospital-form-section__head">
                <span class="hospital-form-section__icon">
                    <i class="bi bi-calendar2-week"></i>
                </span>
                <div>
                    <h3>Параметры плановой госпитализации</h3>
                    <p>Укажите предполагаемую дату, цель лечения и подготовку пациента.</p>
                </div>
            </div>

            <div class="hospital-form-grid hospital-form-grid--2">
                <div class="hospital-field">
                    <label class="hospital-label" for="plannedAt">Предполагаемая дата и время</label>
                    <input id="plannedAt"
                           type="datetime-local"
                           name="planned_at"
                           class="form-control hospital-control"
                           value="{{ old('planned_at') }}">
                </div>

                <div class="hospital-field">
                    <label class="hospital-label" for="expectedDuration">Ожидаемая длительность</label>
                    <input id="expectedDuration"
                           type="text"
                           name="expected_duration"
                           class="form-control hospital-control"
                           maxlength="255"
                           value="{{ old('expected_duration') }}"
                           placeholder="Например, 14–21 день">
                </div>

                <div class="hospital-field hospital-field--full">
                    <label class="hospital-label" for="treatmentGoal">Цель стационарного лечения</label>
                    <textarea id="treatmentGoal"
                              name="treatment_goal"
                              class="form-control hospital-textarea hospital-textarea--form"
                              rows="4"
                              maxlength="3000"
                              placeholder="Диагностическое уточнение, подбор терапии, реабилитация...">{{ old('treatment_goal') }}</textarea>
                </div>

                <div class="hospital-field hospital-field--full">
                    <label class="hospital-label" for="prehospitalChecklist">Подготовка и документы</label>
                    <textarea id="prehospitalChecklist"
                              name="prehospital_checklist"
                              class="form-control hospital-textarea hospital-textarea--form"
                              rows="4"
                              maxlength="5000"
                              placeholder="Необходимые обследования, заключения, анализы и документы...">{{ old('prehospital_checklist') }}</textarea>
                </div>
            </div>
        </div>
    @else
        <div class="hospital-form-section hospital-form-section--emergency">
            <div class="hospital-form-section__head">
                <span class="hospital-form-section__icon">
                    <i class="bi bi-activity"></i>
                </span>
                <div>
                    <h3>Срочность и текущее состояние</h3>
                    <p>Зафиксируйте объективные данные и уже выполненные меры.</p>
                </div>
            </div>

            <div class="hospital-form-grid hospital-form-grid--3">
                <div class="hospital-field">
                    <label class="hospital-label" for="urgencyLevel">Срочность</label>
                    <select id="urgencyLevel" name="urgency_level" class="form-select hospital-control">
                        @foreach($urgencyOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('urgency_level', 'unknown') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hospital-field">
                    <label class="hospital-label" for="transportType">Транспортировка</label>
                    <select id="transportType" name="transport_type" class="form-select hospital-control">
                        @foreach($transportOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('transport_type', 'unknown') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hospital-field">
                    <label class="hospital-label" for="deteriorationStartedAt">Начало ухудшения</label>
                    <input id="deteriorationStartedAt"
                           type="datetime-local"
                           name="deterioration_started_at"
                           class="form-control hospital-control"
                           value="{{ old('deterioration_started_at') }}">
                </div>

                <div class="hospital-field hospital-field--full">
                    <label class="hospital-switch">
                        <input type="checkbox"
                               name="ambulance_required"
                               value="1"
                               @checked(old('ambulance_required'))>
                        <span class="hospital-switch__track"></span>
                        <span>
                            <strong>Требуется вызов бригады СМП</strong>
                            <small>Флаг не отправляет вызов автоматически</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="hospital-vitals">
                <div class="hospital-vitals__title">
                    <i class="bi bi-heart-pulse"></i>
                    Витальные показатели
                </div>

                <div class="hospital-vitals__grid">
                    <div class="hospital-field">
                        <label class="hospital-label" for="bloodPressure">АД</label>
                        <input id="bloodPressure"
                               type="text"
                               name="blood_pressure"
                               class="form-control hospital-control"
                               maxlength="32"
                               value="{{ old('blood_pressure') }}"
                               placeholder="120/80">
                    </div>

                    <div class="hospital-field">
                        <label class="hospital-label" for="pulse">Пульс</label>
                        <input id="pulse"
                               type="number"
                               name="pulse"
                               class="form-control hospital-control"
                               min="0"
                               max="300"
                               value="{{ old('pulse') }}"
                               placeholder="уд/мин">
                    </div>

                    <div class="hospital-field">
                        <label class="hospital-label" for="spo2">SpO₂</label>
                        <input id="spo2"
                               type="number"
                               name="spo2"
                               class="form-control hospital-control"
                               min="0"
                               max="100"
                               value="{{ old('spo2') }}"
                               placeholder="%">
                    </div>

                    <div class="hospital-field">
                        <label class="hospital-label" for="temperature">Температура</label>
                        <input id="temperature"
                               type="number"
                               name="temperature"
                               class="form-control hospital-control"
                               step="0.1"
                               min="30"
                               max="45"
                               value="{{ old('temperature') }}"
                               placeholder="°C">
                    </div>

                    <div class="hospital-field hospital-field--2">
                        <label class="hospital-label" for="consciousness">Сознание / контакт</label>
                        <input id="consciousness"
                               type="text"
                               name="consciousness"
                               class="form-control hospital-control"
                               maxlength="255"
                               value="{{ old('consciousness') }}"
                               placeholder="Ясное, оглушение, контакт затруднён...">
                    </div>
                </div>
            </div>

            <div class="hospital-form-grid hospital-form-grid--2 mt-3">
                <div class="hospital-field">
                    <label class="hospital-label" for="redFlags">Красные флаги</label>
                    <textarea id="redFlags"
                              name="red_flags"
                              class="form-control hospital-textarea hospital-textarea--form"
                              rows="5"
                              maxlength="5000"
                              placeholder="Суицидальный риск, психомоторное возбуждение, нарушение сознания, дыхательная недостаточность...">{{ old('red_flags') }}</textarea>
                </div>

                <div class="hospital-field">
                    <label class="hospital-label" for="stabilizationMeasures">Выполненные меры</label>
                    <textarea id="stabilizationMeasures"
                              name="stabilization_measures"
                              class="form-control hospital-textarea hospital-textarea--form"
                              rows="5"
                              maxlength="5000"
                              placeholder="Наблюдение, обеспечение безопасности, мониторинг, вызов СМП...">{{ old('stabilization_measures') }}</textarea>
                </div>
            </div>
        </div>
    @endif

    <div class="hospital-form-section">
        <div class="hospital-field">
            <label class="hospital-label" for="additionalNotes">Дополнительные сведения</label>
            <textarea id="additionalNotes"
                      name="additional_notes"
                      class="form-control hospital-textarea hospital-textarea--form"
                      rows="4"
                      maxlength="10000"
                      placeholder="Организационные детали, сопровождающее лицо, согласие, важные уточнения...">{{ old('additional_notes') }}</textarea>
        </div>
    </div>

    <div class="hospital-form-actions">
        <div class="hospital-form-actions__note">
            <i class="bi bi-info-circle"></i>
            После переноса результата ИИ все поля остаются редактируемыми.
        </div>

        <button type="submit" class="hospital-save-button hospital-save-button--draft" data-save-draft formnovalidate>
            <i class="bi bi-file-earmark"></i>
            Сохранить черновик
        </button>

        <button type="submit" class="hospital-save-button">
            <i class="bi bi-check2-circle"></i>
            Сохранить направление
        </button>
    </div>
</form>
