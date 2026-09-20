<style>
    .mse-page {
        --mse-ink: #1e3148;
        --mse-muted: #8292a8;
        --mse-line: #dbe6ec;
        --mse-soft: #f5fafb;
        --mse-accent: #008796;
        --mse-accent-dark: #006d7a;
        --mse-accent-soft: #e9f7f8;
        --mse-danger: #c13f3f;
        --mse-success: #00865f;
        color: var(--mse-ink);
        padding: 24px 18px 52px;
    }

    .mse-container {
        width: min(1540px, 100%);
        margin: 0 auto;
    }

    .mse-hero,
    .mse-card,
    .mse-filter,
    .mse-table-card,
    .mse-stat,
    .mse-detail-card {
        background: #fff;
        border: 1px solid var(--mse-line);
        border-radius: 24px;
        box-shadow: 0 12px 34px rgba(31, 65, 85, .055);
    }

    .mse-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 30px;
        margin-bottom: 20px;
    }

    .mse-hero__main,
    .mse-hero__actions,
    .mse-title-row,
    .mse-actions,
    .mse-patient-summary {
        display: flex;
        align-items: center;
    }

    .mse-hero__main { gap: 18px; }
    .mse-hero__actions { gap: 10px; flex-wrap: wrap; justify-content: flex-end; }

    .mse-hero__icon,
    .mse-card__icon,
    .mse-stat__icon,
    .mse-patient-summary__avatar {
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: var(--mse-accent);
        background: var(--mse-accent-soft);
    }

    .mse-hero__icon {
        width: 66px;
        height: 66px;
        border-radius: 19px;
        font-size: 27px;
    }

    .mse-eyebrow {
        color: var(--mse-accent);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .11em;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .mse-hero h1 {
        color: var(--mse-ink);
        font-size: clamp(24px, 2vw, 34px);
        font-weight: 800;
        line-height: 1.15;
        margin: 0;
    }

    .mse-hero p {
        color: var(--mse-muted);
        font-size: 15px;
        margin: 7px 0 0;
    }

    .mse-button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 10px 17px;
        font-weight: 750;
        text-decoration: none !important;
        cursor: pointer;
        transition: .18s ease;
    }

    .mse-button:hover { transform: translateY(-1px); }
    .mse-button--primary { color: #fff !important; background: var(--mse-accent); }
    .mse-button--primary:hover { background: var(--mse-accent-dark); }
    .mse-button--secondary { color: var(--mse-accent) !important; background: var(--mse-accent-soft); border-color: #cce8eb; }
    .mse-button--ghost { color: var(--mse-ink) !important; background: #fff; border-color: var(--mse-line); }
    .mse-button--danger { color: var(--mse-danger) !important; background: #fff; border-color: #f0d7d7; }

    .mse-back {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border: 1px solid var(--mse-line);
        border-radius: 14px;
        color: var(--mse-ink) !important;
        background: #fff;
        text-decoration: none !important;
    }

    .mse-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1px solid;
        border-radius: 16px;
        padding: 14px 17px;
        margin: 0 0 20px;
    }

    .mse-alert--success { color: #087252; background: #edfbf5; border-color: #cbeee0; }
    .mse-alert--danger { color: #a33c3c; background: #fff4f4; border-color: #f0d2d2; }
    .mse-alert ul { margin: 6px 0 0; padding-left: 19px; }

    .mse-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .mse-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px;
    }

    .mse-stat__icon {
        width: 48px;
        height: 48px;
        border-radius: 15px;
        font-size: 20px;
    }

    .mse-stat__copy span { display: block; color: var(--mse-muted); font-size: 13px; }
    .mse-stat__copy strong { display: block; color: var(--mse-ink); font-size: 25px; line-height: 1.1; margin-top: 3px; }

    .mse-filter { padding: 22px; margin-bottom: 20px; }
    .mse-filter__head { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 16px; }
    .mse-filter__head h2 { font-size: 18px; font-weight: 800; margin: 0; }
    .mse-filter__head p { color: var(--mse-muted); margin: 4px 0 0; }
    .mse-filter__grid { display: grid; grid-template-columns: minmax(240px, 1.5fr) repeat(5, minmax(140px, .7fr)) auto; gap: 12px; align-items: end; }

    .mse-field { min-width: 0; }
    .mse-field label { display: block; color: #45566d; font-size: 13px; font-weight: 750; margin: 0 0 7px; }
    .mse-field .required { color: #d43838; }
    .mse-field input,
    .mse-field select,
    .mse-field textarea,
    .mse-search-input {
        width: 100%;
        color: var(--mse-ink);
        background: #fff;
        border: 1px solid #cbdbe3;
        border-radius: 14px;
        outline: none;
        padding: 11px 13px;
        transition: .18s ease;
    }

    .mse-field input,
    .mse-field select,
    .mse-search-input { min-height: 46px; }
    .mse-field textarea { min-height: 124px; resize: vertical; line-height: 1.55; }
    .mse-field input:focus,
    .mse-field select:focus,
    .mse-field textarea:focus,
    .mse-search-input:focus { border-color: var(--mse-accent); box-shadow: 0 0 0 3px rgba(0, 134, 150, .11); }
    .mse-field small { display: block; color: var(--mse-muted); line-height: 1.4; margin-top: 6px; }
    .mse-field .is-invalid { border-color: #d76666; }
    .mse-field-error { color: #b53d3d !important; font-weight: 650; }

    .mse-search-wrap { position: relative; }
    .mse-search-wrap > i { position: absolute; z-index: 2; left: 14px; top: 50%; transform: translateY(-50%); color: #8ea0b3; }
    .mse-search-wrap .mse-search-input { padding-left: 40px; }
    .mse-filter-reset { color: var(--mse-accent); font-weight: 700; text-decoration: none; white-space: nowrap; }

    .mse-table-card { overflow: visible; }
    .mse-table-wrap { overflow-x: auto; border-radius: 24px 24px 0 0; }
    .mse-table { width: 100%; min-width: 1120px; border-collapse: collapse; }
    .mse-table th { color: #71839a; background: #f7fafb; font-size: 12px; font-weight: 800; letter-spacing: .035em; text-align: left; text-transform: uppercase; padding: 15px 16px; border-bottom: 1px solid var(--mse-line); }
    .mse-table td { vertical-align: middle; padding: 18px 16px; border-bottom: 1px solid #e8eff3; }
    .mse-table tr:last-child td { border-bottom: 0; }
    .mse-table tbody tr:hover { background: #fbfdfd; }
    .mse-table strong { color: var(--mse-ink); }
    .mse-table__muted { display: block; color: var(--mse-muted); font-size: 13px; margin-top: 3px; }

    .mse-patient-summary { gap: 12px; min-width: 230px; }
    .mse-patient-summary__avatar { width: 44px; height: 44px; border-radius: 14px; font-weight: 800; }
    .mse-patient-summary__copy { min-width: 0; }
    .mse-patient-summary__copy strong,
    .mse-patient-summary__copy span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 270px; }

    .mse-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }
    .mse-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .mse-badge--draft { color: #9a6900; background: #fff5df; }
    .mse-badge--prepared { color: #007a58; background: #e8f9f2; }

    .mse-actions { gap: 8px; justify-content: flex-end; }
    .mse-action {
        position: relative;
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        color: #52708a;
        background: #fff;
        border: 1px solid var(--mse-line);
        border-radius: 12px;
        text-decoration: none !important;
        cursor: pointer;
    }
    .mse-action:hover { color: var(--mse-accent); border-color: #acd7dc; background: #f4fbfb; }
    .mse-action--danger:hover { color: var(--mse-danger); border-color: #e7bcbc; background: #fff7f7; }
    .mse-action--disabled { color: #b7c1cb; background: #f8fafb; cursor: not-allowed; }
    .mse-action-form { margin: 0; }

    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        z-index: 30;
        left: 50%;
        bottom: calc(100% + 9px);
        transform: translate(-50%, 4px);
        width: max-content;
        max-width: 220px;
        color: #fff;
        background: #20344a;
        border-radius: 9px;
        padding: 7px 9px;
        font-size: 11px;
        font-weight: 650;
        line-height: 1.2;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: .16s ease;
    }
    [data-tooltip]:hover::after,
    [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translate(-50%, 0); }

    .mse-empty { padding: 54px 24px; text-align: center; }
    .mse-empty i { display: block; color: #a8c9cd; font-size: 42px; margin-bottom: 12px; }
    .mse-empty h3 { font-size: 19px; margin: 0 0 7px; }
    .mse-empty p { color: var(--mse-muted); margin: 0 0 18px; }
    .mse-table-footer { display: flex; align-items: center; justify-content: space-between; gap: 18px; color: var(--mse-muted); padding: 18px 20px; border-top: 1px solid var(--mse-line); }

    .mse-form-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; align-items: start; }
    .mse-form-main { display: grid; gap: 20px; }
    .mse-card { padding: 24px; }
    .mse-card__head { display: flex; align-items: center; gap: 13px; padding-bottom: 18px; margin-bottom: 20px; border-bottom: 1px solid #e8eff3; }
    .mse-card__icon { width: 44px; height: 44px; border-radius: 14px; font-size: 18px; }
    .mse-card__head h2 { font-size: 19px; font-weight: 800; margin: 0; }
    .mse-card__head p { color: var(--mse-muted); font-size: 13px; margin: 3px 0 0; }
    .mse-step { margin-left: auto; color: var(--mse-accent); background: var(--mse-accent-soft); border-radius: 999px; padding: 7px 10px; font-size: 12px; font-weight: 800; }
    .mse-grid { display: grid; gap: 16px; }
    .mse-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .mse-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .mse-field--full { grid-column: 1 / -1; }

    .mse-patient-search { position: relative; }
    .mse-control-wrap { position: relative; }
    .mse-control-wrap > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #8ba0b2; }
    .mse-control-wrap input { padding-left: 42px; padding-right: 45px; }
    .mse-clear { position: absolute; right: 9px; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; display: grid; place-items: center; color: #8495a8; background: transparent; border: 0; border-radius: 9px; }
    .mse-clear:hover { color: var(--mse-danger); background: #fff2f2; }
    .mse-suggestions { position: absolute; z-index: 25; top: calc(100% + 7px); left: 0; right: 0; max-height: 300px; overflow-y: auto; background: #fff; border: 1px solid var(--mse-line); border-radius: 15px; box-shadow: 0 18px 45px rgba(29, 58, 78, .16); padding: 6px; }
    .mse-suggestion { width: 100%; display: flex; align-items: center; gap: 11px; color: var(--mse-ink); background: transparent; border: 0; border-radius: 11px; padding: 11px; text-align: left; cursor: pointer; }
    .mse-suggestion:hover,
    .mse-suggestion:focus { background: var(--mse-accent-soft); outline: none; }
    .mse-suggestion__icon { width: 36px; height: 36px; display: grid; place-items: center; flex: 0 0 auto; color: var(--mse-accent); background: #fff; border: 1px solid #d4e8ea; border-radius: 11px; }
    .mse-suggestion__copy { min-width: 0; }
    .mse-suggestion__copy strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mse-suggestion__copy span { display: block; color: var(--mse-muted); font-size: 12px; margin-top: 2px; }
    .mse-suggestion-message { color: var(--mse-muted); padding: 14px; text-align: center; }
    .d-none { display: none !important; }

    .mse-selected-patient { display: flex; align-items: center; gap: 14px; background: var(--mse-soft); border: 1px solid #d9eaed; border-radius: 16px; padding: 15px; margin-top: 14px; }
    .mse-selected-patient.is-empty { opacity: .72; }
    .mse-selected-patient__avatar { width: 46px; height: 46px; display: grid; place-items: center; flex: 0 0 auto; color: var(--mse-accent); background: #fff; border-radius: 14px; font-size: 19px; }
    .mse-selected-patient__copy { min-width: 0; }
    .mse-selected-patient__copy strong { display: block; }
    .mse-selected-patient__copy span { display: block; color: var(--mse-muted); font-size: 13px; margin-top: 2px; }

    .mse-sidebar { position: sticky; top: 18px; display: grid; gap: 14px; }
    .mse-sidebar-card { background: #fff; border: 1px solid var(--mse-line); border-radius: 20px; padding: 20px; }
    .mse-sidebar-card h3 { font-size: 16px; font-weight: 800; margin: 0 0 9px; }
    .mse-sidebar-card p { color: var(--mse-muted); font-size: 13px; line-height: 1.55; margin: 0; }
    .mse-sidebar-card--notice { color: #5d4b16; background: #fffaf0; border-color: #eee1b7; }
    .mse-sidebar-card--notice p { color: #756631; }
    .mse-sidebar-actions { display: grid; gap: 10px; }
    .mse-sidebar-actions .mse-button { width: 100%; }

    .mse-details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .mse-detail-card { padding: 24px; }
    .mse-detail-card--wide { grid-column: 1 / -1; }
    .mse-detail-card h2 { display: flex; align-items: center; gap: 9px; font-size: 18px; font-weight: 800; padding-bottom: 15px; margin: 0 0 17px; border-bottom: 1px solid #e8eff3; }
    .mse-detail-card h2 i { color: var(--mse-accent); }
    .mse-detail-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 20px; margin: 0; }
    .mse-detail-list > div { min-width: 0; }
    .mse-detail-list dt { color: var(--mse-muted); font-size: 12px; font-weight: 700; margin-bottom: 4px; }
    .mse-detail-list dd { color: var(--mse-ink); font-weight: 650; margin: 0; overflow-wrap: anywhere; }
    .mse-text-block { color: #394d64; line-height: 1.65; white-space: pre-wrap; overflow-wrap: anywhere; }
    .mse-text-block.is-empty { color: var(--mse-muted); font-style: italic; }

    @media (max-width: 1200px) {
        .mse-filter__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .mse-form-layout { grid-template-columns: 1fr; }
        .mse-sidebar { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 780px) {
        .mse-page { padding: 14px 10px 38px; }
        .mse-hero { align-items: flex-start; flex-direction: column; padding: 21px; border-radius: 20px; }
        .mse-hero__actions { width: 100%; justify-content: flex-start; }
        .mse-stats,
        .mse-grid--2,
        .mse-grid--3,
        .mse-details-grid,
        .mse-detail-list,
        .mse-sidebar { grid-template-columns: 1fr; }
        .mse-filter__grid { grid-template-columns: 1fr; }
        .mse-filter__head { flex-direction: column; }
        .mse-card,
        .mse-detail-card { padding: 19px; border-radius: 19px; }
        .mse-card__head { align-items: flex-start; }
        .mse-table-footer { align-items: flex-start; flex-direction: column; }
    }


    .mse-form-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 94% 2%, rgba(0, 135, 150, .09), transparent 26rem),
            linear-gradient(180deg, #f6fbfc 0, #f4f8fa 28rem, #f8fafb 100%);
    }

    .mse-form-hero {
        position: relative;
        overflow: hidden;
    }

    .mse-form-hero::after {
        content: '';
        position: absolute;
        right: -72px;
        top: -104px;
        width: 236px;
        height: 236px;
        border: 42px solid rgba(0, 135, 150, .055);
        border-radius: 50%;
        pointer-events: none;
    }

    .mse-form-hero > * { position: relative; z-index: 1; }

    .mse-form-chip,
    .mse-purpose-chip,
    .mse-condition-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 750;
    }

    .mse-form-chip {
        min-height: 27px;
        color: #476078;
        background: #f5fafb;
        border: 1px solid #dce9ed;
        padding: 5px 10px;
    }

    .mse-form-chip--accent {
        color: var(--mse-accent-dark);
        background: var(--mse-accent-soft);
        border-color: #c7e7ea;
    }

    .mse-form-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 8px;
    }

    .mse-progress {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 6px;
        background: rgba(255, 255, 255, .95);
        border: 1px solid var(--mse-line);
        border-radius: 17px;
        box-shadow: 0 8px 24px rgba(26, 66, 87, .045);
        padding: 7px;
        margin-bottom: 18px;
    }

    .mse-progress__item {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        color: #6f8195 !important;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 9px 10px;
        text-decoration: none !important;
        transition: .18s ease;
    }

    .mse-progress__item:hover,
    .mse-progress__item.is-active {
        color: var(--mse-accent-dark) !important;
        background: var(--mse-accent-soft);
        border-color: #cde8eb;
    }

    .mse-progress__number {
        width: 28px;
        height: 28px;
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

    .mse-progress__item.is-active .mse-progress__number,
    .mse-progress__item.is-complete .mse-progress__number {
        color: #fff;
        background: var(--mse-accent);
        border-color: var(--mse-accent);
    }

    .mse-progress__copy { min-width: 0; }
    .mse-progress__copy strong,
    .mse-progress__copy small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .mse-progress__copy strong { color: inherit; font-size: 12px; font-weight: 800; }
    .mse-progress__copy small { color: #91a0b1; font-size: 10px; margin-top: 1px; }

    .mse-form-card {
        scroll-margin-top: 18px;
        position: relative;
        overflow: visible;
    }

    .mse-form-card::before {
        content: '';
        position: absolute;
        z-index: 2;
        top: -1px;
        left: 24px;
        width: 86px;
        height: 3px;
        background: linear-gradient(90deg, var(--mse-accent), #66c8d1);
        border-radius: 0 0 999px 999px;
    }

    .mse-section-caption {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #4b6178;
        font-size: 13px;
        font-weight: 800;
        margin: 22px 0 11px;
    }

    .mse-section-caption::after {
        content: '';
        height: 1px;
        flex: 1;
        background: #e3edf1;
    }

    .mse-check-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .mse-check-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }

    .mse-check-card {
        position: relative;
        min-width: 0;
        cursor: pointer;
    }

    .mse-check-card > input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .mse-check-card > span {
        min-height: 58px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #42576e;
        background: #f9fbfc;
        border: 1px solid #dbe7ec;
        border-radius: 14px;
        padding: 12px 13px;
        transition: .18s ease;
    }

    .mse-check-card > span::before {
        content: '';
        width: 19px;
        height: 19px;
        flex: 0 0 auto;
        border: 2px solid #b7c9d3;
        border-radius: 6px;
        background: #fff;
        margin-top: 1px;
    }

    .mse-check-card > input:checked + span {
        color: #075d68;
        background: var(--mse-accent-soft);
        border-color: #9dd7dc;
        box-shadow: 0 0 0 2px rgba(0, 135, 150, .06);
    }

    .mse-check-card > input:checked + span::before {
        border-color: var(--mse-accent);
        background: var(--mse-accent);
        box-shadow: inset 0 0 0 4px #fff;
    }

    .mse-check-card strong {
        display: block;
        font-size: 13px;
        line-height: 1.35;
    }

    .mse-check-card small {
        display: block;
        color: #8292a8;
        font-size: 11px;
        line-height: 1.35;
        margin-top: 3px;
    }

    .mse-switch-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .mse-switch-card {
        position: relative;
        min-width: 0;
        cursor: pointer;
    }

    .mse-switch-card input { position: absolute; opacity: 0; }
    .mse-switch-card > span {
        min-height: 88px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        color: #42576e;
        background: #f8fbfc;
        border: 1px solid #dce8ed;
        border-radius: 15px;
        padding: 14px 15px 14px 48px;
        transition: .18s ease;
    }

    .mse-switch-card > span::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 18px;
        width: 23px;
        height: 23px;
        border: 2px solid #b9cbd5;
        border-radius: 8px;
        background: #fff;
    }

    .mse-switch-card input:checked + span {
        color: #075d68;
        background: var(--mse-accent-soft);
        border-color: #9fd8dd;
    }

    .mse-switch-card input:checked + span::before {
        border-color: var(--mse-accent);
        background: var(--mse-accent);
        box-shadow: inset 0 0 0 5px #fff;
    }

    .mse-switch-card strong { font-size: 13px; line-height: 1.3; }
    .mse-switch-card small { color: #8394a7; font-size: 11px; line-height: 1.35; }

    .mse-live-diagnosis {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: #39536b;
        background: linear-gradient(135deg, #eefafb, #f6fbfc);
        border: 1px solid #cfe9ec;
        border-radius: 15px;
        padding: 14px 16px;
        margin-bottom: 16px;
    }

    .mse-live-diagnosis i { color: var(--mse-accent); font-size: 19px; margin-top: 1px; }
    .mse-live-diagnosis span { display: block; color: #8091a4; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
    .mse-live-diagnosis strong { display: block; margin-top: 3px; }

    .mse-collapsible {
        border: 1px solid #dce8ed;
        border-radius: 15px;
        background: #fbfdfd;
        margin-top: 15px;
    }

    .mse-collapsible > summary {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #40566e;
        padding: 13px 15px;
        font-weight: 800;
        cursor: pointer;
        list-style: none;
    }
    .mse-collapsible > summary::-webkit-details-marker { display: none; }
    .mse-collapsible > summary::after { content: '\F282'; font-family: bootstrap-icons; margin-left: auto; transition: .18s ease; }
    .mse-collapsible[open] > summary::after { transform: rotate(180deg); }
    .mse-collapsible__body { border-top: 1px solid #e3edf1; padding: 16px; }

    .mse-repeater {
        overflow-x: auto;
        border: 1px solid #dbe7ec;
        border-radius: 15px;
    }

    .mse-repeater table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .mse-repeater th {
        color: #71839a;
        background: #f6f9fa;
        font-size: 11px;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .035em;
        padding: 10px;
        border-bottom: 1px solid #dbe7ec;
    }

    .mse-repeater td {
        vertical-align: top;
        padding: 8px;
        border-bottom: 1px solid #e6eef2;
    }

    .mse-repeater tr:last-child td { border-bottom: 0; }
    .mse-repeater input,
    .mse-repeater textarea {
        width: 100%;
        color: var(--mse-ink);
        background: #fff;
        border: 1px solid #cbdbe3;
        border-radius: 10px;
        padding: 8px 9px;
        outline: none;
    }
    .mse-repeater textarea { min-height: 58px; resize: vertical; }
    .mse-repeater input:focus,
    .mse-repeater textarea:focus { border-color: var(--mse-accent); box-shadow: 0 0 0 3px rgba(0, 135, 150, .09); }

    .mse-repeater__remove {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        color: #b44a4a;
        background: #fff7f7;
        border: 1px solid #efd2d2;
        border-radius: 10px;
        cursor: pointer;
    }

    .mse-repeater__footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 9px;
    }

    .mse-inline-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #455b72;
        font-weight: 700;
        cursor: pointer;
    }
    .mse-inline-check input { width: 18px; height: 18px; accent-color: var(--mse-accent); }

    .mse-readiness {
        display: grid;
        gap: 9px;
        margin-top: 13px;
    }

    .mse-readiness__item {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #6f8195;
        font-size: 12px;
        font-weight: 700;
    }

    .mse-readiness__dot {
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        color: #92a4b4;
        background: #f2f5f7;
        border-radius: 7px;
        font-size: 10px;
    }

    .mse-readiness__item.is-ready { color: #087252; }
    .mse-readiness__item.is-ready .mse-readiness__dot { color: #fff; background: #0a9b70; }

    .mse-purpose-chip {
        color: #315d65;
        background: #edf9fa;
        border: 1px solid #c9e7ea;
        padding: 6px 9px;
    }

    .mse-condition-chip {
        color: #725912;
        background: #fff8df;
        border: 1px solid #eee0aa;
        padding: 6px 9px;
    }

    .mse-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .mse-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #5d4d1d;
        background: #fffaf0;
        border: 1px solid #eadfb9;
        border-radius: 14px;
        padding: 13px 15px;
        margin-top: 16px;
    }
    .mse-note i { color: #b58a13; margin-top: 1px; }
    .mse-note strong { display: block; margin-bottom: 2px; }
    .mse-note span { display: block; color: #796a3d; font-size: 12px; line-height: 1.5; }

    .mse-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        background: #fff;
        border: 1px solid var(--mse-line);
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(31, 65, 85, .055);
        padding: 15px 17px;
    }
    .mse-actions-bar__hint { color: var(--mse-muted); font-size: 12px; }
    .mse-actions-bar__buttons { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }

    .mse-detail-table {
        width: 100%;
        border-collapse: collapse;
    }
    .mse-detail-table th,
    .mse-detail-table td {
        vertical-align: top;
        text-align: left;
        padding: 9px 10px;
        border: 1px solid #dfe8ed;
    }
    .mse-detail-table th { color: #667b91; background: #f7fafb; font-size: 12px; }
    .mse-detail-table td { color: #344c64; white-space: pre-wrap; }

    .mse-button--consent {
        color: #fff !important;
        background: linear-gradient(135deg, #315b87 0%, #167f8e 100%);
        border-color: rgba(20, 92, 112, .35);
        box-shadow: 0 8px 18px rgba(29, 93, 119, .18);
    }

    .mse-button--consent:hover {
        color: #fff !important;
        background: linear-gradient(135deg, #274f79 0%, #08727f 100%);
        box-shadow: 0 10px 22px rgba(29, 93, 119, .24);
    }

    .mse-consent-workflow {
        display: grid;
        gap: 16px;
        overflow: hidden;
        background:
            radial-gradient(circle at 0 0, rgba(0, 135, 150, .10), transparent 34%),
            linear-gradient(145deg, #f9fcfd 0%, #f1f7f9 100%);
        border: 1px solid #c8dde3;
        border-radius: 22px;
        padding: 20px;
    }

    .mse-consent-workflow.has-error {
        background: #fff8f8;
        border-color: #df8b8b;
        box-shadow: 0 0 0 4px rgba(190, 66, 66, .07);
    }

    .mse-consent-workflow .required {
        color: #c33f3f;
        font-weight: 900;
    }

    .mse-consent-workflow__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 22px;
    }

    .mse-consent-workflow__eyebrow {
        display: block;
        color: var(--mse-accent);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .095em;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .mse-consent-workflow__head h3 {
        color: #29435a;
        font-size: 17px;
        font-weight: 850;
        line-height: 1.3;
        margin: 0;
    }

    .mse-consent-workflow__head p {
        max-width: 810px;
        color: #77899c;
        font-size: 12px;
        line-height: 1.55;
        margin: 6px 0 0;
    }

    .mse-consent-steps {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px minmax(0, 1fr);
        align-items: stretch;
        gap: 10px;
    }

    .mse-consent-step {
        position: relative;
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        align-content: start;
        gap: 11px 14px;
        min-width: 0;
        background: rgba(255, 255, 255, .94);
        border: 1px solid #d5e4e8;
        border-radius: 18px;
        padding: 17px;
        box-shadow: 0 10px 24px rgba(31, 65, 85, .045);
    }

    .mse-consent-step--print {
        border-color: #c5d7e5;
        background: linear-gradient(145deg, #fff 0%, #f5f9fc 100%);
    }

    .mse-consent-step--upload {
        border-color: #c7e1dc;
        background: linear-gradient(145deg, #fff 0%, #f3faf7 100%);
    }

    .mse-consent-step__number {
        position: absolute;
        top: 11px;
        right: 12px;
        display: grid;
        place-items: center;
        width: 27px;
        height: 27px;
        color: #5c7389;
        background: #edf3f6;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
    }

    .mse-consent-step__icon {
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        grid-row: span 1;
        color: #315b87;
        background: #eaf1f8;
        border-radius: 15px;
        font-size: 22px;
    }

    .mse-consent-step--upload .mse-consent-step__icon {
        color: #087252;
        background: #e5f6ee;
    }

    .mse-consent-step__copy {
        min-width: 0;
        padding-right: 30px;
    }

    .mse-consent-step__copy > strong {
        display: block;
        color: #2b455c;
        font-size: 14px;
        font-weight: 850;
        line-height: 1.35;
    }

    .mse-consent-step__copy > span {
        display: block;
        color: #8191a2;
        font-size: 11px;
        line-height: 1.5;
        margin-top: 4px;
    }

    .mse-consent-step__button {
        grid-column: 1 / -1;
        width: 100%;
        margin-top: 4px;
    }

    .mse-consent-steps__arrow {
        display: grid;
        place-items: center;
        color: #79a3ad;
        font-size: 21px;
    }

    .mse-consent-step .mse-upload-dropzone {
        grid-column: 1 / -1;
        min-height: 86px;
        margin-top: 4px;
        cursor: default;
    }

    .mse-consent-upload-button {
        flex: 0 0 auto;
        min-width: 210px;
        cursor: pointer;
        white-space: nowrap;
    }

    .mse-consent-workflow__note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #60778c;
        background: rgba(255, 255, 255, .72);
        border: 1px solid #d9e6ea;
        border-radius: 13px;
        padding: 10px 12px;
        font-size: 11px;
        line-height: 1.5;
    }

    .mse-consent-workflow__note i {
        flex: 0 0 auto;
        color: var(--mse-accent);
        font-size: 15px;
        margin-top: 1px;
    }

    .mse-consent-upload {
        display: grid;
        gap: 12px;
        background: linear-gradient(135deg, #f8fcfd 0%, #f3f9fa 100%);
        border: 1px solid #cfe2e7;
        border-radius: 18px;
        padding: 17px;
    }

    .mse-consent-upload.has-error {
        background: #fff8f8;
        border-color: #df8b8b;
        box-shadow: 0 0 0 3px rgba(190, 66, 66, .07);
    }

    .mse-consent-upload .required {
        color: #c33f3f;
        font-weight: 900;
    }

    .mse-consent-upload__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .mse-consent-upload__head label {
        display: block;
        color: #334b61;
        font-size: 14px;
        font-weight: 800;
        margin: 0 0 4px;
    }

    .mse-consent-upload__head small {
        display: block;
        color: var(--mse-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .mse-consent-upload__status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
        color: #718398;
        background: #fff;
        border: 1px solid #d8e4e9;
        border-radius: 999px;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .mse-consent-upload__status.is-ready {
        color: #087252;
        background: #ecfaf4;
        border-color: #c6eadb;
    }

    .mse-file-input {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    .mse-upload-dropzone {
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 92px;
        color: #4b657b;
        background: rgba(255, 255, 255, .9);
        border: 2px dashed #b8d3da;
        border-radius: 16px;
        padding: 15px 17px;
        cursor: pointer;
        transition: border-color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .mse-upload-dropzone:hover,
    .mse-upload-dropzone.is-dragover {
        color: #075f69;
        background: #f0fbfc;
        border-color: var(--mse-accent);
        box-shadow: 0 0 0 4px rgba(0, 134, 150, .08);
        transform: translateY(-1px);
    }

    .mse-upload-dropzone.is-selected {
        border-color: #6ab9a0;
        background: #f2fbf7;
    }

    .mse-upload-dropzone__icon {
        width: 49px;
        height: 49px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: var(--mse-accent);
        background: var(--mse-accent-soft);
        border-radius: 15px;
        font-size: 22px;
    }

    .mse-upload-dropzone__copy {
        min-width: 0;
        flex: 1;
    }

    .mse-upload-dropzone__copy strong {
        display: block;
        color: #314b61;
        font-size: 13px;
        font-weight: 800;
    }

    .mse-upload-dropzone__copy span {
        display: block;
        color: #8595a7;
        font-size: 11px;
        line-height: 1.4;
        margin-top: 3px;
    }

    .mse-upload-dropzone__action {
        color: var(--mse-accent);
        background: var(--mse-accent-soft);
        border-radius: 11px;
        padding: 9px 12px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .mse-uploaded-file {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        color: #40596f;
        background: #fff;
        border: 1px solid #d5e4e8;
        border-radius: 15px;
        padding: 12px 13px;
    }

    .mse-uploaded-file--new {
        background: #f0faf6;
        border-color: #bde3d2;
    }

    .mse-uploaded-file__icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: #087b88;
        background: #e8f7f8;
        border-radius: 13px;
        font-size: 19px;
    }

    .mse-uploaded-file--new .mse-uploaded-file__icon {
        color: #087252;
        background: #ddf5e9;
    }

    .mse-uploaded-file__copy {
        min-width: 0;
        flex: 1;
    }

    .mse-uploaded-file__copy strong {
        display: block;
        overflow: hidden;
        color: #30485f;
        font-size: 13px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mse-uploaded-file__copy span {
        display: block;
        color: #8595a7;
        font-size: 11px;
        margin-top: 3px;
    }

    .mse-uploaded-file__actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
    }

    .mse-uploaded-file__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 36px;
        color: #3e6176 !important;
        background: #f7fafb;
        border: 1px solid #d8e4e9;
        border-radius: 10px;
        padding: 7px 10px;
        font: inherit;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none !important;
        cursor: pointer;
    }

    .mse-uploaded-file__button:hover {
        color: var(--mse-accent) !important;
        background: var(--mse-accent-soft);
        border-color: #afd8dd;
    }

    .mse-uploaded-file__button--danger {
        color: #a94848 !important;
        background: #fff8f8;
        border-color: #ecd0d0;
    }

    .mse-uploaded-file__button--danger:hover {
        color: #a13030 !important;
        background: #fff0f0;
        border-color: #dfa9a9;
    }

    .mse-upload-removal {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #765516;
        background: #fff9e9;
        border: 1px solid #eadca9;
        border-radius: 14px;
        padding: 11px 13px;
        font-size: 11px;
        line-height: 1.45;
    }

    .mse-upload-removal > i {
        color: #b27c12;
        font-size: 17px;
    }

    .mse-upload-removal > span {
        flex: 1;
    }

    .mse-upload-removal strong {
        display: block;
        color: #63470f;
    }

    .mse-upload-removal button {
        color: #6e551c;
        background: #fff;
        border: 1px solid #dbc98c;
        border-radius: 9px;
        padding: 7px 9px;
        font: inherit;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }

    .mse-consent-detail {
        display: flex;
        align-items: center;
        gap: 14px;
        color: #3d566c;
        background: linear-gradient(135deg, #f0fafb, #f8fcfd);
        border: 1px solid #cfe6e9;
        border-radius: 16px;
        padding: 15px;
    }

    .mse-consent-detail__icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: var(--mse-accent);
        background: #fff;
        border: 1px solid #d6e9eb;
        border-radius: 15px;
        font-size: 21px;
    }

    .mse-consent-detail__copy {
        min-width: 0;
        flex: 1;
    }

    .mse-consent-detail__copy strong {
        display: block;
        overflow: hidden;
        color: #30485f;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mse-consent-detail__copy span {
        display: block;
        color: var(--mse-muted);
        font-size: 12px;
        margin-top: 3px;
    }

    .mse-consent-detail__empty {
        color: #846c34;
        background: #fffaf0;
        border: 1px solid #eadfb9;
    }

    .mse-consent-detail > .mse-button {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .mse-consent-detail__actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .mse-consent-detail__actions .mse-button {
        white-space: nowrap;
    }

    .mse-table__file {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        width: fit-content;
        color: #087252;
        background: #ecfaf4;
        border: 1px solid #c6eadb;
        border-radius: 999px;
        padding: 4px 7px;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
        margin-top: 6px;
        white-space: nowrap;
    }

    .mse-table__file--missing {
        color: #a24d2d;
        background: #fff5ee;
        border-color: #f0cdbb;
    }


    .mse-consent-mode-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .mse-consent-mode-card {
        position: relative;
        display: grid;
        grid-template-columns: 56px minmax(0, 1fr);
        gap: 12px 14px;
        min-width: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid #d5e4e8;
        border-radius: 19px;
        padding: 17px;
        box-shadow: 0 10px 25px rgba(31, 65, 85, .05);
    }

    .mse-consent-mode-card::after {
        content: "";
        position: absolute;
        right: -36px;
        bottom: -46px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        pointer-events: none;
    }

    .mse-consent-mode-card--auto {
        background: linear-gradient(145deg, #fff 0%, #eff9fb 100%);
        border-color: #bcdde2;
    }

    .mse-consent-mode-card--auto::after {
        background: rgba(11, 133, 147, .08);
    }

    .mse-consent-mode-card--manual {
        background: linear-gradient(145deg, #fff 0%, #fff9ee 100%);
        border-color: #e4d3ad;
    }

    .mse-consent-mode-card--manual::after {
        background: rgba(180, 126, 27, .08);
    }

    .mse-consent-mode-card__icon {
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        grid-row: span 2;
        color: #087985;
        background: #e3f5f7;
        border: 1px solid #c6e6e9;
        border-radius: 17px;
        font-size: 23px;
    }

    .mse-consent-mode-card--manual .mse-consent-mode-card__icon {
        color: #94651b;
        background: #fff2d5;
        border-color: #ead5a8;
    }

    .mse-consent-mode-card__badge {
        position: absolute;
        top: 12px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        width: fit-content;
        color: #08717d;
        background: rgba(255, 255, 255, .86);
        border: 1px solid #cae2e6;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .mse-consent-mode-card--manual .mse-consent-mode-card__badge {
        color: #855a19;
        border-color: #e7d5b0;
    }

    .mse-consent-mode-card__copy {
        min-width: 0;
        padding-top: 4px;
        padding-right: 108px;
    }

    .mse-consent-mode-card__copy strong {
        display: block;
        color: #29465c;
        font-size: 15px;
        font-weight: 850;
        line-height: 1.3;
    }

    .mse-consent-mode-card__copy span {
        display: block;
        color: #788b9e;
        font-size: 11px;
        line-height: 1.5;
        margin-top: 5px;
    }

    .mse-button--consent-auto,
    .mse-button--consent-manual {
        position: relative;
        z-index: 1;
        grid-column: 1 / -1;
        width: 100%;
        min-height: 44px;
        margin-top: 2px;
    }

    .mse-button--consent-auto {
        color: #fff !important;
        background: linear-gradient(135deg, #078592, #176f7b);
        border-color: #087985;
        box-shadow: 0 8px 18px rgba(8, 126, 138, .17);
    }

    .mse-button--consent-auto:hover {
        color: #fff !important;
        background: linear-gradient(135deg, #07737e, #145f69);
    }

    .mse-button--consent-manual {
        color: #765018 !important;
        background: #fff8e9;
        border-color: #dec28b;
        box-shadow: 0 8px 18px rgba(140, 95, 20, .08);
    }

    .mse-button--consent-manual:hover {
        color: #66430f !important;
        background: #fff2d5;
        border-color: #d3ae65;
    }

    .mse-consent-upload-stage {
        display: grid;
        gap: 12px;
        background: rgba(255, 255, 255, .82);
        border: 1px solid #cce1dc;
        border-radius: 18px;
        padding: 16px;
    }

    .mse-consent-upload-stage__head {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mse-consent-upload-stage__icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: #087252;
        background: #e5f6ee;
        border-radius: 15px;
        font-size: 21px;
    }

    .mse-consent-upload-stage__head strong,
    .mse-consent-upload-stage__head span {
        display: block;
    }

    .mse-consent-upload-stage__head strong {
        color: #2c4a5d;
        font-size: 14px;
        font-weight: 850;
    }

    .mse-consent-upload-stage__head > div > span {
        color: #7c8e9e;
        font-size: 11px;
        line-height: 1.45;
        margin-top: 4px;
    }

    .mse-consent-upload-stage .mse-upload-dropzone {
        min-height: 86px;
    }

    @media (max-width: 1320px) {
        .mse-progress { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .mse-check-grid--3,
        .mse-switch-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 780px) {
        .mse-progress,
        .mse-check-grid,
        .mse-check-grid--3,
        .mse-switch-row { grid-template-columns: 1fr; }
        .mse-progress__copy small { display: none; }
        .mse-actions-bar { align-items: stretch; flex-direction: column; }
        .mse-actions-bar__buttons { width: 100%; }
        .mse-actions-bar__buttons .mse-button { flex: 1; }
        .mse-consent-workflow__head,
        .mse-consent-upload__head,
        .mse-uploaded-file,
        .mse-upload-removal,
        .mse-consent-detail { align-items: stretch; flex-direction: column; }
        .mse-consent-steps { grid-template-columns: 1fr; }
        .mse-consent-steps__arrow { min-height: 28px; transform: rotate(90deg); }
        .mse-consent-step .mse-upload-dropzone { align-items: stretch; }
        .mse-consent-upload-button { width: 100%; min-width: 0; }
        .mse-consent-upload__status { align-self: flex-start; }
        .mse-upload-dropzone { align-items: flex-start; flex-wrap: wrap; }
        .mse-upload-dropzone__action { margin-left: 62px; }
        .mse-uploaded-file__actions { width: 100%; }
        .mse-uploaded-file__button { flex: 1; }
        .mse-upload-removal button { align-self: flex-start; }
        .mse-consent-detail > .mse-button { width: 100%; }
        .mse-consent-detail__actions { width: 100%; }
        .mse-consent-detail__actions .mse-button { flex: 1; }
    }


    @media (max-width: 780px) {
        .mse-consent-mode-grid { grid-template-columns: 1fr; }
        .mse-consent-mode-card__copy { padding-right: 0; }
        .mse-consent-mode-card__badge {
            position: static;
            grid-column: 2;
            grid-row: 1;
            align-self: start;
            justify-self: start;
        }
        .mse-consent-upload-stage__head {
            align-items: flex-start;
        }
    }

</style>
