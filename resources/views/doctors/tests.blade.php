@extends('layouts.doctor')
@section('title', 'Управление тестами')
@section('assets')
    <style>
        /* =========================
   Общая страница тестов
========================= */

        :root {
            --hospital-primary: #048696;
            --hospital-primary-light: #10b7c6;
            --hospital-primary-dark: #036b78;
            --hospital-bg: #f4f7fb;
            --hospital-card: #ffffff;
            --hospital-border: #e3e9ef;
            --hospital-text: #1f2937;
            --hospital-muted: #6b7280;
            --hospital-danger: #dc3545;
            --hospital-warning: #f59e0b;
            --hospital-success: #16a34a;
        }

        .bg-body-hospital {
            background: linear-gradient(135deg, var(--hospital-primary), var(--hospital-primary-light));
            box-shadow: 0 8px 24px rgba(4, 134, 150, 0.18);
        }

        .navbar.bg-body-hospital {
            margin: 0 0 28px 0;
            padding: 14px 22px;
        }

        .navbar.bg-body-hospital .nav-link {
            color: rgba(255, 255, 255, 0.88);
            font-weight: 600;
            font-size: 15px;
            padding: 9px 14px;
            border-radius: 10px;
            transition: 0.2s ease;
        }

        .navbar.bg-body-hospital .nav-link:hover,
        .navbar.bg-body-hospital .nav-link.active,
        .navbar.bg-body-hospital .nav-link.show {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.16);
        }

        .navbar.bg-body-hospital .dropdown-menu {
            border: 0;
            border-radius: 14px;
            padding: 8px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
        }

        .navbar.bg-body-hospital .dropdown-item {
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 500;
        }

        .navbar.bg-body-hospital .dropdown-item:hover {
            background: #eef8fa;
            color: var(--hospital-primary-dark);
        }

        .test-results-page {
            padding: 0 28px 40px 28px;
            background: var(--hospital-bg);
            min-height: calc(100vh - 80px);
        }

        .test-results-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 24px;
            padding: 28px;
            background: linear-gradient(135deg, #ffffff 0%, #f2fbfc 100%);
            border: 1px solid var(--hospital-border);
            border-radius: 24px;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
        }

        .test-results-title h1 {
            margin: 0 0 12px 0;
            font-size: 34px;
            line-height: 1.15;
            font-weight: 800;
            color: var(--hospital-text);
            letter-spacing: -0.04em;
        }

        .test-results-title p {
            max-width: 980px;
            margin: 0;
            color: var(--hospital-muted);
            font-size: 16px;
            line-height: 1.55;
        }

        .test-results-period {
            flex: 0 0 auto;
            padding: 10px 16px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--hospital-primary-dark);
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }


        /* =========================
           Статистика
        ========================= */

        .test-results-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .test-stat-card {
            position: relative;
            padding: 22px;
            background: var(--hospital-card);
            border: 1px solid var(--hospital-border);
            border-radius: 22px;
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .test-stat-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--hospital-primary), var(--hospital-primary-light));
        }

        .test-stat-label {
            margin-bottom: 10px;
            color: var(--hospital-muted);
            font-size: 14px;
            font-weight: 700;
        }

        .test-stat-value {
            margin-bottom: 4px;
            color: var(--hospital-text);
            font-size: 34px;
            line-height: 1;
            font-weight: 600;
        }

        .test-stat-hint {
            color: #8b95a1;
            font-size: 13px;
        }


        /* =========================
           Панель результатов
        ========================= */

        .test-results-panel {
            background: var(--hospital-card);
            border: 1px solid var(--hospital-border);
            border-radius: 24px;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .test-results-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            padding: 24px 28px;
            border-bottom: 1px solid var(--hospital-border);
            background: #ffffff;
        }

        .test-results-panel-header h2 {
            margin: 0;
            color: var(--hospital-text);
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.03em;
        }

        .test-results-search {
            width: min(420px, 100%);
        }

        .test-results-search .form-control {
            height: 44px;
            border-radius: 14px;
            border: 1px solid #d8e0e5;
            padding: 0 16px;
        }

        .test-results-search .form-control:focus {
            border-color: var(--hospital-primary);
            box-shadow: 0 0 0 0.2rem rgba(4, 134, 150, 0.14);
        }

        .test-results-table-wrap {
            overflow-x: auto;
        }

        .test-results-table {
            width: 100%;
            margin: 0;
            vertical-align: middle;
        }

        .test-results-table thead th {
            padding: 16px 20px;
            background: #f8fafc;
            color: #667085;
            border-bottom: 1px solid var(--hospital-border);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .test-results-table tbody td {
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f6;
            color: var(--hospital-text);
        }

        .test-results-table tbody tr {
            transition: 0.18s ease;
        }

        .test-results-table tbody tr:hover {
            background: #f8fcfd;
        }

        .test-results-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .patient-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .patient-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--hospital-primary), var(--hospital-primary-light));
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex: 0 0 auto;
        }

        .patient-name,
        .test-title {
            color: var(--hospital-text);
            font-weight: 700;
            font-size: 15px;
        }

        .patient-meta,
        .test-date,
        .test-result-text {
            margin-top: 3px;
            color: var(--hospital-muted);
            font-size: 13px;
        }

        .test-score {
            color: var(--hospital-text);
            font-size: 22px;
            line-height: 1;
            font-weight: 800;
        }

        .result-badge {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .result-normal {
            background: #eaf8ef;
            color: #15803d;
        }

        .result-attention {
            background: #fff7e6;
            color: #b45309;
        }

        .result-risk {
            background: #feecec;
            color: #b91c1c;
        }

        .result-default {
            background: #edf2f7;
            color: #475569;
        }

        .test-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .test-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 7px 12px;
            border-radius: 10px;
            background: #f3f6f9;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .test-action-btn:hover {
            background: #e8eef3;
            color: #111827;
        }

        .test-action-btn.primary {
            background: var(--hospital-primary);
            color: #ffffff;
        }

        .test-action-btn.primary:hover {
            background: var(--hospital-primary-dark);
            color: #ffffff;
        }


        /* =========================
           Пустое состояние
        ========================= */

        .test-results-empty {
            padding: 56px 24px 64px;
            text-align: center;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .test-results-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            border-radius: 24px;
            background: #eef8fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
        }

        .test-results-empty h3 {
            margin: 0 0 10px;
            color: var(--hospital-text);
            font-size: 24px;
            font-weight: 800;
        }

        .test-results-empty p {
            max-width: 560px;
            margin: 0 auto;
            color: var(--hospital-muted);
            font-size: 15px;
            line-height: 1.55;
        }


        /* =========================
           Alert
        ========================= */

        .alert {
            border: 0;
            border-radius: 16px;
            padding: 16px 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        }

        .alert-danger {
            background: #feecec;
            color: #991b1b;
        }

        .alert-success {
            background: #eaf8ef;
            color: #166534;
        }


        /* =========================
           Модалки создания тестов
        ========================= */

        .test-modal-ui .modal-content,
        .test-create-modal .modal-content {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .test-modal-ui .modal-header,
        .test-create-modal .modal-header {
            background: linear-gradient(135deg, #f8fafc 0%, #eef8fa 100%);
            border-bottom: 1px solid var(--hospital-border);
            padding: 22px 26px;
        }

        .test-modal-ui .modal-title,
        .test-create-modal .modal-title {
            margin: 0;
            color: var(--hospital-text);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .modal-subtitle {
            margin-top: 5px;
            color: var(--hospital-muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .test-modal-ui .modal-body,
        .test-create-modal .modal-body {
            padding: 26px;
            background: #ffffff;
        }

        .test-modal-ui .modal-footer,
        .test-create-modal .modal-footer {
            padding: 20px 26px;
            background: #f8fafc;
            border-top: 1px solid var(--hospital-border);
        }

        .test-form-section,
        .test-create-section {
            margin-top: 26px;
            padding-top: 26px;
            border-top: 1px solid #e8edf3;
        }

        .test-form-section:first-child,
        .test-create-section:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .test-form-section-title,
        .test-create-section-title {
            margin-bottom: 16px;
            color: var(--hospital-text);
            font-size: 17px;
            font-weight: 800;
        }

        .test-form-label,
        .test-create-modal .form-label,
        .test-modal-ui .form-label {
            margin-bottom: 7px;
            color: #374151;
            font-size: 14px;
            font-weight: 700;
        }

        .test-form-control,
        .test-modal-ui .form-control,
        .test-modal-ui .form-select,
        .test-create-modal .form-control,
        .test-create-modal .form-select {
            border-radius: 12px;
            border: 1px solid #d8e0e5;
        }

        .test-form-control:focus,
        .test-modal-ui .form-control:focus,
        .test-modal-ui .form-select:focus,
        .test-create-modal .form-control:focus,
        .test-create-modal .form-select:focus {
            border-color: var(--hospital-primary);
            box-shadow: 0 0 0 0.2rem rgba(4, 134, 150, 0.14);
        }

        .test-constructor-card,
        .test-create-dynamic-card {
            padding: 18px;
            margin-bottom: 16px;
            background: #ffffff;
            border: 1px solid var(--hospital-border);
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .test-constructor-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .test-constructor-card-title {
            color: var(--hospital-text);
            font-size: 16px;
            font-weight: 800;
        }

        .test-add-btn,
        .test-small-btn,
        .test-primary-btn,
        .test-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 12px;
            font-weight: 800;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .test-add-btn {
            width: 100%;
            min-height: 44px;
            margin-top: 4px;
            background: #eef8fa;
            color: var(--hospital-primary-dark);
        }

        .test-add-btn:hover {
            background: #dff4f7;
        }

        .test-small-btn {
            min-height: 36px;
            padding: 7px 12px;
            background: #eef2f7;
            color: #334155;
            font-size: 13px;
        }

        .test-small-btn:hover {
            background: #e2e8f0;
        }

        .test-small-btn.danger {
            background: #feecec;
            color: #b91c1c;
        }

        .test-small-btn.danger:hover {
            background: #ffdada;
        }

        .test-primary-btn {
            min-height: 42px;
            padding: 10px 18px;
            background: var(--hospital-primary);
            color: #ffffff;
        }

        .test-primary-btn:hover {
            background: var(--hospital-primary-dark);
        }

        .test-secondary-btn {
            min-height: 42px;
            padding: 10px 18px;
            background: #eef2f7;
            color: #334155;
        }

        .test-secondary-btn:hover {
            background: #e2e8f0;
        }


        /* =========================
           Конструктор картинок
        ========================= */

        .test-image-preview {
            min-height: 220px;
            border: 2px dashed #cbd5e1;
            border-radius: 18px;
            background: #f8fafc;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 16px;
            overflow: hidden;
        }

        .test-image-preview.has-image {
            padding: 0;
            border-style: solid;
            background: #ffffff;
        }

        .test-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }


        /* =========================
           Опросник
        ========================= */

        .question-options {
            margin-top: 16px;
        }

        .answer-option-row,
        .resource-band-row {
            display: grid;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .answer-option-row {
            grid-template-columns: minmax(0, 1fr) 120px 42px;
        }

        .resource-band-row {
            grid-template-columns: 90px 90px 1fr 1.4fr 42px;
        }


        /* =========================
           Универсальные карточки сортировки
        ========================= */

        .test-create-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .test-create-actions-title {
            margin: 0;
            color: var(--hospital-text);
            font-size: 17px;
            font-weight: 800;
        }

        .test-create-help {
            padding: 16px;
            border-radius: 16px;
            background: #eef8fa;
            color: #285b63;
            font-size: 14px;
            line-height: 1.5;
        }

        .card-sort-scale {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 12px;
            align-items: center;
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
        }

        .card-sort-scale-label {
            color: #374151;
            font-size: 14px;
            font-weight: 800;
        }

        .card-sort-scale-label:last-child {
            text-align: right;
        }

        .card-sort-scale-arrow {
            color: #64748b;
            font-weight: 900;
        }

        .card-sort-color-field {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-sort-color-preview {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            background: var(--hospital-primary);
            flex: 0 0 auto;
        }

        .card-sort-color-input {
            width: 62px;
            height: 42px;
            padding: 4px;
            cursor: pointer;
        }

        .test-create-remove-btn {
            min-width: 42px;
            height: 42px;
            padding: 0;
            border-radius: 12px;
            font-size: 22px;
            line-height: 1;
        }


        /* =========================
           Адаптив
        ========================= */

        @media (max-width: 1200px) {
            .test-results-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .test-results-page {
                padding: 0 14px 28px 14px;
            }

            .navbar.bg-body-hospital {
                margin-bottom: 18px;
                border-radius: 0 0 14px 14px;
            }

            .test-results-header,
            .test-results-panel-header {
                flex-direction: column;
                align-items: stretch;
            }

            .test-results-header {
                padding: 22px;
            }

            .test-results-title h1 {
                font-size: 28px;
            }

            .test-results-stats {
                grid-template-columns: 1fr;
            }

            .answer-option-row,
            .resource-band-row {
                grid-template-columns: 1fr;
            }

            .test-modal-ui .modal-body,
            .test-create-modal .modal-body {
                padding: 18px;
            }

            .test-create-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .card-sort-scale {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .card-sort-scale-label:last-child {
                text-align: center;
            }
        }

        .test-modal-ui .modal-content {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }

        .test-modal-ui .modal-header {
            background: linear-gradient(135deg, #f8fafc 0%, #eef6f8 100%);
            border-bottom: 1px solid #e5edf0;
            padding: 20px 24px;
        }

        .test-modal-ui .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }

        .test-modal-ui .modal-subtitle {
            margin-top: 4px;
            font-size: 14px;
            color: #64748b;
            line-height: 1.35;
        }

        .test-modal-ui .modal-body {
            padding: 24px;
            background: #ffffff;
        }

        .test-modal-ui .modal-footer {
            padding: 18px 24px;
            background: #f8fafc;
            border-top: 1px solid #e5edf0;
        }

        .test-form-section {
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #e5e7eb;
        }

        .test-form-section:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .test-form-section-title {
            margin-bottom: 14px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .test-form-label {
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .test-form-control {
            border-radius: 10px;
            border-color: #d8e0e5;
        }

        .test-form-control:focus {
            border-color: #048696;
            box-shadow: 0 0 0 0.2rem rgba(4, 134, 150, 0.15);
        }

        .test-constructor-card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            padding: 18px;
        }

        .test-constructor-card + .test-constructor-card {
            margin-top: 14px;
        }

        .test-constructor-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .test-constructor-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .test-add-btn,
        .test-small-btn,
        .test-primary-btn,
        .test-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px;
            border: 1px solid transparent;
            font-weight: 600;
            line-height: 1.2;
            text-decoration: none;
            transition: 0.15s ease-in-out;
        }

        .test-add-btn {
            margin-top: 14px;
            padding: 10px 14px;
            color: #048696;
            background: #eef8fa;
            border-color: rgba(4, 134, 150, 0.25);
        }

        .test-add-btn:hover {
            color: #036b78;
            background: #dff3f6;
        }

        .test-small-btn {
            padding: 8px 11px;
            min-height: 38px;
            color: #048696;
            background: #ffffff;
            border-color: #b7dce2;
        }

        .test-small-btn:hover {
            background: #eef8fa;
        }

        .test-small-btn.danger {
            color: #b91c1c;
            border-color: #fecaca;
            background: #fff7f7;
        }

        .test-small-btn.danger:hover {
            background: #fee2e2;
        }

        .test-primary-btn {
            padding: 10px 18px;
            color: #ffffff;
            background: #048696;
            border-color: #048696;
        }

        .test-primary-btn:hover {
            background: #036b78;
            border-color: #036b78;
        }

        .test-secondary-btn {
            padding: 10px 18px;
            color: #374151;
            background: #ffffff;
            border-color: #d1d5db;
        }

        .test-secondary-btn:hover {
            background: #f3f4f6;
        }

        .test-image-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            color: #64748b;
            background: #f8fafc;
            overflow: hidden;
            text-align: center;
        }

        .test-image-preview.has-image {
            border-style: solid;
            background: #ffffff;
        }

        .test-image-preview img {
            width: 100%;
            height: 100%;
            max-height: 280px;
            object-fit: contain;
            display: block;
        }

        .resource-band-row,
        .answer-option-row {
            display: grid;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .resource-band-row {
            grid-template-columns: 90px 90px 1fr 2fr auto;
        }

        .answer-option-row {
            grid-template-columns: 1fr 120px auto;
        }

        .question-options {
            margin-top: 16px;
        }

        .card-sort-help {
            padding: 14px 16px;
            border-radius: 12px;
            background: #eef8fa;
            color: #285b63;
            font-size: 14px;
            line-height: 1.45;
        }

        .card-sort-scale {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 12px;
            align-items: center;
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        .card-sort-scale-label {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
        }

        .card-sort-scale-label:last-child {
            text-align: right;
        }

        .card-sort-scale-arrow {
            color: #64748b;
            font-weight: 700;
        }

        .card-sort-color-field {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-sort-color-preview {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            background: #048696;
            flex: 0 0 auto;
        }

        .card-sort-color-input {
            width: 58px;
            height: 42px;
            padding: 4px;
            cursor: pointer;
        }

        .card-sort-type-hint {
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
        }

        @media (max-width: 992px) {
            .resource-band-row {
                grid-template-columns: 1fr 1fr;
            }

            .answer-option-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .test-modal-ui .modal-body {
                padding: 18px;
            }

            .test-constructor-card-header {
                align-items: stretch;
                flex-direction: column;
            }

            .card-sort-scale {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .card-sort-scale-label:last-child {
                text-align: center;
            }
        }

        .tests-top-navbar {
            min-height: 68px;
            padding: 0;
            background: linear-gradient(90deg, #048696 0%, #079aa9 100%);
            box-shadow: 0 12px 28px rgba(4, 134, 150, 0.18);
        }

        .tests-navbar-container {
            min-height: 68px;
            padding-left: 24px;
            padding-right: 24px;
            gap: 22px;
        }

        .tests-navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            flex-shrink: 0;
        }

        .tests-navbar-brand:hover {
            color: #fff;
        }

        .tests-navbar-brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.16);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .tests-navbar-brand-title {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .tests-navbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tests-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 8px 16px !important;
            border-radius: 11px;
            color: rgba(255, 255, 255, 0.92) !important;
            font-size: 14px;
            font-weight: 700;
        }

        .tests-nav-link:hover,
        .tests-nav-link.active,
        .tests-nav-link.show {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.15);
        }

        .tests-navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: none !important;
        }

        .tests-navbar-toggler .navbar-toggler-icon {
            filter: invert(1) grayscale(1) brightness(2);
        }

        .tests-dropdown-menu {
            border: 0;
            border-radius: 14px;
            padding: 8px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        }

        .tests-dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 9px;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .tests-dropdown-menu .dropdown-item:hover {
            background: #eef8fa;
            color: #047887;
        }

        @media (max-width: 991.98px) {
            .tests-navbar-container {
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .tests-navbar-nav {
                align-items: stretch;
                gap: 6px;
                padding-top: 12px;
            }

            .tests-nav-link {
                width: 100%;
            }
        }
    </style>
@endsection
@section('main')
    <nav class="navbar navbar-expand-lg tests-top-navbar">
        <div class="container-fluid tests-navbar-container">

            <a class="tests-navbar-brand" href="{{ route('doctors.tests.index') }}">
            <span class="tests-navbar-brand-icon">
                <i class="bi bi-clipboard2-pulse"></i>
            </span>

                <span class="tests-navbar-brand-title">
                Панель тестов
            </span>
            </a>

            <button class="navbar-toggler tests-navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#testsNavbarContent"
                    aria-controls="testsNavbarContent"
                    aria-expanded="false"
                    aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="testsNavbarContent">
                <ul class="navbar-nav tests-navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link tests-nav-link {{ is_route('doctors.tests.index') ? 'active'  : '' }}"
                           aria-current="page"
                           href="{{ route('doctors.tests.index') }}">
                            <i class="bi bi-list-check"></i>
                            <span>Просмотр тестов</span>
                        </a>
                    </li>

                    @if($isHeadmaster || !$doesHaveClinic)
                        <li class="nav-item dropdown">
                            <a class="nav-link tests-nav-link dropdown-toggle"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="bi bi-plus-circle"></i>
                                <span>Создать тест</span>
                            </a>

                            <ul class="dropdown-menu tests-dropdown-menu">
                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        Загрузить тест по сценарию
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#modalCreateImageTest">
                                        <i class="bi bi-images"></i>
                                        Тест с картинками
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#createCardSortTestModal">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                        Тест с сортировками
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#modalCreateAnswerTest">
                                        <i class="bi bi-ui-checks"></i>
                                        Тест с ответами
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link tests-nav-link
                            {{ request()->routeIs('doctors.tests.mine') ? 'active' : '' }}" href="{{ route('doctors.tests.mine') }}"
                            >
                                <i class="bi bi-person-check"></i>
                                <span>Мои тесты</span>
                            </a>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </nav>

    @yield('sub-main')
    {{-- Модальные окна создания тестов под таблицу tests + модель TestCard --}}

    {{-- Модалка: тест с картинками --}}
    <div class="modal fade test-modal-ui" id="modalCreateImageTest" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content"
                  method="post"
                  action="{{ route('doctors.tests.store-image-test') }}"
                  enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="type" value="image">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Создать тест с картинками</h5>
                        <div class="modal-subtitle">
                            Например: проективный тест, карточки с изображениями, тест по типу Роршаха.
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="test-form-section">
                        <div class="test-form-section-title">Основная информация</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="test-form-label">Название теста</label>
                                <input type="text"
                                       name="name"
                                       class="form-control test-form-control"
                                       placeholder="Например: Тест Роршаха"
                                       value="{{ old('name') }}"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Код теста</label>
                                <input type="text"
                                       name="code"
                                       class="form-control test-form-control"
                                       value="{{ old('code') }}"
                                       placeholder="RORSCHACH">
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Статус</label>
                                <select name="status" class="form-select test-form-control" required>
                                    <option value="официально">Официальный</option>
                                    <option value="часто">Часто используемый</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Длительность, минут</label>
                                <input type="number"
                                       name="estimated_minutes"
                                       class="form-control test-form-control"
                                       min="1"
                                       max="240"
                                       placeholder="Например: 40"
                                       value="{{ old('estimated_minutes') }}">
                            </div>

                            @if($isHeadmaster || !$doesHaveClinic)
                                <div class="col-md-9 d-flex align-items-end">
                                    <div>
                                        <input type="hidden" name="access_scope" value="personal">

                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="access_scope"
                                                   value="image"
                                                   id="imageTestAccessScope"
                                                @checked(old('access_scope') === 'image')>

                                            <label class="form-check-label" for="imageTestAccessScope">
                                                Сделать тест доступным врачам моей клиники
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="test-form-label">Описание</label>
                                <textarea name="description"
                                          class="form-control test-form-control"
                                          rows="2"
                                          placeholder="Краткое описание теста для врача">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="test-form-label">Инструкция пациенту</label>
                                <textarea name="instructions"
                                          class="form-control test-form-control"
                                          rows="2"
                                          placeholder="Посмотрите на изображение и опишите, что вы видите">{{ old('instructions') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="test-form-section">
                        <div class="test-form-section-title">Карточки с изображениями</div>

                        <div id="imageCardsContainer">
                            <div class="test-constructor-card image-card-item" data-index="0">
                                <div class="test-constructor-card-header">
                                    <div class="test-constructor-card-title">Карточка №1</div>

                                    <button type="button" class="test-small-btn danger js-remove-image-card">
                                        Удалить
                                    </button>
                                </div>

                                <input type="hidden" name="cards[0][sort]" value="1">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="test-image-preview js-image-preview">
                                            Предпросмотр изображения
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="test-form-label">Изображение</label>
                                            <input type="file"
                                                   name="cards[0][image]"
                                                   class="form-control test-form-control js-image-input"
                                                   accept="image/*"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="test-form-label">Название карточки</label>
                                            <input type="text"
                                                   name="cards[0][title]"
                                                   class="form-control test-form-control"
                                                   placeholder="Например: Карточка I">
                                        </div>

                                        <div>
                                            <label class="test-form-label">Вопрос к карточке</label>
                                            <input type="text"
                                                   name="cards[0][question]"
                                                   class="form-control test-form-control"
                                                   placeholder="Что это может быть?">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="test-add-btn" id="addImageCardBtn">
                            + Добавить карточку
                        </button>
                    </div>

                    <div class="test-form-section">
                        <div class="test-form-section-title">Настройки интерпретации</div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="test-form-label">Тип интерпретации</label>
                                <select name="interpretation_type" class="form-select test-form-control">
                                    <option value="manual">Ручная интерпретация врачом</option>
                                    <option value="template">По шаблону</option>
                                    <option value="scale">По шкалам</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="test-form-label">Тип ответа пациента</label>
                                <select name="answers_type" class="form-select test-form-control">
                                    <option value="FREE_TEXT">Свободный текст</option>
                                    <option value="SINGLE_CHOICE">Выбор одного варианта</option>
                                    <option value="MULTIPLE_CHOICE">Выбор нескольких вариантов</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="test-form-label">Показывать карточки</label>
                                <select name="display_mode" class="form-select test-form-control">
                                    <option value="SEQUENTIAL">Последовательно</option>
                                    <option value="ALL">Все сразу</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="test-form-label">Комментарий к интерпретации</label>
                                <textarea name="resource_profile[interpretation_text]"
                                          class="form-control test-form-control"
                                          rows="2"
                                          placeholder="Например: результат требует клинической оценки специалистом"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="test-secondary-btn" data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit" class="test-primary-btn">
                        Создать тест с картинками
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Модалка: тест-опросник --}}
    <div class="modal fade test-modal-ui" id="modalCreateAnswerTest" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content"
                  method="post"
                  action="{{ route('doctors.tests.store-questionnaire-test') }}">
                @csrf

                <input type="hidden" name="type" value="questionnaire">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Создать тест-опросник</h5>
                        <div class="modal-subtitle">
                            Например: шкала Зунга, шкалы самооценки, скрининговые анкеты.
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="test-form-section">
                        <div class="test-form-section-title">Основная информация</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="test-form-label">Название теста</label>
                                <input type="text"
                                       name="name"
                                       class="form-control test-form-control"
                                       placeholder="Например: Шкала депрессии Зунга"
                                       value="Шкала депрессии Зунга"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Код теста</label>
                                <input type="text"
                                       name="code"
                                       class="form-control test-form-control"
                                       placeholder="ZUNG_SDS"
                                       value="ZUNG_SDS">
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Статус</label>
                                <select name="status" class="form-select test-form-control" required>
                                    <option value="официально">Официальный</option>
                                    <option value="часто">Часто используемый</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Длительность, минут</label>
                                <input type="number"
                                       name="estimated_minutes"
                                       class="form-control test-form-control"
                                       min="1"
                                       max="240"
                                       value="5">
                            </div>

                            @if($isHeadmaster || !$doesHaveClinic)
                                <div class="col-md-9 d-flex align-items-end">
                                    <div>
                                        <input type="hidden" name="access_scope" value="personal">

                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="access_scope"
                                                   value="answer"
                                                   id="answerTestAccessScope"
                                                @checked(old('access_scope') === 'answer')>

                                            <label class="form-check-label" for="answerTestAccessScope">
                                                Сделать тест доступным врачам моей клиники
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="test-form-label">Описание</label>
                                <textarea name="description"
                                          class="form-control test-form-control"
                                          rows="2">Шкала самооценки депрессии Зунга — опросник для предварительной оценки выраженности депрессивных симптомов.</textarea>
                            </div>

                            <div class="col-12">
                                <label class="test-form-label">Инструкция пациенту</label>
                                <textarea name="instructions"
                                          class="form-control test-form-control"
                                          rows="2">Прочитайте каждое утверждение и выберите вариант ответа, который лучше всего описывает ваше состояние за последние несколько дней или последнюю неделю.</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="test-form-section">
                        <div class="test-form-section-title">Настройки подсчета</div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="test-form-label">Тип подсчета</label>
                                <select name="scoring_type" class="form-select test-form-control">
                                    <option value="sum">Сумма баллов</option>
                                    <option value="scale">По шкалам</option>
                                    <option value="manual">Ручная оценка</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Минимальный балл</label>
                                <input type="number"
                                       name="min_scoring"
                                       class="form-control test-form-control"
                                       value="20">
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Максимальный балл</label>
                                <input type="number"
                                       name="max_scoring"
                                       class="form-control test-form-control"
                                       value="80">
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Порог внимания</label>
                                <input type="number"
                                       name="attention_score"
                                       class="form-control test-form-control"
                                       value="50">
                            </div>

                            <div class="col-12">
                                <label class="test-form-label">Описание интерпретации</label>
                                <textarea name="description_interpretation"
                                          class="form-control test-form-control"
                                          rows="3">Суммарный балл рассчитывается по 20 вопросам с учетом обратного подсчета для положительно сформулированных утверждений.</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="test-form-section">
                        <div class="test-form-section-title">Профиль ресурса</div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="test-form-label">Название профиля</label>
                                <input type="text"
                                       name="resource_profile[title]"
                                       class="form-control test-form-control"
                                       value="Ресурс">
                            </div>

                            <div class="col-md-8">
                                <label class="test-form-label">Описание профиля</label>
                                <input type="text"
                                       name="resource_profile[about]"
                                       class="form-control test-form-control"
                                       value="Оценка возможного истощения ресурса на фоне депрессивных симптомов (алгоритм).">
                            </div>
                        </div>

                        <div class="mt-3" id="resourceBandsContainer">
                            <label class="test-form-label">Диапазоны интерпретации</label>

                            <div class="resource-band-row resource-band-item" data-index="0">
                                <input type="number" name="resource_profile[bands][0][min]"
                                       class="form-control test-form-control" value="0" placeholder="min">
                                <input type="number" name="resource_profile[bands][0][max]"
                                       class="form-control test-form-control" value="4" placeholder="max">
                                <input type="text" name="resource_profile[bands][0][label]"
                                       class="form-control test-form-control" value="Ресурс сохранён"
                                       placeholder="Название">
                                <input type="text" name="resource_profile[bands][0][text]"
                                       class="form-control test-form-control"
                                       value="Признаков выраженного истощения по этому тесту не видно."
                                       placeholder="Описание">
                                <button type="button" class="test-small-btn danger js-remove-band">×</button>
                            </div>

                            <div class="resource-band-row resource-band-item" data-index="1">
                                <input type="number" name="resource_profile[bands][1][min]"
                                       class="form-control test-form-control" value="5" placeholder="min">
                                <input type="number" name="resource_profile[bands][1][max]"
                                       class="form-control test-form-control" value="9" placeholder="max">
                                <input type="text" name="resource_profile[bands][1][label]"
                                       class="form-control test-form-control" value="Ресурс снижен"
                                       placeholder="Название">
                                <input type="text" name="resource_profile[bands][1][text]"
                                       class="form-control test-form-control"
                                       value="Возможна умеренная усталость/снижение тонуса. Полезны сон и щадящий режим."
                                       placeholder="Описание">
                                <button type="button" class="test-small-btn danger js-remove-band">×</button>
                            </div>

                            <div class="resource-band-row resource-band-item" data-index="2">
                                <input type="number" name="resource_profile[bands][2][min]"
                                       class="form-control test-form-control" value="10" placeholder="min">
                                <input type="number" name="resource_profile[bands][2][max]"
                                       class="form-control test-form-control" value="14" placeholder="max">
                                <input type="text" name="resource_profile[bands][2][label]"
                                       class="form-control test-form-control" value="Ресурс истощается"
                                       placeholder="Название">
                                <input type="text" name="resource_profile[bands][2][text]"
                                       class="form-control test-form-control"
                                       value="Есть признаки заметного снижения ресурса. Желательна поддержка и обсуждение с врачом."
                                       placeholder="Описание">
                                <button type="button" class="test-small-btn danger js-remove-band">×</button>
                            </div>

                            <div class="resource-band-row resource-band-item" data-index="3">
                                <input type="number" name="resource_profile[bands][3][min]"
                                       class="form-control test-form-control" value="15" placeholder="min">
                                <input type="number" name="resource_profile[bands][3][max]"
                                       class="form-control test-form-control" value="27" placeholder="max">
                                <input type="text" name="resource_profile[bands][3][label]"
                                       class="form-control test-form-control" value="Низкий ресурс"
                                       placeholder="Название">
                                <input type="text" name="resource_profile[bands][3][text]"
                                       class="form-control test-form-control"
                                       value="Высокая нагрузка на ресурс. Рекомендуется очная оценка и план помощи."
                                       placeholder="Описание">
                                <button type="button" class="test-small-btn danger js-remove-band">×</button>
                            </div>
                        </div>

                        <button type="button" class="test-small-btn mt-2" id="addResourceBandBtn">
                            + Добавить диапазон
                        </button>
                    </div>

                    <div class="test-form-section">
                        <div class="test-form-section-title">Вопросы и варианты ответов</div>

                        <div id="answerQuestionsContainer">
                            <div class="test-constructor-card answer-question-item" data-index="0">
                                <div class="test-constructor-card-header">
                                    <div class="test-constructor-card-title">Вопрос №1</div>

                                    <button type="button" class="test-small-btn danger js-remove-question">
                                        Удалить
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="test-form-label">Название вопроса</label>
                                    <input type="text"
                                           name="resource_profile[questions][0][title]"
                                           class="form-control test-form-control"
                                           value="Настроение"
                                           placeholder="Например: Настроение">
                                </div>

                                <div class="mb-3">
                                    <label class="test-form-label">Текст вопроса</label>
                                    <textarea name="resource_profile[questions][0][text]"
                                              class="form-control test-form-control"
                                              rows="2"
                                              required>Я чувствую подавленность или уныние.</textarea>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="test-form-label">Шкала / группа</label>
                                        <input type="text"
                                               name="resource_profile[questions][0][scale]"
                                               class="form-control test-form-control"
                                               value="депрессия"
                                               placeholder="Например: депрессия">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="test-form-label">Тип ответа</label>
                                        <select name="resource_profile[questions][0][answer_type]"
                                                class="form-select test-form-control">
                                            <option value="single">Один вариант</option>
                                            <option value="multiple">Несколько вариантов</option>
                                            <option value="text">Свободный текст</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 d-flex align-items-end">
                                        <div>
                                            <input type="hidden"
                                                   name="resource_profile[questions][0][reverse]"
                                                   value="0">

                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="resource_profile[questions][0][reverse]"
                                                       value="1"
                                                       id="questionReverse0">

                                                <label class="form-check-label" for="questionReverse0">
                                                    Обратный подсчет баллов
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="question-options">
                                    <label class="test-form-label">Варианты ответов</label>

                                    <div class="answer-option-row">
                                        <input type="text"
                                               name="resource_profile[questions][0][options][0][text]"
                                               class="form-control test-form-control"
                                               value="Редко или никогда">

                                        <input type="number"
                                               name="resource_profile[questions][0][options][0][score]"
                                               class="form-control test-form-control"
                                               value="1"
                                               min="0">

                                        <button type="button" class="test-small-btn danger js-remove-option">×</button>
                                    </div>

                                    <div class="answer-option-row">
                                        <input type="text"
                                               name="resource_profile[questions][0][options][1][text]"
                                               class="form-control test-form-control"
                                               value="Иногда">

                                        <input type="number"
                                               name="resource_profile[questions][0][options][1][score]"
                                               class="form-control test-form-control"
                                               value="2"
                                               min="0">

                                        <button type="button" class="test-small-btn danger js-remove-option">×</button>
                                    </div>

                                    <div class="answer-option-row">
                                        <input type="text"
                                               name="resource_profile[questions][0][options][2][text]"
                                               class="form-control test-form-control"
                                               value="Часто">

                                        <input type="number"
                                               name="resource_profile[questions][0][options][2][score]"
                                               class="form-control test-form-control"
                                               value="3"
                                               min="0">

                                        <button type="button" class="test-small-btn danger js-remove-option">×</button>
                                    </div>

                                    <div class="answer-option-row">
                                        <input type="text"
                                               name="resource_profile[questions][0][options][3][text]"
                                               class="form-control test-form-control"
                                               value="Почти всегда или постоянно">

                                        <input type="number"
                                               name="resource_profile[questions][0][options][3][score]"
                                               class="form-control test-form-control"
                                               value="4"
                                               min="0">

                                        <button type="button" class="test-small-btn danger js-remove-option">×</button>
                                    </div>
                                </div>

                                <button type="button" class="test-small-btn js-add-option mt-2">
                                    + Добавить вариант ответа
                                </button>
                            </div>
                        </div>

                        <button type="button" class="test-add-btn" id="addQuestionBtn">
                            + Добавить вопрос
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="test-secondary-btn" data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit" class="test-primary-btn">
                        Создать опросник
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Модалка: универсальный тест с сортировкой карточек --}}
    <div class="modal fade test-modal-ui"
         id="createCardSortTestModal"
         tabindex="-1"
         aria-labelledby="createCardSortTestModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content"
                  method="POST"
                  action="{{ route('doctors.tests.store-card-sort-test') }}"
                  enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="type" value="card_sort">
                <input type="hidden" name="sort_direction" value="left_to_right">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="createCardSortTestModalLabel">
                            Создать тест с сортировкой карточек
                        </h5>

                        <div class="modal-subtitle">
                            Универсальный формат: карточки могут быть цветовыми, текстовыми или с изображениями.
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="test-form-section">
                        <div class="test-form-section-title">Основная информация</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="test-form-label">Название теста</label>
                                <input type="text"
                                       name="name"
                                       class="form-control test-form-control"
                                       placeholder="Например: Сортировка эмоциональных карточек"
                                       value="{{ old('name') }}"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Код теста</label>
                                <input type="text"
                                       name="code"
                                       class="form-control test-form-control"
                                       placeholder="CARD_SORT"
                                       value="{{ old('code') }}"
                                       required>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Статус</label>
                                <select name="status" class="form-select test-form-control" required>
                                    <option value="официально">Официальный</option>
                                    <option value="часто">Часто используемый</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="test-form-label">Длительность, минут</label>
                                <input type="number"
                                       name="duration"
                                       class="form-control test-form-control"
                                       min="1"
                                       max="240"
                                       placeholder="Например: 10"
                                       value="{{ old('duration') }}">
                            </div>

                            @if($isHeadmaster || !$doesHaveClinic)
                                <div class="col-md-9 d-flex align-items-end">
                                    <div>
                                        <input type="hidden" name="access_scope" value="personal">

                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="access_scope"
                                                   value="card"
                                                   id="cardTestAccessScope"
                                                @checked(old('access_scope') === 'card')>

                                            <label class="form-check-label" for="cardTestAccessScope">
                                                Сделать тест доступным врачам моей клиники
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="test-form-label">Описание</label>
                                <textarea name="description"
                                          class="form-control test-form-control"
                                          rows="2"
                                          placeholder="Краткое описание теста для врача">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="test-form-label">Инструкция пациенту</label>
                                <textarea name="instructions"
                                          class="form-control test-form-control"
                                          rows="2"
                                          placeholder="Например: расположите карточки от наиболее подходящей к наименее подходящей">{{ old('instructions', 'Расположите карточки слева направо согласно предложенной шкале.') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="test-form-section">
                        <div class="test-form-section-title">Настройки сортировки</div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="test-form-label">Название профиля / шкалы</label>
                                <input type="text"
                                       name="resource_title"
                                       class="form-control test-form-control"
                                       placeholder="Например: Предпочтения / выраженность"
                                       value="{{ old('resource_title') }}">
                            </div>

                            <div class="col-md-8">
                                <label class="test-form-label">Описание профиля / шкалы</label>
                                <input type="text"
                                       name="resource_about"
                                       class="form-control test-form-control"
                                       placeholder="Например: пациент сортирует карточки по субъективной значимости"
                                       value="{{ old('resource_about') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="test-form-label">Левая граница шкалы</label>
                                <input type="text"
                                       name="left_label"
                                       id="cardSortLeftLabel"
                                       class="form-control test-form-control"
                                       value="{{ old('left_label', 'Больше подходит / нравится') }}"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="test-form-label">Правая граница шкалы</label>
                                <input type="text"
                                       name="right_label"
                                       id="cardSortRightLabel"
                                       class="form-control test-form-control"
                                       value="{{ old('right_label', 'Меньше подходит / не нравится') }}"
                                       required>
                            </div>
                        </div>

                        <div class="card-sort-help mt-3">
                            Это не отдельная форма под тест Люшера, а универсальная сортировка карточек.
                            Врач сам задаёт смысл шкалы и наполнение карточек.

                            <div class="card-sort-scale">
                                <div class="card-sort-scale-label" id="cardSortLeftPreview">
                                    Больше подходит / нравится
                                </div>

                                <div class="card-sort-scale-arrow">→</div>

                                <div class="card-sort-scale-label" id="cardSortRightPreview">
                                    Меньше подходит / не нравится
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="test-form-section">
                        <div class="test-constructor-card-header">
                            <div>
                                <div class="test-form-section-title mb-1">Карточки для сортировки</div>
                                <div class="card-sort-type-hint">
                                    Для каждой карточки выберите тип содержимого: текст, цвет или изображение.
                                </div>
                            </div>

                            <button type="button" class="test-small-btn" id="addCardSortCard">
                                + Добавить карточку
                            </button>
                        </div>

                        <div id="cardSortCardsWrapper">
                            <div class="test-constructor-card card-sort-item" data-index="0">
                                <div class="test-constructor-card-header">
                                    <div class="test-constructor-card-title">Карточка №1</div>

                                    <button type="button" class="test-small-btn danger remove-card-sort-card">
                                        Удалить
                                    </button>
                                </div>

                                <input type="hidden" name="cards[0][sort]" value="1">

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="test-form-label">Название карточки</label>
                                        <input type="text"
                                               name="cards[0][title]"
                                               class="form-control test-form-control"
                                               placeholder="Например: Карточка 1"
                                               required>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="test-form-label">Код</label>
                                        <input type="text"
                                               name="cards[0][code]"
                                               class="form-control test-form-control"
                                               placeholder="card_1">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="test-form-label">Тип карточки</label>
                                        <select name="cards[0][type]"
                                                class="form-select test-form-control card-sort-type-select"
                                                required>
                                            <option value="text" selected>Текст</option>
                                            <option value="color">Цвет</option>
                                            <option value="image">Изображение</option>
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="card-sort-content-field card-sort-content-text">
                                            <label class="test-form-label">Текст карточки</label>
                                            <input type="text"
                                                   name="cards[0][text]"
                                                   class="form-control test-form-control card-sort-required-field"
                                                   placeholder="Текст, который увидит пациент"
                                                   required>
                                        </div>

                                        <div class="card-sort-content-field card-sort-content-color d-none">
                                            <label class="test-form-label">Цвет карточки</label>

                                            <div class="card-sort-color-field">
                                                <span class="card-sort-color-preview"
                                                      style="background: #048696"></span>

                                                <input type="color"
                                                       name="cards[0][color]"
                                                       class="form-control form-control-color card-sort-color-input"
                                                       value="#048696">
                                            </div>
                                        </div>

                                        <div class="card-sort-content-field card-sort-content-image d-none">
                                            <label class="test-form-label">Изображение карточки</label>
                                            <input type="file"
                                                   name="cards[0][image]"
                                                   class="form-control test-form-control card-sort-image-input"
                                                   accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="test-constructor-card card-sort-item" data-index="1">
                                <div class="test-constructor-card-header">
                                    <div class="test-constructor-card-title">Карточка №2</div>

                                    <button type="button" class="test-small-btn danger remove-card-sort-card">
                                        Удалить
                                    </button>
                                </div>

                                <input type="hidden" name="cards[1][sort]" value="2">

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="test-form-label">Название карточки</label>
                                        <input type="text"
                                               name="cards[1][title]"
                                               class="form-control test-form-control"
                                               placeholder="Например: Карточка 2"
                                               required>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="test-form-label">Код</label>
                                        <input type="text"
                                               name="cards[1][code]"
                                               class="form-control test-form-control"
                                               placeholder="card_2">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="test-form-label">Тип карточки</label>
                                        <select name="cards[1][type]"
                                                class="form-select test-form-control card-sort-type-select"
                                                required>
                                            <option value="text" selected>Текст</option>
                                            <option value="color">Цвет</option>
                                            <option value="image">Изображение</option>
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="card-sort-content-field card-sort-content-text">
                                            <label class="test-form-label">Текст карточки</label>
                                            <input type="text"
                                                   name="cards[1][text]"
                                                   class="form-control test-form-control card-sort-required-field"
                                                   placeholder="Текст, который увидит пациент"
                                                   required>
                                        </div>

                                        <div class="card-sort-content-field card-sort-content-color d-none">
                                            <label class="test-form-label">Цвет карточки</label>

                                            <div class="card-sort-color-field">
                                                <span class="card-sort-color-preview"
                                                      style="background: #048696"></span>

                                                <input type="color"
                                                       name="cards[1][color]"
                                                       class="form-control form-control-color card-sort-color-input"
                                                       value="#048696">
                                            </div>
                                        </div>

                                        <div class="card-sort-content-field card-sort-content-image d-none">
                                            <label class="test-form-label">Изображение карточки</label>
                                            <input type="file"
                                                   name="cards[1][image]"
                                                   class="form-control test-form-control card-sort-image-input"
                                                   accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="test-add-btn" id="addCardSortCardBottom">
                            + Добавить карточку
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="test-secondary-btn" data-bs-dismiss="modal">
                        Отмена
                    </button>

                    <button type="submit" class="test-primary-btn">
                        Создать тест с сортировкой
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Модалки открываются штатно через data-bs-toggle="modal" в ссылках navbar.

            const imageCardsContainer = document.getElementById('imageCardsContainer');
            const addImageCardBtn = document.getElementById('addImageCardBtn');

            function refreshImageCardNames() {
                if (!imageCardsContainer) return;

                imageCardsContainer.querySelectorAll('.image-card-item').forEach(function (card, index) {
                    card.dataset.index = index;

                    const title = card.querySelector('.test-constructor-card-title');

                    if (title) {
                        title.textContent = 'Карточка №' + (index + 1);
                    }

                    card.querySelectorAll('input, textarea, select').forEach(function (field) {
                        if (field.name) {
                            field.name = field.name.replace(/cards\[\d+]/, 'cards[' + index + ']');
                        }
                    });

                    const sort = card.querySelector('input[name="cards[' + index + '][sort]"]');

                    if (sort) {
                        sort.value = index + 1;
                    }
                });
            }

            function bindImagePreview(card) {
                const input = card.querySelector('.js-image-input');
                const preview = card.querySelector('.js-image-preview');

                if (!input || !preview) return;

                input.addEventListener('change', function () {
                    const file = input.files && input.files[0];

                    if (!file) {
                        preview.classList.remove('has-image');
                        preview.innerHTML = 'Предпросмотр изображения';
                        return;
                    }

                    const url = URL.createObjectURL(file);

                    preview.classList.add('has-image');
                    preview.innerHTML = '<img src="' + url + '" alt="Предпросмотр">';
                });
            }

            if (imageCardsContainer) {
                imageCardsContainer.querySelectorAll('.image-card-item').forEach(bindImagePreview);

                imageCardsContainer.addEventListener('click', function (event) {
                    if (event.target.classList.contains('js-remove-image-card')) {
                        const cards = imageCardsContainer.querySelectorAll('.image-card-item');

                        if (cards.length <= 1) return;

                        event.target.closest('.image-card-item').remove();
                        refreshImageCardNames();
                    }
                });
            }

            if (addImageCardBtn && imageCardsContainer) {
                addImageCardBtn.addEventListener('click', function () {
                    const index = imageCardsContainer.querySelectorAll('.image-card-item').length;

                    const card = document.createElement('div');
                    card.className = 'test-constructor-card image-card-item';
                    card.dataset.index = index;

                    card.innerHTML = `
                    <div class="test-constructor-card-header">
                        <div class="test-constructor-card-title">Карточка №${index + 1}</div>

                        <button type="button" class="test-small-btn danger js-remove-image-card">
                            Удалить
                        </button>
                    </div>

                    <input type="hidden" name="cards[${index}][sort]" value="${index + 1}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="test-image-preview js-image-preview">
                                Предпросмотр изображения
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="test-form-label">Изображение</label>
                                <input type="file"
                                       name="cards[${index}][image]"
                                       class="form-control test-form-control js-image-input"
                                       accept="image/*"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="test-form-label">Название карточки</label>
                                <input type="text"
                                       name="cards[${index}][title]"
                                       class="form-control test-form-control"
                                       placeholder="Например: Карточка ${index + 1}">
                            </div>

                            <div>
                                <label class="test-form-label">Вопрос к карточке</label>
                                <input type="text"
                                       name="cards[${index}][question]"
                                       class="form-control test-form-control"
                                       placeholder="Что это может быть?">
                            </div>
                        </div>
                    </div>
                `;

                    imageCardsContainer.appendChild(card);
                    bindImagePreview(card);
                });
            }

            const answerQuestionsContainer = document.getElementById('answerQuestionsContainer');
            const addQuestionBtn = document.getElementById('addQuestionBtn');

            function getOptionRow(questionIndex, optionIndex, text = '', score = '') {
                return `
                <div class="answer-option-row">
                    <input type="text"
                           name="resource_profile[questions][${questionIndex}][options][${optionIndex}][text]"
                           class="form-control test-form-control"
                           value="${text}">

                    <input type="number"
                           name="resource_profile[questions][${questionIndex}][options][${optionIndex}][score]"
                           class="form-control test-form-control"
                           value="${score}"
                           min="0">

                    <button type="button" class="test-small-btn danger js-remove-option">×</button>
                </div>
            `;
            }

            function refreshQuestionNames() {
                if (!answerQuestionsContainer) return;

                answerQuestionsContainer.querySelectorAll('.answer-question-item').forEach(function (question, questionIndex) {
                    question.dataset.index = questionIndex;

                    const title = question.querySelector('.test-constructor-card-title');

                    if (title) {
                        title.textContent = 'Вопрос №' + (questionIndex + 1);
                    }

                    const reverseCheckbox = question.querySelector('.form-check-input[type="checkbox"][name*="[reverse]"]');
                    const reverseLabel = question.querySelector('.form-check-label');

                    if (reverseCheckbox) {
                        reverseCheckbox.id = 'questionReverse' + questionIndex;
                    }

                    if (reverseLabel) {
                        reverseLabel.setAttribute('for', 'questionReverse' + questionIndex);
                    }

                    question.querySelectorAll('input, textarea, select').forEach(function (field) {
                        if (field.name) {
                            field.name = field.name.replace(
                                /resource_profile\[questions]\[\d+]/,
                                'resource_profile[questions][' + questionIndex + ']'
                            );
                        }
                    });

                    question.querySelectorAll('.answer-option-row').forEach(function (row, optionIndex) {
                        row.querySelectorAll('input').forEach(function (field) {
                            if (field.name) {
                                field.name = field.name.replace(
                                    /\[options]\[\d+]/,
                                    '[options][' + optionIndex + ']'
                                );
                            }
                        });
                    });
                });
            }

            function addQuestion() {
                if (!answerQuestionsContainer) return;

                const index = answerQuestionsContainer.querySelectorAll('.answer-question-item').length;

                const question = document.createElement('div');
                question.className = 'test-constructor-card answer-question-item';
                question.dataset.index = index;

                question.innerHTML = `
                <div class="test-constructor-card-header">
                    <div class="test-constructor-card-title">Вопрос №${index + 1}</div>

                    <button type="button" class="test-small-btn danger js-remove-question">
                        Удалить
                    </button>
                </div>

                <div class="mb-3">
                    <label class="test-form-label">Название вопроса</label>
                    <input type="text"
                           name="resource_profile[questions][${index}][title]"
                           class="form-control test-form-control"
                           placeholder="Например: Сон">
                </div>

                <div class="mb-3">
                    <label class="test-form-label">Текст вопроса</label>
                    <textarea name="resource_profile[questions][${index}][text]"
                              class="form-control test-form-control"
                              rows="2"
                              placeholder="Введите формулировку вопроса"
                              required></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="test-form-label">Шкала / группа</label>
                        <input type="text"
                               name="resource_profile[questions][${index}][scale]"
                               class="form-control test-form-control"
                               placeholder="Например: депрессия">
                    </div>

                    <div class="col-md-4">
                        <label class="test-form-label">Тип ответа</label>
                        <select name="resource_profile[questions][${index}][answer_type]" class="form-select test-form-control">
                            <option value="single">Один вариант</option>
                            <option value="multiple">Несколько вариантов</option>
                            <option value="text">Свободный текст</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div>
                            <input type="hidden"
                                   name="resource_profile[questions][${index}][reverse]"
                                   value="0">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="resource_profile[questions][${index}][reverse]"
                                       value="1"
                                       id="questionReverse${index}">

                                <label class="form-check-label" for="questionReverse${index}">
                                    Обратный подсчет баллов
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="question-options">
                    <label class="test-form-label">Варианты ответов</label>

                    ${getOptionRow(index, 0, 'Редко или никогда', 1)}
                    ${getOptionRow(index, 1, 'Иногда', 2)}
                    ${getOptionRow(index, 2, 'Часто', 3)}
                    ${getOptionRow(index, 3, 'Почти всегда или постоянно', 4)}
                </div>

                <button type="button" class="test-small-btn js-add-option mt-2">
                    + Добавить вариант ответа
                </button>
            `;

                answerQuestionsContainer.appendChild(question);
            }

            if (addQuestionBtn) {
                addQuestionBtn.addEventListener('click', addQuestion);
            }

            if (answerQuestionsContainer) {
                answerQuestionsContainer.addEventListener('click', function (event) {
                    if (event.target.classList.contains('js-remove-question')) {
                        const questions = answerQuestionsContainer.querySelectorAll('.answer-question-item');

                        if (questions.length <= 1) return;

                        event.target.closest('.answer-question-item').remove();
                        refreshQuestionNames();
                    }

                    if (event.target.classList.contains('js-remove-option')) {
                        const question = event.target.closest('.answer-question-item');
                        const options = question.querySelectorAll('.answer-option-row');

                        if (options.length <= 2) return;

                        event.target.closest('.answer-option-row').remove();
                        refreshQuestionNames();
                    }

                    if (event.target.classList.contains('js-add-option')) {
                        const question = event.target.closest('.answer-question-item');
                        const questionIndex = question.dataset.index;
                        const optionsContainer = question.querySelector('.question-options');
                        const optionIndex = optionsContainer.querySelectorAll('.answer-option-row').length;

                        optionsContainer.insertAdjacentHTML(
                            'beforeend',
                            getOptionRow(questionIndex, optionIndex)
                        );

                        refreshQuestionNames();
                    }
                });
            }


            const cardSortCardsWrapper = document.getElementById('cardSortCardsWrapper');
            const addCardSortCardBtn = document.getElementById('addCardSortCard');
            const addCardSortCardBottomBtn = document.getElementById('addCardSortCardBottom');
            const cardSortLeftLabel = document.getElementById('cardSortLeftLabel');
            const cardSortRightLabel = document.getElementById('cardSortRightLabel');
            const cardSortLeftPreview = document.getElementById('cardSortLeftPreview');
            const cardSortRightPreview = document.getElementById('cardSortRightPreview');

            function refreshCardSortNames() {
                if (!cardSortCardsWrapper) return;

                cardSortCardsWrapper.querySelectorAll('.card-sort-item').forEach(function (card, index) {
                    card.dataset.index = index;

                    const title = card.querySelector('.test-constructor-card-title');

                    if (title) {
                        title.textContent = 'Карточка №' + (index + 1);
                    }

                    card.querySelectorAll('input, textarea, select').forEach(function (field) {
                        if (field.name) {
                            field.name = field.name.replace(/cards\[\d+]/, 'cards[' + index + ']');
                        }
                    });

                    const sort = card.querySelector('input[name="cards[' + index + '][sort]"]');

                    if (sort) {
                        sort.value = index + 1;
                    }
                });
            }

            function updateCardSortRequiredFields(card) {
                const typeSelect = card.querySelector('.card-sort-type-select');
                const selectedType = typeSelect ? typeSelect.value : 'text';

                card.querySelectorAll('.card-sort-content-field').forEach(function (field) {
                    field.classList.add('d-none');
                });

                card.querySelectorAll('.card-sort-content-field input').forEach(function (input) {
                    input.required = false;
                });

                const activeField = card.querySelector('.card-sort-content-' + selectedType);

                if (activeField) {
                    activeField.classList.remove('d-none');

                    const activeInput = activeField.querySelector('input');

                    if (activeInput) {
                        activeInput.required = true;
                    }
                }
            }

            function updateCardSortColorPreview(input) {
                const preview = input
                    .closest('.card-sort-color-field')
                    ?.querySelector('.card-sort-color-preview');

                if (preview) {
                    preview.style.background = input.value;
                }
            }

            function getCardSortCardHtml(index) {
                return `
                <div class="test-constructor-card card-sort-item" data-index="${index}">
                    <div class="test-constructor-card-header">
                        <div class="test-constructor-card-title">Карточка №${index + 1}</div>

                        <button type="button" class="test-small-btn danger remove-card-sort-card">
                            Удалить
                        </button>
                    </div>

                    <input type="hidden" name="cards[${index}][sort]" value="${index + 1}">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="test-form-label">Название карточки</label>
                            <input type="text"
                                   name="cards[${index}][title]"
                                   class="form-control test-form-control"
                                   placeholder="Например: Карточка ${index + 1}"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <label class="test-form-label">Код</label>
                            <input type="text"
                                   name="cards[${index}][code]"
                                   class="form-control test-form-control"
                                   placeholder="card_${index + 1}">
                        </div>

                        <div class="col-md-2">
                            <label class="test-form-label">Тип карточки</label>
                            <select name="cards[${index}][type]"
                                    class="form-select test-form-control card-sort-type-select"
                                    required>
                                <option value="text" selected>Текст</option>
                                <option value="color">Цвет</option>
                                <option value="image">Изображение</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <div class="card-sort-content-field card-sort-content-text">
                                <label class="test-form-label">Текст карточки</label>
                                <input type="text"
                                       name="cards[${index}][text]"
                                       class="form-control test-form-control"
                                       placeholder="Текст, который увидит пациент"
                                       required>
                            </div>

                            <div class="card-sort-content-field card-sort-content-color d-none">
                                <label class="test-form-label">Цвет карточки</label>

                                <div class="card-sort-color-field">
                                    <span class="card-sort-color-preview" style="background: #048696"></span>

                                    <input type="color"
                                           name="cards[${index}][color]"
                                           class="form-control form-control-color card-sort-color-input"
                                           value="#048696">
                                </div>
                            </div>

                            <div class="card-sort-content-field card-sort-content-image d-none">
                                <label class="test-form-label">Изображение карточки</label>
                                <input type="file"
                                       name="cards[${index}][image]"
                                       class="form-control test-form-control card-sort-image-input"
                                       accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }

            function addCardSortCard() {
                if (!cardSortCardsWrapper) return;

                const index = cardSortCardsWrapper.querySelectorAll('.card-sort-item').length;

                cardSortCardsWrapper.insertAdjacentHTML('beforeend', getCardSortCardHtml(index));

                const addedCard = cardSortCardsWrapper.querySelector('.card-sort-item:last-child');

                if (addedCard) {
                    updateCardSortRequiredFields(addedCard);
                }
            }

            function refreshCardSortScalePreview() {
                if (cardSortLeftPreview && cardSortLeftLabel) {
                    cardSortLeftPreview.textContent = cardSortLeftLabel.value || 'Левая граница';
                }

                if (cardSortRightPreview && cardSortRightLabel) {
                    cardSortRightPreview.textContent = cardSortRightLabel.value || 'Правая граница';
                }
            }

            if (addCardSortCardBtn) {
                addCardSortCardBtn.addEventListener('click', addCardSortCard);
            }

            if (addCardSortCardBottomBtn) {
                addCardSortCardBottomBtn.addEventListener('click', addCardSortCard);
            }

            if (cardSortCardsWrapper) {
                cardSortCardsWrapper.querySelectorAll('.card-sort-item').forEach(updateCardSortRequiredFields);

                cardSortCardsWrapper.addEventListener('change', function (event) {
                    if (event.target.classList.contains('card-sort-type-select')) {
                        updateCardSortRequiredFields(event.target.closest('.card-sort-item'));
                    }
                });

                cardSortCardsWrapper.addEventListener('input', function (event) {
                    if (event.target.classList.contains('card-sort-color-input')) {
                        updateCardSortColorPreview(event.target);
                    }
                });

                cardSortCardsWrapper.addEventListener('click', function (event) {
                    if (!event.target.classList.contains('remove-card-sort-card')) {
                        return;
                    }

                    const cards = cardSortCardsWrapper.querySelectorAll('.card-sort-item');

                    if (cards.length <= 2) {
                        alert('В тесте с сортировкой должно быть минимум 2 карточки.');
                        return;
                    }

                    event.target.closest('.card-sort-item').remove();
                    refreshCardSortNames();
                });
            }

            if (cardSortLeftLabel) {
                cardSortLeftLabel.addEventListener('input', refreshCardSortScalePreview);
            }

            if (cardSortRightLabel) {
                cardSortRightLabel.addEventListener('input', refreshCardSortScalePreview);
            }

            refreshCardSortScalePreview();

            const resourceBandsContainer = document.getElementById('resourceBandsContainer');
            const addResourceBandBtn = document.getElementById('addResourceBandBtn');

            function refreshBandNames() {
                if (!resourceBandsContainer) return;

                resourceBandsContainer.querySelectorAll('.resource-band-item').forEach(function (row, index) {
                    row.dataset.index = index;

                    row.querySelectorAll('input').forEach(function (field) {
                        if (field.name) {
                            field.name = field.name.replace(
                                /resource_profile\[bands]\[\d+]/,
                                'resource_profile[bands][' + index + ']'
                            );
                        }
                    });
                });
            }

            function getBandRow(index) {
                return `
                <div class="resource-band-row resource-band-item" data-index="${index}">
                    <input type="number" name="resource_profile[bands][${index}][min]" class="form-control test-form-control" placeholder="min">
                    <input type="number" name="resource_profile[bands][${index}][max]" class="form-control test-form-control" placeholder="max">
                    <input type="text" name="resource_profile[bands][${index}][label]" class="form-control test-form-control" placeholder="Название">
                    <input type="text" name="resource_profile[bands][${index}][text]" class="form-control test-form-control" placeholder="Описание">
                    <button type="button" class="test-small-btn danger js-remove-band">×</button>
                </div>
            `;
            }

            if (addResourceBandBtn && resourceBandsContainer) {
                addResourceBandBtn.addEventListener('click', function () {
                    const index = resourceBandsContainer.querySelectorAll('.resource-band-item').length;
                    resourceBandsContainer.insertAdjacentHTML('beforeend', getBandRow(index));
                });
            }

            if (resourceBandsContainer) {
                resourceBandsContainer.addEventListener('click', function (event) {
                    if (event.target.classList.contains('js-remove-band')) {
                        const rows = resourceBandsContainer.querySelectorAll('.resource-band-item');

                        if (rows.length <= 1) return;

                        event.target.closest('.resource-band-item').remove();
                        refreshBandNames();
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // =========================
// Автогенерация code из name/title
// =========================

            const translitMap = {
                а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e',
                ж: 'zh', з: 'z', и: 'i', й: 'y', к: 'k', л: 'l', м: 'm',
                н: 'n', о: 'o', п: 'p', р: 'r', с: 's', т: 't', у: 'u',
                ф: 'f', х: 'h', ц: 'c', ч: 'ch', ш: 'sh', щ: 'sch',
                ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya'
            };

            function normalizeCode(value) {
                return String(value || '')
                    .trim()
                    .toLowerCase()
                    .split('')
                    .map(function (char) {
                        return translitMap[char] ?? char;
                    })
                    .join('')
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .toUpperCase();
            }

            function getCodeInputForSource(sourceInput) {
                if (sourceInput.name === 'name') {
                    const form = sourceInput.closest('form');

                    return form ? form.querySelector('input[name="code"]') : null;
                }

                if (sourceInput.name && sourceInput.name.endsWith('[title]')) {
                    const card = sourceInput.closest('.card-sort-item, .image-card-item');

                    return card ? card.querySelector('input[name$="[code]"]') : null;
                }

                return null;
            }

            function getSourceInputForCode(codeInput) {
                if (codeInput.name === 'code') {
                    const form = codeInput.closest('form');

                    return form ? form.querySelector('input[name="name"]') : null;
                }

                if (codeInput.name && codeInput.name.endsWith('[code]')) {
                    const card = codeInput.closest('.card-sort-item, .image-card-item');

                    return card ? card.querySelector('input[name$="[title]"]') : null;
                }

                return null;
            }

            document.addEventListener('input', function (event) {
                const target = event.target;

                if (
                    target.matches('input[name="name"]') ||
                    target.matches('input[name$="[title]"]')
                ) {
                    const codeInput = getCodeInputForSource(target);

                    if (!codeInput) {
                        return;
                    }

                    if (codeInput.dataset.codeTouched === '1') {
                        return;
                    }

                    codeInput.value = normalizeCode(target.value);
                }

                if (
                    target.matches('input[name="code"]') ||
                    target.matches('input[name$="[code]"]')
                ) {
                    target.dataset.codeTouched = '1';
                    target.value = normalizeCode(target.value);
                }
            });

            document.addEventListener('blur', function (event) {
                const target = event.target;

                if (
                    !target.matches('input[name="code"]') &&
                    !target.matches('input[name$="[code]"]')
                ) {
                    return;
                }

                target.value = normalizeCode(target.value);

                if (target.value !== '') {
                    return;
                }

                target.dataset.codeTouched = '0';

                const sourceInput = getSourceInputForCode(target);

                if (sourceInput) {
                    target.value = normalizeCode(sourceInput.value);
                }
            }, true);
        });
    </script>

@endsection
