<?php

declare(strict_types=1);

use App\Http\Controllers\Doctors\ClinicTelemedicineSettingsController;
use App\Http\Middleware\ClinicSettingsAccessMiddleware;
use App\Http\Middleware\DoctorHardLockMiddleware;
use App\Http\Middleware\EnsureClinicTelemedicineSettings;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth',
    ClinicSettingsAccessMiddleware::class,
    DoctorHardLockMiddleware::class,
    EnsureClinicTelemedicineSettings::class,
])->prefix('doctors/settings/telemedicine')->name('doctors.settings.telemedicine.')
    ->group(static function (): void {
        Route::get('/', [ClinicTelemedicineSettingsController::class, 'index'])->name('index');
        Route::post('/', [ClinicTelemedicineSettingsController::class, 'update'])->name('update');
    });
