@extends('layouts.patient')

@section('title', 'История обращений')

@section('content')
<div class="patient-activity-page">
    <div class="patient-activity-heading">
        <div>
            <div class="patient-activity-eyebrow">МОЁ ЛЕЧЕНИЕ</div>
            <h1>История обращений</h1>
            <p>Очные приёмы и телемедицинские консультации в одной истории.</p>
        </div>
        <div class="patient-activity-heading__icon">
            <i class="bi bi-clock-history" aria-hidden="true"></i>
        </div>
    </div>

    <form method="GET" action="{{ route('patients.history.index') }}" class="patient-activity-filters">
        <label>
            <span>Формат обращения</span>
            <select name="format" class="form-select">
                <option value="all" @selected($filters['format'] === 'all')>Все обращения</option>
                <option value="in_person" @selected($filters['format'] === 'in_person')>Очные приёмы</option>
                <option value="telemedicine" @selected($filters['format'] === 'telemedicine')>Телемедицина</option>
            </select>
        </label>
        <label>
            <span>Период</span>
            <select name="period" class="form-select">
                <option value="all" @selected($filters['period'] === 'all')>За всё время</option>
                <option value="30" @selected($filters['period'] === '30')>Последние 30 дней</option>
                <option value="90" @selected($filters['period'] === '90')>Последние 90 дней</option>
                <option value="365" @selected($filters['period'] === '365')>Последний год</option>
            </select>
        </label>
        <label>
            <span>Клиника</span>
            <select name="clinic_id" class="form-select">
                <option value="">Все клиники</option>
                @foreach($clinicOptions as $clinic)
                    <option value="{{ $clinic->id }}" @selected((int) $filters['clinic_id'] === (int) $clinic->id)>
                        {{ $clinic->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <div class="patient-activity-filters__actions">
            <button type="submit" class="btn patient-activity-button">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i> Применить
            </button>
            <a href="{{ route('patients.history.index') }}" class="patient-activity-reset">Сбросить</a>
        </div>
    </form>

    <div class="patient-activity-list-heading">
        <h2>Обращения</h2>
        <span>{{ $visits->total() }} записей</span>
    </div>

    @forelse($visits as $visit)
        <article class="patient-visit-card">
            <div class="patient-visit-card__leading">
                <span class="patient-visit-card__icon {{ $visit['is_telemedicine'] ? 'is-video' : 'is-clinic' }}">
                    <i class="bi {{ $visit['is_telemedicine'] ? 'bi-camera-video' : 'bi-hospital' }}" aria-hidden="true"></i>
                </span>
            </div>
            <div class="patient-visit-card__body">
                <div class="patient-visit-card__top">
                    <div>
                        <div class="patient-visit-card__format">{{ $visit['format'] }}</div>
                        <h3>{{ $visit['doctor_name'] }}</h3>
                        @if($visit['doctor_speciality'] !== '')
                            <div class="patient-visit-card__speciality">{{ $visit['doctor_speciality'] }}</div>
                        @endif
                    </div>
                    <span class="patient-visit-status {{ $visit['status_class'] }}">{{ $visit['status'] }}</span>
                </div>
                <div class="patient-visit-card__meta">
                    <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $visit['date'] }}</span>
                    <span><i class="bi bi-hospital" aria-hidden="true"></i>{{ $visit['clinic_name'] }}</span>
                    <span><i class="bi bi-clock" aria-hidden="true"></i>{{ $visit['duration'] }} минут</span>
                </div>
                @if($visit['is_telemedicine'])
                    <div class="patient-visit-card__actions">
                        <a href="{{ route('patients.telemedicine.index', ['tab' => 'history']) }}">
                            История телемедицины <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </a>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="patient-activity-empty">
            <span><i class="bi bi-journal-medical" aria-hidden="true"></i></span>
            <h3>Обращений пока нет</h3>
            <p>Здесь будут отображаться ваши очные приёмы и телемедицинские консультации.</p>
            <a href="{{ route('patients.calendar') }}" class="btn patient-activity-button">Перейти в календарь</a>
        </div>
    @endforelse

    @if($visits->hasPages())
        <div class="patient-activity-pagination">{{ $visits->links() }}</div>
    @endif
</div>
@endsection
