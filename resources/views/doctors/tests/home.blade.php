@extends('doctors.tests')
@section('title', 'Результаты тестов')
@section('sub-main')
    <div class="test-results-page">
        <div class="test-results-header">
            <div class="test-results-title">
                <h1>Последние результаты тестирования</h1>
                <p>
                    На этой странице отображаются пациенты, которые недавно прошли психологические или клинические
                    тесты.
                    Отсюда можно быстро открыть медицинскую карту пациента, посмотреть результат и перейти к подробной
                    интерпретации.
                </p>
            </div>

            <div class="test-results-period">
                Последние прохождения
            </div>
        </div>

        <div class="test-results-stats">
            <div class="test-stat-card">
                <div class="test-stat-label">Всего результатов</div>
                <div class="test-stat-value">{{ $totalResults }}</div>
                <div class="test-stat-hint">За выбранный период</div>
            </div>

            <div class="test-stat-card">
                <div class="test-stat-label">Сегодня</div>
                <div class="test-stat-value">{{ $todayResults }}</div>
                <div class="test-stat-hint">Тестов пройдено сегодня</div>
            </div>

            <div class="test-stat-card">
                <div class="test-stat-label">Пациентов</div>
                <div class="test-stat-value">{{ $uniquePatients }}</div>
                <div class="test-stat-hint">Уникальных пациентов</div>
            </div>

            <div class="test-stat-card">
                <div class="test-stat-label">Требуют внимания</div>
                <div class="test-stat-value">{{ $attentionResults }}</div>
                <div class="test-stat-hint">По результатам тестирования</div>
            </div>
        </div>
        @if($errors->any())
            <div class="alert alert-danger">
                @if (app()->hasDebugModeEnabled())
                    При данной операции совершены следующие ошибки:
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @else
                    Произошла ошибка. Обратитесь к Администратору.
                @endif
            </div>
        @endif
        @if(session()->has('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif
        <div class="test-results-panel">
            <div class="test-results-panel-header">
                <h2>Пациенты, прошедшие тесты</h2>

                <div class="test-results-search">
                    <input type="text"
                           class="form-control"
                           placeholder="Поиск по пациенту или названию теста...">
                </div>
            </div>

            @if($recentResults->count())
                <div class="test-results-table-wrap">
                    <table class="table test-results-table">
                        <thead>
                        <tr>
                            <th>Пациент</th>
                            <th>Тест</th>
                            <th>Результат</th>
                            <th>Интерпретация</th>
                            <th>Дата прохождения</th>
                            <th class="text-end">Действия</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($recentResults as $result)
                            @php
                                $patient = $result->patient ?? null;
                                $test = $result->test ?? null;

                                $patientFullName = trim(
                                    ($patient->surname ?? '') . ' ' .
                                    ($patient->name ?? '') . ' ' .
                                    ($patient->patronym ?? '')
                                );

                                $initials = mb_substr($patient->surname ?? 'П', 0, 1) .
                                            mb_substr($patient->name ?? '', 0, 1);
                            @endphp

                            <tr>
                                <td>
                                    <div class="patient-main">
                                        <div class="patient-avatar">
                                            {{ $initials }}
                                        </div>

                                        <div>
                                            <div class="patient-name">
                                                {{ $patientFullName ?: 'Пациент не указан' }}
                                            </div>

                                            <div class="patient-meta">
                                                @if(!empty($patient?->birth_at))
                                                    {{ \Carbon\Carbon::parse($patient->birth_at)->format('d.m.Y') }}
                                                @else
                                                    Дата рождения не указана
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="test-title">
                                        {{ $test->name ?? $result->test_title ?? 'Название теста не указано' }}
                                    </div>

                                    <div class="test-date">
                                        {{ $test->type ?? $result->test_type ?? 'Психологический тест' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="test-score">
                                        {{ $result->score ?? $result->points ?? '—' }}
                                    </div>

                                    <div class="test-result-text">
                                        {{ $result->scale_name }}
                                    </div>

                                    @if(!empty($result->result_name))
                                        <div class="test-date">
                                            {{ $result->result_name }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="result-badge {{ $result->status_class }}">
                                        {{ $result->status_text }}
                                    </span>
                                </td>

                                <td>
                                    @if(!empty($result->created_at))
                                        <div class="test-title">
                                            {{ $result->created_at->format('d.m.Y') }}
                                        </div>

                                        <div class="test-date">
                                            {{ $result->test_type_label }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    <div class="test-actions">
                                        @if($result->medical_card_url)
                                            <a
                                                href="{{ $result->medical_card_url }}"
                                                class="test-action-btn primary"
                                            >
                                                Медкарта
                                            </a>
                                        @endif

                                        <a
                                            href="{{ $result->result_url }}"
                                            class="test-action-btn"
                                        >
                                            Результат
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="test-results-empty">
                    <div class="test-results-empty-icon">📋</div>
                    <h3>Пока нет пройденных тестов</h3>
                    <p>
                        Когда пациенты начнут проходить тесты в медицинской карте, последние результаты появятся на этой
                        странице.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
