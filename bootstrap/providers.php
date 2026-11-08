<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\LoadConfigurationProvider::class,
    // clinic-telemedicine-timezones:register-ClinicTelemedicineSettingsServiceProvider:v1:begin
    \App\Providers\ClinicTelemedicineSettingsServiceProvider::class,
    // clinic-telemedicine-timezones:register-ClinicTelemedicineSettingsServiceProvider:v1:end
    // clinic-telemedicine-timezones:register-ClinicTelemedicineSessionServiceProvider:v1:begin
    \App\Providers\ClinicTelemedicineSessionServiceProvider::class,
    // clinic-telemedicine-timezones:register-ClinicTelemedicineSessionServiceProvider:v1:end
];
