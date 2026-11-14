@extends('layouts.patient')

@section('title', 'Подтверждение личности')

@section('content')
    <div class="patient-page patient-identity-page">
        <header class="patient-identity-hero">
            <div>
                <div class="patient-page-header__eyebrow">Личный кабинет пациента</div>
                <h1>Подтверждение личности</h1>
                <p>
                    Заполните документы и страховые сведения, чтобы найти и безопасно
                    привязать существующую медицинскую карту.
                </p>
            </div>

            <a href="{{ route('patients.dashboard') }}" class="patient-profile-back">
                <i class="bi bi-arrow-left"></i>
                К обзору
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <strong>Проверьте данные.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
        @else
            @if($linkRequest && in_array($linkRequest->status, [
                \App\Models\PatientLinkRequest::STATUS_PENDING_EMAIL,
                \App\Models\PatientLinkRequest::STATUS_PENDING_REVIEW,
            ], true))
                <section class="patient-identity-status is-pending">
                    <div class="patient-identity-status__icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <strong>Заявка обрабатывается</strong>
                        <p>
                            Если в медицинской карте указан доступный email, на него направлен
                            код подтверждения. Если письмо не пришло, ничего дополнительно
                            делать не нужно — сведения проверит сотрудник клиники.
                        </p>
                    </div>
                </section>

                <section class="patient-identity-card">
                    <div class="patient-identity-card__header">
                        <div>
                            <h2>Код подтверждения</h2>
                            <p>Если вы получили шестизначный код, введите его здесь.</p>
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
                                    <input id="identityDocumentSeries" name="document_series" class="form-control" value="{{ old('document_series') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="identityDocumentNumber">Номер</label>
                                    <input id="identityDocumentNumber" name="document_number" class="form-control" value="{{ old('document_number') }}">
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
                                    <input id="identityDocumentDepartmentCode" name="document_department_code" class="form-control" value="{{ old('document_department_code') }}">
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
                                    <input id="identitySnils" name="snils" class="form-control" inputmode="numeric" placeholder="00000000000" value="{{ old('snils') }}">
                                    <div class="patient-profile-hint">11 цифр без пробелов и дефисов.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityOms">Номер полиса ОМС</label>
                                    <input id="identityOms" name="oms" class="form-control" value="{{ old('oms') }}" maxlength="16">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="identityInsuranceCompany">Страховая компания</label>
                                    <input id="identityInsuranceCompany" name="insurance_company" class="form-control" value="{{ old('insurance_company') }}" maxlength="255">
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
@endsection
