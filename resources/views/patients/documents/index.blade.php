@extends('layouts.patient')

@section('title', 'Документы')

@section('content')
    <div class="patient-page patient-documents-page">
        <header class="patient-page-header">
            <div>
                <div class="patient-page-header__eyebrow">Личный кабинет пациента</div>
                <h1>Мои документы</h1>
                <p>Медицинские документы от клиники и файлы, которые вы добавили самостоятельно.</p>
            </div>
            <div class="patient-documents-header-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <strong>Не удалось выполнить операцию.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="patient-documents-stats">
            <a href="{{ route('patients.documents.index', ['tab' => 'clinic']) }}"
               class="patient-documents-stat {{ $activeTab === 'clinic' ? 'is-active' : '' }}">
                <span class="patient-documents-stat__icon"><i class="bi bi-hospital"></i></span>
                <span><strong>{{ $clinicCount }}</strong><small>От клиники</small></span>
            </a>
            <a href="{{ route('patients.documents.index', ['tab' => 'mine']) }}"
               class="patient-documents-stat {{ $activeTab === 'mine' ? 'is-active' : '' }}">
                <span class="patient-documents-stat__icon"><i class="bi bi-folder-plus"></i></span>
                <span><strong>{{ $patientCount }}</strong><small>Мои документы</small></span>
            </a>
            <div class="patient-documents-stat">
                <span class="patient-documents-stat__icon is-warning"><i class="bi bi-clock-history"></i></span>
                <span><strong>{{ $pendingReviewCount }}</strong><small>Ожидают ознакомления врача</small></span>
            </div>
        </div>

        <div class="patient-documents-tabs">
            <a href="{{ route('patients.documents.index', ['tab' => 'clinic']) }}"
               class="{{ $activeTab === 'clinic' ? 'is-active' : '' }}">
                <i class="bi bi-hospital"></i> От клиники
            </a>
            <a href="{{ route('patients.documents.index', ['tab' => 'mine']) }}"
               class="{{ $activeTab === 'mine' ? 'is-active' : '' }}">
                <i class="bi bi-cloud-arrow-up"></i> Мои документы
            </a>
            <a href="{{ route('patients.documents.index', ['tab' => 'history']) }}"
               class="{{ $activeTab === 'history' ? 'is-active' : '' }}">
                <i class="bi bi-clock-history"></i> История загрузок
                <span class="patient-documents-tabs__count">{{ $historyCount }}</span>
            </a>
        </div>

        @if($activeTab === 'mine')
            <section class="patient-documents-my-card">
                <div class="patient-documents-upload-card__intro">
                    <span class="patient-documents-upload-card__icon"><i class="bi bi-cloud-arrow-up"></i></span>
                    <div>
                        <h2>Добавить документы</h2>
                        <p>Загрузите результаты обследований или заключения из других медицинских организаций.</p>
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('patients.documents.upload') }}"
                      enctype="multipart/form-data"
                      class="patient-documents-upload-form">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="documentCategory">Категория</label>
                            <select class="form-select" id="documentCategory" name="category" required>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', 'other') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="documentDescription">Комментарий (необязательно)</label>
                            <input class="form-control"
                                   id="documentDescription"
                                   name="description"
                                   maxlength="1000"
                                   value="{{ old('description') }}"
                                   placeholder="Например: обследование от 15 ноября">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="patientDocumentsFiles">Файлы</label>

                            <div class="patient-documents-dropzone"
                                 id="patientDocumentsDropzone"
                                 role="button"
                                 tabindex="0"
                                 aria-label="Выбрать или перетащить медицинские документы"
                                 aria-controls="patientDocumentsFiles">
                                <input class="patient-documents-dropzone__input"
                                       type="file"
                                       id="patientDocumentsFiles"
                                       name="documents[]"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt,application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain"
                                       multiple
                                       tabindex="-1"
                                       aria-label="Файлы для загрузки">

                                <span class="patient-documents-dropzone__icon">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </span>
                                <span class="patient-documents-dropzone__title">Перетащите файлы сюда</span>
                                <span class="patient-documents-dropzone__subtitle">или <span>нажмите, чтобы выбрать</span></span>
                                <span class="patient-documents-dropzone__formats">PDF, JPG, PNG, DOC, DOCX, TXT</span>
                            </div>

                            <div class="patient-documents-selected" id="patientDocumentsSelected" hidden>
                                <div class="patient-documents-selected__header">
                                    <span>Выбрано файлов: <strong id="patientDocumentsFileCount">0</strong> из 10</span>
                                    <button type="button" class="patient-documents-selected__clear" id="patientDocumentsClear">Очистить</button>
                                </div>
                                <ul class="patient-documents-file-list" id="patientDocumentsFileList"></ul>
                            </div>

                            <div class="patient-documents-dropzone-message" id="patientDocumentsMessage" role="status" aria-live="polite" hidden></div>
                            <div class="form-text" id="patientDocumentsFilesHelp">До 10 файлов за один раз. PDF, JPG, PNG, DOC, DOCX или TXT. Не более 20 МБ на файл.</div>
                        </div>
                    </div>

                    <div class="patient-documents-upload-note">
                        <i class="bi bi-info-circle"></i>
                        Загруженные вами документы будут доступны вашему врачу в медицинской карте с отметкой «Загружено пациентом». Загрузка не означает, что документ уже проверен клиникой.
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary patient-documents-upload-button" type="submit">
                            <i class="bi bi-upload me-1"></i> Загрузить документы
                        </button>
                    </div>
                </form>

            </section>
        @else
            <section class="patient-documents-list-card">
                <div class="patient-documents-list-card__header">
                    <div>
                        @if($activeTab === 'history')
                            <div class="patient-documents-history__title-row">
                                <h2>История загрузок</h2>
                                <span class="patient-documents-history__count">{{ $historyCount }}</span>
                            </div>
                            <p>Единый журнал документов, загруженных вами и врачами клиники.</p>
                        @else
                            <h2>Документы от клиники</h2>
                            <p>Файлы, которые предоставила вам клиника.</p>
                        @endif
                    </div>

                    @if(($activeTab === 'history' && ($historyCount > 0 || filled($search))) || ($activeTab === 'clinic' && ($clinicCount > 0 || filled($search))))
                        <form method="GET" action="{{ route('patients.documents.index') }}" class="patient-documents-search">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <input class="form-control"
                                   type="search"
                                   name="q"
                                   value="{{ $search }}"
                                   placeholder="{{ $activeTab === 'history' ? 'Поиск по документу или врачу' : 'Поиск документов' }}">
                            <button class="btn btn-light" type="submit" aria-label="Найти"><i class="bi bi-search"></i></button>
                        </form>
                    @endif
                </div>

                @if($activeTab === 'history')
                    <div class="patient-documents-history-legend">
                        <span class="patient-documents-actor is-patient"><i class="bi bi-person"></i> Загружено пациентом</span>
                        <span class="patient-documents-actor is-doctor"><i class="bi bi-stethoscope"></i> Загружено врачом</span>
                    </div>
                @endif

                @if($documents->count())
                    @if($activeTab === 'history')
                        <div class="table-responsive patient-documents-audit-wrap">
                            <table class="table align-middle patient-documents-audit mb-0">
                                <thead>
                                <tr>
                                    <th>Документ</th>
                                    <th>Кто загрузил</th>
                                    <th>Дата загрузки</th>
                                    <th>Статус</th>
                                    <th class="text-end">Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($documents as $document)
                                    @php
                                        $extension = strtolower(pathinfo($document->original_name ?: $document->storage_path, PATHINFO_EXTENSION));
                                        $icon = match($extension) {
                                            'pdf' => 'bi-filetype-pdf',
                                            'jpg', 'jpeg', 'png' => 'bi-file-earmark-image',
                                            'doc', 'docx' => 'bi-filetype-doc',
                                            'txt' => 'bi-filetype-txt',
                                            default => 'bi-file-earmark',
                                        };
                                        $categoryLabel = $categories[$document->category ?? 'other'] ?? 'Другие документы';
                                        $isPatientUpload = ($document->source ?? 'clinic') === 'patient';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="patient-documents-audit-file">
                                                <span class="patient-documents-audit-file__icon"><i class="bi {{ $icon }}"></i></span>
                                                <span class="patient-documents-audit-file__copy">
                                                    <strong>{{ $document->name ?: $document->original_name ?: 'Документ' }}</strong>
                                                    <small>{{ $categoryLabel }}@if($document->file_size) · {{ number_format($document->file_size / 1048576, 2, ',', ' ') }} МБ @endif</small>
                                                    @if($document->description)
                                                        <small class="patient-documents-audit-file__description">{{ $document->description }}</small>
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($isPatientUpload)
                                                <span class="patient-documents-actor is-patient"><i class="bi bi-person"></i> Пациент</span>
                                            @elseif($document->doctor)
                                                <span class="patient-documents-actor is-doctor"><i class="bi bi-stethoscope"></i> Врач</span>
                                                <small class="patient-documents-audit-uploader">{{ $document->doctor->full_name }}</small>
                                            @else
                                                <span class="patient-documents-actor is-clinic"><i class="bi bi-hospital"></i> Клиника</span>
                                            @endif
                                        </td>
                                        <td class="patient-documents-audit-date">{{ $document->created_at?->format('d.m.Y') }}<small>{{ $document->created_at?->format('H:i') }}</small></td>
                                        <td>
                                            @if($isPatientUpload && $document->reviewed_at)
                                                <span class="patient-document-status is-reviewed"><i class="bi bi-check-circle"></i> Ознакомлен</span>
                                            @elseif($isPatientUpload)
                                                <span class="patient-document-status is-pending"><i class="bi bi-clock"></i> Ожидает врача</span>
                                            @else
                                                <span class="patient-document-status is-clinic"><i class="bi bi-check-circle"></i> В архиве клиники</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="patient-documents-audit-actions">
                                                @if(in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true))
                                                    <a class="btn btn-light btn-sm"
                                                       href="{{ route('patients.documents.preview', $document->id) }}"
                                                       target="_blank" rel="noopener" title="Открыть" aria-label="Открыть документ">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endif
                                                <a class="btn btn-light btn-sm"
                                                   href="{{ route('patients.documents.download', $document->id) }}"
                                                   title="Скачать" aria-label="Скачать документ">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @if($isPatientUpload && (int) $document->uploaded_by_user_id === (int) auth()->id())
                                                    <form method="POST"
                                                          action="{{ route('patients.documents.destroy', $document->id) }}"
                                                          onsubmit="return confirm('Удалить этот документ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-light btn-sm patient-documents-audit-delete" type="submit" title="Удалить" aria-label="Удалить документ">
                                                            <i class="bi bi-trash"></i>
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
                        <div class="patient-documents-list">
                            @foreach($documents as $document)
                                @php
                                    $extension = strtolower(pathinfo($document->original_name ?: $document->storage_path, PATHINFO_EXTENSION));
                                    $icon = match($extension) {
                                        'pdf' => 'bi-filetype-pdf',
                                        'jpg', 'jpeg', 'png' => 'bi-file-earmark-image',
                                        'doc', 'docx' => 'bi-filetype-doc',
                                        'txt' => 'bi-filetype-txt',
                                        default => 'bi-file-earmark',
                                    };
                                    $categoryLabel = $categories[$document->category ?? 'other'] ?? 'Другие документы';
                                @endphp
                                <article class="patient-document-row">
                                    <div class="patient-document-row__icon"><i class="bi {{ $icon }}"></i></div>
                                    <div class="patient-document-row__main">
                                        <div class="patient-document-row__title">{{ $document->name }}</div>
                                        <div class="patient-document-row__meta">
                                            <span>{{ $categoryLabel }}</span>
                                            <span>{{ $document->created_at?->format('d.m.Y') }}</span>
                                            @if($document->file_size)
                                                <span>{{ number_format($document->file_size / 1048576, 2, ',', ' ') }} МБ</span>
                                            @endif
                                        </div>
                                        @if($document->description)
                                            <p class="patient-document-row__description">{{ $document->description }}</p>
                                        @endif
                                        <span class="patient-document-status is-clinic"><i class="bi bi-hospital"></i> Документ от клиники</span>
                                    </div>
                                    <div class="patient-document-row__actions">
                                        @if(in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true))
                                            <a class="btn btn-light btn-sm"
                                               href="{{ route('patients.documents.preview', $document->id) }}"
                                               target="_blank" rel="noopener" title="Открыть">
                                                <i class="bi bi-eye"></i><span>Открыть</span>
                                            </a>
                                        @endif
                                        <a class="btn btn-outline-primary btn-sm"
                                           href="{{ route('patients.documents.download', $document->id) }}"
                                           title="Скачать">
                                            <i class="bi bi-download"></i><span>Скачать</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @if($documents->hasPages())
                        <div class="patient-documents-pagination patient-documents-pagination--history">
                            <div class="patient-documents-pagination__summary">
                                Показано {{ $documents->firstItem() }}–{{ $documents->lastItem() }} из {{ $documents->total() }}
                            </div>
                            <div class="patient-documents-pagination__links">
                                {{ $documents->links() }}
                            </div>
                        </div>
                    @endif
                @else
                    @if(filled($search))
                        <div class="patient-documents-empty patient-documents-empty--center">
                            <span><i class="bi bi-search"></i></span>
                            <h3>Ничего не найдено</h3>
                            <p>По запросу «{{ $search }}» документов нет.</p>
                            <a href="{{ route('patients.documents.index', ['tab' => $activeTab]) }}" class="patient-documents-empty__reset">Сбросить поиск</a>
                        </div>
                    @elseif($activeTab === 'history')
                        <div class="patient-documents-empty patient-documents-empty--center">
                            <span><i class="bi bi-folder2-open"></i></span>
                            <h3>Пока нет загруженных документов</h3>
                            <p>Здесь появятся документы, загруженные вами и врачами клиники.</p>
                        </div>
                    @else
                        <div class="patient-documents-empty patient-documents-empty--center">
                            <span><i class="bi bi-folder2-open"></i></span>
                            <h3>Документов от клиники пока нет</h3>
                            <p>Когда клиника добавит документы, они появятся здесь.</p>
                            <a href="{{ route('patients.documents.index', ['tab' => 'mine']) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-upload me-1"></i> Добавить свой документ
                            </a>
                        </div>
                    @endif
                @endif
            </section>
        @endif
    </div>
@endsection

@section('assets')
    <style>
        .patient-documents-page { max-width: 1120px; }
        .patient-documents-header-icon { width: 58px; height: 58px; border-radius: 18px; display:flex; align-items:center; justify-content:center; background:#e9f5f1; color:#168879; font-size:27px; flex:0 0 auto; }
        .patient-documents-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin:22px 0; }
        .patient-documents-stat { display:flex; align-items:center; gap:13px; min-width:0; padding:16px; background:#fff; border:1px solid #e5eeea; border-radius:15px; color:#294a43; text-decoration:none; }
        .patient-documents-stat.is-active { border-color:#78b9aa; box-shadow:0 0 0 3px rgba(22,136,121,.08); }
        .patient-documents-stat__icon { width:42px; height:42px; flex:0 0 42px; border-radius:13px; display:flex; align-items:center; justify-content:center; background:#e9f5f1; color:#168879; font-size:19px; }
        .patient-documents-stat__icon.is-warning { background:#fff5df; color:#a66b00; }
        .patient-documents-stat strong { display:block; font-size:22px; line-height:1.2; }
        .patient-documents-stat small { display:block; color:#71817e; font-size:12px; margin-top:3px; }
        .patient-documents-tabs { display:flex; gap:8px; border-bottom:1px solid #e2ebe6; margin-bottom:18px; }
        .patient-documents-tabs a { display:inline-flex; align-items:center; gap:8px; padding:12px 16px; color:#60736c; text-decoration:none; border-bottom:2px solid transparent; font-weight:700; font-size:14px; }
        .patient-documents-tabs a.is-active { color:#168879; border-color:#168879; }
        .patient-documents-my-card,.patient-documents-list-card { background:#fff; border:1px solid #e5eeea; border-radius:18px; padding:22px; margin-bottom:18px; box-shadow:0 7px 25px rgba(31,67,60,.035); }
        .patient-documents-history { margin-top:20px; padding:16px; border:1px solid #e6efea; border-radius:14px; background:#f8fbf9; }
        .patient-documents-history__header { display:flex; justify-content:space-between; align-items:center; gap:16px; padding-bottom:13px; margin-bottom:10px; border-bottom:1px solid #e7efeb; }
        .patient-documents-history__heading { display:flex; align-items:center; gap:11px; min-width:0; }
        .patient-documents-history__icon { display:inline-flex; align-items:center; justify-content:center; flex:0 0 38px; width:38px; height:38px; border-radius:11px; background:#e9f5f1; color:#168879; font-size:18px; }
        .patient-documents-history__heading-copy { min-width:0; }
        .patient-documents-history__title-row { display:flex; align-items:center; gap:8px; margin-bottom:3px; }
        .patient-documents-history__title-row h2 { margin:0; color:#244640; font-size:15px; font-weight:800; }
        .patient-documents-history__header p { color:#71817e; font-size:12px; margin:0; }
        .patient-documents-history__count { display:inline-flex; min-width:24px; height:21px; align-items:center; justify-content:center; padding:0 7px; border-radius:20px; background:#e8f3ee; color:#168879; font-size:11px; font-weight:750; font-variant-numeric:tabular-nums; }
        .patient-documents-empty--compact { display:flex; align-items:center; gap:12px; padding:13px 14px; border:1px dashed #dce9e3; border-radius:11px; background:rgba(255,255,255,.76); text-align:left; }
        .patient-documents-empty--compact>span { display:inline-flex; align-items:center; justify-content:center; flex:0 0 40px; width:40px; height:40px; margin:0; border-radius:11px; background:#edf5f1; color:#84978f; font-size:19px; }
        .patient-documents-empty__content { min-width:0; }
        .patient-documents-empty--compact h3 { color:#294a43; font-size:13px; font-weight:750; margin:0 0 3px; }
        .patient-documents-empty--compact p { color:#71817e; font-size:12px; margin:0; }
        .patient-documents-empty__reset { display:inline-block; margin-top:8px; color:#168879; font-size:12px; font-weight:700; text-decoration:none; }
        .patient-documents-empty__reset:hover { text-decoration:underline; }
        .patient-documents-upload-card__intro { display:flex; gap:13px; align-items:center; margin-bottom:18px; }
        .patient-documents-upload-card__icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; background:#e9f5f1; color:#168879; font-size:21px; }
        .patient-documents-my-card h2,.patient-documents-list-card h2 { font-size:16px; font-weight:800; color:#244640; margin:0 0 4px; }
        .patient-documents-my-card .patient-documents-upload-card__intro p,.patient-documents-history__header p,.patient-documents-list-card__header p { color:#71817e; font-size:13px; margin:0; }
        .patient-documents-upload-form .form-control,
        .patient-documents-upload-form .form-select,
        .patient-documents-search .form-control { font-size:13px; }
        .patient-documents-upload-form .form-label { font-size:14px; margin-bottom:7px; }
        .patient-documents-upload-form .form-text { font-size:12px; margin-top:7px; }
        .patient-documents-dropzone { position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; min-height:158px; padding:20px; overflow:hidden; text-align:center; cursor:pointer; border:1.5px dashed #168879; border-radius:13px; background:#f8fcfa; color:#294a43; transition:border-color .18s ease,background .18s ease,box-shadow .18s ease; }
        .patient-documents-dropzone:hover,.patient-documents-dropzone:focus-visible { border-color:#168879; background:#f1f9f6; outline:none; box-shadow:0 0 0 3px rgba(22,136,121,.09); }
        .patient-documents-dropzone.is-dragover { border-color:#168879; border-style:solid; background:#eaf7f2; box-shadow:0 0 0 3px rgba(22,136,121,.10); }
        .patient-documents-dropzone.has-files { border-color:#168879; background:#fbfefd; }
        .patient-documents-dropzone__input { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
        .patient-documents-dropzone__icon { display:flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:2px; border-radius:13px; background:#e6f4ef; color:#168879; font-size:22px; }
        .patient-documents-dropzone__title { font-size:14px; font-weight:750; color:#294a43; }
        .patient-documents-dropzone__subtitle { color:#71817e; font-size:13px; }
        .patient-documents-dropzone__subtitle span { color:#168879; font-weight:700; text-decoration:underline; text-underline-offset:2px; }
        .patient-documents-dropzone__formats { margin-top:3px; color:#8a9992; font-size:11px; }
        .patient-documents-selected { margin-top:10px; }
        .patient-documents-selected__header { display:flex; align-items:center; justify-content:space-between; gap:12px; color:#64766f; font-size:12px; margin-bottom:7px; }
        .patient-documents-selected__header strong { color:#294a43; }
        .patient-documents-selected__clear { padding:0; border:0; background:none; color:#168879; font-size:12px; cursor:pointer; }
        .patient-documents-selected__clear:hover { text-decoration:underline; }
        .patient-documents-file-list { display:grid; grid-template-columns:1fr; gap:6px; padding:0; margin:0; list-style:none; }
        .patient-documents-file-item { display:flex; align-items:center; gap:10px; min-width:0; padding:9px 11px; border:1px solid #e5eeea; border-radius:10px; background:#fff; }
        .patient-documents-file-item__icon { display:flex; align-items:center; justify-content:center; flex:0 0 32px; width:32px; height:34px; border-radius:8px; background:#eff6f3; color:#168879; font-size:17px; }
        .patient-documents-file-item__info { display:flex; flex:1; flex-direction:column; min-width:0; gap:2px; }
        .patient-documents-file-item__name { overflow:hidden; color:#294a43; font-size:12px; font-weight:650; text-overflow:ellipsis; white-space:nowrap; }
        .patient-documents-file-item__size { color:#84918c; font-size:11px; }
        .patient-documents-file-item__remove { display:flex; align-items:center; justify-content:center; flex:0 0 28px; width:28px; height:28px; border:0; border-radius:7px; background:transparent; color:#87958f; cursor:pointer; }
        .patient-documents-file-item__remove:hover { background:#fff0ef; color:#bd4a43; }
        .patient-documents-dropzone-message { margin-top:8px; padding:8px 10px; border-radius:8px; font-size:12px; line-height:1.45; }
        .patient-documents-dropzone-message.is-error { background:#fff2f0; color:#a33f36; }
        .patient-documents-dropzone-message.is-success { background:#eef8f3; color:#26734c; }
        .patient-documents-upload-note { display:flex; gap:9px; padding:12px 14px; margin-top:15px; background:#f1f8f5; border:1px solid #e0eee8; border-radius:11px; color:#526d64; font-size:12px; line-height:1.5; }
        .patient-documents-upload-note i { color:#168879; font-size:16px; }
        .patient-documents-upload-button { background:#168879; border-color:#168879; }
        .patient-documents-list-card__header { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:12px; }
        .patient-documents-search { display:flex; gap:6px; width:min(100%,330px); }
        .patient-documents-search .form-control { font-size:13px; }
        .patient-documents-list { border-top:1px solid #edf2ef; }
        .patient-documents-list--history { overflow:hidden; border:1px solid #e6efea; border-radius:11px; background:#fff; }
        .patient-document-row { display:flex; align-items:center; gap:14px; padding:16px 2px; border-bottom:1px solid #edf2ef; }
        .patient-documents-list--history .patient-document-row { padding:13px 14px; }
        .patient-documents-list--history .patient-document-row:last-child { border-bottom:0; }
        .patient-document-row__icon { width:44px; height:48px; flex:0 0 44px; display:flex; align-items:center; justify-content:center; border-radius:12px; background:#f0f5f3; color:#168879; font-size:23px; }
        .patient-document-row__main { flex:1; min-width:0; }
        .patient-document-row__title { font-size:15px; }
        .patient-document-row__title { color:#294a43; font-weight:750; overflow-wrap:anywhere; }
        .patient-document-row__meta { display:flex; flex-wrap:wrap; gap:6px 13px; color:#84918c; font-size:11px; margin-top:4px; }
        .patient-document-row__description { color:#64766f; font-size:12px; margin:6px 0 0; }
        .patient-document-status { display:inline-flex; align-items:center; gap:5px; margin-top:7px; padding:4px 8px; border-radius:20px; font-size:10px; font-weight:700; }
        .patient-document-status.is-reviewed { background:#eaf6ef; color:#26734c; }
        .patient-document-status.is-pending { background:#fff5df; color:#986500; }
        .patient-document-status.is-clinic { background:#edf4ff; color:#42679b; }
        .patient-document-row__actions { display:flex; flex-wrap:wrap; justify-content:flex-end; align-items:center; gap:6px; }
        .patient-document-row__actions .btn { display:inline-flex; align-items:center; gap:6px; }
        .patient-documents-empty { text-align:center; padding:48px 15px; color:#71817e; }
        .patient-documents-empty>span { display:inline-flex; width:58px; height:58px; align-items:center; justify-content:center; border-radius:18px; background:#f0f5f3; color:#8a9a93; font-size:27px; margin-bottom:13px; }
        .patient-documents-empty h3 { color:#294a43; font-size:17px; font-weight:800; }
        .patient-documents-empty p { font-size:13px; margin-bottom:15px; }
        .patient-documents-pagination { padding-top:16px; }
        .patient-documents-pagination--history { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px 16px; padding-top:13px; margin-top:10px; border-top:1px solid #e7efeb; }
        .patient-documents-pagination__summary { color:#84918c; font-size:12px; white-space:nowrap; }
        .patient-documents-pagination--history .patient-documents-pagination__links { min-width:0; margin-left:auto; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav { margin:0; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav > div { gap:12px; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav p { margin:0; color:#71817e; font-size:12px; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav svg { width:15px; height:15px; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav a.relative.inline-flex,
        .patient-documents-pagination--history .patient-documents-pagination__links nav span.relative.inline-flex { min-width:32px; min-height:32px; align-items:center; justify-content:center; border-color:#e1ebe6; background:#fff; color:#60736c; font-size:12px; box-shadow:none; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav a.relative.inline-flex:hover { background:#edf7f2; color:#168879; }
        .patient-documents-pagination--history .patient-documents-pagination__links nav [aria-current="page"] span { border-color:#168879; background:#168879; color:#fff; }
        .patient-documents-pagination--history .patient-documents-pagination__links .pagination { gap:5px; margin:0; }
        .patient-documents-pagination--history .patient-documents-pagination__links .page-link { padding:6px 10px; border:1px solid #e1ebe6; border-radius:8px; color:#60736c; background:#fff; font-size:12px; box-shadow:none; }
        .patient-documents-pagination--history .patient-documents-pagination__links .page-link:hover { background:#edf7f2; color:#168879; }
        .patient-documents-pagination--history .patient-documents-pagination__links .page-item.active .page-link { border-color:#168879; background:#168879; color:#fff; }
        .patient-documents-pagination--history .patient-documents-pagination__links .page-item.disabled .page-link { color:#a7b3ad; background:#f7faf8; }
        .patient-documents-tabs__count { display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:19px; padding:0 6px; margin-left:1px; border-radius:20px; background:#edf5f1; color:#168879; font-size:10px; font-weight:750; }
        .patient-documents-history__title-row { display:flex; align-items:center; gap:8px; margin-bottom:3px; }
        .patient-documents-history__title-row h2 { margin:0; color:#244640; font-size:16px; font-weight:800; }
        .patient-documents-history__count { display:inline-flex; min-width:24px; height:21px; align-items:center; justify-content:center; padding:0 7px; border-radius:20px; background:#e8f3ee; color:#168879; font-size:11px; font-weight:750; font-variant-numeric:tabular-nums; }
        .patient-documents-history-legend { display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:0 0 13px; margin-bottom:4px; border-bottom:1px solid #edf2ef; }
        .patient-documents-actor { display:inline-flex; align-items:center; gap:5px; width:max-content; padding:5px 8px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; }
        .patient-documents-actor.is-patient { background:#eaf5f1; color:#168879; }
        .patient-documents-actor.is-doctor { background:#edf3fb; color:#4b6f9e; }
        .patient-documents-actor.is-clinic { background:#f1f4f2; color:#66776f; }
        .patient-documents-audit-wrap { border:1px solid #e7efeb; border-radius:12px; overflow:hidden; }
        .patient-documents-audit { min-width:780px; --bs-table-bg:transparent; }
        .patient-documents-audit thead th { padding:11px 12px; background:#f7faf8; color:#71817e; border-bottom:1px solid #e7efeb; font-size:11px; font-weight:750; white-space:nowrap; }
        .patient-documents-audit tbody td { padding:12px; border-color:#edf2ef; color:#526760; font-size:12px; vertical-align:middle; }
        .patient-documents-audit tbody tr:last-child td { border-bottom:0; }
        .patient-documents-audit-file { display:flex; align-items:center; gap:9px; min-width:220px; }
        .patient-documents-audit-file__icon { display:inline-flex; align-items:center; justify-content:center; flex:0 0 34px; width:34px; height:36px; border-radius:9px; background:#eff6f3; color:#168879; font-size:18px; }
        .patient-documents-audit-file__copy { display:flex; flex-direction:column; min-width:0; gap:3px; }
        .patient-documents-audit-file__copy strong { color:#294a43; font-size:12px; font-weight:750; overflow-wrap:anywhere; }
        .patient-documents-audit-file__copy small { color:#84918c; font-size:10px; }
        .patient-documents-audit-file__copy .patient-documents-audit-file__description { color:#64766f; font-size:11px; }
        .patient-documents-audit-uploader { display:block; max-width:180px; margin:5px 0 0; color:#71817e; font-size:10px; line-height:1.4; white-space:normal; }
        .patient-documents-audit-date { color:#526760; font-size:12px; font-variant-numeric:tabular-nums; white-space:nowrap; }
        .patient-documents-audit-date small { display:block; margin-top:3px; color:#84918c; font-size:10px; }
        .patient-documents-audit .patient-document-status { margin-top:0; }
        .patient-documents-audit-actions { display:flex; justify-content:flex-end; align-items:center; gap:5px; }
        .patient-documents-audit-actions .btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; padding:0; border:1px solid #e4ece7; color:#60736c; }
        .patient-documents-audit-actions .btn:hover { background:#edf7f2; color:#168879; }
        .patient-documents-audit-actions .patient-documents-audit-delete:hover { background:#fff1ef; border-color:#f3d8d4; color:#b64a40; }
        .patient-documents-empty--center { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:260px; padding:34px 20px; border:1px dashed #e0ebe5; border-radius:12px; background:#fbfdfc; text-align:center; }
        .patient-documents-empty--center>span { display:inline-flex; align-items:center; justify-content:center; width:58px; height:58px; margin:0 0 13px; border-radius:18px; background:#f0f5f3; color:#8a9a93; font-size:27px; }
        .patient-documents-empty--center h3 { margin:0 0 5px; color:#294a43; font-size:15px; font-weight:800; }
        .patient-documents-empty--center p { margin:0; color:#71817e; font-size:12px; }
        .patient-documents-empty--center .patient-documents-empty__reset { margin-top:9px; }
        @media(max-width:760px) {
            .patient-documents-stats { grid-template-columns:1fr; }
            .patient-documents-list-card__header { align-items:stretch; flex-direction:column; }
            .patient-documents-history-legend { align-items:flex-start; }
            .patient-documents-search { width:100%; }
            .patient-documents-pagination--history { align-items:flex-start; flex-direction:column; }
            .patient-documents-pagination--history .patient-documents-pagination__links { width:100%; margin-left:0; }
            .patient-documents-pagination--history .patient-documents-pagination__links nav { width:100%; }
            .patient-document-row { align-items:flex-start; flex-wrap:wrap; }
            .patient-document-row__main { flex-basis:calc(100% - 60px); }
            .patient-document-row__actions { width:100%; justify-content:flex-start; padding-left:58px; }
            .patient-documents-my-card,.patient-documents-list-card { padding:16px; }
            .patient-documents-dropzone { min-height:145px; padding:16px 12px; }
        }
    </style>

    <script>
        (() => {
            const initPatientDocumentsDropzone = () => {
                const form = document.querySelector('.patient-documents-upload-form');
                if (!form) return;

                const dropzone = document.getElementById('patientDocumentsDropzone');
                const input = document.getElementById('patientDocumentsFiles');
                const selectedBox = document.getElementById('patientDocumentsSelected');
                const fileList = document.getElementById('patientDocumentsFileList');
                const fileCount = document.getElementById('patientDocumentsFileCount');
                const clearButton = document.getElementById('patientDocumentsClear');
                const message = document.getElementById('patientDocumentsMessage');

                if (!dropzone || !input || !selectedBox || !fileList || !fileCount || !clearButton || !message) return;

                const maxFiles = 10;
                const maxFileSize = 20 * 1024 * 1024;
                const allowedExtensions = new Set(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'txt']);
                let selectedFiles = [];
                let dragDepth = 0;

                const extensionOf = (name) => {
                    const parts = String(name || '').toLowerCase().split('.');
                    return parts.length > 1 ? parts.pop() : '';
                };

                const formatSize = (bytes) => {
                    if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + ' КБ';
                    return (bytes / (1024 * 1024)).toFixed(2).replace('.', ',') + ' МБ';
                };

                const iconFor = (extension) => {
                    if (extension === 'pdf') return 'bi-filetype-pdf';
                    if (['jpg', 'jpeg', 'png'].includes(extension)) return 'bi-file-earmark-image';
                    if (['doc', 'docx'].includes(extension)) return 'bi-filetype-doc';
                    if (extension === 'txt') return 'bi-filetype-txt';
                    return 'bi-file-earmark';
                };

                const showMessage = (text, type) => {
                    message.textContent = text;
                    message.className = 'patient-documents-dropzone-message is-' + type;
                    message.hidden = !text;
                };

                const syncInputFiles = () => {
                    const transfer = new DataTransfer();
                    selectedFiles.forEach((file) => transfer.items.add(file));
                    input.files = transfer.files;
                };

                const renderFiles = () => {
                    syncInputFiles();
                    fileList.replaceChildren();
                    selectedFiles.forEach((file, index) => {
                        const extension = extensionOf(file.name);
                        const item = document.createElement('li');
                        item.className = 'patient-documents-file-item';

                        const icon = document.createElement('span');
                        icon.className = 'patient-documents-file-item__icon';
                        const iconElement = document.createElement('i');
                        iconElement.className = 'bi ' + iconFor(extension);
                        icon.appendChild(iconElement);

                        const info = document.createElement('span');
                        info.className = 'patient-documents-file-item__info';
                        const name = document.createElement('span');
                        name.className = 'patient-documents-file-item__name';
                        name.textContent = file.name;
                        name.title = file.name;
                        const size = document.createElement('span');
                        size.className = 'patient-documents-file-item__size';
                        size.textContent = formatSize(file.size);
                        info.append(name, size);

                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'patient-documents-file-item__remove';
                        remove.setAttribute('aria-label', 'Удалить файл ' + file.name);
                        const removeIcon = document.createElement('i');
                        removeIcon.className = 'bi bi-x-lg';
                        remove.appendChild(removeIcon);
                        remove.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            selectedFiles.splice(index, 1);
                            renderFiles();
                            showMessage('Файл удалён из списка.', 'success');
                        });

                        item.append(icon, info, remove);
                        fileList.appendChild(item);
                    });

                    fileCount.textContent = String(selectedFiles.length);
                    selectedBox.hidden = selectedFiles.length === 0;
                    dropzone.classList.toggle('has-files', selectedFiles.length > 0);
                };

                const addFiles = (fileCollection) => {
                    const incoming = Array.from(fileCollection || []);
                    if (!incoming.length) return;

                    const issues = [];
                    let added = 0;

                    incoming.forEach((file) => {
                        const extension = extensionOf(file.name);
                        if (!allowedExtensions.has(extension)) {
                            issues.push(file.name + ': неподдерживаемый формат.');
                            return;
                        }
                        if (file.size > maxFileSize) {
                            issues.push(file.name + ': размер больше 20 МБ.');
                            return;
                        }
                        const duplicate = selectedFiles.some((item) =>
                            item.name === file.name && item.size === file.size && item.lastModified === file.lastModified
                        );
                        if (duplicate) return;
                        if (selectedFiles.length >= maxFiles) {
                            issues.push('Можно выбрать не более 10 файлов за один раз.');
                            return;
                        }

                        selectedFiles.push(file);
                        added++;
                    });

                    renderFiles();

                    if (issues.length) {
                        showMessage(issues.join(' '), 'error');
                    } else if (added) {
                        showMessage('Добавлено файлов: ' + added + '.', 'success');
                    }
                };

                dropzone.addEventListener('click', (event) => {
                    if (event.target.closest('.patient-documents-file-item__remove')) return;
                    input.click();
                });

                dropzone.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        input.click();
                    }
                });

                input.addEventListener('change', () => {
                    addFiles(input.files);
                });

                ['dragenter', 'dragover'].forEach((eventName) => {
                    dropzone.addEventListener(eventName, (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
                        if (eventName === 'dragenter') dragDepth++;
                        dropzone.classList.add('is-dragover');
                    });
                });

                dropzone.addEventListener('dragleave', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    dragDepth = Math.max(0, dragDepth - 1);
                    if (dragDepth === 0) dropzone.classList.remove('is-dragover');
                });

                dropzone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    dragDepth = 0;
                    dropzone.classList.remove('is-dragover');
                    if (event.dataTransfer && event.dataTransfer.files) addFiles(event.dataTransfer.files);
                });

                clearButton.addEventListener('click', () => {
                    selectedFiles = [];
                    renderFiles();
                    showMessage('Список файлов очищен.', 'success');
                });

                form.addEventListener('submit', (event) => {
                    if (!selectedFiles.length) {
                        event.preventDefault();
                        showMessage('Добавьте хотя бы один файл для загрузки.', 'error');
                        dropzone.focus();
                        return;
                    }
                    syncInputFiles();
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPatientDocumentsDropzone);
            } else {
                initPatientDocumentsDropzone();
            }
        })();
    </script>
@endsection
