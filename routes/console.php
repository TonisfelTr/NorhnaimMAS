<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('patient:remind-appointments', function () {
    $count = app(\App\Services\PatientAppointmentReminderService::class)->send();
    $this->info('Новых напоминаний: ' . $count);
})->purpose('Пациентам: напоминания о приёмах с однозначным временем UTC')->hourly();
