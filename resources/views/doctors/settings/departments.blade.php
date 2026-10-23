@extends('doctors.settings')

@section('settings-title', 'Отделения стационара')

@section('assets')
    @parent

    <style>
        .hospital-departments-page {
            --department-accent: #078c9b;
            --department-accent-soft: #e9f8fa;
            --department-danger: #c43d4b;
        }

        .department-page-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .department-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .department-stat {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
            min-height: 82px;
            padding: 15px 17px;
            border: 1px solid var(--settings-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .045);
        }

        .department-stat__icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: var(--department-accent-soft);
            color: var(--department-accent);
            font-size: 18px;
        }

        .department-stat__value {
            color: #172238;
            font-size: 22px;
            font-weight: 850;
            line-height: 1;
        }

        .department-stat__label {
            margin-top: 5px;
            color: #738093;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35;
        }

        .department-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .department-search {
            position: relative;
            width: min(440px, 100%);
        }

        .department-search i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #8b99aa;
        }

        .department-search .settings-control {
            padding-left: 40px;
        }

        .department-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .department-card {
            overflow: hidden;
            border: 1px solid #dfe7ed;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .045);
        }

        .department-card.is-disabled {
            opacity: .68;
        }

        .department-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 18px 15px;
            border-bottom: 1px solid #edf2f5;
        }

        .department-card__identity {
            display: flex;
            min-width: 0;
            align-items: flex-start;
            gap: 12px;
        }

        .department-card__icon {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            place-items: center;
            border-radius: 14px;
            background: var(--department-accent-soft);
            color: var(--department-accent);
            font-size: 20px;
        }

        .department-card__title {
            margin: 0;
            color: #1b2638;
            font-size: 17px;
            font-weight: 850;
            line-height: 1.25;
        }

        .department-card__number {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            color: #7c899b;
            font-size: 11px;
            font-weight: 700;
        }

        .department-card__address {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-weight: 600;
        }

        .department-card__address::before {
            content: '•';
            color: #b1bdc9;
        }

        .department-card__actions {
            display: flex;
            gap: 6px;
        }

        .department-card__action {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 1px solid #dce5eb;
            border-radius: 10px;
            background: #fff;
            color: #607184;
            transition: .16s ease;
        }

        .department-card__action:hover {
            border-color: #b9dfe4;
            background: #effafb;
            color: var(--department-accent);
        }

        .department-card__action.is-danger:hover {
            border-color: #f0c8cc;
            background: #fff2f3;
            color: var(--department-danger);
        }

        .department-card__body {
            padding: 16px 18px 18px;
        }

        .department-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 14px;
        }

        .department-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            background: #edf8fa;
            color: #067583;
            font-size: 10px;
            font-weight: 800;
        }

        .department-badge.is-neutral {
            background: #f2f4f7;
            color: #667085;
        }

        .department-badge.is-off {
            background: #fff1f2;
            color: #b42335;
        }

        .department-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .department-info {
            min-width: 0;
            padding: 11px 12px;
            border: 1px solid #edf1f4;
            border-radius: 13px;
            background: #fbfcfd;
        }

        .department-info__label {
            color: #8a96a6;
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .department-info__value {
            margin-top: 5px;
            overflow: hidden;
            color: #273548;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.4;
            text-overflow: ellipsis;
        }

        .department-visiting {
            margin-top: 11px;
            padding: 12px;
            border: 1px solid #dcecef;
            border-radius: 14px;
            background: #f5fbfc;
        }

        .department-visiting__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #315a61;
            font-size: 10px;
            font-weight: 850;
        }

        .department-visiting__text {
            margin-top: 6px;
            color: #567078;
            font-size: 11px;
            line-height: 1.5;
        }

        .department-description {
            margin-top: 11px;
            color: #788494;
            font-size: 11px;
            line-height: 1.55;
        }

        .department-empty {
            padding: 54px 24px;
            border: 1px dashed #cbd8df;
            border-radius: 20px;
            background: #fff;
            color: #788494;
            text-align: center;
        }

        .department-empty i {
            display: block;
            margin-bottom: 12px;
            color: #8dcfd7;
            font-size: 38px;
        }

        .department-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 26px 80px rgba(15, 23, 42, .22);
        }

        .department-modal .modal-header,
        .department-modal .modal-footer {
            border-color: #e8eef2;
        }

        .department-modal .modal-body {
            padding: 20px;
            background: #f7f9fb;
        }

        .department-form-section {
            padding: 17px;
            border: 1px solid #e2e9ee;
            border-radius: 17px;
            background: #fff;
        }

        .department-form-section + .department-form-section {
            margin-top: 14px;
        }

        .department-form-section__title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            color: #253448;
            font-size: 13px;
            font-weight: 850;
        }

        .department-person-state {
            display: block;
            margin-top: 4px;
            font-size: 9px;
            font-weight: 750;
            line-height: 1.35;
        }

        .department-person-state.is-away {
            color: #b45309;
        }

        .department-person-state.is-acting {
            color: #087f8c;
        }

        .department-staff-picker {
            position: relative;
        }

        .department-staff-picker__input {
            position: relative;
        }

        .department-staff-picker__input > i {
            position: absolute;
            top: 50%;
            left: 13px;
            z-index: 2;
            transform: translateY(-50%);
            color: #8b99aa;
            pointer-events: none;
        }

        .department-staff-picker__input .settings-control {
            padding-right: 42px;
            padding-left: 38px;
        }

        .department-staff-picker__clear {
            position: absolute;
            top: 50%;
            right: 9px;
            display: none;
            width: 28px;
            height: 28px;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #8b99aa;
        }

        .department-staff-picker.has-value
        .department-staff-picker__clear,
        .department-staff-picker.has-query
        .department-staff-picker__clear {
            display: grid;
        }

        .department-staff-picker__clear:hover {
            background: #eef6f8;
            color: #087f8c;
        }

        .department-staff-picker__dropdown {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            z-index: 1085;
            display: none;
            max-height: 285px;
            overflow-y: auto;
            padding: 7px;
            border: 1px solid #dce5eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 46px rgba(15, 23, 42, .16);
        }

        .department-staff-picker.is-open
        .department-staff-picker__dropdown {
            display: block;
        }

        .department-staff-picker__option {
            display: flex;
            width: 100%;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 11px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #263746;
            text-align: left;
            transition: .14s ease;
        }

        .department-staff-picker__option + 
        .department-staff-picker__option {
            margin-top: 3px;
        }

        .department-staff-picker__option:hover {
            background: #eefafb;
        }

        .department-staff-picker__option.is-selected {
            background: #e7f8fa;
            color: #075f6b;
        }

        .department-staff-picker__option.is-unavailable {
            cursor: not-allowed;
            opacity: .48;
        }

        .department-staff-picker__option.is-unavailable:hover {
            background: transparent;
        }

        .department-staff-picker__person {
            min-width: 0;
        }

        .department-staff-picker__person strong,
        .department-staff-picker__person small {
            display: block;
        }

        .department-staff-picker__person strong {
            overflow: hidden;
            font-size: 11px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .department-staff-picker__person small {
            margin-top: 2px;
            color: #7d8998;
            font-size: 9px;
            line-height: 1.35;
        }

        .department-staff-picker__badges {
            display: flex;
            flex: 0 0 auto;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .department-staff-picker__badge {
            max-width: 190px;
            overflow: hidden;
            padding: 3px 6px;
            border-radius: 999px;
            background: #eef2f6;
            color: #667085;
            font-size: 8px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .department-staff-picker__badge.is-away {
            background: #fff7ed;
            color: #b45309;
        }

        .department-staff-picker__badge.is-assigned {
            background: #fef2f2;
            color: #b42335;
        }

        .department-staff-picker__vacation-note {
            margin-top: 5px;
            color: #8a6d3b;
            font-size: 8px;
            line-height: 1.35;
        }

        .department-staff-picker__selected-meta {
            display: none;
            margin-top: 6px;
            padding: 7px 9px;
            border: 1px solid #f2dfc2;
            border-radius: 10px;
            background: #fffaf2;
            color: #8a5a13;
            font-size: 9px;
            line-height: 1.4;
        }

        .department-staff-picker__selected-meta.is-visible {
            display: block;
        }

        .department-staff-picker__empty {
            display: none;
            padding: 16px 10px;
            color: #8793a2;
            font-size: 10px;
            text-align: center;
        }

        .department-staff-picker__empty.is-visible {
            display: block;
        }

        .department-doctors-picker__selected {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 9px;
        }

        .department-doctors-picker__selected:empty {
            display: none;
        }

        .department-doctor-chip {
            display: inline-flex;
            max-width: 100%;
            align-items: center;
            gap: 8px;
            padding: 7px 9px;
            border: 1px solid #d9e8ec;
            border-radius: 11px;
            background: #f7fbfc;
            color: #263746;
        }

        .department-doctor-chip__copy {
            min-width: 0;
        }

        .department-doctor-chip__copy strong,
        .department-doctor-chip__copy small {
            display: block;
        }

        .department-doctor-chip__copy strong {
            overflow: hidden;
            font-size: 10px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .department-doctor-chip__copy small {
            margin-top: 2px;
            color: #7b8794;
            font-size: 8px;
        }

        .department-doctor-chip__remove {
            display: grid;
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #8895a5;
        }

        .department-doctor-chip__remove:hover {
            background: #fee2e2;
            color: #b42335;
        }

        .department-doctors-summary {
            margin-top: 14px;
            padding-top: 13px;
            border-top: 1px solid #edf1f5;
        }

        .department-doctors-summary__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            color: #344054;
            font-size: 10px;
            font-weight: 850;
        }

        .department-doctors-summary__count {
            padding: 3px 7px;
            border-radius: 999px;
            background: #e9f7f9;
            color: #087f8c;
            font-size: 8px;
        }

        .department-doctors-summary__list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        .department-doctors-summary__item {
            padding: 8px 9px;
            border: 1px solid #e5ebf0;
            border-radius: 10px;
            background: #fbfcfd;
        }

        .department-doctors-summary__item strong,
        .department-doctors-summary__item small {
            display: block;
        }

        .department-doctors-summary__item strong {
            font-size: 10px;
            font-weight: 800;
        }

        .department-doctors-summary__item small {
            margin-top: 2px;
            color: #7b8794;
            font-size: 8px;
        }

        .department-doctors-summary__empty {
            color: #8b95a1;
            font-size: 9px;
        }

        @media (max-width: 720px) {
            .department-doctors-summary__list {
                grid-template-columns: 1fr;
            }
        }

        .department-mode-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .department-mode-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .department-mode-card {
            display: flex;
            min-height: 78px;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid #dce5eb;
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition: .16s ease;
        }

        .department-mode-option input:checked + .department-mode-card {
            border-color: #0b98a7;
            background: #f0fbfc;
            box-shadow: 0 0 0 2px rgba(11, 152, 167, .08);
        }

        .department-mode-card__icon {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 11px;
            background: #eef7f9;
            color: #087e8d;
        }

        .department-mode-card strong,
        .department-mode-card small {
            display: block;
        }

        .department-mode-card strong {
            color: #223147;
            font-size: 12px;
        }

        .department-mode-card small {
            margin-top: 2px;
            color: #8792a1;
            font-size: 9px;
            line-height: 1.35;
        }

        .department-visiting-choice {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .department-visiting-choice label {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 12px;
            border: 1px solid #dfe7ed;
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
        }

        .department-visiting-choice strong,
        .department-visiting-choice small {
            display: block;
        }

        .department-visiting-choice strong {
            color: #253448;
            font-size: 12px;
        }

        .department-visiting-choice small {
            margin-top: 3px;
            color: #8692a1;
            font-size: 10px;
            line-height: 1.4;
        }

        .department-clinic-visiting {
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid #dcecef;
            border-radius: 12px;
            background: #f3fafb;
            color: #4f6a71;
            font-size: 10px;
            line-height: 1.5;
        }

        .department-visiting-editor {
            margin-top: 12px;
        }

        .department-visiting-editor.d-none {
            display: none !important;
        }

        .department-visiting-row {
            display: grid;
            grid-template-columns: 38px 34px minmax(110px, 1fr) 16px minmax(110px, 1fr);
            gap: 8px;
            align-items: center;
            padding: 8px;
            border: 1px solid #edf1f4;
            border-radius: 12px;
            background: #fbfcfd;
        }

        .department-visiting-row + .department-visiting-row {
            margin-top: 7px;
        }

        .department-visiting-row__day {
            color: #526274;
            font-size: 10px;
            font-weight: 850;
        }

        .department-visiting-row__separator {
            color: #9aa5b1;
            text-align: center;
        }

        .department-visiting-row .settings-control {
            min-height: 38px;
            padding: 7px 9px;
            font-size: 12px;
        }

        .department-visiting-row.is-closed {
            background: #f8fafb;
        }

        .department-visiting-row.is-closed .settings-control {
            color: #7d8998;
            background: #fbfcfd;
        }

        .department-copy-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 9px;
            padding: 7px 10px;
            border: 1px dashed #a9d8de;
            border-radius: 10px;
            background: transparent;
            color: #087b89;
            font-size: 10px;
            font-weight: 800;
        }

        .department-form-switches {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 150px;
            gap: 12px;
            align-items: end;
        }

        .department-enabled-box {
            display: flex;
            min-height: 44px;
            align-items: center;
            gap: 9px;
            padding: 9px 12px;
            border: 1px solid #dce5eb;
            border-radius: 12px;
            background: #fff;
            color: #334155;
            font-size: 11px;
            font-weight: 750;
        }

        @media (max-width: 1000px) {
            .department-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .department-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .department-page-actions,
            .department-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .department-search {
                width: 100%;
            }

            .department-stats,
            .department-info-grid,
            .department-mode-grid,
            .department-visiting-choice,
            .department-form-switches {
                grid-template-columns: 1fr;
            }

            .department-visiting-row {
                grid-template-columns: 36px 30px 1fr 12px 1fr;
                gap: 5px;
            }
        }

        .hospital-building-list {
            display: grid;
            gap: 10px;
            max-height: 440px;
            overflow: auto;
            padding-right: 4px;
        }

        .hospital-building-item {
            width: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 15px;
            border: 1px solid #dce7eb;
            border-radius: 14px;
            background: #fff;
            color: #243746;
            text-align: left;
            transition: .16s ease;
        }

        .hospital-building-item:hover,
        .hospital-building-item.is-selected {
            border-color: var(--settings-primary);
            background: #f1fbfc;
            box-shadow: 0 0 0 3px rgba(4, 134, 150, .08);
        }

        .hospital-building-item__title {
            display: block;
            font-size: 13px;
            font-weight: 850;
        }

        .hospital-building-item__meta {
            display: block;
            margin-top: 4px;
            color: #718096;
            font-size: 11px;
            line-height: 1.45;
        }

        .hospital-building-item__count {
            flex: 0 0 auto;
            padding: 4px 8px;
            border-radius: 999px;
            background: #e8f8fa;
            color: #075f6b;
            font-size: 10px;
            font-weight: 800;
        }

        .hospital-building-empty {
            padding: 26px 18px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            color: #748091;
            text-align: center;
            font-size: 12px;
        }

        .hospital-building-form {
            padding: 16px;
            border: 1px solid #e1e8ee;
            border-radius: 16px;
            background: #fbfdfe;
        }

        .hospital-building-form__title {
            margin-bottom: 14px;
            font-size: 14px;
            font-weight: 850;
            color: #243746;
        }

        .hospital-building-form__grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 14px;
        }

        .hospital-building-form__full {
            grid-column: 1 / -1;
        }

        .hospital-building-address-picker {
            position: relative;
        }

        .hospital-building-address-suggestions {
            position: absolute;
            z-index: 1085;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            display: none;
            max-height: 260px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #d8e3e9;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
        }

        .hospital-building-address-suggestions.is-open {
            display: block;
        }

        .hospital-building-address-suggestion {
            display: flex;
            width: 100%;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 11px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #263746;
            text-align: left;
            cursor: pointer;
        }

        .hospital-building-address-suggestion:hover,
        .hospital-building-address-suggestion:focus {
            outline: 0;
            background: #eefafb;
        }

        .hospital-building-address-suggestion__icon {
            display: grid;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            place-items: center;
            border-radius: 9px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
        }

        .hospital-building-address-suggestion__copy {
            min-width: 0;
        }

        .hospital-building-address-suggestion__copy strong,
        .hospital-building-address-suggestion__copy span {
            display: block;
        }

        .hospital-building-address-suggestion__copy strong {
            color: #233445;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.35;
        }

        .hospital-building-address-suggestion__copy span {
            margin-top: 3px;
            color: #84909d;
            font-size: 10px;
            line-height: 1.35;
        }

        .hospital-building-address-suggestion--state {
            cursor: default;
        }

        .hospital-building-address-suggestion--state:hover {
            background: transparent;
        }

        .hospital-building-form__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
        }

        @media (max-width: 767px) {
            .hospital-building-form__grid {
                grid-template-columns: 1fr;
            }

            .hospital-building-form__full {
                grid-column: auto;
            }

            .hospital-building-form__footer {
                align-items: stretch;
                flex-direction: column;
            }
        }


        /* Быстрое создание геронтологического и психосоматического отделения. */
        .department-profile-presets { display:flex; flex-wrap:wrap; gap:7px; margin-top:9px; }
        .department-profile-preset { display:inline-flex; align-items:center; gap:6px; min-height:34px; padding:7px 10px; border:1px solid #c8e3e8; border-radius:10px; background:#f3fbfc; color:#087a8a; font-size:11px; font-weight:800; transition:background .15s,border-color .15s; }
        .department-profile-preset:hover, .department-profile-preset.is-selected { background:#e0f6f8; border-color:#71c6d1; }
        .department-profile-preset:focus-visible { outline:3px solid #6bcbd4; outline-offset:2px; }
    </style>
@endsection

@section('sub-main')
    <main class="settings-page hospital-departments-page">
        <div class="settings-container">
            <div class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">Отделения стационара</h1>
                    <p class="settings-page-description">
                        Структура стационара: профиль отделения, корпус, заведующий, старшая медсестра,
                        режим работы и правила посещения родственниками.
                    </p>
                </div>

                <div class="department-page-actions">
                    <button type="button"
                            class="settings-button settings-button--secondary"
                            id="manageHospitalBuildingsButton"
                            data-bs-toggle="modal"
                            data-bs-target="#hospitalBuildingModal">
                        <i class="bi bi-buildings"></i>
                        Корпуса стационара
                    </button>

                    <button type="button"
                            class="settings-button settings-button--primary"
                            id="addDepartmentButton"
                            data-bs-toggle="modal"
                            data-bs-target="#departmentModal">
                        <i class="bi bi-plus-lg"></i>
                        Добавить отделение
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-3">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-3">
                    <div class="fw-bold mb-1">Не удалось сохранить отделение:</div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="department-stats">
                <article class="department-stat">
                    <div class="department-stat__icon"><i class="bi bi-building"></i></div>
                    <div>
                        <div class="department-stat__value">{{ $stats['total'] }}</div>
                        <div class="department-stat__label">всего отделений</div>
                    </div>
                </article>

                <article class="department-stat">
                    <div class="department-stat__icon"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="department-stat__value">{{ $stats['active'] }}</div>
                        <div class="department-stat__label">активных отделений</div>
                    </div>
                </article>

                <article class="department-stat">
                    <div class="department-stat__icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="department-stat__value">{{ $stats['round_the_clock'] }}</div>
                        <div class="department-stat__label">круглосуточных</div>
                    </div>
                </article>

                <article class="department-stat">
                    <div class="department-stat__icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="department-stat__value">{{ $stats['custom_visiting'] }}</div>
                        <div class="department-stat__label">со своим графиком посещений</div>
                    </div>
                </article>
            </div>

            <div class="department-toolbar">
                <div class="department-search">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           class="settings-control"
                           id="departmentSearch"
                           placeholder="Поиск по отделению, корпусу или сотруднику...">
                </div>
            </div>

            @if(empty($departmentCards))
                <div class="department-empty">
                    <i class="bi bi-building-add"></i>
                    <strong>Отделения пока не созданы</strong>
                    <div class="mt-2">Добавьте первое отделение стационара.</div>
                </div>
            @else
                <div class="department-grid" id="departmentGrid">
                    @foreach($departmentCards as $department)
                        <article class="department-card {{ $department['enabled'] ? '' : 'is-disabled' }}"
                                 data-department-card
                                 data-search="{{ $department['search_text'] }}">
                            <div class="department-card__head">
                                <div class="department-card__identity">
                                    <div class="department-card__icon">
                                        <i class="bi bi-building"></i>
                                    </div>

                                    <div>
                                        <h2 class="department-card__title">
                                            {{ $department['name'] }}
                                        </h2>
                                        <div class="department-card__number">
                                            <span>
                                                {{ $department['number'] !== '' ? 'Отделение №' . $department['number'] : 'Номер не указан' }}
                                            </span>

                                            @if($department['building_address'] !== '')
                                                <span class="department-card__address">
                                                    {{ $department['building_address'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="department-card__actions">
                                    <button type="button"
                                            class="department-card__action js-edit-department"
                                            data-department-id="{{ $department['id'] }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#departmentModal"
                                            title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <button type="button"
                                            class="department-card__action is-danger js-delete-department"
                                            data-department-id="{{ $department['id'] }}"
                                            data-department-name="{{ $department['name'] }}"
                                            title="Удалить">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="department-card__body">
                                <div class="department-badges">
                                    <span class="department-badge">
                                        <i class="bi bi-clock"></i>
                                        {{ $department['mode_label'] }}
                                    </span>

                                    @if($department['profile'] !== '')
                                        <span class="department-badge is-neutral">
                                            {{ $department['profile'] }}
                                        </span>
                                    @endif

                                    @if(!$department['enabled'])
                                        <span class="department-badge is-off">
                                            Неактивно
                                        </span>
                                    @endif
                                </div>

                                <div class="department-info-grid">
                                    <div class="department-info">
                                        <div class="department-info__label">Корпус</div>
                                        <div class="department-info__value">
                                            {{ $department['building_label'] }}
                                        </div>
                                    </div>

                                    <div class="department-info">
                                        <div class="department-info__label">Заведующий</div>
                                        <div class="department-info__value">
                                            {{ $department['head_doctor_name'] ?: 'Не назначен' }}

                                            @if($department['head_doctor_on_vacation'])
                                                <span class="department-person-state is-away">
                                                    {{ $department['head_doctor_vacation_label'] }}
                                                </span>

                                                @if($department['acting_head_doctor_name'] !== '')
                                                    <span class="department-person-state is-acting">
                                                        И.О.: {{ $department['acting_head_doctor_name'] }}
                                                    </span>
                                                @else
                                                    <span class="department-person-state is-away">
                                                        И.О. не назначен
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <div class="department-info">
                                        <div class="department-info__label">Старшая медсестра</div>
                                        <div class="department-info__value">
                                            {{ $department['head_nurse_name'] ?: 'Не назначена' }}

                                            @if($department['head_nurse_on_vacation'])
                                                <span class="department-person-state is-away">
                                                    {{ $department['head_nurse_vacation_label'] }}
                                                </span>

                                                @if($department['acting_head_nurse_name'] !== '')
                                                    <span class="department-person-state is-acting">
                                                        И.О.: {{ $department['acting_head_nurse_name'] }}
                                                    </span>
                                                @else
                                                    <span class="department-person-state is-away">
                                                        И.О. не назначена
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <div class="department-info">
                                        <div class="department-info__label">Посещения</div>
                                        <div class="department-info__value">
                                            {{ $department['visiting_source_label'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="department-doctors-summary">
                                    <div class="department-doctors-summary__head">
                                        <span>
                                            <i class="bi bi-person-badge me-1"></i>
                                            Врачи отделения
                                        </span>
                                        <span class="department-doctors-summary__count">
                                            {{ $department['doctors_count'] }}
                                        </span>
                                    </div>

                                    @if(empty($department['doctors']))
                                        <div class="department-doctors-summary__empty">
                                            Врачи отделения не назначены.
                                        </div>
                                    @else
                                        <div class="department-doctors-summary__list">
                                            @foreach($department['doctors'] as $departmentDoctor)
                                                <div class="department-doctors-summary__item">
                                                    <strong>{{ $departmentDoctor['name'] }}</strong>
                                                    <small>{{ $departmentDoctor['speciality'] }}</small>

                                                    @if($departmentDoctor['on_vacation'])
                                                        <span class="department-person-state is-away">
                                                            {{ $departmentDoctor['vacation_label'] }}
                                                        </span>

                                                        @if($departmentDoctor['replacement_name'] !== '')
                                                            <span class="department-person-state is-acting">
                                                                И.О.: {{ $departmentDoctor['replacement_name'] }}
                                                            </span>
                                                        @else
                                                            <span class="department-person-state is-away">
                                                                И.О. не назначен
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="department-visiting">
                                    <div class="department-visiting__head">
                                        <span><i class="bi bi-people me-1"></i> Посещение родственниками</span>
                                    </div>
                                    <div class="department-visiting__text">
                                        {{ $department['visiting_hours_label'] }}
                                    </div>
                                </div>

                                @if($department['description'] !== '')
                                    <div class="department-description">
                                        {{ $department['description'] }}
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </main>


    <div class="modal fade"
         id="hospitalBuildingModal"
         tabindex="-1"
         aria-labelledby="hospitalBuildingModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="settings-panel__subtitle mb-1">Стационар</div>
                        <h2 class="modal-title" id="hospitalBuildingModalLabel">
                            Корпуса стационара
                        </h2>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    @if($errors->hospitalBuilding->any())
                        <div class="alert alert-danger border-0 rounded-4 mb-3">
                            <div class="fw-bold mb-1">
                                Не удалось сохранить корпус:
                            </div>
                            <ul class="mb-0">
                                @foreach($errors->hospitalBuilding->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="settings-label mb-0">
                                        Корпуса этой клиники
                                    </div>
                                    <div class="settings-field-hint mt-1">
                                        Стационарные корпуса хранятся отдельно от амбулаторных.
                                    </div>
                                </div>

                                <button type="button"
                                        class="settings-button settings-button--secondary"
                                        id="newHospitalBuildingButton">
                                    <i class="bi bi-plus-lg"></i>
                                    Новый
                                </button>
                            </div>

                            @if(empty($buildingCards))
                                <div class="hospital-building-empty">
                                    Корпуса стационара пока не созданы.
                                </div>
                            @else
                                <div class="hospital-building-list">
                                    @foreach($buildingCards as $building)
                                        <button type="button"
                                                class="hospital-building-item js-hospital-building-edit"
                                                data-building-id="{{ $building['id'] }}">
                                            <span>
                                                <span class="hospital-building-item__title">
                                                    Корпус №{{ $building['number'] }}
                                                    — {{ $building['name'] }}
                                                </span>
                                                <span class="hospital-building-item__meta">
                                                    {{ $building['address'] !== ''
                                                        ? $building['address']
                                                        : 'Адрес не указан' }}
                                                    @if(!$building['enabled'])
                                                        · неактивен
                                                    @endif
                                                </span>
                                            </span>

                                            <span class="hospital-building-item__count">
                                                {{ $building['departments_count'] }}
                                                отд.
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-7">
                            <form method="POST"
                                  action="{{ $buildingStoreUrl }}"
                                  id="hospitalBuildingForm"
                                  class="hospital-building-form"
                                  data-store-url="{{ $buildingStoreUrl }}"
                                  data-update-url="{{ $buildingUpdateUrl }}"
                                  data-delete-url="{{ $buildingDeleteUrl }}"
                                  data-address-search-url="{{ $buildingAddressSearchUrl }}">
                                @csrf

                                <input type="hidden"
                                       name="building_mode"
                                       id="hospitalBuildingMode"
                                       value="{{ old('building_mode', 'create') }}">

                                <input type="hidden"
                                       name="building_id"
                                       id="hospitalBuildingId"
                                       value="{{ old('building_id') }}">

                                <div class="hospital-building-form__title"
                                     id="hospitalBuildingFormTitle">
                                    Новый корпус стационара
                                </div>

                                <div class="hospital-building-form__grid">
                                    <div>
                                        <label class="settings-label"
                                               for="hospitalBuildingNumber">
                                            Номер корпуса *
                                        </label>
                                        <input type="text"
                                               class="settings-control"
                                               id="hospitalBuildingNumber"
                                               name="building_number"
                                               maxlength="50"
                                               required
                                               placeholder="Например: 1"
                                               value="{{ old('building_number') }}">
                                    </div>

                                    <div>
                                        <label class="settings-label"
                                               for="hospitalBuildingName">
                                            Название *
                                        </label>
                                        <input type="text"
                                               class="settings-control"
                                               id="hospitalBuildingName"
                                               name="building_name"
                                               maxlength="255"
                                               required
                                               placeholder="Например: Основной корпус"
                                               value="{{ old('building_name') }}">
                                    </div>

                                    <div class="hospital-building-form__full">
                                        <label class="settings-label"
                                               for="hospitalBuildingAddress">
                                            Адрес корпуса
                                        </label>

                                        <div class="hospital-building-address-picker"
                                             id="hospitalBuildingAddressPicker">
                                            <input type="text"
                                                   class="settings-control"
                                                   id="hospitalBuildingAddress"
                                                   name="building_address"
                                                   maxlength="255"
                                                   autocomplete="off"
                                                   placeholder="Начните вводить адрес корпуса..."
                                                   value="{{ old('building_address') }}">

                                            <div class="hospital-building-address-suggestions"
                                                 id="hospitalBuildingAddressSuggestions"></div>
                                        </div>

                                        <div class="settings-field-hint">
                                            Начните вводить адрес и выберите вариант из подсказок DaData.
                                            Адрес относится к корпусу, а не к отдельному отделению.
                                        </div>
                                    </div>

                                    <div class="hospital-building-form__full d-none"
                                         id="hospitalBuildingEnabledField">
                                        <label class="d-flex align-items-center gap-2">
                                            <input type="hidden"
                                                   name="building_enabled"
                                                   value="0">
                                            <input type="checkbox"
                                                   class="form-check-input m-0"
                                                   name="building_enabled"
                                                   id="hospitalBuildingEnabled"
                                                   value="1"
                                                   @checked(old('building_enabled', '1') === '1')>
                                            <span class="settings-label mb-0">
                                                Корпус активен
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="hospital-building-form__footer">
                                    <button type="submit"
                                            class="settings-button btn btn-outline-danger d-none"
                                            id="deleteHospitalBuildingButton"
                                            formaction="{{ $buildingDeleteUrl }}"
                                            formmethod="POST"
                                            formnovalidate>
                                        <i class="bi bi-trash3"></i>
                                        Удалить
                                    </button>

                                    <div class="d-flex gap-2 ms-auto">
                                        <button type="button"
                                                class="settings-button settings-button--secondary"
                                                id="resetHospitalBuildingButton">
                                            Сбросить
                                        </button>

                                        <button type="submit"
                                                class="settings-button settings-button--primary"
                                                id="saveHospitalBuildingButton">
                                            <i class="bi bi-check2-circle"></i>
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade department-modal"
         id="departmentModal"
         tabindex="-1"
         aria-labelledby="departmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content"
                  method="POST"
                  action="{{ $departmentStoreUrl }}"
                  id="departmentForm"
                  data-store-url="{{ $departmentStoreUrl }}"
                  data-update-url-template="{{ $departmentUpdateUrlTemplate }}">
                @csrf

                <div class="modal-header">
                    <div>
                        <div class="settings-panel__subtitle mb-1">Стационар</div>
                        <h2 class="modal-title" id="departmentModalLabel">Новое отделение</h2>
                    </div>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="department-form-section">
                        <div class="department-form-section__title">
                            <i class="bi bi-building"></i>
                            Основные данные
                        </div>

                        <div class="settings-form-grid">
                            <div class="settings-col-4">
                                <label class="settings-label" for="departmentNumber">Номер отделения</label>
                                <input type="text"
                                       class="settings-control"
                                       name="number"
                                       id="departmentNumber"
                                       maxlength="50"
                                       placeholder="Например: 1">
                            </div>

                            <div class="settings-col-8">
                                <label class="settings-label" for="departmentName">Название *</label>
                                <input type="text"
                                       class="settings-control"
                                       name="name"
                                       id="departmentName"
                                       maxlength="255"
                                       required
                                       placeholder="Например: Психиатрическое отделение №1">
                            </div>

                            <div class="settings-col-6">
                                <label class="settings-label" for="departmentProfile">Профиль</label>
                                <input type="text"
                                       class="settings-control"
                                       name="profile"
                                       id="departmentProfile"
                                       maxlength="255"
                                       placeholder="Например: Геронтологический">
                                <div class="department-profile-presets" role="group" aria-label="Готовые профили отделений">
                                    <button type="button" class="department-profile-preset" data-department-profile-preset="gerontological" aria-pressed="false"><i class="bi bi-person-heart" aria-hidden="true"></i> Геронтологическое</button>
                                    <button type="button" class="department-profile-preset" data-department-profile-preset="psychosomatic" aria-pressed="false"><i class="bi bi-heart-pulse" aria-hidden="true"></i> Психосоматическое</button>
                                    <button type="button" class="department-profile-preset" data-department-profile-preset="narcological" aria-pressed="false"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i> Наркологическое</button>
                                    <button type="button" class="department-profile-preset" data-department-profile-preset="children" aria-pressed="false"><i class="bi bi-balloon" aria-hidden="true"></i> Детское · 0–7</button>
                                    <button type="button" class="department-profile-preset" data-department-profile-preset="adolescents" aria-pressed="false"><i class="bi bi-people" aria-hidden="true"></i> Подростковое · 8–15</button>
                                    <button type="button" class="department-profile-preset" data-department-profile-preset="adults" aria-pressed="false"><i class="bi bi-person" aria-hidden="true"></i> Взрослое · 16+</button>
                                </div>
                                <small class="settings-field-hint">Выбор заполняет название и профиль. Детское: 0–7 лет; подростковое: 8–15 лет; взрослое: 16+. Пол задаётся отдельно. Для наркологического, геронтологического и психосоматического отделений допуск по диагнозу здесь автоматически не определяется.</small>
                            </div>

                            <div class="settings-col-12">
                                <div class="settings-info-box" style="display:block">
                                    <strong>Возрастные ограничения при госпитализации</strong>
                                    <div class="settings-form-grid" style="margin-top:10px">
                                        <div class="settings-col-6"><label class="settings-label" for="departmentAgeMin">Минимальный возраст, лет</label><input class="settings-control" type="number" min="0" max="125" id="departmentAgeMin" name="age_min_years" placeholder="Не ограничен"></div>
                                        <div class="settings-col-6"><label class="settings-label" for="departmentAgeMax">Максимальный возраст, лет</label><input class="settings-control" type="number" min="0" max="125" id="departmentAgeMax" name="age_max_years" placeholder="Не ограничен"></div>
                                    </div>
                                    <small class="settings-field-hint">Оба значения включительно. Детское: 0–7, подростковое: 8–15, взрослое: от 16 лет. Для других профилей возраст указывается вручную. Для сохранения этих полей из полной формы обработчик HospitalDepartmentController должен быть дополнен по инструкции из архива.</small>
                                </div>
                            </div>
                            <div class="settings-col-6">
                                <label class="settings-label" for="departmentBuilding">Корпус</label>
                                <select class="settings-control"
                                        name="building_id"
                                        id="departmentBuilding">
                                    <option value="">Корпус не указан</option>
                                    @foreach($buildingOptions as $buildingOption)
                                        <option value="{{ $buildingOption['id'] }}">
                                            {{ $buildingOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="settings-col-6">
                                <label class="settings-label"
                                       for="departmentHeadDoctorSearch">
                                    Заведующий отделением
                                </label>

                                <div class="department-staff-picker"
                                     data-staff-picker="doctor">
                                    <input type="hidden"
                                           name="head_doctor_id"
                                           id="departmentHeadDoctor"
                                           data-staff-value>

                                    <div class="department-staff-picker__input">
                                        <i class="bi bi-search"></i>

                                        <input type="text"
                                               class="settings-control"
                                               id="departmentHeadDoctorSearch"
                                               data-staff-search
                                               autocomplete="off"
                                               placeholder="Найти врача по ФИО или специальности">

                                        <button type="button"
                                                class="department-staff-picker__clear"
                                                data-staff-clear
                                                title="Убрать заведующего">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <div class="department-staff-picker__dropdown"
                                         data-staff-dropdown>
                                        @foreach($doctorOptions as $doctorOption)
                                            <button type="button"
                                                    class="department-staff-picker__option"
                                                    data-staff-option
                                                    data-id="{{ $doctorOption['id'] }}"
                                                    data-label="{{ $doctorOption['label'] }}"
                                                    data-search="{{ $doctorOption['search'] }}"
                                                    data-assigned-department-id="{{ $doctorOption['assigned_department_id'] ?? '' }}"
                                                    data-assigned-department-name="{{ $doctorOption['assigned_department_name'] }}"
                                                    data-on-vacation="{{ $doctorOption['on_vacation'] ? '1' : '0' }}"
                                                    data-vacation-label="{{ $doctorOption['vacation_label'] }}"
                                                    data-replacement-name="{{ $doctorOption['replacement_name'] }}">
                                                <span class="department-staff-picker__person">
                                                    <strong>{{ $doctorOption['name'] }}</strong>
                                                    <small>{{ $doctorOption['subtitle'] }}</small>

                                                    @if($doctorOption['on_vacation'])
                                                        <span class="department-staff-picker__vacation-note">
                                                            {{ $doctorOption['vacation_label'] }}
                                                            @if($doctorOption['replacement_name'] !== '')
                                                                · И.О. {{ $doctorOption['replacement_name'] }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </span>

                                                <span class="department-staff-picker__badges">
                                                    @if($doctorOption['assigned_department_id'])
                                                        <span class="department-staff-picker__badge is-assigned">
                                                            Уже: {{ $doctorOption['assigned_department_name'] }}
                                                        </span>
                                                    @endif

                                                    @if($doctorOption['on_vacation'])
                                                        <span class="department-staff-picker__badge is-away">
                                                            В отпуске
                                                        </span>
                                                    @endif
                                                </span>
                                            </button>
                                        @endforeach

                                        <div class="department-staff-picker__empty"
                                             data-staff-empty>
                                            Подходящий врач не найден.
                                        </div>
                                    </div>

                                    <div class="department-staff-picker__selected-meta"
                                         data-staff-selected-meta></div>
                                </div>

                                <div class="settings-field-hint">
                                    Один врач может быть заведующим только одного отделения.
                                    Текущий отпуск и И.О. отображаются в списке.
                                </div>
                            </div>

                            <div class="settings-col-6">
                                <label class="settings-label"
                                       for="departmentHeadNurseSearch">
                                    Старшая медицинская сестра
                                </label>

                                <div class="department-staff-picker"
                                     data-staff-picker="nurse">
                                    <input type="hidden"
                                           name="head_nurse_id"
                                           id="departmentHeadNurse"
                                           data-staff-value>

                                    <div class="department-staff-picker__input">
                                        <i class="bi bi-search"></i>

                                        <input type="text"
                                               class="settings-control"
                                               id="departmentHeadNurseSearch"
                                               data-staff-search
                                               autocomplete="off"
                                               placeholder="Найти медсестру по ФИО">

                                        <button type="button"
                                                class="department-staff-picker__clear"
                                                data-staff-clear
                                                title="Убрать старшую медсестру">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <div class="department-staff-picker__dropdown"
                                         data-staff-dropdown>
                                        @foreach($nurseOptions as $nurseOption)
                                            <button type="button"
                                                    class="department-staff-picker__option"
                                                    data-staff-option
                                                    data-id="{{ $nurseOption['id'] }}"
                                                    data-label="{{ $nurseOption['label'] }}"
                                                    data-search="{{ $nurseOption['search'] }}"
                                                    data-assigned-department-id="{{ $nurseOption['assigned_department_id'] ?? '' }}"
                                                    data-assigned-department-name="{{ $nurseOption['assigned_department_name'] }}"
                                                    data-on-vacation="{{ $nurseOption['on_vacation'] ? '1' : '0' }}"
                                                    data-vacation-label="{{ $nurseOption['vacation_label'] }}"
                                                    data-replacement-name="{{ $nurseOption['replacement_name'] }}">
                                                <span class="department-staff-picker__person">
                                                    <strong>{{ $nurseOption['name'] }}</strong>
                                                    <small>{{ $nurseOption['subtitle'] }}</small>

                                                    @if($nurseOption['on_vacation'])
                                                        <span class="department-staff-picker__vacation-note">
                                                            {{ $nurseOption['vacation_label'] }}
                                                            @if($nurseOption['replacement_name'] !== '')
                                                                · И.О. {{ $nurseOption['replacement_name'] }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </span>

                                                <span class="department-staff-picker__badges">
                                                    @if($nurseOption['assigned_department_id'])
                                                        <span class="department-staff-picker__badge is-assigned">
                                                            Уже: {{ $nurseOption['assigned_department_name'] }}
                                                        </span>
                                                    @endif

                                                    @if($nurseOption['on_vacation'])
                                                        <span class="department-staff-picker__badge is-away">
                                                            В отпуске
                                                        </span>
                                                    @endif
                                                </span>
                                            </button>
                                        @endforeach

                                        <div class="department-staff-picker__empty"
                                             data-staff-empty>
                                            Подходящая медсестра не найдена.
                                        </div>
                                    </div>

                                    <div class="department-staff-picker__selected-meta"
                                         data-staff-selected-meta></div>
                                </div>

                                <div class="settings-field-hint">
                                    Одна медсестра может быть старшей только в одном отделении.
                                    Текущий отпуск и замещение учитываются.
                                </div>
                            </div>

                            <div class="settings-col-12">
                                <label class="settings-label"
                                       for="departmentDoctorsSearch">
                                    Врачи отделения
                                </label>

                                <div class="department-staff-picker"
                                     data-department-doctors-picker>
                                    <div class="department-staff-picker__input">
                                        <i class="bi bi-search"></i>

                                        <input type="text"
                                               class="settings-control"
                                               id="departmentDoctorsSearch"
                                               data-department-doctors-search
                                               autocomplete="off"
                                               placeholder="Найти врача и добавить в отделение">

                                        <button type="button"
                                                class="department-staff-picker__clear"
                                                data-department-doctors-clear
                                                title="Очистить поиск">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <div class="department-staff-picker__dropdown"
                                         data-department-doctors-dropdown>
                                        @foreach($doctorOptions as $doctorOption)
                                            <button type="button"
                                                    class="department-staff-picker__option"
                                                    data-department-doctor-option
                                                    data-id="{{ $doctorOption['id'] }}"
                                                    data-name="{{ $doctorOption['name'] }}"
                                                    data-subtitle="{{ $doctorOption['subtitle'] }}"
                                                    data-search="{{ $doctorOption['search'] }}"
                                                    data-assigned-department-id="{{ $doctorOption['assigned_department_id'] ?? '' }}"
                                                    data-assigned-department-name="{{ $doctorOption['assigned_department_name'] }}"
                                                    data-on-vacation="{{ $doctorOption['on_vacation'] ? '1' : '0' }}"
                                                    data-vacation-label="{{ $doctorOption['vacation_label'] }}"
                                                    data-replacement-name="{{ $doctorOption['replacement_name'] }}">
                                                <span class="department-staff-picker__person">
                                                    <strong>{{ $doctorOption['name'] }}</strong>
                                                    <small>{{ $doctorOption['subtitle'] }}</small>

                                                    @if($doctorOption['on_vacation'])
                                                        <span class="department-staff-picker__vacation-note">
                                                            {{ $doctorOption['vacation_label'] }}
                                                            @if($doctorOption['replacement_name'] !== '')
                                                                · И.О. {{ $doctorOption['replacement_name'] }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </span>

                                                <span class="department-staff-picker__badges">
                                                    @if($doctorOption['assigned_department_id'])
                                                        <span class="department-staff-picker__badge is-assigned">
                                                            Уже: {{ $doctorOption['assigned_department_name'] }}
                                                        </span>
                                                    @endif

                                                    @if($doctorOption['on_vacation'])
                                                        <span class="department-staff-picker__badge is-away">
                                                            В отпуске
                                                        </span>
                                                    @endif
                                                </span>
                                            </button>
                                        @endforeach

                                        <div class="department-staff-picker__empty"
                                             data-department-doctors-empty>
                                            Подходящий врач не найден.
                                        </div>
                                    </div>

                                    <div data-department-doctors-hidden></div>
                                    <div class="department-doctors-picker__selected"
                                         data-department-doctors-selected></div>
                                </div>

                                <div class="settings-field-hint">
                                    Врач может постоянно работать только в одном отделении.
                                    Врачи из других отделений видны в поиске, но недоступны для выбора.
                                    Отпуск не снимает постоянное закрепление: И.О. показывается отдельно.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="department-form-section">
                        <div class="department-form-section__title">
                            <i class="bi bi-clock-history"></i>
                            Режим отделения
                        </div>

                        <div class="department-mode-grid">
                            <label class="department-mode-option">
                                <input type="radio"
                                       name="inpatient_mode"
                                       value="day"
                                       id="departmentModeDay">
                                <span class="department-mode-card">
                                    <span class="department-mode-card__icon"><i class="bi bi-sun"></i></span>
                                    <span>
                                        <strong>Дневной</strong>
                                        <small>Пациенты находятся в отделении в дневные часы.</small>
                                    </span>
                                </span>
                            </label>

                            <label class="department-mode-option">
                                <input type="radio"
                                       name="inpatient_mode"
                                       value="night"
                                       id="departmentModeNight">
                                <span class="department-mode-card">
                                    <span class="department-mode-card__icon"><i class="bi bi-moon"></i></span>
                                    <span>
                                        <strong>Ночной</strong>
                                        <small>Ночной режим пребывания пациентов.</small>
                                    </span>
                                </span>
                            </label>

                            <label class="department-mode-option">
                                <input type="radio"
                                       name="inpatient_mode"
                                       value="round_the_clock"
                                       id="departmentModeRoundTheClock"
                                       checked>
                                <span class="department-mode-card">
                                    <span class="department-mode-card__icon"><i class="bi bi-clock-history"></i></span>
                                    <span>
                                        <strong>Круглосуточный</strong>
                                        <small>Отделение работает 24 часа в сутки.</small>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="department-form-section">
                        <div class="department-form-section__title">
                            <i class="bi bi-people"></i>
                            Посещение родственниками
                        </div>

                        <div class="department-visiting-choice">
                            <label>
                                <input type="radio"
                                       name="use_clinic_visiting_hours"
                                       value="1"
                                       id="departmentVisitingUseClinic"
                                       checked>
                                <span>
                                    <strong>Использовать график клиники</strong>
                                    <small>Отделение наследует общее время посещений стационара.</small>
                                </span>
                            </label>

                            <label>
                                <input type="radio"
                                       name="use_clinic_visiting_hours"
                                       value="0"
                                       id="departmentVisitingUseOwn">
                                <span>
                                    <strong>Собственный график</strong>
                                    <small>У отделения будет отдельное время посещений.</small>
                                </span>
                            </label>
                        </div>

                        <div class="department-clinic-visiting">
                            <strong>График клиники:</strong>
                            {{ $clinicVisitingHoursLabel }}
                        </div>

                        <div class="department-visiting-editor d-none"
                             id="departmentVisitingEditor">
                            @foreach($visitingDays as $dayCode => $dayLabel)
                                <div class="department-visiting-row is-closed"
                                     data-visiting-day="{{ $dayCode }}">
                                    <div class="department-visiting-row__day">{{ $dayLabel }}</div>

                                    <div>
                                        <input type="hidden"
                                               name="visiting_hours[{{ $dayCode }}][enabled]"
                                               value="0">
                                        <input type="checkbox"
                                               class="form-check-input js-visiting-enabled"
                                               name="visiting_hours[{{ $dayCode }}][enabled]"
                                               value="1">
                                    </div>

                                    <input type="time"
                                           class="settings-control js-visiting-from"
                                           name="visiting_hours[{{ $dayCode }}][from]">

                                    <div class="department-visiting-row__separator">—</div>

                                    <input type="time"
                                           class="settings-control js-visiting-to"
                                           name="visiting_hours[{{ $dayCode }}][to]">
                                </div>
                            @endforeach

                            <button type="button"
                                    class="department-copy-button"
                                    id="copyDepartmentMondayButton">
                                <i class="bi bi-copy"></i>
                                Скопировать понедельник на всю неделю
                            </button>
                        </div>
                    </div>

                    <div class="department-form-section">
                        <div class="department-form-section__title">
                            <i class="bi bi-card-text"></i>
                            Дополнительно
                        </div>

                        <div class="settings-form-grid">
                            <div class="settings-col-12">
                                <label class="settings-label" for="departmentDescription">Описание</label>
                                <textarea class="settings-control"
                                          name="description"
                                          id="departmentDescription"
                                          maxlength="2000"
                                          placeholder="Дополнительная информация об отделении"></textarea>
                            </div>

                            <div class="settings-col-12 department-form-switches">
                                <label class="department-enabled-box">
                                    <input type="hidden" name="enabled" value="0">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="enabled"
                                           id="departmentEnabled"
                                           value="1"
                                           checked>
                                    <span>Отделение активно</span>
                                </label>

                                <div>
                                    <label class="settings-label" for="departmentSortOrder">Порядок</label>
                                    <input type="number"
                                           class="settings-control"
                                           name="sort_order"
                                           id="departmentSortOrder"
                                           min="0"
                                           value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="settings-button settings-button--secondary"
                            data-bs-dismiss="modal">
                        Отмена
                    </button>
                    <button type="submit"
                            class="settings-button settings-button--primary">
                        <i class="bi bi-check2"></i>
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form method="POST"
          action="#"
          class="d-none"
          id="deleteDepartmentForm"
          data-delete-url-template="{{ $departmentDeleteUrlTemplate }}">
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const departmentForms = @json($departmentForms);
            const buildingForms = @json($buildingForms);
            const hasHospitalBuildingErrors =
                @json($errors->hospitalBuilding->any());
            const oldHospitalBuildingMode =
                @json(old('building_mode', 'create'));
            const oldHospitalBuildingId =
                @json(old('building_id'));
            const oldHospitalBuildingNumber =
                @json(old('building_number'));
            const oldHospitalBuildingName =
                @json(old('building_name'));
            const oldHospitalBuildingAddress =
                @json(old('building_address'));
            const oldHospitalBuildingEnabled =
                @json(old('building_enabled', '1'));

            const departmentForm = document.getElementById('departmentForm');
            // Шаблоны профиля не создают новые типы БД и не меняют маршрутизацию пациентов.
            const departmentProfileTemplates = {
                gerontological: { name:'Геронтологическое отделение', profile:'Геронтологический' },
                psychosomatic: { name:'Психосоматическое отделение', profile:'Психосоматический' },
                narcological: { name:'Наркологическое отделение', profile:'Наркологический' },
                children: { name:'Детское отделение', profile:'Детский' },
                adolescents: { name:'Подростковое отделение', profile:'Подростковый' },
                adults: { name:'Взрослое отделение', profile:'Взрослый' },
            };
            const departmentProfileButtons = Array.from(document.querySelectorAll('[data-department-profile-preset]'));
            const departmentNameInput = document.getElementById('departmentName');
            const departmentProfileInput = document.getElementById('departmentProfile');
            const syncDepartmentProfileButtons = () => {
                const profile = String(departmentProfileInput?.value || '').trim();
                departmentProfileButtons.forEach(button => {
                    const preset = departmentProfileTemplates[button.dataset.departmentProfilePreset];
                    const selected = Boolean(preset && profile === preset.profile);
                    button.classList.toggle('is-selected', selected);
                    button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
            };
            departmentProfileButtons.forEach(button => button.addEventListener('click', () => {
                const preset = departmentProfileTemplates[button.dataset.departmentProfilePreset];
                if (!preset || !departmentForm || !departmentNameInput || !departmentProfileInput) return;
                departmentNameInput.value = preset.name;
                departmentProfileInput.value = preset.profile;
                syncAgePreset();
                syncDepartmentProfileButtons();
                departmentNameInput.focus();
            }));
            const ageMinInput = document.getElementById('departmentAgeMin');
            const ageMaxInput = document.getElementById('departmentAgeMax');
            const departmentAgePresets = {'детский':[0,7],'подростковый':[8,15],'взрослый':[16,null],'детская психиатрия':[0,7],'подростковая психиатрия':[8,15],'взрослая психиатрия':[16,null]};
            const syncAgePreset = () => {
                const p = String(departmentProfileInput?.value || '').trim().toLocaleLowerCase('ru');
                const preset = departmentAgePresets[p];
                if (preset) {
                    ageMinInput.value = String(preset[0]);
                    ageMaxInput.value = preset[1] == null ? '' : String(preset[1]);
                }
                ageMinInput.readOnly = Boolean(preset);
                ageMaxInput.readOnly = Boolean(preset);
            };
            departmentProfileInput?.addEventListener('input', () => { syncDepartmentProfileButtons(); syncAgePreset(); });
            const modalTitle = document.getElementById('departmentModalLabel');
            const addButton = document.getElementById('addDepartmentButton');
            const deleteForm = document.getElementById('deleteDepartmentForm');
            const hospitalBuildingModal =
                document.getElementById('hospitalBuildingModal');
            const hospitalBuildingForm =
                document.getElementById('hospitalBuildingForm');
            const hospitalBuildingMode =
                document.getElementById('hospitalBuildingMode');
            const hospitalBuildingId =
                document.getElementById('hospitalBuildingId');
            const hospitalBuildingNumber =
                document.getElementById('hospitalBuildingNumber');
            const hospitalBuildingName =
                document.getElementById('hospitalBuildingName');
            const hospitalBuildingAddress =
                document.getElementById('hospitalBuildingAddress');
            const hospitalBuildingAddressPicker =
                document.getElementById('hospitalBuildingAddressPicker');
            const hospitalBuildingAddressSuggestions =
                document.getElementById(
                    'hospitalBuildingAddressSuggestions'
                );
            const hospitalBuildingAddressSearchUrl = String(
                hospitalBuildingForm?.dataset.addressSearchUrl || ''
            ).trim();
            const hospitalBuildingEnabled =
                document.getElementById('hospitalBuildingEnabled');
            const hospitalBuildingEnabledField =
                document.getElementById('hospitalBuildingEnabledField');
            const hospitalBuildingFormTitle =
                document.getElementById('hospitalBuildingFormTitle');
            const deleteHospitalBuildingButton =
                document.getElementById('deleteHospitalBuildingButton');

            const searchInput = document.getElementById('departmentSearch');
            const visitingEditor = document.getElementById('departmentVisitingEditor');
            const useClinicVisiting = document.getElementById('departmentVisitingUseClinic');
            const useOwnVisiting = document.getElementById('departmentVisitingUseOwn');
            const visitingRows = Array.from(
                document.querySelectorAll('[data-visiting-day]')
            );

            let currentDepartmentId = null;

            const staffPickers = {};
            let departmentDoctorsPicker = null;

            function createStaffPicker(root) {
                const type = String(root.dataset.staffPicker || '');
                const valueInput = root.querySelector('[data-staff-value]');
                const searchInput = root.querySelector('[data-staff-search]');
                const dropdown = root.querySelector('[data-staff-dropdown]');
                const clearButton = root.querySelector('[data-staff-clear]');
                const emptyState = root.querySelector('[data-staff-empty]');
                const selectedMeta = root.querySelector(
                    '[data-staff-selected-meta]'
                );
                const options = Array.from(
                    root.querySelectorAll('[data-staff-option]')
                );

                let selectedLabel = '';

                function optionUnavailable(option) {
                    const assignedDepartmentId = Number(
                        option.dataset.assignedDepartmentId || 0
                    );

                    if (!assignedDepartmentId) {
                        return false;
                    }

                    return assignedDepartmentId !== Number(
                        currentDepartmentId || 0
                    );
                }

                function refreshAvailability() {
                    options.forEach(option => {
                        const unavailable = optionUnavailable(option);

                        option.classList.toggle(
                            'is-unavailable',
                            unavailable
                        );
                        option.setAttribute(
                            'aria-disabled',
                            unavailable ? 'true' : 'false'
                        );
                    });
                }

                function updateEmptyState() {
                    const hasVisible = options.some(
                        option =>
                            !option.classList.contains('d-none')
                    );

                    emptyState?.classList.toggle(
                        'is-visible',
                        !hasVisible
                    );
                }

                function filterOptions() {
                    const rawQuery = String(
                        searchInput?.value || ''
                    ).trim();

                    const query = (
                        rawQuery === selectedLabel
                            ? ''
                            : rawQuery
                    ).toLowerCase();

                    options.forEach(option => {
                        const haystack = String(
                            option.dataset.search
                            || option.dataset.label
                            || ''
                        ).toLowerCase();

                        option.classList.toggle(
                            'd-none',
                            query !== ''
                            && !haystack.includes(query)
                        );
                    });

                    updateEmptyState();
                }

                function open() {
                    refreshAvailability();
                    filterOptions();
                    root.classList.add('is-open');
                }

                function close() {
                    root.classList.remove('is-open');
                }

                function clear(keepQuery = false) {
                    if (valueInput) {
                        valueInput.value = '';
                    }

                    if (searchInput && !keepQuery) {
                        searchInput.value = '';
                    }

                    searchInput?.setCustomValidity('');

                    options.forEach(option => {
                        option.classList.remove('is-selected');
                    });

                    selectedLabel = '';

                    if (selectedMeta) {
                        selectedMeta.textContent = '';
                        selectedMeta.classList.remove('is-visible');
                    }

                    root.classList.remove('has-value');

                    root.classList.toggle(
                        'has-query',
                        Boolean(
                            String(
                                searchInput?.value || ''
                            ).trim()
                        )
                    );

                    if (type === 'doctor') {
                        departmentDoctorsPicker?.refreshAvailability();
                    }
                }

                function select(id) {
                    const normalized = String(id || '');

                    if (normalized === '') {
                        clear();
                        return true;
                    }

                    refreshAvailability();

                    const option = options.find(
                        item =>
                            String(item.dataset.id || '')
                            === normalized
                    );

                    if (!option || optionUnavailable(option)) {
                        clear();
                        return false;
                    }

                    options.forEach(item => {
                        item.classList.remove('is-selected');
                    });

                    option.classList.add('is-selected');

                    selectedLabel = String(
                        option.dataset.label || ''
                    );

                    if (valueInput) {
                        valueInput.value = normalized;
                    }

                    if (searchInput) {
                        searchInput.value = selectedLabel;
                        searchInput.setCustomValidity('');
                    }

                    const onVacation =
                        option.dataset.onVacation === '1';
                    const vacationLabel = String(
                        option.dataset.vacationLabel || ''
                    );
                    const replacementName = String(
                        option.dataset.replacementName || ''
                    );

                    if (selectedMeta) {
                        if (onVacation) {
                            selectedMeta.textContent =
                                vacationLabel
                                + (
                                    replacementName
                                        ? ` · И.О.: ${replacementName}`
                                        : ' · И.О. не назначен'
                                );

                            selectedMeta.classList.add(
                                'is-visible'
                            );
                        } else {
                            selectedMeta.textContent = '';
                            selectedMeta.classList.remove(
                                'is-visible'
                            );
                        }
                    }

                    root.classList.add('has-value');
                    root.classList.add('has-query');

                    if (type === 'doctor') {
                        departmentDoctorsPicker?.remove(normalized);
                        departmentDoctorsPicker?.refreshAvailability();
                    }

                    close();

                    return true;
                }

                function validate() {
                    const query = String(
                        searchInput?.value || ''
                    ).trim();

                    const hasValue = Boolean(
                        String(
                            valueInput?.value || ''
                        ).trim()
                    );

                    if (query !== '' && !hasValue) {
                        searchInput?.setCustomValidity(
                            type === 'doctor'
                                ? 'Выберите врача из списка.'
                                : 'Выберите медсестру из списка.'
                        );

                        searchInput?.reportValidity();

                        return false;
                    }

                    searchInput?.setCustomValidity('');

                    return true;
                }

                searchInput?.addEventListener(
                    'focus',
                    function () {
                        if (
                            valueInput?.value
                            && searchInput.value === selectedLabel
                        ) {
                            searchInput.select();
                        }

                        open();
                    }
                );

                searchInput?.addEventListener(
                    'input',
                    function () {
                        const currentText = String(
                            searchInput.value || ''
                        );

                        if (
                            valueInput?.value
                            && currentText !== selectedLabel
                        ) {
                            clear(true);
                        }

                        searchInput.setCustomValidity('');

                        root.classList.toggle(
                            'has-query',
                            currentText.trim() !== ''
                        );

                        open();
                    }
                );

                options.forEach(option => {
                    option.addEventListener(
                        'click',
                        function () {
                            if (optionUnavailable(option)) {
                                return;
                            }

                            select(option.dataset.id);
                        }
                    );
                });

                clearButton?.addEventListener(
                    'click',
                    function () {
                        clear();
                        filterOptions();
                        searchInput?.focus();
                        open();
                    }
                );

                return {
                    type,
                    root,
                    select,
                    clear,
                    open,
                    close,
                    validate,
                    refreshAvailability,
                };
            }

            function createDepartmentDoctorsPicker(root) {
                if (!root) {
                    return null;
                }

                const searchInput = root.querySelector(
                    '[data-department-doctors-search]'
                );
                const dropdown = root.querySelector(
                    '[data-department-doctors-dropdown]'
                );
                const clearButton = root.querySelector(
                    '[data-department-doctors-clear]'
                );
                const emptyState = root.querySelector(
                    '[data-department-doctors-empty]'
                );
                const selectedContainer = root.querySelector(
                    '[data-department-doctors-selected]'
                );
                const hiddenContainer = root.querySelector(
                    '[data-department-doctors-hidden]'
                );
                const options = Array.from(
                    root.querySelectorAll(
                        '[data-department-doctor-option]'
                    )
                );

                const selectedIds = new Set();

                function headDoctorId() {
                    return String(
                        document.getElementById('departmentHeadDoctor')
                            ?.value || ''
                    );
                }

                function assignedElsewhere(option) {
                    const assignedDepartmentId = Number(
                        option.dataset.assignedDepartmentId || 0
                    );

                    if (!assignedDepartmentId) {
                        return false;
                    }

                    return assignedDepartmentId !== Number(
                        currentDepartmentId || 0
                    );
                }

                function optionUnavailable(option) {
                    if (assignedElsewhere(option)) {
                        return true;
                    }

                    return String(option.dataset.id || '')
                        === headDoctorId();
                }

                function refreshAvailability() {
                    const currentHeadDoctorId = headDoctorId();

                    if (
                        currentHeadDoctorId
                        && selectedIds.has(currentHeadDoctorId)
                    ) {
                        selectedIds.delete(currentHeadDoctorId);
                    }

                    options.forEach(option => {
                        const unavailable = optionUnavailable(option);

                        option.classList.toggle(
                            'is-unavailable',
                            unavailable
                        );
                        option.classList.toggle(
                            'is-selected',
                            selectedIds.has(
                                String(option.dataset.id || '')
                            )
                        );
                        option.setAttribute(
                            'aria-disabled',
                            unavailable ? 'true' : 'false'
                        );
                    });

                    renderSelected();
                    filterOptions();
                }

                function updateEmptyState() {
                    const visibleOptions = options.filter(
                        option =>
                            !option.classList.contains('d-none')
                            && !selectedIds.has(
                                String(option.dataset.id || '')
                            )
                    );

                    emptyState?.classList.toggle(
                        'is-visible',
                        visibleOptions.length === 0
                    );
                }

                function filterOptions() {
                    const query = String(
                        searchInput?.value || ''
                    )
                        .trim()
                        .toLowerCase();

                    options.forEach(option => {
                        const id = String(option.dataset.id || '');
                        const haystack = String(
                            option.dataset.search || ''
                        ).toLowerCase();

                        const visible =
                            !selectedIds.has(id)
                            && (
                                query === ''
                                || haystack.includes(query)
                            );

                        option.classList.toggle(
                            'd-none',
                            !visible
                        );
                    });

                    updateEmptyState();
                }

                function open() {
                    refreshAvailability();
                    root.classList.add('is-open');
                }

                function close() {
                    root.classList.remove('is-open');
                }

                function renderSelected() {
                    if (hiddenContainer) {
                        hiddenContainer.innerHTML = '';
                    }

                    if (selectedContainer) {
                        selectedContainer.innerHTML = '';
                    }

                    selectedIds.forEach(id => {
                        const option = options.find(
                            item =>
                                String(item.dataset.id || '') === id
                        );

                        if (!option) {
                            return;
                        }

                        if (hiddenContainer) {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'doctor_ids[]';
                            hidden.value = id;
                            hiddenContainer.appendChild(hidden);
                        }

                        if (!selectedContainer) {
                            return;
                        }

                        const chip = document.createElement('div');
                        chip.className = 'department-doctor-chip';

                        const onVacation =
                            option.dataset.onVacation === '1';
                        const vacationLabel = String(
                            option.dataset.vacationLabel || ''
                        );
                        const replacementName = String(
                            option.dataset.replacementName || ''
                        );

                        const meta = [
                            String(option.dataset.subtitle || ''),
                            onVacation
                                ? vacationLabel
                                    + (
                                        replacementName
                                            ? ` · И.О.: ${replacementName}`
                                            : ' · И.О. не назначен'
                                    )
                                : '',
                        ]
                            .filter(Boolean)
                            .join(' · ');

                        const copy = document.createElement('span');
                        copy.className = 'department-doctor-chip__copy';

                        const strong = document.createElement('strong');
                        strong.textContent = String(
                            option.dataset.name || ''
                        );

                        const small = document.createElement('small');
                        small.textContent = meta;

                        copy.appendChild(strong);
                        copy.appendChild(small);

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className =
                            'department-doctor-chip__remove';
                        removeButton.title = 'Убрать врача из отделения';
                        removeButton.innerHTML = '<i class="bi bi-x-lg"></i>';
                        removeButton.addEventListener(
                            'click',
                            function () {
                                remove(id);
                                searchInput?.focus();
                            }
                        );

                        chip.appendChild(copy);
                        chip.appendChild(removeButton);
                        selectedContainer.appendChild(chip);
                    });

                    root.classList.toggle(
                        'has-value',
                        selectedIds.size > 0
                    );
                }

                function add(id) {
                    const normalized = String(id || '');

                    if (!normalized) {
                        return false;
                    }

                    const option = options.find(
                        item =>
                            String(item.dataset.id || '') === normalized
                    );

                    if (!option || optionUnavailable(option)) {
                        return false;
                    }

                    selectedIds.add(normalized);
                    renderSelected();

                    if (searchInput) {
                        searchInput.value = '';
                    }

                    root.classList.remove('has-query');
                    refreshAvailability();
                    open();

                    return true;
                }

                function remove(id) {
                    selectedIds.delete(String(id || ''));
                    renderSelected();
                    refreshAvailability();
                }

                function set(ids) {
                    selectedIds.clear();

                    (Array.isArray(ids) ? ids : [])
                        .map(id => String(id || ''))
                        .filter(Boolean)
                        .forEach(id => {
                            const option = options.find(
                                item =>
                                    String(item.dataset.id || '') === id
                            );

                            if (
                                option
                                && !optionUnavailable(option)
                            ) {
                                selectedIds.add(id);
                            }
                        });

                    if (searchInput) {
                        searchInput.value = '';
                    }

                    root.classList.remove('has-query');
                    refreshAvailability();
                }

                function clear() {
                    selectedIds.clear();

                    if (searchInput) {
                        searchInput.value = '';
                    }

                    root.classList.remove('has-query');
                    renderSelected();
                    refreshAvailability();
                }

                searchInput?.addEventListener(
                    'focus',
                    open
                );

                searchInput?.addEventListener(
                    'input',
                    function () {
                        root.classList.toggle(
                            'has-query',
                            String(searchInput.value || '').trim() !== ''
                        );
                        open();
                    }
                );

                clearButton?.addEventListener(
                    'click',
                    function () {
                        if (searchInput) {
                            searchInput.value = '';
                        }

                        root.classList.remove('has-query');
                        filterOptions();
                        searchInput?.focus();
                        open();
                    }
                );

                options.forEach(option => {
                    option.addEventListener(
                        'click',
                        function () {
                            if (optionUnavailable(option)) {
                                return;
                            }

                            add(option.dataset.id);
                        }
                    );
                });

                renderSelected();
                refreshAvailability();

                return {
                    add,
                    remove,
                    set,
                    clear,
                    open,
                    close,
                    refreshAvailability,
                };
            }

            document.querySelectorAll('[data-staff-picker]')
                .forEach(root => {
                    const picker = createStaffPicker(root);

                    if (picker.type) {
                        staffPickers[picker.type] = picker;
                    }
                });

            departmentDoctorsPicker = createDepartmentDoctorsPicker(
                document.querySelector(
                    '[data-department-doctors-picker]'
                )
            );

            document.addEventListener(
                'click',
                function (event) {
                    /*
                     * Не закрываем выпадающие списки, если клик произошёл
                     * внутри любого из поисковых селекторов формы.
                     *
                     * Раньше здесь проверялся только [data-staff-picker].
                     * Мультиселектор «Врачи отделения» имеет отдельный
                     * атрибут [data-department-doctors-picker], поэтому после
                     * focus поле открывало меню, а следующий click всплывал
                     * до document и мгновенно его закрывал.
                     */
                    if (
                        event.target.closest('[data-staff-picker]')
                        || event.target.closest(
                            '[data-department-doctors-picker]'
                        )
                    ) {
                        return;
                    }

                    Object.values(staffPickers)
                        .forEach(picker => {
                            picker.close();
                        });

                    departmentDoctorsPicker?.close();
                }
            );


            let hospitalBuildingAddressTimer = null;
            let hospitalBuildingAddressController = null;

            function escapeHospitalBuildingHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function closeHospitalBuildingAddressSuggestions() {
                if (!hospitalBuildingAddressSuggestions) {
                    return;
                }

                hospitalBuildingAddressSuggestions
                    .classList.remove('is-open');
                hospitalBuildingAddressSuggestions.replaceChildren();
            }

            function normalizeHospitalBuildingAddressSuggestions(payload) {
                const items = Array.isArray(payload)
                    ? payload
                    : (
                        Array.isArray(payload?.suggestions)
                            ? payload.suggestions
                            : []
                    );

                return items
                    .map(item => ({
                        value: String(
                            item?.value
                            || item?.unrestricted_value
                            || ''
                        ).trim(),
                        postalCode: String(
                            item?.postal_code || ''
                        ).trim(),
                        city: String(
                            item?.city || ''
                        ).trim(),
                        street: String(
                            item?.street || ''
                        ).trim(),
                        house: String(
                            item?.house || ''
                        ).trim(),
                    }))
                    .filter(item => item.value !== '');
            }

            function renderHospitalBuildingAddressState(
                icon,
                title,
                subtitle = ''
            ) {
                if (!hospitalBuildingAddressSuggestions) {
                    return;
                }

                hospitalBuildingAddressSuggestions.innerHTML = `
                    <div class="hospital-building-address-suggestion hospital-building-address-suggestion--state">
                        <span class="hospital-building-address-suggestion__icon">
                            <i class="bi ${escapeHospitalBuildingHtml(icon)}"></i>
                        </span>
                        <span class="hospital-building-address-suggestion__copy">
                            <strong>${escapeHospitalBuildingHtml(title)}</strong>
                            ${subtitle
                                ? `<span>${escapeHospitalBuildingHtml(subtitle)}</span>`
                                : ''}
                        </span>
                    </div>
                `;

                hospitalBuildingAddressSuggestions
                    .classList.add('is-open');
            }

            function renderHospitalBuildingAddressSuggestions(items) {
                if (!hospitalBuildingAddressSuggestions) {
                    return;
                }

                if (!items.length) {
                    renderHospitalBuildingAddressState(
                        'bi-geo-alt',
                        'Подходящий адрес не найден',
                        'Уточните город, улицу или номер дома.'
                    );
                    return;
                }

                hospitalBuildingAddressSuggestions.innerHTML = items
                    .slice(0, 8)
                    .map((item, index) => {
                        const meta = [
                            item.postalCode,
                            item.city,
                            item.street,
                            item.house
                                ? `д. ${item.house}`
                                : '',
                        ]
                            .filter(Boolean)
                            .join(' · ');

                        return `
                            <button type="button"
                                    class="hospital-building-address-suggestion"
                                    data-hospital-building-address-index="${index}">
                                <span class="hospital-building-address-suggestion__icon">
                                    <i class="bi bi-geo-alt"></i>
                                </span>
                                <span class="hospital-building-address-suggestion__copy">
                                    <strong>${escapeHospitalBuildingHtml(item.value)}</strong>
                                    ${meta
                                        ? `<span>${escapeHospitalBuildingHtml(meta)}</span>`
                                        : ''}
                                </span>
                            </button>
                        `;
                    })
                    .join('');

                hospitalBuildingAddressSuggestions
                    .classList.add('is-open');

                hospitalBuildingAddressSuggestions
                    .querySelectorAll(
                        '[data-hospital-building-address-index]'
                    )
                    .forEach(button => {
                        button.addEventListener(
                            'mousedown',
                            event => event.preventDefault()
                        );

                        button.addEventListener(
                            'click',
                            function () {
                                const item = items[
                                    Number(
                                        button.dataset
                                            .hospitalBuildingAddressIndex
                                    )
                                ];

                                if (!item || !hospitalBuildingAddress) {
                                    return;
                                }

                                hospitalBuildingAddress.value = item.value;
                                hospitalBuildingAddress.dispatchEvent(
                                    new Event('change', {
                                        bubbles: true,
                                    })
                                );

                                closeHospitalBuildingAddressSuggestions();
                                hospitalBuildingAddress.focus();
                            }
                        );
                    });
            }

            async function searchHospitalBuildingAddress(query) {
                const value = String(query || '').trim();

                if (
                    value.length < 2
                    || hospitalBuildingAddressSearchUrl === ''
                ) {
                    closeHospitalBuildingAddressSuggestions();
                    return;
                }

                hospitalBuildingAddressController?.abort();
                hospitalBuildingAddressController = new AbortController();

                renderHospitalBuildingAddressState(
                    'bi-search',
                    'Ищем адрес...',
                    'Подсказки DaData'
                );

                try {
                    const url = new URL(
                        hospitalBuildingAddressSearchUrl,
                        window.location.origin
                    );
                    url.searchParams.set('query', value);
                    url.searchParams.set('q', value);

                    const response = await fetch(
                        url.toString(),
                        {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            signal: hospitalBuildingAddressController.signal,
                        }
                    );

                    if (!response.ok) {
                        throw new Error(
                            `DaData address search failed: ${response.status}`
                        );
                    }

                    renderHospitalBuildingAddressSuggestions(
                        normalizeHospitalBuildingAddressSuggestions(
                            await response.json()
                        )
                    );
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error(
                        '[hospital-building] DaData address search failed',
                        error
                    );

                    renderHospitalBuildingAddressState(
                        'bi-exclamation-circle',
                        'Не удалось получить подсказки',
                        'Повторите поиск адреса.'
                    );
                }
            }

            function scheduleHospitalBuildingAddressSearch(query) {
                window.clearTimeout(hospitalBuildingAddressTimer);

                hospitalBuildingAddressTimer = window.setTimeout(
                    function () {
                        searchHospitalBuildingAddress(query);
                    },
                    280
                );
            }

            hospitalBuildingAddress?.addEventListener(
                'input',
                function () {
                    scheduleHospitalBuildingAddressSearch(
                        hospitalBuildingAddress.value
                    );
                }
            );

            hospitalBuildingAddress?.addEventListener(
                'focus',
                function () {
                    if (
                        String(hospitalBuildingAddress.value || '')
                            .trim()
                            .length >= 2
                    ) {
                        scheduleHospitalBuildingAddressSearch(
                            hospitalBuildingAddress.value
                        );
                    }
                }
            );

            hospitalBuildingAddress?.addEventListener(
                'blur',
                function () {
                    window.setTimeout(
                        closeHospitalBuildingAddressSuggestions,
                        180
                    );
                }
            );

            document.addEventListener(
                'click',
                function (event) {
                    if (
                        hospitalBuildingAddressPicker
                        && !hospitalBuildingAddressPicker.contains(
                            event.target
                        )
                    ) {
                        closeHospitalBuildingAddressSuggestions();
                    }
                }
            );

            function resetHospitalBuildingForm() {
                if (!hospitalBuildingForm) {
                    return;
                }

                hospitalBuildingForm.reset();
                hospitalBuildingForm.action =
                    hospitalBuildingForm.dataset.storeUrl;

                if (hospitalBuildingMode) {
                    hospitalBuildingMode.value = 'create';
                }

                if (hospitalBuildingId) {
                    hospitalBuildingId.value = '';
                }

                if (hospitalBuildingFormTitle) {
                    hospitalBuildingFormTitle.textContent =
                        'Новый корпус стационара';
                }

                hospitalBuildingEnabledField?.classList.add('d-none');
                deleteHospitalBuildingButton?.classList.add('d-none');
                closeHospitalBuildingAddressSuggestions();

                document.querySelectorAll('.js-hospital-building-edit')
                    .forEach(item => item.classList.remove('is-selected'));
            }

            function fillHospitalBuildingForm(id) {
                const data = buildingForms[String(id)];

                if (!data || !hospitalBuildingForm) {
                    resetHospitalBuildingForm();
                    return;
                }

                hospitalBuildingForm.action =
                    hospitalBuildingForm.dataset.updateUrl;

                hospitalBuildingMode.value = 'edit';
                hospitalBuildingId.value = String(data.id || '');
                hospitalBuildingNumber.value = data.number || '';
                hospitalBuildingName.value = data.name || '';
                hospitalBuildingAddress.value = data.address || '';
                closeHospitalBuildingAddressSuggestions();
                hospitalBuildingEnabled.checked =
                    Boolean(data.enabled);

                hospitalBuildingEnabledField?.classList.remove('d-none');
                deleteHospitalBuildingButton?.classList.remove('d-none');

                if (hospitalBuildingFormTitle) {
                    hospitalBuildingFormTitle.textContent =
                        `Корпус №${data.number || ''} — ${data.name || ''}`;
                }

                if (deleteHospitalBuildingButton) {
                    const count = Number(data.departments_count || 0);

                    deleteHospitalBuildingButton.disabled = count > 0;
                    deleteHospitalBuildingButton.title =
                        count > 0
                            ? 'Сначала перенесите или удалите отделения этого корпуса.'
                            : 'Удалить корпус';
                }

                document.querySelectorAll('.js-hospital-building-edit')
                    .forEach(item => {
                        item.classList.toggle(
                            'is-selected',
                            String(item.dataset.buildingId || '')
                                === String(data.id || '')
                        );
                    });
            }

            function restoreHospitalBuildingValidationState() {
                if (!hasHospitalBuildingErrors) {
                    return;
                }

                if (
                    oldHospitalBuildingMode === 'edit'
                    && oldHospitalBuildingId
                ) {
                    fillHospitalBuildingForm(
                        oldHospitalBuildingId
                    );
                } else {
                    resetHospitalBuildingForm();
                }

                if (hospitalBuildingMode) {
                    hospitalBuildingMode.value =
                        oldHospitalBuildingMode || 'create';
                }

                if (hospitalBuildingId) {
                    hospitalBuildingId.value =
                        oldHospitalBuildingId || '';
                }

                if (hospitalBuildingNumber) {
                    hospitalBuildingNumber.value =
                        oldHospitalBuildingNumber || '';
                }

                if (hospitalBuildingName) {
                    hospitalBuildingName.value =
                        oldHospitalBuildingName || '';
                }

                if (hospitalBuildingAddress) {
                    hospitalBuildingAddress.value =
                        oldHospitalBuildingAddress || '';
                }

                if (hospitalBuildingEnabled) {
                    hospitalBuildingEnabled.checked =
                        String(oldHospitalBuildingEnabled) === '1';
                }

                if (hospitalBuildingModal && window.bootstrap?.Modal) {
                    window.bootstrap.Modal
                        .getOrCreateInstance(hospitalBuildingModal)
                        .show();
                }
            }

            function setStaffPickerContext(departmentId = null) {
                currentDepartmentId = departmentId
                    ? Number(departmentId)
                    : null;

                Object.values(staffPickers)
                    .forEach(picker => {
                        picker.refreshAvailability();
                    });

                departmentDoctorsPicker?.refreshAvailability();
            }

            function replaceDepartmentId(template, id) {
                return String(template || '')
                    .replace('__DEPARTMENT__', String(id));
            }

            function syncVisitingEditor() {
                visitingEditor?.classList.toggle(
                    'd-none',
                    !useOwnVisiting?.checked
                );
            }

            function syncVisitingRow(row) {
                const checkbox = row.querySelector('.js-visiting-enabled');
                row.classList.toggle(
                    'is-closed',
                    !checkbox?.checked
                );
            }

            function resetVisitingHours() {
                visitingRows.forEach(row => {
                    const checkbox = row.querySelector('.js-visiting-enabled');
                    const from = row.querySelector('.js-visiting-from');
                    const to = row.querySelector('.js-visiting-to');

                    if (checkbox) {
                        checkbox.checked = false;
                    }

                    if (from) {
                        from.value = '';
                    }

                    if (to) {
                        to.value = '';
                    }

                    syncVisitingRow(row);
                });
            }

            function fillVisitingHours(hours) {
                visitingRows.forEach(row => {
                    const code = row.dataset.visitingDay;
                    const item = hours?.[code] || {};
                    const checkbox = row.querySelector('.js-visiting-enabled');
                    const from = row.querySelector('.js-visiting-from');
                    const to = row.querySelector('.js-visiting-to');

                    if (checkbox) {
                        checkbox.checked = Boolean(item.enabled);
                    }

                    if (from) {
                        from.value = item.from || '';
                    }

                    if (to) {
                        to.value = item.to || '';
                    }

                    syncVisitingRow(row);
                });
            }

            function resetDepartmentForm() {
                departmentForm.reset();
                departmentForm.action = departmentForm.dataset.storeUrl;
                modalTitle.textContent = 'Новое отделение';
                ageMinInput.value = '';
                ageMaxInput.value = '';
                syncAgePreset();

                setStaffPickerContext(null);
                staffPickers.doctor?.clear();
                staffPickers.nurse?.clear();
                departmentDoctorsPicker?.clear();

                document.getElementById('departmentModeRoundTheClock').checked = true;
                useClinicVisiting.checked = true;
                document.getElementById('departmentEnabled').checked = true;
                document.getElementById('departmentSortOrder').value = '0';

                resetVisitingHours();
                syncVisitingEditor();
                syncDepartmentProfileButtons();
            }

            function fillDepartmentForm(id) {
                const data = departmentForms[String(id)];

                if (!data) {
                    resetDepartmentForm();
                    return;
                }

                departmentForm.action = replaceDepartmentId(
                    departmentForm.dataset.updateUrlTemplate,
                    data.id
                );

                modalTitle.textContent = 'Редактирование отделения';

                setStaffPickerContext(data.id);

                document.getElementById('departmentNumber').value = data.number || '';
                document.getElementById('departmentName').value = data.name || '';
                document.getElementById('departmentProfile').value = data.profile || '';
                ageMinInput.value = data.age_min_years ?? '';
                ageMaxInput.value = data.age_max_years ?? '';
                syncAgePreset();
                document.getElementById('departmentBuilding').value = data.building_id || '';

                staffPickers.doctor?.select(
                    data.head_doctor_id || ''
                );
                staffPickers.nurse?.select(
                    data.head_nurse_id || ''
                );
                departmentDoctorsPicker?.set(
                    data.doctor_ids || []
                );

                document.getElementById('departmentDescription').value = data.description || '';
                document.getElementById('departmentEnabled').checked = Boolean(data.enabled);
                document.getElementById('departmentSortOrder').value = String(data.sort_order || 0);

                const mode = data.inpatient_mode || 'round_the_clock';
                const modeInput = departmentForm.querySelector(
                    `[name="inpatient_mode"][value="${mode}"]`
                );

                if (modeInput) {
                    modeInput.checked = true;
                }

                useClinicVisiting.checked = Boolean(
                    data.use_clinic_visiting_hours
                );
                useOwnVisiting.checked = !useClinicVisiting.checked;

                fillVisitingHours(data.visiting_hours || {});
                syncVisitingEditor();
                syncDepartmentProfileButtons();
            }

            addButton?.addEventListener('click', function () {
                resetDepartmentForm();
            });

            document.querySelectorAll('.js-edit-department')
                .forEach(button => {
                    button.addEventListener('click', function () {
                        fillDepartmentForm(button.dataset.departmentId);
                    });
                });

            document.querySelectorAll('.js-delete-department')
                .forEach(button => {
                    button.addEventListener('click', function () {
                        const name = button.dataset.departmentName || 'отделение';

                        if (!confirm(`Удалить отделение «${name}»?`)) {
                            return;
                        }

                        deleteForm.action = replaceDepartmentId(
                            deleteForm.dataset.deleteUrlTemplate,
                            button.dataset.departmentId
                        );
                        deleteForm.submit();
                    });
                });

            useClinicVisiting?.addEventListener('change', syncVisitingEditor);
            useOwnVisiting?.addEventListener('change', syncVisitingEditor);

            visitingRows.forEach(row => {
                const checkbox = row.querySelector('.js-visiting-enabled');
                const timeInputs = row.querySelectorAll('input[type="time"]');

                checkbox?.addEventListener('change', function () {
                    syncVisitingRow(row);
                });

                timeInputs.forEach(input => {
                    input.addEventListener('focus', function () {
                        if (checkbox && !checkbox.checked) {
                            checkbox.checked = true;
                            syncVisitingRow(row);
                        }
                    });
                });
            });

            document.getElementById('copyDepartmentMondayButton')
                ?.addEventListener('click', function () {
                    const monday = document.querySelector(
                        '[data-visiting-day="mon"]'
                    );

                    if (!monday) {
                        return;
                    }

                    const enabled = monday.querySelector('.js-visiting-enabled')?.checked || false;
                    const from = monday.querySelector('.js-visiting-from')?.value || '';
                    const to = monday.querySelector('.js-visiting-to')?.value || '';

                    visitingRows.forEach(row => {
                        const checkbox = row.querySelector('.js-visiting-enabled');
                        const fromInput = row.querySelector('.js-visiting-from');
                        const toInput = row.querySelector('.js-visiting-to');

                        if (checkbox) {
                            checkbox.checked = enabled;
                        }

                        if (fromInput) {
                            fromInput.value = from;
                        }

                        if (toInput) {
                            toInput.value = to;
                        }

                        syncVisitingRow(row);
                    });
                });

            departmentForm?.addEventListener(
                'submit',
                function (event) {
                    const doctorValid =
                        staffPickers.doctor?.validate()
                        ?? true;
                    const nurseValid =
                        staffPickers.nurse?.validate()
                        ?? true;

                    if (!doctorValid || !nurseValid) {
                        event.preventDefault();
                    }
                }
            );

            searchInput?.addEventListener('input', function () {
                const query = String(searchInput.value || '')
                    .trim()
                    .toLowerCase();

                document.querySelectorAll('[data-department-card]')
                    .forEach(card => {
                        const haystack = String(card.dataset.search || '');
                        card.classList.toggle(
                            'd-none',
                            query !== '' && !haystack.includes(query)
                        );
                    });
            });


            document.getElementById('manageHospitalBuildingsButton')
                ?.addEventListener(
                    'click',
                    function () {
                        if (!hasHospitalBuildingErrors) {
                            resetHospitalBuildingForm();
                        }
                    }
                );

            document.getElementById('newHospitalBuildingButton')
                ?.addEventListener(
                    'click',
                    resetHospitalBuildingForm
                );

            document.getElementById('resetHospitalBuildingButton')
                ?.addEventListener(
                    'click',
                    resetHospitalBuildingForm
                );

            document.querySelectorAll('.js-hospital-building-edit')
                .forEach(button => {
                    button.addEventListener(
                        'click',
                        function () {
                            fillHospitalBuildingForm(
                                button.dataset.buildingId
                            );
                        }
                    );
                });

            deleteHospitalBuildingButton?.addEventListener(
                'click',
                function (event) {
                    if (deleteHospitalBuildingButton.disabled) {
                        event.preventDefault();
                        return;
                    }

                    const title =
                        hospitalBuildingFormTitle?.textContent
                        || 'корпус';

                    if (!confirm(`Удалить ${title}?`)) {
                        event.preventDefault();
                    }
                }
            );

            restoreHospitalBuildingValidationState();

            syncVisitingEditor();
            visitingRows.forEach(syncVisitingRow);
        });
    </script>
@endpush
