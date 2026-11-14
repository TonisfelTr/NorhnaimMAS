<span
    class="patient-doctor-tooltip-trigger"
    tabindex="0"
    data-doctor-tooltip-trigger
    data-doctor-name="{{ $doctorName }}"
    data-doctor-experience="{{ $experience }}"
    data-doctor-speciality="{{ $speciality }}"
    data-doctor-initials="{{ $initials }}"
    data-doctor-cabinet="{{ $cabinet ?? '' }}"
    data-doctor-phone="{{ $phone ?? '' }}"
    data-doctor-visit-status="{{ $status ?? '' }}"
    data-doctor-visit-status-code="{{ $statusCode ?? '' }}"
    aria-label="Сведения о враче: {{ $doctorName }}"
>{{ $label }}</span>
