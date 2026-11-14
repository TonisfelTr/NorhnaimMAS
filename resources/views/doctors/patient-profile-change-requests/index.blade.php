@extends('layouts.doctor')

@section('title', 'Запросы пациентов')

@section('main')
    @php
        $activeCounts = $type === 'link' ? $linkCounts : $changeCounts;
        $statusLabels = [
            'pending' => 'Ожидают проверки',
            'approved' => 'Одобрены',
            'rejected' => 'Отклонены',
        ];
        $typeLabels = [
            'link' => 'Привязка медкарты',
            'changes' => 'Изменение данных',
        ];
    @endphp

    <div class="nh-doctor-requests">
        <div class="nh-doctor-requests__inner">
            <header class="nh-doctor-requests__hero">
                <div class="nh-doctor-requests__hero-copy">
                    <div class="nh-doctor-requests__eyebrow">
                        <i class="bi bi-clipboard2-check"></i>
                        Медицинские сервисы
                    </div>
                    <h1>Запросы пациентов</h1>
                    <p>Проверка привязки медицинских карт и согласование изменений персональных данных.</p>
                </div>
                <div class="nh-doctor-requests__hero-icon" aria-hidden="true">
                    <i class="bi bi-person-vcard"></i>
                </div>
            </header>

            @if(session('success'))
                <div class="nh-requests-alert is-success" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="nh-requests-alert is-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Не удалось выполнить действие.</strong>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <section class="nh-requests-summary" aria-label="Ожидающие запросы">
                <a href="{{ route('doctors.patient-profile-change-requests.index', ['type' => 'link', 'status' => 'pending']) }}"
                   class="nh-requests-summary__item {{ $type === 'link' && $status === 'pending' ? 'is-current' : '' }}">
                    <span class="nh-requests-summary__icon is-cyan"><i class="bi bi-link-45deg"></i></span>
                    <span class="nh-requests-summary__copy">
                        <small>Привязка медкарты</small>
                        <strong>{{ $linkCounts['pending'] }}</strong>
                    </span>
                    <i class="bi bi-arrow-up-right nh-requests-summary__arrow"></i>
                </a>

                <a href="{{ route('doctors.patient-profile-change-requests.index', ['type' => 'changes', 'status' => 'pending']) }}"
                   class="nh-requests-summary__item {{ $type === 'changes' && $status === 'pending' ? 'is-current' : '' }}">
                    <span class="nh-requests-summary__icon is-blue"><i class="bi bi-file-earmark-person"></i></span>
                    <span class="nh-requests-summary__copy">
                        <small>Изменение данных</small>
                        <strong>{{ $changeCounts['pending'] }}</strong>
                    </span>
                    <i class="bi bi-arrow-up-right nh-requests-summary__arrow"></i>
                </a>

                <div class="nh-requests-summary__item is-static">
                    <span class="nh-requests-summary__icon is-violet"><i class="bi bi-inbox"></i></span>
                    <span class="nh-requests-summary__copy">
                        <small>Всего ожидают</small>
                        <strong>{{ $linkCounts['pending'] + $changeCounts['pending'] }}</strong>
                    </span>
                </div>
            </section>

            <section class="nh-requests-workspace">
                <nav class="nh-requests-types" aria-label="Типы запросов">
                    <a href="{{ route('doctors.patient-profile-change-requests.index', ['type' => 'link', 'status' => $status]) }}"
                       @class(['nh-requests-types__link', 'is-active' => $type === 'link'])>
                        <i class="bi bi-link-45deg"></i>
                        <span>Привязка медкарты</span>
                        <b>{{ $linkCounts['pending'] }}</b>
                    </a>
                    <a href="{{ route('doctors.patient-profile-change-requests.index', ['type' => 'changes', 'status' => $status]) }}"
                       @class(['nh-requests-types__link', 'is-active' => $type === 'changes'])>
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Изменение данных</span>
                        <b>{{ $changeCounts['pending'] }}</b>
                    </a>
                </nav>

                <div class="nh-requests-list-head">
                    <div>
                        <h2>{{ $typeLabels[$type] }}</h2>
                        <p>{{ $statusLabels[$status] }} · {{ $activeCounts[$status] }} в разделе</p>
                    </div>

                    <nav class="nh-requests-statuses" aria-label="Статус запросов">
                        @foreach($statusLabels as $key => $label)
                            <a href="{{ route('doctors.patient-profile-change-requests.index', ['type' => $type, 'status' => $key]) }}"
                               @class(['nh-requests-statuses__link', 'is-active' => $status === $key])>
                                {{ $label }}
                                <span>{{ $activeCounts[$key] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                @if($type === 'link')
                    <div class="nh-requests-note">
                        <i class="bi bi-info-circle"></i>
                        <span>
                            Здесь отображаются заявки, которые система сопоставила с конкретной медицинской картой,
                            закреплённой за вами. Заявки без однозначно найденной карты или без лечащего врача
                            остаются на проверке у администрации клиники в Filament.
                        </span>
                    </div>

                    <div class="nh-requests-cards">
                        @forelse($linkRequests as $linkRequest)
                            @php
                                $payload = (array) $linkRequest->payload;
                                $submittedName = trim(implode(' ', array_filter([
                                    $payload['surname'] ?? null,
                                    $payload['name'] ?? null,
                                    $payload['patronym'] ?? null,
                                ])));
                                $patientName = trim(implode(' ', array_filter([
                                    $linkRequest->patient?->surname,
                                    $linkRequest->patient?->name,
                                    $linkRequest->patient?->patronym,
                                ])));
                            @endphp

                            <article class="nh-request-card">
                                <div class="nh-request-card__top">
                                    <div class="nh-request-kind is-link">
                                        <i class="bi bi-link-45deg"></i>
                                        Первичная привязка медицинской карты
                                    </div>
                                    <span @class([
                                        'nh-request-badge',
                                        'is-pending' => $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_PENDING_REVIEW,
                                        'is-approved' => $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_APPROVED,
                                        'is-rejected' => $linkRequest->status === \App\Models\PatientLinkRequest::STATUS_REJECTED,
                                    ])>
                                        {{ $linkRequest->status === 'approved' ? 'Привязана' : ($linkRequest->status === 'rejected' ? 'Отклонена' : 'Требует проверки') }}
                                    </span>
                                </div>

                                <div class="nh-request-card__body">
                                    <div class="nh-request-person">
                                        <div class="nh-request-person__avatar">
                                            {{ mb_strtoupper(mb_substr($submittedName ?: 'П', 0, 1)) }}
                                        </div>
                                        <div class="nh-request-person__copy">
                                            <h3>{{ $submittedName ?: 'Имя не указано' }}</h3>
                                            <p>Личный кабинет: {{ $linkRequest->user?->login ?: '—' }} · {{ $linkRequest->user?->email ?: 'email не указан' }}</p>
                                        </div>
                                    </div>

                                    <div class="nh-request-facts">
                                        <div>
                                            <small>Найденная медицинская карта</small>
                                            <strong>{{ $patientName ?: 'Не указана' }}</strong>
                                            <span>
                                                @if($linkRequest->patient?->getRawOriginal('birth_at'))
                                                    {{ \Carbon\Carbon::parse($linkRequest->patient->getRawOriginal('birth_at'))->format('d.m.Y') }}
                                                @else
                                                    Дата рождения не указана
                                                @endif
                                                · №{{ $linkRequest->patient_id }}
                                            </span>
                                        </div>
                                        <div>
                                            <small>Заявка поступила</small>
                                            <strong>{{ $linkRequest->created_at?->format('d.m.Y') }}</strong>
                                            <span>{{ $linkRequest->created_at?->format('H:i') }}</span>
                                        </div>
                                        <div>
                                            <small>СНИЛС из заявки</small>
                                            <strong>{{ $payload['snils'] ?? 'Не указан' }}</strong>
                                            <span>Сверьте с документом пациента</span>
                                        </div>
                                    </div>

                                    <details class="nh-request-details">
                                        <summary>
                                            <span><i class="bi bi-card-text"></i> Посмотреть сведения из заявки</span>
                                            <i class="bi bi-chevron-down"></i>
                                        </summary>
                                        <div class="nh-request-details__grid">
                                            <div><small>ФИО</small><strong>{{ $submittedName ?: '—' }}</strong></div>
                                            <div><small>Дата рождения</small><strong>{{ $payload['birth_at'] ?? '—' }}</strong></div>
                                            <div><small>Пол</small><strong>{{ ($payload['gender'] ?? null) === 'M' ? 'Мужской' : (($payload['gender'] ?? null) === 'F' ? 'Женский' : '—') }}</strong></div>
                                            <div><small>Место рождения</small><strong>{{ $payload['birth_place'] ?? '—' }}</strong></div>
                                            <div><small>Документ</small><strong>{{ \App\Models\PatientIdentityDocument::typeOptions()[$payload['document_type'] ?? ''] ?? '—' }}</strong></div>
                                            <div><small>Серия и номер</small><strong>{{ trim(($payload['document_series'] ?? '') . ' ' . ($payload['document_number'] ?? '')) ?: '—' }}</strong></div>
                                            <div><small>Кем выдан</small><strong>{{ $payload['document_issued_by'] ?? '—' }}</strong></div>
                                            <div><small>Дата выдачи</small><strong>{{ $payload['document_issued_at'] ?? '—' }}</strong></div>
                                            <div><small>Код подразделения</small><strong>{{ $payload['document_department_code'] ?? '—' }}</strong></div>
                                            <div><small>СНИЛС</small><strong>{{ $payload['snils'] ?? '—' }}</strong></div>
                                            <div><small>Полис ОМС</small><strong>{{ $payload['oms'] ?? '—' }}</strong></div>
                                            <div><small>Страховая компания</small><strong>{{ $payload['insurance_company'] ?? '—' }}</strong></div>
                                            <div><small>Телефон</small><strong>{{ $payload['phone'] ?? '—' }}</strong></div>
                                            <div><small>Контактный email</small><strong>{{ $payload['email'] ?? '—' }}</strong></div>
                                            <div class="is-wide"><small>Адрес регистрации</small><strong>{{ $payload['address_registration'] ?? '—' }}</strong></div>
                                            <div class="is-wide"><small>Адрес проживания</small><strong>{{ $payload['address_residence'] ?? '—' }}</strong></div>
                                        </div>
                                    </details>

                                    @if($linkRequest->review_note)
                                        <div class="nh-request-review-note">
                                            <i class="bi bi-chat-left-text"></i>
                                            {{ $linkRequest->review_note }}
                                        </div>
                                    @endif

                                    @if($linkRequest->isPending())
                                        <div class="nh-request-actions">
                                            <form method="POST"
                                                  action="{{ route('doctors.patient-link-requests.approve', $linkRequest) }}"
                                                  onsubmit="return confirm('Подтвердить привязку этой медицинской карты к аккаунту пациента?')">
                                                @csrf
                                                <button type="submit" class="nh-request-button is-primary">
                                                    <i class="bi bi-check2-circle"></i>
                                                    Подтвердить привязку
                                                </button>
                                            </form>

                                            <details class="nh-request-reject">
                                                <summary><i class="bi bi-x-circle"></i> Отклонить заявку</summary>
                                                <form method="POST" action="{{ route('doctors.patient-link-requests.reject', $linkRequest) }}">
                                                    @csrf
                                                    <label for="link-reject-{{ $linkRequest->id }}">Причина отклонения</label>
                                                    <textarea id="link-reject-{{ $linkRequest->id }}" name="reason" rows="2" minlength="5" maxlength="2000" required></textarea>
                                                    <button type="submit" class="nh-request-button is-danger">Подтвердить отказ</button>
                                                </form>
                                            </details>
                                        </div>
                                    @else
                                        <div class="nh-request-processed">
                                            @if($linkRequest->reviewedBy)
                                                Рассмотрел: {{ $linkRequest->reviewedBy->login }}
                                            @endif
                                            @if($linkRequest->reviewed_at)
                                                · {{ $linkRequest->reviewed_at->format('d.m.Y H:i') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="nh-requests-empty">
                                <div class="nh-requests-empty__icon"><i class="bi bi-inbox"></i></div>
                                <h3>Заявок пока нет</h3>
                                <p>
                                    @if($status === 'pending')
                                        Нет заявок на привязку, сопоставленных с вашими пациентами и ожидающих проверки.
                                        Если заявка не нашла однозначную медицинскую карту, она поступает администрации клиники.
                                    @elseif($status === 'approved')
                                        Вы ещё не подтверждали заявки на привязку медицинских карт.
                                    @else
                                        Отклонённых заявок на привязку пока нет.
                                    @endif
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if($linkRequests && $linkRequests->hasPages())
                        <div class="nh-requests-pagination">{{ $linkRequests->links() }}</div>
                    @endif
                @else
                    <div class="nh-requests-cards">
                        @forelse($changeRequests as $changeRequest)
                            <article class="nh-request-card">
                                <div class="nh-request-card__top">
                                    <div class="nh-request-kind is-change">
                                        <i class="bi bi-person-lines-fill"></i>
                                        Изменение подтверждённых данных
                                    </div>
                                    <span @class([
                                        'nh-request-badge',
                                        'is-pending' => $changeRequest->status === \App\Models\PatientProfileChangeRequest::STATUS_PENDING,
                                        'is-approved' => $changeRequest->status === \App\Models\PatientProfileChangeRequest::STATUS_APPROVED,
                                        'is-rejected' => $changeRequest->status === \App\Models\PatientProfileChangeRequest::STATUS_REJECTED,
                                    ])>
                                        {{ $changeRequest->status === 'pending' ? 'Требует проверки' : ($changeRequest->status === 'approved' ? 'Одобрена' : 'Отклонена') }}
                                    </span>
                                </div>

                                <div class="nh-request-card__body">
                                    <div class="nh-request-person">
                                        <div class="nh-request-person__avatar is-blue">
                                            {{ mb_strtoupper(mb_substr((string) ($changeRequest->patient?->surname ?: 'П'), 0, 1)) }}
                                        </div>
                                        <div class="nh-request-person__copy">
                                            <h3>{{ $changeRequest->patient?->surname }} {{ $changeRequest->patient?->name }} {{ $changeRequest->patient?->patronym }}</h3>
                                            <p>Личный кабинет: {{ $changeRequest->user?->login ?: '—' }} · {{ $changeRequest->user?->email ?: 'email не указан' }}</p>
                                            @if($changeRequest->reviewClinic)
                                                <p class="nh-request-person__clinic">
                                                    <i class="bi bi-hospital"></i>
                                                    {{ $changeRequest->reviewClinic->name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="nh-request-facts">
                                        <div>
                                            <small>Изменяемые поля</small>
                                            <strong>{{ count($changeRequest->changed_fields ?? []) }}</strong>
                                            <span>{{ collect($changeRequest->changed_fields ?? [])->map(fn ($field) => $fieldLabels[$field] ?? $field)->implode(', ') }}</span>
                                        </div>
                                        <div>
                                            <small>Заявка поступила</small>
                                            <strong>{{ $changeRequest->created_at?->format('d.m.Y') }}</strong>
                                            <span>{{ $changeRequest->created_at?->format('H:i') }}</span>
                                        </div>
                                        <div>
                                            <small>Причина</small>
                                            <strong>{{ \Illuminate\Support\Str::limit($changeRequest->reason, 90) }}</strong>
                                        </div>
                                    </div>

                                    @include('doctors.patient-profile-change-requests.details', ['changeRequest' => $changeRequest])

                                    @if($changeRequest->isPending())
                                        <div class="nh-request-actions">
                                            <form method="POST"
                                                  action="{{ route('doctors.patient-profile-change-requests.approve', $changeRequest) }}"
                                                  onsubmit="return confirm('Одобрить изменение подтверждённых данных пациента?')">
                                                @csrf
                                                <button type="submit" class="nh-request-button is-primary">
                                                    <i class="bi bi-check2-circle"></i>
                                                    Одобрить изменения
                                                </button>
                                            </form>

                                            <details class="nh-request-reject">
                                                <summary><i class="bi bi-x-circle"></i> Отклонить запрос</summary>
                                                <form method="POST" action="{{ route('doctors.patient-profile-change-requests.reject', $changeRequest) }}">
                                                    @csrf
                                                    <label for="change-reject-{{ $changeRequest->id }}">Причина отклонения</label>
                                                    <textarea id="change-reject-{{ $changeRequest->id }}" name="review_note" rows="2" minlength="5" maxlength="2000" required></textarea>
                                                    <button type="submit" class="nh-request-button is-danger">Подтвердить отказ</button>
                                                </form>
                                            </details>
                                        </div>
                                    @else
                                        <div class="nh-request-processed">
                                            @if($changeRequest->reviewer)
                                                Рассмотрел: {{ $changeRequest->reviewer->login }}
                                            @endif
                                            @if($changeRequest->reviewed_at)
                                                · {{ $changeRequest->reviewed_at->format('d.m.Y H:i') }}
                                            @endif
                                            @if($changeRequest->review_note)
                                                <p>{{ $changeRequest->review_note }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="nh-requests-empty">
                                <div class="nh-requests-empty__icon"><i class="bi bi-inbox"></i></div>
                                <h3>Запросов пока нет</h3>
                                <p>
                                    @if($status === 'pending')
                                        Нет запросов на изменение данных, направленных вам как лечащему врачу.
                                    @elseif($status === 'approved')
                                        Одобренных запросов на изменение данных пока нет.
                                    @else
                                        Отклонённых запросов на изменение данных пока нет.
                                    @endif
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if($changeRequests && $changeRequests->hasPages())
                        <div class="nh-requests-pagination">{{ $changeRequests->links() }}</div>
                    @endif
                @endif
            </section>
        </div>
    </div>
@endsection
