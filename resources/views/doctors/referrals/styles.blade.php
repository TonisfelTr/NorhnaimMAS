<style>
    .military-page {
        --military-ink: #1e3148;
        --military-muted: #8292a8;
        --military-line: #dbe6ec;
        --military-soft: #f5fafb;
        --military-accent: #008696;
        --military-accent-dark: #006c79;
        --military-accent-soft: #e9f7f8;
        --military-danger: #c13f3f;
        --military-success: #00865f;
        color: var(--military-ink);
        font-size: 14px;
        padding: 14px 14px 30px;
    }

    .military-container {
        width: min(1540px, 100%);
        margin: 0 auto;
    }

    .military-hero,
    .military-card,
    .military-filter,
    .military-table-card,
    .military-stat,
    .military-detail-card {
        background: #fff;
        border: 1px solid var(--military-line);
        border-radius: 18px;
        box-shadow: 0 8px 22px rgba(31, 65, 85, .045);
    }

    .military-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 22px;
        margin-bottom: 12px;
    }

    .military-hero__main,
    .military-hero__actions,
    .military-title-row,
    .military-actions,
    .military-patient-summary {
        display: flex;
        align-items: center;
    }

    .military-hero__main { gap: 13px; }
    .military-hero__actions { gap: 8px; flex-wrap: wrap; justify-content: flex-end; }

    .military-hero__icon,
    .military-card__icon,
    .military-stat__icon,
    .military-patient-summary__avatar {
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: var(--military-accent);
        background: var(--military-accent-soft);
    }

    .military-hero__icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        font-size: 21px;
    }

    .military-eyebrow {
        color: var(--military-accent);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .11em;
        text-transform: uppercase;
        margin-bottom: 3px;
    }

    .military-hero h1 {
        color: var(--military-ink);
        font-size: clamp(21px, 1.5vw, 28px);
        font-weight: 800;
        line-height: 1.15;
        margin: 0;
    }

    .military-hero p {
        color: var(--military-muted);
        font-size: 13px;
        margin: 4px 0 0;
    }

    .military-button {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid transparent;
        border-radius: 11px;
        padding: 7px 13px;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none !important;
        cursor: pointer;
        transition: .18s ease;
    }

    .military-button:hover { transform: translateY(-1px); }
    .military-button--primary { color: #fff !important; background: var(--military-accent); }
    .military-button--primary:hover { background: var(--military-accent-dark); }
    .military-button--secondary { color: var(--military-accent) !important; background: var(--military-accent-soft); border-color: #cce8eb; }
    .military-button--ghost { color: var(--military-ink) !important; background: #fff; border-color: var(--military-line); }
    .military-button--danger { color: var(--military-danger) !important; background: #fff; border-color: #f0d7d7; }

    .military-back {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border: 1px solid var(--military-line);
        border-radius: 11px;
        color: var(--military-ink) !important;
        background: #fff;
        text-decoration: none !important;
    }

    .military-alert {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        border: 1px solid;
        border-radius: 12px;
        padding: 10px 13px;
        margin: 0 0 12px;
    }

    .military-alert--success { color: #087252; background: #edfbf5; border-color: #cbeee0; }
    .military-alert--danger { color: #a33c3c; background: #fff4f4; border-color: #f0d2d2; }
    .military-alert ul { margin: 6px 0 0; padding-left: 19px; }

    .military-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .military-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 15px;
    }

    .military-stat__icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        font-size: 16px;
    }

    .military-stat__copy span { display: block; color: var(--military-muted); font-size: 12px; }
    .military-stat__copy strong { display: block; color: var(--military-ink); font-size: 21px; line-height: 1.05; margin-top: 2px; }

    .military-filter { padding: 15px 16px; margin-bottom: 12px; }
    .military-filter__head { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 10px; }
    .military-filter__head h2 { font-size: 16px; font-weight: 800; margin: 0; }
    .military-filter__head p { color: var(--military-muted); font-size: 12px; margin: 2px 0 0; }
    .military-filter__grid { display: grid; grid-template-columns: minmax(210px, 1.5fr) repeat(4, minmax(130px, .7fr)) auto; gap: 9px; align-items: end; }

    .military-field { min-width: 0; }
    .military-field label { display: block; color: #45566d; font-size: 12px; font-weight: 750; margin: 0 0 5px; }
    .military-field .required { color: #d43838; }
    .military-field input,
    .military-field select,
    .military-field textarea,
    .military-search-input {
        width: 100%;
        color: var(--military-ink);
        background: #fff;
        border: 1px solid #cbdbe3;
        border-radius: 11px;
        outline: none;
        padding: 8px 10px;
        font-size: 13px;
        transition: .18s ease;
    }

    .military-field input,
    .military-field select,
    .military-search-input { min-height: 38px; }
    .military-field textarea { min-height: 96px; resize: vertical; line-height: 1.5; }
    .military-field input:focus,
    .military-field select:focus,
    .military-field textarea:focus,
    .military-search-input:focus { border-color: var(--military-accent); box-shadow: 0 0 0 3px rgba(0, 134, 150, .11); }
    .military-field small { display: block; color: var(--military-muted); font-size: 11px; line-height: 1.35; margin-top: 4px; }
    .military-field .is-invalid { border-color: #d76666; }
    .military-field-error { color: #b53d3d !important; font-weight: 650; }

    .military-search-wrap { position: relative; }
    .military-search-wrap > i { position: absolute; z-index: 2; left: 12px; top: 50%; transform: translateY(-50%); color: #8ea0b3; }
    .military-search-wrap .military-search-input { padding-left: 34px; }
    .military-filter-reset { color: var(--military-accent); font-weight: 700; text-decoration: none; white-space: nowrap; }

    .military-table-card { overflow: visible; }
    .military-table-wrap { overflow-x: auto; border-radius: 18px 18px 0 0; }
    .military-table { width: 100%; min-width: 1000px; border-collapse: collapse; }
    .military-table th { color: #71839a; background: #f7fafb; font-size: 11px; font-weight: 800; letter-spacing: .035em; text-align: left; text-transform: uppercase; padding: 10px 12px; border-bottom: 1px solid var(--military-line); }
    .military-table td { vertical-align: middle; padding: 12px; border-bottom: 1px solid #e8eff3; }
    .military-table tr:last-child td { border-bottom: 0; }
    .military-table tbody tr:hover { background: #fbfdfd; }
    .military-table strong { color: var(--military-ink); }
    .military-table__muted { display: block; color: var(--military-muted); font-size: 11px; margin-top: 2px; }

    .military-patient-summary { gap: 9px; min-width: 210px; }
    .military-patient-summary__avatar { width: 36px; height: 36px; border-radius: 11px; font-weight: 800; }
    .military-patient-summary__copy { min-width: 0; }
    .military-patient-summary__copy strong,
    .military-patient-summary__copy span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 270px; }

    .military-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 9px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }
    .military-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .military-badge--draft { color: #9a6900; background: #fff5df; }
    .military-badge--prepared { color: #007a58; background: #e8f9f2; }

    .military-actions { gap: 6px; justify-content: flex-end; }
    .military-action {
        position: relative;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        color: #52708a;
        background: #fff;
        border: 1px solid var(--military-line);
        border-radius: 10px;
        text-decoration: none !important;
        cursor: pointer;
    }
    .military-action:hover { color: var(--military-accent); border-color: #acd7dc; background: #f4fbfb; }
    .military-action--danger:hover { color: var(--military-danger); border-color: #e7bcbc; background: #fff7f7; }
    .military-action--disabled { color: #b7c1cb; background: #f8fafb; cursor: not-allowed; }
    .military-action-form { margin: 0; }

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

    .military-empty { padding: 34px 20px; text-align: center; }
    .military-empty i { display: block; color: #a8c9cd; font-size: 32px; margin-bottom: 8px; }
    .military-empty h3 { font-size: 16px; margin: 0 0 5px; }
    .military-empty p { color: var(--military-muted); margin: 0 0 12px; }
    .military-table-footer { display: flex; align-items: center; justify-content: space-between; gap: 14px; color: var(--military-muted); padding: 12px 15px; border-top: 1px solid var(--military-line); }

    .military-form-layout { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 14px; align-items: start; }
    .military-form-main { display: grid; gap: 14px; }
    .military-card { padding: 18px; }
    .military-card__head { display: flex; align-items: center; gap: 10px; padding-bottom: 12px; margin-bottom: 14px; border-bottom: 1px solid #e8eff3; }
    .military-card__icon { width: 38px; height: 38px; border-radius: 12px; font-size: 16px; }
    .military-card__head h2 { font-size: 16px; font-weight: 800; margin: 0; }
    .military-card__head p { color: var(--military-muted); font-size: 12px; margin: 2px 0 0; }
    .military-step { margin-left: auto; color: var(--military-accent); background: var(--military-accent-soft); border-radius: 999px; padding: 5px 8px; font-size: 11px; font-weight: 800; }
    .military-grid { display: grid; gap: 12px; }
    .military-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .military-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .military-field--full { grid-column: 1 / -1; }

    .military-patient-search { position: relative; }
    .military-control-wrap { position: relative; }
    .military-control-wrap > i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8ba0b2; }
    .military-control-wrap input { padding-left: 34px; padding-right: 40px; }
    .military-clear { position: absolute; right: 9px; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; display: grid; place-items: center; color: #8495a8; background: transparent; border: 0; border-radius: 9px; }
    .military-clear:hover { color: var(--military-danger); background: #fff2f2; }
    .military-suggestions { position: absolute; z-index: 25; top: calc(100% + 7px); left: 0; right: 0; max-height: 300px; overflow-y: auto; background: #fff; border: 1px solid var(--military-line); border-radius: 15px; box-shadow: 0 18px 45px rgba(29, 58, 78, .16); padding: 6px; }
    .military-suggestion { width: 100%; display: flex; align-items: center; gap: 11px; color: var(--military-ink); background: transparent; border: 0; border-radius: 11px; padding: 11px; text-align: left; cursor: pointer; }
    .military-suggestion:hover,
    .military-suggestion:focus { background: var(--military-accent-soft); outline: none; }
    .military-suggestion__icon { width: 36px; height: 36px; display: grid; place-items: center; flex: 0 0 auto; color: var(--military-accent); background: #fff; border: 1px solid #d4e8ea; border-radius: 11px; }
    .military-suggestion__copy { min-width: 0; }
    .military-suggestion__copy strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .military-suggestion__copy span { display: block; color: var(--military-muted); font-size: 12px; margin-top: 2px; }
    .military-suggestion-message { color: var(--military-muted); padding: 14px; text-align: center; }
    .d-none { display: none !important; }

    .military-selected-patient { display: flex; align-items: center; gap: 10px; background: var(--military-soft); border: 1px solid #d9eaed; border-radius: 12px; padding: 11px; margin-top: 10px; }
    .military-selected-patient.is-empty { opacity: .72; }
    .military-selected-patient__avatar { width: 38px; height: 38px; display: grid; place-items: center; flex: 0 0 auto; color: var(--military-accent); background: #fff; border-radius: 11px; font-size: 16px; }
    .military-selected-patient__copy { min-width: 0; }
    .military-selected-patient__copy strong { display: block; }
    .military-selected-patient__copy span { display: block; color: var(--military-muted); font-size: 11px; margin-top: 1px; }

    .military-sidebar { position: sticky; top: 14px; display: grid; gap: 10px; }
    .military-sidebar-card { background: #fff; border: 1px solid var(--military-line); border-radius: 15px; padding: 15px; }
    .military-sidebar-card h3 { font-size: 14px; font-weight: 800; margin: 0 0 6px; }
    .military-sidebar-card p { color: var(--military-muted); font-size: 11px; line-height: 1.45; margin: 0; }
    .military-sidebar-card--notice { color: #5d4b16; background: #fffaf0; border-color: #eee1b7; }
    .military-sidebar-card--notice p { color: #756631; }
    .military-sidebar-actions { display: grid; gap: 10px; }
    .military-sidebar-actions .military-button { width: 100%; }

    .military-details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .military-detail-card { padding: 18px; }
    .military-detail-card--wide { grid-column: 1 / -1; }
    .military-detail-card h2 { display: flex; align-items: center; gap: 7px; font-size: 16px; font-weight: 800; padding-bottom: 10px; margin: 0 0 12px; border-bottom: 1px solid #e8eff3; }
    .military-detail-card h2 i { color: var(--military-accent); }
    .military-detail-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 16px; margin: 0; }
    .military-detail-list > div { min-width: 0; }
    .military-detail-list dt { color: var(--military-muted); font-size: 12px; font-weight: 700; margin-bottom: 4px; }
    .military-detail-list dd { color: var(--military-ink); font-weight: 650; margin: 0; overflow-wrap: anywhere; }
    .military-text-block { color: #394d64; line-height: 1.65; white-space: pre-wrap; overflow-wrap: anywhere; }
    .military-text-block.is-empty { color: var(--military-muted); font-style: italic; }

    @media (max-width: 1200px) {
        .military-filter__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .military-form-layout { grid-template-columns: 1fr; }
        .military-sidebar { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 780px) {
        .military-page { padding: 10px 8px 24px; }
        .military-hero { align-items: flex-start; flex-direction: column; padding: 15px; border-radius: 16px; }
        .military-hero__actions { width: 100%; justify-content: flex-start; }
        .military-stats,
        .military-grid--2,
        .military-grid--3,
        .military-details-grid,
        .military-detail-list,
        .military-sidebar { grid-template-columns: 1fr; }
        .military-filter__grid { grid-template-columns: 1fr; }
        .military-filter__head { flex-direction: column; }
        .military-card,
        .military-detail-card { padding: 15px; border-radius: 15px; }
        .military-card__head { align-items: flex-start; }
        .military-table-footer { align-items: flex-start; flex-direction: column; }
    }
</style>
