<?php

use App\Http\Controllers\Doctors\MedicalVisitConfirmationController;
use App\Http\Controllers\Doctors\TelemedicineEventIngestController;
use Illuminate\Support\Facades\Route;

// Loaded from routes/web.php. Keep these routes outside unrelated legacy groups.
Route::middleware(['auth'])
    ->post('/doctors/patients/{patient}/visits/{registryRecord}/confirm',
        [MedicalVisitConfirmationController::class, 'store'])
    ->whereNumber('registryRecord')
    ->name('doctors.patients.visits.confirm');

// Signaling server -> Laravel. Authentication is HMAC over the *raw* request body.
// Both modern and legacy Laravel CSRF middleware are excluded for this ONE route.
Route::post('/internal/telemedicine/audit/events', [TelemedicineEventIngestController::class, 'store'])
    ->middleware('throttle:120,1')
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->name('internal.telemedicine.audit.events');

// NORHNAIM_TELEMED_JOURNAL_V1
require __DIR__ . '/telemedicine_journal.php';
