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

\Illuminate\Support\Facades\Artisan::command('telemedicine:remind {--dry-run}', function (): void {
    $summary = app(\App\Services\TelemedicineSessionReminderService::class)
        ->run((bool) $this->option('dry-run'));
    $this->info('Checked: '.$summary['checked']
        .', doctor notices: '.$summary['doctor_notices']
        .', patient SMS: '.$summary['patient_sms']
        .', doctor SMS: '.$summary['doctor_sms']
        .', errors: '.$summary['errors']);
})->purpose('Telemedicine reminder delivery (SMS optional)');

\Illuminate\Support\Facades\Schedule::command('telemedicine:remind')
    ->everyMinute()->withoutOverlapping(5);
