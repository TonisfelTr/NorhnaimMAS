@extends('layouts.patient')

@section('title', 'Профиль пациента')

@section('content')
    <div class="patient-page patient-profile-page">
        <header class="patient-profile-hero">
            <div class="patient-profile-hero__identity">
                <div class="patient-profile-avatar">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Фотография профиля">
                    @else
                        <span>{{ $initials }}</span>
                    @endif
                </div>

                <div>
                    <div class="patient-page-header__eyebrow">Личный кабинет</div>
                    <h1>{{ $patientName }}</h1>
                    <p>
                        Управление личными данными, клиниками, врачами и настройками учётной записи.
                        @if($patient && $clinicCards->isNotEmpty())
                            <span class="patient-profile-hero__clinic-count">
                                Клиник: {{ $clinicCards->count() }}
                            </span>
                        @endif
                    </p>
                </div>
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
                <strong>Проверьте заполнение формы.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav class="patient-profile-tabs" aria-label="Разделы профиля">
            <a
                href="{{ route('patients.profile.index', ['tab' => 'profile']) }}"
                @class(['is-active' => $tab === 'profile'])
            >
                <i class="bi bi-person"></i>
                Личные данные
            </a>

            <a
                href="{{ route('patients.profile.index', ['tab' => 'identity']) }}"
                @class(['is-active' => $tab === 'identity'])
            >
                <i class="bi bi-person-vcard"></i>
                Медицинская карта
                @if($patient)
                    <span class="patient-profile-tab-status is-linked"></span>
                @elseif($linkRequest?->isPending())
                    <span class="patient-profile-tab-status is-pending"></span>
                @endif
            </a>

            <a
                href="{{ route('patients.profile.index', ['tab' => 'clinics']) }}"
                @class(['is-active' => $tab === 'clinics'])
            >
                <i class="bi bi-hospital"></i>
                Клиники
                @if($patient && $clinicCards->isNotEmpty())
                    <span>{{ $clinicCards->count() }}</span>
                @endif
            </a>

            <a
                href="{{ route('patients.profile.index', ['tab' => 'documents']) }}"
                @class(['is-active' => $tab === 'documents'])
            >
                <i class="bi bi-card-list"></i>
                Документы
                @if($patient && $patient->identityDocuments->isNotEmpty())
                    <span>{{ $patient->identityDocuments->count() }}</span>
                @endif
            </a>

            <a
                href="{{ route('patients.profile.index', ['tab' => 'history']) }}"
                @class(['is-active' => $tab === 'history'])
            >
                <i class="bi bi-clock-history"></i>
                История
            </a>

            <a
                href="{{ route('patients.profile.index', ['tab' => 'security']) }}"
                @class(['is-active' => $tab === 'security'])
            >
                <i class="bi bi-shield-lock"></i>
                Безопасность
            </a>
        </nav>

        @if($tab === 'profile')
            <div class="patient-profile-grid">
                <div class="patient-profile-main">
                    @if(!$patient)
                        <section class="patient-profile-card patient-profile-card--notice">
                            <div class="patient-profile-notice-icon">
                                <i class="bi bi-person-exclamation"></i>
                            </div>
                            <div>
                                <h2>Медицинская карта не привязана</h2>
                                <p>
                                    Учётная запись уже создана, но связь с медицинской картой
                                    ещё не подтверждена. После привязки подтверждённые сведения будут доступны для просмотра,
                                    а изменения можно будет отправлять только на согласование.
                                </p>
                            </div>
                        </section>
                    @else
                        <section class="patient-profile-card">
                            <div class="patient-profile-card__header">
                                <div>
                                    <h2>Личные данные</h2>
                                    <p>Подтверждённые сведения из медицинской карты. Прямое редактирование отключено.</p>
                                </div>
                                <i class="bi bi-person-vcard"></i>
                            </div>

                            <div class="patient-profile-card__body">
                                <div class="patient-profile-readonly-grid">
                                    <div><span>Фамилия</span><strong>{{ $patient->surname }}</strong></div>
                                    <div><span>Имя</span><strong>{{ $patient->name }}</strong></div>
                                    <div><span>Отчество</span><strong>{{ $patient->patronym ?: '—' }}</strong></div>
                                    <div><span>Дата рождения</span><strong>{{ $birthAt ? \Carbon\Carbon::parse($birthAt)->format('d.m.Y') : '—' }}</strong></div>
                                    <div><span>Пол</span><strong>{{ $patient->gender === 'M' ? 'Мужской' : ($patient->gender === 'F' ? 'Женский' : '—') }}</strong></div>
                                    <div><span>Телефон</span><strong>{{ $patient->phone ?: '—' }}</strong></div>
                                    <div><span>Контактный email</span><strong>{{ $patient->email ?: '—' }}</strong></div>
                                    <div><span>Место рождения</span><strong>{{ $patient->birth_place ?: '—' }}</strong></div>
                                    <div class="span-2"><span>Адрес регистрации</span><strong>{{ $patient->address_registration ?: '—' }}</strong></div>
                                    <div class="span-2"><span>Адрес проживания</span><strong>{{ $patient->address_residence ?: '—' }}</strong></div>
                                </div>


                            </div>
                        </section>
                    @endif
                </div>

                <aside class="patient-profile-side">
                    <section class="patient-profile-card">
                        <div class="patient-profile-card__header">
                            <div>
                                <h2>Фотография</h2>
                                <p>Изображение профиля учётной записи.</p>
                            </div>
                            <i class="bi bi-camera"></i>
                        </div>

                        <div class="patient-profile-card__body">
                            <div class="patient-profile-photo-preview">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Фотография профиля">
                                @else
                                    <span>{{ $initials }}</span>
                                @endif
                            </div>

                            <form
                                method="POST"
                                action="{{ route('patients.profile.avatar.store') }}"
                                enctype="multipart/form-data"
                            >
                                @csrf

                                <label for="patientProfileAvatar" class="form-label">
                                    Загрузить фотографию
                                </label>

                                <input
                                    id="patientProfileAvatar"
                                    type="file"
                                    name="avatar"
                                    class="form-control"
                                    accept="image/jpeg,image/png,image/webp"
                                    required
                                >

                                <div class="patient-profile-hint mb-3">
                                    JPG, PNG или WEBP. До 5 МБ.
                                </div>

                                <button type="submit" class="patient-profile-secondary w-100">
                                    <i class="bi bi-upload"></i>
                                    Загрузить
                                </button>
                            </form>

                            @if($avatarUrl)
                                <form
                                    method="POST"
                                    action="{{ route('patients.profile.avatar.destroy') }}"
                                    class="mt-2"
                                    onsubmit="return confirm('Удалить фотографию профиля?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="patient-profile-danger w-100">
                                        <i class="bi bi-trash3"></i>
                                        Удалить фотографию
                                    </button>
                                </form>
                            @endif
                        </div>
                    </section>

                    @if($patient)
                        <section class="patient-profile-card patient-profile-card--clinics-summary">
                            <div class="patient-profile-card__header">
                                <div>
                                    <h2>Клиники</h2>
                                    <p>Где вы наблюдаетесь и кто назначен лечащим врачом.</p>
                                </div>
                                <i class="bi bi-hospital"></i>
                            </div>

                            <div class="patient-profile-card__body">
                                <div class="patient-profile-clinic-summary">
                                    <strong>{{ $clinicCards->count() }}</strong>
                                    <span>активных связей с клиниками</span>
                                </div>

                                <a
                                    href="{{ route('patients.profile.index', ['tab' => 'clinics']) }}"
                                    class="patient-profile-text-link"
                                >
                                    Мои клиники и врачи
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </section>
                    @endif

                    <section class="patient-profile-card">
                        <div class="patient-profile-card__header">
                            <div>
                                <h2>Учётная запись</h2>
                                <p>Данные для входа в Норхнейм.</p>
                            </div>
                            <i class="bi bi-person-lock"></i>
                        </div>

                        <div class="patient-profile-card__body">
                            <div class="patient-profile-info-row">
                                <span>Логин</span>
                                <strong>{{ $user->login }}</strong>
                            </div>

                            <div class="patient-profile-info-row">
                                <span>Email для входа</span>
                                <strong>{{ $user->email }}</strong>
                            </div>

                            <a
                                href="{{ route('patients.profile.index', ['tab' => 'security']) }}"
                                class="patient-profile-text-link"
                            >
                                Настройки безопасности
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </section>
                </aside>
            </div>

        @elseif($tab === 'identity')
            @include('patients.profile.tabs.identity')

        @elseif($tab === 'clinics')
            <section class="patient-profile-card patient-profile-card--clinics">
                <div class="patient-profile-card__header">
                    <div>
                        <h2>Мои клиники</h2>
                        <p>
                            Один профиль пациента может быть связан с несколькими клиниками.
                            Для каждой клиники отдельно хранится лечащий врач.
                        </p>
                    </div>
                    <i class="bi bi-buildings"></i>
                </div>

                <div class="patient-profile-card__body">
                    @if(!$patient)
                        <div class="patient-profile-empty">
                            Медицинская карта ещё не привязана к учётной записи.
                        </div>
                    @elseif($clinicCards->isEmpty())
                        <div class="patient-profile-empty">
                            <i class="bi bi-hospital"></i>
                            <strong>Клиники пока не подключены</strong>
                            <p>
                                Связь появится после обращения или записи в клинику.
                            </p>
                        </div>
                    @else
                        <div class="patient-profile-clinics">
                            @foreach($clinicCards as $clinicCard)
                                <article class="patient-profile-clinic">
                                    <div class="patient-profile-clinic__head">
                                        <div class="patient-profile-clinic__icon">
                                            <i class="bi bi-hospital"></i>
                                        </div>

                                        <div class="patient-profile-clinic__title">
                                            <div>
                                                <h3>{{ $clinicCard['name'] }}</h3>

                                                @if($clinicCard['is_primary'])
                                                    <span class="patient-profile-clinic__badge is-primary">
                                                        Основная
                                                    </span>
                                                @endif

                                                <span class="patient-profile-clinic__badge">
                                                    {{ $clinicCard['clinic_type_label'] }}
                                                </span>
                                            </div>

                                            @if($clinicCard['city'])
                                                <p>
                                                    <i class="bi bi-geo-alt"></i>
                                                    {{ $clinicCard['city'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="patient-profile-clinic__details">
                                        <div>
                                            <span>Лечащий врач в этой клинике</span>

                                            @if($clinicCard['doctor_name'])
                                                <strong>{{ $clinicCard['doctor_name'] }}</strong>
                                                <small>{{ $clinicCard['doctor_speciality'] }}</small>
                                            @else
                                                <strong>Не назначен</strong>
                                                <small>Обратитесь в регистратуру клиники.</small>
                                            @endif
                                        </div>

                                        @if(!empty($clinicCard['phones']))
                                            <div>
                                                <span>Телефон клиники</span>
                                                <strong>{{ implode(', ', $clinicCard['phones']) }}</strong>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="patient-profile-clinic__actions">
                                        @if($clinicCard['clinic_type'] !== \App\Models\Clinic::TYPE_INPATIENT)
                                            <a
                                                href="{{ route('patients.calendar', ['clinic_id' => $clinicCard['id']]) }}"
                                                class="patient-profile-primary"
                                            >
                                                <i class="bi bi-calendar-plus"></i>
                                                Записаться
                                            </a>
                                        @else
                                            <span class="patient-profile-clinic__inpatient-note">
                                                <i class="bi bi-info-circle"></i>
                                                Для стационара самостоятельная амбулаторная запись недоступна.
                                            </span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="patient-profile-note mt-3">
                            <i class="bi bi-info-circle"></i>
                            Клиника добавляется в профиль после обращения или записи.
                            Персональные данные и документы пациента при этом остаются общими.
                        </div>
                    @endif
                </div>
            </section>

        @elseif($tab === 'documents')
            <section class="patient-profile-card">
                <div class="patient-profile-card__header">
                    <div>
                        <h2>Документы пациента</h2>
                        <p>
                            Сведения об удостоверяющих личность документах,
                            внесённых в медицинскую карту.
                        </p>
                    </div>
                    <i class="bi bi-card-list"></i>
                </div>

                <div class="patient-profile-card__body">
                    @if(!$patient)
                        <div class="patient-profile-empty">
                            Медицинская карта ещё не привязана к учётной записи.
                        </div>
                    @elseif($patient->identityDocuments->isEmpty())
                        <div class="patient-profile-empty">
                            <i class="bi bi-file-earmark-text"></i>
                            <strong>Документы пока не добавлены</strong>
                            <p>Документы можно предоставить в регистратуре клиники.</p>
                        </div>
                    @else
                        <div class="patient-profile-documents">
                            @foreach($patient->identityDocuments as $document)
                                @php
                                    $rawNumber = preg_replace('/\s+/', '', (string) $document->number);
                                    $maskedNumber = $rawNumber !== ''
                                        ? str_repeat('•', max(0, mb_strlen($rawNumber) - 4)) . mb_substr($rawNumber, -4)
                                        : 'Не указан';
                                @endphp

                                <article class="patient-profile-document">
                                    <div class="patient-profile-document__icon">
                                        <i class="bi bi-file-earmark-person"></i>
                                    </div>

                                    <div class="patient-profile-document__body">
                                        <div class="patient-profile-document__title">
                                            {{ $document->type_label }}

                                            @if($document->is_primary)
                                                <span>Основной</span>
                                            @endif

                                            @if(!$document->is_active)
                                                <span class="is-muted">Неактивен</span>
                                            @endif
                                        </div>

                                        <div class="patient-profile-document__meta">
                                            <div>Серия: {{ $document->series ?: '—' }}</div>
                                            <div>Номер: {{ $maskedNumber }}</div>
                                            <div>Кем выдан: {{ $document->issued_by ?: '—' }}</div>
                                            <div>Дата выдачи: {{ $document->issued_at?->format('d.m.Y') ?: '—' }}</div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="patient-profile-note">
                            <i class="bi bi-info-circle"></i>
                            Для изменения паспортных данных или загрузки нового документа
                            обратитесь в регистратуру. Документы должны быть проверены клиникой.
                        </div>
                    @endif
                </div>
            </section>

        @elseif($tab === 'history')
            <section class="patient-profile-card">
                <div class="patient-profile-card__header">
                    <div>
                        <h2>История изменений профиля</h2>
                        <p>Последние изменения личных данных и настроек учётной записи.</p>
                    </div>
                    <i class="bi bi-clock-history"></i>
                </div>

                <div class="patient-profile-card__body">
                    @if(!$patient)
                        <div class="patient-profile-empty">
                            История появится после привязки медицинской карты.
                        </div>
                    @elseif($events->isEmpty())
                        <div class="patient-profile-empty">
                            <i class="bi bi-clock"></i>
                            <strong>Изменений пока нет</strong>
                            <p>Здесь будут отображаться действия, выполненные через профиль пациента.</p>
                        </div>
                    @else
                        <div class="patient-profile-history">
                            @foreach($events as $event)
                                <article class="patient-profile-history__item">
                                    <div class="patient-profile-history__dot"></div>

                                    <div class="patient-profile-history__content">
                                        <div class="patient-profile-history__top">
                                            <strong>{{ $event->title }}</strong>
                                            <time>{{ $event->occurred_at?->format('d.m.Y H:i') }}</time>
                                        </div>

                                        @if($event->changes)
                                            <div class="patient-profile-history__changes">
                                                @foreach($event->changes as $label => $change)
                                                    <div>
                                                        <span>{{ $label }}</span>
                                                        <strong>
                                                            {{ $change['old'] ?? '—' }}
                                                            <i class="bi bi-arrow-right"></i>
                                                            {{ $change['new'] ?? '—' }}
                                                        </strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($event->actor)
                                            <small>Изменил: {{ $event->actor->login }}</small>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

        @elseif($tab === 'security')
            <div class="patient-profile-grid patient-profile-grid--security">
                <section class="patient-profile-card">
                    <div class="patient-profile-card__header">
                        <div>
                            <h2>Изменение пароля</h2>
                            <p>Для смены пароля подтвердите текущий пароль.</p>
                        </div>
                        <i class="bi bi-shield-lock"></i>
                    </div>

                    <div class="patient-profile-card__body">
                        <form
                            method="POST"
                            action="{{ route('patients.profile.password.update') }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="patientCurrentPassword" class="form-label">
                                    Текущий пароль
                                </label>
                                <input
                                    id="patientCurrentPassword"
                                    type="password"
                                    name="current_password"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="patientNewPassword" class="form-label">
                                    Новый пароль
                                </label>
                                <input
                                    id="patientNewPassword"
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="patientNewPasswordConfirmation" class="form-label">
                                    Повторите новый пароль
                                </label>
                                <input
                                    id="patientNewPasswordConfirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >
                            </div>

                            <button type="submit" class="patient-profile-primary">
                                <i class="bi bi-key"></i>
                                Изменить пароль
                            </button>
                        </form>
                    </div>
                </section>

                <aside class="patient-profile-card">
                    <div class="patient-profile-card__header">
                        <div>
                            <h2>Данные для входа</h2>
                            <p>Основная учётная запись.</p>
                        </div>
                        <i class="bi bi-envelope"></i>
                    </div>

                    <div class="patient-profile-card__body">
                        <div class="patient-profile-info-row">
                            <span>Логин</span>
                            <strong>{{ $user->login }}</strong>
                        </div>

                        <div class="patient-profile-info-row">
                            <span>Email для входа</span>
                            <strong>{{ $user->email }}</strong>
                        </div>

                        <div class="patient-profile-note mt-3">
                            <i class="bi bi-info-circle"></i>
                            Изменение email для входа требует отдельного подтверждения
                            нового адреса. Пока обратитесь в поддержку.
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
