@php
    $availabilityRecord = $record ?? null;
    $availabilityDuration = old(
        'appointment_duration_minutes',
        $availabilityRecord
            ? $availabilityRecord->appointment_duration_minutes
            : config('doctor_appointment_availability.default_duration_minutes', 30)
    );
    $availabilityDuration = is_scalar($availabilityDuration) ? $availabilityDuration : '';
    $availabilityOriginalFormat = $availabilityRecord?->appointment_format ?? 'in_person';
    $availabilityOriginalFormat = is_string($availabilityOriginalFormat)
        ? trim($availabilityOriginalFormat) : '';
    $availabilityOriginalFormat = $availabilityOriginalFormat === '' ? 'in_person' : $availabilityOriginalFormat;
@endphp
<div class="col-12"
     data-doctor-availability
     data-availability-url="{{ route('doctors.reception.availability') }}"
     data-availability-record-id="{{ $availabilityRecord?->id ?? '' }}"
     data-availability-original-doctor="{{ $availabilityRecord?->doctor_id ?? '' }}"
     data-availability-original-datetime="{{ $appointmentTimeContext['original_datetime'] }}"
     data-availability-original-duration="{{ $availabilityRecord?->appointment_duration_minutes ?? '' }}"
     data-availability-original-format="{{ $availabilityOriginalFormat }}">
    <div class="row g-3">
        {{-- clinic-telemedicine-timezones:timezone-availability-input:v1:begin --}}
        @include('doctors.reception.partials.appointment_timezone_input')
        {{-- clinic-telemedicine-timezones:timezone-availability-input:v1:end --}}
        <div class="col-md-4">
            <label class="form-label" for="appointment_duration_minutes">Длительность приёма, мин.</label>
            <input class="form-control"
                   type="number"
                   id="appointment_duration_minutes"
                   name="appointment_duration_minutes"
                   value="{{ $availabilityDuration }}"
                   min="5"
                   max="480"
                   step="5"
                   inputmode="numeric"
                   placeholder="{{ $availabilityRecord ? 'Ранее не указана' : 'Например, 30' }}"
                   aria-describedby="appointment_duration_hint"
                   data-availability-duration
                   @required(!$availabilityRecord)>
            <div class="form-text" id="appointment_duration_hint">
                @if($availabilityRecord && $availabilityRecord->appointment_duration_minutes === null)
                    В этой записи длительность ранее не указана. Укажите её при смене врача, времени или формата приёма.
                @else
                    От 5 до 480 минут, с шагом 5 минут.
                @endif
            </div>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="appointment_working_time">Время по графику врача</label>
            <select class="form-select"
                    id="appointment_working_time"
                    aria-describedby="appointment_availability_status"
                    data-availability-slots
                    disabled>
                <option value="">Укажите врача, дату и длительность</option>
            </select>
            <div class="form-text">
                В списке указано начало и окончание приёма. Можно также ввести время в поле выше.
            </div>
        </div>
        <div class="col-12">
            <div id="appointment_availability_status"
                 class="small text-muted"
                 role="status"
                 aria-live="polite"
                 aria-atomic="true"
                 data-availability-message>
                Дата и время будут проверены по рабочему графику врача.
            </div>
            <div class="small text-muted mt-1" data-availability-hours></div>
            <div class="small text-muted" data-availability-timezone></div>
            <button class="btn btn-outline-secondary btn-sm mt-2"
                    type="button"
                    data-availability-retry
                    hidden>Повторить проверку</button>
            <noscript>
                <p class="small text-muted mt-2 mb-0">
                    Подбор времени требует JavaScript. Рабочий график также проверяется при сохранении записи.
                </p>
            </noscript>
        </div>
    </div>
</div>
@once
    <script src="{{ asset('js/doctor-appointment-availability.js') }}?v=1.1.1" defer></script>
@endonce
