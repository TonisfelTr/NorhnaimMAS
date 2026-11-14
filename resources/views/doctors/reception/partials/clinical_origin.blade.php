@php
    $origin = is_array($origin ?? null) ? $origin : null;
@endphp

@if($origin)
    <div @class([
        'mc-clinical-origin',
        'is-legacy' => !($origin['exact'] ?? false),
    ])>
        <span>
            <i class="bi bi-hospital"></i>
            {{ $origin['clinic_name'] ?? 'Клиника не зафиксирована' }}
        </span>
        <span>
            <i class="bi bi-person-badge"></i>
            {{ $origin['doctor_name'] ?? 'Врач не зафиксирован' }}
        </span>
        @if(!empty($origin['captured_at']))
            <span>
                <i class="bi bi-clock-history"></i>
                {{ $origin['captured_at'] }}
            </span>
        @endif
        @if(!($origin['exact'] ?? false))
            <span class="mc-clinical-origin__legacy">
                Историческая запись: клиника ранее не сохранялась
            </span>
        @endif
    </div>
@endif
