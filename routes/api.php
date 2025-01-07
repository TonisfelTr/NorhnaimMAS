<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Doctors\APIController;
use Illuminate\Support\Facades\Route;

Route::get('/medicines/{id}', [AjaxController::class, 'medicineList']);
Route::get('/doctors', [AjaxController::class, 'getDoctors'])->name('api-get-doctors');
Route::get('/patients', [AjaxController::class, 'getPatient'])->name('api-get-patients');

Route::prefix('/doctors')->group(function () {
    Route::get('/search-patients', [\App\Http\Controllers\Doctors\APIController::class, 'searchPatient'])->name('api.patients.search');
    Route::get('/search-drugs', [\App\Http\Controllers\Doctors\APIController::class, 'searchDrugs'])->name('api.drugs.search');
    Route::get('/search-drugs/{latin_name}/forms', [\App\Http\Controllers\Doctors\APIController::class, 'getDrugForms'])->name('api.drugs.search.forms');
});
