@extends('layouts.doctor')

@section('title', 'Палаты и койки — настройки стационара')

@section('assets')
    @parent
    {{-- Подключаем существующий Vue-конструктор палат, НЕ вторую его копию. --}}
    @vite(['resources/js/app.js'])
    <style>
        .nh-hospital-rooms-view {
            min-height: calc(100vh - 68px);
            padding: 16px 22px 50px;
            background: #f4f7fb;
        }
        .nh-hospital-rooms-view__container {
            width: min(1660px, 100%);
            margin: 0 auto;
        }
        .nh-hospital-rooms-view .nh-temporary-info {
            margin: 0 0 16px;
            border: 1px solid #cde9ed;
            background: #f0fbfc;
            color: #326676;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 12px;
        }
        @media (max-width: 767px) {
            .nh-hospital-rooms-view { padding: 12px 10px 32px; }
        }
    </style>
@endsection

@section('main')
    @include('doctors.settings.partials.hospital-temporary-nav', ['showBrand' => false])

    <main class="nh-hospital-rooms-view" aria-label="Конструктор палат и коек стационара">
        <div class="nh-hospital-rooms-view__container">
            <div id="hospital-rooms-root"
                 data-bootstrap-url="{{ $roomBootstrapUrl }}"
                 data-room-store-url="{{ $roomStoreUrl }}"
                 data-room-update-url-template="{{ $roomUpdateUrlTemplate }}"
                 data-room-delete-url-template="{{ $roomDeleteUrlTemplate }}"
                 data-room-layout-url-template="{{ $roomLayoutUrlTemplate }}"
                 data-bed-assign-url-template="{{ $bedAssignUrlTemplate }}"
                 data-bed-release-url-template="{{ $bedReleaseUrlTemplate }}"
                 data-patient-search-url="{{ $patientSearchUrl }}"
                 data-patient-card-url-template="{{ $patientCardUrlTemplate }}"
                 data-clinic-name="{{ $clinicName }}"
                 data-patient-placement-enabled="false">
                <div class="nh-temporary-info" role="status">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    Палаты и схемы относятся только к выбранному стационару. Медицинские карты
                    и размещение пациентов в режиме администрирования недоступны.
                </div>
                <div class="settings-panel">
                    <div class="settings-panel__body">
                        <div class="text-muted small">Загружаем конструктор палат…</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
