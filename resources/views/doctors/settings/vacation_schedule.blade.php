@extends('doctors.settings')

@section('settings-title', 'График отпусков')

@section('assets')
    @parent

    <style>
        .vacation-page .settings-container {
            width: min(1580px, 100%);
        }

        .vacation-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .vacation-page-actions {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
        }

        .vacation-year-switcher {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px;
            border: 1px solid #dce6eb;
            border-radius: 13px;
            background: #fff;
        }

        .vacation-year-switcher__button {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 9px;
            color: #607080;
            text-decoration: none;
            transition: .15s ease;
        }

        .vacation-year-switcher__button:hover {
            background: #eaf9fb;
            color: var(--settings-primary-dark);
        }

        .vacation-year-switcher__value {
            min-width: 64px;
            color: #243241;
            font-size: 13px;
            font-weight: 900;
            text-align: center;
        }

        .vacation-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .vacation-stat {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 15px 17px;
            border: 1px solid #e0e8ed;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
        }

        .vacation-stat__icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 18px;
        }

        .vacation-stat__value {
            color: #1f2937;
            font-size: 20px;
            font-weight: 900;
            line-height: 1.1;
        }

        .vacation-stat__label {
            margin-top: 3px;
            color: #7b8794;
            font-size: 10px;
            font-weight: 750;
        }

        .vacation-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .vacation-toolbar__filters {
            display: flex;
            align-items: center;
            gap: 9px;
            flex: 1 1 auto;
        }

        .vacation-toolbar__filters .vacation-year-switcher {
            flex: 0 0 auto;
        }

        .vacation-search {
            position: relative;
            width: min(420px, 100%);
        }

        .vacation-search i {
            position: absolute;
            top: 50%;
            left: 13px;
            color: #8a96a5;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .vacation-search .settings-control {
            padding-left: 39px;
        }

        .vacation-toolbar .settings-control {
            min-height: 40px;
            font-size: 12px;
        }

        .vacation-legend {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #71808e;
            font-size: 10px;
            font-weight: 750;
            flex-wrap: wrap;
        }

        .vacation-legend__item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .vacation-legend__dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #099dac;
        }

        .vacation-legend__dot--planned {
            background: #f59e0b;
        }

        .vacation-timeline-scroll {
            overflow-x: auto;
            border: 1px solid #dfe7ec;
            border-radius: 17px;
            background: #fff;
        }

        .vacation-timeline {
            min-width: 1180px;
        }

        .vacation-timeline__head,
        .vacation-timeline__row {
            display: grid;
            grid-template-columns: 280px minmax(850px, 1fr);
        }

        .vacation-timeline__head {
            position: sticky;
            z-index: 10;
            top: 0;
            border-bottom: 1px solid #e5ebef;
            background: #f8fafb;
        }

        .vacation-timeline__employee-head {
            display: flex;
            align-items: center;
            padding: 12px 14px;
            color: #667585;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .vacation-months {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .vacation-month {
            padding: 12px 4px;
            border-left: 1px solid #edf1f3;
            color: #667585;
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .vacation-timeline__row {
            min-height: 66px;
            border-bottom: 1px solid #edf1f4;
        }

        .vacation-timeline__row:last-child {
            border-bottom: 0;
        }

        .vacation-timeline__row.is-filtered {
            display: none;
        }

        .vacation-employee {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 10px 14px;
            background: #fff;
        }

        .vacation-employee__avatar {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #099dac, #10b7c6);
            color: #fff;
            font-size: 10px;
            font-weight: 900;
        }

        .vacation-employee__body {
            min-width: 0;
        }

        .vacation-employee__name {
            overflow: hidden;
            color: #243241;
            font-size: 12px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vacation-employee__role {
            margin-top: 2px;
            overflow: hidden;
            color: #7b8794;
            font-size: 9px;
            font-weight: 650;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vacation-lane {
            position: relative;
            min-height: 66px;
            background:
                linear-gradient(to right,
                    transparent calc(8.333333% - 1px), #edf1f3 calc(8.333333% - 1px), #edf1f3 8.333333%,
                    transparent 8.333333%, transparent calc(16.666666% - 1px), #edf1f3 calc(16.666666% - 1px), #edf1f3 16.666666%,
                    transparent 16.666666%, transparent calc(25% - 1px), #edf1f3 calc(25% - 1px), #edf1f3 25%,
                    transparent 25%, transparent calc(33.333333% - 1px), #edf1f3 calc(33.333333% - 1px), #edf1f3 33.333333%,
                    transparent 33.333333%, transparent calc(41.666666% - 1px), #edf1f3 calc(41.666666% - 1px), #edf1f3 41.666666%,
                    transparent 41.666666%, transparent calc(50% - 1px), #edf1f3 calc(50% - 1px), #edf1f3 50%,
                    transparent 50%, transparent calc(58.333333% - 1px), #edf1f3 calc(58.333333% - 1px), #edf1f3 58.333333%,
                    transparent 58.333333%, transparent calc(66.666666% - 1px), #edf1f3 calc(66.666666% - 1px), #edf1f3 66.666666%,
                    transparent 66.666666%, transparent calc(75% - 1px), #edf1f3 calc(75% - 1px), #edf1f3 75%,
                    transparent 75%, transparent calc(83.333333% - 1px), #edf1f3 calc(83.333333% - 1px), #edf1f3 83.333333%,
                    transparent 83.333333%, transparent calc(91.666666% - 1px), #edf1f3 calc(91.666666% - 1px), #edf1f3 91.666666%,
                    transparent 91.666666%);
        }

        .vacation-lane__empty {
            position: absolute;
            top: 50%;
            left: 14px;
            color: #b0bbc4;
            font-size: 9px;
            transform: translateY(-50%);
        }

        .vacation-bar {
            position: absolute;
            z-index: 2;
            top: 17px;
            min-width: 12px;
            height: 32px;
            overflow: hidden;
            padding: 0 9px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, #099dac, #0fb3c2);
            color: #fff;
            box-shadow: 0 6px 16px rgba(9, 157, 172, .22);
            font-size: 9px;
            font-weight: 850;
            text-align: left;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
            transition: .15s ease;
        }

        .vacation-bar:hover {
            z-index: 4;
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(9, 157, 172, .28);
        }

        .vacation-bar.is-planned {
            background: linear-gradient(135deg, #f59e0b, #f7b23c);
            box-shadow: 0 6px 16px rgba(245, 158, 11, .2);
        }

        .vacation-list {
            display: grid;
            gap: 9px;
        }

        .vacation-list-item {
            display: grid;
            grid-template-columns: minmax(240px, 1.25fr) minmax(190px, .8fr) minmax(160px, .7fr) minmax(120px, .5fr) auto;
            gap: 12px;
            align-items: center;
            padding: 11px 12px;
            border: 1px solid #e4eaee;
            border-radius: 14px;
            background: #fff;
        }

        .vacation-list-item__name {
            color: #263442;
            font-size: 12px;
            font-weight: 850;
        }

        .vacation-list-item__meta {
            margin-top: 2px;
            color: #81909d;
            font-size: 9px;
        }

        .vacation-period {
            color: #415364;
            font-size: 11px;
            font-weight: 800;
        }

        .vacation-type-badge,
        .vacation-status-badge {
            display: inline-flex;
            width: fit-content;
            min-height: 27px;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 850;
        }

        .vacation-type-badge {
            background: #e8f8fa;
            color: #047786;
        }

        .vacation-status-badge.is-approved {
            background: #edf9f1;
            color: #24724e;
        }

        .vacation-status-badge.is-planned {
            background: #fff7e6;
            color: #9a5d09;
        }

        .vacation-list-actions {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }

        .vacation-icon-button {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 1px solid #dce4e9;
            border-radius: 10px;
            background: #fff;
            color: #667585;
            cursor: pointer;
        }

        .vacation-icon-button:hover {
            background: #eefafb;
            color: var(--settings-primary-dark);
        }

        .vacation-icon-button.is-danger:hover {
            border-color: #fecaca;
            background: #fff4f4;
            color: #b42318;
        }

        .vacation-modal .modal-dialog {
            width: min(920px, calc(100vw - 28px));
            max-width: 920px;
        }

        .vacation-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 26px;
            background: #fff;
            box-shadow: 0 34px 90px rgba(15, 23, 42, .24);
        }

        .vacation-modal .modal-header {
            position: relative;
            align-items: flex-start;
            gap: 18px;
            padding: 24px 26px 22px;
            border-bottom: 1px solid #e5edf0;
            background:
                radial-gradient(circle at 86% 10%, rgba(9, 157, 172, .13), transparent 33%),
                linear-gradient(135deg, #f1fbfc 0%, #ffffff 72%);
        }

        .vacation-modal-head {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 14px;
        }

        .vacation-modal-head__icon {
            display: grid;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            place-items: center;
            border-radius: 15px;
            background: linear-gradient(135deg, #099dac, #16b8c7);
            color: #fff;
            font-size: 21px;
            box-shadow: 0 10px 22px rgba(9, 157, 172, .22);
        }

        .vacation-modal .modal-title {
            margin: 0;
            color: #182738;
            font-size: 21px;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .vacation-modal-head__subtitle {
            margin-top: 4px;
            color: #748394;
            font-size: 12px;
            line-height: 1.45;
        }

        .vacation-modal .btn-close {
            margin: 2px 0 0 auto;
            padding: 9px;
            border-radius: 10px;
            opacity: .55;
        }

        .vacation-modal .btn-close:hover {
            background-color: #edf4f5;
            opacity: .85;
        }

        .vacation-modal .modal-body {
            padding: 22px 26px 24px;
            background: #fbfdfe;
        }

        .vacation-modal .modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 26px 20px;
            border-top: 1px solid #e5edf0;
            background: #fff;
        }

        .vacation-form-stack {
            display: grid;
            gap: 16px;
        }

        .vacation-form-section {
            padding: 17px;
            border: 1px solid #e3eaee;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .025);
        }

        .vacation-form-section__head {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 13px;
            color: #344456;
            font-size: 12px;
            font-weight: 900;
        }

        .vacation-form-section__head i {
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border-radius: 9px;
            background: #e9f8fa;
            color: #087f8e;
            font-size: 13px;
        }

        .vacation-employee-field {
            position: relative;
        }

        .vacation-employee-field .settings-control {
            min-height: 48px;
            padding-left: 43px;
            padding-right: 42px;
            background: #fbfdfe;
            font-weight: 750;
        }

        .vacation-employee-field > i {
            position: absolute;
            z-index: 2;
            top: 24px;
            left: 14px;
            color: #099dac;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .vacation-employee-search-clear {
            position: absolute;
            z-index: 3;
            top: 24px;
            right: 9px;
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #82909d;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .vacation-employee-search-clear:hover {
            background: #eaf8fa;
            color: #087f8e;
        }

        .vacation-employee-search-results {
            display: grid;
            gap: 5px;
            max-height: 250px;
            margin-top: 8px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #dbe7ea;
            border-radius: 13px;
            background: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
            scrollbar-width: thin;
        }

        .vacation-employee-search-results.d-none {
            display: none !important;
        }

        .vacation-employee-search-option {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            width: 100%;
            padding: 9px 10px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #263548;
            text-align: left;
            cursor: pointer;
            transition: .14s ease;
        }

        .vacation-employee-search-option:hover,
        .vacation-employee-search-option.is-active {
            background: #eefafb;
        }

        .vacation-employee-search-option.is-selected {
            background: #e4f7f9;
        }

        .vacation-employee-search-option__avatar {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 10px;
            background: #e4f6f8;
            color: #087f8e;
            font-size: 10px;
            font-weight: 900;
        }

        .vacation-employee-search-option strong {
            display: block;
            overflow: hidden;
            font-size: 11px;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vacation-employee-search-option small {
            display: block;
            margin-top: 2px;
            color: #7c8a97;
            font-size: 9px;
            font-weight: 650;
        }

        .vacation-employee-search-empty {
            padding: 16px 12px;
            color: #84919e;
            font-size: 10px;
            text-align: center;
        }

        .vacation-employee-preview {
            display: none;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid #d8ecef;
            border-radius: 12px;
            background: #f4fbfc;
        }

        .vacation-employee-preview.is-visible {
            display: flex;
        }

        .vacation-employee-preview__avatar {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 10px;
            background: #dff5f7;
            color: #087f8e;
            font-size: 13px;
            font-weight: 900;
        }

        .vacation-employee-preview__name {
            color: #273647;
            font-size: 12px;
            font-weight: 850;
        }

        .vacation-employee-preview__role {
            margin-top: 2px;
            color: #7a8997;
            font-size: 10px;
        }

        .vacation-picker-note {
            display: flex;
            align-items: flex-start;
            gap: 7px;
            margin-top: 9px;
            color: #7a8997;
            font-size: 10px;
            line-height: 1.45;
        }

        .vacation-picker-note i {
            margin-top: 1px;
            color: #099dac;
        }

        .vacation-type-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
        }

        .vacation-type-option,
        .vacation-status-option {
            position: relative;
            cursor: pointer;
        }

        .vacation-type-option input,
        .vacation-status-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .vacation-type-card {
            display: flex;
            min-height: 86px;
            align-items: flex-start;
            gap: 9px;
            padding: 12px;
            border: 1px solid #dfe7eb;
            border-radius: 14px;
            background: #fff;
            transition: .16s ease;
        }

        .vacation-type-option:hover .vacation-type-card {
            border-color: #b9dce1;
            background: #fbfeff;
        }

        .vacation-type-option input:checked + .vacation-type-card {
            border-color: #70ccd5;
            background: #eefafb;
            box-shadow: inset 0 0 0 1px rgba(9, 157, 172, .08);
        }

        .vacation-type-card__icon {
            display: grid;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            place-items: center;
            border-radius: 9px;
            background: #edf5f6;
            color: #637482;
            font-size: 14px;
        }

        .vacation-type-option input:checked + .vacation-type-card .vacation-type-card__icon {
            background: #d8f2f5;
            color: #087f8e;
        }

        .vacation-type-card strong {
            display: block;
            color: #334255;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.25;
        }

        .vacation-type-card span {
            display: block;
            margin-top: 3px;
            color: #8995a1;
            font-size: 9px;
            line-height: 1.25;
        }

        .vacation-period-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 42px minmax(0, 1fr) minmax(150px, .55fr);
            gap: 10px;
            align-items: end;
        }

        .vacation-period-arrow {
            display: grid;
            width: 42px;
            height: 46px;
            place-items: center;
            border-radius: 12px;
            background: #f0f6f7;
            color: #79909a;
        }

        .vacation-date-field {
            position: relative;
        }

        .vacation-date-field > i {
            position: absolute;
            z-index: 2;
            right: 13px;
            bottom: 14px;
            color: #728391;
            pointer-events: none;
        }

        .vacation-date-field .settings-control {
            min-height: 46px;
            padding-right: 39px;
            background: #fbfdfe;
        }

        .vacation-duration-box {
            min-height: 46px;
            padding: 8px 12px;
            border: 1px solid #d9ecef;
            border-radius: 12px;
            background: #f2fbfc;
        }

        .vacation-duration-box__label {
            color: #78909a;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .vacation-duration-box__value {
            margin-top: 2px;
            color: #087f8e;
            font-size: 15px;
            font-weight: 900;
        }

        .vacation-status-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .vacation-status-card {
            display: flex;
            min-height: 60px;
            align-items: center;
            gap: 10px;
            padding: 11px 13px;
            border: 1px solid #e0e7eb;
            border-radius: 13px;
            background: #fff;
            transition: .16s ease;
        }

        .vacation-status-card__dot {
            width: 11px;
            height: 11px;
            flex: 0 0 11px;
            border-radius: 50%;
            background: #c8d0d6;
        }

        .vacation-status-card strong {
            display: block;
            color: #334255;
            font-size: 11px;
            font-weight: 900;
        }

        .vacation-status-card span {
            display: block;
            margin-top: 2px;
            color: #8693a0;
            font-size: 9px;
        }

        .vacation-status-option input[value="planned"]:checked + .vacation-status-card {
            border-color: #efc770;
            background: #fff9ec;
        }

        .vacation-status-option input[value="planned"]:checked + .vacation-status-card .vacation-status-card__dot {
            background: #f0a71a;
            box-shadow: 0 0 0 4px rgba(240, 167, 26, .12);
        }

        .vacation-status-option input[value="approved"]:checked + .vacation-status-card {
            border-color: #8cd3ad;
            background: #f1fbf6;
        }

        .vacation-status-option input[value="approved"]:checked + .vacation-status-card .vacation-status-card__dot {
            background: #27a769;
            box-shadow: 0 0 0 4px rgba(39, 167, 105, .12);
        }

        .vacation-comment-wrap textarea {
            min-height: 84px;
            resize: vertical;
            background: #fbfdfe;
        }

        .vacation-footer-summary {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            color: #6e7e8e;
            font-size: 10px;
        }

        .vacation-footer-summary i {
            color: #099dac;
            font-size: 15px;
        }

        .vacation-footer-summary strong {
            color: #405263;
            font-weight: 850;
        }

        .vacation-footer-actions {
            display: flex;
            gap: 9px;
            flex: 0 0 auto;
        }

        .vacation-empty {
            padding: 30px;
            border: 1px dashed #d5e0e5;
            border-radius: 15px;
            color: #7c8a98;
            background: #fbfdfe;
            text-align: center;
            font-size: 11px;
        }

        @media (max-width: 900px) {
            .vacation-page-header,
            .vacation-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .vacation-stats {
                grid-template-columns: 1fr;
            }

            .vacation-toolbar__filters {
                align-items: stretch;
                flex-direction: column;
            }

            .vacation-toolbar__filters .vacation-year-switcher {
                align-self: flex-start;
            }

            .vacation-search {
                width: 100%;
            }

            .vacation-list-item {
                grid-template-columns: 1fr 1fr;
            }

            .vacation-list-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 760px) {
            .vacation-type-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .vacation-period-layout {
                grid-template-columns: 1fr;
            }

            .vacation-period-arrow {
                width: 100%;
                height: 34px;
            }

            .vacation-period-arrow i {
                transform: rotate(90deg);
            }

            .vacation-modal .modal-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .vacation-footer-actions {
                width: 100%;
            }

            .vacation-footer-actions .settings-button {
                flex: 1;
            }
        }

        @media (max-width: 600px) {
            .vacation-list-item,
            .vacation-status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('sub-main')
    @php
        $monthNames = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
        $vacationTypeLabels = [
            'annual' => 'Ежегодный',
            'study' => 'Учебный',
            'unpaid' => 'Без сохранения',
            'maternity' => 'По беременности/уходу',
            'other' => 'Другой',
        ];
        $yearStart = \Illuminate\Support\Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = \Illuminate\Support\Carbon::create($year, 12, 31)->endOfDay();
        $daysInYear = $yearStart->isLeapYear() ? 366 : 365;
        $storeRoute = route('doctors.settings.vacation-schedule.store');
    @endphp

    <main class="settings-page vacation-page">
        <div class="settings-container">
            <div class="settings-page-header vacation-page-header">
                <div>
                    <h1 class="settings-page-title">График отпусков</h1>
                    <p class="settings-page-description">
                        Годовой график отсутствия врачей и медицинского персонала. Периоды можно планировать заранее,
                        утверждать и редактировать по мере изменения кадрового графика.
                    </p>
                </div>

                <div class="vacation-page-actions">
                    <button type="button"
                            class="settings-button settings-button--primary"
                            id="addVacationBtn"
                            data-bs-toggle="modal"
                            data-bs-target="#vacationModal">
                        <i class="bi bi-plus-lg"></i>
                        Добавить отпуск
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
                    <div class="fw-bold mb-1">Не удалось сохранить отпуск:</div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="vacation-stats">
                <article class="vacation-stat">
                    <div class="vacation-stat__icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="vacation-stat__value">{{ $stats['employees'] }}</div>
                        <div class="vacation-stat__label">сотрудников в графике</div>
                    </div>
                </article>

                <article class="vacation-stat">
                    <div class="vacation-stat__icon"><i class="bi bi-calendar2-check"></i></div>
                    <div>
                        <div class="vacation-stat__value">{{ $stats['vacations'] }}</div>
                        <div class="vacation-stat__label">периодов в {{ $year }} году</div>
                    </div>
                </article>

                <article class="vacation-stat">
                    <div class="vacation-stat__icon"><i class="bi bi-airplane"></i></div>
                    <div>
                        <div class="vacation-stat__value">{{ $stats['currently_away'] }}</div>
                        <div class="vacation-stat__label">сейчас в утверждённом отпуске</div>
                    </div>
                </article>
            </div>

            <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Годовой график</h2>
                        <div class="settings-panel__subtitle">
                            Нажмите на период в шкале, чтобы изменить его.
                        </div>
                    </div>
                </div>

                <div class="settings-panel__body">
                    <div class="vacation-toolbar">
                        <div class="vacation-toolbar__filters">
                            <div class="vacation-search">
                                <i class="bi bi-search"></i>
                                <input type="text"
                                       class="settings-control"
                                       id="vacationEmployeeSearch"
                                       placeholder="Найти сотрудника">
                            </div>

                            <select class="settings-control" id="vacationCategoryFilter" style="width:auto;min-width:180px">
                                <option value="all">Все сотрудники</option>
                                <option value="doctor">Врачи</option>
                                <option value="nurse">Медсёстры</option>
                                <option value="registrar">Регистратура</option>
                            </select>

                            <div class="vacation-year-switcher">
                                <a class="vacation-year-switcher__button"
                                   href="{{ route('doctors.settings.vacation-schedule', ['year' => $year - 1]) }}"
                                   title="Предыдущий год">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <span class="vacation-year-switcher__value">{{ $year }}</span>
                                <a class="vacation-year-switcher__button"
                                   href="{{ route('doctors.settings.vacation-schedule', ['year' => $year + 1]) }}"
                                   title="Следующий год">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="vacation-legend">
                            <span class="vacation-legend__item">
                                <span class="vacation-legend__dot"></span>
                                Утверждён
                            </span>
                            <span class="vacation-legend__item">
                                <span class="vacation-legend__dot vacation-legend__dot--planned"></span>
                                Запланирован
                            </span>
                        </div>
                    </div>

                    @if($employees->isEmpty())
                        <div class="vacation-empty">
                            В клинике пока нет сотрудников, которых можно вывести в график отпусков.
                        </div>
                    @else
                        <div class="vacation-timeline-scroll">
                            <div class="vacation-timeline">
                                <div class="vacation-timeline__head">
                                    <div class="vacation-timeline__employee-head">Сотрудник</div>
                                    <div class="vacation-months">
                                        @foreach($monthNames as $monthName)
                                            <div class="vacation-month">{{ $monthName }}</div>
                                        @endforeach
                                    </div>
                                </div>

                                @foreach($employees as $employee)
                                    @php
                                        $employeeVacations = $vacationsByEmployee->get($employee['key'], collect());
                                    @endphp
                                    <div class="vacation-timeline__row js-vacation-employee-row"
                                         data-category="{{ $employee['category'] }}"
                                         data-search="{{ mb_strtolower($employee['name'] . ' ' . $employee['role']) }}">
                                        <div class="vacation-employee">
                                            <div class="vacation-employee__avatar">{{ $employee['initials'] ?: '—' }}</div>
                                            <div class="vacation-employee__body">
                                                <div class="vacation-employee__name">{{ $employee['name'] }}</div>
                                                <div class="vacation-employee__role">{{ $employee['role'] }}</div>
                                            </div>
                                        </div>

                                        <div class="vacation-lane">
                                            @if($employeeVacations->isEmpty())
                                                <span class="vacation-lane__empty">Нет периодов</span>
                                            @endif

                                            @foreach($employeeVacations as $vacation)
                                                @php
                                                    $barFrom = $vacation->date_from->copy()->startOfDay();
                                                    $barTo = $vacation->date_to->copy()->startOfDay();
                                                    if ($barFrom->lt($yearStart)) $barFrom = $yearStart->copy();
                                                    if ($barTo->gt($yearEnd)) $barTo = $yearEnd->copy()->startOfDay();
                                                    $left = ($yearStart->diffInDays($barFrom) / $daysInYear) * 100;
                                                    $width = (($barFrom->diffInDays($barTo) + 1) / $daysInYear) * 100;
                                                    $typeLabel = $vacationTypeLabels[$vacation->vacation_type] ?? 'Отпуск';
                                                @endphp

                                                <button type="button"
                                                        class="vacation-bar js-edit-vacation {{ $vacation->status === 'planned' ? 'is-planned' : '' }}"
                                                        style="left: {{ number_format($left, 4, '.', '') }}%; width: {{ number_format($width, 4, '.', '') }}%"
                                                        title="{{ $typeLabel }}: {{ $vacation->date_from->format('d.m.Y') }} — {{ $vacation->date_to->format('d.m.Y') }}"
                                                        data-id="{{ $vacation->id }}"
                                                        data-update-url="{{ route('doctors.settings.vacation-schedule.update', ['vacation' => $vacation->id]) }}"
                                                        data-employee-key="{{ $vacation->employee_type }}:{{ $vacation->employee_id }}"
                                                        data-replace-doctor-id="{{ $vacation->replace_doctor_id ?? '' }}"
                                                data-replace-nurse-id="{{ $vacation->replace_nurse_id ?? '' }}"
                                                        data-vacation-type="{{ $vacation->vacation_type }}"
                                                        data-date-from="{{ $vacation->date_from->format('Y-m-d') }}"
                                                        data-date-to="{{ $vacation->date_to->format('Y-m-d') }}"
                                                        data-status="{{ $vacation->status }}"
                                                        data-comment="{{ $vacation->comment }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#vacationModal">
                                                    {{ $vacation->date_from->format('d.m') }}–{{ $vacation->date_to->format('d.m') }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="settings-panel">
                <div class="settings-panel__header">
                    <div class="settings-panel__title-wrap">
                        <h2 class="settings-panel__title">Ближайшие отпуска</h2>
                        <div class="settings-panel__subtitle">
                            Отпуска, которые идут сейчас или начнутся в ближайшие 6 месяцев.
                        </div>
                    </div>
                </div>

                <div class="settings-panel__body">
                    @if($nearestVacations->isEmpty())
                        <div class="vacation-empty">
                            В ближайшие 6 месяцев отпусков пока нет.
                        </div>
                    @else
                        <div class="vacation-list">
                            @foreach($nearestVacations as $vacation)
                                @php
                                    $typeLabel = $vacationTypeLabels[$vacation->vacation_type] ?? 'Другой';
                                @endphp
                                <article class="vacation-list-item">
                                    <div>
                                        <div class="vacation-list-item__name">{{ $vacation->employee_label }}</div>
                                        <div class="vacation-list-item__meta">{{ $vacation->employee_role }}</div>
                                        <div class="vacation-list-item__meta">
                                            И.О.: {{ $vacation->replacement_employee_label ?: 'не назначен' }}
                                        </div>
                                    </div>

                                    <div class="vacation-period">
                                        {{ $vacation->date_from->format('d.m.Y') }} — {{ $vacation->date_to->format('d.m.Y') }}
                                    </div>

                                    <span class="vacation-type-badge">{{ $typeLabel }}</span>

                                    <span class="vacation-status-badge {{ $vacation->status === 'approved' ? 'is-approved' : 'is-planned' }}">
                                        {{ $vacation->status === 'approved' ? 'Утверждён' : 'Запланирован' }}
                                    </span>

                                    <div class="vacation-list-actions">
                                        <button type="button"
                                                class="vacation-icon-button js-edit-vacation"
                                                title="Изменить"
                                                data-id="{{ $vacation->id }}"
                                                data-update-url="{{ route('doctors.settings.vacation-schedule.update', ['vacation' => $vacation->id]) }}"
                                                data-employee-key="{{ $vacation->employee_type }}:{{ $vacation->employee_id }}"
                                                data-replace-doctor-id="{{ $vacation->replace_doctor_id ?? '' }}"
                                                data-replace-nurse-id="{{ $vacation->replace_nurse_id ?? '' }}"
                                                data-vacation-type="{{ $vacation->vacation_type }}"
                                                data-date-from="{{ $vacation->date_from->format('Y-m-d') }}"
                                                data-date-to="{{ $vacation->date_to->format('Y-m-d') }}"
                                                data-status="{{ $vacation->status }}"
                                                data-comment="{{ $vacation->comment }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#vacationModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <form method="post"
                                              action="{{ route('doctors.settings.vacation-schedule.delete', $vacation->id) }}"
                                              onsubmit="return confirm('Удалить этот период отпуска?')">
                                            @csrf
                                            <button type="submit"
                                                    class="vacation-icon-button is-danger"
                                                    title="Удалить">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade vacation-modal"
         id="vacationModal"
         tabindex="-1"
         aria-labelledby="vacationModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form action="{{ $storeRoute }}"
                  method="post"
                  id="vacationForm"
                  class="modal-content">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" id="vacationEditingId" value="">

                <div class="modal-header">
                    <div class="vacation-modal-head">
                        <div class="vacation-modal-head__icon">
                            <i class="bi bi-suitcase2"></i>
                        </div>
                        <div>
                            <h2 class="modal-title" id="vacationModalLabel">Добавить отпуск</h2>
                            <div class="vacation-modal-head__subtitle">
                                Укажите сотрудника, вид отпуска и период отсутствия.
                            </div>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="vacation-form-stack">
                        <section class="vacation-form-section">
                            <div class="vacation-form-section__head">
                                <i class="bi bi-person-badge"></i>
                                Сотрудник
                            </div>

                            <div class="vacation-employee-field" id="vacationEmployeeField">
                                <i class="bi bi-search"></i>

                                <input type="text"
                                       class="settings-control"
                                       id="vacationEmployeeSearchInput"
                                       autocomplete="off"
                                       placeholder="Начните вводить ФИО или должность"
                                       required>

                                <input type="hidden"
                                       name="employee_key"
                                       id="vacationEmployee"
                                       value="">

                                <input type="hidden"
                                       name="employee_type"
                                       id="vacationEmployeeType"
                                       value="">

                                <button type="button"
                                        class="vacation-employee-search-clear d-none"
                                        id="vacationEmployeeSearchClear"
                                        aria-label="Очистить сотрудника">
                                    <i class="bi bi-x-lg"></i>
                                </button>

                                <div class="vacation-employee-search-results d-none"
                                     id="vacationEmployeeSearchResults">
                                    @foreach($employees as $employee)
                                        @php
                                            $employeeWords = preg_split('/\s+/u', trim((string) $employee['name']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                            $employeeInitials = collect($employeeWords)
                                                ->take(2)
                                                ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                                ->implode('');

                                            [$employeeTypeValue, $employeeIdValue] = array_pad(
                                                explode(':', (string) $employee['key'], 2),
                                                2,
                                                ''
                                            );
                                        @endphp

                                        <button type="button"
                                                class="vacation-employee-search-option"
                                                data-employee-key="{{ $employee['key'] }}"
                                                data-employee-id="{{ $employeeIdValue }}"
                                                data-employee-type="{{ $employeeTypeValue }}"
                                                data-name="{{ $employee['name'] }}"
                                                data-role="{{ $employee['role'] }}"
                                                data-search="{{ mb_strtolower($employee['name'] . ' ' . $employee['role']) }}">
                                            <span class="vacation-employee-search-option__avatar">
                                                {{ $employeeInitials ?: 'С' }}
                                            </span>
                                            <span>
                                                <strong>{{ $employee['name'] }}</strong>
                                                <small>{{ $employee['role'] }}</small>
                                            </span>
                                        </button>
                                    @endforeach

                                    <div class="vacation-employee-search-empty d-none"
                                         id="vacationEmployeeSearchEmpty">
                                        Сотрудники по этому запросу не найдены.
                                    </div>
                                </div>
                            </div>

                            <div class="vacation-employee-preview" id="vacationEmployeePreview">
                                <div class="vacation-employee-preview__avatar" id="vacationEmployeeInitials">—</div>
                                <div>
                                    <div class="vacation-employee-preview__name" id="vacationEmployeeName">—</div>
                                    <div class="vacation-employee-preview__role" id="vacationEmployeeRole">—</div>
                                </div>
                            </div>
                        </section>

                        <section class="vacation-form-section d-none"
                                 id="vacationReplacementSection">
                            <div class="vacation-form-section__head">
                                <i class="bi bi-person-check"></i>
                                <span id="vacationReplacementTitle">Исполняющий обязанности</span>
                            </div>

                            <div class="vacation-employee-field"
                                 id="vacationReplacementField">
                                <i class="bi bi-search"></i>

                                <input type="text"
                                       class="settings-control"
                                       id="vacationReplacementSearchInput"
                                       autocomplete="off"
                                       placeholder="Выберите исполняющего обязанности">

                                <input type="hidden"
                                       name="replace_doctor_id"
                                       id="vacationReplaceDoctor"
                                       value="">

                                <input type="hidden"
                                       name="replace_nurse_id"
                                       id="vacationReplaceNurse"
                                       value="">

                                <button type="button"
                                        class="vacation-employee-search-clear d-none"
                                        id="vacationReplacementSearchClear"
                                        aria-label="Очистить исполняющего обязанности">
                                    <i class="bi bi-x-lg"></i>
                                </button>

                                <div class="vacation-employee-search-results d-none"
                                     id="vacationReplacementSearchResults">
                                    @foreach($replacementDoctors as $replacementEmployee)
                                        @php
                                            $replacementWords = preg_split('/\s+/u', trim((string) $replacementEmployee['name']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                            $replacementInitials = collect($replacementWords)
                                                ->take(2)
                                                ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                                ->implode('');
                                        @endphp

                                        <button type="button"
                                                class="vacation-employee-search-option vacation-replacement-option"
                                                data-employee-type="doctor"
                                                data-employee-id="{{ $replacementEmployee['id'] }}"
                                                data-name="{{ $replacementEmployee['name'] }}"
                                                data-role="{{ $replacementEmployee['role'] }}"
                                                data-search="{{ mb_strtolower($replacementEmployee['name'] . ' ' . $replacementEmployee['role']) }}">
                                            <span class="vacation-employee-search-option__avatar">
                                                {{ $replacementInitials ?: 'В' }}
                                            </span>
                                            <span>
                                                <strong>{{ $replacementEmployee['name'] }}</strong>
                                                <small>{{ $replacementEmployee['role'] }}</small>
                                            </span>
                                        </button>
                                    @endforeach

                                    @foreach($replacementNurses as $replacementEmployee)
                                        @php
                                            $replacementWords = preg_split('/\s+/u', trim((string) $replacementEmployee['name']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                            $replacementInitials = collect($replacementWords)
                                                ->take(2)
                                                ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                                ->implode('');
                                        @endphp

                                        <button type="button"
                                                class="vacation-employee-search-option vacation-replacement-option"
                                                data-employee-type="nurse"
                                                data-employee-id="{{ $replacementEmployee['id'] }}"
                                                data-name="{{ $replacementEmployee['name'] }}"
                                                data-role="{{ $replacementEmployee['role'] }}"
                                                data-search="{{ mb_strtolower($replacementEmployee['name'] . ' ' . $replacementEmployee['role']) }}">
                                            <span class="vacation-employee-search-option__avatar">
                                                {{ $replacementInitials ?: 'М' }}
                                            </span>
                                            <span>
                                                <strong>{{ $replacementEmployee['name'] }}</strong>
                                                <small>{{ $replacementEmployee['role'] }}</small>
                                            </span>
                                        </button>
                                    @endforeach

                                    <div class="vacation-employee-search-empty d-none"
                                         id="vacationReplacementSearchEmpty">
                                        Подходящие сотрудники не найдены.
                                    </div>
                                </div>
                            </div>

                            <div class="vacation-picker-note" id="vacationReplacementNote">
                                <i class="bi bi-info-circle"></i>
                                Для утверждённого отпуска И.О. обязателен. Для запланированного можно назначить заранее.
                            </div>
                        </section>

                        <section class="vacation-form-section">
                            <div class="vacation-form-section__head">
                                <i class="bi bi-tags"></i>
                                Тип отпуска
                            </div>

                            <div class="vacation-type-grid">
                                <label class="vacation-type-option">
                                    <input type="radio" name="vacation_type" value="annual" checked>
                                    <span class="vacation-type-card">
                                        <span class="vacation-type-card__icon"><i class="bi bi-sun"></i></span>
                                        <span><strong>Ежегодный</strong><span>Оплачиваемый отпуск</span></span>
                                    </span>
                                </label>

                                <label class="vacation-type-option">
                                    <input type="radio" name="vacation_type" value="study">
                                    <span class="vacation-type-card">
                                        <span class="vacation-type-card__icon"><i class="bi bi-mortarboard"></i></span>
                                        <span><strong>Учебный</strong><span>На период обучения</span></span>
                                    </span>
                                </label>

                                <label class="vacation-type-option">
                                    <input type="radio" name="vacation_type" value="unpaid">
                                    <span class="vacation-type-card">
                                        <span class="vacation-type-card__icon"><i class="bi bi-wallet2"></i></span>
                                        <span><strong>Без сохранения</strong><span>Неоплачиваемый</span></span>
                                    </span>
                                </label>

                                <label class="vacation-type-option">
                                    <input type="radio" name="vacation_type" value="maternity">
                                    <span class="vacation-type-card">
                                        <span class="vacation-type-card__icon"><i class="bi bi-heart"></i></span>
                                        <span><strong>Декретный</strong><span>Беременность / уход</span></span>
                                    </span>
                                </label>

                                <label class="vacation-type-option">
                                    <input type="radio" name="vacation_type" value="other">
                                    <span class="vacation-type-card">
                                        <span class="vacation-type-card__icon"><i class="bi bi-three-dots"></i></span>
                                        <span><strong>Другой</strong><span>Иной вид отсутствия</span></span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <section class="vacation-form-section">
                            <div class="vacation-form-section__head">
                                <i class="bi bi-calendar-range"></i>
                                Период отсутствия
                            </div>

                            <div class="vacation-period-layout">
                                <div class="vacation-date-field">
                                    <label class="settings-label" for="vacationDateFrom">Начало</label>
                                    <input class="settings-control"
                                           type="date"
                                           name="date_from"
                                           id="vacationDateFrom"
                                           required>
                                    <i class="bi bi-calendar3"></i>
                                </div>

                                <div class="vacation-period-arrow" aria-hidden="true">
                                    <i class="bi bi-arrow-right"></i>
                                </div>

                                <div class="vacation-date-field">
                                    <label class="settings-label" for="vacationDateTo">Окончание</label>
                                    <input class="settings-control"
                                           type="date"
                                           name="date_to"
                                           id="vacationDateTo"
                                           required>
                                    <i class="bi bi-calendar3"></i>
                                </div>

                                <div class="vacation-duration-box">
                                    <div class="vacation-duration-box__label">Продолжительность</div>
                                    <div class="vacation-duration-box__value" id="vacationDuration">—</div>
                                </div>
                            </div>
                        </section>

                        <section class="vacation-form-section">
                            <div class="vacation-form-section__head">
                                <i class="bi bi-check2-circle"></i>
                                Статус
                            </div>

                            <div class="vacation-status-grid">
                                <label class="vacation-status-option">
                                    <input type="radio" name="status" value="planned" checked>
                                    <span class="vacation-status-card">
                                        <span class="vacation-status-card__dot"></span>
                                        <span>
                                            <strong>Запланирован</strong>
                                            <span>Период внесён в график, но ещё не утверждён</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="vacation-status-option">
                                    <input type="radio" name="status" value="approved">
                                    <span class="vacation-status-card">
                                        <span class="vacation-status-card__dot"></span>
                                        <span>
                                            <strong>Утверждён</strong>
                                            <span>Отпуск подтверждён и считается окончательным</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <section class="vacation-form-section vacation-comment-wrap">
                            <div class="vacation-form-section__head">
                                <i class="bi bi-chat-left-text"></i>
                                Комментарий
                            </div>

                            <textarea class="settings-control"
                                      name="comment"
                                      id="vacationComment"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Например: отпуск по утверждённому графику"></textarea>
                        </section>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="vacation-footer-summary" id="vacationFooterSummary">
                        <i class="bi bi-info-circle"></i>
                        <span>Выберите сотрудника и <strong>период отпуска</strong>.</span>
                    </div>

                    <div class="vacation-footer-actions">
                        <button type="button"
                                class="settings-button settings-button--secondary"
                                data-bs-dismiss="modal">
                            Отмена
                        </button>
                        <button type="submit" class="settings-button settings-button--primary">
                            <i class="bi bi-check2"></i>
                            Сохранить отпуск
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('vacationEmployeeSearch');
            const categoryFilter = document.getElementById('vacationCategoryFilter');
            const rows = Array.from(document.querySelectorAll('.js-vacation-employee-row'));

            function applyVacationFilters() {
                const search = String(searchInput?.value || '').toLocaleLowerCase('ru-RU').trim();
                const category = categoryFilter?.value || 'all';

                rows.forEach(row => {
                    const matchesSearch = search === '' || String(row.dataset.search || '').includes(search);
                    const matchesCategory = category === 'all' || row.dataset.category === category;
                    row.classList.toggle('is-filtered', !(matchesSearch && matchesCategory));
                });
            }

            searchInput?.addEventListener('input', applyVacationFilters);
            categoryFilter?.addEventListener('change', applyVacationFilters);

            const modalTitle = document.getElementById('vacationModalLabel');
            const form = document.getElementById('vacationForm');
            const employee = document.getElementById('vacationEmployee');
            const employeeType = document.getElementById('vacationEmployeeType');
            const employeeSearchInput = document.getElementById('vacationEmployeeSearchInput');
            const employeeSearchResults = document.getElementById('vacationEmployeeSearchResults');
            const employeeSearchClear = document.getElementById('vacationEmployeeSearchClear');
            const employeeSearchEmpty = document.getElementById('vacationEmployeeSearchEmpty');
            const employeeField = document.getElementById('vacationEmployeeField');
            const employeeSearchOptions = employeeSearchResults
                ? Array.from(employeeSearchResults.querySelectorAll('.vacation-employee-search-option'))
                : [];
            let employeeSearchActiveIndex = -1;
            const replacementSection = document.getElementById('vacationReplacementSection');
            const replacementField = document.getElementById('vacationReplacementField');
            const replaceDoctor = document.getElementById('vacationReplaceDoctor');
            const replaceNurse = document.getElementById('vacationReplaceNurse');
            const replacementTitle = document.getElementById('vacationReplacementTitle');
            const replacementNote = document.getElementById('vacationReplacementNote');
            const replacementSearchInput = document.getElementById('vacationReplacementSearchInput');
            const replacementSearchResults = document.getElementById('vacationReplacementSearchResults');
            const replacementSearchClear = document.getElementById('vacationReplacementSearchClear');
            const replacementSearchEmpty = document.getElementById('vacationReplacementSearchEmpty');
            const replacementOptions = replacementSearchResults
                ? Array.from(replacementSearchResults.querySelectorAll('.vacation-replacement-option'))
                : [];
            let replacementSearchActiveIndex = -1;
            const dateFrom = document.getElementById('vacationDateFrom');
            const dateTo = document.getElementById('vacationDateTo');
            const comment = document.getElementById('vacationComment');
            const employeePreview = document.getElementById('vacationEmployeePreview');
            const employeeInitials = document.getElementById('vacationEmployeeInitials');
            const employeeName = document.getElementById('vacationEmployeeName');
            const employeeRole = document.getElementById('vacationEmployeeRole');
            const duration = document.getElementById('vacationDuration');
            const footerSummary = document.getElementById('vacationFooterSummary');
            const addButton = document.getElementById('addVacationBtn');
            const editingId = document.getElementById('vacationEditingId');
            const storeRoute = @json($storeRoute);

            function setRadioValue(name, value) {
                const input = form?.querySelector(`input[name="${name}"][value="${value}"]`);
                if (input) {
                    input.checked = true;
                }
            }

            function selectedEmployeeOption() {
                if (!employee?.value || !employeeType?.value) {
                    return null;
                }

                return employeeSearchOptions.find(option =>
                    String(option.dataset.employeeId || '') === String(employee.value)
                    && String(option.dataset.employeeType || '') === String(employeeType.value)
                ) || null;
            }

            function syncEmployeeType() {
                if (!employeeType) {
                    return;
                }

                const option = selectedEmployeeOption();

                if (!option && !employee?.value) {
                    employeeType.value = '';
                }
            }

            function visibleEmployeeOptions() {
                return employeeSearchOptions.filter(
                    option => !option.classList.contains('d-none')
                );
            }

            function syncEmployeeSearchState() {
                syncEmployeeType();

                const option = selectedEmployeeOption();
                const hasEmployee = Boolean(option);

                employeePreview?.classList.toggle('is-visible', hasEmployee);
                employeeSearchClear?.classList.toggle(
                    'd-none',
                    !String(employeeSearchInput?.value || '').trim()
                );

                employeeSearchOptions.forEach(item => {
                    item.classList.toggle(
                        'is-selected',
                        hasEmployee && item === option
                    );
                });

                if (!hasEmployee) {
                    if (employeeInitials) employeeInitials.textContent = '—';
                    if (employeeName) employeeName.textContent = '—';
                    if (employeeRole) employeeRole.textContent = '—';
                    syncReplacementVisibility();
                    syncFooterSummary();
                    return;
                }

                const name = option.dataset.name || '';
                const role = option.dataset.role || '';
                const words = name.split(/\s+/).filter(Boolean);
                const initials = words.slice(0, 2)
                    .map(word => word.charAt(0).toUpperCase())
                    .join('');

                if (employeeSearchInput && document.activeElement !== employeeSearchInput) {
                    employeeSearchInput.value = role
                        ? `${name} — ${role}`
                        : name;
                }

                if (employeeInitials) employeeInitials.textContent = initials || 'С';
                if (employeeName) employeeName.textContent = name;
                if (employeeRole) employeeRole.textContent = role;
                syncReplacementVisibility();
                syncFooterSummary();
            }

            function filterEmployeeSearchOptions() {
                const query = String(employeeSearchInput?.value || '')
                    .toLocaleLowerCase('ru-RU')
                    .replace(/\s+/g, ' ')
                    .trim();
                let visibleCount = 0;

                employeeSearchOptions.forEach(option => {
                    const search = String(option.dataset.search || '')
                        .toLocaleLowerCase('ru-RU');
                    const visible = query === '' || search.includes(query);
                    option.classList.toggle('d-none', !visible);

                    if (visible) {
                        visibleCount++;
                    }
                });

                employeeSearchEmpty?.classList.toggle('d-none', visibleCount > 0);
                employeeSearchActiveIndex = -1;
            }

            function openEmployeeSearchResults() {
                if (!employeeSearchResults) {
                    return;
                }

                employeeSearchResults.classList.remove('d-none');
                filterEmployeeSearchOptions();
            }

            function closeEmployeeSearchResults() {
                employeeSearchResults?.classList.add('d-none');
                employeeSearchActiveIndex = -1;
            }

            function setEmployeeSearchActive(index) {
                const options = visibleEmployeeOptions();

                if (!options.length) {
                    employeeSearchActiveIndex = -1;
                    return;
                }

                employeeSearchActiveIndex = Math.max(
                    0,
                    Math.min(index, options.length - 1)
                );

                options.forEach((option, optionIndex) => {
                    option.classList.toggle(
                        'is-active',
                        optionIndex === employeeSearchActiveIndex
                    );
                });

                options[employeeSearchActiveIndex]?.scrollIntoView({
                    block: 'nearest'
                });
            }

            function chooseEmployeeOption(option) {
                if (!option || !employee) {
                    return;
                }

                employee.value = option.dataset.employeeId || '';

                if (employeeType) {
                    employeeType.value = option.dataset.employeeType || '';
                }

                clearReplacementEmployee();

                if (employeeSearchInput) {
                    const name = option.dataset.name || '';
                    const role = option.dataset.role || '';
                    employeeSearchInput.value = role
                        ? `${name} — ${role}`
                        : name;
                    employeeSearchInput.setCustomValidity('');
                }

                syncEmployeeSearchState();
                closeEmployeeSearchResults();
            }

            function replacementEmployeeId() {
                if (employeeType?.value === 'doctor') {
                    return String(replaceDoctor?.value || '');
                }

                if (employeeType?.value === 'nurse') {
                    return String(replaceNurse?.value || '');
                }

                return '';
            }

            function setReplacementEmployeeId(type, id) {
                if (replaceDoctor) replaceDoctor.value = '';
                if (replaceNurse) replaceNurse.value = '';

                if (type === 'doctor' && replaceDoctor) {
                    replaceDoctor.value = id || '';
                } else if (type === 'nurse' && replaceNurse) {
                    replaceNurse.value = id || '';
                }
            }

            function selectedReplacementOption() {
                const type = String(employeeType?.value || '');
                const id = replacementEmployeeId();

                if (!type || !id) {
                    return null;
                }

                return replacementOptions.find(option =>
                    String(option.dataset.employeeType || '') === type
                    && String(option.dataset.employeeId || '') === id
                ) || null;
            }

            function visibleReplacementOptions() {
                return replacementOptions.filter(
                    option => !option.classList.contains('d-none')
                );
            }

            function replacementRequiredMessage() {
                return employeeType?.value === 'nurse'
                    ? 'Выберите медсестру, которая будет исполнять обязанности.'
                    : 'Выберите врача, который будет исполнять обязанности.';
            }

            function syncReplacementLabels() {
                const isNurse = employeeType?.value === 'nurse';
                const approved = form?.querySelector(
                    'input[name="status"]:checked'
                )?.value === 'approved';

                if (replacementTitle) {
                    replacementTitle.textContent = isNurse
                        ? 'Исполняющая обязанности медсестры'
                        : 'Исполняющий обязанности врача';
                }

                if (replacementSearchInput) {
                    replacementSearchInput.placeholder = isNurse
                        ? 'Выберите медсестру, которая будет И.О.'
                        : 'Выберите врача, который будет И.О.';
                }

                if (replacementSearchEmpty) {
                    replacementSearchEmpty.textContent = isNurse
                        ? 'Подходящие медсестры не найдены.'
                        : 'Подходящие врачи не найдены.';
                }

                if (replacementNote) {
                    replacementNote.innerHTML = approved
                        ? '<i class="bi bi-info-circle"></i> Для утверждённого отпуска И.О. обязателен.'
                        : '<i class="bi bi-info-circle"></i> Для запланированного отпуска И.О. можно назначить заранее.';
                }
            }

            function filterReplacementOptions() {
                const query = String(replacementSearchInput?.value || '')
                    .toLocaleLowerCase('ru-RU')
                    .replace(/\s+/g, ' ')
                    .trim();
                const selectedEmployeeId = String(employee?.value || '');
                const selectedEmployeeType = String(employeeType?.value || '');
                let visibleCount = 0;

                replacementOptions.forEach(option => {
                    const optionType = String(option.dataset.employeeType || '');
                    const optionId = String(option.dataset.employeeId || '');
                    const isCorrectType = optionType === selectedEmployeeType;
                    const isSelf = isCorrectType && optionId === selectedEmployeeId;
                    const search = String(option.dataset.search || '')
                        .toLocaleLowerCase('ru-RU');
                    const visible = isCorrectType
                        && !isSelf
                        && (query === '' || search.includes(query));

                    option.classList.toggle('d-none', !visible);
                    if (visible) visibleCount++;
                });

                replacementSearchEmpty?.classList.toggle('d-none', visibleCount > 0);
                replacementSearchActiveIndex = -1;
            }

            function openReplacementResults() {
                if (
                    !replacementSearchResults
                    || !['doctor', 'nurse'].includes(
                        String(employeeType?.value || '')
                    )
                ) {
                    return;
                }

                replacementSearchResults.classList.remove('d-none');
                filterReplacementOptions();
            }

            function closeReplacementResults() {
                replacementSearchResults?.classList.add('d-none');
                replacementSearchActiveIndex = -1;
            }

            function setReplacementSearchActive(index) {
                const options = visibleReplacementOptions();

                if (!options.length) {
                    replacementSearchActiveIndex = -1;
                    return;
                }

                replacementSearchActiveIndex = Math.max(
                    0,
                    Math.min(index, options.length - 1)
                );

                options.forEach((option, optionIndex) => {
                    option.classList.toggle(
                        'is-active',
                        optionIndex === replacementSearchActiveIndex
                    );
                });

                options[replacementSearchActiveIndex]?.scrollIntoView({
                    block: 'nearest'
                });
            }

            function syncReplacementSearchState() {
                const option = selectedReplacementOption();
                const hasReplacement = Boolean(option);

                replacementSearchClear?.classList.toggle(
                    'd-none',
                    !String(replacementSearchInput?.value || '').trim()
                );

                replacementOptions.forEach(item => {
                    item.classList.toggle(
                        'is-selected',
                        hasReplacement && item === option
                    );
                });

                if (
                    hasReplacement
                    && replacementSearchInput
                    && document.activeElement !== replacementSearchInput
                ) {
                    const name = option.dataset.name || '';
                    const role = option.dataset.role || '';
                    replacementSearchInput.value = role
                        ? `${name} — ${role}`
                        : name;
                }
            }

            function clearReplacementEmployee() {
                setReplacementEmployeeId('', '');

                if (replacementSearchInput) {
                    replacementSearchInput.value = '';
                    replacementSearchInput.setCustomValidity('');
                }

                closeReplacementResults();
                syncReplacementSearchState();
            }

            function syncReplacementVisibility() {
                const type = String(employeeType?.value || '');
                const show = Boolean(
                    employee?.value
                    && ['doctor', 'nurse'].includes(type)
                );

                replacementSection?.classList.toggle('d-none', !show);
                syncReplacementLabels();

                if (!show) {
                    clearReplacementEmployee();
                    return;
                }

                const selected = selectedReplacementOption();

                if (
                    selected
                    && String(selected.dataset.employeeType || '') === type
                    && String(selected.dataset.employeeId || '')
                        === String(employee.value)
                ) {
                    clearReplacementEmployee();
                }

                filterReplacementOptions();
                syncReplacementSearchState();
            }

            function chooseReplacementOption(option) {
                if (!option) {
                    return;
                }

                const type = String(option.dataset.employeeType || '');

                if (type !== String(employeeType?.value || '')) {
                    return;
                }

                setReplacementEmployeeId(
                    type,
                    option.dataset.employeeId || ''
                );

                if (replacementSearchInput) {
                    const name = option.dataset.name || '';
                    const role = option.dataset.role || '';
                    replacementSearchInput.value = role
                        ? `${name} — ${role}`
                        : name;
                    replacementSearchInput.setCustomValidity('');
                }

                syncReplacementSearchState();
                closeReplacementResults();
            }

            function parseDateInput(value) {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null;
                const [year, month, day] = value.split('-').map(Number);
                const date = new Date(year, month - 1, day);
                return Number.isNaN(date.getTime()) ? null : date;
            }

            function formatDateShort(value) {
                const date = parseDateInput(value);
                if (!date) return '';
                return new Intl.DateTimeFormat('ru-RU', {day: '2-digit', month: '2-digit', year: 'numeric'}).format(date);
            }

            function syncDuration() {
                const from = parseDateInput(dateFrom?.value || '');
                const to = parseDateInput(dateTo?.value || '');
                let days = null;

                if (from && to && to >= from) {
                    days = Math.floor((to - from) / 86400000) + 1;
                }

                if (duration) {
                    duration.textContent = days
                        ? `${days} ${days % 10 === 1 && days % 100 !== 11 ? 'день' : ([2,3,4].includes(days % 10) && ![12,13,14].includes(days % 100) ? 'дня' : 'дней')}`
                        : '—';
                }

                syncFooterSummary();
            }

            function syncFooterSummary() {
                if (!footerSummary) return;

                const option = selectedEmployeeOption();
                const name = option
                    ? (option.dataset.name || '')
                    : '';
                const from = formatDateShort(dateFrom?.value || '');
                const to = formatDateShort(dateTo?.value || '');

                if (name && from && to) {
                    footerSummary.innerHTML = `<i class="bi bi-calendar-check"></i><span><strong>${name}</strong>: ${from} — ${to}</span>`;
                } else {
                    footerSummary.innerHTML = '<i class="bi bi-info-circle"></i><span>Выберите сотрудника и <strong>период отпуска</strong>.</span>';
                }
            }

            function setVacationFormMode(vacationId = '', updateUrl = '') {
                if (!form) {
                    return;
                }

                const id = String(vacationId || '').trim();
                const url = String(updateUrl || '').trim();

                if (editingId) {
                    editingId.value = id;
                }

                form.dataset.mode = id ? 'edit' : 'create';
                form.dataset.updateUrl = id ? url : '';
                form.action = id && url ? url : storeRoute;
            }

            function resetVacationForm() {
                if (!form) return;
                setVacationFormMode();
                modalTitle.textContent = 'Добавить отпуск';
                employee.value = '';
                if (employeeType) {
                    employeeType.value = '';
                }
                if (employeeSearchInput) {
                    employeeSearchInput.value = '';
                    employeeSearchInput.setCustomValidity('');
                }
                closeEmployeeSearchResults();
                clearReplacementEmployee();
                setRadioValue('vacation_type', 'annual');
                setRadioValue('status', 'planned');
                syncReplacementLabels();
                dateFrom.value = '';
                dateTo.value = '';
                comment.value = '';
                syncEmployeeSearchState();
                syncDuration();
            }

            addButton?.addEventListener('click', resetVacationForm);

            document.querySelectorAll('.js-edit-vacation').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const updateUrl = this.dataset.updateUrl || '';
                    if (!id || !updateUrl || !form) return;

                    setVacationFormMode(id, updateUrl);
                    modalTitle.textContent = 'Изменить отпуск';

                    const employeeKey = String(this.dataset.employeeKey || '');
                    const [editingEmployeeType, editingEmployeeId] = employeeKey.split(':', 2);

                    employee.value = editingEmployeeId || '';
                    if (employeeType) {
                        employeeType.value = ['doctor', 'nurse'].includes(editingEmployeeType)
                            ? editingEmployeeType
                            : '';
                    }

                    const selectedEmployee = selectedEmployeeOption();
                    if (employeeSearchInput) {
                        const name = selectedEmployee?.dataset.name || '';
                        const role = selectedEmployee?.dataset.role || '';
                        employeeSearchInput.value = selectedEmployee
                            ? (role ? `${name} — ${role}` : name)
                            : '';
                        employeeSearchInput.setCustomValidity('');
                    }

                    setReplacementEmployeeId('', '');

                    if (editingEmployeeType === 'doctor') {
                        setReplacementEmployeeId(
                            'doctor',
                            this.dataset.replaceDoctorId || ''
                        );
                    } else if (editingEmployeeType === 'nurse') {
                        setReplacementEmployeeId(
                            'nurse',
                            this.dataset.replaceNurseId || ''
                        );
                    }

                    syncReplacementVisibility();
                    syncReplacementSearchState();

                    setRadioValue('vacation_type', this.dataset.vacationType || 'annual');
                    dateFrom.value = this.dataset.dateFrom || '';
                    dateTo.value = this.dataset.dateTo || '';
                    setRadioValue('status', this.dataset.status || 'planned');
                    syncReplacementLabels();
                    comment.value = this.dataset.comment || '';
                    syncEmployeeSearchState();
                    syncDuration();
                });
            });


            employeeSearchInput?.addEventListener('focus', function () {
                const selected = selectedEmployeeOption();

                if (selected && employeeSearchInput.value) {
                    employeeSearchInput.select();
                }

                openEmployeeSearchResults();
            });

            employeeSearchInput?.addEventListener('input', function () {
                if (employee) {
                    employee.value = '';
                }

                if (employeeType) {
                    employeeType.value = '';
                }

                employeeSearchInput.setCustomValidity('');
                clearReplacementEmployee();
                syncEmployeeSearchState();
                openEmployeeSearchResults();
            });

            employeeSearchInput?.addEventListener('keydown', function (event) {
                const options = visibleEmployeeOptions();

                if (event.key === 'ArrowDown' && options.length) {
                    event.preventDefault();
                    setEmployeeSearchActive(employeeSearchActiveIndex + 1);
                } else if (event.key === 'ArrowUp' && options.length) {
                    event.preventDefault();
                    setEmployeeSearchActive(
                        employeeSearchActiveIndex <= 0
                            ? options.length - 1
                            : employeeSearchActiveIndex - 1
                    );
                } else if (event.key === 'Enter' && employeeSearchActiveIndex >= 0) {
                    event.preventDefault();
                    chooseEmployeeOption(options[employeeSearchActiveIndex]);
                } else if (event.key === 'Escape') {
                    closeEmployeeSearchResults();
                }
            });

            employeeSearchOptions.forEach(option => {
                option.addEventListener('mousedown', event => event.preventDefault());
                option.addEventListener('click', () => chooseEmployeeOption(option));
            });

            employeeSearchClear?.addEventListener('click', function () {
                if (employee) {
                    employee.value = '';
                }

                if (employeeType) {
                    employeeType.value = '';
                }

                if (employeeSearchInput) {
                    employeeSearchInput.value = '';
                    employeeSearchInput.setCustomValidity('');
                    employeeSearchInput.focus();
                }

                clearReplacementEmployee();
                syncEmployeeSearchState();
                openEmployeeSearchResults();
            });

            replacementSearchInput?.addEventListener('focus', function () {
                const selected = selectedReplacementOption();
                if (selected && replacementSearchInput.value) {
                    replacementSearchInput.select();
                }
                openReplacementResults();
            });

            replacementSearchInput?.addEventListener('input', function () {
                setReplacementEmployeeId('', '');
                replacementSearchInput.setCustomValidity('');
                syncReplacementSearchState();
                openReplacementResults();
            });

            replacementSearchInput?.addEventListener('keydown', function (event) {
                const options = visibleReplacementOptions();

                if (event.key === 'ArrowDown' && options.length) {
                    event.preventDefault();
                    setReplacementSearchActive(replacementSearchActiveIndex + 1);
                } else if (event.key === 'ArrowUp' && options.length) {
                    event.preventDefault();
                    setReplacementSearchActive(
                        replacementSearchActiveIndex <= 0
                            ? options.length - 1
                            : replacementSearchActiveIndex - 1
                    );
                } else if (event.key === 'Enter' && replacementSearchActiveIndex >= 0) {
                    event.preventDefault();
                    chooseReplacementOption(options[replacementSearchActiveIndex]);
                } else if (event.key === 'Escape') {
                    closeReplacementResults();
                }
            });

            replacementOptions.forEach(option => {
                option.addEventListener('mousedown', event => event.preventDefault());
                option.addEventListener('click', () => chooseReplacementOption(option));
            });

            replacementSearchClear?.addEventListener('click', function () {
                clearReplacementEmployee();
                replacementSearchInput?.focus();
                openReplacementResults();
            });

            document.addEventListener('click', function (event) {
                if (employeeField && !employeeField.contains(event.target)) {
                    closeEmployeeSearchResults();
                    const selected = selectedEmployeeOption();

                    if (selected && employeeSearchInput) {
                        const name = selected.dataset.name || '';
                        const role = selected.dataset.role || '';
                        employeeSearchInput.value = role
                            ? `${name} — ${role}`
                            : name;
                    }
                }

                if (replacementField && !replacementField.contains(event.target)) {
                    closeReplacementResults();
                    syncReplacementSearchState();
                }
            });

            form?.addEventListener('submit', function (event) {
                syncEmployeeType();

                const currentEditingId = String(editingId?.value || '').trim();
                const currentUpdateUrl = String(form.dataset.updateUrl || '').trim();
                form.action = currentEditingId && currentUpdateUrl
                    ? currentUpdateUrl
                    : storeRoute;

                if (!employee?.value || !employeeType?.value) {
                    event.preventDefault();
                    employeeSearchInput?.setCustomValidity(
                        'Выберите сотрудника из списка.'
                    );
                    employeeSearchInput?.reportValidity();
                    employeeSearchInput?.focus();
                    openEmployeeSearchResults();
                    return;
                }

                employeeSearchInput?.setCustomValidity('');

                const currentStatus = form.querySelector(
                    'input[name="status"]:checked'
                )?.value || 'planned';

                if (
                    currentStatus === 'approved'
                    && !replacementEmployeeId()
                ) {
                    event.preventDefault();
                    replacementSearchInput?.setCustomValidity(
                        replacementRequiredMessage()
                    );
                    replacementSearchInput?.reportValidity();
                    replacementSearchInput?.focus();
                    openReplacementResults();
                    return;
                }

                replacementSearchInput?.setCustomValidity('');
            });

            form?.querySelectorAll('input[name="status"]').forEach(input => {
                input.addEventListener('change', function () {
                    replacementSearchInput?.setCustomValidity('');
                    syncReplacementLabels();
                });
            });

            dateFrom?.addEventListener('change', function () {
                if (dateTo && dateFrom.value) {
                    dateTo.min = dateFrom.value;
                    if (dateTo.value && dateTo.value < dateFrom.value) {
                        dateTo.value = dateFrom.value;
                    }
                }
                syncDuration();
            });
            dateTo?.addEventListener('change', syncDuration);

            syncEmployeeSearchState();
            syncDuration();
        });
    </script>
@endsection
