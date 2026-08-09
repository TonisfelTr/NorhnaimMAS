@extends('doctors.reception')

@section('title', trim($__env->yieldContent('prescription-title', 'Рецептурный отпуск')))

@section('assets')
    @parent

    <style>
        :root {
            --rx-primary: #048696;
            --rx-primary-light: #10b7c6;
            --rx-primary-dark: #036b78;
            --rx-bg: #f4f7fb;
            --rx-card: #ffffff;
            --rx-border: #e3e9ef;
            --rx-text: #1f2937;
            --rx-muted: #6b7280;
            --rx-danger: #dc3545;
            --rx-warning: #f59e0b;
            --rx-success: #16a34a;
            --rx-blue: #2563eb;
        }

        .rx-topbar {
            min-height: 68px;
            padding: 0;
            background: linear-gradient(90deg, var(--rx-primary), #079aa9);
            box-shadow: 0 12px 28px rgba(4, 134, 150, .18);
        }

        .rx-topbar__container {
            min-height: 68px;
            padding-inline: 24px;
            gap: 22px;
        }

        .rx-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .rx-brand:hover {
            color: #fff;
        }

        .rx-brand__icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, .16);
            font-size: 18px;
        }

        .rx-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rx-nav__link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 8px;
            padding: 8px 15px !important;
            border-radius: 11px;
            color: rgba(255, 255, 255, .9) !important;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .rx-nav__link:hover,
        .rx-nav__link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, .16);
        }

        .rx-topbar__actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .rx-topbar__action {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 13px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 11px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 13px;
            font-weight: 750;
            text-decoration: none;
        }

        .rx-topbar__action:hover {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .rx-navbar-toggler {
            border-color: rgba(255, 255, 255, .35);
            background: rgba(255, 255, 255, .12);
        }

        .rx-navbar-toggler:focus {
            box-shadow: 0 0 0 .2rem rgba(255, 255, 255, .16);
        }

        .rx-page {
            min-height: calc(100vh - 68px);
            padding: 24px 28px 44px;
            background:
                radial-gradient(circle at 0 0, rgba(4, 134, 150, .07), transparent 28%),
                var(--rx-bg);
            color: var(--rx-text);
        }

        .rx-container {
            width: min(1480px, 100%);
            margin: 0 auto;
        }

        .rx-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 22px;
            padding: 28px;
            border: 1px solid var(--rx-border);
            border-radius: 24px;
            background: linear-gradient(135deg, #fff 0%, #effafb 100%);
            box-shadow: 0 16px 42px rgba(15, 23, 42, .06);
        }

        .rx-hero::after {
            content: '';
            position: absolute;
            top: -90px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 183, 198, .2), transparent 70%);
            pointer-events: none;
        }

        .rx-hero__content,
        .rx-hero__actions {
            position: relative;
            z-index: 1;
        }

        .rx-kicker {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            gap: 7px;
            margin-bottom: 11px;
            padding: 5px 11px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--rx-primary-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .rx-title {
            margin: 0;
            color: var(--rx-text);
            font-size: clamp(28px, 3vw, 38px);
            font-weight: 850;
            line-height: 1.12;
            letter-spacing: -.035em;
        }

        .rx-description {
            max-width: 900px;
            margin: 11px 0 0;
            color: var(--rx-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .rx-hero__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 9px;
        }

        .rx-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
            transition: .16s ease;
        }

        .rx-button--primary {
            border-color: var(--rx-primary);
            background: var(--rx-primary);
            color: #fff;
        }

        .rx-button--primary:hover {
            border-color: var(--rx-primary-dark);
            background: var(--rx-primary-dark);
            color: #fff;
        }

        .rx-button--secondary {
            border-color: #d6e0e7;
            background: #fff;
            color: #344054;
        }

        .rx-button--secondary:hover {
            border-color: #c3d0da;
            background: #f8fafc;
            color: #111827;
        }

        .rx-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .rx-stat {
            position: relative;
            overflow: hidden;
            min-height: 116px;
            padding: 19px 20px;
            border: 1px solid var(--rx-border);
            border-radius: 20px;
            background: var(--rx-card);
            box-shadow: 0 12px 34px rgba(15, 23, 42, .05);
        }

        .rx-stat::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, var(--rx-primary), var(--rx-primary-light));
        }

        .rx-stat--blue::before { background: var(--rx-blue); }
        .rx-stat--warning::before { background: var(--rx-warning); }
        .rx-stat--success::before { background: var(--rx-success); }

        .rx-stat__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .rx-stat__label {
            color: var(--rx-muted);
            font-size: 13px;
            font-weight: 750;
        }

        .rx-stat__icon {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 11px;
            background: #eef8fa;
            color: var(--rx-primary-dark);
        }

        .rx-stat__value {
            margin-top: 8px;
            color: var(--rx-text);
            font-size: 31px;
            font-weight: 850;
            line-height: 1;
        }

        .rx-stat__hint {
            margin-top: 7px;
            color: #8b95a1;
            font-size: 12px;
        }

        .rx-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(290px, .75fr);
            gap: 20px;
            align-items: start;
        }

        .rx-panel {
            overflow: hidden;
            border: 1px solid var(--rx-border);
            border-radius: 21px;
            background: var(--rx-card);
            box-shadow: 0 14px 38px rgba(15, 23, 42, .055);
        }

        .rx-panel + .rx-panel {
            margin-top: 18px;
        }

        .rx-panel__header {
            display: flex;
            min-height: 62px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--rx-border);
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .rx-panel__title {
            margin: 0;
            color: var(--rx-text);
            font-size: 18px;
            font-weight: 820;
        }

        .rx-panel__subtitle {
            margin-top: 4px;
            color: var(--rx-muted);
            font-size: 12px;
        }

        .rx-panel__body {
            padding: 20px;
        }

        .rx-count {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            padding: 4px 10px;
            border: 1px solid #dce5eb;
            border-radius: 999px;
            background: #f8fafc;
            color: #52606d;
            font-size: 12px;
            font-weight: 750;
            white-space: nowrap;
        }

        .rx-filters {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }

        .rx-filter--2 { grid-column: span 2; }
        .rx-filter--3 { grid-column: span 3; }
        .rx-filter--4 { grid-column: span 4; }
        .rx-filter--5 { grid-column: span 5; }
        .rx-filter--12 { grid-column: span 12; }

        .rx-label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-size: 12px;
            font-weight: 750;
        }

        .rx-control {
            width: 100%;
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #d8e0e5;
            border-radius: 12px;
            background: #fff;
            color: var(--rx-text);
            font-size: 13px;
        }

        .rx-control:focus {
            border-color: var(--rx-primary);
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .13);
        }

        .rx-table-wrap {
            overflow-x: auto;
        }

        .rx-table {
            width: 100%;
            min-width: 960px;
            margin: 0;
            border-collapse: collapse;
            vertical-align: middle;
        }

        .rx-table thead th {
            padding: 14px 17px;
            border-bottom: 1px solid var(--rx-border);
            background: #f8fafc;
            color: #667085;
            font-size: 12px;
            font-weight: 800;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .rx-table tbody td {
            padding: 16px 17px;
            border-bottom: 1px solid #eef2f6;
            color: var(--rx-text);
            font-size: 13px;
            vertical-align: middle;
        }

        .rx-table tbody tr:hover {
            background: #f8fcfd;
        }

        .rx-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .rx-patient {
            display: flex;
            min-width: 210px;
            align-items: center;
            gap: 11px;
        }

        .rx-avatar {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--rx-primary), var(--rx-primary-light));
            color: #fff;
            font-size: 13px;
            font-weight: 850;
        }

        .rx-primary-text {
            color: var(--rx-text);
            font-size: 14px;
            font-weight: 780;
        }

        .rx-secondary-text {
            margin-top: 3px;
            color: var(--rx-muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .rx-status {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 760;
            white-space: nowrap;
        }

        .rx-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .rx-status--active { background: #eaf8ef; color: #15803d; }
        .rx-status--printed { background: #eaf1ff; color: #1d4ed8; }
        .rx-status--warning { background: #fff7e6; color: #b45309; }
        .rx-status--draft { background: #eef2f7; color: #475569; }

        .rx-actions {
            display: flex;
            justify-content: flex-end;
            gap: 7px;
        }

        .rx-action {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 10px;
            border: 1px solid #dce5eb;
            border-radius: 10px;
            background: #f8fafc;
            color: #344054;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            white-space: nowrap;
        }

        .rx-action:hover {
            border-color: #cbd7df;
            background: #eef3f6;
            color: #111827;
        }

        .rx-action--primary {
            border-color: var(--rx-primary);
            background: var(--rx-primary);
            color: #fff;
        }

        .rx-action--primary:hover {
            border-color: var(--rx-primary-dark);
            background: var(--rx-primary-dark);
            color: #fff;
        }

        .rx-info-list {
            display: grid;
            gap: 12px;
        }

        .rx-info-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 11px;
            border-bottom: 1px solid #edf1f4;
        }

        .rx-info-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .rx-info-row__label {
            color: var(--rx-muted);
            font-size: 12px;
        }

        .rx-info-row__value {
            color: var(--rx-text);
            font-size: 13px;
            font-weight: 750;
            text-align: right;
        }

        .rx-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 15px;
            border: 1px solid #cfe9ed;
            border-radius: 14px;
            background: #eef8fa;
            color: #285b63;
            font-size: 13px;
            line-height: 1.55;
        }

        .rx-note--warning {
            border-color: #fde2ad;
            background: #fff8e9;
            color: #8a5a0a;
        }

        .rx-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            border-top: 1px solid var(--rx-border);
            color: var(--rx-muted);
            font-size: 12px;
        }

        .rx-pagination__buttons {
            display: flex;
            gap: 6px;
        }

        .rx-page-button {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 1px solid var(--rx-border);
            border-radius: 9px;
            background: #fff;
            color: #475467;
            font-size: 12px;
            font-weight: 800;
        }

        .rx-page-button.is-active {
            border-color: var(--rx-primary);
            background: var(--rx-primary);
            color: #fff;
        }


        /* Расширенная верхняя навигация */
        .rx-nav__dropdown .dropdown-toggle::after {
            margin-left: 2px;
            opacity: .72;
        }

        .rx-nav__dropdown-menu,
        .rx-create-menu {
            min-width: 290px;
            margin-top: 10px !important;
            padding: 8px;
            border: 1px solid #dfe7ec;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 46px rgba(15, 23, 42, .16);
        }

        .rx-nav__dropdown-item {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 10px 11px;
            border-radius: 11px;
            color: #344054;
            text-decoration: none;
            transition: .14s ease;
        }

        .rx-nav__dropdown-item:hover,
        .rx-nav__dropdown-item.active {
            background: #eef8fa;
            color: var(--rx-primary-dark);
        }

        .rx-nav__dropdown-item.is-disabled {
            cursor: default;
            opacity: .52;
            pointer-events: none;
        }

        .rx-nav__dropdown-icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 10px;
            background: #f2f7f8;
            color: var(--rx-primary-dark);
            font-size: 14px;
        }

        .rx-nav__dropdown-copy {
            min-width: 0;
            flex: 1;
        }

        .rx-nav__dropdown-title {
            display: flex;
            align-items: center;
            gap: 7px;
            color: inherit;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.25;
        }

        .rx-nav__dropdown-hint {
            margin-top: 3px;
            color: #83909f;
            font-size: 11px;
            line-height: 1.35;
        }

        .rx-nav__dropdown-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            border-radius: 999px;
            background: #f2f4f7;
            color: #667085;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.4;
            white-space: nowrap;
        }

        .rx-nav__dropdown-divider {
            height: 1px;
            margin: 6px 4px;
            background: #edf1f4;
        }

        .rx-topbar__action--primary {
            border-color: rgba(255, 255, 255, .36);
            background: rgba(255, 255, 255, .20);
        }

        .rx-topbar__action--primary:hover,
        .rx-topbar__action--primary.show {
            background: rgba(255, 255, 255, .28);
        }

        .rx-create-menu .rx-nav__dropdown-item {
            width: 100%;
        }

        @media (max-width: 991.98px) {
            .rx-nav__dropdown-menu,
            .rx-create-menu {
                min-width: 100%;
                margin-top: 4px !important;
                border-color: rgba(255, 255, 255, .18);
                background: rgba(255, 255, 255, .97);
                box-shadow: none;
            }

            .rx-nav__dropdown {
                width: 100%;
            }

            .rx-nav__dropdown > .rx-nav__link {
                width: 100%;
                justify-content: space-between;
            }

            .rx-topbar__actions .dropdown {
                width: 100%;
            }

            .rx-topbar__actions .dropdown > .rx-topbar__action {
                justify-content: center;
            }
        }

        @media print {
            .rx-topbar,
            .rx-hero__actions,
            .rx-actions,
            .rx-filters {
                display: none !important;
            }

            .rx-page {
                padding: 0;
                background: #fff;
            }

            .rx-panel,
            .rx-hero,
            .rx-stat {
                box-shadow: none;
            }
        }

        @media (max-width: 1199.98px) {
            .rx-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rx-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .rx-topbar__container {
                padding-block: 10px;
            }

            .rx-nav,
            .rx-topbar__actions {
                align-items: stretch;
                flex-direction: column;
                margin: 12px 0 0;
            }

            .rx-nav__link,
            .rx-topbar__action {
                width: 100%;
            }

            .rx-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rx-filter--2,
            .rx-filter--3,
            .rx-filter--4,
            .rx-filter--5,
            .rx-filter--12 {
                grid-column: span 1;
            }
        }

        @media (max-width: 767.98px) {
            .rx-page {
                padding: 16px 12px 30px;
            }

            .rx-hero {
                flex-direction: column;
                padding: 21px;
                border-radius: 19px;
            }

            .rx-hero__actions {
                justify-content: flex-start;
                width: 100%;
            }

            .rx-button {
                flex: 1 1 auto;
            }

            .rx-stats,
            .rx-filters {
                grid-template-columns: 1fr;
            }

            .rx-panel__header,
            .rx-pagination {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('main')
    @php
        $specialistReferralRoute = \Illuminate\Support\Facades\Route::has(
            'doctors.referrals.specialists.index'
        )
            ? route('doctors.referrals.specialists.index')
            : null;

        $specialistReferralCreateRoute = \Illuminate\Support\Facades\Route::has(
            'doctors.referrals.specialists.create'
        )
            ? route('doctors.referrals.specialists.create')
            : null;

        $hospitalReferralRoute = \Illuminate\Support\Facades\Route::has(
            'doctors.referrals.hospital.index'
        )
            ? route('doctors.referrals.hospital.index')
            : null;

        $hospitalReferralCreateRoute = \Illuminate\Support\Facades\Route::has(
            'doctors.referrals.hospital.create'
        )
            ? route('doctors.referrals.hospital.create')
            : null;

        $consultationsRoute = \Illuminate\Support\Facades\Route::has(
            'doctors.referrals.consultations.index'
        )
            ? route('doctors.referrals.consultations.index')
            : null;

        $labAssignmentsRoute = \Illuminate\Support\Facades\Route::has(
            'doctors.analyses.assignments'
        )
            ? route('doctors.analyses.assignments')
            : null;

        $isPrescriptionSection = request()->routeIs(
            'doctors.prescriptions.index',
            'doctors.prescriptions.new',
            'doctors.prescriptions.print*',
            'doctors.prescriptions.repeat'
        );

        $isReferralSection = request()->routeIs(
            'doctors.referrals.*',
            'doctors.analyses.assignments*'
        );

        $isReferenceSection = request()->routeIs(
            'doctors.prescriptions.base'
        );
    @endphp

    <nav class="navbar navbar-expand-lg rx-topbar">
        <div class="container-fluid rx-topbar__container">
            <a
                class="rx-brand"
                href="{{ route('doctors.prescriptions.index') }}"
                title="Медицинские назначения и выписные документы"
            >
                <span class="rx-brand__icon">
                    <i class="bi bi-clipboard2-pulse"></i>
                </span>

                <span>Назначения</span>
            </a>

            <button
                class="navbar-toggler rx-navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#rxNavbar"
                aria-controls="rxNavbar"
                aria-expanded="false"
                aria-label="Переключить навигацию"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="rxNavbar">
                <ul class="navbar-nav rx-nav me-auto">
                    <li class="nav-item">
                        <a
                            class="nav-link rx-nav__link {{ $isPrescriptionSection ? 'active' : '' }}"
                            href="{{ route('doctors.prescriptions.index') }}"
                        >
                            <i class="bi bi-prescription2"></i>
                            Рецепты
                        </a>
                    </li>

                    <li class="nav-item dropdown rx-nav__dropdown">
                        <a
                            class="nav-link rx-nav__link dropdown-toggle {{ $isReferralSection ? 'active' : '' }}"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <span>
                                <i class="bi bi-signpost-split me-2"></i>
                                Направления
                            </span>
                        </a>

                        <div class="dropdown-menu rx-nav__dropdown-menu">
                            <a
                                class="rx-nav__dropdown-item {{ $labAssignmentsRoute ? '' : 'is-disabled' }}"
                                href="{{ $labAssignmentsRoute ?: '#' }}"
                                @if(!$labAssignmentsRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-droplet-half"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        Лабораторные исследования
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Назначение анализов и печать направления
                                    </span>
                                </span>
                            </a>

                            <a
                                class="rx-nav__dropdown-item {{ $specialistReferralRoute ? '' : 'is-disabled' }}"
                                href="{{ $specialistReferralRoute ?: '#' }}"
                                @if(!$specialistReferralRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-person-badge"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        К профильному специалисту
                                        @if(!$specialistReferralRoute)
                                            <span class="rx-nav__dropdown-badge">подключить</span>
                                        @endif
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Консультация врача другой специальности
                                    </span>
                                </span>
                            </a>

                            <a
                                class="rx-nav__dropdown-item {{ $hospitalReferralRoute ? '' : 'is-disabled' }}"
                                href="{{ $hospitalReferralRoute ?: '#' }}"
                                @if(!$hospitalReferralRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-hospital"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        В стационар
                                        @if(!$hospitalReferralRoute)
                                            <span class="rx-nav__dropdown-badge">подключить</span>
                                        @endif
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Плановая или экстренная госпитализация
                                    </span>
                                </span>
                            </a>

                            <a
                                class="rx-nav__dropdown-item {{ $consultationsRoute ? '' : 'is-disabled' }}"
                                href="{{ $consultationsRoute ?: '#' }}"
                                @if(!$consultationsRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-people"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        Консилиум / консультация
                                        @if(!$consultationsRoute)
                                            <span class="rx-nav__dropdown-badge">подключить</span>
                                        @endif
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Внутреннее или внешнее консультирование
                                    </span>
                                </span>
                            </a>
                        </div>
                    </li>

                    <li class="nav-item dropdown rx-nav__dropdown">
                        <a
                            class="nav-link rx-nav__link dropdown-toggle {{ $isReferenceSection ? 'active' : '' }}"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <span>
                                <i class="bi bi-journal-bookmark me-2"></i>
                                Справочники
                            </span>
                        </a>

                        <div class="dropdown-menu rx-nav__dropdown-menu">
                            <a
                                class="rx-nav__dropdown-item {{ request()->routeIs('doctors.prescriptions.base') ? 'active' : '' }}"
                                href="{{ route('doctors.prescriptions.base') }}"
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-capsule-pill"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        База препаратов
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Формы, дозировки, показания и противопоказания
                                    </span>
                                </span>
                            </a>
                        </div>
                    </li>
                </ul>

                <div class="rx-topbar__actions">
                    <div class="dropdown">
                        <button
                            class="rx-topbar__action rx-topbar__action--primary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Оформить
                        </button>

                        <div class="dropdown-menu dropdown-menu-end rx-create-menu">
                            <a
                                class="rx-nav__dropdown-item"
                                href="{{ route('doctors.prescriptions.new') }}"
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-prescription2"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        Новый рецепт
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Лекарственное назначение и печатная форма
                                    </span>
                                </span>
                            </a>

                            <a
                                class="rx-nav__dropdown-item {{ $labAssignmentsRoute ? '' : 'is-disabled' }}"
                                href="{{ $labAssignmentsRoute ?: '#' }}"
                                @if(!$labAssignmentsRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-droplet-half"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        Направление на анализы
                                    </span>
                                    <span class="rx-nav__dropdown-hint">
                                        Лабораторное исследование
                                    </span>
                                </span>
                            </a>

                            <div class="rx-nav__dropdown-divider"></div>

                            <a
                                class="rx-nav__dropdown-item {{ $specialistReferralCreateRoute ? '' : 'is-disabled' }}"
                                href="{{ $specialistReferralCreateRoute ?: '#' }}"
                                @if(!$specialistReferralCreateRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-person-plus"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        К специалисту
                                        @if(!$specialistReferralCreateRoute)
                                            <span class="rx-nav__dropdown-badge">подключить</span>
                                        @endif
                                    </span>
                                </span>
                            </a>

                            <a
                                class="rx-nav__dropdown-item {{ $hospitalReferralCreateRoute ? '' : 'is-disabled' }}"
                                href="{{ $hospitalReferralCreateRoute ?: '#' }}"
                                @if(!$hospitalReferralCreateRoute) aria-disabled="true" @endif
                            >
                                <span class="rx-nav__dropdown-icon">
                                    <i class="bi bi-hospital"></i>
                                </span>

                                <span class="rx-nav__dropdown-copy">
                                    <span class="rx-nav__dropdown-title">
                                        В стационар
                                        @if(!$hospitalReferralCreateRoute)
                                            <span class="rx-nav__dropdown-badge">подключить</span>
                                        @endif
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>

                    <button
                        class="rx-topbar__action"
                        type="button"
                        onclick="window.location.reload()"
                    >
                        <i class="bi bi-arrow-clockwise"></i>
                        Обновить
                    </button>
                </div>
            </div>
        </div>
    </nav>
    @yield('sub-main')
@endsection
