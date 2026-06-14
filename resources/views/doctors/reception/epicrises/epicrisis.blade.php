@extends('doctors.reception')
@section('title', 'Просмотр эпикриза')
@section('assets')
    <style>
        .epicrisis-page {
            --ep-bg: #f6f8fb;
            --ep-card: #ffffff;
            --ep-border: #e7ecf3;
            --ep-muted: #6b7280;
            --ep-text: #111827;
            --ep-primary: #0d6efd;
            --ep-primary-soft: #eef5ff;
            --ep-success-soft: #ecfdf3;
            --ep-success: #15803d;
            --ep-danger: #dc3545;
            --ep-radius: 18px;
            --ep-shadow: 0 8px 28px rgba(15, 23, 42, .06);

            background: var(--ep-bg);
            padding: 24px;
            border-radius: 18px;
            color: var(--ep-text);
        }

        .epicrisis-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 18px;
        }

        .epicrisis-header__left {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            min-width: 0;
        }

        .epicrisis-title-block {
            min-width: 0;
        }

        .epicrisis-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--ep-primary);
            margin-bottom: 4px;
        }

        .epicrisis-title {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.15;
            color: var(--ep-text);
        }

        .epicrisis-subtitle {
            margin-top: 6px;
            font-size: 14px;
            color: var(--ep-muted);
        }

        .epicrisis-header__actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .epicrisis-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 18px;
            align-items: start;
        }

        .epicrisis-main {
            min-width: 0;
        }

        .epicrisis-card,
        .epicrisis-side-card {
            background: var(--ep-card);
            border: 1px solid var(--ep-border);
            border-radius: var(--ep-radius);
            box-shadow: var(--ep-shadow);
        }

        .epicrisis-card {
            overflow: hidden;
        }

        .epicrisis-card__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 20px 22px;
            border-bottom: 1px solid var(--ep-border);
            background: radial-gradient(circle at top right, rgba(13, 110, 253, .08), transparent 34%),
            #ffffff;
        }

        .epicrisis-card__label {
            font-size: 12px;
            font-weight: 700;
            color: var(--ep-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 4px;
        }

        .epicrisis-card__title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: var(--ep-text);
        }

        .epicrisis-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .epicrisis-status--success {
            background: var(--ep-success-soft);
            color: var(--ep-success);
            border: 1px solid #bbf7d0;
        }

        .epicrisis-content {
            padding: 24px 28px 30px;
            font-size: 15px;
            line-height: 1.72;
            color: #253044;
        }

        .epicrisis-content h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 26px 0 10px;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.3;
            color: #111827;
        }

        .epicrisis-content h3:first-child {
            margin-top: 0;
        }

        .epicrisis-content h3::before {
            content: "";
            width: 8px;
            height: 22px;
            border-radius: 999px;
            background: var(--ep-primary);
            flex: 0 0 auto;
        }

        .epicrisis-content p {
            margin: 0 0 12px;
        }

        .epicrisis-content ul,
        .epicrisis-content ol {
            margin: 8px 0 0;
            padding-left: 22px;
        }

        .epicrisis-content li {
            margin-bottom: 7px;
        }

        .epicrisis-sidebar {
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: sticky;
            top: 14px;
        }

        .epicrisis-side-card {
            padding: 16px;
        }

        .epicrisis-side-card__title {
            font-size: 15px;
            font-weight: 800;
            color: var(--ep-text);
            margin-bottom: 14px;
        }

        .epicrisis-info-list {
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .epicrisis-info-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e5eaf1;
        }

        .epicrisis-info-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .epicrisis-info-label {
            font-size: 12px;
            color: var(--ep-muted);
        }

        .epicrisis-info-value {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            text-align: right;
        }

        .epicrisis-diagnosis {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid #dbeafe;
            background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
            border-radius: 16px;
            padding: 14px;
        }

        .epicrisis-diagnosis__icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #ffffff;
            color: #0d6efd;
            border: 1px solid #cfe2ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            font-size: 18px;
        }

        .epicrisis-diagnosis__body {
            min-width: 0;
            flex: 1;
        }

        .epicrisis-diagnosis__label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 7px;
        }

        .epicrisis-diagnosis__main {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .epicrisis-diagnosis__code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #ffffff;
            color: #0d6efd;
            border: 1px solid #bfdbfe;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .epicrisis-diagnosis__title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            color: #1e3a8a;
        }

        .epicrisis-patient {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .epicrisis-patient__avatar {
            width: 46px;
            height: 46px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e0f2fe, #eef2ff);
            color: #1d4ed8;
            font-weight: 800;
            flex: 0 0 auto;
        }

        .epicrisis-patient__name {
            font-size: 14px;
            font-weight: 800;
            color: var(--ep-text);
            line-height: 1.25;
        }

        .epicrisis-patient__meta {
            margin-top: 3px;
            font-size: 12px;
            color: var(--ep-muted);
        }

        .epicrisis-actions-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .epicrisis-action {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px 12px;
            border: 1px solid var(--ep-border);
            border-radius: 12px;
            background: #fff;
            color: #374151;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: .15s ease;
        }

        .epicrisis-action:hover {
            background: #f8fbff;
            border-color: #bfdbfe;
            color: var(--ep-primary);
            text-decoration: none;
        }

        .epicrisis-action i {
            font-size: 16px;
        }

        .epicrisis-action--danger {
            color: var(--ep-danger);
        }

        .epicrisis-action--danger:hover {
            background: #fff5f5;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        @media (max-width: 1199px) {
            .epicrisis-layout {
                grid-template-columns: minmax(0, 1fr) 300px;
            }
        }

        @media (max-width: 991px) {
            .epicrisis-page {
                padding: 16px;
            }

            .epicrisis-header {
                flex-direction: column;
            }

            .epicrisis-header__left {
                flex-direction: column;
            }

            .epicrisis-header__actions {
                width: 100%;
                justify-content: flex-start;
            }

            .epicrisis-layout {
                grid-template-columns: 1fr;
            }

            .epicrisis-sidebar {
                position: static;
            }

            .epicrisis-title {
                font-size: 24px;
            }

            .epicrisis-content {
                padding: 20px;
            }
        }

        @media (max-width: 575px) {
            .epicrisis-page {
                padding: 12px;
                border-radius: 12px;
            }

            .epicrisis-card__header {
                flex-direction: column;
                padding: 16px;
            }

            .epicrisis-title {
                font-size: 21px;
            }

            .epicrisis-subtitle {
                font-size: 13px;
            }

            .epicrisis-content {
                padding: 16px;
                font-size: 14px;
            }

            .epicrisis-info-item {
                flex-direction: column;
                gap: 3px;
            }

            .epicrisis-info-value {
                text-align: left;
            }

            .epicrisis-header__actions .btn {
                width: 100%;
            }
        }

        @media print {
            .epicrisis-page {
                background: #fff;
                padding: 0;
            }

            .epicrisis-header__actions,
            .epicrisis-sidebar,
            .btn {
                display: none !important;
            }

            .epicrisis-layout {
                display: block;
            }

            .epicrisis-card {
                border: 0;
                box-shadow: none;
            }

            .epicrisis-card__header {
                padding: 0 0 14px;
                background: #fff;
            }

            .epicrisis-content {
                padding: 0;
                color: #000;
            }
        }
    </style>
@endsection
@section('sub-main')
    <div class="epicrisis-page">
        <div class="epicrisis-header">
            <div class="epicrisis-header__left">
                <div class="epicrisis-title-block">
                    <div class="epicrisis-label">Эпикриз пациента</div>
                    <h1 class="epicrisis-title">Эпикриз от {{ $epicrisis->created_at?->format('d.m.Y') }}</h1>
                    <div class="epicrisis-subtitle">
                        Пациент: {{ $patient->full_name }} · Дата рождения: {{ $patient->birth_at }}
                    </div>
                </div>
            </div>

            <div class="epicrisis-header__actions">
                <button type="button" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer"></i>
                    Печать
                </button>

                <a type="button" href="{{ route('doctors.patients.epicrisis.edit', [$patient->id, $epicrisis->id]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil-square"></i>
                    Редактировать
                </a>

                <a href="{{ route('doctors.patients.medical_card', $patient->id) }}"
                   class="btn btn-outline-secondary btn-sm">
                    ← Назад к карте пациента
                </a>
            </div>
        </div>

        <!-- Основная сетка -->
        <div class="epicrisis-layout">

            <!-- Основной текст эпикриза -->
            <main class="epicrisis-main">
                @if(session()->get('success') == 'ok')
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
                <section class="epicrisis-card">
                    <div class="epicrisis-card__header">
                        <div>
                            <div class="epicrisis-card__label">Основная информация</div>
                            <h2 class="epicrisis-card__title">Клинический эпикриз</h2>
                        </div>
                    </div>

                    <div class="epicrisis-content epicrisis-document">
                        {!! \Illuminate\Support\Str::markdown($epicrisis->text ?? '', [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}
                    </div>
                </section>

            </main>

            <!-- Правая боковая колонка -->
            <aside class="epicrisis-sidebar">

                <section class="epicrisis-side-card">
                    <div class="epicrisis-side-card__title">
                        Сведения об эпикризе
                    </div>

                    <div class="epicrisis-info-list">
                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Дата создания</div>
                            <div class="epicrisis-info-value">{{ $epicrisis->created_at?->format('d.m.Y') }}</div>
                        </div>

                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Автор</div>
                            <div class="epicrisis-info-value">{{ $epicrisis->doctor->full_name }}</div>
                        </div>

                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Должность</div>
                            <div class="epicrisis-info-value">Врач-психиатр</div>
                        </div>

                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Статус</div>
                            <div class="epicrisis-info-value">
                                <span class="badge text-bg-success">Подписан</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="epicrisis-side-card">
                    <div class="epicrisis-side-card__title">
                        Диагноз
                    </div>

                    <div class="epicrisis-diagnosis">
                        <div class="epicrisis-diagnosis__body">
                            <div class="epicrisis-diagnosis__label">Диагноз по МКБ-10</div>

                            <div class="epicrisis-diagnosis__main">
                                <span class="epicrisis-diagnosis__code">
                                    {{ $epicrisis->diagnose->code }}
                                </span>

                                <span class="epicrisis-diagnosis__title">
                                    {{ $epicrisis->diagnose->title }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="epicrisis-side-card">
                    <div class="epicrisis-side-card__title">
                        Пациент
                    </div>

                    <div class="epicrisis-patient">
                        <div class="epicrisis-patient__avatar">
                            {{ Str::substr(Str::ucfirst($patient->name), 0, 1) . Str::substr(Str::ucfirst($patient->surname), 0, 1) }}
                        </div>

                        <div>
                            <div class="epicrisis-patient__name">
                                {{ $patient->full_name }}
                            </div>
                            <div class="epicrisis-patient__meta">
                                {{ $age = Carbon\Carbon::parse($patient->birth_at)->age }} {{ ageWord($age) }}, @if($patient->gender == 'M')
                                    мужcкой
                                @else
                                    женский
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="epicrisis-info-list mt-3">
                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Дата рождения</div>
                            <div class="epicrisis-info-value">{{ $patient->birth_at }}</div>
                        </div>

                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Номер карты</div>
                            <div class="epicrisis-info-value">{{ $patient->medcard_number ?? '-' }}</div>
                        </div>

                        <div class="epicrisis-info-item">
                            <div class="epicrisis-info-label">Лечащий врач</div>
                            <div class="epicrisis-info-value">{{ $patient->doctor->full_name }}</div>
                        </div>
                    </div>
                </section>

                <section class="epicrisis-side-card">
                    <div class="epicrisis-side-card__title">
                        Быстрые действия
                    </div>

                    <div class="epicrisis-actions-list">
                        <a href="#" class="epicrisis-action">
                            <i class="bi bi-printer"></i>
                            Распечатать эпикриз
                        </a>

                        <a href="#" class="epicrisis-action">
                            <i class="bi bi-download"></i>
                            Скачать PDF
                        </a>

                        <a href="{{ route('doctors.patients.epicrisis.edit', [$patient->id, $epicrisis->id]) }}" class="epicrisis-action">
                            <i class="bi bi-pencil-square"></i>
                            Редактировать
                        </a>

                        <a href="#" class="epicrisis-action epicrisis-action--danger">
                            <i class="bi bi-trash3"></i>
                            Удалить
                        </a>
                    </div>
                </section>

            </aside>

        </div>

    </div>
@endsection
