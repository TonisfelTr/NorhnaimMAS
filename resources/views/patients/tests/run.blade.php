@extends('layouts.patient')

@section('title', $session->test?->name ?: 'Прохождение теста')

@section('assets')
    @vite(['resources/js/patient-tests-runner.js'])
    <style>
        .patient-test-run-page { max-width: 900px; margin:0 auto; padding:28px 28px 56px; }
        .patient-test-run-top { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:20px; }
        .patient-test-run-back { display:inline-flex; align-items:center; gap:8px; color:#168879; text-decoration:none; font-size:13px; font-weight:800; }
        .patient-test-run-back:hover { color:#116f64; }
        .patient-test-run-shell { background:#fff; border:1px solid #e6eeea; border-radius:20px; box-shadow:0 8px 30px rgba(31,67,60,.045); padding:clamp(18px,3vw,30px); }
        .patient-test-run-shell h1 { color:#1d433d; font-size:clamp(21px,2.6vw,28px); font-weight:800; letter-spacing:-.03em; margin:0 0 8px; }
        .patient-test-run-muted { color:#778782; font-size:13px; line-height:1.6; }
        #patient-test-runner { min-height:250px; }
        .patient-run-heading { margin-bottom:22px; }
        .patient-run-eyebrow { color:#168879; font-size:11px; font-weight:800; letter-spacing:.12em; margin-bottom:8px; }
        .patient-run-heading h1 { color:#1d433d; font-size:clamp(22px,2.7vw,29px); font-weight:800; letter-spacing:-.03em; margin:0 0 8px; }
        .patient-run-description { color:#71817e; font-size:14px; line-height:1.6; margin:0 0 12px; }
        .patient-run-instructions { color:#60736d; background:#f5f9f7; border:1px solid #e6efeb; border-radius:12px; padding:13px 15px; white-space:pre-line; font-size:13px; line-height:1.6; }
        .patient-run-progress { margin-bottom:18px; }
        .patient-run-progress__labels { display:flex; justify-content:space-between; gap:10px; color:#71817e; font-size:12px; margin-bottom:8px; }
        .patient-run-progress__track { height:7px; background:#edf2ef; border-radius:20px; overflow:hidden; }
        .patient-run-progress__track div { width:0; height:100%; background:#168879; border-radius:20px; transition:width .2s ease; }
        .patient-run-notice { min-height:18px; margin:0 0 18px; font-size:12px; }
        .patient-run-notice.is-muted { color:#778782; }
        .patient-run-notice.is-success { color:#16805f; }
        .patient-run-notice.is-error { color:#b54747; }
        .patient-run-question { background:#fbfdfc; border:1px solid #e8efec; border-radius:16px; padding:clamp(17px,3vw,25px); }
        .patient-run-question__top { display:flex; justify-content:space-between; gap:10px; color:#168879; font-size:10px; font-weight:800; letter-spacing:.1em; margin-bottom:15px; }
        .patient-run-question h2 { color:#244640; font-size:18px; font-weight:800; line-height:1.5; margin:0 0 17px; }
        .patient-run-help { color:#71817e; font-size:13px; line-height:1.6; margin:0 0 14px; }
        .patient-run-options { display:grid; gap:9px; }
        .patient-run-option { display:flex; align-items:center; gap:12px; padding:13px 14px; border:1px solid #e4ece8; border-radius:12px; background:#fff; color:#435a54; font-size:14px; cursor:pointer; }
        .patient-run-option:has(input:checked) { border-color:#80b9ad; background:#f0f8f5; }
        .patient-run-option input { position:absolute; opacity:0; pointer-events:none; }
        .patient-run-option__marker { width:18px; height:18px; flex:0 0 18px; border:1.5px solid #aabbb4; border-radius:50%; background:#fff; }
        .patient-run-option input:checked + .patient-run-option__marker { border:5px solid #168879; }
        .patient-run-textarea { width:100%; box-sizing:border-box; resize:vertical; min-height:120px; border:1px solid #dce8e2; border-radius:12px; padding:13px 14px; color:#244640; font:inherit; font-size:14px; outline:none; background:#fff; }
        .patient-run-textarea:focus { border-color:#64a99b; box-shadow:0 0 0 3px rgba(22,136,121,.1); }
        .patient-run-question-nav { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:17px; }
        .patient-run-nav-hint { color:#899590; font-size:11px; text-align:center; }
        .patient-run-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; border:1px solid #168879; border-radius:10px; padding:9px 14px; font:inherit; font-size:13px; font-weight:800; cursor:pointer; text-decoration:none; }
        .patient-run-button.is-primary { color:#fff; background:#168879; }
        .patient-run-button.is-primary:hover { background:#116f64; }
        .patient-run-button.is-secondary { color:#168879; background:#fff; }
        .patient-run-button.is-secondary:hover { background:#f1f8f6; }
        .patient-run-button:disabled { opacity:.45; cursor:not-allowed; }
        .patient-run-footer { display:flex; justify-content:space-between; gap:12px; margin-top:20px; }
        .patient-run-image-wrap { margin:14px 0; text-align:center; }
        .patient-run-image-wrap img { max-width:100%; max-height:440px; object-fit:contain; border-radius:12px; }
        .patient-run-sort-list { display:grid; gap:8px; }
        .patient-run-sort-item { display:flex; align-items:center; gap:12px; padding:11px 12px; border:1px solid #e3ece7; border-radius:12px; background:#fff; }
        .patient-run-sort-item__number { width:26px; height:26px; display:grid; place-items:center; flex:0 0 26px; border-radius:8px; background:#edf6f2; color:#168879; font-weight:800; font-size:12px; }
        .patient-run-sort-item img { width:48px; height:48px; object-fit:cover; border-radius:8px; }
        .patient-run-sort-color { width:30px; height:30px; border-radius:8px; border:1px solid #dce5e0; }
        .patient-run-sort-item__text { flex:1; color:#435a54; font-size:13px; font-weight:700; }
        .patient-run-sort-item__actions { display:flex; gap:5px; }
        .patient-run-sort-item__actions button { width:30px; height:30px; border:1px solid #e0e9e4; border-radius:8px; background:#fff; color:#168879; cursor:pointer; }
        .patient-run-sort-item__actions button:disabled { opacity:.3; cursor:not-allowed; }
        .patient-run-empty,.patient-run-error { text-align:center; padding:34px 20px; color:#778782; }
        .patient-run-error h2 { color:#244640; font-size:18px; margin:0 0 8px; }
        .patient-run-error p { font-size:13px; margin-bottom:18px; }
        .patient-run-error__icon { font-size:28px; color:#bd8050; margin-bottom:10px; }
        @media(max-width:640px) {
            .patient-run-question-nav { flex-wrap:wrap; }
            .patient-run-nav-hint { order:3; flex:1 0 100%; }
            .patient-run-footer { flex-direction:column; }
            .patient-run-footer .patient-run-button { width:100%; }
            .patient-run-sort-item { gap:8px; padding:9px; }
        }
        @media(max-width:640px) { .patient-test-run-page{padding:18px 14px 36px}.patient-test-run-top{align-items:flex-start}.patient-test-run-shell{border-radius:16px;padding:17px} }
    </style>
@endsection

@section('content')
    <div class="patient-test-run-page">
        <div class="patient-test-run-top">
            <a class="patient-test-run-back" href="{{ $returnUrl }}">
                <i class="bi bi-arrow-left"></i> К тестированиям
            </a>
            <span class="patient-test-run-muted">Прогресс сохраняется автоматически</span>
        </div>

        <section class="patient-test-run-shell">
            <div
                id="patient-test-runner"
                data-payload-url="{{ route('patients.tests.payload', $session) }}"
                data-save-url="{{ route('patients.tests.save', $session) }}"
                data-finish-url="{{ route('patients.tests.finish', $session) }}"
                data-return-url="{{ $returnUrl }}"
                data-result-url="{{ route('patients.tests.result', $session) }}"
            >
                <div class="patient-test-run-muted">Загружаем тестирование…</div>
            </div>
        </section>
    </div>
@endsection
