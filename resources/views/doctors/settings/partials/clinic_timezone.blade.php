<div class="settings-col-12">
    <label class="settings-label" for="clinicTimezone">Часовой пояс клиники</label>
    <select id="clinicTimezone" name="timezone" class="settings-control @error('timezone') is-invalid @enderror"
            aria-describedby="clinicTimezoneHelp">
        <option value="">Часовой пояс системы — {{ $clinicTimezoneDefault }}</option>
        @foreach ($clinicTimezoneOptions as $timezoneOption)
            <option value="{{ $timezoneOption }}" @selected($clinicTimezoneValue === $timezoneOption)>{{ $timezoneOption }}</option>
        @endforeach
    </select>
    <div id="clinicTimezoneHelp" class="settings-field-hint">Используется для расписания и новых записей на приём. Например, Asia/Tomsk для Томской области.</div>
    @error('timezone')<div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>@enderror
</div>
