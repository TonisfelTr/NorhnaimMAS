{{-- Верхний навбар рабочего стола стационара. Не используется в амбулатории. --}}
<nav class="navbar navbar-expand-lg nh-doctor-topbar nh-doctor-topbar--inpatient" aria-label="Навигация рабочего стола стационара">
    <div class="container-fluid nh-doctor-topbar__inner">
        <div class="nh-doctor-topbar__heading">
            <span class="nh-doctor-topbar__mark" aria-hidden="true"><i class="bi bi-grid-1x2 fs-5"></i></span>
            <span class="nh-doctor-topbar__title">Рабочий стол</span>
        </div>

        <button class="navbar-toggler nh-doctor-topbar__toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#workbenchInpatientTopbar"
                aria-controls="workbenchInpatientTopbar" aria-expanded="false"
                aria-label="Открыть навигацию стационара">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse nh-doctor-topbar__collapse" id="workbenchInpatientTopbar">
            <ul class="navbar-nav nh-doctor-topbar__links">
                <li class="nav-item">
                    <a class="nav-link nh-doctor-topbar__link {{ $tab === 'overview' ? 'active' : '' }}"
                       href="{{ route('doctors.main', $isAdmin ? ['mode' => 'inpatient', 'tab' => 'overview'] : ['tab' => 'overview']) }}"
                       @if($tab === 'overview') aria-current="page" @endif>Обзор</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nh-doctor-topbar__link {{ $tab === 'patients' ? 'active' : '' }}"
                       href="{{ route('doctors.main', $isAdmin ? ['mode' => 'inpatient', 'tab' => 'patients', 'scope' => 'department'] : ['tab' => 'patients', 'scope' => 'mine']) }}"
                       @if($tab === 'patients') aria-current="page" @endif>{{ $isAdmin ? 'Пациенты стационара' : 'Мои пациенты' }}</a>
                </li>
            </ul>
            <div class="nh-doctor-topbar__actions">
                @if($isAdmin)
                    <a class="nh-doctor-topbar__mode-link"
                       href="{{ route('doctors.main', ['mode' => 'outpatient', 'tab' => 'overview']) }}"
                       title="Перейти на рабочий стол амбулатории">
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Амбулатория
                    </a>
                @endif
                <span class="nh-doctor-topbar__context"><i class="bi bi-hospital" aria-hidden="true"></i> Стационар</span>
            </div>
        </div>
    </div>
</nav>
