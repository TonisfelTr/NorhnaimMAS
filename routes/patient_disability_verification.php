<?php

use App\Http\Controllers\Doctors\PatientDisabilityVerificationController;
use App\Http\Middleware\DoctorOrHospitalAdmin;
use Illuminate\Support\Facades\Route;

// NORHNAIM_DISABILITY_VERIFICATION_ACTION_V1
Route::middleware(['auth', DoctorOrHospitalAdmin::class])
    ->post(
        '/doctors/patients/{patient:id}/disability/verify-document',
        [PatientDisabilityVerificationController::class, 'verify']
    )
    ->whereNumber('patient')
    ->middleware('throttle:10,1')
    ->name('doctors.patients.disability.verify-document');
