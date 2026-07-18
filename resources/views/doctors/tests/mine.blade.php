@extends('doctors.tests')

@section('title', 'Мои тесты')

@section('sub-main')
    <style>
        .test-action-btn.danger {
            border: 1px solid #fecaca;
            background: #fff7f7;
            color: #b91c1c;
        }

        .test-action-btn.danger:hover {
            border-color: #fca5a5;
            background: #feecec;
            color: #991b1b;
        }

        .test-action-btn:disabled {
            cursor: not-allowed;
            opacity: .55;
        }
    </style>

    <div class="test-results-page">
        <div class="test-results-header">
            <div class="test-results-title">
                <h1>{{ $pageTitle }}</h1>

                <p>
                    @if($doesHaveClinic)
                        Пользовательские тесты, доступные врачам вашей клиники.
                    @else
                        Тесты, созданные вами для личной практики.
                    @endif
                </p>
            </div>

            <div class="test-results-period">
                {{ $myTests->count() }} тестов
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($debugCascadeDeleteEnabled)
            <div class="alert alert-danger">
                <strong>Режим отладки.</strong>
                При удалении использованного теста будут удалены только связанные
                с ним назначения, сессии, ответы, результаты и элементы шаблона.
                Врачи, пациенты, пользователи и клиники не удаляются.
            </div>
        @endif

        <div class="test-results-panel">
            <div class="test-results-panel-header">
                <h2>
                    {{ $doesHaveClinic ? 'Тесты клиники' : 'Созданные мной тесты' }}
                </h2>

                <div class="test-results-search">
                    <input
                        type="search"
                        id="myTestsSearch"
                        class="form-control"
                        placeholder="Поиск по названию или коду..."
                    >
                </div>
            </div>

            @if($myTests->isNotEmpty())
                <div class="test-results-table-wrap">
                    <table
                        class="table test-results-table"
                        id="myTestsTable"
                    >
                        <thead>
                        <tr>
                            <th>Тест</th>
                            <th>Тип</th>
                            <th>Доступ</th>
                            <th>Использование</th>
                            <th>Создан</th>
                            <th class="text-end">Действия</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($myTests as $test)
                            @php
                                $typeLabel = match($test->type) {
                                    'image' => 'С изображениями',
                                    'sort' => 'Сортировка',
                                    default => 'Опросник',
                                };

                                $isUsed = (bool) $test->has_usage;
                                $canDelete = (bool) $test->can_delete;
                                $debugCascadeDelete =
                                    (bool) $test->debug_cascade_delete;
                            @endphp

                            <tr
                                data-search="{{ mb_strtolower(
                                    ($test->name ?? '') . ' ' .
                                    ($test->code ?? '')
                                ) }}"
                            >
                                <td>
                                    <div class="test-title">
                                        {{ $test->name }}
                                    </div>

                                    <div class="test-date">
                                        Код:
                                        {{ $test->code ?: 'не указан' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="result-badge result-default">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="result-badge result-default">
                                        {{
                                            $test->clinics_count > 0
                                                ? 'Доступен клинике'
                                                : 'Личный тест'
                                        }}
                                    </span>
                                </td>

                                <td>
                                    @if($isUsed)
                                        <span class="result-badge result-attention">
                                            Используется
                                        </span>

                                        <div class="test-date">
                                            Назначений:
                                            {{ $test->assignments_count }},
                                            прохождений:
                                            {{ $test->sessions_count }}
                                        </div>
                                    @else
                                        <span class="result-badge result-normal">
                                            Не использовался
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="test-title">
                                        {{
                                            optional($test->created_at)
                                                ->format('d.m.Y')
                                            ?? '—'
                                        }}
                                    </div>
                                </td>

                                <td>
                                    <div class="test-actions">
                                        @if(!$canDelete)
                                            <span
                                                class="result-badge result-default"
                                                title="Удалить тест может только автор или заведующий в debug-режиме"
                                            >
                                                Только просмотр
                                            </span>
                                        @elseif($isUsed && !$debugCascadeDeleteEnabled)
                                            <button
                                                type="button"
                                                class="test-action-btn"
                                                disabled
                                                title="У теста есть назначения или прохождения"
                                            >
                                                Удалить
                                            </button>
                                        @else
                                            <form
                                                method="POST"
                                                action="{{
                                                    route(
                                                        'doctors.tests.destroy',
                                                        $test
                                                    )
                                                }}"
                                                class="m-0"
                                                onsubmit="return confirm(
                                                    @js(
                                                        $debugCascadeDelete
                                                            ? 'Удалить тест «' . $test->name . '» вместе со всеми его назначениями, сессиями, ответами и результатами? Врачи и пациенты удалены не будут.'
                                                            : 'Удалить тест «' . $test->name . '»? Это действие нельзя отменить.'
                                                    )
                                                )"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="test-action-btn danger"
                                                >
                                                    {{
                                                        $debugCascadeDelete
                                                            ? 'Удалить с данными'
                                                            : 'Удалить'
                                                    }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="test-results-empty">
                    <div class="test-results-empty-icon">
                        🧪
                    </div>

                    <h3>
                        {{
                            $doesHaveClinic
                                ? 'Для клиники пока нет пользовательских тестов'
                                : 'Вы ещё не создавали тесты'
                        }}
                    </h3>

                    <p>
                        @if($doesHaveClinic)
                            После публикации теста для вашей клиники он появится здесь.
                        @else
                            Созданные вами тесты появятся здесь.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const search =
                    document.getElementById('myTestsSearch');

                const rows = document.querySelectorAll(
                    '#myTestsTable tbody tr'
                );

                search?.addEventListener('input', function () {
                    const query = search.value
                        .trim()
                        .toLocaleLowerCase('ru-RU');

                    rows.forEach(function (row) {
                        row.classList.toggle(
                            'd-none',
                            !row.dataset.search.includes(query)
                        );
                    });
                });
            }
        );
    </script>
@endsection
