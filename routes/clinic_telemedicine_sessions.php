<?php

use App\Http\Controllers\Doctors\TelemedicineProviderSessionController;
use App\Http\Controllers\TelemedicineInvitationController;
use App\Http\Middleware\DoctorHardLockMiddleware;
use Illuminate\Support\Facades\Route;

// Loaded by its service provider. Every write retains web CSRF protection.
Route::middleware(['web', 'auth', DoctorHardLockMiddleware::class, 'throttle:30,1'])->group(function (): void {
    $path = '/doctors/patients/{patient}/telemedicine-connection/{recordId}';
    Route::get($path, [TelemedicineProviderSessionController::class, 'show'])
        ->whereNumber(['patient', 'recordId'])->name('doctors.telemedicine-session.show');
    Route::post($path . '/prepare', [TelemedicineProviderSessionController::class, 'prepare'])
        ->whereNumber(['patient', 'recordId'])->name('doctors.telemedicine-session.prepare');
    Route::post($path . '/recover', [TelemedicineProviderSessionController::class, 'recover'])
        ->whereNumber(['patient', 'recordId'])->name('doctors.telemedicine-session.recover');
    Route::post($path . '/doctor-join', [TelemedicineProviderSessionController::class, 'doctorJoin'])
        ->whereNumber(['patient', 'recordId'])->name('doctors.telemedicine-session.doctor-join');
});

// The link is scoped to one random meeting ID and the exact scheduled UTC instant.
// It is not a general patient account and exposes no medical record.
Route::middleware(['web', 'signed', 'throttle:30,1'])->group(function (): void {
    Route::get('/telemedicine/invitation/{meeting}', [TelemedicineInvitationController::class, 'show'])
        ->whereUuid('meeting')->name('telemedicine.invitation.show');
    Route::post('/telemedicine/invitation/{meeting}/join', [TelemedicineInvitationController::class, 'join'])
        ->whereUuid('meeting')->name('telemedicine.invitation.join');
});
