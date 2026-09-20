<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>{{ $document->document_number ?: 'Психиатрическое обследование водителя' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #eceff1; font-family: "Times New Roman", Times, serif; font-size: 11.5pt; line-height: 1.35; }
        .toolbar { width: 210mm; margin: 12px auto 0; text-align: right; font-family: Arial, sans-serif; }
        .toolbar button { padding: 9px 16px; border: 0; border-radius: 7px; color: #fff; background: #087f8e; font-weight: 700; cursor: pointer; }
        .sheet { width: 210mm; min-height: 297mm; margin: 10px auto; padding: 16mm 17mm; background: #fff; box-shadow: 0 5px 22px rgba(0,0,0,.12); }
        .header { margin-bottom: 18px; text-align: center; }
        .header .org { margin-bottom: 12px; font-size: 10pt; }
        .header h1 { margin: 0; font-size: 14pt; line-height: 1.25; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 9.5pt; }
        .meta { width: 100%; border-collapse: collapse; margin: 12px 0 16px; }
        .meta th, .meta td { padding: 5px 7px; border: 1px solid #444; vertical-align: top; }
        .meta th { width: 31%; text-align: left; font-weight: 700; background: #f3f3f3; }
        .section { margin: 14px 0 5px; font-weight: 700; }
        .box { min-height: 28px; padding: 6px 7px; border: 1px solid #777; white-space: pre-wrap; overflow-wrap: anywhere; }
        .line { padding: 4px 0; border-bottom: 1px solid #999; }
        .chips { display: flex; flex-wrap: wrap; gap: 7px; margin: 5px 0 10px; }
        .chip { padding: 2px 7px; border: 1px solid #777; border-radius: 12px; font-size: 9pt; }
        .signature { display: grid; grid-template-columns: 190px 1fr 220px; gap: 12px; align-items: end; margin-top: 28px; }
        .signature__line { min-height: 22px; border-bottom: 1px solid #111; }
        .signature__caption { margin-top: 2px; font-size: 8pt; text-align: center; color: #555; }
        .notice { margin-top: 18px; padding: 7px 8px; border: 1px solid #aaa; font-size: 8.5pt; }
        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
@php
    $creatorName = collect([
        $document->creator?->surname,
        $document->creator?->name,
        $document->creator?->patronym,
    ])->filter()->implode(' ');
    $categories = $document->vehicleCategoryLabels();
    $riskLabels = $document->riskFlagLabels();
    $text = static fn (mixed $value, string $empty = '—'): string => trim((string) $value) !== '' ? trim((string) $value) : $empty;

    if ($document->result === \App\Models\DriverPsychiatricExamination::RESULT_REFER_COMMISSION) {
        $title = 'Направление на психиатрическое освидетельствование врачебной комиссией';
        $subtitle = 'по результатам психиатрического обследования водителя / кандидата в водители';
    } elseif ($document->result === \App\Models\DriverPsychiatricExamination::RESULT_COMMISSION_CONTRAINDICATIONS) {
        $title = 'Заключение психиатрической врачебной комиссии';
        $subtitle = 'по результатам психиатрического освидетельствования';
    } elseif ($document->result === \App\Models\DriverPsychiatricExamination::RESULT_REFUSED_COMMISSION) {
        $title = 'Отметка о результате психиатрического обследования';
        $subtitle = 'отказ от психиатрического освидетельствования врачебной комиссией';
    } else {
        $title = 'Справка врача-психиатра по результатам обследования';
        $subtitle = 'для предоставления в медицинскую организацию, оформляющую водительское медицинское заключение';
    }
@endphp

<div class="toolbar"><button type="button" onclick="window.print()">Печать</button></div>
<article class="sheet">
    <header class="header">
        <div class="org">{{ config('app.name') }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>
    </header>

    <table class="meta">
        <tr><th>Документ</th><td>{{ $text($document->document_number) }} от {{ $document->examination_date?->format('d.m.Y') ?: '—' }}</td></tr>
        @if($document->certificate_number)
            <tr><th>Справка психиатра</th><td>{{ $document->certificate_number }} от {{ $document->certificate_date?->format('d.m.Y') ?: '—' }}</td></tr>
        @endif
        <tr><th>Пациент</th><td>{{ $document->patientFullName() }}, дата рождения {{ $document->patientBirthDateLabel() }}</td></tr>
        <tr><th>Цель обращения</th><td>{{ $document->purposeLabel() }}</td></tr>
        <tr><th>Категории ТС</th><td>{{ $categories ? implode(', ', $categories) : '—' }}</td></tr>
        <tr><th>Диагноз в карте на дату оформления</th><td>{{ $document->diagnosisLabel() }}</td></tr>
        @if(\App\Models\DriverPsychiatricExamination::drivingContraindicationMatch($document->diagnosis_code))
            <tr><th>Оценка противопоказания</th><td>{{ $document->contraindicationAssessmentLabel() }}</td></tr>
        @endif
    </table>

    <div class="section">Психический статус</div>
    <div class="box">{{ $text($document->mental_status) }}</div>

    @if(filled($document->cognitive_behavioral_features))
        <div class="section">Когнитивные и поведенческие особенности</div>
        <div class="box">{{ $document->cognitive_behavioral_features }}</div>
    @endif

    @if($riskLabels)
        <div class="section">Клинически значимые настораживающие признаки</div>
        <div class="chips">
            @foreach($riskLabels as $label)<span class="chip">{{ $label }}</span>@endforeach
        </div>
    @endif

    <div class="section">Результат обследования</div>
    <div class="box"><strong>{{ $document->resultLabel() }}</strong>
@if(filled($document->result_rationale))

Обоснование: {{ $document->result_rationale }}
@endif</div>

    @if($document->isCommissionResult())
        <div class="section">Психиатрическая врачебная комиссия</div>
        <div class="line">Протокол № {{ $text($document->commission_protocol_number) }} от {{ $document->commission_protocol_date?->format('d.m.Y') ?: '—' }}</div>
        <div class="box" style="margin-top:6px">{{ $text($document->commission_decision) }}</div>
    @endif

    <div class="section">Заключение</div>
    <div class="box">{{ $text($document->conclusion_text) }}</div>

    <div class="signature">
        <div>Врач-психиатр</div>
        <div><div class="signature__line"></div><div class="signature__caption">подпись</div></div>
        <div><div class="signature__line">{{ $creatorName }}</div><div class="signature__caption">расшифровка подписи</div></div>
    </div>

    <div class="notice">
        Документ фиксирует результат психиатрического обследования в рамках водительской медицинской комиссии и не является медицинским заключением по форме 003-В/у.
    </div>
</article>
</body>
</html>
