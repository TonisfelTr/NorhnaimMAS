@extends('doctors.settings')

@section('settings-title', 'Шапка документов')

@section('assets')
    @parent
    <style>
        .certificate-header-page .settings-container {
            width: min(1240px, 100%);
        }

        .certificate-header-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
            gap: 18px;
            align-items: start;
        }

        .certificate-header-preview {
            display: grid;
            min-height: 230px;
            place-items: center;
            padding: 24px;
            border: 1px dashed #cfdce3;
            border-radius: 16px;
            background: #fbfdfe;
        }

        .certificate-header-preview img {
            display: block;
            max-width: 100%;
            max-height: 210px;
            object-fit: contain;
        }

        .certificate-header-empty {
            max-width: 420px;
            color: var(--settings-muted);
            font-size: 13px;
            line-height: 1.55;
            text-align: center;
        }

        .certificate-header-family {
            display: grid;
            gap: 8px;
        }

        .certificate-header-family__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 12px;
            border: 1px solid var(--settings-border);
            border-radius: 12px;
            background: #fff;
        }

        .certificate-header-family__name {
            min-width: 0;
            overflow: hidden;
            font-size: 13px;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .certificate-header-family__type {
            flex: 0 0 auto;
            color: var(--settings-muted);
            font-size: 11px;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .certificate-header-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('sub-main')
    <main class="settings-page certificate-header-page">
        <div class="settings-container">
            <div class="settings-page-header">
                <div>
                    <h1 class="settings-page-title">
                        Шапка документов
                    </h1>
                    <p class="settings-page-description">
                        Единая шапка для справок и выписок головной клиники,
                        её диспансеров и стационаров. Файл хранится через
                        Spatie Media Library.
                    </p>
                </div>

                <div class="settings-type-badge">
                    <i class="bi bi-diagram-3"></i>
                    {{ $rootClinic->name }}
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->has('certificate_header'))
                <div class="alert alert-danger">
                    {{ $errors->first('certificate_header') }}
                </div>
            @endif

            <div class="certificate-header-grid">
                <div class="settings-panel">
                    <div class="settings-panel__header">
                        <div class="settings-panel__title-wrap">
                            <h2 class="settings-panel__title">
                                Шапка справок и выписок
                            </h2>
                            <div class="settings-panel__subtitle">
                                Загружается один файл на головную организацию.
                                Все связанные учреждения используют его автоматически.
                            </div>
                        </div>
                    </div>

                    <div class="settings-panel__body">
                        <div class="certificate-header-preview mb-4">
                            @if($headerMedia)
                                <img
                                    src="{{ route('doctors.settings.certificate-header.image') }}?v={{ $headerMedia->updated_at?->timestamp ?? time() }}"
                                    alt="Шапка документов"
                                >
                            @else
                                <div class="certificate-header-empty">
                                    <i class="bi bi-image fs-2 d-block mb-2"></i>
                                    Шапка пока не загружена.
                                    Справки и выписки печатаются без неё.
                                </div>
                            @endif
                        </div>

                        <form
                            method="post"
                            action="{{ route('doctors.settings.certificate-header.store') }}"
                            enctype="multipart/form-data"
                        >
                            @csrf

                            <label
                                class="settings-label"
                                for="clinicCertificateHeader"
                            >
                                {{ $headerMedia ? 'Заменить изображение' : 'Загрузить изображение' }}
                            </label>

                            <input
                                id="clinicCertificateHeader"
                                type="file"
                                name="certificate_header"
                                class="form-control"
                                accept="image/png,image/jpeg,image/webp"
                                required
                            >

                            <div class="settings-field-hint">
                                PNG, JPG/JPEG или WEBP, до 5 МБ.
                                Рекомендуется горизонтальное изображение
                                на белом или прозрачном фоне.
                            </div>

                            <div class="settings-actions">
                                @if($headerMedia)
                                    <button
                                        type="submit"
                                        form="deleteCertificateHeaderForm"
                                        class="settings-button settings-button--secondary"
                                        onclick="return confirm('Удалить шапку? Все учреждения группы будут печатать документы без неё.');"
                                    >
                                        <i class="bi bi-trash"></i>
                                        Удалить
                                    </button>
                                @endif

                                <button
                                    type="submit"
                                    class="settings-button settings-button--primary"
                                >
                                    <i class="bi bi-upload"></i>
                                    {{ $headerMedia ? 'Заменить шапку' : 'Загрузить шапку' }}
                                </button>
                            </div>
                        </form>

                        @if($headerMedia)
                            <form
                                id="deleteCertificateHeaderForm"
                                method="post"
                                action="{{ route('doctors.settings.certificate-header.destroy') }}"
                                class="d-none"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </div>

                <div class="settings-panel">
                    <div class="settings-panel__header">
                        <div class="settings-panel__title-wrap">
                            <h2 class="settings-panel__title">
                                Область действия
                            </h2>
                            <div class="settings-panel__subtitle">
                                Эти учреждения используют одну шапку.
                            </div>
                        </div>
                    </div>

                    <div class="settings-panel__body">
                        <div class="settings-info-box mb-3">
                            <span class="settings-info-box__icon">
                                <i class="bi bi-info-circle"></i>
                            </span>
                            <div>
                                Файл физически хранится только у
                                <strong>{{ $rootClinic->name }}</strong>.
                                Дочерние учреждения получают его через
                                <code>parent_clinic_id</code>.
                            </div>
                        </div>

                        <div class="certificate-header-family">
                            @foreach($familyClinics as $familyClinic)
                                <div class="certificate-header-family__item">
                                    <div class="certificate-header-family__name">
                                        @if((int) $familyClinic->id === (int) $rootClinic->id)
                                            <i class="bi bi-diagram-3 me-1"></i>
                                        @elseif((string) $familyClinic->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                                            <i class="bi bi-hospital me-1"></i>
                                        @else
                                            <i class="bi bi-building me-1"></i>
                                        @endif

                                        {{ $familyClinic->name }}
                                    </div>

                                    <div class="certificate-header-family__type">
                                        @if((int) $familyClinic->id === (int) $rootClinic->id)
                                            Головная
                                        @elseif((string) $familyClinic->clinic_type === \App\Models\Clinic::TYPE_INPATIENT)
                                            Стационар
                                        @else
                                            Диспансер
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
