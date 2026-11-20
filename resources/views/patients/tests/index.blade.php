@extends('layouts.patient')

@section('title', 'Тестирования')

@section('assets')
    <style>
        .patient-tests-page { max-width: 1180px; margin: 0 auto; padding: 30px 32px 56px; }
        .patient-tests-header { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:24px; }
        .patient-tests-eyebrow { color:#168879; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:8px; }
        .patient-tests-header h1 { color:#183d3a; font-size:clamp(25px,3vw,34px); font-weight:800; letter-spacing:-.035em; margin:0 0 8px; }
        .patient-tests-header p { color:#71817e; margin:0; max-width:650px; line-height:1.6; }
        .patient-tests-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
        .patient-tests-stat { background:#fff; border:1px solid #e7eeeb; border-radius:17px; padding:17px 19px; box-shadow:0 5px 20px rgba(31,67,60,.035); }
        .patient-tests-stat strong { display:block; color:#183d3a; font-size:27px; line-height:1.2; margin-bottom:5px; }
        .patient-tests-stat span { color:#7b8986; font-size:12px; }
        .patient-tests-tabs { display:flex; gap:8px; border-bottom:1px solid #e4ece8; margin-bottom:20px; }
        .patient-tests-tab { display:inline-flex; align-items:center; gap:9px; padding:13px 16px; color:#71817e; text-decoration:none; font-weight:700; border-bottom:3px solid transparent; margin-bottom:-1px; }
        .patient-tests-tab:hover { color:#168879; }
        .patient-tests-tab.is-active { color:#137f72; border-color:#168879; }
        .patient-tests-tab-count { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:22px; padding:0 6px; border-radius:20px; background:#eef5f2; color:#39756c; font-size:11px; }
        .patient-tests-list { display:grid; gap:13px; }
        .patient-test-card { background:#fff; border:1px solid #e7eeeb; border-radius:17px; padding:19px 20px; box-shadow:0 5px 20px rgba(31,67,60,.035); }
        .patient-test-card__top { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; }
        .patient-test-card h2 { color:#20443f; font-size:17px; font-weight:800; margin:0 0 6px; }
        .patient-test-card p { color:#71817e; font-size:13px; line-height:1.55; margin:0 0 12px; }
        .patient-test-meta { display:flex; flex-wrap:wrap; gap:8px 16px; color:#7d8c88; font-size:12px; }
        .patient-test-meta span { display:inline-flex; align-items:center; gap:6px; }
        .patient-test-status { display:inline-flex; align-items:center; white-space:nowrap; border-radius:30px; padding:6px 10px; background:#f0f4f2; color:#5c716b; font-size:11px; font-weight:800; }
        .patient-test-status.is-progress { background:#e9f5f3; color:#147d70; }
        .patient-test-status.is-review { background:#fff5e6; color:#98681c; }
        .patient-test-status.is-done { background:#edf6ee; color:#3f7c4c; }
        .patient-test-status.is-cancelled { background:#f8eeee; color:#a45454; }
        .patient-test-card__actions { display:flex; justify-content:flex-end; gap:8px; margin-top:15px; }
        .patient-test-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:1px solid #168879; border-radius:10px; padding:9px 14px; background:#168879; color:#fff; text-decoration:none; font-size:13px; font-weight:800; cursor:pointer; }
        .patient-test-button:hover { color:#fff; background:#116f64; border-color:#116f64; }
        .patient-test-button--secondary { background:#fff; color:#168879; }
        .patient-test-button--secondary:hover { color:#116f64; background:#f1f8f6; }
        .patient-test-button:disabled { opacity:.55; cursor:not-allowed; }
        .patient-tests-empty { text-align:center; padding:38px 22px; background:#fff; border:1px dashed #dce8e3; border-radius:17px; }
        .patient-tests-empty__icon { width:52px; height:52px; margin:0 auto 13px; display:grid; place-items:center; border-radius:16px; background:#eff7f4; color:#168879; font-size:22px; }
        .patient-tests-empty h2 { color:#244640; font-size:17px; font-weight:800; margin:0 0 7px; }
        .patient-tests-empty p { color:#7b8986; font-size:13px; max-width:480px; margin:0 auto; line-height:1.6; }
        .patient-tests-section-title { display:flex; justify-content:space-between; align-items:center; gap:12px; margin:26px 0 13px; }
        .patient-tests-section-title h2 { color:#244640; font-size:17px; font-weight:800; margin:0; }
        .patient-tests-note { color:#778681; font-size:12px; line-height:1.55; background:#f5f9f7; border:1px solid #e6efeb; border-radius:12px; padding:12px 14px; margin-bottom:16px; }
        .patient-tests-note i { color:#168879; margin-right:6px; }
        @media (max-width:760px) {
            .patient-tests-page { padding:22px 16px 40px; }
            .patient-tests-header { display:block; }
            .patient-tests-stats { grid-template-columns:1fr 1fr; }
            .patient-tests-stats .patient-tests-stat:last-child { grid-column:1/-1; }
            .patient-tests-tab { flex:1; justify-content:center; padding:12px 7px; font-size:12px; }
            .patient-test-card { padding:16px; }
            .patient-test-card__top { display:block; }
            .patient-test-card__top .patient-test-status { margin-top:8px; }
            .patient-test-card__actions { justify-content:stretch; }
            .patient-test-button { flex:1; }
        }
    </style>
@endsection

@section('content')
    @php
        $typeLabel = static function ($type): string {
            return match ((string) $type) {
                'image' => 'Тест с изображениями',
                'sort', 'card_sort' => 'Сортировка карточек',
                default => 'Опросник',
            };
        };

        $questionCount = static function ($test): int {
            if (!$test) {
                return 0;
            }

            $type = mb_strtolower(trim((string) ($test->type ?? 'questionnaire')));

            if ($type === 'image') {
                return (int) ($test->test_cards_count ?? $test->testCards?->count() ?? 0);
            }

            if (in_array($type, ['sort', 'card_sort'], true)) {
                return (int) ($test->sort_cards_count ?? $test->sortCards?->count() ?? 0);
            }

            // Вопросы связаны с тестом через test_items.test_id.
            // section_id может быть NULL, поэтому считаем через Test::items.
            return (int) ($test->items?->count() ?? 0);
        };

        $assignmentStatus = static function ($assignment): array {
            $session = $assignment->session;
            $status = (string) ($session?->status ?? $assignment->status);

            if (!$session && in_array((string) $assignment->status, ['started', 'in_progress'], true)) {
                return ['Ожидает прохождения', ''];
            }

            if ($session && $session->in_progress && data_get($session->context, 'source') === 'doctor_assigned_patient_portal') {
                return ['В процессе', 'is-progress'];
            }

            return match ($status) {
                'assigned' => ['Ожидает прохождения', ''],
                'awaiting_unlock' => ['Ожидает запуска', ''],
                'started', 'in_progress' => ['Начато в клинике', 'is-progress'],
                'submitted' => ['Отправлено врачу', 'is-review'],
                'in_review' => ['На проверке у врача', 'is-review'],
                'coding_done' => ['Обработка завершена', 'is-review'],
                'completed', 'complete', 'finished' => ['Завершено', 'is-done'],
                'cancelled' => ['Отменено', 'is-cancelled'],
                'timeout' => ['Время прохождения истекло', 'is-cancelled'],
                default => ['Назначено врачом', ''],
            };
        };
    @endphp

    <div class="patient-tests-page">
        <header class="patient-tests-header">
            <div>
                <div class="patient-tests-eyebrow">Личный кабинет пациента</div>
                <h1>Тестирования</h1>
                <p>Проходите назначенные врачом тесты или самостоятельно выбирайте тесты, не принадлежащие клиникам. Вы можете в любой момент перейти в другой раздел и продолжить позже.</p>
            </div>
        </header>

        <section class="patient-tests-stats" aria-label="Статистика тестирований">
            <div class="patient-tests-stat">
                <strong>{{ $assignedActiveCount }}</strong>
                <span>Назначены и ожидают прохождения</span>
            </div>
            <div class="patient-tests-stat">
                <strong>{{ $assignedCompletedCount }}</strong>
                <span>Назначенные тесты завершены</span>
            </div>
            <div class="patient-tests-stat">
                <strong>{{ $selfSessions->where('status', 'completed')->count() }}</strong>
                <span>Самостоятельные прохождения</span>
            </div>
        </section>

        <nav class="patient-tests-tabs" aria-label="Тип тестирования">
            <a
                href="{{ route('patients.tests.index', ['tab' => 'assigned']) }}"
                class="patient-tests-tab {{ $selectedTab === 'assigned' ? 'is-active' : '' }}"
                @if($selectedTab === 'assigned') aria-current="page" @endif
            >
                <i class="bi bi-person-check"></i>
                Назначенные врачом
                <span class="patient-tests-tab-count">{{ $assignments->count() }}</span>
            </a>
            <a
                href="{{ route('patients.tests.index', ['tab' => 'self']) }}"
                class="patient-tests-tab {{ $selectedTab === 'self' ? 'is-active' : '' }}"
                @if($selectedTab === 'self') aria-current="page" @endif
            >
                <i class="bi bi-person"></i>
                Самостоятельное тестирование
                <span class="patient-tests-tab-count">{{ $catalog->count() }}</span>
            </a>
        </nav>

        @if($selectedTab === 'assigned')
            @if($assignments->isNotEmpty())
                <div class="patient-tests-list">
                    @foreach($assignments as $assignment)
                        @php
                            $test = $assignment->test;
                            $testQuestionCount = $questionCount($test);
                            $session = $assignment->session;
                            [$statusLabel, $statusClass] = $assignmentStatus($assignment);
                            $source = (string) data_get($session?->context, 'source', '');
                            $canContinue = $session
                                && $session->in_progress
                                && $session->status === 'in_progress'
                                && $source === 'doctor_assigned_patient_portal';
                            $canStart = !$session
                                && !(bool) $assignment->is_kiosk
                                && in_array((string) $assignment->status, ['assigned', 'awaiting_unlock', 'started', 'in_progress'], true);
                            $canSeeResult = $session
                                && in_array((string) $session->status, ['completed', 'complete', 'finished'], true);
                            $isKioskActive = (bool) $assignment->is_kiosk
                                || ($session && $session->in_progress && $source !== 'doctor_assigned_patient_portal');
                        @endphp

                        <article class="patient-test-card">
                            <div class="patient-test-card__top">
                                <div>
                                    <h2>{{ $test?->name ?: 'Тестирование' }}</h2>
                                    @if($test?->description)
                                        <p>{{ $test->description }}</p>
                                    @else
                                        <p>Тест назначен вашим лечащим врачом.</p>
                                    @endif
                                    <div class="patient-test-meta">
                                        <span><i class="bi bi-ui-checks"></i> {{ $typeLabel($test?->type) }}</span>
                                        @if($testQuestionCount > 0)
                                            <span><i class="bi bi-list-ol"></i> Вопросов: {{ $testQuestionCount }}</span>
                                        @else
                                            <span><i class="bi bi-exclamation-circle"></i> Вопросы ещё не добавлены</span>
                                        @endif
                                        @if($test?->estimated_minutes)
                                            <span><i class="bi bi-clock"></i> {{ $test->estimated_minutes }} мин.</span>
                                        @endif
                                        <span><i class="bi bi-calendar3"></i> Назначено {{ optional($assignment->created_at)->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                                <span class="patient-test-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>

                            <div class="patient-test-card__actions">
                                @if($canContinue)
                                    <a class="patient-test-button" href="{{ route('patients.tests.run', $session) }}">
                                        Продолжить <i class="bi bi-arrow-right"></i>
                                    </a>
                                @elseif($canStart)
                                    <form method="POST" action="{{ route('patients.tests.assignments.start', $assignment) }}">
                                        @csrf
                                        <button class="patient-test-button" type="submit">
                                            Начать тест <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </form>
                                @elseif($canSeeResult)
                                    <a class="patient-test-button patient-test-button--secondary" href="{{ route('patients.tests.result', $session) }}">
                                        Результат <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                @elseif($isKioskActive)
                                    <span class="patient-tests-note mb-0">Это тестирование проводится в клинике. Для продолжения обратитесь к сотруднику.</span>
                                @elseif(in_array((string) ($session?->status ?? $assignment->status), ['submitted', 'in_review', 'coding_done'], true))
                                    <span class="patient-tests-note mb-0">Ответы переданы врачу. Результат появится после завершения обработки.</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="patient-tests-empty">
                    <div class="patient-tests-empty__icon"><i class="bi bi-clipboard2-check"></i></div>
                    <h2>Пока нет назначенных тестирований</h2>
                    <p>Когда врач назначит вам тест, он появится в этом разделе. Самостоятельные опросники доступны во второй вкладке.</p>
                </div>
            @endif
        @else
            <div class="patient-tests-note">
                <i class="bi bi-shield-check"></i>
                Самостоятельные прохождения сохраняются только в вашем личном кабинете. Они не отправляются врачу, не попадают в медицинскую карту и не используются в обработке врачебных назначений. Тесты, принадлежащие клиникам, в этом каталоге не отображаются.
            </div>

            <div class="patient-tests-section-title">
                <h2>Доступные тесты</h2>
                <span class="text-muted small">{{ $catalog->count() }} в каталоге</span>
            </div>

            @if($catalog->isNotEmpty())
                <div class="patient-tests-list">
                    @foreach($catalog as $test)
                        @php
                            $activeSession = $activeSelfByTest->get($test->id);
                            $testQuestionCount = $questionCount($test);
                            $hasRunnableContent = $testQuestionCount > 0;
                        @endphp
                        <article class="patient-test-card">
                            <div class="patient-test-card__top">
                                <div>
                                    <h2>{{ $test->name }}</h2>
                                    @if($test->description)
                                        <p>{{ $test->description }}</p>
                                    @endif
                                    <div class="patient-test-meta">
                                        <span><i class="bi bi-ui-checks"></i> {{ $typeLabel($test->type) }}</span>
                                        @if($questionCount($test) > 0)
                                            <span><i class="bi bi-list-ol"></i> Вопросов: {{ $questionCount($test) }}</span>
                                        @endif
                                        @if($test->estimated_minutes)
                                            <span><i class="bi bi-clock"></i> {{ $test->estimated_minutes }} мин.</span>
                                        @endif
                                        <span title="Количество завершённых прохождений всеми пациентами">
                                            <i class="bi bi-check2-circle"></i>
                                            Завершённых прохождений: {{ (int) ($test->completed_runs_count ?? 0) }}
                                        </span>
                                    </div>
                                </div>
                                @if($activeSession)
                                    <span class="patient-test-status is-progress">Можно продолжить</span>
                                @elseif($hasRunnableContent)
                                    <span class="patient-test-status">Доступен</span>
                                @else
                                    <span class="patient-test-status is-review">Не подготовлен</span>
                                @endif
                            </div>

                            <div class="patient-test-card__actions">
                                @if($activeSession)
                                    <a class="patient-test-button" href="{{ route('patients.tests.run', $activeSession) }}">
                                        Продолжить <i class="bi bi-arrow-right"></i>
                                    </a>
                                @elseif($hasRunnableContent)
                                    <form method="POST" action="{{ route('patients.tests.self.start', $test) }}">
                                        @csrf
                                        <button class="patient-test-button" type="submit">
                                            Пройти самостоятельно <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="patient-tests-note mb-0">
                                        Тест есть в справочнике, но в нём пока нет вопросов или карточек для прохождения.
                                    </span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="patient-tests-empty">
                    <div class="patient-tests-empty__icon"><i class="bi bi-journal-medical"></i></div>
                    <h2>Пока нет доступных самостоятельных тестов</h2>
                    <p>Здесь отображаются все тесты, не принадлежащие клиникам. Тесты, связанные с клиниками, не доступны для самостоятельного прохождения.</p>
                </div>
            @endif

            <div class="patient-tests-section-title">
                <h2>История самостоятельных прохождений</h2>
            </div>

            @php
                $completedSelfSessions = $selfSessions->filter(
                    static fn ($session) => in_array($session->status, ['completed', 'complete', 'finished'], true)
                );
            @endphp

            @if($completedSelfSessions->isNotEmpty())
                <div class="patient-tests-list">
                    @foreach($completedSelfSessions as $selfSession)
                        <article class="patient-test-card">
                            <div class="patient-test-card__top">
                                <div>
                                    <h2>{{ $selfSession->test?->name ?: 'Тестирование' }}</h2>
                                    <div class="patient-test-meta">
                                        <span><i class="bi bi-calendar-check"></i> {{ optional($selfSession->completed_at)->format('d.m.Y H:i') ?: optional($selfSession->created_at)->format('d.m.Y H:i') }}</span>
                                        <span><i class="bi bi-person"></i> Самостоятельно</span>
                                    </div>
                                </div>
                                <span class="patient-test-status is-done">Завершено</span>
                            </div>
                            <div class="patient-test-card__actions">
                                <a class="patient-test-button patient-test-button--secondary" href="{{ route('patients.tests.result', $selfSession) }}">
                                    Посмотреть результат <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="patient-tests-empty">
                    <div class="patient-tests-empty__icon"><i class="bi bi-clock-history"></i></div>
                    <h2>История пока пуста</h2>
                    <p>Завершённые самостоятельные тесты будут сохраняться здесь. Вы сможете возвращаться к результатам и сравнивать прохождения.</p>
                </div>
            @endif
        @endif
    </div>
@endsection
