<div class="settings-col-12">
    <label class="settings-label" for="clinicBenefitOkatoPrefix">
        Первые 2 цифры ОКАТО
    </label>
    <input id="clinicBenefitOkatoPrefix"
           name="benefit_okato_prefix"
           class="settings-control @error('benefit_okato_prefix') is-invalid @enderror"
           type="text"
           inputmode="numeric"
           minlength="2"
           maxlength="2"
           pattern="[0-9]{2}"
           autocomplete="off"
           value="{{ old(
               'benefit_okato_prefix',
               $clinic->getAttribute('benefit_okato_prefix')
                   ?? config('benefit_prescriptions.clinics.' . $clinic->getKey() . '.okato_prefix', '')
           ) }}"
           aria-describedby="clinicBenefitOkatoPrefixHelp{{ $errors->has('benefit_okato_prefix') ? ' clinicBenefitOkatoPrefixError' : '' }}"
           aria-invalid="{{ $errors->has('benefit_okato_prefix') ? 'true' : 'false' }}">
    <div id="clinicBenefitOkatoPrefixHelp" class="settings-field-hint">
        Для серии льготных рецептов. Настраивается для клиники и подставляется автоматически.
    </div>
    @error('benefit_okato_prefix')
        <div id="clinicBenefitOkatoPrefixError" class="settings-field-hint text-danger" role="alert">
            {{ $message }}
        </div>
    @enderror
</div>
