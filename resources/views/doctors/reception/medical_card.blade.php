@extends('doctors.reception')
@section('title', $medicalCardTitle)
@section('assets')
    @parent
    @vite('resources/js/medical_card.js')
    {{-- NORHNAIM_BENEFIT_PRESCRIPTIONS_V1 --}}
    <link rel="stylesheet" href="{{ asset('css/benefit-prescriptions.css') }}?v=1">
    <script src="{{ asset('js/benefit-prescriptions.js') }}?v=1" defer></script>
@endsection
@section('sub-main')
    <div id="medicalCardRoot"
         class="medical-card-page"
         data-active-tab="{{ $medicalCardErrorTab ?? session('active_tab') }}"
         data-address-suggest-url="{{ route('api-search-address') }}">
        <div class="mc-wrap">
            @if(session('patient_transfer_success'))
                <div class="alert alert-success mc-patient-transfer-alert" role="status">
                    <i class="bi bi-check-circle me-2" aria-hidden="true"></i>
                    {{ session('patient_transfer_success') }}
                </div>
            @endif


            <div class="mc-patient-shell">
                <div class="mc-patient-head">
                    <div class="mc-patient-avatar-modern">
                        {{ $patientAvatarInitials }}
                    </div>

                    <div class="mc-patient-main">
                        <div class="mc-patient-eyebrow">
                            <i class="bi bi-person-vcard"></i>
                            Медицинская карта пациента
                        </div>

                        <h1 class="mc-patient-name">
                            {{ $patient->full_name }}
                        </h1>

                        <div class="mc-patient-subline">
                            <span class="mc-patient-chip">
                                <i class="bi bi-calendar3"></i>
                                Дата рождения: {{ $patient->birth_at }}
                            </span>

                            @if($patientAgeLabel)
                                <span class="mc-patient-chip">
                                    <i class="bi bi-hourglass-split"></i>
                                    Возраст: {{ $patientAgeLabel }}
                                </span>
                            @endif

                            @if($isMinorPatient)
                                <span class="mc-patient-chip">
                                    <i class="bi bi-person-heart"></i>
                                    Несовершеннолетний
                                </span>
                            @endif

                            <span class="mc-patient-chip">
                                <i class="bi bi-gender-ambiguous"></i>
                                Пол: {{ $patientGenderLabel }}
                            </span>

                            <span class="mc-patient-chip">
                                <i class="bi bi-clock-history"></i>
                                Пациент с: {{ $patient->created_at }}
                            </span>

                            @if($patientDisability['status'] !== 'unknown')
                                <span class="mc-patient-chip" title="Наличие инвалидности — по представленным сведениям, не из реестра">
                                    <i class="bi bi-person-badge"></i>
                                    {{ $patientDisability['presence_short_label'] }}
                                    @if($patientDisability['status'] === 'present' && $patientDisability['group'])
                                        · {{ $patientDisability['group_label'] }}
                                    @endif
                                </span>
                                @if($patientDisability['status'] === 'present')
                                    <span class="mc-patient-chip" title="Проверка документа сотрудником клиники; не государственная онлайн-проверка">
                                        <i class="bi bi-{{ $patientDisability['verified'] ? 'shield-check' : 'shield-exclamation' }}"></i>
                                        Документ МСЭ: {{ $patientDisability['document_check_short_label'] }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="mc-patient-side">
                        @if(!$patient->user_id)
                            <span class="mc-status-chip warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                Не зарегистрирован в системе
                            </span>
                        @else
                            <span class="mc-status-chip success">
                                <i class="bi bi-check2-circle"></i>
                                Зарегистрирован в системе
                            </span>
                        @endif

                        <div class="mc-patient-id">
                            ID пациента: {{ $patient->id ?? '—' }}
                        </div>
                    </div>
                </div>

                <div class="mc-patient-meta-grid">
                    <div class="mc-patient-meta-card">
                        <div class="mc-patient-meta-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>

                        <div>
                            <div class="mc-patient-meta-label">Лечащий врач</div>
                            <div class="mc-patient-doctor-line">
                                <div class="mc-patient-meta-value">
                                    {{ $assignedDoctorName }}
                                </div>
                                {{-- PATIENT_DOCTOR_TRANSFER_BUTTON_START --}}
                                @if($canTransferPatient)
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm mc-patient-doctor-transfer"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalTransferPatientDoctor"
                                            title="Передать пациента другому лечащему врачу">
                                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                                        Передать
                                    </button>
                                @endif
                                {{-- PATIENT_DOCTOR_TRANSFER_BUTTON_END --}}
                            </div>
                        </div>
                    </div>

                    <div class="mc-patient-meta-card">
                        <div class="mc-patient-meta-icon">
                            <i class="bi bi-activity"></i>
                        </div>


                        <div class="mc-patient-meta-value">
                            @if($currentDiagnosisCode)
                                <div class="fw-bold fs-5">
                                    {{ $currentDiagnosisCode }}
                                </div>

                                @if($currentDiagnosisTitle)
                                    <div class="mt-1">
                                        {{ $currentDiagnosisTitle }}
                                    </div>
                                @endif

                                <div class="mt-2">
            <span class="badge rounded-pill text-bg-light border">
                Основной диагноз
            </span>
                                </div>
                            @else
                                <div class="is-warning fw-semibold">
                                    Диагноз не установлен
                                </div>

                                <div class="small text-muted mt-1">
                                    Нет установленного основного диагноза
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mc-patient-meta-card">
                        <div class="mc-patient-meta-icon">
                            <i class="bi bi-hash"></i>
                        </div>

                        <div>
                            <div class="mc-patient-meta-label">Номер медкарты</div>
                            <div class="mc-patient-meta-value">
                                {{ $patient->medcard_number }}
                            </div>
                        </div>
                    </div>

                    <div class="mc-patient-meta-card">
                        <div class="mc-patient-meta-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>

                        <div>
                            <div class="mc-patient-meta-label">Адрес прописки</div>
                            <div class="mc-patient-meta-value">
                                {{ $patient->address_registration }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mc-tabs mc-tabs-modern">
                    <ul class="nav" id="cardTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-patient-info"
                                    type="button">
                                Общая информация
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-docs" type="button">
                                Документы
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-anamnesis"
                                    type="button">
                                Анамнез
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-visits" data-bs-toggle="tab"
                                    data-bs-target="#pane-visits" type="button" role="tab"
                                    aria-controls="pane-visits" aria-selected="false">
                                <i class="bi bi-clock-history me-1"></i> История приёмов и телемедицины
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-telemedicine-log" data-bs-toggle="tab"
                                    data-bs-target="#pane-telemedicine-log" type="button" role="tab"
                                    aria-controls="pane-telemedicine-log" aria-selected="false">
                                <i class="bi bi-camera-video me-1" aria-hidden="true"></i> Журнал телемедицины
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-labs" type="button">
                                Анализы и исследования
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-epicrisis"
                                    type="button">
                                Эпикриз
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-certificates" data-bs-toggle="tab"
                                    data-bs-target="#pane-certificates" type="button" role="tab"
                                    aria-controls="pane-certificates" aria-selected="false">
                                Справки
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-tests" type="button">
                                Тесты
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-contacts" type="button">
                                Контакты
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-presc" type="button">
                                Препараты и рецепты
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active mc-pane"
                     id="pane-patient-info"
                     role="tabpanel">
                    <div class="patient-info-dashboard">
                        <div class="patient-info-hero">
                            <div class="patient-info-hero__content">
                                <div class="patient-info-hero__eyebrow">
                                    <i class="bi bi-person-vcard"></i>
                                    Карточка пациента
                                </div>
                                <h2 class="patient-info-hero__title">Общая информация</h2>
                                <div class="patient-info-hero__subtitle">
                                    Редактирование основных регистрационных данных: ФИО, дата рождения, пол,
                                    документ, удостоверяющий личность, адреса, контакты и номер медицинской карты.
                                    @if($isMinorPatient)
                                        Для несовершеннолетнего дополнительно отображаются сведения о законном представителе.
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="patient-info-side-card">
                            <div class="patient-info-side-card__title">Краткая сводка</div>
                            <div class="patient-info-summary-list">
                                <div class="patient-info-summary-item">
                                    <div class="patient-info-summary-item__icon"><i class="bi bi-hash"></i></div>
                                    <div>
                                        <div class="patient-info-summary-item__label">Номер медкарты</div>
                                        <div class="patient-info-summary-item__value">
                                            {{ $patient->medcard_number }}
                                        </div>
                                    </div>
                                </div>
                                <div class="patient-info-summary-item">
                                    <div class="patient-info-summary-item__icon"><i class="bi bi-geo-alt"></i></div>
                                    <div>
                                        <div class="patient-info-summary-item__label">Адрес прописки</div>
                                        <div class="patient-info-summary-item__value">
                                            {{ $patient->address_registration }}
                                        </div>
                                    </div>
                                </div>
                                <div class="patient-info-summary-item">
                                    <div class="patient-info-summary-item__icon"><i class="bi bi-house-heart"></i></div>
                                    <div>
                                        <div class="patient-info-summary-item__label">Фактический адрес</div>
                                        <div class="patient-info-summary-item__value">
                                            {{ $patient->address_residence ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                                @if($isMinorPatient)
                                    <div class="patient-info-summary-item">
                                        <div class="patient-info-summary-item__icon"><i class="bi bi-people"></i></div>
                                        <div>
                                            <div class="patient-info-summary-item__label">Основной представитель</div>
                                            <div class="patient-info-summary-item__value">
                                                {{ $primaryRepresentativeFullName ?: 'Не указан' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="patient-info-summary-item">
                                        <div class="patient-info-summary-item__icon"><i class="bi bi-building"></i></div>
                                        <div>
                                            <div class="patient-info-summary-item__label">Место работы</div>
                                            <div class="patient-info-summary-item__value">
                                                {{ $patient->job_organization ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="patient-info-summary-item">
                                    <div class="patient-info-summary-item__icon"><i class="bi bi-envelope"></i></div>
                                    <div>
                                        <div class="patient-info-summary-item__label">Email</div>
                                        <div class="patient-info-summary-item__value">{{ $patient->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('doctors.reception.partials.patient_disability')
                    @include('doctors.reception.partials.patient_disability_verification_action')

                    @if($patientInfoErrors->any())
                        <div class="alert alert-danger" id="patientInfoValidationErrors" role="alert">
                            Изменения не сохранены. Проверьте данные пациента:
                            <ul class="mb-0 mt-2">
                                @foreach($patientInfoErrors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="patient-info-form-card"
                          method="post"
                          data-address-suggest-url="{{ route('api-search-address') }}"
                          data-insurance-suggest-url="{{ route('api-search-organizations') }}"
                          action="{{ route('doctors.patients.medical_card.update', $patient->id) }}">
                        @csrf
                        <input type="hidden" name="_medical_card_form" value="patient_info">

                        <div class="patient-info-form-card__head">
                            <div>
                                <div class="patient-info-form-card__title">Данные пациента</div>
                                <div class="patient-info-form-card__hint">
                                    После сохранения данные обновятся в шапке медицинской карты и в регистрационных
                                    сведениях пациента.
                                </div>
                            </div>
                        </div>

                        <div class="patient-info-form-sections">
                            <section class="patient-info-section patient-info-section--main">
                                <div class="patient-info-section-title">
                                    <i class="bi bi-person-lines-fill text-primary"></i>
                                    <span class="patient-info-section-title__text">
                                        <span>Основные данные</span>
                                        <span class="patient-info-section-title__hint">ФИО, дата рождения, номер карты, СНИЛС и данные страхового полиса.</span>
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label label-required" for="patientSurname">Фамилия</label>
                                        <input id="patientSurname"
                                               type="text"
                                               name="surname"
                                               class="form-control"
                                               required
                                               maxlength="255"
                                               value="{{ old('surname', $patient->surname) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label label-required" for="patientName">Имя</label>
                                        <input id="patientName"
                                               type="text"
                                               name="name"
                                               class="form-control"
                                               required
                                               maxlength="255"
                                               value="{{ old('name', $patient->name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="patientPatronym">Отчество</label>
                                        <input id="patientPatronym"
                                               type="text"
                                               name="patronym"
                                               class="form-control"
                                               maxlength="255"
                                               value="{{ old('patronym', $patient->patronym) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label" for="patientBirthAt">Дата рождения</label>
                                        <input id="patientBirthAt"
                                               type="date"
                                               name="birth_at"
                                               class="form-control"
                                               value="{{ old('birth_at', $patientBirthDateInput) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="patientGender">Пол</label>
                                        <select id="patientGender" name="gender" class="form-select">
                                            <option
                                                value="" @selected(old('gender', $patient->gender) === null || old('gender', $patient->gender) === '')>
                                                Не указан
                                            </option>
                                            <option value="M" @selected(old('gender', $patient->gender) === 'M')>
                                                Мужской
                                            </option>
                                            <option value="F" @selected(old('gender', $patient->gender) === 'F')>
                                                Женский
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="patientBirthPlace">Место рождения</label>
                                        <input id="patientBirthPlace"
                                               type="text"
                                               name="birth_place"
                                               class="form-control"
                                               maxlength="255"
                                               placeholder="Например: г. Томск, Томская область"
                                               value="{{ old('birth_place', $patient->birth_place) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="patientMedicalCardNumber">Номер медкарты</label>
                                        <input id="patientMedicalCardNumber"
                                               type="text"
                                               name="medcard_number"
                                               class="form-control"
                                               maxlength="100"
                                               placeholder="Например: 000123"
                                               value="{{ old('medcard_number', $patient->medcard_number ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="patientSnils">СНИЛС</label>
                                        <input id="patientSnils"
                                               type="text"
                                               name="snils"
                                               class="form-control js-mask-snils"
                                               maxlength="14"
                                               inputmode="numeric"
                                               autocomplete="off"
                                               placeholder="000-000-000 00"
                                               value="{{ old('snils', $patient->snils ?? '') }}">
                                    </div>

                                    @if(!$isMinorPatient)
                                        <div class="col-md-4">
                                            <label class="form-label" for="patientEmail">Email</label>
                                            <input id="patientEmail"
                                                   type="email"
                                                   name="email"
                                                   class="form-control js-mask-email {{ $patientInfoErrors->has('email') ? 'is-invalid' : '' }}"
                                                   aria-invalid="{{ $patientInfoErrors->has('email') ? 'true' : 'false' }}"
                                                   @if($patientInfoErrors->has('email')) aria-describedby="patientEmailError" @endif
                                                   maxlength="255"
                                                   inputmode="email"
                                                   autocomplete="email"
                                                   placeholder="patient@example.ru"
                                                   value="{{ old('email', $patient->email ?? '') }}">
                                            @if($patientInfoErrors->has('email'))
                                                <div id="patientEmailError" class="invalid-feedback">
                                                    {{ $patientInfoErrors->first('email') }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <input type="hidden"
                                               name="email"
                                               value="{{ old('email', $patient->email ?? '') }}">
                                    @endif
                                    <div class="col-md-5">
                                        <label class="form-label" for="patientInsuranceCompany">Страховая
                                            организация</label>
                                        <div class="patient-info-address-wrap">
                                            <input id="patientInsuranceCompany"
                                                   type="text"
                                                   name="insurance_company"
                                                   class="form-control js-insurance-part js-insurance-autocomplete"
                                                   maxlength="255"
                                                   autocomplete="off"
                                                   data-suggest-box="#patientInsuranceCompanySuggest"
                                                   placeholder="Начните вводить название страховой"
                                                   title="Введите название или дважды кликните, чтобы открыть список популярных страховых"
                                                   value="{{ old('insurance_company', $patient->insurance_company) }}">
                                            <div id="patientInsuranceCompanySuggest"
                                                 class="patient-info-address-suggest"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="patientInsurancePolicyNumber">Номер
                                            полиса</label>
                                        <input id="patientInsurancePolicyNumber"
                                               type="text"
                                               name="oms"
                                               class="form-control js-insurance-part js-mask-policy"
                                               maxlength="32"
                                               inputmode="text"
                                               autocomplete="off"
                                               placeholder="ОМС: 16 цифр / ДМС: серия и номер"
                                               value="{{ old('oms', $patient->oms ?? '') }}">
                                    </div>
                                </div>
                            </section>

                            @if(!$isMinorPatient)
                                <section class="patient-info-section patient-info-section--main">
                                    <div class="patient-info-section-title">
                                        <i class="bi bi-briefcase text-primary"></i>
                                        <span class="patient-info-section-title__text">
                                            <span>Социальные и трудовые сведения</span>
                                            <span class="patient-info-section-title__hint">Профессия, место работы и социальные отметки пациента.</span>
                                        </span>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="patientProfession">Профессия</label>
                                            <input id="patientProfession"
                                                   type="text"
                                                   name="profession"
                                                   class="form-control"
                                                   maxlength="255"
                                                   placeholder="Например: бухгалтер, водитель, пенсионер"
                                                   value="{{ old('profession', $patient->profession) }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="patientAddressJob">Место работы / организация</label>
                                            <input id="patientAddressJob"
                                                   type="text"
                                                   name="job_organization"
                                                   class="form-control"
                                                   maxlength="500"
                                                   placeholder="Название организации или место работы"
                                                   value="{{ old('job_organization', $patient->job_organization) }}">
                                        </div>

                                        <div class="col-12">
                                            <div class="patient-info-check-grid">
                                                <label class="patient-info-check-card" for="patientSociallyDangerous">
                                                    <input type="hidden" name="socially_dangerous" value="0">
                                                    <input id="patientSociallyDangerous"
                                                           class="form-check-input"
                                                           type="checkbox"
                                                           name="socially_dangerous"
                                                           value="1"
                                                        @checked((bool)$patient->socially_dangerous)>
                                                    <span>
                                                        <span class="patient-info-check-card__title">Социально опасный</span>
                                                        <span class="patient-info-check-card__hint d-block">Отметка для регистрационных и медицинских предупреждений.</span>
                                                    </span>
                                                </label>
                                                <div class="patient-info-check-card" role="note">
                                                    <span>
                                                        <span class="patient-info-check-card__title">Инвалидность</span>
                                                        <span class="patient-info-check-card__hint d-block">Группа и подтверждение документа ведутся в отдельном блоке «Инвалидность» выше.</span>
                                                    </span>
                                                </div>

                                                <label class="patient-info-check-card" for="patientMarried">
                                                    <input type="hidden" name="married" value="0">
                                                    <input id="patientMarried"
                                                           class="form-check-input"
                                                           type="checkbox"
                                                           name="married"
                                                           value="1"
                                                        @checked((bool)$patient->married)>
                                                    <span>
                                                        <span class="patient-info-check-card__title">Состоит в браке</span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @else
                                <section class="patient-info-section patient-info-section--main">
                                    <div class="patient-info-section-title">
                                        <i class="bi bi-person-heart text-primary"></i>
                                        <span class="patient-info-section-title__text">
                                            <span>Социальные сведения ребёнка</span>
                                            <span class="patient-info-section-title__hint">Для несовершеннолетнего трудовые и семейные поля не отображаются.</span>
                                        </span>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="patient-info-check-grid">
                                                <label class="patient-info-check-card" for="patientSociallyDangerous">
                                                    <input type="hidden" name="socially_dangerous" value="0">
                                                    <input id="patientSociallyDangerous"
                                                           class="form-check-input"
                                                           type="checkbox"
                                                           name="socially_dangerous"
                                                           value="1"
                                                        @checked((bool)$patient->socially_dangerous)>
                                                    <span>
                                                        <span class="patient-info-check-card__title">Социально опасный</span>
                                                        <span class="patient-info-check-card__hint d-block">Регистрационная и медицинская отметка.</span>
                                                    </span>
                                                </label>
                                                <div class="patient-info-check-card" role="note">
                                                    <span>
                                                        <span class="patient-info-check-card__title">Инвалидность</span>
                                                        <span class="patient-info-check-card__hint d-block">Группа и подтверждение документа ведутся в отдельном блоке «Инвалидность» выше.</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @endif

                            @if(!$isMinorPatient)
                                <section class="patient-info-section patient-info-section--passport">
                                    <div class="patient-info-section-title">
                                        <i class="bi bi-passport text-primary"></i>
                                        <span class="patient-info-section-title__text">
                                            <span>Паспорт</span>
                                            <span class="patient-info-section-title__hint">Серия, номер, дата выдачи, код подразделения и орган выдачи.</span>
                                        </span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label" for="patientPassportSeries">Серия</label>
                                            <input id="patientPassportSeries"
                                                   type="text"
                                                   name="serial"
                                                   class="form-control js-passport-part js-mask-digits"
                                                   maxlength="4"
                                                   inputmode="numeric"
                                                   autocomplete="off"
                                                   data-mask-max="4"
                                                   placeholder="0000"
                                                   value="{{ old('serial', $patient->serial) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="patientPassportNumber">Номер</label>
                                            <input id="patientPassportNumber"
                                                   type="text"
                                                   name="number"
                                                   class="form-control js-passport-part js-mask-digits"
                                                   maxlength="6"
                                                   inputmode="numeric"
                                                   autocomplete="off"
                                                   data-mask-max="6"
                                                   placeholder="000000"
                                                   value="{{ old('number', $patient->number) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="patientPassportIssuedAt">Дата выдачи</label>
                                            <input id="patientPassportIssuedAt"
                                                   type="date"
                                                   name="issued_at"
                                                   class="form-control js-passport-part"
                                                   value="{{ old('issued_at', $patient->issued_at) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="patientPassportDepartmentCode">Код подразделения</label>
                                            <input id="patientPassportDepartmentCode"
                                                   type="text"
                                                   name="department_code"
                                                   class="form-control js-passport-part js-mask-department-code"
                                                   maxlength="7"
                                                   inputmode="numeric"
                                                   autocomplete="off"
                                                   placeholder="000-000"
                                                   value="{{ old('department_code', $patient->department_code) }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="patientPassportIssuedBy">Кем выдан</label>
                                            <textarea id="patientPassportIssuedBy"
                                                      name="issued_by"
                                                      class="form-control js-passport-part"
                                                      rows="2"
                                                      maxlength="500"
                                                      placeholder="Наименование органа, выдавшего паспорт">{{ old('passport_issued_by', $patient->issued_by) }}</textarea>
                                        </div>
                                    </div>
                                </section>
                            @else
                                <section class="patient-info-section patient-info-section--passport">
                                    <div class="patient-info-section-title">
                                        <i class="bi bi-person-badge text-primary"></i>
                                        <span class="patient-info-section-title__text">
                                            <span>{{ $patientIdentityDocumentLabel }}</span>
                                            <span class="patient-info-section-title__hint">Основной документ ребёнка хранится в patient_identity_documents.</span>
                                        </span>
                                    </div>

                                    @if($patientIdentityDocument)
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Тип документа</label>
                                                <div class="form-control bg-light">
                                                    {{ $patientDocumentTypeLabels[$patientIdentityDocument->document_type] ?? ($patientIdentityDocument->document_name ?: $patientIdentityDocument->document_type) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Серия</label>
                                                <div class="form-control bg-light">{{ $patientIdentityDocument->serial ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Номер</label>
                                                <div class="form-control bg-light">{{ $patientIdentityDocument->number ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Кем выдан</label>
                                                <div class="form-control bg-light mc-readonly-multiline">
                                                    {{ $patientIdentityDocument->issued_by ?: '—' }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Дата выдачи</label>
                                                <div class="form-control bg-light">{{ $patientIdentityIssuedLabel }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Основной документ ребёнка не найден. Проверьте данные, внесённые при записи в регистратуру.
                                        </div>
                                    @endif
                                </section>

                                <section class="patient-info-section patient-info-section--main">
                                    <div class="patient-info-section-title">
                                        <i class="bi bi-people text-primary"></i>
                                        <span class="patient-info-section-title__text">
                                            <span>Законный представитель</span>
                                            <span class="patient-info-section-title__hint">Основной активный представитель несовершеннолетнего пациента.</span>
                                        </span>
                                    </div>

                                    @if($primaryRepresentative)
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Кем приходится</label>
                                                <div class="form-control bg-light">
                                                    {{ $representativeTypeLabels[$primaryRepresentative->representative_type] ?? $primaryRepresentative->representative_type }}
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">ФИО представителя</label>
                                                <div class="form-control bg-light">{{ $primaryRepresentativeFullName ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Дата рождения</label>
                                                <div class="form-control bg-light">{{ $primaryRepresentativeBirthLabel }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">СНИЛС</label>
                                                <div class="form-control bg-light">{{ $primaryRepresentative->snils ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Телефон</label>
                                                <div class="form-control bg-light">{{ $primaryRepresentative->phone ?: '—' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">E-mail</label>
                                                <div class="form-control bg-light">{{ $primaryRepresentative->email ?: '—' }}</div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="fw-bold mb-3">
                                            <i class="bi bi-passport me-1"></i>
                                            Документ представителя
                                        </div>

                                        @if($representativeIdentityDocument)
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Тип документа</label>
                                                    <div class="form-control bg-light">
                                                        {{ $patientDocumentTypeLabels[$representativeIdentityDocument->document_type] ?? ($representativeIdentityDocument->document_name ?: $representativeIdentityDocument->document_type) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Серия</label>
                                                    <div class="form-control bg-light">{{ $representativeIdentityDocument->serial ?: '—' }}</div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Номер</label>
                                                    <div class="form-control bg-light">{{ $representativeIdentityDocument->number ?: '—' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Дата выдачи</label>
                                                    <div class="form-control bg-light">{{ $representativeIdentityIssuedLabel }}</div>
                                                </div>
                                                @if($representativeIdentityDocument->department_code)
                                                    <div class="col-md-4">
                                                        <label class="form-label">Код подразделения</label>
                                                        <div class="form-control bg-light">{{ $representativeIdentityDocument->department_code }}</div>
                                                    </div>
                                                @endif
                                                <div class="{{ $representativeIdentityDocument->department_code ? 'col-md-8' : 'col-12' }}">
                                                    <label class="form-label">Кем выдан</label>
                                                    <div class="form-control bg-light mc-readonly-multiline">
                                                        {{ $representativeIdentityDocument->issued_by ?: '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                Документ, удостоверяющий личность представителя, не найден.
                                            </div>
                                        @endif

                                        @if($representativeAuthorityDocument)
                                            <hr class="my-4">

                                            <div class="fw-bold mb-3">
                                                <i class="bi bi-file-earmark-check me-1"></i>
                                                Документ, подтверждающий полномочия
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Тип документа</label>
                                                    <div class="form-control bg-light">
                                                        {{ $authorityDocumentTypeLabels[$representativeAuthorityDocument->document_type] ?? ($representativeAuthorityDocument->document_name ?: $representativeAuthorityDocument->document_type) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Серия</label>
                                                    <div class="form-control bg-light">{{ $representativeAuthorityDocument->serial ?: '—' }}</div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Номер</label>
                                                    <div class="form-control bg-light">{{ $representativeAuthorityDocument->number ?: '—' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Дата выдачи</label>
                                                    <div class="form-control bg-light">{{ $representativeAuthorityIssuedLabel }}</div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Кем выдан</label>
                                                    <div class="form-control bg-light mc-readonly-multiline">
                                                        {{ $representativeAuthorityDocument->issued_by ?: '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Основной представитель несовершеннолетнего пациента не указан.
                                        </div>
                                    @endif
                                </section>
                            @endif

                            <section class="patient-info-section patient-info-section--addresses">
                                <div class="patient-info-section-title">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <span class="patient-info-section-title__text">
                                        <span>Адреса</span>
                                        <span class="patient-info-section-title__hint">Адрес прописки и фактического проживания с подсказками DaData и автоподстановкой индекса.</span>
                                    </span>
                                </div>

                                <div class="patient-info-address-card">
                                    <div class="patient-info-address-card__head">
                                        <div>
                                            <div class="patient-info-address-card__title">Адрес прописки</div>
                                            <div class="patient-info-address-card__hint">Начните вводить город, улицу,
                                                дом или квартиру — адрес можно выбрать из подсказки.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="patient-info-address-grid">
                                        <div class="patient-info-address-grid__address">
                                            <div class="patient-info-address-wrap">
                                                <label class="form-label" for="patientRegistrationAddress">Адрес с
                                                    индексом</label>
                                                <input id="patientRegistrationAddress"
                                                       type="text"
                                                       name="address_registration"
                                                       class="form-control patient-info-address-input js-address-autocomplete"
                                                       maxlength="500"
                                                       autocomplete="off"
                                                       data-postal-target="#patientRegistrationPostalCode"
                                                       data-copy-to="#patientActualAddress"
                                                       data-suggest-box="#patientRegistrationAddressSuggest"
                                                       placeholder="Например: 634000, г Томск, ул Ижевская, д 10, кв 21"
                                                       value="{{ old('address_registration', $patient->address_registration) }}">
                                                <div id="patientRegistrationAddressSuggest"
                                                     class="patient-info-address-suggest"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="patient-info-address-card">
                                    <div class="patient-info-address-card__head">
                                        <div>
                                            <div class="patient-info-address-card__title">Адрес фактического
                                                проживания
                                            </div>
                                            <div class="patient-info-address-card__hint">Заполните только если
                                                отличается от адреса прописки.
                                            </div>
                                        </div>

                                        <div class="patient-info-address-card__actions">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="sameAsRegistrationAddress"
                                                       data-source="#patientRegistrationAddress"
                                                       data-target="#patientActualAddress"
                                                    @checked($patient->address_residence !== '' && $patient->address_residence === $patient->address_registration)>
                                                <label class="form-check-label" for="sameAsRegistrationAddress">
                                                    Совпадает с пропиской
                                                </label>
                                            </div>
                                            <button type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    id="copyRegistrationAddressToActual"
                                                    data-source="#patientRegistrationAddress"
                                                    data-target="#patientActualAddress">
                                                Заполнить из прописки
                                            </button>
                                        </div>
                                    </div>

                                    <div class="patient-info-address-grid">
                                        <div class="patient-info-address-grid__address">
                                            <div class="patient-info-address-wrap">
                                                <label class="form-label" for="patientActualAddress">Адрес с
                                                    индексом</label>
                                                <input id="patientActualAddress"
                                                       type="text"
                                                       name="address_residence"
                                                       class="form-control patient-info-address-input js-address-autocomplete"
                                                       maxlength="500"
                                                       autocomplete="off"
                                                       data-postal-target="#patientActualPostalCode"
                                                       data-suggest-box="#patientActualAddressSuggest"
                                                       placeholder="Например: 634000, г Томск, ул Ижевская, д 10, кв 21"
                                                       value="{{ old('address_residence', $patient->address_residence) }}">
                                                <div id="patientActualAddressSuggest"
                                                     class="patient-info-address-suggest"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="address" id="patientLegacyAddress"
                                       value="{{ old('address', $patient->address_registration) }}">
                            </section>

                            <section class="patient-info-section patient-info-section--comment">
                                <div class="patient-info-section-title">
                                    <i class="bi bi-chat-left-text text-primary"></i>
                                    <span class="patient-info-section-title__text">
                                        <span>Комментарий</span>
                                        <span class="patient-info-section-title__hint">Дополнительные регистрационные примечания по пациенту.</span>
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="patientComment">Комментарий</label>
                                        <textarea id="patientComment"
                                                  name="comment"
                                                  class="form-control"
                                                  rows="3"
                                                  maxlength="1000"
                                                  placeholder="Важные регистрационные примечания">{{ old('comment', $patient->comment ?? '') }}</textarea>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="patient-info-form-actions">
                            <button type="reset" class="btn btn-light">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i> Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade mc-pane"
                     id="pane-docs"
                     role="tabpanel">
                    @if($otherMedicalCardErrors->any())
                        <div class="alert alert-danger">
                            При загрузке файла произошли ошибки:
                            <ul>
                                @foreach($otherMedicalCardErrors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($documents)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="h6 mb-1">Документы пациента</div>
                                <div class="small text-muted">Загруженные файлы, выписки, заключения и изображения</div>
                            </div>

                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    id="document-upload_button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalUploadDocument">
                                <i class="bi bi-upload me-1"></i> Загрузить документ
                            </button>
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="mc-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mc-documents-stat-icon">
                                            <i class="bi bi-folder2-open fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="small text-muted">Всего документов</div>
                                            <div class="h5 mb-0">{{ $patientDocumentsCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mc-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center mc-documents-stat-icon">
                                            <i class="bi bi-file-earmark-medical fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="small text-muted">Медицинские файлы</div>
                                            <div class="h5 mb-0">{{ $medicalFilesCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mc-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center mc-documents-stat-icon">
                                            <i class="bi bi-clock-history fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="small text-muted">Последнее обновление</div>
                                            <div class="fw-semibold">
                                                {{ $lastPatientDocumentUpdateLabel }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mc-card">
                        <div class="card-body">
                            @if(isset($documents) && $documents->count())
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Документ</th>
                                            <th>Комментарий</th>
                                            <th>Дата</th>
                                            <th class="text-end mc-documents-actions-col">Действия</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($documents as $document)
                                            <tr>
                                                <td class="{{ $document->medical_file ? 'mc-document-medical' : '' }}">
                                                    @switch($document->card_file_extension)
                                                        @case('doc')
                                                            <i class="bi bi-filetype-doc"></i>
                                                            @break
                                                        @case('docx')
                                                            <i class="bi bi-filetype-docx"></i>
                                                            @break
                                                        @case('pdf')
                                                            <i class="bi bi-filetype-pdf"></i>
                                                            @break
                                                        @case('txt')
                                                            <i class="bi bi-filetype-txt"></i>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <div
                                                        class="fw-semibold">{{ $document->name ?? $document->title ?? 'Документ' }}</div>
                                                    <div class="small mc-document-medical">{{ $document->medical_file ? 'Медицинский документ' : '' }}</div>
                                                </td>
                                                <td>{{ $document->description ?? '—' }}</td>
                                                <td class="text-nowrap">{{ $document->created_at?->format('d.m.Y H:i') }}</td>
                                                <td class="text-end">
                                                    <form class="d-inline-flex" action="{{ route('doctors.patients.documents.delete',
                                                            [$patient->id, $document->id]) }}" method="post">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link p-1">
                                                            <i class="bi bi-trash text-danger"></i>
                                                        </button>
                                                        <a href="{{ route('doctors.patients.documents.download', [$patient->id, $document->id]) }}"
                                                           class="btn btn-link p-1"
                                                           target="_blank"
                                                           title="Скачать документ">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    {{ $documents->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div
                                        class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 mc-documents-empty-icon">
                                        <i class="bi bi-folder2-open fs-2 text-muted"></i>
                                    </div>
                                    <div class="h6 mb-1">Документов пока нет</div>
                                    <div class="text-muted small mb-3">
                                        Здесь будут отображаться выписки, заключения, изображения и другие файлы
                                        пациента.
                                    </div>
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            id="document-upload-panel_button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalUploadDocument">
                                        <i class="bi bi-upload me-1"></i> Загрузка документов
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal fade mc-modal-ui mc-modal-ui--teal" id="modalUploadDocument" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content" method="post" enctype="multipart/form-data" action="{{ route('doctors.patients.documents.upload', [$patient->id]) }}">
                                <div class="modal-header">
                                    @csrf
                                    <input type="file" id="document-upload__upload" name="document_upload" hidden>
                                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                                    <input type="hidden" name="doctor_id" value="{{ $currentDoctorId ?? '' }}">
                                    <h5 class="modal-title">Загрузка документа</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Закрыть"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label label-required" for="documentUploadName">Название
                                            документа</label>
                                        <input id="documentUploadName"
                                               type="text"
                                               name="name"
                                               class="form-control"
                                               required
                                               maxlength="255"
                                               placeholder="Например: Заключение психиатра"
                                               value="{{ old('name') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="documentUploadComment">Комментарий</label>
                                        <textarea id="documentUploadComment"
                                                  name="description"
                                                  class="form-control"
                                                  rows="3"
                                                  maxlength="255"
                                                  placeholder="Краткое описание документа">{{ old('description') }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <input type="checkbox" name="medical_file" id="medical_file">
                                        <label for="medical_file"> Медицинский документ</label>
                                    </div>
                                    <div id="documentUploadDropzone" class="document-upload-dropzone" role="button"
                                         tabindex="0">
                                        <div class="document-upload-dropzone__icon">
                                            <i class="bi bi-cloud-arrow-up fs-3"></i>
                                        </div>
                                        <div class="fw-semibold">Перетащите файл сюда</div>
                                        <div class="small text-muted mt-1">или выберите его вручную. За один раз можно
                                            загрузить только один файл.
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-3"
                                                id="documentUploadSelectFile">
                                            <i class="bi bi-paperclip me-1"></i> Выбрать файл
                                        </button>
                                    </div>

                                    <div id="documentUploadFileInfo" class="document-upload-file">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <i class="bi bi-file-earmark-medical text-primary fs-5"></i>
                                            <div class="min-w-0">
                                                <div id="documentUploadFileName" class="document-upload-file__name">—
                                                </div>
                                                <div id="documentUploadFileMeta" class="document-upload-file__meta">—
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-link text-danger p-0"
                                                id="documentUploadClearFile" title="Убрать файл">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <div id="documentUploadError" class="alert alert-danger d-none mt-3 mb-0"></div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload me-1"></i> Загрузить
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade mc-pane" id="pane-anamnesis" role="tabpanel">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button"
                                class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAddAnamnesis">
                            <i class="bi bi-plus-lg me-1"></i> Добавить запись анамнеза
                        </button>
                    </div>

                    @forelse($anamneses as $a)
                        <div class="mc-card mb-2">
                            <div class="card-body d-flex flex-wrap gap-2">
                                <div class="me-auto">
                                    <div class="fw-semibold mb-2">{{ $a->title }} <span class="text-muted small">({{ $a->created_at?->format('d.m.Y H:i') }})</span>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        @if($a->source == 'ai')
                                            <i class="bi bi-cpu"></i> Заполнение через анализатор
                                        @elseif($a->source == 'manual')
                                            <i class="bi bi-pencil"></i> Ручное заполнение
                                        @endif
                                    </div>
                                    <div class="text-muted small mb-1">
                                        Автор:
                                        {{ $a->doctor_name ?? '—' }}
                                    </div>
                                    <div>{{ $a->preview ?? '—' }}</div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <a href="{{ route('doctors.patients.anamneses.show', [$patient->id, $a->id]) }}"
                                       class="btn btn-outline-secondary btn-sm">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border">Записей анамнеза пока нет.</div>
                    @endforelse
                </div>

                <div class="tab-pane fade mc-pane" id="pane-visits" role="tabpanel"
                     aria-labelledby="tab-visits" tabindex="0">
                    @include('doctors.reception.partials.medical_visit_confirmation')
                    @include('doctors.reception.partials.visit_history')
                </div>

                <div class="tab-pane fade mc-pane" id="pane-telemedicine-log" role="tabpanel"
                     aria-labelledby="tab-telemedicine-log" tabindex="0">
                    @include('doctors.reception.partials.telemedicine_audit_log')
                </div>

                <div class="tab-pane fade mc-pane"
                     id="pane-labs"
                     role="tabpanel">

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($otherMedicalCardErrors->any())
                        <div class="alert alert-danger">
                            <strong>Не удалось сохранить данные.</strong>

                            <ul class="mb-0 mt-2">
                                @foreach($otherMedicalCardErrors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4 align-items-stretch">

                        {{-- Лабораторные анализы --}}
                        <div class="col-xl-6">
                            <section class="mc-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="h5 mb-1">
                                                <i class="bi bi-droplet-half text-primary me-1"></i>
                                                Лабораторные анализы
                                            </div>

                                            <div class="small text-muted">
                                                Показатели, значения и референсные интервалы
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-light border">
                                {{ $researches->count() }}
                            </span>

                                            <button type="button"
                                                    class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAddLabOrder">
                                                <i class="bi bi-plus-lg me-1"></i>
                                                Добавить
                                            </button>
                                        </div>
                                    </div>

                                    @if($researches->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">
                                                <thead class="table-light">
                                                <tr>
                                                    <th>Анализ</th>
                                                    <th>Дата</th>
                                                    <th>Статус</th>
                                                    <th class="text-end"></th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach($researches as $research)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">
                                                                {{ $research->laboratory ?: 'Лабораторный анализ' }}
                                                            </div>

                                                            <div class="small text-muted">
                                                                {{ $research->sample_type ?: 'Материал не указан' }}
                                                            </div>
                                                        </td>

                                                        <td class="text-nowrap">
                                                            {{ $research->planned_at?->format('d.m.Y') ?: '—' }}
                                                        </td>

                                                        <td>
                                                            @switch($research->status)
                                                                @case('ordered')
                                                                    <span class="badge text-bg-primary">
                                                        Назначено
                                                    </span>
                                                                    @break

                                                                @case('processing')
                                                                    <span class="badge text-bg-warning">
                                                        В работе
                                                    </span>
                                                                    @break

                                                                @case('ready')
                                                                    <span class="badge text-bg-success">
                                                        Выполнен
                                                    </span>
                                                                    @break

                                                                @default
                                                                    <span class="badge text-bg-light border text-muted">
                                                        {{ $research->status }}
                                                    </span>
                                                            @endswitch
                                                        </td>

                                                        <td class="text-end text-nowrap">
                                                            @if($research->status === 'ready')
                                                                <button type="button"
                                                                        class="btn btn-link p-1"
                                                                        title="Печать результатов"
                                                                        data-research-print
                                                                        data-url="{{ route(
                                                            'doctors.patients.researches.print',
                                                            [
                                                                'patient' => $patient->id,
                                                                'labResearch' => $research->id,
                                                            ]
                                                        ) }}"
                                                                        data-research-id="{{ $research->id }}"
                                                                        data-patient-id="{{ $patient->id }}">
                                                                    <i class="bi bi-printer"></i>
                                                                </button>

                                                                <button type="button"
                                                                        class="btn btn-link p-1"
                                                                        title="Просмотреть результаты"
                                                                        data-lab-view
                                                                        data-id="{{ $research->id }}"
                                                                        data-view-url="{{ route(
                                                            'doctors.patients.researches.show',
                                                            [
                                                                'labResearch' => $research->id,
                                                            ]
                                                        ) }}"
                                                                        data-params='@json($research->params_payload ?? [])'
                                                                        data-values='@json($research->values_map ?? [])'
                                                                        data-collected-at="{{ $research->card_collected_at }}"
                                                                        data-laboratory="{{ $research->laboratory }}"
                                                                        data-comment="{{ $research->comment }}">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                        class="btn btn-link p-1"
                                                                        title="Редактировать анализ"
                                                                        data-lab-edit
                                                                        data-id="{{ $research->id }}"
                                                                        data-params='@json($research->params_payload ?? [])'
                                                                        data-values='@json($research->values_map ?? [])'
                                                                        data-planned-at="{{ $research->card_planned_at }}"
                                                                        data-priority="{{ $research->priority }}"
                                                                        data-laboratory="{{ $research->laboratory }}"
                                                                        data-comment="{{ $research->comment }}"
                                                                        data-sample-type="{{ $research->sample_type }}">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                            @endif

                                                            <button type="button"
                                                                    class="btn btn-link p-1"
                                                                    title="Удалить"
                                                                    data-lab-delete
                                                                    data-id="{{ $research->id }}"
                                                                    data-delete-url="{{ route(
                                                        'doctors.patients.researches.delete',
                                                        $research->id
                                                    ) }}">
                                                                <i class="bi bi-trash3 text-danger"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="mb-3 text-muted">
                                                <i class="bi bi-droplet fs-1"></i>
                                            </div>

                                            <div class="fw-semibold">
                                                Лабораторных анализов пока нет
                                            </div>

                                            <div class="small text-muted mt-1">
                                                Создайте направление на лабораторный анализ.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </div>

                        {{-- Инструментальные исследования --}}
                        <div class="col-xl-6"
                             id="instrumentalResearchesPanel">

                            <section class="mc-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="h5 mb-1">
                                                <i class="bi bi-heart-pulse text-danger me-1"></i>
                                                Инструментальные исследования
                                            </div>

                                            <div class="small text-muted">
                                                МРТ, КТ, ЭЭГ, ЭКГ, УЗИ и другие исследования
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-light border">
                                {{ $instrumentalResearches->count() }}
                            </span>

                                            <button type="button"
                                                    class="btn btn-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAddInstrumentalResearch">
                                                <i class="bi bi-plus-lg me-1"></i>
                                                Добавить
                                            </button>
                                        </div>
                                    </div>

                                    @if($instrumentalResearches->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">
                                                <thead class="table-light">
                                                <tr>
                                                    <th>Исследование</th>
                                                    <th>Дата</th>
                                                    <th>Статус</th>
                                                    <th class="text-end"></th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach($instrumentalResearches as $instrumentalResearch)


                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-start gap-2">
                                                <span class="badge text-bg-light border">
                                                    {{ $instrumentalResearch->type_label }}
                                                </span>

                                                                <div>
                                                                    <div class="fw-semibold">
                                                                        {{ $instrumentalResearch->name }}
                                                                    </div>

                                                                    @if($instrumentalResearch->body_area)
                                                                        <div class="small text-muted">
                                                                            {{ $instrumentalResearch->body_area }}
                                                                        </div>
                                                                    @endif

                                                                    @if($instrumentalResearch->with_contrast)
                                                                        <div class="small text-primary">
                                                                            С контрастированием
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td class="text-nowrap">
                                                            {{ $instrumentalResearch->planned_at?->format('d.m.Y') ?: '—' }}
                                                        </td>

                                                        <td>
                                            <span class="badge text-bg-{{ $instrumentalResearch->status_class }}">
                                                {{ $instrumentalResearch->status_label }}
                                            </span>
                                                        </td>

                                                        <td class="text-end text-nowrap">
                                                            <button type="button"
                                                                    class="btn btn-link p-1"
                                                                    title="Редактировать назначение"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditInstrumentalResearch"
                                                                    data-instrumental-edit
                                                                    data-payload='@json($instrumentalResearch->card_payload)'
                                                                    data-update-url="{{ route('doctors.patients.instrumentals.update', [
                                                            'patient' => $patient->id,
                                                            'instrumentalResearch' => $instrumentalResearch->id,
                                                        ]) }}">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>

                                                            @if($instrumentalResearch->status !== 'cancelled')
                                                                <button type="button"
                                                                        class="btn btn-link p-1"
                                                                        title="{{ $instrumentalResearch->status === 'ready'
                                                            ? 'Изменить заключение'
                                                            : 'Внести результат' }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalInstrumentalResult"
                                                                        data-instrumental-result
                                                                        data-payload='@json($instrumentalResearch->card_payload)'
                                                                        data-result-url="{{ route(
                                                            'doctors.patients.instrumentals.result',
                                                            [
                                                                'patient' => $patient->id,
                                                                'instrumentalResearch' => $instrumentalResearch->id,
                                                            ]
                                                        ) }}">
                                                                    <i class="bi bi-file-earmark-medical"></i>
                                                                </button>
                                                            @endif

                                                            @if($instrumentalResearch->status === 'ready')
                                                                <button type="button"
                                                                        class="btn btn-link p-1"
                                                                        title="Просмотреть заключение"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalViewInstrumentalResearch"
                                                                        data-instrumental-view
                                                                        data-id="{{ $instrumentalResearch->id }}"
                                                                        data-payload='@json($instrumentalResearch->card_payload)'
                                                                        data-viewed-url="{{ route(
                                                            'doctors.patients.instrumentals.view',
                                                            [
                                                                'patient' => $patient->id,
                                                                'instrumentalResearch' => $instrumentalResearch->id,
                                                            ]
                                                        ) }}">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                            @endif

                                                            <button type="button"
                                                                    class="btn btn-link p-1 text-danger"
                                                                    title="Удалить исследование"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalDeleteInstrumentalResearch"
                                                                    data-instrumental-delete
                                                                    data-name="{{ $instrumentalResearch->name }}"
                                                                    data-delete-url="{{ route(
                                                        'doctors.patients.instrumentals.delete',
                                                        [
                                                            'patient' => $patient->id,
                                                            'instrumentalResearch' => $instrumentalResearch->id,
                                                        ]
                                                    ) }}">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="mb-3 text-muted">
                                                <i class="bi bi-activity fs-1"></i>
                                            </div>

                                            <div class="fw-semibold">
                                                Инструментальных исследований пока нет
                                            </div>

                                            <div class="small text-muted mt-1">
                                                Назначьте МРТ, КТ, ЭЭГ, ЭКГ, УЗИ или другое исследование.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade mc-pane" id="pane-certificates" role="tabpanel"
                     aria-labelledby="tab-certificates" tabindex="0">
                    @include('doctors.reception.certificates.index')
                </div>

                <div class="tab-pane fade" id="pane-tests" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="h6 mb-0">Тесты пациента</div>
                        <div>
                            <button id="btnOpenAssignModal"
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignTestModal">
                                <i class="bi bi-plus-lg me-1"></i> Добавить назначение
                            </button>
                        </div>
                    </div>

                    <div id="testsDoctorArea"
                         data-url-assign="{{ route('doctors.patients.tests.assign', $patient) }}"
                         data-url-assignments="{{ route('doctors.patients.tests.assignments', $patient) }}"
                         data-url-cancel="{{ route('doctors.patients.sessions.cancel', '__SID__') }}"
                         data-url-result="{{ route('doctors.patients.sessions.result', '__SID__') }}"
                         data-url-finish="{{ route('doctors.patients.sessions.finish', '__SID__') }}"
                         data-url-session-show="{{ route('doctors.patients.sessions.show', '__SID__') }}"
                         data-url-start="{{ route('doctors.patients.tests.start', '__AID__') }}"
                         data-tests-search="{{ route('doctors.patients.tests.list') }}"
                         data-testing-base="{{ $patient->testing_base_url }}"
                    >

                        <div id="testsEmpty" class="text-muted small">
                            Назначений пока нет. Нажмите «Добавить назначение», чтобы назначить тест пациенту.
                        </div>

                        <div id="assignmentsList" class="mt-2"></div>
                    </div>

                    <div class="modal fade mc-modal-ui mc-modal-ui--violet" id="testStartModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered mc-test-start-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Тестирование пациента</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Закрыть"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <div class="small text-muted">Одноразовый PIN (для персонала):</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div id="test-pin" class="mc-test-pin">—
                                            </div>
                                            <span id="pin-timer" class="badge text-bg-light ms-2"></span>
                                            <button id="copy-pin" type="button"
                                                    class="btn btn-outline-secondary btn-sm">Копировать PIN
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="small text-muted">Ссылка для пациента:</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <a id="patient-link" href="#" target="_blank" rel="noopener"
                                               class="text-decoration-none">
                                                <span id="patient-link-text"></span>
                                            </a>
                                            <button id="copy-link" type="button"
                                                    class="btn btn-outline-secondary btn-sm">Скопировать
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3 text-center">
                                        <div class="small text-muted mb-2">Сканируйте QR на устройстве пациента:</div>
                                        <canvas id="pinQrCanvas" width="224" height="224"></canvas>
                                    </div>

                                    <div class="alert alert-info mb-0">
                                        Пациент попадёт только на экран тестирования по токену. Доступ к панели врача
                                        отсутствует.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button id="open-here" type="button" class="btn btn-success">Открыть на этом
                                        устройстве
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Готово
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade mc-pane" id="pane-contacts" role="tabpanel">
                    <div class="contacts-dashboard">
                        <div class="contacts-hero">
                            <div class="contacts-hero__content">
                                <div class="contacts-hero__eyebrow">
                                    <i class="bi bi-person-lines-fill"></i>
                                    Контактные лица
                                </div>

                                <h2 class="contacts-hero__title">Контакты пациента</h2>

                                <div class="contacts-hero__subtitle">
                                    Здесь можно хранить телефоны родственников, сопровождающих лиц и других контактных
                                    лиц пациента.
                                    При необходимости данные можно быстро отредактировать или удалить.
                                </div>
                            </div>
                        </div>

                        <div class="contacts-stat-card">
                            <div class="contacts-stat-card__icon">
                                <i class="bi bi-telephone-forward"></i>
                            </div>
                            <div>
                                <div class="contacts-stat-card__label">Добавлено контактов</div>
                                <div class="contacts-stat-card__value">{{ $contacts->count() }}</div>
                            </div>
                        </div>
                    </div>

                    <form class="contacts-form-card"
                          method="post"
                          action="{{ route('doctors.patients.contacts.store') }}">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                        <div class="contacts-form-card__head">
                            <div>
                                <div class="contacts-form-card__title">
                                    <i class="bi bi-plus-circle me-1 text-primary"></i>
                                    Добавить контакт
                                </div>
                                <div class="contacts-form-card__hint">
                                    Укажите имя или роль контактного лица и номер телефона.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-lg-5 col-md-6">
                                <label class="form-label" for="contactNameInput">Имя / кем приходится</label>
                                <input id="contactNameInput"
                                       type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name') }}"
                                       required
                                       maxlength="255"
                                       placeholder="Например: Мать, Иванова Мария">
                            </div>

                            <div class="col-lg-5 col-md-6">
                                <label class="form-label" for="contactPhoneInput">Телефон</label>
                                <input id="contactPhoneInput"
                                       type="tel"
                                       name="phone"
                                       class="form-control js-mask-phone"
                                       value="{{ old('phone') }}"
                                       required
                                       maxlength="18"
                                       inputmode="tel"
                                       autocomplete="tel"
                                       placeholder="+7 (999) 999-99-99">
                            </div>

                            <div class="col-lg-2 col-md-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    Добавить
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($contacts->count())
                        <div class="contacts-list-card">
                            <div class="contacts-list-head">
                                <div class="contacts-list-title">
                                    <i class="bi bi-card-list me-1 text-primary"></i>
                                    Список контактов
                                </div>

                                <div class="contacts-count-pill">
                                    <i class="bi bi-person-lines-fill"></i>
                                    {{ $contacts->count() }}
                                </div>
                            </div>

                            <div class="contacts-grid">
                                @foreach($contacts as $contact)
                                    <div class="contact-card">
                                        <div class="contact-card__top">
                                            <div class="contact-card__main">
                                                <div class="contact-card__avatar">
                                                    <i class="bi bi-person"></i>
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="contact-card__name">
                                                        {{ $contact->name }}
                                                    </div>

                                                    <a href="tel:{{ $contact->phone_href }}"
                                                       class="contact-card__phone">
                                                        <i class="bi bi-telephone"></i>
                                                        {{ $contact->phone }}
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="contact-card__actions">
                                                <form method="post"
                                                      action="{{ route('doctors.patients.contacts.destroy', [$patient->id, $contact->id]) }}"
                                                      data-confirm="Удалить контакт «{{ $contact->name }}»?">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Удалить контакт">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <details class="contacts-edit-box">
                                            <summary>
                                <span class="contacts-edit-summary">
                                    <i class="bi bi-pencil-square"></i>
                                    Редактировать контакт
                                </span>
                                            </summary>

                                            <form method="post"
                                                  action="{{ route('doctors.patients.contacts.update', [$patient->id, $contact->id]) }}"
                                                  class="contacts-edit-form">
                                                @csrf
                                                @method('PUT')

                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Имя / кем приходится</label>
                                                        <input type="text"
                                                               name="name"
                                                               class="form-control"
                                                               value="{{ old('name', $contact->name) }}"
                                                               required
                                                               maxlength="255">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label">Телефон</label>
                                                        <input type="tel"
                                                               name="phone"
                                                               class="form-control js-mask-phone"
                                                               value="{{ old('phone', $contact->phone) }}"
                                                               required
                                                               maxlength="18"
                                                               inputmode="tel"
                                                               autocomplete="tel"
                                                               placeholder="+7 (999) 999-99-99">
                                                    </div>
                                                </div>

                                                <div class="contacts-edit-form__actions">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-check2 me-1"></i>
                                                        Сохранить
                                                    </button>
                                                </div>
                                            </form>
                                        </details>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="contacts-empty-state">
                            <div class="contacts-empty-state__icon">
                                <i class="bi bi-person-plus"></i>
                            </div>

                            <div class="contacts-empty-state__title">
                                Контакты пока не добавлены
                            </div>

                            <div class="contacts-empty-state__text">
                                Добавьте родственника, сопровождающего или другое контактное лицо пациента.
                                После добавления контакт появится в этом разделе.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade mc-pane" id="pane-presc" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Выписанные рецепты</div>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#prescriptionModal"
                            data-role="open-prescription-modal"
                        >
                            <i class="bi bi-file-earmark-plus me-1"></i> Выписать рецепт
                        </button>
                    </div>

                    @include('doctors.reception.partials.benefit_prescription_list')

                    <div class="d-flex flex-column gap-4">
                        @foreach($prescriptions as $date => $datePrescriptions)

                            <div class="prescription-date-group">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="fw-semibold fs-6">
                                        {{ $prescriptionDateLabels[$date] ?? $date }}
                                    </div>

                                    <div class="flex-grow-1 border-top"></div>

                                    <span class="badge text-bg-light border">
                                {{ $datePrescriptions->count() }} препарат(ов)
                            </span>
                                </div>

                                <div class="d-flex flex-column gap-3">
                                    @foreach($datePrescriptions as $prescription)
                                        <form method="post"
                                              action="{{ route('doctors.prescriptions.repeat', $prescription->id) }}"
                                              class="mc-card border rounded-3"
                                              data-prescription-id="{{ $prescription->id }}">
                                            @csrf

                                            <div class="card-body p-3">
                                                <div
                                                    class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                    <span class="badge text-bg-light border">
                                                        {{ $prescription->card_group_label }}
                                                    </span>
                                                            <span
                                                                class="badge text-bg-success-subtle border text-success-emphasis">
                                                        {{ $prescription->prescription_form }}
                                                    </span>
                                                            @if($prescription->daily_generic_prescriptions_count > 1)
                                                                <span class="badge text-bg-warning border text-dark">
                                                            Выписывался {{ $prescription->daily_generic_prescriptions_count }} раза за день
                                                        </span>
                                                            @endif
                                                        </div>

                                                        <h6 class="mb-1">
                                                            {{ $prescription->drug?->name ?? $prescription->generic_name }}
                                                        </h6>

                                                        <div class="small text-muted">
                                                            Последняя выписка:
                                                            {{ $prescription->created_at_display }}
                                                        </div>
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <button type="button"
                                                                class="js-print-prescription btn btn-sm btn-outline-secondary"
                                                                title="Печать рецепта"
                                                                data-url="{{ route('doctors.prescriptions.print.from_table', $prescription->id) }}">
                                                            <i class="bi bi-printer"></i>
                                                        </button>

                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-primary"
                                                                title="Повторить рецепт">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <hr class="my-3">

                                                <div class="row g-3">
                                                    <div class="col-md-8">
                                                        <div class="small text-muted mb-1">Схема приёма</div>
                                                        <div class="small">
                                                            {{ $prescription->usage_instructions }}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="small text-muted mb-1">Кто выписал</div>
                                                        <div>{{ $prescription->doctor_name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade mc-pane" id="pane-epicrisis" role="tabpanel">

                    <div class="epicrisis-dashboard">
                        <div class="epicrisis-hero">
                            <div class="epicrisis-hero__content">
                                <div>
                                    <div class="epicrisis-hero__eyebrow">
                                        <i class="bi bi-journal-medical"></i>
                                        Медицинские заключения
                                    </div>
                                    <h2 class="epicrisis-hero__title">Эпикризы пациента</h2>
                                    <div class="epicrisis-hero__subtitle">
                                        Быстрый просмотр сохранённых эпикризов: дата создания, автор, диагноз по МКБ-10
                                        и краткое содержание документа.
                                    </div>
                                </div>

                                <button class="btn btn-primary btn-sm epicrisis-hero__action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAddEpicrisis">
                                    <i class="bi bi-plus-lg me-1"></i> Добавить эпикриз
                                </button>
                            </div>
                        </div>

                        <div class="epicrisis-stat-card">
                            <div class="epicrisis-stat-card__icon bg-primary-subtle text-primary">
                                <i class="bi bi-file-earmark-medical"></i>
                            </div>
                            <div>
                                <div class="epicrisis-stat-card__label">Всего эпикризов</div>
                                <div class="epicrisis-stat-card__value">{{ $epicrisesCount }}</div>
                            </div>
                        </div>

                        <div class="epicrisis-stat-card">
                            <div class="epicrisis-stat-card__icon bg-success-subtle text-success">
                                <i class="bi bi-clipboard2-pulse"></i>
                            </div>
                            <div>
                                <div class="epicrisis-stat-card__label">С диагнозом</div>
                                <div
                                    class="epicrisis-stat-card__value">{{ $epicrisesWithDiagnosisCount }}</div>
                            </div>
                        </div>

                        <div class="epicrisis-stat-card">
                            <div class="epicrisis-stat-card__icon bg-warning-subtle text-warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="epicrisis-stat-card__label">Последний эпикриз</div>
                                <div class="epicrisis-stat-card__value fs-6">
                                    {{ $lastEpicrisisDateLabel }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($epicrisesCount > 0)
                        <div class="epicrisis-list-card">
                            <div class="epicrisis-list-head">
                                <div>
                                    <div class="epicrisis-list-title">История эпикризов</div>
                                    <div class="small text-muted">Последние записи отображаются первыми</div>
                                </div>
                                <span class="epicrisis-count-pill">
                                    <i class="bi bi-list-check"></i>
                                    {{ $epicrisesCount }} записей
                                </span>
                            </div>

                            <div class="epicrisis-timeline">
                                @foreach($patient->epicrises as $epicrisis)

                                    <div class="epicrisis-item">
                                        <div class="epicrisis-date-badge">
                                            <span class="epicrisis-date-badge__day">{{ $epicrisis->day }}</span>
                                            <span class="epicrisis-date-badge__month">{{ $epicrisis->month }}</span>
                                        </div>

                                        <div class="epicrisis-item__main">
                                            <div class="epicrisis-item__top">
                                                <h3 class="epicrisis-item__title">Эпикриз
                                                    от {{ $epicrisis->date_title }}</h3>
                                                <span class="badge text-bg-light border">
                                                    {{ $epicrisis->time_display }}
                                                </span>
                                            </div>

                                            <div class="epicrisis-meta">
                                                <span class="epicrisis-meta__item">
                                                    <i class="bi bi-person-badge"></i>
                                                    {{ $epicrisis->doctor_name_display }}
                                                </span>
                                                <span class="epicrisis-meta__item">
                                                    <i class="bi bi-calendar2-week"></i>
                                                    {{ $epicrisis->date_full }}
                                                </span>
                                            </div>

                                            @if($epicrisis->mkb10 || $epicrisis->diagnosis_title_display)
                                                <div class="epicrisis-diagnosis-line">
                                                    @if($epicrisis->mkb10)
                                                        <span class="epicrisis-mkb-badge">
                                                            <i class="bi bi-activity"></i>
                                                            {{ $epicrisis->mkb10 }}
                                                        </span>
                                                    @endif
                                                    @if($epicrisis->diagnosis_title_display)
                                                        <span
                                                            class="epicrisis-diagnosis-title">{{ $epicrisis->diagnosis_title_display }}</span>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="epicrisis-preview">
                                                {{ $epicrisis->preview }}
                                            </div>
                                        </div>

                                        <div class="epicrisis-item__actions">
                                            <a class="btn btn-outline-primary btn-sm epicrisis-open-btn"
                                               href="{{ $epicrisis->show_url }}">
                                                Открыть <i class="bi bi-arrow-right-short"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="epicrisis-empty-state">
                            <div class="epicrisis-empty-state__icon">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div class="epicrisis-empty-state__title">Эпикризов пока нет</div>
                            <div class="epicrisis-empty-state__text">
                                После добавления эпикриза здесь появится аккуратная история документов с диагнозами,
                                авторами и быстрым переходом к просмотру.
                            </div>
                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAddEpicrisis">
                                <i class="bi bi-plus-lg me-1"></i> Добавить первый эпикриз
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('doctors.reception.partials.medical_visit_confirmation_modal')
    @include('doctors.reception.certificates.modals')

    <div class="modal fade mc-modal-ui mc-modal-ui--teal" id="modalAddLab" tabindex="-1" aria-hidden="true"
         data-mode="result"
         data-api-params="{{ route('api.params.search') }}"
         data-gender="{{ $patient->lab_gender }}"
         data-sex="{{ $patient->lab_sex }}"
         data-age="{{ $patient->age_for_labs }}"
         data-update-url="{{ route('doctors.patients.researches.update', ['patient' => $patient->id, 'labResearch' => '__ID__']) }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" method="post"
                  action="{{ route('doctors.patients.researches.update', ['patient' => $patient->id, 'labResearch' => '__ID__']) }}">
                @csrf

                <input type="hidden" name="order_id" id="resultOrderId">

                <div class="modal-header">
                    <h5 class="modal-title">Внести изменения — результаты анализа</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-xl-4 col-lg-5">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2">
                                    <label class="form-label">Дата/время забора</label>
                                    <input type="datetime-local" class="form-control" name="collected_at"
                                           value="{{ $patient->default_datetime }}" required>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Статус</label>
                                    <select class="form-select" name="status">
                                        <option value="ordered" selected>Назначено</option>
                                        <option value="processing">В работе</option>
                                        <option value="ready">Готово</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Лаборатория (опц.)</label>
                                    <input class="form-control" name="lab_name" placeholder="Название/отделение">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Комментарий</label>
                                    <textarea class="form-control" name="comment" rows="3"
                                              placeholder="Например: перед началом терапии"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-7">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">Параметры направления <span id="resultSelCount">(0)</span>
                                    </div>
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" id="toggleHideNormal">
                                        <label class="form-check-label" for="toggleHideNormal">Скрывать
                                            нормальные</label>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="resultParamsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th class="mc-lab-parameter-col">Параметр</th>
                                            <th class="text-nowrap">Реф. интервал</th>
                                            <th>Ед.</th>
                                            <th class="mc-lab-value-col">Значение</th>
                                            <th class="text-center">Флаг</th>
                                            <th class="text-end"></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="small text-muted mt-2">
                                    Подсветка:
                                    <span class="badge bg-success">норма</span>
                                    <span class="badge bg-warning text-dark">низкий</span>
                                    <span class="badge bg-danger">высокий</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить результаты</button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade mc-modal-ui mc-modal-ui--teal"
         id="modalViewLab"
         tabindex="-1"
         aria-hidden="true"
         data-api-params="{{ route('api.params.search') }}"
         data-gender="{{ $patient->lab_gender }}"
         data-sex="{{ $patient->lab_sex }}"
         data-age="{{ $patient->age_for_labs }}"
         data-can-fill-missing="{{ $isTreatingDoctor ? '1' : '0' }}"
         data-fill-missing-url-template="{{ route(
         'doctors.patients.researches.result.fill-missing',
         [
             'labResearch' => '__RESEARCH__',
             'parameter' => '__PARAMETER__',
         ]
     ) }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Результаты анализа</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-xl-4 col-lg-5">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2">
                                    <div class="text-muted small">Дата/время забора</div>
                                    <div id="v_collected_at" class="fw-semibold">—</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Статус</div>
                                    <div id="v_status" class="fw-semibold">Готово</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Лаборатория</div>
                                    <div id="v_lab_name">—</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Комментарий</div>
                                    <div id="v_comment">—</div>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="v_toggleHideNormal">
                                    <label class="form-check-label" for="v_toggleHideNormal">Скрывать нормальные</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-7">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">Параметры анализа <span id="v_selCount">(0)</span></div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="v_paramsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th class="mc-lab-parameter-col">Параметр</th>
                                            <th>Реф. интервал</th>
                                            <th>Ед.</th>
                                            <th class="mc-lab-value-col">Значение</th>
                                            <th class="text-center">Флаг</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="small text-muted mt-2">
                                    Подсветка:
                                    <span class="badge bg-success">норма</span>
                                    <span class="badge bg-warning text-dark">низкий</span>
                                    <span class="badge bg-danger">высокий</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade mc-modal-ui mc-modal-ui--violet"
         id="modalAddAnamnesis"
         tabindex="-1"
         aria-hidden="true"
         data-api-analyze="{{ route('doctors.patients.analyze.anamnesis') }}"
         data-patient-id="{{ $patient->id }}"
         data-open-on-load="{{ $anamnesisErrors->any() ? '1' : '0' }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" method="post"
                  action="{{ route('doctors.patients.anamneses.store', ['patient' => $patient->id]) }}">
                @csrf
                <input type="hidden" name="_medical_card_form" value="anamnesis">

                <div class="modal-header">
                    <h3 class="modal-title">Добавить запись анамнеза</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    @if($anamnesisErrors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($anamnesisErrors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row g-3 p-2">
                        <div class="mb-3">
                            <label class="form-label label-required" for="anamnesisName">Название анамнеза</label>
                            <input id="anamnesisName"
                                   type="text"
                                   name="title"
                                   class="form-control"
                                   required
                                   placeholder="Например: Первичный при поступлении"
                                   value="{{ old('title') }}">
                        </div>
                        <div class="col-lg-7 d-flex flex-column">
                            <label class="form-label">Жалобы / анамнестические данные</label>

                            <div id="editorBox" class="flex-grow-1">
                                <textarea id="anamnesisEditor"
                                          name="text"
                                          class="form-control"
                                          rows="14"
                                          placeholder="Свободный текст...">{{ old('text') }}</textarea>
                                <div id="textError" class="invalid-feedback d-none">
                                    Заполните текст анамнеза.
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label">Категория</label>
                                    <select name="category" class="form-control">
                                        <option value="0">Первичный</option>
                                        <option value="1">Вторичный</option>
                                        <option value="2">Скрининг</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="border rounded p-3 d-flex flex-column">
                                <ul class="nav nav-tabs mb-3" id="aiTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-ai" data-bs-toggle="tab"
                                                data-bs-target="#pane-ai" type="button" role="tab">
                                            Анализатор
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-manual" data-bs-toggle="tab"
                                                data-bs-target="#pane-manual" type="button" role="tab">
                                            Вручную
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content flex-grow-1">
                                    <div class="tab-pane fade show active" id="pane-ai" role="tabpanel"
                                         aria-labelledby="tab-ai">
                                        <div id="aiPanel" class="border rounded p-3 position-relative">
                                            <div class="fw-semibold fs-5">Управление анализатором</div>

                                            <div id="aiResultBox" class="mt-2 d-none"></div>

                                            <div class="row mt-3 g-2">
                                                <div class="col-6">
                                                    <button type="button" id="btnInsertSummary"
                                                            class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        Вставить сводку в текст
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="button" id="btnAppendSummary"
                                                            class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        Добавить сводку в конец
                                                    </button>
                                                </div>
                                                <div class="col-12">
                                                    <button type="button" id="btnRunAiAnalyze"
                                                            class="btn btn-primary btn-sm w-100"
                                                            data-bs-toggle="tooltip"
                                                            title="Запускает автоматический анализ текста анамнеза с помощью ИИ">
                                                        <i class="bi bi-cpu me-1"></i> Проанализировать
                                                    </button>
                                                </div>
                                            </div>

                                            <hr id="aiSep" class="my-3 d-none">

                                            <div id="aiSummary" class="mb-3 d-none"></div>

                                            <div id="aiDiagTitle" class="mb-2 fw-semibold d-none">Предложенные диагнозы
                                                (ТОП-5)
                                            </div>
                                            <div id="aiDiagnoses" class="ps-1 d-none"></div>

                                            <div id="aiSymTitle" class="mb-2 fw-semibold d-none">Найденные симптомы
                                            </div>
                                            <div id="aiSymptoms" class="ps-1 d-none"></div>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" id="ai-set-current">
                                                <label class="form-check-label" for="ai-set-current">
                                                    Назначить выбранный диагноз как текущий для пациента
                                                </label>
                                            </div>

                                            <input type="hidden" id="diagCodeInput" name="diagnosis_code">
                                            <input type="hidden" id="diagTitleInput" name="diagnosis_title">
                                            <input type="hidden" id="symptomsJsonInput" name="symptoms_json">

                                            <div id="aiLoading" class="ai-loading d-none">
                                                <div class="spinner-border" role="status" aria-hidden="true"></div>
                                                <span class="ms-2">Анализируем…</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade d-flex flex-column" id="pane-manual" role="tabpanel"
                                         aria-labelledby="tab-manual">
                                        <div class="row g-2">
                                            <div class="position-relative">
                                                <label for="diagnosisSearch" class="form-label">Диагноз</label>
                                                <input id="diagnosisSearch"
                                                       class="form-control"
                                                       autocomplete="off"
                                                       placeholder="напр., F20.0 или «Параноидная шизофрения»">
                                                <div id="diagnosisMenu" class="dropdown-menu"></div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label class="form-label m-0">Симптомы</label>
                                                <div class="text-muted small">добавляйте свои через поле ниже</div>
                                            </div>

                                            <div id="m-sym-list" class="border rounded p-1 mc-manual-symptoms"></div>

                                            <div class="input-group mt-2">
                                                <input id="m-sym-input" type="text" class="form-control"
                                                       placeholder="Новый симптом… (Enter)">
                                                <button class="btn btn-outline-secondary" id="m-sym-add" type="button">
                                                    Добавить
                                                </button>
                                                <button class="btn btn-outline-danger" id="m-sym-clear" type="button">
                                                    Очистить
                                                </button>
                                            </div>

                                            <div class="mt-2">
                                                <span class="small text-muted me-2">Быстрые:</span>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Галлюцинации">Галлюцинации
                                                </button>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Бред">Бред
                                                </button>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Социальное отстранение">Социальное отстранение
                                                </button>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Нарушение сна">Нарушение сна
                                                </button>
                                            </div>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" id="manual-set-current">
                                                <label class="form-check-label" for="manual-set-current">
                                                    Назначить выбранный диагноз как текущий для пациента
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="diagSource" name="diag_source" value="ai">
                                <input type="hidden" id="setAsCurrentInput" name="set_as_current" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Сохранить запись</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade mc-modal-ui mc-modal-ui--teal"
         id="modalAddLabOrder"
         tabindex="-1"
         aria-hidden="true"
         data-mode="order"
         data-api-params="{{ route('api.params.search') }}"
         data-gender="{{ $patient->lab_gender }}"
         data-sex="{{ $patient->lab_sex }}"
         data-age="{{ $patient->age_for_labs }}"
         data-api-save-template="{{ route('api.store.labtemplate') }}"
         data-templates='@json($templatesMap)'
         data-open-on-error="{{ $errors->labOrder->any() ? '1' : '0' }}"
         data-old-param-ids='@json(old('param_ids', []))'>

        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content"
                  method="post"
                  action="{{ route('doctors.patients.researches.store', $patient->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Создать направление на анализ</h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">

                    @if($errors->labOrder->any())
                        <div class="alert alert-danger mb-3">
                            <div class="fw-semibold mb-2">
                                Не удалось сохранить направление
                            </div>

                            <ul class="mb-0 ps-3">
                                @foreach($errors->labOrder->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">

                        <div class="col-xl-4 col-lg-5">
                            <div class="border rounded p-3 h-100">

                                <div class="mb-2">
                                    <label class="form-label">
                                        Плановая дата/время забора
                                    </label>

                                    <input type="date"
                                           class="form-control @error('planned_at', 'labOrder') is-invalid @enderror"
                                           name="planned_at"
                                           value="{{ old('planned_at', $patient->default_date) }}">

                                    @error('planned_at', 'labOrder')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="row g-2">

                                    <div class="col-6">
                                        <label class="form-label">
                                            Приоритет
                                        </label>

                                        <select class="form-select @error('priority', 'labOrder') is-invalid @enderror"
                                                name="priority">

                                            <option value="normal"
                                                @selected(old('priority', 'normal') === 'normal')>
                                                Обычный
                                            </option>

                                            <option value="urgent"
                                                @selected(old('priority') === 'urgent')>
                                                Срочно
                                            </option>

                                        </select>

                                        @error('priority', 'labOrder')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label">
                                            Статус
                                        </label>

                                        <input class="form-control"
                                               value="Назначено"
                                               disabled>
                                    </div>

                                </div>

                                <div class="mt-2">
                                    <label class="form-label">
                                        Лаборатория (опц.)
                                    </label>

                                    <input class="form-control @error('laboratory', 'labOrder') is-invalid @enderror"
                                           name="laboratory"
                                           value="{{ old('laboratory') }}"
                                           placeholder="Название/отделение">

                                    @error('laboratory', 'labOrder')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">
                                        Комментарий
                                    </label>

                                    <textarea class="form-control @error('comment', 'labOrder') is-invalid @enderror"
                                              name="comment"
                                              rows="3"
                                              placeholder="Например: перед началом терапии">{{ old('comment') }}</textarea>

                                    @error('comment', 'labOrder')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <hr>

                                <div class="mb-2">
                                    <label class="form-label">
                                        Шаблон набора
                                    </label>

                                    <div class="input-group">
                                        <select id="orderTplSelect"
                                                class="form-select">

                                            <option value="">
                                                — не выбран —
                                            </option>

                                            @foreach($labTemplates as $tpl)
                                                <option value="{{ $tpl->id }}">
                                                    {{ $tpl->name }}
                                                    {{ $tpl->doctor_id ? '（личный）' : '（общий）' }}
                                                </option>
                                            @endforeach

                                        </select>

                                        <button class="btn btn-outline-secondary"
                                                type="button"
                                                id="btnOrderTplApply">
                                            Применить
                                        </button>
                                    </div>

                                    <div class="form-text">
                                        Шаблон заполнит список параметров справа.
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">
                                        Сохранить выбранные как шаблон
                                    </label>

                                    <div class="input-group">
                                        <input id="orderTplName"
                                               type="text"
                                               class="form-control"
                                               placeholder="Название шаблона">

                                        <button class="btn btn-outline-primary"
                                                type="button"
                                                id="btnOrderTplSave">
                                            Сохранить
                                        </button>
                                    </div>

                                    <div class="form-text">
                                        Шаблон будет доступен только вам.
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-7">
                            <div class="border rounded p-3">

                                <div class="row g-2">

                                    <div class="col-md-12">
                                        <label class="form-label">
                                            Материал
                                        </label>

                                        <select id="sampleType"
                                                name="sampleType"
                                                class="form-select @error('sampleType', 'labOrder') is-invalid @enderror">

                                            <option value="моча"
                                                @selected(old('sampleType', 'моча') === 'моча')>
                                                моча
                                            </option>

                                            <option value="кровь"
                                                @selected(old('sampleType') === 'кровь')>
                                                кровь
                                            </option>

                                            <option value="кровь/моча"
                                                @selected(old('sampleType') === 'кровь/моча')>
                                                кровь/моча
                                            </option>

                                            <option value="плазма"
                                                @selected(old('sampleType') === 'плазма')>
                                                плазма
                                            </option>

                                            <option value="расчёт"
                                                @selected(old('sampleType') === 'расчёт')>
                                                расчёт
                                            </option>

                                            <option value="сыворотка"
                                                @selected(old('sampleType') === 'сыворотка')>
                                                сыворотка
                                            </option>

                                        </select>

                                        @error('sampleType', 'labOrder')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="col-md">
                                        <label class="form-label">
                                            Поиск параметра
                                        </label>

                                        <input id="paramSearch"
                                               class="form-control"
                                               autocomplete="off"
                                               placeholder="напр.: Hb, натрий, «Гемоглобин»">

                                        <div id="paramMenu"
                                             class="dropdown-menu"></div>

                                        <div class="form-text">
                                            Начните вводить название или просто кликните в поле,
                                            чтобы показать список.
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">
                                            Группа
                                        </label>

                                        <select id="paramGroup"
                                                class="form-select">
                                            <option value="">Все группы</option>
                                            <option value="cbc">ОАК</option>
                                            <option value="cbc_indices">Эритроцитарные индексы</option>
                                            <option value="diff">Лейкоформула (%)</option>
                                            <option value="diff_abs">Лейкоформула (абс.)</option>
                                            <option value="urinalysis">ОАМ</option>
                                            <option value="metabolic">Метаболические</option>
                                            <option value="liver">Печёночные</option>
                                            <option value="renal">Почечные</option>
                                            <option value="electrolytes">Электролиты</option>
                                            <option value="lipids">Липиды</option>
                                            <option value="iron">Железо</option>
                                            <option value="vitamins">Витамины</option>
                                            <option value="endocrine">Эндокринные</option>
                                            <option value="drug_levels">Уровни препаратов</option>
                                        </select>
                                    </div>

                                </div>

                                <hr>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">
                                        Выбранные параметры
                                        <span id="orderSelCount">(0)</span>
                                    </div>
                                </div>

                                @error('param_ids', 'labOrder')
                                <div class="alert alert-danger py-2 px-3 mb-2">
                                    {{ $message }}
                                </div>
                                @enderror

                                @error('param_ids.*', 'labOrder')
                                <div class="alert alert-danger py-2 px-3 mb-2">
                                    {{ $message }}
                                </div>
                                @enderror

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0"
                                           id="orderParamsTable">

                                        <thead class="table-light">
                                        <tr>
                                            <th class="mc-lab-parameter-col">
                                                Параметр
                                            </th>
                                            <th>
                                                Реф. интервал
                                            </th>
                                            <th>
                                                Ед.
                                            </th>
                                            <th class="text-end mc-lab-actions-col"></th>
                                        </tr>
                                        </thead>

                                        <tbody></tbody>

                                    </table>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline-secondary"
                            type="button"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button class="btn btn-primary"
                            type="submit"
                            name="save"
                            value="1">
                        Сохранить направление
                    </button>

                    <button class="btn btn-primary"
                            type="submit"
                            name="save_and_new"
                            value="1">
                        Сохранить и ещё
                    </button>
                </div>

            </form>
        </div>
    </div>
    <div class="modal fade epicrisis-modal-ui mc-modal-ui" id="modalAddEpicrisis" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content"
                  method="post"
                  action="{{ route('doctors.patients.epicrisis.store', $patient->id) }}"
                  data-diagnoses-url="/api/diagnoses">
                @csrf

                <div class="modal-header border-0 pb-2">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="bi bi-journal-medical me-1"></i>
                            Добавить эпикриз
                        </h5>
                        <div class="small text-muted">
                            Поиск диагноза по МКБ-10 и форматированный текст эпикриза.
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-xl-4 col-lg-5">
                            <div class="epicrisis-side-card">
                                <div class="epicrisis-section-title">Основные данные</div>

                                <div class="mb-3">
                                    <label class="form-label label-required">Дата эпикриза</label>
                                    <input name="created_at"
                                           type="date"
                                           class="form-control"
                                           value="{{ $patient->default_date }}"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="epicrisisDiagnosisSelect" class="form-label">Диагноз МКБ-10</label>

                                    <select id="epicrisisDiagnosisSelect"
                                            class="form-select"
                                            data-placeholder="Начните вводить код или название диагноза">
                                        <option value=""></option>
                                    </select>

                                    <input id="epicrisisDiagnosisFallback"
                                           type="text"
                                           class="form-control mt-2 d-none"
                                           placeholder="Если поиск недоступен, введите код МКБ-10 вручную">

                                    <input type="hidden" name="diagnose_id" id="epicrisisDiagnosisId">
                                    <input type="hidden" name="mkb10" id="epicrisisMkb10">
                                    <input type="hidden" name="diagnosis_title" id="epicrisisDiagnosisTitle">
                                </div>

                                <div class="epicrisis-selected-diagnosis d-none" id="epicrisisSelectedDiagnosis">
                                    <div class="small text-muted mb-1">Выбранный диагноз</div>
                                    <div class="fw-semibold" id="epicrisisSelectedDiagnosisCode">—</div>
                                    <div class="small" id="epicrisisSelectedDiagnosisTitle">—</div>
                                </div>

                                <hr>

                                <div class="epicrisis-section-title">Шаблон</div>

                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm w-100 mb-2"
                                        data-epicrisis-template="standard">
                                    Вставить структуру эпикриза
                                </button>

                                <div class="small text-muted">
                                    Шаблон вставляется в редактор и дальше редактируется вручную.
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-7">
                            <div class="epicrisis-editor-card">
                                <label for="epicrisisDocEditor" class="form-label label-required mb-2">
                                    Текст эпикриза
                                </label>
                                <textarea id="epicrisisTextValue" name="text" class="d-none"></textarea>
                                <div id="epicrisisDocEditor" class="epicrisis-doc-editor"></div>


                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check2-circle me-1"></i>
                        Сохранить эпикриз
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade mc-modal-ui mc-modal-ui--teal"
         id="modalAddInstrumentalResearch"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content"
                  method="post"
                  action="{{ route('doctors.patients.instrumentals.store', ['patient' => $patient->id]) }}">
                @csrf

                <input type="hidden"
                       name="_instrumental_form"
                       value="create">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">
                            Назначить исследование
                        </h5>

                        <div class="small text-muted">
                            МРТ, КТ, ЭЭГ, ЭКГ, УЗИ или другое исследование
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                Вид исследования
                            </label>

                            <select class="form-select"
                                    name="study_type"
                                    required>
                                @foreach($instrumentalResearchTypes as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">
                                Название
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="name"
                                   required
                                   maxlength="255"
                                   placeholder="Например: МРТ головного мозга">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Область исследования
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="body_area"
                                   maxlength="255"
                                   placeholder="Например: головной мозг">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Планируемая дата
                            </label>

                            <input type="datetime-local"
                                   class="form-control"
                                   name="planned_at">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Приоритет
                            </label>

                            <select class="form-select"
                                    name="priority"
                                    required>
                                @foreach($instrumentalResearchPriorities as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="hidden"
                                       name="with_contrast"
                                       value="0">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="with_contrast"
                                       value="1"
                                       id="createInstrumentalWithContrast">

                                <label class="form-check-label"
                                       for="createInstrumentalWithContrast">
                                    С контрастированием
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Организация
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="organization"
                                   maxlength="255"
                                   placeholder="Где планируется выполнить исследование">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Показания и клинический вопрос
                            </label>

                            <textarea class="form-control"
                                      name="indication"
                                      rows="4"
                                      maxlength="5000"
                                      placeholder="Причина назначения и вопрос специалисту"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Назначить исследование
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade mc-modal-ui mc-modal-ui--teal"
         id="modalEditInstrumentalResearch"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content"
                  id="editInstrumentalResearchForm"
                  method="post">
                @csrf
                @method('PUT')

                <input type="hidden"
                       name="_instrumental_form"
                       value="edit">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Редактировать исследование
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                Вид исследования
                            </label>

                            <select class="form-select"
                                    name="study_type"
                                    required>
                                @foreach($instrumentalResearchTypes as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">
                                Название
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="name"
                                   required
                                   maxlength="255">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Область исследования
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="body_area"
                                   maxlength="255">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Планируемая дата
                            </label>

                            <input type="datetime-local"
                                   class="form-control"
                                   name="planned_at">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Приоритет
                            </label>

                            <select class="form-select"
                                    name="priority"
                                    required>
                                @foreach($instrumentalResearchPriorities as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Статус
                            </label>

                            <select class="form-select"
                                    name="status"
                                    required>
                                @foreach($instrumentalResearchStatuses as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="hidden"
                                       name="with_contrast"
                                       value="0">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="with_contrast"
                                       value="1"
                                       id="editInstrumentalWithContrast">

                                <label class="form-check-label"
                                       for="editInstrumentalWithContrast">
                                    С контрастированием
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Организация
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="organization"
                                   maxlength="255">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Показания
                            </label>

                            <textarea class="form-control"
                                      name="indication"
                                      rows="4"
                                      maxlength="5000"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade instrumental-result-modal mc-modal-ui mc-modal-ui--teal"
         id="modalInstrumentalResult"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content"
                  id="instrumentalResultForm"
                  method="post"
                  enctype="multipart/form-data">
                @csrf

                <input type="hidden"
                       name="_instrumental_form"
                       value="result">

                <div class="modal-header instrumental-result-modal__header">
                    <div class="instrumental-result-modal__heading">
                        <div class="instrumental-result-modal__icon">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="instrumental-result-modal__eyebrow">
                                Инструментальное исследование
                            </div>

                            <h5 class="modal-title instrumental-result-modal__title">
                                Результат исследования
                            </h5>

                            <div class="instrumental-result-modal__subtitle"
                                 id="instrumentalResultName">
                                —
                            </div>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть">
                    </button>
                </div>

                <div class="modal-body instrumental-result-modal__body">
                    <div class="row g-4">

                        {{-- Основные сведения --}}
                        <div class="col-lg-4">
                            <div class="instrumental-result-sidebar">
                                <div class="instrumental-result-sidebar__title">
                                    <i class="bi bi-info-circle"></i>
                                    Основные сведения
                                </div>

                                <div class="instrumental-result-control">
                                    <label class="form-label">
                                        Дата выполнения
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="datetime-local"
                                           class="form-control"
                                           name="performed_at"
                                           required>
                                </div>

                                <div class="instrumental-result-control">
                                    <label class="form-label">
                                        Дата заключения
                                    </label>

                                    <input type="datetime-local"
                                           class="form-control"
                                           name="result_at">
                                </div>

                                <div class="instrumental-result-control">
                                    <label class="form-label">
                                        Организация
                                    </label>

                                    <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-building"></i>
                                    </span>

                                        <input type="text"
                                               class="form-control"
                                               name="organization"
                                               maxlength="255"
                                               placeholder="Где выполнено исследование">
                                    </div>
                                </div>

                                <div class="instrumental-result-control">
                                    <label class="form-label">
                                        Специалист
                                    </label>

                                    <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-badge"></i>
                                    </span>

                                        <input type="text"
                                               class="form-control"
                                               name="specialist_name"
                                               maxlength="255"
                                               placeholder="ФИО специалиста">
                                    </div>
                                </div>

                                <div class="instrumental-critical-box"
                                     id="instrumentalCriticalBox">

                                    <div class="form-check">
                                        <input type="hidden"
                                               name="is_critical"
                                               value="0">

                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="is_critical"
                                               value="1"
                                               id="instrumentalResultIsCritical">

                                        <label class="form-check-label"
                                               for="instrumentalResultIsCritical">
                                            Критический результат
                                        </label>
                                    </div>

                                    <div class="instrumental-critical-box__hint">
                                        Отметьте, если результат требует срочного внимания врача.
                                    </div>

                                    <div class="instrumental-critical-box__reason"
                                         id="instrumentalCriticalReasonBox">

                                        <label class="form-label">
                                            Причина критичности
                                        </label>

                                        <textarea class="form-control"
                                                  name="critical_reason"
                                                  id="instrumentalCriticalReason"
                                                  rows="3"
                                                  maxlength="5000"
                                                  placeholder="Опишите критические изменения..."></textarea>
                                    </div>
                                </div>

                                <div class="instrumental-result-sidebar__notice">
                                    <i class="bi bi-shield-check"></i>

                                    <span>
                                    После сохранения исследование получит статус
                                    «Заключение готово».
                                </span>
                                </div>
                            </div>
                        </div>

                        {{-- Результат --}}
                        <div class="col-lg-8">
                            <div class="instrumental-result-content">

                                <section class="instrumental-result-card">
                                    <div class="instrumental-result-card__header">
                                        <div class="instrumental-result-card__icon">
                                            <i class="bi bi-card-text"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-result-card__title">
                                                Описание и протокол
                                            </div>

                                            <div class="instrumental-result-card__subtitle">
                                                Ход исследования, измерения и выявленные изменения
                                            </div>
                                        </div>
                                    </div>

                                    <textarea class="form-control"
                                              name="description"
                                              rows="5"
                                              maxlength="30000"
                                              placeholder="Введите описание исследования..."></textarea>
                                </section>

                                <section class="instrumental-result-card instrumental-result-card--primary">
                                    <div class="instrumental-result-card__header">
                                        <div class="instrumental-result-card__icon">
                                            <i class="bi bi-file-earmark-check"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-result-card__title">
                                                Заключение
                                                <span class="text-danger">*</span>
                                            </div>

                                            <div class="instrumental-result-card__subtitle">
                                                Основной итог инструментального исследования
                                            </div>
                                        </div>
                                    </div>

                                    <textarea class="form-control"
                                              name="conclusion"
                                              rows="4"
                                              maxlength="30000"
                                              required
                                              placeholder="Введите заключение специалиста..."></textarea>
                                </section>

                                <section class="instrumental-result-card">
                                    <div class="instrumental-result-card__header">
                                        <div class="instrumental-result-card__icon">
                                            <i class="bi bi-list-check"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-result-card__title">
                                                Рекомендации
                                            </div>

                                            <div class="instrumental-result-card__subtitle">
                                                Дополнительные обследования и дальнейшие действия
                                            </div>
                                        </div>
                                    </div>

                                    <textarea class="form-control"
                                              name="recommendations"
                                              rows="3"
                                              maxlength="10000"
                                              placeholder="Введите рекомендации при их наличии..."></textarea>
                                </section>

                                <section class="instrumental-result-card">
                                    <div class="instrumental-result-card__header">
                                        <div class="instrumental-result-card__icon">
                                            <i class="bi bi-paperclip"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-result-card__title">
                                                Цифровые материалы
                                            </div>

                                            <div class="instrumental-result-card__subtitle">
                                                Заключение, снимки или архив исследования
                                            </div>
                                        </div>
                                    </div>

                                    <input type="file"
                                           class="d-none"
                                           name="result_files[]"
                                           id="instrumentalResultFiles"
                                           accept=".pdf,.jpg,.jpeg,.png,.webp,.zip"
                                           multiple>

                                    <label class="instrumental-result-upload"
                                           for="instrumentalResultFiles">

                                    <span class="instrumental-result-upload__icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </span>

                                        <span class="instrumental-result-upload__content">
                                        <span class="instrumental-result-upload__title">
                                            Перетащите файлы или выберите на компьютере
                                        </span>

                                        <span class="instrumental-result-upload__formats">
                                            PDF, JPG, PNG, WEBP или ZIP-архив
                                        </span>
                                    </span>

                                        <span class="instrumental-result-upload__button">
                                        Выбрать
                                    </span>
                                    </label>

                                    <div class="instrumental-result-upload__summary"
                                         id="instrumentalResultFilesSummary">
                                        Файлы не выбраны
                                    </div>

                                    <div class="instrumental-result-upload__limits">
                                        До 10 файлов, не более 100 МБ каждый
                                    </div>

                                    <div class="instrumental-result-existing"
                                         id="instrumentalResultExistingFiles">
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer instrumental-result-modal__footer">
                    <div class="instrumental-result-modal__footer-hint">
                        <i class="bi bi-info-circle"></i>
                        Поля со звёздочкой обязательны
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-light"
                                data-bs-dismiss="modal">
                            Отмена
                        </button>

                        <button type="submit"
                                class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i>
                            Сохранить заключение
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade instrumental-view-modal mc-modal-ui mc-modal-ui--teal"
         id="modalViewInstrumentalResearch"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header instrumental-view-modal__header">
                    <div class="instrumental-view-modal__heading">
                        <div class="instrumental-view-modal__icon">
                            <i class="bi bi-file-earmark-medical"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="instrumental-view-modal__eyebrow">
                                Результат инструментального исследования
                            </div>

                            <h5 class="modal-title instrumental-view-modal__title"
                                id="viewInstrumentalName">
                                Результат исследования
                            </h5>

                            <div class="instrumental-view-modal__meta">
                            <span class="instrumental-view-modal__type"
                                  id="viewInstrumentalType">
                                —
                            </span>

                                <span class="instrumental-view-modal__contrast"
                                      id="viewInstrumentalContrast">
                                —
                            </span>
                            </div>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body instrumental-view-modal__body">
                    <div class="row g-4">

                        <div class="col-lg-4">
                            <aside class="instrumental-view-summary">
                                <div class="instrumental-view-summary__title">
                                    <i class="bi bi-info-circle"></i>
                                    Основные сведения
                                </div>

                                <div class="instrumental-view-summary__items">
                                    <div class="instrumental-view-summary__item">
                                        <div class="instrumental-view-summary__item-icon">
                                            <i class="bi bi-calendar2-check"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-summary__label">
                                                Дата выполнения
                                            </div>

                                            <div class="instrumental-view-summary__value"
                                                 id="viewInstrumentalPerformedAt">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-summary__item">
                                        <div class="instrumental-view-summary__item-icon">
                                            <i class="bi bi-file-earmark-check"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-summary__label">
                                                Дата заключения
                                            </div>

                                            <div class="instrumental-view-summary__value"
                                                 id="viewInstrumentalResultAt">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-summary__item">
                                        <div class="instrumental-view-summary__item-icon">
                                            <i class="bi bi-building"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-summary__label">
                                                Организация
                                            </div>

                                            <div class="instrumental-view-summary__value"
                                                 id="viewInstrumentalOrganization">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-summary__item">
                                        <div class="instrumental-view-summary__item-icon">
                                            <i class="bi bi-person-badge"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-summary__label">
                                                Специалист
                                            </div>

                                            <div class="instrumental-view-summary__value"
                                                 id="viewInstrumentalSpecialist">
                                                —
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-summary__item">
                                        <div class="instrumental-view-summary__item-icon">
                                            <i class="bi bi-bullseye"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-summary__label">
                                                Область исследования
                                            </div>

                                            <div class="instrumental-view-summary__value"
                                                 id="viewInstrumentalBodyArea">
                                                —
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="instrumental-view-summary__notice">
                                    <i class="bi bi-shield-check"></i>

                                    <span>
                                    Заключение сохранено в медицинской карте пациента.
                                </span>
                                </div>
                            </aside>
                        </div>

                        <div class="col-lg-8">
                            <div class="instrumental-view-content">

                                <section class="instrumental-view-section">
                                    <div class="instrumental-view-section__header">
                                        <div class="instrumental-view-section__icon">
                                            <i class="bi bi-card-text"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-section__title">
                                                Описание и протокол
                                            </div>

                                            <div class="instrumental-view-section__subtitle">
                                                Ход исследования, измерения и обнаруженные изменения
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-section__text"
                                         id="viewInstrumentalDescription">
                                        —
                                    </div>
                                </section>

                                <section class="instrumental-view-section instrumental-view-section--conclusion">
                                    <div class="instrumental-view-section__header">
                                        <div class="instrumental-view-section__icon">
                                            <i class="bi bi-clipboard2-check"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-section__title">
                                                Заключение
                                            </div>

                                            <div class="instrumental-view-section__subtitle">
                                                Основной результат исследования
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-section__text instrumental-view-section__text--important"
                                         id="viewInstrumentalConclusion">
                                        —
                                    </div>
                                </section>

                                <section class="instrumental-view-section">
                                    <div class="instrumental-view-section__header">
                                        <div class="instrumental-view-section__icon">
                                            <i class="bi bi-list-check"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-section__title">
                                                Рекомендации
                                            </div>

                                            <div class="instrumental-view-section__subtitle">
                                                Дальнейшие обследования и действия
                                            </div>
                                        </div>
                                    </div>

                                    <div class="instrumental-view-section__text"
                                         id="viewInstrumentalRecommendations">
                                        —
                                    </div>
                                </section>

                                <section class="instrumental-view-section instrumental-view-section--files">
                                    <div class="instrumental-view-section__header">
                                        <div class="instrumental-view-section__icon">
                                            <i class="bi bi-paperclip"></i>
                                        </div>

                                        <div>
                                            <div class="instrumental-view-section__title">
                                                Цифровые материалы
                                            </div>

                                            <div class="instrumental-view-section__subtitle">
                                                Заключения, изображения и архивы исследования
                                            </div>
                                        </div>
                                    </div>

                                    <div id="viewInstrumentalFiles">
                                        <div class="instrumental-view-files-empty">
                                            <div class="instrumental-view-files-empty__icon">
                                                <i class="bi bi-folder2-open"></i>
                                            </div>

                                            <div>
                                                <div class="instrumental-view-files-empty__title">
                                                    Файлы не прикреплены
                                                </div>

                                                <div class="instrumental-view-files-empty__text">
                                                    У исследования отсутствуют цифровые материалы
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer instrumental-view-modal__footer">
                    <div class="instrumental-view-modal__footer-text">
                        <i class="bi bi-lock"></i>
                        Доступно только медицинским работникам
                    </div>

                    <button type="button"
                            class="btn btn-primary px-4"
                            data-bs-dismiss="modal">
                        Закрыть
                    </button>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade mc-modal-ui mc-modal-ui--danger"
         id="modalDeleteInstrumentalResearch"
         tabindex="-1"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content"
                  id="deleteInstrumentalResearchForm"
                  method="post">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        Удалить исследование
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="mc-modal-delete-notice" role="alert">
                        <span class="mc-modal-delete-notice__icon" aria-hidden="true">
                            <i class="bi bi-exclamation-triangle"></i>
                        </span>
                        <div>
                            <div class="mc-modal-delete-notice__title">Подтвердите удаление</div>
                            <div>Удалить исследование «<strong id="deleteInstrumentalResearchName"></strong>»?</div>
                            <div class="mc-modal-delete-notice__hint">
                                Исследование исчезнет из списка пациента. Убедитесь, что выбрали нужную запись.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit"
                            class="btn btn-danger">
                        Удалить
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade mc-modal-ui mc-modal-ui--violet" id="modalViewResult" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewResultTitle">Результат теста</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body" id="viewResultBody">

                </div>
                <div class="modal-footer">
                    <div class="me-auto small text-muted" id="viewResultFlags"></div>
                    <button id="btnVerifyResult" type="button" class="btn btn-success btn-sm">Верифицировать</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Закрыть
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3 mc-toast-stack">
        <div id="toastSaved" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Сохранено</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button>
            </div>
        </div>
    </div>

    <div class="modal fade assign-test-modal-ui mc-modal-ui mc-modal-ui--violet"
         id="assignTestModal"
         tabindex="-1"
         aria-hidden="true"
         aria-labelledby="assignTestTitle">

        <div class="modal-dialog modal-xl modal-dialog-centered">
            <form id="assignTestForm"
                  class="modal-content"
                  action="{{ route('doctors.patients.tests.assign', $patient->id) }}"
                  method="post">
                @csrf

                <input type="hidden" name="access_source_type" id="assignTestAccessSourceType">
                <input type="hidden" name="access_rule_id" id="assignTestAccessRuleId">
                <input type="hidden" name="practice_context" id="assignTestPracticeContext">

                <div class="modal-header assign-test-modal__header">
                    <div>
                        <div class="assign-test-modal__eyebrow">
                            <i class="bi bi-clipboard2-pulse"></i>
                            Тестирование пациента
                        </div>

                        <h5 class="modal-title assign-test-modal__title" id="assignTestTitle">
                            Назначить тест
                        </h5>

                        <div class="assign-test-modal__subtitle">
                            Выберите тест, который нужно назначить пациенту. В списке отображаются только доступные вам
                            варианты.
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body assign-test-modal__body">
                    <div class="assign-test-grid">
                        <div class="assign-test-main-card">
                            <div class="assign-test-section-title">
                                <i class="bi bi-search"></i>
                                Выбор теста
                            </div>

                            <label class="form-label" for="assignTestSelect">
                                Тест для назначения
                            </label>

                            <select id="assignTestSelect"
                                    name="test_id"
                                    class="form-select"
                                    placeholder="Начните вводить название теста..."
                                    data-source="{{ route('api.search.tests') }}">
                            </select>

                            <div class="assign-test-help">
                                <strong>Подсказка:</strong> после выбора теста справа появится краткая информация о нём
                                и доступных действиях.
                            </div>

                            <div class="assign-test-source-list">
                                <div class="assign-test-source-list__title">
                                    Какие тесты могут быть доступны
                                </div>

                                <div class="assign-test-source-item">
                                <span class="assign-test-source-item__icon system">
                                    <i class="bi bi-box-seam"></i>
                                </span>
                                    <div>
                                        <div class="assign-test-source-item__title">Общие тесты</div>
                                        <div class="assign-test-source-item__text">
                                            Стандартные тесты, доступные для назначения.
                                        </div>
                                    </div>
                                </div>

                                <div class="assign-test-source-item">
                                <span class="assign-test-source-item__icon owner">
                                    <i class="bi bi-person-check"></i>
                                </span>
                                    <div>
                                        <div class="assign-test-source-item__title">Мои тесты</div>
                                        <div class="assign-test-source-item__text">
                                            Тесты, которые были созданы вами.
                                        </div>
                                    </div>
                                </div>

                                <div class="assign-test-source-item">
                                <span class="assign-test-source-item__icon clinic">
                                    <i class="bi bi-building-check"></i>
                                </span>
                                    <div>
                                        <div class="assign-test-source-item__title">Тесты организации</div>
                                        <div class="assign-test-source-item__text">
                                            Тесты, доступные в рамках вашей клиники.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="assign-test-access-card">
                            <div class="assign-test-access-empty" id="assignTestAccessEmpty">
                                <div class="assign-test-access-empty__icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>

                                <div class="assign-test-access-empty__title">
                                    Тест не выбран
                                </div>

                                <div class="assign-test-access-empty__text">
                                    Выберите тест слева. Здесь появится его описание, источник доступности и возможные
                                    действия.
                                </div>
                            </div>

                            <div class="assign-test-access-details d-none" id="assignTestAccessDetails">
                                <div class="assign-test-selected-head">
                                    <div class="assign-test-type-icon" id="assignTestTypeIcon">
                                        <i class="bi bi-ui-checks"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="assign-test-selected-title" id="assignTestName">
                                            —
                                        </div>

                                        <div class="assign-test-selected-meta" id="assignTestMeta">
                                            —
                                        </div>
                                    </div>
                                </div>

                                <div class="assign-test-access-source">
                                    <div class="assign-test-access-source__label">
                                        Доступность теста
                                    </div>

                                    <div class="assign-test-access-source__value" id="assignTestAccessLabel">
                                        —
                                    </div>

                                    <div class="assign-test-access-source__hint" id="assignTestAccessHint">
                                        —
                                    </div>
                                </div>

                                <div class="assign-test-permissions">
                                    <div class="assign-test-permission" id="assignCanAssign">
                                        <i class="bi bi-check2-circle"></i>
                                        Можно назначить пациенту
                                    </div>

                                    <div class="assign-test-permission" id="assignCanConduct">
                                        <i class="bi bi-play-circle"></i>
                                        Можно провести тестирование
                                    </div>

                                    <div class="assign-test-permission" id="assignCanViewResults">
                                        <i class="bi bi-eye"></i>
                                        Результаты будут доступны врачу
                                    </div>
                                </div>

                                <div class="assign-test-warning d-none" id="assignTestWarning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer assign-test-modal__footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            id="assignTestSubmitBtn"
                            disabled>
                        <i class="bi bi-plus-lg me-1"></i>
                        Назначить
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div
        class="modal fade prescription-modal-ui mc-modal-ui"
        id="prescriptionModal"
        tabindex="-1"
        aria-labelledby="prescriptionModalTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog prescription-modal-compact">
            <div class="modal-content">
                <form
                    id="patientPrescriptionForm"
                    method="POST"
                    action="{{ route('doctors.prescriptions.store') }}"
                    data-suggestions-url="{{ url('/api/doctors/prescription-autofill') }}"
                >
                    @csrf

                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    <input type="hidden" name="usage_instructions" id="usage_instructions__">
                    <input type="hidden" name="birth_at" id="birth_at__" value="{{ $prescriptionBirthDateValue }}">
                    <input
                        type="hidden"
                        name="diagnosis_code"
                        id="diagnosis_code"
                        value="{{ $prescriptionDiagnosisCode }}"
                    >
                    <input
                        type="hidden"
                        name="indicated_in_russia"
                        id="indicated_in_russia_hidden"
                        value="0"
                    >
                    <input
                        type="hidden"
                        name="indicated_by_fda"
                        id="indicated_by_fda_hidden"
                        value="0"
                    >

                    <div class="modal-header">
                        <div class="presc-modal-heading">
                            <div class="presc-modal-heading__icon">
                                <i class="bi bi-file-earmark-medical"></i>
                            </div>

                            <div>
                                <h2 class="modal-title" id="prescriptionModalTitle">
                                    Выписать рецепт
                                </h2>
                                <div class="presc-modal-subtitle">
                                    Назначение препарата и оформление рецептурного бланка
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div
                            class="alert alert-danger d-none"
                            id="prescription_form_alert"
                            role="alert"
                        ></div>

                        <div class="presc-layout">
                            <div class="presc-main">
                                @include('doctors.reception.partials.benefit_prescription_fields')
                                <section class="presc-section">
                                    <div class="presc-section-head">
                                        <div>
                                            <div class="presc-section-title">
                                                Статус показания
                                            </div>
                                            <div class="presc-section-hint">
                                                Выберите режим фильтрации списка препаратов
                                            </div>
                                        </div>
                                    </div>

                                    <div class="presc-indication-switch">
                                        <label class="presc-radio-card">
                                            <input
                                                type="radio"
                                                name="indication_source"
                                                value=""
                                                checked
                                            >
                                            <span class="presc-radio-card__body">
                                            <span class="presc-radio-card__icon">
                                                <i class="bi bi-list-ul"></i>
                                            </span>
                                            <span>
                                                <span class="presc-radio-card__title">
                                                    Не указывать
                                                </span>
                                                <span class="presc-radio-card__text">
                                                    Все доступные препараты
                                                </span>
                                            </span>
                                        </span>
                                        </label>

                                        <label class="presc-radio-card">
                                            <input
                                                type="radio"
                                                name="indication_source"
                                                value="russia"
                                            >
                                            <span class="presc-radio-card__body">
                                            <span class="presc-radio-card__icon">
                                                <i class="bi bi-shield-check"></i>
                                            </span>
                                            <span>
                                                <span class="presc-radio-card__title">
                                                    Показан в РФ
                                                </span>
                                                <span class="presc-radio-card__text">
                                                    По рекомендациям РФ
                                                </span>
                                            </span>
                                        </span>
                                        </label>

                                        <label class="presc-radio-card">
                                            <input
                                                type="radio"
                                                name="indication_source"
                                                value="fda"
                                            >
                                            <span class="presc-radio-card__body">
                                            <span class="presc-radio-card__icon">
                                                <i class="bi bi-patch-check"></i>
                                            </span>
                                            <span>
                                                <span class="presc-radio-card__title">
                                                    Показан FDA
                                                </span>
                                                <span class="presc-radio-card__text">
                                                    По рекомендациям FDA
                                                </span>
                                            </span>
                                        </span>
                                        </label>
                                    </div>
                                </section>

                                <section class="presc-section">
                                    <div class="presc-section-title">
                                        Пациент и препарат
                                    </div>

                                    <div class="presc-patient-card">
                                        <div class="presc-patient-card__avatar">
                                            {{ $prescriptionPatientInitials !== '' ? $prescriptionPatientInitials : 'П' }}
                                        </div>

                                        <div class="presc-patient-card__content">
                                            <div class="presc-patient-card__name">
                                                {{ $prescriptionPatientName !== '' ? $prescriptionPatientName : 'Пациент #' . $patient->id }}
                                            </div>
                                            <div class="presc-patient-card__meta">
                                            <span>
                                                <i class="bi bi-calendar3"></i>
                                                {{ $prescriptionBirthDate }}
                                            </span>

                                                @if($prescriptionDiagnosisCode)
                                                    <span>
                                                    <i class="bi bi-clipboard2-pulse"></i>
                                                    МКБ-10: {{ $prescriptionDiagnosisCode }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="presc-field mt-3">
                                        <label for="drug_id_modal" class="form-label">
                                            Лекарство
                                        </label>

                                        <div class="presc-drug-picker">
                                            <select
                                                id="drug_id_modal"
                                                class="form-select"
                                                name="drug_id"
                                                required
                                                data-source="{{ route('api.drugs.search') }}"
                                                data-placeholder="Начните вводить название препарата"
                                            >
                                                <option value=""></option>
                                            </select>
                                        </div>

                                        <div class="form-text" id="drug_select_hint">
                                            Поиск выполняется по русскому названию и МНН.
                                        </div>
                                    </div>

                                    <div
                                        id="strict_message_modal"
                                        class="presc-strict-warning d-none"
                                    >
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <span>
                                        Препарат оформляется на бланке
                                        <strong>№ 148-1/у-88</strong>
                                    </span>
                                    </div>
                                </section>

                                <section class="presc-section">
                                    <div class="presc-section-title">
                                        Параметры рецепта
                                    </div>

                                    <div class="presc-fields-grid">
                                        <div class="presc-field">
                                            <label for="drug_form_modal" class="form-label">
                                                Форма
                                            </label>
                                            <select
                                                id="drug_form_modal"
                                                name="drug_form"
                                                class="form-select"
                                                disabled
                                                required
                                            >
                                                <option value="">Выберите форму</option>
                                            </select>
                                        </div>

                                        <div class="presc-field">
                                            <label for="dosage_modal" class="form-label">
                                                Дозировка
                                            </label>
                                            <select
                                                id="dosage_modal"
                                                name="dosage"
                                                class="form-select"
                                                disabled
                                                required
                                            >
                                                <option value="">Выберите дозировку</option>
                                            </select>
                                        </div>

                                        <div class="presc-field">
                                            <label for="quantity_modal" class="form-label">
                                                Количество формы
                                            </label>
                                            <select
                                                id="quantity_modal"
                                                name="quantity"
                                                class="form-select"
                                                disabled
                                                required
                                            >
                                                <option value="">Выберите количество</option>
                                            </select>
                                        </div>

                                        <div class="presc-field">
                                            <label for="standard_modal" class="form-label">
                                                Количество стандартов
                                            </label>
                                            <input
                                                class="form-control"
                                                type="number"
                                                name="standard"
                                                id="standard_modal"
                                                min="1"
                                                value="1"
                                                disabled
                                                required
                                            >
                                        </div>

                                        <div
                                            class="presc-field presc-field--validity"
                                            id="validity_block_modal"
                                        >
                                            <label for="validity_period_modal" class="form-label">
                                                Срок действия
                                            </label>
                                            <select
                                                class="form-select"
                                                id="validity_period_modal"
                                                name="validity_period"
                                            >
                                                <option value="60">60 дней</option>
                                                <option value="365">1 год</option>
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                <section class="presc-section">
                                    <div class="presc-section-title">
                                        Схема приёма
                                    </div>

                                    <div class="presc-regimen">
                                        <span class="presc-regimen__label">По</span>

                                        <input
                                            class="form-control presc-regimen__input"
                                            type="number"
                                            name="taking_drug"
                                            id="taking_drug_modal"
                                            min="1"
                                            max="4"
                                            value="1"
                                            disabled
                                        >

                                        <span
                                            id="drug_type_modal"
                                            class="presc-regimen__text"
                                        >
                                        единице препарата
                                    </span>

                                        <select
                                            id="taking_count_modal"
                                            name="taking_count"
                                            class="form-select presc-regimen__select"
                                            disabled
                                        >
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>

                                        <span
                                            id="taking_time_modal"
                                            class="presc-regimen__text"
                                        >
                                        раз
                                    </span>

                                        <span class="presc-regimen__text">
                                        в день, курс
                                    </span>

                                        <span
                                            id="days_count_modal"
                                            class="presc-regimen__badge"
                                        >
                                        —
                                    </span>

                                        <span
                                            id="days_label_modal"
                                            class="presc-regimen__text"
                                        >
                                        дней
                                    </span>

                                        <select
                                            class="form-select presc-regimen__select presc-regimen__select--meal"
                                            name="taking_time_meal"
                                            id="taking_time_meal_modal"
                                            disabled
                                        >
                                            <option value="1">после</option>
                                            <option value="2">до</option>
                                        </select>

                                        <span class="presc-regimen__text">
                                        еды
                                    </span>
                                    </div>
                                </section>
                            </div>

                            <aside
                                class="presc-side-card"
                                id="prescriptionSuggestionsCard"
                            >
                                <div class="presc-side-card__head">
                                    <div class="presc-side-card__icon">
                                        <i class="bi bi-stars"></i>
                                    </div>

                                    <div>
                                        <div class="presc-side-card__title">
                                            Что можно назначить по МНН
                                        </div>
                                        <div class="presc-side-card__hint">
                                            Подбор по диагнозу, возрасту и ограничениям
                                        </div>
                                    </div>
                                </div>

                                <div class="presc-side-card__toolbar">
                                <span
                                    class="presc-filter-badge"
                                    id="prescriptionSuggestionsFilterLabel"
                                >
                                    Без фильтра показаний
                                </span>

                                    <span class="presc-count-badge">
                                    <span id="prescriptionSuggestionsCount">0</span>
                                    препаратов
                                </span>
                                </div>

                                <div
                                    id="prescriptionSuggestionsLoading"
                                    class="presc-suggestions-state d-none"
                                >
                                    <span class="spinner-border spinner-border-sm"></span>
                                    <span>Подбираем препараты…</span>
                                </div>

                                <div
                                    id="prescriptionSuggestionsEmpty"
                                    class="presc-suggestions-state"
                                >
                                    <i class="bi bi-capsule"></i>
                                    <span>
                                    Подходящие рекомендации появятся здесь.
                                </span>
                                </div>

                                <div
                                    id="prescriptionSuggestionsList"
                                    class="d-none"
                                ></div>
                            </aside>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="presc-footer-note">
                            <i class="bi bi-shield-check"></i>
                            Проверьте форму, дозировку и схему приёма перед сохранением
                        </div>

                        <button
                            type="button"
                            class="btn btn-light presc-btn"
                            data-bs-dismiss="modal"
                        >
                            Отмена
                        </button>

                        @include('doctors.reception.partials.benefit_prescription_actions')

                        <button
                            id="printRecipeButton_modal"
                            type="button"
                            class="btn btn-outline-success presc-btn"
                            disabled
                        >
                            <i class="bi bi-printer"></i>
                            Распечатать
                        </button>

                        <button
                            id="prescriptionSubmitButton"
                            type="submit"
                            class="btn btn-primary presc-btn presc-btn--primary"
                        >
                            <i class="bi bi-journal-check"></i>
                            Выписать рецепт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- PATIENT_DOCTOR_TRANSFER_MODAL_INCLUDE --}}
    @include('doctors.reception.partials.patient_doctor_transfer_modal')
@endsection
