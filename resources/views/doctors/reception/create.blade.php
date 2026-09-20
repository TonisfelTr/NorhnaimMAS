@extends('doctors.reception')

@section('title', 'Записать в регистратуру')

@section('assets')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <style>
        .reception-create-page {
            padding: 28px 0 42px;
        }

        .reception-page-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .reception-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
            padding: 24px 26px;
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 34%),
                linear-gradient(135deg, #048696 0%, #079aa9 100%);
            color: #fff;
            box-shadow: 0 20px 45px rgba(4, 134, 150, 0.20);
        }

        .reception-hero-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .reception-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .reception-hero h3 {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
            line-height: 1.15;
        }

        .reception-hero-subtitle {
            margin-top: 4px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 14px;
            font-weight: 500;
        }

        .reception-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .reception-form-card {
            border: 0;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .reception-form-body {
            padding: 26px;
        }

        .reception-section {
            padding: 22px;
            border: 1px solid #e8f1f3;
            border-radius: 20px;
            background: #fbfeff;
            margin-bottom: 18px;
        }

        .reception-section:last-child {
            margin-bottom: 0;
        }

        .reception-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .reception-section-icon {
            width: 38px;
            height: 38px;
            border-radius: 13px;
            background: #e8f7f9;
            color: #048696;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .reception-section-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #102a43;
        }

        .reception-section-subtitle {
            margin-top: 2px;
            font-size: 13px;
            color: #7b8794;
        }

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #334e68;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            min-height: 42px;
            border-radius: 12px;
            border: 1px solid #d9e5e8;
            color: #102a43;
            font-size: 14px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #048696;
            box-shadow: 0 0 0 4px rgba(4, 134, 150, 0.12) !important;
        }

        .quick-patient-box {
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f0fbfd 0%, #ffffff 100%);
            border: 1px solid #d9f0f4;
            margin-bottom: 18px;
        }

        .quick-patient-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 12px;
            color: #075e69;
            font-weight: 800;
            font-size: 15px;
        }


        .patient-age-mode {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #d9e5e8;
            background: #f8fbfc;
        }

        .patient-age-mode__icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #e8f7f9;
            color: #048696;
            font-size: 17px;
        }

        .patient-age-mode__title {
            color: #102a43;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .patient-age-mode__text {
            color: #6b7c8f;
            font-size: 13px;
            line-height: 1.45;
        }

        .minor-placeholder {
            border-style: dashed;
            background: #fcfeff;
        }

        .address-field-wrap {
            position: relative;
        }

        .address-suggestions {
            position: absolute;
            z-index: 30;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            max-height: 240px;
            overflow-y: auto;
            border-radius: 14px;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
        }

        .address-suggestions .list-group-item {
            border-color: #edf2f7;
            font-size: 14px;
            cursor: pointer;
        }

        .address-suggestions .list-group-item:hover {
            background: #eef8fa;
            color: #047887;
        }

        .reception-footer-panel {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 20px 26px;
            background: #f8fbfc;
            border-top: 1px solid #e8f1f3;
        }

        .reception-required-note {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #b42318;
            font-size: 14px;
            font-weight: 700;
        }

        .reception-submit-btn {
            min-height: 44px;
            padding: 10px 22px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #048696 0%, #079aa9 100%);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(4, 134, 150, 0.22);
        }

        .reception-submit-btn:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(4, 134, 150, 0.27);
        }

        .reception-alert {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(185, 28, 28, 0.08);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: 42px !important;
            padding: 6px 10px !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            border: 1px solid #d9e5e8 !important;
            border-radius: 12px !important;
            box-shadow: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #102a43 !important;
            line-height: 28px !important;
            padding-left: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #8a9aa8 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border: 1px solid #d9e5e8 !important;
            border-radius: 14px !important;
            overflow: hidden;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
        }

        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background: #048696 !important;
        }

        @media (max-width: 768px) {
            .reception-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .reception-hero-badge {
                white-space: normal;
            }

            .reception-form-body {
                padding: 18px;
            }

            .reception-section {
                padding: 18px;
            }

            .reception-footer-panel {
                flex-direction: column;
                align-items: stretch;
                padding: 18px;
            }

            .reception-submit-btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('sub-main')
    <div class="reception-create-page">
        <div class="reception-page-shell">

            <div class="reception-hero">
                <div class="reception-hero-title">
                    <div class="reception-hero-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <div>
                        <h3>Запись пациента</h3>
                        <div class="reception-hero-subtitle">
                            Создание записи в регистратуру с заполнением данных пациента
                        </div>
                    </div>
                </div>

                <div class="reception-hero-badge">
                    <i class="bi bi-shield-check"></i>
                    Все данные сохраняются в карточку пациента
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger reception-alert mb-4">
                    <div class="fw-bold mb-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        При создании записи появились ошибки:
                    </div>

                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('doctors.reception.store') }}">
                @csrf

                <div class="card reception-form-card">

                    <div class="reception-form-body">

                        <div class="quick-patient-box">
                            <div class="quick-patient-title">
                                <i class="bi bi-search"></i>
                                Быстрое заполнение по существующему пациенту
                            </div>

                            <label class="form-label">Пациент</label>
                            <input type="hidden"
                                   id="patient_id"
                                   name="patient_id"
                                   value="{{ old('patient_id') }}">
                            <select class="form-select" id="existing_patient"></select>

                            <div id="existing_patient_notice"
                                 class="alert alert-info border-0 mt-3 mb-0 d-none">
                                <i class="bi bi-info-circle me-1"></i>
                                Для существующего пациента используются уже сохранённые документы и представители.
                            </div>
                        </div>

                        <div class="reception-section">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-calendar2-check"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Приём</h4>
                                    <div class="reception-section-subtitle">
                                        Выберите врача и дату записи пациента
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Врач</label>
                                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->surname }} {{ $doctor->name }} {{ $doctor->patronym }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Дата и время приёма</label>
                                    <input class="form-control"
                                           type="datetime-local"
                                           id="appointment_datetime"
                                           name="appointment_datetime"
                                           value="{{ old('appointment_datetime') }}"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="reception-section">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-person-vcard"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Основные данные пациента</h4>
                                    <div class="reception-section-subtitle">
                                        ФИО, дата рождения и адреса пациента
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Фамилия</label>
                                    <input class="form-control"
                                           id="surname"
                                           name="surname"
                                           value="{{ old('surname') }}"
                                           autocomplete="family-name"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Имя</label>
                                    <input class="form-control"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           autocomplete="given-name"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Отчество</label>
                                    <input class="form-control"
                                           id="patronym"
                                           name="patronym"
                                           value="{{ old('patronym') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Дата рождения</label>
                                    <input class="form-control"
                                           type="date"
                                           id="birth_at"
                                           name="birth_at"
                                           value="{{ old('birth_at') }}"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Пол</label>
                                    <select class="form-select"
                                            id="gender"
                                            name="gender"
                                            required>
                                        <option value="" {{ old('gender') === null || old('gender') === '' ? 'selected' : '' }}>
                                            Выберите пол
                                        </option>
                                        <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>
                                            Мужской
                                        </option>
                                        <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>
                                            Женский
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Место рождения</label>
                                    <input class="form-control"
                                           id="birth_place"
                                           name="birth_place"
                                           value="{{ old('birth_place') }}"
                                           required>
                                </div>


                                <div class="col-12">
                                    <div class="patient-age-mode" id="patient_age_mode">
                                        <div class="patient-age-mode__icon" id="patient_age_mode_icon">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                        <div>
                                            <div class="patient-age-mode__title" id="patient_age_mode_title">
                                                Укажите дату рождения пациента
                                            </div>
                                            <div class="patient-age-mode__text" id="patient_age_mode_text">
                                                До 14 лет основным документом будет свидетельство о рождении. Законный представитель обязателен до 15 лет; в 15–17 лет его можно указать при необходимости.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Адрес регистрации</label>
                                    <div class="address-field-wrap">
                                        <input class="form-control"
                                               id="address_registration"
                                               name="address_registration"
                                               value="{{ old('address_registration') }}"
                                               required>
                                        <ul id="address_registration_suggestions"
                                            class="list-group address-suggestions"></ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Адрес проживания</label>
                                    <div class="address-field-wrap">
                                        <input class="form-control"
                                               id="address_residence"
                                               name="address_residence"
                                               value="{{ old('address_residence') }}"
                                               required>
                                        <ul id="address_residence_suggestions"
                                            class="list-group address-suggestions"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="reception-section" id="patient_identity_section">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Документ пациента</h4>
                                    <div class="reception-section-subtitle" id="patient_identity_subtitle">
                                        Паспорт, свидетельство о рождении или другой документ, удостоверяющий личность
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Тип документа</label>
                                    <select class="form-select"
                                            id="patient_document_type"
                                            name="patient_document_type">
                                        <option value="passport_rf" {{ old('patient_document_type', 'passport_rf') === 'passport_rf' ? 'selected' : '' }}>
                                            Паспорт гражданина РФ
                                        </option>
                                        <option value="birth_certificate" {{ old('patient_document_type') === 'birth_certificate' ? 'selected' : '' }}>
                                            Свидетельство о рождении
                                        </option>
                                        <option value="foreign_passport" {{ old('patient_document_type') === 'foreign_passport' ? 'selected' : '' }}>
                                            Иностранный паспорт
                                        </option>
                                        <option value="residence_permit" {{ old('patient_document_type') === 'residence_permit' ? 'selected' : '' }}>
                                            Вид на жительство
                                        </option>
                                        <option value="temporary_identity" {{ old('patient_document_type') === 'temporary_identity' ? 'selected' : '' }}>
                                            Временное удостоверение личности
                                        </option>
                                        <option value="foreign_identity" {{ old('patient_document_type') === 'foreign_identity' ? 'selected' : '' }}>
                                            Иностранный документ, удостоверяющий личность
                                        </option>
                                        <option value="other" {{ old('patient_document_type') === 'other' ? 'selected' : '' }}>
                                            Другой документ
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 d-none" id="patient_document_name_wrap">
                                    <label class="form-label">Наименование документа</label>
                                    <input class="form-control"
                                           id="patient_document_name"
                                           name="patient_document_name"
                                           value="{{ old('patient_document_name') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" id="patient_document_series_label">Серия</label>
                                    <input class="form-control"
                                           id="patient_document_series"
                                           name="patient_document_series"
                                           value="{{ old('patient_document_series') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Номер</label>
                                    <input class="form-control"
                                           id="patient_document_number"
                                           name="patient_document_number"
                                           value="{{ old('patient_document_number') }}">
                                </div>

                                <div class="col-md-4" id="patient_department_code_wrap">
                                    <label class="form-label">Код подразделения</label>
                                    <input class="form-control"
                                           id="patient_document_department_code"
                                           name="patient_document_department_code"
                                           value="{{ old('patient_document_department_code') }}">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Кем выдан</label>
                                    <input class="form-control"
                                           id="patient_document_issued_by"
                                           name="patient_document_issued_by"
                                           value="{{ old('patient_document_issued_by') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Дата выдачи</label>
                                    <input class="form-control"
                                           type="date"
                                           id="patient_document_issued_at"
                                           name="patient_document_issued_at"
                                           value="{{ old('patient_document_issued_at') }}">
                                </div>
                            </div>
                        </div>

                        <div class="reception-section minor-placeholder" id="representative_placeholder">
                            <div class="reception-section-header mb-0">
                                <div class="reception-section-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div>
                                    <h4 class="reception-section-title">Представитель несовершеннолетнего</h4>
                                    <div class="reception-section-subtitle">
                                        До 15 лет необходимо указать законного представителя. Для пациента 15–17 лет представитель указывается при его участии.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="reception-section d-none" id="representative_section">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-people"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Представитель несовершеннолетнего</h4>
                                    <div class="reception-section-subtitle">
                                        Родитель, опекун, попечитель или другой законный/уполномоченный представитель. До 15 лет блок обязателен.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Кем приходится</label>
                                    <select class="form-select"
                                            id="representative_type"
                                            name="representative_type">
                                        <option value="mother" {{ old('representative_type', 'mother') === 'mother' ? 'selected' : '' }}>Мать</option>
                                        <option value="father" {{ old('representative_type') === 'father' ? 'selected' : '' }}>Отец</option>
                                        <option value="adoptive_parent" {{ old('representative_type') === 'adoptive_parent' ? 'selected' : '' }}>Усыновитель</option>
                                        <option value="guardian" {{ old('representative_type') === 'guardian' ? 'selected' : '' }}>Опекун</option>
                                        <option value="trustee" {{ old('representative_type') === 'trustee' ? 'selected' : '' }}>Попечитель</option>
                                        <option value="authorized" {{ old('representative_type') === 'authorized' ? 'selected' : '' }}>Уполномоченный представитель</option>
                                        <option value="other" {{ old('representative_type') === 'other' ? 'selected' : '' }}>Другой представитель</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Фамилия</label>
                                    <input class="form-control"
                                           id="representative_surname"
                                           name="representative_surname"
                                           value="{{ old('representative_surname') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Имя</label>
                                    <input class="form-control"
                                           id="representative_name"
                                           name="representative_name"
                                           value="{{ old('representative_name') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Отчество</label>
                                    <input class="form-control"
                                           id="representative_patronym"
                                           name="representative_patronym"
                                           value="{{ old('representative_patronym') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Дата рождения</label>
                                    <input class="form-control"
                                           type="date"
                                           id="representative_birth_at"
                                           name="representative_birth_at"
                                           value="{{ old('representative_birth_at') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">СНИЛС</label>
                                    <input class="form-control"
                                           id="representative_snils"
                                           name="representative_snils"
                                           maxlength="14"
                                           value="{{ old('representative_snils') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Телефон</label>
                                    <input class="form-control"
                                           id="representative_phone"
                                           name="representative_phone"
                                           value="{{ old('representative_phone') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">E-mail</label>
                                    <input class="form-control"
                                           type="email"
                                           id="representative_email"
                                           name="representative_email"
                                           value="{{ old('representative_email') }}">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="fw-bold mb-3">
                                <i class="bi bi-passport me-1"></i>
                                Документ, удостоверяющий личность представителя
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Тип документа</label>
                                    <select class="form-select"
                                            id="representative_identity_document_type"
                                            name="representative_identity_document_type">
                                        <option value="passport_rf" {{ old('representative_identity_document_type', 'passport_rf') === 'passport_rf' ? 'selected' : '' }}>
                                            Паспорт гражданина РФ
                                        </option>
                                        <option value="foreign_passport" {{ old('representative_identity_document_type') === 'foreign_passport' ? 'selected' : '' }}>
                                            Иностранный паспорт
                                        </option>
                                        <option value="residence_permit" {{ old('representative_identity_document_type') === 'residence_permit' ? 'selected' : '' }}>
                                            Вид на жительство
                                        </option>
                                        <option value="temporary_identity" {{ old('representative_identity_document_type') === 'temporary_identity' ? 'selected' : '' }}>
                                            Временное удостоверение личности
                                        </option>
                                        <option value="foreign_identity" {{ old('representative_identity_document_type') === 'foreign_identity' ? 'selected' : '' }}>
                                            Иностранный документ, удостоверяющий личность
                                        </option>
                                        <option value="other" {{ old('representative_identity_document_type') === 'other' ? 'selected' : '' }}>
                                            Другой документ
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 d-none" id="representative_identity_document_name_wrap">
                                    <label class="form-label">Наименование документа</label>
                                    <input class="form-control"
                                           id="representative_identity_document_name"
                                           name="representative_identity_document_name"
                                           value="{{ old('representative_identity_document_name') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Серия</label>
                                    <input class="form-control"
                                           id="representative_identity_series"
                                           name="representative_identity_series"
                                           value="{{ old('representative_identity_series') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Номер</label>
                                    <input class="form-control"
                                           id="representative_identity_number"
                                           name="representative_identity_number"
                                           value="{{ old('representative_identity_number') }}">
                                </div>

                                <div class="col-md-4" id="representative_identity_department_code_wrap">
                                    <label class="form-label">Код подразделения</label>
                                    <input class="form-control"
                                           id="representative_identity_department_code"
                                           name="representative_identity_department_code"
                                           value="{{ old('representative_identity_department_code') }}">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Кем выдан</label>
                                    <input class="form-control"
                                           id="representative_identity_issued_by"
                                           name="representative_identity_issued_by"
                                           value="{{ old('representative_identity_issued_by') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Дата выдачи</label>
                                    <input class="form-control"
                                           type="date"
                                           id="representative_identity_issued_at"
                                           name="representative_identity_issued_at"
                                           value="{{ old('representative_identity_issued_at') }}">
                                </div>
                            </div>

                            <div id="representative_authority_section" class="d-none">
                                <hr class="my-4">

                                <div class="fw-bold mb-3">
                                    <i class="bi bi-file-earmark-check me-1"></i>
                                    Документ, подтверждающий полномочия
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Тип документа</label>
                                        <select class="form-select"
                                                id="representative_authority_document_type"
                                                name="representative_authority_document_type">
                                            <option value="guardianship_order" {{ old('representative_authority_document_type') === 'guardianship_order' ? 'selected' : '' }}>
                                                Документ об установлении опеки/попечительства
                                            </option>
                                            <option value="power_of_attorney" {{ old('representative_authority_document_type') === 'power_of_attorney' ? 'selected' : '' }}>
                                                Доверенность
                                            </option>
                                            <option value="other" {{ old('representative_authority_document_type') === 'other' ? 'selected' : '' }}>
                                                Другой документ
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 d-none" id="representative_authority_document_name_wrap">
                                        <label class="form-label">Наименование документа</label>
                                        <input class="form-control"
                                               id="representative_authority_document_name"
                                               name="representative_authority_document_name"
                                               value="{{ old('representative_authority_document_name') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Серия</label>
                                        <input class="form-control"
                                               id="representative_authority_series"
                                               name="representative_authority_series"
                                               value="{{ old('representative_authority_series') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Номер</label>
                                        <input class="form-control"
                                               id="representative_authority_number"
                                               name="representative_authority_number"
                                               value="{{ old('representative_authority_number') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Дата выдачи</label>
                                        <input class="form-control"
                                               type="date"
                                               id="representative_authority_issued_at"
                                               name="representative_authority_issued_at"
                                               value="{{ old('representative_authority_issued_at') }}">
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">Кем выдан</label>
                                        <input class="form-control"
                                               id="representative_authority_issued_by"
                                               name="representative_authority_issued_by"
                                               value="{{ old('representative_authority_issued_by') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="reception-section">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-file-medical"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Медицинские документы</h4>
                                    <div class="reception-section-subtitle">
                                        СНИЛС и полис обязательного медицинского страхования
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">СНИЛС</label>
                                    <input class="form-control"
                                           id="snils"
                                           name="snils"
                                           value="{{ old('snils') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">ОМС</label>
                                    <input class="form-control"
                                           id="oms"
                                           name="oms"
                                           maxlength="16"
                                           value="{{ old('oms') }}"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="reception-footer-panel">
                        <div class="reception-required-note">
                            <i class="bi bi-info-circle"></i>
                            Поля документов и представителя зависят от возраста пациента и типа документа.
                        </div>

                        <button type="submit" class="btn reception-submit-btn">
                            <i class="bi bi-check2-circle"></i>
                            Записать пациента
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = @json(config('dadata.dadata_api'));

            function fetchSuggestions(input, suggestionsContainer) {
                const query = input.value.trim();

                if (query.length < 3) {
                    suggestionsContainer.innerHTML = '';
                    return;
                }

                fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Token ' + token
                    },
                    body: JSON.stringify({query: query})
                })
                    .then(response => response.json())
                    .then(data => {
                        suggestionsContainer.innerHTML = '';

                        data.suggestions.forEach(suggestion => {
                            const li = document.createElement('li');

                            li.classList.add('list-group-item');
                            li.textContent = suggestion.value;

                            li.addEventListener('click', () => {
                                input.value = suggestion.value;
                                suggestionsContainer.innerHTML = '';
                            });

                            suggestionsContainer.appendChild(li);
                        });
                    })
                    .catch(error => console.error('Error fetching suggestions:', error));
            }

            const addressRegistrationInput = document.getElementById('address_registration');
            const addressResidenceInput = document.getElementById('address_residence');
            const addressRegistrationSuggestions = document.getElementById('address_registration_suggestions');
            const addressResidenceSuggestions = document.getElementById('address_residence_suggestions');

            addressRegistrationInput.addEventListener('input', () => {
                fetchSuggestions(addressRegistrationInput, addressRegistrationSuggestions);
            });

            addressResidenceInput.addEventListener('input', () => {
                fetchSuggestions(addressResidenceInput, addressResidenceSuggestions);
            });

            addressRegistrationInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    addressRegistrationSuggestions.innerHTML = '';
                }
            });

            addressResidenceInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    addressResidenceSuggestions.innerHTML = '';
                }
            });

            document.addEventListener('click', function (event) {
                if (!addressRegistrationInput.contains(event.target)) {
                    addressRegistrationSuggestions.innerHTML = '';
                }

                if (!addressResidenceInput.contains(event.target)) {
                    addressResidenceSuggestions.innerHTML = '';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const patientIdInput = document.getElementById('patient_id');
            const birthAtInput = document.getElementById('birth_at');
            const patientIdentitySection = document.getElementById('patient_identity_section');
            const representativeSection = document.getElementById('representative_section');
            const representativePlaceholder = document.getElementById('representative_placeholder');
            const existingPatientNotice = document.getElementById('existing_patient_notice');
            const patientAgeModeTitle = document.getElementById('patient_age_mode_title');
            const patientAgeModeText = document.getElementById('patient_age_mode_text');
            const patientAgeModeIcon = document.getElementById('patient_age_mode_icon');

            const patientDocumentType = document.getElementById('patient_document_type');
            const patientDocumentNameWrap = document.getElementById('patient_document_name_wrap');
            const patientDepartmentCodeWrap = document.getElementById('patient_department_code_wrap');

            const representativeType = document.getElementById('representative_type');
            const representativeIdentityDocumentType = document.getElementById('representative_identity_document_type');
            const representativeIdentityDocumentNameWrap = document.getElementById('representative_identity_document_name_wrap');
            const representativeIdentityDepartmentCodeWrap = document.getElementById('representative_identity_department_code_wrap');
            const representativeAuthoritySection = document.getElementById('representative_authority_section');
            const representativeAuthorityDocumentType = document.getElementById('representative_authority_document_type');
            const representativeAuthorityDocumentNameWrap = document.getElementById('representative_authority_document_name_wrap');

            let patientDocumentTypeChangedManually = @json(old('patient_document_type') !== null);

            function calculateAge(dateString) {
                if (!dateString) {
                    return null;
                }

                const birthDate = new Date(dateString + 'T00:00:00');

                if (Number.isNaN(birthDate.getTime())) {
                    return null;
                }

                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDifference = today.getMonth() - birthDate.getMonth();

                if (
                    monthDifference < 0
                    || (
                        monthDifference === 0
                        && today.getDate() < birthDate.getDate()
                    )
                ) {
                    age--;
                }

                return age;
            }

            function setRequired(ids, required) {
                ids.forEach(id => {
                    const element = document.getElementById(id);

                    if (element) {
                        element.required = required;
                    }
                });
            }

            function syncPatientDocumentType() {
                const type = patientDocumentType.value;
                const isPassport = type === 'passport_rf';
                const isOther = type === 'other';

                patientDepartmentCodeWrap.classList.toggle('d-none', !isPassport);
                patientDocumentNameWrap.classList.toggle('d-none', !isOther);

                document.getElementById('patient_document_name').required = isOther && !patientIdInput.value;
                document.getElementById('patient_document_department_code').required = isPassport && !patientIdInput.value;
            }

            function representativeHasData() {
                return [
                    'representative_surname',
                    'representative_name',
                    'representative_patronym',
                    'representative_birth_at',
                    'representative_snils',
                    'representative_phone',
                    'representative_email',
                    'representative_identity_series',
                    'representative_identity_number',
                    'representative_identity_issued_by',
                    'representative_identity_issued_at'
                ].some(id => {
                    const element = document.getElementById(id);
                    return element && String(element.value || '').trim() !== '';
                });
            }

            function syncRepresentativeIdentityType() {
                const type = representativeIdentityDocumentType.value;
                const isPassport = type === 'passport_rf';
                const isOther = type === 'other';
                const age = calculateAge(birthAtInput.value);
                const representativeRequired = !patientIdInput.value && age !== null && age < 15;
                const representativeActive = representativeRequired || representativeHasData();

                representativeIdentityDepartmentCodeWrap.classList.toggle('d-none', !isPassport);
                representativeIdentityDocumentNameWrap.classList.toggle('d-none', !isOther);

                document.getElementById('representative_identity_document_name').required = representativeActive && isOther;
                document.getElementById('representative_identity_department_code').required = representativeActive && isPassport;
            }

            function representativeNeedsAuthorityDocument() {
                return [
                    'guardian',
                    'trustee',
                    'authorized',
                    'other'
                ].includes(representativeType.value);
            }

            function syncRepresentativeAuthority() {
                const age = calculateAge(birthAtInput.value);
                const isMinor = age !== null && age < 18;
                const representativeRequired = !patientIdInput.value && age !== null && age < 15;
                const representativeActive = !patientIdInput.value
                    && isMinor
                    && (representativeRequired || representativeHasData());
                const needsAuthority = representativeActive && representativeNeedsAuthorityDocument();
                const isOther = representativeAuthorityDocumentType.value === 'other';

                representativeAuthoritySection.classList.toggle('d-none', !needsAuthority);
                representativeAuthorityDocumentNameWrap.classList.toggle('d-none', !needsAuthority || !isOther);

                setRequired([
                    'representative_authority_document_type',
                    'representative_authority_number',
                    'representative_authority_issued_by',
                    'representative_authority_issued_at'
                ], needsAuthority);

                document.getElementById('representative_authority_document_name').required = needsAuthority && isOther;
            }

            function syncPatientAgeMode(forceDocumentType = false) {
                const existingPatient = Boolean(patientIdInput.value);
                const age = calculateAge(birthAtInput.value);
                const isMinor = age !== null && age < 18;
                const isUnder14 = age !== null && age < 14;
                const representativeRequiredByAge = age !== null && age < 15;

                patientIdentitySection.classList.toggle('d-none', existingPatient);
                representativeSection.classList.toggle('d-none', existingPatient || !isMinor);
                representativePlaceholder.classList.toggle('d-none', existingPatient || isMinor);
                existingPatientNotice.classList.toggle('d-none', !existingPatient);

                if (existingPatient) {
                    patientAgeModeTitle.textContent = 'Существующий пациент выбран';
                    patientAgeModeText.textContent = 'Для повторной записи используются документы и представители, уже сохранённые в карточке пациента.';
                    patientAgeModeIcon.innerHTML = '<i class="bi bi-check2-circle"></i>';
                } else if (age === null) {
                    patientAgeModeTitle.textContent = 'Укажите дату рождения пациента';
                    patientAgeModeText.textContent = 'До 14 лет основным документом будет свидетельство о рождении. Законный представитель обязателен до 15 лет; в 15–17 лет его можно указать при необходимости.';
                    patientAgeModeIcon.innerHTML = '<i class="bi bi-person-check"></i>';
                } else if (isMinor) {
                    patientAgeModeTitle.textContent = `Несовершеннолетний пациент — ${age} ${age === 1 ? 'год' : (age >= 2 && age <= 4 ? 'года' : 'лет')}`;

                    if (isUnder14) {
                        patientAgeModeText.textContent = 'Режим ребёнка: основным документом выбрано свидетельство о рождении, законный представитель обязателен.';
                    } else if (representativeRequiredByAge) {
                        patientAgeModeText.textContent = 'Пациенту 14 лет: основным документом выбран паспорт, законный представитель ещё обязателен.';
                    } else {
                        patientAgeModeText.textContent = 'Пациент 15–17 лет: основным документом выбран паспорт. Представитель указывается, если он участвует в оформлении или оказании помощи.';
                    }

                    patientAgeModeIcon.innerHTML = '<i class="bi bi-balloon-heart"></i>';
                } else {
                    patientAgeModeTitle.textContent = `Совершеннолетний пациент — ${age} лет`;
                    patientAgeModeText.textContent = 'Данные законного представителя для этой записи не требуются.';
                    patientAgeModeIcon.innerHTML = '<i class="bi bi-person-check"></i>';
                }

                if (
                    !existingPatient
                    && age !== null
                    && (forceDocumentType || !patientDocumentTypeChangedManually)
                ) {
                    patientDocumentType.value = isUnder14
                        ? 'birth_certificate'
                        : 'passport_rf';
                }

                const documentRequired = !existingPatient;

                setRequired([
                    'patient_document_type',
                    'patient_document_number',
                    'patient_document_issued_by',
                    'patient_document_issued_at'
                ], documentRequired);

                const representativeRequired = !existingPatient && representativeRequiredByAge;

                setRequired([
                    'representative_type',
                    'representative_surname',
                    'representative_name',
                    'representative_birth_at',
                    'representative_phone',
                    'representative_identity_document_type',
                    'representative_identity_number',
                    'representative_identity_issued_by',
                    'representative_identity_issued_at'
                ], representativeRequired);

                syncPatientDocumentType();
                syncRepresentativeIdentityType();
                syncRepresentativeAuthority();
            }

            $('#existing_patient').select2({
                placeholder: 'Выбрать существующего пациента',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '/api/doctors/search-patients',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results.map(patient => ({
                                id: patient.id,
                                text: patient.text + ' (' + patient.birth_at + ')'
                            }))
                        };
                    },
                    cache: true
                }
            });

            $('#existing_patient').on('select2:select', function (e) {
                const patientId = e.params.data.id;

                patientIdInput.value = patientId;

                fetch(`/api/doctors/search-patients/for/${patientId}`)
                    .then(response => response.json())
                    .then(patient => {
                        $('#surname').val(patient.surname || '');
                        $('#name').val(patient.name || '');
                        $('#patronym').val(patient.patronym || '');
                        $('#birth_at').val(patient.birth_at || '');
                        $('#gender').val(patient.gender || '');
                        $('#address_registration').val(patient.address_registration || '');
                        $('#address_residence').val(patient.address_residence || '');
                        $('#birth_place').val(patient.birth_place || '');
                        $('#snils').val(patient.snils || '');
                        $('#oms').val(patient.oms || '');

                        syncPatientAgeMode(true);
                    });
            });

            $('#existing_patient').on('select2:clear', function () {
                patientIdInput.value = '';
                clearPatientFields();
                patientDocumentTypeChangedManually = false;
                syncPatientAgeMode(true);
            });

            function clearPatientFields() {
                [
                    'surname',
                    'name',
                    'patronym',
                    'birth_at',
                    'gender',
                    'address_registration',
                    'address_residence',
                    'birth_place',
                    'snils',
                    'oms',
                    'patient_document_series',
                    'patient_document_number',
                    'patient_document_department_code',
                    'patient_document_issued_by',
                    'patient_document_issued_at',
                    'patient_document_name',
                    'representative_surname',
                    'representative_name',
                    'representative_patronym',
                    'representative_birth_at',
                    'representative_snils',
                    'representative_phone',
                    'representative_email',
                    'representative_identity_series',
                    'representative_identity_number',
                    'representative_identity_department_code',
                    'representative_identity_issued_by',
                    'representative_identity_issued_at',
                    'representative_identity_document_name',
                    'representative_authority_series',
                    'representative_authority_number',
                    'representative_authority_issued_by',
                    'representative_authority_issued_at',
                    'representative_authority_document_name'
                ].forEach(id => {
                    const element = document.getElementById(id);

                    if (element) {
                        element.value = '';
                    }
                });
            }

            birthAtInput.addEventListener('change', function () {
                patientDocumentTypeChangedManually = false;
                syncPatientAgeMode(true);
            });

            patientDocumentType.addEventListener('change', function () {
                patientDocumentTypeChangedManually = true;
                syncPatientDocumentType();
            });

            representativeType.addEventListener('change', function () {
                if (representativeType.value === 'guardian' || representativeType.value === 'trustee') {
                    representativeAuthorityDocumentType.value = 'guardianship_order';
                } else if (representativeType.value === 'authorized') {
                    representativeAuthorityDocumentType.value = 'power_of_attorney';
                }

                syncRepresentativeAuthority();
            });

            representativeIdentityDocumentType.addEventListener('change', syncRepresentativeIdentityType);
            representativeAuthorityDocumentType.addEventListener('change', syncRepresentativeAuthority);

            document.querySelectorAll('#representative_section input, #representative_section select').forEach(element => {
                element.addEventListener('change', function () {
                    syncRepresentativeIdentityType();
                    syncRepresentativeAuthority();
                });
            });

            const appointmentInput = document.getElementById('appointment_datetime');

            function setMinAppointmentDateTime() {
                const now = new Date();

                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');

                appointmentInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
            }

            syncPatientAgeMode(false);
            setMinAppointmentDateTime();
            setInterval(setMinAppointmentDateTime, 30000);
        });
    </script>
@endsection
