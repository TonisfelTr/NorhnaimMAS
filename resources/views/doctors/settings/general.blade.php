@extends('doctors.settings')

@section('settings-title', 'Настройки клиники')

@section('assets')
    @parent

    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ env('YANDEX_MAPS_API_KEY') }}&lang=ru_RU"
            type="text/javascript"></script>

    <style>
        .clinic-settings-page .settings-container {
            width: min(1540px, 100%);
        }

        .clinic-settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(370px, .85fr);
            gap: 18px;
            align-items: start;
        }

        .clinic-settings-stack {
            display: grid;
            gap: 18px;
        }

        .clinic-settings-section-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: linear-gradient(135deg, #e8f8fa, #f8feff);
            color: var(--settings-primary-dark);
            font-size: 18px;
        }

        .clinic-settings-panel-head {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .clinic-type-option {
            position: relative;
            display: block;
            margin: 0;
            cursor: pointer;
        }

        .clinic-type-option > input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .clinic-type-card {
            display: flex;
            min-height: 104px;
            align-items: flex-start;
            gap: 13px;
            padding: 16px;
            border: 1px solid #dce6eb;
            border-radius: 16px;
            background: #fff;
            transition:
                border-color .18s ease,
                background .18s ease,
                box-shadow .18s ease,
                transform .18s ease;
        }

        .clinic-type-card__icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: #f2f7f9;
            color: #667085;
            font-size: 18px;
            transition: background .18s ease, color .18s ease;
        }

        .clinic-type-card strong {
            display: block;
            color: #172033;
            font-size: 14px;
            line-height: 1.25;
        }

        .clinic-type-card p {
            margin: 5px 0 0;
            color: #7b8794;
            font-size: 12px;
            line-height: 1.45;
        }

        .clinic-type-option:hover .clinic-type-card {
            border-color: #b7dfe4;
            background: #fbfeff;
            transform: translateY(-1px);
        }

        .clinic-type-option > input:checked + .clinic-type-card {
            border-color: var(--settings-primary);
            background: linear-gradient(135deg, #effbfc, #ffffff);
            box-shadow: 0 0 0 3px rgba(16, 183, 198, .10);
        }

        .clinic-type-option > input:checked + .clinic-type-card .clinic-type-card__icon {
            background: linear-gradient(135deg, #dff7f9, #effcfd);
            color: var(--settings-primary-dark);
        }

        .clinic-type-option > input:focus-visible + .clinic-type-card {
            outline: 3px solid rgba(16, 183, 198, .18);
            outline-offset: 2px;
        }

        .clinic-inpatient-mode-options {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .clinic-type-card--compact {
            min-height: 92px;
            padding: 14px;
        }

        .clinic-type-card--compact .clinic-type-card__icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 12px;
            font-size: 16px;
        }

        .clinic-inpatient-schedule-block {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #edf1f4;
        }

        .clinic-inpatient-schedule-title {
            margin-bottom: 5px;
            color: #293544;
            font-size: 13px;
            font-weight: 850;
        }

        .clinic-inpatient-schedule-hint {
            margin-bottom: 12px;
            color: #7a8794;
            font-size: 11px;
            line-height: 1.5;
        }

        .clinic-round-clock-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 18px;
            padding: 13px 14px;
            border: 1px solid #d9eef1;
            border-radius: 13px;
            background: #f4fbfc;
            color: #49606a;
            font-size: 11px;
            line-height: 1.5;
        }

        .clinic-round-clock-note i {
            margin-top: 1px;
            color: var(--settings-primary-dark);
            font-size: 15px;
        }

        .clinic-settings-address {
            position: relative;
        }

        .clinic-address-input-wrap {
            position: relative;
        }

        .clinic-address-input-wrap > i {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: #8a96a5;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .clinic-address-input-wrap .settings-control {
            padding-left: 40px;
            padding-right: 40px;
        }

        .clinic-address-state {
            position: absolute;
            top: 50%;
            right: 13px;
            left: auto;
            display: none;
            color: var(--settings-success);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .clinic-address-state.is-visible {
            display: block;
        }

        .clinic-address-suggestions {
            position: absolute;
            z-index: 80;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            display: none;
            overflow: hidden;
            border: 1px solid #d8e2e9;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .14);
        }

        .clinic-address-suggestions.is-open {
            display: block;
        }

        .clinic-address-suggestion {
            display: flex;
            width: 100%;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 13px;
            border: 0;
            border-bottom: 1px solid #eef2f5;
            background: #fff;
            color: #344054;
            text-align: left;
            cursor: pointer;
        }

        .clinic-address-suggestion:last-child {
            border-bottom: 0;
        }

        .clinic-address-suggestion:hover {
            background: #f2fbfc;
        }

        .clinic-address-suggestion i {
            margin-top: 2px;
            color: var(--settings-primary);
        }

        .clinic-address-suggestion strong {
            display: block;
            color: #1f2937;
            font-size: 13px;
        }

        .clinic-address-suggestion span {
            display: block;
            margin-top: 2px;
            color: #7b8794;
            font-size: 11px;
        }

        .clinic-map-shell {
            position: relative;
            overflow: hidden;
            min-height: 310px;
            border: 1px solid #dce6eb;
            border-radius: 17px;
            background:
                radial-gradient(circle at 75% 25%, rgba(16, 183, 198, .18), transparent 30%),
                linear-gradient(135deg, #effafb, #f9fcfd);
        }

        .clinic-map {
            width: 100%;
            height: 310px;
        }

        .clinic-map-placeholder {
            position: absolute;
            inset: 0;
            z-index: 3;
            display: grid;
            place-items: center;
            padding: 26px;
            background:
                radial-gradient(circle at 70% 22%, rgba(16, 183, 198, .16), transparent 30%),
                linear-gradient(135deg, rgba(240, 250, 251, .97), rgba(255, 255, 255, .97));
            text-align: center;
        }

        .clinic-map-placeholder.is-hidden {
            display: none;
        }

        .clinic-map-placeholder.is-clickable {
            cursor: pointer;
            transition: background .18s ease, box-shadow .18s ease;
        }

        .clinic-map-placeholder.is-clickable:hover {
            background:
                radial-gradient(circle at 70% 22%, rgba(16, 183, 198, .22), transparent 30%),
                linear-gradient(135deg, rgba(234, 249, 251, .99), rgba(255, 255, 255, .99));
            box-shadow: inset 0 0 0 2px rgba(4, 134, 150, .08);
        }

        .clinic-map-placeholder.is-loading {
            cursor: progress;
        }

        .clinic-map-placeholder.is-error .clinic-map-placeholder__icon {
            background: #fff1f0;
            color: #b42318;
        }

        .clinic-map-placeholder__icon {
            display: grid;
            width: 58px;
            height: 58px;
            margin: 0 auto 11px;
            place-items: center;
            border-radius: 18px;
            background: #dff5f7;
            color: var(--settings-primary-dark);
            font-size: 25px;
        }

        .clinic-map-placeholder strong {
            color: #25313d;
            font-size: 15px;
        }

        .clinic-map-placeholder p {
            max-width: 390px;
            margin: 6px auto 0;
            color: #74808d;
            font-size: 12px;
            line-height: 1.5;
        }

        .clinic-address-confirmed {
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 9px;
            padding: 9px 11px;
            border: 1px solid #cde9df;
            border-radius: 11px;
            background: #f2fbf7;
            color: #246b50;
            font-size: 11px;
            font-weight: 700;
        }

        .clinic-address-confirmed.is-visible {
            display: flex;
        }

        .clinic-phone-list,
        .clinic-staff-list {
            display: grid;
            gap: 10px;
        }

        .clinic-staff-pagination {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
        }

        .clinic-staff-pagination.is-visible {
            display: flex;
        }

        .clinic-staff-pagination__button {
            display: inline-flex;
            min-width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            border: 1px solid #d7e1e7;
            border-radius: 10px;
            background: #fff;
            color: #556274;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            transition: .18s ease;
        }

        .clinic-staff-pagination__button:hover:not(:disabled),
        .clinic-staff-pagination__button.is-active {
            border-color: #8ed5de;
            background: #eef9fb;
            color: #087e8e;
        }

        .clinic-staff-pagination__button:disabled {
            opacity: .42;
            cursor: default;
        }

        .clinic-staff-card.is-pagination-hidden {
            display: none !important;
        }

        .clinic-phone-row {
            display: grid;
            grid-template-columns: minmax(160px, 1fr) minmax(180px, .9fr) auto;
            gap: 9px;
            align-items: center;
        }

        .clinic-icon-button {
            display: inline-grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border: 1px solid #d7e1e7;
            border-radius: 11px;
            background: #fff;
            color: #667085;
            cursor: pointer;
            transition: .15s ease;
        }

        .clinic-icon-button:hover {
            border-color: #c4d2da;
            background: #f7fafb;
            color: #1f2937;
        }

        .clinic-icon-button--danger:hover {
            border-color: #fecaca;
            background: #fff6f6;
            color: #b91c1c;
        }

        .clinic-inline-action {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            gap: 7px;
            margin-top: 11px;
            padding: 7px 11px;
            border: 1px dashed #b9cbd3;
            border-radius: 11px;
            background: #fbfdfe;
            color: var(--settings-primary-dark);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .clinic-inline-action:hover {
            border-color: var(--settings-primary);
            background: #f1fbfc;
        }

        .clinic-care-formats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .clinic-care-format {
            position: relative;
            display: flex;
            min-height: 105px;
            align-items: flex-start;
            gap: 11px;
            padding: 15px;
            border: 1px solid #dfe7ec;
            border-radius: 16px;
            background: #fff;
            cursor: pointer;
            transition: .16s ease;
        }

        .clinic-care-format:hover {
            border-color: #bed9de;
            background: #fbfeff;
        }

        .clinic-care-format:has(input:checked) {
            border-color: #9ed6dc;
            background: linear-gradient(135deg, #effafb, #fff);
            box-shadow: inset 0 0 0 1px rgba(4, 134, 150, .08);
        }

        .clinic-care-format input {
            margin-top: 3px;
        }

        .clinic-care-format__icon {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border-radius: 11px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 17px;
        }

        .clinic-care-format strong {
            display: block;
            color: #293544;
            font-size: 13px;
        }

        .clinic-care-format p {
            margin: 4px 0 0;
            color: #7a8794;
            font-size: 11px;
            line-height: 1.45;
        }

        .clinic-working-hours {
            display: grid;
            gap: 8px;
        }

        .clinic-working-day {
            display: grid;
            grid-template-columns: 42px 46px minmax(100px, 1fr) 18px minmax(100px, 1fr);
            gap: 9px;
            align-items: center;
            padding: 8px 10px;
            border: 1px solid #edf1f4;
            border-radius: 12px;
            background: #fbfcfd;
        }

        .clinic-working-day__name {
            color: #475467;
            font-size: 11px;
            font-weight: 850;
        }

        .clinic-working-day .form-check-input {
            margin: 0;
        }

        .clinic-working-day .settings-control {
            min-height: 38px;
            padding: 7px 9px;
            font-size: 12px;
        }

        .clinic-working-day.is-closed {
            opacity: .58;
        }

        .clinic-working-day.is-closed .settings-control {
            pointer-events: none;
            background: #f2f4f7;
        }

        /*
         * Для графика посещений родственниками время можно вводить
         * сразу, даже если день ещё не отмечен. При фокусе на поле
         * день автоматически включается JavaScript ниже.
         */
        .clinic-working-day[data-inpatient-visiting-day].is-closed {
            opacity: 1;
        }

        .clinic-working-day[data-inpatient-visiting-day].is-closed
        .settings-control {
            pointer-events: auto;
            background: #fff;
        }

        .clinic-working-day__separator {
            color: #98a2b3;
            text-align: center;
        }

        .clinic-staff-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
        }

        .clinic-staff-toolbar__summary {
            color: #7a8794;
            font-size: 11px;
        }

        .clinic-staff-card {
            display: grid;
            grid-template-columns: 44px minmax(0, 1.5fr) minmax(140px, .8fr) minmax(160px, .8fr) auto;
            gap: 12px;
            align-items: center;
            padding: 12px 13px;
            border: 1px solid #e5ebef;
            border-radius: 15px;
            background: #fff;
            transition: .15s ease;
        }

        .clinic-staff-card:hover {
            border-color: #d1e2e6;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .055);
        }

        .clinic-staff-avatar {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--settings-primary), var(--settings-primary-light));
            color: #fff;
            font-size: 12px;
            font-weight: 900;
        }

        .clinic-staff-name {
            color: #1f2937;
            font-size: 13px;
            font-weight: 850;
        }

        .clinic-staff-speciality,
        .clinic-staff-contact {
            margin-top: 3px;
            overflow: hidden;
            color: #7a8794;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .clinic-staff-speciality {
            color: #6f7f8d;
            font-weight: 600;
        }

        .clinic-staff-card__position {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .clinic-staff-position {
            display: inline-flex;
            width: fit-content;
            min-height: 27px;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }

        .clinic-staff-position--empty {
            background: #f3f6f8;
            color: #8a98a6;
        }

        .clinic-staff-status {
            display: inline-flex;
            min-height: 27px;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 999px;
            background: #edf9f1;
            color: #28754f;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .clinic-staff-status::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22a06b;
            content: '';
        }

        .clinic-staff-status.is-absent {
            background: #fff7e6;
            color: #a15c05;
        }

        .clinic-staff-status.is-absent::before {
            background: #f59e0b;
        }

        .clinic-staff-status.is-dismissed {
            background: #f2f4f7;
            color: #667085;
        }

        .clinic-staff-status.is-dismissed::before {
            background: #98a2b3;
        }

        .clinic-staff-card.is-dismissed {
            opacity: .72;
            background: #f9fafb;
        }

        .clinic-dismissal-field {
            display: none;
        }

        .clinic-dismissal-field.is-visible {
            display: block;
        }

        .clinic-staff-actions {
            display: flex;
            gap: 6px;
        }

        .clinic-save-bar {
            position: sticky;
            z-index: 40;
            bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 20px;
            padding: 13px 15px;
            border: 1px solid rgba(4, 134, 150, .18);
            border-radius: 16px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 40px rgba(15, 23, 42, .12);
            backdrop-filter: blur(10px);
        }

        .clinic-save-bar__status {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #667085;
            font-size: 11px;
        }

        .clinic-save-bar__status i {
            color: var(--settings-primary);
            font-size: 16px;
        }

        .clinic-staff-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
        }

        .clinic-staff-modal .modal-header {
            padding: 18px 21px;
            border-bottom: 1px solid #e8edf1;
            background: linear-gradient(135deg, #f2fbfc, #fff);
        }

        .clinic-staff-modal .modal-title {
            color: #1f2937;
            font-size: 19px;
            font-weight: 850;
        }

        .clinic-staff-modal .modal-body {
            padding: 21px;
        }

        .clinic-staff-modal .modal-footer {
            padding: 14px 21px;
            border-top: 1px solid #e8edf1;
            background: #fbfcfd;
        }

        .clinic-employee-profile {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 18px;
            padding: 14px;
            border: 1px solid #dce9ed;
            border-radius: 16px;
            background: linear-gradient(135deg, #f0fafb 0%, #ffffff 100%);
        }

        .clinic-employee-profile__avatar {
            display: grid;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--settings-primary), var(--settings-primary-light));
            color: #fff;
            font-size: 13px;
            font-weight: 900;
            box-shadow: 0 8px 18px rgba(4, 134, 150, .18);
        }

        .clinic-employee-profile__content {
            min-width: 0;
            flex: 1;
        }

        .clinic-employee-profile__name {
            overflow: hidden;
            color: #1f2937;
            font-size: 14px;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .clinic-employee-profile__meta {
            margin-top: 3px;
            overflow: hidden;
            color: #7a8794;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .clinic-employee-form-section {
            padding: 15px;
            border: 1px solid #e5ebef;
            border-radius: 16px;
            background: #fcfdfe;
        }

        .clinic-employee-form-section + .clinic-employee-form-section {
            margin-top: 13px;
        }

        .clinic-employee-form-section__head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 13px;
            color: #344054;
            font-size: 12px;
            font-weight: 900;
        }

        .clinic-employee-form-section__head i {
            color: var(--settings-primary-dark);
            font-size: 15px;
        }

        .clinic-employee-form .settings-label {
            margin-bottom: 6px;
        }

        .clinic-employee-form .settings-control {
            background: #fff;
        }

        .clinic-employee-form .settings-control:focus {
            border-color: #90cfd5;
            box-shadow: 0 0 0 3px rgba(4, 134, 150, .08);
        }

        .clinic-employee-form .clinic-speciality-tags {
            gap: 8px;
        }

        .clinic-employee-form .clinic-speciality-tag {
            min-height: 34px;
            padding: 6px 10px;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: .15s ease;
        }

        .clinic-employee-form .clinic-speciality-tag:has(input:checked) {
            border-color: #9ed6dc;
            background: #eefafb;
            color: var(--settings-primary-dark);
        }

        .clinic-employee-danger {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid #fecaca;
            border-radius: 11px;
            background: #fff8f8;
            color: #b42318;
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
            transition: .15s ease;
        }

        .clinic-employee-danger:hover {
            border-color: #fca5a5;
            background: #fff1f1;
            color: #991b1b;
        }

        .clinic-headmaster-transfer {
            border-color: #f4d49b;
            background: linear-gradient(135deg, #fffaf0 0%, #fffdf8 100%);
        }

        .clinic-headmaster-transfer .clinic-employee-form-section__head {
            color: #8a5a12;
        }

        .clinic-headmaster-transfer .clinic-employee-form-section__head i {
            color: #b7791f;
        }

        .clinic-headmaster-transfer__note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 13px;
            padding: 10px 11px;
            border: 1px solid #f1d59f;
            border-radius: 11px;
            background: rgba(255, 248, 231, .9);
            color: #78511a;
            font-size: 11px;
            line-height: 1.5;
        }

        .clinic-headmaster-transfer__note i {
            flex: 0 0 auto;
            margin-top: 1px;
            color: #b7791f;
            font-size: 15px;
        }

        .clinic-employee-feedback {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-bottom: 16px;
            padding: 12px 13px;
            border: 1px solid;
            border-radius: 13px;
        }

        .clinic-employee-feedback__icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 10px;
            font-size: 16px;
        }

        .clinic-employee-feedback__content {
            min-width: 0;
            flex: 1;
        }

        .clinic-employee-feedback__title {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.35;
        }

        .clinic-employee-feedback__text {
            margin-top: 3px;
            font-size: 11px;
            line-height: 1.5;
        }

        .clinic-employee-feedback__list {
            display: grid;
            gap: 3px;
            margin: 7px 0 0;
            padding-left: 17px;
            font-size: 11px;
            line-height: 1.45;
        }

        .clinic-employee-feedback--error {
            border-color: #fecaca;
            background: #fff7f7;
            color: #8f1d1d;
        }

        .clinic-employee-feedback--error .clinic-employee-feedback__icon {
            background: #fee2e2;
            color: #b91c1c;
        }

        .clinic-employee-feedback--success {
            border-color: #bbebd1;
            background: #f3fbf7;
            color: #216447;
        }

        .clinic-employee-feedback--success .clinic-employee-feedback__icon {
            background: #dcf6e8;
            color: #168151;
        }

        .clinic-speciality-tags {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .clinic-speciality-tag {
            display: inline-flex;
            min-height: 31px;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border: 1px solid #dce5ea;
            border-radius: 999px;
            background: #fff;
            color: #52606d;
            font-size: 10px;
            font-weight: 750;
        }

        .clinic-speciality-tag input {
            margin: 0;
        }

        .clinic-doctor-search {
            position: relative;
        }

        .clinic-doctor-search > i {
            position: absolute;
            top: 50%;
            left: 14px;
            color: #8a96a5;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .clinic-doctor-search .settings-control {
            padding-left: 40px;
        }

        .clinic-doctor-results {
            display: grid;
            max-height: 360px;
            gap: 8px;
            margin-top: 12px;
            overflow-y: auto;
        }

        .clinic-doctor-option {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: center;
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #e0e7ec;
            border-radius: 13px;
            background: #fff;
            color: inherit;
            text-align: left;
            cursor: pointer;
            transition: .15s ease;
        }

        .clinic-doctor-option:hover {
            border-color: #bcd8dd;
            background: #f8fdfe;
        }

        .clinic-doctor-option.is-selected {
            border-color: #8ecfd6;
            background: #eefafb;
            box-shadow: inset 0 0 0 1px rgba(4, 134, 150, .08);
        }

        .clinic-doctor-option__icon {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border-radius: 12px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 17px;
        }

        .clinic-doctor-option__name {
            color: #24303d;
            font-size: 13px;
            font-weight: 850;
        }

        .clinic-doctor-option__meta {
            margin-top: 3px;
            color: #7a8794;
            font-size: 10px;
            line-height: 1.45;
        }

        .clinic-doctor-option__status {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f2f4f7;
            color: #667085;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .clinic-doctor-selected {
            display: none;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            padding: 11px 12px;
            border: 1px solid #bce3d0;
            border-radius: 13px;
            background: #f3fbf7;
            color: #285f49;
        }

        .clinic-doctor-selected.is-visible {
            display: flex;
        }

        .clinic-doctor-selected i {
            font-size: 18px;
        }

        .clinic-doctor-selected strong {
            display: block;
            font-size: 12px;
        }

        .clinic-doctor-selected span {
            display: block;
            margin-top: 2px;
            font-size: 10px;
        }

        .clinic-doctor-empty {
            padding: 24px 16px;
            border: 1px dashed #d4dde3;
            border-radius: 13px;
            color: #7a8794;
            text-align: center;
            font-size: 11px;
        }

        .clinic-flash {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin: 0 0 18px;
            padding: 15px 17px;
            border: 1px solid;
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        }

        .clinic-flash__icon {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border-radius: 12px;
            font-size: 18px;
        }

        .clinic-flash__content {
            min-width: 0;
            flex: 1;
        }

        .clinic-flash__title {
            margin: 0;
            font-size: 13px;
            font-weight: 850;
            line-height: 1.35;
        }

        .clinic-flash__text {
            margin-top: 3px;
            font-size: 12px;
            line-height: 1.5;
        }

        .clinic-flash__list {
            display: grid;
            gap: 4px;
            margin: 7px 0 0;
            padding: 0;
            list-style: none;
        }

        .clinic-flash__list li {
            position: relative;
            padding-left: 15px;
            font-size: 12px;
            line-height: 1.45;
        }

        .clinic-flash__list li::before {
            position: absolute;
            top: .55em;
            left: 2px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            content: '';
        }

        .clinic-flash--error {
            border-color: #fecaca;
            background: linear-gradient(135deg, #fff6f6 0%, #fffafa 100%);
            color: #8f1d1d;
        }

        .clinic-flash--error .clinic-flash__icon {
            background: #fee2e2;
            color: #b91c1c;
        }

        .clinic-flash--error .clinic-flash__list li::before {
            background: #dc2626;
        }

        .clinic-flash--success {
            border-color: #bbebd1;
            background: linear-gradient(135deg, #f2fbf7 0%, #fbfffd 100%);
            color: #216447;
        }

        .clinic-flash--success .clinic-flash__icon {
            background: #dcf6e8;
            color: #168151;
        }

        .clinic-flash--success .clinic-flash__list li::before {
            background: #16a34a;
        }

        @media (max-width: 1120px) {
            .clinic-settings-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .clinic-care-formats {
                grid-template-columns: 1fr;
            }

            .clinic-phone-row {
                grid-template-columns: 1fr auto;
            }

            .clinic-phone-row .clinic-phone-label {
                grid-column: 1 / -1;
            }

            .clinic-working-day {
                grid-template-columns: 42px 42px 1fr 14px 1fr;
            }

            .clinic-staff-card {
                grid-template-columns: 44px minmax(0, 1fr) auto;
            }

            .clinic-staff-card__position,
            .clinic-staff-card__contact {
                display: none;
            }

            .clinic-save-bar {
                align-items: stretch;
                flex-direction: column;
            }
        }
        @media (max-width: 680px) {
            .clinic-inpatient-mode-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('sub-main')

    <main class="settings-page clinic-settings-page">
        <div class="settings-container">
            <div class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">Настройки клиники</h1>
                    <p class="settings-page-description">
                        @if(!$isPrivatePractice && $clinicType === \App\Models\Clinic::TYPE_INPATIENT)
                            Основные данные стационара, режим работы, время посещения родственниками
                            и сотрудники медицинской организации.
                        @else
                            Основные данные медицинской организации, контакты регистратуры, форматы приёма,
                            стандартный режим работы и сотрудники клиники.
                        @endif
                    </p>
                </div>
                @if($doctor)
                    <span class="settings-type-badge">
                        <i class="bi {{ $isPrivatePractice ? 'bi-person-badge' : ($clinicType === \App\Models\Clinic::TYPE_INPATIENT ? 'bi-building' : 'bi-hospital') }}"></i>
                        {{ $isPrivatePractice
                            ? 'Частная практика'
                            : ($clinicType === \App\Models\Clinic::TYPE_INPATIENT ? 'Стационар' : 'Амбулатория') }}
                    </span>
                @endif
            </div>

            @if($errors->any())
                <div class="clinic-flash clinic-flash--error" role="alert">
                    <div class="clinic-flash__icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div class="clinic-flash__content">
                        <div class="clinic-flash__title">
                            Не удалось сохранить настройки
                        </div>
                        <div class="clinic-flash__text">
                            Проверьте отмеченные данные и повторите сохранение.
                        </div>

                        <ul class="clinic-flash__list">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="clinic-flash clinic-flash--success" role="status">
                    <div class="clinic-flash__icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div class="clinic-flash__content">
                        <div class="clinic-flash__title">
                            Настройки сохранены
                        </div>
                        <div class="clinic-flash__text">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->employee->any())
                <div class="clinic-flash clinic-flash--error" role="alert">
                    <div class="clinic-flash__icon">
                        <i class="bi bi-person-exclamation"></i>
                    </div>

                    <div class="clinic-flash__content">
                        <div class="clinic-flash__title">
                            Не удалось сохранить сотрудника
                        </div>
                        <div class="clinic-flash__text">
                            Проверьте данные сотрудника и повторите сохранение.
                        </div>

                        <ul class="clinic-flash__list">
                            @foreach($errors->employee->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('employee_success'))
                <div class="clinic-flash clinic-flash--success" role="status">
                    <div class="clinic-flash__icon">
                        <i class="bi bi-person-check-fill"></i>
                    </div>

                    <div class="clinic-flash__content">
                        <div class="clinic-flash__title">
                            Данные сотрудника сохранены
                        </div>
                        <div class="clinic-flash__text">
                            {{ session('employee_success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if(!$doctor)
                <section class="settings-panel">
                    <div class="settings-panel__body">
                        <div class="settings-empty">
                            <div class="settings-empty__icon">
                                <i class="bi bi-person-x"></i>
                            </div>
                            <h3>Не удалось определить профиль врача</h3>
                            <p>Для отображения настроек у пользователя должен быть связан профиль врача.</p>
                        </div>
                    </div>
                </section>
            @elseif($isPrivatePractice)
                <section class="settings-panel">
                    <div class="settings-panel__header">
                        <div class="clinic-settings-panel-head">
                            <div class="clinic-settings-section-icon"><i class="bi bi-person-workspace"></i></div>
                            <div class="settings-panel__title-wrap">
                                <h2 class="settings-panel__title">Частная практика</h2>
                                <div class="settings-panel__subtitle">
                                    Для частного врача этот экран позже адаптируем отдельно — сейчас продолжаем интерфейс клиники.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings-panel__body">
                        <div class="settings-info-box">
                            <div class="settings-info-box__icon"><i class="bi bi-info-circle"></i></div>
                            <div>
                                Текущий пользователь не привязан к клинике. Его отдельную форму настроек не менял,
                                чтобы сейчас сосредоточиться на варианте медицинской организации.
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <form id="clinicSettingsForm"
                      method="POST"
                      action="{{ route('doctors.settings.index.update') }}">
                    @csrf

                    <input type="hidden"
                           name="address_confirmed"
                           id="clinicAddressConfirmedInput"
                           value="{{ old('address_confirmed', $clinic->address ? 1 : 0) }}">

                    <input type="hidden"
                           name="city"
                           id="clinicCity"
                           value="{{ old('city', $clinic->city ?? '') }}">

                    <input type="hidden"
                           name="latitude"
                           id="clinicLatitude"
                           value="{{ old('latitude', $clinic->latitude ?? '') }}">

                    <input type="hidden"
                           name="longitude"
                           id="clinicLongitude"
                           value="{{ old('longitude', $clinic->longitude ?? '') }}">

                    <div class="clinic-settings-grid">
                        <div class="clinic-settings-stack">
                            <section class="settings-panel">
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-hospital"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Основная информация</h2>
                                            <div class="settings-panel__subtitle">
                                                Название медицинской организации и её фактическое местоположение.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <div class="settings-form-grid">
                                        <div class="settings-col-12">
                                            <label class="settings-label" for="clinicName">Название клиники</label>
                                            <input id="clinicName"
                                                   name="name"
                                                   class="settings-control"
                                                   type="text"
                                                   value="{{ old('name', $clinic->name ?? 'Психоневрологический диспансер') }}"
                                                   placeholder="Название медицинской организации">
                                        </div>

                                        <div class="settings-col-12 clinic-settings-address">
                                            <label class="settings-label" for="clinicAddress">
                                                Адрес клиники
                                            </label>

                                            <div class="clinic-address-input-wrap">
                                                <i class="bi bi-geo-alt"></i>
                                                <input id="clinicAddress"
                                                       name="address"
                                                       class="settings-control"
                                                       autocomplete="off"
                                                       type="text"
                                                       value="{{ old('address', $clinic->address ?? '') }}"
                                                       placeholder="Начните вводить адрес, например: Томск, проспект Ленина...">
                                                <i class="bi bi-check-circle-fill clinic-address-state"
                                                   id="clinicAddressState"></i>
                                            </div>

                                            <div class="clinic-address-suggestions" id="clinicAddressSuggestions"></div>

                                            <div class="clinic-address-confirmed" id="clinicAddressConfirmed">
                                                <i class="bi bi-check2-circle"></i>
                                                <span id="clinicAddressConfirmedText"></span>
                                            </div>

                                            <div class="settings-field-hint">
                                                Адрес считается подтверждённым только после выбора варианта из выпадающего списка.
                                                После выбора на карте ниже появится точка клиники.
                                            </div>
                                        </div>

                                        <div class="settings-col-12">
                                            <div class="clinic-map-shell"
                                                 id="clinicMapShell">
                                                <div class="clinic-map" id="clinicMap"></div>

                                                <div class="clinic-map-placeholder"
                                                     id="clinicMapPlaceholder">
                                                    <div>
                                                        <div class="clinic-map-placeholder__icon">
                                                            <i class="bi bi-map"></i>
                                                        </div>

                                                        <strong id="clinicMapPlaceholderTitle">
                                                            Карта появится после подтверждения адреса
                                                        </strong>

                                                        <p id="clinicMapPlaceholderText">
                                                            Выберите один из предложенных адресов — карта автоматически
                                                            приблизится к зданию и поставит метку.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="settings-col-12">
                                            <label class="settings-label" for="clinicDescription">Краткое описание клиники</label>
                                            <textarea id="clinicDescription"
                                                      name="description"
                                                      class="settings-control"
                                                      placeholder="Короткая информация о медицинской организации">{{ old('description', $clinic->description ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-panel">
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-people"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Сотрудники клиники</h2>
                                            <div class="settings-panel__subtitle">
                                                Здесь назначается должность сотрудника и, при необходимости, его медицинская специальность.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <div class="clinic-staff-toolbar">
                                        <div class="clinic-staff-toolbar__summary">
                                            Здесь перечислен весь персонал клиники, имеющий доступ к системе.
                                            <span class="ms-2">
                                                Врачи: {{ $staffSummary['doctors'] ?? $doctors->count() }}
                                                · Медсёстры: {{ $staffSummary['nurses'] ?? $clinicNurses->count() }}
                                                · Регистраторы: {{ $staffSummary['registrars'] ?? $clinicRegistrars->count() }}
                                            </span>
                                        </div>

                                        <button type="button"
                                                class="settings-button settings-button--primary"
                                                id="addClinicEmployeeButton">
                                            <i class="bi bi-person-plus"></i>
                                            Добавить сотрудника
                                        </button>
                                    </div>

                                    <div class="clinic-staff-list" id="clinicStaffList">
                                        @foreach($doctors as $employee)
                                            <article class="clinic-staff-card"
                                                     data-staff-kind="doctor"
                                                     data-employee-index="{{ $loop->index }}"
                                                     data-doctor-id="{{ $employee->id }}"
                                                     data-position="{{ $employee->id == $clinic->headmaster_id ? 'Главный врач' : ($employee->profession ?: 'Врач') }}"
                                                     data-speciality="{{ $employee->status ?: '' }}"
                                                     data-phone="{{ $employee->phone ?: '' }}"
                                                     data-email="{{ $employee->email ?: '' }}"
                                                     data-is-headmaster="{{ $employee->id == $clinic->headmaster_id ? 1 : 0 }}">
                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][id]"
                                                       class="employee-id"
                                                       value="{{ $employee->id }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][surname]"
                                                       class="employee-surname"
                                                       value="{{ $employee->surname }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][name]"
                                                       class="employee-name"
                                                       value="{{ $employee->name }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][patronym]"
                                                       class="employee-patronym"
                                                       value="{{ $employee->patronym }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][position]"
                                                       class="employee-position"
                                                       value="{{ $employee->profession ?: 'Врач' }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][specialties][0]"
                                                       class="employee-speciality-value"
                                                       value="{{ $employee->status ?: '' }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][employment_status]"
                                                       class="employee-status-value"
                                                       value="active">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][dismissed_at]"
                                                       class="employee-dismissed-at"
                                                       value="">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][phone]"
                                                       class="employee-phone"
                                                       value="{{ $employee->phone }}">

                                                <input type="hidden"
                                                       name="employees[{{ $loop->index }}][email]"
                                                       class="employee-email"
                                                       value="{{ $employee->email }}">

                                                <div class="clinic-staff-avatar">
                                                    {{ ucfirst(mb_substr($employee->name, 0, 1)) . ucfirst(mb_substr($employee->surname, 0, 1)) }}
                                                </div>

                                                <div>
                                                    <div class="clinic-staff-name">
                                                        {{ $employee->full_name }}
                                                    </div>
                                                    <div class="clinic-staff-speciality">
                                                        {{ $employee->id == $clinic->headmaster_id
                                                            ? 'Главный врач'
                                                            : ($employee->profession ?: 'Врач') }}
                                                    </div>
                                                </div>

                                                <div class="clinic-staff-card__position">
                                                    @php
                                                        $employeeSpecialities = collect(
                                                            preg_split(
                                                                '/\s*[,;|]\s*/u',
                                                                (string) ($employee->status ?? ''),
                                                                -1,
                                                                PREG_SPLIT_NO_EMPTY
                                                            )
                                                        )
                                                            ->map(fn ($value) => trim($value))
                                                            ->filter()
                                                            ->unique()
                                                            ->values();
                                                    @endphp

                                                    @forelse($employeeSpecialities as $employeeSpeciality)
                                                        <span class="clinic-staff-position">
                                                            {{ $employeeSpeciality }}
                                                        </span>
                                                    @empty
                                                        <span class="clinic-staff-position clinic-staff-position--empty">
                                                            Специальность не указана
                                                        </span>
                                                    @endforelse
                                                </div>

                                                <div class="clinic-staff-card__contact">
                                                    <div class="clinic-staff-status">
                                                        Работает
                                                    </div>
                                                    <div class="clinic-staff-contact">{{ $employee->phone }}</div>
                                                    <div class="clinic-staff-contact">{{ $employee->email }}</div>
                                                </div>

                                                <div class="clinic-staff-actions">
                                                    <button type="button"
                                                            class="clinic-icon-button clinic-edit-employee"
                                                            title="Редактировать сотрудника">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </article>
                                        @endforeach

                                        @foreach($clinicRegistrars as $registrar)
                                            <article class="clinic-staff-card"
                                                     data-staff-kind="registrar"
                                                     data-employee-index="{{ $doctors->count() + $loop->index }}"
                                                     data-doctor-id="{{ $registrar->id }}"
                                                     data-position="Медицинский регистратор"
                                                     data-speciality=""
                                                     data-phone="{{ $registrar->phone ?: '' }}"
                                                     data-email="{{ $registrar->email ?: '' }}"
                                                     data-is-headmaster="0">
                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][id]"
                                                       class="employee-id"
                                                       value="{{ $registrar->id }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][surname]"
                                                       class="employee-surname"
                                                       value="{{ $registrar->surname }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][name]"
                                                       class="employee-name"
                                                       value="{{ $registrar->name }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][patronym]"
                                                       class="employee-patronym"
                                                       value="{{ $registrar->patronym }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][position]"
                                                       class="employee-position"
                                                       value="Медицинский регистратор">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][employment_status]"
                                                       class="employee-status-value"
                                                       value="{{ $registrar->employment_status ?? 'active' }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][dismissed_at]"
                                                       class="employee-dismissed-at"
                                                       value="{{ $registrar->dismissed_at ?? '' }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][phone]"
                                                       class="employee-phone"
                                                       value="{{ $registrar->phone ?? '' }}">

                                                <input type="hidden"
                                                       name="employees[{{ $doctors->count() + $loop->index }}][email]"
                                                       class="employee-email"
                                                       value="{{ $registrar->email ?? '' }}">

                                                <div class="clinic-staff-avatar">
                                                    {{ mb_strtoupper(
                                                        mb_substr((string) $registrar->name, 0, 1)
                                                        . mb_substr((string) $registrar->surname, 0, 1)
                                                    ) }}
                                                </div>

                                                <div>
                                                    <div class="clinic-staff-name">
                                                        {{ $registrar->full_name }}
                                                    </div>
                                                    <div class="clinic-staff-speciality">
                                                        Медицинский регистратор
                                                    </div>
                                                </div>

                                                <div class="clinic-staff-card__position">
                                                    <span class="clinic-staff-position">
                                                        Регистратура
                                                    </span>
                                                </div>

                                                <div class="clinic-staff-card__contact">
                                                    <div class="clinic-staff-status">
                                                        Работает
                                                    </div>
                                                    <div class="clinic-staff-contact">
                                                        {{ $registrar->phone }}
                                                    </div>
                                                    <div class="clinic-staff-contact">
                                                        {{ $registrar->email }}
                                                    </div>
                                                </div>

                                                <div class="clinic-staff-actions">
                                                    <button type="button"
                                                            class="clinic-icon-button clinic-edit-employee"
                                                            title="Редактировать сотрудника">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </article>
                                        @endforeach

                                        @foreach($clinicNurses as $clinicNurse)
                                            @php
                                                $nurseFullName = trim(
                                                    ($clinicNurse->surname ?? '')
                                                    . ' '
                                                    . ($clinicNurse->name ?? '')
                                                    . ' '
                                                    . ($clinicNurse->patronym ?? '')
                                                );

                                                $nurseProfession = trim(
                                                    (string) (
                                                        $clinicNurse->profession
                                                        ?? 'Медицинская сестра'
                                                    )
                                                );

                                                if ($nurseProfession === '') {
                                                    $nurseProfession = 'Медицинская сестра';
                                                }

                                                $nursePhone = (string) ($clinicNurse->phone ?? '');
                                                $nurseEmail = (string) ($clinicNurse->email ?? '');
                                            @endphp

                                            <article class="clinic-staff-card"
                                                     data-staff-kind="nurse"
                                                     data-nurse-id="{{ $clinicNurse->id }}">
                                                <div class="clinic-staff-avatar">
                                                    {{ mb_strtoupper(
                                                        mb_substr((string) ($clinicNurse->name ?? ''), 0, 1)
                                                        . mb_substr((string) ($clinicNurse->surname ?? ''), 0, 1)
                                                    ) }}
                                                </div>

                                                <div>
                                                    <div class="clinic-staff-name">
                                                        {{ $nurseFullName !== '' ? $nurseFullName : 'Медицинская сестра #' . $clinicNurse->id }}
                                                    </div>
                                                    <div class="clinic-staff-speciality">
                                                        {{ $nurseProfession }}
                                                    </div>
                                                </div>

                                                <div class="clinic-staff-card__position">
                                                    <span class="clinic-staff-position">
                                                        Сестринское дело
                                                    </span>
                                                </div>

                                                <div class="clinic-staff-card__contact">
                                                    <div class="clinic-staff-status">
                                                        Работает
                                                    </div>

                                                    @if($nursePhone !== '')
                                                        <div class="clinic-staff-contact">
                                                            {{ $nursePhone }}
                                                        </div>
                                                    @endif

                                                    @if($nurseEmail !== '')
                                                        <div class="clinic-staff-contact">
                                                            {{ $nurseEmail }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="clinic-staff-actions">
                                                    <span class="clinic-staff-position"
                                                          title="Медицинская сестра">
                                                        Медсестра
                                                    </span>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>

                                    <div class="clinic-staff-pagination"
                                         id="clinicStaffPagination"
                                         aria-label="Страницы сотрудников"></div>
                                </div>
                            </section>
                        </div>

                        <div class="clinic-settings-stack">
                            <section class="settings-panel">
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-telephone"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Телефоны регистратуры</h2>
                                            <div class="settings-panel__subtitle">
                                                Можно указать несколько телефонов и назначить им понятные подписи.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <div class="clinic-phone-list" id="clinicPhoneList">
                                        @foreach($clinicPhones as $phoneIndex => $phone)
                                            <div class="clinic-phone-row">
                                                <input type="text"
                                                       name="phones[{{ $phoneIndex }}][phone]"
                                                       class="settings-control clinic-phone-number"
                                                       value="{{ $phone['phone'] ?? '' }}"
                                                       placeholder="+7 (...) ...-..-..">

                                                <input type="text"
                                                       name="phones[{{ $phoneIndex }}][label]"
                                                       class="settings-control clinic-phone-label"
                                                       value="{{ $phone['label'] ?? '' }}"
                                                       placeholder="Подпись">

                                                <button type="button"
                                                        class="clinic-icon-button clinic-icon-button--danger clinic-remove-phone"
                                                        title="Удалить телефон">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button" class="clinic-inline-action" id="addClinicPhoneButton">
                                        <i class="bi bi-plus-lg"></i>
                                        Добавить телефон
                                    </button>
                                </div>
                            </section>

                            <section class="settings-panel"
                                     id="clinicCareFormatsPanel"
                                     @if($clinicType === \App\Models\Clinic::TYPE_INPATIENT) hidden @endif>
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-camera-video"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Форматы оказания помощи</h2>
                                            <div class="settings-panel__subtitle">
                                                Это не перечень конкретных услуг, а способы, которыми клиника принимает пациентов.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <div class="clinic-care-formats">
                                        <label class="clinic-care-format">
                                            <input type="checkbox"
                                                   name="services[]"
                                                   value="in_person"
                                                   class="form-check-input"
                                                {{ in_array('in_person', $clinicServices, true) ? 'checked' : '' }}>
                                            <span class="clinic-care-format__icon">
                                            <i class="bi bi-person-check"></i>
                                        </span>
                                            <span>
                                            <strong>Очный приём</strong>
                                            <p>Пациент посещает клинику и врача в кабинете.</p>
                                        </span>
                                        </label>

                                        <label class="clinic-care-format">
                                            <input type="checkbox"
                                                   name="services[]"
                                                   value="telemedicine"
                                                   class="form-check-input"
                                                {{ in_array('telemedicine', $clinicServices, true) ? 'checked' : '' }}>
                                            <span class="clinic-care-format__icon">
                                            <i class="bi bi-camera-video"></i>
                                        </span>
                                            <span>
                                            <strong>Телемедицина</strong>
                                            <p>Дистанционные консультации через информационную систему.</p>
                                        </span>
                                        </label>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-panel"
                                     id="clinicOutpatientSchedulePanel"
                                     @if($clinicType === \App\Models\Clinic::TYPE_INPATIENT) hidden @endif>
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-clock-history"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Стандартный режим работы</h2>
                                            <div class="settings-panel__subtitle">
                                                Обычная рабочая неделя амбулатории. Карантины и временные ограничения остаются во вкладке «График клиники».
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <div class="clinic-working-hours" id="clinicWorkingHours">
                                        @foreach($weekDays as $day)
                                            <div class="clinic-working-day {{ !$day['open'] ? 'is-closed' : '' }}"
                                                 data-working-day="{{ $day['code'] }}">
                                                <div class="clinic-working-day__name">{{ $day['label'] }}</div>

                                                <div>
                                                    <input type="hidden"
                                                           name="working_hours[{{ $day['code'] }}][enabled]"
                                                           value="0">

                                                    <input class="form-check-input clinic-working-day-toggle"
                                                           type="checkbox"
                                                           name="working_hours[{{ $day['code'] }}][enabled]"
                                                           value="1"
                                                        {{ ($day['open'] === true) ? 'checked' : '' }}>
                                                </div>

                                                <input type="time"
                                                       name="working_hours[{{ $day['code'] }}][from]"
                                                       class="settings-control clinic-working-from"
                                                       value="{{ old("working_hours.{$day['code']}.from", $day['from']) }}">

                                                <div class="clinic-working-day__separator">—</div>

                                                <input type="time"
                                                       name="working_hours[{{ $day['code'] }}][to]"
                                                       class="settings-control clinic-working-to"
                                                       value="{{ old("working_hours.{$day['code']}.to", $day['to']) }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button" class="clinic-inline-action" id="copyMondayButton">
                                        <i class="bi bi-copy"></i>
                                        Скопировать понедельник на ПН–ПТ
                                    </button>
                                </div>
                            </section>

                            <section class="settings-panel"
                                     id="clinicInpatientSchedulePanel"
                                     @if($clinicType !== \App\Models\Clinic::TYPE_INPATIENT) hidden @endif>
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-moon-stars"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Режим работы стационара</h2>
                                            <div class="settings-panel__subtitle">
                                                Укажите, является стационар дневным, ночным или круглосуточным.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <label class="settings-label">Формат стационара</label>

                                    <div class="clinic-inpatient-mode-options">
                                        <label class="clinic-type-option">
                                            <input type="radio"
                                                   name="inpatient_mode"
                                                   value="{{ \App\Models\Clinic::INPATIENT_MODE_DAY }}"
                                                @checked($inpatientMode === \App\Models\Clinic::INPATIENT_MODE_DAY)>

                                            <span class="clinic-type-card clinic-type-card--compact">
                                                <span class="clinic-type-card__icon">
                                                    <i class="bi bi-sun"></i>
                                                </span>
                                                <span>
                                                    <strong>Дневной</strong>
                                                    <p>Пациенты находятся в стационаре в дневные часы.</p>
                                                </span>
                                            </span>
                                        </label>

                                        <label class="clinic-type-option">
                                            <input type="radio"
                                                   name="inpatient_mode"
                                                   value="{{ \App\Models\Clinic::INPATIENT_MODE_NIGHT }}"
                                                @checked($inpatientMode === \App\Models\Clinic::INPATIENT_MODE_NIGHT)>

                                            <span class="clinic-type-card clinic-type-card--compact">
                                                <span class="clinic-type-card__icon">
                                                    <i class="bi bi-moon"></i>
                                                </span>
                                                <span>
                                                    <strong>Ночной</strong>
                                                    <p>Рабочий интервал может переходить через полночь.</p>
                                                </span>
                                            </span>
                                        </label>

                                        <label class="clinic-type-option">
                                            <input type="radio"
                                                   name="inpatient_mode"
                                                   value="{{ \App\Models\Clinic::INPATIENT_MODE_ROUND_THE_CLOCK }}"
                                                @checked($inpatientMode === \App\Models\Clinic::INPATIENT_MODE_ROUND_THE_CLOCK)>

                                            <span class="clinic-type-card clinic-type-card--compact">
                                                <span class="clinic-type-card__icon">
                                                    <i class="bi bi-24-circle"></i>
                                                </span>
                                                <span>
                                                    <strong>Круглосуточный</strong>
                                                    <p>Стационар работает 24 часа в сутки, 7 дней в неделю.</p>
                                                </span>
                                            </span>
                                        </label>
                                    </div>

                                    <div class="clinic-inpatient-schedule-block"
                                         id="inpatientWorkingScheduleBlock"
                                         @if($inpatientMode === \App\Models\Clinic::INPATIENT_MODE_ROUND_THE_CLOCK) hidden @endif>
                                        <div class="clinic-inpatient-schedule-title">
                                            Часы работы стационара
                                        </div>
                                        <div class="clinic-inpatient-schedule-hint">
                                            Для ночного режима допустим переход через полночь, например 20:00 — 08:00 следующего дня.
                                        </div>

                                        <div class="clinic-working-hours" id="inpatientWorkingHours">
                                            @foreach($inpatientWeekDays as $day)
                                                <div class="clinic-working-day {{ !$day['open'] ? 'is-closed' : '' }}"
                                                     data-inpatient-working-day="{{ $day['code'] }}">
                                                    <div class="clinic-working-day__name">{{ $day['label'] }}</div>

                                                    <div>
                                                        <input type="hidden"
                                                               name="inpatient_working_hours[{{ $day['code'] }}][enabled]"
                                                               value="0">

                                                        <input class="form-check-input inpatient-working-day-toggle"
                                                               type="checkbox"
                                                               name="inpatient_working_hours[{{ $day['code'] }}][enabled]"
                                                               value="1"
                                                            {{ $day['open'] ? 'checked' : '' }}>
                                                    </div>

                                                    <input type="time"
                                                           name="inpatient_working_hours[{{ $day['code'] }}][from]"
                                                           class="settings-control inpatient-working-from"
                                                           value="{{ $day['from'] }}">

                                                    <div class="clinic-working-day__separator">—</div>

                                                    <input type="time"
                                                           name="inpatient_working_hours[{{ $day['code'] }}][to]"
                                                           class="settings-control inpatient-working-to"
                                                           value="{{ $day['to'] }}">
                                                </div>
                                            @endforeach
                                        </div>

                                        <button type="button"
                                                class="clinic-inline-action"
                                                id="copyInpatientMondayButton">
                                            <i class="bi bi-copy"></i>
                                            Скопировать понедельник на всю неделю
                                        </button>
                                    </div>

                                    <div class="clinic-round-clock-note"
                                         id="inpatientRoundClockNotice"
                                         @if($inpatientMode !== \App\Models\Clinic::INPATIENT_MODE_ROUND_THE_CLOCK) hidden @endif>
                                        <i class="bi bi-clock-fill"></i>
                                        <span>
                                            Для круглосуточного стационара отдельные часы работы не требуются:
                                            режим считается 24/7. Смены сотрудников будут настраиваться отдельно.
                                        </span>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-panel"
                                     id="clinicInpatientVisitingPanel"
                                     @if($clinicType !== \App\Models\Clinic::TYPE_INPATIENT) hidden @endif>
                                <div class="settings-panel__header">
                                    <div class="clinic-settings-panel-head">
                                        <div class="clinic-settings-section-icon"><i class="bi bi-people"></i></div>
                                        <div class="settings-panel__title-wrap">
                                            <h2 class="settings-panel__title">Посещение родственниками</h2>
                                            <div class="settings-panel__subtitle">
                                                Базовое время посещений для стационара. У каждого отделения можно будет задать своё расписание вместо базового.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="settings-panel__body">
                                    <div class="clinic-inpatient-schedule-hint">
                                        Отметьте дни, когда посещения разрешены, и укажите интервал. Можно сразу нажать на поле времени — соответствующий день включится автоматически. После добавления отделений этот график будет использоваться как общий по умолчанию.
                                    </div>

                                    <div class="clinic-working-hours" id="inpatientVisitingHours">
                                        @foreach($inpatientVisitingWeekDays as $day)
                                            <div class="clinic-working-day {{ !$day['open'] ? 'is-closed' : '' }}"
                                                 data-inpatient-visiting-day="{{ $day['code'] }}">
                                                <div class="clinic-working-day__name">{{ $day['label'] }}</div>

                                                <div>
                                                    <input type="hidden"
                                                           name="inpatient_visiting_hours[{{ $day['code'] }}][enabled]"
                                                           value="0">

                                                    <input class="form-check-input inpatient-visiting-day-toggle"
                                                           type="checkbox"
                                                           name="inpatient_visiting_hours[{{ $day['code'] }}][enabled]"
                                                           value="1"
                                                        {{ $day['open'] ? 'checked' : '' }}>
                                                </div>

                                                <input type="time"
                                                       name="inpatient_visiting_hours[{{ $day['code'] }}][from]"
                                                       class="settings-control inpatient-visiting-from"
                                                       value="{{ $day['from'] }}">

                                                <div class="clinic-working-day__separator">—</div>

                                                <input type="time"
                                                       name="inpatient_visiting_hours[{{ $day['code'] }}][to]"
                                                       class="settings-control inpatient-visiting-to"
                                                       value="{{ $day['to'] }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button"
                                            class="clinic-inline-action"
                                            id="copyVisitingMondayButton">
                                        <i class="bi bi-copy"></i>
                                        Скопировать понедельник на всю неделю
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="clinic-save-bar">
                        <div class="clinic-save-bar__status">
                            <i class="bi bi-cloud-check"></i>
                            <span>
                            Изменения сохраняются в настройках клиники после нажатия «Сохранить настройки».
                        </span>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="reset" class="settings-button settings-button--secondary">
                                Отменить
                            </button>
                            <button type="submit" class="settings-button settings-button--primary">
                                <i class="bi bi-check2-circle"></i>
                                Сохранить настройки
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </main>

    <div class="modal fade clinic-staff-modal"
         id="clinicAddDoctorModal"
         tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="settings-panel__subtitle mb-1">Сотрудник клиники</div>
                        <h2 class="modal-title">Добавить сотрудника из базы</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <label class="settings-label" for="clinicDoctorSearch">Найти сотрудника</label>

                    <div class="clinic-doctor-search">
                        <i class="bi bi-search"></i>
                        <input id="clinicDoctorSearch"
                               class="settings-control"
                               type="text"
                               autocomplete="off"
                               placeholder="ФИО, должность, специальность, телефон...">
                    </div>

                    <div class="settings-field-hint">
                        В списке показаны сотрудники из базы, которые сейчас не привязаны к другой клинике.
                    </div>

                    <div class="clinic-doctor-results" id="clinicDoctorResults">
                        @forelse($availableDoctors as $availableDoctor)
                            <button type="button"
                                    class="clinic-doctor-option"
                                    data-doctor-id="{{ $availableDoctor->id }}"
                                    data-surname="{{ $availableDoctor->surname }}"
                                    data-name="{{ $availableDoctor->name }}"
                                    data-patronym="{{ $availableDoctor->patronym }}"
                                    data-position="{{ $availableDoctor->profession ?: 'Врач' }}"
                                    data-speciality="{{ $availableDoctor->status }}"
                                    data-phone="{{ $availableDoctor->phone }}"
                                    data-email="{{ $availableDoctor->email }}">
                                <span class="clinic-doctor-option__icon">
                                    <i class="bi bi-person-badge"></i>
                                </span>

                                <span>
                                    <span class="clinic-doctor-option__name">
                                        {{ $availableDoctor->full_name }}
                                    </span>
                                    <span class="clinic-doctor-option__meta">
                                        {{ $availableDoctor->status ?: 'Специальность не указана' }}
                                        @if($availableDoctor->phone)
                                            · {{ $availableDoctor->phone }}
                                        @endif
                                    </span>
                                </span>

                                <span class="clinic-doctor-option__status">
                                    {{ $availableDoctor->profession ?: 'Врач' }}
                                </span>
                            </button>
                        @empty
                            <div class="clinic-doctor-empty">
                                Нет врачей, доступных для добавления в клинику.
                            </div>
                        @endforelse
                    </div>

                    <div class="clinic-doctor-selected" id="clinicDoctorSelected">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>
                            <strong id="clinicDoctorSelectedName"></strong>
                            <span id="clinicDoctorSelectedMeta"></span>
                        </div>
                    </div>

                    <div class="mt-3 d-none"
                         id="clinicNewEmployeePositionWrap">
                        <label class="settings-label"
                               for="clinicNewEmployeePosition">
                            Должность в клинике
                        </label>

                        <select id="clinicNewEmployeePosition"
                                class="settings-control">
                            <option value="Врач">Врач</option>
                            <option value="Детский психиатр">Детский психиатр</option>
                            <option value="Медицинский психолог">Медицинский психолог</option>
                            <option value="Медицинский регистратор">Медицинский регистратор</option>
                            <option value="Санитар">Санитар</option>
                        </select>

                        <div class="settings-field-hint">
                            Для регистратора медицинские специальности не назначаются.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="settings-button settings-button--secondary"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="button"
                            class="settings-button settings-button--primary"
                            id="addSelectedDoctorButton"
                            disabled>
                        <i class="bi bi-person-plus"></i>
                        Добавить в клинику
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade clinic-staff-modal"
         id="clinicEmployeeModal"
         tabindex="-1"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="clinicEmployeeForm"
                  class="modal-content clinic-employee-form"
                  method="POST"
                  action="{{ route('doctors.settings.employees.update') }}">
                @csrf
                <div class="modal-header">
                    <div>
                        <div class="settings-panel__subtitle mb-1">Сотрудник клиники</div>
                        <h2 class="modal-title" id="clinicEmployeeModalTitle">Редактировать сотрудника</h2>
                    </div>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="employeeEditIndex" value="">
                    <input type="hidden"
                           id="employeeDoctorId"
                           name="doctor_id"
                           value="{{ old('doctor_id') }}">

                    @if($errors->employee->any())
                        <div class="clinic-employee-feedback clinic-employee-feedback--error"
                             role="alert">
                            <div class="clinic-employee-feedback__icon">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>

                            <div class="clinic-employee-feedback__content">
                                <div class="clinic-employee-feedback__title">
                                    Не удалось сохранить изменения
                                </div>
                                <div class="clinic-employee-feedback__text">
                                    Исправьте ошибки и отправьте форму повторно.
                                </div>

                                <ul class="clinic-employee-feedback__list">
                                    @foreach($errors->employee->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if(session('employee_success'))
                        <div class="clinic-employee-feedback clinic-employee-feedback--success"
                             role="status">
                            <div class="clinic-employee-feedback__icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>

                            <div class="clinic-employee-feedback__content">
                                <div class="clinic-employee-feedback__title">
                                    Изменения сохранены
                                </div>
                                <div class="clinic-employee-feedback__text">
                                    {{ session('employee_success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="clinic-employee-profile">
                        <div class="clinic-employee-profile__avatar"
                             id="employeeProfileAvatar">
                            ВР
                        </div>

                        <div class="clinic-employee-profile__content">
                            <div class="clinic-employee-profile__name"
                                 id="employeeProfileName">
                                Сотрудник
                            </div>
                            <div class="clinic-employee-profile__meta"
                                 id="employeeProfileMeta">
                                Данные из базы врачей
                            </div>
                        </div>
                    </div>

                    <div class="clinic-employee-form-section">
                        <div class="clinic-employee-form-section__head">
                            <i class="bi bi-briefcase"></i>
                            Работа в клинике
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="settings-label" for="employeePosition">
                                    Должность
                                </label>
                                <select id="employeePosition"
                                        name="position"
                                        class="settings-control"
                                        required>
                                    <option value="Главный врач">Главный врач</option>
                                    <option value="Врач">Врач</option>
                                    <option value="Детский психиатр">Детский психиатр</option>
                                    <option value="Старшая медицинская сестра">Старшая медицинская сестра</option>
                                    <option value="Медицинская сестра">Медицинская сестра</option>
                                    <option value="Санитар">Санитар</option>
                                    <option value="Медицинский психолог">Медицинский психолог</option>
                                    <option value="Медицинский регистратор">Медицинский регистратор</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="settings-label" for="employeeState">
                                    Статус работы
                                </label>
                                <select id="employeeState"
                                        name="employment_status"
                                        class="settings-control"
                                        required>
                                    <option value="active">Работает</option>
                                    <option value="temporarily_absent">Временно отсутствует</option>
                                    <option value="dismissed">Уволен</option>
                                </select>
                            </div>

                            <div class="col-md-6 clinic-dismissal-field"
                                 id="employeeDismissalField">
                                <label class="settings-label" for="employeeDismissedAt">
                                    Дата увольнения
                                </label>
                                <input id="employeeDismissedAt"
                                       name="dismissed_at"
                                       class="settings-control"
                                       type="date">
                            </div>
                        </div>
                    </div>

                    <div class="clinic-employee-form-section clinic-headmaster-transfer d-none"
                         id="headmasterTransferSection">
                        <div class="clinic-employee-form-section__head">
                            <i class="bi bi-arrow-left-right"></i>
                            Передача должности главного врача
                        </div>

                        <div class="clinic-headmaster-transfer__note">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                Этот сотрудник является главным врачом клиники.
                                Перед увольнением или сменой должности необходимо выбрать нового главного врача.
                            </div>
                        </div>

                        <label class="settings-label" for="newHeadmasterId">
                            Новый главный врач
                        </label>

                        <select id="newHeadmasterId"
                                name="new_headmaster_id"
                                class="settings-control">
                            <option value="">Выберите врача</option>

                            @foreach($doctors as $headmasterCandidate)
                                <option value="{{ $headmasterCandidate->id }}">
                                    {{ $headmasterCandidate->full_name }}
                                    — {{ $headmasterCandidate->status ?: 'Специальность не указана' }}
                                </option>
                            @endforeach
                        </select>

                        <div class="settings-field-hint">
                            После сохранения выбранный врач станет главным врачом клиники.
                        </div>
                    </div>

                    <div class="clinic-employee-form-section"
                         id="employeeSpecialitySection">
                        <div class="clinic-employee-form-section__head">
                            <i class="bi bi-award"></i>
                            Специальности / профиль
                        </div>

                        <div class="clinic-speciality-tags">
                            <label class="clinic-speciality-tag">
                                <input type="checkbox"
                                       name="specialties[]"
                                       class="form-check-input employee-speciality-option"
                                       value="Психиатрия">
                                Психиатрия
                            </label>

                            <label class="clinic-speciality-tag">
                                <input type="checkbox"
                                       name="specialties[]"
                                       class="form-check-input employee-speciality-option"
                                       value="Психиатр-нарколог">
                                Психиатр-нарколог
                            </label>

                            <label class="clinic-speciality-tag">
                                <input type="checkbox"
                                       name="specialties[]"
                                       class="form-check-input employee-speciality-option"
                                       value="Психотерапия">
                                Психотерапия
                            </label>

                            <label class="clinic-speciality-tag">
                                <input type="checkbox"
                                       name="specialties[]"
                                       class="form-check-input employee-speciality-option"
                                       value="Детская психиатрия">
                                Детская психиатрия
                            </label>

                            <label class="clinic-speciality-tag">
                                <input type="checkbox"
                                       name="specialties[]"
                                       class="form-check-input employee-speciality-option"
                                       value="Клиническая психология">
                                Клиническая психология
                            </label>

                            <label class="clinic-speciality-tag">
                                <input type="checkbox"
                                       name="specialties[]"
                                       class="form-check-input employee-speciality-option"
                                       value="Сестринское дело">
                                Сестринское дело
                            </label>
                        </div>
                    </div>

                    <div class="clinic-employee-form-section">
                        <div class="clinic-employee-form-section__head">
                            <i class="bi bi-person-lines-fill"></i>
                            Контакты
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="settings-label" for="employeePhone">
                                    Телефон
                                </label>
                                <input id="employeePhone"
                                       name="phone"
                                       class="settings-control"
                                       type="text"
                                       placeholder="+7 (...) ...-..-..">
                            </div>

                            <div class="col-md-6">
                                <label class="settings-label" for="employeeEmail">
                                    E-mail
                                </label>
                                <input id="employeeEmail"
                                       name="email"
                                       class="settings-control"
                                       type="email"
                                       placeholder="employee@clinic.ru">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="clinic-employee-danger me-auto d-none"
                            id="dismissEmployeeButton"
                            title="Уволить сотрудника из клиники">
                        <i class="bi bi-person-dash"></i>
                        Уволить сотрудника
                    </button>

                    <button type="button"
                            class="settings-button settings-button--secondary"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit"
                            class="settings-button settings-button--primary">
                        <i class="bi bi-check-lg"></i>
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const addressInput = document.getElementById('clinicAddress');
            const suggestionsBox = document.getElementById('clinicAddressSuggestions');
            const addressState = document.getElementById('clinicAddressState');
            const confirmedBox = document.getElementById('clinicAddressConfirmed');
            const confirmedText = document.getElementById('clinicAddressConfirmedText');
            const mapShell = document.getElementById('clinicMapShell');
            const mapPlaceholder = document.getElementById('clinicMapPlaceholder');
            const mapPlaceholderTitle = document.getElementById('clinicMapPlaceholderTitle');
            const mapPlaceholderText = document.getElementById('clinicMapPlaceholderText');
            const addressConfirmedInput = document.getElementById('clinicAddressConfirmedInput');
            const cityInput = document.getElementById('clinicCity');
            const latitudeInput = document.getElementById('clinicLatitude');
            const longitudeInput = document.getElementById('clinicLongitude');
            const clinicCareFormatsPanel = document.getElementById(
                'clinicCareFormatsPanel'
            );
            const clinicOutpatientSchedulePanel = document.getElementById(
                'clinicOutpatientSchedulePanel'
            );
            const clinicInpatientSchedulePanel = document.getElementById(
                'clinicInpatientSchedulePanel'
            );
            const clinicInpatientVisitingPanel = document.getElementById(
                'clinicInpatientVisitingPanel'
            );
            const inpatientModeInputs = Array.from(
                document.querySelectorAll('input[name="inpatient_mode"]')
            );
            const inpatientWorkingScheduleBlock = document.getElementById(
                'inpatientWorkingScheduleBlock'
            );
            const inpatientRoundClockNotice = document.getElementById(
                'inpatientRoundClockNotice'
            );

            let yandexMap = null;
            let yandexPlacemark = null;
            let addressConfirmed = false;
            let addressSearchTimer = null;
            let addressSearchController = null;
            let addressSuggestions = [];

            const ADDRESS_SEARCH_URL = @json(route('api-search-address'));
            const IS_INPATIENT_CLINIC = @json($clinicType === \App\Models\Clinic::TYPE_INPATIENT);
            const INPATIENT_MODE_DAY = @json(\App\Models\Clinic::INPATIENT_MODE_DAY);
            const INPATIENT_MODE_NIGHT = @json(\App\Models\Clinic::INPATIENT_MODE_NIGHT);
            const INPATIENT_MODE_ROUND_THE_CLOCK = @json(\App\Models\Clinic::INPATIENT_MODE_ROUND_THE_CLOCK);

            function setPanelState(panel, visible) {
                if (!panel) {
                    return;
                }

                panel.hidden = !visible;

                panel
                    .querySelectorAll('input, select, textarea, button')
                    .forEach(control => {
                        control.disabled = !visible;
                    });
            }

            function selectedInpatientMode() {
                return inpatientModeInputs.find(
                    input => input.checked
                )?.value || '';
            }

            function syncInpatientModePanels(
                isInpatient = IS_INPATIENT_CLINIC
            ) {
                const mode = selectedInpatientMode();
                const isRoundTheClock =
                    mode === INPATIENT_MODE_ROUND_THE_CLOCK;

                if (inpatientWorkingScheduleBlock) {
                    inpatientWorkingScheduleBlock.hidden =
                        isRoundTheClock;

                    inpatientWorkingScheduleBlock
                        .querySelectorAll(
                            'input, select, textarea, button'
                        )
                        .forEach(control => {
                            control.disabled =
                                !isInpatient
                                || isRoundTheClock;
                        });
                }

                if (inpatientRoundClockNotice) {
                    inpatientRoundClockNotice.hidden =
                        !isInpatient
                        || !isRoundTheClock;
                }
            }

            // Тип учреждения задаётся при регистрации; в этой форме он не переключается.
            function syncPanelsForSavedClinicType() {
                const isInpatient = IS_INPATIENT_CLINIC;

                setPanelState(
                    clinicCareFormatsPanel,
                    !isInpatient
                );

                setPanelState(
                    clinicOutpatientSchedulePanel,
                    !isInpatient
                );

                setPanelState(
                    clinicInpatientSchedulePanel,
                    isInpatient
                );

                setPanelState(
                    clinicInpatientVisitingPanel,
                    isInpatient
                );

                syncInpatientModePanels(isInpatient);
            }

            function applySuggestedInpatientHours(mode) {
                if (
                    mode !== INPATIENT_MODE_DAY
                    && mode !== INPATIENT_MODE_NIGHT
                ) {
                    return;
                }

                const suggestedFrom =
                    mode === INPATIENT_MODE_NIGHT
                        ? '20:00'
                        : '08:00';

                const suggestedTo =
                    mode === INPATIENT_MODE_NIGHT
                        ? '08:00'
                        : '20:00';

                document
                    .querySelectorAll(
                        '[data-inpatient-working-day]'
                    )
                    .forEach(row => {
                        const from = row.querySelector(
                            '.inpatient-working-from'
                        );
                        const to = row.querySelector(
                            '.inpatient-working-to'
                        );

                        if (!from || !to) {
                            return;
                        }

                        const pair =
                            `${from.value}-${to.value}`;

                        const usesStandardPair = [
                            '08:00-20:00',
                            '20:00-08:00',
                            '-'
                        ].includes(pair);

                        if (!usesStandardPair) {
                            return;
                        }

                        from.value = suggestedFrom;
                        to.value = suggestedTo;
                    });
            }

            inpatientModeInputs.forEach(input => {
                input.addEventListener(
                    'change',
                    () => {
                        applySuggestedInpatientHours(
                            input.value
                        );
                        syncInpatientModePanels(IS_INPATIENT_CLINIC);
                    }
                );
            });

            syncPanelsForSavedClinicType();

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = String(value ?? '');
                return div.innerHTML;
            }

            function setAddressUnconfirmed() {
                addressConfirmed = false;
                addressState?.classList.remove('is-visible');
                confirmedBox?.classList.remove('is-visible');

                if (addressConfirmedInput) {
                    addressConfirmedInput.value = '0';
                }

                if (cityInput) {
                    cityInput.value = '';
                }

                if (latitudeInput) {
                    latitudeInput.value = '';
                }

                if (longitudeInput) {
                    longitudeInput.value = '';
                }
            }

            function normalizeCity(value) {
                return String(value || '')
                    .replace(/^(г|г\.|город|п|п\.|пос|пос\.|поселок|посёлок|с|с\.|деревня|д)\s+/i, '')
                    .trim();
            }

            function closeAddressSuggestions() {
                if (!suggestionsBox) return;

                suggestionsBox.classList.remove('is-open');
                suggestionsBox.innerHTML = '';
            }

            function renderAddressSuggestions(items) {
                if (!suggestionsBox) return;

                addressSuggestions = Array.isArray(items) ? items : [];

                if (addressSuggestions.length === 0) {
                    suggestionsBox.innerHTML = `
                        <div class="clinic-address-suggestion">
                            <i class="bi bi-geo-alt"></i>
                            <div>
                                <strong>Адрес не найден</strong>
                                <span>Уточните улицу, номер дома или населённый пункт.</span>
                            </div>
                        </div>
                    `;
                    suggestionsBox.classList.add('is-open');

                    return;
                }

                suggestionsBox.innerHTML = addressSuggestions
                    .map((item, index) => {
                        const details = [
                            item.postal_code,
                            item.region,
                            item.city
                        ]
                            .filter(Boolean)
                            .join(' · ');

                        return `
                            <button type="button"
                                    class="clinic-address-suggestion"
                                    data-address-index="${index}">
                                <i class="bi bi-geo-alt"></i>
                                <div>
                                    <strong>${escapeHtml(item.value || '')}</strong>
                                    <span>${escapeHtml(details || item.unrestricted_value || '')}</span>
                                </div>
                            </button>
                        `;
                    })
                    .join('');

                suggestionsBox.classList.add('is-open');

                suggestionsBox
                    .querySelectorAll('[data-address-index]')
                    .forEach(button => {
                        button.addEventListener('click', () => {
                            const item = addressSuggestions[
                                Number(button.dataset.addressIndex)
                                ];

                            if (!item) return;

                            confirmAddress(item);
                        });
                    });
            }

            async function searchAddress(query) {
                const value = String(query || '').trim();

                if (value.length < 2) {
                    closeAddressSuggestions();

                    return;
                }

                if (addressSearchController) {
                    addressSearchController.abort();
                }

                addressSearchController = new AbortController();

                if (suggestionsBox) {
                    suggestionsBox.innerHTML = `
                        <div class="clinic-address-suggestion">
                            <i class="bi bi-search"></i>
                            <div>
                                <strong>Ищем адрес...</strong>
                                <span>Подсказки DaData</span>
                            </div>
                        </div>
                    `;
                    suggestionsBox.classList.add('is-open');
                }

                try {
                    const url = new URL(
                        ADDRESS_SEARCH_URL,
                        window.location.origin
                    );

                    url.searchParams.set('query', value);

                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        signal: addressSearchController.signal,
                    });

                    if (!response.ok) {
                        throw new Error(
                            `Address search failed: ${response.status}`
                        );
                    }

                    const data = await response.json();

                    renderAddressSuggestions(
                        data.suggestions || []
                    );
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error(
                        '[clinic-settings] address search failed',
                        error
                    );

                    if (suggestionsBox) {
                        suggestionsBox.innerHTML = `
                            <div class="clinic-address-suggestion">
                                <i class="bi bi-exclamation-circle"></i>
                                <div>
                                    <strong>Не удалось получить подсказки</strong>
                                    <span>Попробуйте повторить поиск.</span>
                                </div>
                            </div>
                        `;
                        suggestionsBox.classList.add('is-open');
                    }
                }
            }

            function scheduleAddressSearch(query) {
                window.clearTimeout(addressSearchTimer);

                addressSearchTimer = window.setTimeout(() => {
                    searchAddress(query);
                }, 300);
            }

            function setMapPlaceholderState(
                state,
                title,
                message
            ) {
                if (!mapPlaceholder) {
                    return;
                }

                mapPlaceholder.classList.toggle(
                    'is-clickable',
                    state === 'ready'
                    || state === 'error'
                );

                mapPlaceholder.classList.toggle(
                    'is-loading',
                    state === 'loading'
                );

                mapPlaceholder.classList.toggle(
                    'is-error',
                    state === 'error'
                );

                if (
                    state === 'ready'
                    || state === 'error'
                ) {
                    mapPlaceholder.setAttribute(
                        'role',
                        'button'
                    );

                    mapPlaceholder.setAttribute(
                        'tabindex',
                        '0'
                    );
                } else {
                    mapPlaceholder.removeAttribute(
                        'role'
                    );

                    mapPlaceholder.removeAttribute(
                        'tabindex'
                    );
                }

                if (mapPlaceholderTitle && title) {
                    mapPlaceholderTitle.textContent =
                        title;
                }

                if (mapPlaceholderText && message) {
                    mapPlaceholderText.textContent =
                        message;
                }
            }

            function storedClinicCoordinates() {
                const latitude = Number.parseFloat(
                    latitudeInput?.value || ''
                );

                const longitude = Number.parseFloat(
                    longitudeInput?.value || ''
                );

                if (
                    !Number.isFinite(latitude)
                    || !Number.isFinite(longitude)
                ) {
                    return null;
                }

                return [
                    latitude,
                    longitude
                ];
            }

            function yandexMapsReady() {
                return new Promise(
                    (resolve, reject) => {
                        if (
                            !window.ymaps
                            || typeof window.ymaps.ready
                                !== 'function'
                        ) {
                            reject(
                                new Error(
                                    'API Яндекс.Карт ещё не загружен.'
                                )
                            );

                            return;
                        }

                        window.ymaps.ready(
                            () => resolve()
                        );
                    }
                );
            }

            async function initYandexMap(
                coords,
                address
            ) {
                const mapElement =
                    document.getElementById(
                        'clinicMap'
                    );

                if (!mapElement) {
                    return;
                }

                await yandexMapsReady();

                if (!yandexMap) {
                    yandexMap = new ymaps.Map(
                        'clinicMap',
                        {
                            center: coords,
                            zoom: 16,
                            controls: [
                                'zoomControl'
                            ]
                        },
                        {
                            suppressMapOpenBlock:
                                true
                        }
                    );
                } else {
                    yandexMap.setCenter(
                        coords,
                        16,
                        {
                            checkZoomRange: true
                        }
                    );
                }

                if (yandexPlacemark) {
                    yandexMap.geoObjects.remove(
                        yandexPlacemark
                    );
                }

                yandexPlacemark =
                    new ymaps.Placemark(
                        coords,
                        {
                            hintContent: address,
                            balloonContent: address
                        },
                        {
                            preset:
                                'islands#tealMedicalIcon'
                        }
                    );

                yandexMap.geoObjects.add(
                    yandexPlacemark
                );

                mapPlaceholder?.classList.add(
                    'is-hidden'
                );
            }

            async function geocodeAddressViaBrowserOsm(
                address
            ) {
                const url = new URL(
                    'https://nominatim.openstreetmap.org/search'
                );

                url.searchParams.set(
                    'q',
                    address
                );

                url.searchParams.set(
                    'format',
                    'jsonv2'
                );

                url.searchParams.set(
                    'addressdetails',
                    '1'
                );

                url.searchParams.set(
                    'limit',
                    '1'
                );

                url.searchParams.set(
                    'countrycodes',
                    'ru'
                );

                url.searchParams.set(
                    'accept-language',
                    'ru'
                );

                const response = await fetch(
                    url.toString(),
                    {
                        method: 'GET',
                        mode: 'cors',
                        headers: {
                            Accept: 'application/json'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `OSM geocoding failed: HTTP ${response.status}`
                    );
                }

                const items =
                    await response.json();

                const first =
                    Array.isArray(items)
                        ? items[0]
                        : null;

                const latitude =
                    Number.parseFloat(
                        first?.lat
                    );

                const longitude =
                    Number.parseFloat(
                        first?.lon
                    );

                if (
                    !Number.isFinite(latitude)
                    || !Number.isFinite(longitude)
                ) {
                    throw new Error(
                        'OpenStreetMap не вернул координаты адреса.'
                    );
                }

                return [
                    latitude,
                    longitude
                ];
            }

            function storeResolvedCoordinates(
                coords
            ) {
                if (
                    !Array.isArray(coords)
                    || coords.length < 2
                ) {
                    return;
                }

                if (latitudeInput) {
                    latitudeInput.value =
                        String(coords[0]);
                }

                if (longitudeInput) {
                    longitudeInput.value =
                        String(coords[1]);
                }
            }

            async function geocodeConfirmedAddress(
                address
            ) {
                /*
                 * Сначала используем наш Laravel endpoint.
                 * Если сервер не имеет выхода к DaData/OSM и отвечает
                 * 5xx, пробуем OSM непосредственно из браузера.
                 *
                 * Это нужно только для старых записей, где адрес уже
                 * подтверждён, но latitude/longitude ранее не сохранялись.
                 */
                try {
                    const url = new URL(
                        ADDRESS_SEARCH_URL,
                        window.location.origin
                    );

                    url.searchParams.set(
                        'query',
                        address
                    );

                    const response = await fetch(
                        url.toString(),
                        {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest'
                            }
                        }
                    );

                    if (!response.ok) {
                        throw new Error(
                            `Address API HTTP ${response.status}`
                        );
                    }

                    const data =
                        await response.json();

                    const suggestions =
                        Array.isArray(
                            data?.suggestions
                        )
                            ? data.suggestions
                            : [];

                    const suggestion =
                        suggestions.find(item => {
                            const latitude =
                                Number.parseFloat(
                                    item?.geo_lat
                                );

                            const longitude =
                                Number.parseFloat(
                                    item?.geo_lon
                                );

                            return Number.isFinite(
                                latitude
                            )
                                && Number.isFinite(
                                    longitude
                                );
                        });

                    if (suggestion) {
                        const coords = [
                            Number.parseFloat(
                                suggestion.geo_lat
                            ),
                            Number.parseFloat(
                                suggestion.geo_lon
                            )
                        ];

                        storeResolvedCoordinates(
                            coords
                        );

                        return coords;
                    }
                } catch (error) {
                    console.warn(
                        '[clinic-settings] server geocoder unavailable, using browser OSM fallback',
                        error
                    );
                }

                const coords =
                    await geocodeAddressViaBrowserOsm(
                        address
                    );

                storeResolvedCoordinates(
                    coords
                );

                return coords;
            }

            async function loadConfirmedAddressMap() {
                if (yandexMap) {
                    return;
                }

                const address = String(
                    addressInput?.value || ''
                ).trim();

                const isConfirmed =
                    addressConfirmed
                    || addressConfirmedInput
                        ?.value === '1';

                if (
                    !isConfirmed
                    || address === ''
                ) {
                    return;
                }

                setMapPlaceholderState(
                    'loading',
                    'Загружаем карту…',
                    'Получаем координаты через серверный поиск адресов.'
                );

                try {
                    let coords =
                        storedClinicCoordinates();

                    if (!coords) {
                        coords =
                            await geocodeConfirmedAddress(
                                address
                            );
                    }

                    await initYandexMap(
                        coords,
                        address
                    );

                    /*
                     * Для старой записи координаты могли появиться
                     * только сейчас. Они уже находятся в hidden-полях
                     * формы и сохранятся вместе с настройками клиники.
                     */
                    if (
                        mapPlaceholderText
                        && latitudeInput?.value
                        && longitudeInput?.value
                    ) {
                        mapPlaceholderText.textContent =
                            'Координаты определены. Нажмите «Сохранить настройки», чтобы больше не запрашивать их повторно.';
                    }
                } catch (error) {
                    console.error(
                        '[clinic-settings] lazy map load failed',
                        error
                    );

                    setMapPlaceholderState(
                        'error',
                        'Не удалось загрузить карту',
                        'Нажмите ещё раз, чтобы повторить попытку.'
                    );
                }
            }

            mapShell?.addEventListener(
                'click',
                () => {
                    if (!yandexMap) {
                        loadConfirmedAddressMap();
                    }
                }
            );

            mapPlaceholder?.addEventListener(
                'keydown',
                event => {
                    if (
                        event.key !== 'Enter'
                        && event.key !== ' '
                    ) {
                        return;
                    }

                    event.preventDefault();
                    loadConfirmedAddressMap();
                }
            );

            function confirmAddress(item) {
                if (!addressInput) return;

                const latitude = Number.parseFloat(item.geo_lat);
                const longitude = Number.parseFloat(item.geo_lon);
                const hasCoordinates = Number.isFinite(latitude)
                    && Number.isFinite(longitude);

                addressInput.value = item.value || '';
                closeAddressSuggestions();

                addressConfirmed = true;
                addressState?.classList.add('is-visible');

                if (addressConfirmedInput) {
                    addressConfirmedInput.value = '1';
                }

                if (cityInput) {
                    cityInput.value = normalizeCity(item.city);
                }

                if (latitudeInput) {
                    latitudeInput.value = hasCoordinates
                        ? String(latitude)
                        : '';
                }

                if (longitudeInput) {
                    longitudeInput.value = hasCoordinates
                        ? String(longitude)
                        : '';
                }

                if (confirmedText) {
                    confirmedText.textContent =
                        `Подтверждённый адрес: ${item.value || ''}`;
                }

                confirmedBox?.classList.add('is-visible');

                if (hasCoordinates) {
                    initYandexMap(
                        [
                            latitude,
                            longitude
                        ],
                        item.value || ''
                    ).catch(error => {
                        console.error(
                            '[clinic-settings] map init failed',
                            error
                        );

                        mapPlaceholder?.classList.remove(
                            'is-hidden'
                        );

                        setMapPlaceholderState(
                            'ready',
                            'Адрес подтверждён',
                            'Нажмите, чтобы загрузить карту.'
                        );
                    });
                } else {
                    mapPlaceholder?.classList.remove(
                        'is-hidden'
                    );

                    setMapPlaceholderState(
                        'ready',
                        'Адрес подтверждён',
                        'Нажмите, чтобы загрузить карту.'
                    );
                }
            }

            addressInput?.addEventListener('input', () => {
                setAddressUnconfirmed();
                scheduleAddressSearch(addressInput.value);
            });

            addressInput?.addEventListener('focus', () => {
                scheduleAddressSearch(addressInput.value);
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('.clinic-settings-address')) {
                    suggestionsBox?.classList.remove('is-open');
                }
            });

            if (addressInput?.value) {
                addressConfirmed = true;

                addressState?.classList.add(
                    'is-visible'
                );

                if (addressConfirmedInput) {
                    addressConfirmedInput.value =
                        '1';
                }

                if (confirmedText) {
                    confirmedText.textContent =
                        `Текущий адрес: ${addressInput.value}`;
                }

                confirmedBox?.classList.add(
                    'is-visible'
                );

                mapPlaceholder?.classList.remove(
                    'is-hidden'
                );

                setMapPlaceholderState(
                    'ready',
                    'Адрес уже подтверждён',
                    'Нажмите, чтобы загрузить карту и показать клинику.'
                );
            }

            const phoneList = document.getElementById('clinicPhoneList');
            const addPhoneButton = document.getElementById('addClinicPhoneButton');

            function reindexPhones() {
                phoneList?.querySelectorAll('.clinic-phone-row').forEach((row, index) => {
                    const number = row.querySelector('.clinic-phone-number');
                    const label = row.querySelector('.clinic-phone-label');

                    if (number) {
                        number.name = `phones[${index}][phone]`;
                    }

                    if (label) {
                        label.name = `phones[${index}][label]`;
                    }
                });
            }

            function bindRemovePhoneButtons() {
                phoneList?.querySelectorAll('.clinic-remove-phone').forEach(button => {
                    button.onclick = () => {
                        const rows = phoneList.querySelectorAll('.clinic-phone-row');

                        if (rows.length <= 1) {
                            const row = rows[0];

                            if (row) {
                                row.querySelector('.clinic-phone-number').value = '';
                                row.querySelector('.clinic-phone-label').value = '';
                            }

                            return;
                        }

                        button.closest('.clinic-phone-row')?.remove();
                        reindexPhones();
                    };
                });
            }

            addPhoneButton?.addEventListener('click', () => {
                if (!phoneList) return;

                phoneList.insertAdjacentHTML('beforeend', `
                    <div class="clinic-phone-row">
                        <input type="text"
                               class="settings-control clinic-phone-number"
                               value=""
                               placeholder="+7 (...) ...-..-..">

                        <input type="text"
                               class="settings-control clinic-phone-label"
                               value=""
                               placeholder="Например: детская регистратура">

                        <button type="button"
                                class="clinic-icon-button clinic-icon-button--danger clinic-remove-phone"
                                title="Удалить телефон">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `);

                reindexPhones();
                bindRemovePhoneButtons();
            });

            reindexPhones();
            bindRemovePhoneButtons();

            document
                .querySelectorAll(
                    '.clinic-working-day-toggle, '
                    + '.inpatient-working-day-toggle, '
                    + '.inpatient-visiting-day-toggle'
                )
                .forEach(toggle => {
                    toggle.addEventListener('change', () => {
                        const row = toggle.closest(
                            '.clinic-working-day'
                        );

                        row?.classList.toggle(
                            'is-closed',
                            !toggle.checked
                        );
                    });
                });

            /*
             * В графике посещений поля времени не должны выглядеть
             * заблокированными. Если пользователь начинает вводить
             * время в выключенном дне, включаем этот день автоматически.
             */
            document
                .querySelectorAll(
                    '[data-inpatient-visiting-day] '
                    + '.inpatient-visiting-from, '
                    + '[data-inpatient-visiting-day] '
                    + '.inpatient-visiting-to'
                )
                .forEach(control => {
                    const enableVisitingDay = () => {
                        const row = control.closest(
                            '[data-inpatient-visiting-day]'
                        );

                        const toggle = row?.querySelector(
                            '.inpatient-visiting-day-toggle'
                        );

                        if (!toggle || toggle.disabled) {
                            return;
                        }

                        if (!toggle.checked) {
                            toggle.checked = true;
                            toggle.dispatchEvent(
                                new Event('change', {
                                    bubbles: true
                                })
                            );
                        }
                    };

                    control.addEventListener(
                        'focus',
                        enableVisitingDay
                    );

                    control.addEventListener(
                        'pointerdown',
                        enableVisitingDay
                    );
                });

            function bindScheduleCopy({
                buttonId,
                rowAttribute,
                toggleSelector,
                fromSelector,
                toSelector,
                targetDays
            }) {
                document
                    .getElementById(buttonId)
                    ?.addEventListener('click', () => {
                        const monday = document.querySelector(
                            `[${rowAttribute}="mon"]`
                        );

                        if (!monday) {
                            return;
                        }

                        const mondayEnabled = monday.querySelector(
                            toggleSelector
                        )?.checked ?? true;

                        const mondayFrom = monday.querySelector(
                            fromSelector
                        )?.value ?? '';

                        const mondayTo = monday.querySelector(
                            toSelector
                        )?.value ?? '';

                        targetDays.forEach(code => {
                            const row = document.querySelector(
                                `[${rowAttribute}="${code}"]`
                            );

                            if (!row) {
                                return;
                            }

                            const toggle = row.querySelector(
                                toggleSelector
                            );
                            const from = row.querySelector(
                                fromSelector
                            );
                            const to = row.querySelector(
                                toSelector
                            );

                            if (toggle) {
                                toggle.checked = mondayEnabled;
                            }

                            if (from) {
                                from.value = mondayFrom;
                            }

                            if (to) {
                                to.value = mondayTo;
                            }

                            row.classList.toggle(
                                'is-closed',
                                !mondayEnabled
                            );
                        });
                    });
            }

            bindScheduleCopy({
                buttonId: 'copyMondayButton',
                rowAttribute: 'data-working-day',
                toggleSelector: '.clinic-working-day-toggle',
                fromSelector: '.clinic-working-from',
                toSelector: '.clinic-working-to',
                targetDays: ['tue', 'wed', 'thu', 'fri']
            });

            bindScheduleCopy({
                buttonId: 'copyInpatientMondayButton',
                rowAttribute: 'data-inpatient-working-day',
                toggleSelector: '.inpatient-working-day-toggle',
                fromSelector: '.inpatient-working-from',
                toSelector: '.inpatient-working-to',
                targetDays: [
                    'tue', 'wed', 'thu',
                    'fri', 'sat', 'sun'
                ]
            });

            bindScheduleCopy({
                buttonId: 'copyVisitingMondayButton',
                rowAttribute: 'data-inpatient-visiting-day',
                toggleSelector: '.inpatient-visiting-day-toggle',
                fromSelector: '.inpatient-visiting-from',
                toSelector: '.inpatient-visiting-to',
                targetDays: [
                    'tue', 'wed', 'thu',
                    'fri', 'sat', 'sun'
                ]
            });

            const addDoctorModalElement = document.getElementById('clinicAddDoctorModal');
            const addDoctorModal = addDoctorModalElement
                ? bootstrap.Modal.getOrCreateInstance(addDoctorModalElement)
                : null;

            const clinicDoctorSearch = document.getElementById('clinicDoctorSearch');
            const clinicDoctorResults = document.getElementById('clinicDoctorResults');
            const clinicDoctorSelected = document.getElementById('clinicDoctorSelected');
            const clinicDoctorSelectedName = document.getElementById('clinicDoctorSelectedName');
            const clinicDoctorSelectedMeta = document.getElementById('clinicDoctorSelectedMeta');
            const addSelectedDoctorButton = document.getElementById('addSelectedDoctorButton');
            const clinicNewEmployeePositionWrap =
                document.getElementById('clinicNewEmployeePositionWrap');
            const clinicNewEmployeePosition =
                document.getElementById('clinicNewEmployeePosition');

            let selectedClinicDoctor = null;

            function currentClinicDoctorIds() {
                return new Set(
                    Array.from(
                        document.querySelectorAll('#clinicStaffList .employee-id')
                    )
                        .map(input => String(input.value || ''))
                        .filter(Boolean)
                );
            }

            function clearSelectedClinicDoctor() {
                selectedClinicDoctor = null;

                clinicDoctorResults
                    ?.querySelectorAll('.clinic-doctor-option')
                    .forEach(option => option.classList.remove('is-selected'));

                clinicDoctorSelected?.classList.remove('is-visible');
                clinicNewEmployeePositionWrap?.classList.add('d-none');

                if (clinicNewEmployeePosition) {
                    clinicNewEmployeePosition.value =
                        'Врач';
                }

                if (clinicDoctorSelectedName) {
                    clinicDoctorSelectedName.textContent = '';
                }

                if (clinicDoctorSelectedMeta) {
                    clinicDoctorSelectedMeta.textContent = '';
                }

                if (addSelectedDoctorButton) {
                    addSelectedDoctorButton.disabled = true;
                }
            }

            function filterClinicDoctors() {
                const query = String(clinicDoctorSearch?.value || '')
                    .trim()
                    .toLowerCase();

                const usedIds = currentClinicDoctorIds();

                clinicDoctorResults
                    ?.querySelectorAll('.clinic-doctor-option')
                    .forEach(option => {
                        const haystack = [
                            option.dataset.surname,
                            option.dataset.name,
                            option.dataset.patronym,
                            option.dataset.position,
                            option.dataset.speciality,
                            option.dataset.phone,
                            option.dataset.email,
                        ]
                            .filter(Boolean)
                            .join(' ')
                            .toLowerCase();

                        const alreadyAdded = usedIds.has(
                            String(option.dataset.doctorId || '')
                        );

                        const visible = !alreadyAdded
                            && (!query || haystack.includes(query));

                        option.classList.toggle('d-none', !visible);
                    });
            }

            clinicDoctorSearch?.addEventListener('input', () => {
                clearSelectedClinicDoctor();
                filterClinicDoctors();
            });

            clinicDoctorResults
                ?.querySelectorAll('.clinic-doctor-option')
                .forEach(option => {
                    option.addEventListener('click', () => {
                        clearSelectedClinicDoctor();

                        selectedClinicDoctor = {
                            id: option.dataset.doctorId || '',
                            surname: option.dataset.surname || '',
                            name: option.dataset.name || '',
                            patronym: option.dataset.patronym || '',
                            position: option.dataset.position || 'Врач',
                            speciality: option.dataset.speciality || '',
                            phone: option.dataset.phone || '',
                            email: option.dataset.email || '',
                        };

                        option.classList.add('is-selected');

                        if (clinicDoctorSelectedName) {
                            clinicDoctorSelectedName.textContent = [
                                selectedClinicDoctor.surname,
                                selectedClinicDoctor.name,
                                selectedClinicDoctor.patronym,
                            ].filter(Boolean).join(' ');
                        }

                        if (clinicDoctorSelectedMeta) {
                            clinicDoctorSelectedMeta.textContent = [
                                selectedClinicDoctor.speciality,
                                selectedClinicDoctor.position,
                            ].filter(Boolean).join(' · ');
                        }

                        clinicDoctorSelected?.classList.add('is-visible');

                        if (clinicNewEmployeePosition) {
                            const knownPositions = new Set([
                                'Врач',
                                'Детский психиатр',
                                'Медицинский психолог',
                                'Медицинский регистратор',
                                'Санитар',
                            ]);

                            clinicNewEmployeePosition.value =
                                knownPositions.has(
                                    selectedClinicDoctor.position
                                )
                                    ? selectedClinicDoctor.position
                                    : 'Врач';
                        }

                        clinicNewEmployeePositionWrap?.classList.remove(
                            'd-none'
                        );

                        if (addSelectedDoctorButton) {
                            addSelectedDoctorButton.disabled = false;
                        }
                    });
                });

            const employeeModalElement = document.getElementById('clinicEmployeeModal');
            const employeeModal = employeeModalElement
                ? bootstrap.Modal.getOrCreateInstance(employeeModalElement)
                : null;

            const employeeForm = document.getElementById('clinicEmployeeForm');
            const employeeEditIndex = document.getElementById('employeeEditIndex');
            const employeeDoctorId = document.getElementById('employeeDoctorId');
            const employeeProfileAvatar = document.getElementById('employeeProfileAvatar');
            const employeeProfileName = document.getElementById('employeeProfileName');
            const employeeProfileMeta = document.getElementById('employeeProfileMeta');
            const employeePosition = document.getElementById('employeePosition');
            const employeeSpecialitySection =
                document.getElementById('employeeSpecialitySection');
            const employeeState = document.getElementById('employeeState');
            const employeePhone = document.getElementById('employeePhone');
            const employeeEmail = document.getElementById('employeeEmail');
            const employeeDismissedAt = document.getElementById('employeeDismissedAt');
            const employeeDismissalField = document.getElementById('employeeDismissalField');
            const dismissEmployeeButton = document.getElementById('dismissEmployeeButton');
            const headmasterTransferSection = document.getElementById('headmasterTransferSection');
            const newHeadmasterId = document.getElementById('newHeadmasterId');
            const staffList = document.getElementById('clinicStaffList');
            const staffPagination =
                document.getElementById('clinicStaffPagination');

            const STAFF_PAGE_SIZE = 6;
            let staffCurrentPage = 1;

            function staffCards() {
                return staffList
                    ? Array.from(
                        staffList.querySelectorAll(
                            '.clinic-staff-card'
                        )
                    )
                    : [];
            }

            function renderStaffPagination() {
                if (!staffPagination) {
                    return;
                }

                const cards = staffCards();
                const pageCount = Math.max(
                    1,
                    Math.ceil(
                        cards.length / STAFF_PAGE_SIZE
                    )
                );

                staffCurrentPage = Math.min(
                    staffCurrentPage,
                    pageCount
                );

                const start =
                    (staffCurrentPage - 1)
                    * STAFF_PAGE_SIZE;

                const end =
                    start + STAFF_PAGE_SIZE;

                cards.forEach((card, index) => {
                    card.classList.toggle(
                        'is-pagination-hidden',
                        index < start || index >= end
                    );
                });

                if (pageCount <= 1) {
                    staffPagination.innerHTML = '';
                    staffPagination.classList.remove(
                        'is-visible'
                    );
                    return;
                }

                const buttons = [];

                buttons.push(`
                    <button type="button"
                            class="clinic-staff-pagination__button"
                            data-staff-page="${staffCurrentPage - 1}"
                            ${staffCurrentPage <= 1 ? 'disabled' : ''}>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                `);

                for (
                    let page = 1;
                    page <= pageCount;
                    page++
                ) {
                    buttons.push(`
                        <button type="button"
                                class="clinic-staff-pagination__button ${page === staffCurrentPage ? 'is-active' : ''}"
                                data-staff-page="${page}">
                            ${page}
                        </button>
                    `);
                }

                buttons.push(`
                    <button type="button"
                            class="clinic-staff-pagination__button"
                            data-staff-page="${staffCurrentPage + 1}"
                            ${staffCurrentPage >= pageCount ? 'disabled' : ''}>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                `);

                staffPagination.innerHTML =
                    buttons.join('');

                staffPagination.classList.add(
                    'is-visible'
                );

                staffPagination
                    .querySelectorAll(
                        '[data-staff-page]'
                    )
                    .forEach(button => {
                        button.addEventListener(
                            'click',
                            () => {
                                if (button.disabled) {
                                    return;
                                }

                                const page = Number(
                                    button.dataset.staffPage
                                );

                                if (
                                    !Number.isInteger(page)
                                    || page < 1
                                    || page > pageCount
                                ) {
                                    return;
                                }

                                staffCurrentPage = page;
                                renderStaffPagination();
                            }
                        );
                    });
            }

            let editingHeadmaster = false;

            function employeeStatusText(status) {
                if (status === 'temporarily_absent') {
                    return 'Временно отсутствует';
                }

                if (status === 'dismissed') {
                    return 'Уволен';
                }

                return 'Работает';
            }

            function employeeStatusClass(status) {
                if (status === 'temporarily_absent') {
                    return 'is-absent';
                }

                if (status === 'dismissed') {
                    return 'is-dismissed';
                }

                return '';
            }

            function selectedEmployeeSpecialities() {
                return Array.from(
                    document.querySelectorAll('.employee-speciality-option:checked')
                ).map(input => input.value);
            }

            function normalizeEmployeeSpeciality(value) {
                const normalized = String(value || '')
                    .replace(/\u00a0/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .toLocaleLowerCase('ru-RU');

                const map = {
                    'психиатр': 'Психиатрия',
                    'психиатрия': 'Психиатрия',

                    'психиатр-нарколог': 'Психиатр-нарколог',
                    'врач-психиатр-нарколог': 'Психиатр-нарколог',
                    'психиатрия-наркология': 'Психиатр-нарколог',

                    'детский психиатр': 'Детская психиатрия',
                    'врач-психиатр детский': 'Детская психиатрия',
                    'детская психиатрия': 'Детская психиатрия',

                    'психотерапевт': 'Психотерапия',
                    'психотерапия': 'Психотерапия',
                    'детский психотерапевт': 'Психотерапия',

                    'медицинский психолог': 'Клиническая психология',
                    'клинический психолог': 'Клиническая психология',
                    'клиническая психология': 'Клиническая психология',

                    'медицинская сестра': 'Сестринское дело',
                    'старшая медицинская сестра': 'Сестринское дело',
                    'процедурная медицинская сестра': 'Сестринское дело',
                    'сестринское дело': 'Сестринское дело',
                };

                return map[normalized] || String(value || '').trim();
            }

            function employeeSpecialityLabel(value) {
                const map = {
                    'Психиатрия': 'Психиатр',
                    'Психиатр-нарколог': 'Психиатр-нарколог',
                    'Детская психиатрия': 'Детский психиатр',
                    'Психотерапия': 'Психотерапевт',
                    'Клиническая психология': 'Клинический психолог',
                    'Сестринское дело': 'Сестринское дело',
                };

                const normalized = normalizeEmployeeSpeciality(value);

                return map[normalized] || String(value || '').trim();
            }

            function employeeSpecialitiesForForm(values) {
                return Array.from(values || [])
                    .flatMap(value => {
                        return String(value || '')
                            .split(/[,;|]/);
                    })
                    .map(normalizeEmployeeSpeciality)
                    .filter(Boolean)
                    .filter((value, index, array) => {
                        return array.indexOf(value) === index;
                    });
            }

            function clearEmployeeModal() {
                employeeEditIndex.value = '';
                employeePosition.value = 'Врач';
                employeeState.value = 'active';
                employeePhone.value = '';
                employeeEmail.value = '';
                employeeDismissedAt.value = '';

                document.querySelectorAll('.employee-speciality-option').forEach(input => {
                    input.checked = false;
                });

                employeeDismissalField?.classList.remove('is-visible');
                dismissEmployeeButton?.classList.add('d-none');

                editingHeadmaster = false;
                headmasterTransferSection?.classList.add('d-none');

                updateEmployeeSpecialityVisibility();

                if (newHeadmasterId) {
                    newHeadmasterId.required = false;
                    newHeadmasterId.value = '';

                    Array.from(newHeadmasterId.options).forEach(option => {
                        option.disabled = false;
                    });
                }
            }

            function updateEmployeeSpecialityVisibility() {
                const isRegistrar =
                    employeePosition?.value
                    === 'Медицинский регистратор';

                employeeSpecialitySection?.classList.toggle(
                    'd-none',
                    isRegistrar
                );

                document
                    .querySelectorAll(
                        '.employee-speciality-option'
                    )
                    .forEach(input => {
                        input.disabled =
                            isRegistrar;

                        if (isRegistrar) {
                            input.checked = false;
                        }
                    });

                if (
                    isRegistrar
                    && employeeProfileMeta
                ) {
                    employeeProfileMeta.textContent =
                        'Медицинский регистратор';
                }
            }

            function updateDismissalVisibility() {
                const dismissed = employeeState.value === 'dismissed';
                employeeDismissalField?.classList.toggle('is-visible', dismissed);

                if (
                    dismissed
                    && employeeDismissedAt
                    && !employeeDismissedAt.value
                ) {
                    employeeDismissedAt.value = new Date()
                        .toISOString()
                        .slice(0, 10);
                }
            }

            function updateHeadmasterTransferVisibility() {
                const transferRequired = editingHeadmaster
                    && (
                        employeeState.value === 'dismissed'
                        || employeePosition.value !== 'Главный врач'
                    );

                headmasterTransferSection?.classList.toggle(
                    'd-none',
                    !transferRequired
                );

                if (!newHeadmasterId) {
                    return;
                }

                newHeadmasterId.required = transferRequired;

                if (!transferRequired) {
                    newHeadmasterId.value = '';
                }
            }

            function reindexEmployees() {
                staffList?.querySelectorAll('.clinic-staff-card').forEach((card, index) => {
                    card.dataset.employeeIndex = String(index);

                    const fields = {
                        '.employee-id': 'id',
                        '.employee-surname': 'surname',
                        '.employee-name': 'name',
                        '.employee-patronym': 'patronym',
                        '.employee-position': 'position',
                        '.employee-status-value': 'employment_status',
                        '.employee-dismissed-at': 'dismissed_at',
                        '.employee-phone': 'phone',
                        '.employee-email': 'email',
                    };

                    Object.entries(fields).forEach(([selector, field]) => {
                        const input = card.querySelector(selector);

                        if (input) {
                            input.name = `employees[${index}][${field}]`;
                        }
                    });

                    card.querySelectorAll('.employee-speciality-value').forEach((input, specialityIndex) => {
                        input.name = `employees[${index}][specialties][${specialityIndex}]`;
                    });
                });
            }

            function setCardStatus(card, status, dismissedAt = '') {
                const statusElement = card.querySelector('.clinic-staff-status');

                statusElement.classList.remove('is-absent', 'is-dismissed');

                const statusClass = employeeStatusClass(status);

                if (statusClass) {
                    statusElement.classList.add(statusClass);
                }

                statusElement.textContent = employeeStatusText(status);

                if (status === 'dismissed' && dismissedAt) {
                    statusElement.textContent = `Уволен с ${dismissedAt.split('-').reverse().join('.')}`;
                }

                card.classList.toggle('is-dismissed', status === 'dismissed');
            }

            function fillEmployeeModal(card) {
                const index = card.dataset.employeeIndex;
                const surname = card.querySelector('.employee-surname')?.value || '';
                const name = card.querySelector('.employee-name')?.value || '';
                const patronym = card.querySelector('.employee-patronym')?.value || '';

                const storedSpecialities = Array.from(
                    card.querySelectorAll('.employee-speciality-value')
                )
                    .map(input => input.value)
                    .filter(Boolean);

                const speciality = employeeSpecialitiesForForm(
                    storedSpecialities.length
                        ? storedSpecialities
                        : [card.dataset.speciality || '']
                );

                employeeForm?.reset();

                employeeEditIndex.value = index;
                employeeDoctorId.value = card.dataset.doctorId
                    || card.querySelector('.employee-id')?.value
                    || '';

                editingHeadmaster = card.dataset.isHeadmaster === '1';

                employeePosition.value = editingHeadmaster
                    ? 'Главный врач'
                    : (
                        card.dataset.position
                        || card.querySelector('.employee-position')?.value
                        || 'Врач'
                    );

                employeeState.value =
                    card.querySelector('.employee-status-value')?.value
                    || 'active';

                employeePhone.value =
                    card.dataset.phone
                    || card.querySelector('.employee-phone')?.value
                    || '';

                employeeEmail.value =
                    card.dataset.email
                    || card.querySelector('.employee-email')?.value
                    || '';

                employeeDismissedAt.value =
                    card.querySelector('.employee-dismissed-at')?.value
                    || '';

                const selectedSpecialities = new Set(
                    speciality.map(normalizeEmployeeSpeciality)
                );

                document.querySelectorAll('.employee-speciality-option')
                    .forEach(input => {
                        input.checked = selectedSpecialities.has(
                            normalizeEmployeeSpeciality(input.value)
                        );
                    });

                if (newHeadmasterId) {
                    Array.from(newHeadmasterId.options).forEach(option => {
                        option.disabled = option.value === employeeDoctorId.value;
                    });

                    newHeadmasterId.value = '';
                }

                if (employeeProfileName) {
                    employeeProfileName.textContent = [
                        surname,
                        name,
                        patronym,
                    ].filter(Boolean).join(' ');
                }

                if (employeeProfileMeta) {
                    employeeProfileMeta.textContent = storedSpecialities.length
                        ? storedSpecialities.join(', ')
                        : (card.dataset.speciality || 'Специальность не указана');
                }

                if (employeeProfileAvatar) {
                    employeeProfileAvatar.textContent =
                        `${name?.[0] || ''}${surname?.[0] || ''}`.toUpperCase();
                }

                dismissEmployeeButton?.classList.remove('d-none');

                updateEmployeeSpecialityVisibility();
                updateDismissalVisibility();
                updateHeadmasterTransferVisibility();
            }

            function employeeCardTemplate(data, index) {
                const fullName = [
                    data.surname,
                    data.name,
                    data.patronym
                ].filter(Boolean).join(' ');

                const initials = `${data.name?.[0] || ''}${data.surname?.[0] || ''}`.toUpperCase();
                const specialityBadges = data.specialties
                    .map(employeeSpecialityLabel)
                    .filter(Boolean)
                    .filter((value, index, array) => {
                        return array.indexOf(value) === index;
                    });

                const specialityBadgesHtml =
                    data.position === 'Медицинский регистратор'
                        ? `
                            <span class="clinic-staff-position">
                                Регистратура
                            </span>
                        `
                        : (
                            specialityBadges.length
                                ? specialityBadges
                                    .map(value => `
                                        <span class="clinic-staff-position">
                                            ${escapeHtml(value)}
                                        </span>
                                    `)
                                    .join('')
                                : `
                                    <span class="clinic-staff-position clinic-staff-position--empty">
                                        Специальность не указана
                                    </span>
                                `
                        );

                const specialityInputs = data.specialties
                    .map((value, specialityIndex) => `
                        <input type="hidden"
                               name="employees[${index}][specialties][${specialityIndex}]"
                               class="employee-speciality-value"
                               value="${escapeHtml(value)}">
                    `)
                    .join('');

                return `
                    <article class="clinic-staff-card ${data.employment_status === 'dismissed' ? 'is-dismissed' : ''}"
                             data-employee-index="${index}"
                             data-is-headmaster="0">
                        <input type="hidden"
                               name="employees[${index}][id]"
                               class="employee-id"
                               value="${escapeHtml(data.id || '')}">

                        <input type="hidden"
                               name="employees[${index}][surname]"
                               class="employee-surname"
                               value="${escapeHtml(data.surname)}">

                        <input type="hidden"
                               name="employees[${index}][name]"
                               class="employee-name"
                               value="${escapeHtml(data.name)}">

                        <input type="hidden"
                               name="employees[${index}][patronym]"
                               class="employee-patronym"
                               value="${escapeHtml(data.patronym || '')}">

                        <input type="hidden"
                               name="employees[${index}][position]"
                               class="employee-position"
                               value="${escapeHtml(data.position)}">

                        ${specialityInputs}

                        <input type="hidden"
                               name="employees[${index}][employment_status]"
                               class="employee-status-value"
                               value="${escapeHtml(data.employment_status)}">

                        <input type="hidden"
                               name="employees[${index}][dismissed_at]"
                               class="employee-dismissed-at"
                               value="${escapeHtml(data.dismissed_at || '')}">

                        <input type="hidden"
                               name="employees[${index}][phone]"
                               class="employee-phone"
                               value="${escapeHtml(data.phone || '')}">

                        <input type="hidden"
                               name="employees[${index}][email]"
                               class="employee-email"
                               value="${escapeHtml(data.email || '')}">

                        <div class="clinic-staff-avatar">${escapeHtml(initials)}</div>

                        <div>
                            <div class="clinic-staff-name">${escapeHtml(fullName)}</div>
                            <div class="clinic-staff-speciality">
                                ${escapeHtml(data.position || 'Врач')}
                            </div>
                        </div>

                        <div class="clinic-staff-card__position">
                            ${specialityBadgesHtml}
                        </div>

                        <div class="clinic-staff-card__contact">
                            <div class="clinic-staff-status ${employeeStatusClass(data.employment_status)}">
                                ${data.employment_status === 'dismissed' && data.dismissed_at
                    ? `Уволен с ${escapeHtml(data.dismissed_at.split('-').reverse().join('.'))}`
                    : escapeHtml(employeeStatusText(data.employment_status))}
                            </div>
                            <div class="clinic-staff-contact">${escapeHtml(data.phone || '')}</div>
                            <div class="clinic-staff-contact">${escapeHtml(data.email || '')}</div>
                        </div>

                        <div class="clinic-staff-actions">
                            <button type="button"
                                    class="clinic-icon-button clinic-edit-employee"
                                    title="Редактировать сотрудника">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </article>
                `;
            }

            function bindEmployeeEditButtons() {
                document.querySelectorAll('.clinic-edit-employee').forEach(button => {
                    button.onclick = () => {
                        const card = button.closest('.clinic-staff-card');

                        if (!card) {
                            return;
                        }

                        fillEmployeeModal(card);
                        employeeModal?.show();
                    };
                });
            }

            document.getElementById('addClinicEmployeeButton')?.addEventListener('click', () => {
                clearSelectedClinicDoctor();

                if (clinicDoctorSearch) {
                    clinicDoctorSearch.value = '';
                }

                filterClinicDoctors();
                addDoctorModal?.show();

                window.setTimeout(() => {
                    clinicDoctorSearch?.focus();
                }, 200);
            });

            addSelectedDoctorButton?.addEventListener('click', () => {
                if (!selectedClinicDoctor || !staffList) {
                    return;
                }

                const selectedPosition =
                    clinicNewEmployeePosition?.value
                    || selectedClinicDoctor.position
                    || 'Врач';

                const data = {
                    id: selectedClinicDoctor.id,
                    surname: selectedClinicDoctor.surname,
                    name: selectedClinicDoctor.name,
                    patronym: selectedClinicDoctor.patronym,
                    position: selectedPosition,
                    specialties:
                        selectedPosition
                            === 'Медицинский регистратор'
                            ? []
                            : (
                                selectedClinicDoctor.speciality
                                    ? [selectedClinicDoctor.speciality]
                                    : []
                            ),
                    employment_status: 'active',
                    dismissed_at: '',
                    phone: selectedClinicDoctor.phone,
                    email: selectedClinicDoctor.email,
                };

                const newIndex = staffList
                    .querySelectorAll('.clinic-staff-card')
                    .length;

                staffList.insertAdjacentHTML(
                    'beforeend',
                    employeeCardTemplate(data, newIndex)
                );

                reindexEmployees();
                bindEmployeeEditButtons();

                staffCurrentPage = Math.max(
                    1,
                    Math.ceil(
                        staffCards().length
                        / STAFF_PAGE_SIZE
                    )
                );

                renderStaffPagination();

                addDoctorModal?.hide();
                clearSelectedClinicDoctor();
                filterClinicDoctors();
            });

            employeeState?.addEventListener('change', () => {
                updateDismissalVisibility();
                updateHeadmasterTransferVisibility();
            });

            employeePosition?.addEventListener('change', () => {
                updateEmployeeSpecialityVisibility();
                updateHeadmasterTransferVisibility();
            });

            employeeModalElement?.addEventListener('hidden.bs.modal', () => {
                clearEmployeeModal();
                employeeForm?.reset();
            });

            dismissEmployeeButton?.addEventListener('click', () => {
                if (!employeeForm || !employeeDoctorId?.value) {
                    return;
                }

                const employeeName =
                    employeeProfileName?.textContent?.trim()
                    || 'этого сотрудника';

                const confirmed = window.confirm(
                    `Уволить ${employeeName} из клиники?`
                );

                if (!confirmed) {
                    return;
                }

                employeeState.value = 'dismissed';

                if (
                    employeeDismissedAt
                    && !employeeDismissedAt.value
                ) {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(
                        now.getMonth() + 1
                    ).padStart(2, '0');
                    const day = String(
                        now.getDate()
                    ).padStart(2, '0');

                    employeeDismissedAt.value =
                        `${year}-${month}-${day}`;
                }

                updateDismissalVisibility();
                updateHeadmasterTransferVisibility();

                /*
                 * Главного врача нельзя уволить без передачи должности.
                 * В этом случае сначала показываем обязательный выбор
                 * нового главного врача и не отправляем форму молча.
                 */
                if (
                    editingHeadmaster
                    && newHeadmasterId
                    && !newHeadmasterId.value
                ) {
                    headmasterTransferSection?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });

                    newHeadmasterId.focus();

                    return;
                }

                /*
                 * Раньше кнопка только переключала select на «Уволен»,
                 * но запрос на сервер вообще не отправлялся.
                 */
                employeeForm.requestSubmit();
            });

            reindexEmployees();
            bindEmployeeEditButtons();

            document.getElementById('clinicSettingsForm')?.addEventListener('submit', event => {
                reindexPhones();
                reindexEmployees();
            });

            @if($errors->employee->any())
                employeeDoctorId.value = @json((string) old('doctor_id', ''));
            employeePosition.value = @json(old('position', 'Врач'));
            updateEmployeeSpecialityVisibility();
            employeeState.value = @json(old('employment_status', 'active'));
            employeePhone.value = @json(old('phone', ''));
            employeeEmail.value = @json(old('email', ''));
            employeeDismissedAt.value = @json(old('dismissed_at', ''));

            const oldEmployeeSpecialities = employeeSpecialitiesForForm(
                @json(old('specialties', []))
            );

            const oldEmployeeSpecialitySet = new Set(
                oldEmployeeSpecialities.map(
                    normalizeEmployeeSpeciality
                )
            );

            document.querySelectorAll('.employee-speciality-option')
                .forEach(input => {
                    input.checked = oldEmployeeSpecialitySet.has(
                        normalizeEmployeeSpeciality(input.value)
                    );
                });

            const errorEmployeeCard = Array.from(
                staffList?.querySelectorAll('.clinic-staff-card') || []
            ).find(card => {
                return String(card.dataset.doctorId || '')
                    === String(employeeDoctorId.value || '');
            });

            editingHeadmaster =
                errorEmployeeCard?.dataset?.isHeadmaster === '1';

            if (newHeadmasterId) {
                newHeadmasterId.value =
                @json((string) old('new_headmaster_id', ''));

                Array.from(newHeadmasterId.options).forEach(option => {
                    option.disabled =
                        option.value === employeeDoctorId.value;
                });
            }

            updateDismissalVisibility();
            updateHeadmasterTransferVisibility();
            employeeModal?.show();
            @endif

            renderStaffPagination();

        })();
    </script>
@endpush
