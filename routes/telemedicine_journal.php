<?php

use App\Http\Controllers\Doctors\TelemedicineJournalController;
use Illuminate\Support\Facades\Route;

// Required from the root of web.php, outside legacy prefix/name groups.
// Both writes retain the web middleware's CSRF protection.
Route::middleware(['auth', 'throttle:30,1'])->group(function (): void {
    Route::post('/doctors/patients/{patient}/telemedicine-journal', [TelemedicineJournalController::class, 'store'])
        ->whereNumber('patient')->name('doctors.patients.telemedicine-journal.store');
    Route::delete('/doctors/patients/{patient}/telemedicine-journal/{registryRecord}', [TelemedicineJournalController::class, 'destroy'])
        ->whereNumber(['patient', 'registryRecord'])->name('doctors.patients.telemedicine-journal.destroy');
});

// The signaling server holds the audit secret; no secret is exposed in the browser.
Route::post('/internal/telemedicine/journal/authorize',
    [\App\Http\Controllers\Doctors\TelemedicineJournalGateController::class, 'check'])
    ->middleware('throttle:120,1')
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ])->name('internal.telemedicine.journal.authorize');
