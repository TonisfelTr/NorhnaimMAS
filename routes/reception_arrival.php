<?php

use App\Http\Controllers\Doctors\ReceptionArrivalController;
use Illuminate\Support\Facades\Route;

// NORHNAIM_REGISTRY_APPOINTMENT_ARRIVAL_V1
Route::middleware(['auth'])
    ->post(
        '/doctors/reception/records/{registryRecord}/arrived',
        [ReceptionArrivalController::class, 'confirm']
    )
    ->whereNumber('registryRecord')
    ->name('doctors.reception.arrival.confirm');
