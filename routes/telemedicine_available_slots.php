<?php

use App\Http\Controllers\Doctors\TelemedicineAvailableSlotsController;
use App\Http\Middleware\DoctorOrHospitalAdmin;
use Illuminate\Support\Facades\Route;

// NORHNAIM_TELEMED_AVAILABLE_SLOTS_V1
Route::middleware(['auth', DoctorOrHospitalAdmin::class])
    ->get(
        '/doctors/patients/{patient:id}/telemedicine/available-slots',
        TelemedicineAvailableSlotsController::class
    )
    ->whereNumber('patient')
    ->middleware('throttle:60,1')
    ->name('doctors.patients.telemedicine.available-slots');
