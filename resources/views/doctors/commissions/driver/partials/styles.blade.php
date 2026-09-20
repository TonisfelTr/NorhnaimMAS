<style>
    .driver-page {
        --driver-ink: #1e3148;
        --driver-muted: #8292a8;
        --driver-line: #dbe6ec;
        --driver-soft: #f5fafb;
        --driver-accent: #008796;
        --driver-accent-dark: #006d7a;
        --driver-accent-soft: #e9f7f8;
        --driver-danger: #bf4242;
        --driver-warning: #9a6900;
        --driver-success: #007a58;
        color: var(--driver-ink);
        padding: 18px 16px 42px;
        background: linear-gradient(180deg, #f6fbfc 0, #f8fafb 38rem, #fff 100%);
    }

    .driver-container {
        width: min(1420px, 100%);
        margin: 0 auto;
    }

    .driver-hero,
    .driver-stat,
    .driver-panel,
    .driver-card,
    .driver-sidebar-card,
    .driver-table-card {
        border: 1px solid var(--driver-line);
        background: #fff;
        box-shadow: 0 8px 26px rgba(31, 65, 85, .045);
    }

    .driver-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 14px;
        padding: 20px 22px;
        border-radius: 20px;
    }

    .driver-hero__main,
    .driver-hero__actions,
    .driver-actions,
    .driver-patient-summary {
        display: flex;
        align-items: center;
    }

    .driver-hero__main { gap: 14px; min-width: 0; }
    .driver-hero__actions { gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .driver-hero__copy { min-width: 0; }
    .driver-hero__icon {
        display: grid;
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        place-items: center;
        border-radius: 16px;
        color: var(--driver-accent);
        background: var(--driver-accent-soft);
        font-size: 23px;
    }

    .driver-eyebrow {
        margin-bottom: 4px;
        color: var(--driver-accent);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .driver-hero h1 {
        margin: 0;
        color: var(--driver-ink);
        font-size: clamp(23px, 2vw, 31px);
        font-weight: 800;
        line-height: 1.12;
    }

    .driver-hero p {
        margin: 5px 0 0;
        color: var(--driver-muted);
        font-size: 13px;
        line-height: 1.4;
    }

    .driver-button {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 14px;
        border: 1px solid transparent;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 750;
        line-height: 1;
        text-decoration: none !important;
        cursor: pointer;
        transition: .15s ease;
    }

    .driver-button:hover { transform: translateY(-1px); }
    .driver-button--primary { color: #fff !important; background: var(--driver-accent); }
    .driver-button--primary:hover { background: var(--driver-accent-dark); }
    .driver-button--secondary { color: var(--driver-accent) !important; border-color: #cbe6e9; background: var(--driver-accent-soft); }
    .driver-button--ghost { color: var(--driver-ink) !important; border-color: var(--driver-line); background: #fff; }
    .driver-button--danger { color: var(--driver-danger) !important; border-color: #efd5d5; background: #fff; }

    .driver-back {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        place-items: center;
        border: 1px solid var(--driver-line);
        border-radius: 12px;
        color: var(--driver-ink) !important;
        background: #fff;
        text-decoration: none !important;
    }

    .driver-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin: 0 0 14px;
        padding: 12px 14px;
        border: 1px solid;
        border-radius: 14px;
        font-size: 13px;
    }
    .driver-alert--success { color: #087252; border-color: #cbeee0; background: #edfbf5; }
    .driver-alert--danger { color: #a33c3c; border-color: #f0d2d2; background: #fff4f4; }
    .driver-alert--warning { color: #755711; border-color: #eedda8; background: #fff9e9; }
    .driver-alert--neutral { color: #46616d; border-color: #d8e7ea; background: #f5fafb; }
    .driver-alert ul { margin: 5px 0 0; padding-left: 18px; }

    .driver-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .driver-stat {
        display: flex;
        min-height: 82px;
        align-items: center;
        gap: 12px;
        padding: 15px 17px;
        border-radius: 18px;
    }

    .driver-stat__icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 13px;
        color: var(--driver-accent);
        background: var(--driver-accent-soft);
        font-size: 18px;
    }
    .driver-stat__copy span { display: block; color: var(--driver-muted); font-size: 12px; }
    .driver-stat__copy strong { display: block; margin-top: 2px; color: var(--driver-ink); font-size: 22px; line-height: 1.05; }

    .driver-panel {
        margin-bottom: 14px;
        padding: 17px 18px;
        border-radius: 18px;
    }
    .driver-panel__head { display: flex; justify-content: space-between; gap: 14px; margin-bottom: 12px; }
    .driver-panel__head h2 { margin: 0; font-size: 17px; font-weight: 800; }
    .driver-panel__head p { margin: 3px 0 0; color: var(--driver-muted); font-size: 12px; }
    .driver-filter-reset { color: var(--driver-accent); font-size: 12px; font-weight: 750; text-decoration: none; white-space: nowrap; }

    .driver-filter-grid {
        display: grid;
        grid-template-columns: minmax(220px, 1.5fr) repeat(3, minmax(145px, .8fr)) repeat(2, minmax(135px, .72fr)) auto;
        gap: 9px;
        align-items: end;
    }

    .driver-field { min-width: 0; }
    .driver-field label {
        display: block;
        margin: 0 0 5px;
        color: #45566d;
        font-size: 11px;
        font-weight: 750;
    }
    .driver-field .required { color: #d43838; }
    .driver-field input,
    .driver-field select,
    .driver-field textarea,
    .driver-search-input {
        width: 100%;
        color: var(--driver-ink);
        border: 1px solid #cbdbe3;
        border-radius: 11px;
        outline: none;
        background: #fff;
        font-size: 13px;
        transition: .15s ease;
    }
    .driver-field input,
    .driver-field select,
    .driver-search-input {
        min-height: 39px;
        padding: 7px 10px;
    }
    .driver-field textarea {
        min-height: 96px;
        padding: 9px 10px;
        line-height: 1.45;
        resize: vertical;
    }
    .driver-field textarea[rows="3"] { min-height: 84px; }
    .driver-field textarea[rows="4"] { min-height: 104px; }
    .driver-field input:focus,
    .driver-field select:focus,
    .driver-field textarea:focus,
    .driver-search-input:focus {
        border-color: var(--driver-accent);
        box-shadow: 0 0 0 3px rgba(0, 135, 150, .09);
    }
    .driver-field small { display: block; margin-top: 4px; color: var(--driver-muted); font-size: 10px; line-height: 1.35; }
    .driver-field-error { color: #b53d3d !important; font-weight: 650; }
    .driver-field .is-invalid { border-color: #d76666; }

    .driver-search-wrap,
    .driver-control-wrap { position: relative; }
    .driver-search-wrap > i,
    .driver-control-wrap > i {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #8ea0b3;
    }
    .driver-search-wrap .driver-search-input,
    .driver-control-wrap input { padding-left: 36px; }

    .driver-table-card { overflow: hidden; border-radius: 18px; }
    .driver-table-wrap { overflow-x: auto; }
    .driver-table { width: 100%; min-width: 1040px; border-collapse: collapse; }
    .driver-table th {
        padding: 11px 12px;
        border-bottom: 1px solid var(--driver-line);
        color: #71839a;
        background: #f7fafb;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .03em;
        text-align: left;
        text-transform: uppercase;
    }
    .driver-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #e8eff3;
        vertical-align: middle;
        font-size: 12px;
    }
    .driver-table tr:last-child td { border-bottom: 0; }
    .driver-table tbody tr:hover { background: #fbfdfd; }
    .driver-table strong { color: var(--driver-ink); }
    .driver-table__muted { display: block; margin-top: 2px; color: var(--driver-muted); font-size: 11px; line-height: 1.35; }

    .driver-patient-summary { min-width: 220px; gap: 9px; }
    .driver-patient-summary__avatar {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 11px;
        color: var(--driver-accent);
        background: var(--driver-accent-soft);
        font-weight: 800;
    }
    .driver-patient-summary__copy { min-width: 0; }
    .driver-patient-summary__copy strong,
    .driver-patient-summary__copy span {
        display: block;
        max-width: 270px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .driver-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }
    .driver-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .driver-badge--draft { color: var(--driver-warning); background: #fff5df; }
    .driver-badge--prepared { color: var(--driver-success); background: #e8f9f2; }
    .driver-badge--referred { color: #865b0a; background: #fff4d7; }
    .driver-badge--blocked { color: #a33c3c; background: #fff0f0; }

    .driver-actions { justify-content: flex-end; gap: 6px; }
    .driver-action {
        position: relative;
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid var(--driver-line);
        border-radius: 10px;
        color: #52708a;
        background: #fff;
        text-decoration: none !important;
        cursor: pointer;
    }
    .driver-action:hover { color: var(--driver-accent); border-color: #acd7dc; background: #f4fbfb; }
    .driver-action--danger:hover { color: var(--driver-danger); border-color: #e7bcbc; background: #fff7f7; }
    .driver-action--disabled { color: #b6c1c8; background: #f7f9fa; cursor: not-allowed; pointer-events: none; }
    .driver-action-form { margin: 0; }

    .driver-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 13px 15px;
        border-top: 1px solid var(--driver-line);
        color: var(--driver-muted);
        font-size: 11px;
    }

    .driver-empty { padding: 44px 22px; text-align: center; }
    .driver-empty i { display: block; margin-bottom: 10px; color: #a8c9cd; font-size: 38px; }
    .driver-empty h3 { margin: 0 0 5px; font-size: 18px; }
    .driver-empty p { margin: 0 0 15px; color: var(--driver-muted); }

    .driver-form-layout { display: grid; grid-template-columns: minmax(0, 1fr) 290px; gap: 14px; align-items: start; }
    .driver-form-main { display: grid; gap: 14px; }
    .driver-card { padding: 18px; border-radius: 18px; }
    .driver-card__head {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 14px;
        padding-bottom: 13px;
        border-bottom: 1px solid #e8eff3;
    }
    .driver-card__icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 12px;
        color: var(--driver-accent);
        background: var(--driver-accent-soft);
        font-size: 16px;
    }
    .driver-card__head h2 { margin: 0; font-size: 17px; font-weight: 800; }
    .driver-card__head p { margin: 2px 0 0; color: var(--driver-muted); font-size: 11px; }
    .driver-step { margin-left: auto; padding: 5px 8px; border-radius: 999px; color: var(--driver-accent); background: var(--driver-accent-soft); font-size: 10px; font-weight: 800; }

    .driver-grid { display: grid; gap: 10px 12px; }
    .driver-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .driver-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .driver-field--full { grid-column: 1 / -1; }

    .driver-patient-search { position: relative; }
    .driver-clear {
        position: absolute;
        z-index: 3;
        top: 50%;
        right: 7px;
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        transform: translateY(-50%);
        border: 0;
        border-radius: 8px;
        color: #8495a8;
        background: transparent;
        cursor: pointer;
    }
    .driver-clear:hover { color: var(--driver-danger); background: #fff2f2; }
    .driver-suggestions {
        position: absolute;
        z-index: 50;
        top: calc(100% + 5px);
        right: 0;
        left: 0;
        max-height: 300px;
        overflow-y: auto;
        padding: 5px;
        border: 1px solid var(--driver-line);
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(29, 58, 78, .16);
    }
    .driver-suggestion {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 9px;
        padding: 9px;
        border: 0;
        border-radius: 10px;
        color: var(--driver-ink);
        background: transparent;
        text-align: left;
        cursor: pointer;
    }
    .driver-suggestion:hover { background: var(--driver-accent-soft); }
    .driver-suggestion__icon { display: grid; width: 32px; height: 32px; flex: 0 0 32px; place-items: center; border: 1px solid #d4e8ea; border-radius: 10px; color: var(--driver-accent); background: #fff; }
    .driver-suggestion__copy { min-width: 0; }
    .driver-suggestion__copy strong { display: block; font-size: 12px; }
    .driver-suggestion__copy span { display: block; margin-top: 2px; color: var(--driver-muted); font-size: 10px; }
    .driver-suggestion-message { padding: 11px; color: var(--driver-muted); font-size: 11px; text-align: center; }

    .driver-selected-patient {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(250px, .75fr);
        gap: 9px;
        margin-top: 10px;
    }
    .driver-selected-box {
        display: flex;
        min-height: 82px;
        align-items: center;
        gap: 14px;
        padding: 13px 15px;
        border: 1px solid #dce8eb;
        border-radius: 13px;
        background: #f8fbfc;
    }
    .driver-selected-box__icon {
        display: grid;
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        place-items: center;
        border: 1px solid #e5eef1;
        border-radius: 15px;
        color: var(--driver-accent);
        background: #fff;
        font-size: 22px;
        box-shadow: 0 5px 14px rgba(31, 65, 85, .04);
    }
    .driver-selected-box strong { display: block; font-size: 13px; }
    .driver-selected-box > div > span {
        display: block;
        margin-top: 3px;
        color: var(--driver-muted);
        font-size: 11px;
    }
    .driver-selected-box .driver-selected-box__icon {
        margin-top: 0;
        color: var(--driver-accent);
        font-size: 28px;
        line-height: 1;
    }
    .driver-selected-box__icon i {
        display: block;
        font-size: inherit;
        line-height: 1;
    }

    .driver-section-caption {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 15px 0 8px;
        color: #566d7c;
        font-size: 11px;
        font-weight: 800;
    }
    .driver-section-caption::after { content: ''; height: 1px; flex: 1; background: #dbe6ea; }
    .driver-section-caption i { color: var(--driver-accent); }

    .driver-check-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .driver-check-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 8px 10px;
        border: 1px solid #dce7ea;
        border-radius: 11px;
        background: #fff;
        cursor: pointer;
    }
    .driver-check-card input { width: 16px; height: 16px; flex: 0 0 16px; accent-color: var(--driver-accent); }
    .driver-check-card span { color: #405b68; font-size: 11px; line-height: 1.25; }
    .driver-check-card:has(input:checked) { border-color: #abd9dd; background: #f1fbfc; }

    .driver-check-card[data-driver-tooltip]::before,
    .driver-check-card[data-driver-tooltip]::after {
        position: absolute;
        z-index: 120;
        left: 50%;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .14s ease, transform .14s ease, visibility .14s ease;
    }
    .driver-check-card[data-driver-tooltip]::after {
        content: attr(data-driver-tooltip);
        bottom: calc(100% + 10px);
        width: max-content;
        max-width: min(330px, calc(100vw - 36px));
        padding: 8px 10px;
        transform: translate(-50%, 4px);
        border-radius: 9px;
        color: #fff;
        background: #20344a;
        box-shadow: 0 10px 26px rgba(24, 45, 63, .20);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        text-align: left;
        white-space: normal;
    }
    .driver-check-card[data-driver-tooltip]::before {
        content: '';
        bottom: calc(100% + 4px);
        width: 0;
        height: 0;
        transform: translate(-50%, 4px);
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        border-top: 6px solid #20344a;
    }
    .driver-check-card[data-driver-tooltip]:hover::before,
    .driver-check-card[data-driver-tooltip]:hover::after,
    .driver-check-card[data-driver-tooltip]:focus-within::before,
    .driver-check-card[data-driver-tooltip]:focus-within::after {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, 0);
    }


    .driver-button.is-disabled,
    .driver-button:disabled {
        opacity: .48;
        cursor: not-allowed;
        pointer-events: none;
        transform: none !important;
        filter: grayscale(.12);
    }

    .driver-contra-assessment {
        margin-top: 10px;
        padding: 12px;
        border: 1px solid #d9e6ea;
        border-radius: 14px;
        background: #fbfdfd;
    }
    .driver-contra-assessment__intro {
        margin: 0 0 9px;
        color: #5e7480;
        font-size: 10.5px;
        line-height: 1.45;
    }
    .driver-contra-assessment__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    .driver-contra-assessment__option {
        display: flex;
        min-width: 0;
        align-items: flex-start;
        gap: 8px;
        padding: 10px;
        border: 1px solid #dce7ea;
        border-radius: 11px;
        background: #fff;
        cursor: pointer;
    }
    .driver-contra-assessment__option input {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        margin-top: 1px;
        accent-color: var(--driver-accent);
    }
    .driver-contra-assessment__option span { min-width: 0; }
    .driver-contra-assessment__option strong {
        display: block;
        color: #314f5f;
        font-size: 10.5px;
        line-height: 1.35;
    }
    .driver-contra-assessment__option small {
        display: block;
        margin-top: 3px;
        color: var(--driver-muted);
        font-size: 9.5px;
        line-height: 1.4;
    }
    .driver-contra-assessment__option:has(input:checked) {
        border-color: #94cfd5;
        background: #f0fafb;
        box-shadow: 0 0 0 2px rgba(0, 135, 150, .06);
    }
    .driver-contra-assessment__option.is-danger:has(input:checked) {
        border-color: #e0aaaa;
        background: #fff2f2;
    }
    .driver-contra-assessment__option.is-danger input { accent-color: #b73f3f; }

    .driver-hard-lock {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 10px;
        padding: 12px 13px;
        border: 1px solid #e7b9b9;
        border-radius: 13px;
        color: #9f3030;
        background: #fff1f1;
    }
    .driver-hard-lock > i { margin-top: 1px; font-size: 17px; }
    .driver-hard-lock strong { display: block; font-size: 11.5px; }
    .driver-hard-lock span { display: block; margin-top: 3px; font-size: 10.5px; line-height: 1.45; }

    .driver-f-contraindications {
        margin-top: 10px;
        border: 1px solid #dce7ea;
        border-radius: 15px;
        background: #fff;
        overflow: hidden;
    }
    .driver-f-contraindications__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px 10px;
        border-bottom: 1px solid #e7eef1;
    }
    .driver-f-contraindications__head strong {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #29475a;
        font-size: 12px;
    }
    .driver-f-contraindications__head strong i { color: var(--driver-accent); }
    .driver-f-contraindications__head small {
        display: block;
        margin-top: 3px;
        color: var(--driver-muted);
        font-size: 10px;
        line-height: 1.35;
    }
    .driver-f-contraindications__badge {
        flex: 0 0 auto;
        padding: 4px 7px;
        border: 1px solid #cce5e8;
        border-radius: 999px;
        color: var(--driver-accent-dark);
        background: var(--driver-accent-soft);
        font-size: 9px;
        font-weight: 800;
    }
    .driver-f-contraindications__status {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 10px 12px 0;
        padding: 10px 11px;
        border: 1px solid #dce7ea;
        border-radius: 11px;
        color: #506775;
        background: #f7fafb;
        font-size: 10.5px;
        line-height: 1.45;
    }
    .driver-f-contraindications__status > i { margin-top: 1px; flex: 0 0 auto; }
    .driver-f-contraindications__status.is-match { color: #775410; border-color: #ead49e; background: #fff8e7; }
    .driver-f-contraindications__status.is-narcology { color: #7a4f22; border-color: #ead8c5; background: #fff8f1; }
    .driver-f-contraindications__status.is-clear { color: #087252; border-color: #cde9de; background: #effaf5; }
    .driver-f-contraindications__status.is-empty { color: #536a76; border-color: #dce7ea; background: #f7fafb; }
    .driver-f-contraindications__details { padding: 0 12px 12px; }
    .driver-f-contraindications__details > summary {
        cursor: pointer;
        padding: 10px 1px 4px;
        color: var(--driver-accent-dark);
        font-size: 10.5px;
        font-weight: 800;
        user-select: none;
    }
    .driver-f-contraindications__groups {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
        margin-top: 7px;
    }
    .driver-f-group {
        min-width: 0;
        padding: 9px 10px;
        border: 1px solid #e1e9ed;
        border-radius: 10px;
        background: #fbfdfd;
    }
    .driver-f-group.is-narcology { border-color: #eadfd4; background: #fffaf5; }
    .driver-f-group__title { display: flex; gap: 7px; align-items: flex-start; }
    .driver-f-group__title > span {
        flex: 0 0 auto;
        min-width: 28px;
        padding: 2px 5px;
        border-radius: 6px;
        color: var(--driver-accent-dark);
        background: var(--driver-accent-soft);
        font-size: 9px;
        font-weight: 800;
        text-align: center;
    }
    .driver-f-group__title strong { color: #304c5d; font-size: 10.5px; line-height: 1.35; }
    .driver-f-group__codes { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 7px; }
    .driver-f-group__codes code {
        padding: 2px 5px;
        border: 1px solid #d7e4e8;
        border-radius: 6px;
        color: #3f5968;
        background: #fff;
        font-size: 9.5px;
        font-family: inherit;
        font-weight: 750;
    }
    .driver-f-group > small {
        display: block;
        margin-top: 7px;
        color: #7a8995;
        font-size: 9px;
        line-height: 1.4;
    }
    .driver-f-contraindications__footnote {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        margin-top: 8px;
        padding: 8px 9px;
        border-radius: 9px;
        color: #667b86;
        background: #f6f9fa;
        font-size: 9.5px;
        line-height: 1.4;
    }

    .driver-risk-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .driver-risk-card {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 9px 10px;
        border: 1px solid #e2e8eb;
        border-radius: 11px;
        background: #fff;
        cursor: pointer;
    }
    .driver-risk-card input { width: 16px; height: 16px; margin-top: 1px; flex: 0 0 16px; accent-color: #b66b19; }
    .driver-risk-card span { color: #4a5f69; font-size: 11px; line-height: 1.35; }
    .driver-risk-card:has(input:checked) { border-color: #e2c38f; background: #fff9ed; }

    .driver-inline-check {
        display: inline-flex;
        align-items: flex-start;
        gap: 8px;
        padding: 9px 10px;
        border: 1px solid #dce7ea;
        border-radius: 11px;
        background: #f8fbfc;
        cursor: pointer;
    }
    .driver-inline-check input { width: 16px; height: 16px; margin-top: 1px; flex: 0 0 16px; accent-color: var(--driver-accent); }
    .driver-inline-check span { color: #405b68; font-size: 11px; line-height: 1.35; }

    .driver-result-note {
        margin-top: 9px;
        padding: 10px 11px;
        border-left: 3px solid #d7b35d;
        border-radius: 0 10px 10px 0;
        color: #66501e;
        background: #fff9ea;
        font-size: 11px;
        line-height: 1.4;
    }

    .driver-sidebar { position: sticky; top: 14px; display: grid; gap: 10px; }
    .driver-sidebar-card { padding: 15px; border-radius: 16px; }
    .driver-sidebar-card h3 { margin: 0 0 7px; font-size: 14px; font-weight: 800; }
    .driver-sidebar-card p { margin: 0; color: var(--driver-muted); font-size: 11px; line-height: 1.45; }
    .driver-sidebar-actions { display: grid; gap: 8px; }
    .driver-sidebar-actions .driver-button { width: 100%; }
    .driver-readiness { display: grid; gap: 6px; margin-top: 10px; }
    .driver-readiness__item { display: flex; align-items: center; gap: 7px; color: #7b8997; font-size: 10px; }
    .driver-readiness__dot { display: grid; width: 17px; height: 17px; flex: 0 0 17px; place-items: center; border-radius: 50%; background: #eef2f4; color: #a3adb6; font-size: 9px; }
    .driver-readiness__item.is-ready { color: #386657; }
    .driver-readiness__item.is-ready .driver-readiness__dot { color: #fff; background: var(--driver-success); }

    .driver-details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .driver-detail-card { padding: 18px; border: 1px solid var(--driver-line); border-radius: 17px; background: #fff; }
    .driver-detail-card--wide { grid-column: 1 / -1; }
    .driver-detail-card h2 { display: flex; align-items: center; gap: 8px; margin: 0 0 13px; padding-bottom: 11px; border-bottom: 1px solid #e8eff3; font-size: 16px; font-weight: 800; }
    .driver-detail-card h2 i { color: var(--driver-accent); }
    .driver-detail-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 11px 16px; margin: 0; }
    .driver-detail-list dt { margin-bottom: 3px; color: var(--driver-muted); font-size: 10px; font-weight: 700; }
    .driver-detail-list dd { margin: 0; color: var(--driver-ink); font-size: 12px; font-weight: 650; overflow-wrap: anywhere; }
    .driver-text-block { padding: 10px 11px; border: 1px solid #e3ebef; border-radius: 10px; color: #3b5060; background: #fbfcfd; font-size: 12px; line-height: 1.5; white-space: pre-wrap; overflow-wrap: anywhere; }
    .driver-text-block.is-empty { color: var(--driver-muted); font-style: italic; }
    .driver-chip-list { display: flex; flex-wrap: wrap; gap: 6px; }
    .driver-chip { display: inline-flex; min-height: 25px; align-items: center; padding: 4px 8px; border: 1px solid #d8e8eb; border-radius: 999px; color: #476078; background: #f5fafb; font-size: 10px; font-weight: 750; }

    .d-none { display: none !important; }

    @media (max-width: 1220px) {
        .driver-filter-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .driver-filter-grid .driver-field:first-child { grid-column: span 2; }
        .driver-form-layout { grid-template-columns: minmax(0, 1fr) 260px; }
        .driver-check-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 980px) {
        .driver-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .driver-form-layout { grid-template-columns: 1fr; }
        .driver-sidebar { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .driver-selected-patient { grid-template-columns: 1fr; }
    }
</style>
