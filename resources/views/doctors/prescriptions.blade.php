@extends('doctors.reception')

@section('title', trim($__env->yieldContent('prescription-title', 'Рецептурный отпуск')))

@section('assets')
    @parent

    <style>
        .referral-modal-ui {
            --ref-primary: #078c9b;
            --ref-primary-dark: #057482;
            --ref-primary-soft: #e8f7f9;
            --ref-text: #273247;
            --ref-muted: #7e8da3;
            --ref-border: #e3eaf0;
            --ref-bg: #f5f8fa;
        }

        /*
         * =========================================================
         * MODAL
         * =========================================================
         */

        .referral-modal-ui .modal-dialog {
            width: min(1180px, calc(100vw - 32px));
            max-width: 1180px;
        }

        .referral-modal-ui .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 32px 90px rgba(15, 23, 42, .22), 0 5px 18px rgba(15, 23, 42, .07);
        }

        /*
         * =========================================================
         * HEADER
         * =========================================================
         */

        .referral-modal__header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--ref-border);
            background: radial-gradient(circle at 0 0, rgba(7, 140, 155, .10), transparent 34%), #fff;
        }

        .referral-modal__heading {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .referral-modal__heading-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: var(--ref-primary-soft);
            color: var(--ref-primary);
            font-size: 22px;
        }

        .referral-modal__eyebrow {
            margin-bottom: 2px;
            color: var(--ref-primary);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .referral-modal-ui .modal-title {
            color: var(--ref-text);
            font-size: 20px;
            font-weight: 850;
        }

        .referral-modal__subtitle {
            margin-top: 3px;
            color: var(--ref-muted);
            font-size: 12px;
        }

        /*
         * =========================================================
         * BODY
         * =========================================================
         */

        .referral-modal__body {
            padding: 20px;
            background: var(--ref-bg);
        }

        .referral-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(300px, .75fr);
            gap: 18px;
            align-items: start;
        }

        .referral-main {
            display: grid;
            gap: 15px;
        }

        /*
         * =========================================================
         * CARDS
         * =========================================================
         */

        .referral-card,
        .referral-side-card,
        .referral-patient-card,
        .referral-status-preview {
            padding: 17px;
            border: 1px solid var(--ref-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .025);
        }

        .referral-card__head {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 16px;
        }

        .referral-card__icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: var(--ref-primary-soft);
            color: var(--ref-primary);
            font-size: 17px;
        }

        .referral-card__title {
            color: var(--ref-text);
            font-size: 14px;
            font-weight: 850;
        }

        .referral-card__hint {
            margin-top: 2px;
            color: var(--ref-muted);
            font-size: 11px;
        }

        /*
         * =========================================================
         * FORM
         * =========================================================
         */

        .referral-modal-ui .form-label {
            margin-bottom: 6px;
            color: #364152;
            font-size: 12px;
            font-weight: 750;
        }

        .referral-modal-ui .form-control,
        .referral-modal-ui .form-select {
            min-height: 44px;
            border-color: #dce4eb;
            border-radius: 12px;
            background: #fff;
            color: #273247;
            font-size: 13px;
        }

        .referral-modal-ui .form-control:focus,
        .referral-modal-ui .form-select:focus {
            border-color: #50b8c2;
            box-shadow: 0 0 0 3px rgba(7, 140, 155, .09);
        }

        .referral-purpose {
            min-height: 110px !important;
        }

        /*
         * =========================================================
         * SEARCH
         * =========================================================
         */

        .referral-doctor-search,
        .referral-diagnosis-search {
            position: relative;
        }

        .referral-doctor-search > i,
        .referral-diagnosis-search > i {
            position: absolute;
            z-index: 3;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #96a1b1;
            pointer-events: none;
        }

        .referral-doctor-search .form-control,
        .referral-diagnosis-search .form-control {
            padding-left: 40px;
        }

        /*
         * =========================================================
         * SEARCH RESULTS
         * =========================================================
         */

        .referral-doctor-results,
        .referral-diagnosis-results {
            position: absolute;
            z-index: 1085;
            right: 0;
            left: 0;
            display: none;
            max-height: 260px;
            margin-top: 6px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid var(--ref-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .14);
        }

        .referral-doctor-results.is-open,
        .referral-diagnosis-results.is-open {
            display: block;
        }

        /*
         * =========================================================
         * SELECTED DOCTOR
         * =========================================================
         */

        .referral-selected-doctor {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-top: 13px;
            padding: 11px;
            border: 1px solid #cfebee;
            border-radius: 14px;
            background: #f1fafb;
        }

        .referral-search-box {
            position: relative;
        }

        .referral-search-box > i {
            position: absolute;
            z-index: 2;
            top: 22px;
            left: 14px;
            transform: translateY(-50%);
            color: #98a2b3;
            pointer-events: none;
        }

        .referral-search-box > .form-control {
            padding-left: 40px;
        }

        .referral-search-results {
            position: absolute;
            z-index: 1090;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            display: none;
            max-height: 310px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #dce6eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .16);
        }

        .referral-search-results.is-open {
            display: block;
        }

        .referral-search-result {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            text-align: left;
            transition: .15s ease;
        }

        .referral-search-result:hover {
            background: #edf9fa;
        }

        .referral-search-result__icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #e8f7f9;
            color: #078c9b;
        }

        .referral-search-result__content {
            min-width: 0;
            flex: 1;
        }

        .referral-search-result__name {
            display: block;
            color: #29364a;
            font-size: 13px;
            font-weight: 800;
        }

        .referral-search-result__meta {
            display: block;
            margin-top: 2px;
            color: #8491a3;
            font-size: 11px;
        }

        .referral-search-result__arrow {
            color: #a5afbc;
        }

        .referral-search-state {
            padding: 18px;
            color: #8a96a5;
            font-size: 12px;
            text-align: center;
        }

        .referral-selected-entity {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-top: 11px;
            padding: 11px 12px;
            border: 1px solid #cfe8eb;
            border-radius: 14px;
            background: #f1fafb;
        }

        .referral-selected-entity__avatar {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #dff4f6;
            color: #078c9b;
            font-size: 17px;
        }

        .referral-selected-entity__avatar.doctor {
            background: #edf4ff;
            color: #3568b8;
        }

        .referral-selected-entity__content {
            min-width: 0;
            flex: 1;
        }

        .referral-selected-entity__name {
            color: #273247;
            font-size: 13px;
            font-weight: 800;
        }

        .referral-selected-entity__meta {
            margin-top: 3px;
            color: #7c899a;
            font-size: 11px;
        }

        .referral-selected-entity__clear {
            width: 31px;
            height: 31px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #98a2b3;
        }

        .referral-selected-entity__clear:hover {
            background: #fff;
            color: #dc3545;
        }

        .referral-selected-doctor__avatar {
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #dff4f6;
            color: var(--ref-primary);
        }

        .referral-selected-doctor__content {
            min-width: 0;
            flex: 1;
        }

        .referral-selected-doctor__name {
            color: var(--ref-text);
            font-size: 13px;
            font-weight: 800;
        }

        .referral-selected-doctor__meta {
            margin-top: 2px;
            color: var(--ref-muted);
            font-size: 11px;
        }

        .referral-selected-doctor__clear {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #8d99a8;
        }

        .referral-selected-doctor__clear:hover {
            background: #fff;
            color: #d9534f;
        }

        .referral-doctor-scope {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            padding: 6px 10px;
            border: 1px solid #d7eaed;
            border-radius: 999px;
            background: #f1fafb;
            color: #35747c;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.2;
        }

        .referral-doctor-scope i {
            color: var(--ref-primary);
            font-size: 11px;
        }

        /*
         * =========================================================
         * QUICK PURPOSES
         * =========================================================
         */

        .referral-quick-purposes {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 10px;
        }

        .referral-chip {
            padding: 6px 10px;
            border: 1px solid #d9e7e9;
            border-radius: 999px;
            background: #f8fbfc;
            color: #53747a;
            font-size: 11px;
            font-weight: 650;
            transition: .15s ease;
        }

        .referral-chip:hover {
            border-color: #abd9de;
            background: var(--ref-primary-soft);
            color: var(--ref-primary-dark);
        }

        /*
         * =========================================================
         * PRIORITY
         * =========================================================
         */

        .referral-priority {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .referral-priority__option {
            position: relative;
            margin: 0;
            cursor: pointer;
        }

        .referral-priority__option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .referral-priority__option > span {
            min-height: 74px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 1px solid #dfe6ed;
            border-radius: 13px;
            background: #fff;
            transition: .15s ease;
        }

        .referral-priority__option strong,
        .referral-priority__option small {
            display: block;
        }

        .referral-priority__option strong {
            color: var(--ref-text);
            font-size: 12px;
        }

        .referral-priority__option small {
            margin-top: 2px;
            color: var(--ref-muted);
            font-size: 10px;
        }

        .referral-priority__option input:checked + span {
            border-color: #68bec7;
            background: #eefafb;
            box-shadow: 0 0 0 3px rgba(7, 140, 155, .07);
        }

        .referral-priority__icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
        }

        .referral-priority__icon.normal {
            background: #e9f7f2;
            color: #198754;
        }

        .referral-priority__icon.urgent {
            background: #fff0eb;
            color: #dc5c31;
        }

        /*
         * =========================================================
         * SIDE
         * =========================================================
         */

        .referral-side {
            position: sticky;
            top: 0;
            display: grid;
            gap: 13px;
        }

        .referral-side-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 13px;
            color: var(--ref-text);
            font-size: 12px;
            font-weight: 850;
        }

        .referral-side-title i {
            color: var(--ref-primary);
        }

        /*
         * =========================================================
         * PATIENT
         * =========================================================
         */

        .referral-patient {
            display: flex;
            align-items: center;
            gap: 11px;
            padding-bottom: 13px;
            border-bottom: 1px solid #edf1f4;
        }

        .referral-patient__avatar {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, #0694a4, #17adbb);
            color: #fff;
            font-size: 13px;
            font-weight: 900;
        }

        .referral-patient__name {
            color: var(--ref-text);
            font-size: 13px;
            font-weight: 850;
        }

        .referral-patient__meta {
            margin-top: 3px;
            color: var(--ref-muted);
            font-size: 11px;
        }

        .referral-info-list {
            display: grid;
            gap: 9px;
            margin-top: 13px;
        }

        .referral-info {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            font-size: 11px;
        }

        .referral-info span {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--ref-muted);
        }

        .referral-info strong {
            color: #39465a;
            text-align: right;
        }

        /*
         * =========================================================
         * DIAGNOSIS
         * =========================================================
         */

        .referral-selected-diagnosis {
            display: flex;
            gap: 10px;
            margin-top: 11px;
            padding: 11px;
            border-radius: 13px;
            background: #f4f8fb;
        }

        .referral-selected-diagnosis__code {
            min-width: 52px;
            align-self: flex-start;
            padding: 5px 8px;
            border-radius: 8px;
            background: #e5f2f4;
            color: var(--ref-primary-dark);
            font-size: 11px;
            font-weight: 850;
            text-align: center;
        }

        .referral-selected-diagnosis__content {
            min-width: 0;
        }

        .referral-selected-diagnosis__content strong,
        .referral-selected-diagnosis__content span {
            display: block;
        }

        .referral-selected-diagnosis__content strong {
            color: var(--ref-text);
            font-size: 11px;
            line-height: 1.4;
        }

        .referral-selected-diagnosis__content span {
            margin-top: 2px;
            color: var(--ref-muted);
            font-size: 10px;
        }

        /*
         * =========================================================
         * INFORMATION
         * =========================================================
         */

        .referral-info-card {
            display: flex;
            gap: 10px;
            padding: 13px;
            border: 1px solid #dcebed;
            border-radius: 15px;
            background: #f3fafb;
        }

        .referral-info-card__icon {
            color: var(--ref-primary);
        }

        .referral-info-card strong {
            display: block;
            color: #34545a;
            font-size: 11px;
        }

        .referral-info-card p {
            margin: 4px 0 0;
            color: #6e858a;
            font-size: 10px;
            line-height: 1.5;
        }

        /*
         * =========================================================
         * STATUS
         * =========================================================
         */

        .referral-status-preview__label {
            color: var(--ref-muted);
            font-size: 10px;
        }

        .referral-status-preview__value {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 5px;
            color: #354155;
            font-size: 12px;
            font-weight: 750;
        }

        .referral-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #17a2b8;
            box-shadow: 0 0 0 4px rgba(23, 162, 184, .10);
        }

        /*
         * =========================================================
         * FOOTER
         * =========================================================
         */

        .referral-modal__footer {
            padding: 13px 20px;
            border-top: 1px solid var(--ref-border);
            background: #fff;
        }

        .referral-footer-note {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-right: auto;
            color: var(--ref-muted);
            font-size: 10px;
        }

        .referral-footer-note i {
            color: var(--ref-primary);
        }

        .referral-btn {
            min-height: 41px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 15px;
            border-radius: 11px;
            font-size: 12px;
            font-weight: 750;
        }

        .referral-btn--primary {
            min-width: 180px;
            border-color: var(--ref-primary);
            background: var(--ref-primary);
        }

        .referral-btn--primary:hover {
            border-color: var(--ref-primary-dark);
            background: var(--ref-primary-dark);
        }

        /*
         * =========================================================
         * RESPONSIVE
         * =========================================================
         */

        @media (max-width: 991px) {
            .referral-layout {
                grid-template-columns: 1fr;
            }

            .referral-side {
                position: static;
            }
        }

        @media (max-width: 575px) {
            .referral-modal-ui .modal-dialog {
                width: calc(100vw - 12px);
                margin: 6px auto;
            }

            .referral-priority {
                grid-template-columns: 1fr;
            }

            .referral-footer-note {
                display: none;
            }
        }

        .patient-search-modal {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .16), 0 4px 16px rgba(15, 23, 42, .08);
        }

        .patient-search-modal .modal-header {
            padding: 24px 24px 8px;
        }

        .patient-search-modal .modal-title {
            font-size: 20px;
            font-weight: 750;
            color: #253047;
        }

        .patient-search-modal .modal-body {
            padding: 18px 24px 24px;
        }

        /* Поле поиска */

        .patient-search-input-wrap {
            position: relative;
        }

        .patient-search-input-wrap > i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #8995a7;
            pointer-events: none;
        }

        .patient-search-input-wrap .form-control {
            height: 48px;
            padding-left: 43px;
            padding-right: 15px;
            border: 1px solid #e0e7ef;
            border-radius: 13px;
            font-size: 14px;
            background: #f8fafc;
        }

        .patient-search-input-wrap .form-control:focus {
            background: #fff;
            border-color: #1595a5;
            box-shadow: 0 0 0 3px rgba(21, 149, 165, .10);
        }

        /* Пустое состояние */

        .patient-search-empty {
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: #98a2b3;
            font-size: 13px;
        }

        .patient-search-empty i {
            font-size: 28px;
            color: #b9c3ce;
        }

        /* Загрузка */

        .patient-search-loading {
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #7b8798;
            font-size: 13px;
        }

        /* Результаты */

        .patient-search-results {
            margin-top: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .patient-search-result {
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px 13px;
            border: 1px solid transparent;
            border-radius: 13px;
            background: #fff;
            cursor: pointer;
            text-align: left;
            transition: background .15s ease, border-color .15s ease, transform .15s ease;
        }

        .patient-search-result:hover {
            background: #f0f9fa;
            border-color: #d5eef1;
        }

        .patient-search-result__avatar {
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 11px;
            border-radius: 11px;
            background: #edf8fa;
            color: #128b9b;
            font-size: 17px;
        }

        .patient-search-result__content {
            min-width: 0;
            flex: 1;
        }

        .patient-search-result__name {
            font-size: 14px;
            font-weight: 700;
            color: #263247;
        }

        .patient-search-result__meta {
            margin-top: 3px;
            font-size: 12px;
            color: #8a95a5;
        }

        .patient-search-result__arrow {
            margin-left: 10px;
            color: #a4adba;
            font-size: 14px;
        }

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
            background: radial-gradient(circle at 0 0, rgba(4, 134, 150, .07), transparent 28%), var(--rx-bg);
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

        .rx-stat--blue::before {
            background: var(--rx-blue);
        }

        .rx-stat--warning::before {
            background: var(--rx-warning);
        }

        .rx-stat--success::before {
            background: var(--rx-success);
        }

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

        .rx-filter--2 {
            grid-column: span 2;
        }

        .rx-filter--3 {
            grid-column: span 3;
        }

        .rx-filter--4 {
            grid-column: span 4;
        }

        .rx-filter--5 {
            grid-column: span 5;
        }

        .rx-filter--12 {
            grid-column: span 12;
        }

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

        .rx-status--active {
            background: #eaf8ef;
            color: #15803d;
        }

        .rx-status--printed {
            background: #eaf1ff;
            color: #1d4ed8;
        }

        .rx-status--warning {
            background: #fff7e6;
            color: #b45309;
        }

        .rx-status--draft {
            background: #eef2f7;
            color: #475569;
        }

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

        /*
         * В основном layout для ссылок navbar задан белый цвет с !important.
         * Без этого переопределения заголовки и стрелки выпадающего меню
         * наследуют белый цвет и становятся невидимыми на белом фоне.
         */

        .rx-topbar .rx-nav__dropdown-menu .rx-nav__dropdown-item,
        .rx-topbar .rx-create-menu .rx-nav__dropdown-item {
            color: #344054 !important;
        }

        .rx-topbar .rx-nav__dropdown-menu .rx-nav__dropdown-item:hover,
        .rx-topbar .rx-nav__dropdown-menu .rx-nav__dropdown-item:focus,
        .rx-topbar .rx-nav__dropdown-menu .rx-nav__dropdown-item.active,
        .rx-topbar .rx-create-menu .rx-nav__dropdown-item:hover,
        .rx-topbar .rx-create-menu .rx-nav__dropdown-item:focus,
        .rx-topbar .rx-create-menu .rx-nav__dropdown-item.active {
            color: var(--rx-primary-dark) !important;
        }

        .rx-nav__dropdown-title,
        .rx-nav__submenu-arrow {
            color: currentColor !important;
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

        .rx-nav__submenu-wrap {
            position: relative;
        }

        .rx-nav__dropdown-item--submenu {
            position: relative;
            padding-right: 38px;
        }

        .rx-nav__submenu-arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            opacity: .5;
            transition: transform .18s ease, opacity .18s ease;
        }

        /* Второй уровень */

        .rx-nav__submenu {
            position: absolute;
            display: none;
            top: -8px;
            left: calc(100% + 8px);
            width: 310px;
            padding: 8px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .13), 0 2px 8px rgba(15, 23, 42, .06);
            z-index: 1100;
        }

        /* Показываем при наведении */

        .rx-nav__submenu-wrap:hover > .rx-nav__submenu,
        .rx-nav__submenu-wrap:focus-within > .rx-nav__submenu {
            display: block;
        }

        /* Стрелка */

        .rx-nav__submenu-wrap:hover > .rx-nav__dropdown-item .rx-nav__submenu-arrow {
            opacity: 1;
            transform: translateY(-50%) translateX(2px);
        }

        /* Чтобы мышь могла спокойно перейти в подменю */

        .rx-nav__submenu::before {
            content: "";
            position: absolute;
            left: -12px;
            top: 0;
            width: 12px;
            height: 100%;
        }

        /*
         * =========================================================
         * REFERRAL: INTERNAL / EXTERNAL SPECIALIST
         * =========================================================
         */

        .referral-specialist-mode {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .referral-specialist-mode__item {
            width: 100%;
            min-height: 78px;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 12px;
            border: 1px solid #dfe6ed;
            border-radius: 14px;
            background: #fff;
            color: inherit;
            text-align: left;
            cursor: pointer;
            transition: border-color .15s ease, background .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .referral-specialist-mode__item:hover {
            border-color: #bcdde1;
            background: #fbfefe;
        }

        .referral-specialist-mode__item.is-active {
            border-color: #66bdc6;
            background: #eefafb;
            box-shadow: 0 0 0 3px rgba(7, 140, 155, .07);
        }

        .referral-specialist-mode__icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #edf7f8;
            color: #078c9b;
            font-size: 17px;
        }

        .referral-specialist-mode__item.is-active .referral-specialist-mode__icon {
            background: #dff4f6;
        }

        .referral-specialist-mode__item strong,
        .referral-specialist-mode__item small {
            display: block;
        }

        .referral-specialist-mode__item strong {
            color: #273247;
            font-size: 12px;
            font-weight: 800;
        }

        .referral-specialist-mode__item small {
            margin-top: 3px;
            color: #8491a3;
            font-size: 10px;
            line-height: 1.35;
        }

        .referral-external-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-top: 13px;
            padding: 11px 12px;
            border: 1px solid #dbecef;
            border-radius: 12px;
            background: #f2fbfc;
            color: #617e83;
            font-size: 11px;
            line-height: 1.45;
        }

        .referral-external-note i {
            flex: 0 0 auto;
            margin-top: 1px;
            color: #078c9b;
        }

        .referral-selected-diagnosis {
            position: relative;
            align-items: flex-start;
            padding-right: 46px;
        }

        .referral-selected-diagnosis__clear {
            position: absolute;
            top: 9px;
            right: 9px;
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #98a2b3;
        }

        .referral-selected-diagnosis__clear:hover {
            background: #fff;
            color: #dc3545;
        }

        .referral-summary__group + .referral-summary__group {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #edf1f4;
        }

        .referral-summary__badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
            padding: 5px 8px;
            border-radius: 999px;
            background: #e9f7f9;
            color: #087f8c;
            font-size: 10px;
            font-weight: 800;
        }

        .referral-summary__diagnosis {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #edf1f4;
        }

        .referral-summary__diagnosis .referral-summary__value {
            line-height: 1.45;
        }

        @media (max-width: 767px) {
            .referral-specialist-mode {
                grid-template-columns: 1fr;
            }
        }

        /*
         * =========================================================
         * EXTERNAL REFERRAL: YANDEX MAP + ORGANIZATIONS
         * =========================================================
         */

        .referral-external-routing {
            display: grid;
            gap: 14px;
        }

        .referral-external-map-card,
        .referral-external-specialist-card {
            overflow: visible;
            padding: 15px;
            border: 1px solid #dfe8ed;
            border-radius: 16px;
            background: #fff;
        }

        .referral-external-section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .referral-external-section-head__main {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .referral-external-section-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #e9f8fa;
            color: #078c9b;
            font-size: 15px;
        }

        .referral-external-section-title {
            color: #273247;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.25;
        }

        .referral-external-section-hint {
            margin-top: 3px;
            color: #8793a3;
            font-size: 10px;
            line-height: 1.4;
        }

        .referral-external-map-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 26px;
            padding: 4px 8px;
            border: 1px solid #d8eaed;
            border-radius: 999px;
            color: #4a737a;
            background: #f4fbfc;
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
        }

        .referral-external-map-badge i {
            color: #078c9b;
        }

        .referral-external-clinic-search {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            align-items: stretch;
        }

        .referral-external-search-box {
            position: relative;
        }

        .referral-external-search-box > i {
            position: absolute;
            z-index: 3;
            top: 50%;
            left: 13px;
            transform: translateY(-50%);
            color: #96a3b3;
            pointer-events: none;
        }

        .referral-external-search-box .form-control {
            padding-left: 39px;
            padding-right: 38px;
        }

        .referral-external-search-clear {
            position: absolute;
            z-index: 4;
            top: 50%;
            right: 8px;
            width: 28px;
            height: 28px;
            display: none;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #98a4b1;
            font-size: 10px;
        }

        .referral-external-search-clear.is-visible {
            display: grid;
        }

        .referral-external-search-clear:hover {
            background: #f0f4f6;
            color: #526477;
        }

        .referral-external-search-button {
            min-width: 88px;
            border: 1px solid #078c9b;
            border-radius: 12px;
            background: #078c9b;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
        }

        .referral-external-search-button:hover {
            background: #057482;
            border-color: #057482;
        }

        .referral-external-quick {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .referral-external-quick button {
            min-height: 27px;
            padding: 4px 9px;
            border: 1px solid #dde7eb;
            border-radius: 999px;
            background: #f8fafb;
            color: #60717f;
            font-size: 9px;
            font-weight: 750;
            transition: .14s ease;
        }

        .referral-external-quick button:hover {
            border-color: #b9dfe3;
            background: #edf9fa;
            color: #087782;
        }

        .referral-external-map-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(250px, .65fr);
            gap: 10px;
            margin-top: 11px;
        }

        .referral-external-map-wrap {
            position: relative;
            min-height: 345px;
            overflow: hidden;
            border: 1px solid #dce6eb;
            border-radius: 14px;
            background: #eef3f5;
        }

        .referral-external-map {
            width: 100%;
            height: 345px;
            min-height: 345px;
        }

        .referral-external-map-state {
            position: absolute;
            z-index: 10;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(247, 250, 251, .92);
            color: #6f7f8e;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
            backdrop-filter: blur(4px);
        }

        .referral-external-map-state.d-none {
            display: none !important;
        }

        .referral-external-map-state__inner {
            max-width: 320px;
        }

        .referral-external-map-state__icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            margin: 0 auto 9px;
            border-radius: 13px;
            background: #e7f7f8;
            color: #078c9b;
            font-size: 18px;
        }

        .referral-external-map-state strong {
            display: block;
            margin-bottom: 3px;
            color: #33495a;
            font-size: 11px;
            font-weight: 850;
        }

        .referral-external-list-card {
            min-height: 345px;
            overflow: hidden;
            border: 1px solid #dce6eb;
            border-radius: 14px;
            background: #fff;
        }

        .referral-external-list-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 42px;
            padding: 9px 10px;
            border-bottom: 1px solid #e8eef2;
            color: #455466;
            font-size: 10px;
            font-weight: 800;
        }

        .referral-external-list-count {
            min-width: 24px;
            height: 24px;
            display: inline-grid;
            place-items: center;
            padding: 0 6px;
            border-radius: 999px;
            background: #eef6f7;
            color: #087985;
            font-size: 9px;
        }

        .referral-external-clinic-results {
            max-height: 302px;
            overflow-y: auto;
            padding: 5px;
            scrollbar-width: thin;
            scrollbar-color: #cbd7df transparent;
        }

        .referral-external-clinic-item {
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 9px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            text-align: left;
            transition: .14s ease;
        }

        .referral-external-clinic-item:hover {
            background: #f0f9fa;
        }

        .referral-external-clinic-item.is-selected {
            background: #e9f7f8;
            box-shadow: inset 0 0 0 1px #bfe5e8;
        }

        .referral-external-clinic-item__icon {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: #e8f6f8;
            color: #078c9b;
            font-size: 13px;
        }

        .referral-external-clinic-item__content {
            min-width: 0;
            flex: 1;
        }

        .referral-external-clinic-item__name {
            display: block;
            color: #2e3e51;
            font-size: 10.5px;
            font-weight: 800;
            line-height: 1.3;
        }

        .referral-external-clinic-item__address,
        .referral-external-clinic-item__meta {
            display: block;
            margin-top: 2px;
            color: #8794a3;
            font-size: 9px;
            line-height: 1.35;
        }

        .referral-external-list-state {
            min-height: 170px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 18px;
            color: #8b97a6;
            text-align: center;
            font-size: 10px;
        }

        .referral-external-list-state i {
            color: #a5b0bb;
            font-size: 18px;
        }

        .referral-external-selected-clinic {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-top: 11px;
            padding: 11px;
            border: 1px solid #cce7ea;
            border-radius: 13px;
            background: #f0fafb;
        }

        .referral-external-selected-clinic__icon {
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #dff4f6;
            color: #078c9b;
            font-size: 17px;
        }

        .referral-external-selected-clinic__content {
            min-width: 0;
            flex: 1;
        }

        .referral-external-selected-clinic__name {
            color: #2f4052;
            font-size: 11px;
            font-weight: 850;
        }

        .referral-external-selected-clinic__address,
        .referral-external-selected-clinic__meta {
            margin-top: 2px;
            color: #768696;
            font-size: 9.5px;
            line-height: 1.4;
        }

        .referral-external-selected-clinic__actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .referral-external-selected-clinic__actions button,
        .referral-external-selected-clinic__actions a {
            width: 29px;
            height: 29px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: #fff;
            color: #81909f;
            text-decoration: none;
            font-size: 11px;
        }

        .referral-external-speciality-search {
            position: relative;
            z-index: 4;
        }

        .referral-external-speciality-search:focus-within {
            z-index: 40;
        }

        .referral-external-field-clear {
            position: absolute;
            z-index: 5;
            top: 50%;
            right: 8px;
            width: 28px;
            height: 28px;
            display: none;
            place-items: center;
            padding: 0;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #98a4b1;
            font-size: 10px;
            cursor: pointer;
        }

        .referral-external-field-clear.is-visible {
            display: grid;
        }

        .referral-external-field-clear:hover {
            background: #f0f4f6;
            color: #526477;
        }

        .referral-external-speciality-results {
            position: absolute;
            z-index: 1120;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            display: none;
            max-height: 290px;
            overflow-y: auto;
            padding: 5px;
            border: 1px solid #dce6eb;
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 18px 46px rgba(15, 23, 42, .15);
        }

        .referral-external-speciality-results.is-open {
            display: block;
        }

        .referral-external-speciality-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .referral-external-speciality-item:hover,
        .referral-external-speciality-item.is-active {
            background: #f1f8f9;
        }

        .referral-external-speciality-item__icon {
            width: 33px;
            height: 33px;
            flex: 0 0 33px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: #eaf7f8;
            color: #087f8c;
            font-size: 12px;
        }

        .referral-external-speciality-item__content {
            min-width: 0;
            flex: 1;
        }

        .referral-external-speciality-item__name {
            display: block;
            color: #2f3f51;
            font-size: 10.5px;
            font-weight: 800;
        }

        .referral-external-speciality-item__meta {
            display: block;
            margin-top: 2px;
            color: #8793a2;
            font-size: 9px;
        }

        .referral-external-speciality-state {
            padding: 14px;
            color: #8793a2;
            font-size: 10px;
            text-align: center;
        }

        .referral-speciality-verification {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-top: 9px;
            padding: 9px 10px;
            border: 1px solid #dfe7ec;
            border-radius: 11px;
            background: #f8fafb;
        }

        .referral-speciality-verification__icon {
            width: 27px;
            height: 27px;
            flex: 0 0 27px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #edf2f5;
            color: #7d8c9b;
            font-size: 11px;
        }

        .referral-speciality-verification__content {
            min-width: 0;
            flex: 1;
        }

        .referral-speciality-verification__content strong,
        .referral-speciality-verification__content span {
            display: block;
        }

        .referral-speciality-verification__content strong {
            color: #425267;
            font-size: 9.5px;
            font-weight: 850;
        }

        .referral-speciality-verification__content span {
            margin-top: 2px;
            color: #718092;
            font-size: 9px;
            line-height: 1.45;
        }

        .referral-speciality-verification.is-confirmed {
            border-color: #c7e8d8;
            background: #eef9f4;
        }

        .referral-speciality-verification.is-confirmed .referral-speciality-verification__icon {
            background: #dcf3e8;
            color: #24865d;
        }

        .referral-speciality-verification.is-confirmed .referral-speciality-verification__content strong {
            color: #267553;
        }

        .referral-speciality-verification.is-unknown {
            border-color: #dbe8ed;
            background: #f4fafb;
        }

        .referral-speciality-verification.is-unknown .referral-speciality-verification__icon {
            background: #e4f4f6;
            color: #078c9b;
        }

        .referral-speciality-verification.is-warning {
            border-color: #efdcae;
            background: #fff9ec;
        }

        .referral-speciality-verification.is-warning .referral-speciality-verification__icon {
            background: #fff0c9;
            color: #a46b00;
        }

        .referral-speciality-verification.is-warning .referral-speciality-verification__content strong {
            color: #875d0d;
        }

        .referral-speciality-verification.is-manual {
            border-color: #dfe5ed;
            background: #f8fafc;
        }

        .referral-speciality-confirmed-badge {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-height: 21px;
            padding: 3px 6px;
            border-radius: 999px;
            background: #e5f6ed;
            color: #257a57;
            font-size: 8px;
            font-weight: 850;
            white-space: nowrap;
        }

        .referral-external-search-box .form-control[type="search"] {
            padding-right: 40px;
        }

        .referral-external-manual-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 10px;
            padding: 9px 10px;
            border: 1px dashed #d7e5e8;
            border-radius: 11px;
            background: #fbfdfd;
            color: #73868b;
            font-size: 9.5px;
            line-height: 1.45;
        }

        .referral-external-manual-note i {
            color: #078c9b;
        }

        @media (max-width: 900px) {
            .referral-external-map-layout {
                grid-template-columns: 1fr;
            }

            .referral-external-list-card {
                min-height: auto;
            }

            .referral-external-clinic-results {
                max-height: 220px;
            }
        }

        @media (max-width: 575px) {
            .referral-external-clinic-search {
                grid-template-columns: 1fr;
            }

            .referral-external-search-button {
                min-height: 40px;
            }

            .referral-external-map,
            .referral-external-map-wrap {
                height: 300px;
                min-height: 300px;
            }
        }

        /* Yandex Maps JS API 3.0 */

        .referral-yandex-marker {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            padding: 0;
            border: 3px solid #fff;
            border-radius: 50% 50% 50% 8px;
            background: #078c9b;
            color: #fff;
            box-shadow: 0 7px 18px rgba(7, 140, 155, .28);
            cursor: pointer;
            transform: rotate(-45deg);
            transition: .15s ease;
        }

        .referral-yandex-marker i {
            transform: rotate(45deg);
            font-size: 13px;
        }

        .referral-yandex-marker:hover,
        .referral-yandex-marker.is-selected {
            transform: rotate(-45deg) scale(1.12);
            background: #056d78;
            box-shadow: 0 9px 22px rgba(7, 140, 155, .38);
        }
    </style>

    @php
        /*
         * Яндекс используется только как картографическая подложка.
         * Медицинские организации ищем бесплатно через OpenStreetMap / Overpass.
         */
        $yandexMapsApiKey =
            config('services.yandex_maps.api_key')
            ?: env('YANDEX_MAPS_API_KEY');
    @endphp

    @if ($yandexMapsApiKey)
        <script
            src="https://api-maps.yandex.ru/v3/?apikey={{ urlencode($yandexMapsApiKey) }}&lang=ru_RU"
            type="text/javascript"
        ></script>
    @endif

@endsection

@section('main')
    @php
        $specialistReferralRoute = \Illuminate\Support\Facades\Route::has('doctors.referrals.specialists.index')
            ? route('doctors.referrals.specialists.index')
            : null;

        $specialistReferralCreateRoute = \Illuminate\Support\Facades\Route::has('doctors.referrals.specialists.create')
            ? route('doctors.referrals.specialists.create')
            : null;

        $hospitalReferralRoute = \Illuminate\Support\Facades\Route::has('doctors.referrals.hospital.index')
            ? route('doctors.referrals.hospital.index')
            : null;

        $hospitalReferralCreateRoute = \Illuminate\Support\Facades\Route::has('doctors.referrals.hospital.create')
            ? route('doctors.referrals.hospital.create')
            : null;

        $consultationsRoute = \Illuminate\Support\Facades\Route::has('doctors.referrals.consultations.index')
            ? route('doctors.referrals.consultations.index')
            : null;

        $labAssignmentsRoute = \Illuminate\Support\Facades\Route::has('doctors.analyses.assignments')
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

        $isReferenceSection = request()->routeIs('doctors.prescriptions.base');
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

                            <!-- ИССЛЕДОВАНИЯ -->

                            <div class="rx-nav__submenu-wrap">
                                <a class="rx-nav__dropdown-item rx-nav__dropdown-item--submenu" href="#">
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-clipboard2-pulse"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">Исследования</span>
                                        <span class="rx-nav__dropdown-hint">
                                            Анализы, диагностика и психодиагностика
                                        </span>
                                    </span>

                                    <i class="bi bi-chevron-right rx-nav__submenu-arrow"></i>
                                </a>

                                <div class="rx-nav__submenu">
                                    <a class="rx-nav__dropdown-item" href="{{ route('doctors.analyses.index') }}">
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-droplet-half"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Лабораторные исследования</span>
                                            <span class="rx-nav__dropdown-hint">Анализы и печать направления</span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#instrumentalPatientModal"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-activity"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Инструментальные исследования</span>
                                            <span class="rx-nav__dropdown-hint">ЭЭГ, ЭКГ, МРТ, КТ и другие</span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCreateDoctorDestination"
                                        data-referral-mode="internal"
                                        data-referral-scope="psy"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-person-heart"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Психолог / патопсихолог</span>
                                            <span class="rx-nav__dropdown-hint">
                                                Патопсихологическое и нейропсихологическое исследование
                                            </span>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- КОНСУЛЬТАЦИИ -->

                            <div class="rx-nav__submenu-wrap">
                                <a class="rx-nav__dropdown-item rx-nav__dropdown-item--submenu" href="#">
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-people"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">Консультации</span>
                                        <span class="rx-nav__dropdown-hint">Специалисты и врачебные консультации</span>
                                    </span>

                                    <i class="bi bi-chevron-right rx-nav__submenu-arrow"></i>
                                </a>

                                <div class="rx-nav__submenu">
                                    <a
                                        class="rx-nav__dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCreateDoctorDestination"
                                        data-referral-mode="internal"
                                        data-referral-scope="general"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-person-badge"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">К профильному специалисту</span>
                                            <span class="rx-nav__dropdown-hint">
                                                Невролог, терапевт, эндокринолог и другие
                                            </span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item consultation-submenu-item"
                                        href="#"
                                        onclick="
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('consilium:open'));
    "
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-people-fill"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Консилиум</span>
                                            <span class="rx-nav__dropdown-hint">
                                                Совместное рассмотрение клинического случая
                                            </span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCreateDoctorDestination"
                                        data-referral-mode="external"
                                        data-referral-scope="general"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Внешняя консультация</span>
                                            <span class="rx-nav__dropdown-hint">
                                                Направление в другую медицинскую организацию
                                            </span>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- ГОСПИТАЛИЗАЦИЯ -->

                            <div class="rx-nav__submenu-wrap">
                                <a class="rx-nav__dropdown-item rx-nav__dropdown-item--submenu" href="#">
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-hospital"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">Госпитализация</span>
                                        <span class="rx-nav__dropdown-hint">Направление пациента в стационар</span>
                                    </span>

                                    <i class="bi bi-chevron-right rx-nav__submenu-arrow"></i>
                                </a>

                                <div class="rx-nav__submenu">
                                    <a
                                        class="rx-nav__dropdown-item"
                                        href="{{ route('doctors.referrals.hospital.planned') }}"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-calendar-check"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Плановая госпитализация</span>
                                            <span class="rx-nav__dropdown-hint">Направление на плановое лечение</span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item"
                                        href="{{ route('doctors.referrals.hospital.emergency') }}"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Экстренная госпитализация</span>
                                            <span class="rx-nav__dropdown-hint">Неотложное направление пациента</span>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- КОМИССИИ -->

                            <div class="rx-nav__submenu-wrap">
                                <a class="rx-nav__dropdown-item rx-nav__dropdown-item--submenu" href="#">
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-clipboard2-check"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">Комиссии и экспертизы</span>
                                        <span class="rx-nav__dropdown-hint">ВК, МСЭ, военкомат и другие комиссии</span>
                                    </span>

                                    <i class="bi bi-chevron-right rx-nav__submenu-arrow"></i>
                                </a>

                                <div class="rx-nav__submenu">
                                    <a
                                        class="rx-nav__dropdown-item {{ request()->routeIs('doctors.commissions.medical.*') ? 'active' : '' }}"
                                        href="{{ route('doctors.commissions.index') }}"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-clipboard2-check"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Врачебная комиссия</span>
                                            <span class="rx-nav__dropdown-hint">Рассмотрение случая на ВК</span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item {{ request()->routeIs('doctors.commissions.military.*') ? 'active' : '' }}"
                                        href="{{ route('doctors.commissions.military.index') }}"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-shield-check"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Военкомат / ВВК</span>
                                            <span class="rx-nav__dropdown-hint">Заключения и медицинские сведения</span>
                                        </span>
                                    </a>

                                    <a
                                        class="rx-nav__dropdown-item {{ request()->routeIs('doctors.commissions.mse.*') ? 'active' : '' }}"
                                        href="{{ route('doctors.commissions.mse.index') }}"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-universal-access"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">МСЭ</span>
                                            <span class="rx-nav__dropdown-hint">
                                                Документы для медико-социальной экспертизы
                                            </span>
                                        </span>
                                    </a>
                                    <a
                                        class="rx-nav__dropdown-item {{ request()->routeIs('doctors.commissions.driver.*') ? 'active' : '' }}"
                                        href="{{ route('doctors.commissions.driver.index') }}"
                                    >
                                        <span class="rx-nav__dropdown-icon">
                                            <i class="bi bi-car-front"></i>
                                        </span>

                                        <span class="rx-nav__dropdown-copy">
                                            <span class="rx-nav__dropdown-title">Водительская комиссия</span>
                                            <span class="rx-nav__dropdown-hint">
                                                Психиатрическое освидетельствование
                                            </span>
                                        </span>
                                    </a>
                                </div>
                            </div>
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
                                    <span class="rx-nav__dropdown-title">База препаратов</span>
                                    <span class="rx-nav__dropdown-hint">
                                        Формы, дозировки, показания и противопоказания
                                    </span>
                                </span>
                            </a>
                        </div>
                    </li>
                </ul>

                @if (!is_route('doctors.referrals.hospital.planned') && !is_route('doctors.referrals.hospital.emergency'))
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
                                <a class="rx-nav__dropdown-item" href="{{ route('doctors.prescriptions.new') }}">
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-prescription2"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">Новый рецепт</span>
                                        <span class="rx-nav__dropdown-hint">
                                            Лекарственное назначение и печатная форма
                                        </span>
                                    </span>
                                </a>

                                <a
                                    class="rx-nav__dropdown-item {{ $labAssignmentsRoute ? '' : 'is-disabled' }}"
                                    href="{{ $labAssignmentsRoute ?: '#' }}"
                                    @if (!$labAssignmentsRoute) aria-disabled="true" @endif
                                >
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-droplet-half"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">Направление на анализы</span>
                                        <span class="rx-nav__dropdown-hint">Лабораторное исследование</span>
                                    </span>
                                </a>

                                <div class="rx-nav__dropdown-divider"></div>
                                <a
                                    class="rx-nav__dropdown-item {{ $specialistReferralCreateRoute ? '' : 'is-disabled' }}"
                                    href="{{ $specialistReferralCreateRoute ?: '#' }}"
                                    @if (!$specialistReferralCreateRoute) aria-disabled="true" @endif
                                >
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-person-plus"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">
                                            К специалисту
                                            @if (!$specialistReferralCreateRoute)
                                                <span class="rx-nav__dropdown-badge">подключить</span>
                                            @endif

                                        </span>
                                    </span>
                                </a>

                                <a
                                    class="rx-nav__dropdown-item {{ $hospitalReferralCreateRoute ? '' : 'is-disabled' }}"
                                    href="{{ $hospitalReferralCreateRoute ?: '#' }}"
                                    @if (!$hospitalReferralCreateRoute) aria-disabled="true" @endif
                                >
                                    <span class="rx-nav__dropdown-icon">
                                        <i class="bi bi-hospital"></i>
                                    </span>

                                    <span class="rx-nav__dropdown-copy">
                                        <span class="rx-nav__dropdown-title">
                                            В стационар
                                            @if (!$hospitalReferralCreateRoute)
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
                @endif

            </div>
        </div>
    </nav>
    @yield('sub-main')
    @include('doctors.commissions.partials.hidden-print')
    <div
        class="modal fade"
        id="instrumentalPatientModal"
        tabindex="-1"
        aria-labelledby="instrumentalPatientModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content patient-search-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="instrumentalPatientModalLabel">Найти пациента</h5>
                        <div class="text-muted small mt-1">
                            Выберите пациента для назначения инструментального исследования
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Закрыть"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="patient-search-input-wrap">
                        <i class="bi bi-search"></i>
                        <input
                            type="text"
                            class="form-control"
                            id="instrumentalPatientSearch"
                            placeholder="ФИО, телефон или дата рождения"
                            autocomplete="off"
                        >
                    </div>

                    <div id="instrumentalPatientSearchHint" class="patient-search-empty">
                        <i class="bi bi-person-search"></i>
                        <div>Начните вводить данные пациента</div>
                    </div>

                    <div id="instrumentalPatientSearchLoading" class="patient-search-loading d-none">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span>Поиск пациента...</span>
                    </div>

                    <div id="instrumentalPatientResults" class="patient-search-results"></div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="modal fade referral-modal-ui"
        id="modalCreateDoctorDestination"
        tabindex="-1"
        aria-labelledby="referralModalTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form
                class="modal-content"
                id="doctorDestinationForm"
                method="POST"
                action="{{ route('doctors.destinations.destination.store') }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="type"
                    id="referralType"
                    value="specialist"
                >
                <input
                    type="hidden"
                    name="speciality"
                    id="referralSpeciality"
                    value=""
                >
                <div class="modal-header referral-modal__header">
                    <div class="referral-modal__heading">
                        <div class="referral-modal__heading-icon">
                            <i class="bi bi-person-badge" id="referralModalHeadingIcon"></i>
                        </div>

                        <div>
                            <div class="referral-modal__eyebrow" id="referralModalEyebrow">
                                Консультация специалиста
                            </div>

                            <h5 class="modal-title" id="referralModalTitle">Направление к профильному специалисту</h5>
                            <div class="referral-modal__subtitle" id="referralModalSubtitle">
                                Найдите пациента, выберите врача нужной специальности и укажите клинический вопрос
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Закрыть"
                    ></button>
                </div>

                <div class="modal-body referral-modal__body">
                    <div class="referral-layout">
                        <div class="referral-main">

                            <!-- =====================================================
                                 ПАЦИЕНТ
                                 ===================================================== -->

                            <section class="referral-card">
                                <div class="referral-card__head">
                                    <div class="referral-card__icon">
                                        <i class="bi bi-person-vcard"></i>
                                    </div>

                                    <div>
                                        <div class="referral-card__title">Пациент</div>
                                        <div class="referral-card__hint">Поиск по ФИО, телефону или дате рождения</div>
                                    </div>
                                </div>

                                <div class="referral-search-box">
                                    <i class="bi bi-search"></i>
                                    <input
                                        type="search"
                                        class="form-control"
                                        id="referralPatientSearch"
                                        placeholder="Начните вводить ФИО пациента..."
                                        autocomplete="off"
                                    >
                                    <input
                                        type="hidden"
                                        name="patient_id"
                                        id="referralPatientId"
                                    >
                                    <div class="referral-search-results" id="referralPatientResults"></div>
                                </div>

                                <div class="referral-selected-entity d-none" id="referralSelectedPatient">
                                    <div class="referral-selected-entity__avatar">
                                        <i class="bi bi-person"></i>
                                    </div>

                                    <div class="referral-selected-entity__content">
                                        <div class="referral-selected-entity__name" id="referralPatientName">—</div>
                                        <div class="referral-selected-entity__meta" id="referralPatientMeta">—</div>
                                    </div>

                                    <button
                                        type="button"
                                        class="referral-selected-entity__clear"
                                        id="referralPatientClear"
                                        title="Изменить пациента"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </section>

                            <!-- =====================================================
                                 СПЕЦИАЛИСТ
                                 ===================================================== -->

                            <section class="referral-card">
                                <div class="referral-card__head">
                                    <div class="referral-card__icon">
                                        <i class="bi bi-person-badge"></i>
                                    </div>

                                    <div>
                                        <div class="referral-card__title" id="referralSpecialistCardTitle">
                                            Психиатрический специалист
                                        </div>

                                        <div class="referral-card__hint" id="referralSpecialistCardHint">
                                            В поиске доступен психиатрический персонал, зарегистрированный в системе
                                        </div>
                                    </div>
                                </div>

                                <div class="referral-specialist-mode">
                                    <button
                                        type="button"
                                        class="referral-specialist-mode__item is-active"
                                        data-specialist-mode="internal"
                                    >
                                        <span class="referral-specialist-mode__icon">
                                            <i class="bi bi-person-check"></i>
                                        </span>

                                        <span>
                                            <strong>Врач системы</strong>
                                            <small>Психиатрический персонал, зарегистрированный в системе</small>
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        class="referral-specialist-mode__item"
                                        data-specialist-mode="external"
                                    >
                                        <span class="referral-specialist-mode__icon">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </span>

                                        <span>
                                            <strong>Внешний специалист</strong>
                                            <small>Любая специальность и медицинская организация вне системы</small>
                                        </span>
                                    </button>
                                </div>

                                <!-- Внутренний специалист -->

                                <div id="referralInternalDoctorBlock">
                                    <div class="referral-doctor-scope" id="referralDoctorScopeBadge">
                                        <i class="bi bi-person-badge" id="referralDoctorScopeIcon"></i>
                                        <span id="referralDoctorScopeText">Психиатрический персонал системы</span>
                                    </div>

                                    <label class="form-label" for="referralDoctorSearch">
                                        <span id="referralDoctorLabelText">Врач</span>
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="referral-search-box">
                                        <i class="bi bi-search"></i>
                                        <input
                                            type="search"
                                            class="form-control"
                                            id="referralDoctorSearch"
                                            placeholder="ФИО или специальность: психиатр, психолог, психотерапевт..."
                                            autocomplete="off"
                                        >
                                        <input
                                            type="hidden"
                                            name="to_doctor_id"
                                            id="referralDoctorId"
                                        >
                                        <div class="referral-search-results" id="referralDoctorResults"></div>
                                    </div>

                                    <div class="referral-selected-entity d-none" id="referralSelectedDoctor">
                                        <div class="referral-selected-entity__avatar doctor">
                                            <i class="bi bi-person-badge"></i>
                                        </div>

                                        <div class="referral-selected-entity__content">
                                            <div class="referral-selected-entity__name" id="referralDoctorName">—</div>
                                            <div class="referral-selected-entity__meta" id="referralDoctorMeta">—</div>
                                        </div>

                                        <button
                                            type="button"
                                            class="referral-selected-entity__clear"
                                            id="referralDoctorClear"
                                            title="Изменить врача"
                                        >
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Внешний специалист -->

                                <div id="referralExternalDoctorBlock" class="d-none">
                                    <div class="referral-external-routing">
                                        <div class="referral-external-map-card">
                                            <div class="referral-external-section-head">
                                                <div class="referral-external-section-head__main">
                                                    <span class="referral-external-section-icon">
                                                        <i class="bi bi-geo-alt"></i>
                                                    </span>

                                                    <div>
                                                        <div class="referral-external-section-title">
                                                            Медицинская организация
                                                        </div>

                                                        <div class="referral-external-section-hint">
                                                            Поиск клиник, больниц и медицинских центров по данным
                                                            OpenStreetMap
                                                        </div>
                                                    </div>
                                                </div>

                                                <span class="referral-external-map-badge">
                                                    <i class="bi bi-map"></i>
                                                    Яндекс карта
                                                </span>
                                            </div>

                                            <div class="referral-external-clinic-search">
                                                <div class="referral-external-search-box">
                                                    <i class="bi bi-search"></i>
                                                    <input
                                                        type="search"
                                                        class="form-control"
                                                        id="referralExternalClinicSearch"
                                                        placeholder="Название клиники, больницы или медицинского центра..."
                                                        autocomplete="off"
                                                    >
                                                    <button
                                                        type="button"
                                                        class="referral-external-search-clear"
                                                        id="referralExternalClinicSearchClear"
                                                        title="Очистить поиск"
                                                    >
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="referral-external-search-button"
                                                    id="referralExternalClinicSearchButton"
                                                >
                                                    Найти
                                                </button>
                                            </div>

                                            <div class="referral-external-quick">
                                                <button type="button" data-external-clinic-query="больница">
                                                    Больницы
                                                </button>

                                                <button type="button" data-external-clinic-query="клиника">
                                                    Клиники
                                                </button>

                                                <button type="button" data-external-clinic-query="медицинский центр">
                                                    Медцентры
                                                </button>

                                                <button type="button" data-external-clinic-query="поликлиника">
                                                    Поликлиники
                                                </button>

                                                <button
                                                    type="button"
                                                    data-external-clinic-query="диагностический центр"
                                                >
                                                    Диагностические центры
                                                </button>
                                            </div>

                                            <div class="referral-external-map-layout">
                                                <div class="referral-external-map-wrap">
                                                    <div id="referralExternalMap" class="referral-external-map"></div>
                                                    <div
                                                        class="referral-external-map-state"
                                                        id="referralExternalMapState"
                                                    >
                                                        <div class="referral-external-map-state__inner">
                                                            <div class="referral-external-map-state__icon">
                                                                <i class="bi bi-map"></i>
                                                            </div>

                                                            <strong>Карта загружается</strong>
                                                            <span>
                                                                После загрузки здесь появятся медицинские организации.
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="referral-external-list-card">
                                                    <div class="referral-external-list-head">
                                                        <span>В текущей области карты</span>
                                                        <span
                                                            class="referral-external-list-count"
                                                            id="referralExternalClinicCount"
                                                        >
                                                            0
                                                        </span>
                                                    </div>

                                                    <div
                                                        class="referral-external-clinic-results"
                                                        id="referralExternalClinicResults"
                                                    >
                                                        <div class="referral-external-list-state">
                                                            <i class="bi bi-hospital"></i>
                                                            Выберите категорию или найдите организацию по названию
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="referral-external-selected-clinic d-none"
                                                id="referralExternalSelectedClinic"
                                            >
                                                <div class="referral-external-selected-clinic__icon">
                                                    <i class="bi bi-hospital"></i>
                                                </div>

                                                <div class="referral-external-selected-clinic__content">
                                                    <div
                                                        class="referral-external-selected-clinic__name"
                                                        id="referralExternalSelectedClinicName"
                                                    >
                                                        —
                                                    </div>

                                                    <div
                                                        class="referral-external-selected-clinic__address"
                                                        id="referralExternalSelectedClinicAddress"
                                                    >
                                                        —
                                                    </div>

                                                    <div
                                                        class="referral-external-selected-clinic__meta"
                                                        id="referralExternalSelectedClinicMeta"
                                                    ></div>
                                                </div>

                                                <div class="referral-external-selected-clinic__actions">
                                                    <a
                                                        href="#"
                                                        id="referralExternalSelectedClinicSite"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="d-none"
                                                        title="Открыть сайт"
                                                    >
                                                        <i class="bi bi-globe2"></i>
                                                    </a>

                                                    <button
                                                        type="button"
                                                        id="referralExternalSelectedClinicClear"
                                                        title="Изменить клинику"
                                                    >
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="referral-external-specialist-card">
                                            <div class="referral-external-section-head">
                                                <div class="referral-external-section-head__main">
                                                    <span class="referral-external-section-icon">
                                                        <i class="bi bi-person-search"></i>
                                                    </span>

                                                    <div>
                                                        <div class="referral-external-section-title">Специалист</div>
                                                        <div class="referral-external-section-hint">
                                                            Укажите специальность, к которой необходимо направить
                                                            пациента
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label" for="referralExternalSpeciality">
                                                        Специальность
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    <div class="referral-external-speciality-search">
                                                        <div class="referral-external-search-box">
                                                            <i class="bi bi-search"></i>
                                                            <input
                                                                type="search"
                                                                class="form-control"
                                                                id="referralExternalSpeciality"
                                                                maxlength="255"
                                                                placeholder="Начните вводить: невролог, кардиолог..."
                                                                autocomplete="off"
                                                                spellcheck="false"
                                                                disabled
                                                            >
                                                            <button
                                                                type="button"
                                                                class="referral-external-field-clear"
                                                                id="referralExternalSpecialityClear"
                                                                title="Очистить специальность"
                                                                tabindex="-1"
                                                            >
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                        </div>

                                                        <div
                                                            class="referral-external-speciality-results"
                                                            id="referralExternalSpecialityResults"
                                                        ></div>
                                                    </div>

                                                    <div
                                                        class="referral-speciality-verification d-none"
                                                        id="referralExternalSpecialityVerification"
                                                        role="status"
                                                        aria-live="polite"
                                                    >
                                                        <span class="referral-speciality-verification__icon">
                                                            <i class="bi bi-info-circle"></i>
                                                        </span>

                                                        <span class="referral-speciality-verification__content">
                                                            <strong
                                                                id="referralExternalSpecialityVerificationTitle"
                                                            ></strong>
                                                            <span
                                                                id="referralExternalSpecialityVerificationText"
                                                            ></span>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label" for="referralOrganization">
                                                        Медицинская организация
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="bi bi-building"></i>
                                                        </span>

                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="organization"
                                                            id="referralOrganization"
                                                            maxlength="255"
                                                            placeholder="Выберите клинику на карте или введите название вручную"
                                                            required
                                                            disabled
                                                        >
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="referral-external-manual-note">
                                                <i class="bi bi-info-circle"></i>
                                                <span>
                                                    Для внешнего направления достаточно выбрать медицинскую организацию
                                                    и указать требуемую специальность. Конкретного врача выбирать не
                                                    требуется.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- =====================================================
                                 ЦЕЛЬ
                                 ===================================================== -->

                            <section class="referral-card">
                                <div class="referral-card__head">
                                    <div class="referral-card__icon">
                                        <i class="bi bi-bullseye"></i>
                                    </div>

                                    <div>
                                        <div class="referral-card__title">Цель консультации</div>
                                        <div class="referral-card__hint">
                                            Укажите клинический вопрос для специалиста
                                        </div>
                                    </div>
                                </div>

                                <textarea
                                    class="form-control referral-purpose"
                                    name="purpose"
                                    rows="4"
                                    maxlength="5000"
                                    placeholder="Например: исключить неврологическую патологию, оценить необходимость дополнительного обследования"
                                ></textarea>
                                <div class="referral-quick-purposes mt-2">
                                    <button
                                        type="button"
                                        class="referral-chip"
                                        data-purpose="Уточнение диагноза"
                                    >
                                        Уточнение диагноза
                                    </button>

                                    <button
                                        type="button"
                                        class="referral-chip"
                                        data-purpose="Дифференциальная диагностика"
                                    >
                                        Дифференциальная диагностика
                                    </button>

                                    <button
                                        type="button"
                                        class="referral-chip"
                                        data-purpose="Коррекция терапии"
                                    >
                                        Коррекция терапии
                                    </button>

                                    <button
                                        type="button"
                                        class="referral-chip"
                                        data-purpose="Исключение сопутствующей патологии"
                                    >
                                        Исключение патологии
                                    </button>
                                </div>
                            </section>

                            <!-- =====================================================
                                 ДИАГНОЗ
                                 ===================================================== -->

                            <section class="referral-card">
                                <div class="referral-card__head">
                                    <div class="referral-card__icon">
                                        <i class="bi bi-activity"></i>
                                    </div>

                                    <div>
                                        <div class="referral-card__title">Предварительный диагноз</div>
                                        <div class="referral-card__hint">
                                            Найдите диагноз по коду МКБ-10 или названию
                                        </div>
                                    </div>
                                </div>

                                <div class="referral-search-box">
                                    <i class="bi bi-search"></i>
                                    <input
                                        type="search"
                                        class="form-control"
                                        id="referralDiagnosisSearch"
                                        placeholder="F31.2 или название диагноза..."
                                        autocomplete="off"
                                    >
                                    <input
                                        type="hidden"
                                        name="diagnose_id"
                                        id="referralDiagnosisId"
                                    >
                                    <div class="referral-search-results" id="referralDiagnosisResults"></div>
                                </div>

                                <div class="referral-selected-diagnosis d-none" id="referralSelectedDiagnosis">
                                    <div class="referral-selected-diagnosis__code" id="referralDiagnosisCode">—</div>
                                    <div class="referral-selected-diagnosis__content">
                                        <strong id="referralDiagnosisTitle">—</strong>
                                        <span>Предварительный диагноз</span>
                                    </div>

                                    <button
                                        type="button"
                                        class="referral-selected-diagnosis__clear"
                                        id="referralDiagnosisClear"
                                        title="Изменить диагноз"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </section>

                            <!-- =====================================================
                                 ПАРАМЕТРЫ
                                 ===================================================== -->

                            <section class="referral-card">
                                <div class="referral-card__head">
                                    <div class="referral-card__icon">
                                        <i class="bi bi-sliders"></i>
                                    </div>

                                    <div>
                                        <div class="referral-card__title">Параметры направления</div>
                                        <div class="referral-card__hint">
                                            Срочность, дата и дополнительная информация
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Приоритет</label>
                                        <select class="form-select" name="priority">
                                            <option value="normal">Планово</option>
                                            <option value="urgent">Срочно</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Планируемая дата</label>
                                        <input
                                            type="datetime-local"
                                            class="form-control"
                                            name="planned_at"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Дополнительный комментарий</label>
                                        <textarea
                                            class="form-control"
                                            name="comment"
                                            rows="3"
                                            maxlength="5000"
                                            placeholder="Дополнительная информация для принимающего врача"
                                        ></textarea>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- =========================================================
                             ПРАВАЯ СВОДКА
                             ========================================================= -->

                        <aside class="referral-side">
                            <section class="referral-side-card">
                                <div class="referral-side-title">
                                    <i class="bi bi-send"></i>
                                    Направление
                                </div>

                                <div class="referral-summary-empty" id="referralSummaryEmpty">
                                    Выберите пациента, специалиста и предварительный диагноз. Здесь появится сводка
                                    направления.
                                </div>

                                <div class="referral-summary d-none" id="referralSummary">
                                    <div class="referral-summary__badge" id="referralSummaryMode">
                                        <i class="bi bi-person-check"></i>
                                        Врач системы
                                    </div>

                                    <div class="referral-summary__group">
                                        <div class="referral-summary__label">Пациент</div>
                                        <div class="referral-summary__value" id="referralSummaryPatient">—</div>
                                    </div>

                                    <div class="referral-summary__arrow">
                                        <i class="bi bi-arrow-down"></i>
                                    </div>

                                    <div class="referral-summary__group">
                                        <div class="referral-summary__label">Специалист</div>
                                        <div class="referral-summary__value" id="referralSummaryDoctor">—</div>
                                    </div>

                                    <div class="referral-summary__diagnosis">
                                        <div class="referral-summary__label">Предварительный диагноз</div>
                                        <div class="referral-summary__value" id="referralSummaryDiagnosis">—</div>
                                    </div>
                                </div>
                            </section>

                            <section class="referral-info-card">
                                <div class="referral-info-card__icon">
                                    <i class="bi bi-info-circle"></i>
                                </div>

                                <div>
                                    <strong>После отправки</strong>
                                    <p id="referralAfterSendText">
                                        Направление появится у принимающего врача в списке входящих направлений.
                                    </p>
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>

                <div class="modal-footer referral-modal__footer">
                    <div class="referral-footer-note">
                        <i class="bi bi-shield-check"></i>
                        Направление будет сохранено в медицинской карте
                    </div>

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Отмена
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="referralPrintButton"
                        disabled
                    >
                        <i class="bi bi-printer me-1"></i>
                        Отправить и печать
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="referralSubmitButton"
                        name="submit_action"
                        value="save"
                        disabled
                    >
                        <i class="bi bi-send me-1"></i>
                        Отправить направление
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div
        id="consilium-modal-root"
        data-doctors-url="{{ route('api-get-doctors') }}"
        data-psy-doctors-url="{{ route('api-get-psy-doctors') }}"
        data-patients-url="{{ route('api.patients.search') }}"
        data-diagnoses-url="{{ route('api-get-diagnoses') }}"
        data-store-url="{{ route('doctors.consiliums.store') }}"
    ></div>
@endsection

@once
    @push('scripts')
        @vite('resources/js/consilium-modal.js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /*
                |--------------------------------------------------------------------------
                | Элементы
                |--------------------------------------------------------------------------
                */

                const modal = document.getElementById('instrumentalPatientModal');
                const input = document.getElementById('instrumentalPatientSearch');
                const results = document.getElementById('instrumentalPatientResults');
                const hint = document.getElementById('instrumentalPatientSearchHint');
                const loading = document.getElementById('instrumentalPatientSearchLoading');

                if (!modal || !input || !results) {
                    console.error('Не найдены элементы модального поиска пациента');
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | URL
                |--------------------------------------------------------------------------
                */

                const searchUrl = @json(route('api.patients.search'));
                const patientCardBaseUrl = @json(route('api.patients.search') );

                /*
                |--------------------------------------------------------------------------
                | Переменные
                |--------------------------------------------------------------------------
                */

                let searchTimer = null;
                let currentController = null;
                const MIN_QUERY_LENGTH = 2;
                const SEARCH_DELAY = 250;

                /*
                |--------------------------------------------------------------------------
                | Открытие модального окна
                |--------------------------------------------------------------------------
                */

                modal.addEventListener('shown.bs.modal', function () {
                    setTimeout(() => {
                        input.focus();
                    }, 50);
                });

                /*
                |--------------------------------------------------------------------------
                | Закрытие модального окна
                |--------------------------------------------------------------------------
                */

                modal.addEventListener('hidden.bs.modal', function () {
                    clearTimeout(searchTimer);

                    if (currentController) {
                        currentController.abort();
                        currentController = null;
                    }

                    input.value = '';
                    results.innerHTML = '';
                    hideLoading();
                    showHint('bi-person-search', 'Начните вводить ФИО, телефон или дату рождения');
                });

                /*
                |--------------------------------------------------------------------------
                | Живой поиск
                |--------------------------------------------------------------------------
                */

                input.addEventListener('input', function () {
                    clearTimeout(searchTimer);

                    const query = input.value.trim();

                    /*
                     * Если пользователь стёр текст
                     */

                    if (!query.length) {
                        if (currentController) {
                            currentController.abort();
                            currentController = null;
                        }

                        results.innerHTML = '';
                        hideLoading();
                        showHint('bi-person-search', 'Начните вводить ФИО, телефон или дату рождения');
                        return;
                    }

                    /*
                     * Минимум 2 символа
                     */

                    if (query.length < MIN_QUERY_LENGTH) {
                        if (currentController) {
                            currentController.abort();
                            currentController = null;
                        }

                        results.innerHTML = '';
                        hideLoading();
                        showHint('bi-keyboard', 'Введите минимум 2 символа');
                        return;
                    }

                    /*
                     * Небольшая задержка, чтобы не стрелять запросом
                     * на каждое мгновение ввода
                     */

                    searchTimer = setTimeout(function () {
                        searchPatients(query);
                    }, SEARCH_DELAY);
                });

                /*
                |--------------------------------------------------------------------------
                | Enter
                |--------------------------------------------------------------------------
                |
                | Если пользователь быстро ввёл запрос и нажал Enter,
                | не ждём debounce 250 мс.
                |
                */

                input.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();

                    const query = input.value.trim();

                    if (query.length < MIN_QUERY_LENGTH) {
                        return;
                    }

                    clearTimeout(searchTimer);
                    searchPatients(query);
                });

                /*
                |--------------------------------------------------------------------------
                | Поиск пациентов
                |--------------------------------------------------------------------------
                */

                async function searchPatients(query) {

                    /*
                     * Отменяем предыдущий запрос
                     */

                    if (currentController) {
                        currentController.abort();
                    }

                    const controller = new AbortController();

                    currentController = controller;

                    /*
                     * Состояние загрузки
                     */

                    results.innerHTML = '';
                    hideHint();
                    showLoading();

                    try {

                        /*
                         * Формируем URL безопасно
                         */

                        const url = new URL(searchUrl, window.location.origin);

                        url.searchParams.set('q', query);

                        /*
                         * AJAX
                         */

                        const response = await fetch(url.toString(), {
                            method: 'GET',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            signal: controller.signal
                        });

                        /*
                         * HTTP ошибка
                         */

                        if (!response.ok) {
                            throw new Error('Ошибка HTTP: ' + response.status);
                        }

                        /*
                         * JSON
                         */

                        const data = await response.json();

                        /*
                         * Для отладки можно оставить.
                         * Потом можно удалить.
                         */

                        console.log('Ответ поиска пациентов:', data);

                        /*
                         * Поддерживаем:
                         *
                         * [
                         *    {...}
                         * ]
                         *
                         * {
                         *    results: [...]
                         * }
                         *
                         * {
                         *    data: [...]
                         * }
                         */

                        let patients = [];

                        if (Array.isArray(data)) {
                            patients = data;
                        } else if (data && Array.isArray(data.results)) {
                            patients = data.results;
                        } else if (data && Array.isArray(data.data)) {
                            patients = data.data;
                        }

                        /*
                         * Проверяем, что это всё ещё актуальный запрос.
                         *
                         * Например:
                         * пользователь ввёл "Ива",
                         * а потом сразу "Иван".
                         */

                        if (controller !== currentController) {
                            return;
                        }

                        renderPatients(patients);
                    } catch (error) {

                        /*
                         * Отмена предыдущего запроса — это нормально
                         */

                        if (error.name === 'AbortError') {
                            return;
                        }

                        console.error('Ошибка поиска пациентов:', error);
                        results.innerHTML = '';
                        showHint('bi-exclamation-circle', 'Не удалось выполнить поиск пациентов');
                    } finally {

                        /*
                         * Старый отменённый запрос не должен
                         * выключать индикатор нового запроса.
                         */

                        if (controller === currentController) {
                            hideLoading();
                            currentController = null;
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Вывод пациентов
                |--------------------------------------------------------------------------
                */

                function renderPatients(patients) {
                    results.innerHTML = '';

                    /*
                     * Никого нет
                     */

                    if (!Array.isArray(patients) || patients.length === 0) {
                        showHint('bi-person-x', 'Пациенты не найдены');
                        return;
                    }

                    hideHint();

                    /*
                     * Создаём каждого пациента
                     */

                    patients.forEach(function (patient) {

                        /*
                         * API сейчас возвращает:
                         *
                         * id
                         * text
                         * birth_at
                         *
                         * Но оставляем поддержку и других
                         * названий полей.
                         */

                        const patientId = patient.id;
                        const fullName = patient.text || patient.full_name || patient.name ||
                            [patient.last_name, patient.first_name, patient.middle_name].filter(Boolean).join(' ') ||
                            'Пациент';
                        const birthDate = patient.birth_at || patient.birth_date || patient.birthday || '';
                        const phone = patient.phone || patient.phone_number || '';

                        /*
                         * Кнопка пациента
                         */

                        const item = document.createElement('button');

                        item.type = 'button';
                        item.className = 'patient-search-result';

                        /*
                         * Дополнительная информация
                         */

                        let meta = '';

                        if (birthDate) {
                            meta += 'Дата рождения: ' + escapeHtml(birthDate);
                        }

                        if (phone) {
                            if (meta) {
                                meta += ' · ';
                            }

                            meta += escapeHtml(phone);
                        }

                        /*
                         * HTML результата
                         */

                        item.innerHTML = `

                <span class="patient-search-result__avatar">
                    <i class="bi bi-person"></i>
                </span>

                <span class="patient-search-result__content">

                    <span class="patient-search-result__name">
                        ${escapeHtml(fullName)}
                    </span>

                    ${meta
                            ? `
                                <span class="patient-search-result__meta">
                                    ${meta}
                                </span>
                              `
                            : ''}

                </span>

                <span class="patient-search-result__arrow">
                    <i class="bi bi-chevron-right"></i>
                </span>

            `;

                        /*
                         * Выбор пациента
                         */

                        item.addEventListener('click', function () {
                            if (!patientId) {
                                console.error('У пациента отсутствует ID:', patient);
                                return;
                            }

                            /*
                             * Переход:
                             *
                             * /.../123?tab=instrumental
                             */

                            window.location.href = buildPatientUrl(patientId);
                        });

                        results.appendChild(item);
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | URL карточки пациента
                |--------------------------------------------------------------------------
                */

                function buildPatientUrl(patientId) {
                    return ('/doctors/patients/' + encodeURIComponent(patientId) + '?open=instrumental');
                }

                /*
                |--------------------------------------------------------------------------
                | Hint
                |--------------------------------------------------------------------------
                */

                function showHint(icon, text) {
                    if (!hint) {
                        return;
                    }

                    hint.innerHTML = `

            <i class="bi ${icon}"></i>

            <div>
                ${escapeHtml(text)}
            </div>

        `;

                    hint.classList.remove('d-none');
                }

                function hideHint() {
                    if (!hint) {
                        return;
                    }

                    hint.classList.add('d-none');
                }

                /*
                |--------------------------------------------------------------------------
                | Loading
                |--------------------------------------------------------------------------
                */

                function showLoading() {
                    if (!loading) {
                        return;
                    }

                    loading.classList.remove('d-none');
                }

                function hideLoading() {
                    if (!loading) {
                        return;
                    }

                    loading.classList.add('d-none');
                }

                /*
                |--------------------------------------------------------------------------
                | Escape HTML
                |--------------------------------------------------------------------------
                */

                function escapeHtml(value) {
                    const div = document.createElement('div');

                    div.textContent = value ?? '';
                    return div.innerHTML;
                }
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /*
                |--------------------------------------------------------------------------
                | API
                |--------------------------------------------------------------------------
                */

                const patientSearchUrl = @json(route('api.patients.search'));
                const doctorSearchUrls = Object.freeze({

                    /*
                     * «Врач системы» ищется среди уже существующего
                     * психиатрического персонала системы.
                     *
                     * AjaxController менять не нужно.
                     */

                    general: @json(route('api-get-psy-doctors')),

                    /*
                     * Отдельный сценарий «Психолог / патопсихолог»
                     * остаётся на том же endpoint.
                     */

                    psy: @json(route('api-get-psy-doctors'))
                });
                const diagnosisSearchUrl = @json(route('api-get-diagnoses'));
                const destinationPreviewUrl = @json(route('doctors.destinations.destination.preview'));

                /*
                |--------------------------------------------------------------------------
                | Основные элементы формы
                |--------------------------------------------------------------------------
                */

                const referralModal = document.getElementById('modalCreateDoctorDestination');
                const referralForm = document.getElementById('doctorDestinationForm');
                const referralSubmitButton = document.getElementById('referralSubmitButton');
                const referralPrintButton = document.getElementById('referralPrintButton');
                const referralType = document.getElementById('referralType');
                const referralSpeciality = document.getElementById('referralSpeciality');
                const internalDoctorBlock = document.getElementById('referralInternalDoctorBlock');
                const externalDoctorBlock = document.getElementById('referralExternalDoctorBlock');
                const externalSpeciality = document.getElementById('referralExternalSpeciality');
                const organization = document.getElementById('referralOrganization');
                const referralModalHeadingIcon = document.getElementById('referralModalHeadingIcon');
                const referralModalEyebrow = document.getElementById('referralModalEyebrow');
                const referralModalTitle = document.getElementById('referralModalTitle');
                const referralModalSubtitle = document.getElementById('referralModalSubtitle');
                const referralSpecialistCardTitle = document.getElementById('referralSpecialistCardTitle');
                const referralSpecialistCardHint = document.getElementById('referralSpecialistCardHint');
                const referralDoctorLabelText = document.getElementById('referralDoctorLabelText');
                const referralDoctorSearch = document.getElementById('referralDoctorSearch');
                const referralDoctorScopeBadge = document.getElementById('referralDoctorScopeBadge');
                const referralDoctorScopeIcon = document.getElementById('referralDoctorScopeIcon');
                const referralDoctorScopeText = document.getElementById('referralDoctorScopeText');
                const internalReferralPresentations = Object.freeze({
                    general: Object.freeze({
                        eyebrow: 'Консультация специалиста',
                        title: 'Направление к врачу',
                        subtitle: 'Найдите пациента и выберите специалиста из психиатрического персонала системы',
                        cardTitle: 'Психиатрический специалист',
                        cardHint: 'В поиске доступен психиатрический персонал, зарегистрированный в системе',
                        doctorLabel: 'Врач',
                        placeholder: 'ФИО или специальность: психиатр, психолог, психотерапевт...',
                        scopeText: 'Психиатрический персонал системы',
                        headingIcon: 'bi-person-badge',
                        scopeIcon: 'bi-person-badge'
                    }),
                    psy: Object.freeze({
                        eyebrow: 'Психодиагностическое исследование',
                        title: 'Направление к психологу / патопсихологу',
                        subtitle: 'Найдите пациента и выберите специалиста психодиагностического профиля',
                        cardTitle: 'Психолог / патопсихолог',
                        cardHint: 'В поиске доступны только психонаправленные специальности',
                        doctorLabel: 'Специалист',
                        placeholder: 'ФИО психолога, патопсихолога или специальность...',
                        scopeText: 'Психонаправленные специальности',
                        headingIcon: 'bi-person-heart',
                        scopeIcon: 'bi-person-heart'
                    })
                });
                const externalReferralPresentation = Object.freeze({
                    eyebrow: 'Внешняя маршрутизация',
                    title: 'Внешняя консультация',
                    subtitle: 'Выберите медицинскую организацию и укажите любую нужную специальность',
                    cardTitle: 'Внешний специалист',
                    cardHint: 'Для внешнего направления доступна любая медицинская специальность',
                    headingIcon: 'bi-box-arrow-up-right'
                });
                let specialistMode = 'internal';
                let doctorScope = 'general';

                /*
                |--------------------------------------------------------------------------
                | Пациент
                |--------------------------------------------------------------------------
                */

                createReferralAutocomplete({
                    type: 'patient',
                    input: '#referralPatientSearch',
                    hidden: '#referralPatientId',
                    results: '#referralPatientResults',
                    selected: '#referralSelectedPatient',
                    selectedName: '#referralPatientName',
                    selectedMeta: '#referralPatientMeta',
                    clear: '#referralPatientClear',
                    url: patientSearchUrl,
                    icon: 'bi-person',
                    getName(item) {
                        if (item.text) {
                            return item.text;
                        }

                        if (item.full_name) {
                            return item.full_name;
                        }

                        const fullName = [item.surname || item.last_name, item.name || item.first_name, item.patronym || item.middle_name]
                            .filter(Boolean)
                            .join(' ');

                        return (fullName || item.name || 'Пациент #' + item.id);
                    },
                    getMeta(item) {
                        const parts = [];
                        const birthDate = item.birth_at || item.birth_date || item.birthday || '';
                        const phone = item.phone || item.phone_number || '';

                        if (birthDate) {
                            parts.push('Дата рождения: ' + birthDate);
                        }

                        if (phone) {
                            parts.push(phone);
                        }

                        return parts.join(' · ');
                    },
                    onSelect() {
                        updateReferralSummary();
                    },
                    onClear() {
                        updateReferralSummary();
                    }
                });

                /*
                |--------------------------------------------------------------------------
                | Медицинский специалист
                |--------------------------------------------------------------------------
                */

                createReferralAutocomplete({
                    type: 'doctor',
                    input: '#referralDoctorSearch',
                    hidden: '#referralDoctorId',
                    results: '#referralDoctorResults',
                    selected: '#referralSelectedDoctor',
                    selectedName: '#referralDoctorName',
                    selectedMeta: '#referralDoctorMeta',
                    clear: '#referralDoctorClear',
                    url() {
                        return (doctorSearchUrls[doctorScope] || doctorSearchUrls.general);
                    },
                    icon: 'bi-person-badge',
                    filterItems(items, query) {
                        const needle = String(query || '').trim().toLocaleLowerCase('ru').replace(/ё/g, 'е');

                        if (!needle) {
                            return items;
                        }

                        return (Array.isArray(items) ? items : []).filter(function (item) {
                            const fullName = [item.surname || item.last_name, item.name || item.first_name, item.patronym || item.middle_name]
                                .filter(Boolean)
                                .join(' ');
                            const haystack = [
                                fullName,
                                item.status,
                                item.speciality,
                                item.specialty,
                                item.specialization,
                                item.speciality_name,
                                item.specialty_name
                            ]
                                .filter(Boolean)
                                .join(' ')
                                .toLocaleLowerCase('ru')
                                .replace(/ё/g, 'е');

                            return haystack.includes(needle);
                        })
                            .slice(0, 30);
                    },
                    getName(item) {
                        if (item.text) {
                            return item.text;
                        }

                        if (item.full_name) {
                            return item.full_name;
                        }

                        const fullName = [item.surname || item.last_name, item.name || item.first_name, item.patronym || item.middle_name]
                            .filter(Boolean)
                            .join(' ');

                        return (fullName || 'Врач #' + item.id);
                    },
                    getMeta(item) {
                        const parts = [];

                        /*
                         * status = специальность сотрудника.
                         */

                        const speciality = item.status || item.speciality || item.specialty || item.specialization || item.speciality_name ||
                            item.specialty_name ||
                            '';

                        if (speciality) {
                            parts.push(speciality);
                        }

                        if (item.experience) {
                            parts.push(item.experience);
                        }

                        if (item.clinic) {
                            const clinic = typeof item.clinic === 'string' ? item.clinic : (item.clinic.name || item.clinic.title || '');

                            if (clinic) {
                                parts.push(clinic);
                            }
                        }

                        if (item.office) {
                            const office = typeof item.office === 'string'
                                ? item.office
                                : (item.office.title || item.office.name || item.office.id || '');

                            if (office) {
                                parts.push('каб. ' + office);
                            }
                        }

                        return parts.filter(Boolean).join(' · ');
                    },
                    onSelect(item) {
                        if (specialistMode === 'internal') {
                            referralSpeciality.value = item.status || item.speciality || item.specialty || item.specialization || '';
                        }

                        updateReferralSummary();
                    },
                    onClear() {
                        if (specialistMode === 'internal') {
                            referralSpeciality.value = '';
                        }

                        updateReferralSummary();
                    }
                });

                /*
                |--------------------------------------------------------------------------
                | Предварительный диагноз
                |--------------------------------------------------------------------------
                */

                createReferralAutocomplete({
                    type: 'diagnosis',
                    input: '#referralDiagnosisSearch',
                    hidden: '#referralDiagnosisId',
                    results: '#referralDiagnosisResults',
                    selected: '#referralSelectedDiagnosis',
                    selectedName: '#referralDiagnosisTitle',
                    selectedMeta: '#referralDiagnosisCode',
                    clear: '#referralDiagnosisClear',
                    url: diagnosisSearchUrl,
                    icon: 'bi-activity',
                    getName(item) {
                        if (item.text) {
                            return item.text;
                        }

                        if (item.code && item.title) {
                            return (item.code + ' — ' + item.title);
                        }

                        return (item.code || item.title || 'Диагноз #' + item.id);
                    },
                    getMeta(item) {
                        return item.code || '';
                    },
                    getSelectedName(item) {
                        return item.title || item.text || '—';
                    },
                    getSelectedMeta(item) {
                        return item.code || '—';
                    },
                    onSelect() {
                        updateReferralSummary();
                    },
                    onClear() {
                        updateReferralSummary();
                    }
                });

                /*
                |--------------------------------------------------------------------------
                | Переключатель внутренний / внешний специалист
                |--------------------------------------------------------------------------
                */

                document.querySelectorAll('#modalCreateDoctorDestination [data-specialist-mode]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        setSpecialistMode(button.dataset.specialistMode || 'internal');
                    });
                });

                function setSpecialistMode(mode) {
                    specialistMode = mode === 'external' ? 'external' : 'internal';

                    document.querySelectorAll('#modalCreateDoctorDestination [data-specialist-mode]').forEach(function (button) {
                        button.classList.toggle('is-active', button.dataset.specialistMode === specialistMode);
                    });

                    if (specialistMode === 'external') {
                        referralType.value = 'external';
                        internalDoctorBlock?.classList.add('d-none');
                        externalDoctorBlock?.classList.remove('d-none');

                        /*
                         * Внутренний врач для внешнего направления
                         * не должен попасть в POST.
                         */

                        clearInternalDoctor();
                        externalSpeciality.disabled = false;
                        organization.disabled = false;
                        referralSpeciality.value = externalSpeciality.value.trim();
                    } else {
                        referralType.value = 'specialist';
                        internalDoctorBlock?.classList.remove('d-none');
                        externalDoctorBlock?.classList.add('d-none');
                        externalSpeciality.disabled = true;
                        organization.disabled = true;
                        externalSpeciality.value = '';
                        organization.value = '';

                        /*
                         * Специальность восстановится после
                         * выбора внутреннего врача.
                         */

                        referralSpeciality.value = '';
                    }

                    refreshReferralPresentation();
                    updateReferralSummary();
                }

                function setDoctorScope(scope) {
                    const nextScope = scope === 'psy' ? 'psy' : 'general';

                    if (doctorScope !== nextScope) {
                        clearInternalDoctor();
                    }

                    doctorScope = nextScope;
                    refreshReferralPresentation();
                }

                function refreshReferralPresentation() {
                    const presentation = specialistMode === 'external'
                        ? externalReferralPresentation
                        : (internalReferralPresentations[doctorScope] || internalReferralPresentations.general);

                    if (referralModalHeadingIcon) {
                        referralModalHeadingIcon.className = 'bi ' + presentation.headingIcon;
                    }

                    if (referralModalEyebrow) {
                        referralModalEyebrow.textContent = presentation.eyebrow;
                    }

                    if (referralModalTitle) {
                        referralModalTitle.textContent = presentation.title;
                    }

                    if (referralModalSubtitle) {
                        referralModalSubtitle.textContent = presentation.subtitle;
                    }

                    if (referralSpecialistCardTitle) {
                        referralSpecialistCardTitle.textContent = presentation.cardTitle;
                    }

                    if (referralSpecialistCardHint) {
                        referralSpecialistCardHint.textContent = presentation.cardHint;
                    }

                    referralDoctorScopeBadge?.classList.toggle('d-none', specialistMode === 'external');

                    if (specialistMode === 'external') {
                        return;
                    }

                    if (referralDoctorLabelText) {
                        referralDoctorLabelText.textContent = presentation.doctorLabel;
                    }

                    if (referralDoctorSearch) {
                        referralDoctorSearch.placeholder = presentation.placeholder;
                    }

                    if (referralDoctorScopeText) {
                        referralDoctorScopeText.textContent = presentation.scopeText;
                    }

                    if (referralDoctorScopeIcon) {
                        referralDoctorScopeIcon.className = 'bi ' + presentation.scopeIcon;
                    }
                }

                externalSpeciality?.addEventListener('input', function () {
                    if (specialistMode !== 'external') {
                        return;
                    }

                    referralSpeciality.value = externalSpeciality.value.trim();
                    updateReferralSummary();
                });

                organization?.addEventListener('input', updateReferralSummary);

                function clearInternalDoctor() {
                    const doctorId = document.getElementById('referralDoctorId');
                    const doctorSearch = document.getElementById('referralDoctorSearch');

                    if (doctorId) {
                        doctorId.value = '';
                    }

                    if (referralSpeciality) {
                        referralSpeciality.value = '';
                    }

                    if (doctorSearch) {
                        doctorSearch.value = '';
                        doctorSearch.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    const selectedDoctor = document.getElementById('referralSelectedDoctor');

                    selectedDoctor?.classList.add('d-none');

                    const selectedDoctorName = document.getElementById('referralDoctorName');
                    const selectedDoctorMeta = document.getElementById('referralDoctorMeta');

                    if (selectedDoctorName) {
                        selectedDoctorName.textContent = '—';
                    }

                    if (selectedDoctorMeta) {
                        selectedDoctorMeta.textContent = '—';
                    }

                    const doctorResults = document.getElementById('referralDoctorResults');

                    if (doctorResults) {
                        doctorResults.innerHTML = '';
                        doctorResults.classList.remove('is-open');
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Универсальный AJAX autocomplete
                |--------------------------------------------------------------------------
                */

                function createReferralAutocomplete(config) {
                    const input = getElement(config.input);
                    const hidden = getElement(config.hidden);
                    const results = getElement(config.results);
                    const selected = getElement(config.selected);
                    const selectedName = getElement(config.selectedName);
                    const selectedMeta = getElement(config.selectedMeta);
                    const clear = getElement(config.clear);

                    if (!input || !hidden || !results) {
                        console.warn('Autocomplete: не найдены обязательные элементы', config);
                        return;
                    }

                    let timer = null;
                    let controller = null;
                    let requestNumber = 0;

                    input.addEventListener('input', function () {
                        clearTimeout(timer);
                        hidden.value = '';
                        selected?.classList.add('d-none');

                        if (typeof config.onClear === 'function') {
                            config.onClear();
                        } else {
                            updateReferralSummary();
                        }

                        const query = input.value.trim();

                        if (query.length < 2) {
                            abortSearch();
                            closeResults();
                            return;
                        }

                        timer = setTimeout(function () {
                            performSearch(query);
                        }, 250);
                    });

                    input.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            closeResults();
                            return;
                        }

                        if (event.key !== 'Enter') {
                            return;
                        }

                        event.preventDefault();

                        const query = input.value.trim();

                        if (query.length < 2) {
                            return;
                        }

                        clearTimeout(timer);
                        performSearch(query);
                    });

                    async function performSearch(query) {
                        abortSearch();
                        controller = new AbortController();

                        const currentController = controller;
                        const currentRequest = ++requestNumber;

                        results.innerHTML = `
                            <div class="referral-search-state">
                                <span
                                    class="spinner-border spinner-border-sm me-1"
                                    role="status"
                                ></span>
                                Поиск...
                            </div>
                        `;

                        results.classList.add('is-open');

                        try {
                            const configuredUrl = typeof config.url === 'function' ? config.url() : config.url;

                            if (!configuredUrl) {
                                throw new Error('Не указан URL для поиска');
                            }

                            const url = new URL(configuredUrl, window.location.origin);

                            url.searchParams.set('q', query);

                            if (config.type === 'diagnosis') {
                                url.searchParams.set('by', 'any');
                                url.searchParams.set('per_page', '30');
                                url.searchParams.set('page', '1');
                            }

                            const response = await fetch(url.toString(), {
                                method: 'GET',
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                signal: currentController.signal
                            });

                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }

                            const data = await response.json();
                            const currentConfiguredUrl = typeof config.url === 'function' ? config.url() : config.url;

                            if (currentRequest !== requestNumber || currentConfiguredUrl !== configuredUrl) {
                                return;
                            }

                            let items = extractItems(data, config.type);

                            /*
                             * Текущий getPsyDoctors() возвращает весь список
                             * и не использует ?q=. Поэтому для врача системы
                             * фильтруем полученные items на frontend.
                             */

                            if (typeof config.filterItems === 'function') {
                                items = config.filterItems(items, query);
                            }

                            renderResults(items);
                        } catch (error) {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            console.error('Ошибка поиска:', error);

                            results.innerHTML = `
                                <div class="referral-search-state text-danger">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Не удалось выполнить поиск
                                </div>
                            `;

                            results.classList.add('is-open');
                        } finally {
                            if (controller === currentController) {
                                controller = null;
                            }
                        }
                    }

                    function abortSearch() {
                        if (!controller) {
                            return;
                        }

                        controller.abort();
                        controller = null;
                    }

                    function extractItems(data, type) {
                        if (Array.isArray(data)) {
                            return data;
                        }

                        if (!data || typeof data !== 'object') {
                            return [];
                        }

                        /*
                         * Врачи:
                         * { status: "success", items: [...] }
                         */

                        if (type === 'doctor' && Array.isArray(data.items)) {
                            return data.items;
                        }

                        /*
                         * Пациенты и диагнозы:
                         * { results: [...] }
                         */

                        if ((type === 'patient' || type === 'diagnosis') && Array.isArray(data.results)) {
                            return data.results;
                        }

                        if (Array.isArray(data.items)) {
                            return data.items;
                        }

                        if (Array.isArray(data.results)) {
                            return data.results;
                        }

                        if (Array.isArray(data.data)) {
                            return data.data;
                        }

                        if (data.data && typeof data.data === 'object') {
                            if (Array.isArray(data.data.items)) {
                                return data.data.items;
                            }

                            if (Array.isArray(data.data.results)) {
                                return data.data.results;
                            }

                            if (Array.isArray(data.data.data)) {
                                return data.data.data;
                            }
                        }

                        return [];
                    }

                    function renderResults(items) {
                        results.innerHTML = '';

                        if (!Array.isArray(items) || items.length === 0) {
                            results.innerHTML = `
                                <div class="referral-search-state">
                                    <i class="bi bi-search me-1"></i>
                                    Ничего не найдено
                                </div>
                            `;

                            results.classList.add('is-open');
                            return;
                        }

                        items.forEach(function (item) {
                            const name = config.getName(item);
                            const meta = config.getMeta(item);
                            const button = document.createElement('button');

                            button.type = 'button';
                            button.className = 'referral-search-result';

                            if (config.type === 'diagnosis') {
                                const code = item.code || '—';
                                const title = item.title || name;

                                button.innerHTML = `
                                        <span class="referral-search-result__icon">
                                            <i class="bi bi-activity"></i>
                                        </span>

                                        <span class="referral-search-result__content">
                                            <span class="referral-search-result__diagnosis">
                                                <strong class="referral-diagnosis-code">
                                                    ${escapeHtml(code)}
                                                </strong>

                                                <span class="referral-diagnosis-title">
                                                    ${escapeHtml(title)}
                                                </span>
                                            </span>
                                        </span>

                                        <span class="referral-search-result__arrow">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    `;
                            } else if (config.type === 'doctor') {
                                const speciality = item.status || item.speciality || item.specialty || item.specialization || '';
                                const experience = item.experience || '';

                                button.innerHTML = `
                                        <span class="referral-search-result__icon">
                                            <i class="bi ${config.icon}"></i>
                                        </span>

                                        <span class="referral-search-result__content">

                                            <span class="referral-search-result__name">
                                                ${escapeHtml(name)}
                                            </span>

                                            ${speciality || experience
                                    ? `
                                                        <span class="referral-search-result__meta">

                                                            ${speciality
                                        ? `
                                                                        <strong class="referral-doctor-speciality">
                                                                            ${escapeHtml(speciality)}
                                                                        </strong>
                                                                      `
                                        : ''}

                                                            ${speciality && experience ? ' · ' : ''}

                                                            ${experience ? escapeHtml(experience) : ''}

                                                        </span>
                                                      `
                                    : ''}

                                        </span>

                                        <span class="referral-search-result__arrow">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    `;
                            } else {
                                button.innerHTML = `
                                        <span class="referral-search-result__icon">
                                            <i class="bi ${config.icon}"></i>
                                        </span>

                                        <span class="referral-search-result__content">

                                            <span class="referral-search-result__name">
                                                ${escapeHtml(name)}
                                            </span>

                                            ${meta
                                    ? `
                                                        <span class="referral-search-result__meta">
                                                            ${escapeHtml(meta)}
                                                        </span>
                                                      `
                                    : ''}

                                        </span>

                                        <span class="referral-search-result__arrow">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    `;
                            }

                            button.addEventListener('click', function () {
                                if (!item.id) {
                                    console.warn('У найденного элемента отсутствует ID', item);
                                    return;
                                }

                                hidden.value = item.id;
                                input.value = name;
                                selected?.classList.remove('d-none');

                                if (selectedName) {
                                    selectedName.textContent =
                                        typeof config.getSelectedName === 'function' ? config.getSelectedName(item) : name;
                                }

                                if (selectedMeta) {
                                    selectedMeta.textContent =
                                        typeof config.getSelectedMeta === 'function' ? config.getSelectedMeta(item) : (meta || '—');
                                }

                                closeResults();

                                if (typeof config.onSelect === 'function') {
                                    config.onSelect(item);
                                }
                            });

                            results.appendChild(button);
                        });

                        results.classList.add('is-open');
                    }

                    clear?.addEventListener('click', function () {
                        abortSearch();
                        clearTimeout(timer);
                        hidden.value = '';
                        input.value = '';
                        selected?.classList.add('d-none');
                        closeResults();
                        input.focus();

                        if (typeof config.onClear === 'function') {
                            config.onClear();
                        }
                    });

                    document.addEventListener('click', function (event) {
                        if (event.target === input || results.contains(event.target)) {
                            return;
                        }

                        closeResults();
                    });

                    function closeResults() {
                        results.classList.remove('is-open');
                        results.innerHTML = '';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Сводка + кнопка отправки
                |--------------------------------------------------------------------------
                */

                function updateReferralSummary() {
                    const patientId = document.getElementById('referralPatientId')?.value || '';
                    const doctorId = document.getElementById('referralDoctorId')?.value || '';
                    const diagnosisId = document.getElementById('referralDiagnosisId')?.value || '';
                    const patientName = document.getElementById('referralPatientName')?.textContent?.trim() || '';
                    const doctorName = document.getElementById('referralDoctorName')?.textContent?.trim() || '';
                    const diagnosisInputValue = document.getElementById('referralDiagnosisSearch')?.value?.trim() || '';
                    const summary = document.getElementById('referralSummary');
                    const empty = document.getElementById('referralSummaryEmpty');
                    const summaryPatient = document.getElementById('referralSummaryPatient');
                    const summaryDoctor = document.getElementById('referralSummaryDoctor');
                    const summaryDiagnosis = document.getElementById('referralSummaryDiagnosis');
                    const summaryMode = document.getElementById('referralSummaryMode');
                    const afterSendText = document.getElementById('referralAfterSendText');
                    let specialistSelected = false;
                    let specialistLabel = '';

                    if (specialistMode === 'external') {
                        const speciality = externalSpeciality?.value?.trim() || '';
                        const orgName = organization?.value?.trim() || '';

                        specialistSelected = Boolean(speciality && orgName);
                        specialistLabel = [speciality, orgName].filter(Boolean).join(' · ') || 'Внешняя консультация';

                        if (summaryMode) {
                            summaryMode.innerHTML = `
                                <i class="bi bi-box-arrow-up-right"></i>
                                Внешнее направление
                            `;
                        }

                        if (afterSendText) {
                            afterSendText.textContent =
                                'Направление будет сохранено в медицинской карте пациента как внешнее направление.';
                        }
                    } else {
                        specialistSelected = Boolean(doctorId);
                        specialistLabel = doctorName || '—';

                        if (summaryMode) {
                            summaryMode.innerHTML =
                                doctorScope === 'psy'
                                    ? `
                                        <i class="bi bi-person-heart"></i>
                                        Специалист системы
                                      `
                                    : `
                                        <i class="bi bi-person-check"></i>
                                        Врач системы
                                      `;
                        }

                        if (afterSendText) {
                            afterSendText.textContent =
                                doctorScope === 'psy'
                                    ? 'Направление появится у выбранного специалиста в списке входящих направлений.'
                                    : 'Направление появится у принимающего врача в списке входящих направлений.';
                        }
                    }

                    const completed = Boolean(patientId && specialistSelected && diagnosisId);

                    if (patientId || specialistSelected || diagnosisId) {
                        empty?.classList.add('d-none');
                        summary?.classList.remove('d-none');

                        if (summaryPatient) {
                            summaryPatient.textContent = patientName || '—';
                        }

                        if (summaryDoctor) {
                            summaryDoctor.textContent = specialistLabel || '—';
                        }

                        if (summaryDiagnosis) {
                            summaryDiagnosis.textContent = diagnosisInputValue || '—';
                        }
                    } else {
                        summary?.classList.add('d-none');
                        empty?.classList.remove('d-none');
                    }

                    if (referralSubmitButton) {
                        referralSubmitButton.disabled = !completed;
                    }

                    if (referralPrintButton) {
                        referralPrintButton.disabled = !completed;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Быстрые цели
                |--------------------------------------------------------------------------
                */

                document.querySelectorAll('#modalCreateDoctorDestination [data-purpose]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const textarea = document.querySelector('#modalCreateDoctorDestination [name="purpose"]');

                        if (!textarea) {
                            return;
                        }

                        const value = button.dataset.purpose || '';

                        if (!value) {
                            return;
                        }

                        if (!textarea.value.trim()) {
                            textarea.value = value;
                        } else {
                            textarea.value = textarea.value.trim().replace(/[.;]+$/, '') + '. ' + value;
                        }

                        textarea.focus();
                    });
                });

                /*
                |--------------------------------------------------------------------------
                | Модалка
                |--------------------------------------------------------------------------
                */

                referralModal?.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    const requestedMode = trigger?.dataset?.referralMode || 'internal';
                    const requestedScope = trigger?.dataset?.referralScope || 'general';

                    setDoctorScope(requestedScope);
                    setSpecialistMode(requestedMode);
                });

                referralModal?.addEventListener('shown.bs.modal', function () {
                    document.getElementById('referralPatientSearch')?.focus();
                });

                referralModal?.addEventListener('hidden.bs.modal', function () {

                    /*
                     * Сбрасываем только поля этой формы.
                     */

                    referralForm?.reset();

                    ['referralPatientSearch', 'referralDoctorSearch', 'referralDiagnosisSearch'].forEach(function (id) {
                        const input = document.getElementById(id);

                        if (!input) {
                            return;
                        }

                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });

                    ['referralPatientId', 'referralDoctorId', 'referralDiagnosisId', 'referralSpeciality'].forEach(function (id) {
                        const element = document.getElementById(id);

                        if (element) {
                            element.value = '';
                        }
                    });

                    ['referralSelectedPatient', 'referralSelectedDoctor', 'referralSelectedDiagnosis'].forEach(function (id) {
                        document.getElementById(id)?.classList.add('d-none');
                    });

                    ['referralPatientResults', 'referralDoctorResults', 'referralDiagnosisResults'].forEach(function (id) {
                        const element = document.getElementById(id);

                        if (!element) {
                            return;
                        }

                        element.innerHTML = '';
                        element.classList.remove('is-open');
                    });

                    setDoctorScope('general');
                    setSpecialistMode('internal');
                    updateReferralSummary();
                });

                /*
                |--------------------------------------------------------------------------
                | Печать -> подтверждение -> сохранение
                |--------------------------------------------------------------------------
                |
                | ВАЖНО:
                |
                | Браузер не сообщает JavaScript, нажал пользователь
                | «Печать» или «Отмена» в системном print-dialog.
                |
                | Поэтому после закрытия системного окна:
                |
                | - если печать действительно выполнена -> подтверждаем
                |   и отправляем форму;
                |
                | - если печать отменена -> не отправляем форму,
                |   возвращаем кнопки в обычное состояние.
                |
                */

                let referralPrintInProgress = false;

                referralPrintButton?.addEventListener('click', async function () {
                    if (referralPrintInProgress || !referralForm) {
                        return;
                    }

                    if (!referralForm.reportValidity()) {
                        return;
                    }

                    const patientId = document.getElementById('referralPatientId')?.value || '';
                    const diagnosisId = document.getElementById('referralDiagnosisId')?.value || '';
                    const doctorId = document.getElementById('referralDoctorId')?.value || '';
                    const speciality = referralSpeciality?.value?.trim() || '';
                    const validSpecialist = specialistMode === 'external'
                        ? Boolean(speciality && organization?.value?.trim())
                        : Boolean(doctorId);

                    if (!patientId || !diagnosisId || !validSpecialist) {
                        updateReferralSummary();
                        return;
                    }

                    referralPrintInProgress = true;

                    const initialPrintHtml = referralPrintButton.innerHTML;

                    setReferralPrintBusy(true);

                    referralPrintButton.innerHTML = `
                                <span
                                    class="spinner-border spinner-border-sm"
                                    role="status"
                                    aria-hidden="true"
                                ></span>
                                Подготовка печати...
                            `;

                    try {

                        /*
                         * Preview ничего не сохраняет в БД.
                         */

                        const response = await fetch(destinationPreviewUrl, {
                            method: 'POST',
                            body: new FormData(referralForm),
                            credentials: 'same-origin',
                            headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        if (!response.ok) {
                            throw new Error('Не удалось подготовить печатную форму. HTTP ' + response.status);
                        }

                        const html = await response.text();
                        const shouldSend = await printDoctorDestinationPreview(html);

                        /*
                         * Печать отменена либо врач ответил,
                         * что документ не был распечатан.
                         *
                         * Ничего на сервер не отправляем.
                         * Полностью восстанавливаем кнопки.
                         */

                        if (!shouldSend) {
                            referralPrintInProgress = false;
                            referralPrintButton.innerHTML = initialPrintHtml;
                            updateReferralSummary();
                            return;
                        }

                        /*
                         * Печать подтверждена.
                         *
                         * Только теперь создаём направление.
                         */

                        referralPrintButton.innerHTML = `
                                    <span
                                        class="spinner-border spinner-border-sm"
                                        role="status"
                                        aria-hidden="true"
                                    ></span>
                                    Отправка...
                                `;

                        /*
                         * Используем обычную отправку формы.
                         *
                         * Валидация уже была выполнена выше.
                         * submit() здесь намеренно:
                         * не запускаем повторно наш JS submit-handler.
                         */

                        referralForm.submit();
                    } catch (error) {
                        referralPrintInProgress = false;
                        referralPrintButton.innerHTML = initialPrintHtml;
                        updateReferralSummary();
                        console.error('[Doctor destination preview]', error);
                        alert(error?.message || 'Не удалось подготовить направление к печати.');
                    }
                });

                function setReferralPrintBusy(busy) {
                    if (referralPrintButton) {
                        referralPrintButton.disabled = busy;
                    }

                    if (referralSubmitButton) {
                        referralSubmitButton.disabled = busy;
                    }
                }

                function printDoctorDestinationPreview(html) {
                    return new Promise(function (resolve, reject) {
                        document.getElementById('doctorDestinationPrintFrame')?.remove();

                        const frame = document.createElement('iframe');

                        frame.id = 'doctorDestinationPrintFrame';
                        frame.title = 'Печать направления';

                        Object.assign(frame.style, {
                            position: 'fixed',
                            left: '-10000px',
                            top: '0',
                            width: '210mm',
                            height: '297mm',
                            border: '0',
                            opacity: '0',
                            pointerEvents: 'none'
                        });

                        let settled = false;

                        function cleanup() {
                            setTimeout(function () {
                                frame.remove();
                            }, 100);
                        }

                        function finishPrintDialog() {
                            if (settled) {
                                return;
                            }

                            settled = true;
                            cleanup();

                            /*
                             * Нельзя программно узнать,
                             * была реальная печать или нажата «Отмена».
                             *
                             * Поэтому явно спрашиваем врача.
                             *
                             * При «Отмена» Promise вернёт false,
                             * а кнопка печати будет снова доступна.
                             */

                            const printed = window.confirm('Документ распечатан?\n\n' + 'Нажмите «ОК», чтобы отправить направление.\n'
                                + 'Если печать была отменена — нажмите «Отмена».');

                            resolve(printed);
                        }

                        frame.addEventListener('load', function () {
                            const printWindow = frame.contentWindow;

                            if (!printWindow) {
                                cleanup();
                                reject(new Error('Не удалось открыть документ для печати.'));
                                return;
                            }

                            /*
                             * Основной сигнал закрытия print-dialog.
                             */

                            printWindow.addEventListener('afterprint', finishPrintDialog, { once: true });

                            setTimeout(function () {
                                try {
                                    printWindow.focus();

                                    /*
                                     * В Firefox print() блокирует выполнение
                                     * до закрытия системного окна.
                                     *
                                     * Поэтому finishPrintDialog() вызываем
                                     * и после возврата из print().
                                     *
                                     * Если afterprint уже сработал,
                                     * защита settled не даст выполнить
                                     * обработку второй раз.
                                     */

                                    printWindow.print();
                                    finishPrintDialog();
                                } catch (error) {
                                    if (settled) {
                                        return;
                                    }

                                    settled = true;
                                    cleanup();
                                    reject(error);
                                }
                            }, 120);
                        }, { once: true });

                        frame.srcdoc = html;
                        document.body.appendChild(frame);
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Защита перед обычным submit
                |--------------------------------------------------------------------------
                */

                referralForm?.addEventListener('submit', function (event) {
                    const patientId = document.getElementById('referralPatientId')?.value || '';
                    const diagnosisId = document.getElementById('referralDiagnosisId')?.value || '';
                    const doctorId = document.getElementById('referralDoctorId')?.value || '';
                    const speciality = referralSpeciality?.value?.trim() || '';
                    const validSpecialist = specialistMode === 'external' ? Boolean(speciality && organization?.value?.trim()) : Boolean(doctorId);

                    if (patientId && diagnosisId && validSpecialist) {
                        return;
                    }

                    event.preventDefault();
                    updateReferralSummary();
                });

                /*
                |--------------------------------------------------------------------------
                | Helpers
                |--------------------------------------------------------------------------
                */

                function getElement(selector) {
                    if (!selector) {
                        return null;
                    }

                    return document.querySelector(selector);
                }

                function escapeHtml(value) {
                    const div = document.createElement('div');

                    div.textContent = value ?? '';
                    return div.innerHTML;
                }

                /*
                 * Начальное состояние.
                 */

                setDoctorScope('general');
                setSpecialistMode('internal');
                updateReferralSummary();
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /*
                |--------------------------------------------------------------------------
                | External referral
                |--------------------------------------------------------------------------
                |
                | Карта:
                |   Yandex Maps JavaScript API 3.0
                |
                | Клиники / больницы / врачи:
                |   OpenStreetMap + Overpass API через Laravel proxy
                |
                | Платный Yandex Organization Search здесь НЕ используется.
                |
                */

                const OSM_SEARCH_URL = @json(url('/api/osm-medical-organizations'));
                const OSM_REVERSE_URL = @json(url('/api/osm-reverse-geocode'));
                const EXTERNAL_SPECIALITIES = Object.freeze([
                    ['Аллерголог-иммунолог', 'allergology;immunology', 'аллерголог иммунолог аллергология иммунология'],
                    ['Анестезиолог-реаниматолог', 'anaesthetics', 'анестезиолог реаниматолог анестезиология'],
                    ['Врач общей практики', 'general', 'воп семейный врач общая практика'],
                    ['Гастроэнтеролог', 'gastroenterology', 'гастроэнтерология'],
                    ['Гематолог', 'haematology', 'гематология'],
                    ['Генетик', 'genetics', 'медицинский генетик генетика'],
                    ['Гериатр', 'geriatrics', 'гериатрия'],
                    ['Гинеколог', 'gynaecology', 'акушер гинеколог гинекология'],
                    ['Дерматовенеролог', 'dermatology', 'дерматолог дерматология'],
                    ['Диетолог', 'nutrition', 'диетология нутрициолог'],
                    ['Инфекционист', 'infectious_diseases', 'инфекционные болезни'],
                    ['Кардиолог', 'cardiology', 'кардиология'],
                    ['Колопроктолог', 'proctology', 'проктолог проктология'],
                    ['Невролог', 'neurology', 'невропатолог неврология детский невролог'],
                    ['Нейрохирург', 'neurosurgery', 'нейрохирургия'],
                    ['Нефролог', 'nephrology', 'нефрология'],
                    ['Онколог', 'oncology', 'онкология маммолог'],
                    ['Ортопед', 'orthopaedics', 'ортопедия'],
                    ['Оториноларинголог (ЛОР)', 'otolaryngology', 'лор оториноларинголог отоларинголог'],
                    ['Офтальмолог', 'ophthalmology', 'окулист офтальмология'],
                    ['Педиатр', 'paediatrics', 'педиатрия детский врач'],
                    ['Психиатр', 'psychiatry', 'психиатрия детский психиатр'],
                    ['Психотерапевт', 'psychotherapy', 'психотерапия'],
                    ['Пульмонолог', 'pulmonology', 'пульмонология'],
                    ['Ревматолог', 'rheumatology', 'ревматология'],
                    ['Рентгенолог', 'radiology', 'радиолог рентгенология лучевая диагностика'],
                    ['Сердечно-сосудистый хирург', 'cardiothoracic_surgery', 'кардиохирург сосудистый хирург'],
                    ['Стоматолог', 'dentistry', 'стоматология зубной врач'],
                    ['Терапевт', 'general', 'терапия врач терапевт'],
                    ['Травматолог-ортопед', 'orthopaedics', 'травматолог травматология'],
                    ['Уролог', 'urology', 'урология андролог'],
                    ['Фтизиатр', 'tuberculosis', 'фтизиатрия'],
                    ['Хирург', 'surgery', 'хирургия общий хирург'],
                    ['Челюстно-лицевой хирург', 'oral_surgery', 'челюстно лицевая хирургия'],
                    ['Эндокринолог', 'endocrinology', 'эндокринология']
                ]).map(function (item) {
                    return { label: item[0], osm: item[1], aliases: item[2] };
                });
                const modal = document.getElementById('modalCreateDoctorDestination');
                const externalBlock = document.getElementById('referralExternalDoctorBlock');
                const mapContainer = document.getElementById('referralExternalMap');
                const mapState = document.getElementById('referralExternalMapState');
                const clinicSearchInput = document.getElementById('referralExternalClinicSearch');
                const clinicSearchButton = document.getElementById('referralExternalClinicSearchButton');
                const clinicSearchClear = document.getElementById('referralExternalClinicSearchClear');
                const clinicResults = document.getElementById('referralExternalClinicResults');
                const clinicCount = document.getElementById('referralExternalClinicCount');
                const selectedClinicCard = document.getElementById('referralExternalSelectedClinic');
                const selectedClinicName = document.getElementById('referralExternalSelectedClinicName');
                const selectedClinicAddress = document.getElementById('referralExternalSelectedClinicAddress');
                const selectedClinicMeta = document.getElementById('referralExternalSelectedClinicMeta');
                const selectedClinicSite = document.getElementById('referralExternalSelectedClinicSite');
                const selectedClinicClear = document.getElementById('referralExternalSelectedClinicClear');
                const specialityInput = document.getElementById('referralExternalSpeciality');
                const specialityResults = document.getElementById('referralExternalSpecialityResults');
                const specialityClear = document.getElementById('referralExternalSpecialityClear');
                const specialityVerification = document.getElementById('referralExternalSpecialityVerification');
                const specialityVerificationTitle = document.getElementById('referralExternalSpecialityVerificationTitle');
                const specialityVerificationText = document.getElementById('referralExternalSpecialityVerificationText');
                const organizationInput = document.getElementById('referralOrganization');

                if (!modal || !externalBlock || !mapContainer || !clinicResults) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | State
                |--------------------------------------------------------------------------
                */

                let map = null;
                let YMapMarker = null;
                let mapPromise = null;
                let clinics = [];
                let markers = [];
                let selectedClinic = null;
                let clinicTimer = null;
                let doctorTimer = null;
                let clinicController = null;

                /*
                 * Автообновление организаций при перемещении/масштабировании карты.
                 */

                let viewportTimer = null;
                let viewportSearchBlockedUntil = 0;
                let syncingOrganization = false;
                let specialityMatches = [];
                let specialityActiveIndex = -1;

                /*
                 * Не запрашиваем один и тот же адрес повторно
                 * даже в рамках одной открытой страницы.
                 */

                const reverseAddressCache = new Map();

                /*
                 * JS API 3.0 coordinates:
                 * [longitude, latitude]
                 */

                const TOMSK = [84.9476, 56.4846];

                /*
                |--------------------------------------------------------------------------
                | Open modal
                |--------------------------------------------------------------------------
                */

                modal.addEventListener('shown.bs.modal', function () {
                    if (!externalBlock.classList.contains('d-none')) {
                        initMap().catch(function () { });
                    }
                });

                document.querySelectorAll('#modalCreateDoctorDestination [data-specialist-mode="external"]').forEach(function (button) {
                    button.addEventListener('click', function () {

                        /*
                         * Дожидаемся, пока основной код формы
                         * снимет d-none с внешнего блока.
                         */

                        setTimeout(function () {
                            initMap().catch(function () { });
                        }, 50);
                    });
                });

                modal.addEventListener('hidden.bs.modal', function () {
                    clearTimeout(clinicTimer);
                    clearTimeout(viewportTimer);
                    clinicController?.abort();
                });

                /*
                |--------------------------------------------------------------------------
                | Yandex Map JS API 3.0
                |--------------------------------------------------------------------------
                */

                async function initMap() {
                    if (map) {
                        return map;
                    }

                    if (mapPromise) {
                        return mapPromise;
                    }

                    mapPromise =
                        (async function () {
                            if (!window.ymaps3) {
                                showMapState('bi-key', 'JavaScript API 3.0 не загружен', 'Проверьте YANDEX_MAPS_API_KEY и HTTP Referer.');
                                throw new Error('ymaps3 is unavailable');
                            }

                            try {
                                await ymaps3.ready;

                                const { YMap, YMapDefaultSchemeLayer, YMapDefaultFeaturesLayer, YMapMarker: Marker, YMapListener } = ymaps3;

                                YMapMarker = Marker;

                                map =
                                    new YMap(mapContainer, { location: { center: TOMSK, zoom: 12 }, margin: [28, 28, 28, 28] }, [new YMapDefaultSchemeLayer({}), new YMapDefaultFeaturesLayer({ zIndex: 1800 })]);

                                /*
                                 * Первичные update-события возникают и при создании карты.
                                 * На короткое время блокируем авто-поиск, чтобы не делать
                                 * дубль первого запроса.
                                 */

                                blockViewportSearch(1200);

                                map.addChild(new YMapListener({
                                    layer: 'any',
                                    onUpdate: function () {
                                        scheduleViewportSearch();
                                    }
                                }));

                                hideMapState();

                                /*
                                 * При первом открытии сразу показываем
                                 * больницы в текущей области.
                                 */

                                if (!clinicSearchInput.value.trim()) {
                                    clinicSearchInput.value = 'больница';
                                    updateClinicClear();
                                }

                                await searchClinics(clinicSearchInput.value, { skipMapInit: true });
                                return map;
                            } catch (error) {
                                console.error('[External referral] map init:', error);
                                showMapState('bi-exclamation-triangle', 'Карта не загрузилась', 'Проверьте ключ JavaScript API и консоль браузера.');
                                mapPromise = null;
                                throw error;
                            }
                        })();

                    return mapPromise;
                }

                /*
                |--------------------------------------------------------------------------
                | Clinic controls
                |--------------------------------------------------------------------------
                */

                clinicSearchButton?.addEventListener('click', function () {
                    runClinicSearch();
                });

                clinicSearchInput?.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();
                    clearTimeout(clinicTimer);
                    runClinicSearch();
                });

                clinicSearchInput?.addEventListener('input', function () {
                    updateClinicClear();
                    clearTimeout(clinicTimer);

                    const query = clinicSearchInput.value.trim();

                    if (query.length < 2) {
                        return;
                    }

                    clinicTimer =
                        setTimeout(function () {
                            searchClinics(query);
                        }, 380);
                });

                clinicSearchClear?.addEventListener('click', function () {
                    clinicSearchInput.value = '';
                    updateClinicClear();
                    clinicSearchInput.focus();
                });

                document.querySelectorAll('#referralExternalDoctorBlock [data-external-clinic-query]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const query = button.dataset.externalClinicQuery || '';

                        if (!query) {
                            return;
                        }

                        clinicSearchInput.value = query;
                        updateClinicClear();
                        searchClinics(query);
                    });
                });

                function runClinicSearch() {
                    const query = clinicSearchInput.value.trim();

                    if (query.length < 2) {
                        clinicSearchInput.focus();
                        return;
                    }

                    searchClinics(query);
                }

                /*
                |--------------------------------------------------------------------------
                | Clinic search: Laravel -> Overpass
                |--------------------------------------------------------------------------
                */

                async function searchClinics(query, options = {}) {
                    query = String(query || '').trim();

                    if (query.length < 2) {
                        return;
                    }

                    if (!options.skipMapInit) {
                        try {
                            await initMap();
                        } catch (error) {
                            return;
                        }
                    }

                    clinicController?.abort();
                    clinicController = new AbortController();

                    const currentController = clinicController;

                    clinicCount.textContent = '…';
                    showClinicState('bi-hourglass-split', 'Ищем медицинские организации в OpenStreetMap...');

                    try {
                        const url = new URL(OSM_SEARCH_URL, window.location.origin);

                        url.searchParams.set('kind', 'organization');
                        url.searchParams.set('q', query);
                        url.searchParams.set('results', '30');

                        const area = getMapArea();

                        if (area) {
                            url.searchParams.set('ll', area.ll);
                            url.searchParams.set('spn', area.spn);
                        }

                        const response = await fetch(url.toString(), {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            signal: currentController.signal
                        });
                        const payload = await response.json().catch(function () {
                            return {};
                        });

                        if (!response.ok) {
                            throw new Error(payload.message || ('HTTP ' + response.status));
                        }

                        if (currentController !== clinicController) {
                            return;
                        }

                        /*
                         * Overpass для way/relation считает попаданием в bbox
                         * даже пересечение геометрии объекта с областью.
                         * При этом center, где мы ставим маркер, может оказаться
                         * уже за пределами видимой карты.
                         *
                         * Поэтому финально оставляем ТОЛЬКО организации,
                         * чей фактический маркер находится внутри текущих
                         * Yandex map.bounds.
                         */

                        clinics =
                            (Array.isArray(payload.items) ? payload.items : []).filter(function (place) {
                                return markerIsVisibleNow(place?.coords);
                            });

                        renderClinics();
                        renderMarkers();

                        /*
                         * ВАЖНО:
                         * карту здесь не двигаем. Результаты уже ограничены
                         * текущими bounds и должны соответствовать именно
                         * тому, что пользователь сейчас видит.
                         */

                    } catch (error) {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        console.error('[OSM clinic search]', error);
                        clinics = [];
                        clearMarkers();
                        clinicCount.textContent = '0';
                        showClinicState('bi-exclamation-circle', error.message || 'Не удалось выполнить поиск организаций.');
                    } finally {
                        if (currentController === clinicController) {
                            clinicController = null;
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Auto refresh by visible map area
                |--------------------------------------------------------------------------
                */

                function blockViewportSearch(milliseconds = 900) {
                    viewportSearchBlockedUntil = Math.max(viewportSearchBlockedUntil, Date.now() + milliseconds);
                    clearTimeout(viewportTimer);
                }

                function scheduleViewportSearch() {

                    /*
                     * map.setLocation() используется при выборе клиники.
                     * В этот момент не запускаем новый поиск автоматически.
                     */

                    if (Date.now() < viewportSearchBlockedUntil) {
                        return;
                    }

                    clearTimeout(viewportTimer);

                    viewportTimer =
                        setTimeout(function () {
                            if (Date.now() < viewportSearchBlockedUntil) {
                                return;
                            }

                            if (externalBlock.classList.contains('d-none')) {
                                return;
                            }

                            const query = clinicSearchInput.value.trim() || 'больница';

                            if (query.length < 2) {
                                return;
                            }

                            /*
                             * getMapArea() внутри searchClinics()
                             * возьмёт уже НОВЫЕ bounds карты.
                             *
                             * Таким образом список справа всегда
                             * соответствует текущей видимой области.
                             */

                            searchClinics(query, { skipMapInit: true });
                        }, 650);
                }

                /*
                |--------------------------------------------------------------------------
                | Current visible map area
                |--------------------------------------------------------------------------
                */

                function getMapArea() {
                    const bounds = map?.bounds;

                    if (!Array.isArray(bounds) || bounds.length !== 2) {
                        return { ll: TOMSK.join(','), spn: '0.30,0.22' };
                    }

                    const first = bounds[0];
                    const second = bounds[1];

                    if (!Array.isArray(first) || !Array.isArray(second)) {
                        return { ll: TOMSK.join(','), spn: '0.30,0.22' };
                    }

                    const west = Math.min(first[0], second[0]);
                    const east = Math.max(first[0], second[0]);
                    const south = Math.min(first[1], second[1]);
                    const north = Math.max(first[1], second[1]);
                    const center = [(west + east) / 2, (south + north) / 2];
                    const span = [Math.max(east - west, 0.01), Math.max(north - south, 0.01)];

                    return { ll: center.join(','), spn: span.join(',') };
                }

                /*
                |--------------------------------------------------------------------------
                | Strict visible marker filter
                |--------------------------------------------------------------------------
                */

                function markerIsVisibleNow(coords) {
                    if (!Array.isArray(coords) || coords.length !== 2) {
                        return false;
                    }

                    const longitude = Number(coords[0]);
                    const latitude = Number(coords[1]);

                    if (!Number.isFinite(longitude) || !Number.isFinite(latitude)) {
                        return false;
                    }

                    const bounds = map?.bounds;

                    /*
                     * Если bounds ещё не готовы, не выбрасываем
                     * корректный результат.
                     */

                    if (!Array.isArray(bounds) || bounds.length !== 2 || !Array.isArray(bounds[0]) ||
                        !Array.isArray(bounds[1])) {
                        return true;
                    }

                    const west = Math.min(Number(bounds[0][0]), Number(bounds[1][0]));
                    const east = Math.max(Number(bounds[0][0]), Number(bounds[1][0]));
                    const south = Math.min(Number(bounds[0][1]), Number(bounds[1][1]));
                    const north = Math.max(Number(bounds[0][1]), Number(bounds[1][1]));

                    if (![west, east, south, north].every(Number.isFinite)) {
                        return true;
                    }

                    return (longitude >= west && longitude <= east && latitude >= south && latitude <= north);
                }

                /*
                |--------------------------------------------------------------------------
                | Clinic list
                |--------------------------------------------------------------------------
                */

                function renderClinics() {
                    clinicResults.innerHTML = '';
                    clinicCount.textContent = String(clinics.length);

                    if (!clinics.length) {
                        showClinicState('bi-search', 'Медицинские организации не найдены в OpenStreetMap.');
                        return;
                    }

                    clinics.forEach(function (place) {
                        const button = document.createElement('button');

                        button.type = 'button';
                        button.className = 'referral-external-clinic-item';

                        if (selectedClinic && sameClinic(selectedClinic, place)) {
                            button.classList.add('is-selected');
                        }

                        const meta = [formatRussianPhone(place.phone), formatRussianCategories(place.categories)].filter(Boolean).join(' · ');

                        button.innerHTML = `
                                <span class="referral-external-clinic-item__icon">
                                    <i class="bi bi-hospital"></i>
                                </span>

                                <span class="referral-external-clinic-item__content">

                                    <span class="referral-external-clinic-item__name">
                                        ${esc(place.name)}
                                    </span>

                                    ${place.address
                            ? `
                                                <span class="referral-external-clinic-item__address">
                                                    ${esc(formatRussianAddress(place.address))}
                                                </span>
                                              `
                            : ''}

                                    ${meta
                            ? `
                                                <span class="referral-external-clinic-item__meta">
                                                    ${esc(meta)}
                                                </span>
                                              `
                            : ''}

                                </span>
                            `;

                        button.addEventListener('click', function () {
                            chooseClinic(place);
                        });

                        clinicResults.appendChild(button);
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Map markers
                |--------------------------------------------------------------------------
                */

                function clearMarkers() {
                    if (!map) {
                        markers = [];
                        return;
                    }

                    markers.forEach(function (item) {
                        try {
                            map.removeChild(item.entity);
                        } catch (error) {
                            // Marker already removed.
                        }
                    });

                    markers = [];
                }

                function renderMarkers() {
                    if (!map || !YMapMarker) {
                        return;
                    }

                    clearMarkers();

                    clinics.forEach(function (place) {
                        if (!Array.isArray(place.coords) || place.coords.length !== 2) {
                            return;
                        }

                        const element = document.createElement('button');

                        element.type = 'button';
                        element.className = 'referral-yandex-marker';
                        element.title = place.name;
                        element.innerHTML = '<i class="bi bi-hospital-fill"></i>';

                        element.addEventListener('click', function (event) {
                            event.preventDefault();
                            chooseClinic(place);
                        });

                        const entity = new YMapMarker({ coordinates: place.coords }, element);

                        map.addChild(entity);
                        markers.push({ place, entity, element });
                    });

                    updateMarkerSelection();
                }

                function updateMarkerSelection() {
                    markers.forEach(function (item) {
                        item.element.classList.toggle('is-selected', Boolean(selectedClinic && sameClinic(item.place, selectedClinic)));
                    });
                }

                function fitClinics() {
                    const items = clinics.filter(function (place) {
                        return (Array.isArray(place.coords) && place.coords.length === 2);
                    });

                    if (!map || !items.length) {
                        return;
                    }

                    if (items.length === 1) {
                        map.setLocation({ center: items[0].coords, zoom: 15, duration: 250 });
                        return;
                    }

                    const x = items.map(function (place) {
                        return place.coords[0];
                    });
                    const y = items.map(function (place) {
                        return place.coords[1];
                    });

                    map.setLocation({
                        bounds: [[Math.min(...x), Math.min(...y)], [Math.max(...x), Math.max(...y)]],
                        duration: 280
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Selected clinic
                |--------------------------------------------------------------------------
                */

                async function chooseClinic(place) {
                    selectedClinic = place;
                    selectedClinicName.textContent = place.name || 'Медицинская организация';

                    /*
                     * Если addr:* отсутствует в самом OSM-объекте,
                     * попробуем определить адрес по координатам.
                     */

                    selectedClinicAddress.textContent = place.address || 'Определяем адрес...';

                    const selectedClinicId = String(place.id || '');
                    const resolvedAddress = await ensureClinicAddress(place);

                    /*
                     * Пока reverse-geocoding выполнялся,
                     * пользователь мог выбрать уже другую клинику.
                     */

                    if (selectedClinic && String(selectedClinic.id || '') === selectedClinicId) {
                        selectedClinicAddress.textContent =
                            resolvedAddress ? formatRussianAddress(resolvedAddress) : 'Адрес не определён';
                    }

                    selectedClinicMeta.textContent =
                        [
                            formatRussianPhone(place.phone),
                            formatRussianCategories(place.categories),
                            formatOpeningHoursRussian(place.hours)
                        ]
                            .filter(Boolean)
                            .join(' · ');

                    if (place.url) {
                        selectedClinicSite.href = place.url;
                        selectedClinicSite.classList.remove('d-none');
                    } else {
                        selectedClinicSite?.classList.add('d-none');
                    }

                    selectedClinicCard.classList.remove('d-none');
                    syncingOrganization = true;
                    organizationInput.value = place.name || '';
                    organizationInput.dispatchEvent(new Event('input', { bubbles: true }));
                    syncingOrganization = false;

                    if (map && Array.isArray(place.coords)) {

                        /*
                         * Это программное перемещение карты после выбора,
                         * а не запрос пользователя на новую область.
                         */

                        blockViewportSearch(1000);
                        map.setLocation({ center: place.coords, zoom: 16, duration: 240 });
                    }

                    /*
                     * Если адрес был получен через reverse geocoding,
                     * обновляем и карточку организации в списке.
                     */

                    renderClinics();
                    updateMarkerSelection();

                    if (!specialityInput?.value?.trim()) {
                        renderSpecialities('');
                    }

                    updateSpecialityVerification();
                }

                selectedClinicClear?.addEventListener('click', function () {
                    clearSelectedClinic();
                });

                function clearSelectedClinic(options = {}) {
                    selectedClinic = null;
                    selectedClinicCard?.classList.add('d-none');

                    if (!options.keepOrganization) {
                        syncingOrganization = true;
                        organizationInput.value = '';
                        organizationInput.dispatchEvent(new Event('input', { bubbles: true }));
                        syncingOrganization = false;
                    }

                    renderClinics();
                    updateMarkerSelection();
                    updateSpecialityVerification();
                }

                organizationInput?.addEventListener('input', function () {
                    if (syncingOrganization) {
                        return;
                    }

                    if (selectedClinic && norm(organizationInput.value) !== norm(selectedClinic.name)) {
                        clearSelectedClinic({ keepOrganization: true });
                    }
                });

                /*
                |--------------------------------------------------------------------------
                | Specialty autocomplete
                |--------------------------------------------------------------------------
                */

                specialityInput?.addEventListener('focus', function () {
                    renderSpecialities(specialityInput.value.trim());
                });

                specialityInput?.addEventListener('input', function () {
                    specialityInput.dataset.osmSpeciality = '';
                    updateSpecialityClear();
                    renderSpecialities(specialityInput.value.trim());
                    updateSpecialityVerification();
                });

                specialityInput?.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeSpecialities();
                        return;
                    }

                    if (!specialityResults?.classList.contains('is-open')) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        moveSpeciality(1);
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        moveSpeciality(-1);
                        return;
                    }

                    if (event.key === 'Enter' && specialityMatches.length) {
                        event.preventDefault();
                        chooseSpeciality(specialityMatches[specialityActiveIndex >= 0 ? specialityActiveIndex : 0]);
                    }
                });

                specialityClear?.addEventListener('click', function () {
                    specialityInput.value = '';
                    specialityInput.dataset.osmSpeciality = '';
                    specialityInput.dispatchEvent(new Event('input', { bubbles: true }));
                    specialityInput.focus();
                });

                function renderSpecialities(query) {
                    const q = norm(query);

                    specialityMatches = EXTERNAL_SPECIALITIES.map(function (item) {
                        const haystack = norm(item.label + ' ' + item.aliases + ' ' + item.osm);
                        let score = 50;

                        if (q) {
                            if (norm(item.label) === q) {
                                score = 0;
                            } else if (norm(item.label).startsWith(q)) {
                                score = 10;
                            } else if (haystack.includes(q)) {
                                score = 20;
                            } else {
                                return null;
                            }
                        }

                        const clinicSpeciality = norm(selectedClinic?.speciality || '');

                        if (clinicSpeciality &&
                            item.osm.split(';').some(function (value) {
                                return clinicSpeciality.includes(norm(value));
                            })) {
                            score -= 12;
                        }

                        return { ...item, score };
                    })
                        .filter(Boolean)
                        .sort(function (a, b) {
                            return (a.score - b.score || a.label.localeCompare(b.label, 'ru'));
                        })
                        .slice(0, 12);

                    specialityActiveIndex = -1;
                    specialityResults.innerHTML = '';

                    if (!specialityMatches.length) {
                        specialityResults.innerHTML = `
                            <div class="referral-external-speciality-state">
                                Специальность не найдена в подсказках.
                                Можно оставить введённое значение вручную.
                            </div>
                        `;

                        specialityResults.classList.add('is-open');
                        return;
                    }

                    specialityMatches.forEach(function (item, index) {
                        const button = document.createElement('button');

                        button.type = 'button';
                        button.className = 'referral-external-speciality-item';

                        const confirmed = specialityIsConfirmedForClinic(item);

                        button.innerHTML = `
                            <span class="referral-external-speciality-item__icon">
                                <i class="bi bi-person-heart"></i>
                            </span>

                            <span class="referral-external-speciality-item__content">
                                <span class="referral-external-speciality-item__name">
                                    ${esc(item.label)}
                                </span>
                                <span class="referral-external-speciality-item__meta">
                                    ${esc(item.aliases)}
                                </span>
                            </span>

                            ${confirmed
                            ? `
                                        <span class="referral-speciality-confirmed-badge">
                                            <i class="bi bi-check-lg"></i>
                                            указана
                                        </span>
                                      `
                            : '<i class="bi bi-chevron-right"></i>'}
                        `;

                        button.addEventListener('mousedown', function (event) {
                            event.preventDefault();
                        });

                        button.addEventListener('click', function () {
                            chooseSpeciality(item);
                        });

                        specialityResults.appendChild(button);
                    });

                    specialityResults.classList.add('is-open');
                }

                function chooseSpeciality(item) {
                    specialityInput.value = item.label;
                    specialityInput.dataset.osmSpeciality = item.osm || '';

                    /*
                     * Основной скрипт формы слушает input и переносит
                     * значение в hidden #referralSpeciality.
                     */

                    specialityInput.dispatchEvent(new Event('input', { bubbles: true }));
                    specialityInput.dataset.osmSpeciality = item.osm || '';
                    closeSpecialities();
                    updateSpecialityClear();
                    updateSpecialityVerification();
                }

                function moveSpeciality(direction) {
                    if (!specialityMatches.length) {
                        return;
                    }

                    specialityActiveIndex =
                        (specialityActiveIndex + direction + specialityMatches.length) % specialityMatches.length;

                    specialityResults.querySelectorAll('.referral-external-speciality-item').forEach(function (element, index) {
                        element.classList.toggle('is-active', index === specialityActiveIndex);
                    });
                }

                function getClinicSpecialityTokens() {
                    const raw = String(selectedClinic?.speciality || '').trim();

                    if (!raw) {
                        return [];
                    }

                    return raw.split(/[;,|]+/).map(function (value) {
                        return norm(value);
                    })
                        .filter(Boolean);
                }

                function getCatalogOsmTokens(item) {
                    return String(item?.osm || '').split(/[;,|]+/).map(function (value) {
                        return norm(value);
                    })
                        .filter(Boolean);
                }

                function specialityIsConfirmedForClinic(item) {
                    const clinicTokens = getClinicSpecialityTokens();

                    if (!clinicTokens.length) {
                        return false;
                    }

                    return getCatalogOsmTokens(item).some(function (token) {
                        return clinicTokens.includes(token);
                    });
                }

                function resolveSelectedSpeciality() {
                    const value = specialityInput?.value?.trim() || '';

                    if (!value) {
                        return null;
                    }

                    const dataOsm = specialityInput?.dataset?.osmSpeciality || '';

                    if (dataOsm) {
                        return {
                            label: value,
                            tokens: String(dataOsm).split(/[;,|]+/).map(function (token) {
                                return norm(token);
                            })
                                .filter(Boolean),
                            manual: false
                        };
                    }

                    const normalized = norm(value);
                    const item = EXTERNAL_SPECIALITIES.find(function (candidate) {
                        if (norm(candidate.label) === normalized) {
                            return true;
                        }

                        return String(candidate.aliases || '').split(/[,;]+/).some(function (alias) {
                            return (norm(alias) === normalized);
                        });
                    });

                    if (!item) {
                        return { label: value, tokens: [], manual: true };
                    }

                    return { label: item.label, tokens: getCatalogOsmTokens(item), manual: false };
                }

                function updateSpecialityVerification() {
                    if (!specialityVerification || !specialityVerificationTitle || !specialityVerificationText) {
                        return;
                    }

                    specialityVerification.classList.remove('is-confirmed', 'is-unknown', 'is-warning', 'is-manual');

                    const selected = resolveSelectedSpeciality();

                    if (!selectedClinic || !selected) {
                        specialityVerification.classList.add('d-none');
                        return;
                    }

                    specialityVerification.classList.remove('d-none');

                    const icon = specialityVerification.querySelector('i');
                    const clinicTokens = getClinicSpecialityTokens();

                    if (!clinicTokens.length) {
                        specialityVerification.classList.add('is-unknown');

                        if (icon) {
                            icon.className = 'bi bi-info-circle';
                        }

                        specialityVerificationTitle.textContent = 'У организации не заполнен перечень специальностей';

                        specialityVerificationText.textContent =
                            'OpenStreetMap не содержит данных о специальностях «' + (selectedClinic.name || 'этой организации')
                            + '». Это не мешает оформить направление.';

                        return;
                    }

                    if (selected.manual || !selected.tokens.length) {
                        specialityVerification.classList.add('is-manual');

                        if (icon) {
                            icon.className = 'bi bi-pencil-square';
                        }

                        specialityVerificationTitle.textContent = 'Специальность введена вручную';

                        specialityVerificationText.textContent =
                            'Автоматически сопоставить «' + selected.label + '» с кодами специальностей OpenStreetMap не удалось. '
                            + 'Направление можно продолжить оформлять.';

                        return;
                    }

                    const matched = selected.tokens.some(function (token) {
                        return clinicTokens.includes(token);
                    });

                    if (matched) {
                        specialityVerification.classList.add('is-confirmed');

                        if (icon) {
                            icon.className = 'bi bi-check-lg';
                        }

                        specialityVerificationTitle.textContent = 'Специальность указана у этой организации';

                        specialityVerificationText.textContent =
                            'В данных OpenStreetMap у «' + (selectedClinic.name || 'выбранной организации')
                            + '» отмечена специальность, соответствующая «'
                            + selected.label
                            + '».';

                        return;
                    }

                    specialityVerification.classList.add('is-warning');

                    if (icon) {
                        icon.className = 'bi bi-exclamation-triangle';
                    }

                    const clinicLabels = String(selectedClinic?.speciality_label || selectedClinic?.speciality || '').trim();

                    specialityVerificationTitle.textContent = 'Выбранная специальность не указана в открытых данных';

                    specialityVerificationText.textContent =
                        (clinicLabels ? ('Для этой организации в OpenStreetMap указаны: ' + clinicLabels + '. ') : '') + '«'
                        + selected.label
                        + '» среди них не отмечена. '
                        + 'Это не означает, что такого специалиста нет — '
                        + 'направление можно отправить.';
                }

                function closeSpecialities() {
                    specialityResults?.classList.remove('is-open');

                    if (specialityResults) {
                        specialityResults.innerHTML = '';
                    }

                    specialityMatches = [];
                    specialityActiveIndex = -1;
                }

                function updateSpecialityClear() {
                    specialityClear?.classList.toggle('is-visible', Boolean(specialityInput?.value));
                }

                /*
                |--------------------------------------------------------------------------
                | Reverse geocoding
                |--------------------------------------------------------------------------
                |
                | Используется ТОЛЬКО когда у выбранного OSM-объекта нет
                | адреса. Для списка всех маркеров reverse-geocoding
                | намеренно не запускаем.
                |
                */

                async function ensureClinicAddress(place) {
                    if (place?.address) {
                        return place.address;
                    }

                    if (!Array.isArray(place?.coords) || place.coords.length !== 2) {
                        return '';
                    }

                    const longitude = Number(place.coords[0]);
                    const latitude = Number(place.coords[1]);

                    if (!Number.isFinite(longitude) || !Number.isFinite(latitude)) {
                        return '';
                    }

                    const cacheKey = longitude.toFixed(6) + ',' + latitude.toFixed(6);

                    if (reverseAddressCache.has(cacheKey)) {
                        const cached = reverseAddressCache.get(cacheKey) || '';

                        if (cached) {
                            place.address = cached;
                        }

                        return cached;
                    }

                    try {
                        const url = new URL(OSM_REVERSE_URL, window.location.origin);

                        url.searchParams.set('lon', longitude);
                        url.searchParams.set('lat', latitude);

                        const response = await fetch(url.toString(), {
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const payload = await response.json().catch(function () {
                            return {};
                        });

                        if (!response.ok) {
                            throw new Error(payload.message || ('HTTP ' + response.status));
                        }

                        const address = String(payload.address || payload.display_name || '').trim();

                        reverseAddressCache.set(cacheKey, address);

                        if (address) {
                            place.address = address;
                        }

                        return address;
                    } catch (error) {
                        console.warn('[OSM reverse geocoding]', error);

                        /*
                         * Кэшируем и неудачу на время жизни страницы,
                         * чтобы повторный клик не создавал серию запросов.
                         */

                        reverseAddressCache.set(cacheKey, '');
                        return '';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | UI helpers
                |--------------------------------------------------------------------------
                */

                function showClinicState(icon, value) {
                    clinicResults.innerHTML = `
                        <div class="referral-external-list-state">

                            <i class="bi ${esc(icon)}"></i>

                            ${esc(value)}

                        </div>
                    `;
                }

                function showMapState(icon, title, value) {
                    mapState.innerHTML = `
                        <div class="referral-external-map-state__inner">

                            <div class="referral-external-map-state__icon">
                                <i class="bi ${esc(icon)}"></i>
                            </div>

                            <strong>
                                ${esc(title)}
                            </strong>

                            <span>
                                ${esc(value)}
                            </span>

                        </div>
                    `;

                    mapState.classList.remove('d-none');
                }

                function hideMapState() {
                    mapState.classList.add('d-none');
                }

                function updateClinicClear() {
                    clinicSearchClear?.classList.toggle('is-visible', Boolean(clinicSearchInput?.value));
                }

                function sameClinic(first, second) {
                    if (!first || !second) {
                        return false;
                    }

                    if (first.id && second.id && String(first.id) === String(second.id)) {
                        return true;
                    }

                    return (norm(first.name) === norm(second.name) && norm(first.address) === norm(second.address));
                }

                /*
                |--------------------------------------------------------------------------
                | Русское отображение данных организации
                |--------------------------------------------------------------------------
                */

                function formatRussianPhone(value) {
                    let phone = String(value || '').trim();

                    if (!phone) {
                        return '';
                    }

                    /*
                     * В OSM иногда несколько номеров:
                     * +7...;+7...
                     */

                    const parts = phone.split(/\s*;\s*/).filter(Boolean);

                    return parts.map(function (item) {
                        const extensionMatch = item.match(/(?:ext\.?|доб\.?)\s*(\d+)/i);
                        const extension = extensionMatch ? extensionMatch[1] : '';
                        let digits = item.replace(/(?:ext\.?|доб\.?)\s*\d+/ig, '').replace(/\D/g, '');

                        /*
                         * 8XXXXXXXXXX -> 7XXXXXXXXXX
                         */

                        if (digits.length === 11 && digits.startsWith('8')) {
                            digits = '7' + digits.slice(1);
                        }

                        if (digits.length === 11 && digits.startsWith('7')) {
                            const formatted = '+7 (' + digits.slice(1, 4) + ') ' + digits.slice(4, 7) + '-' + digits.slice(7, 9) + '-'
                                + digits.slice(9, 11);

                            return extension ? (formatted + ' доб. ' + extension) : formatted;
                        }

                        /*
                         * Не российский или нестандартный номер:
                         * исходную строку не портим.
                         */

                        return item;
                    })
                        .join(', ');
                }

                function formatRussianCategories(value) {
                    return String(value || '').split(/\s*,\s*/).filter(Boolean).map(function (category) {

                        /*
                         * В русском тексте после запятой
                         * второе слово обычно со строчной буквы.
                         */

                        return category.charAt(0).toLocaleLowerCase('ru') + category.slice(1);
                    })
                        .join(', ')
                        .replace(/^./u, function (letter) {
                            return letter.toLocaleUpperCase('ru');
                        });
                }

                function formatOpeningHoursRussian(value) {
                    let hours = String(value || '').trim();

                    if (!hours) {
                        return '';
                    }

                    /*
                     * Частые специальные значения opening_hours.
                     */

                    const specialValues = { '24/7': 'Круглосуточно', 'off': 'Закрыто', 'closed': 'Закрыто' };

                    if (specialValues[hours.toLowerCase()]) {
                        return specialValues[hours.toLowerCase()];
                    }

                    /*
                     * Названия дней OSM:
                     * Mo Tu We Th Fr Sa Su
                     */

                    const days = { Mo: 'Пн', Tu: 'Вт', We: 'Ср', Th: 'Чт', Fr: 'Пт', Sa: 'Сб', Su: 'Вс', PH: 'праздничные дни' };

                    /*
                     * Сначала переводим интервалы:
                     * Mo-Fr -> Пн–Пт
                     */

                    hours =
                        hours.replace(/\b(Mo|Tu|We|Th|Fr|Sa|Su)-(Mo|Tu|We|Th|Fr|Sa|Su)\b/g, function (_match, from, to) {
                            return (days[from] + '–' + days[to]);
                        });

                    /*
                     * Затем одиночные дни.
                     */

                    hours =
                        hours.replace(/\b(Mo|Tu|We|Th|Fr|Sa|Su|PH)\b/g, function (day) {
                            return (days[day] || day);
                        });

                    /*
                     * OSM использует запятые для нескольких дней:
                     * Mo,We,Fr -> Пн, Ср, Пт
                     */

                    hours = hours.replace(/,(?=[А-ЯЁ])/g, ', ');

                    /*
                     * Типографский диапазон времени:
                     * 08:00-21:00 -> 08:00–21:00
                     */

                    hours = hours.replace(/(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/g, '$1–$2');

                    /*
                     * Секции:
                     * Пн–Пт 08:00–21:00;Сб ... ->
                     * Пн–Пт 08:00–21:00; Сб ...
                     */

                    hours = hours.replace(/;\s*/g, '; ').replace(/\s+/g, ' ').trim();
                    return hours;
                }

                function formatRussianAddress(value) {
                    let address = String(value || '').trim();

                    if (!address) {
                        return '';
                    }

                    /*
                     * Не пытаемся полноценно парсить почтовый адрес.
                     * Только делаем хорошо читаемые русские сокращения.
                     */

                    address =
                        address.replace(/\bулица\s+/giu, 'ул. ').replace(/\bпроспект\s+/giu, 'пр-т ').replace(/\bпереулок\s+/giu, 'пер. ')
                            .replace(/\bпроезд\s+/giu, 'пр-д ')
                            .replace(/\bшоссе\s+/giu, 'ш. ')
                            .replace(/\bплощадь\s+/giu, 'пл. ');

                    /*
                     * "Сибирская улица" -> "ул. Сибирская"
                     * Только когда это отдельный адресный компонент.
                     */

                    address =
                        address.replace(/(?:^|,\s*)([\p{L}\- ]+?)\s+улица(?=,|$)/giu, function (match, street) {
                            const prefix = match.startsWith(',') ? ', ' : '';

                            return (prefix + 'ул. ' + street.trim());
                        });

                    /*
                     * "Томск, ..." -> "г. Томск, ..."
                     * Не добавляем "г.", если оно уже есть.
                     */

                    if (/^Томск(?:,|$)/iu.test(address)) {
                        address = 'г. ' + address;
                    }

                    return address;
                }

                function norm(value) {
                    return String(value || '').trim().toLocaleLowerCase('ru').replace(/\s+/g, ' ');
                }

                function esc(value) {
                    const div = document.createElement('div');

                    div.textContent = value ?? '';
                    return div.innerHTML;
                }

                updateClinicClear();
                updateSpecialityClear();
                updateSpecialityVerification();
            });
        </script>

    @endpush

@endonce
