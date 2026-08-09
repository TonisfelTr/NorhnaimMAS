@extends('doctors.prescriptions')

@section('title', 'Выписка рецептов')
@section('prescription-title', 'Выписка рецепта')

@section('assets')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <style>
        .rx-create {
            --rx-create-primary: #078c9c;
            --rx-create-primary-dark: #056b78;
            --rx-create-text: #1f2d3d;
            --rx-create-muted: #6b778c;
            --rx-create-border: #dbe6ec;
            --rx-create-soft: #eef8fa;
            --rx-create-bg: #f7fafc;
            --rx-create-success: #16834f;
            --rx-create-warning: #b56a00;
            --rx-create-danger: #c43232;

            padding: 28px 0 48px;
        }

        .rx-create__container {
            width: min(1420px, calc(100% - 32px));
            margin: 0 auto;
        }

        .rx-create__hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            padding: 26px 28px;
            border: 1px solid rgba(7, 140, 156, .12);
            border-radius: 26px;
            background:
                radial-gradient(circle at 92% 12%, rgba(22, 196, 211, .13), transparent 30%),
                linear-gradient(135deg, #fbfeff 0%, #eef9fb 100%);
            box-shadow: 0 18px 46px rgba(26, 68, 78, .08);
        }

        .rx-create__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(7, 140, 156, .10);
            color: var(--rx-create-primary-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .rx-create__hero-title {
            margin: 14px 0 8px;
            color: #17263a;
            font-size: clamp(30px, 3vw, 46px);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -.025em;
        }

        .rx-create__hero-text {
            max-width: 860px;
            margin: 0;
            color: var(--rx-create-muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .rx-create__hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .rx-create__button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 17px;
            border: 1px solid var(--rx-create-border);
            border-radius: 13px;
            background: #fff;
            color: #26374c;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition:
                transform .15s ease,
                box-shadow .15s ease,
                border-color .15s ease,
                background .15s ease;
        }

        .rx-create__button:hover {
            transform: translateY(-1px);
            color: #26374c;
            text-decoration: none;
            border-color: #bfd3dc;
            box-shadow: 0 8px 20px rgba(16, 24, 40, .07);
        }

        .rx-create__button--primary {
            border-color: var(--rx-create-primary);
            background: var(--rx-create-primary);
            color: #fff;
            box-shadow: 0 10px 22px rgba(7, 140, 156, .22);
        }

        .rx-create__button--primary:hover {
            color: #fff;
            border-color: var(--rx-create-primary-dark);
            background: var(--rx-create-primary-dark);
        }

        .rx-create__layout {
            display: grid;
            grid-template-columns: minmax(0, 1.75fr) minmax(300px, .75fr);
            gap: 18px;
            margin-top: 18px;
            align-items: start;
        }

        .rx-create__panel {
            overflow: hidden;
            border: 1px solid var(--rx-create-border);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 15px 38px rgba(16, 24, 40, .05);
        }

        .rx-create__panel + .rx-create__panel {
            margin-top: 16px;
        }

        .rx-create__panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 21px;
            border-bottom: 1px solid #edf2f5;
        }

        .rx-create__panel-title {
            margin: 0;
            color: #17324c;
            font-size: 18px;
            font-weight: 900;
        }

        .rx-create__panel-subtitle {
            margin-top: 4px;
            color: var(--rx-create-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .rx-create__step {
            display: inline-flex;
            min-width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: var(--rx-create-soft);
            color: var(--rx-create-primary-dark);
            font-size: 12px;
            font-weight: 900;
        }

        .rx-create__panel-body {
            padding: 20px 21px 22px;
        }

        .rx-create__field {
            margin-bottom: 16px;
        }

        .rx-create__label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: #344054;
            font-size: 13px;
            font-weight: 800;
        }

        .rx-create__label i {
            color: var(--rx-create-primary);
        }

        .rx-create__hint {
            margin-top: 6px;
            color: #8a96a8;
            font-size: 11px;
            line-height: 1.45;
        }

        .rx-create .form-control,
        .rx-create .form-select {
            min-height: 46px;
            border: 1px solid var(--rx-create-border);
            border-radius: 13px;
            background-color: #fff;
            color: var(--rx-create-text);
            font-size: 14px;
            box-shadow: none;
        }

        .rx-create .form-control:focus,
        .rx-create .form-select:focus {
            border-color: rgba(7, 140, 156, .55);
            box-shadow: 0 0 0 4px rgba(7, 140, 156, .08);
        }

        .rx-create .form-control:disabled,
        .rx-create .form-select:disabled {
            background: #f5f7f9;
            color: #9aa4b2;
            opacity: 1;
        }

        .rx-create .select2-container {
            width: 100% !important;
        }

        .rx-create .select2-container--bootstrap-5 .select2-selection {
            min-height: 46px;
            border: 1px solid var(--rx-create-border);
            border-radius: 13px;
            box-shadow: none;
        }

        .rx-create .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .rx-create .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: rgba(7, 140, 156, .55);
            box-shadow: 0 0 0 4px rgba(7, 140, 156, .08);
        }

        .rx-create .select2-container--bootstrap-5 .select2-selection--single {
            padding-top: .65rem;
            padding-bottom: .65rem;
        }

        .rx-create__grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 14px;
        }

        .rx-create__col-3 {
            grid-column: span 3;
        }

        .rx-create__col-4 {
            grid-column: span 4;
        }

        .rx-create__col-6 {
            grid-column: span 6;
        }

        .rx-create__col-12 {
            grid-column: span 12;
        }

        .rx-create__strict {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 12px 0 0;
            padding: 12px 14px;
            border: 1px solid #f3c989;
            border-radius: 14px;
            background: #fff8e8;
            color: #935b08;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.5;
        }

        .rx-create__strict i {
            margin-top: 1px;
            font-size: 16px;
        }

        .rx-create__scheme {
            padding: 16px;
            border: 1px solid #dcecf0;
            border-radius: 18px;
            background:
                linear-gradient(135deg, #fbfeff 0%, #f4fafb 100%);
        }

        .rx-create__scheme-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }

        .rx-create__scheme-row .form-control {
            width: 82px;
            min-height: 40px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .rx-create__scheme-row select.form-control {
            width: 88px;
        }

        .rx-create__scheme-word {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            padding: 0 4px;
            color: #455468;
        }

        .rx-create__days {
            display: inline-flex;
            min-width: 74px;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 0 10px;
            border-radius: 11px;
            background: #eaf7f9;
            color: var(--rx-create-primary-dark);
            font-weight: 900;
        }

        .rx-create__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 4px;
        }

        .rx-create__submit,
        .rx-create__print {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 18px;
            border-radius: 13px;
            font-size: 13px;
            font-weight: 900;
            transition: .15s ease;
        }

        .rx-create__submit {
            border: 1px solid var(--rx-create-primary);
            background: var(--rx-create-primary);
            color: #fff;
            box-shadow: 0 10px 22px rgba(7, 140, 156, .22);
        }

        .rx-create__submit:hover {
            border-color: var(--rx-create-primary-dark);
            background: var(--rx-create-primary-dark);
            color: #fff;
        }

        .rx-create__print {
            border: 1px solid #b8d8c7;
            background: #f4fbf7;
            color: var(--rx-create-success);
        }

        .rx-create__print:hover:not(:disabled) {
            border-color: #86c5a4;
            background: #eaf8f0;
        }

        .rx-create__print:disabled,
        .rx-create__submit:disabled {
            cursor: not-allowed;
            opacity: .48;
            box-shadow: none;
        }

        .rx-create__aside {
            position: sticky;
            top: 88px;
        }

        .rx-create__info-list {
            display: grid;
            gap: 10px;
        }

        .rx-create__info-row {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 13px 14px;
            border: 1px solid #edf2f5;
            border-radius: 15px;
            background: #fbfdfe;
        }

        .rx-create__info-icon {
            display: inline-flex;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: var(--rx-create-soft);
            color: var(--rx-create-primary-dark);
        }

        .rx-create__info-title {
            margin-bottom: 2px;
            color: #24364b;
            font-size: 12px;
            font-weight: 900;
        }

        .rx-create__info-text {
            color: var(--rx-create-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .rx-create__notice {
            margin-top: 12px;
            padding: 13px 14px;
            border: 1px solid #f0d79e;
            border-radius: 15px;
            background: #fff9ec;
            color: #8a5a0a;
            font-size: 11px;
            line-height: 1.55;
        }

        .rx-create__errors {
            margin: 18px 0 0;
            padding: 15px 17px;
            border: 1px solid #f3b9b9;
            border-radius: 16px;
            background: #fff4f4;
            color: #9e2f2f;
        }

        .rx-create__errors-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 7px;
            font-weight: 900;
        }

        .rx-create__errors ul {
            margin: 0;
            padding-left: 20px;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 1100px) {
            .rx-create__layout {
                grid-template-columns: 1fr;
            }

            .rx-create__aside {
                position: static;
            }
        }

        @media (max-width: 820px) {
            .rx-create__hero {
                flex-direction: column;
            }

            .rx-create__hero-actions {
                justify-content: flex-start;
            }

            .rx-create__col-3,
            .rx-create__col-4,
            .rx-create__col-6 {
                grid-column: span 12;
            }
        }

        @media (max-width: 620px) {
            .rx-create {
                padding-top: 16px;
            }

            .rx-create__container {
                width: min(100% - 18px, 100%);
            }

            .rx-create__hero,
            .rx-create__panel-head,
            .rx-create__panel-body {
                padding-left: 16px;
                padding-right: 16px;
            }

            .rx-create__hero-title {
                font-size: 30px;
            }

            .rx-create__scheme-row {
                align-items: stretch;
            }

            .rx-create__scheme-row .form-control,
            .rx-create__scheme-row select.form-control {
                width: 100%;
            }

            .rx-create__scheme-word {
                min-height: auto;
            }

            .rx-create__days {
                min-width: 100%;
            }

            .rx-create__actions > * {
                width: 100%;
            }
        }
    </style>
@endsection

@section('sub-main')
    <main class="rx-create">
        <div class="rx-create__container">
            <section class="rx-create__hero">
                <div>
                    <div class="rx-create__eyebrow">
                        <i class="bi bi-prescription2"></i>
                        Новое лекарственное назначение
                    </div>

                    <h1 class="rx-create__hero-title">
                        Выписка рецепта
                    </h1>

                    <p class="rx-create__hero-text">
                        Выберите пациента и препарат, затем уточните форму выпуска,
                        дозировку, количество и схему приёма. Печатная форма станет
                        доступна после заполнения обязательных полей.
                    </p>
                </div>

                <div class="rx-create__hero-actions">
                    <a
                        class="rx-create__button"
                        href="{{ route('doctors.prescriptions.index') }}"
                    >
                        <i class="bi bi-journal-medical"></i>
                        К журналу
                    </a>

                    <a
                        class="rx-create__button rx-create__button--primary"
                        href="{{ route('doctors.prescriptions.base') }}"
                    >
                        <i class="bi bi-capsule-pill"></i>
                        База препаратов
                    </a>
                </div>
            </section>

            @if($errors->any())
                <div class="rx-create__errors">
                    <div class="rx-create__errors-title">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        Не удалось оформить рецепт
                    </div>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rx-create__layout">
                <form
                    method="post"
                    action="{{ route('doctors.prescriptions.store') }}"
                    enctype="multipart/form-data"
                >
                    <input
                        type="hidden"
                        name="usage_instructions"
                        id="usage_instructions__"
                    >
                    <input
                        type="hidden"
                        name="birth_at"
                        id="birth_at__"
                    >
                    @csrf

                    <section class="rx-create__panel">
                        <div class="rx-create__panel-head">
                            <div>
                                <h2 class="rx-create__panel-title">
                                    Пациент и препарат
                                </h2>
                                <div class="rx-create__panel-subtitle">
                                    Начните с выбора пациента, затем найдите препарат.
                                </div>
                            </div>

                            <span class="rx-create__step">1</span>
                        </div>

                        <div class="rx-create__panel-body">
                            <div class="rx-create__field">
                                <label
                                    for="patient_id"
                                    class="rx-create__label"
                                >
                                    <i class="bi bi-person-vcard"></i>
                                    Пациент
                                </label>

                                <select
                                    id="patient_id"
                                    name="patient_id"
                                    class="form-control w-100"
                                    required
                                >
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->latin_name }}">
                                            {{ $patient->surname }},
                                            {{ substr($patient->name, 0, 1) }}.
                                            {{ substr($patient->patronym, 0, 1) }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="rx-create__hint">
                                    Поиск выполняется по пациентам через существующий API.
                                </div>
                            </div>

                            <div class="rx-create__field mb-0">
                                <label
                                    for="drug_id"
                                    class="rx-create__label"
                                >
                                    <i class="bi bi-capsule"></i>
                                    Лекарство
                                </label>

                                <select
                                    id="drug_id"
                                    class="form-control w-100"
                                    name="drug_id"
                                ></select>

                                <div class="rx-create__hint">
                                    После выбора препарата будут доступны формы выпуска и дозировки.
                                </div>

                                <p
                                    id="strict_message"
                                    class="rx-create__strict d-none"
                                >
                                    <i class="bi bi-shield-exclamation"></i>
                                    <span>
                                        Данный препарат отпускается по рецепту
                                        <strong>148-1/у-88</strong>.
                                    </span>
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rx-create__panel">
                        <div class="rx-create__panel-head">
                            <div>
                                <h2 class="rx-create__panel-title">
                                    Параметры назначения
                                </h2>
                                <div class="rx-create__panel-subtitle">
                                    Форма выпуска, дозировка и количество препарата.
                                </div>
                            </div>

                            <span class="rx-create__step">2</span>
                        </div>

                        <div class="rx-create__panel-body">
                            <div class="rx-create__grid">
                                <div class="rx-create__field rx-create__col-3">
                                    <label
                                        for="drug_form"
                                        class="rx-create__label"
                                    >
                                        <i class="bi bi-box-seam"></i>
                                        Форма
                                    </label>

                                    <select
                                        id="drug_form"
                                        name="drug_form"
                                        class="form-control w-100"
                                        disabled
                                    ></select>
                                </div>

                                <div class="rx-create__field rx-create__col-3">
                                    <label
                                        for="dosage"
                                        class="rx-create__label"
                                    >
                                        <i class="bi bi-speedometer2"></i>
                                        Дозировка
                                    </label>

                                    <select
                                        id="dosage"
                                        name="dosage"
                                        class="form-control w-100"
                                        disabled
                                    ></select>
                                </div>

                                <div class="rx-create__field rx-create__col-3">
                                    <label
                                        for="quantity"
                                        class="rx-create__label"
                                    >
                                        <i class="bi bi-123"></i>
                                        Количество
                                    </label>

                                    <select
                                        id="quantity"
                                        name="quantity"
                                        class="form-control w-100"
                                        disabled
                                    ></select>
                                </div>

                                <div class="rx-create__field rx-create__col-3">
                                    <label
                                        for="standard"
                                        class="rx-create__label"
                                    >
                                        <i class="bi bi-boxes"></i>
                                        Стандартов
                                    </label>

                                    <input
                                        class="form-control w-100"
                                        type="number"
                                        name="standard"
                                        id="standard"
                                        min="1"
                                        value="1"
                                        disabled
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rx-create__panel">
                        <div class="rx-create__panel-head">
                            <div>
                                <h2 class="rx-create__panel-title">
                                    Схема приёма
                                </h2>
                                <div class="rx-create__panel-subtitle">
                                    Настройте разовую дозу, кратность и связь с приёмом пищи.
                                </div>
                            </div>

                            <span class="rx-create__step">3</span>
                        </div>

                        <div class="rx-create__panel-body">
                            <div class="rx-create__scheme">
                                <label
                                    for="usage_instructions"
                                    class="rx-create__label"
                                >
                                    <i class="bi bi-clock-history"></i>
                                    Схема приёма
                                </label>

                                <div
                                    class="rx-create__scheme-row"
                                    id="usage_instructions"
                                >
                                    <span class="rx-create__scheme-word">По</span>

                                    <input
                                        class="form-control"
                                        type="number"
                                        name="taking_drug"
                                        id="taking_drug"
                                        min="1"
                                        max="4"
                                        value="1"
                                        disabled
                                    >

                                    <span
                                        class="rx-create__scheme-word"
                                        id="drug_type"
                                    ></span>

                                    <select
                                        id="taking_count"
                                        name="taking_count"
                                        class="form-control"
                                        disabled
                                    >
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>

                                    <span
                                        class="rx-create__scheme-word"
                                        id="taking_time"
                                    ></span>

                                    <span class="rx-create__scheme-word">
                                        в день на
                                    </span>

                                    <span class="rx-create__days">
                                        <span id="days_count">—</span>
                                        <span id="days_label">дней</span>
                                    </span>

                                    <select
                                        class="form-control"
                                        name="taking_time_meal"
                                        id="taking_time_meal"
                                        disabled
                                    >
                                        <option value="1">после</option>
                                        <option value="2">до</option>
                                    </select>

                                    <span class="rx-create__scheme-word">
                                        еды
                                    </span>
                                </div>
                            </div>

                            <div
                                class="rx-create__field mt-3 mb-0"
                                id="validity_block"
                            >
                                <label
                                    for="validity_period"
                                    class="rx-create__label"
                                >
                                    <i class="bi bi-calendar2-check"></i>
                                    Срок действия рецепта
                                </label>

                                <select
                                    class="form-control"
                                    id="validity_period"
                                    name="validity_period"
                                >
                                    <option value="60">60 дней</option>
                                    <option value="365">1 год</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="rx-create__panel">
                        <div class="rx-create__panel-head">
                            <div>
                                <h2 class="rx-create__panel-title">
                                    Оформление
                                </h2>
                                <div class="rx-create__panel-subtitle">
                                    Сохраните назначение или предварительно распечатайте рецепт.
                                </div>
                            </div>

                            <span class="rx-create__step">4</span>
                        </div>

                        <div class="rx-create__panel-body">
                            <div class="rx-create__actions">
                                <button
                                    type="submit"
                                    class="rx-create__submit"
                                >
                                    <i class="bi bi-journal-check"></i>
                                    Выписать рецепт
                                </button>

                                <button
                                    id="printRecipeButton"
                                    type="button"
                                    class="rx-create__print"
                                    disabled
                                >
                                    <i class="bi bi-printer"></i>
                                    Распечатать рецепт
                                </button>
                            </div>
                        </div>
                    </section>
                </form>

                <aside class="rx-create__aside">
                    <section class="rx-create__panel">
                        <div class="rx-create__panel-head">
                            <div>
                                <h2 class="rx-create__panel-title">
                                    Перед оформлением
                                </h2>
                                <div class="rx-create__panel-subtitle">
                                    Быстрая проверка основных шагов.
                                </div>
                            </div>
                        </div>

                        <div class="rx-create__panel-body">
                            <div class="rx-create__info-list">
                                <div class="rx-create__info-row">
                                    <span class="rx-create__info-icon">
                                        <i class="bi bi-person-check"></i>
                                    </span>

                                    <div>
                                        <div class="rx-create__info-title">
                                            Выберите пациента
                                        </div>
                                        <div class="rx-create__info-text">
                                            Дата рождения автоматически сохраняется
                                            в скрытое поле после выбора пациента.
                                        </div>
                                    </div>
                                </div>

                                <div class="rx-create__info-row">
                                    <span class="rx-create__info-icon">
                                        <i class="bi bi-capsule-pill"></i>
                                    </span>

                                    <div>
                                        <div class="rx-create__info-title">
                                            Укажите препарат
                                        </div>
                                        <div class="rx-create__info-text">
                                            Форма, дозировка и количество зависят
                                            от выбранного лекарственного средства.
                                        </div>
                                    </div>
                                </div>

                                <div class="rx-create__info-row">
                                    <span class="rx-create__info-icon">
                                        <i class="bi bi-calendar-week"></i>
                                    </span>

                                    <div>
                                        <div class="rx-create__info-title">
                                            Проверьте схему
                                        </div>
                                        <div class="rx-create__info-text">
                                            Срок курса рассчитывается автоматически
                                            из количества и кратности приёма.
                                        </div>
                                    </div>
                                </div>

                                <div class="rx-create__info-row">
                                    <span class="rx-create__info-icon">
                                        <i class="bi bi-printer"></i>
                                    </span>

                                    <div>
                                        <div class="rx-create__info-title">
                                            Печать
                                        </div>
                                        <div class="rx-create__info-text">
                                            Кнопка печати активируется только после
                                            заполнения всех необходимых полей.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rx-create__notice">
                                <i class="bi bi-info-circle me-1"></i>
                                Для препаратов строгого учёта система показывает
                                отдельное предупреждение и применяет форму
                                рецепта 148-1/у-88.
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Асинхронная загрузка пациентов
            $('#patient_id').select2({
                placeholder: 'Выберите пациента',
                theme: 'bootstrap-5',
                ajax: {
                    url: '/api/doctors/search-patients',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.results.map(function (patient) {
                                return {
                                    id: patient.id,
                                    text: patient.text,
                                    birth_at: patient.birth_at
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Обработчик выбора пациента
            $('#patient_id').on('select2:select', function (e) {
                const selectedData = e.params.data;

                if (selectedData && selectedData.birth_at) {
                    // Заполняем скрытое поле датой рождения
                    document.getElementById('birth_at__').value = selectedData.birth_at;
                } else {
                    console.warn('Дата рождения не найдена для выбранного пациента.');
                    document.getElementById('birth_at__').value = ''; // Очищаем поле, если данных нет
                }
            });

            // Асинхронная загрузка препаратов
            $('#drug_id').select2({
                placeholder: 'Выберите препарат',
                theme: 'bootstrap-5',
                ajax: {
                    url: '/api/doctors/search-drugs',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                }
            });

            let drugData = []; // Данные о текущем лекарстве

            // Обновление зависимых селекторов при выборе препарата
            $('#drug_id').on('select2:select', function(e) {
                const drugLatinName = e.params.data.latin_name;

                if (!drugLatinName) {
                    alert('Не удалось определить название препарата.');
                    return;
                }

                // Выполняем AJAX-запрос для получения форм, доз и стандартов
                $.ajax({
                    url: `/api/doctors/search-drugs/${drugLatinName}/forms`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        drugData = data; // Сохраняем данные лекарства для дальнейшего использования

                        // Разблокировка полей
                        $('#drug_form, #dosage, #quantity, #standard, #taking_drug, #taking_time_meal').prop('disabled', false);

                        // Уникализация форм
                        const uniqueForms = [...new Set(data.map(item => item.form))];

                        // Очистка селектора форм
                        $('#drug_form').empty().append(new Option('', '', true, true));
                        uniqueForms.forEach(form => {
                            $('#drug_form').append(new Option(form, form));
                        });
                        $('#drug_form').trigger('change');

                        // Настройка поля "Кол-во стандартов"
                        const $standardField = $('#standard');
                        const $strictMessage = $('#strict_message');
                        const $validityBlock = $('#validity_block');
                        const strict = data.some(item => item.strict); // Проверяем наличие strict = true
                        if (strict) {
                            $standardField.attr('min', 1).attr('max', 3).prop('disabled', false);
                            $strictMessage.removeClass('d-none');
                            $validityBlock.addClass('d-none');
                        } else {
                            $standardField.attr('min', 1).prop('disabled', false);
                            $strictMessage.addClass('d-none');
                            $validityBlock.removeClass('d-none');
                        }

                        $('#standard').trigger('change');
                    },
                    error: function() {
                        alert('Ошибка при загрузке данных для выбранного препарата.');
                    }
                });
            });

            $('#quantity, #standard, #taking_time_meal, #taking_count').on('change', function () {
                const doseCount = parseInt($('#quantity').val(), 10) * parseInt($('#standard').val(), 10);
                const dosePerDay = parseInt($('#taking_drug').val(), 10) * parseInt($('#taking_count').val(), 10);

                let result = Math.floor(doseCount / dosePerDay);
                let daysCount = parseInt($('#quantity').val()) * parseInt($('#standard').val());

                $('#days_label').text(getDayWord(result));
                $('#days_count').text(result);

                let forHiddenText = `По ${$('#taking_drug').val()} ${$('#drug_type').text()} ${$('#taking_count').val()} ${$('#taking_time').text()} в день на ${$('#days_count').text()} ${$('#days_label').text()} ${$('#taking_time_meal').val() == 1 ? 'после' : 'до'} еды`;
                $('#usage_instructions__').val(forHiddenText);
            });

            // Обновление дозировок и количества при выборе формы
            $('#drug_form').on('change', function() {
                const selectedForm = $(this).val();
                const takingDrug = $('#taking_drug');
                const takingCount = $('#taking_count');

                if (!selectedForm) {
                    // Если форма не выбрана, очищаем и блокируем зависимые селекторы
                    $('#dosage, #quantity').empty().prop('disabled', true);
                    return;
                }

                // Фильтрация данных по выбранной форме
                const filteredData = drugData.filter(item => item.form === selectedForm);

                // Уникализация дозировок и количества
                const uniqueDoses = [...new Set(filteredData.map(item => item.dose))];
                const uniqueCounts = [...new Set(filteredData.map(item => item.count))];

                // Обновление селектора дозировок
                $('#dosage').empty();
                uniqueDoses.forEach(dose => {
                    $('#dosage').append(new Option(dose, dose));
                });
                $('#dosage').prop('disabled', false).trigger('change');

                // Обновление селектора количества
                $('#quantity').empty();
                uniqueCounts.forEach(count => {
                    $('#quantity').append(new Option(count, count));
                });
                $('#quantity').prop('disabled', false).trigger('change');

                takingDrug.change();
                takingCount.prop('disabled', false).change();

                const doseCount = parseInt($('#quantity').val(), 10) * parseInt($('#standard').val(), 10);
                const dosePerDay = parseInt($('#taking_drug').val(), 10) * parseInt($('#taking_count').val(), 10);

                $('#days_count').text((doseCount / dosePerDay).toString());

                let result = doseCount / dosePerDay;
                $('#days_label').text(getDayWord(result));

                $('#quantity, #standard, #taking_time_meal, #taking_count').change()
            });

            $('#taking_drug').on('change', function () {
                const value = $(this).val();
                const formWord = $('#drug_type');
                const formSelect = $('#drug_form').val();

                let word = '';

                switch (formSelect) {
                    case 'Таблетки':
                        switch (value) {
                            case '1':
                                word = 'таблетке';
                                break;
                            case '2':
                            case '3':
                            case '4':
                                word = 'таблетки';
                                break;
                        }
                        break;
                    case 'Драже':
                        word = 'драже';
                        break;
                    case 'Ампулы':
                        switch (value) {
                            case '1':
                                word = 'ампуле';
                                break;
                            case '2':
                            case '3':
                            case '4':
                                word = 'ампулы';
                                break;
                        }
                        break;
                    case 'Капсулы':
                        switch (value) {
                            case '1':
                                word = 'капсуле';
                                break;
                            case '2':
                            case '3':
                            case '4':
                                word = 'капсулы';
                                break;
                        }
                        break;
                    default:
                        console.warn('Не удалось определить тип лекарственной формы. Шаг: 5');
                        word = '?'
                        break;
                }

                formWord.text(word);
            })

            $('#taking_count').on('change', function () {
                const takingTime = $('#taking_time');
                const value = $(this).val();

                let content = '';

                switch (value) {
                    case '1':
                    case '5':
                        content = 'раз';
                        break;
                    case '2':
                    case '3':
                    case '4':
                        content = 'раза';
                        break;
                }

                takingTime.text(content);
            })

            function getDayWord(result) {
                // Преобразуем число в целое (на случай дробного результата)
                const count = Math.floor(result);

                // Определяем окончание
                if (count % 10 === 1 && count % 100 !== 11) {
                    return 'день';
                } else if ([2, 3, 4].includes(count % 10) && ![12, 13, 14].includes(count % 100)) {
                    return 'дня';
                } else {
                    return 'дней';
                }
            }

            // Обработчик нажатия на кнопку «Распечатать рецепт»
            const printRecipeButton = document.getElementById('printRecipeButton');

            printRecipeButton.addEventListener('click', function (e) {
                e.preventDefault(); // Отменяем стандартное поведение кнопки

                // Получаем данные формы
                const formData = new FormData(document.querySelector('form'));

                // Выполняем AJAX-запрос на маршрут для генерации рецепта
                fetch('{{ route('doctors.prescriptions.print') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                    .then(response => response.text()) // Ожидаем HTML-контент
                    .then(html => {
                        // Создаем скрытый iframe
                        const iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);

                        // Записываем HTML в iframe и вызываем печать
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write(html);
                        iframeDoc.close();

                        // Ждем загрузки содержимого, затем вызываем печать
                        iframe.onload = function () {
                            iframe.contentWindow.print();
                            // Удаляем iframe после печати
                            document.body.removeChild(iframe);
                        };
                    })
                    .catch(error => {
                        console.error('Ошибка при печати рецепта:', error);
                        alert('Произошла ошибка при попытке распечатать рецепт.');
                    });
            });

            const printButton = document.getElementById('printRecipeButton');
            const formFields = [
                'patient_id',
                'drug_id',
                'drug_form',
                'dosage',
                'quantity',
                'standard',
                'taking_drug',
                'taking_count',
                'taking_time_meal'
            ];

            // Функция для проверки заполненности всех полей
            function checkFormCompletion() {
                let isComplete = true;

                // Проверяем, что все поля заполнены
                formFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (!field || !field.value) {
                        isComplete = false;
                    }
                });

                // Активируем или деактивируем кнопку в зависимости от заполненности
                printButton.disabled = !isComplete;
            }

            // Добавляем обработчики событий для всех полей формы
            formFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('change', checkFormCompletion);
                    field.addEventListener('input', checkFormCompletion); // Для текстовых полей
                }
            });

            // Проверяем форму при загрузке страницы
            checkFormCompletion()

            $('#drug_form').on('change', function() {
                const selectedForm = $(this).val();
                const filteredData = drugData.filter(item => item.form === selectedForm);

                $('#dosage').empty().prop('disabled', false);
                [...new Set(filteredData.map(item => item.dose))].forEach(dose => $('#dosage').append(new Option(dose, dose)));

                if (selectedForm === 'Ампулы') {
                    $('#ampule_volume').empty().prop('disabled', false);
                    [...new Set(filteredData.map(item => item.volume))].forEach(volume => $('#ampule_volume').append(new Option(volume, volume)));
                    $('#ampule_volume_block').show();
                } else {
                    $('#ampule_volume').empty().prop('disabled', true);
                    $('#ampule_volume_block').hide();
                }

                $('#quantity').empty().prop('disabled', false);
                [...new Set(filteredData.map(item => item.count))].forEach(count => $('#quantity').append(new Option(count, count)));
            });
        });
    </script>
@endsection
