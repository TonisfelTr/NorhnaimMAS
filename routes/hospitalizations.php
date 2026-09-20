<?php

use App\Http\Controllers\Doctors\HospitalizationReferralController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ВАЖНО
|--------------------------------------------------------------------------
| Подключайте этот файл ВНУТРИ существующей защищённой группы doctors,
| где уже заданы prefix('doctors') и as('doctors.').
*/

Route::prefix('referrals/hospital')
    ->as('referrals.hospital.')
    ->controller(HospitalizationReferralController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');

        Route::get('/planned', 'planned')->name('planned');
        Route::get('/emergency', 'emergency')->name('emergency');

        Route::get('/patient/{patient}/context', 'patientContext')->name('patient_context');

        Route::post('/', 'store')->name('store');
        Route::post('/analyze', 'analyze')
            ->middleware('throttle:6,1')
            ->name('analyze');
    });
