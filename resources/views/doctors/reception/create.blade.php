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
                            <select class="form-select" id="existing_patient"></select>
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
                                           value="{{ old('patronym') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Дата рождения</label>
                                    <input class="form-control"
                                           type="date"
                                           id="birth_at"
                                           name="birth_at"
                                           value="{{ old('birth_at') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Место рождения</label>
                                    <input class="form-control"
                                           id="birth_place"
                                           name="birth_place"
                                           value="{{ old('birth_place') }}"
                                           required>
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

                        <div class="reception-section">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-passport"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Паспортные данные</h4>
                                    <div class="reception-section-subtitle">
                                        Документ, удостоверяющий личность пациента
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Серия паспорта</label>
                                    <input class="form-control"
                                           id="serial"
                                           name="serial"
                                           value="{{ old('serial') }}"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Номер паспорта</label>
                                    <input class="form-control"
                                           id="number"
                                           name="number"
                                           value="{{ old('number') }}"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Код подразделения</label>
                                    <input class="form-control"
                                           id="department_code"
                                           name="department_code"
                                           value="{{ old('department_code') }}"
                                           required>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Кем выдан</label>
                                    <input class="form-control"
                                           id="issued_by"
                                           name="issued_by"
                                           value="{{ old('issued_by') }}"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Дата выдачи</label>
                                    <input class="form-control"
                                           type="date"
                                           id="issued_at"
                                           name="issued_at"
                                           value="{{ old('issued_at') }}"
                                           required>
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
                            Все поля обязательны к заполнению.
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
                let patientId = e.params.data.id;

                fetch(`/api/doctors/search-patients/for/${patientId}`)
                    .then(response => response.json())
                    .then(patient => {
                        $('#surname').val(patient.surname || '');
                        $('#name').val(patient.name || '');
                        $('#patronym').val(patient.patronym || '');
                        $('#birth_at').val(patient.birth_at || '');
                        $('#address_registration').val(patient.address_registration || '');
                        $('#address_residence').val(patient.address_residence || '');

                        $('#serial').val(patient.serial || '');
                        $('#number').val(patient.number || '');
                        $('#department_code').val(patient.department_code || '');
                        $('#issued_by').val(patient.issued_by || '');
                        $('#issued_at').val(patient.issued_at || '');
                        $('#birth_place').val(patient.birth_place || '');
                        $('#snils').val(patient.snils || '');
                        $('#oms').val(patient.oms || '');
                    });
            });

            $('#existing_patient').on('select2:clear', function () {
                clearPatientFields();
            });

            function clearPatientFields() {
                [
                    'surname',
                    'name',
                    'patronym',
                    'birth_at',
                    'address_registration',
                    'address_residence',
                    'serial',
                    'number',
                    'department_code',
                    'issued_by',
                    'issued_at',
                    'birth_place',
                    'snils',
                    'oms'
                ].forEach(id => {
                    const element = document.getElementById(id);

                    if (element) {
                        element.value = '';
                    }
                });
            }

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

            setMinAppointmentDateTime();
            setInterval(setMinAppointmentDateTime, 30000);
        });
    </script>
@endsection
