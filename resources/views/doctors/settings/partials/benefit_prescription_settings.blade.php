<div class="settings-col-12">
    <section class="settings-panel" aria-labelledby="benefitClinicSettingsTitle">
        <div class="settings-panel__header">
            <div class="settings-panel__title-wrap">
                <h3 id="benefitClinicSettingsTitle" class="settings-panel__title">Реквизиты для льготных рецептов</h3>
                <div class="settings-panel__subtitle">
                    Название и адрес берутся из основной информации о клинике, телефон — первый заполненный в её списке телефонов.
                </div>
            </div>
        </div>

        <div class="settings-panel__body">
            <div class="settings-form-grid">
                @if (!empty($benefitClinicSetupErrors))
                    <div class="settings-col-12">
                        <div class="settings-info-box" role="status">
                            <div>
                                <div class="settings-label">Что нужно проверить в сохранённых настройках</div>
                                <ul class="mb-0">
                                    @foreach ($benefitClinicSetupErrors as $setupError)
                                        <li>{{ $setupError }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @error('benefit_clinic')
                    <div class="settings-col-12 settings-field-hint text-danger" role="alert">{{ $message }}</div>
                @enderror

                <div class="settings-col-12">
                    <input type="hidden" name="benefit_clinic[prescribing_enabled]" value="0">
                    <label class="settings-label" for="benefitClinicPrescribingEnabled">
                        <input id="benefitClinicPrescribingEnabled"
                               name="benefit_clinic[prescribing_enabled]"
                               type="checkbox"
                               class="form-check-input"
                               value="1"
                               aria-describedby="benefitClinicPrescribingEnabledHelp"
                               @checked($benefitClinicForm['prescribing_enabled'])>
                        Включить оформление льготных рецептов
                    </label>
                    <div id="benefitClinicPrescribingEnabledHelp" class="settings-field-hint">
                        Включайте, когда клиника уполномочена выписывать рецепты по соответствующей программе.
                    </div>
                    @error('benefit_clinic.prescribing_enabled')
                        <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-col-6">
                    <label class="settings-label" for="benefitClinicKind">Юридический статус</label>
                    <select id="benefitClinicKind"
                            name="benefit_clinic[kind]"
                            class="settings-control @error('benefit_clinic.kind') is-invalid @enderror"
                            required>
                        <option value="" @selected($benefitClinicForm['kind'] === '')>Выберите юридический статус</option>
                        <option value="organization" @selected($benefitClinicForm['kind'] === 'organization')>Юридическое лицо</option>
                        <option value="individual" @selected($benefitClinicForm['kind'] === 'individual')>Индивидуальный предприниматель</option>
                    </select>
                    @error('benefit_clinic.kind')
                        <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-col-6">
                    <label class="settings-label" for="benefitClinicOgrn">ОГРН / ОГРНИП</label>
                    <input id="benefitClinicOgrn"
                           name="benefit_clinic[ogrn]"
                           type="text"
                           class="settings-control @error('benefit_clinic.ogrn') is-invalid @enderror"
                           inputmode="numeric"
                           minlength="13"
                           maxlength="15"
                           pattern="[0-9]{13}([0-9]{2})?"
                           autocomplete="off"
                           aria-describedby="benefitClinicOgrnHelp"
                           value="{{ $benefitClinicForm['ogrn'] }}">
                    <div id="benefitClinicOgrnHelp" class="settings-field-hint">ОГРН юридического лица — 13 цифр, ОГРНИП — 15 цифр.</div>
                    @error('benefit_clinic.ogrn')
                        <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-col-6">
                    <label class="settings-label" for="benefitClinicCode">Код организации в программе — необязательно</label>
                    <input id="benefitClinicCode"
                           name="benefit_clinic[code]"
                           type="text"
                           class="settings-control @error('benefit_clinic.code') is-invalid @enderror"
                           maxlength="50"
                           autocomplete="off"
                           aria-describedby="benefitClinicCodeHelp"
                           value="{{ $benefitClinicForm['code'] }}">
                    <div id="benefitClinicCodeHelp" class="settings-field-hint">Заполните, если вашей организации присвоен отдельный код в программе лекарственного обеспечения.</div>
                    @error('benefit_clinic.code')
                        <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-col-6">
                    <label class="settings-label" for="benefitClinicSeries">Серия по умолчанию — необязательно</label>
                    <input id="benefitClinicSeries"
                           name="benefit_clinic[series]"
                           type="text"
                           class="settings-control @error('benefit_clinic.series') is-invalid @enderror"
                           maxlength="20"
                           autocomplete="off"
                           aria-describedby="benefitClinicSeriesHelp"
                           value="{{ $benefitClinicForm['series'] }}">
                    <div id="benefitClinicSeriesHelp" class="settings-field-hint">Используемая клиникой серия по действующему порядку нумерации. Начинается с настроенных цифр ОКАТО; до 20 символов: цифры, русские или латинские буквы, дефис и косая черта.</div>
                    @error('benefit_clinic.series')
                        <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-col-12">
                    <label class="settings-label" for="benefitClinicNumberingSource">Источник нумерации</label>
                    <textarea id="benefitClinicNumberingSource"
                              name="benefit_clinic[numbering_source]"
                              class="settings-control @error('benefit_clinic.numbering_source') is-invalid @enderror"
                              rows="2"
                              maxlength="1000"
                              aria-describedby="benefitClinicNumberingSourceHelp">{{ $benefitClinicForm['numbering_source'] }}</textarea>
                    <div id="benefitClinicNumberingSourceHelp" class="settings-field-hint">Действующий порядок присвоения серий и номеров: реквизиты документа или принятой схемы.</div>
                    @error('benefit_clinic.numbering_source')
                        <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <details class="settings-col-12"
                         @if ($benefitClinicForm['kind'] === 'individual' || $errors->has('benefit_clinic.license_number') || $errors->has('benefit_clinic.license_date') || $errors->has('benefit_clinic.license_authority')) open @endif>
                    <summary class="settings-label">Лицензия — реквизиты для ИП</summary>
                    <div class="settings-field-hint">Для ИП заполните номер лицензии, дату её выдачи и лицензирующий орган.</div>
                    <div class="settings-form-grid">
                        <div class="settings-col-6">
                            <label class="settings-label" for="benefitClinicLicenseNumber">Номер лицензии</label>
                            <input id="benefitClinicLicenseNumber"
                                   name="benefit_clinic[license_number]"
                                   type="text"
                                   class="settings-control @error('benefit_clinic.license_number') is-invalid @enderror"
                                   maxlength="100"
                                   value="{{ $benefitClinicForm['license_number'] }}">
                            @error('benefit_clinic.license_number')
                                <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="settings-col-6">
                            <label class="settings-label" for="benefitClinicLicenseDate">Дата выдачи лицензии</label>
                            <input id="benefitClinicLicenseDate"
                                   name="benefit_clinic[license_date]"
                                   type="date"
                                   class="settings-control @error('benefit_clinic.license_date') is-invalid @enderror"
                                   value="{{ $benefitClinicForm['license_date'] }}">
                            @error('benefit_clinic.license_date')
                                <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="settings-col-12">
                            <label class="settings-label" for="benefitClinicLicenseAuthority">Лицензирующий орган</label>
                            <input id="benefitClinicLicenseAuthority"
                                   name="benefit_clinic[license_authority]"
                                   type="text"
                                   class="settings-control @error('benefit_clinic.license_authority') is-invalid @enderror"
                                   maxlength="500"
                                   value="{{ $benefitClinicForm['license_authority'] }}">
                            @error('benefit_clinic.license_authority')
                                <div class="settings-field-hint text-danger" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </section>
</div>
