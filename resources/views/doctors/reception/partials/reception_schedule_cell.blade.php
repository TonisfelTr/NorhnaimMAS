@if(isset($receptionRow['schedule']))
    <div class="d-flex flex-column gap-1 align-items-start">
        @if($receptionRow['schedule']['date'])
            @if($receptionRow['schedule']['iso'])
                <time datetime="{{ $receptionRow['schedule']['iso'] }}"
                      class="text-nowrap"
                      data-appointment-primary
                      data-appointment-utc="{{ $receptionRow['schedule']['iso'] }}">
                    <span class="fw-bold fs-5"
                          data-appointment-primary-time>{{ $receptionRow['schedule']['time'] }}</span>
                    <span class="ms-1"
                          data-appointment-primary-date>{{ $receptionRow['schedule']['date'] }}</span>
                    <span class="small text-muted ms-1"
                          data-appointment-primary-zone>{{ $receptionRow['schedule']['timezone'] ?? '' }}</span>
                </time>
            @else
                <span class="text-nowrap"><strong>{{ $receptionRow['schedule']['time'] }}</strong> {{ $receptionRow['schedule']['date'] }}</span>
            @endif
            @include('shared.appointment_timezone_display', ['appointmentTime' => $receptionRow['schedule'], 'showViewerTime' => false])
            @if($receptionRow['schedule']['relative_day'])
                <span class="small text-muted">{{ $receptionRow['schedule']['relative_day'] }}</span>
            @endif
        @else
            <span class="text-muted">Дата и время не указаны</span>
        @endif
        <span class="small text-nowrap">
            <i class="bi {{ $receptionRow['schedule']['format_icon'] }} me-1" aria-hidden="true"></i>
            {{ $receptionRow['schedule']['format_label'] }}
        </span>
        <span class="badge rounded-pill {{ $receptionRow['schedule']['state_class'] }}">
            {{ $receptionRow['schedule']['state_label'] }}
        </span>

        {{-- NORHNAIM_REGISTRY_APPOINTMENT_ARRIVAL_V1 --}}
        @if(
            ($receptionRow['appointment_format'] ?? 'in_person') === 'in_person'
            && ($receptionRow['appointment'] ?? false)
        )
            <span class="badge rounded-pill text-bg-success">
                <i class="bi bi-person-check me-1" aria-hidden="true"></i>
                Пациент пришёл · консультация состоялась
            </span>
        @elseif(
            ($receptionRow['appointment_format'] ?? 'in_person') === 'in_person'
            && !(($receptionRow['appointment'] ?? false))
            && (($receptionRow['schedule']['relative_day'] ?? '') === 'Сегодня')
        )
            <span class="small text-muted">Явка ещё не подтверждена</span>
        @endif

        @if($receptionRow['schedule']['appointment_doctor'])
            <span class="small">Принимает: {{ $receptionRow['schedule']['appointment_doctor'] }}</span>
        @endif
        @if($receptionRow['schedule']['journal_url'])
            <a class="small mt-1" href="{{ $receptionRow['schedule']['journal_url'] }}">Журнал телемедицины</a>
        @endif
    </div>
@else
    <span class="text-muted">—</span>
@endif
