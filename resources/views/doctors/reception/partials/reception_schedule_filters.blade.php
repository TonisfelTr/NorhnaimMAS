@if(!$receptionPage['is_inpatient'] && isset($receptionScheduleFilters))
    <div class="row g-3 mt-1 mb-2">
        <div class="col-12 col-lg-4">
            <label for="reception_period" class="form-label">Период записей</label>
            <select id="reception_period" name="reception_period" class="form-select">
                @foreach($receptionScheduleFilters['period_options'] as $option)
                    <option value="{{ $option['value'] }}" @selected($option['selected'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-lg-4">
            <label for="reception_date" class="form-label">Конкретная дата приёма</label>
            <input id="reception_date" name="reception_date" type="date" class="form-control"
                   value="{{ $receptionScheduleFilters['date'] }}" aria-describedby="reception_schedule_hint">
        </div>
        <div class="col-12 col-lg-4">
            <label for="reception_format" class="form-label">Формат приёма</label>
            <select id="reception_format" name="reception_format" class="form-select">
                @foreach($receptionScheduleFilters['format_options'] as $option)
                    <option value="{{ $option['value'] }}" @selected($option['selected'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 small text-muted" id="reception_schedule_hint">
            {{-- clinic-telemedicine-timezones:timezone-reception-display-control:v1:begin --}}
            @include('shared.appointment_timezone_control')
            {{-- clinic-telemedicine-timezones:timezone-reception-display-control:v1:end --}}
            Заполненная дата имеет приоритет над периодом. Время: {{ $receptionScheduleFilters['timezone'] }}.
            Запись на приём не подтверждает, что консультация состоялась.
        </div>
    </div>
@endif
