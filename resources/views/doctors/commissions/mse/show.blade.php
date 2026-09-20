@extends('doctors.prescriptions')

@section('title', $document->document_number ?: 'Направление на МСЭ')
@section('prescription-title', $document->document_number ?: 'Направление на МСЭ')

@section('sub-main')
    @include('doctors.commissions.mse.partials.styles')

    <style>
        .mse-preparation-notice {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr) auto;
            gap: 13px 16px;
            align-items: start;
            margin: 18px 0 22px;
            padding: 16px 18px;
            border: 1px solid #efd69a;
            border-radius: 17px;
            background: #fffaf0;
            color: #584819;
            box-shadow: 0 8px 24px rgba(54, 72, 79, .05);
        }

        .mse-preparation-notice.is-ready {
            border-color: #b9ddd4;
            background: #f2fbf8;
            color: #285d50;
        }

        .mse-preparation-notice.is-danger {
            border-color: #efc5c5;
            background: #fff7f7;
            color: #7d3434;
        }

        .mse-preparation-notice__icon {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            border-radius: 13px;
            background: #fff0c8;
            color: #9a6a00;
            font-size: 21px;
        }

        .mse-preparation-notice.is-ready .mse-preparation-notice__icon {
            background: #dff3ed;
            color: #14715d;
        }

        .mse-preparation-notice.is-danger .mse-preparation-notice__icon {
            background: #fde8e8;
            color: #b13c3c;
        }

        .mse-preparation-notice__copy { min-width: 0; }
        .mse-preparation-notice__copy h2 {
            margin: 0;
            color: inherit;
            font-size: 16px;
            line-height: 1.25;
        }
        .mse-preparation-notice__copy > p {
            margin: 4px 0 0;
            color: inherit;
            opacity: .82;
            font-size: 12px;
            line-height: 1.45;
        }
        .mse-preparation-notice__list {
            display: grid;
            gap: 5px;
            margin: 11px 0 0;
            padding: 0;
            list-style: none;
        }
        .mse-preparation-notice__list li {
            position: relative;
            padding-left: 18px;
            font-size: 12px;
            line-height: 1.4;
        }
        .mse-preparation-notice__list li::before {
            content: '';
            position: absolute;
            top: .52em;
            left: 3px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            opacity: .62;
        }
        .mse-preparation-notice details { margin-top: 8px; }
        .mse-preparation-notice summary {
            width: fit-content;
            cursor: pointer;
            font-size: 11px;
            font-weight: 750;
        }
        .mse-preparation-notice__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }
        .mse-preparation-notice__actions .mse-button {
            min-height: 38px;
            height: 38px;
            padding: 0 13px;
            white-space: nowrap;
            font-size: 11px;
        }

        .mse-readiness-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border: 1px solid #efd59a;
            border-radius: 999px;
            background: #fff8e8;
            color: #8a6208;
            font-size: 10px;
            font-weight: 800;
        }

        .mse-readiness-chip.is-ready {
            border-color: #b9ddd4;
            background: #f0faf7;
            color: #176d59;
        }

        .mse-age-profile-card {
            border-color: #d6e6ea !important;
            background: linear-gradient(180deg, #fbfdfe 0%, #ffffff 100%);
        }

        .mse-age-profile-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
        }

        .mse-age-profile-head h2 {
            margin: 0 !important;
        }

        .mse-age-profile-badge {
            flex: 0 0 auto;
            padding: 6px 10px;
            border-radius: 999px;
            background: #e8f7f8;
            color: #087d89;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .mse-age-profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .mse-age-profile-item {
            padding: 10px 11px;
            border: 1px solid #e3edef;
            border-radius: 12px;
            background: #fff;
        }

        .mse-age-profile-item--wide {
            grid-column: 1 / -1;
        }

        .mse-age-profile-item strong,
        .mse-age-profile-item span {
            display: block;
        }

        .mse-age-profile-item strong {
            margin-bottom: 3px;
            color: #718790;
            font-size: 9px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .mse-age-profile-item span {
            color: #314d58;
            font-size: 11px;
            line-height: 1.42;
        }

        .mse-age-profile-special {
            margin-top: 10px;
            padding: 9px 11px;
            border-left: 3px solid #d2b464;
            border-radius: 0 10px 10px 0;
            background: #fffaf0;
            color: #66511d;
            font-size: 10px;
            line-height: 1.4;
        }

        @media (max-width: 900px) {
            .mse-preparation-notice {
                grid-template-columns: 42px minmax(0, 1fr);
            }
            .mse-preparation-notice__actions {
                grid-column: 1 / -1;
                justify-content: flex-start;
            }
            .mse-age-profile-grid {
                grid-template-columns: minmax(0, 1fr);
            }
            .mse-age-profile-item--wide {
                grid-column: auto;
            }
        }
    </style>

    @php
        $snapshot = (array) ($document->patient_snapshot ?: []);
        $details = (array) ($document->details ?: []);
        $display = static function (mixed $value, string $empty = 'Не указано'): string {
            if (is_bool($value)) {
                return $value ? 'Да' : 'Нет';
            }
            $text = trim((string) $value);
            return $text !== '' ? $text : $empty;
        };
        $date = static function (mixed $value, string $empty = 'Не указана'): string {
            if (!$value) {
                return $empty;
            }
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d.m.Y');
            } catch (\Throwable) {
                return (string) $value;
            }
        };
        $label = static fn (array $options, mixed $value, string $empty = 'Не указано'): string =>
            $value && isset($options[$value]) ? $options[$value] : $empty;
        $identityParts = collect([
            data_get($snapshot, 'identity.name'),
            filled(data_get($snapshot, 'identity.series')) ? 'серия ' . data_get($snapshot, 'identity.series') : null,
            filled(data_get($snapshot, 'identity.number')) ? '№ ' . data_get($snapshot, 'identity.number') : null,
        ])->filter()->implode(', ');
        $temporaryRows = collect(data_get($details, 'clinical.temporary_disability', []))->filter(fn ($row) => is_array($row))->values();
        $examinationRows = collect(data_get($details, 'clinical.examinations', []))->filter(fn ($row) => is_array($row))->values();
        $commissionMembers = collect(data_get($details, 'commission.members', []))->filter()->values();
        $purposeLabels = $document->purposeLabels();
        $conditionLabels = $document->conditionLabels();

        /*
         * Блок «Предыдущая МСЭ» показываем только тогда, когда в сохранённых
         * данных направления действительно есть хотя бы один заполненный
         * реквизит предыдущей экспертизы.
         */
        $previousMse = (array) data_get($details, 'previous_mse', []);
        $hasPreviousMse = collect($previousMse)
            ->contains(static fn (mixed $value): bool => filled($value));

        $hasPreviousIpraData = collect([
            data_get($details, 'clinical.ipra.number'),
            data_get($details, 'clinical.ipra.protocol_number'),
            data_get($details, 'clinical.ipra.protocol_date'),
            data_get($details, 'clinical.rehabilitation_results.function_restoration'),
            data_get($details, 'clinical.rehabilitation_results.function_compensation'),
            data_get($details, 'clinical.rehabilitation_results.notes'),
        ])->contains(static fn (mixed $value): bool => filled($value));

        $showPreviousIpra = (bool) data_get($snapshot, 'disability', false)
            || filled(data_get($details, 'previous_mse.disability_group'))
            || in_array('ipra', (array) ($document->purposes ?: []), true)
            || $hasPreviousIpraData;

        /*
         * Возраст берём из сохранённого снимка направления.
         * Так страница просмотра не зависит от последующих изменений карточки пациента.
         */
        $patientAge = data_get($snapshot, 'age');

        if ($patientAge === null && filled(data_get($snapshot, 'birth_date'))) {
            try {
                $patientAge = \Illuminate\Support\Carbon::parse(
                    data_get($snapshot, 'birth_date')
                )->age;
            } catch (\Throwable) {
                $patientAge = null;
            }
        }

        $isChildUnder14 = is_numeric($patientAge) && (int) $patientAge < 14;

        $patientAddress = trim((string) data_get($snapshot, 'address'));
        $locationAddress = trim((string) data_get($snapshot, 'location.address'));
        $locationName = trim((string) data_get($snapshot, 'location.name'));
        $locationOgrn = trim((string) data_get($snapshot, 'location.ogrn'));

        /*
         * Место нахождения выводим отдельно только если оно действительно
         * отличается от обычного места жительства/пребывания либо указана
         * отдельная организация.
         */
        $hasSpecialLocation = $locationName !== ''
            || $locationOgrn !== ''
            || (
                $locationAddress !== ''
                && $patientAddress !== ''
                && mb_strtolower($locationAddress) !== mb_strtolower($patientAddress)
            );

        $preparationIssues = collect($preparationIssues ?? [])->values();
        $visiblePreparationIssues = $preparationIssues->take(5);
        $hiddenPreparationIssues = $preparationIssues->slice(5);
        $hasDiagnosisIssue = $preparationIssues->contains(
            static fn (array $issue): bool => in_array($issue['key'] ?? null, ['diagnosis', 'diagnosis_code', 'patient_profile'], true)
        );
        $ageDocumentationProfile = (array) ($ageDocumentationProfile ?? []);
        $patientAgeMonths = data_get($snapshot, 'age_months');
        $patientAgeLabel = is_numeric($patientAge) && (int) $patientAge === 0 && is_numeric($patientAgeMonths)
            ? (int) $patientAgeMonths . ' полн. мес.'
            : (is_numeric($patientAge) ? (int) $patientAge . ' полн. лет' : 'Не определён');
        $patientAgeLabel = (string) data_get($ageDocumentationProfile, 'age_label', $patientAgeLabel);
    @endphp

    <main class="mse-page">
        <div class="mse-container">
            <header class="mse-hero">
                <div class="mse-hero__main">
                    <a class="mse-back" href="{{ route('doctors.commissions.mse.index') }}" title="Назад в журнал">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="mse-hero__icon"><i class="bi bi-universal-access"></i></div>
                    <div>
                        <div class="mse-eyebrow">МСЭ · форма 088/у</div>
                        <h1>{{ $document->document_number ?: 'Направление без номера' }}</h1>
                        <p>{{ $document->patientFullName() }} · {{ $document->diagnosisLabel() }}</p>
                        <div class="mse-form-meta">
                            <span class="mse-badge mse-badge--{{ $document->status === 'prepared' ? 'prepared' : 'draft' }}">
                                {{ $document->statusLabel() }}
                            </span>
                            @if($preparationIssues->isNotEmpty())
                                <span class="mse-readiness-chip">
                                    <i class="bi bi-tools"></i> Требует доработки · {{ $preparationIssues->count() }}
                                </span>
                            @elseif($document->status === 'draft')
                                <span class="mse-readiness-chip is-ready">
                                    <i class="bi bi-check-circle"></i> Готово к оформлению
                                </span>
                            @endif
                            <span class="mse-form-chip"><i class="bi bi-calendar3"></i> {{ $document->document_date?->format('d.m.Y') ?: 'Дата не указана' }}</span>
                            <span class="mse-form-chip"><i class="bi bi-arrow-repeat"></i> {{ $document->referralKindLabel() }}</span>
                        </div>
                    </div>
                </div>

                <div class="mse-hero__actions">
                    <a class="mse-button mse-button--primary" href="{{ route('doctors.commissions.mse.edit', $document) }}">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <a class="mse-button mse-button--secondary" target="_blank"
                       href="{{ route('doctors.commissions.mse.print', $document) }}">
                        <i class="bi bi-printer"></i> Печать
                    </a>
                    @if($document->status === 'draft')
                        <form method="POST" action="{{ route('doctors.commissions.mse.destroy', $document) }}"
                              onsubmit="return confirm('Удалить этот черновик?')">
                            @csrf
                            @method('DELETE')
                            <button class="mse-button mse-button--danger" type="submit">
                                <i class="bi bi-trash3"></i> Удалить
                            </button>
                        </form>
                    @endif
                </div>
            </header>

            @if(session('message'))
                <div class="mse-alert {{ session('status') === 'error' ? 'mse-alert--danger' : 'mse-alert--success' }}">
                    <i class="bi {{ session('status') === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill' }}"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if($preparationIssues->isNotEmpty())
                <section class="mse-preparation-notice {{ $document->status === 'prepared' ? 'is-danger' : '' }}" aria-live="polite">
                    <span class="mse-preparation-notice__icon">
                        <i class="bi {{ $document->status === 'prepared' ? 'bi-exclamation-octagon-fill' : 'bi-tools' }}"></i>
                    </span>
                    <div class="mse-preparation-notice__copy">
                        <h2>{{ $document->status === 'prepared' ? 'В оформленном направлении есть недочёты' : 'Направление требует доработки' }}</h2>
                        <p>
                            @if($document->status === 'prepared')
                                Обнаружены сведения, которые стоит проверить до фактической отправки документа в бюро МСЭ.
                            @else
                                Черновик сохранён, но пока его нельзя перевести в статус «Оформлено» и направить в МСЭ.
                            @endif
                            Не выполнено: {{ $preparationIssues->count() }}.
                        </p>
                        <ul class="mse-preparation-notice__list">
                            @foreach($visiblePreparationIssues as $issue)
                                <li>{{ $issue['message'] }}</li>
                            @endforeach
                        </ul>
                        @if($hiddenPreparationIssues->isNotEmpty())
                            <details>
                                <summary>Показать ещё {{ $hiddenPreparationIssues->count() }}</summary>
                                <ul class="mse-preparation-notice__list">
                                    @foreach($hiddenPreparationIssues as $issue)
                                        <li>{{ $issue['message'] }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </div>
                    <div class="mse-preparation-notice__actions">
                        @if($hasDiagnosisIssue && $document->patient_id)
                            <a class="mse-button mse-button--secondary"
                               href="{{ route('doctors.patients.medical_card', ['patient' => $document->patient_id]) }}">
                                <i class="bi bi-person-vcard"></i> Медкарта пациента
                            </a>
                        @endif
                        <a class="mse-button mse-button--primary" href="{{ route('doctors.commissions.mse.edit', $document) }}">
                            <i class="bi bi-pencil-square"></i> Доработать
                        </a>
                    </div>
                </section>
            @elseif($document->status === 'draft')
                <section class="mse-preparation-notice is-ready">
                    <span class="mse-preparation-notice__icon"><i class="bi bi-check-circle-fill"></i></span>
                    <div class="mse-preparation-notice__copy">
                        <h2>Критических препятствий не найдено</h2>
                        <p>По сохранённым данным направление готово к переводу в статус «Оформлено». Окончательное решение о направлении остаётся за врачебной комиссией.</p>
                    </div>
                    <div class="mse-preparation-notice__actions">
                        <a class="mse-button mse-button--primary" href="{{ route('doctors.commissions.mse.edit', $document) }}">
                            <i class="bi bi-check2-circle"></i> Оформить
                        </a>
                    </div>
                </section>
            @endif

            <div class="mse-details-grid">
                <section class="mse-detail-card">
                    <h2><i class="bi bi-file-earmark-medical"></i> Направление</h2>
                    <dl class="mse-detail-list">
                        <div><dt>Номер</dt><dd>{{ $display($document->document_number) }}</dd></div>
                        <div><dt>Дата</dt><dd>{{ $document->document_date?->format('d.m.Y') ?: 'Не указана' }}</dd></div>
                        <div><dt>Статус</dt><dd>{{ $document->statusLabel() }}</dd></div>
                        <div><dt>Вид направления</dt><dd>{{ $document->referralKindLabel() }}</dd></div>
                        <div><dt>Форма проведения МСЭ</dt><dd>{{ $document->examinationFormatLabel() }}</dd></div>
                        <div><dt>Способ уведомления</dt><dd>{{ $document->notificationMethodLabel() }}</dd></div>
                        <div><dt>Дата согласия</dt><dd>{{ $document->consent_date?->format('d.m.Y') ?: 'Не указана' }}</dd></div>
                        <div><dt>Оформлено</dt><dd>{{ $document->prepared_at?->format('d.m.Y H:i') ?: 'Нет' }}</dd></div>
                    </dl>
                </section>

                <section class="mse-detail-card">
                    <h2><i class="bi bi-clipboard2-check"></i> Решение врачебной комиссии</h2>
                    <dl class="mse-detail-list">
                        <div><dt>Номер протокола ВК</dt><dd>{{ $display($document->protocol_number) }}</dd></div>
                        <div><dt>Дата протокола ВК</dt><dd>{{ $document->protocol_date?->format('d.m.Y') ?: 'Не указана' }}</dd></div>
                        <div><dt>Председатель ВК</dt><dd>{{ $display(data_get($details, 'commission.chairman')) }}</dd></div>
                        <div><dt>Создал документ</dt><dd>{{ $display(collect([$document->creator?->surname, $document->creator?->name, $document->creator?->patronym])->filter()->implode(' ')) }}</dd></div>
                    </dl>
                    @if($commissionMembers->isNotEmpty())
                        <div class="mse-section-caption"><i class="bi bi-people"></i> Члены комиссии</div>
                        <div class="mse-chip-list">
                            @foreach($commissionMembers as $member)
                                <span class="mse-form-chip">{{ $member }}</span>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="mse-detail-card mse-detail-card--wide">
                    <h2><i class="bi bi-bullseye"></i> Цели и особые условия проведения МСЭ</h2>
                    <div class="mse-section-caption">Цели направления</div>
                    @if(count($purposeLabels))
                        <div class="mse-chip-list">
                            @foreach($purposeLabels as $purpose)
                                <span class="mse-form-chip mse-purpose-chip">{{ $purpose }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="mse-text-block is-empty">Цели не выбраны.</div>
                    @endif

                    <div class="mse-section-caption">Особые условия</div>
                    @if(count($conditionLabels))
                        <div class="mse-chip-list">
                            @foreach($conditionLabels as $condition)
                                <span class="mse-form-chip mse-condition-chip">{{ $condition }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="mse-text-block is-empty">Особые условия не отмечены.</div>
                    @endif
                </section>

                <section class="mse-detail-card">
                    <h2><i class="bi bi-building"></i> Медицинская организация</h2>
                    <dl class="mse-detail-list">
                        <div class="mse-detail-card--wide"><dt>Наименование</dt><dd>{{ $display(data_get($details, 'organization.name')) }}</dd></div>
                        <div><dt>ОГРН</dt><dd>{{ $display(data_get($details, 'organization.ogrn')) }}</dd></div>
                        <div><dt>Бюро МСЭ</dt><dd>{{ $display(data_get($details, 'organization.bureau_name')) }}</dd></div>
                        <div class="mse-detail-card--wide"><dt>Адрес</dt><dd>{{ $display(data_get($details, 'organization.address')) }}</dd></div>
                    </dl>
                </section>

                <section class="mse-detail-card">
                    <h2><i class="bi bi-person-vcard"></i> Пациент</h2>
                    <dl class="mse-detail-list">
                        <div class="mse-detail-card--wide"><dt>ФИО</dt><dd>{{ $document->patientFullName() }}</dd></div>
                        <div><dt>Дата рождения</dt><dd>{{ $document->patientBirthDateLabel() }}</dd></div>
                        <div><dt>Возраст для формы 088/у</dt><dd>{{ $patientAgeLabel }}</dd></div>
                        <div><dt>Пол</dt><dd>{{ $document->patientSexLabel() }}</dd></div>

                        @if(filled(data_get($snapshot, 'citizenship.type')))
                            <div><dt>Гражданство</dt><dd>{{ $label($citizenshipOptions, data_get($snapshot, 'citizenship.type')) }}</dd></div>
                        @endif

                        @if(filled(data_get($snapshot, 'citizenship.country')))
                            <div><dt>Государство</dt><dd>{{ $display(data_get($snapshot, 'citizenship.country')) }}</dd></div>
                        @endif

                        @unless($isChildUnder14)
                            @if(filled(data_get($snapshot, 'military_duty')))
                                <div class="mse-detail-card--wide"><dt>Воинская обязанность</dt><dd>{{ $label($militaryDutyOptions, data_get($snapshot, 'military_duty')) }}</dd></div>
                            @endif
                        @endunless

                        @if(filled(data_get($snapshot, 'address')))
                            <div class="mse-detail-card--wide"><dt>Адрес</dt><dd>{{ $display(data_get($snapshot, 'address')) }}</dd></div>
                        @endif

                        @if(data_get($snapshot, 'homeless'))
                            <div><dt>Без определённого места жительства</dt><dd>Да</dd></div>
                        @endif

                        @unless($isChildUnder14)
                            @if(filled(data_get($snapshot, 'phone')))
                                <div><dt>Телефон</dt><dd>{{ $display(data_get($snapshot, 'phone')) }}</dd></div>
                            @endif
                            @if(filled(data_get($snapshot, 'email')))
                                <div><dt>Электронная почта</dt><dd>{{ $display(data_get($snapshot, 'email')) }}</dd></div>
                            @endif
                        @endunless

                        @if(filled(data_get($snapshot, 'snils')))
                            <div><dt>СНИЛС</dt><dd>{{ $display(data_get($snapshot, 'snils')) }}</dd></div>
                        @endif

                        @if(filled(data_get($snapshot, 'oms')))
                            <div><dt>Полис ОМС</dt><dd>{{ $display(data_get($snapshot, 'oms')) }}</dd></div>
                        @endif
                    </dl>
                </section>

                <section class="mse-detail-card mse-detail-card--wide mse-age-profile-card">
                    <div class="mse-age-profile-head">
                        <h2><i class="bi bi-person-bounding-box"></i> Возрастной профиль документов МСЭ</h2>
                        <span class="mse-age-profile-badge">{{ data_get($ageDocumentationProfile, 'age_label', $patientAgeLabel) }}</span>
                    </div>
                    <div class="mse-age-profile-grid">
                        <div class="mse-age-profile-item">
                            <strong>Возрастной сценарий</strong>
                            <span>{{ data_get($ageDocumentationProfile, 'title', 'Возрастной профиль не определён') }}</span>
                        </div>
                        <div class="mse-age-profile-item">
                            <strong>Раздел обследований</strong>
                            <span>{{ data_get($ageDocumentationProfile, 'mse_section', 'Не определён') }}</span>
                        </div>
                        <div class="mse-age-profile-item">
                            <strong>Документ личности</strong>
                            <span>{{ data_get($ageDocumentationProfile, 'identity', 'Не определён') }}</span>
                        </div>
                        <div class="mse-age-profile-item">
                            <strong>Согласие / представитель</strong>
                            <span>{{ data_get($ageDocumentationProfile, 'consent', 'Не определено') }}</span>
                        </div>
                        <div class="mse-age-profile-item mse-age-profile-item--wide">
                            <strong>Профиль специалиста</strong>
                            <span>{{ data_get($ageDocumentationProfile, 'psychiatrist', 'Не определён') }}</span>
                        </div>
                    </div>
                    @if(collect(data_get($ageDocumentationProfile, 'special_fields', []))->isNotEmpty())
                        <div class="mse-age-profile-special">
                            <strong>Особенности этого возраста:</strong>
                            {{ collect(data_get($ageDocumentationProfile, 'special_fields', []))->implode(' ') }}
                        </div>
                    @endif
                </section>

                @if($isChildUnder14)
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-file-earmark-person"></i> Документ ребёнка</h2>
                        <dl class="mse-detail-list">
                            @if(filled($identityParts))
                                <div class="mse-detail-card--wide">
                                    <dt>Документ</dt>
                                    <dd>{{ $display($identityParts) }}</dd>
                                </div>
                            @endif

                            @if(filled(data_get($snapshot, 'identity.issued_by')))
                                <div class="mse-detail-card--wide">
                                    <dt>Кем выдан</dt>
                                    <dd>{{ $display(data_get($snapshot, 'identity.issued_by')) }}</dd>
                                </div>
                            @endif

                            @if(filled(data_get($snapshot, 'identity.issue_date')))
                                <div>
                                    <dt>Дата выдачи</dt>
                                    <dd>{{ $date(data_get($snapshot, 'identity.issue_date')) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if($hasSpecialLocation)
                        <section class="mse-detail-card mse-detail-card--wide">
                            <h2><i class="bi bi-geo-alt"></i> Место нахождения ребёнка</h2>
                            <dl class="mse-detail-list">
                                @if(filled(data_get($snapshot, 'location.type')))
                                    <div>
                                        <dt>Тип места</dt>
                                        <dd>{{ $label($locationTypeOptions, data_get($snapshot, 'location.type')) }}</dd>
                                    </div>
                                @endif

                                @if(filled(data_get($snapshot, 'location.name')))
                                    <div>
                                        <dt>Организация / место</dt>
                                        <dd>{{ $display(data_get($snapshot, 'location.name')) }}</dd>
                                    </div>
                                @endif

                                @if(filled(data_get($snapshot, 'location.address')))
                                    <div class="mse-detail-card--wide">
                                        <dt>Адрес</dt>
                                        <dd>{{ $display(data_get($snapshot, 'location.address')) }}</dd>
                                    </div>
                                @endif

                                @if(filled(data_get($snapshot, 'location.ogrn')))
                                    <div>
                                        <dt>ОГРН организации</dt>
                                        <dd>{{ $display(data_get($snapshot, 'location.ogrn')) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </section>
                    @endif
                @else
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-geo-alt"></i> Место нахождения и документы пациента</h2>
                        <dl class="mse-detail-list">
                            @if(filled(data_get($snapshot, 'location.type')))
                                <div><dt>Тип места нахождения</dt><dd>{{ $label($locationTypeOptions, data_get($snapshot, 'location.type')) }}</dd></div>
                            @endif
                            @if(filled(data_get($snapshot, 'location.name')))
                                <div><dt>Организация / место</dt><dd>{{ $display(data_get($snapshot, 'location.name')) }}</dd></div>
                            @endif
                            @if(filled(data_get($snapshot, 'location.address')))
                                <div class="mse-detail-card--wide"><dt>Адрес места нахождения</dt><dd>{{ $display(data_get($snapshot, 'location.address')) }}</dd></div>
                            @endif
                            @if(filled(data_get($snapshot, 'location.ogrn')))
                                <div><dt>ОГРН организации</dt><dd>{{ $display(data_get($snapshot, 'location.ogrn')) }}</dd></div>
                            @endif
                            @if(filled($identityParts))
                                <div class="mse-detail-card--wide"><dt>Документ, удостоверяющий личность</dt><dd>{{ $display($identityParts) }}</dd></div>
                            @endif
                            @if(filled(data_get($snapshot, 'identity.issued_by')))
                                <div class="mse-detail-card--wide"><dt>Кем выдан</dt><dd>{{ $display(data_get($snapshot, 'identity.issued_by')) }}</dd></div>
                            @endif
                            @if(filled(data_get($snapshot, 'identity.issue_date')))
                                <div><dt>Дата выдачи</dt><dd>{{ $date(data_get($snapshot, 'identity.issue_date')) }}</dd></div>
                            @endif
                        </dl>
                    </section>
                @endif

                @if(filled(data_get($snapshot, 'representative.full_name')) || filled(data_get($snapshot, 'guardian_organization.name')))
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-people"></i> {{ $isChildUnder14 ? 'Законный представитель ребёнка' : 'Представитель пациента' }}</h2>
                        <dl class="mse-detail-list">
                            @if(filled(data_get($snapshot, 'representative.full_name')))
                                <div><dt>ФИО представителя</dt><dd>{{ $display(data_get($snapshot, 'representative.full_name')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'representative.birth_date')))
                                <div><dt>Дата рождения</dt><dd>{{ $date(data_get($snapshot, 'representative.birth_date')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'representative.authority_document')))
                                <div class="mse-detail-card--wide"><dt>Основание полномочий</dt><dd>{{ $display(data_get($snapshot, 'representative.authority_document')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'representative.identity')))
                                <div class="mse-detail-card--wide"><dt>Документ, удостоверяющий личность</dt><dd>{{ $display(data_get($snapshot, 'representative.identity')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'representative.phone')))
                                <div><dt>Телефон</dt><dd>{{ $display(data_get($snapshot, 'representative.phone')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'representative.email')))
                                <div><dt>Электронная почта</dt><dd>{{ $display(data_get($snapshot, 'representative.email')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'representative.snils')))
                                <div><dt>СНИЛС</dt><dd>{{ $display(data_get($snapshot, 'representative.snils')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'guardian_organization.name')))
                                <div><dt>Организация-представитель</dt><dd>{{ $display(data_get($snapshot, 'guardian_organization.name')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'guardian_organization.address')))
                                <div class="mse-detail-card--wide"><dt>Адрес организации</dt><dd>{{ $display(data_get($snapshot, 'guardian_organization.address')) }}</dd></div>
                            @endif

                            @if(filled(data_get($snapshot, 'guardian_organization.ogrn')))
                                <div><dt>ОГРН организации</dt><dd>{{ $display(data_get($snapshot, 'guardian_organization.ogrn')) }}</dd></div>
                            @endif
                        </dl>
                    </section>
                @endif

                @if($hasPreviousMse)
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-clock-history"></i> Предыдущая МСЭ</h2>
                        <dl class="mse-detail-list">
                            <div><dt>Группа / категория</dt><dd>{{ $label($disabilityGroupOptions, data_get($details, 'previous_mse.disability_group')) }}</dd></div>
                            <div><dt>Установлена до</dt><dd>{{ $date(data_get($details, 'previous_mse.disability_until')) }}</dd></div>
                            <div><dt>Период инвалидности</dt><dd>{{ $label($disabilityPeriodOptions, data_get($details, 'previous_mse.disability_period')) }}</dd></div>
                            <div><dt>Причина инвалидности</dt><dd>{{ $label($disabilityCauseOptions, data_get($details, 'previous_mse.disability_cause')) }}</dd></div>
                            <div class="mse-detail-card--wide"><dt>Уточнение причины</dt><dd>{{ $display(data_get($details, 'previous_mse.disability_cause_other')) }}</dd></div>
                            <div><dt>Утрата профессиональной трудоспособности</dt><dd>{{ filled(data_get($details, 'previous_mse.professional_loss_percent')) ? data_get($details, 'previous_mse.professional_loss_percent') . '%' : 'Не указано' }}</dd></div>
                            <div><dt>Срок</dt><dd>{{ $display(data_get($details, 'previous_mse.professional_loss_term')) }}</dd></div>
                            <div><dt>Установлена до</dt><dd>{{ $date(data_get($details, 'previous_mse.professional_loss_until')) }}</dd></div>
                            <div class="mse-detail-card--wide"><dt>Иные результаты</dt><dd>{{ $display(data_get($details, 'previous_mse.professional_loss_other')) }}</dd></div>
                        </dl>
                    </section>
                @endif

                <section class="mse-detail-card">
                    <h2><i class="bi bi-mortarboard"></i> Образование</h2>
                    <dl class="mse-detail-list">
                        <div class="mse-detail-card--wide"><dt>Образовательная организация</dt><dd>{{ $display(data_get($details, 'education.organization')) }}</dd></div>
                        <div><dt>Уровень образования</dt><dd>{{ $display(data_get($details, 'education.level')) }}</dd></div>
                        @unless($isChildUnder14)
                            <div><dt>Профессия / специальность</dt><dd>{{ $display(data_get($details, 'education.profession')) }}</dd></div>
                        @endunless
                    </dl>
                </section>

                @unless($isChildUnder14)
                    <section class="mse-detail-card">
                        <h2><i class="bi bi-briefcase"></i> Трудовая деятельность</h2>
                        <dl class="mse-detail-list">
                            <div><dt>Профессия</dt><dd>{{ $display(data_get($details, 'employment.profession')) }}</dd></div>
                            <div><dt>Квалификация</dt><dd>{{ $display(data_get($details, 'employment.qualification')) }}</dd></div>
                            <div><dt>Стаж</dt><dd>{{ $display(data_get($details, 'employment.experience')) }}</dd></div>
                            <div><dt>Работодатель</dt><dd>{{ $display(data_get($details, 'employment.organization')) }}</dd></div>
                            <div class="mse-detail-card--wide"><dt>Выполняемая работа</dt><dd>{{ $display(data_get($details, 'employment.current_work')) }}</dd></div>
                            <div class="mse-detail-card--wide"><dt>Условия труда</dt><dd>{{ $display(data_get($details, 'employment.conditions')) }}</dd></div>
                            <div class="mse-detail-card--wide"><dt>Адрес работодателя</dt><dd>{{ $display(data_get($details, 'employment.address')) }}</dd></div>
                        </dl>
                    </section>
                @endunless

                <section class="mse-detail-card mse-detail-card--wide">
                    <h2><i class="bi bi-heart-pulse"></i> Анамнез и клиническое состояние</h2>
                    <dl class="mse-detail-list">
                        <div><dt>Наблюдается с года</dt><dd>{{ $display(data_get($details, 'clinical.observed_since_year')) }}</dd></div>
                        <div><dt>Электронный листок нетрудоспособности</dt><dd>{{ data_get($details, 'clinical.has_eln') ? 'Да, № ' . $display(data_get($details, 'clinical.eln_number'), 'не указан') : 'Нет' }}</dd></div>
                    </dl>
                    <div class="mse-section-caption">Анамнез заболевания</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.disease_history')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.disease_history')) }}</div>
                    <div class="mse-section-caption">Анамнез жизни</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.life_history')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.life_history')) }}</div>
                    <div class="mse-section-caption">Жалобы</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.complaints')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.complaints')) }}</div>
                    <div class="mse-section-caption">Объективное состояние здоровья</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.health_status')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.health_status')) }}</div>
                </section>

                @if($temporaryRows->isNotEmpty())
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-calendar2-week"></i> Временная нетрудоспособность</h2>
                        <div style="overflow-x:auto">
                            <table class="mse-detail-table">
                                <thead><tr><th>Начало</th><th>Окончание</th><th>Длительность</th><th>Диагноз</th></tr></thead>
                                <tbody>
                                @foreach($temporaryRows as $row)
                                    <tr>
                                        <td>{{ $date(data_get($row, 'start_date'), '—') }}</td>
                                        <td>{{ $date(data_get($row, 'end_date'), '—') }}</td>
                                        <td>{{ $display(data_get($row, 'duration'), '—') }}</td>
                                        <td>{{ $display(data_get($row, 'diagnosis'), '—') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <section class="mse-detail-card">
                    <h2><i class="bi bi-rulers"></i> Антропометрия</h2>
                    <dl class="mse-detail-list">
                        <div><dt>Рост</dt><dd>{{ filled(data_get($details, 'clinical.anthropometry.height_cm')) ? data_get($details, 'clinical.anthropometry.height_cm') . ' см' : 'Не указан' }}</dd></div>
                        <div><dt>Масса тела</dt><dd>{{ filled(data_get($details, 'clinical.anthropometry.weight_kg')) ? data_get($details, 'clinical.anthropometry.weight_kg') . ' кг' : 'Не указана' }}</dd></div>
                        <div><dt>ИМТ</dt><dd>{{ $display(data_get($details, 'clinical.anthropometry.bmi')) }}</dd></div>
                        <div><dt>Телосложение</dt><dd>{{ $display(data_get($details, 'clinical.anthropometry.body_build')) }}</dd></div>
                        <div><dt>Окружность талии</dt><dd>{{ filled(data_get($details, 'clinical.anthropometry.waist_cm')) ? data_get($details, 'clinical.anthropometry.waist_cm') . ' см' : 'Не указана' }}</dd></div>
                        <div><dt>Окружность бёдер</dt><dd>{{ filled(data_get($details, 'clinical.anthropometry.hip_cm')) ? data_get($details, 'clinical.anthropometry.hip_cm') . ' см' : 'Не указана' }}</dd></div>
                        <div><dt>Масса при рождении</dt><dd>{{ filled(data_get($details, 'clinical.anthropometry.birth_weight_g')) ? data_get($details, 'clinical.anthropometry.birth_weight_g') . ' г' : 'Не указана' }}</dd></div>
                        <div><dt>Суточные отправления</dt><dd>{{ filled(data_get($details, 'clinical.anthropometry.daily_excretions_ml')) ? data_get($details, 'clinical.anthropometry.daily_excretions_ml') . ' мл' : 'Не указаны' }}</dd></div>
                        <div class="mse-detail-card--wide"><dt>Физическое развитие</dt><dd>{{ $display(data_get($details, 'clinical.anthropometry.physical_development')) }}</dd></div>
                    </dl>
                </section>

                <section class="mse-detail-card">
                    <h2><i class="bi bi-prescription2"></i> Диагноз</h2>
                    <div class="mse-live-diagnosis">
                        <i class="bi bi-activity"></i>
                        <div><span>Основной диагноз</span><strong>{{ $document->diagnosisLabel() }}</strong></div>
                    </div>
                    <div class="mse-section-caption">Осложнения</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.complications')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.complications')) }}</div>
                    <div class="mse-section-caption">Сопутствующие заболевания</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.concomitant_diseases')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.concomitant_diseases')) }}</div>
                    <div class="mse-section-caption">Коды МКБ</div>
                    <div class="mse-text-block {{ filled(data_get($details, 'clinical.concomitant_codes')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.concomitant_codes')) }}</div>
                </section>

                @if($examinationRows->isNotEmpty())
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-journal-medical"></i> Исследования и консультации</h2>
                        <div style="overflow-x:auto">
                            <table class="mse-detail-table">
                                <thead><tr><th>Дата</th><th>Код</th><th>Наименование</th><th>Результат</th></tr></thead>
                                <tbody>
                                @foreach($examinationRows as $row)
                                    <tr>
                                        <td>{{ $date(data_get($row, 'date'), '—') }}</td>
                                        <td>{{ $display(data_get($row, 'code'), '—') }}</td>
                                        <td>{{ $display(data_get($row, 'name'), '—') }}</td>
                                        <td>{{ $display(data_get($row, 'result'), '—') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if($showPreviousIpra)
                    <section class="mse-detail-card mse-detail-card--wide">
                        <h2><i class="bi bi-arrow-repeat"></i> Результаты предыдущей ИПРА</h2>
                        <dl class="mse-detail-list">
                            <div><dt>Номер ИПРА</dt><dd>{{ $display(data_get($details, 'clinical.ipra.number')) }}</dd></div>
                            <div><dt>Протокол разработки ИПРА</dt><dd>{{ $display(data_get($details, 'clinical.ipra.protocol_number')) }} от {{ $date(data_get($details, 'clinical.ipra.protocol_date')) }}</dd></div>
                            <div><dt>Восстановление функций</dt><dd>{{ $label($rehabilitationEffectOptions, data_get($details, 'clinical.rehabilitation_results.function_restoration')) }}</dd></div>
                            <div><dt>Компенсация функций</dt><dd>{{ $label($rehabilitationEffectOptions, data_get($details, 'clinical.rehabilitation_results.function_compensation')) }}</dd></div>
                        </dl>
                        <div class="mse-section-caption">Выполнение и результат мероприятий предыдущей ИПРА</div>
                        <div class="mse-text-block {{ filled(data_get($details, 'clinical.rehabilitation_results.notes')) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'clinical.rehabilitation_results.notes')) }}</div>
                    </section>
                @endif

                <section class="mse-detail-card mse-detail-card--wide">
                    <h2><i class="bi bi-heart-pulse"></i> Терапия, прогноз и психосоциальная реабилитация</h2>
                    <dl class="mse-detail-list">
                        <div><dt>Клинический прогноз</dt><dd>{{ $label($prognosisOptions, data_get($details, 'rehabilitation.clinical_prognosis')) }}</dd></div>
                        <div><dt>Реабилитационный потенциал</dt><dd>{{ $label($rehabilitationPotentialOptions, data_get($details, 'rehabilitation.potential')) }}</dd></div>
                        <div><dt>Реабилитационный прогноз</dt><dd>{{ $label($prognosisOptions, data_get($details, 'rehabilitation.prognosis')) }}</dd></div>
                    </dl>
                    @foreach([
                        'medical_rehabilitation' => 'Психосоциальная и медицинская реабилитация',
                        'medications_devices' => 'Психофармакотерапия и поддерживающее лечение',
                        'special_medical_care' => 'Специализированная психиатрическая помощь',
                        'additional_notes' => 'Дополнительные сведения',
                    ] as $key => $heading)
                        <div class="mse-section-caption">{{ $heading }}</div>
                        <div class="mse-text-block {{ filled(data_get($details, 'rehabilitation.' . $key)) ? '' : 'is-empty' }}">{{ $display(data_get($details, 'rehabilitation.' . $key)) }}</div>
                    @endforeach
                </section>
            </div>
        </div>
    </main>
@endsection
