{{-- Paper benefit prescription. All permissions and defaults are prepared by the service. --}}
<div id="benefitPrescriptionModule"
     data-store-url="{{ $benefitPrescriptions['store_url'] ?? '' }}"
     data-drug-url="{{ $benefitPrescriptions['drug_url'] ?? '' }}"
     data-can-write="{{ !empty($benefitPrescriptions['can_write']) ? '1' : '0' }}"
     data-can-issue="{{ !empty($benefitPrescriptions['can_issue']) ? '1' : '0' }}">
    <section class="benefit-rx-mode presc-section">
        <label class="benefit-rx-mode__label" for="benefitPrescriptionMode">
            <span class="benefit-rx-mode__icon" aria-hidden="true"><i class="bi bi-file-earmark-medical"></i></span>
            <span class="benefit-rx-mode__text">
                <strong>Льготный рецепт</strong>
                <span>Бумажная форма № 148-1/у-04(л)</span>
            </span>
            <span class="form-check form-switch m-0">
                <input type="checkbox" class="form-check-input" id="benefitPrescriptionMode"
                       aria-controls="benefitPrescriptionPanel" aria-expanded="false"
                       @disabled(empty($benefitPrescriptions['available']) || empty($benefitPrescriptions['can_write']))>
            </span>
        </label>
        {{-- clinic-benefit-requisites:prescription-errors:v1:begin --}}
        @if (!empty($benefitPrescriptions['setup_errors']))
            <div class="benefit-rx-help mb-0 mt-2" role="status">
                <div>Проверьте настройки клиники:</div>
                <ul class="mb-0">
                    @foreach ($benefitPrescriptions['setup_errors'] as $setupError)
                        <li>{{ $setupError }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif (!empty($benefitPrescriptions['setup_message']))
            <p class="benefit-rx-help mb-0 mt-2">{{ $benefitPrescriptions['setup_message'] }}</p>
        @endif
        {{-- clinic-benefit-requisites:prescription-errors:v1:end --}}
    </section>

    <div id="benefitPrescriptionPanel" class="benefit-rx-panel" hidden>
        <div id="benefitPrescriptionFeedback" class="benefit-rx-feedback" role="status" hidden></div>
        <div id="benefitPrescriptionErrors" class="benefit-rx-errors" role="alert" tabindex="-1" hidden></div>

        <section id="benefitSavedClinical" class="benefit-rx-saved" aria-label="Сохранённое назначение" hidden>
            <div class="benefit-rx-eyebrow">Назначение из черновика</div>
            <strong id="benefitSavedClinicalName"></strong>
            <p id="benefitSavedClinicalDetails" class="mb-1"></p>
            <p class="benefit-rx-help mb-0">Здесь редактируются реквизиты льготного рецепта. Для другого препарата создайте новый рецепт.</p>
        </section>

        <fieldset id="benefitPrescriptionFieldset" class="benefit-rx-fieldset" disabled>
            <legend class="visually-hidden">Реквизиты льготного рецепта</legend>
            <input type="hidden" name="benefit[id]" value="">
            <input type="hidden" name="benefit[version]" value="">
            <input type="hidden" name="benefit[request_key]" value="{{ $benefitPrescriptions['defaults']['request_key'] ?? '' }}">
            <input type="hidden" name="benefit[use_saved_clinical]" value="0">

            <div class="benefit-rx-panel__head">
                <div>
                    <div class="benefit-rx-eyebrow">№ 148-1/у-04(л)</div>
                    <h3 class="benefit-rx-heading">Реквизиты льготного рецепта</h3>
                </div>
                <span class="benefit-rx-badge">Бумажный бланк</span>
            </div>
            <div class="benefit-rx-context">
                <span><i class="bi bi-person" aria-hidden="true"></i> {{ $benefitPrescriptions['defaults']['patient_name'] ?? '' }}</span>
                <span><i class="bi bi-hospital" aria-hidden="true"></i> {{ $benefitPrescriptions['defaults']['clinic_name'] ?? '' }}</span>
            </div>
            <div class="benefit-rx-notice">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <div>
                    <strong>{{ $benefitPrescriptions['defaults']['disability_label'] ?? 'Сведения об инвалидности не указаны' }}</strong>
                    @if(!empty($benefitPrescriptions['defaults']['disability_verified']))
                        <span>Документ МСЭ проверен сотрудником.</span>
                    @endif
                    <span>Право на лекарственную льготу подтверждается отдельно для выбранной программы.</span>
                </div>
            </div>

            <details class="benefit-rx-section" open>
                <summary><span class="benefit-rx-step">1</span> Льгота и оплата</summary>
                <div class="benefit-rx-section__body">
                    <div class="benefit-rx-grid">
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitProgram">Программа обеспечения</label>
                            <select class="form-select" id="benefitProgram" name="benefit[program]">
                                <option value="">Выберите программу</option>
                                <option value="federal_nsu">Федеральная — НСУ</option>
                                <option value="regional">Региональная</option>
                                <option value="municipal">Муниципальная</option>
                                <option value="other">Другое основание</option>
                            </select>
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitCategoryCode">Код категории граждан</label>
                            <input class="form-control" id="benefitCategoryCode" name="benefit[category_code]"
                                   inputmode="numeric" maxlength="3" placeholder="Три цифры" autocomplete="off">
                            <div class="benefit-rx-help">Код льготной категории по выбранной программе.</div>
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitDiagnosisCode">Диагноз по МКБ</label>
                            <input class="form-control" id="benefitDiagnosisCode" name="benefit[diagnosis_code]"
                                   value="{{ $benefitPrescriptions['defaults']['diagnosis_code'] ?? '' }}"
                                   maxlength="16" autocomplete="off">
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitFunding">Источник финансирования</label>
                            <select class="form-select" id="benefitFunding" name="benefit[funding]">
                                <option value="">Выберите источник</option>
                                <option value="1">1. Федеральный бюджет</option>
                                <option value="2">2. Бюджет субъекта РФ</option>
                                <option value="3">3. Муниципальный бюджет</option>
                            </select>
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitPayment">Оплата пациентом</label>
                            <select class="form-select" id="benefitPayment" name="benefit[payment]">
                                <option value="">Выберите условие</option>
                                <option value="1">1. Бесплатно</option>
                                <option value="2">2. 50%</option>
                                <option value="3">3. Иной процент</option>
                            </select>
                        </div>
                        <div class="benefit-rx-field" id="benefitPaymentPercentWrap" hidden>
                            <label class="form-label" for="benefitPaymentPercent">Доля оплаты пациентом, %</label>
                            <input class="form-control" id="benefitPaymentPercent" name="benefit[payment_percent]"
                                   type="number" min="0" max="100" step="0.01" inputmode="decimal" disabled>
                        </div>
                        <div class="benefit-rx-field benefit-rx-field--wide">
                            <label class="form-label" for="benefitEligibilityBasis">Основание права на льготу</label>
                            <textarea class="form-control" id="benefitEligibilityBasis" name="benefit[eligibility_basis]"
                                      rows="2" maxlength="1000" placeholder="Документ или сведения о праве: наименование, номер, дата, источник проверки"></textarea>
                            <div class="benefit-rx-help">Реквизиты сохраняются в медкарте и не печатаются в поле диагноза.</div>
                        </div>
                    </div>
                    <label class="benefit-rx-check" id="benefitNsuWrap" hidden>
                        <input type="checkbox" class="form-check-input" name="benefit[nsu_medicines_retained]" value="1" disabled>
                        <span>Проверено: лекарственная часть НСУ сохранена в натуральной форме на дату выписки.</span>
                    </label>
                    <label class="benefit-rx-check">
                        <input type="checkbox" class="form-check-input" name="benefit[eligibility_confirmed]" value="1">
                        <span>Право пациента на указанную программу проверено на дату выписки.</span>
                    </label>
                </div>
            </details>

            <details class="benefit-rx-section" open>
                <summary><span class="benefit-rx-step">2</span> Текст назначения</summary>
                <div class="benefit-rx-section__body">
                    <div id="benefitDrugStatus" class="benefit-rx-help mb-2" role="status"></div>
                    <div id="benefitStrictNotice" class="benefit-rx-notice benefit-rx-notice--amber" hidden>
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <span>В каталоге препарат отмечен как требующий специального рецепта. Укажите соответствующий сопутствующий документ ниже.</span>
                    </div>
                    <div class="benefit-rx-grid">
                        <div class="benefit-rx-field benefit-rx-field--wide">
                            <label class="form-label" for="benefitRp">Rp.</label>
                            <textarea class="form-control" id="benefitRp" name="benefit[rp]" rows="2" maxlength="240"
                                      placeholder="Лекарственная форма, наименование и дозировка"></textarea>
                        </div>
                        <div class="benefit-rx-field benefit-rx-field--wide">
                            <label class="form-label" for="benefitDtd">D.t.d.</label>
                            <input class="form-control" id="benefitDtd" name="benefit[dtd]" maxlength="180"
                                   placeholder="Количество отпускаемых доз и указание формы отпуска">
                            <div class="benefit-rx-help">Проверьте количество с учётом упаковки и числа стандартов.</div>
                        </div>
                        <div class="benefit-rx-field benefit-rx-field--wide">
                            <div class="benefit-rx-label-row">
                                <label class="form-label" for="benefitSigna">Signa — способ применения</label>
                                <button type="button" class="benefit-rx-text-button" id="benefitFillRegimen">Подставить пустые поля из схемы</button>
                            </div>
                            <textarea class="form-control" id="benefitSigna" name="benefit[signa]" rows="3" maxlength="600"
                                      placeholder="Разовая доза, путь введения, частота и продолжительность применения"></textarea>
                        </div>
                    </div>
                    <div class="benefit-rx-grid benefit-rx-grid--three">
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitTreatmentDays">Курс, дней</label>
                            <input class="form-control" id="benefitTreatmentDays" name="benefit[treatment_days]"
                                   type="number" min="1" max="180" step="1" inputmode="numeric">
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitDosesPerDay">Приёмов в день</label>
                            <input class="form-control" id="benefitDosesPerDay" name="benefit[doses_per_day]"
                                   maxlength="32" placeholder="Например, 2">
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitUnitsPerDose">На один приём</label>
                            <input class="form-control" id="benefitUnitsPerDose" name="benefit[units_per_dose]"
                                   maxlength="32" placeholder="Например, 1 таблетка">
                        </div>
                    </div>
                    <p class="benefit-rx-help mb-0">Сведения о курсе и приёме также попадут в отрывной корешок.</p>
                </div>
            </details>

            <details class="benefit-rx-section" open>
                <summary><span class="benefit-rx-step">3</span> Срок действия и сопутствующий рецепт</summary>
                <div class="benefit-rx-section__body">
                    <div class="benefit-rx-grid">
                        <div class="benefit-rx-field benefit-rx-field--wide">
                            <label class="form-label" for="benefitCompanionForm">Нужен ли дополнительный рецептурный бланк?</label>
                            <select class="form-select" id="benefitCompanionForm" name="benefit[companion_form]">
                                <option value="">Выберите по назначенному препарату</option>
                                <option value="none">Дополнительный бланк не требуется</option>
                                <option value="148_88">№ 148-1/у-88</option>
                                <option value="107_np">№ 107/у-НП</option>
                                <option value="107_1">№ 107-1/у в случаях, предусмотренных п. 39 порядка назначения</option>
                            </select>
                        </div>
                    </div>
                    <div id="benefitCompanionWrap" hidden>
                        <div class="benefit-rx-field mt-3">
                            <label class="form-label" for="benefitCompanionReference">Реквизиты сопутствующего рецепта</label>
                            <textarea class="form-control" id="benefitCompanionReference" name="benefit[companion_reference]"
                                      rows="2" maxlength="300" placeholder="Форма, серия и номер, дата оформления" disabled></textarea>
                        </div>
                        <label class="benefit-rx-check">
                            <input type="checkbox" class="form-check-input" name="benefit[companion_confirmed]" value="1" disabled>
                            <span>Соответствующий сопутствующий рецепт оформлен; его реквизиты проверены.</span>
                        </label>
                        <p class="benefit-rx-help">Эта форма дополняет сопутствующий рецепт. Специальный типографский бланк оформляется отдельно.</p>
                    </div>
                    <div class="benefit-rx-grid mt-3">
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitValidityDays">Рецепт действителен</label>
                            <select class="form-select" id="benefitValidityDays" name="benefit[validity_days]">
                                <option value="30" selected>30 дней</option>
                                <option value="90">90 дней — при наличии основания</option>
                                <option value="15">15 дней — для предусмотренных случаев</option>
                            </select>
                        </div>
                        <div class="benefit-rx-field" id="benefitValidityBasisWrap" hidden>
                            <label class="form-label" for="benefitValidityBasis">Основание для 90 дней</label>
                            <select class="form-select" id="benefitValidityBasis" name="benefit[validity_basis]">
                                <option value="standard" selected>Обычный срок</option>
                                <option value="controlled">Сопутствующий рецепт</option>
                                <option value="">Выберите основание</option>
                                <option value="disability_i">Инвалидность I группы</option>
                                <option value="disabled_child">Ребёнок-инвалид</option>
                                <option value="pension_age">Достигнут пенсионный возраст</option>
                                <option value="chronic_long">Хроническое заболевание, длительное лечение</option>
                            </select>
                        </div>
                    </div>
                    <label class="benefit-rx-check" id="benefitExtendedValidityWrap" hidden>
                        <input type="checkbox" class="form-check-input" name="benefit[extended_validity_confirmed]" value="1" disabled>
                        <span>Указанное основание для срока действия 90 дней проверено.</span>
                    </label>
                    <p class="benefit-rx-help mt-2 mb-0">Срок действия рецепта заполняется отдельно от длительности лечения. II группа инвалидности сама по себе не является основанием для 90 дней.</p>
                </div>
            </details>

            <details class="benefit-rx-section">
                <summary><span class="benefit-rx-step">4</span> Данные пациента для бланка</summary>
                <div class="benefit-rx-section__body">
                    <div class="benefit-rx-grid">
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitSnils">СНИЛС, при наличии</label>
                            <input class="form-control" id="benefitSnils" name="benefit[snils]" inputmode="numeric"
                                   maxlength="14" value="{{ $benefitPrescriptions['defaults']['snils'] ?? '' }}" autocomplete="off">
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitOms">Полис ОМС</label>
                            <input class="form-control" id="benefitOms" name="benefit[oms]" maxlength="32"
                                   value="{{ $benefitPrescriptions['defaults']['oms'] ?? '' }}" autocomplete="off">
                        </div>
                        <div class="benefit-rx-field benefit-rx-field--wide">
                            <label class="form-label" for="benefitCardNumber">Номер амбулаторной медкарты</label>
                            <input class="form-control" id="benefitCardNumber" name="benefit[medcard_number]" maxlength="40"
                                   value="{{ $benefitPrescriptions['defaults']['medcard_number'] ?? '' }}" autocomplete="off">
                        </div>
                    </div>
                    <p class="benefit-rx-help mt-2 mb-0">ФИО и дата рождения берутся из медкарты. Исправления идентификаторов здесь сохраняются в рецепте.</p>
                </div>
            </details>

            <details class="benefit-rx-section" open>
                <summary><span class="benefit-rx-step">5</span> Серия и номер</summary>
                <div class="benefit-rx-section__body">
                    <div class="benefit-rx-grid">
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitSeries">Серия</label>
                            <input class="form-control" id="benefitSeries" name="benefit[series]" maxlength="20"
                                   value="{{ $benefitPrescriptions['defaults']['series'] ?? '' }}" autocomplete="off">
                        </div>
                        <div class="benefit-rx-field">
                            <label class="form-label" for="benefitNumber">Номер</label>
                            <input class="form-control" id="benefitNumber" name="benefit[number]" maxlength="20" autocomplete="off">
                        </div>
                    </div>
                    <p class="benefit-rx-help mt-2 mb-0">
                        Укажите реквизиты по принятой в клинике системе нумерации.
                        @if(!empty($benefitPrescriptions['defaults']['okato_prefix']))
                            Первые две цифры серии по ОКАТО: {{ $benefitPrescriptions['defaults']['okato_prefix'] }}.
                        @endif
                        Два печатных экземпляра получат одинаковые серию и номер.
                    </p>
                </div>
            </details>

            <label class="benefit-rx-check benefit-rx-check--final">
                <input type="checkbox" class="form-check-input" name="benefit[clinical_confirmed]" value="1">
                <span>Назначение, дозировка, количество, способ применения и необходимость сопутствующего рецепта проверены.</span>
            </label>
            <p class="benefit-rx-help mb-0">Черновик можно сохранить до проверки всех реквизитов. Для бумажного рецепта нужны собственноручная подпись и предусмотренные печати.</p>
        </fieldset>
    </div>
</div>
