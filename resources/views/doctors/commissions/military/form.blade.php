@extends('doctors.prescriptions')

@section('prescription-title', $pageTitle)

@push('scripts')
    @vite('resources/js/military-commission-form.js')
@endpush

@section('sub-main')
    @include('doctors.commissions.military.partials.styles')

    <style>
        /* VVK_FORM_CANDY_V1 */
        #militaryCommissionFormRoot.military-form-page {
            --form-accent: #008d9d;
            --form-accent-dark: #006f7d;
            --form-accent-soft: #e8f7f8;
            --form-surface: #ffffff;
            --form-surface-muted: #f7fafb;
            --form-shadow: 0 12px 34px rgba(26, 66, 87, .07);
            background:
                radial-gradient(circle at 92% 4%, rgba(0, 141, 157, .08), transparent 25rem),
                linear-gradient(180deg, #f7fbfc 0, #f4f8fa 26rem, #f8fafb 100%);
            min-height: 100vh;
        }

        .military-form-page .military-form-hero {
            position: relative;
            overflow: hidden;
            border-color: #cfe5e8;
            box-shadow: var(--form-shadow);
        }

        .military-form-page .military-form-hero::after {
            content: '';
            position: absolute;
            right: -72px;
            top: -102px;
            width: 230px;
            height: 230px;
            border: 42px solid rgba(0, 141, 157, .055);
            border-radius: 50%;
            pointer-events: none;
        }

        .military-form-page .military-form-hero > * {
            position: relative;
            z-index: 1;
        }

        .military-form-page .military-form-hero__copy {
            min-width: 0;
        }

        .military-form-page .military-form-hero__meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 7px;
        }

        .military-form-page .military-form-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 25px;
            color: #476078;
            background: #f5fafb;
            border: 1px solid #dce9ed;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 750;
            line-height: 1;
        }

        .military-form-page .military-form-chip--status {
            color: var(--form-accent-dark);
            background: var(--form-accent-soft);
            border-color: #c8e7ea;
        }

        .military-form-page .military-form-progress {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 6px;
            background: rgba(255, 255, 255, .94);
            border: 1px solid #d9e7ec;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(26, 66, 87, .045);
            padding: 7px;
            margin-bottom: 14px;
        }

        .military-form-page .military-form-progress__item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            color: #6f8195 !important;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 9px 11px;
            text-decoration: none !important;
            transition: .18s ease;
        }

        .military-form-page .military-form-progress__item:hover {
            color: var(--form-accent-dark) !important;
            background: #f4fbfb;
        }

        .military-form-page .military-form-progress__item.is-active {
            color: var(--form-accent-dark) !important;
            background: var(--form-accent-soft);
            border-color: #cde8eb;
        }

        .military-form-page .military-form-progress__number {
            width: 29px;
            height: 29px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: #70859a;
            background: #f4f7f9;
            border: 1px solid #dce6eb;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 850;
        }

        .military-form-page .is-active .military-form-progress__number,
        .military-form-page .is-complete .military-form-progress__number {
            color: #fff;
            background: var(--form-accent);
            border-color: var(--form-accent);
        }

        .military-form-page .military-form-progress__copy {
            min-width: 0;
        }

        .military-form-page .military-form-progress__copy strong,
        .military-form-page .military-form-progress__copy small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .military-form-page .military-form-progress__copy strong {
            color: inherit;
            font-size: 12px;
            font-weight: 800;
        }

        .military-form-page .military-form-progress__copy small {
            color: #91a0b1;
            font-size: 10px;
            margin-top: 1px;
        }

        .military-form-page .military-form-layout {
            grid-template-columns: minmax(0, 1fr) 304px;
            gap: 16px;
        }

        .military-form-page .military-form-main {
            gap: 16px;
        }

        .military-form-page .military-form-card {
            position: relative;
            overflow: visible;
            border-color: #d8e6eb;
            border-radius: 18px;
            box-shadow: var(--form-shadow);
            scroll-margin-top: 16px;
        }

        .military-form-page .military-form-card::before {
            content: '';
            position: absolute;
            z-index: 2;
            top: -1px;
            left: 24px;
            width: 82px;
            height: 3px;
            background: linear-gradient(90deg, var(--form-accent), #66c8d1);
            border-radius: 0 0 999px 999px;
        }

        .military-form-page .military-form-card .military-card__head {
            align-items: center;
            background: linear-gradient(180deg, #fbfdfd, #f8fbfc);
            border-bottom-color: #e5eef2;
            border-radius: 17px 17px 0 0;
            padding: 15px 18px 13px;
            margin: -18px -18px 16px;
        }

        .military-form-page .military-form-card .military-card__icon {
            width: 42px;
            height: 42px;
            border: 1px solid #cfe9ec;
            box-shadow: 0 5px 14px rgba(0, 141, 157, .09);
        }

        .military-form-page .military-form-card .military-card__head h2 {
            font-size: 17px;
        }

        .military-form-page .military-form-card .military-card__head p {
            max-width: 760px;
            line-height: 1.35;
        }

        .military-form-page .military-form-card .military-step {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: var(--form-accent-dark);
            background: #fff;
            border: 1px solid #cfe5e8;
            border-radius: 10px;
            padding: 0;
        }

        .military-form-page .military-field label {
            color: #344a61;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .military-form-page .military-field input,
        .military-form-page .military-field select,
        .military-form-page .military-field textarea {
            background: #fcfefe;
            border-color: #cbdde4;
            border-radius: 11px;
        }

        .military-form-page .military-field input,
        .military-form-page .military-field select {
            min-height: 42px;
        }

        .military-form-page .military-field textarea {
            min-height: 112px;
            line-height: 1.5;
        }

        .military-form-page .military-field input:hover,
        .military-form-page .military-field select:hover,
        .military-form-page .military-field textarea:hover {
            border-color: #a9cbd2;
        }

        .military-form-page .military-field input:focus,
        .military-form-page .military-field select:focus,
        .military-form-page .military-field textarea:focus {
            background: #fff;
            border-color: var(--form-accent);
            box-shadow: 0 0 0 4px rgba(0, 141, 157, .1);
        }

        .military-form-page .military-field small {
            line-height: 1.4;
        }

        .military-form-page textarea.is-codemirror-source {
            display: none;
        }

        .military-form-page .military-codemirror-shell {
            overflow: hidden;
            background: #fff;
            border: 1px solid #cfe0e6;
            border-radius: 13px;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .military-form-page .military-codemirror-shell:hover {
            border-color: #a9cbd2;
        }

        .military-form-page .military-codemirror-shell:focus-within {
            background: #fff;
            border-color: var(--section-color, var(--form-accent));
            box-shadow: 0 0 0 4px var(--section-shadow, rgba(0, 141, 157, .1));
        }

        .military-form-page .military-codemirror-toolbar {
            display: flex;
            align-items: center;
            gap: 3px;
            min-height: 40px;
            padding: 4px 7px;
            background: linear-gradient(180deg, #fbfdfd, #f4f8f9);
            border-bottom: 1px solid #dbe7eb;
        }

        .military-form-page .military-codemirror-toolbar__button {
            width: 31px;
            height: 31px;
            display: inline-grid;
            flex: 0 0 31px;
            place-items: center;
            padding: 0;
            color: #526c80;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
            transition: color .15s ease, background .15s ease, border-color .15s ease, transform .15s ease;
        }

        .military-form-page .military-codemirror-toolbar__button:hover,
        .military-form-page .military-codemirror-toolbar__button:focus-visible,
        .military-form-page .military-codemirror-toolbar__button.is-active {
            color: var(--section-color, var(--form-accent));
            background: var(--section-soft, #eaf8fa);
            border-color: var(--section-border, #cbe7ea);
            outline: none;
        }

        .military-form-page .military-codemirror-toolbar__button:active {
            transform: translateY(1px);
        }

        .military-form-page .military-codemirror-toolbar__button--danger {
            margin-left: auto;
        }

        .military-form-page .military-codemirror-toolbar__button--danger:hover,
        .military-form-page .military-codemirror-toolbar__button--danger:focus-visible {
            color: #b42318;
            background: #fff1f0;
            border-color: #ffd1cc;
        }

        .military-form-page .military-codemirror-toolbar__separator {
            width: 1px;
            height: 20px;
            flex: 0 0 1px;
            margin: 0 2px;
            background: #d7e3e7;
        }

        .military-form-page .military-codemirror {
            overflow: hidden;
            background: #fff;
        }

        .military-form-page .military-codemirror .cm-editor {
            min-height: 132px;
            color: #243b53;
            background: transparent;
            outline: none;
        }

        .military-form-page .military-codemirror .cm-scroller {
            max-height: 310px;
            overflow: auto;
            font-family: inherit;
            font-size: 12px;
            line-height: 1.55;
        }

        .military-form-page .military-codemirror .cm-content {
            min-height: 132px;
            padding: 12px 14px;
            caret-color: var(--section-color, var(--form-accent));
        }

        .military-form-page .military-codemirror .cm-line {
            padding: 0;
        }

        .military-form-page .military-codemirror .cm-placeholder {
            color: #8a99a9;
            font-style: normal;
        }

        .military-form-page .military-codemirror .cm-selectionBackground,
        .military-form-page .military-codemirror .cm-content ::selection {
            background: rgba(0, 141, 157, .18) !important;
        }

        body.has-expanded-medical-editor {
            overflow: hidden;
        }

        .military-form-page .military-codemirror-shell.is-expanded {
            position: fixed;
            inset: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            border-color: var(--section-color, var(--form-accent));
            box-shadow: 0 24px 80px rgba(18, 48, 70, .28);
        }

        .military-form-page .military-codemirror-shell.is-expanded .military-codemirror {
            min-height: 0;
            display: flex;
            flex: 1;
        }

        .military-form-page .military-codemirror-shell.is-expanded .cm-editor,
        .military-form-page .military-codemirror-shell.is-expanded .cm-scroller,
        .military-form-page .military-codemirror-shell.is-expanded .cm-content {
            min-height: 0;
            height: 100%;
        }

        .military-form-page .military-codemirror-shell.is-expanded .cm-editor {
            flex: 1;
        }

        .military-form-page .military-codemirror-shell.is-expanded .cm-scroller {
            max-height: none;
        }

        .military-form-page .military-patient-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(300px, .9fr);
            gap: 14px;
            align-items: stretch;
        }

        .military-form-page .military-selected-patient {
            position: relative;
            align-items: flex-start;
            min-height: 96px;
            overflow: hidden;
            background: linear-gradient(135deg, #edf9fa, #f8fcfc);
            border-color: #cbe7ea;
            border-radius: 14px;
            padding: 14px 42px 14px 14px;
            margin: 0;
        }

        .military-form-page .military-selected-patient::after {
            content: '\2713';
            position: absolute;
            right: 13px;
            top: 13px;
            width: 23px;
            height: 23px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--form-accent);
            border-radius: 50%;
            font-size: 12px;
            font-weight: 900;
        }

        .military-form-page .military-selected-patient.is-empty {
            background: #f8fafb;
            border-style: dashed;
        }

        .military-form-page .military-selected-patient.is-empty::after {
            content: '?';
            color: #8799ab;
            background: #e7eef2;
        }

        .military-form-page .military-selected-patient__avatar {
            width: 44px;
            height: 44px;
            border: 1px solid #d3e8eb;
            box-shadow: 0 4px 12px rgba(0, 141, 157, .08);
        }

        .military-form-page .military-selected-patient__copy strong {
            color: #243b53;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .military-form-page .military-selected-patient__copy span {
            font-size: 11px;
            line-height: 1.45;
        }

        .military-form-page .military-commissariat-search {
            position: relative;
        }

        .military-form-page .military-commissariat-control {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
        }

        .military-form-page .military-commissariat-search-button {
            min-width: 86px;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 13px;
            color: #fff;
            background: var(--form-accent);
            border: 1px solid var(--form-accent);
            border-radius: 11px;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease, opacity .15s ease;
        }

        .military-form-page .military-commissariat-search-button:hover {
            background: var(--form-accent-dark);
            border-color: var(--form-accent-dark);
        }

        .military-form-page .military-commissariat-search-button:disabled {
            opacity: .58;
            cursor: wait;
        }

        .military-form-page .military-commissariat-search .military-suggestions {
            z-index: 40;
        }

        .military-form-page .military-commissariat-search .military-suggestion__copy span {
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
            line-height: 1.4;
        }

        .military-form-page .military-commissariat-search .military-suggestion-message {
            padding: 13px;
            font-size: 11px;
            line-height: 1.45;
        }

        .military-form-page .military-commissariat-attribution a {
            color: inherit;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .military-form-page .military-subsection-title {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 9px;
            color: #53677d;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .055em;
            text-transform: uppercase;
            margin: 3px 0 -1px;
        }

        .military-form-page .military-subsection-title::after {
            content: '';
            height: 1px;
            flex: 1;
            background: #e2ebef;
        }

        .military-form-page .military-conclusion-field textarea {
            min-height: 174px;
            background: linear-gradient(180deg, #fff, #fbfefe);
        }

        .military-form-page .military-sidebar {
            top: 12px;
            gap: 11px;
        }

        .military-form-page .military-sidebar-card {
            border-color: #d8e6eb;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(26, 66, 87, .05);
            padding: 15px;
        }

        .military-form-page .military-sidebar-card__title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .military-form-page .military-sidebar-card__title > i {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: var(--form-accent);
            background: var(--form-accent-soft);
            border-radius: 9px;
        }

        .military-form-page .military-sidebar-card__title h3 {
            margin: 0;
        }

        .military-form-page .military-readiness__head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 9px;
        }

        .military-form-page .military-readiness__head h3 {
            margin: 0;
        }

        .military-form-page .military-readiness__value {
            color: var(--form-accent-dark);
            font-size: 18px;
            font-weight: 850;
        }

        .military-form-page .military-readiness__track {
            height: 7px;
            overflow: hidden;
            background: #e8eff2;
            border-radius: 999px;
            margin-bottom: 12px;
        }

        .military-form-page .military-readiness__bar {
            display: block;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, var(--form-accent), #62c9d2);
            border-radius: inherit;
            transition: width .22s ease;
        }

        .military-form-page .military-readiness__list {
            display: grid;
            gap: 7px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .military-form-page .military-readiness__list li {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #8494a6;
            font-size: 11px;
        }

        .military-form-page .military-readiness__list i {
            font-size: 13px;
        }

        .military-form-page .military-readiness__list li.is-complete {
            color: #08775f;
            font-weight: 750;
        }

        .military-form-page .military-sidebar-card--notice {
            color: #40546b;
            background: linear-gradient(145deg, #f4fbfc, #ffffff);
            border-color: #cfe5e8;
        }

        .military-form-page .military-sidebar-card--notice p {
            color: #66798e;
        }

        .military-form-page .military-save-card {
            background: linear-gradient(145deg, #ffffff, #f6fbfc);
            border-color: #cbe4e7;
        }

        .military-form-page .military-save-card__status {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #65788d;
            background: #f4f8fa;
            border: 1px solid #e0e9ed;
            border-radius: 10px;
            padding: 8px 10px;
            margin-bottom: 11px;
            font-size: 11px;
        }

        .military-form-page .military-save-card__status i {
            color: var(--form-accent);
        }

        .military-form-page .military-sidebar-actions .military-button {
            min-height: 40px;
        }

        .military-form-page .military-sidebar-actions .military-button--primary {
            box-shadow: 0 8px 18px rgba(0, 141, 157, .18);
        }

        .military-form-page [data-conclusion-helper].is-required {
            color: #9b6410;
            font-weight: 700;
        }

        @media (max-width: 1200px) {
            .military-form-page .military-form-layout {
                grid-template-columns: 1fr;
            }

            .military-form-page .military-sidebar {
                position: static;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .military-form-page .military-save-card {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 780px) {
            .military-form-page .military-codemirror-toolbar {
                overflow-x: auto;
                scrollbar-width: thin;
            }

            .military-form-page .military-codemirror-shell.is-expanded {
                inset: 8px;
            }

            .military-form-page .military-form-progress {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .military-form-page .military-form-progress__copy small {
                display: none;
            }

            .military-form-page .military-patient-layout,
            .military-form-page .military-sidebar {
                grid-template-columns: 1fr;
            }

            .military-form-page .military-form-card .military-card__head {
                padding: 13px 14px;
                margin: -15px -15px 14px;
            }

            .military-form-page .military-form-card .military-card__head p {
                display: none;
            }

            .military-form-page .military-form-card .military-card__icon {
                width: 38px;
                height: 38px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .military-form-page *,
            .military-form-page *::before,
            .military-form-page *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }

        /* VVK_FORM_COMMISSION_STYLE_V2 */
        #militaryCommissionFormRoot.military-form-page {
            --form-ink: #18324a;
            --form-muted: #71869c;
            --form-accent: #0095a4;
            --form-accent-dark: #007582;
            background:
                radial-gradient(circle at 4% 3%, rgba(0, 157, 172, .12), transparent 24rem),
                radial-gradient(circle at 94% 13%, rgba(74, 109, 219, .08), transparent 30rem),
                #f1f7f9;
            color: var(--form-ink);
        }

        .military-form-page .military-container {
            width: min(1580px, 100%);
        }

        .military-form-page .military-form-hero {
            min-height: 142px;
            overflow: hidden;
            background:
                linear-gradient(108deg, #ffffff 0%, #ffffff 55%, #edfafa 76%, #dff5f6 100%);
            border: 1px solid #d2e6ea;
            border-radius: 23px;
            box-shadow: 0 18px 42px rgba(29, 74, 94, .09);
            padding: 24px 28px;
            margin-bottom: 16px;
        }

        .military-form-page .military-form-hero::before {
            content: '';
            position: absolute;
            right: 52px;
            top: -112px;
            width: 270px;
            height: 270px;
            border: 52px solid rgba(0, 149, 164, .07);
            border-radius: 50%;
            pointer-events: none;
        }

        .military-form-page .military-form-hero::after {
            right: -54px;
            top: 42px;
            width: 170px;
            height: 170px;
            border-width: 32px;
            border-color: rgba(74, 109, 219, .045);
        }

        .military-form-page .military-back {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            font-size: 17px;
            box-shadow: 0 7px 18px rgba(29, 74, 94, .06);
        }

        .military-form-page .military-form-hero .military-hero__main {
            gap: 16px;
        }

        .military-form-page .military-form-hero .military-hero__icon {
            width: 72px;
            height: 72px;
            color: #fff;
            background: linear-gradient(145deg, #08a8b6, #008493);
            border: 0;
            border-radius: 21px;
            box-shadow: 0 14px 28px rgba(0, 132, 147, .22);
            font-size: 28px;
        }

        .military-form-page .military-form-hero .military-eyebrow {
            color: #008795;
            font-size: 11px;
            letter-spacing: .14em;
            margin-bottom: 4px;
        }

        .military-form-page .military-form-hero h1 {
            color: #172f47;
            font-size: clamp(27px, 2vw, 36px);
            letter-spacing: -.025em;
            line-height: 1.06;
        }

        .military-form-page .military-form-hero p {
            max-width: 820px;
            color: #778aa0;
            font-size: 14px;
            margin-top: 6px;
        }

        .military-form-page .military-form-chip {
            min-height: 29px;
            color: #38556d;
            background: #f5fafb;
            border-color: #d4e6ea;
            padding: 5px 11px;
            font-size: 11px;
        }

        .military-form-page .military-form-chip--status {
            color: #006f7c;
            background: #ddf5f6;
            border-color: #bde5e8;
        }

        .military-form-page .military-form-chip--status > i {
            font-size: 9px;
        }

        .military-form-page .military-form-hero .military-hero__actions {
            min-width: max-content;
        }

        .military-form-page .military-form-hero__save {
            min-height: 48px;
            color: #fff;
            background: linear-gradient(135deg, #06a4b1, #008895);
            border: 0;
            border-radius: 14px;
            padding: 10px 19px;
            font-size: 14px;
            box-shadow: 0 12px 24px rgba(0, 137, 151, .22);
        }

        .military-form-page .military-form-hero__save:hover {
            color: #fff;
            background: linear-gradient(135deg, #0697a4, #007784);
            box-shadow: 0 15px 28px rgba(0, 119, 132, .26);
        }

        .military-form-page .military-form-progress {
            gap: 10px;
            background: rgba(255, 255, 255, .96);
            border: 0;
            border-radius: 19px;
            box-shadow: 0 13px 32px rgba(29, 74, 94, .075);
            padding: 10px;
            margin-bottom: 18px;
        }

        .military-form-page [data-progress-link="patient"] {
            --step-color: #0097a7;
            --step-soft: #e4f7f8;
            --step-line: #bce5e8;
        }

        .military-form-page [data-progress-link="document"] {
            --step-color: #4b6fd6;
            --step-soft: #edf1ff;
            --step-line: #d4ddfb;
        }

        .military-form-page [data-progress-link="medical"] {
            --step-color: #735bc2;
            --step-soft: #f2effc;
            --step-line: #ddd6f5;
        }

        .military-form-page [data-progress-link="conclusion"] {
            --step-color: #0a966f;
            --step-soft: #e8f8f2;
            --step-line: #c9ecdf;
        }

        .military-form-page .military-form-progress__item {
            position: relative;
            min-height: 66px;
            color: #596f85 !important;
            background: #f8fafb;
            border: 1px solid #e4ecef;
            border-radius: 14px;
            padding: 10px 13px;
        }

        .military-form-page .military-form-progress__item::after {
            content: '';
            position: absolute;
            left: 13px;
            right: 13px;
            bottom: -1px;
            height: 3px;
            background: var(--step-color);
            border-radius: 999px 999px 0 0;
            opacity: .25;
        }

        .military-form-page .military-form-progress__item:hover,
        .military-form-page .military-form-progress__item.is-active {
            color: var(--step-color) !important;
            background: var(--step-soft);
            border-color: var(--step-line);
            transform: translateY(-1px);
        }

        .military-form-page .military-form-progress__item.is-active::after {
            opacity: 1;
        }

        .military-form-page .military-form-progress__number {
            width: 36px;
            height: 36px;
            color: var(--step-color);
            background: #fff;
            border-color: var(--step-line);
            border-radius: 11px;
        }

        .military-form-page .is-active .military-form-progress__number,
        .military-form-page .is-complete .military-form-progress__number {
            color: #fff;
            background: var(--step-color);
            border-color: var(--step-color);
            box-shadow: 0 7px 14px color-mix(in srgb, var(--step-color) 22%, transparent);
        }

        .military-form-page .military-form-progress__copy strong {
            color: inherit;
            font-size: 13px;
        }

        .military-form-page .military-form-progress__copy small {
            color: #8da0b2;
            font-size: 11px;
            margin-top: 3px;
        }

        .military-form-page .military-form-layout {
            grid-template-columns: minmax(0, 1fr) 326px;
            gap: 18px;
        }

        .military-form-page [data-form-section="patient"] {
            --section-color: #0097a7;
            --section-soft: #e5f7f8;
            --section-shadow: rgba(0, 151, 167, .12);
        }

        .military-form-page [data-form-section="document"] {
            --section-color: #4b6fd6;
            --section-soft: #edf1ff;
            --section-shadow: rgba(75, 111, 214, .12);
        }

        .military-form-page [data-form-section="medical"] {
            --section-color: #735bc2;
            --section-soft: #f2effc;
            --section-shadow: rgba(115, 91, 194, .12);
        }

        .military-form-page [data-form-section="conclusion"] {
            --section-color: #0a966f;
            --section-soft: #e8f8f2;
            --section-shadow: rgba(10, 150, 111, .12);
        }

        .military-form-page .military-form-card {
            overflow: visible;
            background: #fff;
            border: 1px solid #dbe7eb;
            border-radius: 21px;
            box-shadow: 0 15px 38px rgba(27, 68, 88, .075);
        }

        .military-form-page .military-form-card::before {
            top: -1px;
            left: 24px;
            width: 104px;
            height: 4px;
            background: linear-gradient(90deg, var(--section-color), color-mix(in srgb, var(--section-color) 45%, white));
            border-radius: 0 0 999px 999px;
        }

        .military-form-page .military-form-card .military-card__head {
            background: linear-gradient(105deg, #ffffff 0%, #ffffff 62%, var(--section-soft) 145%);
            border-bottom-color: #e7eef1;
            border-radius: 20px 20px 0 0;
            padding: 18px 20px 16px;
            margin: -18px -18px 18px;
        }

        .military-form-page .military-form-card .military-card__icon {
            width: 48px;
            height: 48px;
            color: #fff;
            background: var(--section-color);
            border: 0;
            border-radius: 14px;
            box-shadow: 0 9px 18px var(--section-shadow);
            font-size: 19px;
        }

        .military-form-page .military-form-card .military-card__head h2 {
            color: #1b344b;
            font-size: 18px;
        }

        .military-form-page .military-form-card .military-card__head p {
            color: #7d90a4;
            font-size: 12px;
        }

        .military-form-page .military-form-card .military-step {
            width: 38px;
            height: 38px;
            color: var(--section-color);
            background: var(--section-soft);
            border-color: color-mix(in srgb, var(--section-color) 25%, white);
            border-radius: 11px;
        }

        .military-form-page .military-form-card .military-field input:focus,
        .military-form-page .military-form-card .military-field select:focus,
        .military-form-page .military-form-card .military-field textarea:focus {
            border-color: var(--section-color);
            box-shadow: 0 0 0 4px var(--section-shadow);
        }

        .military-form-page .military-selected-patient {
            background: linear-gradient(135deg, var(--section-soft), #fff);
            border-color: color-mix(in srgb, var(--section-color) 28%, white);
        }

        .military-form-page .military-selected-patient::after {
            background: var(--section-color);
        }

        .military-form-page .military-selected-patient__avatar {
            color: var(--section-color);
            border-color: color-mix(in srgb, var(--section-color) 20%, white);
        }

        .military-form-page .military-subsection-title {
            color: var(--section-color);
        }

        .military-form-page .military-subsection-title::after {
            background: linear-gradient(90deg, color-mix(in srgb, var(--section-color) 28%, white), #e6edf0);
        }

        .military-form-page .military-sidebar {
            gap: 13px;
        }

        .military-form-page .military-sidebar-card {
            border-radius: 19px;
            box-shadow: 0 13px 32px rgba(27, 68, 88, .07);
            padding: 18px;
        }

        .military-form-page .military-readiness {
            color: #fff;
            background:
                radial-gradient(circle at 100% 0, rgba(255, 255, 255, .16), transparent 9rem),
                linear-gradient(145deg, #075b68, #008e9c);
            border: 0;
            box-shadow: 0 17px 36px rgba(0, 112, 124, .22);
        }

        .military-form-page .military-readiness__head h3,
        .military-form-page .military-readiness__value {
            color: #fff;
        }

        .military-form-page .military-readiness__value {
            font-size: 24px;
        }

        .military-form-page .military-readiness__track {
            height: 8px;
            background: rgba(255, 255, 255, .2);
        }

        .military-form-page .military-readiness__bar {
            background: linear-gradient(90deg, #ffffff, #93f1ea);
        }

        .military-form-page .military-readiness__list li {
            color: rgba(255, 255, 255, .68);
            font-size: 12px;
        }

        .military-form-page .military-readiness__list li.is-complete {
            color: #b8ffe8;
        }

        .military-form-page .military-sidebar-card--notice {
            color: #514621;
            background: linear-gradient(145deg, #fff9e9, #fffdf7);
            border-color: #eddfb8;
        }

        .military-form-page .military-sidebar-card--notice p {
            color: #766a46;
        }

        .military-form-page .military-sidebar-card--notice .military-sidebar-card__title > i {
            color: #a77500;
            background: #fff0c3;
        }

        .military-form-page .military-save-card {
            background: #fff;
            border-color: #cfe3e7;
        }

        .military-form-page .military-save-card .military-sidebar-card__title > i {
            color: #008894;
            background: #e2f6f7;
        }

        .military-form-page .military-save-card__status {
            color: #496278;
            background: #eef8f9;
            border-color: #d5ebed;
            padding: 10px 11px;
        }

        .military-form-page .military-sidebar-actions .military-button--primary {
            min-height: 46px;
            background: linear-gradient(135deg, #06a2af, #008895);
            border-radius: 12px;
            box-shadow: 0 11px 22px rgba(0, 137, 151, .2);
        }

        @media (max-width: 1200px) {
            .military-form-page .military-form-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 780px) {
            .military-form-page .military-form-hero {
                min-height: 0;
                padding: 17px;
                border-radius: 18px;
            }

            .military-form-page .military-form-hero .military-hero__icon {
                width: 52px;
                height: 52px;
                border-radius: 15px;
                font-size: 21px;
            }

            .military-form-page .military-form-hero h1 {
                font-size: 25px;
            }

            .military-form-page .military-form-hero .military-hero__actions {
                min-width: 0;
            }

            .military-form-page .military-form-hero__save {
                width: 100%;
            }

            .military-form-page .military-form-progress {
                gap: 7px;
                padding: 7px;
            }

            .military-form-page .military-form-progress__item {
                min-height: 56px;
                padding: 8px;
            }

            .military-form-page .military-form-card .military-card__head {
                margin: -15px -15px 15px;
            }
        }
    </style>

    @php
        $patientName = (string) ($initialPatient['full_name'] ?? '');
        $patientBirth = (string) ($initialPatient['birth_at_display'] ?? '');
        $patientDiagnosis = (string) ($initialPatient['diagnosis_label'] ?? '');
        $currentStatus = (string) old('status', $document->status);
        $currentStatusLabel = (string) ($statusOptions[$currentStatus] ?? $currentStatus);
        $currentDocumentType = (string) old('document_type', $document->document_type);
        $currentDocumentTypeLabel = (string) ($documentTypeOptions[$currentDocumentType] ?? $currentDocumentType);
        $showsRequestDetails = preg_match('/запрос/ui', $currentDocumentTypeLabel) === 1;
    @endphp

    <main id="militaryCommissionFormRoot"
          class="military-page military-form-page"
          data-patient-search-url="{{ route('api.patients.search') }}"
          data-commissariat-search-url="{{ config('services.nominatim.search_url') ?: 'https://nominatim.openstreetmap.org/search' }}">
        <script id="militaryInitialPatient" type="application/json">@json($initialPatient)</script>

        <div class="military-container">
            <header class="military-hero military-form-hero">
                <div class="military-hero__main">
                    <a class="military-back" href="{{ route('doctors.commissions.military.index') }}" title="Назад в журнал">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="military-hero__icon"><i class="bi bi-shield-check"></i></div>
                    <div class="military-form-hero__copy">
                        <div class="military-eyebrow">Военкомат / ВВК</div>
                        <h1>{{ $headingTitle }}</h1>
                        <p>Медицинские сведения и заключение врача для предоставления в военный комиссариат.</p>
                        <div class="military-form-hero__meta">
                            @if($isEditing && filled($document->document_number))
                                <span class="military-form-chip">
                                    <i class="bi bi-file-earmark-text"></i>
                                    {{ $document->document_number }}
                                </span>
                            @endif
                            <span class="military-form-chip military-form-chip--status" data-header-status>
                                <i class="bi bi-circle-fill"></i>
                                <span>{{ $currentStatusLabel }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="military-hero__actions">
                    <button class="military-button military-form-hero__save"
                            type="submit" form="militaryDocumentForm">
                        <i class="bi bi-check2-circle"></i> Сохранить документ
                    </button>
                    @if($isEditing)
                        <a class="military-button military-button--ghost"
                           href="{{ route('doctors.commissions.military.show', $document) }}">
                            <i class="bi bi-eye"></i> Просмотр
                        </a>
                        <a class="military-button military-button--secondary" target="_blank"
                           href="{{ route('doctors.commissions.military.print', $document) }}">
                            <i class="bi bi-printer"></i> Печать
                        </a>
                    @endif
                </div>
            </header>

            <nav class="military-form-progress" aria-label="Разделы формы">
                <a class="military-form-progress__item is-active"
                   href="#militarySectionPatient" data-progress-link="patient">
                    <span class="military-form-progress__number">01</span>
                    <span class="military-form-progress__copy">
                        <strong>Пациент</strong>
                        <small>Карта и диагноз</small>
                    </span>
                </a>
                <a class="military-form-progress__item"
                   href="#militarySectionDocument" data-progress-link="document">
                    <span class="military-form-progress__number">02</span>
                    <span class="military-form-progress__copy">
                        <strong>Документ</strong>
                        <small>Вид, дата и статус</small>
                    </span>
                </a>
                <a class="military-form-progress__item"
                   href="#militarySectionMedical" data-progress-link="medical">
                    <span class="military-form-progress__number">03</span>
                    <span class="military-form-progress__copy">
                        <strong>Медсведения</strong>
                        <small>Осмотр и лечение</small>
                    </span>
                </a>
                <a class="military-form-progress__item"
                   href="#militarySectionConclusion" data-progress-link="conclusion">
                    <span class="military-form-progress__number">04</span>
                    <span class="military-form-progress__copy">
                        <strong>Заключение</strong>
                        <small>Вывод и рекомендации</small>
                    </span>
                </a>
            </nav>

            @if($errors->any())
                <div class="military-alert military-alert--danger">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div>
                        <strong>Проверьте заполнение формы</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form id="militaryDocumentForm" method="POST"
                  action="{{ $isEditing
                      ? route('doctors.commissions.military.update', $document)
                      : route('doctors.commissions.military.store') }}"
                  data-military-form>
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                <div class="military-form-layout">
                    <div class="military-form-main">
                        <section id="militarySectionPatient"
                                 class="military-card military-form-card"
                                 data-form-section="patient">
                            <div class="military-card__head">
                                <span class="military-card__icon"><i class="bi bi-person-vcard"></i></span>
                                <div>
                                    <h2>Пациент</h2>
                                    <p>Диагноз берётся из карты и сохраняется в документе как исторический снимок.</p>
                                </div>
                                <span class="military-step">01</span>
                            </div>

                            <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}" data-patient-id>

                            <div class="military-patient-layout">
                                <div class="military-field military-patient-search">
                                    <label for="militaryPatientSearch">Поиск пациента <span class="required">*</span></label>
                                    <div class="military-control-wrap">
                                        <i class="bi bi-search"></i>
                                        <input id="militaryPatientSearch"
                                               class="{{ $errors->has('patient_id') ? 'is-invalid' : '' }}"
                                               type="search"
                                               value="{{ $patientName }}"
                                               placeholder="Начните вводить ФИО пациента"
                                               autocomplete="off"
                                               data-patient-search>
                                        <button class="military-clear {{ $initialPatient ? '' : 'd-none' }}"
                                                type="button" aria-label="Очистить пациента" data-patient-clear>
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div class="military-suggestions d-none" data-patient-suggestions></div>
                                    @error('patient_id')
                                    <small class="military-field-error">{{ $message }}</small>
                                    @else
                                        <small>Введите ФИО и выберите пациента. Enter добавит первый найденный результат.</small>
                                        @enderror
                                </div>

                                <div class="military-selected-patient {{ $initialPatient ? '' : 'is-empty' }}"
                                     data-patient-card aria-live="polite">
                                    <span class="military-selected-patient__avatar"><i class="bi bi-person"></i></span>
                                    <span class="military-selected-patient__copy">
                                        <strong data-patient-name>{{ $patientName !== '' ? $patientName : 'Пациент не выбран' }}</strong>
                                        <span data-patient-birth>{{ $patientBirth !== '' ? 'Дата рождения: ' . $patientBirth : 'Выберите пациента из поиска' }}</span>
                                        <span data-patient-diagnosis>{{ $patientDiagnosis !== '' ? 'Диагноз: ' . $patientDiagnosis : 'Диагноз: не установлен' }}</span>
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section id="militarySectionDocument"
                                 class="military-card military-form-card"
                                 data-form-section="document">
                            <div class="military-card__head">
                                <span class="military-card__icon"><i class="bi bi-file-earmark-medical"></i></span>
                                <div>
                                    <h2>Реквизиты документа</h2>
                                    <p>Вид, дата и статус. Реквизиты запроса появятся только для ответа военкомату.</p>
                                </div>
                                <span class="military-step">02</span>
                            </div>

                            <div class="military-grid military-grid--3">
                                <div class="military-field">
                                    <label for="documentDate">Дата документа <span class="required">*</span></label>
                                    <input id="documentDate" type="date" name="document_date" max="{{ today()->format('Y-m-d') }}"
                                           value="{{ old('document_date', $document->document_date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                                           class="{{ $errors->has('document_date') ? 'is-invalid' : '' }}" required>
                                    @error('document_date')<small class="military-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="military-field">
                                    <label for="documentType">Вид документа <span class="required">*</span></label>
                                    <select id="documentType" name="document_type"
                                            class="{{ $errors->has('document_type') ? 'is-invalid' : '' }}" required>
                                        @foreach($documentTypeOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                    data-has-request-details="{{ preg_match('/запрос/ui', (string) $label) === 1 ? '1' : '0' }}"
                                                    @selected(old('document_type', $document->document_type) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('document_type')<small class="military-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="military-field">
                                    <label for="documentStatus">Статус <span class="required">*</span></label>
                                    <select id="documentStatus" name="status"
                                            class="{{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $document->status) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')<small class="military-field-error">{{ $message }}</small>@enderror
                                    <small>Статус управляет обязательностью медицинского заключения.</small>
                                </div>

                                <div class="military-subsection-title"
                                     data-request-detail
                                     @if(!$showsRequestDetails) hidden @endif>
                                    Реквизиты входящего запроса
                                </div>

                                <div class="military-field military-commissariat-search"
                                     data-request-detail
                                     @if(!$showsRequestDetails) hidden @endif>
                                    <label for="requestingOrganization">Военный комиссариат, направивший запрос</label>
                                    <div class="military-commissariat-control">
                                        <div class="military-control-wrap">
                                            <i class="bi bi-search"></i>
                                            <input id="requestingOrganization"
                                                   type="search"
                                                   name="requesting_organization"
                                                   value="{{ old('requesting_organization', $document->requesting_organization) }}"
                                                   class="{{ $errors->has('requesting_organization') ? 'is-invalid' : '' }}"
                                                   placeholder="Название, город или адрес"
                                                   autocomplete="off"
                                                   aria-autocomplete="list"
                                                   aria-expanded="false"
                                                   data-commissariat-search>
                                            <button class="military-clear {{ old('requesting_organization', $document->requesting_organization) ? '' : 'd-none' }}"
                                                    type="button"
                                                    aria-label="Очистить выбранный военкомат"
                                                    data-commissariat-clear>
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <button class="military-commissariat-search-button"
                                                type="button"
                                                data-commissariat-submit>
                                            <i class="bi bi-search"></i>
                                            Найти
                                        </button>
                                    </div>
                                    <div class="military-suggestions d-none"
                                         role="listbox"
                                         data-commissariat-suggestions></div>
                                    @error('requesting_organization')
                                    <small class="military-field-error">{{ $message }}</small>
                                    @else
                                        <small class="military-commissariat-attribution">
                                            Введите название или город и нажмите «Найти». Данные ©
                                            <a href="https://www.openstreetmap.org/copyright"
                                               target="_blank" rel="noopener noreferrer">OpenStreetMap</a>.
                                        </small>
                                    @enderror
                                </div>

                                <div class="military-field"
                                     data-request-detail
                                     @if(!$showsRequestDetails) hidden @endif>
                                    <label for="requestNumber">Исходящий номер запроса</label>
                                    <input id="requestNumber" type="text" name="request_number"
                                           value="{{ old('request_number', $document->request_number) }}"
                                           class="{{ $errors->has('request_number') ? 'is-invalid' : '' }}"
                                           placeholder="Например, 123/ВВК"
                                           autocomplete="off">
                                    @error('request_number')
                                    <small class="military-field-error">{{ $message }}</small>
                                    @else
                                        <small>Укажите исходящий номер с бланка поступившего запроса.</small>
                                    @enderror
                                </div>

                                <div class="military-field"
                                     data-request-detail
                                     @if(!$showsRequestDetails) hidden @endif>
                                    <label for="requestDate">Дата запроса</label>
                                    <input id="requestDate" type="date" name="request_date" max="{{ today()->format('Y-m-d') }}"
                                           value="{{ old('request_date', $document->request_date?->format('Y-m-d')) }}"
                                           class="{{ $errors->has('request_date') ? 'is-invalid' : '' }}">
                                    @error('request_date')<small class="military-field-error">{{ $message }}</small>@enderror
                                </div>

                                <div class="military-field military-field--full"
                                     data-request-detail
                                     @if(!$showsRequestDetails) hidden @endif>
                                    <label for="purpose">Запрошенные медицинские сведения</label>
                                    <textarea id="purpose" name="purpose"
                                              class="{{ $errors->has('purpose') ? 'is-invalid' : '' }}"
                                              placeholder="Укажите основание запроса и перечень сведений, которые необходимо предоставить">{{ old('purpose', $document->purpose) }}</textarea>
                                    @error('purpose')<small class="military-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </section>

                        <section id="militarySectionMedical"
                                 class="military-card military-form-card"
                                 data-form-section="medical">
                            <div class="military-card__head">
                                <span class="military-card__icon"><i class="bi bi-journal-medical"></i></span>
                                <div>
                                    <h2>Медицинские сведения</h2>
                                    <p>Клинические данные, необходимые для ответа на запрос или направления на ВВК.</p>
                                </div>
                                <span class="military-step">03</span>
                            </div>

                            <div class="military-grid military-grid--2">
                                <div class="military-field">
                                    <label for="complaints">Жалобы</label>
                                    <textarea id="complaints" name="complaints"
                                              data-codemirror-medical
                                              placeholder="Актуальные жалобы пациента">{{ old('complaints', $document->complaints) }}</textarea>
                                </div>
                                <div class="military-field">
                                    <label for="anamnesis">Анамнез</label>
                                    <textarea id="anamnesis" name="anamnesis" placeholder="Анамнез заболевания и жизни">{{ old('anamnesis', $document->anamnesis) }}</textarea>
                                </div>
                                <div class="military-field">
                                    <label for="objectiveStatus">Объективный статус</label>
                                    <textarea id="objectiveStatus" name="objective_status" placeholder="Объективные данные на дату осмотра">{{ old('objective_status', $document->objective_status) }}</textarea>
                                </div>
                                <div class="military-field">
                                    <label for="examinationResults">Результаты обследований</label>
                                    <textarea id="examinationResults" name="examination_results"
                                              data-codemirror-medical
                                              placeholder="Лабораторные, инструментальные и иные исследования">{{ old('examination_results', $document->examination_results) }}</textarea>
                                </div>
                                <div class="military-field military-field--full">
                                    <label for="treatmentHistory">Сведения о лечении и динамике</label>
                                    <textarea id="treatmentHistory" name="treatment_history" placeholder="Проведённое лечение, эффект и динамика состояния">{{ old('treatment_history', $document->treatment_history) }}</textarea>
                                </div>
                            </div>
                        </section>

                        <section id="militarySectionConclusion"
                                 class="military-card military-form-card"
                                 data-form-section="conclusion">
                            <div class="military-card__head">
                                <span class="military-card__icon"><i class="bi bi-clipboard2-pulse"></i></span>
                                <div>
                                    <h2>Заключение врача</h2>
                                    <p>Медицинская формулировка без автоматического определения категории годности.</p>
                                </div>
                                <span class="military-step">04</span>
                            </div>

                            <div class="military-grid military-grid--2">
                                <div class="military-field military-field--full military-conclusion-field">
                                    <label for="medicalConclusion">
                                        Медицинское заключение
                                        <span class="required {{ $currentStatus === 'prepared' ? '' : 'd-none' }}"
                                              data-conclusion-required>*</span>
                                    </label>
                                    <textarea id="medicalConclusion" name="medical_conclusion" rows="7"
                                              class="{{ $errors->has('medical_conclusion') ? 'is-invalid' : '' }}"
                                              @if($currentStatus === 'prepared') required @endif
                                              placeholder="Сформулируйте медицинское заключение и значимые сведения">{{ old('medical_conclusion', $document->medical_conclusion) }}</textarea>
                                    @error('medical_conclusion')<small class="military-field-error">{{ $message }}</small>@enderror
                                    <small data-conclusion-helper
                                           class="{{ $currentStatus === 'prepared' ? 'is-required' : '' }}">
                                        {{ $currentStatus === 'prepared'
                                            ? 'Обязательно для статуса «Документ оформлен».'
                                            : 'Можно заполнить позже, пока документ сохраняется как черновик.' }}
                                    </small>
                                </div>
                                <div class="military-field">
                                    <label for="recommendations">Рекомендации</label>
                                    <textarea id="recommendations" name="recommendations" placeholder="Дополнительное обследование, наблюдение или лечение">{{ old('recommendations', $document->recommendations) }}</textarea>
                                </div>
                                <div class="military-field">
                                    <label for="notes">Примечания</label>
                                    <textarea id="notes" name="notes" placeholder="Служебные пояснения и особые отметки">{{ old('notes', $document->notes) }}</textarea>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="military-sidebar" aria-label="Состояние и сохранение документа">
                        <section class="military-sidebar-card military-readiness" data-readiness-card>
                            <div class="military-readiness__head">
                                <h3>Готовность формы</h3>
                                <strong class="military-readiness__value" data-readiness-value>0%</strong>
                            </div>
                            <div class="military-readiness__track" aria-hidden="true">
                                <span class="military-readiness__bar" data-readiness-bar></span>
                            </div>
                            <ul class="military-readiness__list">
                                <li data-readiness-item="patient">
                                    <i class="bi bi-circle"></i><span>Пациент выбран</span>
                                </li>
                                <li data-readiness-item="document">
                                    <i class="bi bi-circle"></i><span>Реквизиты документа</span>
                                </li>
                                <li data-readiness-item="medical">
                                    <i class="bi bi-circle"></i><span>Медицинские сведения</span>
                                </li>
                                <li data-readiness-item="conclusion">
                                    <i class="bi bi-circle"></i><span>Заключение врача</span>
                                </li>
                            </ul>
                        </section>

                        <section class="military-sidebar-card military-sidebar-card--notice">
                            <div class="military-sidebar-card__title">
                                <i class="bi bi-shield-exclamation"></i>
                                <h3>Важно</h3>
                            </div>
                            <p>Форма не определяет категорию годности к военной службе. Итоговую категорию устанавливает уполномоченная ВВК.</p>
                        </section>

                        <section class="military-sidebar-card military-sidebar-actions military-save-card">
                            <div class="military-sidebar-card__title">
                                <i class="bi bi-cloud-check"></i>
                                <h3>Сохранение</h3>
                            </div>
                            <div class="military-save-card__status">
                                <i class="bi bi-circle-fill"></i>
                                <span>Будет сохранено: <strong data-save-status>{{ $currentStatusLabel }}</strong></span>
                            </div>
                            <p>В черновике заключение можно оставить пустым. Для оформленного документа оно обязательно.</p>
                            <button class="military-button military-button--primary" type="submit" data-submit-button>
                                <i class="bi bi-check2-circle"></i> Сохранить документ
                            </button>
                            <a class="military-button military-button--ghost"
                               href="{{ route('doctors.commissions.military.index') }}">
                                <i class="bi bi-x-lg"></i> Отмена
                            </a>
                        </section>
                    </aside>
                </div>
            </form>
        </div>
    </main>

    <script>
        (() => {
            const root = document.getElementById('militaryCommissionFormRoot');
            if (!root) return;

            const form = root.querySelector('[data-military-form]');
            const searchInput = root.querySelector('[data-patient-search]');
            const patientIdInput = root.querySelector('[data-patient-id]');
            const clearButton = root.querySelector('[data-patient-clear]');
            const suggestions = root.querySelector('[data-patient-suggestions]');
            const patientCard = root.querySelector('[data-patient-card]');
            const patientName = root.querySelector('[data-patient-name]');
            const patientBirth = root.querySelector('[data-patient-birth]');
            const patientDiagnosis = root.querySelector('[data-patient-diagnosis]');
            const searchUrl = root.dataset.patientSearchUrl;
            const initialNode = document.getElementById('militaryInitialPatient');
            const documentTypeSelect = root.querySelector('#documentType');
            const requestDetailElements = [...root.querySelectorAll('[data-request-detail]')];
            const statusSelect = root.querySelector('#documentStatus');
            const conclusionInput = root.querySelector('#medicalConclusion');
            const conclusionRequired = root.querySelector('[data-conclusion-required]');
            const conclusionHelper = root.querySelector('[data-conclusion-helper]');
            const headerStatus = root.querySelector('[data-header-status] span');
            const saveStatus = root.querySelector('[data-save-status]');
            const readinessValue = root.querySelector('[data-readiness-value]');
            const readinessBar = root.querySelector('[data-readiness-bar]');
            const readinessItems = [...root.querySelectorAll('[data-readiness-item]')];
            const progressLinks = [...root.querySelectorAll('[data-progress-link]')];
            const formSections = [...root.querySelectorAll('[data-form-section]')];
            const submitButton = root.querySelector('[data-submit-button]');

            let selectedPatient = null;
            let resultItems = [];
            let debounceTimer = null;
            let requestController = null;

            try {
                selectedPatient = JSON.parse(initialNode?.textContent || 'null');
            } catch (_) {
                selectedPatient = null;
            }

            const normalizePatient = (item) => {
                const diagnosis = item?.diagnosis && typeof item.diagnosis === 'object'
                    ? item.diagnosis
                    : (item?.diagnose && typeof item.diagnose === 'object' ? item.diagnose : {});
                const fullName = String(item?.full_name || item?.text || item?.name || '').trim();
                const code = String(diagnosis?.code || item?.diagnosis_code || '').trim();
                const title = String(diagnosis?.title || diagnosis?.name || item?.diagnosis_title || '').trim();
                const diagnosisLabel = String(item?.diagnosis_label || '').trim()
                    || (code || title ? `${code}${code && title ? ' — ' : ''}${title}` : 'не установлен');

                return {
                    id: Number(item?.id || 0),
                    full_name: fullName,
                    text: fullName,
                    birth_at_display: String(item?.birth_at_display || item?.birth_at || '').trim(),
                    diagnosis: { code: code || null, title: title || null },
                    diagnosis_label: diagnosisLabel,
                };
            };

            const fieldValue = (name) => String(form.elements.namedItem(name)?.value || '').trim();
            const hasAnyValue = (names) => names.some((name) => fieldValue(name) !== '');

            const readinessState = () => ({
                patient: Boolean(patientIdInput.value),
                document: ['document_date', 'document_type', 'status']
                    .every((name) => fieldValue(name) !== ''),
                medical: hasAnyValue([
                    'complaints',
                    'anamnesis',
                    'objective_status',
                    'examination_results',
                    'treatment_history',
                ]),
                conclusion: hasAnyValue(['medical_conclusion', 'recommendations', 'notes']),
            });

            const updateReadiness = () => {
                const state = readinessState();
                const completed = Object.values(state).filter(Boolean).length;
                const percent = Math.round((completed / Object.keys(state).length) * 100);

                if (readinessValue) readinessValue.textContent = `${percent}%`;
                if (readinessBar) readinessBar.style.width = `${percent}%`;

                readinessItems.forEach((item) => {
                    const isComplete = Boolean(state[item.dataset.readinessItem]);
                    item.classList.toggle('is-complete', isComplete);
                    const icon = item.querySelector('i');
                    if (icon) icon.className = isComplete ? 'bi bi-check-circle-fill' : 'bi bi-circle';
                });

                progressLinks.forEach((link) => {
                    link.classList.toggle('is-complete', Boolean(state[link.dataset.progressLink]));
                });
            };

            const syncStatus = () => {
                if (!statusSelect || !conclusionInput) return;

                const isPrepared = statusSelect.value === 'prepared';
                const label = statusSelect.options[statusSelect.selectedIndex]?.text?.trim() || statusSelect.value;

                conclusionInput.required = isPrepared;
                conclusionRequired?.classList.toggle('d-none', !isPrepared);
                conclusionHelper?.classList.toggle('is-required', isPrepared);

                if (conclusionHelper) {
                    conclusionHelper.textContent = isPrepared
                        ? 'Обязательно для статуса «Документ оформлен».'
                        : 'Можно заполнить позже, пока документ сохраняется как черновик.';
                }

                if (headerStatus) headerStatus.textContent = label;
                if (saveStatus) saveStatus.textContent = label;
                updateReadiness();
            };

            const syncRequestDetails = () => {
                if (!documentTypeSelect) return;

                const selectedOption = documentTypeSelect.options[documentTypeSelect.selectedIndex];
                const shouldShow = selectedOption?.dataset?.hasRequestDetails === '1';

                requestDetailElements.forEach((element) => {
                    element.hidden = !shouldShow;

                    element.querySelectorAll('input, select, textarea').forEach((control) => {
                        control.disabled = !shouldShow;
                    });
                });
            };

            const setActiveSection = (sectionName) => {
                progressLinks.forEach((link) => {
                    link.classList.toggle('is-active', link.dataset.progressLink === sectionName);
                });
            };

            const renderPatient = (patient) => {
                if (!patient || !patient.id) {
                    patientCard.classList.add('is-empty');
                    patientName.textContent = 'Пациент не выбран';
                    patientBirth.textContent = 'Выберите пациента из поиска';
                    patientDiagnosis.textContent = 'Диагноз: не установлен';
                    clearButton.classList.add('d-none');
                    updateReadiness();
                    return;
                }

                patientCard.classList.remove('is-empty');
                patientName.textContent = patient.full_name || 'Пациент';
                patientBirth.textContent = patient.birth_at_display
                    ? `Дата рождения: ${patient.birth_at_display}`
                    : 'Дата рождения не указана';
                patientDiagnosis.textContent = `Диагноз: ${patient.diagnosis_label || 'не установлен'}`;
                clearButton.classList.remove('d-none');
                updateReadiness();
            };

            const closeSuggestions = () => {
                suggestions.classList.add('d-none');
                suggestions.replaceChildren();
                resultItems = [];
            };

            const clearSelection = (clearSearch = true) => {
                selectedPatient = null;
                patientIdInput.value = '';
                searchInput.setCustomValidity('');
                if (clearSearch) searchInput.value = '';
                renderPatient(null);
                closeSuggestions();
            };

            const selectPatient = (rawPatient) => {
                const patient = normalizePatient(rawPatient);
                if (!patient.id) return;

                selectedPatient = patient;
                patientIdInput.value = String(patient.id);
                searchInput.value = patient.full_name;
                searchInput.setCustomValidity('');
                renderPatient(patient);
                closeSuggestions();
            };

            const renderMessage = (message) => {
                const node = document.createElement('div');
                node.className = 'military-suggestion-message';
                node.textContent = message;
                suggestions.replaceChildren(node);
                suggestions.classList.remove('d-none');
            };

            const renderResults = (items) => {
                resultItems = items.map(normalizePatient).filter((item) => item.id && item.full_name);

                if (!resultItems.length) {
                    renderMessage('Пациенты не найдены');
                    return;
                }

                const fragment = document.createDocumentFragment();
                resultItems.forEach((patient, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'military-suggestion';
                    button.dataset.patientIndex = String(index);

                    const icon = document.createElement('span');
                    icon.className = 'military-suggestion__icon';
                    const iconNode = document.createElement('i');
                    iconNode.className = 'bi bi-person';
                    icon.append(iconNode);

                    const copy = document.createElement('span');
                    copy.className = 'military-suggestion__copy';
                    const name = document.createElement('strong');
                    name.textContent = patient.full_name;
                    const meta = document.createElement('span');
                    meta.textContent = [
                        patient.birth_at_display ? `д.р. ${patient.birth_at_display}` : '',
                        patient.diagnosis_label ? `Диагноз: ${patient.diagnosis_label}` : '',
                    ].filter(Boolean).join(' · ');
                    copy.append(name, meta);
                    button.append(icon, copy);
                    fragment.append(button);
                });

                suggestions.replaceChildren(fragment);
                suggestions.classList.remove('d-none');
            };

            const searchPatients = async (query) => {
                requestController?.abort();
                requestController = new AbortController();
                renderMessage('Поиск…');

                try {
                    const separator = searchUrl.includes('?') ? '&' : '?';
                    const response = await fetch(`${searchUrl}${separator}q=${encodeURIComponent(query)}`, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                        signal: requestController.signal,
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const payload = await response.json();
                    const items = Array.isArray(payload)
                        ? payload
                        : (Array.isArray(payload?.results) ? payload.results : (Array.isArray(payload?.data) ? payload.data : []));
                    renderResults(items);
                } catch (error) {
                    if (error.name !== 'AbortError') renderMessage('Не удалось выполнить поиск');
                }
            };

            if (selectedPatient) {
                selectedPatient = normalizePatient(selectedPatient);
                renderPatient(selectedPatient);
            } else {
                renderPatient(null);
            }

            syncStatus();
            syncRequestDetails();

            form.addEventListener('input', updateReadiness);
            form.addEventListener('change', (event) => {
                if (event.target === statusSelect) syncStatus();
                else if (event.target === documentTypeSelect) {
                    syncRequestDetails();
                    updateReadiness();
                }
                else updateReadiness();
            });

            progressLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const section = root.querySelector(link.getAttribute('href'));
                    if (!section) return;

                    event.preventDefault();
                    setActiveSection(link.dataset.progressLink);
                    section.scrollIntoView({
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                        block: 'start',
                    });
                });
            });

            if ('IntersectionObserver' in window) {
                const sectionObserver = new IntersectionObserver((entries) => {
                    const visible = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((left, right) => right.intersectionRatio - left.intersectionRatio)[0];

                    if (visible?.target?.dataset?.formSection) {
                        setActiveSection(visible.target.dataset.formSection);
                    }
                }, {
                    rootMargin: '-18% 0px -62% 0px',
                    threshold: [0, .1, .25],
                });

                formSections.forEach((section) => sectionObserver.observe(section));
            }

            searchInput.addEventListener('input', () => {
                searchInput.setCustomValidity('');
                const query = searchInput.value.trim();

                if (selectedPatient && query !== selectedPatient.full_name) {
                    clearSelection(false);
                }

                window.clearTimeout(debounceTimer);

                if (query.length < 1) {
                    closeSuggestions();
                    return;
                }

                debounceTimer = window.setTimeout(() => searchPatients(query), 250);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSuggestions();
                    return;
                }

                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (resultItems.length) selectPatient(resultItems[0]);
                }
            });

            suggestions.addEventListener('click', (event) => {
                const button = event.target.closest('[data-patient-index]');
                if (!button) return;
                selectPatient(resultItems[Number(button.dataset.patientIndex)]);
            });

            clearButton.addEventListener('click', () => {
                clearSelection(true);
                searchInput.focus();
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.military-patient-search')) closeSuggestions();
            });

            form.addEventListener('submit', (event) => {
                if (!patientIdInput.value) {
                    event.preventDefault();
                    searchInput.setCustomValidity('Выберите пациента из найденного списка.');
                    searchInput.reportValidity();
                    searchInput.focus();
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.setAttribute('aria-busy', 'true');
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Сохраняем…';
                }
            });
        })();
    </script>
@endsection
