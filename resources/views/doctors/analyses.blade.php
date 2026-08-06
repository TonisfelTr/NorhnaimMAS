@extends('doctors.reception')
@section('title', trim($__env->yieldContent('analysis-title', 'Лабораторные исследования')))

@section('assets')
    @parent

    <style>
        :root {
            --lab-primary: #048696;
            --lab-primary-light: #10b7c6;
            --lab-primary-dark: #036b78;
            --lab-bg: #f4f7fb;
            --lab-card: #fff;
            --lab-border: #e3e9ef;
            --lab-text: #1f2937;
            --lab-muted: #6b7280;
            --lab-danger: #dc3545;
            --lab-warning: #f59e0b;
            --lab-success: #16a34a;
        }

        .lab-topbar {
            min-height: 68px;
            padding: 0;
            background: linear-gradient(90deg, var(--lab-primary), #079aa9);
            box-shadow: 0 12px 28px rgba(4, 134, 150, .18);
        }

        .lab-topbar__container {
            min-height: 68px;
            padding-inline: 24px;
            gap: 22px;
        }

        .lab-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .lab-brand:hover {
            color: #fff;
        }

        .lab-brand__icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, .16);
        }

        .lab-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lab-nav__link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 8px;
            padding: 8px 15px !important;
            border-radius: 11px;
            color: rgba(255, 255, 255, .9) !important;
            font-size: 14px;
            font-weight: 700;
        }

        .lab-nav__link:hover,
        .lab-nav__link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, .16);
        }

        .lab-topbar__actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .lab-topbar__action {
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

        .lab-topbar__action:hover {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .lab-page {
            min-height: calc(100vh - 68px);
            padding: 24px 28px 44px;
            background: var(--lab-bg);
            color: var(--lab-text);
        }

        .lab-container {
            width: min(1480px, 100%);
            margin: 0 auto;
        }

        .lab-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 22px;
            padding: 28px;
            border: 1px solid var(--lab-border);
            border-radius: 24px;
            background: linear-gradient(135deg, #fff 0%, #effafb 100%);
            box-shadow: 0 16px 42px rgba(15, 23, 42, .06);
        }

        .lab-hero::after {
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

        .lab-hero__content,
        .lab-hero__actions {
            position: relative;
            z-index: 1;
        }

        .lab-kicker {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            margin-bottom: 11px;
            padding: 5px 11px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--lab-primary-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .lab-title {
            margin: 0;
            color: var(--lab-text);
            font-size: clamp(28px, 3vw, 38px);
            font-weight: 850;
            line-height: 1.12;
            letter-spacing: -.035em;
        }

        .lab-description {
            max-width: 930px;
            margin: 11px 0 0;
            color: var(--lab-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .lab-hero__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 9px;
        }

        .lab-button {
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

        .lab-button--primary {
            border-color: var(--lab-primary);
            background: var(--lab-primary);
            color: #fff;
        }

        .lab-button--primary:hover {
            border-color: var(--lab-primary-dark);
            background: var(--lab-primary-dark);
            color: #fff;
        }

        .lab-button--secondary {
            border-color: #d6e0e7;
            background: #fff;
            color: #344054;
        }

        .lab-button--secondary:hover {
            border-color: #c3d0da;
            background: #f8fafc;
            color: #111827;
        }

        .lab-button--success {
            border-color: var(--lab-success);
            background: var(--lab-success);
            color: #fff;
        }

        .lab-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .lab-stat {
            position: relative;
            overflow: hidden;
            min-height: 116px;
            padding: 19px 20px;
            border: 1px solid var(--lab-border);
            border-radius: 20px;
            background: var(--lab-card);
            box-shadow: 0 12px 34px rgba(15, 23, 42, .05);
        }

        .lab-stat::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, var(--lab-primary), var(--lab-primary-light));
        }

        .lab-stat--danger::before { background: var(--lab-danger); }
        .lab-stat--warning::before { background: var(--lab-warning); }
        .lab-stat--success::before { background: var(--lab-success); }
        .lab-stat--neutral::before { background: #94a3b8; }

        .lab-stat__label {
            color: var(--lab-muted);
            font-size: 13px;
            font-weight: 750;
        }

        .lab-stat__value {
            margin-top: 8px;
            color: var(--lab-text);
            font-size: 31px;
            font-weight: 850;
            line-height: 1;
        }

        .lab-stat__hint {
            margin-top: 7px;
            color: #8b95a1;
            font-size: 12px;
        }

        .lab-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(290px, .75fr);
            gap: 20px;
            align-items: start;
        }

        .lab-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, .62fr);
            gap: 20px;
            align-items: start;
        }

        .lab-panel {
            overflow: hidden;
            border: 1px solid var(--lab-border);
            border-radius: 21px;
            background: var(--lab-card);
            box-shadow: 0 14px 38px rgba(15, 23, 42, .055);
        }

        .lab-panel + .lab-panel {
            margin-top: 18px;
        }

        .lab-panel__header {
            display: flex;
            min-height: 62px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--lab-border);
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .lab-panel__title {
            margin: 0;
            color: var(--lab-text);
            font-size: 18px;
            font-weight: 820;
        }

        .lab-panel__subtitle {
            margin-top: 4px;
            color: var(--lab-muted);
            font-size: 12px;
        }

        .lab-panel__body {
            padding: 20px;
        }

        .lab-count {
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

        .lab-filters {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }

        .lab-filter--2 { grid-column: span 2; }
        .lab-filter--3 { grid-column: span 3; }
        .lab-filter--4 { grid-column: span 4; }
        .lab-filter--6 { grid-column: span 6; }
        .lab-filter--12 { grid-column: span 12; }

        .lab-label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-size: 12px;
            font-weight: 750;
        }

        .lab-control {
            width: 100%;
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #d8e0e5;
            border-radius: 12px;
            background: #fff;
            color: var(--lab-text);
            font-size: 13px;
        }

        .lab-control:focus {
            border-color: var(--lab-primary);
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .13);
        }

        .lab-table-wrap {
            overflow-x: auto;
        }

        .lab-table {
            width: 100%;
            margin: 0;
            vertical-align: middle;
        }

        .lab-table thead th {
            padding: 14px 17px;
            border-bottom: 1px solid var(--lab-border);
            background: #f8fafc;
            color: #667085;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .lab-table tbody td {
            padding: 16px 17px;
            border-bottom: 1px solid #eef2f6;
            color: var(--lab-text);
            font-size: 13px;
        }

        .lab-table tbody tr:hover {
            background: #f8fcfd;
        }

        .lab-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .lab-patient {
            display: flex;
            min-width: 220px;
            align-items: center;
            gap: 11px;
        }

        .lab-avatar {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--lab-primary), var(--lab-primary-light));
            color: #fff;
            font-size: 13px;
            font-weight: 850;
        }

        .lab-primary-text {
            color: var(--lab-text);
            font-size: 14px;
            font-weight: 780;
        }

        .lab-secondary-text {
            margin-top: 3px;
            color: var(--lab-muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .lab-status {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 760;
            white-space: nowrap;
        }

        .lab-status--normal { background: #eaf8ef; color: #15803d; }
        .lab-status--warning { background: #fff7e6; color: #b45309; }
        .lab-status--critical { background: #feecec; color: #b91c1c; }
        .lab-status--new { background: #e8f8fa; color: var(--lab-primary-dark); }
        .lab-status--pending { background: #eef2f7; color: #475569; }
        .lab-status--progress { background: #eaf1ff; color: #1d4ed8; }

        .lab-result-value {
            color: var(--lab-text);
            font-size: 18px;
            font-weight: 850;
            line-height: 1;
        }

        .lab-result-value--danger { color: var(--lab-danger); }
        .lab-result-value--warning { color: #b45309; }

        .lab-actions {
            display: flex;
            justify-content: flex-end;
            gap: 7px;
        }

        .lab-action {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            padding: 7px 11px;
            border: 1px solid #dce5eb;
            border-radius: 10px;
            background: #f8fafc;
            color: #344054;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            white-space: nowrap;
        }

        .lab-action:hover {
            border-color: #cbd7df;
            background: #eef3f6;
            color: #111827;
        }

        .lab-action--primary {
            border-color: var(--lab-primary);
            background: var(--lab-primary);
            color: #fff;
        }

        .lab-action--primary:hover {
            border-color: var(--lab-primary-dark);
            background: var(--lab-primary-dark);
            color: #fff;
        }

        .lab-empty {
            padding: 48px 22px 54px;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: #f8fafc;
            color: var(--lab-muted);
            text-align: center;
        }

        .lab-empty__icon {
            display: grid;
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            place-items: center;
            border-radius: 21px;
            background: #eef8fa;
            font-size: 28px;
        }

        .lab-empty h3 {
            margin: 0 0 7px;
            color: var(--lab-text);
            font-size: 20px;
            font-weight: 820;
        }

        .lab-empty p {
            max-width: 560px;
            margin: 0 auto;
            font-size: 13px;
            line-height: 1.55;
        }

        .lab-attention-list {
            display: grid;
            gap: 11px;
        }

        .lab-attention-card {
            padding: 15px;
            border: 1px solid #e6edf2;
            border-radius: 15px;
            background: #fff;
        }

        .lab-attention-card--critical {
            border-color: #fecaca;
            background: #fffafa;
        }

        .lab-attention-card__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .lab-attention-card__value {
            margin-top: 8px;
            color: var(--lab-danger);
            font-size: 20px;
            font-weight: 850;
        }

        .lab-stepper {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 13px;
        }

        .lab-step {
            position: relative;
            padding-top: 18px;
            color: #94a3b8;
            font-size: 11px;
            text-align: center;
        }

        .lab-step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            z-index: 2;
            width: 11px;
            height: 11px;
            border: 3px solid #fff;
            border-radius: 50%;
            background: #cbd5e1;
            box-shadow: 0 0 0 1px #cbd5e1;
            transform: translateX(-50%);
        }

        .lab-step::after {
            content: '';
            position: absolute;
            top: 5px;
            right: 50%;
            width: 100%;
            height: 2px;
            background: #dbe4ea;
        }

        .lab-step:first-child::after {
            display: none;
        }

        .lab-step.is-done,
        .lab-step.is-current {
            color: var(--lab-primary-dark);
            font-weight: 750;
        }

        .lab-step.is-done::before,
        .lab-step.is-current::before {
            background: var(--lab-primary);
            box-shadow: 0 0 0 1px var(--lab-primary);
        }

        .lab-step.is-done::after {
            background: var(--lab-primary-light);
        }

        .lab-info-list {
            display: grid;
            gap: 12px;
        }

        .lab-info-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 11px;
            border-bottom: 1px solid #edf1f4;
        }

        .lab-info-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .lab-info-row__label {
            color: var(--lab-muted);
            font-size: 12px;
        }

        .lab-info-row__value {
            color: var(--lab-text);
            font-size: 13px;
            font-weight: 750;
            text-align: right;
        }

        .lab-note {
            padding: 14px 15px;
            border: 1px solid #cfe9ed;
            border-radius: 14px;
            background: #eef8fa;
            color: #285b63;
            font-size: 13px;
            line-height: 1.55;
        }

        .lab-note--warning {
            border-color: #fde2ad;
            background: #fff8e9;
            color: #8a5a0a;
        }

        .lab-note--danger {
            border-color: #fecaca;
            background: #fff1f1;
            color: #991b1b;
        }

        .lab-chart {
            overflow-x: auto;
        }

        .lab-chart svg {
            display: block;
            min-width: 760px;
            width: 100%;
            height: auto;
        }

        .lab-pagination {
            padding: 16px 20px;
            border-top: 1px solid var(--lab-border);
        }

        @media print {
            .lab-topbar,
            .lab-hero__actions,
            .lab-actions,
            .lab-filters {
                display: none !important;
            }

            .lab-page {
                padding: 0;
                background: #fff;
            }

            .lab-panel,
            .lab-hero,
            .lab-stat {
                box-shadow: none;
            }
        }

        @media (max-width: 1199.98px) {
            .lab-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lab-grid,
            .lab-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .lab-topbar__container {
                padding-block: 10px;
            }

            .lab-nav,
            .lab-topbar__actions {
                align-items: stretch;
                flex-direction: column;
                margin: 12px 0 0;
            }

            .lab-nav__link,
            .lab-topbar__action {
                width: 100%;
            }

            .lab-filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lab-filter--2,
            .lab-filter--3,
            .lab-filter--4,
            .lab-filter--6,
            .lab-filter--12 {
                grid-column: span 1;
            }
        }

        @media (max-width: 767.98px) {
            .lab-page {
                padding: 16px 12px 30px;
            }

            .lab-hero {
                flex-direction: column;
                padding: 21px;
                border-radius: 19px;
            }

            .lab-hero__actions {
                justify-content: flex-start;
                width: 100%;
            }

            .lab-button {
                flex: 1 1 auto;
            }

            .lab-stats,
            .lab-filters {
                grid-template-columns: 1fr;
            }

            .lab-panel__header {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('main')
    <nav class="navbar navbar-expand-lg lab-topbar">
        <div class="container-fluid lab-topbar__container">
            <a class="lab-brand" href="{{ route('doctors.analyses.index') }}">
                <span class="lab-brand__icon">
                    <i class="bi bi-activity"></i>
                </span>

                <span>Лаборатория</span>
            </a>

            <button
                class="navbar-toggler lab-navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#labNavbar"
                aria-controls="labNavbar"
                aria-expanded="false"
                aria-label="Переключить навигацию"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="labNavbar">
                <ul class="navbar-nav lab-nav me-auto">
                    <li class="nav-item">
                        <a
                            class="nav-link lab-nav__link {{ is_route('doctors.analyses.index') ? 'active' : '' }}"
                            href="{{ route('doctors.analyses.index') }}"
                        >
                            <i class="bi bi-speedometer2"></i>
                            Обзор
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link lab-nav__link {{ is_route('doctors.analyses.journal') ? 'active' : '' }}"
                            href="{{ route('doctors.analyses.journal') }}"
                        >
                            <i class="bi bi-journal-medical"></i>
                            Все исследования
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link lab-nav__link {{ is_route('doctors.analyses.critical') ? 'active' : '' }}"
                            href="{{ route('doctors.analyses.critical') }}"
                        >
                            <i class="bi bi-exclamation-triangle"></i>
                            Критические
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link lab-nav__link {{ is_route('doctors.analyses.dynamics') ? 'active' : '' }}"
                            href="{{ route('doctors.analyses.dynamics') }}"
                        >
                            <i class="bi bi-graph-up-arrow"></i>
                            Динамика
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link lab-nav__link {{ is_route('doctors.analyses.assignments') ? 'active' : '' }}"
                            href="{{ route('doctors.analyses.assignments.store') }}"
                        >
                            <i class="bi bi-clipboard2-check"></i>
                            Назначения
                        </a>
                    </li>
                </ul>

                <div class="lab-topbar__actions">
                    <a class="lab-topbar__action" href="{{ route('doctors.analyses.assignments.store') }}">
                        <i class="bi bi-plus-circle"></i>
                        Назначить
                    </a>

                    <a class="lab-topbar__action" href="/upload">
                        <i class="bi bi-cloud-arrow-up"></i>
                        Загрузить результат
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @yield('analysis-content')
@endsection
