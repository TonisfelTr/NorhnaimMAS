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
        </div>

        @if($activeTab === 'mine')
            <section class="patient-documents-upload-card">
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
                            <input class="form-control"
                                   type="file"
                                   id="patientDocumentsFiles"
                                   name="documents[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt,application/pdf,image/jpeg,image/png"
                                   multiple
                                   required>
                            <div class="form-text">До 10 файлов за один раз. PDF, JPG, PNG, DOC, DOCX или TXT. Не более 20 МБ на файл.</div>
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
        @endif

        <section class="patient-documents-list-card">
            <div class="patient-documents-list-card__header">
                <div>
                    <h2>{{ $activeTab === 'clinic' ? 'Документы от клиники' : 'Загруженные мной документы' }}</h2>
                    <p>{{ $activeTab === 'clinic' ? 'Файлы, которые предоставила вам клиника.' : 'Ваши файлы, добавленные в медицинский архив.' }}</p>
                </div>
                <form method="GET" action="{{ route('patients.documents.index') }}" class="patient-documents-search">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <input class="form-control" type="search" name="q" value="{{ $search }}" placeholder="Поиск документов">
                    <button class="btn btn-light" type="submit" aria-label="Найти"><i class="bi bi-search"></i></button>
                </form>
            </div>

            @if($documents->count())
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
                                @if($activeTab === 'mine')
                                    @if($document->reviewed_at)
                                        <span class="patient-document-status is-reviewed"><i class="bi bi-check-circle"></i> Врач ознакомился {{ $document->reviewed_at->format('d.m.Y') }}</span>
                                    @else
                                        <span class="patient-document-status is-pending"><i class="bi bi-clock"></i> Ожидает ознакомления врача</span>
                                    @endif
                                @else
                                    <span class="patient-document-status is-clinic"><i class="bi bi-hospital"></i> Документ от клиники</span>
                                @endif
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
                                @if($activeTab === 'mine')
                                    <form method="POST"
                                          action="{{ route('patients.documents.destroy', $document->id) }}"
                                          onsubmit="return confirm('Удалить этот документ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit" title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="patient-documents-pagination">{{ $documents->links() }}</div>
            @else
                <div class="patient-documents-empty">
                    <span><i class="bi bi-folder2-open"></i></span>
                    <h3>Документов пока нет</h3>
                    <p>{{ $activeTab === 'clinic' ? 'Когда клиника добавит документы, они появятся здесь.' : 'Загрузите результаты обследований или заключения из другой клиники.' }}</p>
                    @if($activeTab === 'clinic')
                        <a href="{{ route('patients.documents.index', ['tab' => 'mine']) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-upload me-1"></i> Добавить свой документ
                        </a>
                    @endif
                </div>
            @endif
        </section>
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
        .patient-documents-upload-card,.patient-documents-list-card { background:#fff; border:1px solid #e5eeea; border-radius:18px; padding:22px; margin-bottom:18px; box-shadow:0 7px 25px rgba(31,67,60,.035); }
        .patient-documents-upload-card__intro { display:flex; gap:13px; align-items:center; margin-bottom:18px; }
        .patient-documents-upload-card__icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; background:#e9f5f1; color:#168879; font-size:21px; }
        .patient-documents-upload-card h2,.patient-documents-list-card h2 { font-size:16px; font-weight:800; color:#244640; margin:0 0 4px; }
        .patient-documents-upload-card p,.patient-documents-list-card__header p { color:#71817e; font-size:13px; margin:0; }
        .patient-documents-upload-note { display:flex; gap:9px; padding:12px 14px; margin-top:15px; background:#f1f8f5; border:1px solid #e0eee8; border-radius:11px; color:#526d64; font-size:12px; line-height:1.5; }
        .patient-documents-upload-note i { color:#168879; font-size:16px; }
        .patient-documents-upload-button { background:#168879; border-color:#168879; }
        .patient-documents-list-card__header { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:12px; }
        .patient-documents-search { display:flex; gap:6px; width:min(100%,330px); }
        .patient-documents-search .form-control { font-size:13px; }
        .patient-documents-list { border-top:1px solid #edf2ef; }
        .patient-document-row { display:flex; align-items:center; gap:14px; padding:16px 2px; border-bottom:1px solid #edf2ef; }
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
        @media(max-width:760px) {
            .patient-documents-stats { grid-template-columns:1fr; }
            .patient-documents-list-card__header { align-items:stretch; flex-direction:column; }
            .patient-documents-search { width:100%; }
            .patient-document-row { align-items:flex-start; flex-wrap:wrap; }
            .patient-document-row__main { flex-basis:calc(100% - 60px); }
            .patient-document-row__actions { width:100%; justify-content:flex-start; padding-left:58px; }
            .patient-documents-upload-card,.patient-documents-list-card { padding:16px; }
        }
    </style>
@endsection
