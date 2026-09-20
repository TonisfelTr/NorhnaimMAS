@extends('doctors.prescriptions')

@section('title', $document->document_number ?: 'Водительская комиссия')
@section('prescription-title', $document->document_number ?: 'Водительская комиссия')

@section('sub-main')
    @include('doctors.commissions.driver.partials.styles')

    @php
        $snapshot = (array) ($document->patient_snapshot ?: []);
        $display = static function (mixed $value, string $empty = 'Не указано'): string {
            $text = trim((string) $value);
            return $text !== '' ? $text : $empty;
        };
        $creatorName = collect([
            $document->creator?->surname,
            $document->creator?->name,
            $document->creator?->patronym,
        ])->filter()->implode(' ');
        $categories = $document->vehicleCategoryLabels();
        $riskLabels = $document->riskFlagLabels();
        $driverOutputBlocked = $driverOutputBlocked ?? $document->isDrivingDocumentOutputBlocked();
        $driverHardBlocked = $driverHardBlocked ?? $document->hasConfirmedDrivingContraindication();
        $driverBlockReason = $driverBlockReason ?? $document->drivingDocumentBlockReason();

        $blocking = [];
        if ($document->status === \App\Models\DriverPsychiatricExamination::STATUS_DRAFT) {
            if (!$document->patient_id) $blocking[] = 'Не выбран пациент.';
            if (!$categories) $blocking[] = 'Не выбраны категории транспортных средств.';
            if (blank($document->mental_status)) $blocking[] = 'Не заполнен психический статус.';
            if (!$document->medical_record_reviewed) $blocking[] = 'Не подтверждена проверка медицинской карты.';
            if (blank($document->result)) $blocking[] = 'Не выбран результат обследования.';
            if (blank($document->conclusion_text)) $blocking[] = 'Не заполнено итоговое заключение.';
            if ($riskLabels && $document->result === \App\Models\DriverPsychiatricExamination::RESULT_NO_CONTRAINDICATIONS) {
                $blocking[] = 'Отмечены настораживающие признаки, но выбран обычный результат «противопоказания не выявлены».';
            }
        }
    @endphp

    <main class="driver-page">
        <div class="driver-container">
            <header class="driver-hero">
                <div class="driver-hero__main">
                    <a class="driver-back" href="{{ route('doctors.commissions.driver.index') }}"><i class="bi bi-arrow-left"></i></a>
                    <div class="driver-hero__icon"><i class="bi bi-car-front"></i></div>
                    <div class="driver-hero__copy">
                        <div class="driver-eyebrow">Водительская комиссия · психиатр</div>
                        <h1>{{ $document->document_number ?: 'Документ без номера' }}</h1>
                        <p>{{ $document->patientFullName() }} · {{ $document->diagnosisLabel() }}</p>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:7px">
                            <span class="driver-badge driver-badge--{{ $document->status === 'prepared' ? 'prepared' : 'draft' }}">{{ $document->statusLabel() }}</span>
                            @if($document->result === \App\Models\DriverPsychiatricExamination::RESULT_REFER_COMMISSION)
                                <span class="driver-badge driver-badge--referred">Направление на ВК</span>
                            @elseif($document->result === \App\Models\DriverPsychiatricExamination::RESULT_COMMISSION_CONTRAINDICATIONS)
                                <span class="driver-badge driver-badge--blocked">Противопоказания подтверждены</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="driver-hero__actions">
                    @if($driverHardBlocked)
                        <span class="driver-button driver-button--primary is-disabled" aria-disabled="true"><i class="bi bi-lock-fill"></i> Редактирование закрыто</span>
                    @else
                        <a class="driver-button driver-button--primary" href="{{ route('doctors.commissions.driver.edit', $document) }}"><i class="bi bi-pencil"></i> Редактировать</a>
                    @endif
                    @if($driverOutputBlocked)
                        <span class="driver-button driver-button--secondary is-disabled" aria-disabled="true"><i class="bi bi-printer-fill"></i> Печать заблокирована</span>
                    @else
                        <a class="driver-button driver-button--secondary" target="_blank" href="{{ route('doctors.commissions.driver.print', $document) }}"><i class="bi bi-printer"></i> Печать</a>
                    @endif
                    @if($document->status === 'draft')
                        <form method="POST" action="{{ route('doctors.commissions.driver.destroy', $document) }}" onsubmit="return confirm('Удалить этот черновик?')">
                            @csrf
                            @method('DELETE')
                            <button class="driver-button driver-button--danger" type="submit"><i class="bi bi-trash3"></i> Удалить</button>
                        </form>
                    @endif
                </div>
            </header>

            @if(session('message'))
                <div class="driver-alert {{ session('status') === 'error' ? 'driver-alert--danger' : 'driver-alert--success' }}">
                    <i class="bi {{ session('status') === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill' }}"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if($driverOutputBlocked)
                <div class="driver-alert {{ $driverHardBlocked ? 'driver-alert--danger' : 'driver-alert--warning' }}">
                    <i class="bi {{ $driverHardBlocked ? 'bi-slash-circle-fill' : 'bi-shield-exclamation' }}"></i>
                    <div>
                        <strong>{{ $driverHardBlocked ? 'Водительский документ заблокирован' : 'Печать временно заблокирована' }}</strong>
                        <div style="margin-top:3px">{{ $driverBlockReason }}</div>
                    </div>
                </div>
            @endif

            @if($blocking)
                <div class="driver-alert driver-alert--warning">
                    <i class="bi bi-tools"></i>
                    <div>
                        <strong>Документ требует доработки перед оформлением</strong>
                        <ul>
                            @foreach($blocking as $item)<li>{{ $item }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($fContraindicationMatch)
                <div class="driver-alert {{ ($fContraindicationMatch['specialist'] ?? '') === 'psychiatrist_narcologist' ? 'driver-alert--neutral' : 'driver-alert--warning' }}">
                    <i class="bi {{ ($fContraindicationMatch['specialist'] ?? '') === 'psychiatrist_narcologist' ? 'bi-capsule' : 'bi-shield-exclamation' }}"></i>
                    <div>
                        <strong>{{ $document->diagnosis_code }} входит в перечень медицинских противопоказаний № 892-р</strong>
                        <div style="margin-top:3px">{{ $fContraindicationMatch['title'] }}. {{ $fContraindicationMatch['condition'] }}</div>
                    </div>
                </div>
            @endif

            <div class="driver-details-grid">
                <section class="driver-detail-card">
                    <h2><i class="bi bi-file-earmark-medical"></i> Обследование</h2>
                    <dl class="driver-detail-list">
                        <div><dt>Номер</dt><dd>{{ $display($document->document_number) }}</dd></div>
                        <div><dt>Дата</dt><dd>{{ $document->examination_date?->format('d.m.Y') ?: 'Не указана' }}</dd></div>
                        <div><dt>Цель</dt><dd>{{ $document->purposeLabel() }}</dd></div>
                        <div><dt>Статус</dt><dd>{{ $document->statusLabel() }}</dd></div>
                        @if($fContraindicationMatch)
                            <div class="driver-detail-card--wide"><dt>Оценка противопоказания</dt><dd>{{ $document->contraindicationAssessmentLabel() }}</dd></div>
                        @endif
                        <div><dt>Автор</dt><dd>{{ $display($creatorName) }}</dd></div>
                        <div><dt>Оформлено</dt><dd>{{ $document->prepared_at?->format('d.m.Y H:i') ?: 'Нет' }}</dd></div>
                    </dl>
                </section>

                <section class="driver-detail-card">
                    <h2><i class="bi bi-person-vcard"></i> Пациент</h2>
                    <dl class="driver-detail-list">
                        <div class="driver-detail-card--wide"><dt>ФИО</dt><dd>{{ $document->patientFullName() }}</dd></div>
                        <div><dt>Дата рождения</dt><dd>{{ $document->patientBirthDateLabel() }}</dd></div>
                        <div><dt>Возраст</dt><dd>{{ data_get($snapshot, 'age') !== null ? data_get($snapshot, 'age') . ' лет' : 'Не указан' }}</dd></div>
                        <div class="driver-detail-card--wide"><dt>Диагноз в карте на дату оформления</dt><dd>{{ $document->diagnosisLabel() }}</dd></div>
                    </dl>
                </section>

                <section class="driver-detail-card driver-detail-card--wide">
                    <h2><i class="bi bi-card-checklist"></i> Категории транспортных средств</h2>
                    @if($categories)
                        <div class="driver-chip-list">
                            @foreach($categories as $category)<span class="driver-chip">{{ $category }}</span>@endforeach
                        </div>
                    @else
                        <div class="driver-text-block is-empty">Категории не выбраны.</div>
                    @endif
                </section>

                <section class="driver-detail-card driver-detail-card--wide">
                    <h2><i class="bi bi-journal-medical"></i> Анамнез</h2>
                    @foreach([
                        'complaints' => 'Жалобы',
                        'disease_history' => 'Анамнез текущего состояния',
                        'psychiatric_history' => 'Психиатрический анамнез',
                        'hospitalizations' => 'Госпитализации',
                        'current_treatment' => 'Текущая терапия',
                    ] as $field => $title)
                        <div class="driver-section-caption"><i class="bi bi-dot"></i> {{ $title }}</div>
                        <div class="driver-text-block {{ filled($document->{$field}) ? '' : 'is-empty' }}">{{ $display($document->{$field}) }}</div>
                    @endforeach
                </section>

                <section class="driver-detail-card driver-detail-card--wide">
                    <h2><i class="bi bi-person-lines-fill"></i> Психиатрическое обследование</h2>
                    <div class="driver-section-caption"><i class="bi bi-activity"></i> Психический статус</div>
                    <div class="driver-text-block {{ filled($document->mental_status) ? '' : 'is-empty' }}">{{ $display($document->mental_status) }}</div>
                    <div class="driver-section-caption"><i class="bi bi-brain"></i> Когнитивные и поведенческие особенности</div>
                    <div class="driver-text-block {{ filled($document->cognitive_behavioral_features) ? '' : 'is-empty' }}">{{ $display($document->cognitive_behavioral_features) }}</div>
                    <div class="driver-section-caption"><i class="bi bi-exclamation-triangle"></i> Настораживающие признаки</div>
                    @if($riskLabels)
                        <div class="driver-chip-list">
                            @foreach($riskLabels as $label)<span class="driver-chip" style="border-color:#e4c18e;background:#fff8e9;color:#76581e">{{ $label }}</span>@endforeach
                        </div>
                    @else
                        <div class="driver-text-block is-empty">Не отмечены.</div>
                    @endif
                    <div class="driver-section-caption"><i class="bi bi-shield-check"></i> Проверка медицинской карты</div>
                    <div class="driver-text-block">{{ $document->medical_record_reviewed ? 'Подтверждена' : 'Не подтверждена' }}</div>
                </section>

                <section class="driver-detail-card driver-detail-card--wide">
                    <h2><i class="bi bi-clipboard2-check"></i> Результат</h2>
                    <dl class="driver-detail-list">
                        <div class="driver-detail-card--wide"><dt>Решение</dt><dd>{{ $document->resultLabel() }}</dd></div>
                        @if(!$driverOutputBlocked && $document->certificate_number)
                            <div><dt>Номер справки психиатра</dt><dd>{{ $document->certificate_number }}</dd></div>
                            <div><dt>Дата справки</dt><dd>{{ $document->certificate_date?->format('d.m.Y') ?: '—' }}</dd></div>
                        @endif
                        @if($document->commission_protocol_number)
                            <div><dt>Протокол психиатрической ВК</dt><dd>№ {{ $document->commission_protocol_number }}</dd></div>
                            <div><dt>Дата протокола</dt><dd>{{ $document->commission_protocol_date?->format('d.m.Y') ?: '—' }}</dd></div>
                        @endif
                    </dl>
                    <div class="driver-section-caption"><i class="bi bi-chat-left-text"></i> Клиническое обоснование</div>
                    <div class="driver-text-block {{ filled($document->result_rationale) ? '' : 'is-empty' }}">{{ $display($document->result_rationale) }}</div>
                    @if(filled($document->commission_decision))
                        <div class="driver-section-caption"><i class="bi bi-people"></i> Решение ВК</div>
                        <div class="driver-text-block">{{ $document->commission_decision }}</div>
                    @endif
                    <div class="driver-section-caption"><i class="bi bi-file-earmark-text"></i> Итоговое заключение</div>
                    <div class="driver-text-block {{ filled($document->conclusion_text) ? '' : 'is-empty' }}">{{ $display($document->conclusion_text) }}</div>
                </section>
            </div>
        </div>
    </main>
@endsection
