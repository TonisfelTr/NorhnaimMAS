@extends('doctors.settings')

@section('settings-title', 'Кабинеты')

@section('assets')
    @parent

    <style>
        .office-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .office-toolbar__search {
            position: relative;
            width: min(520px, 100%);
        }

        .office-toolbar__search i {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .office-toolbar__search .settings-control {
            padding-left: 39px;
        }

        .office-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .office-summary-card {
            position: relative;
            overflow: hidden;
            min-height: 105px;
            padding: 18px 19px;
            border: 1px solid var(--settings-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .045);
        }

        .office-summary-card::before {
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, var(--settings-primary), var(--settings-primary-light));
            content: '';
        }

        .office-summary-card__label {
            color: var(--settings-muted);
            font-size: 12px;
            font-weight: 760;
        }

        .office-summary-card__value {
            margin-top: 7px;
            color: var(--settings-text);
            font-size: 29px;
            font-weight: 850;
            line-height: 1;
        }

        .office-summary-card__hint {
            margin-top: 6px;
            color: #8b95a1;
            font-size: 11px;
        }

        .office-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
            gap: 18px;
        }

        .office-card {
            position: relative;
            display: flex;
            overflow: hidden;
            min-height: 100%;
            flex-direction: column;
            border: 1px solid var(--settings-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 14px 38px rgba(15, 23, 42, .05);
        }

        .office-card--procedure {
            border-color: #dcd9f7;
            box-shadow: 0 16px 42px rgba(76, 70, 160, .08);
        }

        .office-card--procedure::before {
            position: absolute;
            z-index: 3;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #7167d9, #a29af0);
            content: '';
        }

        .office-card--procedure .office-card__header {
            border-bottom-color: #e8e6f8;
            background:
                radial-gradient(circle at 90% 10%, rgba(113, 103, 217, .12), transparent 32%),
                linear-gradient(135deg, #f6f5ff 0%, #ffffff 78%);
        }

        .office-card--procedure .office-card__icon {
            background: linear-gradient(135deg, #7167d9, #9188e8);
            color: #fff;
            box-shadow: 0 8px 18px rgba(113, 103, 217, .22);
        }

        .office-card--procedure .office-card__name {
            color: #37316f;
        }

        .office-card--procedure .office-card__body {
            background: linear-gradient(180deg, #fff 0%, #fdfcff 100%);
        }

        .office-card--functional {
            border-color: #cfe2f3;
            box-shadow: 0 16px 42px rgba(37, 99, 235, .07);
        }

        .office-card--functional::before {
            position: absolute;
            z-index: 3;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #60a5fa);
            content: '';
        }

        .office-card--functional .office-card__header {
            border-bottom-color: #deebf8;
            background:
                radial-gradient(circle at 90% 10%, rgba(37, 99, 235, .10), transparent 32%),
                linear-gradient(135deg, #f4f9ff 0%, #ffffff 78%);
        }

        .office-card--functional .office-card__icon {
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: #fff;
            box-shadow: 0 8px 18px rgba(37, 99, 235, .20);
        }

        .office-card--functional .office-card__name {
            color: #24446f;
        }

        .office-card--functional .office-card__body {
            background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
        }

        .office-kind {
            display: inline-flex;
            min-height: 25px;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eef8fa;
            color: var(--settings-primary-dark);
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .office-kind--procedure {
            background: #eeecff;
            color: #5d55b7;
        }

        .office-kind--functional {
            background: #eaf3ff;
            color: #2563eb;
        }

        .office-functional-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .office-functional-method {
            display: inline-flex;
            min-height: 29px;
            align-items: center;
            gap: 6px;
            padding: 6px 9px;
            border: 1px solid #d7e6fb;
            border-radius: 999px;
            background: #f4f8ff;
            color: #285a9f;
            font-size: 10px;
            font-weight: 780;
            line-height: 1.2;
        }

        .office-functional-method i {
            color: #3b82f6;
            font-size: 11px;
        }

        .office-functional-method-single {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px 13px;
            border: 1px solid #d7e6fb;
            border-radius: 14px;
            background: linear-gradient(135deg, #f4f8ff 0%, #fff 100%);
        }

        .office-functional-method-single__icon {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border-radius: 11px;
            background: #e8f2ff;
            color: #2563eb;
            font-size: 17px;
        }

        .office-functional-method-single__label {
            color: #24446f;
            font-size: 12px;
            font-weight: 850;
        }

        .office-functional-method-single__description {
            margin-top: 2px;
            color: #7b8da5;
            font-size: 9px;
            line-height: 1.35;
        }

        .office-functional-methods-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .office-functional-method-option {
            display: flex;
            min-height: 44px;
            align-items: center;
            gap: 9px;
            padding: 9px 11px;
            border: 1px solid #dfe7f2;
            border-radius: 11px;
            background: #fff;
            color: #334155;
            font-size: 10px;
            font-weight: 720;
            cursor: pointer;
            transition: .15s ease;
        }

        .office-functional-method-option:hover {
            border-color: #b9d1f3;
            background: #f8fbff;
        }

        .office-functional-method-option:has(input:checked) {
            border-color: #8cb8ef;
            background: #eef6ff;
            color: #24558f;
        }

        @media (max-width: 700px) {
            .office-functional-methods-form {
                grid-template-columns: 1fr;
            }
        }

        .office-procedure-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 14px;
            border: 1px solid #dfdcf8;
            border-radius: 15px;
            background: linear-gradient(135deg, #f6f5ff, #fff);
        }

        .office-procedure-summary__main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 11px;
        }

        .office-procedure-summary__icon {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border-radius: 12px;
            background: #e9e6ff;
            color: #6259bf;
            font-size: 18px;
        }

        .office-procedure-summary__role {
            color: #8e89b5;
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .055em;
            text-transform: uppercase;
        }

        .office-procedure-summary__name {
            margin-top: 3px;
            overflow: hidden;
            color: #2f2a60;
            font-size: 13px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-procedure-summary__meta {
            margin-top: 2px;
            color: #77719d;
            font-size: 10px;
        }

        .office-procedure-summary__badge {
            display: inline-flex;
            min-height: 27px;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #e9e6ff;
            color: #5d55b7;
            font-size: 9px;
            font-weight: 850;
            white-space: nowrap;
        }

        .office-procedure-doctor-note {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 9px;
            color: #8b87a8;
            font-size: 10px;
        }

        .office-procedure-doctor-note i {
            color: #7167d9;
        }

        .office-procedure-schedule {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 7px;
            margin-top: 13px;
        }

        .office-procedure-schedule__day {
            min-width: 0;
            padding: 9px 10px;
            border: 1px solid #e5e1fb;
            border-radius: 11px;
            background: #faf9ff;
        }

        .office-procedure-schedule__day.is-closed {
            border-style: dashed;
            background: #fcfcfe;
            opacity: .68;
        }

        .office-procedure-schedule__name {
            color: #817aa8;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .05em;
        }

        .office-procedure-schedule__time {
            display: block;
            margin-top: 3px;
            overflow: hidden;
            color: #403978;
            font-size: 10px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-procedure-schedule__day.is-closed .office-procedure-schedule__time {
            color: #9b97ac;
            font-weight: 700;
        }

        .office-procedure-schedule-form {
            display: grid;
            gap: 8px;
        }

        .office-procedure-schedule-row {
            display: grid;
            grid-template-columns: 42px 46px minmax(105px, 1fr) 18px minmax(105px, 1fr);
            gap: 9px;
            align-items: center;
            padding: 8px 10px;
            border: 1px solid #ece9f8;
            border-radius: 12px;
            background: #fcfbff;
        }

        .office-procedure-schedule-row.is-closed {
            opacity: .58;
        }

        .office-procedure-schedule-row.is-closed .settings-control {
            background: #f7f6fb;
            color: #8f8aa4;
        }

        .office-procedure-schedule-row.is-closed .settings-control:hover,
        .office-procedure-schedule-row.is-closed .settings-control:focus {
            background: #fff;
            color: #344054;
        }

        .office-procedure-schedule-row__day {
            color: #5b557f;
            font-size: 10px;
            font-weight: 900;
        }

        .office-procedure-schedule-row .settings-control {
            min-height: 38px;
            padding: 7px 9px;
            font-size: 11px;
        }

        .office-procedure-schedule-row__separator {
            color: #aaa5bd;
            text-align: center;
        }

        .office-procedure-copy {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 7px 11px;
            border: 1px solid #d9d5f4;
            border-radius: 10px;
            background: #f8f7ff;
            color: #5d55b7;
            font-size: 10px;
            font-weight: 850;
            line-height: 1;
            cursor: pointer;
            transition: .15s ease;
        }

        .office-procedure-copy:hover {
            border-color: #bdb6ee;
            background: #f0eeff;
            color: #493fa5;
        }

        .office-procedure-copy i {
            font-size: 13px;
        }

        .office-procedure-day-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .office-procedure-day-toggle .form-check-input {
            width: 32px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .office-card--procedure .office-card__footer {
            justify-content: flex-end;
        }

        .office-card--procedure .office-actions {
            margin-left: auto;
        }

        .office-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 19px 16px;
            border-bottom: 1px solid #edf1f5;
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .office-card__identity {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
        }

        .office-card__icon {
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

        .office-card__name {
            margin: 0;
            color: var(--settings-text);
            font-size: 18px;
            font-weight: 830;
        }

        .office-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 9px 12px;
            margin-top: 5px;
            color: var(--settings-muted);
            font-size: 12px;
        }

        .office-card__meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .office-status {
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

        .office-status--muted {
            background: #f1f5f9;
            color: #64748b;
        }

        .office-card__body {
            flex: 1 1 auto;
            padding: 18px 19px;
        }

        .office-doctor-replacement {
            margin: 14px 18px 0;
            padding: 13px 14px;
            border: 1px solid #f1c67c;
            border-radius: 15px;
            background: linear-gradient(135deg, #fff8ec 0%, #fffdf8 100%);
            color: #8a4c00;
        }

        .office-doctor-replacement__title {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #a55b00;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .office-doctor-replacement__name {
            margin-top: 8px;
            color: #7a3f00;
            font-size: 13px;
            font-weight: 850;
        }

        .office-doctor-replacement__meta {
            margin-top: 3px;
            color: #a96718;
            font-size: 10px;
            line-height: 1.45;
        }

        .office-doctor-replacement__main {
            margin-top: 4px;
            color: #b47a34;
            font-size: 9px;
            line-height: 1.4;
        }

        .office-doctor-away-today {
            margin: 14px 18px 0;
            padding: 13px 14px;
            border: 1px solid #f2c77f;
            border-radius: 15px;
            background: linear-gradient(135deg, #fff8ec 0%, #fffdf8 100%);
            color: #8a4c00;
        }

        .office-doctor-away-today__title {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #a55b00;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .office-doctor-away-today__item {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(207, 142, 47, .22);
        }

        .office-doctor-away-today__item:first-of-type {
            padding-top: 0;
            border-top: 0;
        }

        .office-doctor-away-today__cabinet {
            color: #7a3f00;
            font-size: 13px;
            font-weight: 850;
        }

        .office-doctor-away-today__time {
            margin-top: 3px;
            color: #a96718;
            font-size: 10px;
            font-weight: 750;
        }

        .office-doctor-away-today__meta {
            margin-top: 3px;
            color: #b47a34;
            font-size: 9px;
            line-height: 1.4;
        }

        /*
         * Для кабинета функциональной диагностики служебные плашки
         * используют ту же синюю гамму, что и сама карточка.
         */
        .office-card--functional .office-doctor-replacement,
        .office-card--functional .office-doctor-away-today {
            border-color: #bfd8fb;
            background:
                linear-gradient(
                    135deg,
                    #f3f8ff 0%,
                    #fbfdff 100%
                );
            color: #24446f;
        }

        .office-card--functional .office-doctor-replacement__title,
        .office-card--functional .office-doctor-away-today__title {
            color: #2563eb;
        }

        .office-card--functional .office-doctor-replacement__name,
        .office-card--functional .office-doctor-away-today__cabinet {
            color: #24446f;
        }

        .office-card--functional .office-doctor-replacement__meta,
        .office-card--functional .office-doctor-away-today__time {
            color: #4f6f98;
        }

        .office-card--functional .office-doctor-replacement__main,
        .office-card--functional .office-doctor-away-today__meta {
            color: #6d84a4;
        }

        .office-card--functional .office-doctor-away-today__item {
            border-top-color: rgba(37, 99, 235, .14);
        }

        .office-replacement-settings {
            padding: 14px;
            border: 1px solid #dbe7f0;
            border-radius: 14px;
            background: #f8fbfd;
        }

        .office-replacement-settings__head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 11px;
            color: #40566b;
            font-size: 11px;
            font-weight: 850;
        }

        .office-replacement-settings__hint {
            margin-top: 8px;
            color: #8291a1;
            font-size: 9px;
            line-height: 1.4;
        }

        .office-replacement-settings .row {
            margin-right: 0;
            margin-left: 0;
        }

        .office-replacement-settings .row > * {
            min-width: 0;
        }

        .office-section + .office-section {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #edf1f5;
        }

        .office-section__title {
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

        .office-people {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .office-people--single {
            grid-template-columns: 1fr;
        }

        .office-person {
            min-width: 0;
            padding: 12px 13px;
            border: 1px solid #e5ebf0;
            border-radius: 14px;
            background: #fbfcfd;
        }

        .office-person__role {
            margin-bottom: 5px;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .office-person__name {
            overflow: hidden;
            color: var(--settings-text);
            font-size: 13px;
            font-weight: 760;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-person__speciality {
            margin-top: 3px;
            overflow: hidden;
            color: var(--settings-muted);
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-person--empty {
            border-style: dashed;
            background: #fff;
        }

        .office-person--empty .office-person__name {
            color: #94a3b8;
            font-weight: 650;
        }

        .office-recipes {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
            gap: 10px;
        }

        .office-recipe {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 13px;
            border: 1px solid #d9ecef;
            border-radius: 14px;
            background: #f4fbfc;
        }

        .office-recipe__label {
            color: #46656c;
            font-size: 12px;
            font-weight: 720;
        }

        .office-recipe__label small {
            display: block;
            margin-top: 2px;
            color: #8aa0a5;
            font-size: 10px;
            font-weight: 600;
        }

        .office-recipe__value {
            color: var(--settings-primary-dark);
            font-size: 21px;
            font-weight: 860;
            white-space: nowrap;
        }

        .office-recipe__value span {
            margin-left: 2px;
            font-size: 10px;
            font-weight: 750;
        }

        .office-recipe--148 {
            align-items: stretch;
            flex-direction: column;
        }

        .office-recipe--148 .office-recipe__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .office-recipe__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #d9ecef;
        }

        .office-recipe__stat {
            padding: 8px 9px;
            border-radius: 10px;
            background: #fff;
        }

        .office-recipe__stat-label {
            color: #8aa0a5;
            font-size: 9px;
            font-weight: 750;
            text-transform: uppercase;
        }

        .office-recipe__stat-value {
            margin-top: 2px;
            color: #344054;
            font-size: 15px;
            font-weight: 850;
        }

        .office-recipe__stat-value--remaining {
            color: var(--settings-primary-dark);
        }

        .office-card-list {
            display: grid;
            gap: 8px;
        }

        .office-medcard {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 11px;
            border: 1px solid #e7edf2;
            border-radius: 13px;
            background: #fff;
        }

        .office-medcard__main {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
        }

        .office-medcard__icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 11px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 14px;
        }

        .office-medcard__name {
            overflow: hidden;
            color: var(--settings-text);
            font-size: 12px;
            font-weight: 760;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-medcard__number {
            margin-top: 2px;
            color: var(--settings-muted);
            font-size: 10px;
        }

        .office-medcard__time {
            color: #94a3b8;
            font-size: 10px;
            white-space: nowrap;
        }

        .office-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: auto;
            padding: 13px 19px;
            border-top: 1px solid #edf1f5;
            background: #fbfcfd;
        }

        .office-card__footer-hint {
            color: #94a3b8;
            font-size: 10px;
        }

        .office-actions {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-left: auto;
        }

        .office-action {
            display: inline-flex;
            min-height: 35px;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 11px;
            border: 1px solid #d8e0e5;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-size: 11px;
            font-weight: 760;
            text-decoration: none;
            cursor: pointer;
        }

        .office-action:hover {
            border-color: #c8d4dc;
            background: #f8fafc;
            color: #111827;
        }

        .office-action--primary {
            border-color: var(--settings-primary);
            background: var(--settings-primary);
            color: #fff;
        }

        .office-action--primary:hover {
            border-color: var(--settings-primary-dark);
            background: var(--settings-primary-dark);
            color: #fff;
        }

        .office-empty-cards {
            padding: 14px 13px;
            border: 1px dashed #cbd5e1;
            border-radius: 13px;
            background: #f8fafc;
            color: #94a3b8;
            font-size: 11px;
            text-align: center;
        }

        .office-demo-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 18px;
            padding: 11px 13px;
            border: 1px solid #f3dfb3;
            border-radius: 14px;
            background: #fffaf0;
            color: #7c5a12;
            font-size: 11px;
            line-height: 1.5;
        }

        .office-modal .modal-dialog {
            width: calc(100vw - 40px);
            max-width: 1380px;
            margin-right: auto;
            margin-left: auto;
        }

        .office-modal .modal-content {
            overflow: hidden;
            max-height: calc(100vh - 36px);
            border: 0;
            border-radius: 20px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, .22);
        }

        .office-modal .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--settings-border);
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .office-modal .modal-title {
            color: var(--settings-text);
            font-size: 18px;
            font-weight: 820;
        }

        .office-modal .modal-body {
            overflow-y: auto;
            padding: 20px;
        }

        .office-modal .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--settings-border);
            background: #fbfcfd;
        }

        @media (min-width: 1101px) {
            .office-modal .settings-form-grid {
                display: grid;
                grid-template-columns: repeat(12, minmax(0, 1fr));
                gap: 14px 16px;
                align-items: start;
            }

            .office-modal .settings-form-grid > .settings-col-12,
            .office-modal .settings-form-grid > .settings-col-6 {
                width: auto;
                max-width: none;
            }

            /*
             * Левая часть модалки занимает 7/12.
             * Привязка идет к конкретным полям, а не к nth-child:
             * добавление новых блоков больше не ломает сетку.
             */
            .office-modal .settings-form-grid > :has(#officeName),
            .office-modal .settings-form-grid > :has(.office-cabinet-types),
            #replacementStaffField,
            #functionalMethodsField {
                grid-column: 1 / 8;
            }

            .office-modal .settings-form-grid > :has(#officeNumber),
            .office-modal .settings-form-grid > :has(#officePhone),
            #officeDoctorField,
            .office-modal .settings-form-grid > :has(#officeRecipe148) {
                grid-column: 1 / 4;
            }

            .office-modal .settings-form-grid > :has(#officeStage),
            .office-modal .settings-form-grid > :has(#officeStatus),
            #officeNurseField,
            .office-modal .settings-form-grid > :has(#officeRecipe107) {
                grid-column: 4 / 8;
            }

            #doctorScheduleField,
            #procedureScheduleField {
                grid-column: 8 / 13;
                grid-row: 1 / span 10;
                align-self: stretch;
                min-width: 0;
                padding: 16px;
                border: 1px solid #e7e4f6;
                border-radius: 16px;
                background: linear-gradient(180deg, #fcfbff 0%, #fff 100%);
            }

            #doctorScheduleField.d-none,
            #procedureScheduleField.d-none {
                display: none !important;
            }

            #doctorScheduleField .office-procedure-schedule-form,
            #procedureScheduleField .office-procedure-schedule-form {
                gap: 7px;
            }

            #doctorScheduleField .office-procedure-schedule-row,
            #procedureScheduleField .office-procedure-schedule-row {
                grid-template-columns:
                    32px 38px minmax(90px, 1fr)
                    14px minmax(90px, 1fr);
                gap: 7px;
                padding: 7px 8px;
            }

            #doctorScheduleField .office-procedure-copy,
            #procedureScheduleField .office-procedure-copy {
                flex: 0 0 auto;
            }

            .office-modal .settings-divider,
            .office-modal .settings-info-box {
                grid-column: 1 / 13;
            }
        }


        .office-cabinet-types {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .office-cabinet-type {
            display: flex;
            min-height: 76px;
            align-items: flex-start;
            gap: 11px;
            padding: 13px;
            border: 1px solid #dfe7ec;
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition: .15s ease;
        }

        .office-cabinet-type:hover {
            border-color: #bed9de;
            background: #fbfeff;
        }

        .office-cabinet-type:has(input:checked) {
            border-color: #91cfd6;
            background: linear-gradient(135deg, #eefafb, #fff);
            box-shadow: inset 0 0 0 1px rgba(4, 134, 150, .08);
        }

        .office-cabinet-type input {
            margin-top: 3px;
        }

        .office-cabinet-type__icon {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 10px;
            background: #e8f8fa;
            color: var(--settings-primary-dark);
            font-size: 16px;
        }

        .office-cabinet-type strong {
            display: block;
            color: #293544;
            font-size: 12px;
        }

        .office-cabinet-type span:last-child {
            display: block;
            margin-top: 3px;
            color: #7a8794;
            font-size: 10px;
            line-height: 1.4;
        }

        .office-doctor-picker {
            position: relative;
        }

        .office-doctor-picker__input {
            position: relative;
        }

        .office-doctor-picker__input > i {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .office-doctor-picker__input .settings-control {
            padding-left: 40px;
            padding-right: 40px;
        }

        .office-doctor-picker__clear {
            position: absolute;
            top: 50%;
            right: 10px;
            display: none;
            width: 28px;
            height: 28px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #94a3b8;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .office-doctor-picker__clear.is-visible {
            display: grid;
        }

        .office-doctor-picker__clear:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .office-doctor-picker__dropdown {
            position: absolute;
            z-index: 90;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            display: none;
            max-height: 270px;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            padding: 7px;
            border: 1px solid #d8e2e9;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .14);
        }

        .office-doctor-picker__dropdown::-webkit-scrollbar {
            width: 8px;
        }

        .office-doctor-picker__dropdown::-webkit-scrollbar-thumb {
            border: 2px solid transparent;
            border-radius: 999px;
            background: #cbd5e1;
            background-clip: padding-box;
        }

        .office-doctor-picker__dropdown.is-open {
            display: grid;
            gap: 6px;
        }

        .office-doctor-option {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            width: 100%;
            padding: 10px 11px;
            border: 1px solid transparent;
            border-radius: 11px;
            background: #fff;
            color: inherit;
            text-align: left;
            cursor: pointer;
            transition: .14s ease;
        }

        .office-doctor-option:hover {
            border-color: #d5e7ea;
            background: #f7fcfd;
        }

        .office-doctor-option.is-selected {
            border-color: #9fd6dc;
            background: #eefafb;
        }

        .office-doctor-option.is-unavailable,
        .office-nurse-option.is-unavailable {
            border-color: #e5e7eb;
            background: #f8fafc;
            opacity: .58;
            cursor: not-allowed;
        }

        .office-doctor-option.is-unavailable:hover,
        .office-nurse-option.is-unavailable:hover {
            border-color: #e5e7eb;
            background: #f8fafc;
        }


        .office-doctor-option__avatar {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--settings-primary), var(--settings-primary-light));
            color: #fff;
            font-size: 11px;
            font-weight: 900;
        }

        .office-doctor-option__name {
            overflow: hidden;
            color: #24303d;
            font-size: 12px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-doctor-option__meta {
            margin-top: 2px;
            overflow: hidden;
            color: #7a8794;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-doctor-option__badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f2f4f7;
            color: #667085;
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
        }

        .office-doctor-picker__empty {
            display: none;
            padding: 18px 12px;
            color: #8a96a5;
            font-size: 11px;
            text-align: center;
        }

        .office-doctor-picker__empty.is-visible {
            display: block;
        }

        .office-doctor-selected {
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            color: #32705a;
            font-size: 10px;
            font-weight: 750;
        }

        .office-doctor-selected.is-visible {
            display: flex;
        }

        .office-doctor-picker__error {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            color: #b42318;
            font-size: 10px;
            font-weight: 750;
        }

        .office-doctor-picker__error.is-visible {
            display: flex;
        }

        .office-doctor-picker.has-error .office-doctor-picker__input .settings-control {
            border-color: #fca5a5;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .07);
        }

        .office-nurse-picker {
            position: relative;
        }

        .office-nurse-picker__input {
            position: relative;
        }

        .office-nurse-picker__input > i {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .office-nurse-picker__input .settings-control {
            padding-left: 40px;
            padding-right: 40px;
        }

        .office-nurse-picker__clear {
            position: absolute;
            top: 50%;
            right: 10px;
            display: none;
            width: 28px;
            height: 28px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #94a3b8;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .office-nurse-picker__clear.is-visible {
            display: grid;
        }

        .office-nurse-picker__clear:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .office-nurse-picker__dropdown {
            position: absolute;
            z-index: 90;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            display: none;
            max-height: 310px;
            overflow-y: auto;
            padding: 7px;
            border: 1px solid #d8e2e9;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .14);
        }

        .office-nurse-picker__dropdown.is-open {
            display: grid;
            gap: 6px;
        }

        .office-nurse-option {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            width: 100%;
            padding: 10px 11px;
            border: 1px solid transparent;
            border-radius: 11px;
            background: #fff;
            color: inherit;
            text-align: left;
            cursor: pointer;
            transition: .14s ease;
        }

        .office-nurse-option:hover {
            border-color: #d5e7ea;
            background: #f7fcfd;
        }

        .office-nurse-option.is-selected {
            border-color: #9fd6dc;
            background: #eefafb;
        }

        .office-nurse-option__avatar {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--settings-primary), var(--settings-primary-light));
            color: #fff;
            font-size: 11px;
            font-weight: 900;
        }

        .office-nurse-option__content {
            min-width: 0;
        }

        .office-nurse-option__name {
            display: block;
            overflow: hidden;
            color: #24303d;
            font-size: 12px;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-nurse-option__meta {
            display: block;
            margin-top: 2px;
            overflow: hidden;
            color: #7a8794;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .office-nurse-option__badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: #f2f4f7;
            color: #667085;
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
        }

        .office-nurse-picker__empty {
            display: none;
            padding: 18px 12px;
            color: #8a96a5;
            font-size: 11px;
            text-align: center;
        }

        .office-nurse-picker__empty.is-visible {
            display: block;
        }

        .office-nurse-selected {
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            color: #32705a;
            font-size: 10px;
            font-weight: 750;
        }

        .office-nurse-selected.is-visible {
            display: flex;
        }

        .office-nurse-picker__error {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            color: #b42318;
            font-size: 10px;
            font-weight: 750;
        }

        .office-nurse-picker__error.is-visible {
            display: flex;
        }

        .office-nurse-picker.has-error .office-nurse-picker__input .settings-control {
            border-color: #fca5a5;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .07);
        }

        .office-flash {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 0 0 18px;
            padding: 14px 16px;
            border: 1px solid;
            border-radius: 15px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        }

        .office-flash__icon {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 11px;
            font-size: 17px;
        }

        .office-flash__content {
            min-width: 0;
            flex: 1;
        }

        .office-flash__title {
            font-size: 13px;
            font-weight: 850;
            line-height: 1.35;
        }

        .office-flash__text {
            margin-top: 3px;
            font-size: 11px;
            line-height: 1.5;
        }

        .office-flash__list {
            display: grid;
            gap: 4px;
            margin: 7px 0 0;
            padding: 0;
            list-style: none;
        }

        .office-flash__list li {
            position: relative;
            padding-left: 14px;
            font-size: 11px;
            line-height: 1.45;
        }

        .office-flash__list li::before {
            position: absolute;
            top: .55em;
            left: 2px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            content: '';
        }

        .office-flash--error {
            border-color: #fecaca;
            background: linear-gradient(135deg, #fff6f6 0%, #fffafa 100%);
            color: #8f1d1d;
        }

        .office-flash--error .office-flash__icon {
            background: #fee2e2;
            color: #b91c1c;
        }

        .office-flash--error .office-flash__list li::before {
            background: #dc2626;
        }

        .office-flash--success {
            border-color: #bbebd1;
            background: linear-gradient(135deg, #f2fbf7 0%, #fbfffd 100%);
            color: #216447;
        }

        .office-flash--success .office-flash__icon {
            background: #dcf6e8;
            color: #168151;
        }

        @media (max-width: 1100px) {
            .office-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .office-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .office-toolbar__search {
                width: 100%;
            }

            .office-summary {
                grid-template-columns: 1fr;
            }

            .office-cabinet-types {
                grid-template-columns: 1fr;
            }

            .office-people,
            .office-recipes {
                grid-template-columns: 1fr;
            }

            .office-procedure-schedule {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .office-procedure-schedule-row {
                grid-template-columns: 38px 38px 1fr 14px 1fr;
            }

            .office-card__footer {
                align-items: stretch;
                flex-direction: column;
            }

            .office-actions {
                width: 100%;
            }

            .office-action {
                flex: 1 1 auto;
            }
        }

        /* Force functional-diagnostics service banners into the KFD blue palette. */
        .office-card.office-card--functional > .office-doctor-replacement,
        .office-card.office-card--functional > .office-doctor-away-today {
            border-color: #bfd8fb !important;
            background: linear-gradient(135deg, #f3f8ff 0%, #fbfdff 100%) !important;
            color: #24446f !important;
        }

        .office-card.office-card--functional > .office-doctor-replacement .office-doctor-replacement__title,
        .office-card.office-card--functional > .office-doctor-away-today .office-doctor-away-today__title {
            color: #2563eb !important;
        }

        .office-card.office-card--functional > .office-doctor-replacement .office-doctor-replacement__name,
        .office-card.office-card--functional > .office-doctor-away-today .office-doctor-away-today__cabinet {
            color: #24446f !important;
        }

        .office-card.office-card--functional > .office-doctor-replacement .office-doctor-replacement__meta,
        .office-card.office-card--functional > .office-doctor-away-today .office-doctor-away-today__time {
            color: #4f6f98 !important;
        }

        .office-card.office-card--functional > .office-doctor-replacement .office-doctor-replacement__main,
        .office-card.office-card--functional > .office-doctor-away-today .office-doctor-away-today__meta {
            color: #6d84a4 !important;
        }

        .office-card.office-card--functional > .office-doctor-away-today .office-doctor-away-today__item {
            border-top-color: rgba(37, 99, 235, .16) !important;
        }

    </style>
@endsection

@section('sub-main')


    <main class="settings-page">
        <div class="settings-container">
            <div class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">Кабинеты</h1>
                    <p class="settings-page-description">
                        Настройка кабинетов клиники: телефон, прикреплённые врач и медсестра,
                        количество подготовленных рецептурных бланков, выдаваемых на кабинет,
                        и конкретные медицинские карты, которые физически находятся в кабинете сейчас.
                    </p>
                </div>

                <button type="button"
                        class="settings-button settings-button--primary"
                        data-bs-toggle="modal"
                        data-bs-target="#officeModal"
                        data-office-mode="create">
                    <i class="bi bi-plus-lg"></i>
                    Добавить кабинет
                </button>
            </div>

            @if($errors->any())
                <div class="office-flash office-flash--error" role="alert">
                    <div class="office-flash__icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div class="office-flash__content">
                        <div class="office-flash__title">
                            Не удалось сохранить кабинет
                        </div>
                        <div class="office-flash__text">
                            Проверьте введённые данные и повторите сохранение.
                        </div>

                        <ul class="office-flash__list">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="office-flash office-flash--success" role="status">
                    <div class="office-flash__icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div class="office-flash__content">
                        <div class="office-flash__title">
                            Кабинет сохранён
                        </div>
                        <div class="office-flash__text">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            <section class="office-summary">
                <article class="office-summary-card">
                    <div class="office-summary-card__label">Кабинетов</div>
                    <div class="office-summary-card__value">{{ $cabinets->total() }}</div>
                    <div class="office-summary-card__hint">В текущей клинике</div>
                </article>

                <article class="office-summary-card">
                    <div class="office-summary-card__label">Медкарт в кабинетах</div>
                    <div class="office-summary-card__value">{{ $previewCardsCount }}</div>
                    <div class="office-summary-card__hint">Физически находятся сейчас</div>
                </article>
            </section>

            <div class="office-toolbar">
                <div class="office-toolbar__search">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           id="officeSearch"
                           class="settings-control"
                           placeholder="Поиск по кабинету, этажу, сотруднику, методу диагностики или карте...">
                </div>

                <span class="settings-type-badge">
                    <i class="bi bi-building"></i>
                    {{ auth()->user()?->doctor?->clinic?->name ?? 'Клиника' }}
                </span>
            </div>

            <section class="office-grid" id="officeGrid">
                @foreach($cabinets as $office)
                    <article class="office-card office-card--{{ $office->cabinet_type }}"
                             data-office-card
                             data-office-number="{{ $office->cabinet }}"
                             data-office-stage="{{ $office->stage }}"
                             data-office-name="{{ mb_strtolower($office['name']) }}"
                             data-office-doctor="{{ mb_strtolower($office->mainDoctor?->full_name ?? '') }}"
                             data-office-nurse="{{ mb_strtolower($office->mainNurse?->full_name ?? '') }}"
                             data-office-method="{{ mb_strtolower($office->functional_method_label ?? '') }}"
                             data-office-cards="{{ $office->cabinet_type === 'doctor' ? mb_strtolower(collect($office['cards'])->pluck('patient')->implode(' ')) : '' }}">
                        <header class="office-card__header">
                            <div class="office-card__identity">
                                <div class="office-card__icon">
                                    <i class="bi {{ $office->cabinet_card_icon }}"></i>
                                </div>

                                <div>
                                    <h2 class="office-card__name">{{ $office->name }}</h2>
                                    <div class="office-card__meta">
                                        <span>
                                            <i class="bi bi-door-closed"></i>
                                            кабинет №{{ $office->cabinet }}
                                        </span>
                                        <span>
                                            <i class="bi bi-layers"></i>
                                            {{ $office->stage !== null && $office->stage !== ''
                                                ? $office->stage . ' этаж'
                                                : 'этаж не указан' }}
                                        </span>
                                        <span class="office-kind {{ $office->cabinet_type === 'procedure'
                                            ? 'office-kind--procedure'
                                            : ($office->cabinet_type === 'functional'
                                                ? 'office-kind--functional'
                                                : '') }}">
                                            <i class="bi {{ $office->cabinet_type_icon }}"></i>
                                            {{ $office->cabinet_type_label }}
                                        </span>
                                        <span>
                                            <i class="bi bi-telephone"></i>
                                            {{ $office['phone'] ? 'тел. ' . $office['phone'] : 'телефон не указан' }}
                                        </span>
                                        @if($office->cabinet_type === 'doctor')
                                            <span>
                                                <i class="bi bi-file-medical"></i>
                                                {{ $office->patients_count }} карт(ы)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <span class="office-status {{ $office->enabled ? '' : 'office-status--muted' }}">
                                <i class="bi {{ $office->enabled ? 'bi-check-circle' : 'bi-dash-circle' }}"></i>
                                {{ $office->enabled ? 'Используется' : 'Не используется' }}
                            </span>
                        </header>


                        @if(
                            $office->alternativeDoctor
                            && $office->cabinet_type !== 'procedure'
                        )
                            <div class="office-doctor-replacement">
                                <div class="office-doctor-replacement__title">
                                    <i class="bi bi-person-fill-check"></i>
                                    На данный момент врача заменяет
                                </div>

                                <div class="office-doctor-replacement__name">
                                    {{ $office->alternativeDoctor->full_name }}
                                </div>

                                @if(
                                    $office->alternativeDoctor->status
                                    || $office->alternativeDoctor->profession
                                )
                                    <div class="office-doctor-replacement__meta">
                                        {{ $office->alternativeDoctor->profession
                                            ?: $office->alternativeDoctor->status }}
                                    </div>
                                @endif

                                @if($office->mainDoctor)
                                    <div class="office-doctor-replacement__main">
                                        Основной врач:
                                        {{ $office->mainDoctor->full_name }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(
                            $office->cabinet_type === 'doctor'
                            && !empty($office->doctor_other_cabinets_today)
                        )
                            <div class="office-doctor-away-today">
                                <div class="office-doctor-away-today__title">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    Сегодня приём врача временно перенесён в другой кабинет
                                </div>

                                @foreach($office->doctor_other_cabinets_today as $location)
                                    <div class="office-doctor-away-today__item">
                                        <div class="office-doctor-away-today__cabinet">
                                            Кабинет №{{ $location['cabinet'] }}
                                            @if(!empty($location['name']))
                                                — {{ $location['name'] }}
                                            @endif
                                        </div>

                                        <div class="office-doctor-away-today__time">
                                            {{ $location['from'] }}
                                            —
                                            {{ $location['to'] }}
                                            @if(
                                                $location['stage'] !== null
                                                && $location['stage'] !== ''
                                            )
                                                · {{ $location['stage'] }} этаж
                                            @endif
                                        </div>

                                        <div class="office-doctor-away-today__meta">
                                            @if(!empty($location['replaced_doctor']))
                                                Замещает врача:
                                                {{ $location['replaced_doctor'] }}.
                                            @endif

                                            @if(!empty($location['has_own_schedule_overlap']))
                                                В собственном расписании есть пересечение
                                                {{ $location['overlap_from'] }}
                                                —
                                                {{ $location['overlap_to'] }};
                                                в этот период приём в своём кабинете перенесён.
                                            @else
                                                Сегодня в указанный период врач работает
                                                в кабинете замещения.
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="office-card__body">
                            <section class="office-section">
                                <div class="office-section__title">
                                    <span>{{ $office->cabinet_type === 'procedure'
                                        ? 'Персонал процедурного кабинета'
                                        : 'Сотрудники кабинета' }}</span>
                                    <i class="bi {{ $office->cabinet_type === 'procedure'
                                        ? 'bi-person-heart'
                                        : 'bi-people' }}"></i>
                                </div>

                                @if($office->cabinet_type !== 'procedure')
                                    <div class="office-people">
                                        <div class="office-person {{ $office->mainDoctor ? '' : 'office-person--empty' }}">
                                            <div class="office-person__role">Врач</div>
                                            <div class="office-person__name">
                                                {{ $office->mainDoctor?->full_name ?? 'Врач не найден' }}
                                            </div>
                                            @if($office->mainDoctor)
                                                <div class="office-person__speciality">
                                                    {{ $office->mainDoctor->profession ?: 'Специальность не указана' }}
                                                </div>
                                            @endif
                                        </div>

                                        @if(
                                            in_array(
                                                $office->cabinet_type,
                                                ['doctor', 'functional'],
                                                true
                                            )
                                        )
                                            <div class="office-person {{ $office->mainNurse ? '' : 'office-person--empty' }}">
                                                <div class="office-person__role">
                                                    Медсестра
                                                </div>
                                                <div class="office-person__name">
                                                    {{ $office->mainNurse?->full_name ?? 'Не назначена' }}
                                                </div>
                                                @if($office->mainNurse)
                                                    <div class="office-person__speciality">
                                                        {{ $office->mainNurse->profession
                                                            ?: 'Медицинская сестра' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="office-procedure-summary">
                                        <div class="office-procedure-summary__main">
                                            <div class="office-procedure-summary__icon">
                                                <i class="bi bi-person-heart"></i>
                                            </div>

                                            <div>
                                                <div class="office-procedure-summary__role">
                                                    Ответственная медсестра
                                                </div>
                                                <div class="office-procedure-summary__name">
                                                    {{ $office->mainNurse?->full_name ?? 'Медсестра не назначена' }}
                                                </div>
                                                <div class="office-procedure-summary__meta">
                                                    {{ $office->mainNurse?->profession ?: 'Процедурный кабинет' }}
                                                </div>
                                            </div>
                                        </div>

                                        <span class="office-procedure-summary__badge">
                                            <i class="bi bi-bandaid"></i>
                                            Процедурный
                                        </span>
                                    </div>

                                    <div class="office-procedure-doctor-note">
                                        <i class="bi bi-info-circle"></i>
                                        Постоянное назначение врача для этого кабинета не требуется.
                                    </div>

                                @endif
                            </section>

                            @if($office->cabinet_type === 'functional')
                                <section class="office-section">
                                    <div class="office-section__title">
                                        <span>Метод диагностики</span>
                                        <i class="bi bi-activity"></i>
                                    </div>

                                    @if($office->functional_method_short_label)
                                        <div class="office-functional-method-single">
                                            <div class="office-functional-method-single__icon">
                                                <i class="bi bi-activity"></i>
                                            </div>

                                            <div>
                                                <div class="office-functional-method-single__label">
                                                    {{ $office->functional_method_short_label }}
                                                </div>
                                                <div class="office-functional-method-single__description">
                                                    {{ $office->functional_method_label }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="office-empty-cards">
                                            Метод диагностики не указан.
                                        </div>
                                    @endif
                                </section>
                            @endif

                            <section class="office-section">
                                <div class="office-section__title">
                                    <span>Время работы кабинета</span>
                                    <i class="bi bi-clock"></i>
                                </div>

                                @if(!empty($office->work_schedule))
                                    <div class="office-procedure-schedule">
                                        @foreach($office->work_schedule as $day)
                                            <div class="office-procedure-schedule__day {{ $day['open'] ? '' : 'is-closed' }}">
                                                <div class="office-procedure-schedule__name">
                                                    {{ $day['label'] }}
                                                </div>

                                                <span class="office-procedure-schedule__time">
                                                    {{ $day['open']
                                                        ? $day['from'] . '–' . $day['to']
                                                        : 'Выходной' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(!empty($office->work_schedule_source))
                                        <div class="office-procedure-doctor-note">
                                            <i class="bi bi-info-circle"></i>
                                            {{ $office->work_schedule_source }}
                                        </div>
                                    @endif
                                @else
                                    <div class="office-empty-cards">
                                        Расписание сотрудника не задано.
                                    </div>
                                @endif
                            </section>

                            @if($office->cabinet_type === 'doctor')
                                <section class="office-section">
                                    <div class="office-section__title">
                                        <span>Выдача рецептурных бланков</span>
                                        <i class="bi bi-prescription2"></i>
                                    </div>

                                    <div class="office-recipes">
                                        <div class="office-recipe office-recipe--148">
                                            <div class="office-recipe__top">
                                                <div class="office-recipe__label">
                                                    Форма 148
                                                    <small>выдано на кабинет</small>
                                                </div>

                                                <div class="office-recipe__value">
                                                    {{ $office->count_148 ?? 0 }}<span>шт.</span>
                                                </div>
                                            </div>

                                            <div class="office-recipe__stats">
                                                <div class="office-recipe__stat">
                                                    <div class="office-recipe__stat-label">
                                                        Выписано
                                                    </div>
                                                    <div class="office-recipe__stat-value">
                                                        {{ $office->issuedPrescriptions() }}
                                                    </div>
                                                </div>

                                                <div class="office-recipe__stat">
                                                    <div class="office-recipe__stat-label">
                                                        Осталось
                                                    </div>
                                                    <div class="office-recipe__stat-value office-recipe__stat-value--remaining">
                                                        {{ $office->remainingPrescriptions() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="office-recipe">
                                            <div class="office-recipe__label">
                                                Форма 107
                                                <small>выдавать на кабинет</small>
                                            </div>
                                            <div class="office-recipe__value">
                                                {{ $office->count_107 ?? 0 }}<span>шт.</span>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @endif

                            @if($office->cabinet_type === 'doctor')
                                <section class="office-section">
                                    <div class="office-section__title">
                                        <span>Медицинские карты в кабинете</span>
                                        <span>{{ $office->patients_count }}</span>
                                    </div>

                                    @if($office->patients_count)
                                        <div class="office-card-list">
                                            @foreach($office['cards'] as $card)
                                                <div class="office-medcard">
                                                    <div class="office-medcard__main">
                                                        <div class="office-medcard__icon">
                                                            <i class="bi bi-folder2-open"></i>
                                                        </div>
                                                        <div>
                                                            <div class="office-medcard__name">{{ $card['patient'] }}</div>
                                                            <div class="office-medcard__number">
                                                                Медкарта №{{ $card['number'] }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="office-medcard__time">{{ $card['time'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="office-empty-cards">
                                            Сейчас в кабинете нет медицинских карт.
                                        </div>
                                    @endif
                                </section>
                            @endif
                        </div>

                        <footer class="office-card__footer">
                            @if($office->cabinet_type === 'doctor')
                                <div class="office-card__footer-hint">
                                    Медкарта отображается здесь только пока физически находится в кабинете.
                                </div>
                            @endif

                            <div class="office-actions">
                                <button type="button"
                                        class="office-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#officeModal"
                                        data-office-mode="edit"
                                        data-office-id="{{ $office->id }}"
                                        data-office-type="{{ $office->cabinet_type }}"
                                        data-office-number="{{ $office->cabinet }}"
                                        data-office-stage="{{ $office->stage }}"
                                        data-office-name="{{ $office->name }}"
                                        data-office-phone="{{ $office->phone }}"
                                        data-office-status="{{ $office->enabled ? 'active' : 'inactive' }}"
                                        data-office-doctor="{{ $office->doctor_id }}"
                                        data-office-nurse="{{ $office->nurse_id }}"
                                        data-office-replace-doctor="{{ $office->replace_doctor_id }}"
                                        data-office-replace-nurse="{{ $office->replace_nurse_id }}"
                                        data-office-recipe-148="{{ $office->count_148 }}"
                                        data-office-recipe-107="{{ $office->count_107 }}"
                                        data-office-functional-method="{{ $office->functional_method }}"
                                        data-office-schedule='@json($office->procedure_schedule ?? [])'>
                                    <i class="bi bi-pencil"></i>
                                    Изменить
                                </button>

                                @if($office->cabinet_type === 'doctor')
                                    <button type="button" class="office-action office-action--primary">
                                        <i class="bi bi-folder-symlink"></i>
                                        Карты
                                    </button>
                                @endif
                            </div>
                        </footer>
                    </article>
                @endforeach
            </section>
        </div>
    </main>

    <div class="modal fade office-modal"
         id="officeModal"
         tabindex="-1"
         aria-labelledby="officeModalTitle"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="officeForm"
                  class="modal-content"
                  method="POST"
                  action="{{ route('doctors.settings.offices.store') }}">
                @csrf

                <input type="hidden"
                       id="officeId"
                       name="office_id"
                       value="{{ old('office_id') }}">

                <input type="hidden"
                       id="officeMode"
                       name="mode"
                       value="{{ old('mode', 'create') }}">

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="officeModalTitle">Добавить кабинет</h2>
                        <div class="settings-panel__subtitle">
                            Укажите параметры кабинета и сохраните изменения.
                        </div>
                    </div>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    @if($errors->any())
                        <div class="office-flash office-flash--error" role="alert">
                            <div class="office-flash__icon">
                                <i class="bi bi-exclamation-circle-fill"></i>
                            </div>

                            <div class="office-flash__content">
                                <div class="office-flash__title">
                                    Исправьте ошибки в форме
                                </div>

                                <ul class="office-flash__list">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="settings-form-grid">
                        <div class="settings-col-12">
                            <label class="settings-label" for="officeName">Название</label>
                            <input id="officeName"
                                   name="name"
                                   type="text"
                                   class="settings-control"
                                   placeholder="Например: Кабинет врача-психиатра"
                                   value="{{ old('name') }}"
                                   maxlength="255"
                                   required>
                        </div>

                        <div class="settings-col-12">
                            <label class="settings-label">Тип кабинета</label>

                            <div class="office-cabinet-types">
                                <label class="office-cabinet-type">
                                    <input type="radio"
                                           name="cabinet_type"
                                           value="doctor"
                                           class="form-check-input office-cabinet-type-input"
                                        @checked(old('cabinet_type', 'doctor') === 'doctor')>

                                    <span class="office-cabinet-type__icon">
                                        <i class="bi bi-person-badge"></i>
                                    </span>

                                    <span>
                                        <strong>Врачебный кабинет</strong>
                                        <span>За кабинетом закрепляется врач. Медсестра — при необходимости.</span>
                                    </span>
                                </label>

                                <label class="office-cabinet-type">
                                    <input type="radio"
                                           name="cabinet_type"
                                           value="procedure"
                                           class="form-check-input office-cabinet-type-input"
                                        @checked(old('cabinet_type') === 'procedure')>

                                    <span class="office-cabinet-type__icon">
                                        <i class="bi bi-bandaid"></i>
                                    </span>

                                    <span>
                                        <strong>Процедурный кабинет</strong>
                                        <span>Врач не требуется. Необходимо назначить медицинскую сестру.</span>
                                    </span>
                                </label>

                                <label class="office-cabinet-type">
                                    <input type="radio"
                                           name="cabinet_type"
                                           value="functional"
                                           class="form-check-input office-cabinet-type-input"
                                        @checked(old('cabinet_type') === 'functional')>

                                    <span class="office-cabinet-type__icon">
                                        <i class="bi bi-activity"></i>
                                    </span>

                                    <span>
                                        <strong>Функциональная диагностика</strong>
                                        <span>ЭЭГ, ЭКГ и другие исследования. За кабинетом закрепляется врач.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-col-6">
                            <label class="settings-label" for="officeNumber">Номер кабинета</label>
                            <input id="officeNumber"
                                   name="number"
                                   type="text"
                                   class="settings-control"
                                   placeholder="214"
                                   value="{{ old('number') }}"
                                   required>
                        </div>

                        <div class="settings-col-6">
                            <label class="settings-label" for="officeStage">Этаж</label>
                            <input id="officeStage"
                                   name="stage"
                                   type="number"
                                   class="settings-control"
                                   placeholder="Например: 2"
                                   value="{{ old('stage') }}">
                        </div>

                        <div class="settings-col-6">
                            <label class="settings-label" for="officePhone">Телефон</label>
                            <input id="officePhone"
                                   name="phone"
                                   type="text"
                                   class="settings-control"
                                   placeholder="2214"
                                   value="{{ old('phone') }}">
                        </div>

                        <div class="settings-col-6">
                            <label class="settings-label" for="officeStatus">Статус</label>
                            <select id="officeStatus"
                                    name="status"
                                    class="settings-control"
                                    required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Используется</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Не используется</option>
                            </select>
                        </div>

                        <div class="settings-col-6" id="officeDoctorField">
                            <label class="settings-label" for="officeDoctorSearch">Врач</label>

                            <div class="office-doctor-picker" id="officeDoctorPicker">
                                <input type="hidden"
                                       id="officeDoctorId"
                                       name="doctor"
                                       form="officeForm"
                                       value="{{ old('doctor') }}">

                                <div class="office-doctor-picker__input">
                                    <i class="bi bi-search"></i>

                                    <input id="officeDoctorSearch"
                                           type="text"
                                           class="settings-control"
                                           autocomplete="off"
                                           placeholder="Начните вводить ФИО врача...">

                                    <button type="button"
                                            class="office-doctor-picker__clear"
                                            id="officeDoctorClear"
                                            title="Убрать врача">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="office-doctor-picker__dropdown"
                                     id="officeDoctorDropdown">
                                    @foreach($doctors as $clinicDoctor)
                                        <label class="office-doctor-option"
                                               data-doctor-id="{{ $clinicDoctor->id }}"
                                               data-doctor-name="{{ $clinicDoctor->full_name }}"
                                               data-doctor-speciality="{{ $clinicDoctor->status ?: '' }}"
                                               data-doctor-schedule='@json($doctorScheduleMap[$clinicDoctor->id] ?? [])'
                                               data-assigned-office-id="{{ $doctorAssignments[$clinicDoctor->id]['id'] ?? '' }}"
                                               data-assigned-office-number="{{ $doctorAssignments[$clinicDoctor->id]['number'] ?? '' }}">
                                            <input type="radio"
                                                   name="doctor_picker"
                                                   value="{{ $clinicDoctor->id }}"
                                                   class="office-doctor-radio visually-hidden"
                                                @checked((string) old('doctor') === (string) $clinicDoctor->id)>
                                            <span class="office-doctor-option__avatar">
                                                {{ mb_substr($clinicDoctor->name, 0, 1) }}{{ mb_substr($clinicDoctor->surname, 0, 1) }}
                                            </span>

                                            <span>
                                                <span class="office-doctor-option__name">
                                                    {{ $clinicDoctor->full_name }}
                                                </span>
                                                <span class="office-doctor-option__meta">
                                                    {{ $clinicDoctor->status ?: 'Специальность не указана' }}
                                                </span>
                                            </span>

                                            <span class="office-doctor-option__badge">
                                                {{ $clinicDoctor->profession ?: 'Врач' }}
                                            </span>
                                        </label>
                                    @endforeach

                                    <div class="office-doctor-picker__empty"
                                         id="officeDoctorEmpty">
                                        Врач не найден
                                    </div>
                                </div>

                                <div class="office-doctor-selected"
                                     id="officeDoctorSelected">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span id="officeDoctorSelectedText"></span>
                                </div>

                                <div class="office-doctor-picker__error"
                                     id="officeDoctorError">
                                    <i class="bi bi-exclamation-circle"></i>
                                    Выберите врача из выпадающего списка.
                                </div>
                            </div>

                            <div class="settings-field-hint">
                                Поиск выполняется среди врачей текущей клиники.
                            </div>
                        </div>

                        <div class="settings-col-6" id="officeNurseField">
                            <label class="settings-label" for="officeNurseSearch">Медсестра</label>

                            <div class="office-nurse-picker" id="officeNursePicker">
                                <input type="hidden"
                                       id="officeNurseId"
                                       name="nurse"
                                       form="officeForm"
                                       value="{{ old('nurse') }}">

                                <div class="office-nurse-picker__input">
                                    <i class="bi bi-search"></i>

                                    <input id="officeNurseSearch"
                                           type="text"
                                           class="settings-control"
                                           autocomplete="off"
                                           placeholder="Начните вводить ФИО медсестры...">

                                    <button type="button"
                                            class="office-nurse-picker__clear"
                                            id="officeNurseClear"
                                            title="Убрать медсестру">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="office-nurse-picker__dropdown"
                                     id="officeNurseDropdown">
                                    @foreach($nurses as $clinicNurse)
                                        <label class="office-nurse-option"
                                               data-nurse-id="{{ $clinicNurse->id }}"
                                               data-nurse-name="{{ trim($clinicNurse->surname . ' ' . $clinicNurse->name . ' ' . ($clinicNurse->patronym ?? '')) }}"
                                               data-nurse-profession="{{ $clinicNurse->profession }}"
                                               data-assigned-office-id="{{ $nurseAssignments[$clinicNurse->id]['id'] ?? '' }}"
                                               data-assigned-office-number="{{ $nurseAssignments[$clinicNurse->id]['number'] ?? '' }}">
                                            <input type="radio"
                                                   name="nurse_picker"
                                                   value="{{ $clinicNurse->id }}"
                                                   class="office-nurse-radio visually-hidden"
                                                @checked((string) old('nurse') === (string) $clinicNurse->id)>

                                            <span class="office-nurse-option__avatar">
                                                {{ mb_substr($clinicNurse->name, 0, 1) }}{{ mb_substr($clinicNurse->surname, 0, 1) }}
                                            </span>

                                            <span class="office-nurse-option__content">
                                                <span class="office-nurse-option__name">
                                                    {{ trim($clinicNurse->surname . ' ' . $clinicNurse->name . ' ' . ($clinicNurse->patronym ?? '')) }}
                                                </span>
                                                <span class="office-nurse-option__meta">
                                                    {{ $clinicNurse->profession }}
                                                </span>
                                            </span>

                                            <span class="office-nurse-option__badge">
                                                Медсестра
                                            </span>
                                        </label>
                                    @endforeach

                                    <div class="office-nurse-picker__empty"
                                         id="officeNurseEmpty">
                                        Медсестра не найдена
                                    </div>
                                </div>

                                <div class="office-nurse-selected"
                                     id="officeNurseSelected">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span id="officeNurseSelectedText"></span>
                                </div>

                                <div class="office-nurse-picker__error"
                                     id="officeNurseError">
                                    <i class="bi bi-exclamation-circle"></i>
                                    Выберите медсестру из выпадающего списка.
                                </div>
                            </div>

                            <div class="settings-field-hint" id="officeNurseHint">
                                Доступны только медицинские сёстры,
                                добавленные в список сотрудников текущей клиники.
                            </div>
                        </div>

                        <div class="settings-col-12 office-replacement-settings d-none"
                             id="replacementStaffField">
                            <div class="office-replacement-settings__head">
                                <i class="bi bi-arrow-repeat"></i>
                                Замещающие сотрудники
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6"
                                     id="replaceDoctorField">
                                    <label class="settings-label"
                                           for="officeReplaceDoctor">
                                        Замещающий врач
                                    </label>

                                    <select id="officeReplaceDoctor"
                                            name="replace_doctor_id"
                                            class="settings-control">
                                        <option value="">Не назначен</option>

                                        @foreach($replacementDoctors as $replacementDoctor)
                                            <option value="{{ $replacementDoctor->id }}"
                                                @selected(
                                                    (string) old('replace_doctor_id')
                                                    === (string) $replacementDoctor->id
                                                )>
                                                {{ $replacementDoctor->full_name }}
                                                @if($replacementDoctor->status)
                                                    — {{ $replacementDoctor->status }}
                                                @elseif($replacementDoctor->profession)
                                                    — {{ $replacementDoctor->profession }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6"
                                     id="replaceNurseField">
                                    <label class="settings-label"
                                           for="officeReplaceNurse">
                                        Замещающая медсестра
                                    </label>

                                    <select id="officeReplaceNurse"
                                            name="replace_nurse_id"
                                            class="settings-control">
                                        <option value="">Не назначена</option>

                                        @foreach($replacementNurses as $replacementNurse)
                                            <option value="{{ $replacementNurse->id }}"
                                                @selected(
                                                    (string) old('replace_nurse_id')
                                                    === (string) $replacementNurse->id
                                                )>
                                                {{ $replacementNurse->full_name }}
                                                @if($replacementNurse->profession)
                                                    — {{ $replacementNurse->profession }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="office-replacement-settings__hint">
                                Замещение настраивается только при редактировании кабинета.
                                Для процедурного кабинета используется только замещающая медсестра,
                                для функциональной диагностики — только замещающий врач.
                            </div>
                        </div>

                        <div class="settings-col-12"
                             id="doctorScheduleField">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <div>
                                    <label class="settings-label mb-1">График работы врача</label>
                                    <div class="settings-field-hint">
                                        Этот график определяет время работы кабинета для выбранного врача.
                                    </div>
                                </div>

                                <button type="button"
                                        class="office-procedure-copy"
                                        id="copyDoctorMondayButton">
                                    <i class="bi bi-copy"></i>
                                    Скопировать ПН на ПН–ПТ
                                </button>
                            </div>

                            <div class="office-procedure-schedule-form" id="doctorScheduleForm">
                                @foreach($procedureWeekDays as $day)
                                    <div class="office-procedure-schedule-row is-closed"
                                         data-doctor-day="{{ $day['code'] }}"
                                         data-clinic-from="{{ $day['from'] }}"
                                         data-clinic-to="{{ $day['to'] }}">
                                        <div class="office-procedure-schedule-row__day">{{ $day['label'] }}</div>

                                        <div class="office-procedure-day-toggle">
                                            <input type="hidden"
                                                   name="doctor_schedule[{{ $day['code'] }}][enabled]"
                                                   value="0">
                                            <input type="checkbox"
                                                   role="switch"
                                                   class="form-check-input doctor-schedule-toggle"
                                                   name="doctor_schedule[{{ $day['code'] }}][enabled]"
                                                   value="1">
                                        </div>

                                        <input type="time"
                                               class="settings-control doctor-schedule-from"
                                               name="doctor_schedule[{{ $day['code'] }}][from]"
                                               min="{{ $day['from'] }}"
                                               max="{{ $day['to'] }}"
                                               value="{{ $day['from'] }}">

                                        <div class="office-procedure-schedule-row__separator">—</div>

                                        <input type="time"
                                               class="settings-control doctor-schedule-to"
                                               name="doctor_schedule[{{ $day['code'] }}][to]"
                                               min="{{ $day['from'] }}"
                                               max="{{ $day['to'] }}"
                                               value="{{ $day['to'] }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="settings-col-12 d-none"
                             id="procedureScheduleField">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <div>
                                    <label class="settings-label mb-1">
                                        Расписание процедурного кабинета
                                    </label>
                                    <div class="settings-field-hint">
                                        Расписание действует независимо от расписания врачей клиники.
                                    </div>
                                </div>

                                <button type="button"
                                        class="office-procedure-copy"
                                        id="copyProcedureMondayButton">
                                    <i class="bi bi-copy"></i>
                                    Скопировать ПН на ПН–ПТ
                                </button>
                            </div>

                            <div class="office-procedure-schedule-form"
                                 id="procedureScheduleForm">
                                @foreach($procedureWeekDays as $day)
                                    <div class="office-procedure-schedule-row {{ $day['open'] ? '' : 'is-closed' }}"
                                         data-procedure-day="{{ $day['code'] }}"
                                         data-clinic-from="{{ $day['from'] }}"
                                         data-clinic-to="{{ $day['to'] }}">
                                        <div class="office-procedure-schedule-row__day">
                                            {{ $day['label'] }}
                                        </div>

                                        <div class="office-procedure-day-toggle">
                                            <input type="hidden"
                                                   name="procedure_schedule[{{ $day['code'] }}][enabled]"
                                                   value="0">

                                            <input type="checkbox"
                                                   role="switch"
                                                   class="form-check-input procedure-schedule-toggle"
                                                   name="procedure_schedule[{{ $day['code'] }}][enabled]"
                                                   value="1"
                                                {{ old("procedure_schedule.{$day['code']}.enabled", $day['open']) ? 'checked' : '' }}>
                                        </div>

                                        <input type="time"
                                               class="settings-control procedure-schedule-from"
                                               name="procedure_schedule[{{ $day['code'] }}][from]"
                                               min="{{ $day['from'] }}"
                                               max="{{ $day['to'] }}"
                                               value="{{ old("procedure_schedule.{$day['code']}.from", $day['from']) }}">

                                        <div class="office-procedure-schedule-row__separator">—</div>

                                        <input type="time"
                                               class="settings-control procedure-schedule-to"
                                               name="procedure_schedule[{{ $day['code'] }}][to]"
                                               min="{{ $day['from'] }}"
                                               max="{{ $day['to'] }}"
                                               value="{{ old("procedure_schedule.{$day['code']}.to", $day['to']) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="settings-col-6 office-prescription-field">
                            <label class="settings-label" for="officeRecipe148">
                                Форма 148 — сколько выдавать на кабинет
                            </label>
                            <input id="officeRecipe148"
                                   name="recipe_148"
                                   type="number"
                                   min="0"
                                   class="settings-control"
                                   value="{{ old('recipe_148', 0) }}"
                                   required>
                        </div>

                        <div class="settings-col-6 office-prescription-field">
                            <label class="settings-label" for="officeRecipe107">
                                Форма 107 — сколько выдавать на кабинет
                            </label>
                            <input id="officeRecipe107"
                                   name="recipe_107"
                                   type="number"
                                   min="0"
                                   class="settings-control"
                                   value="{{ old('recipe_107', 0) }}"
                                   required>
                        </div>

                        <div class="settings-col-12 d-none"
                             id="functionalMethodsField">
                            <label class="settings-label" for="officeFunctionalMethod">
                                Метод функциональной диагностики
                            </label>

                            <select id="officeFunctionalMethod"
                                    name="functional_method"
                                    class="settings-control">
                                <option value="">Выберите метод</option>

                                @foreach($functionalMethods as $methodCode => $method)
                                    <option value="{{ $methodCode }}"
                                        @selected(old('functional_method') === $methodCode)>
                                        {{ $method['label'] }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="settings-field-hint">
                                Для кабинета функциональной диагностики
                                указывается один метод.
                            </div>
                        </div>
                        <div class="settings-divider"></div>

                        <div class="settings-info-box"
                             id="officeCardsInfo">
                            <div class="settings-info-box__icon">
                                <i class="bi bi-folder2"></i>
                            </div>
                            <div>
                                Конкретные медицинские карты не прикрепляются к кабинету «навсегда».
                                Здесь будет отображаться их текущее физическое местонахождение —
                                карта появляется в кабинете после передачи и исчезает после перемещения в другое место.
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
                            <i class="bi bi-check2-circle"></i>
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
            const search = document.getElementById('officeSearch');
            const cards = Array.from(document.querySelectorAll('[data-office-card]'));

            if (search) {
                search.addEventListener('input', () => {
                    const query = search.value.trim().toLowerCase();

                    cards.forEach((card) => {
                        const haystack = [
                            card.dataset.officeNumber,
                            card.dataset.officeStage,
                            card.dataset.officeName,
                            card.dataset.officeDoctor,
                            card.dataset.officeNurse,
                            card.dataset.officeMethod,
                            card.dataset.officeCards,
                        ].join(' ').toLowerCase();

                        card.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            const modal = document.getElementById('officeModal');
            if (!modal) return;

            const officeForm = document.getElementById('officeForm');
            const title = document.getElementById('officeModalTitle');
            const officeId = document.getElementById('officeId');
            const officeMode = document.getElementById('officeMode');
            const name = document.getElementById('officeName');
            const number = document.getElementById('officeNumber');
            const stage = document.getElementById('officeStage');
            const phone = document.getElementById('officePhone');
            const status = document.getElementById('officeStatus');
            const cabinetTypeInputs = Array.from(document.querySelectorAll('.office-cabinet-type-input'));
            const doctorField = document.getElementById('officeDoctorField');
            const nurseField = document.getElementById('officeNurseField');
            const nurseHint = document.getElementById('officeNurseHint');
            const doctorPicker = document.getElementById('officeDoctorPicker');
            const doctorId = document.getElementById('officeDoctorId');
            const doctorSearch = document.getElementById('officeDoctorSearch');
            const doctorDropdown = document.getElementById('officeDoctorDropdown');
            const doctorClear = document.getElementById('officeDoctorClear');
            const doctorEmpty = document.getElementById('officeDoctorEmpty');
            const doctorSelected = document.getElementById('officeDoctorSelected');
            const doctorSelectedText = document.getElementById('officeDoctorSelectedText');
            const doctorError = document.getElementById('officeDoctorError');
            const doctorOptions = Array.from(document.querySelectorAll('.office-doctor-option'));
            const doctorRadios = Array.from(document.querySelectorAll('.office-doctor-radio'));

            const nursePicker = document.getElementById('officeNursePicker');
            const nurseId = document.getElementById('officeNurseId');
            const nurseSearch = document.getElementById('officeNurseSearch');
            const nurseDropdown = document.getElementById('officeNurseDropdown');
            const nurseClear = document.getElementById('officeNurseClear');
            const nurseEmpty = document.getElementById('officeNurseEmpty');
            const nurseSelected = document.getElementById('officeNurseSelected');
            const nurseSelectedText = document.getElementById('officeNurseSelectedText');
            const nurseError = document.getElementById('officeNurseError');
            const nurseOptions = Array.from(document.querySelectorAll('.office-nurse-option'));
            const nurseRadios = Array.from(document.querySelectorAll('.office-nurse-radio'));

            const replacementStaffField =
                document.getElementById('replacementStaffField');
            const replaceDoctorField =
                document.getElementById('replaceDoctorField');
            const replaceNurseField =
                document.getElementById('replaceNurseField');
            const replaceDoctor =
                document.getElementById('officeReplaceDoctor');
            const replaceNurse =
                document.getElementById('officeReplaceNurse');

            let currentEditingOfficeId = '';

            const recipe148 = document.getElementById('officeRecipe148');
            const recipe107 = document.getElementById('officeRecipe107');
            const prescriptionFields = Array.from(document.querySelectorAll('.office-prescription-field'));
            const functionalMethodsField = document.getElementById('functionalMethodsField');
            const functionalMethod = document.getElementById('officeFunctionalMethod');
            const officeCardsInfo = document.getElementById('officeCardsInfo');

            const doctorScheduleField = document.getElementById('doctorScheduleField');
            const doctorScheduleRows = Array.from(document.querySelectorAll('[data-doctor-day]'));
            const procedureScheduleField = document.getElementById('procedureScheduleField');
            const procedureScheduleRows = Array.from(document.querySelectorAll('[data-procedure-day]'));
            const hasOfficeErrors = @json($errors->any());

            function resetReplacementStaff() {
                if (replaceDoctor) {
                    replaceDoctor.value = '';
                }

                if (replaceNurse) {
                    replaceNurse.value = '';
                }
            }

            function updateReplacementStaffFields() {
                const isEdit = officeMode?.value === 'edit';
                const cabinetType = currentCabinetType();
                const isProcedure =
                    cabinetType === 'procedure';
                const isFunctional =
                    cabinetType === 'functional';

                replacementStaffField?.classList.toggle(
                    'd-none',
                    !isEdit
                );

                replaceDoctorField?.classList.toggle(
                    'd-none',
                    isProcedure
                );

                replaceNurseField?.classList.toggle(
                    'd-none',
                    isFunctional
                );

                if (isProcedure && replaceDoctor) {
                    replaceDoctor.value = '';
                }

                if (isFunctional && replaceNurse) {
                    replaceNurse.value = '';
                }

                if (replaceDoctor) {
                    const primaryDoctorId = String(
                        doctorId?.value || ''
                    );

                    Array.from(replaceDoctor.options)
                        .forEach(option => {
                            if (!option.value) {
                                option.disabled = false;
                                option.hidden = false;
                                return;
                            }

                            const sameDoctor =
                                String(option.value)
                                === primaryDoctorId;

                            option.disabled = sameDoctor;
                            option.hidden = sameDoctor;

                            if (
                                sameDoctor
                                && option.selected
                            ) {
                                replaceDoctor.value = '';
                            }
                        });
                }
            }

            function currentCabinetType() {
                return cabinetTypeInputs.find(input => input.checked)?.value || 'doctor';
            }

            function setCabinetType(value) {
                cabinetTypeInputs.forEach(input => {
                    input.checked = input.value === value;
                });

                updateCabinetType();
            }

            function resetFunctionalMethod() {
                if (functionalMethod) {
                    functionalMethod.value = '';
                }
            }

            function fillFunctionalMethod(method) {
                if (functionalMethod) {
                    functionalMethod.value = String(method || '');
                }
            }

            function updateCabinetType() {
                const cabinetType = currentCabinetType();
                const isProcedure = cabinetType === 'procedure';
                const isDoctor = cabinetType === 'doctor';
                const isFunctional = cabinetType === 'functional';

                doctorField?.classList.toggle('d-none', isProcedure);
                /*
                 * Медсестра доступна для всех типов кабинетов.
                 * Для procedure она обязательна, для doctor/functional —
                 * опциональна.
                 */
                nurseField?.classList.remove('d-none');

                prescriptionFields.forEach(field => {
                    field.classList.toggle('d-none', !isDoctor);
                });

                functionalMethodsField?.classList.toggle(
                    'd-none',
                    !isFunctional
                );

                updateReplacementStaffFields();

                officeCardsInfo?.classList.toggle(
                    'd-none',
                    !isDoctor
                );

                doctorScheduleField?.classList.toggle('d-none', isProcedure);
                procedureScheduleField?.classList.toggle('d-none', !isProcedure);

                if (!isDoctor) {
                    recipe148.value = 0;
                    recipe107.value = 0;
                }

                if (isProcedure) {
                    clearDoctorSelection();

                    if (nurseHint) {
                        nurseHint.textContent =
                            'Для процедурного кабинета выберите медицинскую сестру из сотрудников текущей клиники.';
                    }

                    return;
                }

                nurseError?.classList.remove('is-visible');
                nursePicker?.classList.remove('has-error');

                if (nurseHint && !isProcedure) {
                    nurseHint.textContent =
                        'Медсестра не обязательна. Доступны только сотрудники текущей клиники, не назначенные в другой кабинет.';
                }
            }

            cabinetTypeInputs.forEach(input => {
                input.addEventListener('change', updateCabinetType);
            });

            function updateDoctorScheduleRow(row) {
                const toggle = row.querySelector('.doctor-schedule-toggle');
                row.classList.toggle('is-closed', !toggle?.checked);
            }

            function resetDoctorSchedule() {
                doctorScheduleRows.forEach(row => {
                    const toggle = row.querySelector('.doctor-schedule-toggle');
                    const from = row.querySelector('.doctor-schedule-from');
                    const to = row.querySelector('.doctor-schedule-to');

                    if (toggle) toggle.checked = false;
                    if (from) from.value = row.dataset.clinicFrom || '08:00';
                    if (to) to.value = row.dataset.clinicTo || '16:00';
                    updateDoctorScheduleRow(row);
                });
            }

            function fillDoctorSchedule(schedule) {
                const items = Array.isArray(schedule) ? schedule : [];

                doctorScheduleRows.forEach(row => {
                    const code = row.dataset.doctorDay;
                    const item = items.find(day => day.code === code);
                    const toggle = row.querySelector('.doctor-schedule-toggle');
                    const from = row.querySelector('.doctor-schedule-from');
                    const to = row.querySelector('.doctor-schedule-to');

                    if (toggle) toggle.checked = Boolean(item?.open);
                    if (from) from.value = item?.from || row.dataset.clinicFrom || '08:00';
                    if (to) to.value = item?.to || row.dataset.clinicTo || '16:00';
                    updateDoctorScheduleRow(row);
                });
            }

            doctorScheduleRows.forEach(row => {
                const toggle = row.querySelector('.doctor-schedule-toggle');
                const from = row.querySelector('.doctor-schedule-from');
                const to = row.querySelector('.doctor-schedule-to');

                toggle?.addEventListener('change', () => updateDoctorScheduleRow(row));

                [from, to].forEach(input => {
                    input?.addEventListener('focus', () => {
                        if (toggle && !toggle.checked) {
                            toggle.checked = true;
                            updateDoctorScheduleRow(row);
                        }
                    });
                });

                updateDoctorScheduleRow(row);
            });

            document.getElementById('copyDoctorMondayButton')?.addEventListener('click', () => {
                const monday = document.querySelector('[data-doctor-day="mon"]');
                if (!monday) return;

                const enabled = monday.querySelector('.doctor-schedule-toggle')?.checked ?? false;
                const from = monday.querySelector('.doctor-schedule-from')?.value || monday.dataset.clinicFrom || '08:00';
                const to = monday.querySelector('.doctor-schedule-to')?.value || monday.dataset.clinicTo || '16:00';

                ['mon', 'tue', 'wed', 'thu', 'fri'].forEach(code => {
                    const row = document.querySelector(`[data-doctor-day="${code}"]`);
                    if (!row) return;
                    const toggle = row.querySelector('.doctor-schedule-toggle');
                    const rowFrom = row.querySelector('.doctor-schedule-from');
                    const rowTo = row.querySelector('.doctor-schedule-to');
                    if (toggle) toggle.checked = enabled;
                    if (rowFrom) rowFrom.value = from;
                    if (rowTo) rowTo.value = to;
                    updateDoctorScheduleRow(row);
                });
            });

            function updateProcedureScheduleRow(row) {
                const toggle = row.querySelector('.procedure-schedule-toggle');
                row.classList.toggle('is-closed', !toggle?.checked);
            }

            function timeToMinutes(value) {
                if (!value || !value.includes(':')) {
                    return null;
                }

                const [hours, minutes] = value.split(':').map(Number);

                if (
                    Number.isNaN(hours)
                    || Number.isNaN(minutes)
                ) {
                    return null;
                }

                return hours * 60 + minutes;
            }

            function minutesToTime(value) {
                const normalized = Math.max(
                    0,
                    Math.min(value, 23 * 60 + 59)
                );

                const hours = Math.floor(normalized / 60);
                const minutes = normalized % 60;

                return [
                    String(hours).padStart(2, '0'),
                    String(minutes).padStart(2, '0'),
                ].join(':');
            }

            function normalizeProcedureScheduleRow(row, changedField = null) {
                const toggle = row.querySelector('.procedure-schedule-toggle');
                const from = row.querySelector('.procedure-schedule-from');
                const to = row.querySelector('.procedure-schedule-to');

                if (!toggle?.checked || !from || !to) {
                    return;
                }

                const clinicFrom = timeToMinutes(row.dataset.clinicFrom);
                const clinicTo = timeToMinutes(row.dataset.clinicTo);

                let fromMinutes = timeToMinutes(from.value);
                let toMinutes = timeToMinutes(to.value);

                if (
                    clinicFrom === null
                    || clinicTo === null
                ) {
                    return;
                }

                if (fromMinutes === null) {
                    fromMinutes = clinicFrom;
                    from.value = minutesToTime(fromMinutes);
                }

                if (toMinutes === null) {
                    toMinutes = clinicTo;
                    to.value = minutesToTime(toMinutes);
                }

                fromMinutes = Math.max(clinicFrom, Math.min(fromMinutes, clinicTo));
                toMinutes = Math.max(clinicFrom, Math.min(toMinutes, clinicTo));

                from.value = minutesToTime(fromMinutes);
                to.value = minutesToTime(toMinutes);

                if (fromMinutes < toMinutes) {
                    return;
                }

                if (changedField === 'to') {
                    const candidateFrom = toMinutes - 60;

                    if (candidateFrom >= clinicFrom) {
                        fromMinutes = candidateFrom;
                    } else {
                        fromMinutes = clinicFrom;
                        toMinutes = Math.min(clinicTo, clinicFrom + 60);
                    }
                } else {
                    const candidateTo = fromMinutes + 60;

                    if (candidateTo <= clinicTo) {
                        toMinutes = candidateTo;
                    } else {
                        toMinutes = clinicTo;
                        fromMinutes = Math.max(clinicFrom, clinicTo - 60);
                    }
                }

                from.value = minutesToTime(fromMinutes);
                to.value = minutesToTime(toMinutes);
            }

            function resetProcedureSchedule() {
                procedureScheduleRows.forEach(row => {
                    const toggle = row.querySelector('.procedure-schedule-toggle');
                    const from = row.querySelector('.procedure-schedule-from');
                    const to = row.querySelector('.procedure-schedule-to');

                    if (toggle) {
                        toggle.checked = false;
                    }

                    if (from) {
                        from.value = row.dataset.clinicFrom || '08:00';
                    }

                    if (to) {
                        to.value = row.dataset.clinicTo || '16:00';
                    }

                    updateProcedureScheduleRow(row);
                });
            }

            function fillProcedureSchedule(schedule) {
                const items = Array.isArray(schedule) ? schedule : [];

                procedureScheduleRows.forEach(row => {
                    const code = row.dataset.procedureDay;
                    const item = items.find(day => day.code === code);
                    const toggle = row.querySelector('.procedure-schedule-toggle');
                    const from = row.querySelector('.procedure-schedule-from');
                    const to = row.querySelector('.procedure-schedule-to');

                    if (toggle) {
                        toggle.checked = Boolean(item?.open);
                    }

                    if (from) {
                        from.value = item?.from
                            || row.dataset.clinicFrom
                            || '08:00';
                    }

                    if (to) {
                        to.value = item?.to
                            || row.dataset.clinicTo
                            || '16:00';
                    }

                    normalizeProcedureScheduleRow(row);
                    updateProcedureScheduleRow(row);
                });
            }

            procedureScheduleRows.forEach(row => {
                const toggle = row.querySelector('.procedure-schedule-toggle');
                const from = row.querySelector('.procedure-schedule-from');
                const to = row.querySelector('.procedure-schedule-to');

                toggle?.addEventListener('change', () => {
                    if (toggle.checked) {
                        normalizeProcedureScheduleRow(row);
                    }

                    updateProcedureScheduleRow(row);
                });

                from?.addEventListener('focus', () => {
                    if (toggle && !toggle.checked) {
                        toggle.checked = true;
                    }

                    updateProcedureScheduleRow(row);
                });

                from?.addEventListener('change', () => {
                    if (toggle && !toggle.checked) {
                        toggle.checked = true;
                    }

                    normalizeProcedureScheduleRow(row, 'from');
                    updateProcedureScheduleRow(row);
                });

                to?.addEventListener('focus', () => {
                    if (toggle && !toggle.checked) {
                        toggle.checked = true;
                    }

                    updateProcedureScheduleRow(row);
                });

                to?.addEventListener('change', () => {
                    if (toggle && !toggle.checked) {
                        toggle.checked = true;
                    }

                    normalizeProcedureScheduleRow(row, 'to');
                    updateProcedureScheduleRow(row);
                });

                updateProcedureScheduleRow(row);
            });

            document.getElementById('copyProcedureMondayButton')?.addEventListener('click', () => {
                const monday = document.querySelector('[data-procedure-day="mon"]');

                if (!monday) {
                    return;
                }

                const enabled =
                    monday.querySelector('.procedure-schedule-toggle')?.checked
                    ?? false;

                const from =
                    monday.querySelector('.procedure-schedule-from')?.value
                    || monday.dataset.clinicFrom
                    || '08:00';

                const to =
                    monday.querySelector('.procedure-schedule-to')?.value
                    || monday.dataset.clinicTo
                    || '16:00';

                ['mon', 'tue', 'wed', 'thu', 'fri'].forEach(code => {
                    const row = document.querySelector(
                        `[data-procedure-day="${code}"]`
                    );

                    if (!row) {
                        return;
                    }

                    const toggle = row.querySelector('.procedure-schedule-toggle');
                    const rowFrom = row.querySelector('.procedure-schedule-from');
                    const rowTo = row.querySelector('.procedure-schedule-to');

                    if (toggle) {
                        toggle.checked = enabled;
                    }

                    if (rowFrom) {
                        rowFrom.value = from;
                    }

                    if (rowTo) {
                        rowTo.value = to;
                    }

                    normalizeProcedureScheduleRow(row);
                    updateProcedureScheduleRow(row);
                });
            });

            function openDoctorDropdown() {
                doctorDropdown?.classList.add('is-open');
            }

            function closeDoctorDropdown() {
                doctorDropdown?.classList.remove('is-open');
            }

            function isStaffOptionUnavailable(option) {
                const assignedOfficeId = String(
                    option?.dataset?.assignedOfficeId || ''
                );

                if (!assignedOfficeId) {
                    return false;
                }

                return assignedOfficeId
                    !== String(currentEditingOfficeId || '');
            }

            function updateStaffAvailability(officeId = '') {
                currentEditingOfficeId = String(officeId || '');

                doctorOptions.forEach(option => {
                    const unavailable =
                        isStaffOptionUnavailable(option);

                    const radio =
                        option.querySelector('.office-doctor-radio');

                    option.classList.toggle(
                        'is-unavailable',
                        unavailable
                    );

                    /*
                     * Не скрываем занятого врача: он остаётся в списке,
                     * но выбрать его нельзя. Так видно всех врачей клиники.
                     */
                    option.classList.remove('d-none');

                    const badge = option.querySelector('.office-doctor-option__badge');
                    if (badge) {
                        if (!badge.dataset.originalText) {
                            badge.dataset.originalText = badge.textContent.trim();
                        }

                        badge.textContent = unavailable
                            ? (option.dataset.assignedOfficeNumber
                                ? `Занят · каб. ${option.dataset.assignedOfficeNumber}`
                                : 'Занят')
                            : badge.dataset.originalText;
                    }

                    if (radio) {
                        radio.disabled = unavailable;

                        if (unavailable && radio.checked) {
                            radio.checked = false;
                        }
                    }
                });

                nurseOptions.forEach(option => {
                    const unavailable =
                        isStaffOptionUnavailable(option);

                    const radio =
                        option.querySelector('.office-nurse-radio');

                    option.classList.toggle(
                        'is-unavailable',
                        unavailable
                    );

                    option.classList.toggle(
                        'd-none',
                        unavailable
                    );

                    if (radio) {
                        radio.disabled = unavailable;

                        if (unavailable && radio.checked) {
                            radio.checked = false;
                        }
                    }
                });

                filterDoctors();
                filterNurses();
            }

            function selectedDoctorRadio() {
                return doctorRadios.find(radio => radio.checked) || null;
            }

            function clearDoctorSelection() {
                doctorRadios.forEach(radio => {
                    radio.checked = false;
                });

                doctorId.value = '';
                doctorSearch.value = '';
                resetDoctorSchedule();

                if (doctorSelectedText) {
                    doctorSelectedText.textContent = '';
                }

                doctorSelected?.classList.remove('is-visible');
                doctorClear?.classList.remove('is-visible');
                doctorError?.classList.remove('is-visible');
                doctorPicker?.classList.remove('has-error');

                doctorOptions.forEach(option => {
                    option.classList.remove('is-selected');

                    if (
                        !option.classList.contains(
                            'is-unavailable'
                        )
                    ) {
                        option.classList.remove('d-none');
                    }
                });

                doctorEmpty?.classList.remove('is-visible');
                updateReplacementStaffFields();
            }

            function selectDoctor(value) {
                const normalized = String(value || '');

                const option = doctorOptions.find(item => {
                    return String(item.dataset.doctorId || '') === normalized
                        || String(item.dataset.doctorName || '') === normalized;
                });

                if (!option) {
                    clearDoctorSelection();

                    return false;
                }

                if (option.classList.contains('is-unavailable')) {
                    return false;
                }

                const radio = option.querySelector('.office-doctor-radio');

                if (!radio) {
                    return false;
                }

                doctorRadios.forEach(item => {
                    item.checked = false;
                });

                radio.checked = true;
                doctorId.value = String(radio.value || '');

                doctorOptions.forEach(item => {
                    item.classList.remove('is-selected');
                });

                option.classList.add('is-selected');

                const doctorName = String(option.dataset.doctorName || '');
                const speciality =
                    option.dataset.doctorSpeciality
                    || 'Специальность не указана';

                doctorSearch.value = doctorName;

                try {
                    fillDoctorSchedule(JSON.parse(option.dataset.doctorSchedule || '[]'));
                } catch (error) {
                    resetDoctorSchedule();
                }

                if (doctorSelectedText) {
                    doctorSelectedText.textContent =
                        `${doctorName} · ${speciality}`;
                }

                doctorSelected?.classList.add('is-visible');
                doctorClear?.classList.add('is-visible');
                doctorError?.classList.remove('is-visible');
                doctorPicker?.classList.remove('has-error');

                closeDoctorDropdown();
                updateReplacementStaffFields();

                return true;
            }

            function filterDoctors() {
                const query = String(doctorSearch?.value || '')
                    .trim()
                    .toLowerCase();

                let visibleCount = 0;

                doctorOptions.forEach(option => {
                    const haystack = [
                        option.dataset.doctorName,
                        option.dataset.doctorSpeciality,
                        option.querySelector('.office-doctor-option__badge')?.textContent,
                    ]
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();

                    /*
                     * Занятые врачи участвуют в отображении и поиске,
                     * но остаются disabled и не выбираются.
                     */
                    const visible = !query || haystack.includes(query);

                    option.classList.toggle('d-none', !visible);

                    if (visible) {
                        visibleCount++;
                    }
                });

                doctorEmpty?.classList.toggle(
                    'is-visible',
                    visibleCount === 0
                );
            }

            doctorSearch?.addEventListener('focus', () => {
                filterDoctors();
                openDoctorDropdown();
            });

            doctorSearch?.addEventListener('input', () => {
                const selected = selectedDoctorRadio();

                if (
                    selected
                    && doctorSearch.value.trim()
                    !== selected.closest('.office-doctor-option')?.dataset.doctorName
                ) {
                    doctorRadios.forEach(radio => {
                        radio.checked = false;
                    });

                    doctorId.value = '';

                    doctorOptions.forEach(option => {
                        option.classList.remove('is-selected');
                    });

                    doctorSelected?.classList.remove('is-visible');
                }

                doctorError?.classList.remove('is-visible');
                doctorPicker?.classList.remove('has-error');

                doctorClear?.classList.toggle(
                    'is-visible',
                    doctorSearch.value.length > 0
                );

                filterDoctors();
                openDoctorDropdown();
            });

            doctorOptions.forEach(option => {
                option.addEventListener('click', event => {
                    event.preventDefault();

                    if (option.classList.contains('is-unavailable')) {
                        return;
                    }

                    selectDoctor(option.dataset.doctorId);
                });
            });

            doctorClear?.addEventListener('click', () => {
                clearDoctorSelection();
                filterDoctors();
                doctorSearch?.focus();
                openDoctorDropdown();
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('#officeDoctorPicker')) {
                    closeDoctorDropdown();
                }
            });

            function openNurseDropdown() {
                nurseDropdown?.classList.add('is-open');
            }

            function closeNurseDropdown() {
                nurseDropdown?.classList.remove('is-open');
            }

            function selectedNurseRadio() {
                return nurseRadios.find(radio => radio.checked) || null;
            }

            function clearNurseSelection() {
                nurseRadios.forEach(radio => {
                    radio.checked = false;
                });

                nurseId.value = '';
                nurseSearch.value = '';

                if (nurseSelectedText) {
                    nurseSelectedText.textContent = '';
                }

                nurseSelected?.classList.remove('is-visible');
                nurseClear?.classList.remove('is-visible');
                nurseError?.classList.remove('is-visible');
                nursePicker?.classList.remove('has-error');

                nurseOptions.forEach(option => {
                    option.classList.remove('is-selected');

                    if (
                        !option.classList.contains(
                            'is-unavailable'
                        )
                    ) {
                        option.classList.remove('d-none');
                    }
                });

                nurseEmpty?.classList.remove('is-visible');
            }

            function selectNurse(value) {
                const normalized = String(value || '');

                const option = nurseOptions.find(item => {
                    return String(item.dataset.nurseId || '') === normalized
                        || String(item.dataset.nurseName || '') === normalized;
                });

                if (!option) {
                    clearNurseSelection();
                    return false;
                }

                if (option.classList.contains('is-unavailable')) {
                    return false;
                }

                const radio = option.querySelector('.office-nurse-radio');

                nurseRadios.forEach(item => {
                    item.checked = false;
                });

                radio.checked = true;
                nurseId.value = String(radio.value || '');

                nurseOptions.forEach(item => {
                    item.classList.remove('is-selected');
                });

                option.classList.add('is-selected');

                const nurseName = String(option.dataset.nurseName || '');
                const profession = String(option.dataset.nurseProfession || '');

                nurseSearch.value = nurseName;

                if (nurseSelectedText) {
                    nurseSelectedText.textContent =
                        `${nurseName}${profession ? ' · ' + profession : ''}`;
                }

                nurseSelected?.classList.add('is-visible');
                nurseClear?.classList.add('is-visible');
                nurseError?.classList.remove('is-visible');
                nursePicker?.classList.remove('has-error');

                closeNurseDropdown();

                return true;
            }

            function filterNurses() {
                const query = String(nurseSearch?.value || '')
                    .trim()
                    .toLowerCase();

                let visibleCount = 0;

                nurseOptions.forEach(option => {
                    const haystack = [
                        option.dataset.nurseName,
                        option.dataset.nurseProfession,
                    ]
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();

                    const unavailable =
                        option.classList.contains('is-unavailable');

                    const visible = !unavailable
                        && (!query || haystack.includes(query));

                    option.classList.toggle('d-none', !visible);

                    if (visible) {
                        visibleCount++;
                    }
                });

                nurseEmpty?.classList.toggle('is-visible', visibleCount === 0);
            }

            nurseSearch?.addEventListener('focus', () => {
                filterNurses();
                openNurseDropdown();
            });

            nurseSearch?.addEventListener('input', () => {
                const selected = selectedNurseRadio();

                if (
                    selected
                    && nurseSearch.value.trim()
                    !== selected.closest('.office-nurse-option')?.dataset.nurseName
                ) {
                    nurseRadios.forEach(radio => {
                        radio.checked = false;
                    });

                    nurseId.value = '';

                    nurseOptions.forEach(option => {
                        option.classList.remove('is-selected');
                    });

                    nurseSelected?.classList.remove('is-visible');
                }

                nurseError?.classList.remove('is-visible');
                nursePicker?.classList.remove('has-error');

                nurseClear?.classList.toggle(
                    'is-visible',
                    nurseSearch.value.length > 0
                );

                filterNurses();
                openNurseDropdown();
            });

            nurseOptions.forEach(option => {
                option.addEventListener('click', event => {
                    event.preventDefault();

                    if (option.classList.contains('is-unavailable')) {
                        return;
                    }

                    selectNurse(option.dataset.nurseId);
                });
            });

            nurseClear?.addEventListener('click', () => {
                clearNurseSelection();
                filterNurses();
                nurseSearch?.focus();
                openNurseDropdown();
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('#officeNursePicker')) {
                    closeNurseDropdown();
                }
            });

            updateCabinetType();

            if (currentCabinetType() !== 'procedure' && doctorId.value) {
                selectDoctor(doctorId.value);
            }

            if (nurseId.value) {
                selectNurse(nurseId.value);
            }

            officeForm?.addEventListener('submit', event => {
                const selected = selectedDoctorRadio();

                if (selected) {
                    doctorId.value = String(selected.value || '');
                }

                if (currentCabinetType() !== 'procedure' && !doctorId.value && doctorSearch.value.trim()) {
                    const query = doctorSearch.value
                        .trim()
                        .toLowerCase();

                    const exactDoctor = doctorOptions.find(option => {
                        return !option.classList.contains('is-unavailable')
                            && String(option.dataset.doctorName || '')
                                .trim()
                                .toLowerCase() === query;
                    });

                    if (exactDoctor) {
                        selectDoctor(exactDoctor.dataset.doctorId);
                    }
                }

                if (currentCabinetType() !== 'procedure' && !doctorId.value) {
                    event.preventDefault();

                    doctorError?.classList.add('is-visible');
                    doctorPicker?.classList.add('has-error');

                    filterDoctors();
                    openDoctorDropdown();
                    doctorSearch?.focus();

                    return;
                }

                doctorError?.classList.remove('is-visible');
                doctorPicker?.classList.remove('has-error');

                const selectedNurse = selectedNurseRadio();

                if (selectedNurse) {
                    nurseId.value = String(selectedNurse.value || '');
                }

                if (!nurseId.value && nurseSearch.value.trim()) {
                    const query = nurseSearch.value
                        .trim()
                        .toLowerCase();

                    const exactNurse = nurseOptions.find(option => {
                        return !option.classList.contains('is-unavailable')
                            && String(option.dataset.nurseName || '')
                                .trim()
                                .toLowerCase() === query;
                    });

                    if (exactNurse) {
                        selectNurse(exactNurse.dataset.nurseId);
                    }
                }

                if (currentCabinetType() === 'procedure' && !nurseId.value) {
                    event.preventDefault();

                    nurseError?.classList.add('is-visible');
                    nursePicker?.classList.add('has-error');

                    filterNurses();
                    openNurseDropdown();
                    nurseSearch?.focus();

                    return;
                }

                nurseError?.classList.remove('is-visible');
                nursePicker?.classList.remove('has-error');
            });

            officeForm?.addEventListener('formdata', event => {
                if (doctorId.value) {
                    event.formData.set('doctor', doctorId.value);
                } else {
                    event.formData.delete('doctor');
                }

                if (nurseId.value) {
                    event.formData.set('nurse', nurseId.value);
                } else {
                    event.formData.delete('nurse');
                }
            });

            modal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;

                if (!button && hasOfficeErrors) {
                    title.textContent = officeMode.value === 'edit'
                        ? `Кабинет №${number.value}`
                        : 'Добавить кабинет';

                    updateStaffAvailability(
                        officeMode.value === 'edit'
                            ? officeId.value
                            : ''
                    );

                    updateCabinetType();
                        updateReplacementStaffFields();

                    if (currentCabinetType() !== 'procedure' && doctorId.value) {
                        selectDoctor(doctorId.value);
                    }

                    if (nurseId.value) {
                        selectNurse(nurseId.value);
                    }

                    return;
                }

                const mode = button?.dataset.officeMode || 'create';

                if (mode === 'edit') {
                    title.textContent = `Кабинет №${button.dataset.officeNumber}`;
                    officeMode.value = 'edit';
                    officeId.value = button.dataset.officeId || '';
                    updateStaffAvailability(officeId.value);
                    name.value = button.dataset.officeName || '';
                    number.value = button.dataset.officeNumber || '';
                    stage.value = button.dataset.officeStage || '';
                    phone.value = button.dataset.officePhone || '';
                    status.value = button.dataset.officeStatus || 'active';
                    setCabinetType(button.dataset.officeType || 'doctor');

                    if (replaceDoctor) {
                        replaceDoctor.value =
                            button.dataset.officeReplaceDoctor
                            || '';
                    }

                    if (replaceNurse) {
                        replaceNurse.value =
                            button.dataset.officeReplaceNurse
                            || '';
                    }

                    updateReplacementStaffFields();

                    fillFunctionalMethod(
                        button.getAttribute(
                            'data-office-functional-method'
                        ) || ''
                    );

                    if (currentCabinetType() !== 'procedure') {
                        selectDoctor(button.dataset.officeDoctor || '');
                    }

                    selectNurse(
                        button.dataset.officeNurse || ''
                    );

                    if (currentCabinetType() === 'doctor') {
                        recipe148.value = button.getAttribute('data-office-recipe-148') ?? 0;
                        recipe107.value = button.getAttribute('data-office-recipe-107') ?? 0;
                    } else {
                        recipe148.value = 0;
                        recipe107.value = 0;

                        try {
                            fillProcedureSchedule(
                                JSON.parse(button.getAttribute('data-office-schedule') || '[]')
                            );
                        } catch (error) {
                            resetProcedureSchedule();
                        }
                    }

                    return;
                }

                title.textContent = 'Добавить кабинет';
                officeMode.value = 'create';
                officeId.value = '';
                updateStaffAvailability('');
                name.value = '';
                number.value = '';
                stage.value = '';
                phone.value = '';
                status.value = 'active';
                setCabinetType('doctor');
                clearDoctorSelection();
                clearNurseSelection();
                resetDoctorSchedule();
                resetProcedureSchedule();
                resetFunctionalMethod();
                resetReplacementStaff();
                updateReplacementStaffFields();
                recipe148.value = 0;
                recipe107.value = 0;
            });

            updateStaffAvailability(
                officeMode.value === 'edit'
                    ? officeId.value
                    : ''
            );

            updateReplacementStaffFields();

            if (hasOfficeErrors) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        })();
    </script>
@endpush
