    <div class="small text-muted my-2" data-appointment-zone-control>
        <label class="form-label mb-1 d-block">
            Отображать «Ваше время»
            <select class="form-select form-select-sm mt-1" data-appointment-zone-select aria-label="Часовой пояс для отображения времени">
                <option value="auto">По часовому поясу устройства</option>
            </select>
        </label>
        <span data-appointment-zone-current></span>
    </div>
@include('shared.appointment_timezone_assets')
