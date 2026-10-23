@extends('doctors.settings')

@section('settings-title', 'Принадлежность стационаров')

@section('sub-main')
    <style>
        .nh-affiliations, .nh-affiliations * { box-sizing: border-box; }
        .nh-affiliations { min-height: calc(100vh - 68px); padding: 30px 28px 65px; background: #f4f8fa; color: #204254; }
        .nh-affiliations__shell { max-width: 1180px; margin: 0 auto; }
        .nh-affiliations__hero { position: relative; display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding: 30px 32px; border-radius: 22px; border: 1px solid #cfe9ee; background: linear-gradient(115deg, #e7f8f7, #f6fcfd); overflow: hidden; }
        .nh-affiliations__hero::after { content: ''; position: absolute; width: 230px; height: 230px; border-radius: 50%; border: 1px solid #cdebed; top: -125px; right: 10px; pointer-events: none; }
        .nh-affiliations__eyebrow { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 11px; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #077d8c; }
        .nh-affiliations__hero h1 { color: #163d51; font-size: clamp(24px, 2.5vw, 31px); font-weight: 850; margin: 0 0 8px; letter-spacing: -.025em; }
        .nh-affiliations__hero p { color: #5f7d8c; font-size: 13.5px; line-height: 1.65; max-width: 730px; margin: 0; }
        .nh-affiliations__hero-icon { display: grid; place-items: center; width: 68px; height: 68px; flex: 0 0 68px; border-radius: 18px; background: #fff; color: #068b99; font-size: 28px; box-shadow: 0 12px 30px #1b8c9c17; }
        .nh-affiliations__metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin: 18px 0; }
        .nh-affiliations__metric { border: 1px solid #dfebee; background: #fff; border-radius: 17px; padding: 19px 21px; box-shadow: 0 8px 25px #163f4d06; }
        .nh-affiliations__metric-label { color: #6d8795; font-size: 12px; font-weight: 700; }
        .nh-affiliations__metric-value { color: #153b4e; font-size: 30px; font-weight: 850; line-height: 1.15; margin-top: 8px; }
        .nh-affiliations__card { padding: 23px; background: #fff; border-radius: 20px; border: 1px solid #dfebee; box-shadow: 0 9px 30px #123e5008; }
        .nh-affiliations__card-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .nh-affiliations__card h2 { color: #1c4558; font-size: 18px; font-weight: 800; margin: 0; }
        .nh-affiliations__card-subtitle { color: #758c97; font-size: 12px; margin: 6px 0 0; }
        .nh-affiliations__search { min-height: 39px; width: min(290px,100%); border-radius: 11px; border: 1px solid #dbe8ed; background: #f9fcfd; padding: 7px 13px; color: #214a5c; font-size: 13px; }
        .nh-affiliations__row { display: flex; align-items: center; gap: 15px; padding: 18px 4px; border-top: 1px solid #eef2f4; }
        .nh-affiliations__row[hidden] { display: none; }
        .nh-affiliations__row-icon { display: grid; place-items: center; border-radius: 13px; background: #e5f7f8; color: #068d9c; width: 45px; height: 45px; flex: 0 0 45px; font-size: 21px; }
        .nh-affiliations__row-copy { flex: 1 1 260px; min-width: 0; }
        .nh-affiliations__hospital-name { margin-bottom: 6px; color: #1d4356; font-size: 15px; font-weight: 800; }
        .nh-affiliations__parent { display: inline-flex; align-items: center; gap: 7px; color: #477482; font-size: 12px; }
        .nh-affiliations__parent--empty { color: #a46c20; }
        .nh-affiliations__edit { min-height: 40px; white-space: nowrap; display: inline-flex; gap: 8px; align-items: center; border: 1px solid #b5e4e9; border-radius: 11px; background: #effbfc; color: #087987; padding: 8px 15px; font-size: 13px; font-weight: 800; transition: .15s; }
        .nh-affiliations__edit:hover { background: #dff6f8; }
        .nh-affiliations__actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
        .nh-affiliations__edit--primary { background: #098f9e; border-color: #098f9e; color: #fff; box-shadow: 0 5px 14px #04899a21; }
        .nh-affiliations__edit--primary:hover { background: #077987; border-color: #077987; color: #fff; }
        .nh-affiliations__row-mode { display: inline-flex; align-items: center; gap: 6px; margin-top: 7px; font-size: 11.5px; color: #7d929b; }
        .nh-affiliations__empty { text-align: center; padding: 40px; background: #f8fbfc; border-radius: 14px; color: #6d8796; }
        .nh-affiliations__notice { display: flex; gap: 11px; padding: 16px 18px; margin-top: 18px; border: 1px solid #d7e9ec; border-radius: 14px; background: #f0fafb; color: #4e7482; font-size: 12.5px; line-height: 1.6; }
        .nh-affiliations__notice i { font-size: 18px; color: #068b9c; }
        .nh-affiliations-modal .modal-dialog { max-width: 580px; }
        .nh-affiliations-modal .modal-content { border: 0; border-radius: 22px; overflow: visible; box-shadow: 0 32px 85px #0a304a3d; }
        .nh-affiliations-modal .modal-header { position: relative; display: flex; align-items: flex-start; padding: 23px 70px 23px 26px; border: 0; border-radius: 22px 22px 0 0; color: #fff; background: linear-gradient(125deg, #05778b, #0db4bf); }
        .nh-affiliations-modal__eyebrow { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 800; color: #cefafd; text-transform: uppercase; letter-spacing: .08em; }
        .nh-affiliations-modal .modal-title { font-size: 21px; font-weight: 850; }
        .nh-affiliations-modal .btn-close { position: absolute; top: 24px; right: 26px; left: auto; margin: 0; transform: none; filter: brightness(0) invert(1); opacity: .95; flex-shrink: 0; }
        .nh-affiliations-modal .modal-body { padding: 26px; background: #f8fbfc; }
        .nh-affiliations-modal__hospital { display: flex; gap: 12px; align-items: center; padding: 16px; margin-bottom: 22px; border: 1px solid #dbebee; border-radius: 14px; background: #fff; }
        .nh-affiliations-modal__hospital-icon { flex: 0 0 41px; width: 41px; height: 41px; display: grid; place-items: center; border-radius: 12px; background: #e8f8f9; color: #00899a; font-size: 21px; }
        .nh-affiliations-modal__hospital small { display: block; color: #718a95; font-size: 11px; margin-bottom: 4px; }
        .nh-affiliations-modal__hospital strong { display: block; color: #183e51; font-size: 15px; }
        .nh-affiliations-modal .form-label { display: block; font-weight: 800; color: #254d61; font-size: 13px; margin-bottom: 8px; }
        .nh-affiliations-modal .form-select { min-height: 45px; border: 1px solid #d3e2e8; border-radius: 11px; color: #234658; }
        .nh-affiliations-modal .select2-container .select2-selection--single { height: 45px; border-color: #d3e2e8; border-radius: 11px; display: flex; align-items: center; }
        .nh-affiliations-modal .select2-container .select2-selection__rendered { line-height: 43px; padding-left: 12px; font-size: 13px; }
        .nh-affiliations-modal .select2-container .select2-selection__arrow { height: 43px; }
        .nh-affiliations-modal__help { margin: 8px 0 0; color: #748b96; font-size: 12px; line-height: 1.55; }
        .nh-affiliations-modal .modal-footer { border-top: 1px solid #edf1f2; padding: 17px 26px; background: #fff; border-radius: 0 0 22px 22px; }
        .nh-affiliations-modal__save { display: inline-flex; align-items: center; gap: 8px; border: 0; border-radius: 11px; padding: 10px 17px; background: #069aaa; color: #fff; font-size: 13px; font-weight: 800; }
        .nh-affiliations-modal__save:hover { background: #047e8d; }

        /* Добавление стационара: только эта страница. */
        .nh-affiliations__hero-actions { position: relative; z-index: 1; display: flex; align-items: center; gap: 13px; flex: 0 0 auto; }
        .nh-affiliations__create { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 45px; padding: 11px 18px; border: 1px solid #067f91; border-radius: 13px; background: linear-gradient(135deg,#068c9e,#05a8b6); color: #fff; font-size: 13px; font-weight: 800; white-space: nowrap; box-shadow: 0 9px 20px #028fa529; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
        .nh-affiliations__create i { font-size: 17px; }
        .nh-affiliations__create:hover { color: #fff; background: linear-gradient(135deg,#067789,#0494a5); transform: translateY(-1px); box-shadow: 0 12px 25px #028fa53e; }
        .nh-affiliations__create:focus-visible { outline: 3px solid #7ee0e9; outline-offset: 3px; }
        .nh-hospital-create .modal-dialog { max-width: 740px; }
        .nh-hospital-create .modal-content { overflow: hidden; border: 0; border-radius: 23px; box-shadow: 0 36px 100px #0a304b52; }
        .nh-hospital-create__header { position: relative; overflow: hidden; display: flex; align-items: center; gap: 17px; padding: 26px 64px 26px 28px; color: #fff; background: linear-gradient(115deg,#066d83 0%,#078d9e 58%,#12b8bf 100%); border: 0; }
        .nh-hospital-create__header::after { content: ''; position: absolute; width: 240px; height: 240px; right: -95px; top: -155px; border: 1px solid #ffffff47; border-radius: 50%; pointer-events: none; }
        .nh-hospital-create__mark { flex: 0 0 52px; display: grid; place-items: center; width: 52px; height: 52px; border-radius: 16px; background: #ffffff2d; border: 1px solid #ffffff3f; font-size: 25px; }
        .nh-hospital-create__caption { margin: 0 0 5px; color: #c5f8fc; font-size: 10px; font-weight: 850; letter-spacing: .1em; text-transform: uppercase; }
        .nh-hospital-create .modal-title { margin: 0; color: #fff; font-size: clamp(20px,2vw,25px); font-weight: 850; letter-spacing: -.025em; }
        .nh-hospital-create__subtitle { margin: 6px 0 0; color: #d7f5f8; font-size: 12px; line-height: 1.5; }
        .nh-hospital-create .btn-close { position: absolute; top: 24px; right: 23px; z-index: 1; margin: 0; filter: brightness(0) invert(1); opacity: .95; }
        .nh-hospital-create .modal-body { padding: 26px 29px 22px; background: #f7fafc; }
        .nh-hospital-create__section { margin-bottom: 21px; }
        .nh-hospital-create__step { display: flex; align-items: center; gap: 9px; margin-bottom: 13px; color: #244c5d; font-size: 13px; font-weight: 850; }
        .nh-hospital-create__step-num { display: grid; place-items: center; width: 27px; height: 27px; border-radius: 9px; background: #dcf4f6; color: #078b9c; font-size: 12px; }
        .nh-hospital-create__parent { display: flex; gap: 12px; align-items: center; padding: 15px 16px; border: 1px solid #cde6e9; border-radius: 14px; background: #ecf9fa; }
        .nh-hospital-create__parent-icon { display: grid; place-items: center; width: 43px; height: 43px; flex: 0 0 43px; border: 1px solid #d1e8eb; border-radius: 12px; color: #008d9c; background: #fff; font-size: 19px; }
        .nh-hospital-create__parent-copy { flex: 1; min-width: 0; }
        .nh-hospital-create__parent-copy small { display: block; margin-bottom: 3px; color: #698c97; font-size: 11px; font-weight: 700; }
        .nh-hospital-create__parent-copy strong { display: block; color: #19465a; font-size: 14px; line-height: 1.4; overflow-wrap: anywhere; }
        .nh-hospital-create__parent-check { color: #058d86; font-size: 18px; }
        .nh-hospital-create__fields { display: grid; grid-template-columns: minmax(0,1fr); gap: 17px; }
        .nh-hospital-create__wide { grid-column: 1 / -1; }
        .nh-hospital-create .form-label { display: inline-flex; gap: 5px; align-items: center; margin-bottom: 7px; color: #29495c; font-size: 12px; font-weight: 800; }
        .nh-hospital-create__required { color: #d05252; }
        .nh-hospital-create .form-control { min-height: 45px; padding: 11px 13px; border: 1px solid #d5e3e8; border-radius: 11px; background: #fff; color: #19465a; font-size: 13px; transition: border-color .15s, box-shadow .15s; }
        .nh-hospital-create .form-control::placeholder { color: #9aafba; }
        .nh-hospital-create .form-control:focus { border-color: #25b2bd; box-shadow: 0 0 0 3px #0aa0b21c; }
        .nh-hospital-create textarea.form-control { min-height: 82px; resize: vertical; }
        .nh-hospital-create__hint { margin: 7px 0 0; color: #8096a1; font-size: 11px; line-height: 1.45; }
        /* Подсказки адресов из уже существующего API DaData/OSM; стили только модалки создания. */
        .nh-hospital-create__lookup { position: relative; }
        .nh-hospital-create__lookup.is-open { z-index: 15; }
        .nh-hospital-create__lookup .form-control { padding-right: 41px; }
        .nh-hospital-create__lookup-icon { position: absolute; top: 13px; right: 14px; color: #80a5b1; pointer-events: none; }
        .nh-hospital-create__suggestions { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 30; max-height: 225px; overflow-y: auto; border: 1px solid #c5e2e7; border-radius: 13px; background: #fff; box-shadow: 0 16px 38px #123c5040; padding: 5px; }
        .nh-hospital-create__suggestions[hidden] { display: none !important; }
        .nh-hospital-create__suggestion { display: flex; align-items: flex-start; width: 100%; gap: 10px; padding: 11px 10px; border: 0; border-radius: 9px; color: #1d4759; background: #fff; text-align: left; cursor: pointer; }
        .nh-hospital-create__suggestion:hover, .nh-hospital-create__suggestion.is-active { background: #e8f7f9; }
        .nh-hospital-create__suggestion > i { margin-top: 1px; color: #0a94a2; font-size: 16px; }
        .nh-hospital-create__suggestion-copy { min-width: 0; flex: 1 1 auto; }
        .nh-hospital-create__suggestion-copy strong { display: block; font-size: 12.5px; font-weight: 770; line-height: 1.45; }
        .nh-hospital-create__suggestion-copy small { display: block; margin-top: 3px; font-size: 11px; color: #8194a0; line-height: 1.4; }
        .nh-hospital-create__suggestion-message { display: flex; align-items: flex-start; gap: 9px; padding: 11px; color: #6b8793; font-size: 12px; line-height: 1.45; }
        .nh-hospital-create__suggestion-message i { color: #0a94a2; font-size: 16px; }
        .nh-hospital-create__address-info { display: flex; align-items: flex-start; gap: 9px; margin: 3px 0 0; padding: 11px 13px; border-radius: 11px; border: 1px solid #cde9df; background: #f1faf6; color: #28654e; }
        .nh-hospital-create__address-info[hidden] { display: none !important; }
        .nh-hospital-create__address-info > i { color: #118962; font-size: 17px; }
        .nh-hospital-create__address-info strong { display: block; font-size: 12px; font-weight: 800; }
        .nh-hospital-create__address-info small { display: block; font-size: 11px; margin-top: 3px; color: #497b69; line-height: 1.5; }
        .nh-hospital-create__next { display: flex; align-items: flex-start; gap: 11px; margin-top: 3px; padding: 13px 15px; border: 1px solid #d7e9ee; border-radius: 12px; background: #eef6fa; }
        .nh-hospital-create__next i { color: #078e9e; font-size: 17px; }
        .nh-hospital-create__next strong { display: block; color: #285166; font-size: 12px; margin-bottom: 4px; }
        .nh-hospital-create__next p { margin: 0; color: #638290; font-size: 11.5px; line-height: 1.55; }
        .nh-hospital-create .modal-footer { padding: 17px 28px; border-top: 1px solid #e5edf0; background: #fff; gap: 10px; }
        .nh-hospital-create__cancel { min-height: 43px; padding: 9px 16px; border: 1px solid #d9e3e8; border-radius: 11px; background: #fff; color: #496577; font-size: 12.5px; font-weight: 750; }
        .nh-hospital-create__cancel:hover { background: #f3f7fa; }
        .nh-hospital-create__submit { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 43px; padding: 10px 19px; border: 0; border-radius: 11px; background: #078f9f; color: #fff; font-size: 12.5px; font-weight: 850; box-shadow: 0 6px 17px #058b9c27; }
        .nh-hospital-create__submit:hover { background: #057d8b; color: #fff; }
        .nh-hospital-create__submit:disabled { opacity: .7; cursor: wait; }
        /* Отдельный редактор дочернего стационара; не меняет режим амбулатории. */
        .nh-hospital-edit .modal-dialog { max-width: 740px; }
        .nh-hospital-edit__immutable { display: flex; align-items: center; gap: 12px; background: #eaf8f9; border: 1px solid #c9e8ec; border-radius: 13px; padding: 14px 16px; color: #27596b; }
        .nh-hospital-edit__immutable > i { font-size: 25px; color: #0795a4; }
        .nh-hospital-edit__immutable strong { display: block; font-size: 13px; }
        .nh-hospital-edit__immutable small { display: block; margin-top: 4px; color: #6e8996; font-size: 11.5px; line-height: 1.55; }
        .nh-hospital-edit__mode-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 11px; }
        .nh-hospital-edit__mode { cursor: pointer; min-width: 0; }
        .nh-hospital-edit__mode input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .nh-hospital-edit__mode-card { display: flex; flex-direction: column; min-height: 100%; gap: 6px; padding: 15px 13px; background: #fff; border: 1px solid #dce7ed; border-radius: 13px; transition: .15s; }
        .nh-hospital-edit__mode-card > i { font-size: 21px; color: #668895; }
        .nh-hospital-edit__mode-card strong { font-size: 12px; color: #295369; }
        .nh-hospital-edit__mode-card small { color: #8497a2; font-size: 10.5px; line-height: 1.45; }
        .nh-hospital-edit__mode input:checked + .nh-hospital-edit__mode-card { border-color: #20aabc; background: #eefbfc; box-shadow: 0 0 0 2px #10aabb21; }
        .nh-hospital-edit__mode input:focus-visible + .nh-hospital-edit__mode-card { outline: 3px solid #75d6e1; outline-offset: 2px; }
        .nh-hospital-edit__phones { display: flex; flex-direction: column; gap: 9px; }
        .nh-hospital-edit__phone { display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr) 40px; gap: 9px; align-items: center; }
        .nh-hospital-edit__remove-phone { display: grid; place-items: center; width: 40px; height: 43px; border: 1px solid #f1d7d8; background: #fff; color: #b75a65; border-radius: 10px; }
        .nh-hospital-edit__remove-phone:hover { background: #fff0f0; }
        .nh-hospital-edit__add-phone { border: 1px dashed #b8dde3; border-radius: 10px; padding: 9px 13px; background: #f0fafb; color: #087e8e; font-size: 12px; font-weight: 800; }
        .nh-hospital-edit__add-phone:hover { background: #e0f6f8; }
        @media (max-width: 650px) { .nh-hospital-edit__mode-grid { grid-template-columns: minmax(0,1fr); } .nh-hospital-edit__phone { grid-template-columns: minmax(0,1fr) 40px; } .nh-hospital-edit__phone .nh-hospital-edit__phone-number { grid-column: 1/2; } .nh-hospital-edit__phone .nh-hospital-edit__phone-label { grid-column: 1/2; } .nh-hospital-edit__phone .nh-hospital-edit__remove-phone { grid-column: 2; grid-row: 1/3; } .nh-affiliations__actions { justify-content: flex-start; width: 100%; } }
        @media (max-width: 850px) { .nh-affiliations__hero-actions { flex-wrap: wrap; justify-content: flex-end; } }
        @media (max-width: 700px) { .nh-affiliations__hero { flex-wrap: wrap; } .nh-affiliations__hero-actions, .nh-affiliations__create { width: 100%; } .nh-hospital-create .modal-dialog { margin: 12px; } .nh-hospital-create__header { padding: 20px 55px 20px 19px; gap: 11px; } .nh-hospital-create__mark { width: 44px; height: 44px; flex-basis: 44px; } .nh-hospital-create .modal-body { padding: 20px; } .nh-hospital-create__fields { grid-template-columns: minmax(0,1fr); gap: 13px; } .nh-hospital-create .modal-footer { padding: 16px 20px; } }
        @media(max-width: 700px) { .nh-affiliations { padding: 16px 12px 35px; } .nh-affiliations__hero { padding: 20px; } .nh-affiliations__hero-icon { display:none; } .nh-affiliations__metrics { grid-template-columns: 1fr; } .nh-affiliations__row { flex-wrap: wrap; } .nh-affiliations__edit { margin-left: 60px; } .nh-affiliations__card { padding: 16px; } }
        .nh-affiliations__login-form { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .nh-affiliations__login-form select { max-width:220px; min-height:38px; border-radius:9px; }
        .nh-affiliations__login-help { font-size:11px; max-width:235px; color:#805e32; line-height:1.35; }
    </style>

    <main class="nh-affiliations">
        <div class="nh-affiliations__shell">
            <header class="nh-affiliations__hero">
                <div>
                    <div class="nh-affiliations__eyebrow"><i class="bi bi-diagram-3-fill"></i> Структура учреждений</div>
                    <h1>Принадлежность стационаров</h1>
                    <p>Определи, какой клинике или диспансеру принадлежит каждый стационар. Один стационар может иметь одну головную организацию, а у головной организации может быть несколько стационаров.</p>
                </div>
                <div class="nh-affiliations__hero-actions">
                    <button type="button" class="nh-affiliations__create" data-bs-toggle="modal" data-bs-target="#nhCreateHospitalModal">
                        <i class="bi bi-plus-circle-fill" aria-hidden="true"></i> Создать стационар
                    </button>
                    <span class="nh-affiliations__hero-icon" aria-hidden="true"><i class="bi bi-hospital"></i></span>
                </div>
            </header>

            @if(session('error'))
                <div class="alert alert-warning mt-3" role="alert">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success mt-3" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mt-3" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="nh-affiliations__metrics">
                <div class="nh-affiliations__metric"><div class="nh-affiliations__metric-label">Стационаров</div><div class="nh-affiliations__metric-value">{{ $hospitals->count() }}</div></div>
                <div class="nh-affiliations__metric"><div class="nh-affiliations__metric-label">С привязкой</div><div class="nh-affiliations__metric-value">{{ $linkedCount }}</div></div>
                <div class="nh-affiliations__metric"><div class="nh-affiliations__metric-label">Без головной организации</div><div class="nh-affiliations__metric-value">{{ $hospitals->count() - $linkedCount }}</div></div>
            </div>

            <section class="nh-affiliations__card" aria-labelledby="nhAffiliationListHeading">
                <div class="nh-affiliations__card-header">
                    <div>
                        <h2 id="nhAffiliationListHeading">Стационары и их организации</h2>
                        <p class="nh-affiliations__card-subtitle">Один администратор управляет своей амбулаторией и связанными стационарами. Отдельный аккаунт стационара не требуется.</p>
                    </div>
                    <input type="search" id="nhAffiliationsSearch" class="nh-affiliations__search" placeholder="Найти стационар..." aria-label="Поиск стационара">
                </div>
                <div id="nhAffiliationsList">
                    @forelse($hospitals as $hospital)
                        <div class="nh-affiliations__row" data-hospital-search="{{ mb_strtolower($hospital['name'] . ' ' . ($hospital['parent_name'] ?? '')) }}">
                            <span class="nh-affiliations__row-icon" aria-hidden="true"><i class="bi bi-hospital"></i></span>
                            <div class="nh-affiliations__row-copy">
                                <div class="nh-affiliations__hospital-name">{{ $hospital['name'] }}</div>
                                @if($hospital['parent_name'])
                                    <span class="nh-affiliations__parent"><i class="bi bi-diagram-2"></i> Головная организация: {{ $hospital['parent_name'] }}</span>
                                @else
                                    <span class="nh-affiliations__parent nh-affiliations__parent--empty"><i class="bi bi-exclamation-circle"></i> Головная организация не назначена</span>
                                @endif
                                <span class="nh-affiliations__row-mode"><i class="bi bi-clock-history" aria-hidden="true"></i>
                                    Режим стационара:
                                    {{ $hospital['inpatient_mode'] === 'day' ? 'дневной' : ($hospital['inpatient_mode'] === 'night' ? 'ночной' : 'круглосуточный') }}
                                </span>
                            </div>
                            <div class="nh-affiliations__actions">
                                @if($hospital['can_edit'])
                                    <form method="POST" class="nh-affiliations__login-form"
                                          action="{{ route('doctors.hospital-impersonation.enter', ['hospital' => $hospital['id']]) }}">
                                        @csrf
                                        <button type="submit" class="nh-affiliations__edit nh-affiliations__edit--primary">
                                            <i class="bi bi-gear" aria-hidden="true"></i> Настройки стационара
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="nh-affiliations__edit"
                                        data-bs-toggle="modal" data-bs-target="#nhAffiliationModal"
                                        data-hospital-id="{{ $hospital['id'] }}"
                                        data-hospital-name="{{ $hospital['name'] }}"
                                        data-parent-id="{{ $hospital['parent_id'] ?? '' }}">
                                    <i class="bi bi-diagram-3" aria-hidden="true"></i> Изменить связь
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="nh-affiliations__empty">Стационаров пока нет. Нажми «Создать стационар», чтобы добавить первый и связать его с головной организацией.</div>
                    @endforelse
                </div>
            </section>

            <div class="nh-affiliations__notice">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <div>Привязка стационара к клинике — организационная настройка. Она <strong>не</strong> назначает врачей, не расширяет доступ к медкартам и не переносит пациентов между учреждениями.</div>
            </div>
        </div>
    </main>

    <div class="modal fade nh-affiliations-modal" id="nhAffiliationModal" tabindex="-1" aria-labelledby="nhAffiliationModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" action="{{ route('doctors.settings.clinic-affiliations.update') }}" method="POST">
                @csrf
                <input type="hidden" id="nhHospitalId" name="hospital_id" value="">
                <div class="modal-header">
                    <div>
                        <span class="nh-affiliations-modal__eyebrow"><i class="bi bi-diagram-3" aria-hidden="true"></i> Структура учреждения</span>
                        <h2 class="modal-title" id="nhAffiliationModalTitle">Головная организация</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="nh-affiliations-modal__hospital">
                        <span class="nh-affiliations-modal__hospital-icon" aria-hidden="true"><i class="bi bi-hospital"></i></span>
                        <div><small>Выбранный стационар</small><strong id="nhHospitalName">—</strong></div>
                    </div>
                    <label class="form-label" for="nhParentClinicId">Клиника / диспансер</label>
                    <select class="form-select" name="parent_clinic_id" id="nhParentClinicId">
                        <option value="" disabled>Без головной организации (изменение через Filament)</option>
                        @foreach($potentialParents as $parentCandidate)
                            <option value="{{ $parentCandidate['id'] }}">
                                {{ $parentCandidate['name'] }} {{ $parentCandidate['type'] === 'inpatient' ? '· стационар' : '· амбулатория' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="nh-affiliations-modal__help">Выбери головное учреждение или оставь поле пустым, если пока не определено. Нельзя привязать стационар к самому себе.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="nh-affiliations-modal__save"><i class="bi bi-check2-circle"></i> Сохранить связь</button>
                </div>
            </form>
        </div>
    </div>


    {{-- Создание нового стационара: головная организация определяется сервером из текущей амбулатории. --}}
    <div class="modal fade nh-hospital-create" id="nhCreateHospitalModal" tabindex="-1"
         aria-labelledby="nhCreateHospitalModalTitle" aria-hidden="true"
         data-reopen-on-error="{{ old('form_intent') === 'create_hospital' && $errors->any() ? '1' : '0' }}">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" id="nhCreateHospitalForm"
                  action="{{ route('doctors.settings.clinic-affiliations.store') }}" method="POST">
                @csrf
                <input type="hidden" name="form_intent" value="create_hospital">
                <header class="modal-header nh-hospital-create__header">
                    <span class="nh-hospital-create__mark" aria-hidden="true"><i class="bi bi-hospital"></i></span>
                    <div>
                        <div class="nh-hospital-create__caption">Новое учреждение · стационар</div>
                        <h2 class="modal-title" id="nhCreateHospitalModalTitle">Создать стационар</h2>
                        <p class="nh-hospital-create__subtitle">Добавьте основные сведения. Отделения и палаты можно настроить позднее.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </header>
                <div class="modal-body">
                    <section class="nh-hospital-create__section" aria-label="Головная организация">
                        <div class="nh-hospital-create__step"><span class="nh-hospital-create__step-num">1</span> Головная организация</div>
                        <div class="nh-hospital-create__parent">
                            <span class="nh-hospital-create__parent-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                            <div class="nh-hospital-create__parent-copy">
                                <small>Стационар будет закреплён за текущей амбулаторией</small>
                                <strong>{{ $currentClinicName }}</strong>
                            </div>
                            <i class="bi bi-check-circle-fill nh-hospital-create__parent-check" aria-hidden="true"></i>
                        </div>
                        <p class="nh-hospital-create__hint">Связь устанавливается автоматически. Другая организация не сможет быть указана через форму.</p>
                    </section>
                    <section class="nh-hospital-create__section" aria-label="Сведения о стационаре">
                        <div class="nh-hospital-create__step"><span class="nh-hospital-create__step-num">2</span> Основные сведения</div>
                        <div class="nh-hospital-create__fields">
                            <div class="nh-hospital-create__wide">
                                <label class="form-label" for="nhNewHospitalName">Название стационара <span class="nh-hospital-create__required">*</span></label>
                                <input class="form-control @error('hospital_name') is-invalid @enderror"
                                       type="text" id="nhNewHospitalName" name="hospital_name"
                                       value="{{ old('hospital_name') }}" maxlength="255" required
                                       placeholder="Например, Психиатрический стационар № 1" autocomplete="organization">
                                @error('hospital_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="nh-hospital-create__wide">
                                <label class="form-label" for="nhNewHospitalAddress">Полный адрес стационара <span class="nh-hospital-create__required">*</span></label>
                                <div class="nh-hospital-create__lookup" id="nhHospitalAddressLookup">
                                    <input class="form-control @error('hospital_address') is-invalid @enderror"
                                           type="text" id="nhNewHospitalAddress" name="hospital_address"
                                           value="{{ old('hospital_address') }}" maxlength="500" required
                                           placeholder="Например, Северск, ул. Ленина, 5" autocomplete="off"
                                           role="combobox" aria-autocomplete="list" aria-expanded="false"
                                           aria-controls="nhHospitalAddressSuggestions">
                                    <i class="bi bi-geo-alt nh-hospital-create__lookup-icon" aria-hidden="true"></i>
                                    <div id="nhHospitalAddressSuggestions" class="nh-hospital-create__suggestions"
                                         role="listbox" aria-label="Подсказки адресов" hidden></div>
                                    @error('hospital_address') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <p class="nh-hospital-create__hint">Укажи адрес целиком: регион, город, улицу и дом. При выборе подсказки город определится автоматически. Если адрес не находится, введи его вручную.</p>
                                <input type="hidden" name="hospital_latitude" id="nhHospitalLatitude" value="{{ old('hospital_latitude') }}">
                                <input type="hidden" name="hospital_longitude" id="nhHospitalLongitude" value="{{ old('hospital_longitude') }}">
                            </div>
                            {{-- Город для существующей колонки clinics.city заполняется из выбранной подсказки. --}}
                            <input type="hidden" id="nhNewHospitalCity" name="hospital_city" value="{{ old('hospital_city') }}">
                            <div class="nh-hospital-create__wide">
                                <div class="nh-hospital-create__address-info" id="nhHospitalAddressInfo" role="status" aria-live="polite" hidden>
                                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                    <div>
                                        <strong>Адрес выбран из справочника</strong>
                                        <small id="nhHospitalAddressDetails"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="nh-hospital-create__wide">
                                <label class="form-label" for="nhNewHospitalDescription">Краткое описание <span style="color:#8da0ac;font-weight:500">(необязательно)</span></label>
                                <textarea class="form-control @error('hospital_description') is-invalid @enderror"
                                          id="nhNewHospitalDescription" name="hospital_description" rows="2"
                                          maxlength="2000" placeholder="Например, стационар для круглосуточного наблюдения и лечения">{{ old('hospital_description') }}</textarea>
                                @error('hospital_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </section>
                    <div class="nh-hospital-create__next">
                        <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                        <div>
                            <strong>Что будет после создания?</strong>
                            <p>Стационар появится в списке и автоматически получит тип «Стационар». Отделения, палаты, койки, сотрудников и телефоны с указанием назначения можно добавить позже в настройках стационара. Медицинские данные пациентов не переносятся.</p>
                        </div>
                    </div>
                </div>
                <footer class="modal-footer">
                    <button type="button" class="nh-hospital-create__cancel" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="nh-hospital-create__submit" id="nhCreateHospitalSubmit">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i> Создать стационар
                    </button>
                </footer>
            </form>
        </div>
    </div>

    {{-- Создание стационара и привязка остаются отдельными операциями; изменение параметров только через workspace. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('nhAffiliationModal');
            const select = document.getElementById('nhParentClinicId');
            const search = document.getElementById('nhAffiliationsSearch');
            if (!modal || !select) return;

            if (window.jQuery?.fn?.select2) {
                window.jQuery(select).select2({
                    width: '100%', dropdownParent: window.jQuery(modal),
                    minimumResultsForSearch: 0, placeholder: 'Головная организация'
                });
            }
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                const hospitalId = button.dataset.hospitalId || '';
                document.getElementById('nhHospitalId').value = hospitalId;
                document.getElementById('nhHospitalName').textContent = button.dataset.hospitalName || 'Стационар';
                for (const option of select.options) {
                    option.disabled = Boolean(hospitalId && option.value === hospitalId);
                }
                select.value = button.dataset.parentId || '';
                if (window.jQuery?.fn?.select2) window.jQuery(select).trigger('change.select2');
            });

            // При ошибке валидации возвращаем пользователя в форму, сохранив поля.
            const createModal = document.getElementById('nhCreateHospitalModal');
            const createForm = document.getElementById('nhCreateHospitalForm');
            createModal?.addEventListener('shown.bs.modal', function () {
                document.getElementById('nhNewHospitalName')?.focus();
            });
            if (createModal?.dataset.reopenOnError === '1' && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(createModal).show();
            }
            createForm?.addEventListener('submit', function () {
                // Повторный клик не должен отправлять несколько одинаковых POST.
                if (!createForm.checkValidity()) return;
                const button = document.getElementById('nhCreateHospitalSubmit');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Создаём...';
                }
            });

            // Используем тот же серверный поиск DaData / OSM, что и в «Общих настройках».
            // Ключи и токены остаются на сервере; новый внешний API не подключаем.
            const hospitalAddressInput = document.getElementById('nhNewHospitalAddress');
            const hospitalCityInput = document.getElementById('nhNewHospitalCity');
            const addressInfo = document.getElementById('nhHospitalAddressInfo');
            const addressDetails = document.getElementById('nhHospitalAddressDetails');
            const latitudeInput = document.getElementById('nhHospitalLatitude');
            const longitudeInput = document.getElementById('nhHospitalLongitude');
            const addressSearchUrl = @json(route('api-search-address'));

            function normalizeCity(value) {
                return String(value || '')
                    .replace(/^(?:г\.?|город|п\.?|пос\.?|посёлок|поселок|пгт|с\.?|село|д\.?|деревня)\s+/i, '')
                    .trim();
            }

            function clearSelectedAddress() {
                if (latitudeInput) latitudeInput.value = '';
                if (longitudeInput) longitudeInput.value = '';
                if (addressInfo) addressInfo.hidden = true;
                if (addressDetails) addressDetails.textContent = '';
            }

            // Одна строка полного адреса. Из выбранной подсказки берём город и координаты.
            // Второго видимого поля для города и поля телефона при создании нет.
            function createAddressSuggest() {
                const input = hospitalAddressInput;
                const holder = document.getElementById('nhHospitalAddressLookup');
                const list = document.getElementById('nhHospitalAddressSuggestions');
                if (!input || !holder || !list) return;

                let timer = null;
                let activeController = null;
                let requestVersion = 0;
                let suggestions = [];
                let currentIndex = -1;

                const hide = () => {
                    list.hidden = true;
                    list.replaceChildren();
                    holder.classList.remove('is-open');
                    input.setAttribute('aria-expanded', 'false');
                    input.removeAttribute('aria-activedescendant');
                    suggestions = [];
                    currentIndex = -1;
                };
                const cancel = () => {
                    window.clearTimeout(timer);
                    requestVersion++;
                    activeController?.abort();
                    activeController = null;
                };
                const show = () => {
                    list.hidden = false;
                    holder.classList.add('is-open');
                    input.setAttribute('aria-expanded', 'true');
                };
                const message = (content, icon = 'bi-search') => {
                    list.replaceChildren();
                    const row = document.createElement('div');
                    row.className = 'nh-hospital-create__suggestion-message';
                    const symbol = document.createElement('i');
                    symbol.className = `bi ${icon}`;
                    symbol.setAttribute('aria-hidden', 'true');
                    const text = document.createElement('span');
                    text.textContent = content;
                    row.append(symbol, text);
                    list.append(row);
                    show();
                };
                const markCurrent = () => {
                    list.querySelectorAll('[data-index]').forEach((button, i) => {
                        const isCurrent = i === currentIndex;
                        button.classList.toggle('is-active', isCurrent);
                        button.setAttribute('aria-selected', String(isCurrent));
                        if (isCurrent) button.scrollIntoView({ block: 'nearest' });
                    });
                    if (currentIndex >= 0) {
                        input.setAttribute('aria-activedescendant', `nhHospitalAddressSuggestions-${currentIndex}`);
                    } else {
                        input.removeAttribute('aria-activedescendant');
                    }
                };
                const selectItem = (index) => {
                    const item = suggestions[index];
                    if (!item) return;
                    cancel();
                    input.value = String(item.value || item.unrestricted_value || '').trim();
                    const city = normalizeCity(item.city);
                    if (hospitalCityInput) hospitalCityInput.value = city;
                    const lat = Number.parseFloat(item.geo_lat);
                    const lon = Number.parseFloat(item.geo_lon);
                    const geoOk = Number.isFinite(lat) && Number.isFinite(lon)
                        && Math.abs(lat) <= 90 && Math.abs(lon) <= 180;
                    if (latitudeInput) latitudeInput.value = geoOk ? String(lat) : '';
                    if (longitudeInput) longitudeInput.value = geoOk ? String(lon) : '';
                    const details = [item.region, city, item.postal_code ? `Индекс: ${item.postal_code}` : null]
                        .filter(Boolean);
                    if (addressDetails) addressDetails.textContent = details.join(' · ') || 'Адрес заполнен по подсказке';
                    if (addressInfo) addressInfo.hidden = false;
                    hide();
                };
                const render = (items) => {
                    suggestions = items;
                    currentIndex = -1;
                    list.replaceChildren();
                    if (!items.length) {
                        message('Адрес не найден. Попробуйте уточнить улицу или дом либо введите его вручную.', 'bi-geo-alt');
                        return;
                    }
                    items.forEach((item, i) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.id = `nhHospitalAddressSuggestions-${i}`;
                        button.className = 'nh-hospital-create__suggestion';
                        button.tabIndex = -1;
                        button.dataset.index = String(i);
                        button.setAttribute('role', 'option');
                        button.setAttribute('aria-selected', 'false');
                        const icon = document.createElement('i');
                        icon.className = 'bi bi-geo-alt';
                        icon.setAttribute('aria-hidden', 'true');
                        const label = document.createElement('span');
                        label.className = 'nh-hospital-create__suggestion-copy';
                        const title = document.createElement('strong');
                        title.textContent = String(item.value || item.unrestricted_value || '');
                        const subtitle = document.createElement('small');
                        subtitle.textContent = [item.region, item.city, item.postal_code].filter(Boolean).join(' · ');
                        label.append(title, subtitle);
                        button.append(icon, label);
                        button.addEventListener('mousedown', event => event.preventDefault());
                        button.addEventListener('click', () => selectItem(i));
                        list.append(button);
                    });
                    show();
                };
                const searchAddress = async (typedValue) => {
                    const raw = String(typedValue || '').trim();
                    if (raw.length < 2) return hide();
                    const version = ++requestVersion;
                    activeController?.abort();
                    const controller = new AbortController();
                    activeController = controller;
                    message('Ищем подходящие варианты...');
                    try {
                        const url = new URL(addressSearchUrl, window.location.origin);
                        url.searchParams.set('query', raw);
                        const response = await fetch(url.toString(), {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            signal: controller.signal,
                        });
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        const data = await response.json();
                        if (version !== requestVersion || controller.signal.aborted) return;
                        const returned = Array.isArray(data?.suggestions) ? data.suggestions : [];
                        render(returned.filter(item => String(item?.value || item?.unrestricted_value || '').trim().length));
                    } catch (error) {
                        if (controller.signal.aborted || version !== requestVersion) return;
                        console.warn('[hospital-address] address lookup failed', error);
                        message('Сервис адресов недоступен. Полный адрес можно ввести вручную.', 'bi-exclamation-circle');
                    } finally {
                        if (activeController === controller) activeController = null;
                    }
                };
                input.addEventListener('input', () => {
                    cancel();
                    hide();
                    clearSelectedAddress();
                    if (hospitalCityInput) hospitalCityInput.value = '';
                    if (input.value.trim().length < 2) return;
                    timer = window.setTimeout(() => searchAddress(input.value), 320);
                });
                input.addEventListener('focus', () => {
                    if (input.value.trim().length >= 2 && (addressInfo?.hidden ?? true)) {
                        cancel();
                        timer = window.setTimeout(() => searchAddress(input.value), 320);
                    }
                });
                input.addEventListener('keydown', event => {
                    if (list.hidden) return;
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        cancel();
                        hide();
                    } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        if (!suggestions.length) return;
                        event.preventDefault();
                        currentIndex = event.key === 'ArrowDown'
                            ? (currentIndex + 1) % suggestions.length
                            : (currentIndex + suggestions.length - 1) % suggestions.length;
                        markCurrent();
                    } else if (event.key === 'Enter') {
                        event.preventDefault();
                        if (suggestions.length) selectItem(Math.max(currentIndex, 0));
                    } else if (event.key === 'Tab') {
                        hide();
                    }
                });
                document.addEventListener('pointerdown', event => {
                    if (!holder.contains(event.target)) hide();
                });
                createModal?.addEventListener('hidden.bs.modal', () => {
                    cancel();
                    hide();
                });
            }
            createAddressSuggest();

            search?.addEventListener('input', function () {
                const query = search.value.toLocaleLowerCase('ru').trim();
                document.querySelectorAll('.nh-affiliations__row').forEach(function (row) {
                    row.hidden = !row.dataset.hospitalSearch.includes(query);
                });
            });
        });
    </script>

@endsection
