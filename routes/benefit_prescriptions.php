<?php

use App\Http\Controllers\Doctors\PrescriptionsController;
use Illuminate\Support\Facades\Route;

// Required at the top level of routes/web.php; that file already has the web middleware.
Route::middleware('auth')->prefix('doctors/patients/{patient}/benefit-prescriptions')
    ->name('doctors.prescriptions.benefit.')->group(function (): void {
        Route::get('drug', [PrescriptionsController::class, 'benefitDrug'])->name('drug');
        Route::post('/', [PrescriptionsController::class, 'storeBenefit'])->name('store');
        Route::get('{benefit}/edit', [PrescriptionsController::class, 'editBenefit'])
            ->whereUuid('benefit')->name('edit');
        Route::get('{benefit}/print', [PrescriptionsController::class, 'printBenefit'])
            ->whereUuid('benefit')->name('print');
        Route::post('{benefit}/void', [PrescriptionsController::class, 'voidBenefit'])
            ->whereUuid('benefit')->name('void');
    });
