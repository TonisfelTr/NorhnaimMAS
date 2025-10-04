@extends('doctors.reception')
@section('title', "{$patient->getFullNameWithInitials()} - медицинская карта пациента")
@section('assets')
    <style>
        :root {
            --mc-surface: #fff;
            --mc-border: #e9eef3;
            --mc-muted: #6b7280;
            --mc-radius: 12px;
            --mc-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 4px 16px rgba(15, 23, 42, .03);
        }

        /* Узкая центральная колонка */
        .mc-wrap {
            max-width: 1490px;
            margin: 0 auto;
        }

        /* Карточка шапки и элементы ленты */
        .mc-card {
            background: var(--mc-surface);
            border: 1px solid var(--mc-border);
            border-radius: var(--mc-radius);
            box-shadow: var(--mc-shadow);
        }

        .mc-card .card-body {
            padding: 1rem 1.25rem;
        }

        /* Аватар */
        .mc-avatar {
            width: 56px;
            height: 56px;
            border-radius: 9999px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Блок табов как на макете: мягкие «пилюли» на светлой подложке */
        .mc-tabs {
            background: #f9fafb;
            border-top: 1px solid var(--mc-border);
            border-bottom-left-radius: var(--mc-radius);
            border-bottom-right-radius: var(--mc-radius);
            padding: .5rem 1rem 0;
        }

        .mc-tabs .nav {
            gap: .25rem;
        }

        .mc-tabs .nav-link {
            color: var(--mc-muted);
            border: 0;
            border-radius: 9999px;
            padding: .35rem .75rem;
            font-weight: 500;
        }

        .mc-tabs .nav-link:hover {
            background: #eef2f7;
            color: #111827;
        }

        .mc-tabs .nav-link.active {
            background: rgba(13, 110, 253, .10);
            color: #0d6efd;
        }

        /* Контент табов */
        .mc-pane {
            padding: 1rem 0;
        }

        /* Плейсхолдеры помягче */
        .placeholder {
            opacity: .55
        }

        /* Кнопка редактирования в шапке — маленькая, справа */
        .btn-edit {
            white-space: nowrap;
        }

        .nav-link.active[data-bs-toggle="tab"] {
            background: transparent;
            border-bottom: 3px solid #0a58ca;
            border-radius: 0;
        }

        #suneditor_anamnesisEditor {
            font-family: 'Trebuchet MS';
        }

        .label-required::after {
            content: " *";
            color: #dc3545; /* bootstrap danger */
            font-weight: 700;
            margin-left: 2px;
        }

        .tooltip {
            text-align: left !important;
        }

        #modalAddAnamnesis .modal-dialog {
            max-width: 90%; /* например, 90% от ширины окна */
        }

        #aiPanel {
            position: relative;
        }

        .ai-loading {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }

        #aiSymptoms .form-check.text-muted {
            opacity: .7;
        }

        #pane-manual .form-label {
            margin-bottom: .125rem;
        }

        /* меньше стандартного .5rem */
        #pane-manual .input-group {
            margin-top: .25rem;
        }

        #aiTabs {
            --bs-nav-tabs-border-color: white;
        }
    </style>
@endsection
@section('sub-main')
    <div class="p-4">
        <div class="mc-wrap">

            <div class="mc-card mb-2">
                <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                    <div class="mc-avatar">
                        <i class="bi bi-person fs-4 text-secondary"></i>
                    </div>

                    <div class="me-auto">
                        <div class="h5 mb-1">{{ $patient->surname }}
                            , {{ $patient->name }} {{ $patient->patronym }}</div>
                        <div class="text-muted small">Дата рождения: {{ $patient->birth_at }}</div>
                        <div class="text-muted small">Пол:
                            @if($patient->gender == 'M')
                                мужской
                            @elseif($patient->gender == 'F')
                                женский
                            @else
                                не указано
                            @endif
                        </div>
                        <div class="text-muted small">Пациент с: {{ $patient->created_at }}</div>
                        <div class="text-muted small">Лечащий врач: @if($patient->doctor)
                                {{ $patient->doctor->full_name }}
                            @else
                                <span class="text-info">не указан</span>
                            @endif</div>
                        <div class="text-muted small">Диагноз: @if($patient->diagnose_id)
                                {{ $patient->diagnose->code }}
                            @else
                                <span class="text-warning">не поставлен</span>
                            @endif</div>
                        @if(!$patient->user_id)
                            <div class="text-warning small">
                                <i class="bi bi-exclamation-triangle"></i> Не зарегистрирован в системе!
                            </div>
                        @else()
                            <span class="text-info">Зарегистрирован в системе!</span>
                        @endif
                    </div>

                </div>

                {{-- Tabs (под шапкой, как на макете) --}}
                <div class="mc-tabs">
                    <ul class="nav" id="cardTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-anamnesis"
                                    type="button">Анамнез
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-labs" type="button">
                                Анализы и исследования
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-tests" type="button">
                                Тесты
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-presc" type="button">
                                Препараты и рецепты
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-epicrisis"
                                    type="button">Эпикриз
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-docs" type="button">
                                Документы
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active mc-pane" id="pane-anamnesis" role="tabpanel">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button"
                                class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAddAnamnesis">
                            <i class="bi bi-plus-lg me-1"></i> Добавить запись анамнеза
                        </button>
                    </div>

                    @forelse($anamneses as $a)
                        <div class="mc-card mb-2">
                            <div class="card-body d-flex flex-wrap gap-2">
                                <div class="me-auto">
                                    <div class="fw-semibold mb-2">{{ $a->title }} <span class="text-muted small">({{ $a->created_at?->format('d.m.Y H:i') }})</span>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        @if($a->source == 'ai')
                                            <i class="bi bi-cpu"></i> Заполнение через анализатор
                                        @elseif($a->source == 'manual')
                                            <i class="bi bi-pencil"></i> Ручное заполнение
                                        @endif
                                    </div>
                                    <div class="text-muted small mb-1">
                                        Автор:
                                        @if(method_exists($a,'doctor') && $a->doctor)
                                            {{ $a->doctor->full_name ?? '' }}
                                        @else
                                            Дебаггинг
                                        @endif
                                    </div>
                                    <div>{{ strip_tags(html_entity_decode(\Illuminate\Support\Str::limit($a->text, 260))) }}</div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <a href="{{ route('doctors.anamneses.show', [$patient->id, $a->id]) }}"
                                       class="btn btn-outline-secondary btn-sm">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border">Записей анамнеза пока нет.</div>
                    @endforelse
                </div>

                {{-- === Анализы и исследования (рыбка) === --}}
                <div class="tab-pane fade mc-pane" id="pane-labs" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Исследования</div>
                        <a href="#" class="btn btn-primary btn-sm"
                           data-bs-toggle="modal" data-bs-target="#modalAddLabOrder">
                            <i class="bi bi-plus-lg me-1"></i> Добавить
                        </a>
                    </div>

                    <div class="mc-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Лабораторное название</th>
                                        <th>Дата</th>
                                        <th>Приоритет</th>
                                        <th>Статус</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($researches as $research)
                                        <tr class="placeholder-glow">
                                            <td><span class="col-7">{{ $research->laboratory }}</span></td>
                                            <td><span class="col-6">{{ \Carbon\Carbon::parse($research->updated_at)->format('d.m.Y H:i:s') }}</span></td>
                                            <td><span class="col-8">{{ $research->priority == 'normal' ? 'Обычный' : 'Срочный' }}</span></td>
                                            <td>
                                                @switch($research->status)
                                                    @case('ordered')
                                                        <span class="badge bg-primary col-5">Назначено</span>
                                                        @break
                                                    @case('processing')
                                                        <span class="badge bg-warning col-5">В работе</span>
                                                        @break
                                                    @case('ready')
                                                        <span class="badge bg-success col-5">Выполнен</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    // Сформируем карту значений параметров для просмотра (id => value)
                                                    // Подстрой под свою модель данных, пример ниже:
                                                    $valuesMap = collect($research->results ?? [])   // напр., relation results
                                                        ->mapWithKeys(fn($r) => [ (string)($r->parameter_id ?? $r->lab_parameter_id ?? $r->id) => $r->value ])
                                                        ->toArray();
                                                @endphp

                                                @if($research->status === 'ready')
                                                    {{-- Просмотр результатов (read-only) --}}
                                                    <button type="button"
                                                            class="btn btn-link p-1"
                                                            title="Печать результатов"
                                                            data-research-print
                                                            data-research-id="{{ $research->id }}"
                                                            data-patient-id="{{ $patient->id }}">
                                                        <i class="bi bi-printer"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-link p-1"
                                                            title="Просмотреть результаты"
                                                            data-lab-view
                                                            data-id="{{ $research->id }}"
                                                            data-param-ids='@json($research->parameters)'
                                                            data-values='@json($valuesMap)'
                                                            data-collected-at="{{ optional($research->collected_at ?? $research->planned_at)->format('Y-m-d H:i') }}"
                                                            data-laboratory="{{ $research->laboratory }}"
                                                            data-comment="{{ $research->comment }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-link p-1"
                                                            title="Внести изменения"
                                                            data-lab-edit
                                                            data-id="{{ $research->id }}"
                                                            data-param-ids='@json($research->parameters)'
                                                            data-planned-at="{{ optional($research->planned_at)->format('Y-m-d\TH:i') }}"
                                                            data-priority="{{ $research->priority }}"
                                                            data-laboratory="{{ $research->laboratory }}"
                                                            data-comment="{{ $research->comment }}"
                                                            data-sample-type="{{ $research->sample_type }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                @endif

                                                <button type="button"
                                                        class="btn btn-link p-1"
                                                        title="Удалить"
                                                        data-lab-delete
                                                        data-id="{{ $research->id }}"
                                                        data-delete-url="{{ route('doctors.researches.delete', ['patient' => $patient->id, 'labResearch' => $research->id]) }}"
                                                    @disabled($research->status !== 'ordered')>
                                                    <i class="bi bi-trash3 text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-tests" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="h6 mb-0">Тесты пациента</div>
                        <div>
                            <button id="btnOpenAssignModal" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Добавить назначение
                            </button>
                        </div>
                    </div>

                    <div id="testsDoctorArea">
                        <!-- Изначально пусто -->
                        <div id="testsEmpty" class="text-muted small">Назначений пока нет. Нажмите «Добавить назначение», чтобы назначить тест пациенту.</div>

                        <!-- Список назначений (заполняется JS) -->
                        <div id="assignmentsList" style="margin-top:8px;"></div>
                    </div>
                </div>

                {{-- === Рецепты (рыбка) === --}}
                <div class="tab-pane fade mc-pane" id="pane-presc" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Выписанные рецепты</div>
                        <a href="#" class="btn btn-primary btn-sm disabled">
                            <i class="bi bi-file-earmark-plus me-1"></i> Выписать рецепт
                        </a>
                    </div>
                    <div class="mc-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Препарат</th>
                                        <th>Дозировка</th>
                                        <th>Срок действия</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @for($i=0;$i<4;$i++)
                                        <tr class="placeholder-glow">
                                            <td><span class="placeholder col-6"></span></td>
                                            <td><span class="placeholder col-8"></span></td>
                                            <td><span class="placeholder col-5"></span></td>
                                            <td><span class="placeholder col-6"></span></td>
                                            <td class="text-end"><span
                                                    class="btn btn-sm btn-outline-secondary disabled placeholder col-6">&nbsp;</span>
                                            </td>
                                        </tr>
                                    @endfor
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-light border mt-2 mb-0">Раздел в разработке.</div>
                        </div>
                    </div>
                </div>

                {{-- === Эпикриз === --}}
                <div class="tab-pane fade mc-pane" id="pane-epicrisis" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Эпикризы</div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalAddEpicrisis">
                            <i class="bi bi-plus-lg me-1"></i> Добавить эпикриз
                        </button>
                    </div>

                    @php
                        $epicrises = method_exists($patient,'epicrises') ? $patient->epicrises()->latest()->get() : collect();
                    @endphp

                    @forelse($epicrises as $e)
                        <div class="mc-card mb-2">
                            <div class="card-body d-flex">
                                <div class="me-auto">
                                    <div class="fw-semibold">{{ $e->created_at?->format('d.m.Y') }}</div>
                                    <div class="text-muted small mb-1">
                                        Автор:
                                        @if(method_exists($e,'doctor') && $e->doctor)
                                            {{ $e->doctor->surname ?? '' }} {{ $e->doctor->name ?? '' }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                    <div>{{ \Illuminate\Support\Str::limit($e->text, 260) }}</div>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" href="#">Открыть</a>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border">Эпикризов нет.</div>
                    @endforelse
                </div>

                {{-- === Документы (рыбка) === --}}
                <div class="tab-pane fade mc-pane" id="pane-docs" role="tabpanel">
                    <form class="mc-card p-3 mb-2" action="#" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2 fw-semibold">Загрузить документ</div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Файл (PDF/JPG/PNG)</label>
                                <input class="form-control" type="file" name="file" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Комментарий</label>
                                <input class="form-control" type="text" name="comment"
                                       placeholder="например, Выписка из стационара">
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-primary w-100" type="submit" disabled>
                                    <i class="bi bi-upload me-1"></i>Загрузить
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mc-card">
                        <div class="card-body">
                            <div class="list-group">
                                @for($i=0;$i<3;$i++)
                                    <div
                                        class="list-group-item d-flex justify-content-between align-items-center placeholder-glow">
                                        <div>
                                            <div class="fw-semibold"><span class="placeholder col-8"></span></div>
                                            <div class="small text-muted"><span class="placeholder col-6"></span></div>
                                        </div>
                                        <i class="bi bi-box-arrow-up-right text-muted"></i>
                                    </div>
                                @endfor
                            </div>
                            <div class="alert alert-light border mt-3 mb-0">Раздел в разработке.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @php
        $labSex = strtolower($patient->sex ?? 'any'); // 'm' | 'f' | 'any'
        $labAge = $patient->birth_at ? \Carbon\Carbon::parse($patient->birth_at)->age : null;
    @endphp
    {{-- Модалка: Внести изменения (ввод результатов) --}}
    <div class="modal fade" id="modalAddLab" tabindex="-1" aria-hidden="true"
         data-mode="result"
         data-api-params="{{ route('api.params.search') }}"
         data-gender="{{ strtolower($patient->gender ?? '') }}"  {{-- <- НОВОЕ --}}
         data-sex="{{ strtolower($patient->sex ?? 'any') }}"      {{-- fallback --}}
         data-age="{{ $patient->birth_at ? \Carbon\Carbon::parse($patient->birth_at)->age : '' }}"
         data-update-url="{{ route('doctors.researches.update', ['patient' => $patient->id, 'labResearch' => '__ID__']) }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" method="post"
                  action="{{ route('doctors.researches.update', ['patient' => $patient->id, 'labResearch' => '__ID__']) }}">
                @csrf

                {{-- важно для редактирования конкретного направления --}}
                <input type="hidden" name="order_id" id="resultOrderId">

                <div class="modal-header">
                    <h5 class="modal-title">Внести изменения — результаты анализа</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Левая колонка: только метаданные --}}
                        <div class="col-lg-4">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2">
                                    <label class="form-label">Дата/время забора</label>
                                    <input type="datetime-local" class="form-control" name="collected_at"
                                           value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Статус</label>
                                    <select class="form-select" name="status">
                                        <option value="ordered" selected>Назначено</option>
                                        <option value="processing">В работе</option>
                                        <option value="ready">Готово</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Лаборатория (опц.)</label>
                                    <input class="form-control" name="lab_name" placeholder="Название/отделение">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Комментарий</label>
                                    <textarea class="form-control" name="comment" rows="3"
                                              placeholder="Например: перед началом терапии"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Правая колонка: только выбранные параметры с вводом значений --}}
                        <div class="col-lg-8">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">Параметры направления <span id="selCount">(0)</span></div>
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" id="toggleHideNormal">
                                        <label class="form-check-label" for="toggleHideNormal">Скрывать нормальные</label>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="selParamsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 280px;">Параметр</th>
                                            <th class="text-nowrap">Реф. интервал</th>
                                            <th>Ед.</th>
                                            <th style="width: 180px;">Значение</th>
                                            <th class="text-center">Флаг</th>
                                            <th class="text-end"></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="small text-muted mt-2">
                                    Подсветка:
                                    <span class="badge bg-success">норма</span>
                                    <span class="badge bg-warning text-dark">низкий</span>
                                    <span class="badge bg-danger">высокий</span>
                                </div>
                            </div>
                        </div>
                    </div> {{-- /row --}}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить результаты</button>
                </div>
            </form>
        </div>
    </div>
    {{-- Модалка: Просмотр результатов (READ-ONLY) --}}
    <div class="modal fade" id="modalViewLab" tabindex="-1" aria-hidden="true"
         data-api-params="{{ route('api.params.search') }}"
         data-gender="{{ strtolower($patient->gender ?? '') }}"
         data-sex="{{ strtolower($patient->sex ?? 'any') }}"
         data-age="{{ $patient->birth_at ? \Carbon\Carbon::parse($patient->birth_at)->age : '' }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Результаты анализа</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Левая колонка: метаданные --}}
                        <div class="col-lg-4">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2">
                                    <div class="text-muted small">Дата/время забора</div>
                                    <div id="v_collected_at" class="fw-semibold">—</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Статус</div>
                                    <div id="v_status" class="fw-semibold">Готово</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Лаборатория</div>
                                    <div id="v_lab_name">—</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Комментарий</div>
                                    <div id="v_comment">—</div>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="v_toggleHideNormal">
                                    <label class="form-check-label" for="v_toggleHideNormal">Скрывать нормальные</label>
                                </div>
                            </div>
                        </div>

                        {{-- Правая колонка: таблица параметров --}}
                        <div class="col-lg-8">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">Параметры анализа <span id="v_selCount">(0)</span></div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="v_paramsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="min-width:280px">Параметр</th>
                                            <th>Реф. интервал</th>
                                            <th>Ед.</th>
                                            <th style="width:180px;">Значение</th>
                                            <th class="text-center">Флаг</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="small text-muted mt-2">
                                    Подсветка:
                                    <span class="badge bg-success">норма</span>
                                    <span class="badge bg-warning text-dark">низкий</span>
                                    <span class="badge bg-danger">высокий</span>
                                </div>
                            </div>
                        </div>
                    </div> {{-- /row --}}
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade"
         id="modalAddAnamnesis"
         tabindex="-1"
         aria-hidden="true"
         data-api-analyze="{{ route('analyze.anamnesis') }}"
         data-open-on-load="{{ $errors->any() ? '1' : '0' }}">>
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" method="post"
                  action="{{ route('doctors.anamneses.store', ['patient' => $patient->id]) }}">
                @csrf

                <div class="modal-header">
                    <h3 class="modal-title">Добавить запись анамнеза</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row g-3 p-2">
                        <div class="mb-3">
                            <label class="form-label label-required" for="anamnesisName">Название анамнеза</label>
                            <input id="anamnesisName"
                                   type="text"
                                   name="title"
                                   class="form-control"
                                   required
                                   placeholder="Например: Первичный при поступлении"
                                   value="{{ old('title') }}">
                        </div>
                        <div class="col-lg-7 d-flex flex-column">
                            <label class="form-label">Жалобы / анамнестические данные</label>

                            <div id="editorBox" class="flex-grow-1">
                                <textarea id="anamnesisEditor"
                                          name="text"
                                          class="form-control"
                                          rows="14"
                                          placeholder="Свободный текст...">{{ old('text') }}</textarea>
                                <div id="textError" class="invalid-feedback d-none">
                                    Заполните текст анамнеза.
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label">Категория</label>
                                    <select name="category" class="form-control">
                                        <option value="0">Первичный</option>
                                        <option value="1">Вторичный</option>
                                        <option value="2">Скрининг</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="border rounded p-3 d-flex flex-column">
                                <ul class="nav nav-tabs mb-3" id="aiTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-ai" data-bs-toggle="tab"
                                                data-bs-target="#pane-ai" type="button" role="tab">
                                            Анализатор
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-manual" data-bs-toggle="tab"
                                                data-bs-target="#pane-manual" type="button" role="tab">
                                            Вручную
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content flex-grow-1">
                                    <div class="tab-pane fade show active" id="pane-ai" role="tabpanel"
                                         aria-labelledby="tab-ai">
                                        <div id="aiPanel" class="border rounded p-3 position-relative">
                                            <div class="fw-semibold fs-5">Управление анализатором</div>

                                            {{-- Текстовая сводка для вставки в редактор --}}
                                            <div id="aiResultBox" class="mt-2 d-none"></div>

                                            <div class="row mt-3 g-2">
                                                <div class="col-6">
                                                    <button type="button" id="btnInsertSummary"
                                                            class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        Вставить сводку в текст
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="button" id="btnAppendSummary"
                                                            class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                        Добавить сводку в конец
                                                    </button>
                                                </div>
                                                <div class="col-12">
                                                    <button type="button" id="btnRunAiAnalyze"
                                                            class="btn btn-primary btn-sm w-100"
                                                            data-bs-toggle="tooltip"
                                                            title="Запускает автоматический анализ текста анамнеза с помощью ИИ">
                                                        <i class="bi bi-cpu me-1"></i> Проанализировать
                                                    </button>
                                                </div>
                                            </div>

                                            <hr id="aiSep" class="my-3 d-none">

                                            {{-- НОВОЕ: структурная сводка (диагноз + ключевые симптомы) --}}
                                            <div id="aiSummary" class="mb-3 d-none"></div>

                                            {{-- НОВОЕ: ТОП-5 диагнозов (radio) --}}
                                            <div id="aiDiagTitle" class="mb-2 fw-semibold d-none">Предложенные диагнозы
                                                (ТОП-5)
                                            </div>
                                            <div id="aiDiagnoses" class="ps-1 d-none"></div>

                                            {{-- НОВОЕ: симптомы (checkbox) --}}
                                            <div id="aiSymTitle" class="mb-2 fw-semibold d-none">Найденные симптомы
                                            </div>
                                            <div id="aiSymptoms" class="ps-1 d-none"></div>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" id="m-set-current">
                                                <label class="form-check-label" for="m-set-current">
                                                    Назначить выбранный диагноз как текущий для пациента
                                                </label>
                                            </div>

                                            {{-- hidden для отправки выбора на бек при сохранении --}}
                                            <input type="hidden" id="diagCodeInput" name="diagnosis_code">
                                            <input type="hidden" id="diagTitleInput" name="diagnosis_title">
                                            <input type="hidden" id="symptomsJsonInput" name="symptoms_json">

                                            {{-- оверлей загрузки --}}
                                            <div id="aiLoading" class="ai-loading d-none">
                                                <div class="spinner-border" role="status" aria-hidden="true"></div>
                                                <span class="ms-2">Анализируем…</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade d-flex flex-column" id="pane-manual" role="tabpanel"
                                         aria-labelledby="tab-manual">
                                        <div class="row g-2">
                                            <div class="position-relative">
                                                <label for="diagnosisSearch" class="form-label">Диагноз</label>
                                                <input id="diagnosisSearch"
                                                       class="form-control"
                                                       autocomplete="off"
                                                       placeholder="напр., F20.0 или «Параноидная шизофрения»">
                                                <div id="diagnosisMenu" class="dropdown-menu"></div>
                                            </div>

                                            <input type="hidden" id="diagCodeInput" name="diagCode">
                                            <input type="hidden" id="diagTitleInput" name="diagTitle">
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label class="form-label m-0">Симптомы</label>
                                                <div class="text-muted small">добавляйте свои через поле ниже</div>
                                            </div>

                                            <div id="m-sym-list" class="border rounded p-1"
                                                 style="max-height:260px; overflow:auto;"></div>

                                            <div class="input-group mt-2">
                                                <input id="m-sym-input" type="text" class="form-control"
                                                       placeholder="Новый симптом… (Enter)">
                                                <button class="btn btn-outline-secondary" id="m-sym-add" type="button">
                                                    Добавить
                                                </button>
                                                <button class="btn btn-outline-danger" id="m-sym-clear" type="button">
                                                    Очистить
                                                </button>
                                            </div>

                                            <div class="mt-2">
                                                <span class="small text-muted me-2">Быстрые:</span>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Галлюцинации">Галлюцинации
                                                </button>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Бред">Бред
                                                </button>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Социальное отстранение">Социальное отстранение
                                                </button>
                                                <button class="btn btn-light btn-sm m-1 m-quick" type="button"
                                                        data-sym="Нарушение сна">Нарушение сна
                                                </button>
                                            </div>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" id="m-set-current">
                                                <label class="form-check-label" for="m-set-current">
                                                    Назначить выбранный диагноз как текущий для пациента
                                                </label>
                                            </div>
                                        </div>
                                        @if(config('app.debug'))
                                            <div class="mt-auto small text-muted">
                                                Подсказка: значения из этой вкладки уйдут в форму (как и данные ИИ),
                                                т.к. пишутся в <code>diagCodeInput</code>, <code>diagTitleInput</code> и
                                                <code>symptomsJsonInput</code>.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <input type="hidden" id="diagSource" name="diag_source" value="ai">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Сохранить запись</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade"
         id="modalAddLabOrder"
         tabindex="-1"
         aria-hidden="true"
         data-mode="order"
         data-api-params="{{ route('api.params.search') }}"
         data-gender="{{ strtolower($patient->gender ?? '') }}"  {{-- <- НОВОЕ --}}
         data-sex="{{ strtolower($patient->sex ?? 'any') }}"      {{-- fallback --}}
         data-age="{{ $patient->birth_at ? \Carbon\Carbon::parse($patient->birth_at)->age : '' }}"
         data-api-save-template="{{ route('api.store.labtemplate') }}"
         data-templates='@json($templatesMap)'>
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" method="post" action="{{ route('doctors.researches.store', $patient->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Создать направление на анализ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Левая колонка: метаданные направления --}}
                        <div class="col-lg-4">
                            <div class="border rounded p-3 h-100">
                                <div class="mb-2">
                                    <label class="form-label">Плановая дата/время забора</label>
                                    <input type="date" class="form-control" name="planned_at"
                                           value="{{ now()->format('Y-m-d') }}">
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Приоритет</label>
                                        <select class="form-select" name="priority">
                                            <option value="normal">Обычный</option>
                                            <option value="urgent">Срочно</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Статус</label>
                                        <input class="form-control" value="Назначено" disabled>
                                        <input type="hidden" name="status" value="ordered">
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">Лаборатория (опц.)</label>
                                    <input class="form-control" name="laboratory" placeholder="Название/отделение">
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">Комментарий</label>
                                    <textarea class="form-control" name="comment" rows="3"
                                              placeholder="Например: перед началом терапии"></textarea>
                                </div>

                                <hr>

                                <div class="mb-2">
                                    <label class="form-label">Шаблон набора</label>
                                    <div class="input-group">
                                        <select id="orderTplSelect" class="form-select">
                                            <option value="">— не выбран —</option>
                                            @foreach($labTemplates as $tpl)
                                                <option value="{{ $tpl->id }}">
                                                    {{ $tpl->name }} {{ $tpl->doctor_id ? '（личный）' : '（общий）' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" id="btnOrderTplApply">Применить</button>
                                    </div>
                                    <div class="form-text">Шаблон заполнит список параметров справа.</div>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">Сохранить выбранные как шаблон</label>
                                    <div class="input-group">
                                        <input id="orderTplName" type="text" class="form-control" placeholder="Название шаблона">
                                        <button class="btn btn-outline-primary" type="button" id="btnOrderTplSave">Сохранить</button>
                                    </div>
                                    <div class="form-text">Шаблон будет доступен только вам.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Правая колонка: поиск и выбранные параметры (БЕЗ значений) --}}
                        <div class="col-lg-8">
                            <div class="border rounded p-3">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <label class="form-label">Материал</label>
                                        <select id="sampleType" name="sampleType" class="form-select">
                                            <option value="моча">моча</option>
                                            <option value="кровь">кровь</option>
                                            <option value="кровь/моча">кровь/моча</option>
                                            <option value="плазма">плазма</option>
                                            <option value="расчёт">расчёт</option>
                                            <option value="сыворотка">сыворотка</option>
                                        </select>
                                    </div>
                                    <div class="col-md">
                                        <label class="form-label">Поиск параметра</label>
                                        <input id="paramSearch" class="form-control" autocomplete="off"
                                               placeholder="напр.: Hb, натрий, «Гемоглобин»">
                                        <div id="paramMenu" class="dropdown-menu"></div>
                                        <div class="form-text">Начните вводить название или просто кликните в поле, чтобы показать список.</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Группа</label>
                                        <div class="input-group">
                                            <select id="paramGroup" class="form-select">
                                                <option value="">Все группы</option>
                                                <option value="cbc">ОАК</option>
                                                <option value="cbc_indices">Эритроцитарные индексы</option>
                                                <option value="diff">Лейкоформула (%)</option>
                                                <option value="diff_abs">Лейкоформула (абс.)</option>
                                                <option value="urinalysis">ОАМ</option>
                                                <option value="metabolic">Метаболические</option>
                                                <option value="liver">Печёночные</option>
                                                <option value="renal">Почечные</option>
                                                <option value="electrolytes">Электролиты</option>
                                                <option value="lipids">Липиды</option>
                                                <option value="iron">Железо</option>
                                                <option value="vitamins">Витамины</option>
                                                <option value="endocrine">Эндокринные</option>
                                                <option value="drug_levels">Уровни препаратов</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold">Выбранные параметры <span id="selCount">(0)</span></div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="selParamsTable">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="min-width:280px">Параметр</th>
                                            <th>Реф. интервал</th>
                                            <th>Ед.</th>
                                            <th class="text-end" style="width:70px;"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {{-- строки добавляются JS; по строке будет hidden param_ids[] --}}
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Отмена</button>
                    <button class="btn btn-primary" type="submit" name="save" value="1">Сохранить направление</button>
                    <button class="btn btn-primary" type="submit" name="save_and_new" value="1">Сохранить и ещё</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalAddEpicrisis" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" method="post" action="#">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить эпикриз</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Текст эпикриза</label>
                        <textarea name="text" class="form-control" rows="10" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Диагноз (МКБ-10)</label>
                            <input name="mkb10" class="form-control" placeholder="F31.1 ...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Дата</label>
                            <input name="created_at" type="date" class="form-control"
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
{{--    <div class="modal fade" id="modalAssignTest" tabindex="-1" aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-dialog-centered modal-lg">--}}
{{--            <div class="modal-content">--}}
{{--                <form id="formAssignTest">--}}
{{--                    <div class="modal-header">--}}
{{--                        <h5 class="modal-title">Назначить тест пациенту</h5>--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>--}}
{{--                    </div>--}}
{{--                    <div class="modal-body">--}}
{{--                        <div class="mb-3">--}}
{{--                            <label class="form-label">Тест</label>--}}
{{--                            <select id="assignTestSelect" name="test_code" class="form-select" required>--}}
{{--                                <option value="">— выберите тест —</option>--}}
{{--                                @foreach($psyTests as $t)--}}
{{--                                    <option value="{{ $t->code }}" data-name="{{ $t->name }}">{{ $t->name }} @if($t->minutes) — {{ $t->minutes }} мин @endif</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}

{{--                        <div class="row g-2">--}}
{{--                            <div class="col-md-6 mb-3">--}}
{{--                                <label class="form-label">Срок (до)</label>--}}
{{--                                <input type="date" name="due_at" class="form-control">--}}
{{--                            </div>--}}
{{--                            <div class="col-md-6 mb-3">--}}
{{--                                <label class="form-label">Самостоятельная инициатива</label>--}}
{{--                                <select name="is_self_initiated" class="form-select">--}}
{{--                                    <option value="0">Назначено врачом</option>--}}
{{--                                    <option value="1">Пациент может пройти самостоятельно</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div class="mb-3">--}}
{{--                            <label class="form-label">Комментарий (необязательно)</label>--}}
{{--                            <textarea name="notes" class="form-control" rows="2"></textarea>--}}
{{--                        </div>--}}

{{--                    </div>--}}
{{--                    <div class="modal-footer">--}}
{{--                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Отмена</button>--}}
{{--                        <button id="btnSaveAssign" type="submit" class="btn btn-primary btn-sm">Сохранить</button>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    <!-- Modal: Просмотр результата (общий) -->
    <div class="modal fade" id="modalViewResult" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewResultTitle">Результат теста</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewResultBody">
                    <!-- наполняется JS: краткая информация, ответы, индекс, интерпретация -->
                </div>
                <div class="modal-footer">
                    <div class="me-auto small text-muted" id="viewResultFlags"></div>
                    <button id="btnVerifyResult" type="button" class="btn btn-success btn-sm">Верифицировать</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
        <div id="toastSaved" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Сохранено</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/medical_card.js'])
@endpush
