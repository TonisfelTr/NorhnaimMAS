@extends('doctors.settings')

@section('settings-title', 'Участки')

@section('assets')
    @parent

    <style>
        .district-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .district-toolbar__search {
            position: relative;
            width: min(520px, 100%);
        }

        .district-toolbar__search i {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .district-toolbar__search .settings-control {
            padding-left: 39px;
        }

        .district-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .district-summary-card {
            position: relative;
            overflow: hidden;
            min-height: 105px;
            padding: 18px 19px;
            border: 1px solid var(--settings-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .045);
        }

        .district-summary-card::before {
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, var(--settings-primary), var(--settings-primary-light));
            content: '';
        }

        .district-summary-card--warning::before {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .district-summary-card--neutral::before {
            background: #94a3b8;
        }

        .district-summary-card__label {
            color: var(--settings-muted);
            font-size: 12px;
            font-weight: 760;
        }

        .district-summary-card__value {
            margin-top: 7px;
            color: var(--settings-text);
            font-size: 29px;
            font-weight: 850;
            line-height: 1;
        }

        .district-summary-card__hint {
            margin-top: 6px;
            color: #8b95a1;
            font-size: 11px;
        }

        .district-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .district-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--settings-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 14px 38px rgba(15, 23, 42, .05);
        }


        .district-card--transferred {
            border-color: #d6dbe1;
            background: #f3f4f6;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .035);
        }

        .district-card--transferred .district-card__identity,
        .district-card--transferred .district-card__body {
            filter: grayscale(1);
            opacity: .48;
        }

        .district-card--transferred .district-card__body {
            pointer-events: none;
            user-select: none;
        }

        .district-card--transferred .district-card__header,
        .district-card--transferred .district-card__footer {
            background: #f7f8f9;
        }

        .district-card--transferred .district-card__footer {
            border-top-color: #dfe3e8;
        }

        .district-status--merged {
            background: #e9edf1;
            color: #586575;
        }

        .district-status--incoming {
            background: #e9f8f5;
            color: #0f766e;
        }

        .district-action--separate {
            border-color: #8bd6db;
            background: #edfafa;
            color: #087d88;
            opacity: 1;
        }

        .district-action--separate:hover {
            border-color: #48bfc9;
            background: #def7f8;
            color: #066873;
        }

        .district-transfer--incoming {
            border-color: #ccebe6;
            background: #f3fbf9;
        }

        .district-transfer--incoming .district-transfer__title,
        .district-transfer--incoming .district-transfer__hint {
            color: #0f766e;
        }

        .district-transfer--incoming .district-transfer__doctor {
            color: #115e59;
        }

        .district-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 19px 16px;
            border-bottom: 1px solid #edf1f5;
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .district-card__identity {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
        }

        .district-card__icon {
            display: grid;
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            place-items: center;
            border-radius: 15px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 20px;
        }

        .district-card__name {
            margin: 0;
            color: var(--settings-text);
            font-size: 18px;
            font-weight: 830;
        }

        .district-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 9px 12px;
            margin-top: 5px;
            color: var(--settings-muted);
            font-size: 12px;
        }

        .district-card__meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .district-status {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #eaf8ef;
            color: #15803d;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .district-status--transfer {
            background: #fff7e6;
            color: #b45309;
        }

        .district-card__body {
            padding: 18px 19px;
        }

        .district-section + .district-section {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #edf1f5;
        }

        .district-section__title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 11px;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .045em;
            text-transform: uppercase;
        }

        .district-doctor {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border: 1px solid #e5ebf0;
            border-radius: 15px;
            background: #fbfcfd;
        }

        .district-doctor__avatar {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--settings-primary), var(--settings-primary-light));
            color: #fff;
            font-size: 12px;
            font-weight: 850;
        }

        .district-doctor__role {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .district-doctor__name {
            margin-top: 2px;
            color: var(--settings-text);
            font-size: 13px;
            font-weight: 780;
        }

        .district-doctor__speciality {
            margin-top: 2px;
            color: var(--settings-muted);
            font-size: 11px;
        }

        .district-doctor--home {
            margin-top: 9px;
            border-color: #d8eef0;
            background: #f5fcfd;
        }

        .district-doctor--home .district-doctor__avatar {
            background: #eef8fa;
            color: var(--settings-primary-dark);
            border: 1px solid #d4ecef;
        }

        .district-doctor-vacation {
            min-width: 210px;
            margin-left: auto;
            padding: 9px 11px;
            border: 1px solid #f0d7a5;
            border-radius: 11px;
            background: #fff9ec;
        }

        .district-doctor-vacation.is-active {
            border-color: #fecaca;
            background: #fff4f4;
        }

        .district-doctor-vacation.is-planned {
            border-color: #d9e4ea;
            background: #f7fafc;
        }

        .district-doctor-vacation__status {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #9a5d09;
            font-size: 10px;
            font-weight: 850;
        }

        .district-doctor-vacation.is-active .district-doctor-vacation__status {
            color: #b42318;
        }

        .district-doctor-vacation.is-planned .district-doctor-vacation__status {
            color: #64748b;
        }

        .district-doctor-vacation__dates {
            margin-top: 3px;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
        }

        .district-doctor-vacation__replacement {
            margin-top: 5px;
            color: #334155;
            font-size: 10px;
            line-height: 1.35;
        }

        .district-doctor-vacation__replacement strong {
            color: var(--settings-primary-dark);
            font-weight: 850;
        }

        .district-doctor-vacation__replacement.is-missing strong {
            color: #b42318;
        }

        @media (max-width: 720px) {
            .district-doctor {
                flex-wrap: wrap;
            }

            .district-doctor-vacation {
                width: 100%;
                margin-left: 52px;
            }
        }

        .district-picker-hint {
            margin-top: 5px;
            color: #8a98a9;
            font-size: 10px;
            line-height: 1.45;
        }

        .district-form-errors {
            display: none;
            margin-bottom: 14px;
            padding: 11px 13px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fff7f7;
            color: #b91c1c;
            font-size: 11px;
            line-height: 1.5;
        }

        .district-form-errors.is-visible {
            display: block;
        }

        .district-transfer {
            margin-top: 10px;
            padding: 13px 14px;
            border: 1px solid #f4d9a9;
            border-radius: 15px;
            background: #fffaf0;
        }

        .district-transfer__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .district-transfer__title {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #a16207;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .district-transfer__dates {
            color: #92400e;
            font-size: 11px;
            font-weight: 760;
            white-space: nowrap;
        }

        .district-transfer__doctor {
            margin-top: 7px;
            color: #713f12;
            font-size: 13px;
            font-weight: 760;
        }

        .district-transfer__hint {
            margin-top: 4px;
            color: #a16207;
            font-size: 11px;
        }

        /* Входящая временная передача — отдельное спокойное состояние, не warning */
        .district-transfer.district-transfer--incoming {
            position: relative;
            overflow: hidden;
            border: 1px solid #cde8eb;
            background: linear-gradient(135deg, #f7fcfd 0%, #eef9fa 100%);
            box-shadow: 0 7px 20px rgba(8, 145, 159, .055);
        }

        .district-transfer.district-transfer--incoming::before {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 3px;
            background: linear-gradient(180deg, #08919f, #43bdc7);
            content: '';
        }

        .district-transfer.district-transfer--incoming .district-transfer__title {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border: 1px solid #ccebed;
            border-radius: 999px;
            background: #e8f8fa;
            color: #087985;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .025em;
            line-height: 1;
            text-transform: uppercase;
        }

        .district-transfer.district-transfer--incoming .district-transfer__title i {
            display: grid;
            width: 17px;
            height: 17px;
            place-items: center;
            border-radius: 50%;
            background: #d6f1f4;
            color: #087985;
            font-size: 10px;
        }

        .district-transfer.district-transfer--incoming .district-transfer__dates {
            display: inline-flex;
            align-items: center;
            padding: 5px 8px;
            border-radius: 999px;
            background: #edf4f8;
            color: #557189;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .district-transfer.district-transfer--incoming .district-transfer__doctor {
            margin-top: 9px;
            color: #203445;
            font-size: 13px;
            font-weight: 820;
        }

        .district-transfer.district-transfer--incoming .district-transfer__hint {
            margin-top: 5px;
            color: #70879a;
            font-size: 11px;
            line-height: 1.45;
        }

        .district-address-list {
            display: grid;
            gap: 8px;
        }

        .district-address {
            display: flex;
            min-width: 0;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 11px;
            border: 1px solid #e7edf1;
            border-radius: 13px;
            background: #fff;
        }

        .district-address__icon {
            display: grid;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            place-items: center;
            border-radius: 10px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 13px;
        }

        .district-address__main {
            min-width: 0;
        }

        .district-address__text {
            color: var(--settings-text);
            font-size: 12px;
            font-weight: 730;
            line-height: 1.4;
        }

        .district-address__type {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 10px;
        }

        .district-address-more {
            display: inline-flex;
            margin-top: 9px;
            padding: 0;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: var(--settings-primary-dark);
            font-family: inherit;
            font-size: 11px;
            font-weight: 760;
            line-height: inherit;
            text-align: left;
            cursor: pointer;
        }

        .district-address-more:hover {
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .district-address-more:focus-visible {
            outline: 2px solid var(--settings-primary);
            outline-offset: 4px;
        }

        .district-addresses-modal__subtitle {
            margin-top: 5px;
            color: var(--settings-muted);
            font-size: 12px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .district-addresses-modal__note {
            margin: 0 0 15px;
            color: var(--settings-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .district-addresses-modal .district-address__text,
        .district-addresses-modal .district-address__type {
            overflow-wrap: anywhere;
        }

        .district-addresses-empty {
            padding: 25px 16px;
            border: 1px dashed #dbe3e9;
            border-radius: 15px;
            background: #fbfcfd;
            text-align: center;
        }

        .district-addresses-empty > i {
            display: inline-block;
            margin-bottom: 10px;
            color: var(--settings-primary-dark);
            font-size: 26px;
        }

        .district-addresses-empty h3 {
            margin: 0 0 8px;
            color: var(--settings-text);
            font-size: 15px;
            font-weight: 780;
        }

        .district-addresses-empty p {
            max-width: 520px;
            margin: 0 auto;
            color: var(--settings-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .district-card__footer {
            display: flex;
            margin-top: auto;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 19px 17px;
            border-top: 1px solid #edf1f5;
            background: #fbfcfd;
        }

        .district-card__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .district-action {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 7px 11px;
            border: 1px solid #dbe3e9;
            border-radius: 11px;
            background: #fff;
            color: #475569;
            font-size: 11px;
            font-weight: 760;
            text-decoration: none;
            cursor: pointer;
            transition: .16s ease;
        }

        .district-action:hover {
            border-color: #c6d2db;
            background: #f8fafc;
            color: #111827;
        }

        .district-action--primary {
            border-color: #cfe8eb;
            background: #f0fafb;
            color: var(--settings-primary-dark);
        }

        .district-action--warning {
            border-color: #f1d49b;
            background: #fffaf0;
            color: #a16207;
        }

        .district-action--danger {
            border-color: #fecaca;
            background: #fff7f7;
            color: #b42318;
        }

        .district-action--danger:hover {
            border-color: #fca5a5;
            background: #fff0f0;
            color: #991b1b;
        }

        .district-additional-doctors {
            display: grid;
            gap: 7px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #dfe7ec;
        }

        .district-additional-doctor {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 11px;
            border: 1px solid #e2ebf0;
            border-radius: 13px;
            background: #f8fbfc;
        }

        .district-additional-doctor__avatar {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 10px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 10px;
            font-weight: 850;
        }

        .district-additional-doctor__role {
            color: #8b9aaf;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .district-additional-doctor__name {
            margin-top: 2px;
            color: var(--settings-text);
            font-size: 12px;
            font-weight: 760;
        }

        .district-additional-doctor__meta {
            margin-top: 2px;
            color: var(--settings-muted);
            font-size: 10px;
        }

        .district-delete-summary {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px solid #e3eaf0;
            border-radius: 15px;
            background: #fbfcfd;
        }

        .district-delete-summary__row {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .district-delete-summary__label {
            color: #8b98aa;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .district-delete-summary__value {
            color: var(--settings-text);
            font-size: 12px;
            font-weight: 740;
        }

        .district-delete-warning {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 12px;
            padding: 12px 13px;
            border: 1px solid #fde2c2;
            border-radius: 14px;
            background: #fffaf4;
            color: #8a5a16;
            font-size: 11px;
            line-height: 1.5;
        }

        .district-delete-warning i {
            margin-top: 1px;
            color: #d97706;
        }

        .district-page-notice {
            position: fixed;
            top: 18px;
            right: 20px;
            z-index: 2000;
            display: none;
            width: min(430px, calc(100vw - 32px));
            padding: 14px 16px;
            border: 1px solid;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .16);
        }

        .district-page-notice.is-visible {
            display: flex;
            align-items: flex-start;
            gap: 11px;
        }

        .district-page-notice--success {
            border-color: #b7e4cf;
            background: #f2fbf7;
            color: #166534;
        }

        .district-page-notice--error {
            border-color: #fecaca;
            background: #fff7f7;
            color: #b42318;
        }

        .district-page-notice__icon {
            font-size: 18px;
            line-height: 1;
        }

        .district-page-notice__title {
            font-size: 12px;
            font-weight: 850;
        }

        .district-page-notice__message {
            margin-top: 2px;
            font-size: 11px;
            line-height: 1.45;
        }

        .district-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 22px 70px rgba(15, 23, 42, .2);
        }

        .district-modal .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--settings-border);
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .district-modal .modal-title {
            color: var(--settings-text);
            font-size: 19px;
            font-weight: 820;
        }

        .district-modal .modal-body {
            padding: 20px;
            background: #fff;
        }

        .district-modal .modal-footer {
            padding: 14px 20px 18px;
            border-top: 1px solid var(--settings-border);
            background: #fbfcfd;
        }

        .district-form-section + .district-form-section {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid #edf1f5;
        }

        .district-form-section__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
        }

        .district-form-section__title {
            margin: 0;
            color: #334155;
            font-size: 13px;
            font-weight: 820;
        }

        .district-address-editor {
            display: grid;
            gap: 10px;
        }

        .district-address-row {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr) 44px;
            gap: 9px;
            align-items: end;
            padding: 12px;
            border: 1px solid #e5ebf0;
            border-radius: 15px;
            background: #fbfcfd;
        }

        .district-address-row__details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .district-remove-address {
            display: inline-flex;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            border: 1px solid #f3c8c8;
            border-radius: 12px;
            background: #fff7f7;
            color: #b91c1c;
            cursor: pointer;
        }

        .district-inline-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 12px;
            padding: 12px 13px;
            border: 1px solid #d9ecef;
            border-radius: 14px;
            background: #f4fbfc;
            color: #46656c;
            font-size: 11px;
            line-height: 1.5;
        }


        /* Улучшенная форма редактирования участка */
        #districtEditModal .modal-dialog {
            max-width: 1080px;
        }

        #districtEditModal .modal-content {
            background: #f8fafc;
        }

        #districtEditModal .modal-header {
            align-items: center;
            padding: 20px 24px;
            background: #fff;
        }

        .district-edit-heading {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 14px;
        }

        .district-edit-heading__icon {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            place-items: center;
            border-radius: 14px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 19px;
        }

        .district-edit-heading__subtitle {
            margin-top: 3px;
            color: var(--settings-muted);
            font-size: 12px;
        }

        #districtEditModal .modal-body {
            padding: 18px 20px 20px;
            background: #f8fafc;
        }

        .district-form-card {
            padding: 18px;
            border: 1px solid #e3e9ef;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .035);
        }

        .district-form-card + .district-form-card {
            margin-top: 14px;
        }

        .district-form-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .district-form-card__title-wrap {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
        }

        .district-form-card__step {
            display: grid;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            place-items: center;
            border-radius: 10px;
            background: #eef8fa;
            color: var(--settings-primary-dark);
            font-size: 12px;
            font-weight: 850;
        }

        .district-form-card__title {
            margin: 0;
            color: #1e293b;
            font-size: 14px;
            font-weight: 850;
        }

        .district-form-card__hint {
            margin-top: 2px;
            color: #94a3b8;
            font-size: 11px;
        }

        #districtEditModal .settings-label {
            margin-bottom: 6px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }

        #districtEditModal .settings-control {
            min-height: 46px;
            border-color: #dce4eb;
            border-radius: 12px;
            background: #fff;
        }

        #districtEditModal .settings-control:focus {
            border-color: #78cbd2;
            box-shadow: 0 0 0 3px rgba(8, 145, 159, .09);
        }

        #districtEditModal .district-address-editor {
            gap: 9px;
        }

        #districtEditModal .district-address-row {
            position: relative;
            grid-template-columns: 146px minmax(0, 1fr) 42px;
            grid-template-areas:
                "rule rule rule"
                "type details remove";
            column-gap: 10px;
            row-gap: 8px;
            align-items: start;
            padding: 13px;
            border: 1px solid #e4eaf0;
            border-radius: 15px;
            background: #fbfcfd;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        #districtEditModal .district-address-row:focus-within {
            border-color: #b9dfe3;
            background: #fff;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .045);
        }

        .district-rule-label {
            display: flex;
            grid-area: rule;
            align-items: center;
            gap: 6px;
            margin: 0;
            color: #64748b;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .035em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .district-rule-label i {
            color: var(--settings-primary-dark);
        }

        #districtEditModal .district-address-type {
            grid-area: type;
            min-width: 0;
        }

        #districtEditModal .district-address-row__details {
            grid-area: details;
        }

        #districtEditModal .district-remove-address {
            grid-area: remove;
        }

        #districtEditModal .district-address-row__details {
            grid-template-columns: minmax(0, 1.35fr) minmax(180px, .65fr);
            align-items: start;
            gap: 10px;
        }

        #districtEditModal .district-address-picker,
        #districtEditModal .district-house-picker {
            align-self: start;
        }

        #districtEditModal .district-remove-address {
            width: 42px;
            height: 46px;
            align-self: start;
            margin-top: 23px;
            border-radius: 12px;
            background: #fff;
            opacity: .78;
        }

        #districtEditModal .district-remove-address:hover {
            border-color: #f0aaaa;
            background: #fff5f5;
            opacity: 1;
        }

        #districtEditModal .modal-footer {
            padding: 14px 20px;
            background: #fff;
        }

        #districtEditModal .modal-footer .settings-button {
            min-height: 42px;
            padding-inline: 16px;
        }

        #addDistrictAddress {
            white-space: nowrap;
        }

        #districtEditModal .select2-container {
            width: 100% !important;
        }

        #districtEditModal .select2-container .select2-selection--single {
            min-height: 46px;
            border: 1px solid #dce4eb;
            border-radius: 12px;
            background: #fff;
        }

        #districtEditModal .select2-container .select2-selection--single .select2-selection__rendered {
            height: 44px;
            padding: 0 38px 0 13px;
            color: #1e293b;
            line-height: 44px;
        }

        #districtEditModal .select2-container .select2-selection--single .select2-selection__arrow {
            width: 36px;
            height: 44px;
        }

        #districtEditModal .select2-container--open .select2-selection--single,
        #districtEditModal .select2-container--focus .select2-selection--single {
            border-color: #78cbd2;
            box-shadow: 0 0 0 3px rgba(8, 145, 159, .09);
        }

        .district-doctor-select2-dropdown {
            z-index: 1065;
            border-color: #dce4eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
        }

        .district-doctor-select2-dropdown .select2-search--dropdown {
            padding: 10px;
            background: #fff;
        }

        .district-doctor-select2-dropdown .select2-search__field {
            min-height: 40px;
            padding: 8px 11px;
            border: 1px solid #dce4eb !important;
            border-radius: 10px;
            outline: 0;
        }

        .district-doctor-select2-dropdown .select2-results__option {
            padding: 9px 12px;
        }

        .district-doctor-picker {
            position: relative;
        }

        .district-doctor-picker__search {
            position: relative;
        }

        .district-doctor-picker__search > i {
            position: absolute;
            top: 50%;
            left: 15px;
            z-index: 2;
            color: #7c91ad;
            font-size: 15px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .district-doctor-picker__input {
            padding-left: 42px !important;
            padding-right: 42px !important;
        }

        .district-doctor-picker__clear {
            position: absolute;
            top: 50%;
            right: 10px;
            z-index: 3;
            display: none;
            width: 30px;
            height: 30px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            background: transparent;
            color: #8ea0b7;
            font-size: 18px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .district-doctor-picker.has-value .district-doctor-picker__clear {
            display: inline-flex;
        }

        .district-doctor-picker__selected {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            color: #147852;
            font-size: 11px;
            font-weight: 760;
        }

        .district-doctor-picker.has-selection .district-doctor-picker__selected {
            display: flex;
        }

        .district-doctor-picker__selected i {
            font-size: 12px;
        }

        .district-doctor-picker__results {
            display: none;
            margin-top: 8px;
            padding: 7px;
            border: 1px solid #d8e3ee;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(15, 23, 42, .08);
        }

        .district-doctor-picker.is-open .district-doctor-picker__results {
            display: block;
        }

        .district-doctor-option {
            display: grid;
            width: 100%;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            align-items: center;
            gap: 11px;
            padding: 10px;
            border: 1px solid transparent;
            border-radius: 12px;
            background: #fff;
            color: inherit;
            text-align: left;
            cursor: pointer;
            transition: .15s ease;
        }

        .district-doctor-option + .district-doctor-option {
            margin-top: 5px;
        }

        .district-doctor-option:hover,
        .district-doctor-option.is-selected {
            border-color: #83d8e1;
            background: #effbfd;
        }

        .district-doctor-option__avatar {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--settings-primary), var(--settings-primary-light));
            color: #fff;
            font-size: 11px;
            font-weight: 850;
        }

        .district-doctor-option__name {
            color: var(--settings-text);
            font-size: 12px;
            font-weight: 790;
            line-height: 1.35;
        }

        .district-doctor-option__meta {
            margin-top: 2px;
            color: #8595aa;
            font-size: 10px;
        }

        .district-doctor-option__badge {
            padding: 4px 7px;
            border-radius: 7px;
            background: #edf2f7;
            color: #61718a;
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
        }

        .district-doctor-picker__empty {
            display: none;
            padding: 13px;
            color: #8b98aa;
            font-size: 11px;
            text-align: center;
        }

        .district-doctor-picker.no-results .district-doctor-picker__empty {
            display: block;
        }

        .transfer-modal .modal-dialog {
            max-width: 860px;
        }

        .transfer-modal__hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            padding: 18px 20px;
            border: 1px solid #d6ecf0;
            border-radius: 18px;
            background: linear-gradient(135deg, #f3fbfc 0%, #fbfeff 100%);
        }

        .transfer-modal__hero-main {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-width: 0;
        }

        .transfer-modal__hero-icon {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            place-items: center;
            border-radius: 14px;
            background: #dff4f6;
            color: var(--settings-primary);
            font-size: 18px;
        }

        .transfer-modal__hero-title {
            margin: 0;
            color: var(--settings-text);
            font-size: 15px;
            font-weight: 850;
        }

        .transfer-modal__hero-subtitle {
            margin-top: 4px;
            color: var(--settings-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .transfer-modal__hero-doctor {
            margin-top: 10px;
            color: var(--settings-text);
            font-size: 12px;
            line-height: 1.45;
        }

        .transfer-modal__hero-doctor strong {
            font-weight: 800;
        }

        .transfer-modal__hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
            padding: 9px 12px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #d7e6ec;
            color: #4d6477;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
        }

        .transfer-modal__section {
            padding: 18px;
            border: 1px solid #e2ebf2;
            border-radius: 18px;
            background: #fff;
        }

        .transfer-modal__section + .transfer-modal__section {
            margin-top: 14px;
        }

        .transfer-modal__section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .transfer-modal__section-title {
            margin: 0;
            color: var(--settings-text);
            font-size: 14px;
            font-weight: 850;
        }

        .transfer-modal__section-text {
            color: var(--settings-muted);
            font-size: 11px;
        }

        .transfer-modal__hint {
            margin-top: 8px;
            color: #7e8ea6;
            font-size: 11px;
            line-height: 1.45;
        }

        .transfer-modal__note {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 11px;
            border-radius: 12px;
            background: #f6fafc;
            color: #708196;
            font-size: 11px;
            line-height: 1.4;
        }

        .transfer-modal__note i {
            color: var(--settings-primary);
        }

        .transfer-modal__footer-note {
            margin-right: auto;
            color: #7e8ea6;
            font-size: 11px;
        }

        #districtTransferModal .district-doctor-picker__results {
            max-height: 252px;
            overflow-y: auto;
        }

        #districtTransferModal .district-doctor-picker__results::-webkit-scrollbar {
            width: 8px;
        }

        #districtTransferModal .district-doctor-picker__results::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: #cfdae6;
        }

        @media (max-width: 767.98px) {
            .transfer-modal__hero {
                grid-template-columns: 1fr;
            }

            .transfer-modal__hero-badge {
                justify-content: center;
            }

            .transfer-modal__footer-note {
                width: 100%;
                margin: 0 0 10px;
            }
        }

        .district-address-picker,
        .district-house-picker {
            position: relative;
            min-width: 0;
        }

        .district-address-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            z-index: 1085;
            display: none;
            max-height: 220px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #d8e3ee;
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
        }

        .district-address-suggestions.is-visible {
            display: block;
        }

        .district-address-suggestion {
            display: block;
            width: 100%;
            padding: 9px 10px;
            border: 1px solid transparent;
            border-radius: 10px;
            background: #fff;
            color: var(--settings-text);
            font-size: 11px;
            font-weight: 720;
            line-height: 1.35;
            text-align: left;
            cursor: pointer;
        }

        .district-address-suggestion:hover {
            border-color: #83d8e1;
            background: #effbfd;
        }

        .district-address-suggestion__meta {
            display: block;
            margin-top: 2px;
            color: #8b9aaf;
            font-size: 9px;
            font-weight: 500;
        }

        .district-house-status {
            min-height: 16px;
            margin-top: 4px;
            font-size: 9px;
            line-height: 1.3;
        }

        .district-house-status.is-valid {
            color: #15803d;
        }

        .district-house-status.is-invalid {
            color: #b91c1c;
        }

        .js-address-detail.is-invalid,
        .js-address-value.is-invalid {
            border-color: #ef9a9a !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .08);
        }

        @media (max-width: 1100px) {
            .district-grid,
            .district-summary {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .district-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .district-toolbar__search {
                width: 100%;
            }

            .district-card__header,
            .district-card__footer {
                align-items: stretch;
                flex-direction: column;
            }

            .district-address-row {
                grid-template-columns: 1fr;
            }

            #districtEditModal .district-address-row {
                grid-template-areas:
                    "rule"
                    "type"
                    "details"
                    "remove";
            }

            .district-address-row__details {
                grid-template-columns: 1fr;
            }

            .district-remove-address {
                width: 100%;
            }

            #districtEditModal .district-remove-address {
                margin-top: 0;
            }
        }
    </style>
@endsection

@section('sub-main')
    <div class="district-page-notice" id="districtPageNotice" role="status" aria-live="polite">
        <i class="bi district-page-notice__icon" id="districtPageNoticeIcon"></i>
        <div>
            <div class="district-page-notice__title" id="districtPageNoticeTitle"></div>
            <div class="district-page-notice__message" id="districtPageNoticeMessage"></div>
        </div>
    </div>

    <main class="settings-page">
        <div class="settings-container">
            <div class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">Участки</h1>
                    <p class="settings-page-description">
                        Распределение территории обслуживания между врачами. Участок может включать отдельные адреса,
                        диапазоны домов, улицы целиком или населённые пункты. Здесь же настраивается временная передача
                        участка другому врачу.
                    </p>
                </div>

                <span class="settings-type-badge">
                    <i class="bi bi-geo-alt"></i>
                    Территории обслуживания
                </span>
            </div>

            <div class="district-summary">
                <article class="district-summary-card">
                    <div class="district-summary-card__label">Всего участков</div>
                    <div class="district-summary-card__value">{{ $districtSummary['total'] }}</div>
                    <div class="district-summary-card__hint">Активные территории клиники</div>
                </article>

                <article class="district-summary-card district-summary-card--neutral">
                    <div class="district-summary-card__label">Закреплено врачей</div>
                    <div class="district-summary-card__value">{{ $districtSummary['doctors'] }}</div>
                    <div class="district-summary-card__hint">Основные врачи участков</div>
                </article>

                <article class="district-summary-card district-summary-card--warning">
                    <div class="district-summary-card__label">Временные передачи</div>
                    <div class="district-summary-card__value">{{ $districtSummary['transfers'] }}</div>
                    <div class="district-summary-card__hint">Действует на текущий период</div>
                </article>
            </div>

            <div class="district-toolbar">
                <div class="district-toolbar__search">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           id="districtSearch"
                           class="settings-control"
                           placeholder="Поиск по названию, врачу или адресу...">
                </div>

                <button type="button"
                        class="settings-button settings-button--primary"
                        data-bs-toggle="modal"
                        data-bs-target="#districtEditModal"
                        data-district-mode="create">
                    <i class="bi bi-plus-lg"></i>
                    Добавить участок
                </button>
            </div>

            <div class="district-grid" id="districtGrid">
                @foreach($districts as $district)
                    <article class="district-card {{ $district['is_transferred'] ? 'district-card--transferred' : '' }}"
                             data-district-card
                             data-search="{{ $district['search_text'] }}">
                        <div class="district-card__header">
                            <div class="district-card__identity">
                                <div class="district-card__icon">
                                    <i class="bi bi-map"></i>
                                </div>

                                <div>
                                    <h2 class="district-card__name">{{ $district['name'] }}</h2>
                                    <div class="district-card__meta">
                                        <span><i class="bi bi-hash"></i> Код {{ $district['code'] }}</span>
                                        <span><i class="bi bi-info-circle"></i> {{ $district['description'] }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($district['is_transferred'])
                                <span class="district-status district-status--merged">
                                    <i class="bi bi-diagram-3"></i>
                                    Передан: {{ $district['transfer']['district'] }}
                                </span>
                            @elseif($district['transfer'] && $district['transfer']['scheduled'])
                                <span class="district-status district-status--transfer">
                                    <i class="bi bi-calendar2-range"></i>
                                    Передача запланирована
                                </span>
                            @elseif($district['incoming_transfer'])
                                <span class="district-status district-status--incoming">
                                    <i class="bi bi-diagram-2"></i>
                                    Принимает {{ $district['incoming_transfer']['district'] }}
                                </span>
                            @else
                                <span class="district-status">
                                    <i class="bi bi-check-circle"></i>
                                    Активен
                                </span>
                            @endif
                        </div>

                        <div class="district-card__body">
                            <section class="district-section">
                                <div class="district-section__title">
                                    <span>Врачи участка</span>
                                </div>

                                <div class="district-doctor">
                                    <div class="district-doctor__avatar">{{ $district['initials'] }}</div>
                                    <div>
                                        <div class="district-doctor__role">Участковый психиатр · приём в кабинете</div>
                                        <div class="district-doctor__name">{{ $district['doctor'] }}</div>
                                        <div class="district-doctor__speciality">{{ $district['speciality'] }}</div>
                                    </div>

                                    @if(!empty($district['doctor_vacation']))
                                        <div class="district-doctor-vacation {{ !empty($district['doctor_vacation']['active']) ? 'is-active' : (!empty($district['doctor_vacation']['approved']) ? 'is-approved' : 'is-planned') }}">
                                            <div class="district-doctor-vacation__status">
                                                <i class="bi bi-suitcase2"></i>
                                                {{ $district['doctor_vacation']['label'] ?? 'Отпуск' }}
                                            </div>
                                            <div class="district-doctor-vacation__dates">
                                                {{ $district['doctor_vacation']['dates'] ?? '' }}
                                            </div>
                                            <div class="district-doctor-vacation__replacement {{ !empty($district['doctor_vacation']['replacement_doctor']) ? '' : 'is-missing' }}">
                                                И.О.:
                                                <strong>{{ ($district['doctor_vacation']['replacement_doctor'] ?? '') ?: 'не назначен' }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="district-doctor district-doctor--home">
                                    <div class="district-doctor__avatar">{{ $district['home_visit_initials'] }}</div>
                                    <div>
                                        <div class="district-doctor__role">Выездной психиатр · вызовы на дом</div>
                                        <div class="district-doctor__name">{{ $district['home_visit_doctor'] }}</div>
                                        <div class="district-doctor__speciality">{{ $district['home_visit_speciality'] }}</div>
                                    </div>

                                    @if(!empty($district['home_visit_vacation']))
                                        <div class="district-doctor-vacation {{ !empty($district['home_visit_vacation']['active']) ? 'is-active' : (!empty($district['home_visit_vacation']['approved']) ? 'is-approved' : 'is-planned') }}">
                                            <div class="district-doctor-vacation__status">
                                                <i class="bi bi-suitcase2"></i>
                                                {{ $district['home_visit_vacation']['label'] ?? 'Отпуск' }}
                                            </div>
                                            <div class="district-doctor-vacation__dates">
                                                {{ $district['home_visit_vacation']['dates'] ?? '' }}
                                            </div>
                                            <div class="district-doctor-vacation__replacement {{ !empty($district['home_visit_vacation']['replacement_doctor']) ? '' : 'is-missing' }}">
                                                И.О.:
                                                <strong>{{ ($district['home_visit_vacation']['replacement_doctor'] ?? '') ?: 'не назначен' }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($district['additional_doctors']))
                                    <div class="district-additional-doctors">
                                        @foreach($district['additional_doctors'] as $additionalDoctor)
                                            <div class="district-additional-doctor">
                                                <div class="district-additional-doctor__avatar">{{ $additionalDoctor['initials'] }}</div>
                                                <div>
                                                    <div class="district-additional-doctor__role">{{ $additionalDoctor['role_label'] }}</div>
                                                    <div class="district-additional-doctor__name">{{ $additionalDoctor['name'] }}</div>
                                                    <div class="district-additional-doctor__meta">
                                                        {{ $additionalDoctor['speciality'] ?: 'Психиатр' }}
                                                        @if($additionalDoctor['source_district'])
                                                            · из {{ $additionalDoctor['source_district'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($district['transfer'])
                                    <div class="district-transfer">
                                        <div class="district-transfer__head">
                                            <div class="district-transfer__title">
                                                <i class="bi bi-diagram-3"></i>
                                                {{ $district['transfer']['active'] ? 'Функциональность передана' : 'Передача запланирована' }}
                                            </div>
                                            <div class="district-transfer__dates">{{ $district['transfer']['dates'] }}</div>
                                        </div>
                                        <div class="district-transfer__doctor">
                                            {{ $district['transfer']['district'] }}
                                            @if($district['transfer']['doctor'])
                                                · {{ $district['transfer']['doctor'] }}
                                            @endif
                                        </div>
                                        <div class="district-transfer__hint">{{ $district['transfer']['reason'] }}</div>
                                    </div>
                                @elseif($district['incoming_transfer'])
                                    <div class="district-transfer district-transfer--incoming">
                                        <div class="district-transfer__head">
                                            <div class="district-transfer__title">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                Временно принимает участок
                                            </div>
                                            <div class="district-transfer__dates">{{ $district['incoming_transfer']['dates'] }}</div>
                                        </div>
                                        <div class="district-transfer__doctor">{{ $district['incoming_transfer']['district'] }}</div>
                                        <div class="district-transfer__hint">Территория и обслуживание этого участка временно присоединены к текущему.</div>
                                    </div>
                                @endif
                            </section>

                            <section class="district-section">
                                <div class="district-section__title">
                                    <span>Территория участка</span>
                                    <span>{{ $district['total_addresses_label'] }}</span>
                                </div>

                                <div class="district-address-list">
                                    @foreach($district['visible_addresses'] as $address)
                                        <div class="district-address">
                                            <div class="district-address__icon">
                                                <i class="bi bi-geo"></i>
                                            </div>
                                            <div class="district-address__main">
                                                <div class="district-address__text">{{ $address['text'] }}</div>
                                                <div class="district-address__type">{{ $address['type'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($district['has_more_addresses'])
                                    <button type="button"
                                            class="district-address-more"
                                            data-bs-toggle="modal"
                                            data-bs-target="#districtAddressesModal"
                                            data-district-name="{{ $district['name'] }}"
                                            data-district-code="{{ $district['code'] }}"
                                            aria-haspopup="dialog"
                                            aria-controls="districtAddressesModal"
                                            aria-label="Показать остальные адресные правила: {{ $district['name'] }}">
                                        {{ $district['more_addresses_label'] }}
                                    </button>

                                    <template data-district-addresses-template>
                                        @if($district['has_remaining_addresses'])
                                            <div class="district-section__title">
                                                <span>Остальные адресные правила</span>
                                                <span>{{ $district['remaining_addresses_count'] }}</span>
                                            </div>
                                            <p class="district-addresses-modal__note">
                                                Первые {{ $district['visible_addresses_label'] }} уже показаны в карточке.
                                                Здесь — оставшаяся часть списка.
                                            </p>

                                            <div class="district-address-list" role="list">
                                                @foreach($district['remaining_addresses'] as $address)
                                                    <div class="district-address" role="listitem">
                                                        <div class="district-address__icon" aria-hidden="true">
                                                            <i class="bi bi-geo"></i>
                                                        </div>
                                                        <div class="district-address__main">
                                                            <div class="district-address__text">{{ $address['text'] }}</div>
                                                            <div class="district-address__type">{{ $address['type'] }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="district-addresses-empty" role="status">
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                                <h3>Остальные адреса не переданы</h3>
                                                <p>
                                                    В данных участка указано ещё {{ $district['missing_addresses_label'] }},
                                                    но сами адреса отсутствуют.
                                                    Список появится после передачи полных адресных правил участка.
                                                </p>
                                            </div>
                                        @endif
                                    </template>
                                @endif
                            </section>
                        </div>

                        <div class="district-card__footer">
                            <div class="district-card__actions">
                                @if($district['transfer'])
                                    <button type="button"
                                            class="district-action district-action--separate"
                                            data-separate-district
                                            data-separate-url="{{ route('doctors.settings.districts.separate', ['district' => $district['id']]) }}"
                                            data-separate-name="{{ $district['name'] }}"
                                            data-separate-target="{{ $district['transfer']['district'] }}">
                                        <i class="bi bi-intersect"></i>
                                        {{ $district['is_transferred'] ? 'Сепаратировать участки' : 'Отменить передачу' }}
                                    </button>
                                @else
                                    <button type="button"
                                            class="district-action district-action--primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#districtEditModal"
                                            data-district-mode="edit"
                                            data-district-id="{{ $district['id'] }}"
                                            data-district-name="{{ $district['name'] }}"
                                            data-district-code="{{ $district['code'] }}"
                                            data-district-description="{{ $district['description'] }}"
                                            data-district-doctor="{{ $district['doctor'] }}"
                                            data-district-doctor-id="{{ $district['doctor_id'] }}"
                                            data-district-home-doctor="{{ $district['home_visit_doctor'] }}"
                                            data-district-home-doctor-id="{{ $district['home_visit_doctor_id'] }}"
                                            data-district-rules="{{ $district['rules_json'] }}">
                                        <i class="bi bi-pencil"></i>
                                        Изменить
                                    </button>

                                    @if(!$district['incoming_transfer'] && $transferSchemaReady)
                                        <button type="button"
                                                class="district-action district-action--warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#districtTransferModal"
                                                data-district-id="{{ $district['id'] }}"
                                                data-transfer-url="{{ route('doctors.settings.districts.transfer', ['district' => $district['id']]) }}"
                                                data-transfer-district="{{ $district['name'] }}"
                                                data-transfer-doctor="{{ $district['doctor'] }}"
                                                data-transfer-doctor-id="{{ $district['doctor_id'] }}"
                                                data-transfer-code="{{ $district['code'] }}">
                                            <i class="bi bi-arrow-left-right"></i>
                                            Временно передать
                                        </button>
                                    @endif

                                    @if($districtDeleteReady)
                                        <button type="button"
                                                class="district-action district-action--danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#districtDeleteModal"
                                                data-delete-district-id="{{ $district['id'] }}"
                                                data-delete-district-name="{{ $district['name'] }}"
                                                data-delete-district-code="{{ $district['code'] }}"
                                                data-delete-doctor="{{ $district['doctor'] }}"
                                                data-delete-home-doctor="{{ $district['home_visit_doctor'] }}"
                                                data-delete-additional-doctors='@json($district['additional_doctors'] ?? [])'
                                                data-delete-url="{{ route('doctors.settings.districts.delete', ['district' => $district['id']]) }}">
                                            <i class="bi bi-trash3"></i>
                                            Удалить участок
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="settings-empty d-none" id="districtEmpty">
                <div class="settings-empty__icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>Участки не найдены</h3>
                <p>Попробуйте изменить поисковый запрос.</p>
            </div>
        </div>
    </main>

    <div class="modal fade district-modal district-addresses-modal"
         id="districtAddressesModal"
         tabindex="-1"
         aria-labelledby="districtAddressesModalLabel"
         aria-describedby="districtAddressesModalSubtitle"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="districtAddressesModalLabel">Остальные адреса участка</h2>
                        <div class="district-addresses-modal__subtitle" id="districtAddressesModalSubtitle"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body" id="districtAddressesModalBody"></div>

                <div class="modal-footer">
                    <button type="button" class="settings-button settings-button--secondary" data-bs-dismiss="modal">
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade district-modal"
         id="districtEditModal"
         tabindex="-1"
         aria-labelledby="districtEditModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable district-edit-dialog">
            <form class="modal-content"
                  id="districtForm"
                  method="POST"
                  data-address-search-url="{{ $districtAddressSearchUrl }}"
                  data-store-url="{{ $districtStoreUrl }}"
                  data-update-url-template="{{ $districtUpdateUrlTemplate }}"
                  autocomplete="off">
                @csrf
                <input type="hidden" id="districtId" value="">
                <div class="modal-header">
                    <div class="district-edit-heading">
                        <div class="district-edit-heading__icon">
                            <i class="bi bi-map"></i>
                        </div>
                        <div>
                            <h2 class="modal-title" id="districtEditModalLabel">Новый участок</h2>
                            <div class="district-edit-heading__subtitle">Врач и территория обслуживания</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="district-form-errors" id="districtFormErrors" role="alert"></div>

                    <section class="district-form-card">
                        <div class="district-form-card__head">
                            <div class="district-form-card__title-wrap">
                                <span class="district-form-card__step">1</span>
                                <div>
                                    <h3 class="district-form-card__title">Основные данные</h3>
                                    <div class="district-form-card__hint">Название, код и ответственный врач</div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-form-grid">
                            <div class="settings-col-8">
                                <label class="settings-label" for="districtName">Название участка</label>
                                <input type="text"
                                       id="districtName"
                                       name="name"
                                       class="settings-control"
                                       placeholder="Участок №4 — Южный">
                            </div>

                            <div class="settings-col-4">
                                <label class="settings-label" for="districtCode">Код</label>
                                <input type="text"
                                       id="districtCode"
                                       name="code"
                                       class="settings-control"
                                       placeholder="04">
                            </div>

                            <div class="settings-col-6">
                                <label class="settings-label" for="districtDoctorSearch">Участковый психиатр</label>
                                <div class="district-doctor-picker" id="districtDoctorPicker">
                                    <input type="hidden" id="districtDoctor" name="doctor_id" value="">

                                    <div class="district-doctor-picker__search">
                                        <i class="bi bi-search"></i>
                                        <input type="text"
                                               id="districtDoctorSearch"
                                               class="settings-control district-doctor-picker__input"
                                               placeholder="Начните вводить ФИО врача"
                                               autocomplete="off">
                                        <button type="button"
                                                class="district-doctor-picker__clear"
                                                id="districtDoctorClear"
                                                aria-label="Очистить выбор врача">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <div class="district-doctor-picker__selected" id="districtDoctorSelected">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span></span>
                                    </div>

                                    <div class="district-doctor-picker__results" id="districtDoctorResults">
                                        @foreach($districtDoctors as $doctor)
                                            <button type="button"
                                                    class="district-doctor-option"
                                                    data-main-doctor-option
                                                    data-doctor-id="{{ $doctor['id'] }}"
                                                    data-doctor-name="{{ $doctor['name'] }}"
                                                    data-doctor-profession="{{ $doctor['profession'] }}"
                                                    data-doctor-speciality="{{ $doctor['speciality'] }}"
                                                    data-assigned-district-id="{{ $doctor['assigned_district_id'] }}"
                                                    data-assigned-role="{{ $doctor['assigned_role'] }}"
                                                    data-home-eligible="{{ $doctor['home_eligible'] ? 1 : 0 }}">
                                                <span class="district-doctor-option__avatar">{{ $doctor['initials'] }}</span>
                                                <span>
                                                        <span class="district-doctor-option__name">{{ $doctor['name'] }}</span>
                                                        <span class="district-doctor-option__meta">
                                                            {{ $doctor['speciality'] ?: $doctor['profession'] }}
                                                        </span>
                                                    </span>
                                                <span class="district-doctor-option__badge">Врач</span>
                                            </button>
                                        @endforeach
                                        <div class="district-doctor-picker__empty">Свободный психиатр не найден</div>
                                    </div>
                                </div>
                                <div class="district-picker-hint">Ведёт приём пациентов этого участка в кабинете.</div>
                            </div>

                            <div class="settings-col-6">
                                <label class="settings-label" for="districtHomeDoctorSearch">Психиатр для вызовов на дом</label>
                                <div class="district-doctor-picker" id="districtHomeDoctorPicker">
                                    <input type="hidden" id="districtHomeDoctor" name="home_visit_doctor_id" value="">

                                    <div class="district-doctor-picker__search">
                                        <i class="bi bi-search"></i>
                                        <input type="text"
                                               id="districtHomeDoctorSearch"
                                               class="settings-control district-doctor-picker__input"
                                               placeholder="Начните вводить ФИО врача"
                                               autocomplete="off">
                                        <button type="button"
                                                class="district-doctor-picker__clear"
                                                id="districtHomeDoctorClear"
                                                aria-label="Очистить выбор выездного врача">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <div class="district-doctor-picker__selected" id="districtHomeDoctorSelected">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span></span>
                                    </div>

                                    <div class="district-doctor-picker__results" id="districtHomeDoctorResults">
                                        @foreach($districtDoctors as $doctor)
                                            <button type="button"
                                                    class="district-doctor-option"
                                                    data-home-doctor-option
                                                    data-doctor-id="{{ $doctor['id'] }}"
                                                    data-doctor-name="{{ $doctor['name'] }}"
                                                    data-doctor-profession="{{ $doctor['profession'] }}"
                                                    data-doctor-speciality="{{ $doctor['speciality'] }}"
                                                    data-assigned-district-id="{{ $doctor['assigned_district_id'] }}"
                                                    data-assigned-role="{{ $doctor['assigned_role'] }}"
                                                    data-home-eligible="{{ $doctor['home_eligible'] ? 1 : 0 }}">
                                                <span class="district-doctor-option__avatar">{{ $doctor['initials'] }}</span>
                                                <span>
                                                        <span class="district-doctor-option__name">{{ $doctor['name'] }}</span>
                                                        <span class="district-doctor-option__meta">
                                                            {{ $doctor['speciality'] ?: $doctor['profession'] }}
                                                        </span>
                                                    </span>
                                                <span class="district-doctor-option__badge">Выезды</span>
                                            </button>
                                        @endforeach
                                        <div class="district-doctor-picker__empty">Свободный выездной психиатр не найден</div>
                                    </div>
                                </div>
                                <div class="district-picker-hint">После назначения врач скрывается из кабинетов, замещений и других участков.</div>
                            </div>

                            <div class="settings-col-12">
                                <label class="settings-label" for="districtDescription">Описание</label>
                                <input type="text"
                                       id="districtDescription"
                                       name="description"
                                       class="settings-control"
                                       placeholder="Например: центральная часть города">
                            </div>
                        </div>
                    </section>

                    <section class="district-form-card">
                        <div class="district-form-card__head">
                            <div class="district-form-card__title-wrap">
                                <span class="district-form-card__step">2</span>
                                <div>
                                    <h3 class="district-form-card__title">Территория участка</h3>
                                    <div class="district-form-card__hint">Добавьте улицы, диапазоны домов или отдельные адреса</div>
                                </div>
                            </div>

                            <button type="button"
                                    class="settings-button settings-button--secondary"
                                    id="addDistrictAddress">
                                <i class="bi bi-plus-lg"></i>
                                Добавить правило
                            </button>
                        </div>

                        <div class="district-address-editor" id="districtAddressEditor">
                            <div class="district-address-row" data-address-row>
                                <div class="district-rule-label"><i class="bi bi-geo-alt"></i> Правило <span class="district-rule-number">1</span></div>

                                <div class="district-address-type">
                                    <label class="settings-label">Тип</label>
                                    <select class="settings-control js-address-type" name="addresses[0][type]">
                                        <option value="street-range">Диапазон домов</option>
                                        <option value="street">Улица целиком</option>
                                        <option value="settlement">Населённый пункт</option>
                                        <option value="exact">Конкретный адрес</option>
                                    </select>
                                </div>

                                <div class="district-address-row__details">
                                    <div class="district-address-picker">
                                        <label class="settings-label">Адрес</label>
                                        <input type="text"
                                               class="settings-control js-address-value"
                                               name="addresses[0][address]"
                                               placeholder="Начните вводить город и улицу"
                                               autocomplete="off">
                                        <input type="hidden" class="js-address-fias" name="addresses[0][fias_id]" value="">
                                        <div class="district-address-suggestions js-address-suggestions"></div>
                                    </div>
                                    <div class="district-house-picker">
                                        <label class="settings-label">Дома</label>
                                        <input type="text"
                                               class="settings-control js-address-detail"
                                               name="addresses[0][detail]"
                                               placeholder="Например: 1-10"
                                               autocomplete="off">
                                        <div class="district-address-suggestions js-house-suggestions"></div>
                                        <div class="district-house-status js-house-status"></div>
                                    </div>
                                </div>

                                <button type="button" class="district-remove-address js-remove-address" title="Удалить правило" aria-label="Удалить правило">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <div class="district-address-row" data-address-row>
                                <div class="district-rule-label"><i class="bi bi-geo-alt"></i> Правило <span class="district-rule-number">2</span></div>

                                <div class="district-address-type">
                                    <label class="settings-label">Тип</label>
                                    <select class="settings-control js-address-type" name="addresses[1][type]">
                                        <option value="street-range">Диапазон домов</option>
                                        <option value="street" selected>Улица целиком</option>
                                        <option value="settlement">Населённый пункт</option>
                                        <option value="exact">Конкретный адрес</option>
                                    </select>
                                </div>

                                <div class="district-address-row__details">
                                    <div class="district-address-picker">
                                        <label class="settings-label">Адрес</label>
                                        <input type="text"
                                               class="settings-control js-address-value"
                                               name="addresses[1][address]"
                                               placeholder="Начните вводить город и улицу"
                                               autocomplete="off">
                                        <input type="hidden" class="js-address-fias" name="addresses[1][fias_id]" value="">
                                        <div class="district-address-suggestions js-address-suggestions"></div>
                                    </div>
                                    <div class="district-house-picker">
                                        <label class="settings-label">Дома</label>
                                        <input type="text"
                                               class="settings-control js-address-detail"
                                               name="addresses[1][detail]"
                                               placeholder="Не требуется"
                                               disabled
                                               autocomplete="off">
                                        <div class="district-address-suggestions js-house-suggestions"></div>
                                        <div class="district-house-status js-house-status"></div>
                                    </div>
                                </div>

                                <button type="button" class="district-remove-address js-remove-address" title="Удалить правило" aria-label="Удалить правило">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="settings-button settings-button--secondary" data-bs-dismiss="modal">
                        Отмена
                    </button>
                    <button type="submit" class="settings-button settings-button--primary">
                        <i class="bi bi-check-lg"></i>
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade district-modal transfer-modal"
         id="districtTransferModal"
         tabindex="-1"
         aria-labelledby="districtTransferModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content"
                  id="districtTransferForm"
                  method="POST"
                  action=""
                  autocomplete="off">
                @csrf

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="districtTransferModalLabel">Временная передача участка</h2>
                        <div class="settings-panel__subtitle">Функциональность участка временно перейдёт другому участку.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="district-form-errors" id="transferFormErrors" role="alert"></div>

                    <div class="transfer-modal__hero mb-3">
                        <div class="transfer-modal__hero-main">
                            <span class="transfer-modal__hero-icon"><i class="bi bi-arrow-left-right"></i></span>
                            <div>
                                <h3 class="transfer-modal__hero-title" id="transferDistrictName">Участок</h3>
                                <div class="transfer-modal__hero-subtitle">После передачи исходный участок станет неактивным на выбранный период, а его функциональность перейдёт целевому участку.</div>
                                <div class="transfer-modal__hero-doctor">Основной врач: <strong id="transferBaseDoctor">—</strong></div>
                            </div>
                        </div>
                        <div class="transfer-modal__hero-badge">
                            <i class="bi bi-shield-check"></i>
                            Автовозврат после периода
                        </div>
                    </div>

                    <div class="transfer-modal__section">
                        <div class="transfer-modal__section-head">
                            <div>
                                <div class="transfer-modal__section-title">К какому участку передать</div>
                                <div class="transfer-modal__section-text">Найдите участкового психиатра другого участка. По врачу система определит целевой участок.</div>
                            </div>
                        </div>

                        <label class="settings-label" for="transferDoctorSearch">Участковый психиатр целевого участка</label>
                        <div class="district-doctor-picker" id="transferDoctorPicker">
                            <input type="hidden" id="transferDoctor" name="doctor_id" value="">

                            <div class="district-doctor-picker__search">
                                <i class="bi bi-search"></i>
                                <input type="text"
                                       id="transferDoctorSearch"
                                       class="settings-control district-doctor-picker__input"
                                       placeholder="Начните вводить ФИО врача"
                                       autocomplete="off">
                                <button type="button"
                                        class="district-doctor-picker__clear"
                                        id="transferDoctorClear"
                                        aria-label="Очистить выбор врача">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="district-doctor-picker__selected" id="transferDoctorSelected">
                                <i class="bi bi-check-circle-fill"></i>
                                <span></span>
                            </div>

                            <div class="district-doctor-picker__results" id="transferDoctorResults">
                                @foreach($districtDoctors as $doctor)
                                    @if($doctor['assigned_role'] === 'main')
                                        <button type="button"
                                                class="district-doctor-option"
                                                data-transfer-doctor-option
                                                data-doctor-id="{{ $doctor['id'] }}"
                                                data-doctor-name="{{ $doctor['name'] }}"
                                                data-doctor-profession="{{ $doctor['profession'] ?? '' }}"
                                                data-doctor-speciality="{{ $doctor['speciality'] ?? '' }}"
                                                data-doctor-assigned-role="{{ $doctor['assigned_role'] ?? '' }}"
                                                data-doctor-district-id="{{ $doctor['assigned_district_id'] ?? '' }}"
                                                data-doctor-district-name="{{ $doctor['assigned_district_name'] ?? '' }}"
                                                data-doctor-district-code="{{ $doctor['assigned_district_code'] ?? '' }}">
                                            <span class="district-doctor-option__avatar">{{ $doctor['initials'] }}</span>
                                            <span>
                                                <span class="district-doctor-option__name">{{ $doctor['name'] }}</span>
                                                <span class="district-doctor-option__meta">
                                                    {{ $doctor['assigned_district_name'] ?? 'Участок не указан' }}
                                                    @if(!empty($doctor['assigned_district_code']))
                                                        · код {{ $doctor['assigned_district_code'] }}
                                                    @endif
                                                </span>
                                            </span>
                                            <span class="district-doctor-option__badge">Участок</span>
                                        </button>
                                    @endif
                                @endforeach
                                <div class="district-doctor-picker__empty">Другой доступный участок не найден</div>
                            </div>
                        </div>
                        <div class="transfer-modal__hint">В списке показываются только участковые психиатры, уже закреплённые за другими участками.</div>
                    </div>

                    <div class="transfer-modal__section">
                        <div class="transfer-modal__section-head">
                            <div>
                                <div class="transfer-modal__section-title">Период передачи</div>
                                <div class="transfer-modal__section-text">После даты окончания участок автоматически снова станет самостоятельным.</div>
                            </div>
                            <div class="transfer-modal__note"><i class="bi bi-info-circle"></i> Даты можно скорректировать перед сохранением</div>
                        </div>

                        <div class="settings-form-grid">
                            <div class="settings-col-6">
                                <label class="settings-label" for="transferDateFrom">С даты</label>
                                <input type="date"
                                       id="transferDateFrom"
                                       name="transfer_from"
                                       class="settings-control"
                                       required>
                            </div>

                            <div class="settings-col-6">
                                <label class="settings-label" for="transferDateTo">По дату</label>
                                <input type="date"
                                       id="transferDateTo"
                                       name="transfer_until"
                                       class="settings-control"
                                       required>
                            </div>

                            <div class="settings-col-12">
                                <label class="settings-label" for="transferReason">Причина / комментарий</label>
                                <textarea id="transferReason"
                                          name="reason"
                                          class="settings-control"
                                          placeholder="Например: отпуск основного врача"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="transfer-modal__footer-note">Для досрочного возврата используйте кнопку «Сепаратировать участки» на карточке переданного участка.</div>
                    <button type="button" class="settings-button settings-button--secondary" data-bs-dismiss="modal">
                        Отмена
                    </button>
                    <button type="button" class="settings-button settings-button--primary" id="transferSubmitButton">
                        <i class="bi bi-check-lg"></i>
                        Передать участок
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade district-modal"
         id="districtDeleteModal"
         tabindex="-1"
         aria-labelledby="districtDeleteModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" id="districtDeleteForm" method="POST" action="">
                @csrf
                <input type="hidden" id="deleteSourceDistrictId" name="source_district_id" value="">

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="districtDeleteModalLabel">Удаление участка</h2>
                        <div class="settings-panel__subtitle">Перед удалением перенесите закреплённых врачей в другой участок.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="district-form-errors" id="deleteDistrictErrors" role="alert"></div>

                    <div class="district-delete-summary">
                        <div class="district-delete-summary__row">
                            <div class="district-delete-summary__label">Удаляемый участок</div>
                            <div class="district-delete-summary__value" id="deleteDistrictName">—</div>
                        </div>
                        <div class="district-delete-summary__row">
                            <div class="district-delete-summary__label">Участковый психиатр</div>
                            <div class="district-delete-summary__value" id="deleteDistrictDoctor">—</div>
                        </div>
                        <div class="district-delete-summary__row">
                            <div class="district-delete-summary__label">Выездной психиатр</div>
                            <div class="district-delete-summary__value" id="deleteDistrictHomeDoctor">—</div>
                        </div>
                        <div class="district-delete-summary__row d-none" id="deleteDistrictAdditionalRow">
                            <div class="district-delete-summary__label">Доп. врачи</div>
                            <div class="district-delete-summary__value" id="deleteDistrictAdditionalDoctors">—</div>
                        </div>
                    </div>

                    <div class="district-delete-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>
                            Участок будет удалён вместе с его территорией. Участковый и выездной психиатры останутся сотрудниками клиники, но больше не будут закреплены за этим участком. Если на удаляемый участок временно переданы другие участки, такая передача будет автоматически прекращена.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="settings-button settings-button--secondary" data-bs-dismiss="modal">
                        Отмена
                    </button>
                    <button type="submit" class="settings-button settings-button--primary" id="deleteDistrictSubmit">
                        <i class="bi bi-trash3"></i>
                        Удалить участок
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const search = document.getElementById('districtSearch');
            const cards = Array.from(document.querySelectorAll('[data-district-card]'));
            const empty = document.getElementById('districtEmpty');

            const pageNotice = document.getElementById('districtPageNotice');
            const pageNoticeIcon = document.getElementById('districtPageNoticeIcon');
            const pageNoticeTitle = document.getElementById('districtPageNoticeTitle');
            const pageNoticeMessage = document.getElementById('districtPageNoticeMessage');

            function showPageNotice(type, message) {
                if (!pageNotice) {
                    return;
                }

                const success = type === 'success';
                pageNotice.classList.remove(
                    'district-page-notice--success',
                    'district-page-notice--error'
                );
                pageNotice.classList.add(
                    success
                        ? 'district-page-notice--success'
                        : 'district-page-notice--error',
                    'is-visible'
                );

                if (pageNoticeIcon) {
                    pageNoticeIcon.className = `bi district-page-notice__icon ${success ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}`;
                }
                if (pageNoticeTitle) {
                    pageNoticeTitle.textContent = success ? 'Готово' : 'Ошибка';
                }
                if (pageNoticeMessage) {
                    pageNoticeMessage.textContent = message || (success ? 'Операция выполнена.' : 'Не удалось выполнить операцию.');
                }

                window.setTimeout(() => {
                    pageNotice.classList.remove('is-visible');
                }, 5600);
            }

            try {
                const stored = sessionStorage.getItem('districtNotice');
                if (stored) {
                    sessionStorage.removeItem('districtNotice');
                    const notice = JSON.parse(stored);
                    showPageNotice(notice.type || 'success', notice.message || '');
                }
            } catch (noticeError) {
                // sessionStorage может быть недоступен.
            }

            if (search) {
                search.addEventListener('input', () => {
                    const query = search.value.trim().toLowerCase();
                    let visibleCount = 0;

                    cards.forEach((card) => {
                        const haystack = (card.dataset.search || '').toLowerCase();
                        const visible = query === '' || haystack.includes(query);
                        card.classList.toggle('d-none', !visible);

                        if (visible) {
                            visibleCount += 1;
                        }
                    });

                    empty?.classList.toggle('d-none', visibleCount !== 0);
                });
            }

            const addressesModal = document.getElementById('districtAddressesModal');
            const addressesSubtitle = document.getElementById('districtAddressesModalSubtitle');
            const addressesBody = document.getElementById('districtAddressesModalBody');

            addressesModal?.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                const card = trigger?.closest('[data-district-card]');
                const template = card?.querySelector('[data-district-addresses-template]');
                const districtName = trigger?.dataset.districtName || 'Участок';
                const districtCode = trigger?.dataset.districtCode || '';

                if (!addressesSubtitle || !addressesBody) {
                    return;
                }

                addressesSubtitle.textContent = districtName + (districtCode ? ` · Код ${districtCode}` : '');
                addressesBody.replaceChildren();

                if (template?.content) {
                    // Blade экранирует адреса; копируем только готовую разметку участка.
                    addressesBody.appendChild(template.content.cloneNode(true));
                } else {
                    const message = document.createElement('p');
                    message.className = 'district-addresses-modal__note';
                    message.textContent = 'Не удалось получить список адресных правил этого участка.';
                    addressesBody.appendChild(message);
                }

                addressesBody.scrollTop = 0;
            });

            addressesModal?.addEventListener('hidden.bs.modal', () => {
                addressesBody?.replaceChildren();

                if (addressesSubtitle) {
                    addressesSubtitle.textContent = '';
                }
            });

            const editModal = document.getElementById('districtEditModal');
            const districtForm = document.getElementById('districtForm');
            const districtId = document.getElementById('districtId');
            const districtFormErrors = document.getElementById('districtFormErrors');
            let currentDistrictId = '';

            const pickers = {
                main: {
                    picker: document.getElementById('districtDoctorPicker'),
                    input: document.getElementById('districtDoctor'),
                    search: document.getElementById('districtDoctorSearch'),
                    clear: document.getElementById('districtDoctorClear'),
                    selected: document.getElementById('districtDoctorSelected'),
                    options: Array.from(document.querySelectorAll('[data-main-doctor-option]')),
                },
                home: {
                    picker: document.getElementById('districtHomeDoctorPicker'),
                    input: document.getElementById('districtHomeDoctor'),
                    search: document.getElementById('districtHomeDoctorSearch'),
                    clear: document.getElementById('districtHomeDoctorClear'),
                    selected: document.getElementById('districtHomeDoctorSelected'),
                    options: Array.from(document.querySelectorAll('[data-home-doctor-option]')),
                },
            };

            function otherRole(role) {
                return role === 'main' ? 'home' : 'main';
            }

            function optionAvailable(role, option) {
                const optionId = String(option.dataset.doctorId || '');
                const assignedDistrictId = String(option.dataset.assignedDistrictId || '');
                const assignedRole = String(option.dataset.assignedRole || '');
                const otherSelected = String(
                    pickers[otherRole(role)]?.input?.value || ''
                );

                if (optionId && optionId === otherSelected) {
                    return false;
                }

                if (
                    assignedDistrictId
                    && assignedDistrictId !== currentDistrictId
                ) {
                    return false;
                }

                if (role === 'home') {
                    const currentOwnHome =
                        assignedDistrictId === currentDistrictId
                        && assignedRole === 'home';

                    if (
                        option.dataset.homeEligible !== '1'
                        && !currentOwnHome
                    ) {
                        return false;
                    }
                }

                return true;
            }

            function setPickerDoctor(
                role,
                id,
                name,
                closeResults = true
            ) {
                const picker = pickers[role];
                const doctorIdValue = String(id || '').trim();
                const doctorName = String(name || '').trim();

                if (picker.input) {
                    picker.input.value = doctorIdValue;
                }

                if (picker.search) {
                    picker.search.value = doctorName;
                }

                picker.picker?.classList.toggle(
                    'has-value',
                    doctorName !== ''
                );
                picker.picker?.classList.toggle(
                    'has-selection',
                    doctorIdValue !== ''
                );

                const selectedOption = picker.options.find(
                    (option) =>
                        String(option.dataset.doctorId || '')
                        === doctorIdValue
                );

                if (picker.selected) {
                    const label = picker.selected.querySelector('span');
                    const speciality = String(
                        selectedOption?.dataset.doctorSpeciality
                        || selectedOption?.dataset.doctorProfession
                        || 'Психиатр'
                    ).trim();

                    if (label) {
                        label.textContent = doctorIdValue
                            ? `${doctorName} · ${speciality}`
                            : '';
                    }
                }

                picker.options.forEach((option) => {
                    option.classList.toggle(
                        'is-selected',
                        doctorIdValue !== ''
                        && String(option.dataset.doctorId || '')
                        === doctorIdValue
                    );
                });

                if (closeResults) {
                    picker.picker?.classList.remove('is-open');
                }

                filterPicker(otherRole(role));
            }

            function filterPicker(role) {
                const picker = pickers[role];
                const query = String(
                    picker.search?.value || ''
                ).trim().toLowerCase();
                let visible = 0;

                picker.options.forEach((option) => {
                    const haystack = [
                        option.dataset.doctorName,
                        option.dataset.doctorProfession,
                        option.dataset.doctorSpeciality,
                    ]
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();

                    const show = optionAvailable(role, option)
                        && (query === '' || haystack.includes(query));

                    option.classList.toggle('d-none', !show);

                    if (show) {
                        visible += 1;
                    }
                });

                picker.picker?.classList.toggle(
                    'no-results',
                    visible === 0
                );
            }

            function bindPicker(role) {
                const picker = pickers[role];

                picker.search?.addEventListener('focus', () => {
                    filterPicker(role);
                    picker.picker?.classList.add('is-open');
                });

                picker.search?.addEventListener('input', () => {
                    const current = String(
                        picker.search.value || ''
                    ).trim();

                    picker.picker?.classList.toggle(
                        'has-value',
                        current !== ''
                    );

                    if (picker.input?.value) {
                        const selected = picker.options.find(
                            (option) =>
                                String(option.dataset.doctorId || '')
                                === picker.input.value
                        );

                        if (
                            !selected
                            || String(selected.dataset.doctorName || '')
                            !== current
                        ) {
                            picker.input.value = '';
                            picker.picker?.classList.remove(
                                'has-selection'
                            );
                        }
                    }

                    filterPicker(role);
                    filterPicker(otherRole(role));
                    picker.picker?.classList.add('is-open');
                });

                picker.options.forEach((option) => {
                    option.addEventListener('click', () => {
                        if (!optionAvailable(role, option)) {
                            return;
                        }

                        setPickerDoctor(
                            role,
                            option.dataset.doctorId || '',
                            option.dataset.doctorName || ''
                        );
                    });
                });

                picker.clear?.addEventListener('click', () => {
                    setPickerDoctor(role, '', '');
                    filterPicker(role);
                    filterPicker(otherRole(role));
                    picker.picker?.classList.add('is-open');
                    picker.search?.focus();
                });
            }

            bindPicker('main');
            bindPicker('home');

            document.addEventListener('click', (event) => {
                Object.values(pickers).forEach((picker) => {
                    if (
                        picker.picker
                        && !picker.picker.contains(event.target)
                    ) {
                        picker.picker.classList.remove('is-open');
                    }
                });
            });

            function setAddressRows(rules) {
                if (!editor) {
                    return;
                }

                const source = Array.isArray(rules) && rules.length
                    ? rules
                    : [
                        {type: 'street-range'},
                        {type: 'street'},
                    ];

                editor.replaceChildren();

                source.forEach((rule) => {
                    const row = createAddressRow();
                    editor.appendChild(row);

                    const type = row.querySelector('.js-address-type');
                    const address = row.querySelector('.js-address-value');
                    const fias = row.querySelector('.js-address-fias');
                    const detail = row.querySelector('.js-address-detail');

                    if (type) {
                        type.value = rule.type || 'street-range';
                    }

                    if (address) {
                        address.value = rule.address || '';
                        address.dataset.selectedAddress = rule.address || '';
                        address.dataset.fiasId = rule.fias_id || '';
                    }

                    if (fias) {
                        fias.value = rule.fias_id || '';
                    }

                    syncAddressRow(row);

                    if (detail && !detail.disabled) {
                        detail.value = rule.detail || '';
                    }
                });

                reindexAddressRows();
            }

            function clearDistrictErrors() {
                if (!districtFormErrors) {
                    return;
                }

                districtFormErrors.classList.remove('is-visible');
                districtFormErrors.replaceChildren();
            }

            function showDistrictErrors(errors) {
                if (!districtFormErrors) {
                    return;
                }

                const messages = [];

                Object.values(errors || {}).forEach((value) => {
                    if (Array.isArray(value)) {
                        messages.push(...value);
                    } else if (value) {
                        messages.push(String(value));
                    }
                });

                if (!messages.length) {
                    messages.push('Не удалось сохранить участок.');
                }

                const list = document.createElement('ul');
                list.className = 'mb-0 ps-3';

                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    list.appendChild(item);
                });

                districtFormErrors.replaceChildren(list);
                districtFormErrors.classList.add('is-visible');
            }

            editModal?.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                const mode = trigger?.dataset.districtMode || 'create';
                const title = document.getElementById(
                    'districtEditModalLabel'
                );
                const name = document.getElementById('districtName');
                const code = document.getElementById('districtCode');
                const description = document.getElementById(
                    'districtDescription'
                );

                clearDistrictErrors();
                currentDistrictId = mode === 'edit'
                    ? String(trigger?.dataset.districtId || '')
                    : '';

                if (districtId) {
                    districtId.value = currentDistrictId;
                }

                if (mode === 'edit') {
                    title.textContent = 'Редактирование участка';
                    name.value = trigger?.dataset.districtName || '';
                    code.value = trigger?.dataset.districtCode || '';
                    description.value =
                        trigger?.dataset.districtDescription || '';

                    setPickerDoctor(
                        'main',
                        trigger?.dataset.districtDoctorId || '',
                        trigger?.dataset.districtDoctor || ''
                    );
                    setPickerDoctor(
                        'home',
                        trigger?.dataset.districtHomeDoctorId || '',
                        trigger?.dataset.districtHomeDoctor || ''
                    );

                    let rules = [];
                    try {
                        rules = JSON.parse(
                            trigger?.dataset.districtRules || '[]'
                        );
                    } catch (error) {
                        rules = [];
                    }
                    setAddressRows(rules);
                } else {
                    title.textContent = 'Новый участок';
                    name.value = '';
                    code.value = '';
                    description.value = '';
                    setPickerDoctor('main', '', '');
                    setPickerDoctor('home', '', '');
                    setAddressRows([]);
                }

                filterPicker('main');
                filterPicker('home');
            });

            districtForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearDistrictErrors();

                if (!pickers.main.input?.value) {
                    showDistrictErrors({
                        doctor_id: ['Выберите участкового психиатра.'],
                    });
                    pickers.main.search?.focus();
                    pickers.main.picker?.classList.add('is-open');
                    return;
                }

                if (!pickers.home.input?.value) {
                    showDistrictErrors({
                        home_visit_doctor_id: [
                            'Выберите психиатра для вызовов на дом.',
                        ],
                    });
                    pickers.home.search?.focus();
                    pickers.home.picker?.classList.add('is-open');
                    return;
                }

                if (
                    pickers.main.input.value
                    === pickers.home.input.value
                ) {
                    showDistrictErrors({
                        doctors: [
                            'Участковый и выездной психиатр должны быть разными врачами.',
                        ],
                    });
                    return;
                }

                const rows = Array.from(
                    document.querySelectorAll(
                        '#districtAddressEditor [data-address-row]'
                    )
                );

                for (const row of rows) {
                    const valid = await validateHouseField(row, true);
                    if (!valid) {
                        row.querySelector(
                            '.js-address-detail:not([disabled])'
                        )?.focus();
                        return;
                    }
                }

                const submitButton = districtForm.querySelector(
                    'button[type="submit"]'
                );
                const formData = new FormData(districtForm);
                const editing = currentDistrictId !== '';
                let url = editing
                    ? String(
                        districtForm.dataset.updateUrlTemplate || ''
                    ).replace(
                        '__DISTRICT__',
                        encodeURIComponent(currentDistrictId)
                    )
                    : String(districtForm.dataset.storeUrl || '');

                if (editing) {
                    formData.set('_method', 'PUT');
                }

                submitButton?.setAttribute('disabled', 'disabled');

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        showDistrictErrors(
                            payload.errors || {
                                form: [
                                    payload.message
                                    || 'Не удалось сохранить участок.',
                                ],
                            }
                        );
                        return;
                    }

                    window.location.reload();
                } catch (error) {
                    showDistrictErrors({
                        form: ['Ошибка соединения при сохранении участка.'],
                    });
                } finally {
                    submitButton?.removeAttribute('disabled');
                }
            });

            const transferModal = document.getElementById('districtTransferModal');
            const transferForm = document.getElementById('districtTransferForm');
            const transferErrors = document.getElementById('transferFormErrors');
            const transferSubmitButton = document.getElementById('transferSubmitButton');
            const transferDateFrom = document.getElementById('transferDateFrom');
            const transferDateTo = document.getElementById('transferDateTo');
            const transferReason = document.getElementById('transferReason');
            const transferState = {
                districtId: '',
                baseDoctorId: '',
                action: '',
            };
            const transferPicker = {
                picker: document.getElementById('transferDoctorPicker'),
                input: document.getElementById('transferDoctor'),
                search: document.getElementById('transferDoctorSearch'),
                clear: document.getElementById('transferDoctorClear'),
                selected: document.getElementById('transferDoctorSelected'),
                options: Array.from(document.querySelectorAll('[data-transfer-doctor-option]')),
            };

            function transferDoctorAllowed(option) {
                const optionId = String(option?.dataset?.doctorId || '').trim();
                const targetDistrictId = String(option?.dataset?.doctorDistrictId || '').trim();
                const assignedRole = String(option?.dataset?.doctorAssignedRole || '').trim();

                if (!optionId || !targetDistrictId || assignedRole !== 'main') {
                    return false;
                }

                if (
                    transferState.baseDoctorId
                    && optionId === transferState.baseDoctorId
                ) {
                    return false;
                }

                if (
                    transferState.districtId
                    && targetDistrictId === transferState.districtId
                ) {
                    return false;
                }

                return true;
            }

            function updateTransferSelection(id = '', name = '') {
                const doctorId = String(id || '').trim();
                const doctorName = String(name || '').trim();

                if (transferPicker.input) {
                    transferPicker.input.value = doctorId;
                }

                if (transferPicker.search) {
                    transferPicker.search.value = doctorName;
                }

                transferPicker.picker?.classList.toggle('has-value', doctorName !== '');
                transferPicker.picker?.classList.toggle('has-selection', doctorId !== '');

                const selectedOption = transferPicker.options.find(
                    (option) => String(option.dataset.doctorId || '') === doctorId
                );

                if (transferPicker.selected) {
                    const label = transferPicker.selected.querySelector('span');
                    const districtName = String(
                        selectedOption?.dataset.doctorDistrictName || ''
                    ).trim();
                    const districtCode = String(
                        selectedOption?.dataset.doctorDistrictCode || ''
                    ).trim();

                    if (label) {
                        label.textContent = doctorId
                            ? `${doctorName} · ${districtName}${districtCode ? ' · код ' + districtCode : ''}`
                            : '';
                    }
                }

                transferPicker.options.forEach((option) => {
                    option.classList.toggle(
                        'is-selected',
                        doctorId !== ''
                        && String(option.dataset.doctorId || '') === doctorId
                    );
                });
            }

            function filterTransferPicker() {
                if (!transferPicker.picker) {
                    return;
                }

                const query = String(transferPicker.search?.value || '')
                    .trim()
                    .toLowerCase();
                let visible = 0;

                transferPicker.options.forEach((option) => {
                    const haystack = [
                        option.dataset.doctorName,
                        option.dataset.doctorProfession,
                        option.dataset.doctorSpeciality,
                        option.dataset.doctorDistrictName,
                        option.dataset.doctorDistrictCode,
                    ]
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();

                    const show = transferDoctorAllowed(option)
                        && (query === '' || haystack.includes(query));

                    option.classList.toggle('d-none', !show);

                    if (show) {
                        visible += 1;
                    }
                });

                transferPicker.picker.classList.toggle('no-results', visible === 0);
            }

            function clearTransferErrors() {
                if (!transferErrors) {
                    return;
                }

                transferErrors.replaceChildren();
                transferErrors.classList.remove('is-visible');
            }

            function showTransferErrors(payload) {
                if (!transferErrors) {
                    return;
                }

                const messages = [];
                const errors = payload?.errors || {};

                Object.values(errors).forEach((value) => {
                    (Array.isArray(value) ? value : [value]).forEach((message) => {
                        if (message) {
                            messages.push(String(message));
                        }
                    });
                });

                if (!messages.length && payload?.message) {
                    messages.push(String(payload.message));
                }

                if (!messages.length) {
                    messages.push('Не удалось выполнить передачу участка.');
                }

                const list = document.createElement('ul');
                list.className = 'mb-0 ps-3';

                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    list.appendChild(item);
                });

                transferErrors.replaceChildren(list);
                transferErrors.classList.add('is-visible');
            }

            function formatLocalDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function setDefaultTransferDates() {
                const today = new Date();
                const until = new Date(today);
                until.setDate(until.getDate() + 14);

                if (transferDateFrom) {
                    transferDateFrom.value = formatLocalDate(today);
                }

                if (transferDateTo) {
                    transferDateTo.value = formatLocalDate(until);
                    transferDateTo.min = formatLocalDate(today);
                }
            }

            function resetTransferPicker() {
                updateTransferSelection('', '');
                filterTransferPicker();
                transferPicker.picker?.classList.remove('is-open');
                clearTransferErrors();

                if (transferReason) {
                    transferReason.value = '';
                }

                setDefaultTransferDates();
            }

            function bindTransferPicker() {
                transferPicker.search?.addEventListener('focus', () => {
                    filterTransferPicker();
                    transferPicker.picker?.classList.add('is-open');
                });

                transferPicker.search?.addEventListener('input', () => {
                    const current = String(transferPicker.search.value || '').trim();
                    transferPicker.picker?.classList.toggle('has-value', current !== '');

                    if (transferPicker.input?.value) {
                        const selected = transferPicker.options.find(
                            (option) => String(option.dataset.doctorId || '') === transferPicker.input.value
                        );

                        if (!selected || String(selected.dataset.doctorName || '') !== current) {
                            transferPicker.input.value = '';
                            transferPicker.picker?.classList.remove('has-selection');
                        }
                    }

                    filterTransferPicker();
                    transferPicker.picker?.classList.add('is-open');
                });

                transferPicker.options.forEach((option) => {
                    option.addEventListener('click', () => {
                        if (!transferDoctorAllowed(option)) {
                            return;
                        }

                        updateTransferSelection(
                            option.dataset.doctorId || '',
                            option.dataset.doctorName || ''
                        );
                        filterTransferPicker();
                        transferPicker.picker?.classList.remove('is-open');
                    });
                });

                transferPicker.clear?.addEventListener('click', () => {
                    updateTransferSelection('', '');
                    filterTransferPicker();
                    transferPicker.picker?.classList.add('is-open');
                    transferPicker.search?.focus();
                });
            }

            bindTransferPicker();

            transferModal?.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                transferState.districtId = String(trigger?.dataset.districtId || '').trim();
                transferState.baseDoctorId = String(trigger?.dataset.transferDoctorId || '').trim();
                transferState.action = String(trigger?.dataset.transferUrl || '').trim();

                if (transferForm) {
                    transferForm.action = transferState.action;
                }

                document.getElementById('transferDistrictName').textContent = trigger?.dataset.transferDistrict || 'Участок';
                document.getElementById('transferBaseDoctor').textContent = trigger?.dataset.transferDoctor || '—';

                resetTransferPicker();
            });

            transferDateFrom?.addEventListener('change', () => {
                if (transferDateTo && transferDateFrom.value) {
                    transferDateTo.min = transferDateFrom.value;

                    if (
                        transferDateTo.value
                        && transferDateTo.value < transferDateFrom.value
                    ) {
                        transferDateTo.value = transferDateFrom.value;
                    }
                }
            });

            async function submitDistrictTransfer(event = null) {
                event?.preventDefault?.();
                clearTransferErrors();

                if (!transferPicker.input?.value) {
                    showTransferErrors({
                        errors: {
                            doctor_id: ['Выберите участкового психиатра другого участка.'],
                        },
                    });
                    filterTransferPicker();
                    transferPicker.picker?.classList.add('is-open');
                    transferPicker.search?.focus();
                    return;
                }

                if (!transferDateFrom?.value || !transferDateTo?.value) {
                    showTransferErrors({
                        errors: {
                            period: ['Укажите дату начала и дату окончания передачи.'],
                        },
                    });
                    return;
                }

                if (transferDateTo.value < transferDateFrom.value) {
                    showTransferErrors({
                        errors: {
                            period: ['Дата окончания не может быть раньше даты начала.'],
                        },
                    });
                    transferDateTo.focus();
                    return;
                }

                if (!transferState.action) {
                    showTransferErrors({
                        message: 'Не удалось определить участок для передачи.',
                    });
                    return;
                }

                transferSubmitButton?.setAttribute('disabled', 'disabled');

                try {
                    const response = await fetch(
                        transferState.action,
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(transferForm),
                        }
                    );

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        showTransferErrors(payload);
                        return;
                    }

                    try {
                        sessionStorage.setItem(
                            'districtNotice',
                            JSON.stringify({
                                type: 'success',
                                message: payload.message || 'Участок успешно передан.',
                            })
                        );
                    } catch (storageError) {
                        // sessionStorage может быть недоступен в некоторых режимах браузера.
                    }

                    window.location.reload();
                } catch (error) {
                    showTransferErrors({
                        message: 'Ошибка соединения при передаче участка.',
                    });
                } finally {
                    transferSubmitButton?.removeAttribute('disabled');
                }
            }

            transferSubmitButton?.addEventListener('click', submitDistrictTransfer);
            transferForm?.addEventListener('submit', submitDistrictTransfer);

            document.addEventListener('click', async (event) => {
                const separateButton = event.target.closest('[data-separate-district]');

                if (separateButton) {
                    event.preventDefault();

                    const sourceName = separateButton.dataset.separateName || 'Участок';
                    const targetName = separateButton.dataset.separateTarget || 'целевой участок';
                    const url = separateButton.dataset.separateUrl || '';

                    if (!url) {
                        return;
                    }

                    if (!window.confirm(
                        `Сепаратировать «${sourceName}» и «${targetName}»? Участок снова начнёт обслуживаться самостоятельно.`
                    )) {
                        return;
                    }

                    separateButton.setAttribute('disabled', 'disabled');

                    try {
                        const token = transferForm?.querySelector('[name="_token"]')?.value || '';
                        const body = new FormData();
                        body.append('_token', token);

                        const response = await fetch(
                            url,
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body,
                            }
                        );

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            window.alert(
                                payload.message
                                || Object.values(payload.errors || {})
                                    .flat()
                                    .join('\n')
                                || 'Не удалось сепаратировать участки.'
                            );
                            return;
                        }

                        window.location.reload();
                    } catch (error) {
                        window.alert('Ошибка соединения при сепарации участков.');
                    } finally {
                        separateButton.removeAttribute('disabled');
                    }

                    return;
                }

                if (
                    transferPicker.picker
                    && !transferPicker.picker.contains(event.target)
                ) {
                    transferPicker.picker.classList.remove('is-open');
                }
            });

            const deleteModal = document.getElementById('districtDeleteModal');
            const deleteForm = document.getElementById('districtDeleteForm');
            const deleteErrors = document.getElementById('deleteDistrictErrors');
            const deleteSubmit = document.getElementById('deleteDistrictSubmit');
            const deleteSourceId = document.getElementById('deleteSourceDistrictId');
            let deleteAction = '';

            function clearDeleteErrors() {
                deleteErrors?.classList.remove('is-visible');
                deleteErrors?.replaceChildren();
            }

            function showDeleteErrors(payload) {
                if (!deleteErrors) {
                    return;
                }

                const messages = [];
                Object.values(payload?.errors || {}).forEach((value) => {
                    (Array.isArray(value) ? value : [value]).forEach((message) => {
                        if (message) {
                            messages.push(String(message));
                        }
                    });
                });

                if (!messages.length && payload?.message) {
                    messages.push(String(payload.message));
                }

                if (!messages.length) {
                    messages.push('Не удалось удалить участок.');
                }

                const list = document.createElement('ul');
                list.className = 'mb-0 ps-3';
                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    list.appendChild(item);
                });
                deleteErrors.replaceChildren(list);
                deleteErrors.classList.add('is-visible');
            }

            deleteModal?.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                const sourceId = String(trigger?.dataset.deleteDistrictId || '').trim();
                deleteAction = String(trigger?.dataset.deleteUrl || '').trim();

                if (deleteForm) {
                    deleteForm.action = deleteAction;
                }

                if (deleteSourceId) {
                    deleteSourceId.value = sourceId;
                }

                document.getElementById('deleteDistrictName').textContent =
                    `${trigger?.dataset.deleteDistrictName || 'Участок'}${trigger?.dataset.deleteDistrictCode ? ' · код ' + trigger.dataset.deleteDistrictCode : ''}`;
                document.getElementById('deleteDistrictDoctor').textContent = trigger?.dataset.deleteDoctor || '—';
                document.getElementById('deleteDistrictHomeDoctor').textContent = trigger?.dataset.deleteHomeDoctor || '—';

                let additional = [];
                try {
                    additional = JSON.parse(trigger?.dataset.deleteAdditionalDoctors || '[]');
                } catch (error) {
                    additional = [];
                }

                const additionalRow = document.getElementById('deleteDistrictAdditionalRow');
                const additionalText = document.getElementById('deleteDistrictAdditionalDoctors');
                if (additional.length) {
                    additionalRow?.classList.remove('d-none');
                    if (additionalText) {
                        additionalText.textContent = additional
                            .map((doctor) => doctor.name)
                            .filter(Boolean)
                            .join(', ');
                    }
                } else {
                    additionalRow?.classList.add('d-none');
                    if (additionalText) {
                        additionalText.textContent = '—';
                    }
                }

                clearDeleteErrors();
            });

            deleteForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearDeleteErrors();

                if (!deleteAction) {
                    showDeleteErrors({message: 'Не удалось определить удаляемый участок.'});
                    return;
                }

                deleteSubmit?.setAttribute('disabled', 'disabled');

                try {
                    const formData = new FormData(deleteForm);
                    formData.set('_method', 'POST');

                    const response = await fetch(deleteAction, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        showDeleteErrors(payload);
                        return;
                    }

                    try {
                        sessionStorage.setItem(
                            'districtNotice',
                            JSON.stringify({
                                type: 'success',
                                message: payload.message || 'Участок удалён.',
                            })
                        );
                    } catch (storageError) {
                        // Уведомление просто не переживёт reload.
                    }

                    window.location.reload();
                } catch (error) {
                    showDeleteErrors({message: 'Ошибка соединения при удалении участка.'});
                } finally {
                    deleteSubmit?.removeAttribute('disabled');
                }
            });

            const editor = document.getElementById('districtAddressEditor');
            const addAddress = document.getElementById('addDistrictAddress');

            const addressSearchUrl = String(
                districtForm?.dataset.addressSearchUrl || ''
            ).trim();
            const addressRequestTimers = new WeakMap();
            const houseRequestTimers = new WeakMap();

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function normalizeAddressSuggestions(payload) {
                const raw = Array.isArray(payload)
                    ? payload
                    : (Array.isArray(payload?.suggestions) ? payload.suggestions : []);

                return raw.map((item) => ({
                    value: String(item?.value || item?.unrestricted_value || '').trim(),
                    city: String(item?.city || '').trim(),
                    street: String(item?.street || '').trim(),
                    house: String(item?.house || '').trim(),
                    fiasId: String(item?.fias_id || '').trim(),
                    cityFiasId: String(item?.city_fias_id || '').trim(),
                    settlementFiasId: String(item?.settlement_fias_id || '').trim(),
                    streetFiasId: String(item?.street_fias_id || '').trim(),
                }));
            }

            async function fetchAddressSuggestions(query) {
                if (!addressSearchUrl || String(query || '').trim().length < 2) {
                    return [];
                }

                const separator = addressSearchUrl.includes('?') ? '&' : '?';
                const response = await fetch(
                    addressSearchUrl
                    + separator
                    + 'query=' + encodeURIComponent(String(query).trim()),
                    {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );

                if (!response.ok) {
                    return [];
                }

                return normalizeAddressSuggestions(await response.json());
            }

            function hideSuggestionBox(box) {
                if (!box) {
                    return;
                }

                box.classList.remove('is-visible');
                box.replaceChildren();
            }

            function buildStreetLabel(item) {
                return [item.city, item.street]
                    .filter(Boolean)
                    .join(', ')
                    .trim();
            }

            function renderAddressSuggestions(row, items) {
                const type = row.querySelector('.js-address-type')?.value || 'street-range';
                const box = row.querySelector('.js-address-suggestions');
                const field = row.querySelector('.js-address-value');
                const fiasField = row.querySelector('.js-address-fias');

                if (!box || !field) {
                    return;
                }

                const unique = new Map();

                items.forEach((item) => {
                    let label = '';

                    if (type === 'settlement') {
                        label = item.city;
                    } else if (item.street) {
                        label = buildStreetLabel(item);
                    }

                    if (!label) {
                        return;
                    }

                    const key = label.toLowerCase();
                    if (!unique.has(key)) {
                        unique.set(key, { ...item, label });
                    }
                });

                const suggestions = Array.from(unique.values()).slice(0, 8);

                if (!suggestions.length) {
                    hideSuggestionBox(box);
                    return;
                }

                box.innerHTML = suggestions.map((item, index) => `
                    <button type="button"
                            class="district-address-suggestion"
                            data-address-index="${index}">
                        ${escapeHtml(item.label)}
                        <span class="district-address-suggestion__meta">
                            ${type === 'settlement' ? 'Населённый пункт' : 'Улица'}
                        </span>
                    </button>
                `).join('');

                box.classList.add('is-visible');

                box.querySelectorAll('[data-address-index]').forEach((button) => {
                    button.addEventListener('mousedown', (event) => event.preventDefault());
                    button.addEventListener('click', () => {
                        const item = suggestions[Number(button.dataset.addressIndex)];

                        const selectedFiasId = type === 'settlement'
                            ? (
                                item.settlementFiasId
                                || item.cityFiasId
                                || item.fiasId
                            )
                            : (item.streetFiasId || item.fiasId);

                        field.value = item.label;
                        field.dataset.selectedAddress = item.label;
                        field.dataset.fiasId = selectedFiasId;
                        field.classList.remove('is-invalid');

                        if (fiasField) {
                            fiasField.value = selectedFiasId;
                        }

                        hideSuggestionBox(box);
                        syncAddressRow(row);

                        const detail = row.querySelector('.js-address-detail');
                        if (detail && !detail.disabled) {
                            detail.focus();
                        }
                    });
                });
            }

            function scheduleAddressLookup(row) {
                const field = row.querySelector('.js-address-value');

                if (!field) {
                    return;
                }

                const oldTimer = addressRequestTimers.get(field);
                if (oldTimer) {
                    window.clearTimeout(oldTimer);
                }

                addressRequestTimers.set(field, window.setTimeout(async () => {
                    const query = String(field.value || '').trim();
                    const items = await fetchAddressSuggestions(query);
                    renderAddressSuggestions(row, items);
                }, 250));
            }

            function renderHouseSuggestions(row, houses) {
                const box = row.querySelector('.js-house-suggestions');
                const field = row.querySelector('.js-address-detail');

                if (!box || !field) {
                    return;
                }

                const unique = Array.from(new Set(houses.filter(Boolean))).slice(0, 12);

                if (!unique.length) {
                    hideSuggestionBox(box);
                    return;
                }

                box.innerHTML = unique.map((house, index) => `
                    <button type="button"
                            class="district-address-suggestion"
                            data-house-index="${index}">
                        Дом ${escapeHtml(house)}
                    </button>
                `).join('');
                box.classList.add('is-visible');

                box.querySelectorAll('[data-house-index]').forEach((button) => {
                    button.addEventListener('mousedown', (event) => event.preventDefault());
                    button.addEventListener('click', () => {
                        field.value = unique[Number(button.dataset.houseIndex)] || '';
                        hideSuggestionBox(box);
                        setHouseStatus(row, true, 'Дом найден на выбранной улице');
                    });
                });
            }

            async function fetchHouses(row, token) {
                const address = String(
                    row.querySelector('.js-address-value')?.dataset.selectedAddress
                    || row.querySelector('.js-address-value')?.value
                    || ''
                ).trim();

                token = String(token || '').trim();

                if (!address || !token) {
                    return [];
                }

                const items = await fetchAddressSuggestions(`${address}, ${token}`);
                const normalizedAddress = address.toLowerCase();

                return items
                    .filter((item) => {
                        const label = buildStreetLabel(item).toLowerCase();
                        return item.house && label === normalizedAddress;
                    })
                    .map((item) => item.house);
            }

            function scheduleHouseLookup(row) {
                const field = row.querySelector('.js-address-detail');

                if (!field || field.disabled) {
                    return;
                }

                const raw = String(field.value || '').trim();
                const token = raw.split(/[-–,]/)[0].trim();

                const oldTimer = houseRequestTimers.get(field);
                if (oldTimer) {
                    window.clearTimeout(oldTimer);
                }

                houseRequestTimers.set(field, window.setTimeout(async () => {
                    if (!token) {
                        hideSuggestionBox(row.querySelector('.js-house-suggestions'));
                        return;
                    }

                    renderHouseSuggestions(row, await fetchHouses(row, token));
                }, 250));
            }

            function normalizeHouse(value) {
                return String(value || '')
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, '')
                    .replace(/^д(?:ом)?\.?/u, '');
            }

            async function houseExists(row, house) {
                const houses = await fetchHouses(row, house);
                const wanted = normalizeHouse(house);

                return houses.some((candidate) => normalizeHouse(candidate) === wanted);
            }

            function setHouseStatus(row, valid, message) {
                const field = row.querySelector('.js-address-detail');
                const status = row.querySelector('.js-house-status');

                field?.classList.toggle('is-invalid', valid === false);

                if (status) {
                    status.textContent = message || '';
                    status.classList.toggle('is-valid', valid === true);
                    status.classList.toggle('is-invalid', valid === false);
                }
            }

            async function validateHouseField(row, silent = false) {
                const type = row.querySelector('.js-address-type')?.value || '';
                const address = row.querySelector('.js-address-value');
                const detail = row.querySelector('.js-address-detail');

                if (!address || !detail) {
                    return true;
                }

                if (!address.dataset.selectedAddress) {
                    address.classList.add('is-invalid');
                    setHouseStatus(
                        row,
                        false,
                        type === 'settlement'
                            ? 'Выберите населённый пункт из подсказки'
                            : 'Выберите улицу из подсказки'
                    );
                    return false;
                }

                if (['street', 'settlement'].includes(type)) {
                    setHouseStatus(row, null, '');
                    return true;
                }

                const raw = String(detail.value || '').trim();

                if (!raw) {
                    setHouseStatus(row, false, 'Укажите дом или диапазон домов');
                    return false;
                }

                const base = raw.split(',')[0].trim();
                const range = base.match(/^(.+?)\s*[-–]\s*(.+)$/u);

                if (type === 'exact' && range) {
                    setHouseStatus(row, false, 'Для конкретного адреса укажите один дом');
                    return false;
                }

                if (range) {
                    const from = range[1].trim();
                    const to = range[2].trim();
                    const [fromExists, toExists] = await Promise.all([
                        houseExists(row, from),
                        houseExists(row, to),
                    ]);

                    if (!fromExists || !toExists) {
                        const missing = [
                            !fromExists ? from : null,
                            !toExists ? to : null,
                        ].filter(Boolean).join(', ');
                        setHouseStatus(row, false, `На улице не найден дом: ${missing}`);
                        return false;
                    }

                    if (!silent) {
                        setHouseStatus(row, true, `Диапазон ${from}-${to}: граничные дома найдены`);
                    } else {
                        setHouseStatus(row, true, '');
                    }

                    return true;
                }

                const exists = await houseExists(row, base);

                if (!exists) {
                    setHouseStatus(row, false, `Дом ${base} не найден на выбранной улице`);
                    return false;
                }

                setHouseStatus(row, true, silent ? '' : 'Дом найден на выбранной улице');
                return true;
            }

            function syncAddressRow(row) {
                const type = row.querySelector('.js-address-type');
                const detail = row.querySelector('.js-address-detail');
                const address = row.querySelector('.js-address-value');
                const fias = row.querySelector('.js-address-fias');

                if (!type || !detail || !address) {
                    return;
                }

                const noDetail = ['street', 'settlement'].includes(type.value);
                const hasAddressText = String(address.value || '').trim() !== '';
                const hasConfirmedAddress = Boolean(
                    address.dataset.selectedAddress
                    || String(fias?.value || '').trim()
                );

                detail.disabled = noDetail || !hasAddressText;
                detail.placeholder = noDetail
                    ? 'Не требуется'
                    : (type.value === 'exact' ? 'Например: 7' : 'Например: 1-10');

                detail.dataset.addressConfirmed = hasConfirmedAddress ? '1' : '0';

                if (noDetail) {
                    detail.value = '';
                    setHouseStatus(row, null, '');
                }
            }

            function reindexAddressRows() {
                editor?.querySelectorAll('[data-address-row]').forEach((row, index) => {
                    const number = row.querySelector('.district-rule-number');
                    const type = row.querySelector('.js-address-type');
                    const address = row.querySelector('.js-address-value');
                    const fias = row.querySelector('.js-address-fias');
                    const detail = row.querySelector('.js-address-detail');

                    if (number) {
                        number.textContent = String(index + 1);
                    }

                    if (type) {
                        type.name = `addresses[${index}][type]`;
                    }

                    if (address) {
                        address.name = `addresses[${index}][address]`;
                    }

                    if (fias) {
                        fias.name = `addresses[${index}][fias_id]`;
                    }

                    if (detail) {
                        detail.name = `addresses[${index}][detail]`;
                    }
                });
            }

            function createAddressRow() {
                const wrapper = document.createElement('div');
                wrapper.className = 'district-address-row';
                wrapper.dataset.addressRow = '';
                wrapper.innerHTML = `
                    <div class="district-rule-label"><i class="bi bi-geo-alt"></i> Правило <span class="district-rule-number"></span></div>
                    <div class="district-address-type">
                        <label class="settings-label">Тип</label>
                        <select class="settings-control js-address-type">
                            <option value="street-range">Диапазон домов</option>
                            <option value="street">Улица целиком</option>
                            <option value="settlement">Населённый пункт</option>
                            <option value="exact">Конкретный адрес</option>
                        </select>
                    </div>
                    <div class="district-address-row__details">
                        <div class="district-address-picker">
                            <label class="settings-label">Адрес</label>
                            <input type="text"
                                   class="settings-control js-address-value"
                                   placeholder="Начните вводить город и улицу"
                                   autocomplete="off">
                            <input type="hidden" class="js-address-fias" value="">
                            <div class="district-address-suggestions js-address-suggestions"></div>
                        </div>
                        <div class="district-house-picker">
                            <label class="settings-label">Дома</label>
                            <input type="text"
                                   class="settings-control js-address-detail"
                                   placeholder="Например: 1-10"
                                   disabled
                                   autocomplete="off">
                            <div class="district-address-suggestions js-house-suggestions"></div>
                            <div class="district-house-status js-house-status"></div>
                        </div>
                    </div>
                    <button type="button" class="district-remove-address js-remove-address" title="Удалить правило" aria-label="Удалить правило">
                        <i class="bi bi-trash"></i>
                    </button>
                `;

                return wrapper;
            }

            addAddress?.addEventListener('click', () => {
                const row = createAddressRow();
                editor?.appendChild(row);
                syncAddressRow(row);
                reindexAddressRows();
            });

            editor?.addEventListener('input', (event) => {
                const row = event.target.closest('[data-address-row]');

                if (!row) {
                    return;
                }

                if (event.target.classList.contains('js-address-value')) {
                    event.target.dataset.selectedAddress = '';
                    event.target.dataset.fiasId = '';
                    event.target.classList.remove('is-invalid');
                    row.querySelector('.js-address-fias').value = '';
                    const detail = row.querySelector('.js-address-detail');
                    if (detail) {
                        detail.value = '';
                    }
                    setHouseStatus(row, null, '');
                    syncAddressRow(row);
                    scheduleAddressLookup(row);
                }

                if (event.target.classList.contains('js-address-detail')) {
                    setHouseStatus(row, null, '');
                    scheduleHouseLookup(row);
                }
            });

            editor?.addEventListener('change', (event) => {
                if (event.target.classList.contains('js-address-type')) {
                    const row = event.target.closest('[data-address-row]');
                    const address = row?.querySelector('.js-address-value');

                    if (address) {
                        address.value = '';
                        address.dataset.selectedAddress = '';
                        address.dataset.fiasId = '';
                    }

                    const fias = row?.querySelector('.js-address-fias');
                    if (fias) {
                        fias.value = '';
                    }

                    syncAddressRow(row);
                }
            });

            editor?.addEventListener('focusout', (event) => {
                if (!event.target.classList.contains('js-address-detail')) {
                    return;
                }

                const row = event.target.closest('[data-address-row]');
                window.setTimeout(() => validateHouseField(row), 120);
            });

            editor?.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.js-remove-address');

                if (!removeButton) {
                    return;
                }

                const rows = editor.querySelectorAll('[data-address-row]');

                if (rows.length <= 1) {
                    return;
                }

                removeButton.closest('[data-address-row]')?.remove();
                reindexAddressRows();
            });

            document.addEventListener('click', (event) => {
                document.querySelectorAll('.district-address-suggestions.is-visible')
                    .forEach((box) => {
                        if (!box.parentElement?.contains(event.target)) {
                            hideSuggestionBox(box);
                        }
                    });
            });

            editor?.querySelectorAll('[data-address-row]').forEach(syncAddressRow);
            reindexAddressRows();
        })();
    </script>
@endpush
