@extends('doctors.reception')

@section('title', 'Редактирование эпикриза')

@section('assets')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/eclipse.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/markdown/markdown.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/selection/active-line.min.js"></script>

    <style>
        .epicrisis-edit-page {
            --ep-bg: #f6f8fb;
            --ep-card: #ffffff;
            --ep-border: #e7ecf3;
            --ep-muted: #6b7280;
            --ep-text: #111827;
            --ep-primary: #0d6efd;
            --ep-primary-soft: #eef5ff;
            --ep-danger: #dc3545;
            --ep-radius: 18px;
            --ep-shadow: 0 8px 28px rgba(15, 23, 42, .06);

            background: var(--ep-bg);
            padding: 24px;
            color: var(--ep-text);
            min-height: calc(100vh - 90px);
        }

        .epicrisis-edit-wrap {
            max-width: 1500px;
            margin: 0 auto;
        }

        .epicrisis-edit-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 22px;
        }

        .epicrisis-edit-title {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            line-height: 1.15;
            color: var(--ep-text);
        }

        .epicrisis-edit-subtitle {
            margin-top: 8px;
            font-size: 15px;
            color: var(--ep-muted);
        }

        .epicrisis-edit-created {
            font-size: 14px;
            color: #374151;
            white-space: nowrap;
            padding-top: 8px;
        }

        .epicrisis-edit-layout {
            display: grid;
            grid-template-columns: 470px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .epicrisis-edit-card {
            background: var(--ep-card);
            border: 1px solid var(--ep-border);
            border-radius: var(--ep-radius);
            box-shadow: var(--ep-shadow);
        }

        .epicrisis-edit-sidebar {
            padding: 24px;
        }

        .epicrisis-edit-card__title {
            font-size: 18px;
            font-weight: 800;
            color: var(--ep-text);
            margin-bottom: 22px;
        }

        .epicrisis-edit-page .form-label {
            font-size: 15px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 9px;
        }

        .epicrisis-edit-page .form-control,
        .epicrisis-edit-page .form-select {
            min-height: 52px;
            border-radius: 16px;
            font-size: 17px;
            border-color: #dbe3ea;
        }

        .epicrisis-edit-page .form-control:focus,
        .epicrisis-edit-page .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .12);
        }

        .epicrisis-edit-page .form-text {
            font-size: 13px;
            color: var(--ep-muted);
            line-height: 1.45;
        }

        .label-required::after {
            content: " *";
            color: var(--ep-danger);
            font-weight: 800;
            margin-left: 2px;
        }

        .epicrisis-diagnosis-picker {
            background: #f8fafc;
            border: 1px solid #e5eaf1;
            border-radius: 18px;
            padding: 9px;
        }

        .epicrisis-diagnosis-picker .select2-container {
            width: 100% !important;
        }

        .epicrisis-diagnosis-picker .select2-container--bootstrap-5 .select2-selection,
        .epicrisis-diagnosis-picker .select2-container--default .select2-selection {
            min-height: 50px;
            border-radius: 14px !important;
            border-color: #d8e0ea !important;
            display: flex;
            align-items: center;
            padding-left: 10px;
            font-size: 16px;
        }

        .epicrisis-diagnosis-picker .select2-selection__rendered {
            line-height: 38px !important;
            color: #111827 !important;
        }

        .epicrisis-diagnosis-picker .select2-selection__clear {
            font-size: 20px;
            margin-right: 8px;
        }

        .select2-dropdown {
            z-index: 3000;
            border-radius: 14px;
            overflow: hidden;
        }

        .epicrisis-selected-diagnosis {
            margin-top: 16px;
            border: 1px solid #dbeafe;
            background:
                radial-gradient(circle at top right, rgba(13, 110, 253, .10), transparent 34%),
                linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
            border-radius: 18px;
            padding: 16px;
        }

        .epicrisis-selected-diagnosis__head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .epicrisis-selected-diagnosis__icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: #ffffff;
            color: var(--ep-primary);
            border: 1px solid #bfdbfe;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            font-size: 20px;
        }

        .epicrisis-selected-diagnosis__label {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
            line-height: 1.2;
        }

        .epicrisis-selected-diagnosis__hint {
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
        }

        .epicrisis-selected-diagnosis__main {
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 9px;
        }

        .epicrisis-selected-diagnosis__code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 5px 12px;
            border-radius: 999px;
            background: #ffffff;
            color: var(--ep-primary);
            border: 1px solid #bfdbfe;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .epicrisis-selected-diagnosis__title {
            flex: 1;
            min-width: 190px;
            padding-top: 4px;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.35;
            color: #1e3a8a;
        }

        .epicrisis-template-block {
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #dbe3ea;
        }

        .epicrisis-template-block__title {
            font-size: 17px;
            font-weight: 800;
            color: var(--ep-text);
            margin-bottom: 12px;
        }

        .epicrisis-editor-section {
            padding: 0;
            overflow: hidden;
        }

        .epicrisis-editor-section__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 24px;
            border-bottom: 1px solid var(--ep-border);
            background:
                radial-gradient(circle at top right, rgba(13, 110, 253, .06), transparent 32%),
                #ffffff;
        }

        .epicrisis-editor-section__title {
            font-size: 19px;
            font-weight: 800;
            color: var(--ep-text);
            margin-bottom: 6px;
        }

        .epicrisis-editor-section__hint {
            font-size: 15px;
            color: var(--ep-muted);
            line-height: 1.4;
        }

        .epicrisis-editor-section__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--ep-primary-soft);
            color: var(--ep-primary);
            border: 1px solid #d7e7ff;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .epicrisis-editor-shell {
            padding: 24px;
            background: #f8fafc;
        }

        .epicrisis-editor-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 42px;
            padding: 8px 15px;
            border: 1px solid #dbe3ea;
            border-bottom: 0;
            border-radius: 18px 18px 0 0;
            background: linear-gradient(180deg, #ffffff 0%, #f3f6fa 100%);
            flex-wrap: wrap;
        }

        .epicrisis-editor-toolbar__left {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 190px;
        }

        .epicrisis-editor-toolbar__dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #cbd5e1;
        }

        .epicrisis-editor-toolbar__title {
            margin-left: 7px;
            font-size: 13px;
            font-weight: 800;
            color: #64748b;
        }

        .epicrisis-md-toolbar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .epicrisis-md-btn {
            min-width: 34px;
            height: 32px;
            border: 1px solid #dbe3ea;
            border-radius: 10px;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 9px;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            transition: .15s ease;
        }

        .epicrisis-md-btn:hover {
            background: #eef5ff;
            border-color: #bfdbfe;
            color: #0d6efd;
        }

        .epicrisis-md-separator {
            width: 1px;
            height: 24px;
            background: #dbe3ea;
            margin: 0 3px;
        }

        .epicrisis-doc-codemirror {
            display: block;
            width: 100%;
        }

        .epicrisis-editor-shell .CodeMirror {
            min-height: 620px;
            height: auto;
            border: 1px solid #dbe3ea;
            border-radius: 0 0 18px 18px;
            background: #ffffff;
            font-size: 16px;
            line-height: 1.75;
            color: #253044;
            font-family: "Trebuchet MS", Arial, sans-serif;
            box-shadow: inset 0 1px 0 rgba(15, 23, 42, .02);
        }

        .epicrisis-editor-shell .CodeMirror-focused {
            border-color: #86b7fe;
            box-shadow:
                inset 0 1px 0 rgba(15, 23, 42, .02),
                0 0 0 .25rem rgba(13, 110, 253, .12);
        }

        .epicrisis-editor-shell .CodeMirror-scroll {
            min-height: 620px;
        }

        .epicrisis-editor-shell .CodeMirror-lines {
            padding: 26px 30px;
        }

        .epicrisis-editor-shell .cm-header {
            color: #111827;
            font-weight: 800;
        }

        .epicrisis-editor-shell .cm-strong {
            color: #111827;
            font-weight: 800;
        }

        .epicrisis-editor-shell .cm-em {
            color: #334155;
            font-style: italic;
        }

        .epicrisis-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .epicrisis-error {
            border: 1px solid #fecaca;
            background: #fff5f5;
            color: #991b1b;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .epicrisis-error ul {
            margin: 8px 0 0;
        }

        @media (max-width: 1199px) {
            .epicrisis-edit-layout {
                grid-template-columns: 390px minmax(0, 1fr);
            }
        }

        @media (max-width: 991px) {
            .epicrisis-edit-page {
                padding: 16px;
            }

            .epicrisis-edit-header {
                flex-direction: column;
            }

            .epicrisis-edit-created {
                white-space: normal;
                padding-top: 0;
            }

            .epicrisis-edit-layout {
                grid-template-columns: 1fr;
            }

            .epicrisis-editor-section__header {
                flex-direction: column;
                padding: 18px;
            }

            .epicrisis-editor-shell {
                padding: 16px;
            }

            .epicrisis-editor-shell .CodeMirror,
            .epicrisis-editor-shell .CodeMirror-scroll {
                min-height: 430px;
            }

            .epicrisis-editor-shell .CodeMirror-lines {
                padding: 18px;
            }

            .epicrisis-actions {
                flex-direction: column-reverse;
            }

            .epicrisis-actions .btn {
                width: 100%;
            }
        }

        @media (max-width: 575px) {
            .epicrisis-edit-page {
                padding: 12px;
            }

            .epicrisis-edit-title {
                font-size: 24px;
            }

            .epicrisis-edit-sidebar {
                padding: 18px;
            }

            .epicrisis-selected-diagnosis__main {
                flex-direction: column;
            }

            .epicrisis-selected-diagnosis__title {
                min-width: 0;
                padding-top: 0;
            }

            .epicrisis-editor-section__badge {
                width: 100%;
            }

            .epicrisis-editor-toolbar__left,
            .epicrisis-md-toolbar {
                width: 100%;
            }

            .epicrisis-md-toolbar {
                gap: 5px;
            }

            .epicrisis-editor-shell .CodeMirror {
                font-size: 14px;
            }

            .epicrisis-editor-shell .CodeMirror-lines {
                padding: 14px;
            }
        }
    </style>
@endsection

@section('sub-main')
    @php
        $rawEpicrisisText = old('text', $epicrisis->text ?? '');

        $preparedEpicrisisText = html_entity_decode($rawEpicrisisText);

        if ($preparedEpicrisisText !== strip_tags($preparedEpicrisisText)) {
            $preparedEpicrisisText = preg_replace('/<br\s*\/?>/i', "\n", $preparedEpicrisisText);
            $preparedEpicrisisText = preg_replace('/<\/p>/i', "\n\n", $preparedEpicrisisText);
            $preparedEpicrisisText = preg_replace('/<\/div>/i', "\n", $preparedEpicrisisText);
            $preparedEpicrisisText = preg_replace('/<\/h[1-6]>/i', "\n\n", $preparedEpicrisisText);
            $preparedEpicrisisText = strip_tags($preparedEpicrisisText);
            $preparedEpicrisisText = trim($preparedEpicrisisText);
        }
    @endphp

    <div class="epicrisis-edit-page">
        <div class="epicrisis-edit-wrap">

            <div class="epicrisis-edit-header">
                <div>
                    <a href="{{ route('doctors.patients.epicrisis.show', [$patient->id, $epicrisis->id]) }}"
                       class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-arrow-left me-1"></i>
                        Назад к эпикризу
                    </a>

                    <h1 class="epicrisis-edit-title">
                        Редактирование эпикриза
                    </h1>

                    <div class="epicrisis-edit-subtitle">
                        Пациент:
                        {{ trim(($patient->surname ?? '') . ' ' . ($patient->name ?? '') . ' ' . ($patient->patronym ?? '')) }}
                    </div>
                </div>

                <div class="epicrisis-edit-created">
                    Создан:
                    {{ $epicrisis->created_at?->format('d.m.Y') }}
                </div>
            </div>

            @if($errors->any())
                <div class="epicrisis-error">
                    <strong>Проверьте заполнение формы:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post"
                  action="{{ route('doctors.patients.epicrisis.update', [$patient->id, $epicrisis->id]) }}"
                  id="epicrisisEditForm"
                  data-diagnoses-url="/api/diagnoses">
                @csrf

                <div class="epicrisis-edit-layout">

                    <aside class="epicrisis-edit-card epicrisis-edit-sidebar">
                        <div class="epicrisis-edit-card__title">
                            Основные данные
                        </div>

                        <div class="mb-4">
                            <label class="form-label label-required" for="epicrisisCreatedAt">
                                Дата эпикриза
                            </label>

                            <input id="epicrisisCreatedAt"
                                   name="created_at"
                                   type="date"
                                   class="form-control"
                                   value="{{ old('created_at', optional($epicrisis->created_at)->format('Y-m-d')) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="epicrisisDiagnosisSelect" class="form-label">
                                Диагноз МКБ-10
                            </label>

                            <div class="epicrisis-diagnosis-picker">
                                <select id="epicrisisDiagnosisSelect"
                                        class="form-select"
                                        data-placeholder="Начните вводить код или название диагноза">
                                    @if($epicrisis->diagnose)
                                        <option value="{{ $epicrisis->diagnose->id }}" selected>
                                            {{ $epicrisis->diagnose->code }} — {{ $epicrisis->diagnose->title }}
                                        </option>
                                    @elseif(!empty($epicrisis->diagnose_id))
                                        <option value="{{ $epicrisis->diagnose_id }}" selected>
                                            {{ $epicrisis->mkb10 }} — {{ $epicrisis->diagnosis_title }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <input type="hidden"
                                   name="diagnose_id"
                                   id="epicrisisDiagnosisId"
                                   value="{{ old('diagnose_id', $epicrisis->diagnose_id) }}">

                            <input type="hidden"
                                   name="mkb10"
                                   id="epicrisisMkb10"
                                   value="{{ old('mkb10', $epicrisis->mkb10 ?? $epicrisis->diagnose?->code) }}">

                            <input type="hidden"
                                   name="diagnosis_title"
                                   id="epicrisisDiagnosisTitle"
                                   value="{{ old('diagnosis_title', $epicrisis->diagnosis_title ?? $epicrisis->diagnose?->title) }}">
                        </div>

                        <div class="epicrisis-selected-diagnosis"
                             id="epicrisisSelectedDiagnosis">
                            <div class="epicrisis-selected-diagnosis__head">
                                <div class="epicrisis-selected-diagnosis__icon">
                                    <i class="bi bi-clipboard2-pulse"></i>
                                </div>

                                <div>
                                    <div class="epicrisis-selected-diagnosis__label">
                                        Текущий диагноз
                                    </div>

                                    <div class="epicrisis-selected-diagnosis__hint">
                                        Будет сохранён в эпикризе
                                    </div>
                                </div>
                            </div>

                            <div class="epicrisis-selected-diagnosis__main">
                                <span class="epicrisis-selected-diagnosis__code"
                                      id="epicrisisSelectedDiagnosisCode">
                                    {{ old('mkb10', $epicrisis->mkb10 ?? $epicrisis->diagnose?->code ?? '—') }}
                                </span>

                                <span class="epicrisis-selected-diagnosis__title"
                                      id="epicrisisSelectedDiagnosisTitle">
                                    {{ old('diagnosis_title', $epicrisis->diagnosis_title ?? $epicrisis->diagnose?->title ?? 'Диагноз не выбран') }}
                                </span>
                            </div>
                        </div>

                        <div class="epicrisis-template-block">
                            <div class="epicrisis-template-block__title">
                                Шаблон
                            </div>

                            <button type="button"
                                    class="btn btn-outline-secondary w-100 mb-3"
                                    id="epicrisisInsertTemplate">
                                Вставить структуру эпикриза
                            </button>

                            <div class="form-text">
                                Шаблон заменит текущий текст в редакторе. Перед нажатием убедитесь, что текущие данные не нужны.
                            </div>
                        </div>
                    </aside>

                    <main class="epicrisis-edit-card epicrisis-editor-section">
                        <div class="epicrisis-editor-section__header">
                            <div>
                                <div class="epicrisis-editor-section__title">
                                    Текст эпикриза
                                </div>

                                <div class="epicrisis-editor-section__hint">
                                    Редактор хранит текст в формате, удобном для DOC/DOCX. Форматирование записывается Markdown-разметкой.
                                </div>
                            </div>

                            <div class="epicrisis-editor-section__badge">
                                DOC/DOCX-текст
                            </div>
                        </div>

                        <div class="epicrisis-editor-shell">
                            <div class="epicrisis-editor-toolbar" role="toolbar" aria-label="Панель форматирования эпикриза">
                                <div class="epicrisis-editor-toolbar__left">
                                    <span class="epicrisis-editor-toolbar__dot"></span>
                                    <span class="epicrisis-editor-toolbar__dot"></span>
                                    <span class="epicrisis-editor-toolbar__dot"></span>
                                    <span class="epicrisis-editor-toolbar__title">
                                        Клинический эпикриз
                                    </span>
                                </div>

                                <div class="epicrisis-md-toolbar">
                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="bold"
                                            title="Жирный">
                                        <strong>Ж</strong>
                                    </button>

                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="italic"
                                            title="Курсив">
                                        <em>К</em>
                                    </button>

                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="h3"
                                            title="Заголовок">
                                        H3
                                    </button>

                                    <span class="epicrisis-md-separator"></span>

                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="ul"
                                            title="Маркированный список">
                                        <i class="bi bi-list-ul"></i>
                                    </button>

                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="ol"
                                            title="Нумерованный список">
                                        <i class="bi bi-list-ol"></i>
                                    </button>

                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="quote"
                                            title="Цитата / примечание">
                                        <i class="bi bi-blockquote-left"></i>
                                    </button>

                                    <span class="epicrisis-md-separator"></span>

                                    <button type="button"
                                            class="epicrisis-md-btn"
                                            data-md-action="clear"
                                            title="Очистить Markdown-разметку в выделении">
                                        <i class="bi bi-eraser"></i>
                                    </button>
                                </div>
                            </div>

                            <textarea id="epicrisisTextValue"
                                      name="text"
                                      class="epicrisis-doc-codemirror">{{ $preparedEpicrisisText }}</textarea>
                        </div>
                    </main>

                </div>

                <div class="epicrisis-actions">
                    <a href="{{ route('doctors.patients.epicrisis.show', [$patient->id, $epicrisis->id]) }}"
                       class="btn btn-light">
                        Отмена
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('epicrisisEditForm');
            const textValue = document.getElementById('epicrisisTextValue');
            const insertTemplateButton = document.getElementById('epicrisisInsertTemplate');

            const diagnosisSelect = document.getElementById('epicrisisDiagnosisSelect');
            const diagnosisIdInput = document.getElementById('epicrisisDiagnosisId');
            const mkb10Input = document.getElementById('epicrisisMkb10');
            const diagnosisTitleInput = document.getElementById('epicrisisDiagnosisTitle');

            const selectedDiagnosisCode = document.getElementById('epicrisisSelectedDiagnosisCode');
            const selectedDiagnosisTitle = document.getElementById('epicrisisSelectedDiagnosisTitle');

            let epicrisisMirror = null;

            if (textValue && window.CodeMirror) {
                epicrisisMirror = CodeMirror.fromTextArea(textValue, {
                    mode: 'markdown',
                    theme: 'eclipse',
                    lineWrapping: true,
                    styleActiveLine: true,
                    viewportMargin: Infinity,
                    tabSize: 4,
                    indentUnit: 4
                });
            }

            function saveEditor() {
                if (epicrisisMirror) {
                    epicrisisMirror.save();
                }
            }

            function replaceSelection(before, after, placeholder) {
                if (!epicrisisMirror) {
                    return;
                }

                const doc = epicrisisMirror.getDoc();
                const selection = doc.getSelection();
                const text = selection || placeholder;

                doc.replaceSelection(before + text + after);

                if (!selection) {
                    const cursor = doc.getCursor();

                    const startCh = cursor.ch - after.length - placeholder.length;
                    const endCh = cursor.ch - after.length;

                    doc.setSelection(
                        { line: cursor.line, ch: startCh },
                        { line: cursor.line, ch: endCh }
                    );
                }

                epicrisisMirror.focus();
                saveEditor();
            }

            function prefixSelectedLines(prefix, removePattern = null) {
                if (!epicrisisMirror) {
                    return;
                }

                const doc = epicrisisMirror.getDoc();
                const from = doc.getCursor('from');
                const to = doc.getCursor('to');

                const startLine = from.line;
                const endLine = to.ch === 0 && to.line > from.line ? to.line - 1 : to.line;

                epicrisisMirror.operation(function () {
                    for (let line = startLine; line <= endLine; line++) {
                        let lineText = doc.getLine(line);

                        if (removePattern) {
                            lineText = lineText.replace(removePattern, '');
                        }

                        doc.replaceRange(
                            prefix + lineText,
                            { line: line, ch: 0 },
                            { line: line, ch: doc.getLine(line).length }
                        );
                    }
                });

                epicrisisMirror.focus();
                saveEditor();
            }

            function clearMarkdownInSelection() {
                if (!epicrisisMirror) {
                    return;
                }

                const doc = epicrisisMirror.getDoc();
                const selection = doc.getSelection();

                if (!selection) {
                    return;
                }

                let cleaned = selection
                    .replace(/\*\*(.*?)\*\*/g, '$1')
                    .replace(/\*(.*?)\*/g, '$1')
                    .replace(/^#{1,6}\s+/gm, '')
                    .replace(/^>\s+/gm, '')
                    .replace(/^(\s*)([-*+]|\d+\.)\s+/gm, '$1');

                doc.replaceSelection(cleaned);
                epicrisisMirror.focus();
                saveEditor();
            }

            function insertTemplate() {
                if (!epicrisisMirror) {
                    return;
                }

                const currentText = epicrisisMirror.getValue().trim();

                if (currentText.length > 0) {
                    const confirmed = confirm('Текущий текст эпикриза будет заменён шаблоном. Продолжить?');

                    if (!confirmed) {
                        return;
                    }
                }

                epicrisisMirror.setValue(
                    `### Жалобы
Пациент предъявляет жалобы на...

### Анамнез заболевания
Со слов пациента, ухудшение состояния отмечается...

### Анамнез жизни
Наследственность, перенесённые заболевания, аллергологический анамнез...

### Объективный статус
Общее состояние...

### Психический статус
Сознание ясное. Контакт доступен. Ориентировка...

### Проведённое лечение
Проводилась терапия...

### Динамика состояния
На фоне проведённого лечения отмечается...

### Заключение
Состояние пациента на момент составления эпикриза...

### Рекомендации
1. Продолжить назначенную терапию.
2. Соблюдать режим труда и отдыха.
3. Повторная консультация врача.`
                );

                epicrisisMirror.focus();
                saveEditor();
            }

            document.querySelectorAll('[data-md-action]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const action = button.dataset.mdAction;

                    switch (action) {
                        case 'bold':
                            replaceSelection('**', '**', 'жирный текст');
                            break;

                        case 'italic':
                            replaceSelection('*', '*', 'курсив');
                            break;

                        case 'h3':
                            prefixSelectedLines('### ', /^#{1,6}\s+/);
                            break;

                        case 'ul':
                            prefixSelectedLines('- ', /^(\s*)([-*+]|\d+\.)\s+/);
                            break;

                        case 'ol':
                            prefixSelectedLines('1. ', /^(\s*)([-*+]|\d+\.)\s+/);
                            break;

                        case 'quote':
                            prefixSelectedLines('> ', /^>\s+/);
                            break;

                        case 'clear':
                            clearMarkdownInSelection();
                            break;
                    }
                });
            });

            if (insertTemplateButton) {
                insertTemplateButton.addEventListener('click', insertTemplate);
            }

            if (form) {
                form.addEventListener('submit', function () {
                    saveEditor();
                });
            }

            function updateSelectedDiagnosis(data) {
                const id = data && data.id ? data.id : '';
                const code = data && data.code ? data.code : '';
                const title = data && data.title ? data.title : '';

                if (diagnosisIdInput) {
                    diagnosisIdInput.value = id;
                }

                if (mkb10Input) {
                    mkb10Input.value = code;
                }

                if (diagnosisTitleInput) {
                    diagnosisTitleInput.value = title;
                }

                if (selectedDiagnosisCode) {
                    selectedDiagnosisCode.textContent = code || '—';
                }

                if (selectedDiagnosisTitle) {
                    selectedDiagnosisTitle.textContent = title || 'Диагноз не выбран';
                }
            }

            function normalizeDiagnosisItem(item) {
                if (!item) {
                    return null;
                }

                const code = item.code || item.mkb10 || item.icd10 || '';
                const title = item.title || item.name || item.text || item.diagnosis_title || '';

                return {
                    id: item.id || item.value || '',
                    code: code,
                    title: title,
                    text: code && title ? code + ' — ' + title : (title || code || '')
                };
            }

            function getDiagnosisResults(data) {
                let items = [];

                if (Array.isArray(data)) {
                    items = data;
                } else if (data && Array.isArray(data.results)) {
                    items = data.results;
                } else if (data && Array.isArray(data.data)) {
                    items = data.data;
                } else if (data && data.items && Array.isArray(data.items)) {
                    items = data.items;
                }

                return items
                    .map(normalizeDiagnosisItem)
                    .filter(function (item) {
                        return item && item.id;
                    });
            }

            if (diagnosisSelect && window.jQuery && jQuery.fn.select2) {
                const $select = jQuery(diagnosisSelect);

                $select.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: diagnosisSelect.dataset.placeholder || 'Начните вводить диагноз',
                    ajax: {
                        url: form ? form.dataset.diagnosesUrl : '/api/diagnoses',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return {
                                q: params.term || '',
                                search: params.term || '',
                                term: params.term || ''
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: getDiagnosisResults(data)
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1,
                    language: {
                        inputTooShort: function () {
                            return 'Введите минимум 1 символ';
                        },
                        searching: function () {
                            return 'Поиск диагноза...';
                        },
                        noResults: function () {
                            return 'Диагноз не найден';
                        },
                        errorLoading: function () {
                            return 'Не удалось загрузить диагнозы';
                        },
                        loadingMore: function () {
                            return 'Загрузка...';
                        },
                        removeAllItems: function () {
                            return 'Очистить';
                        }
                    },
                    templateResult: function (item) {
                        if (item.loading) {
                            return item.text;
                        }

                        const code = item.code || '';
                        const title = item.title || item.text || '';

                        const $result = jQuery(`
                            <div style="padding: 4px 0;">
                                <div style="font-weight: 700; color: #111827;"></div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;"></div>
                            </div>
                        `);

                        $result.find('div:first').text(code || 'Без кода');
                        $result.find('div:last').text(title || 'Без названия');

                        return $result;
                    },
                    templateSelection: function (item) {
                        if (!item.id) {
                            return item.text;
                        }

                        const code = item.code || '';
                        const title = item.title || item.text || '';

                        if (code && title) {
                            return code + ' — ' + title;
                        }

                        return title || code || item.text;
                    }
                });

                $select.on('select2:select', function (event) {
                    const item = event.params.data;

                    updateSelectedDiagnosis({
                        id: item.id,
                        code: item.code || '',
                        title: item.title || item.text || ''
                    });
                });

                $select.on('select2:clear', function () {
                    updateSelectedDiagnosis({
                        id: '',
                        code: '',
                        title: ''
                    });
                });

                const selectedOption = diagnosisSelect.options[diagnosisSelect.selectedIndex];

                if (selectedOption && selectedOption.value) {
                    const currentCode = mkb10Input ? mkb10Input.value : '';
                    const currentTitle = diagnosisTitleInput ? diagnosisTitleInput.value : '';

                    updateSelectedDiagnosis({
                        id: selectedOption.value,
                        code: currentCode,
                        title: currentTitle
                    });
                }
            } else if (diagnosisSelect) {
                diagnosisSelect.addEventListener('change', function () {
                    const selectedOption = diagnosisSelect.options[diagnosisSelect.selectedIndex];

                    if (!selectedOption) {
                        updateSelectedDiagnosis({
                            id: '',
                            code: '',
                            title: ''
                        });

                        return;
                    }

                    const text = selectedOption.textContent.trim();
                    const parts = text.split('—');

                    updateSelectedDiagnosis({
                        id: selectedOption.value,
                        code: parts[0] ? parts[0].trim() : '',
                        title: parts[1] ? parts.slice(1).join('—').trim() : text
                    });
                });
            }
        });
    </script>
@endsection
