{{-- Единая навигация временного режима. Не подменяет clinic_id и не расширяет права. --}}
<style>
    .nh-temp-nav, .nh-temp-nav * { box-sizing: border-box; }
    .nh-temp-nav {
        margin: 0; padding: 0; min-height: 55px;
        background: linear-gradient(110deg, #067b8d, #10a9b7);
        color: #fff; border: 0;
        box-shadow: 0 2px 10px rgba(4, 104, 122, .11);
        position: relative; z-index: 40;
    }
    .hospital-editor .nh-temp-nav {
        background: rgba(0, 58, 75, .09);
        border-top: 1px solid rgba(255, 255, 255, .16);
        box-shadow: none;
    }
    .nh-temp-nav .nh-temp-nav__inner {
        max-width: 1240px; min-height: 55px;
        margin: 0 auto; padding: 0 20px; display: flex;
        align-items: center; gap: 15px;
    }
    .nh-temp-nav .nh-temp-nav__brand {
        display: inline-flex; align-items: center; gap: 9px;
        flex: 0 0 auto; white-space: nowrap;
        color: #fff; font-size: 15px; font-weight: 800;
        text-decoration: none; margin-right: 7px;
    }
    .nh-temp-nav .nh-temp-nav__brand:hover,
    .nh-temp-nav .nh-temp-nav__brand:focus { color: #fff; }
    .nh-temp-nav .nh-temp-nav__brand-icon {
        display: grid; place-items: center; width: 34px; height: 34px;
        border-radius: 11px; background: rgba(255,255,255,.18);
        font-size: 17px;
    }
    .nh-temp-nav .nh-temp-nav__collapse { min-width: 0; }
    .nh-temp-nav .nh-temp-nav__items {
        display: flex; align-items: center; gap: 4px;
        margin: 0; padding: 0; list-style: none;
    }
    .nh-temp-nav .nh-temp-nav__link {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; min-height: 42px; padding: 10px 13px;
        border: 1px solid transparent; border-radius: 11px;
        background: transparent; color: rgba(255,255,255,.9);
        font-size: 13px; font-weight: 720;
        white-space: nowrap; line-height: 1.25; text-decoration: none;
        transition: background .16s ease, border-color .16s ease;
    }
    .nh-temp-nav .nh-temp-nav__link i { font-size: 15px; }
    .nh-temp-nav .nh-temp-nav__link:hover,
    .nh-temp-nav .nh-temp-nav__link:focus-visible,
    .nh-temp-nav .nh-temp-nav__link.is-active {
        color: #fff; background: rgba(255,255,255,.18);
        border-color: rgba(255,255,255,.15);
        outline-offset: 2px;
    }
    .nh-temp-nav .nh-temp-nav__mode {
        margin-left:auto; flex:0 0 auto; display:inline-flex; align-items:center;
        gap:6px; border:1px solid rgba(255,255,255,.24);
        background:rgba(255,255,255,.12); padding:7px 12px;
        border-radius:999px; font-size:11px; font-weight:740; white-space:nowrap;
    }
    .nh-temp-nav .nh-temp-nav__toggle {
        margin-left:auto; display: none; align-items:center; gap:8px;
        border:1px solid rgba(255,255,255,.4);
        border-radius:10px; padding:8px 11px;
        background:rgba(255,255,255,.12); color:#fff;
        font-size:12px; font-weight:750;
    }
    .nh-temp-nav .nh-temp-nav__toggle i { font-size:17px; }
    @media (max-width: 1199.98px) {
        .nh-temp-nav .nh-temp-nav__inner { flex-wrap: wrap; padding:10px 16px; }
        .nh-temp-nav .nh-temp-nav__toggle { display:inline-flex; }
        .nh-temp-nav .nh-temp-nav__collapse { flex-basis:100%; width:100%; }
        .nh-temp-nav .nh-temp-nav__items { flex-direction:column; align-items:stretch; gap:4px; padding:6px 0; }
        .nh-temp-nav .nh-temp-nav__link { width:100%; justify-content:flex-start; border-radius:10px; }
        .nh-temp-nav .nh-temp-nav__mode { width:max-content; margin:8px 0 3px; }
    }
</style>
<nav class="nh-temp-nav navbar navbar-expand-xl" aria-label="Разделы настроек выбранного стационара">
    <div class="container-fluid nh-temp-nav__inner">
        @if($showBrand ?? true)
            <a class="nh-temp-nav__brand" href="{{ route('doctors.hospital-impersonation.workspace', ['hospital' => session('norhnaim.hospital_impersonation.hospital_id')]) }}#ws-general">
                <span class="nh-temp-nav__brand-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
                <span>Настройки стационара</span>
            </a>
        @endif
        <button class="nh-temp-nav__toggle navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#nhTempStationNav"
                aria-controls="nhTempStationNav" aria-expanded="false" aria-label="Открыть разделы стационара">
            <i class="bi bi-list" aria-hidden="true"></i> Разделы
        </button>
        <div class="collapse navbar-collapse nh-temp-nav__collapse" id="nhTempStationNav">
            <ul class="navbar-nav nh-temp-nav__items">
                <li class="nav-item">
                    <a class="nh-temp-nav__link" data-tab="general"
                       href="{{ route('doctors.hospital-impersonation.workspace', ['hospital' => session('norhnaim.hospital_impersonation.hospital_id')]) }}#ws-general">
                        <i class="bi bi-sliders" aria-hidden="true"></i> Общие
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nh-temp-nav__link" data-tab="schedule"
                       href="{{ route('doctors.hospital-impersonation.workspace', ['hospital' => session('norhnaim.hospital_impersonation.hospital_id')]) }}#ws-schedule">
                        <i class="bi bi-calendar-week" aria-hidden="true"></i> Режим и графики
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nh-temp-nav__link" data-tab="building"
                       href="{{ route('doctors.hospital-impersonation.workspace', ['hospital' => session('norhnaim.hospital_impersonation.hospital_id')]) }}#ws-building">
                        <i class="bi bi-buildings" aria-hidden="true"></i> Корпуса
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nh-temp-nav__link" data-tab="department" title="Быстрое редактирование отделений"
                       href="{{ route('doctors.hospital-impersonation.workspace', ['hospital' => session('norhnaim.hospital_impersonation.hospital_id')]) }}#ws-department">
                        <i class="bi bi-diagram-3" aria-hidden="true"></i> Отделения
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nh-temp-nav__link {{ request()->routeIs('doctors.hospital-impersonation.rooms.*') ? 'is-active' : '' }}"
                       href="{{ route('doctors.hospital-impersonation.rooms.index', ['hospital' => session('norhnaim.hospital_impersonation.hospital_id')]) }}"
                       title="Палаты и койки выбранного стационара"
                       @if(request()->routeIs('doctors.hospital-impersonation.rooms.*')) aria-current="page" @endif>
                        <i class="bi bi-door-closed" aria-hidden="true"></i> Палаты и койки
                    </a>
                </li>
            </ul>
            @if($showBrand ?? true)
                <span class="nh-temp-nav__mode"><i class="bi bi-hospital" aria-hidden="true"></i> Стационар</span>
            @endif
        </div>
    </div>
</nav>
