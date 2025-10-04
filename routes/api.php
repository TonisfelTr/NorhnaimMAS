<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Doctors\APIController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/medicines/{id}', [AjaxController::class, 'medicineList']);
Route::get('/doctors', [AjaxController::class, 'getDoctors'])->name('api-get-doctors');
Route::get('/diagnoses', [AjaxController::class, 'getDiagnoses'])->name('api-get-diagnoses');
Route::get('/patients', [AjaxController::class, 'getPatient'])
    ->name('api-get-patients')
    ->middleware([RoleMiddleware::class . (config('app.debug') ? ':admins|doctors' : ':doctors')]);

Route::prefix('/doctors')->group(function () {
    Route::get('/search-patients', [\App\Http\Controllers\Doctors\APIController::class, 'searchPatient'])->name('api.patients.search');
    Route::get('/search-patients/for/{param}', [APIController::class, 'searchPatientFor'])->name('api.patients.for.search');
    Route::get('/search-drugs', [\App\Http\Controllers\Doctors\APIController::class, 'searchDrugs'])->name('api.drugs.search');
    Route::get('/search-drugs/{latin_name}/forms', [\App\Http\Controllers\Doctors\APIController::class, 'getDrugForms'])->name('api.drugs.search.forms');
    Route::get('/search-params', [\App\Http\Controllers\Doctors\APIController::class, 'searchLabParameter'])->name('api.params.search');
    Route::get('/search-params/groups', [APIController::class, 'searchLabParameterGroup'])->name('api.params.search.groups');
    Route::post('/labtemplates/store', [APIController::class, 'storeLabTemplate'])->name('api.store.labtemplate');
});
