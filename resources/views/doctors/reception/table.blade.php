@extends('doctors.reception')

@section('title', $receptionPage['title'])

@section('assets')
    <style>
        :root {
            --reception-primary: #048696;
            --reception-primary-dark: #036b78;
            --reception-primary-soft: #e8f8fa;
            --reception-bg: #f4f7fb;
            --reception-card: #ffffff;
            --reception-border: #e3e9ef;
            --reception-text: #1f2937;
            --reception-muted: #6b7280;
            --reception-danger: #dc3545;
            --reception-warning: #f59e0b;
        }

        .reception-page {
            min-height: calc(100vh - 80px);
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(4, 134, 150, 0.12), transparent 34%),
                var(--reception-bg);
        }

        .reception-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 24px;
            padding: 28px;
            border: 1px solid var(--reception-border);
            border-radius: 26px;
            background: linear-gradient(135deg, #ffffff 0%, #f0fbfc 100%);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
        }

        .reception-header h1 {
            margin: 0 0 10px;
            color: var(--reception-text);
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .reception-header p {
            max-width: 760px;
            margin: 0;
            color: var(--reception-muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .reception-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 999px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .reception-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 22px;
            margin-bottom: 22px;
        }

        .reception-card {
            border: 1px solid var(--reception-border);
            border-radius: 24px;
            background: var(--reception-card);
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .reception-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--reception-border);
            background: #ffffff;
        }

        .reception-card-title {
            margin: 0;
            color: var(--reception-text);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .reception-card-subtitle {
            margin-top: 4px;
            color: var(--reception-muted);
            font-size: 13px;
        }

        .reception-card-body {
            padding: 24px;
        }

        .reception-search .form-label {
            margin-bottom: 7px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .reception-search .form-control,
        .reception-search .form-select {
            min-height: 42px;
            border-radius: 13px;
            border: 1px solid #d8e0e5;
            font-size: 14px;
        }

        .reception-search .form-control:focus,
        .reception-search .form-select:focus {
            border-color: var(--reception-primary);
            box-shadow: 0 0 0 0.2rem rgba(4, 134, 150, 0.14);
        }

        .reception-search-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
        }

        .reception-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 16px;
            border: 0;
            border-radius: 12px;
            color: #ffffff;
            background: var(--reception-primary);
            font-weight: 700;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .reception-btn-primary:hover {
            color: #ffffff;
            background: var(--reception-primary-dark);
        }

        .reception-btn-light {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 16px;
            border: 1px solid var(--reception-border);
            border-radius: 12px;
            color: #334155;
            background: #ffffff;
            font-weight: 700;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .reception-btn-light:hover {
            color: #111827;
            background: #f3f6f9;
        }

        .account-info-list {
            display: grid;
            gap: 14px;
        }

        .account-info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border: 1px solid #eef2f6;
            border-radius: 16px;
            background: #f8fafc;
        }

        .account-info-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 18px;
        }

        .account-info-label {
            margin-bottom: 2px;
            color: var(--reception-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .account-info-value {
            color: var(--reception-text);
            font-size: 15px;
            font-weight: 700;
            line-height: 1.35;
        }

        .reception-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .reception-stat {
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f8fafc 0%, #eef8fa 100%);
            border: 1px solid #e3eef2;
        }

        .reception-stat-label {
            margin-bottom: 8px;
            color: var(--reception-muted);
            font-size: 13px;
            font-weight: 700;
        }

        .reception-stat-value {
            color: var(--reception-text);
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
        }

        .reception-table-panel {
            border: 1px solid var(--reception-border);
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .reception-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--reception-border);
            background: #ffffff;
        }

        .reception-table-header h2 {
            margin: 0;
            color: var(--reception-text);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .reception-table-count {
            padding: 7px 12px;
            border-radius: 999px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .reception-table-wrap {
            overflow-x: auto;
        }

        .reception-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .reception-table thead th {
            padding: 15px 18px;
            color: #667085;
            background: #f8fafc;
            border-bottom: 1px solid var(--reception-border);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.045em;
            white-space: nowrap;
        }

        .reception-table tbody td {
            padding: 17px 18px;
            border-bottom: 1px solid #eef2f6;
            color: var(--reception-text);
            font-size: 14px;
            vertical-align: middle;
        }

        .reception-table tbody tr {
            transition: 0.18s ease;
        }

        .reception-table tbody tr:hover {
            background: #f8fcfd;
        }

        .reception-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .reception-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 5px 9px;
            border-radius: 999px;
            color: #475569;
            background: #eef2f7;
            font-size: 13px;
            font-weight: 800;
        }

        .patient-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 240px;
        }

        .patient-avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: #ffffff;
            background: linear-gradient(135deg, var(--reception-primary), #10b7c6);
            font-size: 14px;
            font-weight: 800;
        }

        .patient-name {
            color: var(--reception-text);
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
        }

        .patient-meta {
            margin-top: 3px;
            color: var(--reception-muted);
            font-size: 13px;
        }

        .doctor-cell {
            min-width: 260px;
        }

        .doctor-profession {
            color: var(--reception-primary-dark);
            font-size: 13px;
            font-weight: 800;
        }

        .doctor-name {
            margin-top: 3px;
            color: var(--reception-text);
            font-weight: 700;
        }

        .address-cell {
            min-width: 240px;
            color: #475569;
            line-height: 1.4;
        }

        .reception-location {
            min-width: 200px;
            line-height: 1.4;
        }

        .reception-location__department {
            font-weight: 700;
        }

        .reception-location__warning {
            max-width: 320px;
            margin-top: 8px;
            padding: 8px 10px;
            border: 1px solid #fde68a;
            border-radius: 10px;
            color: #92400e;
            background: #fffbeb;
            font-size: 12px;
            white-space: normal;
        }

        .reception-table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 18px 24px;
            border-top: 1px solid var(--reception-border);
            color: var(--reception-muted);
            font-size: 13px;
        }

        .reception-pagination {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reception-pagination__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            min-height: 34px;
            border: 1px solid var(--reception-border);
            border-radius: 10px;
            color: var(--reception-primary-dark);
            background: #ffffff;
            text-decoration: none;
        }

        .reception-pagination__link:hover {
            background: var(--reception-primary-soft);
        }

        .reception-pagination__link.is-disabled {
            color: #a1aab6;
            background: #f8fafc;
        }

        .reception-pagination__position {
            padding: 0 6px;
            color: var(--reception-text);
            font-weight: 700;
            white-space: nowrap;
        }

        .empty-muted {
            color: #94a3b8;
        }

        .table-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .table-action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #475569;
            background: #ffffff;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .table-action-btn:hover {
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            border-color: #b7dce2;
        }

        .table-action-btn.danger {
            color: #b91c1c;
            background: #fff7f7;
            border-color: #fecaca;
        }

        .table-action-btn.danger:hover {
            color: #991b1b;
            background: #fee2e2;
        }

        .table-action-btn-discharge {
            width: auto;
            gap: 7px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .table-action-btn-discharge:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        .table-discharge-unavailable {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            min-width: 140px;
            max-width: 220px;
            white-space: normal;
        }

        .table-discharge-unavailable .table-action-btn-discharge:disabled {
            opacity: 1;
            color: #667085;
            background: #f2f4f7;
            border-color: #e4e7ec;
            cursor: not-allowed;
        }

        .table-discharge-reason {
            color: #667085;
            font-size: 12px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .table-action-btn-placement {
            width: auto;
            gap: 7px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .table-action-btn-placement:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        .table-placement-unavailable {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            min-width: 170px;
            max-width: 220px;
            white-space: normal;
        }

        .table-placement-unavailable .table-action-btn-placement:disabled {
            opacity: 1;
            color: #667085;
            background: #f2f4f7;
            border-color: #e4e7ec;
            cursor: not-allowed;
        }

        .placement-edit-modal .modal-content {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.22);
        }

        .placement-edit-modal .modal-header,
        .placement-edit-modal .modal-body,
        .placement-edit-modal .modal-footer {
            padding: 22px 24px;
        }

        .placement-edit-modal .modal-title {
            color: var(--reception-text);
            font-size: 20px;
            font-weight: 800;
        }

        .placement-edit-summary {
            display: grid;
            gap: 12px;
            margin: 0 0 18px;
            padding: 16px;
            border-radius: 14px;
            background: #f8fafc;
        }

        .placement-edit-summary dt,
        .placement-edit-help,
        .placement-edit-status {
            color: #667085;
            font-size: 13px;
            line-height: 1.45;
        }

        .placement-edit-summary dd {
            margin: 3px 0 0;
            color: #344054;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .placement-edit-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
        }

        .placement-edit-label {
            display: block;
            margin-bottom: 7px;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
        }

        .placement-picker {
            position: relative;
        }

        .placement-picker input,
        .placement-picker select {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid #dfe7ed;
            border-radius: 11px;
            color: #344054;
            background: #fff;
        }

        .placement-picker input:focus {
            outline: 2px solid #b7dce2;
            outline-offset: 1px;
        }

        .placement-picker input:disabled {
            color: #98a2b3;
            background: #f8fafc;
        }

        .placement-picker-options {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 1080;
            max-height: 220px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #dfe7ed;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        }

        .placement-picker-option {
            display: block;
            width: 100%;
            padding: 9px 10px;
            border: 0;
            border-radius: 8px;
            color: #344054;
            background: #fff;
            text-align: left;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .placement-picker-option.is-active,
        .placement-picker-option:hover:not(:disabled) {
            background: #eefafb;
        }

        .placement-picker-option:disabled {
            color: #667085;
            background: #f8fafc;
            cursor: not-allowed;
        }

        .placement-picker-option small {
            display: block;
            margin-top: 4px;
            font-size: 12px;
        }

        .placement-picker-empty {
            padding: 10px;
            color: #667085;
            font-size: 13px;
        }

        .discharge-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.22);
        }

        .discharge-modal .modal-header,
        .discharge-modal .modal-body {
            padding: 22px 24px;
        }

        .discharge-modal .modal-title {
            color: var(--reception-text);
            font-size: 20px;
            font-weight: 800;
        }

        .discharge-modal .modal-body {
            color: #374151;
            line-height: 1.55;
        }

        .discharge-modal .modal-footer {
            padding: 18px 24px;
            background: #f8fafc;
        }

        .reception-empty {
            padding: 56px 24px;
            text-align: center;
        }

        .reception-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 34px;
        }

        .reception-empty h3 {
            margin: 0 0 8px;
            color: var(--reception-text);
            font-size: 22px;
            font-weight: 800;
        }

        .reception-empty p {
            max-width: 520px;
            margin: 0 auto;
            color: var(--reception-muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .archive-modal .modal-content {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .archive-modal .modal-header {
            padding: 22px 24px;
            background: #fff7f7;
            border-bottom: 1px solid #fee2e2;
        }

        .archive-modal .modal-title {
            color: #991b1b;
            font-size: 20px;
            font-weight: 800;
        }

        .archive-modal .modal-body {
            padding: 24px;
            color: #374151;
            font-size: 15px;
            line-height: 1.55;
        }

        .archive-modal .modal-footer {
            padding: 18px 24px;
            background: #f8fafc;
            border-top: 1px solid var(--reception-border);
        }



        /*
         * Выпадающие элементы фильтра должны выходить за границы карточки.
         * Базовая .reception-card использует overflow: hidden для скруглений,
         * поэтому без этого override поиск врача и календарь обрезаются снизу.
         */
        .reception-grid {
            position: relative;
            z-index: 20;
            overflow: visible;
        }

        .reception-card.reception-search {
            position: relative;
            z-index: 40;
            overflow: visible;
        }

        .reception-search .reception-card-header {
            overflow: hidden;
            border-radius: 24px 24px 0 0;
        }

        .reception-search .reception-card-body {
            position: relative;
            overflow: visible;
            border-radius: 0 0 24px 24px;
            background: #fff;
        }

        /* Поисковый выбор врача */
        .reception-doctor-search,
        .reception-date-picker,
        .reception-address-wrap {
            position: relative;
        }

        .reception-search-input-wrap {
            position: relative;
        }

        .reception-search-input-icon,
        .reception-address-icon {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: 14px;
            color: #8a98a8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .reception-search-input,
        .reception-address-input {
            padding-left: 40px !important;
            padding-right: 42px !important;
        }

        .reception-search-clear {
            position: absolute;
            z-index: 3;
            top: 50%;
            right: 10px;
            display: inline-grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #7b8794;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .reception-search-clear:hover {
            background: #eef8fa;
            color: var(--reception-primary-dark);
        }

        .reception-doctor-dropdown,
        .reception-address-suggestions {
            position: absolute;
            z-index: 5000;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            overflow-y: auto;
            max-height: 330px;
            padding: 7px;
            border: 1px solid #dce5ea;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 20px 48px rgba(15, 23, 42, .16);
        }

        .reception-doctor-option {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            width: 100%;
            padding: 9px 10px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #243142;
            text-align: left;
            cursor: pointer;
        }

        .reception-doctor-option:hover,
        .reception-doctor-option.is-active {
            background: #eefafb;
        }

        .reception-doctor-option__icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            background: #e8f8fa;
            color: var(--reception-primary-dark);
        }

        .reception-doctor-option__body {
            min-width: 0;
        }

        .reception-doctor-option__body strong,
        .reception-doctor-option__body small {
            display: block;
        }

        .reception-doctor-option__body strong {
            overflow: hidden;
            font-size: 13px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reception-doctor-option__body small {
            margin-top: 2px;
            color: var(--reception-primary-dark);
            font-size: 11px;
            font-weight: 700;
        }

        .reception-doctor-empty {
            padding: 18px 12px;
            color: #7b8794;
            font-size: 12px;
            text-align: center;
        }

        /* Календарь даты рождения */
        .reception-date-trigger {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            color: #334155;
            background: #fff;
            text-align: left;
            cursor: pointer;
        }

        .reception-date-trigger.is-placeholder {
            color: #8a98a8;
        }

        .reception-date-trigger i {
            color: #6f7d8c;
        }

        .reception-calendar {
            position: absolute;
            z-index: 5100;
            top: calc(100% + 7px);
            left: 0;
            width: 330px;
            max-width: calc(100vw - 40px);
            padding: 15px;
            border: 1px solid #dce5ea;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 22px 52px rgba(15, 23, 42, .18);
        }

        .reception-calendar__head {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr) 36px;
            gap: 8px;
            align-items: center;
            margin-bottom: 12px;
        }

        .reception-calendar__nav {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 0;
            border-radius: 10px;
            background: #f6f9fa;
            color: #596979;
            cursor: pointer;
        }

        .reception-calendar__nav:hover:not(:disabled) {
            background: #e8f8fa;
            color: var(--reception-primary-dark);
        }

        .reception-calendar__month {
            min-height: 36px;
            padding: 6px 8px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #243142;
            font-size: 14px;
            font-weight: 850;
            text-align: center;
            text-transform: capitalize;
            cursor: pointer;
        }

        .reception-calendar__month:not(:disabled)::after {
            display: inline-block;
            width: 6px;
            height: 6px;
            margin: 0 0 3px 9px;
            border-right: 1.5px solid currentColor;
            border-bottom: 1.5px solid currentColor;
            content: '';
            transform: rotate(45deg);
        }

        .reception-calendar__month:hover:not(:disabled) {
            background: #e8f8fa;
            color: var(--reception-primary-dark);
        }

        .reception-calendar__month:disabled {
            cursor: default;
        }

        .reception-calendar__nav:disabled,
        .reception-calendar__choice:disabled {
            color: #cbd3d9;
            cursor: not-allowed;
        }

        .reception-calendar__years,
        .reception-calendar__months {
            display: grid;
            gap: 6px;
        }

        .reception-calendar__years {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .reception-calendar__months {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .reception-calendar__choice {
            min-height: 43px;
            padding: 8px 3px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #283849;
            font-size: 12px;
            text-transform: capitalize;
            cursor: pointer;
        }

        .reception-calendar__choice:hover:not(:disabled) {
            background: #e8f8fa;
            color: var(--reception-primary-dark);
        }

        .reception-calendar__choice.is-selected:not(:disabled) {
            background: #099dac;
            color: #fff;
            font-weight: 800;
        }

        .reception-calendar__year-jump {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 8px;
            align-items: center;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .reception-calendar__year-input {
            width: 100%;
            min-width: 0;
            height: 36px;
            padding: 7px 9px;
            border: 1px solid #dce5ea;
            border-radius: 9px;
            color: #283849;
            background: #fff;
            font-size: 14px;
        }

        .reception-calendar__year-go {
            min-height: 36px;
            padding: 7px 10px;
            border: 0;
            border-radius: 9px;
            color: #fff;
            background: #099dac;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .reception-calendar__year-error {
            margin: -4px 0 10px;
            color: #c23838;
            font-size: 11px;
        }

        .reception-calendar button:focus-visible,
        .reception-calendar__year-input:focus-visible {
            outline: 2px solid #099dac;
            outline-offset: 2px;
        }

        .reception-calendar__weekdays,
        .reception-calendar__days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .reception-calendar__weekdays {
            margin-bottom: 5px;
            color: #7c8996;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
        }

        .reception-calendar__weekdays span {
            padding: 5px 0;
        }

        .reception-calendar__weekdays .is-weekend {
            color: #e25050;
        }

        .reception-calendar__day {
            display: grid;
            aspect-ratio: 1;
            place-items: center;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #283849;
            font-size: 12px;
            cursor: pointer;
        }

        .reception-calendar__day:hover:not(:disabled) {
            background: #e8f8fa;
            color: var(--reception-primary-dark);
        }

        .reception-calendar__day.is-selected {
            background: #099dac;
            color: #fff;
            font-weight: 900;
            box-shadow: 0 7px 18px rgba(9, 157, 172, .25);
        }

        .reception-calendar__day.is-weekend:not(.is-selected) {
            color: #e25050;
        }

        .reception-calendar__day.is-other-month {
            color: #c1cbd3;
        }

        .reception-calendar__day:disabled {
            color: #cbd3d9;
            cursor: not-allowed;
        }

        .reception-calendar__footer {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 12px;
            padding-top: 11px;
            border-top: 1px solid #edf1f4;
        }

        .reception-calendar__today,
        .reception-calendar__back,
        .reception-calendar__clear {
            padding: 7px 10px;
            border: 0;
            border-radius: 9px;
            background: #f3f7f8;
            color: #526273;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .reception-calendar__today:hover,
        .reception-calendar__back:hover,
        .reception-calendar__clear:hover {
            background: #e8f8fa;
            color: var(--reception-primary-dark);
        }

        /* DaData */
        .reception-address-suggestion {
            display: flex;
            width: 100%;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 11px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #334155;
            text-align: left;
            cursor: pointer;
        }

        .reception-address-suggestion:hover,
        .reception-address-suggestion.is-active {
            background: #eefafb;
        }

        .reception-address-suggestion > i {
            margin-top: 2px;
            color: var(--reception-primary);
        }

        .reception-address-suggestion strong,
        .reception-address-suggestion small {
            display: block;
        }

        .reception-address-suggestion strong {
            font-size: 12px;
            font-weight: 800;
            line-height: 1.35;
        }

        .reception-address-suggestion small {
            margin-top: 3px;
            color: #7b8794;
            font-size: 10px;
            line-height: 1.35;
        }

        @media (max-width: 1100px) {
            .reception-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .reception-page {
                padding: 18px 14px;
            }

            .reception-header {
                flex-direction: column;
                padding: 22px;
            }

            .reception-header h1 {
                font-size: 28px;
            }

            .reception-card-header,
            .reception-table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .reception-stats {
                grid-template-columns: 1fr;
            }

            .reception-search-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .reception-btn-primary,
            .reception-btn-light {
                width: 100%;
            }
        }

        .appointment-format-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .appointment-format-badge.is-in-person {
            background: #eef5ff;
            color: #3368a5;
        }

        .appointment-format-badge.is-telemedicine {
            background: #e8f9f7;
            color: #0d887f;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* ---------------------------------------------------------
             * Архивирование записи
             * --------------------------------------------------------- */
            const modal = document.getElementById('confirmArchiveModal');

            if (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;

                    if (!button) {
                        return;
                    }

                    const recordId = button.getAttribute('data-record-id');
                    const patientName = button.getAttribute('data-patient-name') || 'эту запись';
                    const form = document.getElementById('archiveForm');
                    const modalPatientName = document.getElementById('archivePatientName');

                    if (form && recordId) {
                        form.action = `reception/delete/${recordId}`;
                    }

                    if (modalPatientName) {
                        modalPatientName.textContent = patientName;
                    }
                });
            }

            /* ---------------------------------------------------------
             * Выписка из стационара
             * --------------------------------------------------------- */
            const dischargeModal = document.getElementById('confirmDischargeModal');

            if (dischargeModal) {
                const dischargeForm = document.getElementById('inpatientDischargeForm');
                const dischargeSubmit = document.getElementById('inpatientDischargeSubmit');
                const dischargeError = document.getElementById('inpatientDischargeError');
                const dischargePatientName = document.getElementById('dischargePatientName');
                const patientInput = dischargeForm.querySelector('[name="patient_id"]');
                const assignmentInput = dischargeForm.querySelector('[name="assignment_id"]');
                const dismissButtons = dischargeModal.querySelectorAll('[data-bs-dismiss="modal"]');
                let dischargePending = false;
                let dischargeSourceButton = null;

                function setDischargePending(pending) {
                    dischargePending = pending;
                    dischargeSubmit.disabled = pending;
                    dischargeSubmit.textContent = pending ? 'Выписываем…' : 'Да, выписать';
                    dischargeForm.setAttribute('aria-busy', String(pending));
                    dismissButtons.forEach(button => { button.disabled = pending; });

                    if (dischargeSourceButton) {
                        dischargeSourceButton.disabled = pending;
                    }
                }

                dischargeModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;

                    if (dischargePending || !button?.dataset.dischargeUrl
                        || !button.dataset.patientId || !button.dataset.assignmentId) {
                        event.preventDefault();
                        return;
                    }

                    dischargeSourceButton = button;
                    dischargeForm.action = button.dataset.dischargeUrl;
                    patientInput.value = button.dataset.patientId;
                    assignmentInput.value = button.dataset.assignmentId;
                    dischargePatientName.textContent = button.dataset.patientName || 'Пациент';
                    dischargeError.textContent = '';
                    dischargeError.classList.add('d-none');
                    setDischargePending(false);
                });

                dischargeModal.addEventListener('hide.bs.modal', function (event) {
                    if (dischargePending) event.preventDefault();
                });

                dischargeForm.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    if (dischargePending) return;

                    const csrfToken = dischargeForm.querySelector('[name="_token"]')?.value;
                    const failureMessage = 'Не удалось выписать пациента. Повторите попытку.';
                    dischargeError.textContent = '';
                    dischargeError.classList.add('d-none');
                    setDischargePending(true);

                    try {
                        if (!csrfToken || !patientInput.value || !assignmentInput.value) {
                            throw new Error('Обновите страницу и повторите выписку пациента.');
                        }

                        const response = await fetch(dischargeForm.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                patient_id: patientInput.value,
                                assignment_id: assignmentInput.value,
                                intent: 'discharge',
                            }),
                        });
                        const payload = await response.json().catch(() => null);

                        if (!response.ok || payload?.ok !== true) {
                            const validationError = payload?.errors && typeof payload.errors === 'object'
                                ? Object.values(payload.errors).flat().find(message => typeof message === 'string')
                                : null;
                            const responseMessage = typeof payload?.message === 'string' ? payload.message : null;
                            throw new Error(response.status === 419
                                ? 'Сессия истекла. Обновите страницу и повторите выписку.'
                                : validationError || responseMessage || failureMessage);
                        }

                        window.location.reload();
                    } catch (error) {
                        dischargeError.textContent = error instanceof TypeError
                            ? failureMessage
                            : error.message || failureMessage;
                        dischargeError.classList.remove('d-none');
                        setDischargePending(false);
                    }
                });
            }

            /* ---------------------------------------------------------
             * Поисковый выбор врача без Select2
             * --------------------------------------------------------- */
            const doctorSelect = document.getElementById('doctor_id');
            const doctorSearch = document.getElementById('doctor_search');
            const doctorWrap = document.getElementById('doctorSearchWrap');
            const doctorDropdown = document.getElementById('doctorSearchDropdown');
            const doctorClear = document.getElementById('doctorSearchClear');
            const doctorEmpty = document.getElementById('doctorSearchEmpty');
            const doctorOptions = doctorDropdown
                ? Array.from(doctorDropdown.querySelectorAll('.reception-doctor-option'))
                : [];

            function selectedDoctorOption() {
                if (!doctorSelect) {
                    return null;
                }

                return doctorSelect.options[doctorSelect.selectedIndex] || null;
            }

            function syncDoctorSearchText() {
                if (!doctorSearch || !doctorSelect) {
                    return;
                }

                const option = selectedDoctorOption();
                const hasDoctor = Boolean(doctorSelect.value && option);

                doctorSearch.value = hasDoctor
                    ? `${option.dataset.fullname || ''} — ${option.dataset.profile || ''}`
                    : '';

                doctorClear?.classList.toggle('d-none', !hasDoctor);
            }

            function normalizeDoctorSearch(value) {
                return (value || '')
                    .toLocaleLowerCase('ru-RU')
                    .replace(/ё/g, 'е')
                    .replace(/[—–-]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            function filterDoctorOptions() {
                if (!doctorDropdown || !doctorSearch) {
                    return;
                }

                const terms = normalizeDoctorSearch(doctorSearch.value)
                    .split(' ')
                    .filter(Boolean);

                let visibleDoctors = 0;

                doctorOptions.forEach(option => {
                    const search = normalizeDoctorSearch(option.dataset.search);
                    const isAll = option.dataset.doctorId === '';
                    const match = isAll || terms.every(term => search.includes(term));

                    option.classList.toggle('d-none', !match);
                    option.classList.toggle(
                        'is-active',
                        !isAll && option.dataset.doctorId === doctorSelect?.value
                    );

                    if (match && !isAll) {
                        visibleDoctors++;
                    }
                });

                doctorEmpty?.classList.toggle('d-none', visibleDoctors > 0);
            }

            function openDoctorDropdown() {
                if (!doctorDropdown) {
                    return;
                }

                doctorDropdown.classList.remove('d-none');
                filterDoctorOptions();
            }

            function closeDoctorDropdown() {
                doctorDropdown?.classList.add('d-none');
            }

            doctorSearch?.addEventListener('focus', function () {
                /* При фокусе выбранный label заменяем поисковым текстом,
                 * чтобы можно было сразу начать вводить новое ФИО. */
                if (doctorSelect?.value) {
                    doctorSearch.select();
                }

                openDoctorDropdown();
            });

            doctorSearch?.addEventListener('input', function () {
                if (doctorSelect?.value) {
                    doctorSelect.value = '';
                    doctorSelect.dispatchEvent(new Event('change', {bubbles: true}));
                }

                doctorClear?.classList.toggle('d-none', !doctorSearch.value.trim());
                openDoctorDropdown();
            });

            doctorOptions.forEach(option => {
                option.addEventListener('click', function () {
                    if (!doctorSelect) {
                        return;
                    }

                    doctorSelect.value = option.dataset.doctorId || '';
                    doctorSelect.dispatchEvent(new Event('change', {bubbles: true}));
                    syncDoctorSearchText();
                    closeDoctorDropdown();
                });
            });

            doctorClear?.addEventListener('click', function () {
                if (doctorSelect) {
                    doctorSelect.value = '';
                    doctorSelect.dispatchEvent(new Event('change', {bubbles: true}));
                }

                if (doctorSearch) {
                    doctorSearch.value = '';
                    doctorSearch.focus();
                }

                doctorClear.classList.add('d-none');
                openDoctorDropdown();
            });

            /* ---------------------------------------------------------
             * Календарь даты рождения
             * --------------------------------------------------------- */
            const birthInput = document.getElementById('birth_at');
            const birthTrigger = document.getElementById('birthDateTrigger');
            const birthText = document.getElementById('birthDateText');
            const birthCalendar = document.getElementById('birthDateCalendar');
            const birthMonthLabel = document.getElementById('birthCalendarMonth');
            const birthWeekdays = document.getElementById('birthCalendarWeekdays');
            const birthDays = document.getElementById('birthCalendarDays');
            const birthYearPicker = document.getElementById('birthCalendarYearPicker');
            const birthYears = document.getElementById('birthCalendarYears');
            const birthMonths = document.getElementById('birthCalendarMonths');
            const birthYearInput = document.getElementById('birthCalendarYearInput');
            const birthYearGo = document.getElementById('birthCalendarYearGo');
            const birthYearError = document.getElementById('birthCalendarYearError');
            const birthBack = document.getElementById('birthCalendarBack');
            const birthPrev = document.getElementById('birthPrevMonth');
            const birthNext = document.getElementById('birthNextMonth');
            const birthToday = document.getElementById('birthTodayBtn');
            const birthClear = document.getElementById('birthClearBtn');

            const monthNames = [
                'январь', 'февраль', 'март', 'апрель', 'май', 'июнь',
                'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'
            ];

            function birthDate(year, month = 0, day = 1) {
                const date = new Date(0);
                date.setFullYear(year, month, day);
                date.setHours(0, 0, 0, 0);
                return date;
            }

            function birthTodayDate() {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return today;
            }

            function parseIsoDate(value) {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) {
                    return null;
                }

                const [year, month, day] = value.split('-').map(Number);
                const date = birthDate(year, month - 1, day);
                return year >= 1
                    && date.getFullYear() === year
                    && date.getMonth() === month - 1
                    && date.getDate() === day
                    ? date
                    : null;
            }

            function isoDate(date) {
                const year = String(date.getFullYear()).padStart(4, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function displayDate(date) {
                return [
                    String(date.getDate()).padStart(2, '0'),
                    String(date.getMonth() + 1).padStart(2, '0'),
                    String(date.getFullYear()).padStart(4, '0')
                ].join('.');
            }

            const selectedBirth = parseIsoDate(birthInput?.value || '') || birthTodayDate();
            let birthView = birthDate(selectedBirth.getFullYear(), selectedBirth.getMonth());
            let birthMode = 'days';
            let birthDecade = Math.floor(birthView.getFullYear() / 10) * 10;

            function syncBirthText() {
                if (!birthText || !birthTrigger) {
                    return;
                }

                const selected = parseIsoDate(birthInput?.value || '');
                birthText.textContent = selected ? displayDate(selected) : 'дд.мм.гггг';
                birthTrigger.classList.toggle('is-placeholder', !selected);
            }

            function closeBirthCalendar() {
                birthCalendar?.classList.add('d-none');
                birthTrigger?.setAttribute('aria-expanded', 'false');
            }

            function setBirthNavigation(previousLabel, nextLabel, previousDisabled, nextDisabled) {
                if (birthPrev) {
                    birthPrev.disabled = previousDisabled;
                    birthPrev.setAttribute('aria-label', previousLabel);
                    birthPrev.title = previousLabel;
                }
                if (birthNext) {
                    birthNext.disabled = nextDisabled;
                    birthNext.setAttribute('aria-label', nextLabel);
                    birthNext.title = nextLabel;
                }
            }

            function focusBirthChoice() {
                const panel = birthMode === 'months' ? birthMonths : birthDays;
                const selected = panel?.querySelector('.is-selected:not(:disabled)');
                const first = panel?.querySelector('button:not(:disabled)');
                (selected || first)?.focus();
            }

            function chooseBirthYear(year) {
                const today = birthTodayDate();
                if (!Number.isInteger(year) || year < 1 || year > today.getFullYear()) {
                    return false;
                }

                const month = year === today.getFullYear()
                    ? Math.min(birthView.getMonth(), today.getMonth())
                    : birthView.getMonth();
                birthView = birthDate(year, month);
                birthMode = 'months';
                renderBirthCalendar();
                focusBirthChoice();
                return true;
            }

            function showBirthYears() {
                birthMode = 'years';
                birthDecade = Math.floor(birthView.getFullYear() / 10) * 10;
                if (birthYearInput) {
                    birthYearInput.value = String(birthView.getFullYear());
                    birthYearInput.removeAttribute('aria-invalid');
                }
                birthYearError?.classList.add('d-none');
                renderBirthCalendar();
                birthYearInput?.focus();
                birthYearInput?.select();
            }

            function renderBirthCalendar() {
                if (!birthDays || !birthMonthLabel || !birthYears || !birthMonths) {
                    return;
                }

                const today = birthTodayDate();
                const nowMonth = birthDate(today.getFullYear(), today.getMonth());
                if (birthView > nowMonth) {
                    birthView = nowMonth;
                }

                const year = birthView.getFullYear();
                const month = birthView.getMonth();
                birthWeekdays?.classList.toggle('d-none', birthMode !== 'days');
                birthDays.classList.toggle('d-none', birthMode !== 'days');
                birthYearPicker?.classList.toggle('d-none', birthMode !== 'years');
                birthMonths.classList.toggle('d-none', birthMode !== 'months');
                birthBack?.classList.toggle('d-none', birthMode === 'days');
                birthMonthLabel.disabled = birthMode === 'years';
                birthMonthLabel.title = birthMode === 'years' ? '' : 'Выбрать год';
                birthMonthLabel.setAttribute('aria-label', birthMode === 'years'
                    ? 'Диапазон годов'
                    : 'Выбрать год');

                if (birthMode === 'years') {
                    birthMonthLabel.textContent = `${Math.max(1, birthDecade)}–${birthDecade + 9}`;
                    setBirthNavigation(
                        'Предыдущее десятилетие', 'Следующее десятилетие',
                        birthDecade <= 0,
                        birthDecade >= Math.floor(today.getFullYear() / 10) * 10
                    );
                    birthYears.innerHTML = '';

                    for (let candidate = Math.max(1, birthDecade); candidate <= birthDecade + 9; candidate++) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'reception-calendar__choice';
                        button.textContent = String(candidate);
                        button.dataset.year = String(candidate);
                        button.disabled = candidate > today.getFullYear();
                        button.classList.toggle('is-selected', candidate === year);
                        button.setAttribute('aria-pressed', String(candidate === year));
                        button.addEventListener('click', () => chooseBirthYear(candidate));
                        birthYears.appendChild(button);
                    }
                    return;
                }

                if (birthMode === 'months') {
                    birthMonthLabel.textContent = String(year);
                    setBirthNavigation('Предыдущий год', 'Следующий год', year <= 1, year >= today.getFullYear());
                    birthMonths.innerHTML = '';

                    monthNames.forEach((name, index) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'reception-calendar__choice';
                        button.textContent = name;
                        button.dataset.month = String(index);
                        button.disabled = birthDate(year, index) > today;
                        button.classList.toggle('is-selected', index === month);
                        button.setAttribute('aria-pressed', String(index === month));
                        button.addEventListener('click', function () {
                            if (button.disabled) {
                                return;
                            }
                            birthView = birthDate(year, index);
                            birthMode = 'days';
                            renderBirthCalendar();
                            focusBirthChoice();
                        });
                        birthMonths.appendChild(button);
                    });
                    return;
                }

                birthMonthLabel.textContent = `${monthNames[month]} ${year}`;
                setBirthNavigation(
                    'Предыдущий месяц', 'Следующий месяц',
                    year === 1 && month === 0,
                    birthDate(year, month + 1) > nowMonth
                );
                birthDays.innerHTML = '';
                const firstDay = birthDate(year, month);
                const mondayIndex = (firstDay.getDay() + 6) % 7;
                const gridStart = birthDate(year, month, 1 - mondayIndex);
                const selected = parseIsoDate(birthInput?.value || '');

                for (let i = 0; i < 42; i++) {
                    const date = new Date(gridStart);
                    date.setDate(gridStart.getDate() + i);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'reception-calendar__day';
                    button.textContent = String(date.getDate());
                    button.dataset.date = isoDate(date);
                    button.setAttribute('aria-label', displayDate(date));
                    button.classList.toggle('is-other-month', date.getMonth() !== month);
                    button.classList.toggle('is-weekend', date.getDay() === 0 || date.getDay() === 6);
                    button.classList.toggle('is-selected', Boolean(selected && isoDate(selected) === isoDate(date)));
                    button.disabled = date > today || date.getFullYear() < 1;
                    button.addEventListener('click', function () {
                        if (!birthInput || button.disabled) {
                            return;
                        }
                        birthInput.value = isoDate(date);
                        birthView = birthDate(date.getFullYear(), date.getMonth());
                        syncBirthText();
                        closeBirthCalendar();
                        birthTrigger?.focus();
                    });
                    birthDays.appendChild(button);
                }
            }

            function navigateBirthCalendar(direction) {
                const today = birthTodayDate();
                if (birthMode === 'years') {
                    const candidate = birthDecade + direction * 10;
                    if (candidate < 0 || candidate > Math.floor(today.getFullYear() / 10) * 10) {
                        return;
                    }
                    birthDecade = candidate;
                } else if (birthMode === 'months') {
                    const candidate = birthView.getFullYear() + direction;
                    if (candidate < 1 || candidate > today.getFullYear()) {
                        return;
                    }
                    birthView = birthDate(candidate, Math.min(
                        birthView.getMonth(),
                        candidate === today.getFullYear() ? today.getMonth() : 11
                    ));
                } else {
                    const candidate = birthDate(birthView.getFullYear(), birthView.getMonth() + direction);
                    if (candidate.getFullYear() < 1 || candidate > birthDate(today.getFullYear(), today.getMonth())) {
                        return;
                    }
                    birthView = candidate;
                }
                renderBirthCalendar();
            }

            function jumpToBirthYear() {
                const value = (birthYearInput?.value || '').trim();
                if (!/^\d{1,4}$/.test(value) || !chooseBirthYear(Number(value))) {
                    if (birthYearError) {
                        birthYearError.textContent = `Укажите год от 1 до ${birthTodayDate().getFullYear()}.`;
                        birthYearError.classList.remove('d-none');
                    }
                    birthYearInput?.setAttribute('aria-invalid', 'true');
                    birthYearInput?.focus();
                }
            }

            birthTrigger?.addEventListener('click', function () {
                if (!birthCalendar?.classList.contains('d-none')) {
                    closeBirthCalendar();
                    return;
                }
                const selected = parseIsoDate(birthInput?.value || '') || birthTodayDate();
                birthView = birthDate(selected.getFullYear(), selected.getMonth());
                birthMode = 'days';
                renderBirthCalendar();
                birthCalendar.classList.remove('d-none');
                birthTrigger.setAttribute('aria-expanded', 'true');
                closeDoctorDropdown();
            });

            birthMonthLabel?.addEventListener('click', showBirthYears);
            birthPrev?.addEventListener('click', () => navigateBirthCalendar(-1));
            birthNext?.addEventListener('click', () => navigateBirthCalendar(1));
            birthYearGo?.addEventListener('click', jumpToBirthYear);
            birthYearInput?.addEventListener('input', function () {
                birthYearError?.classList.add('d-none');
                birthYearInput.removeAttribute('aria-invalid');
            });
            birthYearInput?.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    event.stopPropagation();
                    jumpToBirthYear();
                }
            });
            birthBack?.addEventListener('click', function () {
                if (birthMode === 'months') {
                    showBirthYears();
                } else {
                    birthMode = 'days';
                    renderBirthCalendar();
                    focusBirthChoice();
                }
            });

            function handleBirthEscape(event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                    closeBirthCalendar();
                    birthTrigger?.focus();
                }
            }

            birthCalendar?.addEventListener('keydown', handleBirthEscape);
            birthTrigger?.addEventListener('keydown', handleBirthEscape);

            birthToday?.addEventListener('click', function () {
                const today = birthTodayDate();
                if (birthInput) {
                    birthInput.value = isoDate(today);
                }
                birthView = birthDate(today.getFullYear(), today.getMonth());
                birthMode = 'days';
                syncBirthText();
                closeBirthCalendar();
                birthTrigger?.focus();
            });

            birthClear?.addEventListener('click', function () {
                if (birthInput) {
                    birthInput.value = '';
                }
                birthMode = 'days';
                syncBirthText();
                closeBirthCalendar();
                birthTrigger?.focus();
            });

            /* ---------------------------------------------------------
             * DaData — место прописки
             * --------------------------------------------------------- */
            const addressSuggestionsUrl = @json(route('api-search-address'));
            const addressInput = document.getElementById('address_residence');
            const addressSuggestions = document.getElementById('addressResidenceSuggestions');
            let addressTimer = null;
            let addressAbort = null;
            let addressActiveIndex = -1;

            function hideAddressSuggestions() {
                addressSuggestions?.classList.add('d-none');
                addressActiveIndex = -1;
            }

            function addressButtons() {
                return addressSuggestions
                    ? Array.from(addressSuggestions.querySelectorAll('.reception-address-suggestion'))
                    : [];
            }

            function setAddressActive(index) {
                const buttons = addressButtons();

                if (!buttons.length) {
                    addressActiveIndex = -1;
                    return;
                }

                addressActiveIndex = Math.max(0, Math.min(index, buttons.length - 1));

                buttons.forEach((button, buttonIndex) => {
                    button.classList.toggle('is-active', buttonIndex === addressActiveIndex);
                });

                buttons[addressActiveIndex]?.scrollIntoView({block: 'nearest'});
            }

            function chooseAddress(button) {
                if (!addressInput || !button) {
                    return;
                }

                addressInput.value = button.dataset.value || '';
                hideAddressSuggestions();
            }

            async function loadAddressSuggestions(query) {
                if (!addressSuggestions || query.length < 2) {
                    hideAddressSuggestions();
                    return;
                }

                if (addressAbort) {
                    addressAbort.abort();
                }

                addressAbort = new AbortController();

                try {
                    const response = await fetch(
                        `${addressSuggestionsUrl}?query=${encodeURIComponent(query)}`,
                        {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: addressAbort.signal
                        }
                    );

                    if (!response.ok) {
                        throw new Error(`Address suggestions HTTP ${response.status}`);
                    }

                    const payload = await response.json();
                    addressSuggestions.innerHTML = '';
                    addressActiveIndex = -1;

                    (payload.suggestions || []).forEach(suggestion => {
                        const button = document.createElement('button');
                        const meta = [suggestion.region, suggestion.city]
                            .filter(Boolean)
                            .filter((value, index, array) => array.indexOf(value) === index)
                            .join(' · ');

                        button.type = 'button';
                        button.className = 'reception-address-suggestion';
                        button.dataset.value = suggestion.value || suggestion.unrestricted_value || '';
                        button.innerHTML = `
                            <i class="bi bi-geo-alt"></i>
                            <span>
                                <strong></strong>
                                <small></small>
                            </span>
                        `;
                        button.querySelector('strong').textContent = suggestion.value || suggestion.unrestricted_value || '';
                        button.querySelector('small').textContent = meta;
                        button.addEventListener('mousedown', event => event.preventDefault());
                        button.addEventListener('click', () => chooseAddress(button));
                        addressSuggestions.appendChild(button);
                    });

                    addressSuggestions.classList.toggle(
                        'd-none',
                        !addressButtons().length
                    );
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Address suggestions:', error);
                    }
                }
            }

            addressInput?.addEventListener('input', function () {
                window.clearTimeout(addressTimer);
                const query = addressInput.value.trim();

                addressTimer = window.setTimeout(
                    () => loadAddressSuggestions(query),
                    250
                );
            });

            addressInput?.addEventListener('keydown', function (event) {
                const buttons = addressButtons();

                if (event.key === 'ArrowDown' && buttons.length) {
                    event.preventDefault();
                    setAddressActive(addressActiveIndex + 1);
                } else if (event.key === 'ArrowUp' && buttons.length) {
                    event.preventDefault();
                    setAddressActive(addressActiveIndex <= 0 ? buttons.length - 1 : addressActiveIndex - 1);
                } else if (event.key === 'Enter' && addressActiveIndex >= 0 && buttons[addressActiveIndex]) {
                    event.preventDefault();
                    chooseAddress(buttons[addressActiveIndex]);
                } else if (event.key === 'Escape') {
                    hideAddressSuggestions();
                }
            });

            /* ---------------------------------------------------------
             * Общие закрытия popup'ов
             * --------------------------------------------------------- */
            document.addEventListener('click', function (event) {
                if (doctorWrap && !doctorWrap.contains(event.target)) {
                    closeDoctorDropdown();
                    syncDoctorSearchText();
                }

                const clickPath = typeof event.composedPath === 'function'
                    ? event.composedPath()
                    : [];
                if (birthCalendar && birthTrigger
                    && !clickPath.includes(birthCalendar)
                    && !clickPath.includes(birthTrigger)
                    && !birthCalendar.contains(event.target)
                    && !birthTrigger.contains(event.target)) {
                    closeBirthCalendar();
                }

                if (addressInput && addressSuggestions && !addressSuggestions.contains(event.target) && event.target !== addressInput) {
                    hideAddressSuggestions();
                }
            });

            syncDoctorSearchText();
            syncBirthText();
        });
    </script>

    <script id="placement-edit-script">
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('placementEditModal');
    const form = document.getElementById('placementEditForm');
    if (!modal || !form) return;

    const elements = {
        patient: document.getElementById('placementEditPatient'),
        location: document.getElementById('placementEditCurrentLocation'),
        assignedAt: document.getElementById('placementEditAssignedAt'),
        warning: document.getElementById('placementEditWarning'),
        help: document.getElementById('placementDepartmentHelp'),
        status: document.getElementById('placementEditStatus'),
        error: document.getElementById('placementEditError'),
        reload: document.getElementById('placementOptionsReload'),
        submit: document.getElementById('placementEditSubmit'),
    };
    const dismissButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
    let context = null;
    let modalOpen = false;
    let loading = false;
    let saving = false;
    let optionsReady = false;
    let optionsController = null;
    let departments = [];
    let rooms = [];
    const pickers = {};

    function positiveId(value) {
        const text = String(value ?? '');
        const number = Number(text);
        return /^\d+$/.test(text) && Number.isSafeInteger(number) && number > 0 ? String(number) : '';
    }

    function createPicker(prefix, onChange) {
        const root = document.getElementById(prefix + 'Picker');
        const select = document.getElementById(prefix + 'Select');
        const search = document.getElementById(prefix + 'Search');
        const dropdown = document.getElementById(prefix + 'Options');
        const empty = document.createElement('div');
        empty.className = 'placement-picker-empty';
        empty.textContent = 'Ничего не найдено.';
        let items = [];
        let selectable = [];
        let activeIndex = -1;

        function close() {
            dropdown.classList.add('d-none');
            search.setAttribute('aria-expanded', 'false');
            search.removeAttribute('aria-activedescendant');
        }
        function highlight(index) {
            activeIndex = index;
            items.forEach(item => item.button.classList.remove('is-active'));
            const active = selectable[index];
            if (active) {
                active.button.classList.add('is-active');
                search.setAttribute('aria-activedescendant', active.button.id);
            } else {
                search.removeAttribute('aria-activedescendant');
            }
        }
        function open(query = '') {
            if (search.disabled) return;
            const term = String(query).trim().toLowerCase();
            const visible = items.filter(item => {
                const matches = item.label.toLowerCase().includes(term);
                item.button.classList.toggle('d-none', !matches);
                return matches;
            });
            selectable = visible.filter(item => !item.disabled);
            empty.classList.toggle('d-none', visible.length > 0);
            dropdown.classList.remove('d-none');
            search.setAttribute('aria-expanded', 'true');
            const selectedIndex = selectable.findIndex(item => item.id === select.value);
            highlight(selectedIndex >= 0 ? selectedIndex : (selectable.length ? 0 : -1));
        }
        function setValue(value, notify = false) {
            const selected = items.find(item => item.id === String(value ?? '') && !item.disabled);
            select.value = selected?.id ?? '';
            search.value = selected?.label ?? '';
            search.setCustomValidity(selected ? '' : 'Выберите значение из списка.');
            items.forEach(item => item.button.setAttribute('aria-selected', String(item === selected)));
            close();
            if (notify) onChange();
        }
        function setItems(choices, placeholder) {
            select.replaceChildren();
            dropdown.replaceChildren();
            select.add(new Option(placeholder, ''));
            search.placeholder = placeholder;
            items = choices.map(item => ({
                id: positiveId(item.id), label: String(item.label ?? ''),
                disabled: item.disabled === true, reason: String(item.reason ?? ''),
            })).filter(item => item.id);
            items.forEach((item, index) => {
                const option = new Option(item.label, item.id);
                option.disabled = item.disabled;
                select.add(option);
                const button = document.createElement('button');
                button.type = 'button';
                button.id = prefix + 'Option' + index;
                button.className = 'placement-picker-option';
                button.tabIndex = -1;
                button.disabled = item.disabled;
                button.setAttribute('role', 'option');
                const label = document.createElement('span');
                label.textContent = item.label;
                button.appendChild(label);
                if (item.reason) {
                    const reason = document.createElement('small');
                    reason.textContent = item.reason;
                    button.appendChild(reason);
                }
                button.addEventListener('mousedown', event => event.preventDefault());
                button.addEventListener('click', () => { if (!item.disabled && !search.disabled) setValue(item.id, true); });
                item.button = button;
                dropdown.appendChild(button);
            });
            dropdown.appendChild(empty);
            setValue('');
        }
        search.addEventListener('focus', () => {
            open(select.value ? '' : search.value);
            search.select();
        });
        search.addEventListener('input', () => {
            select.value = '';
            search.setCustomValidity('Выберите значение из списка.');
            items.forEach(item => item.button.setAttribute('aria-selected', 'false'));
            open(search.value);
            onChange();
        });
        search.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (dropdown.classList.contains('d-none')) open(select.value ? '' : search.value);
                else if (selectable.length) {
                    highlight((activeIndex + (event.key === 'ArrowDown' ? 1 : -1) + selectable.length) % selectable.length);
                }
            } else if (event.key === 'Enter' && !dropdown.classList.contains('d-none')) {
                event.preventDefault();
                if (selectable[activeIndex]) setValue(selectable[activeIndex].id, true);
            } else if (event.key === 'Escape' && !dropdown.classList.contains('d-none')) {
                event.preventDefault();
                event.stopPropagation();
                close();
            } else if (event.key === 'Tab') close();
        });
        search.addEventListener('blur', () => window.setTimeout(close, 120));
        document.addEventListener('click', event => { if (!root.contains(event.target)) close(); });
        select.addEventListener('change', () => setValue(select.value, true));
        select.required = false;
        select.classList.add('d-none');
        search.required = true;
        search.classList.remove('d-none');
        const label = document.querySelector('label[for="' + prefix + 'Select"]');
        if (label) label.htmlFor = search.id;
        return {
            setItems, setValue,
            value: () => select.value,
            setDisabled(disabled) {
                select.disabled = search.disabled = disabled || items.length === 0;
                if (search.disabled) close();
            },
        };
    }

    function selection() {
        const department = departments.find(item => String(item.id) === pickers.department.value());
        const room = rooms.find(item => String(item.id) === pickers.room.value()
            && String(item.department_id) === pickers.department.value());
        const bed = (Array.isArray(room?.beds) ? room.beds : []).find(item => String(item.id) === pickers.bed.value());
        return { department, room, bed };
    }
    function updateControls() {
        const selected = selection();
        const blocked = !modalOpen || !context || loading || saving || !optionsReady;
        pickers.department.setDisabled(blocked);
        pickers.room.setDisabled(blocked || selected.department?.patient_eligible !== true);
        pickers.bed.setDisabled(blocked || !selected.room);
        elements.submit.disabled = blocked || selected.department?.patient_eligible !== true || !selected.room || !selected.bed;
        elements.reload.disabled = !modalOpen || !context || loading || saving;
        form.setAttribute('aria-busy', String(loading || saving));
    }
    function clearError() {
        elements.error.textContent = '';
        elements.error.classList.add('d-none');
    }
    function showError(message) {
        elements.error.textContent = message;
        elements.error.classList.remove('d-none');
    }
    function updateSelectionMessage() {
        const selected = selection();
        elements.help.textContent = selected.department?.patient_restriction
            || selected.department?.age_group_label || 'Выберите подходящее отделение.';
        if (optionsReady) {
            if (!departments.length) elements.status.textContent = 'Доступных отделений нет.';
            else if (!departments.some(item => item.patient_eligible === true)) elements.status.textContent = 'Для пациента нет подходящих отделений. Причины указаны в списке.';
            else if (!selected.department) elements.status.textContent = 'Выберите отделение, палату и койку для этой записи.';
            else if (!rooms.some(item => String(item.department_id) === pickers.department.value())) elements.status.textContent = 'В выбранном отделении нет доступных палат.';
            else if (!selected.room) elements.status.textContent = 'Выберите палату и койку.';
            else if (!Array.isArray(selected.room.beds) || !selected.room.beds.length) elements.status.textContent = 'В выбранной палате нет доступных коек.';
            else if (!selected.bed) elements.status.textContent = 'Выберите койку.';
            else elements.status.textContent = 'Размещение выбрано. Нажмите «Сохранить запись».';
        }
        updateControls();
    }
    function updateBeds(bedId = '') {
        const selected = selection();
        const beds = Array.isArray(selected.room?.beds) ? selected.room.beds : [];
        pickers.bed.setItems(beds, selected.room ? (beds.length ? 'Найти койку' : 'Нет доступных коек') : 'Сначала выберите палату');
        pickers.bed.setValue(bedId);
        updateSelectionMessage();
    }
    function updateRooms(roomId = '', bedId = '') {
        const selected = selection();
        const list = selected.department?.patient_eligible === true
            ? rooms.filter(item => String(item.department_id) === pickers.department.value()) : [];
        pickers.room.setItems(list, selected.department ? (list.length ? 'Найти палату' : 'Нет доступных палат') : 'Сначала выберите отделение');
        pickers.room.setValue(roomId);
        updateBeds(bedId);
    }
    pickers.department = createPicker('placementDepartment', () => { clearError(); updateRooms(); });
    pickers.room = createPicker('placementRoom', () => { clearError(); updateBeds(); });
    pickers.bed = createPicker('placementBed', () => { clearError(); updateSelectionMessage(); });

    function responseMessage(response, payload, fallback) {
        if (response.status === 419) return 'Сессия истекла. Обновите страницу и повторите действие.';
        const validation = payload?.errors && typeof payload.errors === 'object'
            ? Object.values(payload.errors).flat().find(message => typeof message === 'string') : null;
        const message = typeof payload?.message === 'string' ? payload.message : null;
        return validation || message || (response.status === 403 ? 'Нет права редактировать эту запись размещения.' : fallback);
    }
    async function loadOptions(expected, keepError = false) {
        if (!modalOpen || context !== expected || saving) return;
        optionsController?.abort();
        const controller = new AbortController();
        optionsController = controller;
        loading = true;
        optionsReady = false;
        departments = [];
        rooms = [];
        if (!keepError) clearError();
        elements.status.textContent = 'Загружаем варианты размещения…';
        elements.help.textContent = 'Выберите подходящее отделение.';
        pickers.department.setItems([], 'Загружаем отделения…');
        pickers.room.setItems([], 'Сначала выберите отделение');
        pickers.bed.setItems([], 'Сначала выберите палату');
        updateControls();
        try {
            const url = new URL(expected.optionsUrl, window.location.href);
            url.searchParams.set('intent', 'edit_placement');
            url.searchParams.set('patient_id', expected.patientId);
            url.searchParams.set('assignment_id', expected.assignmentId);
            url.searchParams.set('current_bed_id', expected.currentBedId);
            const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal, cache: 'no-store' });
            const payload = await response.json().catch(() => null);
            if (controller.signal.aborted || context !== expected || !modalOpen || optionsController !== controller) return;
            if (!response.ok) throw new Error(responseMessage(response, payload, 'Не удалось загрузить варианты размещения.'));
            const placement = payload?.placement;
            if (!placement || String(placement.patient_id) !== expected.patientId
                || String(placement.assignment_id) !== expected.assignmentId
                || String(placement.current_bed_id) !== expected.currentBedId) {
                throw new Error('Запись размещения изменилась. Обновите страницу и откройте её снова.');
            }
            if (!Array.isArray(payload.departments) || !Array.isArray(payload.rooms)) throw new Error('Не удалось получить варианты размещения. Повторите загрузку.');
            departments = payload.departments;
            rooms = payload.rooms;
            elements.patient.textContent = placement.patient_name || expected.patientName;
            elements.location.textContent = placement.location || expected.location;
            elements.assignedAt.textContent = placement.assigned_at_display || expected.assignedAt || '—';
            pickers.department.setItems(departments.map(item => ({
                id: item.id,
                label: [item.name, item.building, item.age_group_label].filter(Boolean).join(' · '),
                disabled: item.patient_eligible !== true,
                reason: item.patient_eligible !== true ? (item.patient_restriction || 'Размещение в этом отделении недоступно.') : '',
            })), departments.length ? 'Найти отделение' : 'Нет доступных отделений');
            const currentDepartment = departments.find(item => String(item.id) === String(placement.department_id));
            if (currentDepartment?.patient_eligible === true) pickers.department.setValue(placement.department_id);
            optionsReady = true;
            updateRooms(placement.room_id, placement.bed_id);
        } catch (error) {
            if (!controller.signal.aborted && context === expected && modalOpen && optionsController === controller) {
                optionsReady = false;
                showError(error.name === 'TypeError' ? 'Не удалось загрузить варианты размещения. Проверьте соединение и повторите попытку.' : error.message);
                elements.status.textContent = 'Варианты размещения не загружены.';
            }
        } finally {
            if (context === expected && modalOpen && optionsController === controller) {
                loading = false;
                optionsController = null;
                updateControls();
            }
        }
    }
    function setSaving(value) {
        saving = value;
        elements.submit.textContent = value ? 'Сохраняем…' : 'Сохранить запись';
        dismissButtons.forEach(button => { button.disabled = value; });
        if (context?.source) context.source.disabled = value;
        updateControls();
    }
    modal.addEventListener('show.bs.modal', function (event) {
        if (saving) { event.preventDefault(); return; }
        const button = event.relatedTarget;
        const data = button?.dataset;
        const patientId = positiveId(data?.patientId);
        const assignmentId = positiveId(data?.assignmentId);
        const currentBedId = positiveId(data?.currentBedId);
        if (!patientId || !assignmentId || !currentBedId || !data?.placementEditUrl || !data?.placementOptionsUrl) {
            event.preventDefault();
            return;
        }
        let saveUrl, optionsUrl;
        try {
            saveUrl = new URL(data.placementEditUrl, window.location.href);
            optionsUrl = new URL(data.placementOptionsUrl, window.location.href);
            if (saveUrl.origin !== window.location.origin || optionsUrl.origin !== window.location.origin) throw new Error('Invalid origin');
        } catch (error) { event.preventDefault(); return; }
        optionsController?.abort();
        context = { source: button, patientId, assignmentId, currentBedId, saveUrl: String(saveUrl), optionsUrl: String(optionsUrl),
            patientName: data.patientName || 'Пациент', location: data.currentLocation || '—', assignedAt: data.assignedAt || '—' };
        modalOpen = true;
        form.reset();
        form.action = context.saveUrl;
        elements.patient.textContent = context.patientName;
        elements.location.textContent = context.location;
        elements.assignedAt.textContent = context.assignedAt;
        elements.warning.textContent = data.placementWarning || '';
        elements.warning.classList.toggle('d-none', !data.placementWarning);
        setSaving(false);
        loadOptions(context);
    });
    modal.addEventListener('hide.bs.modal', function (event) { if (saving) event.preventDefault(); });
    modal.addEventListener('hidden.bs.modal', function () {
        if (saving) return;
        modalOpen = false;
        optionsController?.abort();
        optionsController = null;
        context = null;
        loading = false;
        optionsReady = false;
        updateControls();
    });
    elements.reload.addEventListener('click', () => { if (context && !saving && !loading) loadOptions(context); });
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (!context || !modalOpen || loading || saving || !optionsReady) return;
        const selected = selection();
        if (selected.department?.patient_eligible !== true || !selected.room || !selected.bed) {
            showError('Выберите подходящее отделение, палату и койку.');
            return;
        }
        const csrf = form.querySelector('[name="_token"]')?.value;
        if (!csrf) { showError('Сессия истекла. Обновите страницу и повторите действие.'); return; }
        const expected = context;
        clearError();
        setSaving(true);
        let refreshOptions = false;
        let completed = false;
        try {
            const response = await fetch(expected.saveUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ intent: 'edit_placement', patient_id: Number(expected.patientId), assignment_id: Number(expected.assignmentId),
                    current_bed_id: Number(expected.currentBedId), department_id: Number(selected.department.id), room_id: Number(selected.room.id), bed_id: Number(selected.bed.id) }),
            });
            const payload = await response.json().catch(() => null);
            if (context !== expected || !modalOpen) return;
            if (!response.ok || payload?.ok !== true) {
                refreshOptions = response.status === 422 || response.status === 409;
                throw new Error(responseMessage(response, payload, 'Не удалось сохранить запись размещения. Повторите попытку.'));
            }
            completed = true;
            window.location.reload();
        } catch (error) {
            if (context === expected && modalOpen) {
                optionsReady = false;
                showError(error.name === 'TypeError' ? 'Не удалось сохранить запись. Проверьте соединение и обновите варианты размещения.' : error.message);
            }
        } finally {
            if (context === expected && modalOpen && !completed) {
                setSaving(false);
                if (refreshOptions) loadOptions(expected, true);
            }
        }
    });
    updateControls();
});
    </script>
@endsection

@section('sub-main')
    <div class="reception-page">
        <div class="reception-header">
            <div>
                <h1>{{ $receptionPage['title'] }}</h1>
                <p>{{ $receptionPage['description'] }}</p>
            </div>

            <div class="reception-header-badge">
                <i class="bi bi-hospital"></i>
                {{ $receptionPage['badge'] }}
            </div>
        </div>

        <div class="reception-grid">
            <form class="reception-card reception-search" method="get" action="{{ $receptionPage['form_action'] }}">
                <div class="reception-card-header">
                    <div>
                        <h2 class="reception-card-title">{{ $receptionPage['search_title'] }}</h2>
                        <div class="reception-card-subtitle">
                            {{ $receptionPage['search_description'] }}
                        </div>
                    </div>

                    <i class="bi bi-search fs-4 text-secondary"></i>
                </div>

                <div class="reception-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="fullname" class="form-label">ФИО пациента</label>
                            <input id="fullname"
                                   name="fullname"
                                   class="form-control"
                                   placeholder="Например: Иванов И.И."
                                   value="{{ request('fullname', request('q')) }}">
                        </div>

                        <div class="col-md-6">
                            <label for="doctor_search" class="form-label">{{ $receptionPage['doctor_label'] }}</label>

                            <div class="reception-doctor-search" id="doctorSearchWrap">
                                <div class="reception-search-input-wrap">
                                    <i class="bi bi-search reception-search-input-icon"></i>
                                    <input id="doctor_search"
                                           class="form-control reception-search-input"
                                           type="text"
                                           autocomplete="off"
                                           placeholder="Все врачи — начните вводить ФИО или профиль">
                                    <button type="button"
                                            class="reception-search-clear d-none"
                                            id="doctorSearchClear"
                                            aria-label="Очистить врача">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <select id="doctor_id" name="doctor_id" class="d-none">
                                    <option value="">— Все врачи —</option>

                                    @foreach($doctorSearchOptions as $doctorOption)
                                        <option value="{{ $doctorOption['id'] }}"
                                                data-profile="{{ $doctorOption['profile'] }}"
                                                data-fullname="{{ $doctorOption['full_name'] }}"
                                                {{ $doctorOption['selected'] ? 'selected' : '' }}>
                                            {{ $doctorOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="reception-doctor-dropdown d-none" id="doctorSearchDropdown">
                                    <button type="button"
                                            class="reception-doctor-option"
                                            data-doctor-id=""
                                            data-search="все врачи">
                                        <span class="reception-doctor-option__icon">
                                            <i class="bi bi-people"></i>
                                        </span>
                                        <span class="reception-doctor-option__body">
                                            <strong>Все врачи</strong>
                                            <small>Не ограничивать поиск конкретным специалистом</small>
                                        </span>
                                    </button>

                                    @foreach($doctorSearchOptions as $doctorOption)
                                        <button type="button"
                                                class="reception-doctor-option"
                                                data-doctor-id="{{ $doctorOption['id'] }}"
                                                data-search="{{ $doctorOption['search_text'] }}">
                                            <span class="reception-doctor-option__icon">
                                                <i class="bi bi-person-badge"></i>
                                            </span>
                                            <span class="reception-doctor-option__body">
                                                <strong>{{ $doctorOption['full_name'] }}</strong>
                                                <small>{{ $doctorOption['profile'] }}</small>
                                            </span>
                                        </button>
                                    @endforeach

                                    <div class="reception-doctor-empty d-none" id="doctorSearchEmpty">
                                        Врачи по этому запросу не найдены.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="birthDateTrigger" class="form-label">Дата рождения</label>

                            <div class="reception-date-picker" id="birthDatePicker">
                                <input id="birth_at"
                                       name="birth_at"
                                       type="hidden"
                                       value="{{ request('birth_at') }}">

                                <button type="button"
                                        class="form-control reception-date-trigger"
                                        id="birthDateTrigger"
                                        aria-expanded="false"
                                        aria-controls="birthDateCalendar">
                                    <span id="birthDateText">дд.мм.гггг</span>
                                    <i class="bi bi-calendar3"></i>
                                </button>

                                <div class="reception-calendar d-none" id="birthDateCalendar">
                                    <div class="reception-calendar__head">
                                        <button type="button" class="reception-calendar__nav" id="birthPrevMonth" aria-label="Предыдущий месяц">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>

                                        <button type="button"
                                                class="reception-calendar__month"
                                                id="birthCalendarMonth"
                                                title="Выбрать год"
                                                aria-label="Выбрать год"></button>

                                        <button type="button" class="reception-calendar__nav" id="birthNextMonth" aria-label="Следующий месяц">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>

                                    <div class="d-none" id="birthCalendarYearPicker">
                                        <div class="reception-calendar__year-jump">
                                            <label for="birthCalendarYearInput">Год</label>
                                            <input type="text"
                                                   id="birthCalendarYearInput"
                                                   class="reception-calendar__year-input"
                                                   inputmode="numeric"
                                                   maxlength="4"
                                                   autocomplete="off"
                                                   placeholder="Например, 1980"
                                                   aria-describedby="birthCalendarYearError">
                                            <button type="button"
                                                    class="reception-calendar__year-go"
                                                    id="birthCalendarYearGo">Перейти</button>
                                        </div>
                                        <div class="reception-calendar__year-error d-none"
                                             id="birthCalendarYearError"
                                             role="status"></div>
                                        <div class="reception-calendar__years"
                                             id="birthCalendarYears"
                                             role="group"
                                             aria-label="Выбор года"></div>
                                    </div>

                                    <div class="reception-calendar__months d-none"
                                         id="birthCalendarMonths"
                                         role="group"
                                         aria-label="Выбор месяца"></div>

                                    <div class="reception-calendar__weekdays" id="birthCalendarWeekdays">
                                        <span>Пн</span><span>Вт</span><span>Ср</span><span>Чт</span><span>Пт</span><span class="is-weekend">Сб</span><span class="is-weekend">Вс</span>
                                    </div>

                                    <div class="reception-calendar__days" id="birthCalendarDays"></div>

                                    <div class="reception-calendar__footer">
                                        <button type="button" class="reception-calendar__today" id="birthTodayBtn">Сегодня</button>
                                        <button type="button" class="reception-calendar__back d-none" id="birthCalendarBack">Назад</button>
                                        <button type="button" class="reception-calendar__clear" id="birthClearBtn">Очистить</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="address_residence" class="form-label">Адрес регистрации или проживания</label>

                            <div class="reception-address-wrap">
                                <i class="bi bi-geo-alt reception-address-icon"></i>
                                <input id="address_residence"
                                       name="address_residence"
                                       class="form-control reception-address-input"
                                       autocomplete="off"
                                       placeholder="Начните вводить адрес"
                                       value="{{ request('address_residence') }}">
                                <div class="reception-address-suggestions d-none" id="addressResidenceSuggestions"></div>
                            </div>
                        </div>
                    </div>

                    <div class="reception-search-actions">
                        <a href="{{ $receptionPage['reset_url'] }}" class="reception-btn-light">
                            Сбросить
                        </a>

                        <button type="submit" class="reception-btn-primary">
                            <i class="bi bi-search"></i>
                            {{ $receptionPage['search_button_label'] ?? 'Найти пациента' }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="reception-card">
                <div class="reception-card-header">
                    <div>
                        <h2 class="reception-card-title">Ваши данные</h2>
                        <div class="reception-card-subtitle">
                            Информация по текущему аккаунту и клинике.
                        </div>
                    </div>

                    <i class="bi bi-person-badge fs-4 text-secondary"></i>
                </div>

                <div class="reception-card-body">
                    <div class="account-info-list">
                        <div class="account-info-item">
                            <div class="account-info-icon">
                                <i class="bi bi-building"></i>
                            </div>

                            <div>
                                <div class="account-info-label">Клиника</div>
                                <div class="account-info-value">{{ $receptionClinicName }}</div>
                            </div>
                        </div>

                        <div class="account-info-item">
                            <div class="account-info-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>

                            <div>
                                <div class="account-info-label">Сотрудник</div>
                                <div class="account-info-value">
                                    {{ $surname }} {{ $name }} {{ $patronym }}
                                    <span class="text-muted">({{ $profession }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="reception-stats">
                        <div class="reception-stat">
                            <div class="reception-stat-label">{{ $receptionPage['first_stat_label'] }}</div>
                            <div class="reception-stat-value">{{ $receptionPage['first_stat_value'] }}</div>
                        </div>

                        <div class="reception-stat">
                            <div class="reception-stat-label">{{ $receptionPage['second_stat_label'] }}</div>
                            <div class="reception-stat-value">{{ $receptionPage['second_stat_value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reception-table-panel"@if($receptionPage['is_inpatient']) data-placement-editor-version="2026-10-18.2"@endif>
            <div class="reception-table-header">
                <h2>{{ $receptionPage['table_title'] }}</h2>

                <div class="reception-table-count">
                    {{ $receptionPage['count_label'] }}
                </div>
            </div>

            @if($receptions->count())
                <div class="reception-table-wrap">
                    <table class="reception-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пациент</th>
                            <th>Дата рождения</th>
                            @if($receptionPage['is_inpatient'])
                                <th>Размещение</th>
                            @endif
                            <th>{{ $receptionPage['doctor_label'] }}</th>
                            <th>Адрес прописки</th>
                            <th>Адрес проживания</th>
                            <th class="text-center">Действия</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($receptionRows as $receptionRow)
                            <tr>
                                <td>
                                    <span class="reception-id">#{{ $receptionRow['id'] }}</span>
                                </td>

                                <td>
                                    <div class="patient-cell">
                                        <div class="patient-avatar">
                                            {{ $receptionRow['patient_initials'] }}
                                        </div>

                                        <div>
                                            <div class="patient-name">
                                                {{ $receptionRow['patient_name'] }}
                                            </div>

                                            <div class="patient-meta">
                                                ID пациента: {{ $receptionRow['patient_id'] ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($receptionRow['birth_date'])
                                        {{ $receptionRow['birth_date'] }}
                                    @else
                                        <span class="empty-muted">Не указана</span>
                                    @endif
                                </td>

                                @if($receptionPage['is_inpatient'])
                                    <td>
                                        <div class="reception-location">
                                            @if($receptionRow['location_clinic'] ?? '')
                                                <div class="patient-meta">{{ $receptionRow['location_clinic'] }}</div>
                                            @endif
                                            <div class="reception-location__department">{{ $receptionRow['location_department'] }}</div>
                                            <div>{{ $receptionRow['location_room_bed'] }}</div>
                                            @if($receptionRow['assigned_at_display'])
                                                <div class="patient-meta">С {{ $receptionRow['assigned_at_display'] }}</div>
                                            @endif
                                            @if($receptionRow['placement_warning'] ?? null)
                                                <div class="reception-location__warning" role="note">
                                                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                                                    {{ $receptionRow['placement_warning'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endif

                                <td>
                                    <div class="doctor-cell">
                                        <div class="doctor-profession">
                                            {{ $receptionRow['doctor_profile'] }}
                                        </div>

                                        <div class="doctor-name">
                                            {{ $receptionRow['doctor_name'] }}
                                        </div>

                                        @if(!$receptionPage['is_inpatient'])
                                            <div class="appointment-format-badge {{ $receptionRow['appointment_format_class'] ?? 'is-in-person' }}">
                                                <i class="bi {{ $receptionRow['appointment_format_icon'] ?? 'bi-person-check' }}"></i>
                                                {{ $receptionRow['appointment_format_label'] ?? 'Очный приём' }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="address-cell">
                                        {{ $receptionRow['address_registration'] }}
                                    </div>
                                </td>

                                <td>
                                    <div class="address-cell">
                                        {{ $receptionRow['address_residence'] }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-actions">
                                        @if($receptionRow['medical_card_url'])
                                            <a href="{{ $receptionRow['medical_card_url'] }}"
                                               class="table-action-btn"
                                               title="Перейти к карточке пациента">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </a>
                                        @endif

                                        @if($receptionPage['is_inpatient']
                                            && !($receptionRow['placement_warning'] ?? null)
                                            && ($receptionRow['can_discharge'] ?? false)
                                            && ($receptionRow['discharge_url'] ?? null)
                                            && ($receptionRow['assignment_id'] ?? null)
                                            && ($receptionRow['patient_id'] ?? null))
                                            <button type="button"
                                                    class="table-action-btn table-action-btn-discharge"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmDischargeModal"
                                                    data-discharge-url="{{ $receptionRow['discharge_url'] }}"
                                                    data-patient-id="{{ $receptionRow['patient_id'] }}"
                                                    data-assignment-id="{{ $receptionRow['assignment_id'] }}"
                                                    data-patient-name="{{ $receptionRow['patient_name'] }}"
                                                    title="Выписать пациента из стационара">
                                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                                Выписать
                                            </button>
                                        @elseif($receptionPage['is_inpatient'] && !($receptionRow['placement_warning'] ?? null))
                                            <div class="table-discharge-unavailable">
                                                <button type="button"
                                                        class="table-action-btn table-action-btn-discharge"
                                                        aria-describedby="dischargeUnavailableReason{{ $loop->index }}"
                                                        disabled>
                                                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                                    Выписать
                                                </button>
                                                <div class="table-discharge-reason" id="dischargeUnavailableReason{{ $loop->index }}">
                                                    {{ ($receptionRow['discharge_unavailable_reason'] ?? null) ?: 'Не удалось получить данные для выписки. Обновите страницу.' }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($receptionPage['is_inpatient'] && (isset($receptionRow['can_edit_placement']) || ($receptionRow['placement_warning'] ?? null)))
                                            @if(($receptionRow['can_edit_placement'] ?? false)
                                                && ($receptionRow['placement_edit_url'] ?? null)
                                                && ($receptionRow['placement_options_url'] ?? null)
                                                && ($receptionRow['placement_bed_id'] ?? null)
                                                && ($receptionRow['assignment_id'] ?? null)
                                                && ($receptionRow['patient_id'] ?? null))
                                                <button type="button"
                                                        class="table-action-btn table-action-btn-placement"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#placementEditModal"
                                                        data-placement-edit-url="{{ $receptionRow['placement_edit_url'] }}"
                                                        data-placement-options-url="{{ $receptionRow['placement_options_url'] }}"
                                                        data-patient-id="{{ $receptionRow['patient_id'] }}"
                                                        data-assignment-id="{{ $receptionRow['assignment_id'] }}"
                                                        data-current-bed-id="{{ $receptionRow['placement_bed_id'] }}"
                                                        data-patient-name="{{ $receptionRow['patient_name'] }}"
                                                        data-current-location="{{ ($receptionRow['location_clinic'] ?? '') . ' · ' . ($receptionRow['location_department'] ?? '') . ' · ' . ($receptionRow['location_room_bed'] ?? '') }}"
                                                        data-assigned-at="{{ $receptionRow['assigned_at_display'] ?? '' }}"
                                                        data-placement-warning="{{ $receptionRow['placement_warning'] ?? '' }}">
                                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                                    Редактировать запись
                                                </button>
                                            @else
                                                <div class="table-placement-unavailable">
                                                    <button type="button"
                                                            class="table-action-btn table-action-btn-placement"
                                                            aria-describedby="placementEditUnavailableReason{{ $loop->index }}"
                                                            disabled>
                                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                                        Редактировать запись
                                                    </button>
                                                    <div class="table-discharge-reason" id="placementEditUnavailableReason{{ $loop->index }}">
                                                        {{ ($receptionRow['placement_edit_unavailable_reason'] ?? null) ?: 'Данные для редактирования не загружены. Обновите страницу; если сообщение осталось, требуется обновление модуля госпитализации.' }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        @if(!$receptionPage['is_inpatient'] && ($receptionRow['can_modify'] ?? false))
                                            @if($receptionRow['edit_url'])
                                                <a href="{{ $receptionRow['edit_url'] }}"
                                                   class="table-action-btn"
                                                   title="Редактировать запись">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endif

                                            <button type="button"
                                                    class="table-action-btn danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmArchiveModal"
                                                    data-record-id="{{ $receptionRow['id'] }}"
                                                    data-patient-name="{{ $receptionRow['patient_name'] }}"
                                                    title="Перенести в архив">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="reception-empty">
                    <div class="reception-empty-icon">
                        <i class="bi {{ $receptionPage['is_inpatient'] ? 'bi-person-x' : 'bi-calendar-x' }}" aria-hidden="true"></i>
                    </div>

                    <h3>{{ $receptionPage['empty_title'] }}</h3>

                    <p>{{ $receptionPage['empty_description'] }}</p>
                </div>
            @endif

            <div class="reception-table-footer">
                <span title="Показаны {{ $receptions->firstItem() ?? 0 }}–{{ $receptions->lastItem() ?? 0 }} из {{ $receptions->total() }}">
                    {{ $receptions->firstItem() ?? 0 }}–{{ $receptions->lastItem() ?? 0 }} из {{ $receptions->total() }}
                </span>

                @if($receptions->hasPages())
                    <nav class="reception-pagination" aria-label="Страницы результатов поиска">
                        @if($receptions->onFirstPage())
                            <span class="reception-pagination__link is-disabled" aria-disabled="true" aria-label="Первая страница">
                                <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
                            </span>
                            <span class="reception-pagination__link is-disabled" aria-disabled="true" aria-label="Предыдущая страница">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                            </span>
                        @else
                            <a class="reception-pagination__link" href="{{ $receptions->url(1) }}" aria-label="Первая страница" title="Первая страница">
                                <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
                            </a>
                            <a class="reception-pagination__link" href="{{ $receptions->previousPageUrl() }}" rel="prev" aria-label="Предыдущая страница" title="Предыдущая страница">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                            </a>
                        @endif

                        <span class="reception-pagination__position" aria-current="page">
                            {{ $receptions->currentPage() }} / {{ $receptions->lastPage() }}
                        </span>

                        @if($receptions->hasMorePages())
                            <a class="reception-pagination__link" href="{{ $receptions->nextPageUrl() }}" rel="next" aria-label="Следующая страница" title="Следующая страница">
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </a>
                            <a class="reception-pagination__link" href="{{ $receptions->url($receptions->lastPage()) }}" aria-label="Последняя страница" title="Последняя страница">
                                <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
                            </a>
                        @else
                            <span class="reception-pagination__link is-disabled" aria-disabled="true" aria-label="Следующая страница">
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </span>
                            <span class="reception-pagination__link is-disabled" aria-disabled="true" aria-label="Последняя страница">
                                <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
                            </span>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </div>

    @if($receptionPage['is_inpatient'])
    <div class="modal fade placement-edit-modal"
         id="placementEditModal"
         tabindex="-1"
         aria-labelledby="placementEditModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="placementEditForm" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="placementEditModalLabel">Редактирование записи размещения</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="placement-edit-summary">
                            <div><dt>Пациент</dt><dd id="placementEditPatient">—</dd></div>
                            <div><dt>Текущее размещение</dt><dd id="placementEditCurrentLocation">—</dd></div>
                            <div><dt>Дата размещения</dt><dd id="placementEditAssignedAt">—</dd></div>
                        </dl>
                        <div id="placementEditWarning" class="alert alert-warning d-none" role="note"></div>
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <strong>Отделение, палата и койка</strong>
                            <button type="button" id="placementOptionsReload" class="btn btn-sm btn-outline-secondary" disabled>Обновить варианты</button>
                        </div>
                        <div class="placement-edit-fields">
                            <div>
                                <label class="placement-edit-label" for="placementDepartmentSelect">Отделение *</label>
                                <div class="placement-picker" id="placementDepartmentPicker">
                                    <select id="placementDepartmentSelect" name="department_id" required disabled><option value="">Загружаем отделения…</option></select>
                                    <input type="search" id="placementDepartmentSearch" class="d-none" role="combobox" aria-autocomplete="list" aria-controls="placementDepartmentOptions" aria-describedby="placementDepartmentHelp" aria-expanded="false" autocomplete="off" placeholder="Найти отделение" disabled>
                                    <div id="placementDepartmentOptions" class="placement-picker-options d-none" role="listbox"></div>
                                </div>
                            </div>
                            <div>
                                <label class="placement-edit-label" for="placementRoomSelect">Палата *</label>
                                <div class="placement-picker" id="placementRoomPicker">
                                    <select id="placementRoomSelect" name="room_id" required disabled><option value="">Сначала выберите отделение</option></select>
                                    <input type="search" id="placementRoomSearch" class="d-none" role="combobox" aria-autocomplete="list" aria-controls="placementRoomOptions" aria-expanded="false" autocomplete="off" placeholder="Найти палату" disabled>
                                    <div id="placementRoomOptions" class="placement-picker-options d-none" role="listbox"></div>
                                </div>
                            </div>
                            <div>
                                <label class="placement-edit-label" for="placementBedSelect">Койка *</label>
                                <div class="placement-picker" id="placementBedPicker">
                                    <select id="placementBedSelect" name="bed_id" required disabled><option value="">Сначала выберите палату</option></select>
                                    <input type="search" id="placementBedSearch" class="d-none" role="combobox" aria-autocomplete="list" aria-controls="placementBedOptions" aria-expanded="false" autocomplete="off" placeholder="Найти койку" disabled>
                                    <div id="placementBedOptions" class="placement-picker-options d-none" role="listbox"></div>
                                </div>
                            </div>
                        </div>
                        <div class="placement-edit-help mt-3" id="placementDepartmentHelp">Выберите подходящее отделение.</div>
                        <div class="placement-edit-status mt-2" id="placementEditStatus" role="status" aria-live="polite">Загружаем варианты размещения…</div>
                        <div id="placementEditError" class="alert alert-danger mt-3 mb-0 d-none" role="alert" aria-live="polite"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="reception-btn-light" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="reception-btn-primary" id="placementEditSubmit" disabled>Сохранить запись</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade discharge-modal"
         id="confirmDischargeModal"
         tabindex="-1"
         aria-labelledby="confirmDischargeModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="inpatientDischargeForm" action="">
                    @csrf
                    <input type="hidden" name="patient_id" value="">
                    <input type="hidden" name="assignment_id" value="">
                    <input type="hidden" name="intent" value="discharge">

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDischargeModalLabel">Выписать пациента?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <div class="modal-body">
                        <p>Пациент: <strong id="dischargePatientName">—</strong>.</p>
                        <p class="mb-0">Текущее размещение в стационаре завершится, койка освободится. Пациент исчезнет из списка госпитализированных.</p>
                        <div id="inpatientDischargeError"
                             class="alert alert-danger border-0 mt-3 mb-0 d-none"
                             role="alert"
                             aria-live="polite"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="reception-btn-light" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="reception-btn-primary" id="inpatientDischargeSubmit">Да, выписать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if(!$receptionPage['is_inpatient'])
    <div class="modal fade archive-modal"
         id="confirmArchiveModal"
         tabindex="-1"
         aria-labelledby="confirmArchiveModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="archiveForm" action="">
                @csrf

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmArchiveModalLabel">
                            Перенести запись в архив?
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Закрыть"></button>
                    </div>

                    <div class="modal-body">
                        Вы действительно хотите перенести в архив запись пациента
                        <strong id="archivePatientName">—</strong>?
                        <br>
                        Запись исчезнет из текущего списка регистратуры.
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="reception-btn-light" data-bs-dismiss="modal">
                            Отмена
                        </button>

                        <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">
                            Да, перенести
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection
