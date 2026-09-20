<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Doctors\APIController;
use App\Http\Controllers\OpenStreetMapOrganizationController;
use App\Http\Controllers\TestsController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/medicines/{id}', [AjaxController::class, 'medicineList']);
Route::get('/doctors', [AjaxController::class, 'getDoctors'])->name('api-get-doctors');
Route::get('/psy-doctors', [AjaxController::class, 'getPsyDoctors'])->name('api-get-psy-doctors');
Route::get('/diagnoses', [AjaxController::class, 'getDiagnoses'])->name('api-get-diagnoses');

Route::get('/patients', [AjaxController::class, 'getPatient'])
    ->name('api-get-patients')
    ->middleware([
        RoleMiddleware::class
        . (config('app.debug') ? ':admins|doctors' : ':doctors')
    ]);

Route::get('/addresses', [AjaxController::class, 'searchAddress'])
    ->name('api-search-address');

Route::get('/insurance_organizations', [AjaxController::class, 'searchInsuranceOrganizations'])
    ->name('api-search-organizations');

/*
 * Бесплатный поиск клиник, больниц и (при наличии данных)
 * отдельных врачей через OpenStreetMap / Overpass.
 */
Route::get(
    '/osm-medical-organizations',
    [OpenStreetMapOrganizationController::class, 'search']
)
    ->name('api.osm-medical-organizations.search')
    ->middleware([
        RoleMiddleware::class
        . (config('app.debug') ? ':admins|doctors' : ':doctors'),
        'throttle:30,1',
    ]);

Route::get(
    '/osm-reverse-geocode',
    [OpenStreetMapOrganizationController::class, 'reverseGeocode']
)
    ->name('api.osm-reverse')
    ->middleware([
        RoleMiddleware::class
        . (config('app.debug') ? ':admins|doctors' : ':doctors'),
        'throttle:20,1',
    ]);

Route::prefix('/doctors')->group(function () {
    Route::get('/search-patients', [AjaxController::class, 'searchPatient'])
        ->name('api.patients.search');

    Route::get('/search-patients/for/{param}', [AjaxController::class, 'searchPatientFor'])
        ->name('api.patients.for.search');

    Route::get('/search-drugs', [AjaxController::class, 'searchDrugs'])
        ->name('api.drugs.search');

    Route::get('/search-drugs/{latin_name}/forms', [AjaxController::class, 'getDrugForms'])
        ->name('api.drugs.search.forms');

    Route::get('/search-params', [AjaxController::class, 'searchLabParameter'])
        ->name('api.params.search');

    Route::get('/search-params/groups', [AjaxController::class, 'searchLabParameterGroup'])
        ->name('api.params.search.groups');

    Route::get('/sarch-patients', [AjaxController::class, 'searchCandlePatient'])
        ->name('api.patients.cardssearch');

    Route::post('/labtemplates/store', [APIController::class, 'storeLabTemplate'])
        ->name('api.store.labtemplate');

    Route::get('/search-tests', [AjaxController::class, 'searchTestsForPatient'])
        ->name('api.search.tests');

    Route::match(
        ['get', 'post'],
        '/prescription-autofill',
        [APIController::class, 'prescriptionAutofill']
    )->name('api.autofill.prescription');
});

Route::middleware('doctor.lock')->group(function () {
    Route::post('/tests/{session}/autosave', [APIController::class, 'autosave'])
        ->name('api.tests.autosave');
});
