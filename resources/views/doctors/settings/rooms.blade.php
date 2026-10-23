@extends('doctors.settings')

@section('settings-title', 'Палаты и койки')

@section('assets')
    @parent
    {{-- Загрузка Vue-приложения редактора палат --}}
    @vite(['resources/js/app.js'])
@endsection

@section('sub-main')
    <main class="settings-page">
        <div class="settings-container">
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
                 data-clinic-name="{{ $clinicName }}">
                <div class="settings-panel">
                    <div class="settings-panel__body">
                        <div class="text-muted small">
                            Загрузка карты палат…
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
