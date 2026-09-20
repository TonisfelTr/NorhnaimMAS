@extends('doctors.prescriptions')
@section('title', 'Военкомат / ВВК')
@section('assets')
    @parent
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

        .military-table-card { overflow: hidden; }
        .military-table-wrap {
            max-width: 100%;
            overflow-x: auto;
            border-radius: 18px 18px 0 0;
            scrollbar-width: thin;
            scrollbar-color: #c5d7df transparent;
        }
        .military-table {
            width: 100%;
            min-width: 1120px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .military-table__col--document { width: 13%; }
        .military-table__col--patient { width: 31%; }
        .military-table__col--date { width: 11%; }
        .military-table__col--recipient { width: 22%; }
        .military-table__col--status { width: 13%; }
        .military-table__col--actions { width: 180px; }
        .military-table th {
            color: #71839a;
            background: #f7fafb;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .035em;
            line-height: 1.25;
            text-align: left;
            text-transform: uppercase;
            padding: 11px 12px;
            border-bottom: 1px solid var(--military-line);
        }
        .military-table td {
            min-width: 0;
            vertical-align: middle;
            padding: 12px;
            border-bottom: 1px solid #e8eff3;
        }
        .military-table th:last-child,
        .military-table td:last-child { text-align: right; }
        .military-table tr:last-child td { border-bottom: 0; }
        .military-table tbody tr:hover { background: #fbfdfd; }
        .military-table strong { color: var(--military-ink); }
        .military-table__muted { display: block; color: var(--military-muted); font-size: 11px; margin-top: 2px; }

        .military-patient-summary { gap: 10px; min-width: 0; }
        .military-patient-summary__avatar { width: 36px; height: 36px; border-radius: 11px; font-weight: 800; }
        .military-patient-summary__copy { display: block; min-width: 0; }
        .military-patient-summary__copy strong {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .military-patient-summary__copy span {
            display: -webkit-box;
            overflow: hidden;
            color: #61738a;
            font-size: 12px;
            line-height: 1.35;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .military-recipient { min-width: 0; }
        .military-recipient__name {
            display: -webkit-box;
            overflow: hidden;
            line-height: 1.3;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .military-recipient__address {
            display: block;
            overflow: hidden;
            margin-top: 3px;
            color: var(--military-muted);
            font-size: 11px;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .military-recipient__request {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            margin-top: 6px;
            padding: 3px 7px;
            overflow: hidden;
            color: #52677d;
            background: #f1f6f8;
            border-radius: 7px;
            font-size: 10px;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .military-table__document-number {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .military-table__document-type {
            display: -webkit-box;
            overflow: hidden;
            line-height: 1.35;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

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
        .military-table .military-actions {
            flex-wrap: nowrap;
            justify-content: flex-end;
            gap: 5px;
        }

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

        /* VVK_BALANCED_DESKTOP_V3 */
        @media (min-width: 1201px) {
            .military-page {
                font-size: 14px;
                padding: 12px 14px 24px;
            }

            .military-hero {
                min-height: 0;
                gap: 16px;
                padding: 15px 20px;
                margin-bottom: 10px;
                border-radius: 16px;
            }

            .military-hero__main { gap: 14px; }
            .military-hero__icon {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                font-size: 21px;
            }

            .military-eyebrow {
                font-size: 10px;
                line-height: 1.2;
                margin-bottom: 3px;
            }

            .military-hero h1 {
                font-size: 27px;
                line-height: 1.12;
            }

            .military-hero p {
                font-size: 14px;
                line-height: 1.35;
                margin-top: 4px;
            }

            .military-button {
                min-height: 40px;
                gap: 7px;
                padding: 7px 15px;
                border-radius: 11px;
                font-size: 13px;
            }

            .military-stats {
                gap: 0;
                margin-bottom: 10px;
                overflow: hidden;
                background: #fff;
                border: 1px solid var(--military-line);
                border-radius: 16px;
                box-shadow: 0 5px 16px rgba(31, 65, 85, .04);
            }

            .military-stat {
                min-height: 68px;
                gap: 11px;
                padding: 10px 16px;
                border: 0;
                border-right: 1px solid var(--military-line);
                border-radius: 0;
                box-shadow: none;
            }

            .military-stat:last-child { border-right: 0; }
            .military-stat__icon {
                width: 40px;
                height: 40px;
                border-radius: 11px;
                font-size: 16px;
            }

            .military-stat__copy span {
                font-size: 12px;
                line-height: 1.2;
            }

            .military-stat__copy strong {
                font-size: 22px;
                line-height: 1.05;
                margin-top: 2px;
            }

            .military-filter {
                padding: 9px 11px 10px;
                margin-bottom: 8px;
                border-radius: 14px;
            }

            .military-filter__head { margin-bottom: 6px; }
            .military-filter__head h2 {
                font-size: 14px;
                line-height: 1.2;
            }

            .military-filter__head p { display: none; }
            .military-filter__grid {
                grid-template-columns: minmax(180px, 1.45fr) repeat(4, minmax(108px, .72fr)) auto;
                gap: 7px;
            }

            .military-filter .military-field label {
                font-size: 10px;
                line-height: 1.1;
                margin-bottom: 3px;
            }

            .military-filter .military-field input,
            .military-filter .military-field select,
            .military-filter .military-search-input {
                min-height: 33px;
                padding: 5px 8px;
                border-radius: 9px;
                font-size: 12px;
            }

            .military-filter .military-search-wrap .military-search-input { padding-left: 29px; }
            .military-filter .military-search-wrap > i {
                left: 10px;
                font-size: 11px;
            }

            .military-empty { padding: 24px 18px; }
        }

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


        /* VVK_RICH_UI_V4 */
        .military-page {
            position: relative;
            min-height: calc(100vh - 70px);
            overflow: hidden;
            background:
                radial-gradient(circle at 7% 4%, rgba(0, 134, 150, .09), transparent 28%),
                radial-gradient(circle at 92% 12%, rgba(74, 117, 162, .07), transparent 25%),
                linear-gradient(180deg, #f8fbfc 0%, #f3f8fa 100%);
        }

        .military-page::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: .34;
            background-image:
                linear-gradient(rgba(111, 144, 158, .075) 1px, transparent 1px),
                linear-gradient(90deg, rgba(111, 144, 158, .075) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: linear-gradient(to bottom, #000, transparent 42%);
        }

        .military-container {
            position: relative;
            z-index: 1;
        }

        .military-hero {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(112deg, rgba(255, 255, 255, .98) 0%, rgba(247, 253, 253, .97) 66%, rgba(232, 248, 249, .94) 100%);
            box-shadow: 0 15px 45px rgba(24, 64, 86, .07);
        }

        .military-hero::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            background: linear-gradient(180deg, #00a2ad, #006c79);
        }

        .military-hero::after {
            content: '';
            position: absolute;
            width: 210px;
            height: 210px;
            right: -72px;
            top: -112px;
            border: 34px solid rgba(0, 134, 150, .055);
            border-radius: 50%;
        }

        .military-hero__main,
        .military-hero__actions {
            position: relative;
            z-index: 1;
        }

        .military-hero__copy {
            min-width: 0;
        }

        .military-breadcrumbs {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            color: #7a8da2;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .025em;
            margin-bottom: 5px;
        }

        .military-breadcrumbs i {
            color: #aab8c4;
            font-size: 9px;
        }

        .military-hero__icon {
            position: relative;
            border: 1px solid #cdebed;
            box-shadow: 0 10px 24px rgba(0, 134, 150, .13);
        }

        .military-hero__icon::after {
            content: '';
            position: absolute;
            right: -4px;
            bottom: -4px;
            width: 15px;
            height: 15px;
            border: 3px solid #fff;
            border-radius: 50%;
            background: var(--military-success);
        }

        .military-hero__meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 9px;
        }

        .military-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 25px;
            color: #557087;
            background: rgba(255, 255, 255, .76);
            border: 1px solid #dcebed;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 750;
            backdrop-filter: blur(7px);
        }

        .military-meta-chip i {
            color: var(--military-accent);
        }

        .military-meta-chip--active {
            color: #006a77;
            background: #e7f8f8;
            border-color: #bde3e6;
        }

        .military-button {
            box-shadow: 0 5px 13px rgba(39, 75, 94, .04);
        }

        .military-button:focus-visible,
        .military-action:focus-visible,
        .military-icon-button:focus-visible,
        .military-status-tab:focus-visible,
        .military-filter-chip:focus-visible {
            outline: 3px solid rgba(0, 134, 150, .18);
            outline-offset: 2px;
        }

        .military-button--primary {
            background: linear-gradient(135deg, #0096a5, #007785);
            box-shadow: 0 10px 22px rgba(0, 134, 150, .20);
        }

        .military-button--primary:hover {
            background: linear-gradient(135deg, #008a98, #006875);
            box-shadow: 0 12px 24px rgba(0, 108, 121, .24);
        }

        .military-button__counter {
            display: grid;
            place-items: center;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            color: var(--military-accent);
            background: #fff;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
        }

        .military-alert {
            position: relative;
            box-shadow: 0 8px 22px rgba(43, 79, 98, .05);
        }

        .military-alert__content {
            min-width: 0;
            flex: 1;
        }

        .military-alert__close {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: currentColor;
            background: transparent;
            border: 0;
            border-radius: 8px;
            opacity: .65;
            cursor: pointer;
        }

        .military-alert__close:hover {
            background: rgba(255, 255, 255, .58);
            opacity: 1;
        }

        .military-stats.military-stats--enhanced {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .military-stats--enhanced .military-stat {
            position: relative;
            min-height: 90px;
            overflow: hidden;
            color: inherit;
            border: 1px solid var(--military-line);
            border-radius: 17px;
            padding: 14px 15px 16px;
            text-decoration: none !important;
            box-shadow: 0 10px 28px rgba(31, 65, 85, .055);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .military-stats--enhanced a.military-stat:hover {
            transform: translateY(-2px);
            border-color: #bcdce0;
            box-shadow: 0 15px 33px rgba(31, 65, 85, .09);
        }

        .military-stats--enhanced .military-stat::before {
            content: '';
            position: absolute;
            width: 92px;
            height: 92px;
            right: -40px;
            top: -45px;
            border-radius: 50%;
            background: currentColor;
            opacity: .045;
        }

        .military-stat--total { color: #26708b !important; }
        .military-stat--draft { color: #9a6900 !important; }
        .military-stat--prepared { color: #007a58 !important; }
        .military-stat--readiness { color: #6553a5 !important; }

        .military-stat--total .military-stat__icon { color: #26708b; background: #eaf5f8; }
        .military-stat--draft .military-stat__icon { color: #9a6900; background: #fff4dc; }
        .military-stat--prepared .military-stat__icon { color: #007a58; background: #e8f8f1; }

        .military-stats--enhanced .military-stat__copy {
            min-width: 0;
            flex: 1;
        }

        .military-stats--enhanced .military-stat__copy span {
            color: #74879b;
            font-size: 11px;
            font-weight: 700;
        }

        .military-stats--enhanced .military-stat__copy strong {
            color: var(--military-ink);
            font-size: 24px;
            margin-top: 3px;
        }

        .military-stat__copy small {
            display: block;
            overflow: hidden;
            color: #91a0af;
            font-size: 10px;
            line-height: 1.25;
            margin-top: 4px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .military-stat__arrow {
            display: grid;
            place-items: center;
            width: 25px;
            height: 25px;
            flex: 0 0 auto;
            color: currentColor;
            background: rgba(255, 255, 255, .68);
            border: 1px solid currentColor;
            border-radius: 8px;
            font-size: 10px;
            opacity: .55;
        }

        .military-stat__progress {
            position: absolute;
            inset: auto 0 0;
            height: 3px;
            overflow: hidden;
            background: #eef4f6;
        }

        .military-stat__progress span {
            display: block;
            height: 100%;
            border-radius: 0 999px 999px 0;
            background: currentColor;
        }

        .military-progress-ring {
            --progress: 0;
            position: relative;
            width: 49px;
            height: 49px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 50%;
            background: conic-gradient(currentColor calc(var(--progress) * 1%), #e8edf5 0);
        }

        .military-progress-ring::before {
            content: '';
            position: absolute;
            inset: 5px;
            border-radius: 50%;
            background: #fff;
        }

        .military-progress-ring span {
            position: relative;
            z-index: 1;
            color: var(--military-ink);
            font-size: 10px;
            font-weight: 900;
        }

        .military-workspace-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, .88);
            border: 1px solid var(--military-line);
            border-radius: 16px;
            box-shadow: 0 8px 23px rgba(31, 65, 85, .04);
            backdrop-filter: blur(8px);
        }

        .military-status-tabs {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            background: #f0f5f7;
            border-radius: 12px;
        }

        .military-status-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 32px;
            color: #667b8e !important;
            border: 1px solid transparent;
            border-radius: 9px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none !important;
            white-space: nowrap;
            transition: .16s ease;
        }

        .military-status-tab:hover {
            color: var(--military-accent) !important;
            background: rgba(255, 255, 255, .72);
        }

        .military-status-tab.is-active {
            color: var(--military-ink) !important;
            background: #fff;
            border-color: #dce7eb;
            box-shadow: 0 4px 12px rgba(31, 65, 85, .07);
        }

        .military-status-tab__count {
            display: grid;
            place-items: center;
            min-width: 21px;
            height: 19px;
            padding: 0 5px;
            color: #60768a;
            background: #eaf0f3;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 900;
        }

        .military-status-tab.is-active .military-status-tab__count {
            color: #fff;
            background: var(--military-accent);
        }

        .military-workflow-summary {
            min-width: 310px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px 13px;
        }

        .military-workflow-summary__copy span {
            display: block;
            color: #7a8da0;
            font-size: 10px;
            font-weight: 750;
        }

        .military-workflow-summary__copy strong {
            display: block;
            margin-top: 2px;
            color: var(--military-ink);
            font-size: 12px;
        }

        .military-workflow-summary__value {
            color: var(--military-success);
            font-size: 18px;
            font-weight: 900;
        }

        .military-workflow-progress {
            grid-column: 1 / -1;
            height: 5px;
            overflow: hidden;
            background: #e8f0f2;
            border-radius: 999px;
        }

        .military-workflow-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #00a27b, #008696);
        }

        .military-filter {
            position: relative;
            transition: .2s ease;
        }

        .military-filter::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, var(--military-accent), #69bbc3 52%, transparent);
            opacity: .9;
        }

        .military-filter__head {
            align-items: center;
        }

        .military-filter__title-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .military-filter__title-icon {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            color: var(--military-accent);
            background: var(--military-accent-soft);
            border-radius: 9px;
            font-size: 13px;
        }

        .military-filter__head-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .military-filter__counter {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 28px;
            color: #006e7a;
            background: #e7f8f8;
            border: 1px solid #c4e7e9;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 10px;
            font-weight: 850;
        }

        .military-icon-button {
            position: relative;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: #5e7488;
            background: #fff;
            border: 1px solid var(--military-line);
            border-radius: 10px;
            cursor: pointer;
            transition: .16s ease;
        }

        .military-icon-button:hover {
            color: var(--military-accent);
            background: #f5fbfb;
            border-color: #b7dde1;
        }

        .military-icon-button[aria-expanded="false"] .bi-chevron-up {
            transform: rotate(180deg);
        }

        .military-icon-button i {
            transition: transform .18s ease;
        }

        .military-filter__body {
            animation: militaryReveal .18s ease;
        }

        .military-filter.is-collapsed {
            padding-bottom: 10px;
        }

        .military-filter.is-collapsed .military-filter__head {
            margin-bottom: 0;
        }

        .military-filter.is-collapsed .military-filter__body {
            display: none;
        }

        @keyframes militaryReveal {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .military-active-filters {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            padding: 8px;
            margin-bottom: 10px;
            background: #f7fafb;
            border: 1px dashed #cedee4;
            border-radius: 12px;
        }

        .military-active-filters__label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #7d8fa1;
            padding: 0 4px;
            font-size: 10px;
            font-weight: 800;
        }

        .military-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 320px;
            min-height: 28px;
            color: #36566d !important;
            background: #fff;
            border: 1px solid #d7e4e9;
            border-radius: 999px;
            padding: 4px 7px 4px 9px;
            font-size: 10px;
            font-weight: 750;
            text-decoration: none !important;
            transition: .15s ease;
        }

        .military-filter-chip:hover {
            color: #9d3939 !important;
            background: #fff7f7;
            border-color: #edcaca;
        }

        .military-filter-chip > span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .military-filter-chip small {
            color: #91a0af;
            font-size: 9px;
            font-weight: 750;
            margin-right: 3px;
        }

        .military-filter-chip__remove {
            width: 17px;
            height: 17px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: #8ea0ae;
            background: #edf3f5;
            border-radius: 50%;
            font-size: 9px;
        }

        .military-search-wrap .military-search-input {
            padding-right: 38px;
        }

        .military-search-clear {
            position: absolute;
            z-index: 2;
            right: 5px;
            top: 50%;
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            color: #8c9dac;
            background: transparent;
            border: 0;
            border-radius: 8px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .military-search-clear:hover {
            color: var(--military-danger);
            background: #fff1f1;
        }

        .military-filter__foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #8798a8;
            margin-top: 9px;
            font-size: 10px;
        }

        .military-key-hint {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .military-key-hint kbd {
            color: #607589;
            background: #f1f5f7;
            border: 1px solid #d6e1e5;
            border-bottom-width: 2px;
            border-radius: 5px;
            padding: 1px 5px;
            font-family: inherit;
            font-size: 9px;
            font-weight: 800;
        }

        .military-table-card {
            position: relative;
            box-shadow: 0 14px 36px rgba(31, 65, 85, .065);
        }

        .military-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--military-line);
            background: linear-gradient(180deg, #fff, #fbfdfd);
        }

        .military-table-toolbar__main {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .military-table-toolbar__icon {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: var(--military-accent);
            background: var(--military-accent-soft);
            border-radius: 11px;
        }

        .military-table-toolbar h2 {
            color: var(--military-ink);
            font-size: 15px;
            font-weight: 850;
            margin: 0;
        }

        .military-table-toolbar p {
            color: var(--military-muted);
            font-size: 10px;
            margin: 2px 0 0;
        }

        .military-results-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #526c81;
            background: #f1f6f8;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .military-table-toolbar__actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .military-toolbar-button {
            position: relative;
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #5b7286 !important;
            background: #fff;
            border: 1px solid var(--military-line);
            border-radius: 10px;
            padding: 5px 9px;
            font-size: 10px;
            font-weight: 800;
            text-decoration: none !important;
            cursor: pointer;
            transition: .16s ease;
        }

        .military-toolbar-button:hover {
            color: var(--military-accent) !important;
            background: #f5fbfb;
            border-color: #b9dde1;
        }

        .military-toolbar-button[aria-pressed="true"] {
            color: #fff !important;
            background: var(--military-accent);
            border-color: var(--military-accent);
        }

        .military-table th {
            position: sticky;
            z-index: 3;
            top: 0;
            box-shadow: inset 0 -1px 0 var(--military-line);
        }

        .military-table tbody tr {
            position: relative;
            transition: background .16s ease, box-shadow .16s ease;
        }

        .military-table tbody tr:hover {
            background: linear-gradient(90deg, #fbfefe, #f8fcfc);
            box-shadow: inset 3px 0 0 rgba(0, 134, 150, .42);
        }

        .military-row--draft td:first-child {
            border-left: 3px solid #e7b64a;
        }

        .military-row--prepared td:first-child {
            border-left: 3px solid #32ac84;
        }

        .military-document-cell,
        .military-date-block {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .military-document-icon,
        .military-date-icon {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: #51758d;
            background: #edf5f7;
            border-radius: 10px;
            font-size: 13px;
        }

        .military-document-icon--draft {
            color: #9a6900;
            background: #fff3d9;
        }

        .military-document-icon--prepared {
            color: #007a58;
            background: #e6f8f0;
        }

        .military-document-cell__copy,
        .military-date-block__copy {
            min-width: 0;
        }

        .military-patient-summary__avatar {
            position: relative;
            border: 1px solid #d4e9eb;
            box-shadow: inset 0 0 0 3px rgba(255, 255, 255, .62);
        }

        .military-patient-summary__avatar::after {
            content: '';
            position: absolute;
            right: -2px;
            bottom: -2px;
            width: 9px;
            height: 9px;
            background: #35ad86;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        .military-diagnosis-line {
            display: flex !important;
            align-items: center;
            gap: 5px;
            margin-top: 3px;
        }

        .military-diagnosis-line i {
            flex: 0 0 auto;
            color: #8aa1b3;
            font-size: 10px;
        }

        .military-date-block__copy strong {
            display: block;
            white-space: nowrap;
        }

        .military-recipient__name {
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .military-recipient__name > i {
            flex: 0 0 auto;
            color: #7f9aaa;
            margin-top: 2px;
            font-size: 11px;
        }

        .military-recipient__request {
            gap: 4px;
            border: 1px solid #e0eaee;
        }

        .military-status-stack {
            display: grid;
            justify-items: start;
            gap: 5px;
        }

        .military-status-hint {
            color: #8798a7;
            font-size: 9px;
            line-height: 1.25;
        }

        .military-action {
            transition: transform .15s ease, color .15s ease, border-color .15s ease, background .15s ease;
        }

        .military-action:hover {
            transform: translateY(-1px);
        }

        .military-action--view {
            color: #fff !important;
            background: var(--military-accent);
            border-color: var(--military-accent);
            box-shadow: 0 5px 12px rgba(0, 134, 150, .17);
        }

        .military-action--view:hover {
            color: #fff !important;
            background: var(--military-accent-dark);
            border-color: var(--military-accent-dark);
        }

        .military-actions__separator {
            width: 1px;
            height: 22px;
            margin: 0 1px;
            background: #e0e9ed;
        }

        .military-table-card.is-compact .military-table td {
            padding-top: 7px;
            padding-bottom: 7px;
        }

        .military-table-card.is-compact .military-patient-summary__avatar,
        .military-table-card.is-compact .military-document-icon,
        .military-table-card.is-compact .military-date-icon {
            width: 29px;
            height: 29px;
            border-radius: 9px;
            font-size: 11px;
        }

        .military-table-card.is-compact .military-recipient__address,
        .military-table-card.is-compact .military-status-hint,
        .military-table-card.is-compact .military-table__muted {
            display: none;
        }

        .military-table-card.is-compact .military-recipient__request {
            margin-top: 3px;
        }

        .military-table-footer__summary {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .military-table-footer__page {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #687e91;
            background: #f2f6f8;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 750;
        }

        .military-table-footer .pagination {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            margin: 0;
        }

        .military-table-footer .page-link {
            min-width: 31px;
            height: 31px;
            display: grid;
            place-items: center;
            color: #587087;
            background: #fff;
            border: 1px solid #d9e5e9;
            border-radius: 9px !important;
            padding: 4px 7px;
            font-size: 10px;
            font-weight: 750;
            box-shadow: none !important;
        }

        .military-table-footer .page-item.active .page-link {
            color: #fff;
            background: var(--military-accent);
            border-color: var(--military-accent);
        }

        .military-empty {
            position: relative;
            min-height: 310px;
            display: grid;
            place-items: center;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 48%, rgba(0, 134, 150, .075), transparent 27%),
                linear-gradient(180deg, #fff, #fbfdfd);
        }

        .military-empty::before,
        .military-empty::after {
            content: '';
            position: absolute;
            border: 1px solid rgba(0, 134, 150, .10);
            border-radius: 50%;
        }

        .military-empty::before { width: 230px; height: 230px; }
        .military-empty::after { width: 170px; height: 170px; }

        .military-empty__content {
            position: relative;
            z-index: 1;
            max-width: 430px;
        }

        .military-empty__visual {
            position: relative;
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            margin: 0 auto 13px;
            color: var(--military-accent);
            background: #fff;
            border: 1px solid #cfe7e9;
            border-radius: 21px;
            box-shadow: 0 16px 35px rgba(0, 134, 150, .13);
        }

        .military-empty__visual i {
            color: inherit;
            font-size: 28px;
            margin: 0;
        }

        .military-empty__visual span {
            position: absolute;
            right: -5px;
            bottom: -5px;
            width: 24px;
            height: 24px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--military-success);
            border: 3px solid #fff;
            border-radius: 50%;
            font-size: 10px;
        }

        .military-empty__actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .military-floating-create {
            display: none;
        }

        .military-sr-only {
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

        @media (max-width: 1180px) {
            .military-stats.military-stats--enhanced {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .military-workspace-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .military-workflow-summary {
                min-width: 0;
            }
        }

        @media (max-width: 900px) {
            .military-table-toolbar {
                align-items: flex-start;
            }

            .military-table-toolbar__actions {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .military-toolbar-button span {
                display: none;
            }

            .military-toolbar-button {
                width: 34px;
                padding: 5px;
            }
        }

        @media (max-width: 780px) {
            .military-page {
                overflow: visible;
                background: #f4f8fa;
            }

            .military-page::before {
                display: none;
            }

            .military-hero::after {
                right: -118px;
            }

            .military-hero__main {
                align-items: flex-start;
            }

            .military-hero__meta {
                margin-left: -63px;
                padding-top: 8px;
            }

            .military-hero__actions .military-button--ghost {
                flex: 1;
            }

            .military-hero__actions .military-button--primary {
                flex: 2;
            }

            .military-stats.military-stats--enhanced {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .military-stats--enhanced .military-stat {
                min-height: 86px;
                padding: 12px 12px 15px;
            }

            .military-stats--enhanced .military-stat__icon {
                width: 34px;
                height: 34px;
            }

            .military-stats--enhanced .military-stat__copy strong {
                font-size: 21px;
            }

            .military-stat__arrow {
                display: none;
            }

            .military-progress-ring {
                width: 43px;
                height: 43px;
            }

            .military-status-tabs {
                width: 100%;
                overflow-x: auto;
                scrollbar-width: none;
            }

            .military-status-tabs::-webkit-scrollbar {
                display: none;
            }

            .military-status-tab {
                flex: 1 0 auto;
            }

            .military-filter__head-actions .military-filter-reset {
                display: none;
            }

            .military-active-filters {
                align-items: stretch;
            }

            .military-active-filters__label {
                width: 100%;
            }

            .military-filter-chip {
                max-width: 100%;
            }

            .military-filter__foot {
                align-items: flex-start;
                flex-direction: column;
            }

            .military-table-toolbar {
                padding: 11px;
            }

            .military-table-toolbar p,
            .military-results-pill {
                display: none;
            }

            .military-table-wrap {
                overflow: visible;
                border-radius: 0;
                background: #f4f8fa;
            }

            .military-table {
                min-width: 0;
                display: block;
                background: transparent;
            }

            .military-table colgroup,
            .military-table thead {
                display: none;
            }

            .military-table tbody {
                display: grid;
                gap: 10px;
                padding: 10px;
            }

            .military-table tbody tr {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 0 10px;
                overflow: hidden;
                background: #fff;
                border: 1px solid var(--military-line);
                border-radius: 15px;
                box-shadow: 0 7px 20px rgba(31, 65, 85, .045);
            }

            .military-table tbody tr:hover {
                background: #fff;
                box-shadow: 0 9px 25px rgba(31, 65, 85, .075);
            }

            .military-row--draft td:first-child,
            .military-row--prepared td:first-child {
                border-left: 0;
            }

            .military-row--draft {
                border-left: 4px solid #e7b64a !important;
            }

            .military-row--prepared {
                border-left: 4px solid #32ac84 !important;
            }

            .military-table td,
            .military-table-card.is-compact .military-table td {
                display: block;
                padding: 10px 11px;
                border: 0;
                border-bottom: 1px solid #edf2f4;
            }

            .military-table td::before {
                content: attr(data-label);
                display: block;
                color: #91a0af;
                margin-bottom: 5px;
                font-size: 9px;
                font-weight: 850;
                letter-spacing: .045em;
                text-transform: uppercase;
            }

            .military-table td.military-cell--document {
                grid-column: 1;
                border-bottom: 0;
            }

            .military-table td.military-cell--status {
                grid-column: 2;
                align-self: start;
                border-bottom: 0;
                text-align: right;
            }

            .military-table td.military-cell--document::before,
            .military-table td.military-cell--status::before {
                display: none;
            }

            .military-table td.military-cell--patient,
            .military-table td.military-cell--date,
            .military-table td.military-cell--recipient,
            .military-table td.military-cell--actions {
                grid-column: 1 / -1;
            }

            .military-table td.military-cell--actions {
                border-bottom: 0;
                background: #fafcfd;
            }

            .military-table td.military-cell--actions::before {
                display: none;
            }

            .military-table .military-actions {
                justify-content: flex-start;
            }

            .military-table .military-action {
                flex: 1;
                max-width: 44px;
            }

            .military-status-stack {
                justify-items: end;
            }

            .military-status-hint {
                display: none;
            }

            .military-table-footer {
                background: #fff;
            }

            .military-floating-create {
                position: fixed;
                z-index: 50;
                right: 18px;
                bottom: 18px;
                width: 52px;
                height: 52px;
                display: grid;
                place-items: center;
                color: #fff !important;
                background: linear-gradient(135deg, #0097a5, #006f7d);
                border: 4px solid rgba(255, 255, 255, .92);
                border-radius: 17px;
                box-shadow: 0 16px 30px rgba(0, 108, 121, .31);
                font-size: 19px;
                text-decoration: none !important;
            }
        }

        @media (max-width: 520px) {
            .military-breadcrumbs {
                display: none;
            }

            .military-hero h1 {
                font-size: 22px;
            }

            .military-hero p {
                font-size: 12px;
            }

            .military-meta-chip:nth-child(2) {
                display: none;
            }

            .military-stats.military-stats--enhanced {
                grid-template-columns: 1fr;
            }

            .military-stats--enhanced .military-stat {
                min-height: 76px;
            }

            .military-workflow-summary {
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .military-table-toolbar__icon {
                display: none;
            }

            .military-table-toolbar__actions {
                gap: 4px;
            }

            .military-table-footer {
                align-items: stretch;
            }

            .military-table-footer > div:last-child {
                width: 100%;
                overflow-x: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .military-page *,
            .military-page *::before,
            .military-page *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }

        @media print {
            .military-page {
                min-height: 0;
                padding: 0;
                overflow: visible;
                background: #fff;
            }

            .military-page::before,
            .military-hero__actions,
            .military-hero__meta,
            .military-stats,
            .military-workspace-bar,
            .military-filter,
            .military-table-toolbar__actions,
            .military-cell--actions,
            .military-floating-create,
            .military-table-footer > div:last-child {
                display: none !important;
            }

            .military-hero,
            .military-table-card {
                border: 0;
                box-shadow: none;
            }

            .military-table {
                min-width: 0;
            }

            .military-table th,
            .military-table td {
                position: static;
                padding: 7px;
                font-size: 10px;
            }
        }

    </style>

@endsection
@section('sub-main')
    @php
        $indexRouteName = 'doctors.commissions.military.index';
        $totalDocuments = (int) ($stats['total'] ?? 0);
        $draftDocuments = (int) ($stats['draft'] ?? 0);
        $preparedDocuments = (int) ($stats['prepared'] ?? 0);
        $preparedRate = $totalDocuments > 0
            ? max(0, min(100, (int) round(($preparedDocuments / $totalDocuments) * 100)))
            : 0;
        $draftRate = $totalDocuments > 0
            ? max(0, min(100, (int) round(($draftDocuments / $totalDocuments) * 100)))
            : 0;

        $formatFilterDate = static function ($value) {
            if (!$value) {
                return null;
            }

            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d.m.Y');
            } catch (\Throwable $exception) {
                return (string) $value;
            }
        };

        $activeFilters = collect([
            [
                'key' => 'q',
                'icon' => 'bi-search',
                'label' => 'Поиск',
                'value' => request('q'),
            ],
            [
                'key' => 'status',
                'icon' => 'bi-check2-circle',
                'label' => 'Статус',
                'value' => request('status')
                    ? ($statusOptions[request('status')] ?? request('status'))
                    : null,
            ],
            [
                'key' => 'document_type',
                'icon' => 'bi-file-earmark-medical',
                'label' => 'Вид',
                'value' => request('document_type')
                    ? ($documentTypeOptions[request('document_type')] ?? request('document_type'))
                    : null,
            ],
            [
                'key' => 'date_from',
                'icon' => 'bi-calendar2-event',
                'label' => 'С даты',
                'value' => $formatFilterDate(request('date_from')),
            ],
            [
                'key' => 'date_to',
                'icon' => 'bi-calendar2-check',
                'label' => 'По дату',
                'value' => $formatFilterDate(request('date_to')),
            ],
        ])->filter(fn ($filter) => filled($filter['value']))->values();

        $activeFilterCount = $activeFilters->count();
        $allDocumentsUrl = route($indexRouteName, request()->except(['page', 'status']));
        $draftDocumentsUrl = route(
            $indexRouteName,
            array_merge(request()->except(['page', 'status']), ['status' => 'draft'])
        );
        $preparedDocumentsUrl = route(
            $indexRouteName,
            array_merge(request()->except(['page', 'status']), ['status' => 'prepared'])
        );
    @endphp

    <main class="military-page">
        <div class="military-container">
            <header class="military-hero">
                <div class="military-hero__main">
                    <div class="military-hero__icon" aria-hidden="true">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="military-hero__copy">
                        <div class="military-breadcrumbs" aria-label="Раздел">
                            <span>Кабинет врача</span>
                            <i class="bi bi-chevron-right"></i>
                            <span>Комиссии и экспертизы</span>
                            <i class="bi bi-chevron-right"></i>
                            <span>Военкомат</span>
                        </div>
                        <div class="military-eyebrow">Комиссии и экспертизы</div>
                        <h1>Военкомат / ВВК</h1>
                        <p>Журнал медицинских сведений, заключений врача и ответов на запросы военкомата.</p>

                        <div class="military-hero__meta" aria-label="Сводная информация">
                            <span class="military-meta-chip">
                                <i class="bi bi-journal-medical"></i>
                                Документов: {{ $totalDocuments }}
                            </span>
                            <span class="military-meta-chip">
                                <i class="bi bi-patch-check"></i>
                                Готовность {{ $preparedRate }}%
                            </span>
                            @if($activeFilterCount)
                                <span class="military-meta-chip military-meta-chip--active">
                                    <i class="bi bi-funnel-fill"></i>
                                    Активных фильтров: {{ $activeFilterCount }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="military-hero__actions">
                    <button class="military-button military-button--ghost"
                            type="button"
                            data-military-filter-toggle
                            aria-expanded="true"
                            aria-controls="militaryFilterPanel">
                        <i class="bi bi-sliders2"></i>
                        <span>Фильтры</span>
                        @if($activeFilterCount)
                            <span class="military-button__counter">{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                    <a class="military-button military-button--primary"
                       href="{{ route('doctors.commissions.military.create') }}">
                        <i class="bi bi-plus-lg"></i>
                        Новый документ
                    </a>
                </div>
            </header>

            @if(session('message'))
                <div class="military-alert {{ session('status') === 'error' ? 'military-alert--danger' : 'military-alert--success' }}"
                     role="alert"
                     data-military-alert>
                    <i class="bi {{ session('status') === 'error' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill' }}"></i>
                    <div class="military-alert__content">{{ session('message') }}</div>
                    <button class="military-alert__close" type="button" data-military-alert-close aria-label="Закрыть сообщение">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            @endif

            <section class="military-stats military-stats--enhanced" aria-label="Статистика документов">
                <a class="military-stat military-stat--total"
                   href="{{ $allDocumentsUrl }}"
                   @if(!request('status')) aria-current="page" @endif>
                    <div class="military-stat__icon"><i class="bi bi-files"></i></div>
                    <div class="military-stat__copy">
                        <span>Всего документов</span>
                        <strong>{{ $totalDocuments }}</strong>
                        <small>Полный журнал ВВК</small>
                    </div>
                    <span class="military-stat__arrow"><i class="bi bi-arrow-up-right"></i></span>
                    <span class="military-stat__progress" aria-hidden="true"><span style="width: 100%"></span></span>
                </a>

                <a class="military-stat military-stat--draft"
                   href="{{ $draftDocumentsUrl }}"
                   @if(request('status') === 'draft') aria-current="page" @endif>
                    <div class="military-stat__icon"><i class="bi bi-pencil-square"></i></div>
                    <div class="military-stat__copy">
                        <span>Черновики</span>
                        <strong>{{ $draftDocuments }}</strong>
                        <small>{{ $draftRate }}% требуют завершения</small>
                    </div>
                    <span class="military-stat__arrow"><i class="bi bi-arrow-up-right"></i></span>
                    <span class="military-stat__progress" aria-hidden="true"><span style="width: {{ $draftRate }}%"></span></span>
                </a>

                <a class="military-stat military-stat--prepared"
                   href="{{ $preparedDocumentsUrl }}"
                   @if(request('status') === 'prepared') aria-current="page" @endif>
                    <div class="military-stat__icon"><i class="bi bi-file-earmark-check"></i></div>
                    <div class="military-stat__copy">
                        <span>Оформлено</span>
                        <strong>{{ $preparedDocuments }}</strong>
                        <small>{{ $preparedRate }}% готовы к выдаче</small>
                    </div>
                    <span class="military-stat__arrow"><i class="bi bi-arrow-up-right"></i></span>
                    <span class="military-stat__progress" aria-hidden="true"><span style="width: {{ $preparedRate }}%"></span></span>
                </a>

                <article class="military-stat military-stat--readiness">
                    <div class="military-progress-ring" style="--progress: {{ $preparedRate }}" aria-label="Готовность {{ $preparedRate }} процентов">
                        <span>{{ $preparedRate }}%</span>
                    </div>
                    <div class="military-stat__copy">
                        <span>Готовность журнала</span>
                        <strong>{{ $preparedDocuments }}/{{ $totalDocuments }}</strong>
                        <small>{{ $totalDocuments ? 'Оформленные документы' : 'Создайте первый документ' }}</small>
                    </div>
                </article>
            </section>

            <section class="military-workspace-bar" aria-label="Быстрая навигация по журналу">
                <nav class="military-status-tabs" aria-label="Фильтр по статусу">
                    <a class="military-status-tab {{ request('status') ? '' : 'is-active' }}"
                       href="{{ $allDocumentsUrl }}">
                        <i class="bi bi-grid"></i>
                        Все
                        <span class="military-status-tab__count">{{ $totalDocuments }}</span>
                    </a>
                    <a class="military-status-tab {{ request('status') === 'draft' ? 'is-active' : '' }}"
                       href="{{ $draftDocumentsUrl }}">
                        <i class="bi bi-pencil"></i>
                        Черновики
                        <span class="military-status-tab__count">{{ $draftDocuments }}</span>
                    </a>
                    <a class="military-status-tab {{ request('status') === 'prepared' ? 'is-active' : '' }}"
                       href="{{ $preparedDocumentsUrl }}">
                        <i class="bi bi-check2-circle"></i>
                        Оформлено
                        <span class="military-status-tab__count">{{ $preparedDocuments }}</span>
                    </a>
                </nav>

                <div class="military-workflow-summary">
                    <div class="military-workflow-summary__copy">
                        <span>Текущий прогресс</span>
                        <strong>Черновик → проверка → оформление</strong>
                    </div>
                    <div class="military-workflow-summary__value">{{ $preparedRate }}%</div>
                    <div class="military-workflow-progress" aria-hidden="true">
                        <span style="width: {{ $preparedRate }}%"></span>
                    </div>
                </div>
            </section>

            <section class="military-filter" id="militaryFilterPanel" data-military-filter-panel>
                <div class="military-filter__head">
                    <div class="military-filter__title-row">
                        <span class="military-filter__title-icon"><i class="bi bi-funnel"></i></span>
                        <div>
                            <h2>Поиск и фильтры</h2>
                            <p>Найдите документ по пациенту, номеру, диагнозу или организации.</p>
                        </div>
                    </div>

                    <div class="military-filter__head-actions">
                        @if($activeFilterCount)
                            <span class="military-filter__counter">
                                <i class="bi bi-check2"></i>
                                Выбрано: {{ $activeFilterCount }}
                            </span>
                            <a class="military-filter-reset" href="{{ route($indexRouteName) }}">
                                <i class="bi bi-x-circle"></i> Сбросить всё
                            </a>
                        @endif
                        <button class="military-icon-button"
                                type="button"
                                data-military-filter-toggle
                                aria-expanded="true"
                                aria-controls="militaryFilterBody"
                                data-tooltip="Свернуть фильтры">
                            <i class="bi bi-chevron-up"></i>
                            <span class="military-sr-only">Свернуть или развернуть фильтры</span>
                        </button>
                    </div>
                </div>

                <div class="military-filter__body" id="militaryFilterBody" data-military-filter-body>
                    @if($activeFilterCount)
                        <div class="military-active-filters" aria-label="Активные фильтры">
                            <span class="military-active-filters__label">
                                <i class="bi bi-stars"></i>
                                Сейчас применено
                            </span>
                            @foreach($activeFilters as $filter)
                                <a class="military-filter-chip"
                                   href="{{ route($indexRouteName, request()->except(['page', $filter['key']])) }}"
                                   title="Удалить фильтр «{{ $filter['label'] }}»">
                                    <i class="bi {{ $filter['icon'] }}"></i>
                                    <span><small>{{ $filter['label'] }}:</small>{{ $filter['value'] }}</span>
                                    <span class="military-filter-chip__remove"><i class="bi bi-x"></i></span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <form class="military-filter__grid" method="GET" action="{{ route($indexRouteName) }}">
                        <div class="military-field">
                            <label for="militarySearch">Поиск</label>
                            <div class="military-search-wrap">
                                <i class="bi bi-search"></i>
                                <input id="militarySearch"
                                       class="military-search-input"
                                       type="search"
                                       name="q"
                                       value="{{ request('q') }}"
                                       placeholder="Пациент, номер или диагноз"
                                       autocomplete="off">
                                <button class="military-search-clear {{ request('q') ? '' : 'd-none' }}"
                                        type="button"
                                        data-military-search-clear
                                        aria-label="Очистить поиск">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="military-field">
                            <label for="militaryStatus">Статус</label>
                            <select id="militaryStatus" name="status">
                                <option value="">Все статусы</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="military-field">
                            <label for="militaryType">Вид документа</label>
                            <select id="militaryType" name="document_type">
                                <option value="">Все виды</option>
                                @foreach($documentTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('document_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="military-field">
                            <label for="militaryFrom">С даты</label>
                            <input id="militaryFrom" type="date" name="date_from" value="{{ request('date_from') }}">
                        </div>

                        <div class="military-field">
                            <label for="militaryTo">По дату</label>
                            <input id="militaryTo" type="date" name="date_to" value="{{ request('date_to') }}">
                        </div>

                        <button class="military-button military-button--secondary" type="submit">
                            <i class="bi bi-funnel-fill"></i>
                            Применить
                        </button>
                    </form>

                    <div class="military-filter__foot">
                        <span>Можно сочетать несколько параметров поиска одновременно.</span>
                        <span class="military-key-hint">
                            <kbd>/</kbd>
                            перейти к поиску
                        </span>
                    </div>
                </div>
            </section>

            <section class="military-table-card" data-military-table-card>
                <header class="military-table-toolbar">
                    <div class="military-table-toolbar__main">
                        <span class="military-table-toolbar__icon"><i class="bi bi-journal-text"></i></span>
                        <div>
                            <div class="military-title-row" style="gap: 8px; flex-wrap: wrap;">
                                <h2>Журнал документов</h2>
                                <span class="military-results-pill">
                                    <i class="bi bi-list-check"></i>
                                    {{ $documents->total() }} найдено
                                </span>
                            </div>
                            <p>Просматривайте, редактируйте и печатайте подготовленные документы.</p>
                        </div>
                    </div>

                    <div class="military-table-toolbar__actions">
                        <button class="military-toolbar-button"
                                type="button"
                                data-military-density-toggle
                                aria-pressed="false"
                                data-tooltip="Изменить плотность строк">
                            <i class="bi bi-arrows-collapse"></i>
                            <span>Компактно</span>
                        </button>
                        <a class="military-toolbar-button"
                           href="{{ request()->fullUrl() }}"
                           data-tooltip="Обновить журнал">
                            <i class="bi bi-arrow-clockwise"></i>
                            <span>Обновить</span>
                        </a>
                        <a class="military-toolbar-button"
                           href="{{ route('doctors.commissions.military.create') }}"
                           data-tooltip="Создать документ">
                            <i class="bi bi-plus-lg"></i>
                            <span>Добавить</span>
                        </a>
                    </div>
                </header>

                @if($documents->count())
                    <div class="military-table-wrap">
                        <table class="military-table">
                            <caption class="military-sr-only">Документы для военкомата и военно-врачебной комиссии</caption>
                            <colgroup>
                                <col class="military-table__col--document">
                                <col class="military-table__col--patient">
                                <col class="military-table__col--date">
                                <col class="military-table__col--recipient">
                                <col class="military-table__col--status">
                                <col class="military-table__col--actions">
                            </colgroup>
                            <thead>
                            <tr>
                                <th>Документ</th>
                                <th>Пациент</th>
                                <th>Дата и вид</th>
                                <th>Получатель / запрос</th>
                                <th>Статус</th>
                                <th style="text-align: right">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($documents as $document)
                                @php
                                    $patient = $document->patient;
                                    $initials = collect([$patient?->surname, $patient?->name])
                                        ->filter()
                                        ->map(fn ($part) => mb_strtoupper(mb_substr((string) $part, 0, 1)))
                                        ->implode('') ?: '—';
                                    $requestingOrganization = trim((string) $document->requesting_organization);
                                    $organizationParts = preg_split(
                                        '/\s+[—–-]\s+/u',
                                        $requestingOrganization,
                                        2
                                    ) ?: [];
                                    $organizationName = trim((string) ($organizationParts[0] ?? ''));
                                    $organizationAddress = trim((string) ($organizationParts[1] ?? ''));
                                    $isPrepared = $document->status === 'prepared';
                                @endphp
                                <tr class="military-row--{{ $isPrepared ? 'prepared' : 'draft' }}">
                                    <td class="military-cell--document" data-label="Документ">
                                        <div class="military-document-cell">
                                            <span class="military-document-icon military-document-icon--{{ $isPrepared ? 'prepared' : 'draft' }}">
                                                <i class="bi {{ $isPrepared ? 'bi-file-earmark-check' : 'bi-file-earmark-text' }}"></i>
                                            </span>
                                            <span class="military-document-cell__copy">
                                                <strong class="military-table__document-number"
                                                        title="{{ $document->document_number ?: 'Без номера' }}">
                                                    {{ $document->document_number ?: 'Без номера' }}
                                                </strong>
                                                <span class="military-table__muted">ID {{ $document->id }}</span>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="military-cell--patient" data-label="Пациент">
                                        <div class="military-patient-summary">
                                            <span class="military-patient-summary__avatar">{{ $initials }}</span>
                                            <span class="military-patient-summary__copy">
                                                <strong title="{{ $document->patientFullName() }}">{{ $document->patientFullName() }}</strong>
                                                <span class="military-diagnosis-line" title="{{ $document->diagnosisLabel() }}">
                                                    <i class="bi bi-activity"></i>
                                                    {{ $document->diagnosisLabel() }}
                                                </span>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="military-cell--date" data-label="Дата и вид">
                                        <div class="military-date-block">
                                            <span class="military-date-icon"><i class="bi bi-calendar3"></i></span>
                                            <span class="military-date-block__copy">
                                                <strong>{{ $document->document_date?->format('d.m.Y') ?: '—' }}</strong>
                                                <span class="military-table__muted military-table__document-type"
                                                      title="{{ $document->documentTypeLabel() }}">
                                                    {{ $document->documentTypeLabel() }}
                                                </span>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="military-cell--recipient" data-label="Получатель / запрос">
                                        <div class="military-recipient"
                                             title="{{ $requestingOrganization ?: 'Получатель не указан' }}">
                                            <strong class="military-recipient__name">
                                                <i class="bi bi-building"></i>
                                                <span>{{ $organizationName ?: 'Получатель не указан' }}</span>
                                            </strong>
                                            @if($organizationAddress !== '')
                                                <span class="military-recipient__address">
                                                    {{ $organizationAddress }}
                                                </span>
                                            @endif
                                            <span class="military-recipient__request">
                                                <i class="bi bi-envelope-paper"></i>
                                                {{ $document->request_number ? 'Запрос № ' . $document->request_number : 'Без номера запроса' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="military-cell--status" data-label="Статус">
                                        <div class="military-status-stack">
                                            <span class="military-badge military-badge--{{ $isPrepared ? 'prepared' : 'draft' }}">
                                                {{ $document->statusLabel() }}
                                            </span>
                                            <span class="military-status-hint">
                                                {{ $isPrepared ? 'Готов к печати и выдаче' : 'Можно продолжить редактирование' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="military-cell--actions" data-label="Действия">
                                        <div class="military-actions" aria-label="Действия с документом">
                                            <a class="military-action military-action--view"
                                               data-tooltip="Просмотреть"
                                               aria-label="Просмотреть документ"
                                               href="{{ route('doctors.commissions.military.show', $document) }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="military-action"
                                               data-tooltip="Редактировать"
                                               aria-label="Редактировать документ"
                                               href="{{ route('doctors.commissions.military.edit', $document) }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a class="military-action"
                                               data-tooltip="Печать"
                                               aria-label="Печать документа"
                                               data-military-print
                                               href="{{ route('doctors.commissions.military.print', $document) }}">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <span class="military-actions__separator" aria-hidden="true"></span>
                                            @if(!$isPrepared)
                                                <form class="military-action-form"
                                                      method="POST"
                                                      action="{{ route('doctors.commissions.military.destroy', $document) }}"
                                                      onsubmit="return confirm('Удалить этот черновик?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="military-action military-action--danger"
                                                            data-tooltip="Удалить"
                                                            aria-label="Удалить черновик"
                                                            type="submit">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="military-action military-action--disabled"
                                                      data-tooltip="Оформленный документ нельзя удалить"
                                                      aria-label="Оформленный документ нельзя удалить">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <footer class="military-table-footer">
                        <div class="military-table-footer__summary">
                            <span>
                                Показано <strong>{{ $documents->count() }}</strong>
                                из <strong>{{ $documents->total() }}</strong>
                            </span>
                            <span class="military-table-footer__page">
                                <i class="bi bi-file-earmark-text"></i>
                                Страница {{ $documents->currentPage() }} из {{ $documents->lastPage() }}
                            </span>
                        </div>
                        <div>{{ $documents->links() }}</div>
                    </footer>
                @else
                    <div class="military-empty">
                        <div class="military-empty__content">
                            <div class="military-empty__visual">
                                <i class="bi bi-shield-check"></i>
                                <span><i class="bi bi-search"></i></span>
                            </div>
                            <h3>{{ $activeFilterCount ? 'По заданным условиям ничего не найдено' : 'Документов пока нет' }}</h3>
                            <p>
                                {{ $activeFilterCount
                                    ? 'Уберите часть фильтров или измените поисковый запрос.'
                                    : 'Создайте первый медицинский документ для военкомата или ВВК.' }}
                            </p>
                            <div class="military-empty__actions">
                                @if($activeFilterCount)
                                    <a class="military-button military-button--ghost" href="{{ route($indexRouteName) }}">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Сбросить фильтры
                                    </a>
                                @endif
                                <a class="military-button military-button--primary"
                                   href="{{ route('doctors.commissions.military.create') }}">
                                    <i class="bi bi-plus-lg"></i>
                                    Создать документ
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <a class="military-floating-create"
           href="{{ route('doctors.commissions.military.create') }}"
           aria-label="Создать новый документ">
            <i class="bi bi-plus-lg"></i>
        </a>
    </main>

    @include('doctors.commissions.partials.hidden-print')

    <script>
        (() => {
            const initMilitaryPage = () => {
                const page = document.querySelector('.military-page');
                if (!page || page.dataset.uiInitialized === 'true') {
                    return;
                }

                page.dataset.uiInitialized = 'true';

                const filterPanel = page.querySelector('[data-military-filter-panel]');
                const filterBody = page.querySelector('[data-military-filter-body]');
                const filterToggles = page.querySelectorAll('[data-military-filter-toggle]');

                const setFilterExpanded = (expanded) => {
                    if (!filterPanel || !filterBody) {
                        return;
                    }

                    filterPanel.classList.toggle('is-collapsed', !expanded);
                    filterBody.setAttribute('aria-hidden', expanded ? 'false' : 'true');

                    filterToggles.forEach((button) => {
                        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                        const tooltip = button.matches('.military-icon-button');
                        if (tooltip) {
                            button.dataset.tooltip = expanded ? 'Свернуть фильтры' : 'Развернуть фильтры';
                        }
                    });
                };

                filterToggles.forEach((button) => {
                    button.addEventListener('click', () => {
                        const isExpanded = button.getAttribute('aria-expanded') !== 'false';
                        setFilterExpanded(!isExpanded);

                        if (!isExpanded && button.closest('.military-hero__actions')) {
                            filterPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                });

                page.querySelectorAll('[data-military-alert-close]').forEach((button) => {
                    button.addEventListener('click', () => {
                        button.closest('[data-military-alert]')?.remove();
                    });
                });

                const searchInput = page.querySelector('#militarySearch');
                const searchClear = page.querySelector('[data-military-search-clear]');

                const syncSearchClear = () => {
                    if (!searchInput || !searchClear) {
                        return;
                    }
                    searchClear.classList.toggle('d-none', searchInput.value.trim() === '');
                };

                searchInput?.addEventListener('input', syncSearchClear);
                searchClear?.addEventListener('click', () => {
                    searchInput.value = '';
                    syncSearchClear();
                    searchInput.focus();
                });

                const tableCard = page.querySelector('[data-military-table-card]');
                const densityButton = page.querySelector('[data-military-density-toggle]');
                const densityStorageKey = 'military-vvk-table-density';

                const setCompactMode = (compact, save = true) => {
                    if (!tableCard || !densityButton) {
                        return;
                    }

                    tableCard.classList.toggle('is-compact', compact);
                    densityButton.setAttribute('aria-pressed', compact ? 'true' : 'false');

                    const icon = densityButton.querySelector('i');
                    const label = densityButton.querySelector('span');
                    if (icon) {
                        icon.className = compact ? 'bi bi-arrows-expand' : 'bi bi-arrows-collapse';
                    }
                    if (label) {
                        label.textContent = compact ? 'Обычный вид' : 'Компактно';
                    }
                    densityButton.dataset.tooltip = compact ? 'Вернуть обычные строки' : 'Сделать строки компактнее';

                    if (save) {
                        try {
                            localStorage.setItem(densityStorageKey, compact ? 'compact' : 'comfortable');
                        } catch (error) {
                            // Хранилище может быть недоступно в приватном режиме.
                        }
                    }
                };

                if (tableCard && densityButton) {
                    let compactByDefault = false;
                    try {
                        compactByDefault = localStorage.getItem(densityStorageKey) === 'compact';
                    } catch (error) {
                        compactByDefault = false;
                    }
                    setCompactMode(compactByDefault, false);

                    densityButton.addEventListener('click', () => {
                        setCompactMode(!tableCard.classList.contains('is-compact'));
                    });
                }

                document.addEventListener('keydown', (event) => {
                    const target = event.target;
                    const isTyping = target instanceof HTMLInputElement
                        || target instanceof HTMLTextAreaElement
                        || target instanceof HTMLSelectElement
                        || target?.isContentEditable;

                    if (event.key === '/' && !isTyping && !event.ctrlKey && !event.metaKey && !event.altKey) {
                        event.preventDefault();
                        setFilterExpanded(true);
                        searchInput?.focus();
                    }

                    if (event.key === 'Escape' && document.activeElement === searchInput && searchInput?.value) {
                        searchInput.value = '';
                        syncSearchClear();
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMilitaryPage, { once: true });
            } else {
                initMilitaryPage();
            }
        })();
    </script>
@endsection
