<?php

use App\Http\Controllers\AjaxController;
use Illuminate\Support\Facades\Route;

Route::get('/medicines/{id}', [AjaxController::class, 'medicineList']);
Route::get('/doctors', [AjaxController::class, 'getDoctors'])->name('api-get-doctors');
Route::get('/patients', [AjaxController::class, 'getPatient'])->name('api-get-patients');
