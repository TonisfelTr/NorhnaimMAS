@extends('doctors.prescriptions')

@section('title', 'Направление для оказания медицинской помощи — плановая форма')

@section('sub-main')
    {{-- Важно: подключаем Vite здесь напрямую, а не через вложенную assets-секцию.
         doctors.prescriptions уже является дочерним Blade, и при тройном наследовании
         assets может потеряться в зависимости от порядка секций. --}}
    @vite('resources/js/hospitalizations.js')

    {{-- Учетная форма № 057/у: приказ Минздрава России от 02.09.2025 № 519н.
         Эта страница сохраняет дополнительные рабочие поля МИС, которые не являются
         самостоятельными реквизитами официальной формы. --}}

    @php
        $type = 'planned';
        $mode = $type;
        $pageTitle = 'Направление для оказания медицинской помощи';
        $pageSubtitle = 'Учетная форма № 057/у · плановая форма оказания медицинской помощи (код 3).';
        $pageIcon = 'bi-calendar2-check';
        $isEmergency = false;
        $medicalFormNumber = '057/у';
        $medicalFormOrder = 'Утверждена приказом Министерства здравоохранения Российской Федерации от 2 сентября 2025 г. № 519н';
        $careFormLabel = 'Плановая';
        $careFormCode = '3';
        $referralPurpose = 'Госпитализация';
        $careTypeLabel = 'Специализированная, в том числе высокотехнологичная медицинская помощь';
        $careTypeCode = '2';
        $careConditionsLabel = 'Стационарно';
        $careConditionsCode = '3';
    @endphp
    <main id="hospitalizationRoot"
          class="hospital-page {{ $isEmergency ? 'hospital-page--emergency' : 'hospital-page--planned' }}"
          data-mode="{{ $type }}"
          data-analyze-url="{{ route('doctors.referrals.hospital.analyze') }}"
          data-patient-search-url="{{ route('api.patients.search') }}"
          data-patient-details-url="{{ str_replace('/0', '/__ID__', route('api.patients.for.search', ['param' => 0])) }}"
          data-patient-context-url="{{ str_replace('/0/context', '/__ID__/context', route('doctors.referrals.hospital.patient_context', ['patient' => 0])) }}"
          data-address-suggest-url="{{ \Illuminate\Support\Facades\Route::has('api-search-address') ? route('api-search-address') : '' }}"
          data-hospital-search-url="{{ \Illuminate\Support\Facades\Route::has('api.osm-medical-organizations.search') ? route('api.osm-medical-organizations.search') : '' }}"
          data-reverse-geocode-url="{{ \Illuminate\Support\Facades\Route::has('api.osm-reverse') ? route('api.osm-reverse') : '' }}">
        <div class="container-fluid hospital-page__container">
            <div class="hospital-shell">
                @php
                    $isEmergency = ($type ?? 'planned') === 'emergency';
                    $selectedPatientName = $selectedPatient
                        ? collect([
                            $selectedPatient->surname,
                            $selectedPatient->name,
                            $selectedPatient->patronym,
                        ])->filter()->implode(' ')
                        : '';
                @endphp

                <aside class="hospital-sidebar">
                    <div class="hospital-sidebar__sticky">

                        <section class="hospital-sidebar-card">
                            <div class="hospital-sidebar-card__head">
                                <span class="hospital-sidebar-card__head-icon">
                                    <i class="bi bi-person-vcard"></i>
                                </span>
                                <div>
                                    <div class="hospital-sidebar-card__eyebrow">Пациент</div>
                                    <h2 class="hospital-sidebar-card__title">Выберите карту</h2>
                                </div>
                            </div>
                            <label class="form-label hospital-label" for="hospitalizationPatientSearch">
                                Поиск пациента
                            </label>

                            <div class="hospital-patient-search" data-patient-search>
                                <div class="hospital-patient-search__control">
                                    <i class="bi bi-search hospital-patient-search__icon"></i>
                                    <input id="hospitalizationPatientSearch"
                                           type="search"
                                           class="form-control hospital-patient-search__input"
                                           value="{{ $selectedPatientName }}"
                                           placeholder="Начните вводить ФИО пациента"
                                           autocomplete="off"
                                           spellcheck="false"
                                           data-patient-search-input>
                                    <button type="button"
                                            class="hospital-patient-search__clear {{ $selectedPatient ? '' : 'd-none' }}"
                                            aria-label="Очистить пациента"
                                            data-patient-search-clear>
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="hospital-patient-suggestions d-none"
                                     role="listbox"
                                     aria-label="Найденные пациенты"
                                     data-patient-suggestions></div>

                                <div class="hospital-patient-search__hint" data-patient-search-hint>
                                    Поиск выполняется в реальном времени по фамилии, имени и отчеству.
                                </div>
                            </div>

                            <div id="hospitalPatientCard"
                                 class="hospital-patient-card {{ $selectedPatient ? '' : 'is-empty' }}">
                                <div class="hospital-patient-card__avatar" data-patient-avatar>
                                    @if($selectedPatient)
                                        {{ mb_substr((string) $selectedPatient->surname, 0, 1) }}{{ mb_substr((string) $selectedPatient->name, 0, 1) }}
                                    @else
                                        <i class="bi bi-person"></i>
                                    @endif
                                </div>

                                <div class="hospital-patient-card__body">
                                    <div class="hospital-patient-card__name" data-patient-name>
                                        {{ $selectedPatientName !== '' ? $selectedPatientName : 'Пациент не выбран' }}
                                    </div>
                                    <div class="hospital-patient-card__meta" data-patient-meta>
                                        @if($selectedPatient)
                                            {{ $selectedPatient->birth_at ? 'Дата рождения: ' . \Illuminate\Support\Carbon::parse($selectedPatient->birth_at)->format('d.m.Y') : 'Дата рождения не указана' }}
                                        @else
                                            Начните вводить фамилию
                                        @endif
                                    </div>
                                    <div class="hospital-patient-card__diagnosis" data-patient-diagnosis>
                                        @if($selectedPatient?->diagnose)
                                            Диагноз: {{ $selectedPatient->diagnose->code }} — {{ $selectedPatient->diagnose->title ?? $selectedPatient->diagnose->name }}
                                        @else
                                            Диагноз: не установлен
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="hospital-sidebar-card hospital-sidebar-card--notice">
                            <div class="hospital-notice-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <strong>Решение остаётся за врачом</strong>
                                <p>
                                    Qwen формирует проект, MedGemma перепроверяет клиническую согласованность.
                                    Тип госпитализации и содержание направления подтверждаются вручную.
                                </p>
                            </div>
                        </section>
                    </div>
                </aside>



                <section class="hospital-main">
                    @if(session('status') === 'success')
                        <div class="alert alert-success hospital-flash" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ session('message') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger hospital-flash" role="alert">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                            <div>
                                <div class="fw-bold">Проверьте заполнение формы</div>
                                <ul class="mb-0 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
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
                                <i class="bi bi-file-medical"></i>
                                Медицинская документация · учетная форма № {{ $medicalFormNumber }}
                            </div>
                            <h1>{{ $pageTitle }}</h1>
                            <p>{{ $pageSubtitle }}</p>
                        </div>

                        <div class="hospital-hero__status {{ $isEmergency ? 'is-emergency' : '' }}">
                            <span class="hospital-hero__status-dot"></span>
                            {{ $careFormLabel }} форма · код {{ $careFormCode }}
                        </div>
                    </header>

                    <section class="card border-0 shadow-sm mb-4" aria-label="Сведения об учетной форме 057/у">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <div class="text-uppercase small fw-semibold text-muted mb-1">Медицинская документация</div>
                                    <div class="h5 mb-1">Учетная форма № {{ $medicalFormNumber }}</div>
                                    <div class="small text-muted">{{ $medicalFormOrder }}</div>
                                </div>
                                <div class="text-lg-end">
                                    <div class="fw-semibold">Направление для оказания медицинской помощи</div>
                                    <div class="small text-muted">
                                        Цель: {{ mb_strtolower($referralPurpose) }} · форма: {{ mb_strtolower($careFormLabel) }} ({{ $careFormCode }}) · условия: {{ mb_strtolower($careConditionsLabel) }} ({{ $careConditionsCode }})
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="small">
                                <strong>Уточнение по медицинской документации.</strong>
                                Форма № 057/у является учетным документом медицинской организации,
                                оказывающей медицинскую помощь в амбулаторных условиях.
                                Если медицинская документация ведется в электронной форме, направление
                                формируется в МИС/ГИС и электронный документ подписывается усиленной
                                квалифицированной электронной подписью медицинского работника.
                                Бумажный экземпляр оформляется в случаях, предусмотренных порядком ведения формы.
                            </div>

                            <div class="alert alert-light border mt-3 mb-0 py-2 px-3 small" role="note">
                                Перед формированием официального документа должны быть заполнены реквизиты,
                                предусмотренные 057/у: сведения направляющей медицинской организации и ОГРН/ОГРНИП,
                                организация назначения, ОМС и страховая организация, ФИО/дата рождения/пол,
                                регистрация, местность, занятость, код диагноза по МКБ, цель, форма, вид и условия
                                медицинской помощи, обоснование направления и сведения о направившем медработнике.
                            </div>
                        </div>
                    </section>

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
                                            <h3>Медицинская организация, куда направлен пациент</h3>
                                            <p>Наименование организации — обязательный реквизит 057/у. Адрес сохраняем дополнительно для маршрутизации.</p>
                                        </div>
                                    </div>

                                    <div class="hospital-destination-search" data-destination-search>
                                        <label class="hospital-label" for="destinationHospitalLookup">
                                            Найти медицинскую организацию по городу
                                        </label>

                                        <div class="hospital-destination-search__control">
                                            <i class="bi bi-search hospital-destination-search__icon"></i>
                                            <input id="destinationHospitalLookup"
                                                   type="search"
                                                   class="form-control hospital-destination-search__input"
                                                   value="{{ $activeDraft?->destination_organization ?? '' }}"
                                                   placeholder="Введите город, например Томск"
                                                   autocomplete="off"
                                                   spellcheck="false"
                                                   data-destination-search-input>
                                            <span class="hospital-destination-search__spinner d-none"
                                                  data-destination-search-spinner
                                                  aria-hidden="true">
                                                <span class="spinner-border spinner-border-sm"></span>
                                            </span>
                                        </div>

                                        <div class="hospital-destination-suggestions d-none"
                                             role="listbox"
                                             aria-label="Найденные психиатрические стационары"
                                             data-destination-suggestions></div>

                                        <div class="hospital-destination-search__hint" data-destination-search-hint>
                                            Введите город. Покажем психиатрические больницы, психоневрологические учреждения,
                                            частные стационары и НИИ психического здоровья, найденные в OpenStreetMap.
                                        </div>
                                    </div>

                                    <div class="hospital-destination-fields">
                                        <div class="hospital-field">
                                            <label class="hospital-label" for="destinationOrganization">
                                                Медицинская организация
                                            </label>
                                            <input id="destinationOrganization"
                                                   type="text"
                                                   name="destination_organization"
                                                   form="hospitalReferralForm"
                                                   class="form-control hospital-control"
                                                   maxlength="255"
                                                   value="{{ old('destination_organization', $activeDraft?->destination_organization) }}"
                                                   placeholder="Наименование медицинской организации">
                                        </div>

                                        <div class="hospital-field">
                                            <label class="hospital-label" for="destinationDepartment">
                                                Профиль / отделение
                                            </label>
                                            <input id="destinationDepartment"
                                                   type="text"
                                                   name="department"
                                                   form="hospitalReferralForm"
                                                   class="form-control hospital-control"
                                                   maxlength="255"
                                                   value="{{ old('department', $activeDraft?->department) }}"
                                                   placeholder="Например, психиатрическое отделение">
                                        </div>

                                        <div class="hospital-field hospital-field--full">
                                            <label class="hospital-label" for="destinationAddress">
                                                Адрес медицинской организации <span class="hospital-required-mark">*</span>
                                            </label>
                                            <input id="destinationAddress"
                                                   type="text"
                                                   name="destination_address"
                                                   form="hospitalReferralForm"
                                                   class="form-control hospital-control"
                                                   maxlength="500"
                                                   value="{{ old('destination_address', $activeDraft?->destination_address) }}"
                                                   placeholder="Заполнится после выбора стационара; можно исправить вручную"
                                                   autocomplete="street-address">
                                            <div class="hospital-field-hint">
                                                После выбора организации адрес подставляется автоматически. Если в OSM адрес не заполнен,
                                                система дополнительно определит его по координатам. Поля остаются редактируемыми.
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
                                            <h3>Код диагноза по МКБ</h3>
                                            <p>
                                                Учетная форма 057/у предусматривает код диагноза по МКБ.
                                                Код выбирает медицинский работник; ИИ может использоваться только как вспомогательный инструмент.
                                                Код должен соответствовать диагнозу, указанному медицинским работником в направлении.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="hospital-preliminary-state" data-preliminary-state>
                                        @if($selectedPatient && $hasCurrentDiagnosis)
                                            <i class="bi bi-info-circle"></i>
                                            <span>
                                                Текущий диагноз пациента:
                                                <strong>{{ data_get($selectedPatient, 'diagnose.code') }}</strong>
                                                {{ data_get($selectedPatient, 'diagnose.title') ?: data_get($selectedPatient, 'diagnose.name') }}.
                                                Если отдельный диагноз для направления ещё не выбран, текущий диагноз подставляется автоматически. Медицинский работник может изменить его перед сохранением или печатью.
                                            </span>
                                        @elseif($selectedPatient)
                                            <i class="bi bi-clipboard2-check"></i>
                                            <span>
                                                Выберите код диагноза по МКБ для направления.
                                            </span>
                                        @else
                                            <i class="bi bi-info-circle"></i>
                                            <span>
                                                Сначала выберите пациента, затем укажите код диагноза по МКБ.
                                            </span>
                                        @endif
                                    </div>

                                    <div class="hospital-preliminary-fields">
                                        <div class="hospital-field hospital-field--full">
                                            <label class="hospital-label" for="diagnosisCode">
                                                Код диагноза по МКБ <span class="text-danger">*</span>
                                            </label>

                                            <select id="diagnosisCode"
                                                    name="diagnosis_code"
                                                    form="hospitalReferralForm"
                                                    class="form-select hospital-control"
                                                    required
                                                    data-preliminary-diagnosis-code>
                                                <option value="">— Выберите код диагноза —</option>

                                                {{-- Для полного 057/у контроллер должен передавать $diagnoses со всем справочником МКБ.
                                                     $zDiagnoses оставлен как fallback для совместимости с текущим backend. --}}
                                                @foreach(($diagnoses ?? $zDiagnoses) as $diagnosis)
                                                    <option value="{{ $diagnosis->code }}"
                                                            data-title="{{ $diagnosis->title }}"
                                                        {{ old('diagnosis_code', $activeDraft?->diagnosis_code ?: data_get($selectedPatient, 'diagnose.code')) === $diagnosis->code ? 'selected' : '' }}>
                                                        {{ $diagnosis->code }} — {{ $diagnosis->title }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input id="diagnosisTitle"
                                                   type="hidden"
                                                   name="diagnosis_title"
                                                   form="hospitalReferralForm"
                                                   value="{{ old('diagnosis_title', $activeDraft?->diagnosis_title ?: (data_get($selectedPatient, 'diagnose.title') ?: data_get($selectedPatient, 'diagnose.name'))) }}"
                                                   data-preliminary-diagnosis-title>

                                            <div class="hospital-field-hint">
                                                Выберите код диагноза по МКБ из справочника diagnoses.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hospital-preliminary-ai-note" data-preliminary-ai-note>
                                        <i class="bi bi-shield-check"></i>
                                        По умолчанию используется текущий диагноз из карты пациента; медицинский работник может выбрать другой код. ИИ не меняет диагноз автоматически.
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
                                                    <small>Только как клинический контекст; код диагноза в 057/у подтверждает медицинский работник</small>
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
                                                При первичном обращении этот текст используется как клинический контекст; официальный код диагноза подтверждает медицинский работник
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
                                                Сначала Qwen подготовит проект клинического текста, затем MedGemma отдельно
                                                перепроверит клиническую согласованность, срочность и недостающие сведения.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hospital-form-actions hospital-form-actions--simple hospital-form-actions--always-visible"
                                 aria-label="Действия с направлением">
                                <button type="button"
                                        class="hospital-save-button hospital-save-button--secondary"
                                        data-print-blank>
                                    <i class="bi bi-file-earmark"></i>
                                    Печатать пустой бланк
                                </button>

                                <button type="button"
                                        class="hospital-save-button hospital-save-button--secondary"
                                        data-print-filled>
                                    <i class="bi bi-printer"></i>
                                    Печатать заполненный бланк
                                </button>

                                <button type="submit"
                                        class="hospital-save-button"
                                        form="hospitalReferralForm">
                                    <i class="bi bi-check2-circle"></i>
                                    Сохранить
                                </button>
                            </div>

                            <div class="hospital-tab-panel" data-hospital-panel="manual">
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
                                    <input type="hidden" name="referral_purpose" value="hospitalization">
                                    <input type="hidden" name="care_form_code" value="{{ $careFormCode }}">
                                    <input type="hidden" name="care_type" value="{{ $careTypeCode }}">
                                    <input type="hidden" name="care_conditions" value="{{ $careConditionsCode }}">
                                    {{-- ВАЖНО: StoreHospitalizationReferralRequest/контроллер должны валидировать
                                         и сохранять care_form_code, care_type, care_conditions и referral_purpose. --}}

                                    <input type="hidden"
                                           name="referral_id"
                                           value="{{ old('referral_id', $activeDraft?->id) }}"
                                           data-referral-id>
                                    <input type="hidden"
                                           name="patient_id"
                                           value="{{ old('patient_id', $selectedPatient?->id) }}"
                                           data-form-patient-id>
                                    <input type="hidden"
                                           name="source_mode"
                                           value="{{ old('source_mode', $activeDraft?->source_mode ?: 'manual') }}"
                                           data-source-mode>
                                    <input type="hidden"
                                           name="ai_analysis"
                                           value="{{ old('ai_analysis', is_array($activeDraft?->ai_analysis) ? json_encode($activeDraft->ai_analysis, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ($activeDraft?->ai_analysis ?? '')) }}"
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
                                                        <option value="{{ $value }}" @selected(old('status', $activeDraft?->status ?: 'draft') === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="hospital-field hospital-field--full">
                                            <div class="alert alert-light border mb-0 small" role="note">
                                                По 057/у сведения о ФИО, дате рождения, поле, регистрации по месту
                                                жительства/пребывания, местности и занятости заполняются из медицинской карты пациента.
                                                Также проверьте полис ОМС, дату его выдачи и страховую медицинскую организацию.
                                            </div>
                                        </div>

                                    </div>


                                    <div class="hospital-form-section">
                                        <div class="hospital-form-section__head">
                                            <span class="hospital-form-section__icon">
                                                <i class="bi bi-file-medical"></i>
                                            </span>
                                            <div>
                                                <h3>Параметры оказания медицинской помощи по 057/у</h3>
                                                <p>Эти значения должны попадать в печатную/электронную форму направления.</p>
                                            </div>
                                        </div>

                                        <div class="hospital-form-grid hospital-form-grid--2">
                                            <div class="hospital-field">
                                                <label class="hospital-label">Цель направления</label>
                                                <input class="form-control hospital-control" type="text"
                                                       value="{{ $referralPurpose }}" readonly>
                                            </div>
                                            <div class="hospital-field">
                                                <label class="hospital-label">Форма медицинской помощи</label>
                                                <input class="form-control hospital-control" type="text"
                                                       value="{{ $careFormLabel }} — {{ $careFormCode }}" readonly>
                                            </div>
                                            <div class="hospital-field">
                                                <label class="hospital-label">Вид медицинской помощи</label>
                                                <input class="form-control hospital-control" type="text"
                                                       value="{{ $careTypeLabel }} — {{ $careTypeCode }}" readonly>
                                            </div>
                                            <div class="hospital-field">
                                                <label class="hospital-label">Условия оказания</label>
                                                <input class="form-control hospital-control" type="text"
                                                       value="{{ $careConditionsLabel }} — {{ $careConditionsCode }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hospital-form-section">
                                        <div class="hospital-form-section__head">
                                            <span class="hospital-form-section__icon">
                                                <i class="bi bi-clipboard2-pulse"></i>
                                            </span>
                                            <div>
                                                <h3>Обоснование (показания) направления</h3>
                                                <p>Официальный реквизит 057/у: укажите основную причину направления и, при наличии, число назначаемых курсов (циклов) лечения.</p>
                                            </div>
                                        </div>

                                        <div class="hospital-form-grid hospital-form-grid--2">
                                            <div class="hospital-field hospital-field--full">
                                                <label class="hospital-label" for="clinicalSummary">Дополнительное клиническое описание <span class="text-muted">(внутреннее поле МИС)</span></label>
                                                <textarea id="clinicalSummary"
                                                          name="clinical_summary"
                                                          class="form-control hospital-textarea hospital-textarea--form"
                                                          rows="6"
                                                          maxlength="20000"
                                                          placeholder="Жалобы, динамика, психический и соматический статус, значимые обстоятельства...">{{ old('clinical_summary', $activeDraft?->clinical_summary) }}</textarea>
                                            </div>

                                            <div class="hospital-field hospital-field--full">
                                                <label class="hospital-label" for="hospitalReason">
                                                    Обоснование (показания) направления
                                                </label>
                                                <textarea id="hospitalReason"
                                                          name="reason"
                                                          class="form-control hospital-textarea hospital-textarea--form"
                                                          rows="5"
                                                          maxlength="12000"
                                                          placeholder="Основная причина направления; при наличии укажите число назначаемых курсов (циклов) лечения...">{{ old('reason', $activeDraft?->reason) }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hospital-form-section hospital-form-section--planned">
                                        <div class="hospital-form-section__head">
                                                <span class="hospital-form-section__icon">
                                                    <i class="bi bi-calendar2-week"></i>
                                                </span>
                                            <div>
                                                <h3>Плановая форма и дополнительные сведения</h3>
                                                <p>Форма медицинской помощи по 057/у: плановая — 3; условия для этого модуля: стационарно — 3. Остальные поля ниже являются дополнительными сведениями МИС.</p>
                                            </div>
                                        </div>

                                        <div class="hospital-form-grid hospital-form-grid--2">
                                            <div class="hospital-field">
                                                <label class="hospital-label" for="plannedAt">Предполагаемая дата и время <span class="text-muted">(доп. поле МИС)</span></label>
                                                <input id="plannedAt"
                                                       type="datetime-local"
                                                       name="planned_at"
                                                       class="form-control hospital-control"
                                                       value="{{ old('planned_at', $activeDraft?->planned_at ? \Illuminate\Support\Carbon::parse($activeDraft->planned_at)->format('Y-m-d\TH:i') : '') }}">
                                            </div>

                                            <div class="hospital-field">
                                                <label class="hospital-label" for="expectedDuration">Ожидаемая длительность <span class="text-muted">(доп. поле МИС)</span></label>
                                                <input id="expectedDuration"
                                                       type="text"
                                                       name="expected_duration"
                                                       class="form-control hospital-control"
                                                       maxlength="255"
                                                       value="{{ old('expected_duration', data_get($activeDraft?->details, 'expected_duration')) }}"
                                                       placeholder="Например, 14–21 день">
                                            </div>

                                            <div class="hospital-field hospital-field--full">
                                                <label class="hospital-label" for="treatmentGoal">Направляется для оказания медицинской помощи (цель)</label>
                                                <textarea id="treatmentGoal"
                                                          name="treatment_goal"
                                                          class="form-control hospital-textarea hospital-textarea--form"
                                                          rows="4"
                                                          maxlength="3000"
                                                          placeholder="Например: госпитализация для обследования и подбора терапии">{{ old('treatment_goal', data_get($activeDraft?->details, 'treatment_goal', 'Госпитализация')) }}</textarea>
                                            </div>

                                            <div class="hospital-field hospital-field--full">
                                                <label class="hospital-label" for="prehospitalChecklist">Подготовка и документы <span class="text-muted">(доп. поле МИС)</span></label>
                                                <textarea id="prehospitalChecklist"
                                                          name="prehospital_checklist"
                                                          class="form-control hospital-textarea hospital-textarea--form"
                                                          rows="4"
                                                          maxlength="5000"
                                                          placeholder="Необходимые обследования, заключения, анализы и документы...">{{ old('prehospital_checklist', data_get($activeDraft?->details, 'prehospital_checklist')) }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hospital-form-section">
                                        <div class="hospital-field">
                                            <label class="hospital-label" for="additionalNotes">Дополнительные сведения</label>
                                            <textarea id="additionalNotes"
                                                      name="additional_notes"
                                                      class="form-control hospital-textarea hospital-textarea--form"
                                                      rows="4"
                                                      maxlength="10000"
                                                      placeholder="Организационные детали, сопровождающее лицо, согласие, важные уточнения...">{{ old('additional_notes', data_get($activeDraft?->details, 'additional_notes')) }}</textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </section>

                        <aside class="hospital-history-card">
                            <div class="hospital-history-card__head">
                                <div>
                                    <div class="hospital-section-eyebrow">История</div>
                                    <h2>Последние направления</h2>
                                    <p>
                                        {{ $selectedPatient ? 'Показаны записи выбранного пациента.' : 'Показаны последние записи по текущему типу.' }}
                                    </p>
                                </div>
                                <span class="hospital-history-card__count">{{ $referrals->total() }}</span>
                            </div>

                            <div class="hospital-history-list">
                                @forelse($referrals as $referral)
                                    @php
                                        $patientName = collect([
                                            $referral->patient?->surname,
                                            $referral->patient?->name,
                                            $referral->patient?->patronym,
                                        ])->filter()->implode(' ');
                                        $statusClass = match($referral->status) {
                                            'sent', 'admitted' => 'is-success',
                                            'cancelled' => 'is-danger',
                                            'prepared' => 'is-primary',
                                            default => 'is-muted',
                                        };
                                    @endphp

                                    <article class="hospital-history-item">
                                        <div class="hospital-history-item__top">
                                            <span class="hospital-history-item__status {{ $statusClass }}">
                                                {{ $referral->status_label }}
                                            </span>
                                            <time datetime="{{ $referral->created_at?->toAtomString() }}">
                                                {{ $referral->created_at?->format('d.m.Y H:i') }}
                                            </time>
                                        </div>

                                        <h3>{{ $patientName !== '' ? $patientName : 'Пациент #' . $referral->patient_id }}</h3>

                                        <div class="hospital-history-item__diagnosis" title="Код диагноза по МКБ">
                                            @if($referral->diagnosis_code)
                                                <span>{{ $referral->diagnosis_code }}</span>
                                            @endif
                                            {{ $referral->diagnosis_title ?: 'Код диагноза по МКБ не указан' }}
                                        </div>

                                        <p>
                                            {{ \Illuminate\Support\Str::limit(
                                                trim((string) ($referral->reason ?: $referral->clinical_summary)),
                                                170
                                            ) ?: 'Клиническое обоснование пока не заполнено.' }}
                                        </p>

                                        <div class="hospital-history-item__footer">
                                            <span>
                                                <i class="bi bi-building"></i>
                                                {{ $referral->destination_organization ?: $referral->department ?: 'Организация не указана' }}
                                            </span>
                                            <span>
                                                <i class="bi bi-geo-alt"></i>
                                                {{ $referral->destination_address ?: 'Адрес стационара не указан' }}
                                            </span>
                                            <span>
                                                <i class="bi bi-magic"></i>
                                                {{ $referral->source_mode_label }}
                                            </span>
                                        </div>
                                    </article>
                                @empty
                                    <div class="hospital-history-empty">
                                        <div class="hospital-history-empty__icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3>Записей пока нет</h3>
                                        <p>После сохранения направление появится в этой колонке.</p>
                                    </div>
                                @endforelse
                            </div>

                            @if($referrals->hasPages())
                                <div class="hospital-history-pagination">
                                    {{ $referrals->links() }}
                                </div>
                            @endif
                        </aside>
                    </div>


                </section>
            </div>
        </div>
    </main>
@endsection
