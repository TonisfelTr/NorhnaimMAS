@php
    $showViewerTime = $showViewerTime ?? true;
    $iso = $appointmentTime['iso_utc'] ?? $appointmentTime['iso'] ?? '';
@endphp

<div class="small text-muted"
     data-appointment-display
     data-appointment-utc="{{ $iso }}">
    @if($showViewerTime)
        @if($iso)
            <div>
                Ваше время:
                <strong data-appointment-local-time>
                    {{ trim(($appointmentTime['date'] ?? '') . ' ' . ($appointmentTime['time'] ?? '')) ?: '—' }}
                </strong>
                <span data-appointment-local-zone-label>
                    @if(!empty($appointmentTime['timezone']))
                        ({{ $appointmentTime['timezone'] }})
                    @endif
                </span>
            </div>
        @else
            <div>Для пересчёта времени нужен абсолютный момент записи.</div>
        @endif
    @endif

    @if(
        !empty($appointmentTime['clinic_date'])
        && !empty($appointmentTime['clinic_time'])
        && !empty($appointmentTime['clinic_timezone'])
    )
        <div>
            Время клиники:
            {{ $appointmentTime['clinic_date'] }}
            {{ $appointmentTime['clinic_time'] }}
            ({{ $appointmentTime['clinic_timezone'] }})
        </div>
    @elseif(!empty($appointmentTime['timezone_label']))
        <div>{{ $appointmentTime['timezone_label'] }}</div>
    @endif
</div>

@include('shared.appointment_timezone_assets')
