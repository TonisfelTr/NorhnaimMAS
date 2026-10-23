{{-- Отдельный навбар рабочего стола амбулатории. Стационарные вкладки здесь отсутствуют. --}}
<nav class="navbar navbar-expand-lg nh-doctor-topbar nh-doctor-topbar--outpatient" aria-label="Навигация рабочего стола амбулатории">
    <div class="container-fluid nh-doctor-topbar__inner">
        <div class="nh-doctor-topbar__heading">
            <span class="nh-doctor-topbar__mark" aria-hidden="true"><i class="bi bi-person-lines-fill fs-5"></i></span>
            <span class="nh-doctor-topbar__title">Рабочий стол</span>
        </div>

        <button class="navbar-toggler nh-doctor-topbar__toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#workbenchOutpatientTopbar"
                aria-controls="workbenchOutpatientTopbar" aria-expanded="false"
                aria-label="Открыть навигацию амбулатории">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse nh-doctor-topbar__collapse" id="workbenchOutpatientTopbar">
            <ul class="navbar-nav nh-doctor-topbar__links">
                <li class="nav-item">
                    <a class="nav-link nh-doctor-topbar__link active"
                       href="{{ route('doctors.main', $isAdmin ? ['mode' => 'outpatient', 'tab' => 'overview'] : ['tab' => 'overview']) }}"
                       aria-current="page">Обзор</a>
                </li>
                @if($isAdmin)
                    <li class="nav-item">
                        <button class="nav-link nh-doctor-topbar__link" type="button"
                                data-bs-toggle="modal" data-bs-target="#workbenchOutpatientNavigationModal">
                            Регистратура
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link nh-doctor-topbar__link" type="button"
                                data-bs-toggle="modal" data-bs-target="#workbenchOutpatientNavigationModal">
                            Записать на приём
                        </button>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link nh-doctor-topbar__link" href="{{ route('doctors.reception.index') }}">Регистратура</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nh-doctor-topbar__link" href="{{ route('doctors.reception.create') }}">Записать на приём</a>
                    </li>
                @endif
            </ul>
            <div class="nh-doctor-topbar__actions">
                @if($isAdmin)
                    <a class="nh-doctor-topbar__mode-link"
                       href="{{ route('doctors.main', ['mode' => 'inpatient', 'tab' => 'overview']) }}"
                       title="Перейти на рабочий стол стационара">
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Стационар
                    </a>
                @endif
                <span class="nh-doctor-topbar__context"><i class="bi bi-building" aria-hidden="true"></i> Амбулатория</span>
            </div>
        </div>
    </div>
</nav>

{{-- Спецпользователь не имеет своей clinic_id: выбирает учреждение, прежде чем открыть маршрут. --}}
@if($isAdmin)
<div class="modal fade" id="workbenchOutpatientNavigationModal" tabindex="-1"
     aria-labelledby="workbenchOutpatientNavigationTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="workbenchOutpatientNavigationTitle">Выбери амбулаторию</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body d-grid gap-3">
                @if($quickActionOutpatientClinics->isEmpty())
                    <p class="text-muted mb-0">Амбулаторных клиник пока нет.</p>
                @else
                    <form method="GET" action="{{ route('doctors.reception.index') }}" class="d-grid gap-2">
                        <label for="workbenchOutpatientRegistryClinic" class="form-label mb-0">Открыть регистратуру</label>
                        <select name="clinic_id" id="workbenchOutpatientRegistryClinic" class="form-select" required>
                            <option value="">Выбери клинику</option>
                            @foreach($quickActionOutpatientClinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-info text-white">Открыть регистратуру</button>
                    </form>
                    <hr class="my-1">
                    <form method="GET" action="{{ route('doctors.reception.create') }}" class="d-grid gap-2">
                        <label for="workbenchOutpatientCreateClinic" class="form-label mb-0">Записать на приём</label>
                        <select name="clinic_id" id="workbenchOutpatientCreateClinic" class="form-select" required>
                            <option value="">Выбери клинику</option>
                            @foreach($quickActionOutpatientClinics as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-info">К форме записи</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
