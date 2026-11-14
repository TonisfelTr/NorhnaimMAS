@php
    $fieldLabels = [
        'surname' => 'Фамилия', 'name' => 'Имя', 'patronym' => 'Отчество',
        'birth_at' => 'Дата рождения', 'gender' => 'Пол', 'phone' => 'Телефон',
        'email' => 'Email', 'birth_place' => 'Место рождения',
        'address_registration' => 'Адрес регистрации', 'address_residence' => 'Адрес проживания',
        'snils' => 'СНИЛС', 'oms' => 'ОМС', 'insurance_company' => 'Страховая компания',
        'document_type' => 'Тип документа', 'document_series' => 'Серия',
        'document_number' => 'Номер', 'document_issued_by' => 'Кем выдан',
        'document_issued_at' => 'Дата выдачи', 'document_department_code' => 'Код подразделения',
    ];
@endphp

<div
    class="patient-identity-page"
    data-address-suggest-url="{{ route('api-search-address') }}"
    data-insurance-suggest-url="{{ route('api-search-organizations') }}"
>

        @if($patient)
            <section class="patient-identity-status is-approved">
                <div class="patient-identity-status__icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <strong>Медицинская карта привязана</strong>
                    <p>
                        {{ trim(implode(' ', array_filter([
                            $patient->surname,
                            $patient->name,
                            $patient->patronym
                        ]))) }}
                        @if($patient->getRawOriginal('birth_at'))
                            · {{ \Carbon\Carbon::parse($patient->getRawOriginal('birth_at'))->format('d.m.Y') }}
                        @endif
                    </p>
                    <span>Идентификация завершена.</span>
                </div>
            </section>

            <section class="patient-identity-card">
                <div class="patient-identity-card__header">
                    <div>
                        <h2>Данные медицинской карты</h2>
                        <p>Проверенные сведения из медицинской информационной системы.</p>
                    </div>
                    <i class="bi bi-card-checklist"></i>
                </div>

                <div class="patient-identity-card__body">
                    <div class="patient-identity-summary">
                        <div><span>СНИЛС</span><strong>{{ $patient->getRawOriginal('snils') ?: 'Не указан' }}</strong></div>
                        <div><span>Полис ОМС</span><strong>{{ $patient->getRawOriginal('oms') ?: 'Не указан' }}</strong></div>
                        <div><span>Страховая компания</span><strong>{{ $patient->getRawOriginal('insurance_company') ?: 'Не указана' }}</strong></div>
                        <div><span>Телефон</span><strong>{{ $patient->phone ?: 'Не указан' }}</strong></div>
                        <div><span>Контактный email</span><strong>{{ $patient->email ?: 'Не указан' }}</strong></div>
                        <div><span>Место рождения</span><strong>{{ $patient->birth_place ?: 'Не указано' }}</strong></div>
                    </div>

                    <h3>Удостоверяющие документы</h3>

                    @forelse($patient->identityDocuments as $document)
                        @php
                            $documentNumber = preg_replace('/\\s+/', '', (string) $document->number);
                            $maskedDocumentNumber = $documentNumber !== ''
                                ? str_repeat('•', max(0, mb_strlen($documentNumber) - 4)) . mb_substr($documentNumber, -4)
                                : '';
                        @endphp
                        <div class="patient-identity-document">
                            <div>
                                <strong>{{ $document->type_label }}</strong>
                                <span>
                                    {{ trim(($document->series ?? '') . ' ' . $maskedDocumentNumber) ?: 'Реквизиты не указаны' }}
                                </span>
                            </div>
                            @if($document->is_primary && $document->is_active)
                                <span class="patient-identity-document__badge">Основной</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-secondary small mb-0">Документы в карте пока не указаны.</p>
                    @endforelse
                </div>
            </section>

            @if($changeRequest && $changeRequest->status === \App\Models\PatientProfileChangeRequest::STATUS_PENDING)
                <section class="patient-identity-status is-pending mt-3">
                    <div class="patient-identity-status__icon"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <strong>Запрос на изменение данных рассматривается</strong>
                        <p>
                            Клиника: {{ $changeRequest->reviewClinic?->name ?: 'не указана' }}.
                            Направлен:
                            {{ \App\Models\PatientProfileChangeRequest::targetOptions()[$changeRequest->review_target] ?? $changeRequest->review_target }}
                            @if($changeRequest->reviewDoctor)
                                — {{ $changeRequest->reviewDoctor->surname }} {{ $changeRequest->reviewDoctor->name }} {{ $changeRequest->reviewDoctor->patronym }}
                            @endif
                            .
                            Дата: {{ $changeRequest->created_at?->format('d.m.Y H:i') }}.
                        </p>
                        <p>Изменяемые поля: {{ collect($changeRequest->changed_fields ?? [])->map(fn($field) => $fieldLabels[$field] ?? $field)->implode(', ') }}</p>
                    </div>
                </section>
            @elseif($changeRequest && $changeRequest->status === \App\Models\PatientProfileChangeRequest::STATUS_REJECTED)
                <section class="patient-identity-status is-rejected mt-3">
                    <div class="patient-identity-status__icon"><i class="bi bi-exclamation-circle"></i></div>
                    <div>
                        <strong>Предыдущий запрос отклонён</strong>
                        <p>{{ $changeRequest->review_note ?: 'Обратитесь в клинику для уточнения причины.' }}</p>
                    </div>
                </section>
            @elseif($changeRequest && $changeRequest->status === \App\Models\PatientProfileChangeRequest::STATUS_APPROVED)
                <section class="patient-identity-status is-approved mt-3">
                    <div class="patient-identity-status__icon"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <strong>Последний запрос одобрен</strong>
                        <p>{{ $changeRequest->reviewed_at?->format('d.m.Y H:i') }}</p>
                        @if($changeRequest->review_note)<p>{{ $changeRequest->review_note }}</p>@endif
                    </div>
                </section>
            @endif

            @if(!$changeRequest || $changeRequest->status !== \App\Models\PatientProfileChangeRequest::STATUS_PENDING)
                @include('patients.profile.tabs.change-request-form')
            @endif
        @else
            @if($linkRequest && $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_PENDING_REVIEW)
                <section class="patient-identity-status is-pending">
                    <div class="patient-identity-status__icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <strong>Заявка передана на проверку</strong>
                        <p>
                            Сотрудник клиники сопоставит сведения с медицинской картой.
                            Дополнительных действий пока не требуется.
                        </p>
                    </div>
                </section>
            @elseif($linkRequest && $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_PENDING_EMAIL)
                <section class="patient-identity-status is-pending">
                    <div class="patient-identity-status__icon">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                    <div>
                        <strong>Ожидается подтверждение по email</strong>
                        <p>
                            Код отправлен на адрес, который уже был указан в найденной медицинской карте.
                            Если письмо недоступно, запрос будет передан сотруднику клиники.
                        </p>
                    </div>
                </section>

                <section class="patient-identity-card">
                    <div class="patient-identity-card__header">
                        <div>
                            <h2>Код подтверждения</h2>
                            <p>Введите шестизначный код из письма. Он действует 15 минут.</p>
                        </div>
                        <i class="bi bi-envelope-lock"></i>
                    </div>

                    <div class="patient-identity-card__body">
                        <form method="POST" action="{{ route('patients.identity.verify') }}">
                            @csrf
                            <input type="hidden" name="request_id" value="{{ $linkRequest->id }}">

                            <div class="patient-identity-code-row">
                                <input
                                    type="text"
                                    name="code"
                                    class="form-control"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    pattern="[0-9]{6}"
                                    maxlength="6"
                                    placeholder="000000"
                                    required
                                >
                                <button type="submit" class="patient-profile-primary">
                                    Подтвердить
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            @elseif($linkRequest && $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_REJECTED)
                <section class="patient-identity-status is-rejected">
                    <div class="patient-identity-status__icon">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <div>
                        <strong>Не удалось подтвердить данные</strong>
                        <p>
                            {{ $linkRequest->review_note ?: 'Проверьте введённые сведения или обратитесь в регистратуру.' }}
                        </p>
                    </div>
                </section>
            @endif

            @if(!$linkRequest || $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_REJECTED)
                <form
                    method="POST"
                    action="{{ route('patients.identity.submit') }}"
                    class="patient-identity-form"
                >
                    @csrf

                    <section class="patient-identity-card">
                        <div class="patient-identity-card__header">
                            <div>
                                <h2>Персональные данные</h2>
                                <p>Указывайте сведения точно так, как они внесены в медицинскую карту.</p>
                            </div>
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div class="patient-identity-card__body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="identitySurname">Фамилия</label>
                                    <input id="identitySurname" name="surname" class="form-control" value="{{ old('surname') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityName">Имя</label>
                                    <input id="identityName" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityPatronym">Отчество</label>
                                    <input id="identityPatronym" name="patronym" class="form-control" value="{{ old('patronym') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="identityBirthAt">Дата рождения</label>
                                    <input id="identityBirthAt" type="date" name="birth_at" class="form-control" value="{{ old('birth_at') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="identityGender">Пол</label>
                                    <select id="identityGender" name="gender" class="form-select" required>
                                        <option value="">Выберите</option>
                                        <option value="M" @selected(old('gender') === 'M')>Мужской</option>
                                        <option value="F" @selected(old('gender') === 'F')>Женский</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="identityBirthPlace">Место рождения</label>
                                    <input id="identityBirthPlace" name="birth_place" class="form-control" value="{{ old('birth_place') }}">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="patient-identity-card">
                        <div class="patient-identity-card__header">
                            <div>
                                <h2>Удостоверяющий документ</h2>
                                <p>Паспорт, свидетельство о рождении или другой документ.</p>
                            </div>
                            <i class="bi bi-card-text"></i>
                        </div>

                        <div class="patient-identity-card__body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="identityDocumentType">Тип документа</label>
                                    <select id="identityDocumentType" name="document_type" class="form-select">
                                        <option value="">Не выбран</option>
                                        @foreach(\App\Models\PatientIdentityDocument::typeOptions() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="identityDocumentSeries">Серия</label>
                                    <input id="identityDocumentSeries" name="document_series" class="form-control js-identity-document-series" value="{{ old('document_series') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="identityDocumentNumber">Номер</label>
                                    <input id="identityDocumentNumber" name="document_number" class="form-control js-identity-document-number" value="{{ old('document_number') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="identityDocumentIssuedBy">Кем выдан</label>
                                    <input id="identityDocumentIssuedBy" name="document_issued_by" class="form-control" value="{{ old('document_issued_by') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityDocumentIssuedAt">Дата выдачи</label>
                                    <input id="identityDocumentIssuedAt" type="date" name="document_issued_at" class="form-control" value="{{ old('document_issued_at') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityDocumentDepartmentCode">Код подразделения</label>
                                    <input id="identityDocumentDepartmentCode" name="document_department_code" class="form-control js-mask-department-code" maxlength="7" inputmode="numeric" placeholder="000-000" value="{{ old('document_department_code') }}">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="patient-identity-card">
                        <div class="patient-identity-card__header">
                            <div>
                                <h2>Страховые сведения</h2>
                                <p>СНИЛС и полис обязательного медицинского страхования.</p>
                            </div>
                            <i class="bi bi-shield-plus"></i>
                        </div>

                        <div class="patient-identity-card__body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="identitySnils">СНИЛС</label>
                                    <input id="identitySnils" name="snils" class="form-control js-mask-snils" inputmode="numeric" maxlength="14" autocomplete="off" placeholder="000-000-000 00" value="{{ old('snils') }}">
                                    <div class="patient-profile-hint">Введите 11 цифр — форматирование выполняется автоматически.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityOms">Номер полиса ОМС</label>
                                    <input id="identityOms" name="oms" class="form-control js-mask-policy" type="text" inputmode="text" autocomplete="off" value="{{ old('oms') }}" maxlength="32" placeholder="Номер полиса ОМС">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityInsuranceCompany">Страховая компания</label>
                                    <div class="patient-profile-autocomplete-wrap">
                                        <input
                                            id="identityInsuranceCompany"
                                            name="insurance_company"
                                            type="text"
                                            class="form-control js-patient-insurance-autocomplete"
                                            value="{{ old('insurance_company') }}"
                                            maxlength="255"
                                            autocomplete="off"
                                            data-suggest-box="#identityInsuranceCompanySuggest"
                                            placeholder="Начните вводить название страховой"
                                        >
                                        <div
                                            id="identityInsuranceCompanySuggest"
                                            class="patient-profile-autocomplete-suggest"
                                            role="listbox"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="patient-identity-card">
                        <div class="patient-identity-card__header">
                            <div>
                                <h2>Контактные данные и адреса</h2>
                                <p>Эти сведения также будут переданы для сопоставления с медицинской картой.</p>
                            </div>
                            <i class="bi bi-telephone"></i>
                        </div>

                        <div class="patient-identity-card__body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="identityPhone">Телефон</label>
                                    <input id="identityPhone" name="phone" type="tel" class="form-control js-mask-phone" value="{{ old('phone') }}" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="+7 (___) ___-__-__">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="identityEmail">Контактный email</label>
                                    <input id="identityEmail" name="email" type="email" class="form-control js-mask-email" value="{{ old('email') }}" maxlength="255" autocomplete="email">
                                    <div class="patient-profile-hint">Код подтверждения отправляется не на этот новый адрес, а на email, ранее указанный в медицинской карте.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="identityAddressRegistration">Адрес регистрации</label>
                                    <div class="patient-profile-autocomplete-wrap">
                                        <input
                                            id="identityAddressRegistration"
                                            name="address_registration"
                                            type="text"
                                            class="form-control js-patient-address-autocomplete"
                                            maxlength="500"
                                            autocomplete="off"
                                            data-suggest-box="#identityAddressRegistrationSuggest"
                                            placeholder="Начните вводить регион, город, улицу и дом"
                                            value="{{ old('address_registration') }}"
                                        >
                                        <div
                                            id="identityAddressRegistrationSuggest"
                                            class="patient-profile-autocomplete-suggest"
                                            role="listbox"
                                        ></div>
                                    </div>
                                    <div class="patient-profile-hint">Выберите подходящий адрес из подсказок — почтовый индекс добавится автоматически.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="identityAddressResidence">Адрес проживания</label>
                                    <div class="patient-profile-autocomplete-wrap">
                                        <input
                                            id="identityAddressResidence"
                                            name="address_residence"
                                            type="text"
                                            class="form-control js-patient-address-autocomplete"
                                            maxlength="500"
                                            autocomplete="off"
                                            data-suggest-box="#identityAddressResidenceSuggest"
                                            placeholder="Начните вводить регион, город, улицу и дом"
                                            value="{{ old('address_residence') }}"
                                        >
                                        <div
                                            id="identityAddressResidenceSuggest"
                                            class="patient-profile-autocomplete-suggest"
                                            role="listbox"
                                        ></div>
                                    </div>
                                    <div class="patient-profile-hint">Если адрес проживания совпадает с регистрацией, можно указать тот же адрес.</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="patient-identity-consent">
                        <i class="bi bi-lock"></i>
                        <span>
                            Переданные сведения шифруются и используются только для сопоставления
                            с медицинской картой. До подтверждения личности карта не привязывается.
                        </span>
                    </div>

                    <div class="patient-profile-savebar">
                        <span>Для поиска требуется СНИЛС или реквизиты удостоверяющего документа.</span>
                        <button type="submit" class="patient-profile-primary">
                            <i class="bi bi-search"></i>
                            Проверить данные и отправить заявку
                        </button>
                    </div>
                </form>
            @endif
        @endif
</div>
