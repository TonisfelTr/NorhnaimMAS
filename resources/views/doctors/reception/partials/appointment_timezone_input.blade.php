<div class="col-12" data-appointment-input-zone
     data-clinic-timezone="{{ $appointmentTimeContext['clinic_timezone'] }}"
     data-appointment-original="{{ $appointmentTimeContext['original_datetime'] }}"
     data-appointment-legacy="{{ $appointmentTimeContext['legacy'] ? '1' : '0' }}">
    <div class="form-text">Дата и время приёма вводятся по часовому поясу клиники: <strong>{{ $appointmentTimeContext['clinic_timezone'] }}</strong>.</div>
    @if($appointmentTimeContext['legacy'])
        <div class="alert alert-warning mt-2 mb-2">
            <div>В старой записи часовой пояс не сохранён. Прежние дата и время показаны без пересчёта.</div>
            <input type="hidden" name="appointment_timezone_confirmed" value="0">
            <label class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="appointment_timezone_confirmed" value="1"
                       data-appointment-time-confirm @checked($appointmentTimeContext['confirmed'])>
                <span class="form-check-label">Подтверждаю, что время этой записи указано в {{ $appointmentTimeContext['clinic_timezone'] }}.</span>
            </label>
            <div class="small mt-1">При подтверждении укажите длительность. Время будет проверено по графику врача и сохранено с часовым поясом.</div>
        </div>
    @endif
    <div class="small text-muted" data-appointment-input-preview role="status" aria-live="polite"></div>
    @error('appointment_timezone_confirmed')<div class="text-danger small">{{ $message }}</div>@enderror
    @include('shared.appointment_timezone_control')
</div>
