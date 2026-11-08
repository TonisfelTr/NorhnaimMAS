<?php

use App\Http\Controllers\Doctors\DoctorAppointmentTimezoneController;
use Illuminate\Support\Facades\Route;

// NORHNAIM_DOCTOR_APPOINTMENT_TIMEZONE_V1
Route::middleware(['auth'])
    ->put(
        '/doctors/settings/doctors/{doctor}/appointment-timezone',
        [DoctorAppointmentTimezoneController::class, 'update']
    )
    ->whereNumber('doctor')
    ->middleware('throttle:30,1')
    ->name('doctors.settings.doctor-appointment-timezone.update');
