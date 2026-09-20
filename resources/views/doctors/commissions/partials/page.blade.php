@php
    $isMilitary = $commissionType === \App\Models\CommissionDocument::TYPE_MILITARY;
    $draftData = (array) ($activeDocument?->data ?? []);

    $pageTitle = $isMilitary ? 'Военкомат / ВВК' : 'Медико-социальная экспертиза';
    $pageSubtitle = $isMilitary
        ? 'Подготовка медицинских сведений и комплекта документов для военно-врачебной экспертизы'
        : 'Подготовка направления на медико-социальную экспертизу · форма 088/у';
    $pageEyebrow = $isMilitary ? 'Комиссии и экспертизы' : 'Комиссии и экспертизы · форма 088/у';
    $pageIcon = $isMilitary ? 'bi-shield-check' : 'bi-universal-access';
    $formAction = $isMilitary
        ? route('doctors.commissions.military.store')
        : route('doctors.commissions.mse.store');

    $militaryUrl = route(
        'doctors.commissions.military',
        $selectedPatient ? ['patient_id' => $selectedPatient->id] : []
    );
    $mseUrl = route(
        'doctors.commissions.mse',
        $selectedPatient ? ['patient_id' => $selectedPatient->id] : []
    );

    $patientName = $selectedPatient
        ? collect([
            $selectedPatient->surname,
            $selectedPatient->name,
            $selectedPatient->patronym,
        ])->filter()->implode(' ')
        : '';

    $patientInitials = $selectedPatient
        ? mb_strtoupper(
            mb_substr((string) $selectedPatient->surname, 0, 1)
            . mb_substr((string) $selectedPatient->name, 0, 1)
        )
        : '—';

    $ageWord = static function (int $age): string {
        $mod100 = $age % 100;
        $mod10 = $age % 10;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return 'лет';
        }

        return match ($mod10) {
            1 => 'год',
            2, 3, 4 => 'года',
            default => 'лет',
        };
    };

    $birthDate = '';
    $patientAge = null;
    if ($selectedPatient?->birth_at) {
        try {
            $birth = \Illuminate\Support\Carbon::parse($selectedPatient->birth_at);
            $birthDate = $birth->format('d.m.Y');
            $patientAge = $birth->age;
        } catch (\Throwable) {
            $birthDate = (string) $selectedPatient->birth_at;
        }
    }

    $diagnosisCode = trim((string) data_get($selectedPatient, 'diagnose.code'));
    $diagnosisTitle = trim((string) (
        data_get($selectedPatient, 'diagnose.title')
        ?? data_get($selectedPatient, 'diagnose.name')
        ?? ''
    ));
    $diagnosisText = trim(collect([$diagnosisCode, $diagnosisTitle])->filter()->implode(' — '));

    $patientPhone = data_get($selectedPatient, 'phone')
        ?? data_get($selectedPatient, 'telephone')
        ?? data_get($selectedPatient, 'mobile_phone');
    $patientSnils = data_get($selectedPatient, 'snils') ?? data_get($selectedPatient, 'SNILS');
    $patientOms = data_get($selectedPatient, 'oms')
        ?? data_get($selectedPatient, 'insurance_policy')
        ?? data_get($selectedPatient, 'policy')
        ?? data_get($selectedPatient, 'oms_policy');
    $patientAddress = data_get($selectedPatient, 'address_registration')
        ?? data_get($selectedPatient, 'registration_address')
        ?? data_get($selectedPatient, 'address')
        ?? data_get($selectedPatient, 'address_residence');

    $value = static function (string $key, mixed $default = '') use ($draftData): mixed {
        return old($key, data_get($draftData, $key, $default));
    };

    $documentDate = old(
        'document_date',
        $activeDocument?->document_date?->format('Y-m-d') ?? now()->toDateString()
    );

    $militaryAttachments = old('attachments', data_get($draftData, 'attachments', []));
    $militaryAttachments = is_array($militaryAttachments) ? $militaryAttachments : [];

    $mseConditions = old('examination_conditions', data_get($draftData, 'examination_conditions', []));
    $mseConditions = is_array($mseConditions) ? $mseConditions : [];

    $msePurposes = old('purposes', data_get($draftData, 'purposes', []));
    $msePurposes = is_array($msePurposes) ? $msePurposes : [];

    $writtenConsent = old('written_consent', data_get($draftData, 'written_consent', false));
@endphp

@include('doctors.commissions.partials.styles')

<main
    class="commission-page {{ $isMilitary ? 'commission-page--military' : 'commission-page--mse' }}"
    id="commissionPage"
    data-commission-root
    data-patient-search-url="{{ route('api.patients.search') }}"
    data-page-url="{{ $isMilitary ? route('doctors.commissions.military') : route('doctors.commissions.mse') }}"
>
    <div class="container-fluid commission-page__container">
        @if (session('message'))
            <div class="commission-alert commission-alert--success" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="commission-alert commission-alert--danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Проверьте заполнение формы.</strong>
                    <div>{{ $errors->first() }}</div>
                </div>
            </div>
        @endif

        <section class="commission-hero">
            <div class="commission-hero__main">
                <span class="commission-hero__icon">
                    <i class="bi {{ $pageIcon }}"></i>
                </span>
                <div>
                    <div class="commission-hero__eyebrow">{{ $pageEyebrow }}</div>
                    <h1>{{ $pageTitle }}</h1>
                    <p>{{ $pageSubtitle }}</p>
                </div>
            </div>

            <div class="commission-tabs" aria-label="Разделы комиссий">
                <a
                    class="commission-tab {{ $isMilitary ? 'active' : '' }}"
                    href="{{ $militaryUrl }}"
                >
                    <i class="bi bi-shield-check"></i>
                    Военкомат / ВВК
                </a>
                <a
                    class="commission-tab {{ !$isMilitary ? 'active' : '' }}"
                    href="{{ $mseUrl }}"
                >
                    <i class="bi bi-universal-access"></i>
                    МСЭ
                </a>
            </div>
        </section>

        <div class="commission-layout">
            <aside class="commission-sidebar">
                <section class="commission-card commission-sidebar__search">
                    <div class="commission-card__head">
                        <span class="commission-card__icon"><i class="bi bi-person-search"></i></span>
                        <div>
                            <h2>Пациент</h2>
                            <p>Найдите пациента для подготовки документа</p>
                        </div>
                    </div>

                    <div class="commission-patient-search">
                        <i class="bi bi-search"></i>
                        <input
                            type="search"
                            class="form-control"
                            id="commissionPatientSearch"
                            value="{{ $patientName }}"
                            autocomplete="off"
                            placeholder="ФИО пациента..."
                        >
                        <button
                            type="button"
                            class="commission-patient-search__clear {{ $selectedPatient ? '' : 'd-none' }}"
                            id="commissionPatientClear"
                            title="Снять выбор пациента"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <div class="commission-patient-results" id="commissionPatientResults"></div>
                    </div>
                    <div class="commission-patient-search__hint" id="commissionPatientHint">
                        Начните вводить фамилию, имя или отчество.
                    </div>
                </section>

                @if ($selectedPatient)
                    <section class="commission-card commission-patient-card">
                        <div class="commission-patient-card__top">
                            <div class="commission-patient-card__avatar">{{ $patientInitials ?: 'П' }}</div>
                            <div class="commission-patient-card__identity">
                                <strong>{{ $patientName }}</strong>
                                <span>
                                    {{ $birthDate ?: 'Дата рождения не указана' }}
                                    @if ($patientAge !== null)
                                        · {{ $patientAge }} {{ $ageWord($patientAge) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="commission-patient-meta">
                            <div>
                                <span>СНИЛС</span>
                                <strong>{{ $patientSnils ?: 'не указан' }}</strong>
                            </div>
                            <div>
                                <span>ОМС</span>
                                <strong>{{ $patientOms ?: 'не указан' }}</strong>
                            </div>
                            <div>
                                <span>Телефон</span>
                                <strong>{{ $patientPhone ?: 'не указан' }}</strong>
                            </div>
                            <div class="commission-patient-meta__wide">
                                <span>Адрес регистрации</span>
                                <strong>{{ $patientAddress ?: 'не указан' }}</strong>
                            </div>
                        </div>

                        <div class="commission-diagnosis">
                            <div class="commission-diagnosis__label">
                                <i class="bi bi-activity"></i>
                                Диагноз из карточки пациента
                            </div>
                            @if ($diagnosisText)
                                <div class="commission-diagnosis__value">
                                    @if ($diagnosisCode)
                                        <span>{{ $diagnosisCode }}</span>
                                    @endif
                                    <strong>{{ $diagnosisTitle ?: $diagnosisCode }}</strong>
                                </div>
                            @else
                                <div class="commission-diagnosis__empty">
                                    <i class="bi bi-exclamation-circle"></i>
                                    В карточке пациента диагноз не указан.
                                </div>
                            @endif
                        </div>
                    </section>

                    @if ($documents->isNotEmpty())
                        <section class="commission-card commission-history">
                            <div class="commission-card__head commission-card__head--compact">
                                <span class="commission-card__icon"><i class="bi bi-clock-history"></i></span>
                                <div>
                                    <h2>Последние документы</h2>
                                    <p>{{ $isMilitary ? 'По ВВК' : 'По МСЭ' }}</p>
                                </div>
                            </div>

                            <div class="commission-history__list">
                                @foreach ($documents as $document)
                                    <a
                                        class="commission-history__item {{ $activeDocument?->id === $document->id ? 'active' : '' }}"
                                        href="{{ route(
                                            $isMilitary ? 'doctors.commissions.military' : 'doctors.commissions.mse',
                                            ['patient_id' => $selectedPatient->id, 'document_id' => $document->id]
                                        ) }}"
                                    >
                                        <div>
                                            <strong>{{ $document->number ?: 'Без номера' }}</strong>
                                            <span>{{ optional($document->document_date)->format('d.m.Y') ?: $document->created_at?->format('d.m.Y') }}</span>
                                        </div>
                                        <span class="commission-status commission-status--{{ $document->status }}">
                                            {{ $document->status === \App\Models\CommissionDocument::STATUS_PREPARED ? 'Подготовлено' : 'Черновик' }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @else
                    <section class="commission-card commission-empty-patient">
                        <span><i class="bi bi-person-vcard"></i></span>
                        <strong>Пациент не выбран</strong>
                        <p>После выбора пациента форма станет доступна, а диагноз и основные реквизиты подтянутся из карточки.</p>
                    </section>
                @endif
            </aside>

            <section class="commission-workspace">
                <form method="POST" action="{{ $formAction }}" id="commissionForm">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $selectedPatient?->id }}">
                    <input type="hidden" name="document_id" value="{{ $activeDocument?->id }}">

                    <fieldset class="commission-form-fieldset" {{ !$selectedPatient ? 'disabled' : '' }}>
                        <section class="commission-card commission-document-head">
                            <div class="commission-document-head__title">
                                <span class="commission-card__icon commission-card__icon--large">
                                    <i class="bi {{ $pageIcon }}"></i>
                                </span>
                                <div>
                                    <div class="commission-document-head__eyebrow">
                                        {{ $isMilitary ? 'Медицинские сведения для ВВК' : 'Направление на МСЭ' }}
                                    </div>
                                    <h2>{{ $isMilitary ? 'Подготовка документа для военкомата' : 'Форма 088/у' }}</h2>
                                </div>
                            </div>

                            <div class="commission-document-head__meta">
                                <label>
                                    <span>Дата документа</span>
                                    <input class="form-control" type="date" name="document_date" value="{{ $documentDate }}">
                                </label>
                                <div class="commission-number-box">
                                    <span>Номер</span>
                                    <strong>{{ $activeDocument?->number ?: 'присвоится после сохранения' }}</strong>
                                </div>
                            </div>
                        </section>

                        @if ($isMilitary)
                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-building"></i></span>
                                    <div>
                                        <h2>Основание и адресат</h2>
                                        <p>Куда и по какому запросу передаются медицинские сведения</p>
                                    </div>
                                </div>

                                <div class="commission-grid commission-grid--2">
                                    <label class="commission-field commission-field--wide">
                                        <span>Военный комиссариат / организация</span>
                                        <input
                                            class="form-control"
                                            name="recipient"
                                            value="{{ $value('recipient') }}"
                                            placeholder="Например: Военный комиссариат ..."
                                        >
                                    </label>
                                    <label class="commission-field">
                                        <span>Номер запроса</span>
                                        <input class="form-control" name="request_number" value="{{ $value('request_number') }}" placeholder="№ запроса">
                                    </label>
                                    <label class="commission-field">
                                        <span>Дата запроса</span>
                                        <input class="form-control" type="date" name="request_date" value="{{ $value('request_date') }}">
                                    </label>
                                    <label class="commission-field">
                                        <span>Цель предоставления</span>
                                        <select class="form-select" name="purpose">
                                            <option value="">Выберите...</option>
                                            @foreach ([
                                                'initial_registration' => 'Первоначальная постановка на воинский учёт',
                                                'conscription' => 'Призыв на военную службу',
                                                'contract' => 'Поступление на военную службу по контракту',
                                                'training' => 'Военные сборы',
                                                'repeat_vvk' => 'Повторное / контрольное освидетельствование',
                                                'other' => 'Другое',
                                            ] as $key => $label)
                                                <option value="{{ $key }}" @selected($value('purpose') === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="commission-field">
                                        <span>Если «другое»</span>
                                        <input class="form-control" name="purpose_other" value="{{ $value('purpose_other') }}" placeholder="Уточните цель">
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Вопрос, поставленный перед ВВК</span>
                                        <textarea class="form-control" name="commission_question" rows="3" placeholder="Что необходимо оценить / уточнить по запросу">{{ $value('commission_question') }}</textarea>
                                    </label>
                                </div>
                            </section>

                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-heart-pulse"></i></span>
                                    <div>
                                        <h2>Медицинские сведения</h2>
                                        <p>Диагноз уже взят из карточки пациента — здесь только клиническое описание</p>
                                    </div>
                                </div>

                                <div class="commission-live-diagnosis">
                                    <span>Текущий диагноз</span>
                                    <strong>{{ $diagnosisText ?: 'В карточке пациента не указан' }}</strong>
                                </div>

                                <div class="commission-grid commission-grid--2">
                                    <label class="commission-field commission-field--wide">
                                        <span>Жалобы и анамнез заболевания</span>
                                        <textarea class="form-control" name="complaints_anamnesis" rows="5" placeholder="Жалобы, начало заболевания, течение, обострения...">{{ $value('complaints_anamnesis') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Объективный статус</span>
                                        <textarea class="form-control" name="objective_status" rows="5" placeholder="Объективные данные на момент подготовки сведений">{{ $value('objective_status') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Результаты обследований и консультаций</span>
                                        <textarea class="form-control" name="examination_results" rows="5" placeholder="Лабораторные, инструментальные исследования, заключения специалистов...">{{ $value('examination_results') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Лечение и динамика</span>
                                        <textarea class="form-control" name="treatment_dynamics" rows="4" placeholder="Проведённое лечение, эффект, динамика состояния">{{ $value('treatment_dynamics') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Функциональные ограничения</span>
                                        <textarea class="form-control" name="functional_limitations" rows="4" placeholder="Стойкие/временные ограничения, влияющие на повседневную и профессиональную деятельность">{{ $value('functional_limitations') }}</textarea>
                                    </label>
                                </div>
                            </section>

                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-files"></i></span>
                                    <div>
                                        <h2>Комплект документов</h2>
                                        <p>Отметьте сведения, которые входят в передаваемый пакет</p>
                                    </div>
                                </div>

                                <div class="commission-check-grid">
                                    @foreach ([
                                        'outpatient_extract' => ['bi-file-medical', 'Выписка из амбулаторной карты'],
                                        'discharge_summaries' => ['bi-hospital', 'Выписные эпикризы стационара'],
                                        'laboratory' => ['bi-droplet-half', 'Лабораторные исследования'],
                                        'instrumental' => ['bi-activity', 'Инструментальные исследования'],
                                        'specialists' => ['bi-people', 'Заключения специалистов'],
                                        'treatment_documents' => ['bi-capsule', 'Сведения о лечении и реабилитации'],
                                    ] as $key => [$icon, $label])
                                        <label class="commission-check-card">
                                            <input type="checkbox" name="attachments[]" value="{{ $key }}" @checked(in_array($key, $militaryAttachments, true))>
                                            <span>
                                                <i class="bi {{ $icon }}"></i>
                                                <strong>{{ $label }}</strong>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <label class="commission-field commission-field--spaced">
                                    <span>Медицинское заключение / сведения для ВВК</span>
                                    <textarea class="form-control" name="medical_information" rows="6" placeholder="Кратко сформулируйте подтверждённые медицинские сведения, значимые для освидетельствования">{{ $value('medical_information') }}</textarea>
                                </label>

                                <label class="commission-field commission-field--spaced">
                                    <span>Дополнительные примечания</span>
                                    <textarea class="form-control" name="additional_notes" rows="3">{{ $value('additional_notes') }}</textarea>
                                </label>

                                <div class="commission-note">
                                    <i class="bi bi-info-circle"></i>
                                    <div>
                                        <strong>Важно</strong>
                                        <span>Эта страница готовит медицинские сведения для ВВК. Итоговое заключение о категории годности оформляется самой военно-врачебной комиссией.</span>
                                    </div>
                                </div>
                            </section>
                        @else
                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-clipboard2-check"></i></span>
                                    <div>
                                        <h2>Решение врачебной комиссии</h2>
                                        <p>Реквизиты направления на медико-социальную экспертизу</p>
                                    </div>
                                </div>

                                <div class="commission-grid commission-grid--2">
                                    <label class="commission-field">
                                        <span>Номер протокола ВК</span>
                                        <input class="form-control" name="vk_protocol_number" value="{{ $value('vk_protocol_number') }}" placeholder="№ протокола">
                                    </label>
                                    <label class="commission-field">
                                        <span>Дата протокола ВК</span>
                                        <input class="form-control" type="date" name="vk_protocol_date" value="{{ $value('vk_protocol_date') }}">
                                    </label>
                                </div>

                                <div class="commission-inline-options">
                                    <label class="commission-switch-card">
                                        <input type="checkbox" name="written_consent" value="1" @checked((bool) $writtenConsent)>
                                        <span class="commission-switch-card__check"><i class="bi bi-check-lg"></i></span>
                                        <span>
                                            <strong>Получено письменное согласие</strong>
                                            <small>Согласие гражданина на направление и передачу медицинских сведений</small>
                                        </span>
                                    </label>
                                    <label class="commission-field commission-field--date-small">
                                        <span>Дата согласия</span>
                                        <input class="form-control" type="date" name="consent_date" value="{{ $value('consent_date') }}">
                                    </label>
                                </div>
                            </section>

                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-bullseye"></i></span>
                                    <div>
                                        <h2>Цель направления и условия проведения МСЭ</h2>
                                        <p>Отметьте применимые пункты</p>
                                    </div>
                                </div>

                                <div class="commission-section-label">Особые условия проведения экспертизы</div>
                                <div class="commission-check-grid commission-check-grid--3">
                                    @foreach ([
                                        'home' => ['bi-house-heart', 'Требуется проведение МСЭ на дому'],
                                        'palliative' => ['bi-heart-pulse', 'Пациент получает паллиативную помощь'],
                                        'post_amputation' => ['bi-person-wheelchair', 'Первичное протезирование после ампутации'],
                                    ] as $key => [$icon, $label])
                                        <label class="commission-check-card">
                                            <input type="checkbox" name="examination_conditions[]" value="{{ $key }}" @checked(in_array($key, $mseConditions, true))>
                                            <span>
                                                <i class="bi {{ $icon }}"></i>
                                                <strong>{{ $label }}</strong>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="commission-section-label commission-section-label--spaced">Цель проведения МСЭ</div>
                                <div class="commission-check-grid commission-check-grid--2">
                                    @foreach ([
                                        'disability_group' => 'Установление / изменение группы инвалидности',
                                        'disabled_child' => 'Установление категории «ребёнок-инвалид»',
                                        'cause_disability' => 'Определение причины инвалидности',
                                        'onset_time' => 'Определение времени наступления инвалидности',
                                        'disability_term' => 'Определение срока инвалидности',
                                        'professional_loss_degree' => 'Степень утраты профессиональной трудоспособности',
                                        'outside_care' => 'Нуждаемость в постоянном постороннем уходе',
                                        'ipr' => 'Разработка / корректировка ИПРА',
                                        'rehabilitation_program' => 'Разработка программы реабилитации пострадавшего',
                                        'other' => 'Иная цель',
                                    ] as $key => $label)
                                        <label class="commission-check-card commission-check-card--plain">
                                            <input type="checkbox" name="purposes[]" value="{{ $key }}" @checked(in_array($key, $msePurposes, true))>
                                            <span><strong>{{ $label }}</strong></span>
                                        </label>
                                    @endforeach
                                </div>

                                <label class="commission-field commission-field--spaced">
                                    <span>Иная цель / уточнение</span>
                                    <input class="form-control" name="purpose_other" value="{{ $value('purpose_other') }}" placeholder="Заполняется при необходимости">
                                </label>
                            </section>

                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-activity"></i></span>
                                    <div>
                                        <h2>Клинико-функциональные данные</h2>
                                        <p>Диагноз берётся из карточки пациента и не выбирается повторно</p>
                                    </div>
                                </div>

                                <div class="commission-live-diagnosis">
                                    <span>Основной диагноз</span>
                                    <strong>{{ $diagnosisText ?: 'В карточке пациента не указан' }}</strong>
                                </div>

                                <div class="commission-grid commission-grid--2">
                                    <label class="commission-field commission-field--wide">
                                        <span>Анамнез заболевания</span>
                                        <textarea class="form-control" name="clinical_history" rows="5" placeholder="Начало, течение заболевания, обострения, госпитализации...">{{ $value('clinical_history') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Осложнения и сопутствующие заболевания</span>
                                        <textarea class="form-control" name="complications_concomitant" rows="4">{{ $value('complications_concomitant') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Объективный статус</span>
                                        <textarea class="form-control" name="objective_status" rows="5">{{ $value('objective_status') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Стойкие нарушения функций организма</span>
                                        <textarea class="form-control" name="functional_disorders" rows="5" placeholder="Характер, степень выраженности и влияние на основные категории жизнедеятельности">{{ $value('functional_disorders') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Результаты обследований и консультаций</span>
                                        <textarea class="form-control" name="examination_results" rows="5">{{ $value('examination_results') }}</textarea>
                                    </label>
                                </div>
                            </section>

                            <section class="commission-card">
                                <div class="commission-card__head">
                                    <span class="commission-card__icon"><i class="bi bi-arrow-repeat"></i></span>
                                    <div>
                                        <h2>Лечение, реабилитация и прогноз</h2>
                                        <p>Данные, необходимые для оценки реабилитационного потенциала</p>
                                    </div>
                                </div>

                                <div class="commission-grid commission-grid--2">
                                    <label class="commission-field commission-field--wide">
                                        <span>Проведённое лечение и медицинская реабилитация</span>
                                        <textarea class="form-control" name="treatment_rehabilitation" rows="5">{{ $value('treatment_rehabilitation') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Результаты реабилитационных мероприятий</span>
                                        <textarea class="form-control" name="rehabilitation_results" rows="4">{{ $value('rehabilitation_results') }}</textarea>
                                    </label>
                                    <label class="commission-field">
                                        <span>Клинический прогноз</span>
                                        <textarea class="form-control" name="clinical_prognosis" rows="4" placeholder="Благоприятный / относительно благоприятный / сомнительный / неблагоприятный...">{{ $value('clinical_prognosis') }}</textarea>
                                    </label>
                                    <label class="commission-field">
                                        <span>Реабилитационный потенциал</span>
                                        <textarea class="form-control" name="rehabilitation_potential" rows="4">{{ $value('rehabilitation_potential') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Профессия, условия труда и трудовые ограничения</span>
                                        <textarea class="form-control" name="employment_info" rows="4">{{ $value('employment_info') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Рекомендации по медицинской и социальной реабилитации</span>
                                        <textarea class="form-control" name="recommendations" rows="5">{{ $value('recommendations') }}</textarea>
                                    </label>
                                    <label class="commission-field commission-field--wide">
                                        <span>Дополнительные сведения</span>
                                        <textarea class="form-control" name="additional_notes" rows="3">{{ $value('additional_notes') }}</textarea>
                                    </label>
                                </div>

                                <div class="commission-note">
                                    <i class="bi bi-info-circle"></i>
                                    <div>
                                        <strong>Форма 088/у</strong>
                                        <span>Страница предназначена для подготовки данных направления на МСЭ. При необходимости точной бумажной формы её лучше сделать отдельным печатным шаблоном.</span>
                                    </div>
                                </div>
                            </section>
                        @endif

                        <div class="commission-actions">
                            <div class="commission-actions__hint">
                                <i class="bi bi-cloud-check"></i>
                                @if ($activeDocument)
                                    Открыт документ {{ $activeDocument->number }} · {{ $activeDocument->status === \App\Models\CommissionDocument::STATUS_PREPARED ? 'подготовлен' : 'черновик' }}
                                @else
                                    После первого сохранения документу присвоится номер
                                @endif
                            </div>

                            <div class="commission-actions__buttons">
                                <button type="button" class="btn commission-btn commission-btn--ghost" data-commission-print>
                                    <i class="bi bi-printer"></i>
                                    Печать
                                </button>
                                <button type="submit" name="status" value="draft" class="btn commission-btn commission-btn--secondary">
                                    <i class="bi bi-save"></i>
                                    Сохранить черновик
                                </button>
                                <button type="submit" name="status" value="prepared" class="btn commission-btn commission-btn--primary">
                                    <i class="bi bi-check2-circle"></i>
                                    Подготовлено
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </section>
        </div>
    </div>
</main>

@include('doctors.commissions.partials.scripts')
