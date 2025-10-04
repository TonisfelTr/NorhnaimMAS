@extends('doctors.reception')
@section('title', 'Записать в регистратуру')
@section('assets')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        /* Исправляет конфликт Bootstrap и Select2 */
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 4px 6px !important;
            font-size: 1rem !important;
            line-height: 1.5 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 6px !important;
        }
    </style>
@endsection
@section('sub-main')
    <div class="container py-4">
        <h3 class="mb-4">Запись пациента</h3>
        @if($errors->any())
            <div class="alert alert-danger">
                При создании записи, появились следующие ошибки:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="post" action="{{ route('doctors.reception.store') }}">
            @csrf
            <div class="card shadow-sm p-4">
                <div class="mb-3">
                    <label class="form-label">Пациент</label>
                    <select class="form-select" id="existing_patient">
                        <!-- Если это поле тоже нужно с сохранением значения, добавь old() -->
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Врач</label>
                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->surname }} {{ $doctor->name }} {{ $doctor->patronym }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Фамилия</label>
                        <input class="form-control" id="surname" name="surname" value="{{ old('surname') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Имя</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Отчество</label>
                        <input class="form-control" id="patronym" name="patronym" value="{{ old('patronym') }}" required>
                    </div>
                </div>

                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Дата рождения</label>
                        <input class="form-control" type="date" id="birth_at" name="birth_at" value="{{ old('birth_at') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Дата и время приёма</label>
                        <input class="form-control" type="datetime-local" id="appointment_datetime" name="appointment_datetime" value="{{ old('appointment_datetime') }}" required>
                    </div>
                </div>

                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Адрес регистрации</label>
                        <input class="form-control" id="address_registration" name="address_registration" value="{{ old('address_registration') }}" required>
                        <ul id="address_registration_suggestions" class="list-group"></ul>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Адрес проживания</label>
                        <input class="form-control" id="address_residence" name="address_residence" value="{{ old('address_residence') }}" required>
                        <ul id="address_residence_suggestions" class="list-group"></ul>
                    </div>
                </div>

                <div class="row g-3 pt-3">
                    <div class="col-md-4">
                        <label class="form-label">Серия паспорта</label>
                        <input class="form-control" id="serial" name="serial" value="{{ old('serial') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Номер паспорта</label>
                        <input class="form-control" id="number" name="number" value="{{ old('number') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Код подразделения</label>
                        <input class="form-control" id="department_code" name="department_code" value="{{ old('department_code') }}" required>
                    </div>
                </div>

                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Кем выдан</label>
                        <input class="form-control" id="issued_by" name="issued_by" value="{{ old('issued_by') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Дата выдачи</label>
                        <input class="form-control" type="date" id="issued_at" name="issued_at" value="{{ old('issued_at') }}" required>
                    </div>
                </div>

                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Место рождения</label>
                        <input class="form-control" id="birth_place" name="birth_place" value="{{ old('birth_place') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">СНИЛС</label>
                        <input class="form-control" id="snils" name="snils" value="{{ old('snils') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ОМС</label>
                        <input class="form-control" id="oms" name="oms" maxlength="16" value="{{ old('oms') }}" required>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class="col-md-8">
                    <span class="text-danger fs-6 pb-1 d-inline-block border-bottom border-danger w-100">
                        <i class="bi bi-info-circle"></i> Все поля обязательны к заполнению.
                    </span>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="submit" class="btn btn-primary">Записать пациента</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // твой текущий JavaScript для DaData (оставить без изменений)
        document.addEventListener('DOMContentLoaded', function () {
            var token = '{{ config('dadata.dadata_api') }}';

            function fetchSuggestions(input, suggestionsContainer) {
                const query = input.value;
                if (query.length < 3) return;

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

            addressRegistrationInput.addEventListener('input', () => fetchSuggestions(addressRegistrationInput, addressRegistrationSuggestions));
            addressResidenceInput.addEventListener('input', () => fetchSuggestions(addressResidenceInput, addressResidenceSuggestions));

            addressRegistrationInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') addressRegistrationSuggestions.innerHTML = '';
            });
            addressResidenceInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') addressResidenceSuggestions.innerHTML = '';
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

                        // новые поля для заполнения
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
                    'surname', 'name', 'patronym', 'birth_at', 'address_registration', 'address_residence',
                    'serial', 'number', 'department_code', 'issued_by', 'issued_at', 'birth_place', 'snils', 'oms'
                ].forEach(id => {
                    document.getElementById(id).value = '';
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

                const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

                appointmentInput.min = minDateTime;
            }

            setMinAppointmentDateTime();
            setInterval(setMinAppointmentDateTime, 30000);
        });
    </script>
@endsection
