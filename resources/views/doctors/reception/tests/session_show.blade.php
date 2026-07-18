@extends('doctors.reception')
@section('title', 'Результаты тестирования')
@section('assets')
    <style>
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

        .test-result-page {
            min-height: calc(100vh - 70px);
            padding: 24px 24px 42px;
            background: var(--hospital-bg);
            color: var(--hospital-text);
        }

        .test-result-container {
            width: min(1480px, 100%);
            margin: 0 auto;
        }

        .test-result-toolbar {
            position: sticky;
            top: 0;
            z-index: 1015;
            margin: -24px -24px 22px;
            padding: 12px 24px;
            border-bottom: 1px solid var(--hospital-border);
            background: rgba(244, 247, 251, .94);
            backdrop-filter: blur(12px);
        }

        .test-result-toolbar__inner {
            width: min(1480px, 100%);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .test-result-toolbar__title {
            font-size: 15px;
            font-weight: 800;
            color: var(--hospital-text);
        }

        .test-result-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .test-result-btn {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            padding: 9px 15px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 750;
            line-height: 1.2;
            text-decoration: none;
            transition: .18s ease;
            cursor: pointer;
        }

        .test-result-btn--primary {
            color: #fff;
            background: var(--hospital-primary);
            border-color: var(--hospital-primary);
        }

        .test-result-btn--primary:hover {
            color: #fff;
            background: var(--hospital-primary-dark);
            border-color: var(--hospital-primary-dark);
        }

        .test-result-btn--success {
            color: #fff;
            background: var(--hospital-success);
            border-color: var(--hospital-success);
        }

        .test-result-btn--success:hover {
            color: #fff;
            background: #12823c;
            border-color: #12823c;
        }

        .test-result-btn--secondary {
            color: #344054;
            background: #fff;
            border-color: #d7e0e7;
        }

        .test-result-btn--secondary:hover {
            color: #111827;
            background: #f8fafc;
            border-color: #c5d0d9;
        }

        .test-result-btn--danger {
            color: #b91c1c;
            background: #fff7f7;
            border-color: #fecaca;
        }

        .test-result-btn--danger:hover {
            color: #991b1b;
            background: #feecec;
        }

        .test-result-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            padding: 28px;
            border: 1px solid var(--hospital-border);
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #effafb 100%);
            box-shadow: 0 16px 42px rgba(15, 23, 42, .06);
        }

        .test-result-hero::after {
            content: '';
            position: absolute;
            top: -85px;
            right: -65px;
            width: 210px;
            height: 210px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 183, 198, .18) 0%, rgba(16, 183, 198, 0) 70%);
            pointer-events: none;
        }

        .test-result-hero__row {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
        }

        .test-result-kicker {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            margin-bottom: 12px;
            padding: 5px 11px;
            border-radius: 999px;
            background: #e8f8fa;
            color: var(--hospital-primary-dark);
            font-size: 13px;
            font-weight: 800;
        }

        .test-result-title {
            margin: 0;
            color: var(--hospital-text);
            font-size: clamp(27px, 3vw, 38px);
            line-height: 1.14;
            font-weight: 850;
            letter-spacing: -.035em;
        }

        .test-result-code {
            color: var(--hospital-muted);
            font-weight: 700;
            white-space: nowrap;
        }

        .test-result-description {
            max-width: 900px;
            margin-top: 12px;
            color: var(--hospital-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .test-result-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            margin-top: 15px;
            color: #667085;
            font-size: 13px;
        }

        .test-result-status {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .test-result-status--success { background: #eaf8ef; color: #15803d; }
        .test-result-status--warning { background: #fff7e6; color: #b45309; }
        .test-result-status--danger { background: #feecec; color: #b91c1c; }
        .test-result-status--info { background: #e8f8fa; color: var(--hospital-primary-dark); }
        .test-result-status--neutral { background: #eef2f7; color: #475569; }

        .test-result-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .test-result-stat {
            position: relative;
            overflow: hidden;
            min-height: 112px;
            padding: 19px 20px;
            border: 1px solid var(--hospital-border);
            border-radius: 19px;
            background: var(--hospital-card);
            box-shadow: 0 12px 34px rgba(15, 23, 42, .05);
        }

        .test-result-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--hospital-primary), var(--hospital-primary-light));
        }

        .test-result-stat--success::before { background: var(--hospital-success); }
        .test-result-stat--warning::before { background: var(--hospital-warning); }
        .test-result-stat--neutral::before { background: #94a3b8; }

        .test-result-stat__label {
            color: var(--hospital-muted);
            font-size: 13px;
            font-weight: 750;
        }

        .test-result-stat__value {
            margin-top: 8px;
            color: var(--hospital-text);
            font-size: 31px;
            line-height: 1;
            font-weight: 850;
        }

        .test-result-stat__hint {
            margin-top: 7px;
            color: #8b95a1;
            font-size: 12px;
        }

        .test-result-layout {
            display: grid;
            grid-template-columns: minmax(280px, 370px) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }

        .test-result-panel {
            overflow: hidden;
            border: 1px solid var(--hospital-border);
            border-radius: 20px;
            background: var(--hospital-card);
            box-shadow: 0 14px 38px rgba(15, 23, 42, .055);
        }

        .test-result-panel + .test-result-panel {
            margin-top: 16px;
        }

        .test-result-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 60px;
            padding: 16px 19px;
            border-bottom: 1px solid var(--hospital-border);
            background: linear-gradient(180deg, #fff 0%, #fbfcfd 100%);
        }

        .test-result-panel__title {
            margin: 0;
            color: var(--hospital-text);
            font-size: 17px;
            font-weight: 820;
        }

        .test-result-panel__body {
            padding: 19px;
        }

        .test-result-count {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            padding: 4px 10px;
            border: 1px solid #dce5eb;
            border-radius: 999px;
            background: #f8fafc;
            color: #52606d;
            font-size: 12px;
            font-weight: 750;
            white-space: nowrap;
        }

        .test-result-note {
            padding: 15px 16px;
            border: 1px solid #cfe9ed;
            border-radius: 15px;
            background: #eef8fa;
            color: #285b63;
            font-size: 14px;
            line-height: 1.55;
        }

        .test-result-note--warning {
            border-color: #fde2ad;
            background: #fff8e9;
            color: #8a5a0a;
        }

        .test-result-note--danger {
            border-color: #fecaca;
            background: #fff1f1;
            color: #991b1b;
        }

        .test-result-list {
            display: grid;
            gap: 12px;
        }

        .test-result-question {
            padding: 17px;
            border: 1px solid #e6edf2;
            border-radius: 16px;
            background: #fff;
        }

        .test-result-question__top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .test-result-number {
            display: inline-flex;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #eef8fa;
            color: var(--hospital-primary-dark);
            font-size: 13px;
            font-weight: 850;
        }

        .test-result-question__text {
            padding-top: 4px;
            color: var(--hospital-text);
            font-size: 15px;
            line-height: 1.5;
            font-weight: 720;
        }

        .test-result-answer {
            margin-top: 13px;
            padding: 13px 14px;
            border: 1px solid #dce7ec;
            border-radius: 13px;
            background: #f8fafc;
            color: #23303d;
            font-size: 14px;
            line-height: 1.55;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .test-result-answer__label {
            display: block;
            margin-bottom: 4px;
            color: var(--hospital-muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .test-result-answer--empty {
            border-color: #f8d79a;
            background: #fff8e9;
            color: #a15c08;
        }

        .test-result-media-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .test-result-media-card {
            overflow: hidden;
            border: 1px solid var(--hospital-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .05);
        }

        .test-result-media-card__visual {
            display: grid;
            min-height: 290px;
            place-items: center;
            padding: 16px;
            border-bottom: 1px solid var(--hospital-border);
            background: #f8fafc;
        }

        .test-result-media-card__visual img {
            display: block;
            width: 100%;
            height: clamp(250px, 32vw, 440px);
            object-fit: contain;
            object-position: center;
            border-radius: 12px;
            background: #fff;
        }

        .test-result-media-card__missing {
            display: grid;
            width: 100%;
            min-height: 250px;
            place-items: center;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            color: #94a3b8;
            text-align: center;
        }

        .test-result-media-card__body {
            padding: 18px;
        }

        .test-result-media-card__heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .test-result-media-card__title {
            margin: 0;
            color: var(--hospital-text);
            font-size: 17px;
            line-height: 1.35;
            font-weight: 820;
        }

        .test-result-media-card__question {
            margin-top: 8px;
            color: #52606d;
            font-size: 14px;
            line-height: 1.5;
        }

        .test-result-rank-viewport {
            width: 100%;
            overflow-x: auto;
            padding: 4px 2px 12px;
            scrollbar-width: thin;
            scrollbar-color: #b9dfe4 transparent;
        }

        .test-result-rank-viewport::-webkit-scrollbar {
            height: 8px;
        }

        .test-result-rank-viewport::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: #b9dfe4;
        }

        .test-result-rank-axis {
            width: 100%;
            min-width: max(100%, var(--rank-min-width));
        }

        .test-result-rank-axis__labels {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            gap: 16px;
            align-items: center;
            padding: 13px 15px;
            border: 1px dashed #b9dfe4;
            border-radius: 15px;
            background: #eef8fa;
            color: #285b63;
            font-size: 13px;
            font-weight: 800;
        }

        .test-result-rank-axis__labels > :last-child {
            text-align: right;
        }

        .test-result-rank-axis__direction {
            color: #667085;
            font-size: 11px;
            font-weight: 750;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .test-result-rank-columns {
            position: relative;
            display: grid;
            grid-template-columns: repeat(var(--rank-count), minmax(150px, 1fr));
            gap: 14px;
            align-items: start;
            margin-top: 24px;
        }

        .test-result-rank-columns::before {
            content: '';
            position: absolute;
            z-index: 0;
            top: 17px;
            left: 22px;
            right: 24px;
            height: 7px;
            border-radius: 999px;
            background: linear-gradient(
                90deg,
                var(--hospital-primary) 0%,
                var(--hospital-primary-light) 52%,
                var(--hospital-warning) 100%
            );
        }

        .test-result-rank-columns::after {
            content: '';
            position: absolute;
            z-index: 1;
            top: 12px;
            right: 18px;
            width: 13px;
            height: 13px;
            border-top: 3px solid var(--hospital-warning);
            border-right: 3px solid var(--hospital-warning);
            transform: rotate(45deg);
        }

        .test-result-rank-column {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-rows: 38px 26px auto;
            min-width: 0;
            justify-items: center;
        }

        .test-result-rank-marker {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border: 4px solid #fff;
            border-radius: 50%;
            background: var(--hospital-primary);
            color: #fff;
            box-shadow: 0 5px 14px rgba(4, 134, 150, .24);
            font-size: 14px;
            font-weight: 850;
        }

        .test-result-rank-connector {
            width: 2px;
            height: 26px;
            background: #b9dfe4;
        }

        .test-result-rank-card {
            width: 100%;
            min-width: 0;
            overflow: hidden;
            border: 1px solid #e3ebf0;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 9px 24px rgba(15, 23, 42, .055);
        }

        .test-result-rank-card__visual {
            display: grid;
            width: 100%;
            height: 128px;
            place-items: center;
            overflow: hidden;
            border-bottom: 1px solid #e3e9ef;
            background: #f8fafc;
        }

        .test-result-rank-card__visual img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #fff;
        }

        .test-result-rank-card__color {
            width: 100%;
            height: 100%;
        }

        .test-result-rank-card__text {
            padding: 14px;
            color: #344054;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .test-result-rank-card__body {
            padding: 12px 13px 14px;
            text-align: center;
        }

        .test-result-rank-card__title {
            color: var(--hospital-text);
            font-size: 14px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .test-result-rank-card__description {
            margin-top: 5px;
            color: var(--hospital-muted);
            font-size: 12px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .test-result-score {
            color: var(--hospital-text);
            font-size: 28px;
            line-height: 1;
            font-weight: 850;
        }

        .test-result-progress {
            height: 11px;
            overflow: hidden;
            border-radius: 999px;
            background: #e8eef2;
        }

        .test-result-progress__bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--hospital-primary), var(--hospital-primary-light));
        }

        .test-result-scale-row {
            padding: 14px 0;
            border-bottom: 1px solid #edf1f4;
        }

        .test-result-scale-row:first-child { padding-top: 0; }
        .test-result-scale-row:last-child { padding-bottom: 0; border-bottom: 0; }

        .test-result-scale-row__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .test-result-interpretation {
            color: #344054;
            font-size: 14px;
            line-height: 1.65;
        }

        .test-result-interpretation ul {
            margin-top: 10px;
            padding-left: 20px;
        }

        .test-result-empty {
            padding: 45px 22px;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: #f8fafc;
            color: var(--hospital-muted);
            text-align: center;
        }

        .test-result-alert {
            margin-bottom: 18px;
            padding: 15px 17px;
            border: 1px solid transparent;
            border-radius: 15px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
        }

        .test-result-alert--danger {
            border-color: #fecaca;
            background: #fff1f1;
            color: #991b1b;
        }

        .test-result-alert ul {
            margin: 8px 0 0;
        }

        @media (max-width: 1199.98px) {
            .test-result-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .test-result-layout {
                grid-template-columns: 1fr;
            }

            .test-result-sidebar {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .test-result-sidebar .test-result-panel + .test-result-panel {
                margin-top: 0;
            }
        }

        @media (max-width: 767.98px) {
            .test-result-page {
                padding: 16px 12px 30px;
            }

            .test-result-toolbar {
                position: static;
                margin: -16px -12px 16px;
                padding: 11px 12px;
            }

            .test-result-toolbar__inner,
            .test-result-hero__row {
                align-items: stretch;
                flex-direction: column;
            }

            .test-result-actions {
                justify-content: flex-start;
            }

            .test-result-btn {
                flex: 1 1 auto;
            }

            .test-result-hero {
                padding: 21px;
                border-radius: 19px;
            }

            .test-result-title {
                font-size: 27px;
            }

            .test-result-stats {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .test-result-stat {
                min-height: 100px;
                padding: 16px;
            }

            .test-result-rank-axis__labels {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }

            .test-result-rank-axis__direction {
                display: none;
            }

            .test-result-rank-card__visual {
                height: 108px;
            }
        }
    </style>
@endsection

@section('sub-main')
    <div class="test-result-page">
        <div class="test-result-toolbar">
            <div class="test-result-toolbar__inner">
                <div class="test-result-toolbar__title">Ответы пациента</div>

                <div class="test-result-actions">
                    <a class="test-result-btn test-result-btn--secondary" href="{{ $backUrl }}">
                        Назад
                    </a>

                    @if($canRestart ?? false)
                        <form method="POST"
                              action="{{ route('doctors.patients.sessions.restart', $session) }}"
                              class="m-0"
                              onsubmit="return confirm('Начать тест заново? Все ответы будут удалены.')">
                            @csrf
                            <button type="submit" class="test-result-btn test-result-btn--danger">
                                Начать заново
                            </button>
                        </form>
                    @endif

                    @if($session->status === 'submitted')
                        @if(!data_get($session->context, 'final_pin_ok'))
                            <button type="button"
                                    class="test-result-btn test-result-btn--primary"
                                    id="btn-final-pin"
                                    data-url="{{ route('doctors.tests.final_pin.form', $session) }}">
                                Завершить и разблокировать
                            </button>
                        @else
                            <form method="POST"
                                  action="{{ route('doctors.patients.sessions.finish', $session->id) }}"
                                  class="m-0">
                                @csrf
                                <button type="submit" class="test-result-btn test-result-btn--success">
                                    Завершить тест и перейти в карту
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="test-result-container">
            @if($errors->any())
                <div class="test-result-alert test-result-alert--danger">
                    <strong>Не удалось выполнить действие.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="test-result-hero">
                <div class="test-result-hero__row">
                    <div>
                        <div class="test-result-kicker">{{ $typeLabel }}</div>

                        <h1 class="test-result-title">
                            {{ $test->name ?? 'Тест' }}
                            @if(!empty($test->code))
                                <span class="test-result-code">({{ $test->code }})</span>
                            @endif
                        </h1>

                        @if(!empty($test->description))
                            <div class="test-result-description">
                                {{ $test->description }}
                            </div>
                        @endif

                        <div class="test-result-meta">
                            <span>Сессия #{{ $session->id }}</span>
                            <span>Назначено: {{ optional($session->created_at)->format('d.m.Y H:i') ?? '—' }}</span>
                            @if($session->completed_at)
                                <span>Завершено: {{ optional($session->completed_at)->format('d.m.Y H:i') }}</span>
                            @endif
                        </div>
                    </div>

                    <span class="test-result-status test-result-status--{{ $statusMeta['class'] }}">
                        {{ $statusMeta['label'] }}
                    </span>
                </div>
            </section>

            <section class="test-result-stats">
                <div class="test-result-stat">
                    <div class="test-result-stat__label">{{ $statLabels['total'] }}</div>
                    <div class="test-result-stat__value">{{ $displayTotal }}</div>
                    <div class="test-result-stat__hint">Объём теста</div>
                </div>

                <div class="test-result-stat test-result-stat--success">
                    <div class="test-result-stat__label">{{ $statLabels['answered'] }}</div>
                    <div class="test-result-stat__value">{{ $displayAnswered }}</div>
                    <div class="test-result-stat__hint">Сохранённый результат</div>
                </div>

                <div class="test-result-stat test-result-stat--warning">
                    <div class="test-result-stat__label">{{ $statLabels['missed'] }}</div>
                    <div class="test-result-stat__value">{{ $displayMissed }}</div>
                    <div class="test-result-stat__hint">Без сохранённого ответа</div>
                </div>

                <div class="test-result-stat test-result-stat--neutral">
                    <div class="test-result-stat__label">Тип результата</div>
                    <div class="test-result-stat__value" style="font-size: 20px; line-height: 1.25;">
                        {{ $typeLabel }}
                    </div>
                    <div class="test-result-stat__hint">Формат прохождения</div>
                </div>
            </section>

            @if($isImageTest)
                <section class="test-result-panel">
                    <div class="test-result-panel__header">
                        <h2 class="test-result-panel__title">Карточки и ответы пациента</h2>
                        <span class="test-result-count">{{ $normalizedCards->count() }} карточек</span>
                    </div>

                    <div class="test-result-panel__body">
                        @if(!empty($test->instructions))
                            <div class="test-result-note mb-3">
                                <strong>Инструкция пациенту:</strong> {{ $test->instructions }}
                            </div>
                        @endif

                        @if($normalizedCards->isEmpty())
                            <div class="test-result-empty">
                                Карточки для этого теста не найдены.
                            </div>
                        @else
                            <div class="test-result-media-grid">
                                @foreach($normalizedCards as $index => $card)
                                    <article class="test-result-media-card">
                                        <div class="test-result-media-card__visual">
                                            @if($card['image_url'] !== '')
                                                <img src="{{ $card['image_url'] }}"
                                                     alt="{{ $card['title'] }}"
                                                     loading="lazy">
                                            @else
                                                <div class="test-result-media-card__missing">
                                                    Изображение карточки отсутствует
                                                </div>
                                            @endif
                                        </div>

                                        <div class="test-result-media-card__body">
                                            <div class="test-result-media-card__heading">
                                                <h3 class="test-result-media-card__title">
                                                    {{ $card['title'] }}
                                                </h3>
                                                <span class="test-result-count">№ {{ $index + 1 }}</span>
                                            </div>

                                            @if($card['question'] !== '')
                                                <div class="test-result-media-card__question">
                                                    {{ $card['question'] }}
                                                </div>
                                            @endif

                                            @if($card['has_answer'])
                                                <div class="test-result-answer">
                                                    <span class="test-result-answer__label">Ответ пациента</span>
                                                    {!! nl2br(e($card['answer_text'])) !!}
                                                </div>
                                            @else
                                                <div class="test-result-answer test-result-answer--empty">
                                                    <span class="test-result-answer__label">Ответ пациента</span>
                                                    Нет ответа
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @elseif($isCardSort)
                <section class="test-result-panel">
                    <div class="test-result-panel__header">
                        <h2 class="test-result-panel__title">Порядок карточек пациента</h2>
                        <span class="test-result-count">{{ $orderedCards->count() }} карточек</span>
                    </div>

                    <div class="test-result-panel__body">
                        @if(!$hasSortOrder)
                            <div class="test-result-note test-result-note--warning mb-3">
                                В сессии не найден сохранённый порядок карточек.
                                Ниже показан исходный порядок теста.
                            </div>
                        @endif

                        @if($orderedCards->isEmpty())
                            <div class="test-result-empty">
                                Карточки для сортировки не найдены.
                            </div>
                        @else
                            <div class="test-result-rank-viewport">
                                <div
                                    class="test-result-rank-axis"
                                    style="
                                        --rank-count: {{ max(1, $orderedCards->count()) }};
                                        --rank-min-width: {{ max(1, $orderedCards->count()) * 170 }}px;
                                    "
                                >
                                    <div class="test-result-rank-axis__labels">
                                        <span>{{ $leftLabel }}</span>

                                        <span class="test-result-rank-axis__direction">
                                            Шкала предпочтения
                                        </span>

                                        <span>{{ $rightLabel }}</span>
                                    </div>

                                    <div class="test-result-rank-columns">
                                        @foreach($orderedCards as $index => $card)
                                            <article class="test-result-rank-column">
                                                <div
                                                    class="test-result-rank-marker"
                                                    title="Позиция {{ $index + 1 }}"
                                                >
                                                    {{ $index + 1 }}
                                                </div>

                                                <div class="test-result-rank-connector"></div>

                                                <div class="test-result-rank-card">
                                                    <div class="test-result-rank-card__visual">
                                                        @if($card['type'] === 'color' && $card['color'] !== '')
                                                            <div
                                                                class="test-result-rank-card__color"
                                                                style="background-color: {{ $card['color'] }}"
                                                            ></div>
                                                        @elseif($card['image_url'] !== '')
                                                            <img
                                                                src="{{ $card['image_url'] }}"
                                                                alt="{{ $card['title'] }}"
                                                                loading="lazy"
                                                            >
                                                        @else
                                                            <div class="test-result-rank-card__text">
                                                                {{
                                                                    $card['text'] !== ''
                                                                        ? $card['text']
                                                                        : $card['title']
                                                                }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="test-result-rank-card__body">
                                                        <div class="test-result-rank-card__title">
                                                            {{ $card['title'] }}
                                                        </div>

                                                        @if($card['text'] !== '' && $card['type'] !== 'text')
                                                            <div class="test-result-rank-card__description">
                                                                {{ $card['text'] }}
                                                            </div>
                                                        @elseif($card['question'] !== '')
                                                            <div class="test-result-rank-card__description">
                                                                {{ $card['question'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @else
                @forelse($questionSections as $section)
                    <section class="test-result-panel">
                        <div class="test-result-panel__header">
                            <h2 class="test-result-panel__title">{{ $section['title'] }}</h2>
                            <span class="test-result-count">
                                {{ $section['items_count'] }} вопросов
                            </span>
                        </div>

                        <div class="test-result-panel__body">
                            <div class="test-result-list">
                                @foreach($section['items'] as $index => $item)
                                    <article class="test-result-question">
                                        <div class="test-result-question__top">
                                            <span class="test-result-number">{{ $index + 1 }}</span>
                                            <div class="test-result-question__text">
                                                {{ $item['text'] }}
                                            </div>
                                        </div>

                                        @if($item['has_answer'])
                                            <div class="test-result-answer">
                                                <span class="test-result-answer__label">Ответ пациента</span>
                                                {!! nl2br(e($item['answer_text'])) !!}
                                            </div>
                                        @else
                                            <div class="test-result-answer test-result-answer--empty">
                                                <span class="test-result-answer__label">Ответ пациента</span>
                                                Нет ответа
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @empty
                    <div class="test-result-empty">
                        Вопросы или ответы для этого теста не найдены.
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    <script>
        document.getElementById('btn-final-pin')?.addEventListener('click', function () {
            const url = this.dataset.url;

            if (url) {
                window.location.href = url;
            }
        });
    </script>
@endsection
