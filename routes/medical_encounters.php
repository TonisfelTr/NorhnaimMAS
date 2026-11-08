<?php

use App\Http\Controllers\Doctors\MedicalEncounterController;
use App\Http\Middleware\DoctorOrHospitalAdmin;
use Illuminate\Support\Facades\Route;

// Imported once from web.php. Keep exactly the same entry middleware as the
// full medical-card GET route. Fine-grained patient/clinic authorization happens
// again inside MedicalEncounterController.
Route::middleware(['auth', DoctorOrHospitalAdmin::class])
    ->prefix('doctors')->as('doctors.')
    ->group(function (): void {
        Route::post('/patients/{patient:id}/visits/{registryRecord}/encounter',
            [MedicalEncounterController::class, 'save'])
            ->whereNumber('registryRecord')->middleware('throttle:30,1')
            ->name('patients.visits.encounter.save');

        Route::get('/patients/{patient:id}/visits/{registryRecord}/protocol',
            [MedicalEncounterController::class, 'printProtocol'])
            ->whereNumber('registryRecord')
            ->name('patients.visits.protocol.print');
    });
