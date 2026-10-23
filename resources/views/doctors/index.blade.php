@extends('layouts.doctor')
@section('title', 'Рабочий стол')
@section('main')
    <style>
        /* Только рабочий стол: не изменяем общие стили, ширину сайдбара или layout. */
        .nh-doctor-topbar {
            box-sizing: border-box;
            width: 100%; min-height: 68px; margin: 0; padding: 0;
            background: linear-gradient(135deg, #048696 0%, #10b7c6 100%);
            color: #fff; box-shadow: 0 2px 8px rgba(7,82,96,.12);
        }
        .nh-doctor-topbar, .nh-doctor-topbar * { box-sizing: border-box; }
        .nh-doctor-topbar .nh-doctor-topbar__inner {
            display: flex; align-items: center; gap: 12px; min-height: 68px;
            width: 100%; padding: 10px 24px;
        }
        .nh-doctor-topbar .nh-doctor-topbar__heading {
            display: inline-flex; flex: 0 0 auto; align-items: center; gap: 10px;
        }
        .nh-doctor-topbar .nh-doctor-topbar__mark {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 13px;
            background: rgba(255,255,255,.17); color: #fff;
        }
        .nh-doctor-topbar .nh-doctor-topbar__title {
            color: #fff; font-size: 18px; font-weight: 800; line-height: 1.25; white-space: nowrap;
        }
        .nh-doctor-topbar .nh-doctor-topbar__collapse { min-width: 0; }
        .nh-doctor-topbar .nh-doctor-topbar__links {
            display: flex; align-items: center; flex-wrap: wrap; gap: 5px;
            margin: 0; padding: 0; list-style: none;
        }
        .nh-doctor-topbar .nh-doctor-topbar__link {
            display: inline-flex; align-items: center; min-height: 40px; padding: 9px 11px;
            border: 1px solid transparent; border-radius: 12px;
            color: rgba(255,255,255,.90); font-size: 15px; font-weight: 700;
            line-height: 1.2; text-decoration: none; white-space: nowrap;
        }
        .nh-doctor-topbar .nh-doctor-topbar__link:hover,
        .nh-doctor-topbar .nh-doctor-topbar__link:focus {
            color: #fff; background: rgba(255,255,255,.12);
        }
        .nh-doctor-topbar .nh-doctor-topbar__link.active {
            color: #fff; font-weight: 700; background: rgba(255,255,255,.20);
            border-color: rgba(255,255,255,.15);
        }
        /* Общее оформление для двух независимых навбаров рабочего стола. */
        .nh-doctor-topbar .nh-doctor-topbar__actions {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 10px; margin-left: auto; min-width: 0;
        }
        .nh-doctor-topbar .nh-doctor-topbar__mode-link {
            display: inline-flex; align-items: center; gap: 7px;
            min-height: 36px; padding: 6px 10px;
            border-radius: 11px; color: rgba(255,255,255,.87);
            font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap;
        }
        .nh-doctor-topbar .nh-doctor-topbar__mode-link:hover,
        .nh-doctor-topbar .nh-doctor-topbar__mode-link:focus-visible {
            color: #fff; background: rgba(255,255,255,.16);
        }
        .nh-doctor-topbar .nh-doctor-topbar__link[type="button"] {
            background: transparent; cursor: pointer; font-family: inherit;
        }
        .nh-doctor-topbar .nh-doctor-topbar__link[type="button"]:hover,
        .nh-doctor-topbar .nh-doctor-topbar__link[type="button"]:focus-visible {
            background: rgba(255,255,255,.16);
        }
        /* Такой же контекстный бейдж, как в разделе «Настройки». */
        .nh-doctor-topbar .nh-doctor-topbar__context {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            flex: 0 0 auto;
            min-height: 36px;
            margin-left: 0;
            padding: 7px 12px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }
        .nh-doctor-topbar .nh-doctor-topbar__toggler {
            margin-left: auto; padding: 5px 8px; border: 1px solid rgba(255,255,255,.35);
        }
        .nh-doctor-topbar .nh-doctor-topbar__toggler .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }
        /* Оформление ограничено содержимым рабочего стола. */
        .nh-workbench,
        .nh-workbench * { box-sizing: border-box; }
        .nh-workbench {
            width: 100%; min-width: 0; max-width: 100%;
            min-height: calc(100vh - 68px);
            padding: 28px 30px 44px;
            background: #f5f8fa; color: #183d50;
        }
        .nh-workbench__container { max-width: 1500px; margin: 0 auto; }
        .nh-workbench__hero {
            position: relative; isolation: isolate; overflow: hidden;
            display: flex; align-items: center; justify-content: space-between;
            gap: 22px; padding: 26px 30px; margin-bottom: 20px;
            border: 1px solid #d7ebed; border-radius: 18px;
            background: linear-gradient(108deg, #e6f7f6 0%, #f3fbfc 65%, #eef6fa 100%);
            box-shadow: 0 8px 25px rgba(20, 70, 84, .035);
        }
        .nh-workbench__hero::before,
        .nh-workbench__hero::after {
            content: ""; position: absolute; z-index: -1; pointer-events: none;
            border-radius: 50%; border: 1px solid rgba(9,150,162,.10);
        }
        .nh-workbench__hero::before { width: 310px; height: 310px; right: -110px; top: -158px; }
        .nh-workbench__hero::after { width: 190px; height: 190px; right: 65px; bottom: -160px; }
        .nh-workbench__eyebrow {
            display: inline-flex; align-items: center; gap: 9px;
            color: #187f8d; font-size: 10px; font-weight: 800;
            letter-spacing: 1.4px; text-transform: uppercase; margin-bottom: 9px;
        }
        .nh-workbench__eyebrow::before {
            content: ""; width: 7px; height: 7px; border-radius: 50%;
            background: #25b9b7; box-shadow: 0 0 0 4px rgba(37,185,183,.13);
        }
        .nh-workbench__title {
            margin: 0; color: #183e52; font-size: clamp(24px,2.5vw,31px);
            font-weight: 760; letter-spacing: -.65px; line-height: 1.2;
        }
        .nh-workbench__subtitle { color: #5d7c89; font-size: 13.5px; margin: 8px 0 0; }
        .nh-workbench__hero-icon {
            display: inline-flex; align-items: center; justify-content: center;
            flex: 0 0 73px; width: 73px; height: 73px; border-radius: 22px;
            color: #087f8d; background: rgba(255,255,255,.74);
            border: 1px solid rgba(255,255,255,.86);
            box-shadow: 0 8px 25px rgba(24,119,132,.09);
        }
        .nh-workbench__hero-icon svg { width: 36px; height: 36px; }
        /* Иконка шапки декоративная: никаких действий госпитализации здесь нет. */
        .nh-workbench__hero-icon i { font-size: 35px; line-height: 1; }
        .nh-workbench__institution {
            display: inline-flex; align-items: center; flex-wrap: wrap; gap: 7px 10px;
            margin-top: 13px; padding: 7px 11px; border: 1px solid #cfe8ea;
            border-radius: 10px; background: rgba(255,255,255,.71);
            color: #1e6577; font-size: 12px; font-weight: 750;
        }
        .nh-workbench__institution small { color: #5e7f8e; font-size: 11px; font-weight: 600; }


        .nh-workbench__grid {
            display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(290px, 1fr);
            gap: 18px; align-items: stretch;
        }
        .nh-workbench__card {
            min-width: 0; border: 1px solid #e0e9ed; border-radius: 16px;
            background: #fff; box-shadow: 0 5px 18px rgba(20,63,84,.035);
            padding: 23px;
        }
        .nh-workbench__section-heading {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
        }
        .nh-workbench__section-title {
            margin: 0; color: #21465a; font-size: 17px; font-weight: 750;
            letter-spacing: -.2px;
        }
        .nh-workbench__muted { color: #708795; font-size: 12.5px; }
        .nh-workbench__setup { padding: 32px; }
        .nh-workbench__setup-top { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
        .nh-workbench__setup-icon {
            display: flex; align-items: center; justify-content: center;
            flex: 0 0 60px; width: 60px; height: 60px; border-radius: 17px;
            background: #e8f8f8; color: #0a91a0;
        }
        .nh-workbench__setup-icon svg { width: 29px; height: 29px; }
        .nh-workbench__status {
            display: inline-flex; align-items: center; gap: 7px;
            width: max-content; padding: 5px 10px; border-radius: 30px;
            background: #fff4e1; color: #9b6a1b; font-size: 11px; font-weight: 700;
        }
        .nh-workbench__status::before {
            content: ""; width: 6px; height: 6px; border-radius: 50%; background: #d9a447;
        }
        .nh-workbench__setup-title { font-size: 22px; font-weight: 750; color: #1b4357; margin: 0 0 9px; }
        .nh-workbench__setup-description {
            margin: 0 0 22px; max-width: 730px; font-size: 13.5px;
            line-height: 1.7; color: #637f8b;
        }
        .nh-workbench__steps {
            list-style: none; margin: 0; padding: 20px;
            border-radius: 13px; background: #f6f9fb; border: 1px solid #ebf0f2;
            display: grid; gap: 15px;
        }
        .nh-workbench__step {
            display: flex; align-items: flex-start; gap: 12px;
            color: #3f6170; font-size: 13px; line-height: 1.55;
        }
        .nh-workbench__step-number {
            display: inline-flex; align-items: center; justify-content: center;
            flex: 0 0 25px; height: 25px; border-radius: 8px;
            color: #158694; font-size: 12px; font-weight: 750;
            background: #dcf3f4;
        }
        .nh-workbench__side-title { margin: 2px 0 8px; color: #234a5e; font-size: 16px; font-weight: 750; }
        .nh-workbench__side-intro { color: #708895; font-size: 12.5px; line-height: 1.6; margin: 0 0 19px; }
        .nh-workbench__feature-list { display: grid; gap: 18px; }
        .nh-workbench__feature { display: flex; align-items: flex-start; gap: 12px; }
        .nh-workbench__feature-icon {
            display: flex; justify-content: center; align-items: center;
            width: 39px; height: 39px; flex: 0 0 39px;
            border-radius: 11px; background: #edf7f8; color: #128b9a;
        }
        .nh-workbench__feature-icon svg { width: 19px; height: 19px; }
        .nh-workbench__feature-title { color: #2a4b5e; font-size: 13px; font-weight: 700; margin: 1px 0 3px; }
        .nh-workbench__feature-desc { color: #7b929c; font-size: 12px; line-height: 1.5; margin: 0; }
        .nh-workbench__side-note {
            margin-top: 24px; padding-top: 17px; border-top: 1px solid #edf1f3;
            color: #8b9da5; font-size: 11.5px; line-height: 1.6;
        }
        .nh-workbench__stats {
            display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 17px; margin-bottom: 18px;
        }
        .nh-workbench__stat { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; padding: 22px; }
        .nh-workbench__metric { color: #143f53; font-size: 32px; line-height: 1.05; font-weight: 780; margin: 12px 0 7px; }
        .nh-workbench__metric-label { font-size: 12.5px; color: #708895; line-height: 1.45; max-width: 230px; }
        .nh-workbench__stat-icon {
            display: flex; justify-content: center; align-items: center; width: 43px; height: 43px;
            border-radius: 12px; flex: 0 0 43px; background: #e8f7f8; color: #0a93a0;
        }
        .nh-workbench__stat-icon svg { width: 21px; height: 21px; }
        .nh-workbench__stat-caption { color: #168392; font-weight: 700; font-size: 11px; }
        .nh-workbench__dashboard-table { overflow: hidden; }
        .nh-workbench__all-link { color: #087e8d; font-size: 12px; font-weight: 700; text-decoration: none; }
        .nh-workbench__all-link:hover { text-decoration: underline; }
        .nh-workbench__switch {
            display: inline-flex; flex-wrap: wrap; gap: 4px; padding: 4px;
            margin-bottom: 19px; background: #ecf5f7; border-radius: 11px;
        }
        .nh-workbench__switch-link {
            display: inline-flex; align-items: center; padding: 9px 14px;
            border-radius: 8px; color: #51808b; font-size: 12.5px; text-decoration: none;
        }
        .nh-workbench__switch-link:hover { color: #167e8b; }
        .nh-workbench__switch-link.active {
            background: #fff; color: #087f8d; font-weight: 750;
            box-shadow: 0 2px 7px rgba(16,85,100,.095);
        }
        .nh-workbench__filters {
            display: flex; align-items: end; flex-wrap: wrap; gap: 10px;
            padding: 15px; margin: 0 0 18px;
            border: 1px solid #e9eff2; border-radius: 12px; background: #f9fbfc;
        }
        .nh-workbench__filter { min-width: 0; flex: 1 1 190px; }
        .nh-workbench__filter--search { flex: 2 1 250px; }
        .nh-workbench__filter .form-label { color: #567685; font-size: 12px; font-weight: 650; }
        .nh-workbench__filters .btn { min-height: 38px; }
        .nh-workbench__patient-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .nh-workbench__patient-table th {
            padding: 11px 12px; border-bottom: 1px solid #e1ebef;
            color: #77919d; font-size: 11.5px; font-weight: 700;
            background: #f8fbfc; white-space: nowrap;
        }
        .nh-workbench__patient-table td {
            padding: 15px 12px; border-bottom: 1px solid #edf2f4;
            color: #325569; font-size: 12.5px; vertical-align: middle;
        }
        .nh-workbench__patient-table tr:last-child td { border-bottom: 0; }
        .nh-workbench__patient-name {
            display: block; color: #155a70; font-size: 12.5px; font-weight: 750;
            line-height: 1.4; text-decoration: none;
        }
        .nh-workbench__patient-name:hover { color: #0695a5; }
        .nh-workbench__doctor-select { display: flex; align-items: center; gap: 7px; min-width: 255px; }
        .nh-workbench__doctor-picker { position: relative; flex: 1 1 210px; min-width: 165px; }
        .nh-workbench__doctor-native { height: 35px; font-size: 12.5px; border-radius: 9px; }
        .nh-workbench__doctor-picker.is-enhanced .nh-workbench__doctor-native { display: none; }
        .nh-workbench__doctor-trigger {
            display: flex; width: 100%; min-height: 35px; align-items: center;
            justify-content: space-between; gap: 6px; padding: 6px 10px;
            border: 1px solid #d9e6eb; border-radius: 9px; background: #fff;
            color: #284a5b; font: inherit; font-size: 12.5px; line-height: 1.35;
            text-align: left; cursor: pointer;
        }
        .nh-workbench__doctor-trigger:hover { border-color: #6dc4cc; }
        .nh-workbench__doctor-trigger:focus-visible {
            outline: 0; border-color: #25b5c6;
            box-shadow: 0 0 0 3px rgba(37,181,198,.15);
        }
        .nh-workbench__doctor-trigger-label { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .nh-workbench__doctor-trigger svg { flex: 0 0 14px; color: #648693; }
        .nh-workbench__doctor-select .btn { height: 35px; white-space: nowrap; }
        /* Portal: список размещается у body, поэтому не обрезается .table-responsive. */
        .nh-workbench__doctor-popover {
            position: fixed; z-index: 1100; border: 1px solid #cfe2e7;
            border-radius: 11px; background: #fff; padding: 8px;
            box-shadow: 0 15px 35px rgba(20,63,83,.20); box-sizing: border-box;
        }
        .nh-workbench__doctor-popover[hidden] { display: none !important; }
        .nh-workbench__doctor-filter {
            width: 100%; min-height: 36px; padding: 7px 10px;
            border: 1px solid #dae7eb; border-radius: 8px;
            outline: none; font-size: 13px; color: #244a59;
        }
        .nh-workbench__doctor-filter:focus { border-color: #13aaba; box-shadow: 0 0 0 3px rgba(19,170,186,.10); }
        .nh-workbench__doctor-options { overflow-y: auto; max-height: 240px; padding-top: 5px; }
        .nh-workbench__doctor-option {
            display: block; width: 100%; padding: 9px 10px;
            border: 0; border-radius: 7px; background: none;
            color: #284d60; text-align: left; font: inherit; font-size: 12.5px; cursor: pointer;
        }
        .nh-workbench__doctor-option:hover,
        .nh-workbench__doctor-option:focus-visible { background: #e7f6f8; outline: none; }
        .nh-workbench__doctor-option[aria-selected="true"] { color: #037e8e; font-weight: 750; }
        .nh-workbench__doctor-no-results { padding: 12px 10px; color: #77919d; font-size: 12px; }
        .nh-workbench__doctor-help {
            margin: 10px 0 0; color: #718b96; font-size: 11.5px; line-height: 1.5;
        }
        .nh-workbench__empty {
            display: flex; align-items: center; justify-content: center; text-align: center;
            min-height: 130px; padding: 25px 15px; color: #788f9b; font-size: 13px;
        }
        .nh-workbench__full-card { margin-bottom: 18px; }
        /* Единственная точка оформления госпитализации — шапка списка пациентов. */
        .nh-workbench__patients-heading { margin-bottom: 19px; gap: 16px; }
        .nh-workbench__patients-heading-copy { display: grid; gap: 5px; min-width: 0; }
        .nh-workbench__admission-toolbar { margin-left: auto; max-width: 100%; }
        .nh-workbench__admission-form {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            justify-content: flex-end;
        }
        .nh-workbench__admission-clinic {
            height: 44px; min-width: 210px; max-width: 310px; width: auto;
            border: 1px solid #d5e4e9; border-radius: 12px;
            font-size: 13px; color: #24475a;
        }
        .nh-workbench__admission-btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 10px; min-height: 44px; padding: 10px 16px;
            border: 1px solid #088e9e; border-radius: 12px;
            background: linear-gradient(115deg, #0793a4, #12bac5);
            box-shadow: 0 5px 13px rgba(6, 125, 143, .18);
            color: #fff; font-size: 14px; line-height: 1.25;
            font-weight: 750; white-space: nowrap; text-decoration: none;
            cursor: pointer; transition: box-shadow .16s ease, background .16s ease, transform .16s ease;
        }
        .nh-workbench__admission-btn i:first-child { font-size: 17px; }
        .nh-workbench__admission-arrow { opacity: .76; font-size: 12px; }
        .nh-workbench__admission-btn:hover {
            color: #fff; background: linear-gradient(115deg, #077e8f, #0fa9b7);
            box-shadow: 0 8px 20px rgba(6, 125, 143, .23); transform: translateY(-1px);
        }
        .nh-workbench__admission-btn:focus-visible {
            outline: 3px solid rgba(18, 186, 197, .4); outline-offset: 3px;
        }
        @media (max-width: 650px) {
            .nh-workbench__admission-toolbar, .nh-workbench__admission-form { width: 100%; }
            .nh-workbench__admission-form { justify-content: stretch; }
            .nh-workbench__admission-clinic { max-width: none; flex: 1 1 100%; }
            .nh-workbench__admission-btn { width: 100%; white-space: normal; }
        }
        @media (max-width: 991.98px) {
            .nh-doctor-topbar .nh-doctor-topbar__inner { flex-wrap: wrap; padding: 10px 14px; }
            .nh-doctor-topbar .nh-doctor-topbar__collapse { flex-basis: 100%; width: 100%; }
            .nh-doctor-topbar .nh-doctor-topbar__links { align-items: stretch; padding: 8px 0 4px; }
            .nh-doctor-topbar .nh-doctor-topbar__actions { width: 100%; margin: 8px 0 0; justify-content: flex-start; flex-wrap: wrap; }
            .nh-doctor-topbar .nh-doctor-topbar__context { margin: 8px 0 4px; }
            .nh-doctor-topbar .nh-doctor-topbar__link { min-height: 34px; }
            .nh-workbench__grid { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 767.98px) {
            .nh-workbench { padding: 18px 14px 28px; }
            .nh-workbench__hero { padding: 22px 20px; }
            .nh-workbench__hero-icon { width: 51px; height: 51px; flex-basis: 51px; border-radius: 14px; }
            .nh-workbench__hero-icon svg { width: 25px; height: 25px; }
            .nh-workbench__hero-icon i { font-size: 25px; }
            .nh-workbench__stats { grid-template-columns: minmax(0,1fr); }
            .nh-workbench__setup { padding: 21px; }
            .nh-workbench__setup-title { font-size: 19px; }
            .nh-workbench__steps { padding: 15px; }
            .nh-workbench__card { padding: 18px; }
            .nh-workbench__doctor-select { flex-wrap: wrap; }
        }
    </style>

    {{-- Отдельные Blade-шаблоны для стационара и амбулатории. --}}
    @if($workbenchMode === 'inpatient')
        @include('doctors.partials.workbench.navbars.inpatient')
    @else
        @include('doctors.partials.workbench.navbars.outpatient')
    @endif

    <div class="nh-workbench">
        <div class="nh-workbench__container">
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @error('attending_doctor_id')
                <div class="alert alert-danger" role="alert">{{ $message }}</div>
            @enderror

            <header class="nh-workbench__hero">
                <div>
                    <div class="nh-workbench__eyebrow">
                        @if($workbenchMode === 'outpatient')
                            Амбулаторный режим
                        @else
                            {{ $isAdmin ? 'Административный обзор стационара' : 'Личное пространство врача' }}
                        @endif
                    </div>
                    <h1 class="nh-workbench__title">
                        @if($workbenchMode === 'outpatient')
                            Обзор амбулатории
                        @else
                            {{ $tab === 'overview' ? ($isAdmin ? 'Обзор стационара' : 'Обзор рабочего дня') : ($isAdmin ? 'Пациенты стационара' : 'Мои пациенты') }}
                        @endif
                    </h1>
                    <p class="nh-workbench__subtitle">
                        @if($workbenchMode === 'outpatient')
                            Амбулаторный приём и записи пациентов в своей клинике.
                        @else
                            {{ $tab === 'overview'
                                ? ($isAdmin ? 'Состояние госпитализаций в стационарах системы.' : 'Важная информация и рабочие инструменты — в одном месте.')
                                : ($isAdmin ? 'Только текущие госпитализации в стационарах системы.' : 'Текущие госпитализации в доступных тебе отделениях.') }}
                        @endif
                    </p>
                    <div class="nh-workbench__institution">
                        <i class="bi bi-buildings" aria-hidden="true"></i>
                        <span>{{ $workbenchInstitutionTitle ?? 'Учреждение не указано' }}</span>
                        @if($workbenchInstitutionParent)
                            <small>Головная организация: {{ $workbenchInstitutionParent }}</small>
                        @endif
                    </div>
                </div>
                {{-- Декоративная иконка, не кнопка оформления. --}}
                <span class="nh-workbench__hero-icon" aria-hidden="true">
                    <i class="bi {{ $workbenchMode === 'inpatient' ? 'bi-hospital' : 'bi-person-lines-fill' }}"></i>
                </span>
            </header>





            @if($workbenchMode === 'outpatient')
                <section class="nh-workbench__card nh-workbench__full-card" aria-labelledby="workbenchOutpatientOverview">
                    <h2 class="nh-workbench__section-title" id="workbenchOutpatientOverview">Амбулаторный рабочий стол</h2>
                    <p class="nh-workbench__muted mt-2 mb-0">
                        Здесь будет персональный обзор амбулаторного приёма. Для работы с текущими записями
                        используй пункты «Регистратура» и «Записать на приём» в верхнем меню.
                        @if($isAdmin)
                            Для отладочной учётной записи перед переходом выбирается нужная амбулаторная клиника.
                        @endif
                    </p>
                </section>
            @elseif($departments->isEmpty() && $isAdmin)
                <section class="nh-workbench__card nh-workbench__setup" aria-labelledby="workbenchAdminSetupTitle">
                    <div class="nh-workbench__setup-top">
                        <span class="nh-workbench__status">Нет активных отделений</span>
                    </div>
                    <h2 id="workbenchAdminSetupTitle" class="nh-workbench__setup-title">Пока нечего показывать</h2>
                    <p class="nh-workbench__setup-description mb-0">
                        Не найдено активных отделений в учреждениях с типом «Стационар».
                        Проверь наличие отделений в «Настройки → Отделения» и значение clinic_type = inpatient у клиники.
                    </p>
                </section>
            @elseif($departments->isEmpty())
                <div class="nh-workbench__grid">
                    <section class="nh-workbench__card nh-workbench__setup" aria-labelledby="workbenchSetupTitle">
                        <div class="nh-workbench__setup-top">
                            <span class="nh-workbench__setup-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 20V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14M2 20h16"/><path d="M8 8h4M10 6v4M7 20v-5h5v5"/><path d="M17 9h4v11"/>
                                </svg>
                            </span>
                            <span class="nh-workbench__status">Ожидает настройки</span>
                        </div>
                        <h2 id="workbenchSetupTitle" class="nh-workbench__setup-title">Давай настроим твой рабочий стол</h2>
                        <p class="nh-workbench__setup-description">
                            Пока за твоей учётной записью врача не закреплено ни одного отделения.
                            Когда администратор назначит отделение, здесь появятся пациенты и данные по текущим госпитализациям.
                        </p>
                        <ol class="nh-workbench__steps" aria-label="Как получить доступ к отделению">
                            <li class="nh-workbench__step">
                                <span class="nh-workbench__step-number" aria-hidden="true">1</span>
                                <span>Обратись к администратору системы.</span>
                            </li>
                            <li class="nh-workbench__step">
                                <span class="nh-workbench__step-number" aria-hidden="true">2</span>
                                <span>В разделе «Настройки → Отделения» он должен включить тебя в состав врачей отделения или назначить заведующим.</span>
                            </li>
                            <li class="nh-workbench__step">
                                <span class="nh-workbench__step-number" aria-hidden="true">3</span>
                                <span>После назначения обнови страницу — рабочий стол заполнится доступными данными.</span>
                            </li>
                        </ol>
                    </section>
                    <aside class="nh-workbench__card" aria-label="Возможности рабочего стола">
                        <h2 class="nh-workbench__side-title">Что появится здесь</h2>
                        <p class="nh-workbench__side-intro">Всё необходимое для работы с пациентами стационара — без лишней статистики.</p>
                        <div class="nh-workbench__feature-list">
                            <div class="nh-workbench__feature">
                                <span class="nh-workbench__feature-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="6.5" r="3.5"/><path d="M18 8a3 3 0 0 1 0 6M22 20v-2a4 4 0 0 0-3-3.87"/>
                                    </svg>
                                </span>
                                <div><div class="nh-workbench__feature-title">Мои пациенты</div><p class="nh-workbench__feature-desc">Закреплённые и все пациенты доступного отделения.</p></div>
                            </div>
                            <div class="nh-workbench__feature">
                                <span class="nh-workbench__feature-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="5" width="18" height="15" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M8 14h3M8 17h6"/>
                                    </svg>
                                </span>
                                <div><div class="nh-workbench__feature-title">Обзор госпитализаций</div><p class="nh-workbench__feature-desc">Текущие поступления и количество пациентов.</p></div>
                            </div>
                            <div class="nh-workbench__feature">
                                <span class="nh-workbench__feature-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>
                                    </svg>
                                </span>
                                <div><div class="nh-workbench__feature-title">Быстрый доступ к медкарте</div><p class="nh-workbench__feature-desc">Переход к разрешённым медицинским картам из списка.</p></div>
                            </div>
                        </div>
                        <div class="nh-workbench__side-note">Данные пациентов отображаются только в пределах предоставленных прав доступа.</div>
                    </aside>
                </div>
            @else
                <div class="nh-workbench__stats">
                    <div class="nh-workbench__card nh-workbench__stat">
                        <div>
                            <div class="nh-workbench__stat-caption">{{ $isAdmin ? 'Без лечащего врача' : 'Мои пациенты' }}</div>
                            <div class="nh-workbench__metric">{{ $isAdmin ? $unassignedCount : $myCount }}</div>
                            <div class="nh-workbench__metric-label">{{ $isAdmin ? 'Текущие госпитализации без назначения' : 'Закреплены за тобой как за лечащим врачом' }}</div>
                        </div>
                        <span class="nh-workbench__stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="7" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 8a3 3 0 0 1 0 6M21 20v-2a6 6 0 0 0-3-4"/>
                            </svg>
                        </span>
                    </div>
                    <div class="nh-workbench__card nh-workbench__stat">
                        <div>
                            <div class="nh-workbench__stat-caption">{{ $isAdmin ? 'Госпитализировано' : 'Отделение' }}</div>
                            <div class="nh-workbench__metric">{{ $departmentCount }}</div>
                            <div class="nh-workbench__metric-label">{{ $isAdmin ? 'Во всех стационарах системы' : 'Всего пациентов в доступных отделениях' }}</div>
                        </div>
                        <span class="nh-workbench__stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 20h18M5 20V5h14v15M9 9h2M9 13h2M15 9h1M15 13h1M10 20v-4h4v4"/>
                            </svg>
                        </span>
                    </div>
                    <div class="nh-workbench__card nh-workbench__stat">
                        <div>
                            <div class="nh-workbench__stat-caption">Поступления</div>
                            <div class="nh-workbench__metric">{{ $admittedTodayCount }}</div>
                            <div class="nh-workbench__metric-label">Поступили сегодня и остаются в стационаре</div>
                        </div>
                        <span class="nh-workbench__stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14"/><circle cx="12" cy="12" r="10"/>
                            </svg>
                        </span>
                    </div>
                </div>

                @if($tab === 'overview')
                        <section class="nh-workbench__card nh-workbench__dashboard-table nh-workbench__full-card" aria-labelledby="workbenchMyPatientsHeading">
                            <div class="nh-workbench__section-heading">
                                <h2 id="workbenchMyPatientsHeading" class="nh-workbench__section-title">{{ $isAdmin ? 'Текущие госпитализации' : 'Закреплённые за мной' }}</h2>
                                <a href="{{ route('doctors.main', ['tab' => 'patients', 'scope' => $isAdmin ? 'department' : 'mine']) }}" class="nh-workbench__all-link">Все пациенты →</a>
                            </div>
                            <p class="nh-workbench__muted mb-3">{{ $isAdmin ? 'Пациенты действующих стационарных отделений.' : 'Текущие госпитализации, где ты указан лечащим врачом.' }}</p>
                            @include('doctors.partials.workbench-patients')
                        </section>
                @else
                    <section class="nh-workbench__card nh-workbench__full-card" aria-labelledby="workbenchPatientsHeading">
                        <div class="nh-workbench__section-heading nh-workbench__patients-heading">
                            <div class="nh-workbench__patients-heading-copy">
                                <h2 id="workbenchPatientsHeading" class="nh-workbench__section-title">Пациенты стационара</h2>
                                <p class="nh-workbench__muted mb-0">{{ $isAdmin ? 'Текущие госпитализации в стационарах системы' : 'Пациенты доступных отделений' }}</p>
                            </div>
                            <div class="nh-workbench__admission-toolbar">
                                @if(!$isAdmin || $quickActionInpatientClinics->isNotEmpty())
                                    <button type="button" class="nh-workbench__admission-btn"
                                            id="workbenchOpenAdmission"
                                            data-bs-toggle="modal" data-bs-target="#inpatientAdmitModal"
                                            title="Оформить новую госпитализацию" aria-controls="inpatientAdmitModal">
                                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                                        <span>Новая госпитализация</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @unless($isAdmin)
                        <div class="nh-workbench__switch" role="group" aria-label="Выбор списка пациентов">
                            <a class="nh-workbench__switch-link {{ $scope === 'mine' ? 'active' : '' }}"
                               href="{{ route('doctors.main', ['tab' => 'patients', 'scope' => 'mine', 'department_id' => $selectedDepartmentId ?: null, 'q' => $search ?: null]) }}"
                               @if($scope === 'mine') aria-current="page" @endif>Закреплённые ({{ $myCount }})</a>
                            <a class="nh-workbench__switch-link {{ $scope === 'department' ? 'active' : '' }}"
                               href="{{ route('doctors.main', ['tab' => 'patients', 'scope' => 'department', 'department_id' => $selectedDepartmentId ?: null, 'q' => $search ?: null]) }}"
                               @if($scope === 'department') aria-current="page" @endif>Всё отделение ({{ $departmentCount }})</a>
                        </div>
                        @endunless
                        <form class="nh-workbench__filters" action="{{ route('doctors.main') }}" method="get" role="search">
                            <input type="hidden" name="tab" value="patients">
                            <input type="hidden" name="scope" value="{{ $scope }}">
                            <div class="nh-workbench__filter">
                                <label for="wbDepartment" class="form-label small">Отделение</label>
                                <select id="wbDepartment" name="department_id" class="form-select">
                                    <option value="">Все доступные отделения</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected((int) $department->id === $selectedDepartmentId)>
                                            {{ $isAdmin ? (($department->clinic?->name ?? 'Клиника') . ' — ') : '' }}{{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="nh-workbench__filter nh-workbench__filter--search">
                                <label for="wbSearch" class="form-label small">Поиск по ФИО</label>
                                <input id="wbSearch" class="form-control" type="search" name="q"
                                       value="{{ $search }}" maxlength="100" placeholder="Введите ФИО пациента">
                            </div>
                            <button type="submit" class="btn btn-info text-white">Найти</button>
                            <a class="btn btn-outline-secondary" href="{{ route('doctors.main', ['tab' => 'patients', 'scope' => $scope]) }}">Сбросить</a>
                        </form>
                        @include('doctors.partials.workbench-patients')
                    </section>
                @endif
            @endif
        </div>
    </div>

    {{-- Форма доступна только на вкладке «Пациенты стационара». Навигации на другой URL нет. --}}
    @if($workbenchMode === 'inpatient' && $tab === 'patients' && (!$isAdmin || $quickActionInpatientClinics->isNotEmpty()))
        @include('doctors.partials.workbench.inpatient-admission-modal')
    @endif

    <script>
    (() => {
        'use strict';
        const initialize = () => {
            let closeActive = null;

            document.querySelectorAll('[data-nh-doctor-picker]').forEach((holder, index) => {
                const select = holder.querySelector('select[name="attending_doctor_id"]');
                if (!select || holder.classList.contains('is-enhanced')) return;

                // Сохраняем реальный HTML select для отправки ID врача и работы без JS.
                const values = Array.from(select.options).map(option => ({
                    value: option.value,
                    label: option.textContent.trim()
                }));

                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'nh-workbench__doctor-trigger';
                trigger.setAttribute('role', 'combobox');
                trigger.setAttribute('aria-label', select.getAttribute('aria-label') || 'Лечащий врач');
                trigger.setAttribute('aria-haspopup', 'listbox');
                trigger.setAttribute('aria-expanded', 'false');

                const label = document.createElement('span');
                label.className = 'nh-workbench__doctor-trigger-label';
                const arrow = document.createElement('span');
                arrow.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
                arrow.setAttribute('aria-hidden', 'true');
                trigger.append(label, arrow);

                const popup = document.createElement('div');
                popup.className = 'nh-workbench__doctor-popover';
                popup.hidden = true;

                const filter = document.createElement('input');
                filter.type = 'search';
                filter.className = 'nh-workbench__doctor-filter';
                filter.autocomplete = 'off';
                filter.placeholder = 'Поиск врача по ФИО…';
                filter.setAttribute('aria-label', 'Поиск по списку врачей');
                const list = document.createElement('div');
                list.className = 'nh-workbench__doctor-options';
                list.id = `nh-doctor-choices-${select.id || index}`;
                list.setAttribute('role', 'listbox');
                list.setAttribute('aria-label', 'Врачи отделения');
                trigger.setAttribute('aria-controls', list.id);

                popup.append(filter, list);
                document.body.appendChild(popup);
                holder.appendChild(trigger);
                holder.classList.add('is-enhanced');

                const selectedLabel = () => {
                    const current = values.find(item => item.value === select.value);
                    label.textContent = current?.label || 'Не назначен';
                    trigger.title = label.textContent;
                };
                selectedLabel();

                const close = () => {
                    popup.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                    if (closeActive === close) closeActive = null;
                };

                const choose = item => {
                    select.value = item.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    selectedLabel();
                    close();
                    trigger.focus();
                };

                const render = () => {
                    list.replaceChildren();
                    const term = filter.value.toLocaleLowerCase('ru-RU').trim();
                    const matches = values.filter(item =>
                        item.label.toLocaleLowerCase('ru-RU').includes(term)
                    );
                    if (!matches.length) {
                        const empty = document.createElement('div');
                        empty.className = 'nh-workbench__doctor-no-results';
                        empty.textContent = 'Врачи не найдены';
                        list.appendChild(empty);
                        return;
                    }
                    matches.forEach(item => {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'nh-workbench__doctor-option';
                        option.textContent = item.label;
                        option.setAttribute('role', 'option');
                        option.setAttribute('aria-selected', String(item.value === select.value));
                        option.addEventListener('click', () => choose(item));
                        option.addEventListener('keydown', event => {
                            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                                event.preventDefault();
                                const items = Array.from(list.querySelectorAll('button'));
                                const current = items.indexOf(option);
                                const next = items[current + (event.key === 'ArrowDown' ? 1 : -1)];
                                if (next) next.focus();
                                else if (event.key === 'ArrowUp') filter.focus();
                            }
                        });
                        list.appendChild(option);
                    });
                };

                const position = () => {
                    if (popup.hidden) return;
                    const rect = trigger.getBoundingClientRect();
                    const width = Math.min(Math.max(rect.width, 260), window.innerWidth - 16);
                    popup.style.width = `${width}px`;
                    popup.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - width - 8))}px`;
                    const height = popup.offsetHeight;
                    const spaceBelow = window.innerHeight - rect.bottom - 8;
                    const above = spaceBelow < Math.min(height, 190) && rect.top > spaceBelow;
                    const top = above ? rect.top - height - 5 : rect.bottom + 5;
                    popup.style.top = `${Math.max(8, Math.min(top, window.innerHeight - height - 8))}px`;
                };

                const open = () => {
                    if (closeActive && closeActive !== close) closeActive();
                    closeActive = close;
                    filter.value = '';
                    render();
                    popup.hidden = false;
                    trigger.setAttribute('aria-expanded', 'true');
                    position();
                    filter.focus();
                };

                trigger.addEventListener('click', () => popup.hidden ? open() : close());
                select.addEventListener('change', selectedLabel);
                filter.addEventListener('input', () => { render(); position(); });
                popup.addEventListener('keydown', event => {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        close();
                        trigger.focus();
                    } else if (event.target === filter && event.key === 'ArrowDown') {
                        event.preventDefault();
                        list.querySelector('button')?.focus();
                    } else if (event.target === filter && event.key === 'Enter') {
                        event.preventDefault();
                        list.querySelector('button')?.click();
                    }
                });

                document.addEventListener('pointerdown', event => {
                    if (!popup.hidden && !holder.contains(event.target) && !popup.contains(event.target)) close();
                });
                document.addEventListener('focusin', event => {
                    if (!popup.hidden && !holder.contains(event.target) && !popup.contains(event.target)) close();
                });
                window.addEventListener('resize', position);
                window.addEventListener('scroll', event => {
                    if (!popup.hidden && !popup.contains(event.target)) position();
                }, true);
            });
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initialize, { once: true });
        } else {
            initialize();
        }
    })();
    </script>
@endsection
