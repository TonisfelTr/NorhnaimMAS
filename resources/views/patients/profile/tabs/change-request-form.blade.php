@php
    $documentType = $primaryDocument?->document_type
        ?: (($patient->getRawOriginal('number') || $patient->getRawOriginal('serial')) ? 'passport_rf' : '');
    $documentSeries = $primaryDocument?->series ?? $patient->getRawOriginal('serial');
    $documentNumber = $primaryDocument?->number ?? $patient->getRawOriginal('number');
    $documentIssuedBy = $primaryDocument?->issued_by ?? $patient->getRawOriginal('issued_by');
    $documentIssuedAt = $primaryDocument?->issued_at?->format('Y-m-d') ?? $patient->getRawOriginal('issued_at');
    $documentDepartmentCode = $primaryDocument?->department_code ?? $patient->getRawOriginal('department_code');
    $fieldLabels = [
        'surname' => 'Фамилия', 'name' => 'Имя', 'patronym' => 'Отчество',
        'birth_at' => 'Дата рождения', 'gender' => 'Пол', 'phone' => 'Телефон',
        'email' => 'Email', 'birth_place' => 'Место рождения',
        'address_registration' => 'Адрес регистрации', 'address_residence' => 'Адрес проживания',
        'snils' => 'СНИЛС', 'oms' => 'Полис ОМС', 'insurance_company' => 'Страховая компания',
        'document_type' => 'Тип документа', 'document_series' => 'Серия документа',
        'document_number' => 'Номер документа', 'document_issued_by' => 'Кем выдан документ',
        'document_issued_at' => 'Дата выдачи документа', 'document_department_code' => 'Код подразделения',
    ];
@endphp

<details class="patient-identity-change-details">
    <summary>
        <span><i class="bi bi-file-earmark-text"></i> Запросить изменение данных</span>
        <i class="bi bi-chevron-down"></i>
    </summary>

    <form method="POST" action="{{ route('patients.profile.change-requests.store') }}" class="patient-identity-form">
        @csrf

        <section class="patient-identity-card">
            <div class="patient-identity-card__header">
                <div><h3>Персональные данные</h3><p>Заполните форму полностью. Изменятся только отличающиеся сведения после одобрения.</p></div>
                <i class="bi bi-person"></i>
            </div>
            <div class="patient-identity-card__body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Фамилия</label><input name="surname" class="form-control" value="{{ old('surname', $patient->surname) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Имя</label><input name="name" class="form-control" value="{{ old('name', $patient->name) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Отчество</label><input name="patronym" class="form-control" value="{{ old('patronym', $patient->patronym) }}"></div>
                    <div class="col-md-6"><label class="form-label">Дата рождения</label><input type="date" name="birth_at" class="form-control" value="{{ old('birth_at', $birthAt) }}" required></div>
                    <div class="col-md-6">
                        <label class="form-label">Пол</label>
                        <select name="gender" class="form-select" required>
                            <option value="M" @selected(old('gender', $patient->gender) === 'M')>Мужской</option>
                            <option value="F" @selected(old('gender', $patient->gender) === 'F')>Женский</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Телефон</label><input name="phone" type="tel" class="form-control js-mask-phone" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="+7 (___) ___-__-__" value="{{ old('phone', $patient->phone) }}"></div>
                    <div class="col-md-6"><label class="form-label">Контактный email</label><input type="email" name="email" class="form-control js-mask-email" autocomplete="email" value="{{ old('email', $patient->email) }}"></div>
                    <div class="col-12"><label class="form-label">Место рождения</label><input name="birth_place" class="form-control" value="{{ old('birth_place', $patient->birth_place) }}"></div>
                    <div class="col-12">
                        <label class="form-label">Адрес регистрации</label>
                        <div class="patient-profile-autocomplete-wrap">
                            <input
                                name="address_registration"
                                class="form-control js-patient-address-autocomplete"
                                value="{{ old('address_registration', $patient->address_registration) }}"
                                maxlength="500"
                                autocomplete="off"
                                data-suggest-box="#changeRegistrationAddressSuggest"
                                placeholder="Начните вводить регион, город, улицу и дом"
                            >
                            <div id="changeRegistrationAddressSuggest" class="patient-profile-autocomplete-suggest" role="listbox"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Адрес проживания</label>
                        <div class="patient-profile-autocomplete-wrap">
                            <input
                                name="address_residence"
                                class="form-control js-patient-address-autocomplete"
                                value="{{ old('address_residence', $patient->address_residence) }}"
                                maxlength="500"
                                autocomplete="off"
                                data-suggest-box="#changeResidenceAddressSuggest"
                                placeholder="Начните вводить регион, город, улицу и дом"
                            >
                            <div id="changeResidenceAddressSuggest" class="patient-profile-autocomplete-suggest" role="listbox"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="patient-identity-card">
            <div class="patient-identity-card__header">
                <div><h3>Документы и страховые сведения</h3><p>Паспорт, СНИЛС, полис ОМС и страховая компания.</p></div>
                <i class="bi bi-card-text"></i>
            </div>
            <div class="patient-identity-card__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Тип документа</label>
                        <select name="document_type" class="form-select">
                            <option value="">Не указан</option>
                            @foreach(\App\Models\PatientIdentityDocument::typeOptions() as $value => $label)
                                <option value="{{ $value }}" @selected(old('document_type', $documentType) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Серия</label><input name="document_series" class="form-control js-identity-document-series" value="{{ old('document_series', $documentSeries) }}" maxlength="50"></div>
                    <div class="col-md-3"><label class="form-label">Номер</label><input name="document_number" class="form-control js-identity-document-number" value="{{ old('document_number', $documentNumber) }}" maxlength="100"></div>
                    <div class="col-md-8"><label class="form-label">Кем выдан</label><input name="document_issued_by" class="form-control" value="{{ old('document_issued_by', $documentIssuedBy) }}"></div>
                    <div class="col-md-4"><label class="form-label">Дата выдачи</label><input type="date" name="document_issued_at" class="form-control" value="{{ old('document_issued_at', $documentIssuedAt) }}"></div>
                    <div class="col-md-4"><label class="form-label">Код подразделения</label><input name="document_department_code" class="form-control js-mask-department-code" value="{{ old('document_department_code', $documentDepartmentCode) }}" maxlength="7" inputmode="numeric" placeholder="000-000"></div>
                    <div class="col-md-4"><label class="form-label">СНИЛС</label><input name="snils" class="form-control js-mask-snils" value="{{ old('snils', $patient->getRawOriginal('snils')) }}" maxlength="14" inputmode="numeric" placeholder="000-000-000 00"></div>
                    <div class="col-md-4"><label class="form-label">Полис ОМС</label><input name="oms" class="form-control js-mask-policy" value="{{ old('oms', $patient->getRawOriginal('oms')) }}" maxlength="32" autocomplete="off"></div>
                    <div class="col-md-4">
                        <label class="form-label">Страховая компания</label>
                        <div class="patient-profile-autocomplete-wrap">
                            <input
                                name="insurance_company"
                                class="form-control js-patient-insurance-autocomplete"
                                value="{{ old('insurance_company', $patient->insurance_company) }}"
                                maxlength="255"
                                autocomplete="off"
                                data-suggest-box="#changeInsuranceCompanySuggest"
                                placeholder="Начните вводить название страховой"
                            >
                            <div id="changeInsuranceCompanySuggest" class="patient-profile-autocomplete-suggest" role="listbox"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="patient-identity-card">
            <div class="patient-identity-card__header">
                <div>
                    <h3>Кому направить запрос</h3>
                    <p>
                        Выберите клинику и конкретный маршрут согласования.
                        У каждой клиники может быть свой лечащий врач.
                    </p>
                </div>
                <i class="bi bi-send"></i>
            </div>
            <div class="patient-identity-card__body">
                @php
                    $firstDoctorClinic = $clinicCards->first(
                        static fn ($clinic): bool => !empty($clinic['doctor_id'])
                    );
                    $firstClinic = $clinicCards->first();
                    $defaultReviewRoute = $firstDoctorClinic
                        ? 'doctor:' . $firstDoctorClinic['id']
                        : ($firstClinic ? 'administration:' . $firstClinic['id'] : '');
                @endphp

                <label class="form-label" for="patientReviewRoute">
                    Клиника и получатель
                </label>

                <select
                    id="patientReviewRoute"
                    name="review_route"
                    class="form-select"
                    required
                >
                    <option value="">Выберите получателя</option>

                    @foreach($clinicCards as $clinicCard)
                        @if($clinicCard['doctor_id'])
                            <option
                                value="doctor:{{ $clinicCard['id'] }}"
                                @selected(old('review_route', $defaultReviewRoute) === 'doctor:' . $clinicCard['id'])
                            >
                                {{ $clinicCard['name'] }} — лечащий врач: {{ $clinicCard['doctor_name'] }}
                            </option>
                        @endif

                        <option
                            value="administration:{{ $clinicCard['id'] }}"
                            @selected(old('review_route', $defaultReviewRoute) === 'administration:' . $clinicCard['id'])
                        >
                            {{ $clinicCard['name'] }} — администрация клиники
                        </option>
                    @endforeach
                </select>

                <div class="patient-profile-hint mt-2">
                    Запрос врачу увидит только лечащий врач, назначенный именно в выбранной клинике.
                </div>

                <div class="mt-3">
                    <label class="form-label">Причина изменения</label>
                    <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
                </div>
            </div>
        </section>

        <div class="patient-profile-savebar">
            <span><i class="bi bi-lock"></i> Сведения не изменятся до одобрения запроса.</span>
            <button type="submit" class="patient-profile-primary"><i class="bi bi-send"></i> Отправить запрос</button>
        </div>
    </form>
</details>
