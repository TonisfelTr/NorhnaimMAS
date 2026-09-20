<style data-commission-style>
/*
    |--------------------------------------------------------------------------
    | Врачебная комиссия
    |--------------------------------------------------------------------------
    | Стили намеренно изолированы внутри .commission-page, чтобы не влиять
    | на общий navbar страницы "Назначения".
    */

    .commission-page {
        --commission-primary: #078c9b;
        --commission-primary-dark: #056d78;
        --commission-primary-deep: #075d66;
        --commission-primary-soft: #e9f7f9;
        --commission-primary-soft-2: #f3fbfc;
        --commission-text: #243342;
        --commission-muted: #718096;
        --commission-muted-2: #98a2b3;
        --commission-border: #dfe8ed;
        --commission-border-soft: #eaf0f3;
        --commission-bg: #f5f8fa;
        --commission-card: #ffffff;
        --commission-danger: #b42318;
        --commission-danger-soft: #fff1f0;
        --commission-warning: #9a6700;
        --commission-warning-soft: #fff8e7;
        --commission-success: #087443;
        --commission-success-soft: #ecfdf3;
        --commission-shadow: 0 12px 34px rgba(16, 24, 40, .07);
        --commission-shadow-sm: 0 5px 18px rgba(16, 24, 40, .055);

        min-height: calc(100vh - 70px);
        padding: 28px 22px 46px;
        background:
            radial-gradient(circle at 7% 0%, rgba(7, 140, 155, .07), transparent 27rem),
            linear-gradient(180deg, #f8fbfc 0, var(--commission-bg) 250px);
        color: var(--commission-text);
        font-family: inherit;
    }

    .commission-page *,
    .commission-page *::before,
    .commission-page *::after {
        box-sizing: border-box;
    }

    .commission-container {
        width: min(1480px, 100%);
        margin: 0 auto;
        padding: 0;
    }

    /* ---------- hero ---------- */

    .commission-hero {
        position: relative;
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 22px;
        padding: 23px 25px;
        overflow: hidden;
        border: 1px solid #d9e8ec;
        border-radius: 22px;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(243, 251, 252, .97));
        box-shadow: var(--commission-shadow-sm);
    }

    .commission-hero::after {
        content: "";
        position: absolute;
        top: -80px;
        right: -55px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(7, 140, 155, .055);
        pointer-events: none;
    }

    .commission-back,
    .commission-hero__icon {
        position: relative;
        z-index: 1;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .commission-back {
        width: 42px;
        height: 42px;
        border: 1px solid var(--commission-border);
        border-radius: 12px;
        background: #fff;
        color: #667085;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(16, 24, 40, .04);
        transition: .16s ease;
    }

    .commission-back:hover {
        border-color: #b7d9df;
        background: var(--commission-primary-soft);
        color: var(--commission-primary-dark);
        transform: translateX(-2px);
    }

    .commission-hero__icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        background: linear-gradient(145deg, #0b9aaa, var(--commission-primary-dark));
        color: #fff;
        box-shadow: 0 9px 18px rgba(7, 140, 155, .18);
        font-size: 21px;
    }

    .commission-hero__body {
        position: relative;
        z-index: 1;
        min-width: 0;
        flex: 1;
    }

    .commission-eyebrow {
        display: inline-flex;
        align-items: center;
        margin-bottom: 5px;
        color: var(--commission-primary-dark);
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .commission-hero h1 {
        margin: 0;
        color: #1d2939;
        font-size: clamp(22px, 2vw, 29px);
        font-weight: 850;
        line-height: 1.18;
        letter-spacing: -.025em;
    }

    .commission-hero p {
        max-width: 790px;
        margin: 6px 0 0;
        color: var(--commission-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .commission-hero__actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 9px;
        margin-left: auto;
    }

    /* ---------- alerts ---------- */

    .commission-alert {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 18px;
        padding: 14px 16px;
        border: 1px solid;
        border-radius: 14px;
        font-size: 13px;
        line-height: 1.5;
    }

    .commission-alert > i {
        margin-top: 1px;
        font-size: 17px;
    }

    .commission-alert--success {
        border-color: #abefc6;
        background: var(--commission-success-soft);
        color: #05603a;
    }

    .commission-alert--danger {
        border-color: #fecdca;
        background: var(--commission-danger-soft);
        color: #912018;
    }

    .commission-alert ul {
        margin: 6px 0 0;
        padding-left: 18px;
    }

    /* ---------- workspace ---------- */

    .commission-form {
        display: block;
    }

    .commission-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        align-items: start;
        gap: 22px;
    }

    .commission-workspace__main {
        display: grid;
        min-width: 0;
        gap: 18px;
    }

    .commission-workspace__aside {
        position: sticky;
        top: 18px;
        min-width: 0;
    }

    /* ---------- cards ---------- */

    .commission-card,
    .commission-summary-card {
        border: 1px solid var(--commission-border);
        background: var(--commission-card);
        box-shadow: var(--commission-shadow-sm);
    }

    .commission-card {
        padding: 22px;
        border-radius: 18px;
    }

    .commission-card__head {
        display: grid;
        grid-template-columns: 43px minmax(0, 1fr) auto;
        align-items: center;
        gap: 13px;
        margin: -2px 0 20px;
        padding-bottom: 17px;
        border-bottom: 1px solid var(--commission-border-soft);
    }

    .commission-card__icon {
        display: grid;
        width: 43px;
        height: 43px;
        place-items: center;
        border-radius: 12px;
        background: var(--commission-primary-soft);
        color: var(--commission-primary-dark);
        font-size: 17px;
    }

    .commission-card__head h2 {
        margin: 0;
        color: #263746;
        font-size: 16px;
        font-weight: 850;
        line-height: 1.25;
    }

    .commission-card__head p {
        margin: 4px 0 0;
        color: var(--commission-muted);
        font-size: 11.5px;
        line-height: 1.45;
    }

    .commission-step {
        display: grid;
        min-width: 35px;
        height: 28px;
        padding: 0 9px;
        place-items: center;
        border: 1px solid #cce6ea;
        border-radius: 999px;
        background: #f6fbfc;
        color: var(--commission-primary-dark);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .04em;
    }

    /* ---------- fields ---------- */

    .commission-grid {
        display: grid;
        gap: 14px;
    }

    .commission-grid--3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .commission-grid--2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .commission-field,
    .commission-patient-search,
    .commission-doctor-search {
        position: relative;
        margin-bottom: 15px;
    }

    .commission-field:last-child,
    .commission-patient-search:last-child,
    .commission-doctor-search:last-child {
        margin-bottom: 0;
    }

    .commission-field > label,
    .commission-patient-search > label,
    .commission-doctor-search > label {
        display: block;
        margin: 0 0 7px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }

    .commission-page .required {
        color: #d92d20;
    }

    .commission-page input[type="text"],
    .commission-page input[type="search"],
    .commission-page input[type="datetime-local"],
    .commission-page select,
    .commission-page textarea {
        width: 100%;
        min-height: 43px;
        padding: 10px 12px;
        border: 1px solid #ccd9df;
        border-radius: 11px;
        outline: none;
        background: #fff;
        color: #273847;
        font: inherit;
        font-size: 13px;
        line-height: 1.35;
        transition:
            border-color .15s ease,
            box-shadow .15s ease,
            background .15s ease;
    }

    .commission-page textarea {
        min-height: 96px;
        resize: vertical;
        line-height: 1.55;
    }

    .commission-page input::placeholder,
    .commission-page textarea::placeholder {
        color: #98a6b4;
    }

    .commission-page input:hover,
    .commission-page select:hover,
    .commission-page textarea:hover {
        border-color: #adc4cc;
    }

    .commission-page input:focus,
    .commission-page select:focus,
    .commission-page textarea:focus {
        border-color: #4baab5;
        box-shadow: 0 0 0 3px rgba(7, 140, 155, .11);
    }

    .commission-page select:disabled,
    .commission-page input:disabled,
    .commission-page textarea:disabled {
        cursor: not-allowed;
        background: #f3f5f7;
        color: #98a2b3;
    }

    .commission-control-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .commission-control-wrap > i {
        position: absolute;
        left: 13px;
        z-index: 1;
        color: #8a9aa8;
        font-size: 13px;
        pointer-events: none;
    }

    .commission-control-wrap > input {
        padding-left: 38px !important;
        padding-right: 42px !important;
    }

    .commission-clear-button {
        position: absolute;
        right: 7px;
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #8997a5;
        font-size: 11px;
        transition: .14s ease;
    }

    .commission-clear-button:hover {
        background: #f1f4f6;
        color: #344054;
    }

    .commission-field small,
    .commission-patient-search small,
    .commission-doctor-search small {
        display: block;
        margin-top: 6px;
        color: var(--commission-muted-2);
        font-size: 10.5px;
        line-height: 1.4;
    }

    /* ---------- suggestions ---------- */

    .commission-suggestions {
        position: absolute;
        left: 0;
        right: 0;
        z-index: 80;
        max-height: 300px;
        margin-top: 6px;
        overflow-y: auto;
        border: 1px solid #d7e2e7;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 18px 36px rgba(16, 24, 40, .14);
    }

    .commission-suggestion {
        display: flex;
        width: 100%;
        align-items: flex-start;
        flex-direction: column;
        gap: 3px;
        padding: 10px 12px;
        border: 0;
        border-bottom: 1px solid #edf2f4;
        background: #fff;
        color: #344054;
        text-align: left;
        transition: .12s ease;
    }

    .commission-suggestion:last-child {
        border-bottom: 0;
    }

    .commission-suggestion:hover,
    .commission-suggestion:focus {
        outline: none;
        background: #f0f9fa;
    }

    .commission-suggestion__title {
        color: #263746;
        font-size: 12px;
        font-weight: 800;
    }

    .commission-suggestion__meta {
        color: #7b8b99;
        font-size: 10.5px;
        line-height: 1.35;
    }

    .commission-suggestion-state {
        padding: 14px;
        color: #7b8b99;
        font-size: 11.5px;
        text-align: center;
    }

    /* ---------- patient ---------- */

    .commission-patient-card {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 3px;
        padding: 14px;
        border: 1px solid #cfe6e9;
        border-radius: 14px;
        background: linear-gradient(135deg, #f4fbfc, #fff);
    }

    .commission-patient-card__avatar {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 12px;
        background: #dff3f5;
        color: var(--commission-primary-dark);
        font-size: 17px;
    }

    .commission-patient-card > div:last-child {
        display: grid;
        min-width: 0;
        gap: 3px;
    }

    .commission-patient-card strong {
        overflow: hidden;
        color: #233645;
        font-size: 13px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .commission-patient-card span {
        color: #718096;
        font-size: 10.8px;
        line-height: 1.35;
    }

    .commission-patient-card.is-empty {
        border-style: dashed;
        border-color: #d8e1e6;
        background: #fafcfd;
    }

    .commission-patient-card.is-empty .commission-patient-card__avatar {
        background: #f0f3f5;
        color: #98a2b3;
    }

    /* ---------- members ---------- */

    .commission-members {
        display: grid;
        gap: 11px;
        margin-top: 14px;
    }

    .commission-members-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 82px;
        margin-top: 14px;
        padding: 16px;
        border: 1px dashed #d6e1e6;
        border-radius: 13px;
        background: #fbfcfd;
        color: #8493a0;
        font-size: 11.5px;
        text-align: center;
    }

    .commission-members-empty i {
        color: #a1adb8;
        font-size: 18px;
    }

    .commission-member {
        padding: 14px;
        border: 1px solid #dce7eb;
        border-radius: 14px;
        background: #fbfdfe;
    }

    .commission-member__main {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .commission-member__identity {
        display: grid;
        min-width: 0;
        gap: 3px;
    }

    .commission-member__identity strong {
        color: #283a49;
        font-size: 12.5px;
        font-weight: 850;
    }

    .commission-member__identity span {
        color: #7c8c99;
        font-size: 10.5px;
    }

    .commission-member__remove {
        display: grid;
        width: 29px;
        height: 29px;
        flex: 0 0 29px;
        place-items: center;
        border: 1px solid #e2e8ec;
        border-radius: 9px;
        background: #fff;
        color: #8a98a5;
        font-size: 10px;
        transition: .14s ease;
    }

    .commission-member__remove:hover {
        border-color: #f7c8c4;
        background: #fff5f4;
        color: #b42318;
    }

    .commission-member__controls {
        display: grid;
        grid-template-columns: minmax(170px, 1.25fr) minmax(130px, .7fr) minmax(150px, 1fr);
        align-items: end;
        gap: 10px;
    }

    .commission-member__controls > label,
    .commission-member__opinion {
        display: grid;
        gap: 6px;
        margin: 0;
        color: #667085;
        font-size: 10.5px;
        font-weight: 750;
    }

    .commission-member__controls select {
        min-height: 39px;
        padding-block: 8px;
        font-size: 11.5px;
    }

    .commission-member__opinion {
        margin-top: 10px;
    }

    .commission-member__opinion textarea {
        min-height: 66px;
        font-size: 11.5px;
    }

    /* ---------- checkbox ---------- */

    .commission-check {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin: 4px 0 0;
        padding: 13px 14px;
        border: 1px solid #dce7eb;
        border-radius: 13px;
        background: #fafcfd;
        cursor: pointer;
    }

    .commission-check input[type="checkbox"] {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
        margin: 1px 0 0;
        accent-color: var(--commission-primary);
        cursor: pointer;
    }

    .commission-check > span {
        display: grid;
        min-width: 0;
        gap: 2px;
    }

    .commission-check strong {
        color: #344054;
        font-size: 11.5px;
        font-weight: 800;
    }

    .commission-check small {
        color: #7d8c99;
        font-size: 10.5px;
        font-weight: 500;
        line-height: 1.4;
    }

    .commission-check--compact {
        align-items: center !important;
        min-height: 39px;
        padding: 8px 10px;
        background: #fff;
    }

    .commission-check--compact > span {
        display: block;
        color: #51616e;
        font-size: 11px;
        font-weight: 750;
    }

    /* ---------- right summary ---------- */

    .commission-summary-card {
        padding: 18px;
        border-radius: 17px;
    }

    .commission-summary-card + .commission-summary-card {
        margin-top: 14px;
    }

    .commission-summary-card__head {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 15px;
    }

    .commission-summary-card__head > span {
        display: grid;
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        place-items: center;
        border-radius: 10px;
        background: var(--commission-primary-soft);
        color: var(--commission-primary-dark);
        font-size: 14px;
    }

    .commission-summary-card__head h3 {
        margin: 0;
        color: #2c3e4d;
        font-size: 13px;
        font-weight: 850;
        line-height: 1.3;
    }

    .commission-summary-card__head p {
        margin: 3px 0 0;
        color: #8795a2;
        font-size: 10px;
        line-height: 1.4;
    }

    .commission-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #edf2f4;
        color: #697986;
        font-size: 11px;
    }

    .commission-metric strong {
        display: grid;
        min-width: 29px;
        height: 25px;
        padding: 0 7px;
        place-items: center;
        border-radius: 8px;
        background: #f2f6f7;
        color: #314655;
        font-size: 11px;
        font-weight: 900;
    }

    .commission-summary-state {
        margin-top: 11px;
        padding: 9px 10px;
        border: 1px solid #e1e7ea;
        border-radius: 10px;
        background: #f6f8f9;
        color: #667085;
        font-size: 10.5px;
        font-weight: 800;
        line-height: 1.35;
        text-align: center;
    }

    .commission-summary-state.is-success {
        border-color: #abefc6;
        background: var(--commission-success-soft);
        color: var(--commission-success);
    }

    .commission-summary-state.is-danger {
        border-color: #fecdca;
        background: var(--commission-danger-soft);
        color: var(--commission-danger);
    }

    .commission-summary-state.is-warning {
        border-color: #fedf89;
        background: var(--commission-warning-soft);
        color: var(--commission-warning);
    }

    .commission-summary-divider {
        height: 1px;
        margin: 14px 0 4px;
        background: #e7edf0;
    }

    .commission-summary-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 14px;
        padding: 10px;
        border-radius: 10px;
        background: #f5f9fa;
        color: #71818e;
        font-size: 9.8px;
        line-height: 1.5;
    }

    .commission-summary-note i {
        margin-top: 1px;
        color: var(--commission-primary);
        font-size: 13px;
    }

    .commission-summary-card--law {
        border-color: #e4e9ec;
        box-shadow: none;
    }

    .commission-summary-card--law .commission-summary-card__head > span {
        background: #f1f4f6;
        color: #667085;
    }

    .commission-law-note {
        padding: 11px;
        border-radius: 10px;
        background: #f8fafb;
        color: #71818e;
        font-size: 10px;
        line-height: 1.5;
    }

    /* ---------- buttons ---------- */

    .commission-button {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 14px;
        border: 1px solid transparent;
        border-radius: 11px;
        font-size: 11.5px;
        font-weight: 850;
        line-height: 1;
        text-decoration: none;
        transition: .15s ease;
    }

    .commission-button--primary {
        border-color: var(--commission-primary);
        background: var(--commission-primary);
        color: #fff;
        box-shadow: 0 6px 13px rgba(7, 140, 155, .16);
    }

    .commission-button--primary:hover {
        border-color: var(--commission-primary-dark);
        background: var(--commission-primary-dark);
        color: #fff;
        transform: translateY(-1px);
    }

    .commission-button--secondary {
        border-color: #bedbe0;
        background: #fff;
        color: var(--commission-primary-dark);
    }

    .commission-button--secondary:hover {
        background: var(--commission-primary-soft);
        color: var(--commission-primary-deep);
    }

    .commission-button--ghost {
        border-color: #dce4e8;
        background: #fff;
        color: #596977;
    }

    .commission-button--ghost:hover {
        border-color: #c5d2d8;
        background: #f7f9fa;
        color: #344054;
    }

    /* ---------- footer ---------- */

    .commission-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 20px;
        padding: 15px 17px;
        border: 1px solid var(--commission-border);
        border-radius: 16px;
        background: rgba(255, 255, 255, .94);
        box-shadow: var(--commission-shadow-sm);
        backdrop-filter: blur(8px);
    }

    /* ---------- helpers ---------- */

    .commission-page .d-none {
        display: none !important;
    }

    /* ---------- responsive ---------- */

    @media (max-width: 1199.98px) {
        .commission-workspace {
            grid-template-columns: minmax(0, 1fr) 290px;
        }

        .commission-member__controls {
            grid-template-columns: 1fr 1fr;
        }

        .commission-check--compact {
            grid-column: span 1;
        }
    }

    @media (max-width: 991.98px) {
        .commission-page {
            padding: 20px 15px 36px;
        }

        .commission-workspace {
            grid-template-columns: 1fr;
        }

        .commission-workspace__aside {
            position: static;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .commission-summary-card + .commission-summary-card {
            margin-top: 0;
        }

        .commission-grid--3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .commission-grid--3 > :last-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .commission-page {
            padding: 14px 10px 28px;
        }

        .commission-hero {
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 11px;
            padding: 17px;
            border-radius: 17px;
        }

        .commission-back {
            width: 38px;
            height: 38px;
        }

        .commission-hero__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            font-size: 18px;
        }

        .commission-hero__body {
            flex-basis: calc(100% - 103px);
        }

        .commission-hero__actions {
            width: 100%;
            margin: 2px 0 0;
        }

        .commission-hero__actions .commission-button {
            width: 100%;
        }

        .commission-card {
            padding: 17px 14px;
            border-radius: 15px;
        }

        .commission-card__head {
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 10px;
        }

        .commission-card__icon {
            width: 38px;
            height: 38px;
        }

        .commission-grid--2,
        .commission-grid--3,
        .commission-workspace__aside,
        .commission-member__controls {
            grid-template-columns: 1fr;
        }

        .commission-grid--3 > :last-child {
            grid-column: auto;
        }

        .commission-member__controls {
            gap: 9px;
        }

        .commission-form-actions {
            position: sticky;
            bottom: 8px;
            z-index: 50;
            padding: 10px;
        }

        .commission-form-actions .commission-button {
            flex: 1;
        }
    }

    @media (max-width: 480px) {
        .commission-step {
            display: none;
        }

        .commission-card__head {
            grid-template-columns: 38px minmax(0, 1fr);
        }

        .commission-form-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .commission-form-actions .commission-button {
            width: 100%;
        }
    }

    @media print {
        .commission-page {
            padding: 0;
            background: #fff;
        }

        .commission-back,
        .commission-hero__actions,
        .commission-form-actions,
        .commission-patient-search,
        .commission-doctor-search,
        .commission-members-empty {
            display: none !important;
        }

        .commission-workspace {
            grid-template-columns: 1fr;
        }

        .commission-workspace__aside {
            position: static;
        }

        .commission-card,
        .commission-summary-card,
        .commission-hero {
            box-shadow: none;
        }
    }

    /* =========================================================
       CRUD: JOURNAL / SHOW
       ========================================================= */

    .commission-page .crud-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .commission-page .crud-toolbar__copy {
        min-width: 0;
    }

    .commission-page .crud-toolbar__title {
        margin: 0;
        color: #1d2939;
        font-size: 25px;
        font-weight: 850;
        letter-spacing: -.025em;
    }

    .commission-page .crud-toolbar__subtitle {
        margin: 5px 0 0;
        color: var(--commission-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .commission-page .crud-toolbar__actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }

    .commission-page .commission-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .commission-page .commission-stat {
        padding: 15px 16px;
        border: 1px solid var(--commission-border);
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(16, 24, 40, .04);
    }

    .commission-page .commission-stat__label {
        color: #7b8b98;
        font-size: 10.5px;
        font-weight: 750;
    }

    .commission-page .commission-stat__value {
        margin-top: 5px;
        color: #253746;
        font-size: 23px;
        font-weight: 900;
        line-height: 1;
    }

    .commission-page .commission-filter-card {
        margin-bottom: 18px;
        padding: 16px;
        border: 1px solid var(--commission-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(16, 24, 40, .04);
    }

    .commission-page .commission-filter-grid {
        display: grid;
        grid-template-columns: minmax(240px, 2fr) repeat(2, minmax(170px, 1fr)) auto;
        align-items: end;
        gap: 11px;
    }

    .commission-page .commission-filter-grid .commission-field {
        margin-bottom: 0;
    }

    .commission-page .commission-table-card {
        overflow: hidden;
        border: 1px solid var(--commission-border);
        border-radius: 17px;
        background: #fff;
        box-shadow: var(--commission-shadow-sm);
    }

    .commission-page .commission-table-wrap {
        overflow-x: auto;
    }

    .commission-page .commission-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .commission-page .commission-table th {
        padding: 11px 13px;
        border-bottom: 1px solid var(--commission-border);
        background: #f8fafb;
        color: #71808e;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .025em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .commission-page .commission-table td {
        padding: 12px 13px;
        border-bottom: 1px solid #edf2f4;
        color: #344054;
        font-size: 11.5px;
        vertical-align: middle;
    }

    .commission-page .commission-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .commission-page .commission-table tbody tr:hover {
        background: #fbfefe;
    }

    .commission-page .commission-table__primary {
        display: block;
        color: #273846;
        font-size: 12px;
        font-weight: 820;
        line-height: 1.35;
    }

    .commission-page .commission-table__secondary {
        display: block;
        margin-top: 2px;
        color: #8795a1;
        font-size: 10px;
        line-height: 1.35;
    }

    .commission-page .commission-status {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #667085;
        font-size: 9.5px;
        font-weight: 850;
        white-space: nowrap;
    }

    .commission-page .commission-status--draft {
        background: #f2f4f7;
        color: #667085;
    }

    .commission-page .commission-status--scheduled {
        background: #eef8fa;
        color: #057482;
    }

    .commission-page .commission-status--held {
        background: #fff8e7;
        color: #9a6700;
    }

    .commission-page .commission-status--protocol_ready {
        background: #ecfdf3;
        color: #087443;
    }

    .commission-page .commission-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
    }

    .commission-page .commission-icon-button {
        display: grid;
        width: 31px;
        height: 31px;
        place-items: center;
        border: 1px solid #dce5e9;
        border-radius: 9px;
        background: #fff;
        color: #667785;
        font-size: 11px;
        text-decoration: none;
        transition: .14s ease;
    }

    .commission-page .commission-icon-button:hover {
        border-color: #b9d8dd;
        background: #eef8fa;
        color: var(--commission-primary-dark);
    }

    .commission-page .commission-icon-button--danger:hover {
        border-color: #f4c7c3;
        background: #fff3f2;
        color: #b42318;
    }

    .commission-page .commission-empty {
        padding: 48px 22px;
        color: #8795a1;
        text-align: center;
    }

    .commission-page .commission-empty__icon {
        display: grid;
        width: 56px;
        height: 56px;
        margin: 0 auto 12px;
        place-items: center;
        border-radius: 16px;
        background: #eef8fa;
        color: var(--commission-primary-dark);
        font-size: 22px;
    }

    .commission-page .commission-empty strong {
        display: block;
        margin-bottom: 4px;
        color: #344054;
        font-size: 13px;
        font-weight: 850;
    }

    .commission-page .commission-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 13px 15px;
        border-top: 1px solid var(--commission-border);
        color: #7d8c98;
        font-size: 10.5px;
    }

    .commission-page .commission-pagination nav {
        margin-left: auto;
    }

    .commission-page .commission-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        align-items: start;
        gap: 18px;
    }

    .commission-page .commission-detail-stack {
        display: grid;
        gap: 16px;
    }

    .commission-page .commission-detail-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 11px;
    }

    .commission-page .commission-detail-item {
        padding: 12px 13px;
        border: 1px solid #e1e9ed;
        border-radius: 12px;
        background: #fbfcfd;
    }

    .commission-page .commission-detail-item--wide {
        grid-column: 1 / -1;
    }

    .commission-page .commission-detail-item__label {
        margin-bottom: 4px;
        color: #84929f;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .025em;
    }

    .commission-page .commission-detail-item__value {
        color: #2f4150;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
        white-space: pre-line;
    }

    .commission-page .commission-member-view {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 160px 110px 110px;
        gap: 10px;
        align-items: center;
        padding: 11px 12px;
        border: 1px solid #e0e8ec;
        border-radius: 12px;
        background: #fbfdfe;
    }

    .commission-page .commission-member-view + .commission-member-view {
        margin-top: 8px;
    }

    .commission-page .commission-member-view__name {
        color: #2d3f4e;
        font-size: 11.5px;
        font-weight: 820;
    }

    .commission-page .commission-member-view__meta {
        margin-top: 2px;
        color: #8593a0;
        font-size: 9.8px;
    }

    .commission-page .commission-delete-form {
        display: inline-flex;
        margin: 0;
    }

    @media (max-width: 1199.98px) {
        .commission-page .commission-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .commission-page .commission-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .commission-page .commission-filter-grid__actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 991.98px) {
        .commission-page .commission-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .commission-page .crud-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .commission-page .crud-toolbar__actions {
            width: 100%;
        }

        .commission-page .crud-toolbar__actions .commission-button {
            flex: 1;
        }

        .commission-page .commission-stats,
        .commission-page .commission-filter-grid,
        .commission-page .commission-detail-list {
            grid-template-columns: 1fr;
        }

        .commission-page .commission-detail-item--wide {
            grid-column: auto;
        }

        .commission-page .commission-member-view {
            grid-template-columns: 1fr 1fr;
        }
    }



    /* =========================================================
       INDEX / JOURNAL — ENHANCED VISUAL
       ========================================================= */

    .commission-index-page {
        padding-top: 24px;
        background:
            radial-gradient(circle at 10% -10%, rgba(7, 140, 155, .12), transparent 28rem),
            radial-gradient(circle at 95% 5%, rgba(63, 170, 181, .08), transparent 23rem),
            #f5f8fa;
    }

    .commission-index-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 18px;
        padding: 26px 28px;
        overflow: hidden;
        border: 1px solid #d9e7eb;
        border-radius: 22px;
        background:
            linear-gradient(135deg, rgba(255,255,255,.98) 0%, rgba(240,250,251,.98) 100%);
        box-shadow:
            0 16px 42px rgba(16, 24, 40, .07),
            inset 0 1px 0 rgba(255,255,255,.9);
    }

    .commission-index-hero::after {
        content: "";
        position: absolute;
        top: -90px;
        right: -60px;
        width: 290px;
        height: 290px;
        border-radius: 50%;
        background: rgba(7, 140, 155, .055);
        pointer-events: none;
    }

    .commission-index-hero__main {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 17px;
    }

    .commission-index-hero__icon {
        display: grid;
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        place-items: center;
        border-radius: 17px;
        background:
            linear-gradient(145deg, #12a8b8, #067b89);
        color: #fff;
        font-size: 24px;
        box-shadow: 0 12px 24px rgba(7, 140, 155, .22);
    }

    .commission-index-hero__eyebrow {
        margin-bottom: 5px;
        color: #078c9b;
        font-size: 10.5px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .commission-index-hero h1 {
        margin: 0;
        color: #1e2d3a;
        font-size: clamp(25px, 2vw, 31px);
        font-weight: 900;
        letter-spacing: -.035em;
        line-height: 1.1;
    }

    .commission-index-hero p {
        max-width: 740px;
        margin: 7px 0 0;
        color: #71808e;
        font-size: 12.5px;
        line-height: 1.55;
    }

    .commission-index-hero__actions {
        position: relative;
        z-index: 1;
        flex: 0 0 auto;
    }

    .commission-button--hero {
        min-height: 44px;
        padding-inline: 17px;
        border-radius: 12px;
        font-size: 12px;
    }

    .commission-index-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .commission-index-stat {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 86px;
        padding: 15px;
        border: 1px solid #dfe8ec;
        border-radius: 16px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 7px 22px rgba(16, 24, 40, .045);
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .commission-index-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 24, 40, .075);
    }

    .commission-index-stat__icon {
        display: grid;
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        place-items: center;
        border-radius: 13px;
        font-size: 17px;
    }

    .commission-index-stat__icon--all {
        background: #eaf7f9;
        color: #067d8b;
    }

    .commission-index-stat__icon--draft {
        background: #f2f4f7;
        color: #667085;
    }

    .commission-index-stat__icon--scheduled {
        background: #eef4ff;
        color: #3e63b8;
    }

    .commission-index-stat__icon--ready {
        background: #ecfdf3;
        color: #087443;
    }

    .commission-index-stat__copy {
        display: grid;
        min-width: 0;
        gap: 4px;
    }

    .commission-index-stat__copy span {
        color: #7a8995;
        font-size: 10.5px;
        font-weight: 750;
    }

    .commission-index-stat__copy strong {
        color: #253746;
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
    }

    .commission-index-filter {
        margin-bottom: 18px;
        padding: 17px;
        border: 1px solid #dfe8ec;
        border-radius: 17px;
        background: rgba(255,255,255,.97);
        box-shadow: 0 6px 20px rgba(16, 24, 40, .045);
    }

    .commission-index-filter__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 13px;
    }

    .commission-index-filter__head h2 {
        margin: 0;
        color: #2a3c4b;
        font-size: 13px;
        font-weight: 850;
    }

    .commission-index-filter__head p {
        margin: 3px 0 0;
        color: #8a98a4;
        font-size: 10.5px;
    }

    .commission-filter-reset {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #758592;
        font-size: 10.5px;
        font-weight: 800;
        text-decoration: none;
    }

    .commission-filter-reset:hover {
        color: #078c9b;
    }

    .commission-index-filter__grid {
        display: grid;
        grid-template-columns:
            minmax(290px, 2fr)
            minmax(150px, .9fr)
            minmax(150px, .9fr)
            minmax(145px, .8fr)
            minmax(145px, .8fr)
            auto;
        align-items: end;
        gap: 9px;
    }

    .commission-search-field {
        position: relative;
        display: flex;
        align-items: center;
    }

    .commission-search-field > i {
        position: absolute;
        left: 13px;
        color: #8494a1;
        font-size: 12px;
        pointer-events: none;
    }

    .commission-search-field input {
        padding-left: 37px !important;
    }

    .commission-index-filter__field {
        display: grid;
        gap: 5px;
    }

    .commission-index-filter__field label {
        color: #6f7f8b;
        font-size: 9.5px;
        font-weight: 800;
    }

    .commission-index-filter__grid input,
    .commission-index-filter__grid select {
        min-height: 40px !important;
        border-radius: 10px !important;
        font-size: 11.5px !important;
    }

    .commission-filter-submit {
        min-height: 40px;
        white-space: nowrap;
    }

    .commission-journal {
        overflow: hidden;
        border: 1px solid #dbe6ea;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(16, 24, 40, .055);
    }

    .commission-journal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 16px 18px;
        border-bottom: 1px solid #e7eef1;
        background:
            linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
    }

    .commission-journal__head h2 {
        margin: 0;
        color: #293b49;
        font-size: 14px;
        font-weight: 850;
    }

    .commission-journal__head p {
        margin: 3px 0 0;
        color: #8a98a4;
        font-size: 10px;
    }

    .commission-journal__legend {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #84929e;
        font-size: 9.5px;
    }

    .commission-journal__table-wrap {
        overflow-x: auto;
    }

    .commission-journal__table {
        width: 100%;
        min-width: 1120px;
        border-collapse: collapse;
    }

    .commission-journal__table th {
        padding: 10px 14px;
        border-bottom: 1px solid #e8eef1;
        background: #f8fafb;
        color: #7b8a96;
        font-size: 9.5px;
        font-weight: 850;
        letter-spacing: .035em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .commission-journal__table td {
        padding: 12px 14px;
        border-bottom: 1px solid #edf2f4;
        color: #344054;
        font-size: 11px;
        vertical-align: middle;
    }

    .commission-journal__table tbody tr {
        transition: background .12s ease;
    }

    .commission-journal__table tbody tr:hover {
        background: #fbfefe;
    }

    .commission-journal__table tbody tr:last-child td {
        border-bottom: 0;
    }

    .commission-protocol-cell,
    .commission-patient-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        color: inherit;
        text-decoration: none;
    }

    .commission-protocol-cell:hover strong,
    .commission-patient-cell:hover strong {
        color: #057482;
    }

    .commission-protocol-cell__icon {
        display: grid;
        width: 33px;
        height: 33px;
        flex: 0 0 33px;
        place-items: center;
        border-radius: 9px;
        background: #eef8fa;
        color: #078c9b;
        font-size: 13px;
    }

    .commission-protocol-cell > span:last-child,
    .commission-patient-cell__copy {
        display: grid;
        min-width: 0;
        gap: 2px;
    }

    .commission-protocol-cell strong,
    .commission-patient-cell strong,
    .commission-date-cell strong {
        color: #2c3e4c;
        font-size: 11.5px;
        font-weight: 830;
        line-height: 1.3;
    }

    .commission-protocol-cell small,
    .commission-patient-cell small,
    .commission-date-cell small {
        color: #91a0ab;
        font-size: 9.5px;
        line-height: 1.3;
    }

    .commission-patient-cell__avatar {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 10px;
        background:
            linear-gradient(145deg, #edf8fa, #dceff2);
        color: #067987;
        font-size: 10px;
        font-weight: 900;
    }

    .commission-patient-cell__copy {
        max-width: 230px;
    }

    .commission-patient-cell__copy strong,
    .commission-patient-cell__copy small {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .commission-date-cell {
        display: grid;
        grid-template-columns: auto auto;
        align-items: baseline;
        justify-content: start;
        column-gap: 5px;
    }

    .commission-date-cell > span {
        color: #667785;
        font-size: 10px;
        font-weight: 750;
    }

    .commission-date-cell small {
        grid-column: 1 / -1;
        margin-top: 2px;
    }

    .commission-agenda-cell {
        max-width: 300px;
        color: #5f707d;
        font-size: 10.5px;
        line-height: 1.45;
    }

    .commission-status {
        gap: 5px;
        min-height: 27px;
        padding-inline: 9px;
    }

    .commission-status i {
        font-size: 5px;
    }

    .commission-members-chip {
        display: inline-flex;
        min-width: 45px;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 8px;
        border: 1px solid #e0e8ec;
        border-radius: 9px;
        background: #fbfcfd;
        color: #667785;
        font-size: 10px;
    }

    .commission-members-chip strong {
        color: #344754;
        font-size: 10.5px;
    }

    .commission-journal__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 52px;
        padding: 11px 16px;
        border-top: 1px solid #e7eef1;
        background: #fbfcfd;
        color: #7f8e9a;
        font-size: 10px;
    }

    .commission-journal__footer strong {
        color: #445663;
    }

    .commission-journal__pagination {
        margin-left: auto;
    }

    .commission-journal__pagination nav {
        margin: 0;
    }

    .commission-empty--journal {
        padding: 58px 24px;
    }

    .commission-empty__visual span {
        display: grid;
        width: 62px;
        height: 62px;
        margin: 0 auto 14px;
        place-items: center;
        border-radius: 18px;
        background:
            linear-gradient(145deg, #eef9fa, #dff1f4);
        color: #078c9b;
        font-size: 24px;
    }

    .commission-empty--journal h3 {
        margin: 0;
        color: #344754;
        font-size: 15px;
        font-weight: 850;
    }

    .commission-empty--journal p {
        max-width: 430px;
        margin: 6px auto 0;
        color: #8796a2;
        font-size: 11px;
        line-height: 1.5;
    }

    .commission-empty__actions {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 16px;
    }

    @media (max-width: 1280px) {
        .commission-index-filter__grid {
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
        }

        .commission-search-field {
            grid-column: span 2;
        }

        .commission-filter-submit {
            grid-column: span 1;
        }
    }

    @media (max-width: 991.98px) {
        .commission-index-stats {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .commission-index-filter__grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .commission-search-field {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .commission-index-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 20px;
        }

        .commission-index-hero__actions,
        .commission-index-hero__actions .commission-button {
            width: 100%;
        }

        .commission-index-stats,
        .commission-index-filter__grid {
            grid-template-columns: 1fr;
        }

        .commission-search-field,
        .commission-filter-submit {
            grid-column: auto;
        }

        .commission-index-filter__head,
        .commission-journal__head,
        .commission-journal__footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .commission-journal__legend {
            display: none;
        }

        .commission-empty__actions {
            align-items: stretch;
            flex-direction: column;
        }
    }

</style>
