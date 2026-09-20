@extends('doctors.reception')

@section('title', trim($__env->yieldContent('hospitalization-title', 'Госпитализация')))

@section('assets')
    @parent
    @vite('resources/js/hospitalizations.js')
@endsection

@section('main')
    @php
        $prescriptionsRoute = \Illuminate\Support\Facades\Route::has('doctors.prescriptions.index')
            ? route('doctors.prescriptions.index')
            : '#';
        $referenceRoute = \Illuminate\Support\Facades\Route::has('doctors.prescriptions.base')
            ? route('doctors.prescriptions.base')
            : '#';
        $plannedRoute = route('doctors.referrals.hospital.planned');
        $emergencyRoute = route('doctors.referrals.hospital.emergency');
        $isEmergency = ($type ?? 'planned') === 'emergency';
    @endphp

    <nav class="navbar navbar-expand-lg hospital-topbar">
        <div class="container-fluid hospital-topbar__inner">
            <a class="hospital-brand" href="{{ $prescriptionsRoute }}">
                <span class="hospital-brand__icon">
                    <i class="bi bi-clipboard2-pulse"></i>
                </span>
                <span>Назначения</span>
            </a>

            <button class="navbar-toggler hospital-topbar__toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#hospitalizationTopbar"
                    aria-controls="hospitalizationTopbar"
                    aria-expanded="false"
                    aria-label="Открыть навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="hospitalizationTopbar">
                <ul class="navbar-nav hospital-topbar__nav me-auto">
                    <li class="nav-item">
                        <a class="hospital-topbar__link" href="{{ $prescriptionsRoute }}">
                            <i class="bi bi-prescription2"></i>
                            Рецепты
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="hospital-topbar__link active dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-signpost-split"></i>
                            Направления
                        </a>

                        <div class="dropdown-menu hospital-topbar__dropdown">
                            <a class="hospital-topbar__dropdown-item {{ !$isEmergency ? 'active' : '' }}"
                               href="{{ $plannedRoute }}">
                                <span class="hospital-topbar__dropdown-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </span>
                                <span>
                                    <strong>Плановая госпитализация</strong>
                                    <small>Направление на плановое лечение</small>
                                </span>
                            </a>

                            <a class="hospital-topbar__dropdown-item {{ $isEmergency ? 'active emergency' : '' }}"
                               href="{{ $emergencyRoute }}">
                                <span class="hospital-topbar__dropdown-icon">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </span>
                                <span>
                                    <strong>Экстренная госпитализация</strong>
                                    <small>Неотложное направление пациента</small>
                                </span>
                            </a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="hospital-topbar__link" href="{{ $referenceRoute }}">
                            <i class="bi bi-journal-medical"></i>
                            Справочники
                        </a>
                    </li>
                </ul>

                <div class="hospital-topbar__badge {{ $isEmergency ? 'is-emergency' : '' }}">
                    <i class="bi {{ $isEmergency ? 'bi-activity' : 'bi-calendar2-check' }}"></i>
                    {{ $isEmergency ? 'Экстренный маршрут' : 'Плановый маршрут' }}
                </div>
            </div>
        </div>
    </nav>

    <main id="hospitalizationRoot"
          class="hospital-page {{ $isEmergency ? 'hospital-page--emergency' : 'hospital-page--planned' }}"
          data-mode="{{ $type }}"
          data-analyze-url="{{ route('doctors.referrals.hospital.analyze') }}"
          data-patient-search-url="{{ route('api.patients.search') }}"
          data-patient-details-url="{{ str_replace('/0', '/__ID__', route('api.patients.for.search', ['param' => 0])) }}"
          data-patient-context-url="{{ str_replace('/0/context', '/__ID__/context', route('doctors.referrals.hospital.patient_context', ['patient' => 0])) }}">
        <div class="container-fluid hospital-page__container">
            <div class="hospital-shell">
                @include('doctors.referrals.hospitalizations.partials.sidebar')

                <section class="hospital-main">
                    @if(session('status') === 'success')
                        <div class="alert alert-success hospital-flash" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ session('message') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger hospital-flash" role="alert">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                            <div>
                                <div class="fw-bold">Проверьте заполнение формы</div>
                                <ul class="mb-0 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @yield('hospitalization-content')
                </section>
            </div>
        </div>
    </main>
@endsection
