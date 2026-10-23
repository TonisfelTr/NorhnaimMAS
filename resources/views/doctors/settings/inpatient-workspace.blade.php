@extends('layouts.doctor')
@section('title', 'Настройки стационара')
@section('assets')
    @parent
    <style>

        .hospital-editor { color-scheme: light; --teal:#078fa0; --ink:#1a4055; --muted:#658192; --border:#dbe9ed; }
        .hospital-editor, .hospital-editor *, .hospital-editor *::before, .hospital-editor *::after { box-sizing:border-box; }
        .hospital-editor { background:#f3f8fa; min-height:calc(100vh - 130px); color:var(--ink); font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; }
        .hospital-editor button,.hospital-editor input,.hospital-editor select,.hospital-editor textarea { font:inherit; }
        .hospital-editor button { cursor:pointer; }
        .top { color:#fff; background:linear-gradient(117deg,#096d83 0%,#079dad 68%,#13b9c3 100%); box-shadow:0 8px 32px #084a5e24; }
        .top__inner { max-width:1200px; margin:auto; min-height:94px; padding:17px 25px; display:flex; align-items:center; gap:16px; }
        .top__symbol { display:grid; place-items:center; width:49px; height:49px; border-radius:16px; background:#ffffff25; font-size:24px; flex:0 0 49px; }
        .top__copy { flex:1; min-width:0; }
        .top__eyebrow { font-size:10px; font-weight:850; letter-spacing:.09em; text-transform:uppercase; color:#c9f7fa; }
        .top__name { margin:4px 0 0; font-size:clamp(16px,2.3vw,22px); font-weight:850; overflow-wrap:anywhere; }
        .top__desc { margin-top:6px; font-size:12px; color:#d9f4f7; }
        .leave { border:1px solid #ffffff75; color:#fff; background:#ffffff15; padding:11px 14px; display:inline-flex; gap:9px; align-items:center; border-radius:11px; font-weight:750; font-size:12px; white-space:nowrap; }
        .leave:hover { background:#ffffff30; }
        .shell { max-width:1200px; margin:0 auto; padding:24px 25px 56px; }
        .intro { display:flex; gap:13px; align-items:flex-start; padding:16px 19px; background:#e9f8fa; border:1px solid #c6e6eb; border-radius:15px; margin-bottom:21px; }
        .intro > i { color:#0695a3; font-size:21px; }
        .intro strong { display:block; font-size:13px; margin-bottom:5px; }
        .intro p { color:#577888; font-size:12px; line-height:1.5; margin:0; }
        .panel { display:none; }
        .panel.is-active { display:block; }
        .grid { display:grid; grid-template-columns:minmax(0,1.25fr) minmax(320px,1fr); gap:18px; align-items:start; }
        .stack { display:grid; gap:18px; }
        .card { border-radius:18px; border:1px solid #dce8ed; background:#fff; box-shadow:0 8px 25px #0a45580a; overflow:hidden; }
        .card__head { display:flex; align-items:center; gap:12px; background:#fcfefe; border-bottom:1px solid #e8eff2; padding:16px 20px; }
        .card__icon { width:35px; height:35px; border-radius:11px; background:#e8f7f9; color:var(--teal); display:grid; place-items:center; font-size:17px; flex:0 0 35px; }
        .card__title { font-size:15px; font-weight:850; }
        .card__subtitle { margin-top:4px; font-size:11px; color:#81949f; }
        .card__body { padding:20px; display:grid; gap:17px; }
        .field { display:grid; gap:7px; position:relative; }
        .field__label { font-size:12px; font-weight:780; }
        .field__help { color:#7d95a0; font-size:11px; line-height:1.55; margin:0; }
        .required { color:#e16666; }
        .control { width:100%; min-height:42px; padding:10px 12px; border:1px solid #d6e5eb; border-radius:10px; background:#fff; color:#24495b; font-size:12.5px; outline:none; }
        .control:focus { border-color:#13abba; box-shadow:0 0 0 3px #10a0af18; }
        textarea.control { min-height:100px; resize:vertical; }
        .two { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .phone-row { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) 40px; align-items:center; gap:9px; }
        .phone-row button { width:40px; height:40px; border-radius:10px; border:1px solid #f0dce0; background:#fff8f9; color:#b65365; }
        .add { display:inline-flex; gap:8px; align-items:center; width:max-content; border:1px dashed #9fced6; background:#effafb; color:#05798a; font-weight:780; border-radius:10px; padding:9px 13px; font-size:12px; }
        .mode-options { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:9px; }
        .mode-option input { position:absolute; opacity:0; width:1px; height:1px; }
        .mode-box { min-height:118px; border:1px solid #dce8ed; border-radius:13px; padding:14px; display:grid; gap:6px; cursor:pointer; }
        .mode-box i { font-size:19px; color:#6d8e9b; }
        .mode-box strong { font-size:12px; }
        .mode-box small { color:#728995; font-size:10.5px; line-height:1.45; }
        .mode-option input:checked + .mode-box { background:#edfafa; border-color:#079eaf; box-shadow:inset 0 0 0 1px #079eaf; }
        .mode-option input:focus-visible + .mode-box { outline:3px solid #67cfd8; }
        .days { width:100%; border-collapse:collapse; font-size:12px; }
        .days th { text-align:left; color:#76919d; font-weight:750; font-size:11px; padding:8px; border-bottom:1px solid #e9eff2; }
        .days td { padding:7px; border-bottom:1px solid #eff3f5; }
        .days input[type=time] { width:100%; min-width:0; padding:8px; font-size:11.5px; border:1px solid #d8e5ea; border-radius:9px; }
        .days input[type=checkbox] { accent-color:#078e9e; width:16px; height:16px; }
        .days .day-name { min-width:100px; }
        .schedule-grid.is-single { grid-template-columns:minmax(0,1fr); }
        .schedule-grid > [hidden] { display:none !important; }
        .schedule-overview { display:flex; align-items:center; gap:14px; border:1px solid #c7e8df; background:#f1fbf7; border-radius:13px; padding:17px; }
        .schedule-overview > i { font-size:25px; color:#168975; }
        .schedule-overview strong { display:block; color:#245a4c; font-size:14px; margin-bottom:5px; }
        .schedule-overview small { display:block; color:#567b73; font-size:11px; line-height:1.6; }
        .schedule-help { color:#718b96; font-size:11px; line-height:1.5; margin:0; }
        .submitbar { position:sticky; bottom:0; background:#ffffffec; border:1px solid #e3ebef; border-radius:15px; margin-top:18px; padding:13px 18px; display:flex; justify-content:space-between; align-items:center; gap:12px; backdrop-filter:blur(12px); }
        .submitbar__note { font-size:11px; color:#668694; max-width:470px; line-height:1.5; }
        .save { border:0; background:linear-gradient(120deg,#058b9e,#08aaba); color:#fff; padding:12px 18px; border-radius:11px; font-size:12px; font-weight:830; display:inline-flex; align-items:center; gap:9px; white-space:nowrap; }
        .save:disabled { opacity:.55; cursor:wait; }
        .alert { border-radius:11px; padding:12px 15px; display:none; font-size:12px; line-height:1.6; margin-bottom:13px; }
        .alert.visible { display:block; }
        .alert--error { border:1px solid #f1c8cb; background:#fff4f4; color:#9e3440; }
        .alert--ok { border:1px solid #b9e8d2; background:#effbf4; color:#286f50; }
        .suggestions { position:absolute; left:0; right:0; top:calc(100% - 25px); z-index:50; max-height:230px; overflow:auto; padding:5px; border:1px solid #c5e2e6; background:#fff; border-radius:11px; box-shadow:0 14px 27px #1545572d; }
        .suggestions[hidden] { display:none; }
        .suggestions button { width:100%; background:#fff; border:0; border-radius:8px; padding:10px; text-align:left; color:#2b4c5b; font-size:12px; line-height:1.4; }
        .suggestions button:hover { background:#e8f8fa; }
        /* Подсказки корпуса раскрываются за пределами карточки, а не обрезаются её overflow. */
        .hospital-editor .ws-editor-card--building { position:relative; z-index:3; overflow:visible; }
        .hospital-editor .ws-editor-card--building .ws-editor-card__head { border-radius:17px 17px 0 0; }
        .hospital-editor .ws-address-lookup { display:block; position:relative; min-width:0; }
        .hospital-editor .ws-address-lookup .suggestions { top:calc(100% + 5px); z-index:100; }
        .hospital-editor .ws-address-lookup .suggestions button { display:flex; gap:9px; align-items:flex-start; }
        .hospital-editor .ws-address-lookup .suggestions button i { color:#0792a5; flex-shrink:0; }
        .hospital-editor .ws-address-lookup .suggestions button:focus-visible { outline:2px solid #079eaf; background:#e9f8fa; }
        .empty { padding:20px; border-radius:11px; border:1px dashed #bfdbe0; background:#f8fbfc; font-size:12px; color:#65828f; }
        .pill { border-radius:999px; background:#e7f8f9; color:#087e8b; display:inline-block; padding:4px 10px; font-size:10px; font-weight:770; }
        /* Создание отделений и палат, без перехода в врачебную панель */
        .hospital-editor [hidden] { display:none !important; }
        .editor-heading { justify-content:space-between; }
        .editor-counter { flex:0 0 auto; margin-left:auto; }
        .editor-tabs { padding:15px 20px 0; display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
        .editor-switch { display:inline-flex; align-items:center; gap:8px; border-radius:11px; border:1px solid #d6e5eb; background:#f7fbfc; color:#426779; padding:10px 13px; font-size:12px; font-weight:800; }
        .editor-switch:hover { background:#eaf8f9; border-color:#acdce2; }
        .editor-switch.is-active { background:#e3f7f8; color:#057888; border-color:#20aabb; }
        .editor-switch:focus-visible { outline:3px solid #7ad2dd; }
        .editor-section { padding:20px; }
        .editor-section .card__body { padding:0; }
        .editor-section .field { min-width:0; }
        .editor-section .two { align-items:start; }
        .editor-divider { height:1px; background:#e8eff2; margin:18px 0; }
        .editor-note { margin-top:14px; color:#6c8794; font-size:12px; line-height:1.6; }
        .editor-note i { margin-right:6px; color:#078b9c; }
        .editor-summary { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .editor-summary strong { font-size:12px; color:#214a5d; }
        @media(max-width:860px) { .schedule-grid.is-single { grid-template-columns:minmax(0,1fr); } .grid { grid-template-columns:1fr; } .top__inner { padding:14px; } .shell { padding:14px; } .mode-options { grid-template-columns:1fr; } .mode-box { min-height:80px; } }
        @media(max-width:500px) { .top__desc { display:none; } .top__symbol { display:none; } .phone-row { grid-template-columns:1fr 40px; } .phone-row .phone-label { grid-column:1; } .phone-row button { grid-column:2; grid-row:1/3; } .two { grid-template-columns:1fr; } .days .day-name { min-width:50px; } .days th,.days td { padding:4px; } .submitbar { align-items:stretch; flex-direction:column; } }
    
        /* Управление корпусами и отделениями: отдельный визуальный язык внутри стационара. */
        .hospital-editor .ws-sectionbar { display:flex; justify-content:space-between; align-items:center; gap:18px; flex-wrap:wrap; margin-bottom:18px; }
        .hospital-editor .ws-sectionbar__text { display:flex; align-items:center; gap:13px; min-width:0; }
        .hospital-editor .ws-sectionbar__icon { display:grid; place-items:center; width:46px; height:46px; border-radius:15px; background:#e0f6f8; color:#078d9c; font-size:21px; flex:0 0 46px; }
        .hospital-editor .ws-sectionbar__text h2 { margin:0 0 5px; font-size:21px; font-weight:850; letter-spacing:-.02em; color:#173e52; }
        .hospital-editor .ws-sectionbar__text p { margin:0; color:#738b98; font-size:12px; line-height:1.55; }
        .hospital-editor .ws-sectionbar__actions { display:flex; align-items:center; flex-wrap:wrap; gap:10px; }
        .hospital-editor .ws-counter { display:inline-flex; align-items:center; padding:7px 11px; border-radius:20px; background:#e9f7fa; color:#127d8d; font-size:11px; font-weight:800; }
        .hospital-editor .ws-action { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; padding:10px 16px; border:1px solid #048e9e; background:linear-gradient(110deg,#078ea0,#0babb8); color:#fff; border-radius:12px; font-size:12px; font-weight:800; box-shadow:0 6px 16px #0a849c25; transition:transform .14s,box-shadow .14s; }
        .hospital-editor .ws-action:hover { transform:translateY(-1px); box-shadow:0 9px 22px #0a849c2b; color:#fff; }
        .hospital-editor .ws-action--light { border-color:#bedfe5; box-shadow:none; background:#f4fbfc; color:#147a8c; }
        .hospital-editor .ws-action--light:hover { color:#08758a; background:#e7f6f8; box-shadow:none; }
        .hospital-editor .ws-layout { display:grid; grid-template-columns:minmax(250px,.86fr) minmax(0,1.55fr); gap:18px; align-items:start; }
        .hospital-editor .ws-directory,.hospital-editor .ws-editor-card { background:#fff; border:1px solid #dce9ed; border-radius:18px; overflow:hidden; box-shadow:0 9px 24px #19475d0b; }
        .hospital-editor .ws-directory__top { padding:19px 20px 16px; border-bottom:1px solid #e7eff2; }
        .hospital-editor .ws-directory__top strong { color:#20475a; font-weight:850; font-size:13px; }
        .hospital-editor .ws-directory__top small { display:block; margin-top:5px; color:#7a94a1; font-size:11px; }
        .hospital-editor .ws-directory__items { display:grid; gap:7px; padding:10px; max-height:660px; overflow-y:auto; }
        .hospital-editor .ws-item { display:flex; align-items:flex-start; width:100%; padding:13px; gap:12px; border:1px solid transparent; border-radius:13px; color:#21485b; background:#fff; text-align:left; transition:background .14s,border .14s; }
        .hospital-editor .ws-item:hover { border-color:#cde7ec; background:#f4fbfc; }
        .hospital-editor .ws-item.is-active { border-color:#79d0da; background:#ebf9fa; box-shadow:inset 0 0 0 1px #92dce3; }
        .hospital-editor .ws-item:focus-visible { outline:3px solid #53c8d5; outline-offset:2px; }
        .hospital-editor .ws-number { display:grid; place-items:center; width:54px; min-width:54px; height:47px; padding:4px; border-radius:12px; background:#e6f6f8; color:#087e8f; font-size:14px; font-weight:850; }
        .hospital-editor .ws-item.is-active .ws-number { background:#cef0f3; }
        .hospital-editor .ws-item__copy { flex:1; min-width:0; }
        .hospital-editor .ws-item__name { display:block; color:#163c50; font-size:12.5px; line-height:1.35; font-weight:850; overflow-wrap:anywhere; }
        .hospital-editor .ws-item__meta { display:flex; align-items:center; gap:5px; color:#668797; font-size:10.5px; line-height:1.45; margin-top:7px; }
        .hospital-editor .ws-item__status { display:inline-flex; align-items:center; gap:5px; margin-top:8px; padding:4px 8px; border-radius:12px; color:#197454; background:#ebf8f1; font-size:10px; font-weight:800; }
        .hospital-editor .ws-item__status--off { color:#99742e; background:#fcf6e8; }
        .hospital-editor .ws-list-empty { padding:38px 22px; color:#7b919d; text-align:center; }
        .hospital-editor .ws-list-empty i { display:block; font-size:29px; margin-bottom:10px; color:#6fb8c4; }
        .hospital-editor .ws-list-empty strong { display:block; color:#234c5f; margin-bottom:5px; font-size:13px; }
        .hospital-editor .ws-list-empty p { font-size:11.5px; line-height:1.65; margin:0; }
        .hospital-editor .ws-editor-card__head { display:flex; align-items:center; gap:12px; padding:20px 22px; border-bottom:1px solid #e8eff2; background:linear-gradient(100deg,#f8fcfd,#fff); }
        .hospital-editor .ws-editor-card__head-icon { display:grid; place-items:center; width:40px; height:40px; border-radius:12px; background:#e8f7f9; color:#098fa2; font-size:18px; }
        .hospital-editor .ws-editor-card__head strong { font-size:15px; font-weight:850; }
        .hospital-editor .ws-editor-card__head small { display:block; margin-top:5px; font-size:11px; color:#7e95a0; }
        .hospital-editor .ws-editor-card__body { padding:22px; }
        .hospital-editor .ws-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:17px 14px; }
        .hospital-editor .ws-form-grid .field { align-content:start; }
        .hospital-editor .ws-form-grid .field--wide { grid-column:1/-1; }
        .hospital-editor .ws-prefixed { display:flex; align-items:center; border:1px solid #d6e5eb; border-radius:10px; overflow:hidden; height:43px; background:#fff; }
        .hospital-editor .ws-prefixed:focus-within { border-color:#13abba; box-shadow:0 0 0 3px #10a0af18; }
        .hospital-editor .ws-prefixed span { color:#0c8493; font-size:16px; font-weight:850; padding-left:13px; user-select:none; }
        .hospital-editor .ws-prefixed input { border:0; outline:0; flex:1; min-width:0; padding:10px 12px 10px 4px; color:#234b5e; background:#fff; font-size:13px; }
        .hospital-editor .ws-check-row { display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap; background:#f6fafb; border:1px solid #e2eef0; border-radius:13px; padding:12px 15px; margin-top:18px; }
        .hospital-editor .ws-check { display:inline-flex; align-items:center; gap:9px; font-size:12px; font-weight:800; color:#2e5565; cursor:pointer; }
        .hospital-editor .ws-check input { accent-color:#059bae; width:17px; height:17px; }
        .hospital-editor .ws-sort { display:flex; align-items:center; gap:10px; font-size:11px; font-weight:700; color:#62818e; }
        .hospital-editor .ws-sort input { width:85px; min-height:36px; }
        .hospital-editor .ws-editor-card .submitbar { position:static; margin-top:20px; box-shadow:none; }
        .hospital-editor .ws-hint { margin-top:15px; display:flex; gap:9px; align-items:flex-start; padding:12px 13px; border-radius:12px; background:#f0f9fb; border:1px solid #d7ebf0; color:#598190; font-size:11px; line-height:1.6; }
        .hospital-editor .ws-hint i { font-size:15px; flex:0 0 auto; color:#1096a3; }
        .hospital-editor .ws-empty-schema { border:1px solid #f0dcac; background:#fff9ec; border-radius:13px; padding:18px; color:#806020; font-size:12px; line-height:1.6; }
        .hospital-editor .ws-info-link { border:0; background:none; color:#087d8f; padding:0; font-size:11.5px; font-weight:800; text-decoration:underline; text-underline-offset:3px; }
        .hospital-editor .ws-footnote { margin-top:17px; padding:13px 17px; background:#e9f8fa; border:1px solid #cbe8eb; border-radius:13px; display:flex; align-items:center; gap:11px; font-size:11.5px; color:#517b8a; }
        .hospital-editor .ws-footnote i { color:#078a9e; font-size:18px; }
        .hospital-editor .ws-section-hidden { display:none !important; }
        @media (max-width:970px) { .hospital-editor .ws-layout {grid-template-columns:minmax(0,1fr);} .hospital-editor .ws-directory__items { max-height:310px; } }
        @media (max-width:600px) { .hospital-editor .ws-editor-card__body {padding:16px;} .hospital-editor .ws-form-grid {grid-template-columns:1fr;} .hospital-editor .ws-check-row {align-items:flex-start;} .hospital-editor .ws-action {width:100%;} .hospital-editor .ws-sectionbar__actions {width:100%;} .hospital-editor .ws-sectionbar__text h2{font-size:18px;} }

        /* Готовые профили — только подстановка реквизитов, без изменения ограничений по полу и возрасту. */
        .hospital-editor .ws-profile-presets { display:flex; align-items:center; flex-wrap:wrap; gap:7px; margin-top:3px; }
        .hospital-editor .ws-profile-presets__hint { flex:0 0 100%; font-size:10.5px; color:#758f9c; font-weight:650; }
        .hospital-editor .ws-profile-preset { display:inline-flex; align-items:center; gap:6px; min-height:31px; padding:6px 9px; background:#f3fbfc; color:#087b8a; border:1px solid #cce5ea; border-radius:9px; font-size:11px; font-weight:780; transition:background .15s,border-color .15s; }
        .hospital-editor .ws-profile-preset:hover, .hospital-editor .ws-profile-preset.is-selected { background:#e1f6f8; border-color:#63bdca; }
        .hospital-editor .ws-profile-preset:focus-visible { outline:3px solid #64cbd6; outline-offset:2px; }
        .ws-age-panel{padding:16px;border:1px solid #dce8ee;border-radius:14px;background:#f4fbfc}
        .ws-age-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}
        .ws-age-row label{font-size:12px;font-weight:700;color:#35576a}
        .ws-age-row .control{margin-top:6px;background:#fff}
        @media(max-width:560px){.ws-age-row{grid-template-columns:1fr}}
    </style>
@endsection
@section('main')

    <div class="hospital-editor">
    <header class="top">
        <div class="top__inner">
            <span class="top__symbol" aria-hidden="true"><i class="bi bi-hospital"></i></span>
            <div class="top__copy">
                <div class="top__eyebrow">Временный режим администрирования стационара</div>
                <div class="top__name">{{ $hospital->name }}</div>
                <div class="top__desc">Головная организация: {{ $parentClinic->name }}</div>
            </div>

        </div>
        @include('doctors.settings.partials.hospital-temporary-nav', ['showBrand' => false])
    </header>
    <main class="shell">
        <div class="intro"><i class="bi bi-shield-check"></i><div><strong>Вы настраиваете только выбранный стационар</strong><p>Учреждение амбулаторного типа, ваш профиль врача и доступ к медицинским картам не изменяются. После сохранения вы останетесь в настройках стационара. В амбулаторию можно вернуться специальной кнопкой.</p></div></div>
        <div id="workspaceAlert" class="alert" role="alert" aria-live="polite"></div>

        <section class="panel is-active" data-panel="general">
            <form id="generalForm" method="post" action="{{ $saveUrl }}" novalidate>
                @csrf
                <input type="hidden" name="section" value="general">
                <input type="hidden" name="city" id="wsCity" value="{{ $hospital->city }}">
                <input type="hidden" name="latitude" id="wsLatitude" value="{{ $hospital->latitude ?? '' }}">
                <input type="hidden" name="longitude" id="wsLongitude" value="{{ $hospital->longitude ?? '' }}">
                <div class="grid">
                    <div class="stack"><div class="card"><div class="card__head"><div class="card__icon"><i class="bi bi-building"></i></div><div><div class="card__title">Сведения о стационаре</div><div class="card__subtitle">Изменяются только поля выбранного учреждения</div></div></div>
                        <div class="card__body">
                            <label class="field"><span class="field__label">Наименование <span class="required">*</span></span><input class="control" name="name" maxlength="255" required value="{{ $hospital->name }}"></label>
                            <label class="field"><span class="field__label">Полный адрес <span class="required">*</span></span><input id="wsAddress" class="control" name="address" maxlength="500" autocomplete="off" required value="{{ $hospital->address }}" placeholder="Город, улица, дом, корпус"><span class="field__help">Начните вводить адрес и выберите предложенный вариант. Можно ввести адрес вручную.</span><div id="wsSuggestions" class="suggestions" hidden></div></label>
                            <label class="field"><span class="field__label">Краткое описание</span><textarea class="control" name="description" maxlength="5000" placeholder="Например, круглосуточная психиатрическая помощь">{{ $hospital->description }}</textarea></label>
                        </div></div>
                    </div>
                    <div class="stack"><div class="card"><div class="card__head"><div class="card__icon"><i class="bi bi-telephone"></i></div><div><div class="card__title">Телефоны стационара</div><div class="card__subtitle">Приёмное отделение, пост медсестры, администрация и др.</div></div></div><div class="card__body"><div id="wsPhones" class="stack" style="gap:9px"></div><button type="button" id="wsAddPhone" class="add"><i class="bi bi-plus-circle"></i> Добавить телефон</button></div></div>
                        <div class="card"><div class="card__head"><div class="card__icon"><i class="bi bi-lock"></i></div><div><div class="card__title">Неизменяемые сведения</div></div></div><div class="card__body"><div class="field__help"><span class="pill">Тип: стационар</span> <span class="pill">ID: {{ $hospital->id }}</span><p>Связь с амбулаторией изменяется отдельно в разделе «Принадлежность стационаров».</p></div></div></div>
                    </div>
                </div>
                <div class="submitbar"><span class="submitbar__note">При ошибке данные остаются в форме. Успешное сохранение не завершает режим стационара.</span><button class="save" type="submit"><i class="bi bi-check-circle"></i> Сохранить сведения</button></div>
            </form>
        </section>

        <section class="panel" data-panel="schedule">
            <form id="scheduleForm" method="post" action="{{ $saveUrl }}" novalidate>
                @csrf
                <input type="hidden" name="section" value="schedule">
                <div class="card"><div class="card__head"><div class="card__icon"><i class="bi bi-clock-history"></i></div><div><div class="card__title">Формат работы стационара</div><div class="card__subtitle">Не влияет на тип амбулатории</div></div></div><div class="card__body"><div class="mode-options">
                    <label class="mode-option"><input type="radio" name="inpatient_mode" value="day" {{ $hospital->inpatient_mode === 'day' ? 'checked' : '' }}><span class="mode-box"><i class="bi bi-sun"></i><strong>Дневной</strong><small>Стационар работает днём</small></span></label>
                    <label class="mode-option"><input type="radio" name="inpatient_mode" value="night" {{ $hospital->inpatient_mode === 'night' ? 'checked' : '' }}><span class="mode-box"><i class="bi bi-moon-stars"></i><strong>Ночной</strong><small>Смена через полночь</small></span></label>
                    <label class="mode-option"><input type="radio" name="inpatient_mode" value="round_the_clock" {{ !in_array($hospital->inpatient_mode, ['day','night'], true) ? 'checked' : '' }}><span class="mode-box"><i class="bi bi-clock"></i><strong>Круглосуточный</strong><small>Работа 24 часа в сутки</small></span></label>
                </div></div></div>
                <div class="grid schedule-grid" id="wsScheduleGrid" style="margin-top:18px">
                    <div class="card" id="wsWorkingHoursCard">
                        <div class="card__head"><div class="card__icon"><i class="bi bi-calendar-check"></i></div><div><div class="card__title">Рабочие часы</div><div class="card__subtitle" id="wsWorkingHoursSubtitle">Укажите время начала и окончания смены</div></div></div>
                        <div class="card__body">
                            <p class="schedule-help">При смене формата подставляются примерные часы. При необходимости отредактируйте их перед сохранением.</p>
                            <table class="days"><thead><tr><th>День</th><th>Работает</th><th>С</th><th>До</th></tr></thead><tbody>
                                @foreach($weekdays as $code => $label)
                                    <tr data-day="{{ $code }}"><td class="day-name">{{ $label }}</td><td><input type="hidden" name="working_hours[{{ $code }}][enabled]" value="0"><input type="checkbox" name="working_hours[{{ $code }}][enabled]" value="1" {{ ($workingHours[$code]['enabled'] ?? !in_array($code, ['sat','sun'], true)) ? 'checked' : '' }}></td><td><input type="time" name="working_hours[{{ $code }}][from]" value="{{ $workingHours[$code]['from'] ?? ($hospital->inpatient_mode === 'night' ? '20:00' : '08:00') }}"></td><td><input type="time" name="working_hours[{{ $code }}][to]" value="{{ $workingHours[$code]['to'] ?? ($hospital->inpatient_mode === 'night' ? '08:00' : '20:00') }}"></td></tr>
                                @endforeach
                            </tbody></table>
                        </div>
                    </div>
                    <div class="card" id="wsRoundClockCard" hidden>
                        <div class="card__head"><div class="card__icon"><i class="bi bi-clock-history"></i></div><div><div class="card__title">Круглосуточная работа</div><div class="card__subtitle">Все дни недели, без ограничений по часам</div></div></div>
                        <div class="card__body">
                            <div class="schedule-overview"><i class="bi bi-check-circle" aria-hidden="true"></i><div><strong>24 часа в сутки, 7 дней в неделю</strong><small>В круглосуточном режиме отдельный график 08:00–20:00 не применяется. В настройках сохраняется непрерывная работа учреждения.</small></div></div>
                        </div>
                    </div>
                    <div class="card" id="wsVisitingHoursCard">
                        <div class="card__head"><div class="card__icon"><i class="bi bi-people"></i></div><div><div class="card__title">Посещения родственниками</div><div class="card__subtitle">Только для круглосуточного стационара</div></div></div>
                        <div class="card__body">
                            <p class="schedule-help">Заполните время «С» и «До» — разрешение на посещения для этого дня включится автоматически. Чтобы запретить посещения, очистите интервал.</p>
                            <table class="days"><thead><tr><th>День</th><th>Можно</th><th>С</th><th>До</th></tr></thead><tbody>
                                @foreach($weekdays as $code => $label)
                                    <tr data-day="{{ $code }}"><td class="day-name">{{ $label }}</td><td><input type="hidden" name="visiting_hours[{{ $code }}][enabled]" value="0"><input type="checkbox" name="visiting_hours[{{ $code }}][enabled]" value="1" {{ ($visitingHours[$code]['enabled'] ?? false) ? 'checked' : '' }}></td><td><input type="time" name="visiting_hours[{{ $code }}][from]" value="{{ $visitingHours[$code]['from'] ?? '' }}"></td><td><input type="time" name="visiting_hours[{{ $code }}][to]" value="{{ $visitingHours[$code]['to'] ?? '' }}"></td></tr>
                                @endforeach
                            </tbody></table>
                        </div>
                    </div>
                </div>
                <div class="submitbar"><span class="submitbar__note">Графики сохраняются в JSON-полях стационара, не затрагивая график амбулатории.</span><button type="submit" class="save"><i class="bi bi-check-circle"></i> Сохранить графики</button></div>
            </form>
        </section>

        <section class="panel" data-panel="building">
            <div class="ws-sectionbar">
                <div class="ws-sectionbar__text">
                    <span class="ws-sectionbar__icon"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                    <div>
                        <h2>Корпуса стационара</h2>
                        <p>Собственные корпуса этого стационара. Здания амбулатории сюда не подмешиваются.</p>
                    </div>
                </div>
                <div class="ws-sectionbar__actions">
                    <span class="ws-counter">Всего: {{ $hospitalBuildings->count() }}</span>
                    @if($hospitalBuildingTableExists)
                        <button class="ws-action" type="button" id="wsBuildingNew"><i class="bi bi-plus-circle"></i> Новый корпус</button>
                    @endif
                </div>
            </div>
            @if(!$hospitalBuildingTableExists)
                <div class="ws-empty-schema" role="status">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    Таблица <code>hospital_buildings</code> отсутствует в базе. Это отдельная таблица корпусов стационара,
                    не <code>clinic_buildings</code> амбулатории. Перед созданием корпусов необходимо проверить миграции стационара.
                </div>
            @else
                <div class="ws-layout">
                    <aside class="ws-directory" aria-label="Корпуса стационара">
                        <div class="ws-directory__top"><strong>Корпуса учреждения</strong><small>Нажми на корпус, чтобы изменить его данные</small></div>
                        <div class="ws-directory__items">
                            @forelse($hospitalBuildings as $building)
                                <button type="button" class="ws-item" data-building-choice
                                        data-id="{{ $building->id }}"
                                        data-number="{{ $building->number }}"
                                        data-name="{{ $building->name }}"
                                        data-address="{{ $building->address ?? '' }}"
                                        data-enabled="{{ $building->enabled ? '1' : '0' }}">
                                    <span class="ws-number">№{{ $building->number }}</span>
                                    <span class="ws-item__copy">
                                        <strong class="ws-item__name">{{ $building->name }}</strong>
                                        <span class="ws-item__meta"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $building->address ?: 'Адрес не указан' }}</span>
                                        <span class="ws-item__status {{ $building->enabled ? '' : 'ws-item__status--off' }}">
                                            <i class="bi {{ $building->enabled ? 'bi-check-circle' : 'bi-pause-circle' }}" aria-hidden="true"></i>
                                            {{ $building->enabled ? 'Активен' : 'Отключён' }}
                                        </span>
                                    </span>
                                </button>
                            @empty
                                <div class="ws-list-empty"><i class="bi bi-buildings" aria-hidden="true"></i><strong>Корпусов пока нет</strong>
                                    <p>Создай первый корпус стационара или оставь отделения без корпуса, если учреждение небольшое.</p>
                                </div>
                            @endforelse
                        </div>
                    </aside>
                    <div class="ws-editor-card ws-editor-card--building">
                        <div class="ws-editor-card__head">
                            <span class="ws-editor-card__head-icon"><i class="bi bi-building-add" aria-hidden="true"></i></span>
                            <div><strong id="wsBuildingTitle">Новый корпус</strong><small id="wsBuildingSubtitle">Заполни сведения для создания корпуса стационара</small></div>
                        </div>
                        <div class="ws-editor-card__body">
                            <form method="POST" id="wsBuildingForm" action="{{ $saveUrl }}" novalidate>
                                @csrf
                                <input type="hidden" id="wsBuildingAction" name="section" value="building_create">
                                <input type="hidden" id="wsBuildingId" name="building_id" value="">
                                <div class="ws-form-grid">
                                    <label class="field"><span class="field__label">Номер корпуса <span class="required">*</span></span>
                                        <span class="ws-prefixed"><span aria-hidden="true">№</span><input type="text" id="wsBuildingNumber" name="number" maxlength="50" placeholder="1" autocomplete="off" required></span>
                                    </label>
                                    <label class="field"><span class="field__label">Название корпуса <span class="required">*</span></span>
                                        <input class="control" id="wsBuildingName" name="name" maxlength="255" placeholder="Основной корпус" required>
                                    </label>
                                    <label class="field field--wide"><span class="field__label">Адрес корпуса</span>
                                        <span class="ws-address-lookup">
                                            <input class="control" id="wsBuildingAddress" name="address" maxlength="500"
                                                   placeholder="Начните вводить город, улицу и номер дома" autocomplete="off"
                                                   aria-autocomplete="list" aria-controls="wsBuildingAddressSuggestions" aria-expanded="false">
                                            <span id="wsBuildingAddressSuggestions" class="suggestions" role="listbox" hidden></span>
                                        </span>
                                        <span class="field__help">Начните вводить адрес и выберите подходящую подсказку. При необходимости адрес можно ввести вручную. Адрес амбулатории не изменится.</span>
                                    </label>
                                </div>
                                <div class="ws-check-row"><label class="ws-check"><input type="hidden" name="enabled" value="0"><input type="checkbox" id="wsBuildingEnabled" name="enabled" value="1" checked> Корпус активен</label></div>
                                <div class="ws-hint"><i class="bi bi-info-circle"></i><span>После создания корпус появится в выборе при настройке отделений. Изменения затрагивают только этот стационар.</span></div>
                                <div class="submitbar"><span class="submitbar__note">Корпус получит <code>clinic_id</code> текущего стационара.</span>
                                    <button type="submit" class="save" id="wsBuildingSave"><i class="bi bi-plus-circle"></i> Создать корпус</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <section class="panel" data-panel="department">
            <div class="ws-sectionbar">
                <div class="ws-sectionbar__text">
                    <span class="ws-sectionbar__icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                    <div><h2>Отделения стационара</h2>
                        <p>Каждое отделение принадлежит только этому стационару. Корпус выбирается отдельно.</p></div>
                </div>
                <div class="ws-sectionbar__actions">
                    <span class="ws-counter">Всего: {{ $hospitalDepartments->count() }}</span>
                    <button type="button" class="ws-action" id="wsDepartmentNew"><i class="bi bi-plus-circle"></i> Новое отделение</button>
                </div>
            </div>
            <div class="ws-layout">
                <aside class="ws-directory" aria-label="Список отделений стационара">
                    <div class="ws-directory__top"><strong>Структура отделений</strong><small>Выбери отделение слева для редактирования</small></div>
                    <div class="ws-directory__items">
                        @forelse($hospitalDepartments as $department)
                            <button type="button" class="ws-item" data-department-choice
                                    data-id="{{ $department->id }}"
                                    data-number="{{ $department->number }}"
                                    data-name="{{ $department->name }}"
                                    data-profile="{{ $department->profile ?? '' }}"
                                    data-building-id="{{ $department->building_id ?? '' }}"
                                    data-sex-type="{{ $department->sex_type ?? 'mixed' }}"
                                    data-age-min="{{ $department->age_min_years ?? '' }}"
                                    data-age-max="{{ $department->age_max_years ?? '' }}"
                                    data-mode="{{ $department->inpatient_mode ?? 'round_the_clock' }}"
                                    data-description="{{ $department->description ?? '' }}"
                                    data-enabled="{{ $department->enabled ? '1' : '0' }}"
                                    data-sort-order="{{ $department->sort_order ?? 0 }}">
                                <span class="ws-number">№{{ $department->number }}</span>
                                <span class="ws-item__copy">
                                    <strong class="ws-item__name">{{ $department->name }}</strong>
                                    @if($department->profile)
                                        <span class="ws-item__meta"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i> {{ $department->profile }}</span>
                                    @endif
                                    <span class="ws-item__meta"><i class="bi bi-buildings" aria-hidden="true"></i>
                                        {{ $hospitalBuildingNames->get($department->building_id, 'Корпус не назначен') }}</span>
                                    <span class="ws-item__status {{ $department->enabled ? '' : 'ws-item__status--off' }}">
                                        <i class="bi {{ $department->enabled ? 'bi-check-circle' : 'bi-pause-circle' }}" aria-hidden="true"></i>
                                        {{ $department->enabled ? 'Активно' : 'Неактивно' }}
                                    </span>
                                </span>
                            </button>
                        @empty
                            <div class="ws-list-empty"><i class="bi bi-diagram-3" aria-hidden="true"></i>
                                <strong>Отделений пока нет</strong>
                                <p>Создай первое отделение справа. После сохранения оно появится в этом списке.</p>
                            </div>
                        @endforelse
                    </div>
                </aside>
                <div class="ws-editor-card">
                    <div class="ws-editor-card__head">
                        <span class="ws-editor-card__head-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                        <div><strong id="wsDepartmentEditorTitle">Новое отделение</strong>
                            <small id="wsDepartmentEditorSubtitle">Заполни реквизиты отделения выбранного стационара</small></div>
                    </div>
                    <div class="ws-editor-card__body">
                        <form id="wsDepartmentEditorForm" method="POST" action="{{ $saveUrl }}" novalidate>
                            @csrf
                            <input type="hidden" name="section" id="wsDepartmentAction" value="department_create">
                            <input type="hidden" name="department_id" id="wsDepartmentId" value="">
                            <div class="ws-form-grid">
                                <label class="field"><span class="field__label">Номер отделения <span class="required">*</span></span>
                                    <span class="ws-prefixed"><span aria-hidden="true">№</span>
                                        <input type="text" id="wsDepartmentNumber" name="number" maxlength="50" placeholder="1" required>
                                    </span>
                                    <span class="field__help">Введи только номер, без символа №.</span>
                                </label>
                                <label class="field"><span class="field__label">Название отделения <span class="required">*</span></span>
                                    <input class="control" id="wsDepartmentName" name="name" maxlength="255" placeholder="Отделение первичного эпизода" required>
                                </label>
                                <div class="field">
                                    <label class="field__label" for="wsDepartmentProfile">Профиль отделения</label>
                                    <input class="control" id="wsDepartmentProfile" name="profile" maxlength="255" placeholder="Например: Геронтологический">
                                    <div class="ws-profile-presets" role="group" aria-label="Готовые профили отделений">
                                        <span class="ws-profile-presets__hint">Готовые варианты — заполняют название и профиль:</span>
                                        <button type="button" class="ws-profile-preset" data-department-profile-preset="gerontological" aria-pressed="false"><i class="bi bi-person-heart" aria-hidden="true"></i> Геронтологическое</button>
                                        <button type="button" class="ws-profile-preset" data-department-profile-preset="psychosomatic" aria-pressed="false"><i class="bi bi-heart-pulse" aria-hidden="true"></i> Психосоматическое</button>
                                        <button type="button" class="ws-profile-preset" data-department-profile-preset="narcological" aria-pressed="false"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i> Наркологическое</button>
                                        <button type="button" class="ws-profile-preset" data-department-profile-preset="children" aria-pressed="false"><i class="bi bi-balloon" aria-hidden="true"></i> Детское · 0–7</button>
                                        <button type="button" class="ws-profile-preset" data-department-profile-preset="adolescents" aria-pressed="false"><i class="bi bi-people" aria-hidden="true"></i> Подростковое · 8–15</button>
                                        <button type="button" class="ws-profile-preset" data-department-profile-preset="adults" aria-pressed="false"><i class="bi bi-person" aria-hidden="true"></i> Взрослое · 16+</button>
                                    </div>
                                    <span class="field__help">Название можно изменить. Для детского профиля допускаются 0–7 лет, подросткового — 8–15 лет, взрослого — от 16. Пол выбирается отдельно. Клинический профиль сам по себе не проверяет диагноз.</span>
                                </div>
                                <label class="field"><span class="field__label">Корпус</span>
                                    <select class="control" id="wsDepartmentBuilding" name="building_id">
                                        <option value="">Корпус не назначен</option>
                                        @foreach($hospitalBuildings as $building)
                                            <option value="{{ $building->id }}" data-enabled="{{ $building->enabled ? '1' : '0' }}">№{{ $building->number }} — {{ $building->name }}{{ $building->enabled ? '' : ' (неактивен)' }}</option>
                                        @endforeach
                                    </select>
                                    <span class="field__help">Только корпуса этого стационара. <button type="button" class="ws-info-link" data-goto="building">Управлять корпусами</button></span>
                                </label>
                                <div class="field field--wide ws-age-panel" aria-labelledby="wsAgeHeading">
                                    <strong id="wsAgeHeading" class="field__label"><i class="bi bi-person-check"></i> Возраст при госпитализации</strong>
                                    <div class="ws-age-row">
                                        <label>Минимальный возраст <input class="control" type="number" id="wsDepartmentAgeMin" name="age_min_years" min="0" max="125" placeholder="Не ограничен"></label>
                                        <label>Максимальный возраст <input class="control" type="number" id="wsDepartmentAgeMax" name="age_max_years" min="0" max="125" placeholder="Не ограничен"></label>
                                    </div>
                                    <span class="field__help">Обе границы включительно. Пустое поле — без ограничения. Для детского, подросткового и взрослого профилей используются фиксированные возрастные границы.</span>
                                </div>
                                <label class="field"><span class="field__label">Тип размещения</span>
                                    <select class="control" id="wsDepartmentSexType" name="sex_type">
                                        <option value="mixed">Смешанное</option>
                                        <option value="male">Мужское</option>
                                        <option value="female">Женское</option>
                                    </select>
                                </label>
                                <label class="field"><span class="field__label">Режим отделения</span>
                                    <select class="control" id="wsDepartmentMode" name="inpatient_mode">
                                        <option value="round_the_clock">Круглосуточный</option>
                                        <option value="day">Дневной</option>
                                        <option value="night">Ночной</option>
                                    </select>
                                </label>
                                <label class="field field--wide"><span class="field__label">Описание отделения</span>
                                    <textarea class="control" id="wsDepartmentDescription" name="description" maxlength="2000" placeholder="Назначение и особенности отделения"></textarea>
                                </label>
                            </div>
                            <div class="ws-check-row">
                                <label class="ws-check"><input type="hidden" name="enabled" value="0">
                                    <input type="checkbox" id="wsDepartmentEnabled" name="enabled" value="1"> Отделение активно</label>
                                <label class="ws-sort">Порядок сортировки
                                    <input class="control" id="wsDepartmentSortOrder" name="sort_order" type="number" value="0" min="0" max="100000"></label>
                            </div>
                            <div class="ws-hint"><i class="bi bi-info-circle"></i>
                                <span>Новые отделения создаются неактивными. Возрастные ограничения, персонал и койки настраиваются отдельно до начала госпитализаций.</span>
                            </div>
                            <div class="submitbar"><span class="submitbar__note">Изменения сохраняются только в выбранном стационаре. После сохранения ты останешься здесь.</span>
                                <button class="save" type="submit" id="wsDepartmentSave"><i class="bi bi-plus-circle"></i> Создать отделение</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="ws-footnote"><i class="bi bi-shield-check"></i>
                <span>Корпуса и отделения этого стационара не влияют на кабинеты, здания и тип исходной амбулатории.</span>
            </div>
        </section>

        <section class="panel" data-panel="room">
            <div class="card">
                <div class="card__head editor-heading">
                    <div class="card__icon"><i class="bi bi-door-closed"></i></div>
                    <div><div class="card__title">Палаты выбранного стационара</div><div class="card__subtitle">Создание палат и изменение их реквизитов</div></div>
                    <span class="pill editor-counter">Всего: {{ $hospitalRooms->count() }}</span>
                </div>
                @if($hospitalDepartments->isEmpty())
                    <div class="editor-section"><div class="empty"><i class="bi bi-info-circle"></i> Чтобы создать палату, сначала создайте хотя бы одно отделение в разделе «Отделения».</div></div>
                @else
                    <div class="editor-tabs" aria-label="Действия с палатами">
                        <button type="button" class="editor-switch is-active" data-editor-switch="room" data-mode="create" aria-pressed="true"><i class="bi bi-plus-circle"></i> Создать палату</button>
                        @if($hospitalRooms->isNotEmpty())
                            <button type="button" class="editor-switch" data-editor-switch="room" data-mode="edit" aria-pressed="false"><i class="bi bi-pencil-square"></i> Изменить существующую</button>
                        @endif
                    </div>
                    <div class="editor-section" data-editor-section="room" data-mode="create">
                        <form id="roomCreateForm" method="post" action="{{ $saveUrl }}" novalidate>
                            @csrf
                            <input type="hidden" name="section" value="room_create">
                            <label class="field"><span class="field__label">Отделение <span class="required">*</span></span>
                                <select class="control" name="department_id" required>
                                    <option value="">Выбери отделение</option>
                                    @foreach($hospitalDepartments as $department)
                                        <option value="{{ $department->id }}">№{{ $department->number }} — {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="two" style="margin-top:14px"><label class="field"><span class="field__label">Номер палаты <span class="required">*</span></span><input class="control" name="number" maxlength="50" required placeholder="Например, 101"></label><label class="field"><span class="field__label">Этаж</span><input class="control" name="floor" maxlength="50" placeholder="Например, 1"></label></div>
                            <div class="two" style="margin-top:14px"><label class="field"><span class="field__label">Название / назначение</span><input class="control" name="name" maxlength="255" placeholder="Например, палата наблюдения"></label><label class="field"><span class="field__label">Порядок сортировки</span><input class="control" type="number" name="sort_order" min="0" max="100000" value="0"></label></div>
                            <p class="editor-note"><i class="bi bi-shield-check"></i> Создание палаты не создаёт койки и не меняет размещение пациентов. Конструктор койко-мест и схем палат подключается отдельно.</p>
                            <div class="submitbar"><span class="submitbar__note">Новая палата получит clinic_id стационара и department_id выбранного отделения.</span><button class="save" type="submit"><i class="bi bi-plus-circle"></i> Создать палату</button></div>
                        </form>
                    </div>
                    @if($hospitalRooms->isNotEmpty())
                        <div class="editor-section" data-editor-section="room" data-mode="edit" hidden>
                            <form id="roomForm" method="post" action="{{ $saveUrl }}" novalidate>
                                @csrf
                                <input type="hidden" name="section" value="room">
                                <label class="field"><span class="field__label">Палата</span><select class="control" id="wsRoomId" name="room_id" required>
                                    <option value="">Выбери палату</option>
                                    @foreach($hospitalRooms as $room)
                                        <option value="{{ $room->id }}" data-number="{{ $room->number }}" data-name="{{ $room->name }}" data-floor="{{ $room->floor }}">{{ $room->department?->name ?? 'Без отделения' }} · №{{ $room->number }}</option>
                                    @endforeach
                                </select></label>
                                <div class="two" style="margin-top:14px"><label class="field"><span class="field__label">Номер</span><input class="control" id="wsRoomNumber" name="number" maxlength="50" required></label><label class="field"><span class="field__label">Этаж</span><input class="control" id="wsRoomFloor" name="floor" maxlength="50"></label></div>
                                <label class="field" style="margin-top:14px"><span class="field__label">Название / назначение палаты</span><input class="control" id="wsRoomName" name="name" maxlength="255"></label>
                                <div class="submitbar"><span class="submitbar__note">Койки, схемы палат и прикрепления пациентов не изменяются.</span><button class="save" type="submit"><i class="bi bi-check-circle"></i> Сохранить палату</button></div>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </main>
    </div>
    <script>
    (() => {
        'use strict';
        const id = @json($hospital->id);
        const existingPhones = @json($hospitalPhones);
        const apiAddress = @json($addressApiUrl);
        const alertBox = document.getElementById('workspaceAlert');
        const tabs = [...document.querySelectorAll('.nh-temp-nav [data-tab]')];
        const panels = [...document.querySelectorAll('[data-panel]')];
        const showAlert = (message, kind = 'error') => {
            alertBox.className = `alert alert--${kind} visible`;
            alertBox.textContent = message;
            alertBox.scrollIntoView({behavior:'smooth',block:'start'});
        };
        const resetAlert = () => { alertBox.className = 'alert'; alertBox.textContent = ''; };
        const availableTabs = new Set(tabs.map(tab => tab.dataset.tab));
        const flashKey = `norhnaim:hospital-workspace:save:${id}`;
        const activateTab = (key, saveHash = true) => {
            const active = availableTabs.has(key) ? key : 'general';
            tabs.forEach(item => {
                const selected = item.dataset.tab === active;
                item.classList.toggle('is-active', selected);
                if (selected) item.setAttribute('aria-current', 'page');
                else item.removeAttribute('aria-current');
            });
            document.querySelectorAll('.nh-temp-nav [data-nav-group]').forEach(group => {
                group.classList.toggle('is-active', group.dataset.navGroup === active);
            });
            panels.forEach(panel => panel.classList.toggle('is-active', panel.dataset.panel === active));
            if (saveHash) history.replaceState(null, '', `#ws-${active}`);
            resetAlert();
            return active;
        };
        tabs.forEach(tab => tab.addEventListener('click', event => {
            // Внутри рабочего окна ссылки меняют вкладки без повторной загрузки.
            // На оригинальных страницах те же href возвращают в рабочее окно.
            event.preventDefault();
            activateTab(tab.dataset.tab);
            const group = tab.closest('.dropdown');
            const trigger = group?.querySelector('[data-bs-toggle="dropdown"]');
            if (trigger && window.bootstrap?.Dropdown) {
                window.bootstrap.Dropdown.getOrCreateInstance(trigger).hide();
            }
            if (window.matchMedia('(max-width: 1199.98px)').matches && window.bootstrap?.Collapse) {
                const collapse = document.getElementById('nhTempStationNav');
                if (collapse) window.bootstrap.Collapse.getOrCreateInstance(collapse, {toggle:false}).hide();
            }
            window.scrollTo({top:0,behavior:'instant'});
        }));
        window.addEventListener('hashchange', () => {
            activateTab(location.hash.replace(/^#ws-/, ''), false);
        });
        document.querySelectorAll('[data-goto]').forEach(link => {
            link.addEventListener('click', () => {
                activateTab(link.dataset.goto);
                window.scrollTo({top:0,behavior:'instant'});
            });
        });
        activateTab(location.hash.replace(/^#ws-/, ''), false);


        // Интервалы посещений доступны только в круглосуточном стационаре.
        // Смена режима переключает готовые часы, не меняя clinic_type амбулатории.
        const scheduleForm = document.getElementById('scheduleForm');
        const scheduleGrid = document.getElementById('wsScheduleGrid');
        const workingCard = document.getElementById('wsWorkingHoursCard');
        const roundCard = document.getElementById('wsRoundClockCard');
        const visitingCard = document.getElementById('wsVisitingHoursCard');
        const workingSubtitle = document.getElementById('wsWorkingHoursSubtitle');
        const modeRadios = [...scheduleForm.querySelectorAll('input[name="inpatient_mode"]')];
        const workRows = [...workingCard.querySelectorAll('tbody tr')];
        const visitRows = [...visitingCard.querySelectorAll('tbody tr')];
        const isTime = value => /^([01]\d|2[0-3]):[0-5]\d$/.test(value);
        const getFields = row => ({
            enabled: row.querySelector('input[type="checkbox"]'),
            from: row.querySelector('input[name$="[from]"]'),
            to: row.querySelector('input[name$="[to]"]'),
        });
        const syncVisitRow = row => {
            const fields = getFields(row);
            fields.enabled.checked = isTime(fields.from.value) && isTime(fields.to.value);
        };
        visitRows.forEach(row => {
            const fields = getFields(row);
            fields.from.addEventListener('input', () => syncVisitRow(row));
            fields.to.addEventListener('input', () => syncVisitRow(row));
            fields.from.addEventListener('change', () => syncVisitRow(row));
            fields.to.addEventListener('change', () => syncVisitRow(row));
            fields.enabled.addEventListener('change', () => {
                // Включать разрешение без обоих времён нельзя.
                if (!fields.enabled.checked) {
                    fields.from.value = '';
                    fields.to.value = '';
                } else if (!isTime(fields.from.value)) {
                    fields.enabled.checked = false;
                    fields.from.focus();
                } else if (!isTime(fields.to.value)) {
                    fields.enabled.checked = false;
                    fields.to.focus();
                }
            });
            syncVisitRow(row);
        });

        const captureWorking = () => workRows.map(row => {
            const fields = getFields(row);
            return {enabled: fields.enabled.checked, from: fields.from.value, to: fields.to.value};
        });
        const applyWorking = draft => workRows.forEach((row, index) => {
            const fields = getFields(row);
            const value = draft[index];
            fields.enabled.checked = !!value.enabled;
            fields.from.value = value.from;
            fields.to.value = value.to;
        });
        const drafts = {};
        const preset = (mode, reference = null) => workRows.map((row, index) => ({
            // Не меняем пользовательский выбор рабочих дней при смене формата.
            enabled: reference ? reference[index].enabled : index < 5,
            from: mode === 'night' ? '20:00' : '08:00',
            to: mode === 'night' ? '08:00' : '20:00',
        }));
        let currentMode = modeRadios.find(input => input.checked)?.value || 'round_the_clock';
        if (currentMode !== 'round_the_clock') drafts[currentMode] = captureWorking();

        const renderMode = nextMode => {
            if (currentMode !== nextMode) {
                if (currentMode !== 'round_the_clock') drafts[currentMode] = captureWorking();
                if (nextMode !== 'round_the_clock') {
                    const reference = currentMode !== 'round_the_clock' ? drafts[currentMode] : null;
                    applyWorking(drafts[nextMode] || preset(nextMode, reference));
                }
            }
            currentMode = nextMode;
            const round = nextMode === 'round_the_clock';
            workingCard.hidden = round;
            roundCard.hidden = !round;
            visitingCard.hidden = !round;
            scheduleGrid.classList.toggle('is-single', !round);
            workingSubtitle.textContent = nextMode === 'night'
                ? 'Ночные смены переходят через полночь, например 20:00–08:00'
                : 'Дневные смены, например 08:00–20:00';
            if (round) visitRows.forEach(syncVisitRow);
        };
        modeRadios.forEach(input => input.addEventListener('change', () => {
            if (input.checked) renderMode(input.value);
        }));
        renderMode(currentMode);

        // Переходим из "С" в "До", а затем к началу следующего дня.
        // Небольшая задержка предотвращает переключение посреди набора минут.
        const timeInputs = [...scheduleForm.querySelectorAll('input[type="time"]')];
        const focusNextTime = input => {
            if (!isTime(input.value)) return;
            const row = input.closest('tr');
            const fields = getFields(row);
            let next = input === fields.from ? fields.to : null;
            if (!next) {
                const card = input.closest('.card');
                const rows = [...card.querySelectorAll('tbody tr')];
                for (const later of rows.slice(rows.indexOf(row) + 1)) {
                    const candidate = getFields(later).from;
                    if (!candidate.disabled && !candidate.closest('[hidden]')) {
                        next = candidate;
                        break;
                    }
                }
            }
            if (next && !next.closest('[hidden]')) next.focus();
        };
        timeInputs.forEach(input => {
            let timer = null;
            const clear = () => { if (timer !== null) clearTimeout(timer); timer = null; };
            input.addEventListener('input', () => {
                clear();
                if (!isTime(input.value) || document.activeElement !== input) return;
                const finalValue = input.value;
                timer = setTimeout(() => {
                    if (document.activeElement === input && input.value === finalValue) focusNextTime(input);
                }, 550);
            });
            input.addEventListener('change', () => {
                clear();
                if (document.activeElement === input && isTime(input.value)) focusNextTime(input);
            });
            input.addEventListener('keydown', event => {
                if (event.key !== 'Enter' || !isTime(input.value)) return;
                event.preventDefault(); clear(); focusNextTime(input);
            });
            input.addEventListener('blur', clear);
        });

        // Все сохранения используют JSON и отдельный URL выбранного стационара.
        // HTTP 422/403/500 оставляют окно открытым, независимо от формата ответа.
        document.querySelectorAll('main form').forEach(form => {
            form.addEventListener('submit', async event => {
                event.preventDefault(); resetAlert();
                const button = form.querySelector('button[type="submit"]');
                if (button) { button.disabled = true; button.setAttribute('aria-busy','true'); }
                try {
                    const response = await fetch(form.action, {
                        method:'POST', body:new FormData(form), credentials:'same-origin',
                        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    });
                    let data = null;
                    try { data = await response.json(); } catch (_) { /* html error instead of JSON */ }
                    if (response.ok && data && data.success === true) {
                        // Сохранение не закрывает режим, но перечитывает свежие
                        // списки отделений и корпусов без ручного обновления.
                        const active = tabs.find(t => t.classList.contains('is-active'))?.dataset.tab || 'general';
                        const notice = {
                            tab: active,
                            message: String(data.message || 'Изменения сохранены.'),
                            section: String(data.section || ''),
                            recordId: Number(data.record_id || 0),
                        };
                        try { sessionStorage.setItem(flashKey, JSON.stringify(notice)); } catch (_) {}
                        window.location.reload();
                        return;
                    }
                    const first = data && data.errors
                        ? Object.values(data.errors).flat().filter(Boolean).join(' ')
                        : (data && data.message) || `Ошибка сохранения (HTTP ${response.status})`;
                    showAlert(first || 'Сохранение не выполнено. Проверьте данные и повторите попытку.');
                } catch (_) { showAlert('Нет соединения с сервером. Изменения не сохранены.'); }
                finally { if (button) { button.disabled = false; button.removeAttribute('aria-busy'); } }
            });
        });

        const phonesHost = document.getElementById('wsPhones');
        let phoneIndex = 0;
        const addPhone = (phone = '', label = '') => {
            const row = document.createElement('div'); row.className = 'phone-row';
            const number = document.createElement('input'); number.className = 'control'; number.placeholder = '+7 (___) ___-__-__'; number.maxLength = 32;
            const caption = document.createElement('input'); caption.className = 'control phone-label'; caption.placeholder = 'Например, приёмное отделение'; caption.maxLength = 120;
            const remove = document.createElement('button'); remove.type='button'; remove.title='Удалить номер'; remove.setAttribute('aria-label','Удалить номер');
            const icon = document.createElement('i'); icon.className = 'bi bi-trash'; remove.appendChild(icon);
            remove.addEventListener('click', () => { row.remove(); reindexPhones(); });
            number.value = phone; caption.value = label; row.append(number,caption,remove); phonesHost.appendChild(row); reindexPhones();
        };
        const reindexPhones = () => { [...phonesHost.children].forEach((row,index) => {
            const inputs = row.querySelectorAll('input'); inputs[0].name = `phones[${index}][phone]`; inputs[1].name = `phones[${index}][label]`;
        }); };
        existingPhones.forEach(row => addPhone(row.phone || '',row.label || ''));
        document.getElementById('wsAddPhone').addEventListener('click', () => { if (phonesHost.children.length < 20) addPhone(); });

        // Режимы «Создать / Изменить» относятся только к текущей вкладке.
        // Кнопки действуют одинаково и при пустых списках, и после их заполнения.
        document.querySelectorAll('[data-editor-switch]').forEach(button => {
            button.addEventListener('click', () => {
                const section = button.dataset.editorSwitch;
                const mode = button.dataset.mode;
                document.querySelectorAll(`[data-editor-switch="${section}"]`).forEach(item => {
                    const active = item === button;
                    item.classList.toggle('is-active', active);
                    item.setAttribute('aria-pressed', String(active));
                });
                document.querySelectorAll(`[data-editor-section="${section}"]`).forEach(item => {
                    item.hidden = item.dataset.mode !== mode;
                });
                resetAlert();
            });
        });

        // Корпуса -- только hospital_buildings с clinic_id выбранного стационара.
        const buildingForm = document.getElementById('wsBuildingForm');
        const buildingChoices = [...document.querySelectorAll('[data-building-choice]')];
        const buildingNew = document.getElementById('wsBuildingNew');
        const buildingInputs = {
            action:document.getElementById('wsBuildingAction'),
            id:document.getElementById('wsBuildingId'),
            number:document.getElementById('wsBuildingNumber'),
            name:document.getElementById('wsBuildingName'),
            address:document.getElementById('wsBuildingAddress'),
            enabled:document.getElementById('wsBuildingEnabled'),
            title:document.getElementById('wsBuildingTitle'),
            subtitle:document.getElementById('wsBuildingSubtitle'),
            save:document.getElementById('wsBuildingSave'),
        };
        const editBuilding = (choice = null) => {
            if (!buildingForm) return;
            buildingForm.reset();
            buildingInputs.action.value = choice ? 'building' : 'building_create';
            buildingInputs.id.value = choice?.dataset.id || '';
            buildingInputs.number.value = (choice?.dataset.number || '').replace(/^№\s*/, '');
            buildingInputs.name.value = choice?.dataset.name || '';
            buildingInputs.address.value = choice?.dataset.address || '';
            buildingInputs.enabled.checked = choice ? choice.dataset.enabled === '1' : true;
            buildingInputs.title.textContent = choice ? 'Редактировать корпус' : 'Новый корпус';
            buildingInputs.subtitle.textContent = choice ? 'Изменяются данные только выбранного корпуса стационара' : 'Заполни сведения для создания корпуса стационара';
            buildingInputs.save.innerHTML = choice ? '<i class="bi bi-check-circle"></i> Сохранить корпус' : '<i class="bi bi-plus-circle"></i> Создать корпус';
            buildingChoices.forEach(item => item.classList.toggle('is-active', item === choice));
        };
        buildingNew?.addEventListener('click', () => { editBuilding(); buildingInputs.number?.focus(); });
        buildingChoices.forEach(choice => choice.addEventListener('click', () => editBuilding(choice)));

        // Автопоиск корпуса использует тот же адресный API, что и основной адрес стационара.
        // Корпуса и амбулатория не меняются, пока форму корпуса не сохранят отдельно.
        (() => {
            const input = buildingInputs.address;
            const results = document.getElementById('wsBuildingAddressSuggestions');
            if (!input || !results || !apiAddress) return;

            let debounce = null;
            let pending = null;
            let serial = 0;
            const hide = () => {
                results.hidden = true;
                input.setAttribute('aria-expanded', 'false');
            };
            const cancel = () => {
                clearTimeout(debounce);
                if (pending) pending.abort();
                pending = null;
                serial++;
            };
            const choose = (item) => {
                cancel();
                input.value = String(item.value || item.unrestricted_value || '');
                hide();
                results.replaceChildren();
                input.focus();
            };

            input.addEventListener('input', () => {
                cancel();
                hide();
                results.replaceChildren();
                const query = input.value.trim();
                if (query.length < 3) return;
                const requestNumber = serial;
                debounce = setTimeout(async () => {
                    const controller = new AbortController();
                    pending = controller;
                    try {
                        const url = new URL(apiAddress, window.location.href);
                        url.searchParams.set('query', query);
                        const response = await fetch(url.toString(), {
                            credentials: 'same-origin',
                            signal: controller.signal,
                            headers: { Accept: 'application/json' },
                        });
                        if (!response.ok) throw new Error('Address suggestions unavailable');
                        const payload = await response.json();
                        if (controller.signal.aborted || requestNumber !== serial || input.value.trim() !== query) return;
                        const items = Array.isArray(payload.suggestions) ? payload.suggestions : [];
                        results.replaceChildren();
                        items.slice(0, 8).forEach(item => {
                            const value = String(item.value || item.unrestricted_value || '').trim();
                            if (!value) return;
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.setAttribute('role', 'option');
                            const icon = document.createElement('i');
                            icon.className = 'bi bi-geo-alt';
                            icon.setAttribute('aria-hidden', 'true');
                            const text = document.createElement('span');
                            text.textContent = value;
                            button.append(icon, text);
                            button.addEventListener('mousedown', event => event.preventDefault());
                            button.addEventListener('click', () => choose(item));
                            results.append(button);
                        });
                        results.hidden = results.childElementCount === 0;
                        input.setAttribute('aria-expanded', String(!results.hidden));
                    } catch (error) {
                        if (!controller.signal.aborted) hide(); // ручной ввод остаётся доступен
                    } finally {
                        if (pending === controller) pending = null;
                    }
                }, 350);
            });
            input.addEventListener('keydown', event => {
                if (event.key === 'Escape') { cancel(); hide(); }
                if (event.key === 'ArrowDown' && !results.hidden) {
                    const first = results.querySelector('button');
                    if (first) { event.preventDefault(); first.focus(); }
                }
                if (event.key === 'Enter' && !results.hidden) {
                    const first = results.querySelector('button');
                    if (first) { event.preventDefault(); first.click(); }
                }
            });
            results.addEventListener('keydown', event => {
                const buttons = [...results.querySelectorAll('button')];
                const index = buttons.indexOf(document.activeElement);
                if (event.key === 'Escape') { event.preventDefault(); hide(); input.focus(); }
                if (event.key === 'ArrowDown' && index >= 0) {
                    event.preventDefault(); buttons[Math.min(index + 1, buttons.length - 1)]?.focus();
                }
                if (event.key === 'ArrowUp' && index >= 0) {
                    event.preventDefault(); (index ? buttons[index - 1] : input).focus();
                }
            });
            document.addEventListener('pointerdown', event => {
                if (!results.contains(event.target) && event.target !== input) hide();
            });
            buildingForm.addEventListener('reset', () => { cancel(); hide(); results.replaceChildren(); });
            buildingForm.addEventListener('submit', hide);
        })();

        const departmentForm = document.getElementById('wsDepartmentEditorForm');
        const departmentChoices = [...document.querySelectorAll('[data-department-choice]')];
        const depInputs = {
            action:document.getElementById('wsDepartmentAction'),
            id:document.getElementById('wsDepartmentId'),
            number:document.getElementById('wsDepartmentNumber'),
            name:document.getElementById('wsDepartmentName'),
            profile:document.getElementById('wsDepartmentProfile'),
            building:document.getElementById('wsDepartmentBuilding'),
            sex:document.getElementById('wsDepartmentSexType'),
            ageMin:document.getElementById('wsDepartmentAgeMin'),
            ageMax:document.getElementById('wsDepartmentAgeMax'),
            mode:document.getElementById('wsDepartmentMode'),
            desc:document.getElementById('wsDepartmentDescription'),
            enabled:document.getElementById('wsDepartmentEnabled'),
            sort:document.getElementById('wsDepartmentSortOrder'),
            title:document.getElementById('wsDepartmentEditorTitle'),
            subtitle:document.getElementById('wsDepartmentEditorSubtitle'),
            save:document.getElementById('wsDepartmentSave'),
        };
        // Пресеты только подставляют текст. Пол, корпус, возраст и режим не меняются.
        const departmentProfileTemplates = {
            gerontological: { name:'Геронтологическое отделение', profile:'Геронтологический' },
            psychosomatic: { name:'Психосоматическое отделение', profile:'Психосоматический' },
            narcological: { name:'Наркологическое отделение', profile:'Наркологический' },
            children: { name:'Детское отделение', profile:'Детский' },
            adolescents: { name:'Подростковое отделение', profile:'Подростковый' },
            adults: { name:'Взрослое отделение', profile:'Взрослый' },
        };
        const departmentProfileButtons = Array.from(document.querySelectorAll('[data-department-profile-preset]'));
        const syncDepartmentProfileButtons = () => {
            const profile = String(depInputs.profile?.value || '').trim();
            departmentProfileButtons.forEach(button => {
                const preset = departmentProfileTemplates[button.dataset.departmentProfilePreset];
                const selected = Boolean(preset && profile === preset.profile);
                button.classList.toggle('is-selected', selected);
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
        };
        departmentProfileButtons.forEach(button => button.addEventListener('click', () => {
            const preset = departmentProfileTemplates[button.dataset.departmentProfilePreset];
            if (!preset || !departmentForm) return;
            depInputs.name.value = preset.name;
            depInputs.profile.value = preset.profile;
            syncAgeFields(true);
            syncDepartmentProfileButtons();
            depInputs.name.focus();
        }));
        const agePresets = {'детский':[0,7], 'детская психиатрия':[0,7], 'подростковый':[8,15], 'подростковая психиатрия':[8,15], 'взрослый':[16,null], 'взрослая психиатрия':[16,null]};
        const syncAgeFields = (fromPreset = false) => {
            const profile = String(depInputs.profile?.value || '').trim().toLocaleLowerCase('ru');
            const preset = agePresets[profile];
            if (preset && (fromPreset || depInputs.ageMin.value === '' && depInputs.ageMax.value === '')) {
                depInputs.ageMin.value = String(preset[0]);
                depInputs.ageMax.value = preset[1] === null ? '' : String(preset[1]);
            }
            depInputs.ageMin.readOnly = Boolean(preset);
            depInputs.ageMax.readOnly = Boolean(preset);
        };
        depInputs.profile?.addEventListener('input', () => { syncDepartmentProfileButtons(); syncAgeFields(true); });
        const editDepartment = (choice = null) => {
            departmentForm.reset();
            depInputs.action.value = choice ? 'department' : 'department_create';
            depInputs.id.value = choice?.dataset.id || '';
            depInputs.number.value = (choice?.dataset.number || '').replace(/^№\s*/, '');
            depInputs.name.value = choice?.dataset.name || '';
            depInputs.profile.value = choice?.dataset.profile || '';
            depInputs.ageMin.value = choice?.dataset.ageMin ?? '';
            depInputs.ageMax.value = choice?.dataset.ageMax ?? '';
            syncAgeFields();
            const linkedBuildingId = choice?.dataset.buildingId || '';
            [...depInputs.building.options].forEach(option => {
                option.disabled = Boolean(option.value && option.dataset.enabled === '0'
                    && option.value !== linkedBuildingId);
            });
            depInputs.building.value = linkedBuildingId;
            depInputs.sex.value = choice?.dataset.sexType || 'mixed';
            depInputs.mode.value = choice?.dataset.mode || @json($hospital->inpatient_mode ?? 'round_the_clock');
            depInputs.desc.value = choice?.dataset.description || '';
            depInputs.enabled.checked = choice ? choice.dataset.enabled === '1' : false;
            depInputs.sort.value = choice?.dataset.sortOrder || '0';
            depInputs.title.textContent = choice ? 'Редактирование отделения №' + depInputs.number.value : 'Новое отделение';
            depInputs.subtitle.textContent = choice ? 'Редактируются реквизиты выбранного отделения' : 'Заполни реквизиты отделения выбранного стационара';
            depInputs.save.innerHTML = choice ? '<i class="bi bi-check-circle"></i> Сохранить отделение' : '<i class="bi bi-plus-circle"></i> Создать отделение';
            departmentChoices.forEach(item => item.classList.toggle('is-active', item === choice));
            syncDepartmentProfileButtons();
        };
        document.getElementById('wsDepartmentNew')?.addEventListener('click', () => {
            editDepartment(); depInputs.number.focus();
        });
        departmentChoices.forEach(choice => choice.addEventListener('click', () => editDepartment(choice)));
        editDepartment();
        if (buildingForm) editBuilding();

        // Одноразовое уведомление после перезагрузки и возврат к нужной вкладке.
        let flash = null;
        try {
            const value = sessionStorage.getItem(flashKey);
            if (value) { flash = JSON.parse(value); sessionStorage.removeItem(flashKey); }
        } catch (_) {}
        if (flash) {
            activateTab(flash.tab);
            if (flash.recordId) {
                if (flash.section === 'department_create' || flash.section === 'department') {
                    const choice = departmentChoices.find(item => Number(item.dataset.id) === flash.recordId);
                    if (choice) editDepartment(choice);
                } else if (flash.section === 'building_create' || flash.section === 'building') {
                    const choice = buildingChoices.find(item => Number(item.dataset.id) === flash.recordId);
                    if (choice) editBuilding(choice);
                } else if (flash.section === 'room_create' || flash.section === 'room') {
                    const room = document.getElementById('wsRoomId');
                    if (room) {
                        room.value = String(flash.recordId);
                        room.dispatchEvent(new Event('change'));
                    }
                }
            }
            showAlert(flash.message || 'Изменения сохранены.', 'ok');
        }
        const roomSelect = document.getElementById('wsRoomId');
        roomSelect?.addEventListener('change', () => { const selected = roomSelect.selectedOptions[0];
            document.getElementById('wsRoomNumber').value = selected?.dataset.number || '';
            document.getElementById('wsRoomName').value = selected?.dataset.name || '';
            document.getElementById('wsRoomFloor').value = selected?.dataset.floor || '';
        });

        const address = document.getElementById('wsAddress');
        const suggestions = document.getElementById('wsSuggestions');
        let addressTimer = null, addressRequest = null;
        address.addEventListener('input', () => {
            document.getElementById('wsCity').value = '';
            document.getElementById('wsLatitude').value = '';
            document.getElementById('wsLongitude').value = '';
            suggestions.hidden = true; clearTimeout(addressTimer);
            addressRequest?.abort(); addressRequest = null;
            const query = address.value.trim(); if (query.length < 3) return;
            addressTimer = setTimeout(async () => {
                const abort = new AbortController(); addressRequest = abort;
                try {
                    const response = await fetch(`${apiAddress}?query=${encodeURIComponent(query)}`, { credentials:'same-origin', signal:abort.signal,headers:{'Accept':'application/json'} });
                    if (!response.ok || abort.signal.aborted) return;
                    const data = await response.json(); if (abort.signal.aborted) return;
                    suggestions.replaceChildren();
                    const items = Array.isArray(data.suggestions) ? data.suggestions : [];
                    items.forEach(item => {
                        const button = document.createElement('button'); button.type='button';
                        button.textContent = item.value || item.unrestricted_value || '';
                        button.addEventListener('click', () => {
                            address.value = item.value || item.unrestricted_value || '';
                            document.getElementById('wsCity').value = item.city || '';
                            document.getElementById('wsLatitude').value = item.geo_lat || '';
                            document.getElementById('wsLongitude').value = item.geo_lon || '';
                            suggestions.hidden = true;
                        }); suggestions.append(button);
                    }); suggestions.hidden = items.length === 0;
                } catch (_) { suggestions.hidden = true; }
            },350);
        });
        document.addEventListener('click',event => { if (!suggestions.contains(event.target) && event.target !== address) suggestions.hidden = true; });
    })();
    </script>

@endsection
